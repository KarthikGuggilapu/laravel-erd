<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>{{ config('app.name') }} ERD</title>

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link
        rel="icon"
        type="image/svg+xml"
        href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 64 64'%3E%3Crect width='64' height='64' rx='14' fill='%23121d32'/%3E%3Crect x='12' y='12' width='16' height='16' rx='3' fill='%234d83e8'/%3E%3Crect x='36' y='12' width='16' height='16' rx='3' fill='%2355c98a'/%3E%3Crect x='12' y='36' width='16' height='16' rx='3' fill='%235f7fae'/%3E%3Cpath d='M28 20h8M20 28v8M44 28v8M28 44h8' stroke='%23f3f7ff' stroke-width='3' stroke-linecap='round'/%3E%3C/svg%3E"
    >

    <style>
        :root {
            --bg: #080d19;
            --panel: #0e1627;
            --panel-2: #121d32;
            --panel-3: #17233b;
            --border: rgba(255,255,255,.08);
            --border-strong: rgba(255,255,255,.12);
            --text: #f3f7ff;
            --muted: #72819b;
            --muted-2: #56657f;
            --accent: #4d83e8;
            --accent-hover: #5b91f5;
            --success: #55c98a;
            --relation: #5f7fae;
        }

        * {
            box-sizing: border-box;
        }

        html,
        body {
            width: 100%;
            height: 100%;
            margin: 0;
            overflow: hidden;
            background: var(--bg);
            color: var(--text);
            font-family:
                Inter,
                ui-sans-serif,
                system-ui,
                -apple-system,
                BlinkMacSystemFont,
                "Segoe UI",
                sans-serif;
        }

        body {
            user-select: none;
        }

        button,
        input {
            font: inherit;
        }

        button {
            border: 0;
        }

        .erd-app {
            width: 100%;
            height: 100%;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            background: var(--bg);
        }

        .erd-header {
            height: 70px;
            min-height: 70px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
            padding: 0 20px;
            background: rgba(14,22,39,.96);
            border-bottom: 1px solid var(--border);
            z-index: 100;
        }

        .erd-brand {
            display: flex;
            align-items: center;
            gap: 12px;
            min-width: 220px;
        }

        .erd-logo {
            width: 38px;
            height: 38px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 10px;
            background: linear-gradient(145deg,#263b65,#172746);
            border: 1px solid rgba(117,157,231,.18);
            color: #9fc0ff;
            font-size: 17px;
            font-weight: 800;
            box-shadow: 0 8px 22px rgba(0,0,0,.2);
        }

        .erd-brand-copy {
            display: flex;
            flex-direction: column;
            gap: 2px;
            min-width: 0;
        }

        .erd-brand-title {
            max-width: 350px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            font-size: 15px;
            font-weight: 750;
            letter-spacing: -.2px;
        }

        .erd-brand-subtitle {
            color: var(--muted);
            font-size: 10px;
        }

        .erd-toolbar {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 8px;
            min-width: 0;
        }

        .erd-search-wrap {
            position: relative;
        }

        .erd-search {
            width: 230px;
            height: 38px;
            padding: 0 13px 0 36px;
            border: 1px solid var(--border);
            border-radius: 9px;
            outline: none;
            background: #09111f;
            color: var(--text);
            font-size: 12px;
            transition: .18s ease;
        }

        .erd-search:hover {
            border-color: var(--border-strong);
        }

        .erd-search:focus {
            border-color: rgba(77,131,232,.65);
            box-shadow: 0 0 0 3px rgba(77,131,232,.08);
        }

        .erd-search::placeholder {
            color: #5e6d86;
        }

        .erd-search-icon {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #61718b;
            font-size: 13px;
            pointer-events: none;
        }

        .erd-button {
            height: 38px;
            padding: 0 14px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 7px;
            border: 1px solid var(--border);
            border-radius: 9px;
            background: #17233a;
            color: #dce7f8;
            cursor: pointer;
            font-size: 11px;
            font-weight: 650;
            transition:
                background .18s ease,
                border-color .18s ease,
                transform .18s ease;
        }

        .erd-button:hover {
            background: #1d2c48;
            border-color: var(--border-strong);
        }

        .erd-button:active {
            transform: translateY(1px);
        }

        .erd-button.primary {
            background: var(--accent);
            border-color: #6093ef;
            color: #fff;
        }

        .erd-button.primary:hover {
            background: var(--accent-hover);
        }

        .erd-button:disabled {
            opacity: .55;
            cursor: wait;
            transform: none;
        }

        .erd-button-icon {
            font-size: 13px;
            line-height: 1;
        }

        .erd-main {
            position: relative;
            flex: 1;
            min-height: 0;
            overflow: hidden;
            background:
                radial-gradient(
                    circle at 1px 1px,
                    rgba(150,175,220,.075) 1px,
                    transparent 1px
                );
            background-size: 24px 24px;
            cursor: grab;
        }

        .erd-main.is-panning {
            cursor: grabbing;
        }

        .erd-main::before {
            content: "";
            position: absolute;
            inset: 0;
            pointer-events: none;
            background:
                linear-gradient(
                    to bottom,
                    rgba(8,13,25,.25),
                    transparent 90px
                ),
                linear-gradient(
                    to top,
                    rgba(8,13,25,.3),
                    transparent 90px
                );
            z-index: 5;
        }

        .erd-stage {
            position: absolute;
            left: 0;
            top: 0;
            width: 5000px;
            height: 5000px;
            transform-origin: 0 0;
        }

        .erd-relations {
            position: absolute;
            left: 0;
            top: 0;
            width: 5000px;
            height: 5000px;
            z-index: 1;
            pointer-events: none;
            overflow: visible;
        }

        .erd-relation-line {
            fill: none;
            stroke: var(--relation);
            stroke-width: 1.5;
            opacity: .6;
            marker-end: url(#erd-arrow);
        }

        .erd-workspace {
            position: absolute;
            left: 0;
            top: 0;
            width: 5000px;
            height: 5000px;
            z-index: 2;
        }

        .erd-table {
            position: absolute;
            width: 300px;
            overflow: hidden;
            border: 1px solid rgba(151,176,220,.14);
            border-radius: 11px;
            background: rgba(14,22,39,.97);
            box-shadow:
                0 18px 45px rgba(0,0,0,.28),
                0 3px 10px rgba(0,0,0,.18);
            user-select: none;
            transition:
                border-color .15s ease,
                box-shadow .15s ease;
            z-index: 2;
        }

        .erd-table:hover {
            border-color: rgba(116,153,216,.3);
            box-shadow:
                0 22px 55px rgba(0,0,0,.34),
                0 0 0 1px rgba(77,131,232,.04);
        }

        .erd-table.hidden {
            display: none;
        }

        .erd-table-header {
            min-height: 58px;
            padding: 12px 14px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            background: linear-gradient(135deg,#182640,#142038);
            border-bottom: 1px solid rgba(255,255,255,.07);
            cursor: grab;
        }

        .erd-table-header:active {
            cursor: grabbing;
        }

        .erd-table-title-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
        }

        .erd-table-name {
            min-width: 0;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            color: #f4f7fd;
            font-size: 13px;
            font-weight: 750;
            letter-spacing: -.1px;
        }

        .erd-table-operation {
            flex-shrink: 0;
            padding: 3px 6px;
            border-radius: 5px;
            background: rgba(255,255,255,.06);
            color: #7287a7;
            font-size: 8px;
            font-weight: 650;
            text-transform: uppercase;
            letter-spacing: .3px;
        }

        .erd-table-meta {
            margin-top: 5px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            color: #647795;
            font-size: 9px;
        }

        .erd-columns {
            padding: 4px 0;
        }

        .erd-column {
            min-height: 31px;
            display: grid;
            grid-template-columns: minmax(0,1fr) auto;
            align-items: center;
            gap: 10px;
            padding: 0 13px;
            border-bottom: 1px solid rgba(255,255,255,.035);
        }

        .erd-column:last-child {
            border-bottom: 0;
        }

        .erd-column-name {
            min-width: 0;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            color: #d6e0ef;
            font-size: 10px;
        }

        .erd-column-type {
            color: #7184a3;
            font-size: 9px;
            white-space: nowrap;
        }

        .erd-badges {
            display: inline-flex;
            align-items: center;
            gap: 3px;
            margin-left: 4px;
        }

        .erd-badge {
            padding: 2px 4px;
            border-radius: 3px;
            background: rgba(255,255,255,.075);
            color: #91a2bd;
            font-size: 7px;
            font-weight: 750;
            vertical-align: middle;
        }

        .erd-badge.primary {
            background: rgba(77,131,232,.16);
            color: #8fb5ff;
        }

        .erd-badge.unique {
            background: rgba(85,201,138,.1);
            color: #78d9a3;
        }

        .erd-table-footer {
            height: 28px;
            display: flex;
            align-items: center;
            padding: 0 13px;
            border-top: 1px solid rgba(255,255,255,.05);
            background: rgba(7,12,23,.22);
            color: #52627c;
            font-size: 8px;
        }

        .erd-empty {
            position: absolute;
            left: 50%;
            top: 50%;
            transform: translate(-50%,-50%);
            width: 360px;
            padding: 35px;
            text-align: center;
            border: 1px solid var(--border);
            border-radius: 14px;
            background: rgba(14,22,39,.88);
            box-shadow: 0 25px 70px rgba(0,0,0,.25);
        }

        .erd-empty-icon {
            width: 48px;
            height: 48px;
            margin: 0 auto 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 12px;
            background: #172640;
            color: #8fb5ff;
            font-size: 20px;
            font-weight: 800;
        }

        .erd-empty-title {
            color: #f4f7fd;
            font-size: 17px;
            font-weight: 700;
        }

        .erd-empty-text {
            margin: 8px 0 20px;
            color: #6f7f98;
            font-size: 11px;
            line-height: 1.6;
        }

        .erd-bottom {
            position: absolute;
            left: 16px;
            right: 16px;
            bottom: 14px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            pointer-events: none;
            z-index: 30;
        }

        .erd-info {
            display: flex;
            align-items: center;
            gap: 6px;
            padding: 7px 10px;
            border: 1px solid rgba(255,255,255,.07);
            border-radius: 8px;
            background: rgba(9,15,28,.82);
            color: #60718d;
            font-size: 9px;
            backdrop-filter: blur(10px);
        }

        .erd-info-dot {
            width: 5px;
            height: 5px;
            border-radius: 50%;
            background: var(--success);
            box-shadow: 0 0 8px rgba(85,201,138,.55);
        }

        .erd-controls {
            display: flex;
            align-items: center;
            gap: 4px;
            padding: 4px;
            border: 1px solid rgba(255,255,255,.07);
            border-radius: 9px;
            background: rgba(9,15,28,.86);
            backdrop-filter: blur(10px);
            pointer-events: auto;
        }

        .erd-control {
            width: 30px;
            height: 28px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 6px;
            background: transparent;
            color: #91a1ba;
            cursor: pointer;
            font-size: 14px;
        }

        .erd-control:hover {
            background: rgba(255,255,255,.07);
            color: #fff;
        }

        .erd-zoom {
            min-width: 48px;
            text-align: center;
            color: #687993;
            font-size: 9px;
        }

        .erd-footer {
            height: 30px;
            min-height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-top: 1px solid var(--border);
            background: #0b1221;
            color: #52627c;
            font-size: 9px;
            z-index: 100;
        }

        .erd-footer strong {
            margin-left: 4px;
            color: #8192ad;
            font-weight: 600;
        }

        .erd-toast {
            position: fixed;
            right: 20px;
            bottom: 48px;
            z-index: 200;
            padding: 10px 14px;
            border: 1px solid rgba(255,255,255,.08);
            border-radius: 8px;
            background: #17233a;
            color: #dce6f5;
            box-shadow: 0 15px 35px rgba(0,0,0,.35);
            font-size: 10px;
            opacity: 0;
            transform: translateY(8px);
            pointer-events: none;
            transition: .2s ease;
        }

        .erd-toast.show {
            opacity: 1;
            transform: translateY(0);
        }

        @media (max-width: 900px) {
            .erd-brand-subtitle {
                display: none;
            }

            .erd-search {
                width: 180px;
            }

            .erd-brand {
                min-width: auto;
            }
        }

        @media (max-width: 700px) {
            .erd-header {
                padding: 0 12px;
            }

            .erd-search {
                width: 140px;
            }

            .erd-button {
                padding: 0 10px;
            }

            .erd-button-text {
                display: none;
            }

            .erd-brand-title {
                max-width: 180px;
            }
        }
    </style>
</head>

<body>

<div class="erd-app">

    <header class="erd-header">

        <div class="erd-brand">

            <div class="erd-logo">
                E
            </div>

            <div class="erd-brand-copy">

                <div class="erd-brand-title">
                    {{ config('app.name') }} ERD
                </div>

                <div class="erd-brand-subtitle">
                    Database schema visualizer
                </div>

            </div>

        </div>

        <div class="erd-toolbar">

            <div class="erd-search-wrap">

                <span class="erd-search-icon">
                    ⌕
                </span>

                <input
                    type="search"
                    id="tableSearch"
                    class="erd-search"
                    placeholder="Search tables..."
                    autocomplete="off"
                >

            </div>

            <button
                type="button"
                class="erd-button"
                id="refreshButton"
                style="display:none;"
            >
                <span class="erd-button-icon">
                    ↻
                </span>

                <span class="erd-button-text">
                    Refresh
                </span>
            </button>

        </div>

    </header>

    <main
        class="erd-main"
        id="erdCanvas"
    >

        <div
            class="erd-stage"
            id="erdStage"
        >

            <svg
                class="erd-relations"
                id="erdRelations"
                width="5000"
                height="5000"
                aria-hidden="true"
            >

                <defs>

                    <marker
                        id="erd-arrow"
                        markerWidth="8"
                        markerHeight="8"
                        refX="7"
                        refY="4"
                        orient="auto"
                        markerUnits="strokeWidth"
                    >
                        <path
                            d="M0,0 L8,4 L0,8 Z"
                            fill="#5f7fae"
                        />
                    </marker>

                </defs>

                <g id="erdRelationLines"></g>

            </svg>

            <div
                class="erd-workspace"
                id="erdWorkspace"
            ></div>

        </div>

        <div class="erd-bottom">

            <div class="erd-info">

                <span class="erd-info-dot"></span>

                <span id="tableCount">
                    0 tables
                </span>

                <span>
                    ·
                </span>

                <span id="modelCount">
                    0 models
                </span>

                <span>
                    ·
                </span>

                <span id="relationCount">
                    0 relations
                </span>

            </div>

            <div class="erd-controls">

                <button
                    type="button"
                    class="erd-control"
                    id="zoomOut"
                    title="Zoom out"
                >
                    −
                </button>

                <div
                    class="erd-zoom"
                    id="zoomValue"
                >
                    100%
                </div>

                <button
                    type="button"
                    class="erd-control"
                    id="zoomIn"
                    title="Zoom in"
                >
                    +
                </button>

                <button
                    type="button"
                    class="erd-control"
                    id="resetView"
                    title="Reset view"
                >
                    ⌂
                </button>

            </div>

        </div>

    </main>

    <footer class="erd-footer">
        Developed by
        <strong>Karthik Guggilapu</strong>
    </footer>

</div>

<div
    class="erd-toast"
    id="erdToast"
></div>

<script>
    window.ERD = {!! json_encode([
        'metadata' => $metadata,
        'migrations' => $migrations,
        'models' => $models,
        'relations' => $relations,
        'history' => $history,
        'layout' => $layout,
    ]) !!};

    const canvas =
        document.getElementById('erdCanvas');

    const stage =
        document.getElementById('erdStage');

    const workspace =
        document.getElementById('erdWorkspace');

    const relationLines =
        document.getElementById('erdRelationLines');

    const searchInput =
        document.getElementById('tableSearch');

    const refreshButton =
        document.getElementById('refreshButton');

    const tableCount =
        document.getElementById('tableCount');

    const modelCount =
        document.getElementById('modelCount');

    const relationCount =
        document.getElementById('relationCount');

    const zoomInButton =
        document.getElementById('zoomIn');

    const zoomOutButton =
        document.getElementById('zoomOut');

    const resetViewButton =
        document.getElementById('resetView');

    const zoomValue =
        document.getElementById('zoomValue');

    const toast =
        document.getElementById('erdToast');

    let tableElements = [];

    let zoom = 1;

    let panX = 0;

    let panY = 0;

    let isPanning = false;

    let panStartX = 0;

    let panStartY = 0;

    let initialPanX = 0;

    let initialPanY = 0;

    function getTables() {
        const migrations =
            window.ERD.migrations?.migrations ?? [];

        const tables =
            new Map();

        migrations.forEach(migration => {

            const migrationTables =
                migration.tables ?? [];

            migrationTables.forEach(table => {

                if (!table.name) {
                    return;
                }

                if (!tables.has(table.name)) {

                    tables.set(
                        table.name,
                        {
                            name: table.name,
                            columns: table.columns ?? [],
                            operation:
                                table.operation ??
                                'table',
                            migration:
                                migration.file ??
                                migration.id ??
                                null
                        }
                    );

                    return;
                }

                const existing =
                    tables.get(table.name);

                if (
                    table.operation === 'create'
                ) {
                    existing.columns =
                        table.columns ??
                        existing.columns;
                }

                if (table.columns?.length) {

                    const existingNames =
                        new Set(
                            existing.columns.map(
                                column => column.name
                            )
                        );

                    table.columns.forEach(column => {

                        if (
                            !existingNames.has(
                                column.name
                            )
                        ) {
                            existing.columns.push(
                                column
                            );
                        }

                    });
                }

                existing.operation =
                    table.operation ??
                    existing.operation;

                existing.migration =
                    migration.file ??
                    existing.migration;

            });

        });

        return [...tables.values()];
    }

    function getRelations() {
        return (
            window.ERD.relations?.relations ??
            []
        );
    }

    function updateToolbar() {
        const tables =
            getTables();

        refreshButton.style.display =
            tables.length
                ? 'inline-flex'
                : 'none';
    }

    function renderTables() {

        workspace.innerHTML = '';

        relationLines.innerHTML = '';

        tableElements = [];

        const tables =
            getTables();

        const models =
            window.ERD.models?.models ?? [];

        const relations =
            getRelations();

        updateToolbar();

        tableCount.textContent =
            `${tables.length} table${tables.length === 1 ? '' : 's'}`;

        modelCount.textContent =
            `${models.length} model${models.length === 1 ? '' : 's'}`;

        relationCount.textContent =
            `${relations.length} relation${relations.length === 1 ? '' : 's'}`;

        if (!tables.length) {
            renderEmptyState();
            return;
        }

        tables.forEach((table, index) => {

            const element =
                createTable(table, index);

            tableElements.push({
                element,
                name:
                    table.name.toLowerCase(),
                table:
                    table
            });

        });

        layoutTables();

        drawRelations();

        applySearch();

        resetView();
    }

    function renderEmptyState() {

        workspace.innerHTML = `
            <div class="erd-empty">

                <div class="erd-empty-icon">
                    E
                </div>

                <div class="erd-empty-title">
                    No schema analyzed yet
                </div>

                <div class="erd-empty-text">
                    Analyze your Laravel migrations to
                    generate the database schema visualization.
                </div>

                <button
                    type="button"
                    class="erd-button primary"
                    onclick="analyzeSchema()"
                >
                    <span>
                        ◈
                    </span>

                    Analyze Schema
                </button>

            </div>
        `;
    }

    function createTable(table, index) {

        const element =
            document.createElement('div');

        element.className =
            'erd-table';

        element.dataset.table =
            table.name;

        const columns =
            table.columns.map(column => {

                const badges = [];

                if (column.primary) {
                    badges.push({
                        text: 'PK',
                        className: 'primary'
                    });
                }

                if (column.unique) {
                    badges.push({
                        text: 'UQ',
                        className: 'unique'
                    });
                }

                if (column.nullable) {
                    badges.push({
                        text: 'NULL',
                        className: ''
                    });
                }

                return `
                    <div class="erd-column">

                        <div class="erd-column-name">

                            ${escapeHtml(
                                column.name ?? ''
                            )}

                            ${
                                badges.length
                                    ? `
                                        <span class="erd-badges">
                                            ${badges.map(badge => `
                                                <span class="erd-badge ${badge.className}">
                                                    ${badge.text}
                                                </span>
                                            `).join('')}
                                        </span>
                                    `
                                    : ''
                            }

                        </div>

                        <div class="erd-column-type">
                            ${escapeHtml(
                                column.type ?? ''
                            )}
                        </div>

                    </div>
                `;

            }).join('');

        element.innerHTML = `

            <div class="erd-table-header">

                <div class="erd-table-title-row">

                    <div class="erd-table-name">
                        ${escapeHtml(table.name)}
                    </div>

                    <div class="erd-table-operation">
                        ${escapeHtml(
                            table.operation
                        )}
                    </div>

                </div>

                <div class="erd-table-meta">
                    ${escapeHtml(
                        table.migration ?? ''
                    )}
                </div>

            </div>

            <div class="erd-columns">

                ${
                    columns ||
                    `
                        <div class="erd-column">
                            <div class="erd-column-name">
                                No columns detected
                            </div>
                        </div>
                    `
                }

            </div>

            <div class="erd-table-footer">

                ${table.columns.length}

                column${table.columns.length === 1 ? '' : 's'}

            </div>
        `;

        makeDraggable(element);

        workspace.appendChild(element);

        return element;
    }

    function layoutTables() {

        const gapY = 70;

        const startX = 100;

        const startY = 100;

        const columns = 4;

        const columnWidth = 380;

        const columnHeights =
            Array(columns).fill(startY);

        tableElements.forEach((item, index) => {

            const element =
                item.element;

            const column =
                index % columns;

            const x =
                startX +
                column * columnWidth;

            const y =
                columnHeights[column];

            element.style.left =
                `${x}px`;

            element.style.top =
                `${y}px`;

            columnHeights[column] =
                y +
                element.offsetHeight +
                gapY;

        });
    }

    function makeDraggable(element) {

        const header =
            element.querySelector(
                '.erd-table-header'
            );

        let dragging = false;

        let startX = 0;

        let startY = 0;

        let startLeft = 0;

        let startTop = 0;

        header.addEventListener(
            'mousedown',
            event => {

                event.stopPropagation();

                dragging = true;

                startX =
                    event.clientX;

                startY =
                    event.clientY;

                startLeft =
                    parseFloat(
                        element.style.left
                    ) || 0;

                startTop =
                    parseFloat(
                        element.style.top
                    ) || 0;

                element.style.zIndex =
                    '100';

                document.body.style.userSelect =
                    'none';

            }
        );

        document.addEventListener(
            'mousemove',
            event => {

                if (!dragging) {
                    return;
                }

                const deltaX =
                    (
                        event.clientX -
                        startX
                    ) / zoom;

                const deltaY =
                    (
                        event.clientY -
                        startY
                    ) / zoom;

                element.style.left =
                    `${Math.max(
                        0,
                        startLeft + deltaX
                    )}px`;

                element.style.top =
                    `${Math.max(
                        0,
                        startTop + deltaY
                    )}px`;

                drawRelations();

            }
        );

        document.addEventListener(
            'mouseup',
            () => {

                if (!dragging) {
                    return;
                }

                dragging = false;

                element.style.zIndex = '';

                document.body.style.userSelect =
                    '';

            }
        );
    }

    function findTableElement(name) {

        const target =
            String(name)
                .toLowerCase();

        const match =
            tableElements.find(item =>
                item.name === target
            );

        return match?.element ?? null;
    }

    function getConnectionPoint(
        element,
        side
    ) {

        const left =
            parseFloat(
                element.style.left
            ) || 0;

        const top =
            parseFloat(
                element.style.top
            ) || 0;

        const width =
            element.offsetWidth;

        const height =
            element.offsetHeight;

        if (side === 'left') {
            return {
                x: left,
                y: top + height / 2
            };
        }

        return {
            x: left + width,
            y: top + height / 2
        };
    }

    function drawRelations() {

        relationLines.innerHTML = '';

        const relations =
            getRelations();

        relations.forEach(relation => {

            const fromTable =
                findTableElement(
                    relation.from_table
                );

            const toTable =
                findTableElement(
                    relation.to_table
                );

            if (!fromTable || !toTable) {
                return;
            }

            const fromRect =
                getConnectionPoint(
                    fromTable,
                    'right'
                );

            const toRect =
                getConnectionPoint(
                    toTable,
                    'left'
                );

            const distance =
                Math.abs(
                    toRect.x -
                    fromRect.x
                );

            const curve =
                Math.max(
                    60,
                    distance * .35
                );

            const path =
                document.createElementNS(
                    'http://www.w3.org/2000/svg',
                    'path'
                );

            path.setAttribute(
                'class',
                'erd-relation-line'
            );

            path.setAttribute(
                'd',
                `
                    M ${fromRect.x}
                      ${fromRect.y}

                    C ${fromRect.x + curve}
                      ${fromRect.y},

                      ${toRect.x - curve}
                      ${toRect.y},

                      ${toRect.x}
                      ${toRect.y}
                `
            );

            path.setAttribute(
                'data-from',
                relation.from_table
            );

            path.setAttribute(
                'data-to',
                relation.to_table
            );

            relationLines.appendChild(path);

        });
    }

    function applySearch() {

        const search =
            searchInput.value
                .trim()
                .toLowerCase();

        tableElements.forEach(item => {

            const visible =
                !search ||
                item.name.includes(search);

            item.element.classList.toggle(
                'hidden',
                !visible
            );

        });

        drawRelations();
    }

    function updateView() {

        stage.style.transform =
            `translate(${panX}px, ${panY}px) scale(${zoom})`;

        zoomValue.textContent =
            `${Math.round(zoom * 100)}%`;
    }

    function setZoom(value) {

        zoom =
            Math.min(
                1.8,
                Math.max(.45, value)
            );

        updateView();
    }

    function resetView() {

        zoom = 1;

        panX =
            Math.max(
                0,
                (canvas.clientWidth - 1900) / 2
            );

        panY =
            Math.max(
                0,
                (canvas.clientHeight - 1300) / 2
            );

        updateView();
    }

    canvas.addEventListener(
        'mousedown',
        event => {

            if (
                event.target.closest(
                    '.erd-table'
                )
            ) {
                return;
            }

            if (
                event.target.closest(
                    '.erd-controls'
                )
            ) {
                return;
            }

            isPanning = true;

            panStartX =
                event.clientX;

            panStartY =
                event.clientY;

            initialPanX =
                panX;

            initialPanY =
                panY;

            canvas.classList.add(
                'is-panning'
            );
        }
    );

    document.addEventListener(
        'mousemove',
        event => {

            if (!isPanning) {
                return;
            }

            panX =
                initialPanX +
                (
                    event.clientX -
                    panStartX
                );

            panY =
                initialPanY +
                (
                    event.clientY -
                    panStartY
                );

            updateView();

        }
    );

    document.addEventListener(
        'mouseup',
        () => {

            if (!isPanning) {
                return;
            }

            isPanning = false;

            canvas.classList.remove(
                'is-panning'
            );

        }
    );

    canvas.addEventListener(
        'wheel',
        event => {

            if (!event.ctrlKey) {
                return;
            }

            event.preventDefault();

            const direction =
                event.deltaY < 0
                    ? .1
                    : -.1;

            setZoom(
                zoom + direction
            );

        },
        {
            passive: false
        }
    );

    zoomInButton.addEventListener(
        'click',
        () => setZoom(zoom + .1)
    );

    zoomOutButton.addEventListener(
        'click',
        () => setZoom(zoom - .1)
    );

    resetViewButton.addEventListener(
        'click',
        resetView
    );

    searchInput.addEventListener(
        'input',
        applySearch
    );

    async function analyzeSchema() {

        setLoading(true);

        try {

            const response =
                await fetch(
                    '{{ route('erd.refresh') }}',
                    {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN':
                                document
                                    .querySelector(
                                        'meta[name="csrf-token"]'
                                    )
                                    .getAttribute(
                                        'content'
                                    ),

                            'Accept':
                                'application/json'
                        }
                    }
                );

            const data =
                await response.json();

            if (
                !response.ok ||
                !data.success
            ) {
                throw new Error(
                    data.message ||
                    'Schema analysis failed.'
                );
            }

            showToast(
                `Schema analyzed: ${data.migrations} migrations, ${data.models} models, ${data.relations} relations.`
            );

            await reloadRegistry();

        } catch (error) {

            showToast(
                error.message ||
                'Schema analysis failed.'
            );

        } finally {

            setLoading(false);

        }
    }

    async function reloadRegistry() {

        const response =
            await fetch(
                window.location.href,
                {
                    headers: {
                        'Accept':
                            'text/html'
                    }
                }
            );

        const html =
            await response.text();

        const parser =
            new DOMParser();

        const documentObject =
            parser.parseFromString(
                html,
                'text/html'
            );

        const script =
            [...documentObject.scripts]
                .find(script =>
                    script.textContent.includes(
                        'window.ERD'
                    )
                );

        if (!script) {
            window.location.reload();
            return;
        }

        const match =
            script.textContent.match(
                /window\.ERD\s*=\s*(\{[\s\S]*?\});/
            );

        if (!match) {
            window.location.reload();
            return;
        }

        try {

            window.ERD =
                JSON.parse(
                    match[1]
                );

            renderTables();

        } catch {

            window.location.reload();

        }
    }

    function setLoading(loading) {

        refreshButton.disabled =
            loading;

        refreshButton.innerHTML =
            loading
                ? '<span>◌</span><span class="erd-button-text">Analyzing...</span>'
                : '<span class="erd-button-icon">↻</span><span class="erd-button-text">Refresh</span>';

    }

    function showToast(message) {

        toast.textContent =
            message;

        toast.classList.add(
            'show'
        );

        setTimeout(
            () => {
                toast.classList.remove(
                    'show'
                );
            },
            3000
        );
    }

    function escapeHtml(value) {

        return String(value)
            .replace(
                /&/g,
                '&amp;'
            )
            .replace(
                /</g,
                '&lt;'
            )
            .replace(
                />/g,
                '&gt;'
            )
            .replace(
                /"/g,
                '&quot;'
            )
            .replace(
                /'/g,
                '&#039;'
            );
    }

    refreshButton.addEventListener(
        'click',
        analyzeSchema
    );

    renderTables();
</script>

</body>
</html>