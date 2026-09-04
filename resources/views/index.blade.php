<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Laravel ERD</title>

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <style>
        * {
            box-sizing: border-box;
        }

        html,
        body {
            margin: 0;
            width: 100%;
            height: 100%;
            overflow: hidden;
            font-family: Inter, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            background: #080d1b;
            color: #fff;
        }

        button,
        input {
            font: inherit;
        }

        .erd-app {
            width: 100%;
            height: 100%;
            display: flex;
            flex-direction: column;
        }

        .erd-nav {
            height: 68px;
            min-height: 68px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 22px;
            background: #11182b;
            border-bottom: 1px solid rgba(255,255,255,.08);
            z-index: 20;
        }

        .erd-brand {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .erd-logo {
            width: 36px;
            height: 36px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 9px;
            background: #1d2944;
            color: #8fb4ff;
            font-size: 17px;
            font-weight: 800;
        }

        .erd-brand-text {
            display: flex;
            flex-direction: column;
            gap: 2px;
        }

        .erd-brand-title {
            font-size: 16px;
            font-weight: 750;
        }

        .erd-brand-subtitle {
            font-size: 11px;
            color: #71809c;
        }

        .erd-actions {
            display: flex;
            align-items: center;
            gap: 9px;
        }

        .erd-search {
            width: 240px;
            height: 38px;
            padding: 0 13px;
            border: 1px solid rgba(255,255,255,.1);
            border-radius: 8px;
            outline: none;
            background: #0b1223;
            color: #fff;
        }

        .erd-search::placeholder {
            color: #61708a;
        }

        .erd-search:focus {
            border-color: rgba(143,180,255,.5);
        }

        .erd-button {
            height: 38px;
            padding: 0 14px;
            border: 1px solid rgba(255,255,255,.1);
            border-radius: 8px;
            background: #1a2741;
            color: #fff;
            cursor: pointer;
            font-size: 12px;
            font-weight: 650;
        }

        .erd-button:hover {
            background: #223352;
        }

        .erd-button.primary {
            background: #315ea8;
            border-color: #3c70c5;
        }

        .erd-button.primary:hover {
            background: #3b6fc0;
        }

        .erd-button:disabled {
            opacity: .6;
            cursor: wait;
        }

        .erd-content {
            flex: 1;
            min-height: 0;
            position: relative;
        }

        .erd-canvas {
            width: 100%;
            height: 100%;
            overflow: auto;
            background:
                radial-gradient(
                    circle at 1px 1px,
                    rgba(255,255,255,.075) 1px,
                    transparent 0
                );
            background-size: 24px 24px;
        }

        .erd-workspace {
            position: relative;
            width: 1800px;
            min-height: 1300px;
            padding: 50px;
        }

        .erd-empty {
            position: absolute;
            inset: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            color: #71809c;
        }

        .erd-empty strong {
            color: #fff;
            font-size: 20px;
            margin-bottom: 8px;
        }

        .erd-empty span {
            font-size: 13px;
            margin-bottom: 18px;
        }

        .erd-table {
            position: absolute;
            width: 290px;
            background: #11182b;
            border: 1px solid rgba(255,255,255,.11);
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 15px 40px rgba(0,0,0,.28);
            user-select: none;
        }

        .erd-table-header {
            padding: 13px 14px;
            background: #18233b;
            border-bottom: 1px solid rgba(255,255,255,.07);
            cursor: move;
        }

        .erd-table-name {
            font-size: 14px;
            font-weight: 750;
        }

        .erd-table-meta {
            margin-top: 4px;
            color: #71809c;
            font-size: 10px;
        }

        .erd-columns {
            padding: 4px 0;
        }

        .erd-column {
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto;
            gap: 10px;
            padding: 7px 13px;
            border-bottom: 1px solid rgba(255,255,255,.045);
            font-size: 11px;
        }

        .erd-column:last-child {
            border-bottom: 0;
        }

        .erd-column-name {
            min-width: 0;
            color: #d9e2f2;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .erd-column-type {
            color: #7486a3;
            white-space: nowrap;
        }

        .erd-badge {
            display: inline-block;
            margin-left: 4px;
            padding: 2px 4px;
            border-radius: 3px;
            background: rgba(255,255,255,.08);
            color: #9aaac2;
            font-size: 8px;
            font-weight: 750;
        }

        .erd-table-footer {
            padding: 7px 13px;
            border-top: 1px solid rgba(255,255,255,.06);
            color: #596982;
            font-size: 10px;
        }

        .erd-toast {
            position: fixed;
            right: 20px;
            bottom: 20px;
            padding: 11px 15px;
            border: 1px solid rgba(255,255,255,.1);
            border-radius: 8px;
            background: #17213a;
            color: #dce6f6;
            font-size: 12px;
            box-shadow: 0 12px 30px rgba(0,0,0,.3);
            opacity: 0;
            transform: translateY(10px);
            pointer-events: none;
            transition: .2s ease;
            z-index: 100;
        }

        .erd-toast.show {
            opacity: 1;
            transform: translateY(0);
        }

        .erd-footer {
            height: 32px;
            min-height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-top: 1px solid rgba(255,255,255,.06);
            background: #0d1425;
            color: #596982;
            font-size: 10px;
        }

        .erd-footer strong {
            margin-left: 4px;
            color: #8a9bb7;
            font-weight: 600;
        }

        @media (max-width: 900px) {
            .erd-search {
                width: 170px;
            }

            .erd-brand-subtitle {
                display: none;
            }
        }
    </style>
</head>

<body>

<div class="erd-app">

    <nav class="erd-nav">

        <div class="erd-brand">
            <div class="erd-logo">
                E
            </div>

            <div class="erd-brand-text">
                <div class="erd-brand-title">
                    Laravel ERD
                </div>

                <div class="erd-brand-subtitle">
                    Database schema visualizer
                </div>
            </div>
        </div>

        <div class="erd-actions">

            <input
                type="search"
                id="tableSearch"
                class="erd-search"
                placeholder="Search tables..."
            >

            <button
                type="button"
                class="erd-button primary"
                id="analyzeButton"
            >
                Analyze Schema
            </button>

            <button
                type="button"
                class="erd-button"
                id="refreshButton"
            >
                Refresh
            </button>

        </div>

    </nav>

    <main class="erd-content">

        <div class="erd-canvas">
            <div
                class="erd-workspace"
                id="erdWorkspace"
            ></div>
        </div>

    </main>

</div>

<div
    class="erd-toast"
    id="erdToast"
></div>

<footer class="erd-footer">
    Developed by <strong>Karthik Guggilapu</strong>
</footer>

<script>
    window.ERD = {!! json_encode([
        'metadata' => $metadata,
        'migrations' => $migrations,
        'models' => $models,
        'relations' => $relations,
        'history' => $history,
        'layout' => $layout,
    ]) !!};

    const workspace = document.getElementById('erdWorkspace');
    const searchInput = document.getElementById('tableSearch');
    const analyzeButton = document.getElementById('analyzeButton');
    const refreshButton = document.getElementById('refreshButton');
    const toast = document.getElementById('erdToast');

    let tableElements = [];

    function getTables() {
        const migrations = window.ERD.migrations?.migrations ?? [];
        const tables = new Map();

        migrations.forEach(migration => {
            const migrationTables = migration.tables ?? [];

            migrationTables.forEach(table => {
                if (!table.name) {
                    return;
                }

                if (!tables.has(table.name)) {
                    tables.set(table.name, {
                        name: table.name,
                        columns: table.columns ?? [],
                        operation: table.operation ?? 'table',
                        migration: migration.file ?? migration.id ?? null
                    });

                    return;
                }

                const existing = tables.get(table.name);

                if (table.operation === 'create') {
                    existing.columns = table.columns ?? existing.columns;
                }

                if (table.columns?.length) {
                    const existingNames = new Set(
                        existing.columns.map(column => column.name)
                    );

                    table.columns.forEach(column => {
                        if (!existingNames.has(column.name)) {
                            existing.columns.push(column);
                        }
                    });
                }

                existing.operation = table.operation ?? existing.operation;
                existing.migration = migration.file ?? existing.migration;
            });
        });

        return [...tables.values()];
    }

    function renderTables() {
        workspace.innerHTML = '';
        tableElements = [];

        const tables = getTables();

        if (!tables.length) {
            workspace.innerHTML = `
                <div class="erd-empty">
                    <strong>No schema analyzed yet</strong>
                    <span>Analyze your Laravel migrations to build the ERD.</span>
                    <button
                        type="button"
                        class="erd-button primary"
                        onclick="analyzeSchema()"
                    >
                        Analyze Schema
                    </button>
                </div>
            `;

            return;
        }

        tables.forEach((table, index) => {
            const element = createTable(table, index);

            tableElements.push({
                element,
                name: table.name.toLowerCase()
            });
        });

        applySearch();
    }

    function createTable(table, index) {
        const element = document.createElement('div');

        element.className = 'erd-table';

        const columns = table.columns.map(column => {
            const badges = [];

            if (column.primary) {
                badges.push('PK');
            }

            if (column.unique) {
                badges.push('UQ');
            }

            if (column.nullable) {
                badges.push('NULL');
            }

            return `
                <div class="erd-column">
                    <div class="erd-column-name">
                        ${escapeHtml(column.name ?? '')}
                        ${badges.map(badge => `
                            <span class="erd-badge">${badge}</span>
                        `).join('')}
                    </div>

                    <div class="erd-column-type">
                        ${escapeHtml(column.type ?? '')}
                    </div>
                </div>
            `;
        }).join('');

        element.innerHTML = `
            <div class="erd-table-header">
                <div class="erd-table-name">
                    ${escapeHtml(table.name)}
                </div>

                <div class="erd-table-meta">
                    ${escapeHtml(table.operation)}
                </div>
            </div>

            <div class="erd-columns">
                ${columns || `
                    <div class="erd-column">
                        <div class="erd-column-name">
                            No columns detected
                        </div>
                    </div>
                `}
            </div>

            <div class="erd-table-footer">
                ${table.columns.length}
                column${table.columns.length === 1 ? '' : 's'}
            </div>
        `;

        const position = getPosition(index);

        element.style.left = `${position.x}px`;
        element.style.top = `${position.y}px`;

        makeDraggable(element);

        workspace.appendChild(element);

        return element;
    }

    function getPosition(index) {
        const columns = 4;
        const column = index % columns;
        const row = Math.floor(index / columns);

        return {
            x: 50 + column * 350,
            y: 50 + row * 300
        };
    }

    function makeDraggable(element) {
        const header = element.querySelector('.erd-table-header');

        let dragging = false;
        let startX = 0;
        let startY = 0;
        let startLeft = 0;
        let startTop = 0;

        header.addEventListener('mousedown', event => {
            dragging = true;

            startX = event.clientX;
            startY = event.clientY;

            startLeft = element.offsetLeft;
            startTop = element.offsetTop;

            element.style.zIndex = '50';

            document.body.style.userSelect = 'none';
        });

        document.addEventListener('mousemove', event => {
            if (!dragging) {
                return;
            }

            const deltaX = event.clientX - startX;
            const deltaY = event.clientY - startY;

            element.style.left = `${Math.max(0, startLeft + deltaX)}px`;
            element.style.top = `${Math.max(0, startTop + deltaY)}px`;
        });

        document.addEventListener('mouseup', () => {
            if (!dragging) {
                return;
            }

            dragging = false;
            document.body.style.userSelect = '';
        });
    }

    function applySearch() {
        const search = searchInput.value
            .trim()
            .toLowerCase();

        tableElements.forEach(item => {
            item.element.style.display =
                !search || item.name.includes(search)
                    ? ''
                    : 'none';
        });
    }

    async function analyzeSchema() {
        setLoading(true);

        try {
            const response = await fetch(
                '{{ route('erd.refresh') }}',
                {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document
                            .querySelector('meta[name="csrf-token"]')
                            .getAttribute('content'),

                        'Accept': 'application/json'
                    }
                }
            );

            const data = await response.json();

            if (!response.ok || !data.success) {
                throw new Error(
                    data.message || 'Schema analysis failed.'
                );
            }

            showToast(
                `Schema analyzed: ${data.migrations} migrations, ${data.models} models.`
            );

            await reloadRegistry();

        } catch (error) {
            showToast(
                error.message || 'Schema analysis failed.'
            );
        } finally {
            setLoading(false);
        }
    }

    async function reloadRegistry() {
        const response = await fetch(
            window.location.href,
            {
                headers: {
                    'Accept': 'text/html'
                }
            }
        );

        const html = await response.text();

        const parser = new DOMParser();
        const documentObject = parser.parseFromString(
            html,
            'text/html'
        );

        const script = [...documentObject.scripts]
            .find(script => script.textContent.includes('window.ERD'));

        if (!script) {
            window.location.reload();

            return;
        }

        const match = script.textContent.match(
            /window\.ERD\s*=\s*(\{[\s\S]*?\});/
        );

        if (!match) {
            window.location.reload();

            return;
        }

        try {
            window.ERD = JSON.parse(match[1]);
            renderTables();
        } catch {
            window.location.reload();
        }
    }

    function setLoading(loading) {
        analyzeButton.disabled = loading;
        refreshButton.disabled = loading;

        analyzeButton.textContent =
            loading
                ? 'Analyzing...'
                : 'Analyze Schema';

        refreshButton.textContent =
            loading
                ? 'Working...'
                : 'Refresh';
    }

    function showToast(message) {
        toast.textContent = message;
        toast.classList.add('show');

        setTimeout(() => {
            toast.classList.remove('show');
        }, 3000);
    }

    function escapeHtml(value) {
        return String(value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    analyzeButton.addEventListener(
        'click',
        analyzeSchema
    );

    refreshButton.addEventListener(
        'click',
        analyzeSchema
    );

    searchInput.addEventListener(
        'input',
        applySearch
    );

    renderTables();
</script>

</body>
</html>