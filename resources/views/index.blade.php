<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name') }} ERD</title>

    <link
        rel="icon"
        href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 64 64'%3E%3Crect width='64' height='64' rx='14' fill='%23315ea8'/%3E%3Ctext x='32' y='43' text-anchor='middle' font-family='Arial' font-size='34' font-weight='700' fill='white'%3EE%3C/text%3E%3C/svg%3E"
    >

    <style>
        :root {
            --bg: #080e1c;
            --panel: #0e1627;
            --panel-2: #111b2e;
            --panel-3: #182640;
            --text: #edf3fc;
            --muted: #71809c;
            --border: rgba(151,176,220,.13);
            --border-strong: rgba(116,153,216,.32);
            --accent: #315ea8;
            --accent-hover: #3b6fc0;
            --success: #55c98a;
        }

        * {
            box-sizing: border-box;
        }

        html,
        body {
            width: 100%;
            height: 100%;
            margin: 0;
        }

        body {
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
            background: linear-gradient(145deg, #263b65, #172746);
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
        }

        .erd-brand-title {
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

        .erd-workspace {
            position: absolute;
            left: 0;
            top: 0;
            width: 5000px;
            height: 5000px;
            z-index: 2;
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
            background:
                linear-gradient(
                    145deg,
                    #1b2a46,
                    #142038
                );
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
            color: #647795;
            font-size: 9px;
        }

        .erd-columns {
            padding: 4px 0;
        }

        .erd-column {
            min-height: 31px;
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto;
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
            inset: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            text-align: center;
            color: #71809c;
            pointer-events: auto;
        }

        .erd-empty-icon {
            width: 58px;
            height: 58px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 15px;
            border-radius: 16px;
            background:
                linear-gradient(
                    145deg,
                    #263b65,
                    #172746
                );
            border: 1px solid rgba(117,157,231,.18);
            color: #9fc0ff;
            font-size: 22px;
            font-weight: 800;
            box-shadow: 0 15px 35px rgba(0,0,0,.25);
        }

        .erd-empty-title {
            color: #edf3fc;
            font-size: 16px;
            font-weight: 700;
        }

        .erd-empty-text {
            max-width: 390px;
            margin-top: 7px;
            margin-bottom: 18px;
            color: #647795;
            font-size: 11px;
            line-height: 1.6;
        }

        .erd-bottom {
            position: absolute;
            left: 0;
            right: 0;
            bottom: 0;
            height: 44px;
            min-height: 44px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 14px;
            pointer-events: none;
            z-index: 50;
        }

        .erd-info {
            display: flex;
            align-items: center;
            gap: 8px;
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

        .erd-relation-line {
            fill: none;
            stroke: #5f7fae;
            stroke-width: 1.5;
            opacity: .55;
            marker-end: url(#erd-arrow);
            transition: opacity .15s ease;
        }

        .erd-relation-line:hover {
            opacity: .9;
            stroke-width: 2;
        }

        .erd-flow-particle {
            fill: #8fb8ff;
            opacity: .95;
            filter: url(#erd-particle-glow);
        }

        .erd-flow-particle-core {
            fill: #dceaff;
        }

        .erd-flow-particle-halo {
            fill: #6f9fe9;
            opacity: .22;
            filter: url(#erd-particle-glow);
        }

        .erd-table-selector {
            position: absolute;
            top: 18px;
            right: 18px;
            width: 280px;
            max-height: calc(100% - 90px);
            display: flex;
            flex-direction: column;
            background: rgba(14,22,39,.94);
            border: 1px solid rgba(151,176,220,.16);
            border-radius: 12px;
            box-shadow:
                0 20px 50px rgba(0,0,0,.35),
                0 4px 14px rgba(0,0,0,.2);
            backdrop-filter: blur(16px);
            z-index: 70;
            overflow: hidden;
        }

        .erd-selector-header {
            padding: 13px 14px 10px;
            border-bottom: 1px solid rgba(255,255,255,.06);
        }

        .erd-selector-title-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
        }

        .erd-selector-title {
            color: #edf3fc;
            font-size: 11px;
            font-weight: 750;
        }

        .erd-selector-count {
            padding: 3px 7px;
            border-radius: 5px;
            background: rgba(77,131,232,.13);
            color: #8fb5ff;
            font-size: 8px;
            font-weight: 700;
        }

        .erd-selector-search-wrap {
            position: relative;
            margin-top: 10px;
        }

        .erd-selector-search {
            width: 100%;
            height: 32px;
            padding: 0 10px 0 30px;
            border: 1px solid rgba(151,176,220,.12);
            border-radius: 7px;
            outline: none;
            background: #09111f;
            color: #dce7f8;
            font-size: 10px;
        }

        .erd-selector-search:focus {
            border-color: rgba(77,131,232,.5);
        }

        .erd-selector-search::placeholder {
            color: #566681;
        }

        .erd-selector-search-icon {
            position: absolute;
            left: 10px;
            top: 50%;
            transform: translateY(-50%);
            color: #61718b;
            font-size: 11px;
            pointer-events: none;
        }

        .erd-selector-actions {
            display: flex;
            gap: 6px;
            margin-top: 8px;
        }

        .erd-selector-action {
            flex: 1;
            height: 27px;
            border: 1px solid rgba(151,176,220,.1);
            border-radius: 6px;
            background: rgba(255,255,255,.035);
            color: #8090a9;
            cursor: pointer;
            font-size: 8px;
            font-weight: 650;
        }

        .erd-selector-action:hover {
            background: rgba(255,255,255,.07);
            color: #dce7f8;
        }

        .erd-selector-list {
            overflow-y: auto;
            padding: 7px;
        }

        .erd-selector-list::-webkit-scrollbar {
            width: 5px;
        }

        .erd-selector-list::-webkit-scrollbar-track {
            background: transparent;
        }

        .erd-selector-list::-webkit-scrollbar-thumb {
            background: rgba(151,176,220,.16);
            border-radius: 10px;
        }

        .erd-selector-item {
            min-height: 34px;
            display: flex;
            align-items: center;
            gap: 9px;
            padding: 0 8px;
            border-radius: 7px;
            cursor: pointer;
            transition: background .15s ease;
        }

        .erd-selector-item:hover {
            background: rgba(255,255,255,.05);
        }

        .erd-selector-checkbox {
            width: 14px;
            height: 14px;
            margin: 0;
            accent-color: #4d83e8;
            cursor: pointer;
        }

        .erd-selector-name {
            min-width: 0;
            flex: 1;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            color: #cbd6e8;
            font-size: 9px;
        }

        .erd-selector-item-count {
            color: #526681;
            font-size: 8px;
        }

        .erd-selector-empty {
            padding: 18px 10px;
            text-align: center;
            color: #61718b;
            font-size: 9px;
        }

        .erd-selector-footer {
            padding: 8px 12px;
            border-top: 1px solid rgba(255,255,255,.06);
            color: #52627c;
            font-size: 8px;
            line-height: 1.5;
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

            .erd-table-selector {
                width: 240px;
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
                font-size: 13px;
            }

            .erd-table-selector {
                top: 12px;
                right: 12px;
                width: 220px;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            .erd-flow-particle,
            .erd-flow-particle-core,
            .erd-flow-particle-halo {
                display: none;
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
                class="erd-button primary"
                id="analyzeButton"
            >
                <span class="erd-button-icon">
                    ◈
                </span>

                <span class="erd-button-text">
                    Analyze Schema
                </span>
            </button>

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
                viewBox="0 0 5000 5000"
                preserveAspectRatio="none"
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

                    <filter
                        id="erd-particle-glow"
                        x="-100%"
                        y="-100%"
                        width="300%"
                        height="300%"
                    >
                        <feGaussianBlur
                            stdDeviation="2.5"
                            result="blur"
                        />

                        <feMerge>
                            <feMergeNode in="blur"/>
                            <feMergeNode in="SourceGraphic"/>
                        </feMerge>
                    </filter>

                </defs>

                <g id="erdRelationLines"></g>

            </svg>

            <div
                class="erd-workspace"
                id="erdWorkspace"
            ></div>

        </div>

        <div
            class="erd-table-selector"
            id="erdTableSelector"
        >

            <div class="erd-selector-header">

                <div class="erd-selector-title-row">

                    <div class="erd-selector-title">
                        Tables
                    </div>

                    <div
                        class="erd-selector-count"
                        id="selectedTableCount"
                    >
                        0 / 0
                    </div>

                </div>

                <div class="erd-selector-search-wrap">

                    <span class="erd-selector-search-icon">
                        ⌕
                    </span>

                    <input
                        type="search"
                        id="tableSelectorSearch"
                        class="erd-selector-search"
                        placeholder="Find table..."
                        autocomplete="off"
                    >

                </div>

                <div class="erd-selector-actions">

                    <button
                        type="button"
                        class="erd-selector-action"
                        id="selectAllTables"
                    >
                        Select All
                    </button>

                    <button
                        type="button"
                        class="erd-selector-action"
                        id="clearAllTables"
                    >
                        Clear
                    </button>

                </div>

            </div>

            <div
                class="erd-selector-list"
                id="erdTableList"
            ></div>

            <div class="erd-selector-footer">
                Select the tables you want to display.
                Relations are shown only when both connected
                tables are selected.
            </div>

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
        <strong>
            Karthik Guggilapu
        </strong>
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

    const analyzeButton =
        document.getElementById('analyzeButton');

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

    const tableSelectorSearch =
        document.getElementById('tableSelectorSearch');

    const tableList =
        document.getElementById('erdTableList');

    const selectedTableCount =
        document.getElementById('selectedTableCount');

    const selectAllTablesButton =
        document.getElementById('selectAllTables');

    const clearAllTablesButton =
        document.getElementById('clearAllTables');

    let tableElements = [];

    let zoom = 1;

    let panX = 0;

    let panY = 0;

    let isPanning = false;

    let panStartX = 0;

    let panStartY = 0;

    let initialPanX = 0;

    let initialPanY = 0;

    let selectedTables = new Set();

    let tableSelectionInitialized = false;

    const SVG_NS =
        'http://www.w3.org/2000/svg';

    function getTables() {
        const migrations =
            window.ERD.migrations?.migrations ?? [];

        const tables =
            new Map();

        migrations.forEach(
            migration => {

                const migrationTables =
                    migration.tables ?? [];

                migrationTables.forEach(
                    table => {

                        if (!table.name) {
                            return;
                        }

                        if (!tables.has(table.name)) {

                            tables.set(
                                table.name,
                                {
                                    name: table.name,
                                    columns: table.columns ?? [],
                                    operation: table.operation ?? 'table',
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
                                        column =>
                                            column.name
                                    )
                                );

                            table.columns.forEach(
                                column => {

                                    if (
                                        !existingNames.has(
                                            column.name
                                        )
                                    ) {
                                        existing.columns.push(
                                            column
                                        );
                                    }

                                }
                            );
                        }

                        existing.operation =
                            table.operation ??
                            existing.operation;

                        existing.migration =
                            migration.file ??
                            existing.migration;
                    }
                );
            }
        );

        return [...tables.values()];
    }

    function getRelations() {
        return (
            window.ERD.relations?.relations ??
            []
        );
    }

    function normalizeTableName(name) {
        return String(name ?? '')
            .trim()
            .toLowerCase();
    }

    function initializeTableSelection(tables) {
        const currentNames =
            new Set(
                tables.map(
                    table =>
                        normalizeTableName(table.name)
                )
            );

        if (!tableSelectionInitialized) {

            selectedTables =
                new Set(currentNames);

            tableSelectionInitialized = true;

            return;
        }

        selectedTables =
            new Set(
                [...selectedTables].filter(
                    name =>
                        currentNames.has(name)
                )
            );
    }

    function renderTableSelector(tables) {
        initializeTableSelection(tables);

        const selectorSearch =
            tableSelectorSearch.value
                .trim()
                .toLowerCase();

        const filteredTables =
            tables.filter(
                table =>
                    !selectorSearch ||
                    normalizeTableName(
                        table.name
                    ).includes(selectorSearch)
            );

        tableList.innerHTML = '';

        if (!filteredTables.length) {

            tableList.innerHTML = `
                <div class="erd-selector-empty">
                    No tables found
                </div>
            `;

        } else {

            filteredTables.forEach(
                table => {

                    const name =
                        normalizeTableName(
                            table.name
                        );

                    const item =
                        document.createElement('label');

                    item.className =
                        'erd-selector-item';

                    const checkbox =
                        document.createElement('input');

                    checkbox.type =
                        'checkbox';

                    checkbox.className =
                        'erd-selector-checkbox';

                    checkbox.checked =
                        selectedTables.has(name);

                    checkbox.dataset.table =
                        name;

                    const nameElement =
                        document.createElement('span');

                    nameElement.className =
                        'erd-selector-name';

                    nameElement.textContent =
                        table.name;

                    const columnCount =
                        document.createElement('span');

                    columnCount.className =
                        'erd-selector-item-count';

                    columnCount.textContent =
                        table.columns.length;

                    checkbox.addEventListener(
                        'change',
                        () => {

                            if (checkbox.checked) {
                                selectedTables.add(name);
                            } else {
                                selectedTables.delete(name);
                            }

                            updateTableSelection();
                        }
                    );

                    item.appendChild(checkbox);
                    item.appendChild(nameElement);
                    item.appendChild(columnCount);

                    tableList.appendChild(item);
                }
            );
        }

        updateSelectedTableCount(tables);
    }

    function updateSelectedTableCount(tables = getTables()) {
        selectedTableCount.textContent =
            `${selectedTables.size} / ${tables.length}`;
    }

    function updateTableSelection() {
        tableElements.forEach(
            item => {

                const visible =
                    selectedTables.has(
                        item.name
                    );

                item.element.classList.toggle(
                    'hidden',
                    !visible
                );
            }
        );

        drawRelations();

        const tables =
            getTables();

        const visibleRelations =
            getVisibleRelations();

        relationCount.textContent =
            `${visibleRelations.length} relation${visibleRelations.length === 1 ? '' : 's'}`;

        updateSelectedTableCount(tables);
    }

    function getVisibleRelations() {
        return getRelations().filter(
            relation => {

                const from =
                    normalizeTableName(
                        relation.from_table
                    );

                const to =
                    normalizeTableName(
                        relation.to_table
                    );

                return (
                    selectedTables.has(from) &&
                    selectedTables.has(to)
                );
            }
        );
    }

    function renderTables() {
        workspace.innerHTML = '';

        tableElements = [];

        clearRelations();

        const tables =
            getTables();

        const relations =
            getRelations();

        tableCount.textContent =
            `${tables.length} table${tables.length === 1 ? '' : 's'}`;

        const models =
            window.ERD.models?.models ?? [];

        modelCount.textContent =
            `${models.length} model${models.length === 1 ? '' : 's'}`;

        if (!tables.length) {

            renderEmptyState();

            tableList.innerHTML = `
                <div class="erd-selector-empty">
                    No tables available
                </div>
            `;

            selectedTables.clear();

            selectedTableCount.textContent =
                '0 / 0';

            refreshButton.style.display =
                'none';

            relationCount.textContent =
                '0 relations';

            return;
        }

        tables.forEach(
            (table, index) => {

                const element =
                    createTable(
                        table,
                        index
                    );

                tableElements.push({
                    element,
                    name:
                        normalizeTableName(
                            table.name
                        )
                });
            }
        );

        renderTableSelector(tables);

        layoutTables();

        updateTableSelection();

        applySearch();

        refreshButton.style.display =
            'inline-flex';

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

                    <span>
                        Analyze Schema
                    </span>
                </button>

            </div>
        `;
    }

    function createTable(
        table,
        index
    ) {
        const element =
            document.createElement('div');

        element.className =
            'erd-table';

        const columns =
            table.columns
                .map(
                    column => {

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

                                                    ${badges
                                                        .map(
                                                            badge => `
                                                                <span
                                                                    class="erd-badge ${badge.className}"
                                                                >
                                                                    ${badge.text}
                                                                </span>
                                                            `
                                                        )
                                                        .join('')
                                                    }

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
                    }
                )
                .join('');

        element.innerHTML = `
            <div class="erd-table-header">

                <div class="erd-table-title-row">

                    <div class="erd-table-name">
                        ${escapeHtml(
                            table.name
                        )}
                    </div>

                    <div class="erd-table-operation">
                        ${escapeHtml(
                            table.operation ?? 'table'
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
        const gapX = 80;
        const gapY = 70;
        const startX = 100;
        const startY = 100;
        const columns = 4;
        const columnWidth = 380;

        let columnHeights =
            Array(columns)
                .fill(startY);

        tableElements.forEach(
            (item, index) => {

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
            }
        );
    }

    function getTableElement(tableName) {
        const normalized =
            normalizeTableName(tableName);

        const item =
            tableElements.find(
                table =>
                    table.name === normalized
            );

        return item?.element ?? null;
    }

    function getConnectionPoint(
        element,
        targetElement
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

        const targetLeft =
            parseFloat(
                targetElement.style.left
            ) || 0;

        const targetTop =
            parseFloat(
                targetElement.style.top
            ) || 0;

        const targetWidth =
            targetElement.offsetWidth;

        const targetHeight =
            targetElement.offsetHeight;

        const centerX =
            left +
            width / 2;

        const centerY =
            top +
            height / 2;

        const targetCenterX =
            targetLeft +
            targetWidth / 2;

        const targetCenterY =
            targetTop +
            targetHeight / 2;

        const dx =
            targetCenterX -
            centerX;

        const dy =
            targetCenterY -
            centerY;

        if (Math.abs(dx) >= Math.abs(dy)) {

            if (dx >= 0) {
                return {
                    x: left + width,
                    y: centerY,
                    side: 'right'
                };
            }

            return {
                x: left,
                y: centerY,
                side: 'left'
            };
        }

        if (dy >= 0) {
            return {
                x: centerX,
                y: top + height,
                side: 'bottom'
            };
        }

        return {
            x: centerX,
            y: top,
            side: 'top'
        };
    }

    function createRelationPath(
        from,
        to
    ) {
        const distanceX =
            Math.abs(
                to.x -
                from.x
            );

        const distanceY =
            Math.abs(
                to.y -
                from.y
            );

        const curve =
            Math.max(
                70,
                Math.min(
                    220,
                    Math.max(
                        distanceX,
                        distanceY
                    ) * .35
                )
            );

        let c1x = from.x;
        let c1y = from.y;
        let c2x = to.x;
        let c2y = to.y;

        if (from.side === 'right') {
            c1x += curve;
        } else if (from.side === 'left') {
            c1x -= curve;
        } else if (from.side === 'bottom') {
            c1y += curve;
        } else {
            c1y -= curve;
        }

        if (to.side === 'right') {
            c2x += curve;
        } else if (to.side === 'left') {
            c2x -= curve;
        } else if (to.side === 'bottom') {
            c2y += curve;
        } else {
            c2y -= curve;
        }

        return `
            M ${from.x} ${from.y}
            C
            ${c1x} ${c1y},
            ${c2x} ${c2y},
            ${to.x} ${to.y}
        `;
    }

    function clearRelations() {
        relationLines.innerHTML = '';
    }

    function drawRelations() {
        clearRelations();

        const relations =
            getVisibleRelations();

        relationCount.textContent =
            `${relations.length} relation${relations.length === 1 ? '' : 's'}`;

        if (!relations.length) {
            return;
        }

        relations.forEach(
            (relation, relationIndex) => {

                const fromElement =
                    getTableElement(
                        relation.from_table
                    );

                const toElement =
                    getTableElement(
                        relation.to_table
                    );

                if (
                    !fromElement ||
                    !toElement
                ) {
                    return;
                }

                if (
                    fromElement.classList.contains('hidden') ||
                    toElement.classList.contains('hidden')
                ) {
                    return;
                }

                const from =
                    getConnectionPoint(
                        fromElement,
                        toElement
                    );

                const to =
                    getConnectionPoint(
                        toElement,
                        fromElement
                    );

                const pathData =
                    createRelationPath(
                        from,
                        to
                    );

                const pathId =
                    `erd-relation-${relationIndex}-${Date.now()}`;

                const path =
                    document.createElementNS(
                        SVG_NS,
                        'path'
                    );

                path.setAttribute(
                    'id',
                    pathId
                );

                path.setAttribute(
                    'class',
                    'erd-relation-line'
                );

                path.setAttribute(
                    'd',
                    pathData
                );

                path.setAttribute(
                    'data-from',
                    relation.from_table ?? ''
                );

                path.setAttribute(
                    'data-to',
                    relation.to_table ?? ''
                );

                relationLines.appendChild(path);

                createFlowParticles(
                    path,
                    relationIndex
                );
            }
        );
    }

    function createFlowParticles(
        path,
        relationIndex
    ) {
        const particleCount = 3;

        for (
            let index = 0;
            index < particleCount;
            index++
        ) {

            const halo =
                document.createElementNS(
                    SVG_NS,
                    'circle'
                );

            halo.setAttribute(
                'r',
                '5'
            );

            halo.setAttribute(
                'class',
                'erd-flow-particle-halo'
            );

            const motion =
                document.createElementNS(
                    SVG_NS,
                    'animateMotion'
                );

            motion.setAttribute(
                'dur',
                `${2.8 + (relationIndex % 3) * .25}s`
            );

            motion.setAttribute(
                'repeatCount',
                'indefinite'
            );

            motion.setAttribute(
                'begin',
                `${index * .9}s`
            );

            motion.setAttribute(
                'rotate',
                'auto'
            );

            const mpath =
                document.createElementNS(
                    SVG_NS,
                    'mpath'
                );

            mpath.setAttributeNS(
                'http://www.w3.org/1999/xlink',
                'href',
                `#${path.id}`
            );

            mpath.setAttribute(
                'href',
                `#${path.id}`
            );

            motion.appendChild(mpath);
            halo.appendChild(motion);
            relationLines.appendChild(halo);

            const particle =
                document.createElementNS(
                    SVG_NS,
                    'circle'
                );

            particle.setAttribute(
                'r',
                '2.8'
            );

            particle.setAttribute(
                'class',
                'erd-flow-particle'
            );

            const particleMotion =
                document.createElementNS(
                    SVG_NS,
                    'animateMotion'
                );

            particleMotion.setAttribute(
                'dur',
                `${2.8 + (relationIndex % 3) * .25}s`
            );

            particleMotion.setAttribute(
                'repeatCount',
                'indefinite'
            );

            particleMotion.setAttribute(
                'begin',
                `${index * .9}s`
            );

            particleMotion.setAttribute(
                'rotate',
                'auto'
            );

            const particlePath =
                document.createElementNS(
                    SVG_NS,
                    'mpath'
                );

            particlePath.setAttributeNS(
                'http://www.w3.org/1999/xlink',
                'href',
                `#${path.id}`
            );

            particlePath.setAttribute(
                'href',
                `#${path.id}`
            );

            particleMotion.appendChild(
                particlePath
            );

            particle.appendChild(
                particleMotion
            );

            relationLines.appendChild(
                particle
            );

            const core =
                document.createElementNS(
                    SVG_NS,
                    'circle'
                );

            core.setAttribute(
                'r',
                '1.25'
            );

            core.setAttribute(
                'class',
                'erd-flow-particle-core'
            );

            const coreMotion =
                document.createElementNS(
                    SVG_NS,
                    'animateMotion'
                );

            coreMotion.setAttribute(
                'dur',
                `${2.8 + (relationIndex % 3) * .25}s`
            );

            coreMotion.setAttribute(
                'repeatCount',
                'indefinite'
            );

            coreMotion.setAttribute(
                'begin',
                `${index * .9}s`
            );

            coreMotion.setAttribute(
                'rotate',
                'auto'
            );

            const corePath =
                document.createElementNS(
                    SVG_NS,
                    'mpath'
                );

            corePath.setAttributeNS(
                'http://www.w3.org/1999/xlink',
                'href',
                `#${path.id}`
            );

            corePath.setAttribute(
                'href',
                `#${path.id}`
            );

            coreMotion.appendChild(
                corePath
            );

            core.appendChild(
                coreMotion
            );

            relationLines.appendChild(
                core
            );
        }
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

                element.style.zIndex =
                    '';

                document.body.style.userSelect =
                    '';
            }
        );
    }

    function applySearch() {
        const search =
            searchInput.value
                .trim()
                .toLowerCase();

        tableElements.forEach(
            item => {

                const selected =
                    selectedTables.has(
                        item.name
                    );

                const matches =
                    !search ||
                    item.name.includes(
                        search
                    );

                item.element.classList.toggle(
                    'hidden',
                    !selected ||
                    !matches
                );
            }
        );

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
                (
                    canvas.clientWidth -
                    1900
                ) / 2
            );

        panY =
            Math.max(
                0,
                (
                    canvas.clientHeight -
                    1300
                ) / 2
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

            if (
                event.target.closest(
                    '.erd-table-selector'
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
        () => {
            setZoom(
                zoom + .1
            );
        }
    );

    zoomOutButton.addEventListener(
        'click',
        () => {
            setZoom(
                zoom - .1
            );
        }
    );

    resetViewButton.addEventListener(
        'click',
        resetView
    );

    searchInput.addEventListener(
        'input',
        applySearch
    );

    tableSelectorSearch.addEventListener(
        'input',
        () => {
            renderTableSelector(
                getTables()
            );
        }
    );

    selectAllTablesButton.addEventListener(
        'click',
        () => {

            getTables().forEach(
                table => {
                    selectedTables.add(
                        normalizeTableName(
                            table.name
                        )
                    );
                }
            );

            renderTableSelector(
                getTables()
            );

            applySearch();

            drawRelations();
        }
    );

    clearAllTablesButton.addEventListener(
        'click',
        () => {

            selectedTables.clear();

            renderTableSelector(
                getTables()
            );

            applySearch();

            drawRelations();
        }
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
            [
                ...documentObject.scripts
            ].find(
                script =>
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
        analyzeButton.disabled =
            loading;

        refreshButton.disabled =
            loading;

        analyzeButton.innerHTML =
            loading
                ? `
                    <span>◌</span>
                    <span>Analyzing...</span>
                `
                : `
                    <span class="erd-button-icon">
                        ◈
                    </span>

                    <span class="erd-button-text">
                        Analyze Schema
                    </span>
                `;

        refreshButton.innerHTML =
            loading
                ? `
                    <span>◌</span>
                    <span>Working...</span>
                `
                : `
                    <span class="erd-button-icon">
                        ↻
                    </span>

                    <span class="erd-button-text">
                        Refresh
                    </span>
                `;
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

    renderTables();
</script>

</body>
</html>