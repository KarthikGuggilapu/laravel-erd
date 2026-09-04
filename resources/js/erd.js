import { createTableRenderer } from './tables.js';
import { createRelationRenderer } from './relations.js';
import { createLayoutEngine } from './layout.js';
import { createThemeManager } from './theme.js';

const canvas = document.getElementById('erdCanvas');
const stage = document.getElementById('erdStage');
const workspace = document.getElementById('erdWorkspace');
const relationsSvg = document.getElementById('erdRelations');
const relationLayer = document.getElementById('erdRelationLines');
const searchInput = document.getElementById('tableSearch');
const analyzeButton = document.getElementById('analyzeButton');
const refreshButton = document.getElementById('refreshButton');
const emptyAnalyzeButton = document.getElementById('emptyAnalyzeButton');
const tableCount = document.getElementById('tableCount');
const modelCount = document.getElementById('modelCount');
const relationCount = document.getElementById('relationCount');
const zoomInButton = document.getElementById('zoomIn');
const zoomOutButton = document.getElementById('zoomOut');
const resetViewButton = document.getElementById('resetView');
const zoomValue = document.getElementById('zoomValue');
const toast = document.getElementById('erdToast');
const selector = document.getElementById('tableSelector');
const selectorList = document.getElementById('selectorList');
const selectorSearch = document.getElementById('selectorSearch');
const selectorCount = document.getElementById('selectorCount');
const selectorToggle = document.getElementById('selectorToggle');
const selectAllButton = document.getElementById('selectAllTables');
const clearAllButton = document.getElementById('clearAllTables');
const viewStatus = document.getElementById('viewStatus');
const autoLayoutButton = document.getElementById('autoLayoutButton');

let tables = [];
let tableElements = [];
let selectedTables = new Set();
let categoryFilter = 'all';
let searchTerm = '';
let selectorFilter = '';
let viewMode = 'simple';
let zoom = 1;
let panX = 0;
let panY = 0;
let isPanning = false;
let panStartX = 0;
let panStartY = 0;
let initialPanX = 0;
let initialPanY = 0;

const theme = createThemeManager();
const layout = createLayoutEngine(() => tables, () => getVisibleTables());
const tableRenderer = createTableRenderer({
    workspace,
    getViewMode: () => viewMode,
    getZoom: () => zoom,
    onDrag: () => relationRenderer.draw(),
});
const relationRenderer = createRelationRenderer({
    svg: relationsSvg,
    layer: relationLayer,
    getTableElements: () => tableElements,
    getVisibleTables: () => getVisibleTables(),
    getViewMode: () => viewMode,
    getZoom: () => zoom,
});

function normalizeRelation(relation) {
    return {
        from_table: relation.from_table ?? relation.fromTable ?? relation.table ?? null,
        from_column: relation.from_column ?? relation.fromColumn ?? relation.local_column ?? relation.local ?? null,
        to_table: relation.to_table ?? relation.toTable ?? relation.referenced_table ?? relation.references_table ?? relation.on ?? null,
        to_column: relation.to_column ?? relation.toColumn ?? relation.referenced_column ?? relation.references_column ?? 'id',
        type: relation.type ?? 'belongsTo',
    };
}

function getRelations() {
    return (window.ERD.relations?.relations ?? [])
        .map(normalizeRelation)
        .filter(r => r.from_table && r.to_table);
}

function getTables() {
    const migrations = window.ERD.migrations?.migrations ?? [];
    const map = new Map();

    migrations.forEach(migration => {
        (migration.tables ?? []).forEach(table => {
            if (!table.name) return;
            if (!map.has(table.name)) {
                map.set(table.name, {
                    name: table.name,
                    columns: [...(table.columns ?? [])],
                    operation: table.operation ?? 'table',
                    migration: migration.file ?? migration.id ?? null,
                });
                return;
            }
            const existing = map.get(table.name);
            if (table.operation === 'create') existing.columns = [...(table.columns ?? existing.columns)];
            (table.columns ?? []).forEach(column => {
                if (!existing.columns.some(item => item.name === column.name)) existing.columns.push(column);
            });
            existing.operation = table.operation ?? existing.operation;
            existing.migration = migration.file ?? existing.migration;
        });
    });

    return [...map.values()];
}

function relationTableNames() {
    const names = new Set();
    getRelations().forEach(relation => {
        names.add(relation.from_table);
        names.add(relation.to_table);
    });
    return names;
}

function getVisibleTables() {
    const relationNames = relationTableNames();
    return tables.filter(table => {
        const categoryMatch = categoryFilter === 'all'
            || (categoryFilter === 'relationship' && relationNames.has(table.name))
            || (categoryFilter === 'isolated' && !relationNames.has(table.name));
        const searchMatch = !searchTerm || table.name.toLowerCase().includes(searchTerm);
        const selectionMatch = selectedTables.has(table.name);
        return categoryMatch && searchMatch && selectionMatch;
    });
}

function updateCounts() {
    const visible = getVisibleTables();
    const models = window.ERD.models?.models ?? [];
    const relations = relationRenderer.getVisibleRelationCount();
    tableCount.textContent = `${visible.length} table${visible.length === 1 ? '' : 's'}`;
    modelCount.textContent = `${models.length} model${models.length === 1 ? '' : 's'}`;
    relationCount.textContent = `${relations} relation${relations === 1 ? '' : 's'}`;
}

function renderSelector() {
    const relationNames = relationTableNames();
    const relations = getRelations();
    const filtered = tables.filter(table => !selectorFilter || table.name.toLowerCase().includes(selectorFilter));

    selectorList.innerHTML = filtered.map(table => {
        const checked = selectedTables.has(table.name);
        const count = relations.filter(r => r.from_table === table.name || r.to_table === table.name).length;
        const relationClass = relationNames.has(table.name) ? 'is-related' : 'is-isolated';
        return `
            <label class="erd-table-option ${relationClass}">
                <input type="checkbox" data-table-select="${escapeHtml(table.name)}" ${checked ? 'checked' : ''}>
                <span class="erd-option-name">${escapeHtml(table.name)}</span>
                <small>${count}</small>
            </label>`;
    }).join('') || '<div class="erd-selector-empty">No tables match.</div>';

    updateSelectorCount();
}

function updateSelectorCount() {
    selectorCount.textContent = `${selectedTables.size} of ${tables.length} selected`;
}

function applyVisibility() {
    const visibleNames = new Set(getVisibleTables().map(table => table.name));
    tableElements.forEach(item => item.element.classList.toggle('hidden', !visibleNames.has(item.name)));
    relationRenderer.draw();
    updateCounts();
}

function render(keepView = false) {
    tables = getTables();
    const names = new Set(tables.map(table => table.name));
    selectedTables = new Set([...selectedTables].filter(name => names.has(name)));
    if (!selectedTables.size && tables.length) selectedTables = new Set(tables.map(table => table.name));

    const hasTables = tables.length > 0;
    document.getElementById('erdEmptyState').classList.toggle('hidden', hasTables);
    if (!hasTables) {
        workspace.innerHTML = '';
        tableElements = [];
        relationLayer.innerHTML = '';
        renderSelector();
        updateCounts();
        return;
    }

    tableElements = tableRenderer.render(tables);
    layout.apply(tables, tableElements, getRelations());
    applyVisibility();
    renderSelector();
    if (!keepView) fitToVisible();
}

function setViewMode(mode) {
    viewMode = mode === 'detailed' ? 'detailed' : 'simple';
    viewStatus.textContent = viewMode === 'detailed' ? 'Detailed' : 'Simple';
    document.querySelectorAll('[data-view-mode]').forEach(button => button.classList.toggle('active', button.dataset.viewMode === viewMode));
    tableRenderer.setViewMode(viewMode);
    render(true);
    requestAnimationFrame(() => relationRenderer.draw());
}

function setCategoryFilter(filter) {
    categoryFilter = ['all', 'relationship', 'isolated'].includes(filter) ? filter : 'all';
    document.querySelectorAll('[data-table-filter]').forEach(button => button.classList.toggle('active', button.dataset.tableFilter === categoryFilter));
    applyVisibility();
    renderSelector();
    fitToVisible();
}

function setSelectedTable(name, checked) {
    if (checked) selectedTables.add(name);
    else selectedTables.delete(name);
    applyVisibility();
    renderSelector();
    if (getVisibleTables().length) fitToVisible();
}

function selectAll() {
    selectedTables = new Set(tables.map(table => table.name));
    applyVisibility();
    renderSelector();
    fitToVisible();
}

function clearAll() {
    selectedTables.clear();
    applyVisibility();
    renderSelector();
}

function updateView() {
    stage.style.transform = `translate(${panX}px, ${panY}px) scale(${zoom})`;
    zoomValue.textContent = `${Math.round(zoom * 100)}%`;
    document.documentElement.style.setProperty('--erd-zoom', zoom);
    requestAnimationFrame(() => relationRenderer.draw());
}

function setZoom(value, focusX = canvas.clientWidth / 2, focusY = canvas.clientHeight / 2) {
    const next = Math.min(1.8, Math.max(0.45, value));
    if (next === zoom) return;
    const scale = next / zoom;
    panX = focusX - (focusX - panX) * scale;
    panY = focusY - (focusY - panY) * scale;
    zoom = next;
    updateView();
}

function fitToVisible() {
    const visible = getVisibleTables();
    if (!visible.length) {
        zoom = 1;
        panX = 0;
        panY = 0;
        updateView();
        return;
    }

    const items = tableElements.filter(item => visible.some(t => t.name === item.name));
    const bounds = items.reduce((acc, item) => {
        const left = item.element.offsetLeft;
        const top = item.element.offsetTop;
        const right = left + item.element.offsetWidth;
        const bottom = top + item.element.offsetHeight;
        return {
            left: Math.min(acc.left, left),
            top: Math.min(acc.top, top),
            right: Math.max(acc.right, right),
            bottom: Math.max(acc.bottom, bottom),
        };
    }, { left: Infinity, top: Infinity, right: -Infinity, bottom: -Infinity });

    const padding = 100;
    const contentWidth = Math.max(1, bounds.right - bounds.left + padding * 2);
    const contentHeight = Math.max(1, bounds.bottom - bounds.top + padding * 2);
    const nextZoom = Math.min(1.35, Math.max(0.55, Math.min(canvas.clientWidth / contentWidth, canvas.clientHeight / contentHeight)));
    zoom = nextZoom;
    panX = canvas.clientWidth / 2 - ((bounds.left + bounds.right) / 2) * zoom;
    panY = canvas.clientHeight / 2 - ((bounds.top + bounds.bottom) / 2) * zoom;
    updateView();
}

function resetView() {
    fitToVisible();
}

function makePanHandlers() {
    canvas.addEventListener('mousedown', event => {
        if (event.target.closest('.erd-table, .erd-floating, .erd-bottom, .erd-footer')) return;
        isPanning = true;
        panStartX = event.clientX;
        panStartY = event.clientY;
        initialPanX = panX;
        initialPanY = panY;
        canvas.classList.add('is-panning');
    });

    document.addEventListener('mousemove', event => {
        if (!isPanning) return;
        panX = initialPanX + event.clientX - panStartX;
        panY = initialPanY + event.clientY - panStartY;
        updateView();
    });

    document.addEventListener('mouseup', () => {
        isPanning = false;
        canvas.classList.remove('is-panning');
    });

    canvas.addEventListener('wheel', event => {
        if (event.ctrlKey || event.metaKey) {
            event.preventDefault();
            setZoom(zoom + (event.deltaY < 0 ? 0.1 : -0.1), event.clientX, event.clientY);
        } else {
            panX -= event.deltaX;
            panY -= event.deltaY;
            updateView();
        }
    }, { passive: false });
}

async function analyzeSchema() {
    setLoading(true);
    try {
        const response = await fetch(window.ERD_CONFIG.refreshUrl, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').value,
                'Accept': 'application/json',
            },
        });
        const data = await response.json();
        if (!response.ok || !data.success) throw new Error(data.message || 'Schema analysis failed.');
        showToast(`Schema analyzed: ${data.migrations} migrations, ${data.models} models, ${data.relations ?? 0} relations.`);
        await reloadRegistry();
    } catch (error) {
        showToast(error.message || 'Schema analysis failed.');
    } finally {
        setLoading(false);
    }
}

async function reloadRegistry() {
    const response = await fetch(window.location.href, { headers: { Accept: 'text/html' } });
    const html = await response.text();
    const parser = new DOMParser();
    const documentObject = parser.parseFromString(html, 'text/html');
    const script = [...documentObject.scripts].find(item => item.textContent.includes('window.ERD ='));
    if (!script) return window.location.reload();
    const match = script.textContent.match(/window\.ERD\s*=\s*(\{[\s\S]*?\});/);
    if (!match) return window.location.reload();
    try {
        window.ERD = JSON.parse(match[1]);
        render();
    } catch {
        window.location.reload();
    }
}

function setLoading(loading) {
    [analyzeButton, refreshButton, emptyAnalyzeButton].forEach(button => { if (button) button.disabled = loading; });
    analyzeButton.innerHTML = loading ? '<span>◌</span><span>Analyzing...</span>' : '<span class="erd-button-icon">◈</span><span class="erd-button-text">Analyze Schema</span>';
    refreshButton.innerHTML = loading ? '<span>◌</span><span>Working...</span>' : '<span class="erd-button-icon">↻</span><span class="erd-button-text">Refresh</span>';
}

function showToast(message) {
    toast.textContent = message;
    toast.classList.add('show');
    clearTimeout(showToast.timer);
    showToast.timer = setTimeout(() => toast.classList.remove('show'), 3200);
}

function escapeHtml(value) {
    return String(value).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#039;');
}

document.querySelectorAll('[data-view-mode]').forEach(button => button.addEventListener('click', () => setViewMode(button.dataset.viewMode)));
document.querySelectorAll('[data-table-filter]').forEach(button => button.addEventListener('click', () => setCategoryFilter(button.dataset.tableFilter)));
searchInput.addEventListener('input', event => { searchTerm = event.target.value.trim().toLowerCase(); applyVisibility(); });
selectorSearch.addEventListener('input', event => { selectorFilter = event.target.value.trim().toLowerCase(); renderSelector(); });
selectorList.addEventListener('change', event => { const name = event.target.dataset.tableSelect; if (name) setSelectedTable(name, event.target.checked); });
selectAllButton.addEventListener('click', selectAll);
clearAllButton.addEventListener('click', clearAll);
selectorToggle.addEventListener('click', () => { selector.classList.toggle('collapsed'); selectorToggle.textContent = selector.classList.contains('collapsed') ? '+' : '−'; });
zoomInButton.addEventListener('click', () => setZoom(zoom + 0.1));
zoomOutButton.addEventListener('click', () => setZoom(zoom - 0.1));
resetViewButton.addEventListener('click', resetView);
autoLayoutButton?.addEventListener('click', () => { layout.apply(tables, tableElements, getRelations()); fitToVisible(); showToast('Smart layout applied.'); });
analyzeButton.addEventListener('click', analyzeSchema);
refreshButton.addEventListener('click', analyzeSchema);
emptyAnalyzeButton.addEventListener('click', analyzeSchema);
window.erdRefreshRelations = () => relationRenderer.draw();
theme.bind();
makePanHandlers();
render();
