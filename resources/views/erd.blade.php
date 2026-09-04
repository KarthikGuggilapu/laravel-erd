<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Laravel ERD</title>

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
            font-family: Inter, system-ui, sans-serif;
            background: #0b1020;
            color: #fff;
        }

        .erd-app {
            width: 100%;
            height: 100%;
            display: flex;
            flex-direction: column;
        }

        .erd-header {
            height: 64px;
            min-height: 64px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 24px;
            border-bottom: 1px solid rgba(255,255,255,.08);
            background: #11182b;
            z-index: 10;
        }

        .erd-title {
            font-size: 18px;
            font-weight: 700;
        }

        .erd-status {
            font-size: 13px;
            color: #8ea0bd;
        }

        .erd-canvas {
            position: relative;
            flex: 1;
            overflow: auto;
            background:
                radial-gradient(
                    circle at 1px 1px,
                    rgba(255,255,255,.08) 1px,
                    transparent 0
                );
            background-size: 24px 24px;
        }

        .erd-workspace {
            position: relative;
            min-width: 1600px;
            min-height: 1200px;
            width: 100%;
            height: 100%;
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
            font-size: 18px;
            margin-bottom: 8px;
        }

        .erd-table {
            position: absolute;
            width: 280px;
            min-height: 100px;
            background: #11182b;
            border: 1px solid rgba(255,255,255,.12);
            border-radius: 10px;
            box-shadow: 0 12px 35px rgba(0,0,0,.3);
            overflow: hidden;
            user-select: none;
        }

        .erd-table-header {
            padding: 12px 14px;
            background: #18233b;
            border-bottom: 1px solid rgba(255,255,255,.08);
            cursor: move;
        }

        .erd-table-name {
            font-size: 15px;
            font-weight: 700;
            color: #fff;
        }

        .erd-table-meta {
            margin-top: 3px;
            font-size: 11px;
            color: #71809c;
        }

        .erd-columns {
            padding: 6px 0;
        }

        .erd-column {
            display: grid;
            grid-template-columns: 1fr auto;
            gap: 12px;
            padding: 7px 14px;
            border-bottom: 1px solid rgba(255,255,255,.05);
            font-size: 12px;
        }

        .erd-column:last-child {
            border-bottom: 0;
        }

        .erd-column-name {
            color: #dce5f5;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .erd-column-type {
            color: #7f91b0;
            white-space: nowrap;
        }

        .erd-badge {
            display: inline-block;
            margin-left: 5px;
            padding: 2px 5px;
            border-radius: 4px;
            font-size: 9px;
            font-weight: 700;
            background: rgba(255,255,255,.08);
            color: #9fb0cb;
        }

        .erd-footer {
            padding: 7px 14px;
            border-top: 1px solid rgba(255,255,255,.06);
            font-size: 10px;
            color: #61708a;
        }
    </style>
</head>

<body>

<div class="erd-app">

    <header class="erd-header">
        <div class="erd-title">
            Laravel ERD
        </div>

        <div class="erd-status">
            {{ count($migrations['migrations'] ?? []) }} migrations
            · Registry v{{ $metadata['version'] ?? 1 }}
        </div>
    </header>

    <main class="erd-canvas">
        <div class="erd-workspace" id="erdWorkspace"></div>
    </main>

</div>

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
                    operation: table.operation ?? null,
                    migration: migration.file ?? migration.id ?? null
                });
            } else {
                const existing = tables.get(table.name);

                if (table.operation === 'create') {
                    existing.columns = table.columns ?? existing.columns;
                } else if (table.columns?.length) {
                    existing.columns = [
                        ...existing.columns,
                        ...table.columns.filter(column =>
                            !existing.columns.some(
                                existingColumn =>
                                    existingColumn.name === column.name
                            )
                        )
                    ];
                }

                existing.operation = table.operation ?? existing.operation;
                existing.migration = migration.file ?? existing.migration;
            }
        });
    });

    function createTable(table, index) {
        const element = document.createElement('div');

        element.className = 'erd-table';

        const columnCount = table.columns.length;

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
                    ${escapeHtml(table.operation ?? 'table')}
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

            <div class="erd-footer">
                ${columnCount} column${columnCount === 1 ? '' : 's'}
            </div>
        `;

        const position = getPosition(index);

        element.style.left = `${position.x}px`;
        element.style.top = `${position.y}px`;

        makeDraggable(element);

        workspace.appendChild(element);
    }

    function getPosition(index) {
        const columns = 4;
        const column = index % columns;
        const row = Math.floor(index / columns);

        return {
            x: 60 + column * 350,
            y: 60 + row * 300
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

            element.style.zIndex = '100';

            document.body.style.userSelect = 'none';
        });

        document.addEventListener('mousemove', event => {
            if (!dragging) {
                return;
            }

            const deltaX = event.clientX - startX;
            const deltaY = event.clientY - startY;

            element.style.left = `${startLeft + deltaX}px`;
            element.style.top = `${startTop + deltaY}px`;
        });

        document.addEventListener('mouseup', () => {
            if (!dragging) {
                return;
            }

            dragging = false;
            document.body.style.userSelect = '';
        });
    }

    function escapeHtml(value) {
        return String(value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    if (tables.size === 0) {
        workspace.innerHTML = `
            <div class="erd-empty">
                <strong>Laravel ERD</strong>
                <span>No tables have been registered yet.</span>
            </div>
        `;
    } else {
        [...tables.values()].forEach((table, index) => {
            createTable(table, index);
        });
    }
</script>

</body>
</html>