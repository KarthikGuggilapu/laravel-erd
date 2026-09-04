export function createTableRenderer({ workspace, getViewMode, onDrag, getZoom }) {
    let viewMode = getViewMode();

    function setViewMode(mode) { viewMode = mode; }

    function render(tables) {
        workspace.innerHTML = '';
        return tables.map(table => ({ element: createTable(table), name: table.name, table }));
    }

    function createTable(table) {
        const element = document.createElement('div');
        element.className = 'erd-table';
        element.dataset.tableName = table.name;
        element.innerHTML = `
            <div class="erd-table-header">
                <div class="erd-table-title-row">
                    <div class="erd-table-name">${escapeHtml(table.name)}</div>
                    <div class="erd-table-operation">${escapeHtml(table.operation ?? 'table')}</div>
                </div>
                <div class="erd-table-meta">${escapeHtml(table.migration ?? '')}</div>
            </div>
            <div class="erd-columns">${renderColumns(table.columns ?? [])}</div>
            <div class="erd-table-footer">${(table.columns ?? []).length} column${(table.columns ?? []).length === 1 ? '' : 's'}</div>
        `;
        workspace.appendChild(element);
        makeDraggable(element);
        return element;
    }

    function renderColumns(columns) {
        if (viewMode === 'simple') return '';
        if (!columns.length) return '<div class="erd-column"><div class="erd-column-name">No columns detected</div></div>';
        return columns.map(column => {
            const badges = [];
            if (column.primary) badges.push('<span class="erd-badge primary">PK</span>');
            if (column.unique) badges.push('<span class="erd-badge unique">UQ</span>');
            if (column.nullable) badges.push('<span class="erd-badge">NULL</span>');
            return `
                <div class="erd-column" data-column-name="${escapeHtml(column.name ?? '')}">
                    <div class="erd-column-name"><span class="erd-column-port"></span>${escapeHtml(column.name ?? '')}${badges.length ? `<span class="erd-badges">${badges.join('')}</span>` : ''}</div>
                    <div class="erd-column-type">${escapeHtml(column.type ?? '')}</div>
                </div>`;
        }).join('');
    }

    function makeDraggable(element) {
        const header = element.querySelector('.erd-table-header');
        let dragging = false;
        let startX = 0;
        let startY = 0;
        let startLeft = 0;
        let startTop = 0;

        header.addEventListener('mousedown', event => {
            if (event.button !== 0) return;
            event.stopPropagation();
            dragging = true;
            startX = event.clientX;
            startY = event.clientY;
            startLeft = parseFloat(element.style.left) || 0;
            startTop = parseFloat(element.style.top) || 0;
            element.classList.add('dragging');
            document.body.style.userSelect = 'none';
        });

        const move = event => {
            if (!dragging) return;
            const zoom = getZoom() || 1;
            element.style.left = `${Math.max(20, startLeft + (event.clientX - startX) / zoom)}px`;
            element.style.top = `${Math.max(20, startTop + (event.clientY - startY) / zoom)}px`;
            onDrag();
        };

        const up = () => {
            if (!dragging) return;
            dragging = false;
            element.classList.remove('dragging');
            document.body.style.userSelect = '';
        };

        document.addEventListener('mousemove', move);
        document.addEventListener('mouseup', up);
    }

    function escapeHtml(value) {
        return String(value).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#039;');
    }

    return { render, setViewMode };
}
