<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <meta
        name="csrf-token"
        content="{{ csrf_token() }}"
    >

    <title>{{ config('app.name') }} ERD</title>

    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css"
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
            min-width: 0;
        }

        .erd-logo {
            width: 38px;
            height: 38px;
            flex: 0 0 38px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 10px;
            background:
                linear-gradient(
                    145deg,
                    #263b65,
                    #172746
                );
            border: 1px solid rgba(117,157,231,.18);
            color: #9fc0ff;
            font-size: 17px;
            font-weight: 800;
        }

        .erd-brand-copy {
            min-width: 0;
        }

        .erd-brand-title {
            color: #edf3fc;
            font-size: 14px;
            font-weight: 750;
            white-space: nowrap;
        }

        .erd-brand-subtitle {
            margin-top: 2px;
            color: #71809c;
            font-size: 9px;
            white-space: nowrap;
        }

        .erd-navbar-filters {
            display: flex;
            align-items: center;
            gap: 4px;
            margin-left: 4px;
        }

        .erd-navbar-filter {
            position: relative;
            width: 36px;
            height: 34px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 1px solid transparent;
            border-radius: 8px;
            background: transparent;
            color: #687995;
            cursor: pointer;
            transition:
                background .16s ease,
                border-color .16s ease,
                color .16s ease;
        }

        .erd-navbar-filter i {
            font-size: 12px;
        }

        .erd-navbar-filter:hover {
            background: rgba(255,255,255,.045);
            color: #dce6f5;
        }

        .erd-navbar-filter.active {
            background: rgba(77,131,232,.13);
            border-color: rgba(77,131,232,.25);
            color: #9dbdff;
        }

        .erd-navbar-filter[data-tooltip]::after {
            content: attr(data-tooltip);
            position: absolute;
            left: 50%;
            top: calc(100% + 8px);
            transform:
                translate(-50%, -3px);
            padding: 6px 8px;
            border-radius: 6px;
            background: #17233a;
            border: 1px solid rgba(255,255,255,.08);
            color: #dce6f5;
            font-size: 9px;
            white-space: nowrap;
            opacity: 0;
            pointer-events: none;
            transition: .15s ease;
            z-index: 500;
        }

        .erd-navbar-filter:hover::after {
            opacity: 1;
            transform:
                translate(-50%, 0);
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
        }

        .erd-search:focus {
            border-color: rgba(77,131,232,.65);
            box-shadow:
                0 0 0 3px rgba(77,131,232,.08);
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
        }

        .erd-button:hover {
            background: #1d2c48;
            border-color: var(--border-strong);
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
            inset: 0;
            width: 5000px;
            height: 5000px;
        }

        .erd-relations {
            position: absolute;
            inset: 0;
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
            background: rgba(14,22,39,.98);
            box-shadow:
                0 18px 45px rgba(0,0,0,.28),
                0 3px 10px rgba(0,0,0,.18);
            user-select: none;
            z-index: 2;
        }

        .erd-table:hover {
            border-color:
                rgba(116,153,216,.3);
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
                    135deg,
                    #1b2a46,
                    #142038
                );
            border-bottom:
                1px solid rgba(255,255,255,.07);
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
        }

        .erd-table-meta {
            margin-top: 5px;
            color: #647795;
            font-size: 9px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .erd-columns {
            padding: 4px 0;
        }

        .erd-column {
            min-height: 31px;
            display: grid;
            grid-template-columns:
                minmax(0, 1fr) auto;
            align-items: center;
            gap: 10px;
            padding: 0 13px;
            border-bottom:
                1px solid rgba(255,255,255,.035);
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
            border-top:
                1px solid rgba(255,255,255,.05);
            background:
                rgba(7,12,23,.22);
            color: #52627c;
            font-size: 8px;
        }

        .erd-view-selector,
        .erd-table-selector {
            position: fixed;
            z-index: 80;
            border: 1px solid var(--border);
            border-radius: 11px;
            background:
                rgba(14,22,39,.94);
            box-shadow:
                0 18px 45px rgba(0,0,0,.25);
            backdrop-filter: blur(12px);
        }

        .erd-view-selector {
            left: 24px;
            top: 88px;
            width: 156px;
            padding: 13px;
        }

        .erd-selector-title {
            margin-bottom: 10px;
            color: #7f91ae;
            font-size: 9px;
            font-weight: 750;
            text-transform: uppercase;
            letter-spacing: .5px;
        }

        .erd-view-options {
            display: flex;
            gap: 5px;
        }

        .erd-view-option {
            flex: 1;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            border: 1px solid transparent;
            border-radius: 7px;
            background: transparent;
            color: #7485a1;
            cursor: pointer;
            font-size: 9px;
        }

        .erd-view-option.active {
            background:
                rgba(77,131,232,.12);
            border-color:
                rgba(77,131,232,.25);
            color: #9dbdff;
        }

        .erd-view-radio {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            border: 1px solid #647795;
        }

        .erd-view-option.active
        .erd-view-radio {
            background: #78a5f5;
            border-color: #78a5f5;
            box-shadow:
                0 0 7px rgba(120,165,245,.45);
        }

        .erd-table-selector {
            right: 18px;
            top: 88px;
            width: 238px;
            overflow: hidden;
        }

        .erd-table-selector-header {
            padding: 12px;
            border-bottom:
                1px solid rgba(255,255,255,.06);
        }

        .erd-table-selector-title-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .erd-table-selector-title {
            color: #edf3fc;
            font-size: 11px;
            font-weight: 700;
        }

        .erd-table-selector-count {
            color: #60718d;
            font-size: 8px;
        }

        .erd-selector-search {
            width: 100%;
            height: 31px;
            margin-top: 9px;
            padding: 0 9px;
            border:
                1px solid rgba(255,255,255,.08);
            border-radius: 7px;
            outline: none;
            background: #09111f;
            color: #dce6f5;
            font-size: 9px;
        }

        .erd-selector-actions {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 5px;
            margin-top: 7px;
        }

        .erd-selector-action {
            height: 29px;
            border:
                1px solid rgba(255,255,255,.07);
            border-radius: 6px;
            background: rgba(255,255,255,.025);
            color: #8292ac;
            cursor: pointer;
            font-size: 8px;
        }

        .erd-selector-action:hover {
            background: rgba(255,255,255,.06);
            color: #dce6f5;
        }

        .erd-table-list {
            max-height: 360px;
            overflow-y: auto;
            padding: 5px 0;
        }

        .erd-table-list::-webkit-scrollbar {
            width: 5px;
        }

        .erd-table-list::-webkit-scrollbar-thumb {
            background: #273650;
            border-radius: 5px;
        }

        .erd-table-option {
            display: flex;
            align-items: center;
            gap: 8px;
            min-height: 28px;
            padding: 0 12px;
            color: #9aaac1;
            font-size: 8px;
            cursor: pointer;
        }

        .erd-table-option:hover {
            background: rgba(255,255,255,.035);
        }

        .erd-table-option input {
            width: 13px;
            height: 13px;
            accent-color: #5d8fe8;
        }

        .erd-table-option.hidden {
            display: none;
        }

        .erd-empty {
            position: absolute;
            inset: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            text-align: center;
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
            color: #9fc0ff;
            font-size: 22px;
            font-weight: 800;
        }

        .erd-empty-title {
            color: #edf3fc;
            font-size: 16px;
            font-weight: 700;
        }

        .erd-empty-text {
            max-width: 390px;
            margin: 7px 0 18px;
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
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 14px;
            pointer-events: none;
            z-index: 50;
        }

        .erd-info,
        .erd-controls {
            border: 1px solid rgba(255,255,255,.07);
            background:
                rgba(9,15,28,.86);
            backdrop-filter: blur(10px);
        }

        .erd-info {
            display: flex;
            align-items: center;
            gap: 7px;
            padding: 7px 10px;
            border-radius: 8px;
            color: #60718d;
            font-size: 9px;
        }

        .erd-info-dot {
            width: 5px;
            height: 5px;
            border-radius: 50%;
            background: var(--success);
        }

        .erd-controls {
            display: flex;
            align-items: center;
            gap: 4px;
            padding: 4px;
            border-radius: 9px;
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
}

.erd-relation-flow {
    fill: none;
    stroke: #8fb8ff;
    stroke-width: 2;
    stroke-linecap: round;
    stroke-dasharray: 3 18;
    opacity: .95;
    filter: drop-shadow(0 0 4px rgba(143,184,255,.75));
    animation: erdFlow 1.8s linear infinite;
}

@keyframes erdFlow {
    from {
        stroke-dashoffset: 0;
    }

    to {
        stroke-dashoffset: -42;
    }
}

.erd-relation-particle {
    fill: #b8d4ff;
    opacity: .95;
    filter:
        drop-shadow(0 0 4px rgba(143,184,255,.9))
        drop-shadow(0 0 8px rgba(143,184,255,.45));
}

.erd-relation-particle.pulse {
    animation: erdParticlePulse 1.4s ease-in-out infinite;
}

@keyframes erdParticlePulse {
    0%,
    100% {
        opacity: .45;
    }

    50% {
        opacity: 1;
    }
}

        @media (max-width: 900px) {
            .erd-brand-subtitle {
                display: none;
            }

            .erd-search {
                width: 180px;
            }

            .erd-table-selector {
                width: 210px;
            }
        }

        @media (max-width: 700px) {
            .erd-header {
                padding: 0 12px;
            }

            .erd-navbar-filters {
                display: none;
            }

            .erd-search {
                width: 140px;
            }

            .erd-button-text {
                display: none;
            }

            .erd-button {
                padding: 0 10px;
            }

            .erd-view-selector {
                left: 12px;
            }

            .erd-table-selector {
                right: 12px;
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

            <div class="erd-navbar-filters">

                <button
                    type="button"
                    class="erd-navbar-filter active"
                    data-table-filter="relational"
                    data-tooltip="Relational tables"
                    aria-label="Relational tables"
                >
                    <i class="fa-solid fa-code-branch"></i>
                </button>

                <button
                    type="button"
                    class="erd-navbar-filter"
                    data-table-filter="non-relational"
                    data-tooltip="Non-relational tables"
                    aria-label="Non-relational tables"
                >
                    <i class="fa-solid fa-layer-group"></i>
                </button>

            </div>

        </div>

        <div class="erd-toolbar">

            <div class="erd-search-wrap">

                <span class="erd-search-icon">
                    <i class="fa-solid fa-magnifying-glass"></i>
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
                <i class="fa-solid fa-wand-magic-sparkles"></i>

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
                <i class="fa-solid fa-rotate"></i>

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
            class="erd-view-selector"
            id="erdViewSelector"
        >

            <div class="erd-selector-title">
                View Mode
            </div>

            <div class="erd-view-options">

                <button
                    type="button"
                    class="erd-view-option active"
                    data-view-mode="simple"
                >
                    <span class="erd-view-radio"></span>
                    <span>Simple</span>
                </button>

                <button
                    type="button"
                    class="erd-view-option"
                    data-view-mode="detailed"
                >
                    <span class="erd-view-radio"></span>
                    <span>Detailed</span>
                </button>

            </div>

        </div>

        <aside
            class="erd-table-selector"
            id="erdTableSelector"
        >

            <div class="erd-table-selector-header">

                <div class="erd-table-selector-title-row">

                    <div class="erd-table-selector-title">
                        Tables
                    </div>

                    <div
                        class="erd-table-selector-count"
                        id="tableSelectorCount"
                    >
                        0 / 0
                    </div>

                </div>

                <input
                    type="search"
                    class="erd-selector-search"
                    id="tableSelectorSearch"
                    placeholder="Find table..."
                    autocomplete="off"
                >

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
                class="erd-table-list"
                id="erdTableList"
            ></div>

        </aside>

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

                <span>·</span>

                <span id="modelCount">
                    0 models
                </span>

                <span>·</span>

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
                    <i class="fa-solid fa-expand"></i>
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
        document.getElementById(
            'erdCanvas'
        );

    const stage =
        document.getElementById(
            'erdStage'
        );

    const workspace =
        document.getElementById(
            'erdWorkspace'
        );

    const relationLines =
        document.getElementById(
            'erdRelationLines'
        );

    const searchInput =
        document.getElementById(
            'tableSearch'
        );

    const analyzeButton =
        document.getElementById(
            'analyzeButton'
        );

    const refreshButton =
        document.getElementById(
            'refreshButton'
        );

    const tableCount =
        document.getElementById(
            'tableCount'
        );

    const modelCount =
        document.getElementById(
            'modelCount'
        );

    const relationCount =
        document.getElementById(
            'relationCount'
        );

    const tableSelector =
        document.getElementById(
            'erdTableList'
        );

    const tableSelectorSearch =
        document.getElementById(
            'tableSelectorSearch'
        );

    const tableSelectorCount =
        document.getElementById(
            'tableSelectorCount'
        );

    const zoomValue =
        document.getElementById(
            'zoomValue'
        );

    const zoomInButton =
        document.getElementById(
            'zoomIn'
        );

    const zoomOutButton =
        document.getElementById(
            'zoomOut'
        );

    const resetViewButton =
        document.getElementById(
            'resetView'
        );

    const toast =
        document.getElementById(
            'erdToast'
        );

    const SVG_NS =
        'http://www.w3.org/2000/svg';

    let tableElements = [];

    let selectedTables = new Set();

    let viewMode = 'simple';

    let tableFilter = 'relational';

    let zoom = 1;

    let panX = 0;

    let panY = 0;

    let isPanning = false;

    let panStartX = 0;

    let panStartY = 0;

    let initialPanX = 0;

    let initialPanY = 0;

    function normalizeTableName(
        value
    ) {
        return String(
            value ?? ''
        )
            .replace(
                /[`'"]/g,
                ''
            )
            .trim()
            .toLowerCase();
    }

    function getTables() {
        const migrations =
            window.ERD
                ?.migrations
                ?.migrations ?? [];

        const models =
            window.ERD
                ?.models
                ?.models ?? [];

        const tables =
            new Map();

        migrations.forEach(
            migration => {

                (
                    migration.tables ?? []
                ).forEach(
                    table => {

                        if (!table.name) {
                            return;
                        }

                        const key =
                            normalizeTableName(
                                table.name
                            );

                        if (!tables.has(key)) {

                            tables.set(
                                key,
                                {
                                    name:
                                        table.name,

                                    columns:
                                        table.columns ??
                                        [],

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
                            tables.get(key);

                        if (
                            table.operation ===
                            'create'
                        ) {
                            existing.columns =
                                table.columns ??
                                existing.columns;
                        }

                        const names =
                            new Set(
                                existing.columns.map(
                                    column =>
                                        normalizeTableName(
                                            column.name
                                        )
                                )
                            );

                        (
                            table.columns ?? []
                        ).forEach(
                            column => {

                                const columnName =
                                    normalizeTableName(
                                        column.name
                                    );

                                if (
                                    !names.has(
                                        columnName
                                    )
                                ) {
                                    existing.columns.push(
                                        column
                                    );
                                }
                            }
                        );
                    }
                );
            }
        );

        models.forEach(
            model => {

                if (!model.table) {
                    return;
                }

                const key =
                    normalizeTableName(
                        model.table
                    );

                if (
                    tables.has(key)
                ) {
                    return;
                }

                tables.set(
                    key,
                    {
                        name:
                            model.table,

                        columns: [],

                        operation:
                            'model',

                        migration:
                            null,

                        modelOnly:
                            true
                    }
                );
            }
        );

        return [
            ...tables.values()
        ];
    }

    function getRelations() {
        return (
            window.ERD
                ?.relations
                ?.relations ?? []
        );
    }

    function getRelationshipTables() {
        const result =
            new Set();

        getRelations().forEach(
            relation => {

                const from =
                    normalizeTableName(
                        relation.from_table ??
                        relation.from ??
                        relation.table
                    );

                const to =
                    normalizeTableName(
                        relation.to_table ??
                        relation.to ??
                        relation.referenced_table
                    );

                if (from) {
                    result.add(from);
                }

                if (to) {
                    result.add(to);
                }
            }
        );

        return result;
    }

    function matchesTableFilter(
        tableName
    ) {
        const relational =
            getRelationshipTables();

        if (
            tableFilter ===
            'relational'
        ) {
            return relational.has(
                tableName
            );
        }

        if (
            tableFilter ===
            'non-relational'
        ) {
            return !relational.has(
                tableName
            );
        }

        return true;
    }

    function renderTables() {
        workspace.innerHTML = '';

        relationLines.innerHTML = '';

        tableElements = [];

        const tables =
            getTables();

        const relations =
            getRelations();

        const models =
            window.ERD
                ?.models
                ?.models ?? [];

        tableCount.textContent =
            `${tables.length} tables`;

        modelCount.textContent =
            `${models.length} models`;

        relationCount.textContent =
            `${relations.length} relations`;

        if (!tables.length) {
            renderEmptyState();

            return;
        }

        const available =
            new Set(
                tables.map(
                    table =>
                        normalizeTableName(
                            table.name
                        )
                )
            );

        if (
            selectedTables.size === 0 &&
            !sessionStorage.getItem(
                'laravel-erd-selection'
            )
        ) {
            selectedTables =
                new Set(
                    available
                );
        } else {
            selectedTables =
                new Set(
                    [...selectedTables]
                        .filter(
                            name =>
                                available.has(
                                    name
                                )
                        )
                );

            if (
                selectedTables.size === 0
            ) {
                const stored =
                    loadSelection();

                if (
                    stored.length
                ) {
                    selectedTables =
                        new Set(
                            stored.filter(
                                name =>
                                    available.has(
                                        name
                                    )
                            )
                        );
                }
            }
        }

        tables.forEach(
            table => {

                const element =
                    createTable(
                        table
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

        saveSelection();

        renderTableSelector();

        applyVisibility();

        refreshButton.style.display =
            'inline-flex';

        resetView();
    }

    function createTable(
        table
    ) {
        const element =
            document.createElement(
                'div'
            );

        element.className =
            'erd-table';

        element.dataset.table =
            normalizeTableName(
                table.name
            );

        const columns =
            Array.isArray(
                table.columns
            )
                ? table.columns
                : [];

        const columnMarkup =
            columns.map(
                column => {

                    const badges = [];

                    if (
                        column.primary
                    ) {
                        badges.push(
                            `<span class="erd-badge primary">PK</span>`
                        );
                    }

                    if (
                        column.unique
                    ) {
                        badges.push(
                            `<span class="erd-badge unique">UQ</span>`
                        );
                    }

                    if (
                        column.nullable
                    ) {
                        badges.push(
                            `<span class="erd-badge">NULL</span>`
                        );
                    }

                    return `
                        <div
                            class="erd-column"
                            data-column="${escapeHtml(
                                column.name ?? ''
                            )}"
                        >
                            <div class="erd-column-name">
                                ${escapeHtml(
                                    column.name ?? ''
                                )}
                                ${
                                    badges.length
                                        ? `
                                            <span class="erd-badges">
                                                ${badges.join('')}
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
            ).join('');

        element.innerHTML = `
            <div class="erd-table-header">

                <div class="erd-table-title-row">

                    <div class="erd-table-name">
                        ${escapeHtml(
                            table.name
                        )}
                    </div>

                    ${
                        table.modelOnly
                            ? ''
                            : `
                                <div class="erd-table-operation">
                                    ${escapeHtml(
                                        table.operation ??
                                        'table'
                                    )}
                                </div>
                            `
                    }

                </div>

                ${
                    table.migration
                        ? `
                            <div class="erd-table-meta">
                                ${escapeHtml(
                                    table.migration
                                )}
                            </div>
                        `
                        : ''
                }

            </div>

            ${
                viewMode === 'detailed'
                    ? `
                        <div class="erd-columns">

                            ${
                                columnMarkup ||
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
                            ${columns.length}
                            column${columns.length === 1 ? '' : 's'}
                        </div>
                    `
                    : ''
            }
        `;

        makeDraggable(
            element
        );

        workspace.appendChild(
            element
        );

        return element;
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
                    Analyze your Laravel application
                    to generate the ERD.
                </div>

                <button
                    type="button"
                    class="erd-button primary"
                    onclick="analyzeSchema()"
                >
                    <i class="fa-solid fa-wand-magic-sparkles"></i>
                    Analyze Schema
                </button>

            </div>
        `;
    }

    function applyVisibility() {
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

                const matchesSearch =
                    !search ||
                    item.name.includes(
                        search
                    );

                const matchesFilter =
                    matchesTableFilter(
                        item.name
                    );

                const visible =
                    selected &&
                    matchesSearch &&
                    matchesFilter;

                item.element.classList.toggle(
                    'hidden',
                    !visible
                );
            }
        );

        layoutTables();

        drawRelations();
    }

    function layoutTables() {
        const visible =
            tableElements.filter(
                item =>
                    !item.element
                        .classList
                        .contains('hidden')
            );

        if (!visible.length) {
            return;
        }

        const graph =
            new Map();

        visible.forEach(
            item => {
                graph.set(
                    item.name,
                    new Set()
                );
            }
        );

        getRelations().forEach(
            relation => {

                const from =
                    normalizeTableName(
                        relation.from_table ??
                        relation.from ??
                        relation.table
                    );

                const to =
                    normalizeTableName(
                        relation.to_table ??
                        relation.to ??
                        relation.referenced_table
                    );

                if (
                    !graph.has(from) ||
                    !graph.has(to) ||
                    from === to
                ) {
                    return;
                }

                graph.get(from).add(to);
                graph.get(to).add(from);
            }
        );

        const components =
            getConnectedComponents(
                graph
            );

        const positions =
            new Map();

        let componentY = 100;

        components.forEach(
            component => {

                const levels =
                    buildLevels(
                        component,
                        graph
                    );

                let maxWidth = 0;

                levels.forEach(
                    (nodes, level) => {

                        const width =
                            nodes.length *
                            390;

                        maxWidth =
                            Math.max(
                                maxWidth,
                                width
                            );

                        nodes.forEach(
                            (name, index) => {

                                const element =
                                    getTableElement(
                                        name
                                    );

                                if (
                                    !element
                                ) {
                                    return;
                                }

                                const center =
                                    (
                                        nodes.length -
                                        1
                                    ) / 2;

                                positions.set(
                                    name,
                                    {
                                        x:
                                            250 +
                                            (
                                                index -
                                                center
                                            ) *
                                            390,

                                        y:
                                            componentY +
                                            level *
                                            280
                                    }
                                );
                            }
                        );
                    }
                );

                const componentHeight =
                    levels.size *
                    280;

                componentY +=
                    Math.max(
                        componentHeight +
                        100,
                        360
                    );
            }
        );

        visible.forEach(
            item => {

                const position =
                    positions.get(
                        item.name
                    );

                if (!position) {
                    return;
                }

                item.element.style.left =
                    `${position.x}px`;

                item.element.style.top =
                    `${position.y}px`;
            }
        );
    }

    function getConnectedComponents(
        graph
    ) {
        const components = [];

        const visited =
            new Set();

        graph.forEach(
            (_, start) => {

                if (
                    visited.has(start)
                ) {
                    return;
                }

                const component = [];

                const queue = [
                    start
                ];

                visited.add(
                    start
                );

                while (
                    queue.length
                ) {
                    const current =
                        queue.shift();

                    component.push(
                        current
                    );

                    (
                        graph.get(current) ??
                        []
                    ).forEach(
                        neighbor => {

                            if (
                                visited.has(
                                    neighbor
                                )
                            ) {
                                return;
                            }

                            visited.add(
                                neighbor
                            );

                            queue.push(
                                neighbor
                            );
                        }
                    );
                }

                components.push(
                    component
                );
            }
        );

        return components;
    }

    function buildLevels(
        component,
        graph
    ) {
        const root =
            component
                .slice()
                .sort(
                    (a, b) =>
                        (
                            graph.get(b)?.size ??
                            0
                        ) -
                        (
                            graph.get(a)?.size ??
                            0
                        )
                )[0];

        const levels =
            new Map();

        const visited =
            new Set();

        const queue = [
            {
                name: root,
                level: 0
            }
        ];

        visited.add(
            root
        );

        while (
            queue.length
        ) {
            const current =
                queue.shift();

            if (
                !levels.has(
                    current.level
                )
            ) {
                levels.set(
                    current.level,
                    []
                );
            }

            levels
                .get(
                    current.level
                )
                .push(
                    current.name
                );

            (
                graph.get(
                    current.name
                ) ?? []
            )
                .forEach(
                    neighbor => {

                        if (
                            visited.has(
                                neighbor
                            )
                        ) {
                            return;
                        }

                        visited.add(
                            neighbor
                        );

                        queue.push({
                            name:
                                neighbor,

                            level:
                                current.level +
                                1
                        });
                    }
                );
        }

        return levels;
    }

    function getTableElement(
        tableName
    ) {
        const normalized =
            normalizeTableName(
                tableName
            );

        return (
            tableElements.find(
                item =>
                    item.name ===
                    normalized
            )?.element ?? null
        );
    }

function drawRelations() {
    relationLines.innerHTML = '';

    const relations =
        getRelations();

    relations.forEach(
        (
            relation,
            index
        ) => {

            const fromElement =
                getTableElement(
                    relation.from_table ??
                    relation.from ??
                    relation.table
                );

            const toElement =
                getTableElement(
                    relation.to_table ??
                    relation.to ??
                    relation.referenced_table
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
                    relation.from_column,
                    toElement
                );

            const to =
                getConnectionPoint(
                    toElement,
                    relation.to_column,
                    fromElement
                );

            const pathData =
                createRelationPath(
                    from,
                    to
                );

            const pathId =
                `erd-relation-${index}`;

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
                `${relation.from_table ?? ''}.${relation.from_column ?? ''}`
            );

            path.setAttribute(
                'data-to',
                `${relation.to_table ?? ''}.${relation.to_column ?? ''}`
            );

            relationLines.appendChild(
                path
            );

            createFlowAnimation(
                pathData,
                pathId,
                index
            );
        }
    );
}

function createFlowAnimation(
    pathData,
    pathId,
    index
) {
    const flow =
        document.createElementNS(
            SVG_NS,
            'path'
        );

    flow.setAttribute(
        'class',
        'erd-relation-flow'
    );

    flow.setAttribute(
        'd',
        pathData
    );

    flow.style.animationDelay =
        `${(index % 5) * -0.35}s`;

    relationLines.appendChild(
        flow
    );

    const particles = 2;

    for (
        let i = 0;
        i < particles;
        i++
    ) {
        createFlowParticle(
            pathId,
            i,
            particles
        );
    }
}

function createFlowAnimation(
    pathData,
    pathId,
    index
) {
    const flow =
        document.createElementNS(
            SVG_NS,
            'path'
        );

    flow.setAttribute(
        'class',
        'erd-relation-flow'
    );

    flow.setAttribute(
        'd',
        pathData
    );

    flow.style.animationDelay =
        `${(index % 5) * -0.35}s`;

    relationLines.appendChild(
        flow
    );

    const particles = 2;

    for (
        let i = 0;
        i < particles;
        i++
    ) {
        createFlowParticle(
            pathId,
            i,
            particles
        );
    }
}

    function getConnectionPoint(
        element,
        columnName,
        target
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
                target.style.left
            ) || 0;

        const targetTop =
            parseFloat(
                target.style.top
            ) || 0;

        const targetWidth =
            target.offsetWidth;

        const targetHeight =
            target.offsetHeight;

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

        if (
            viewMode === 'detailed' &&
            columnName
        ) {
            const column =
                [
                    ...element.querySelectorAll(
                        '.erd-column'
                    )
                ].find(
                    item =>
                        normalizeTableName(
                            item.dataset.column
                        ) ===
                        normalizeTableName(
                            columnName
                        )
                );

            if (column) {
                const y =
                    top +
                    column.offsetTop +
                    column.offsetHeight /
                    2;

                if (
                    targetCenterX >= centerX
                ) {
                    return {
                        x:
                            left +
                            width,

                        y,

                        side:
                            'right'
                    };
                }

                return {
                    x:
                        left,

                    y,

                    side:
                        'left'
                };
            }
        }

        const dx =
            targetCenterX -
            centerX;

        const dy =
            targetCenterY -
            centerY;

        if (
            Math.abs(dx) >=
            Math.abs(dy)
        ) {
            return dx >= 0
                ? {
                    x:
                        left +
                        width,

                    y:
                        centerY,

                    side:
                        'right'
                }
                : {
                    x:
                        left,

                    y:
                        centerY,

                    side:
                        'left'
                };
        }

        return dy >= 0
            ? {
                x:
                    centerX,

                y:
                    top +
                    height,

                side:
                    'bottom'
            }
            : {
                x:
                    centerX,

                y:
                    top,

                side:
                    'top'
            };
    }

    function createRelationPath(
        from,
        to
    ) {
        const dx =
            to.x -
            from.x;

        const dy =
            to.y -
            from.y;

        const distance =
            Math.max(
                Math.abs(dx),
                Math.abs(dy)
            );

        const curve =
            Math.max(
                60,
                Math.min(
                    180,
                    distance * .35
                )
            );

        let c1x =
            from.x;

        let c1y =
            from.y;

        let c2x =
            to.x;

        let c2y =
            to.y;

        if (
            from.side === 'right'
        ) {
            c1x += curve;
        }

        if (
            from.side === 'left'
        ) {
            c1x -= curve;
        }

        if (
            from.side === 'bottom'
        ) {
            c1y += curve;
        }

        if (
            from.side === 'top'
        ) {
            c1y -= curve;
        }

        if (
            to.side === 'right'
        ) {
            c2x += curve;
        }

        if (
            to.side === 'left'
        ) {
            c2x -= curve;
        }

        if (
            to.side === 'bottom'
        ) {
            c2y += curve;
        }

        if (
            to.side === 'top'
        ) {
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

    // function createRelationDot(
    //     point,
    //     index
    // ) {
    //     const circle =
    //         document.createElementNS(
    //             SVG_NS,
    //             'circle'
    //         );

    //     circle.setAttribute(
    //         'class',
    //         'erd-relation-dot'
    //     );

    //     circle.setAttribute(
    //         'r',
    //         '2.5'
    //     );

    //     circle.setAttribute(
    //         'cx',
    //         point.x
    //     );

    //     circle.setAttribute(
    //         'cy',
    //         point.y
    //     );

    //     relationLines.appendChild(
    //         circle
    //     );
    // }

    function makeDraggable(
        element
    ) {
        const header =
            element.querySelector(
                '.erd-table-header'
            );

        if (!header) {
            return;
        }

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
                        startLeft +
                        deltaX
                    )}px`;

                element.style.top =
                    `${Math.max(
                        0,
                        startTop +
                        deltaY
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

    function renderTableSelector() {
        const tables =
            getTables();

        tableSelector.innerHTML =
            '';

        tables.forEach(
            table => {

                const name =
                    normalizeTableName(
                        table.name
                    );

                const label =
                    document.createElement(
                        'label'
                    );

                label.className =
                    'erd-table-option';

                label.dataset.name =
                    name;

                label.innerHTML = `
                    <input
                        type="checkbox"
                        value="${escapeHtml(name)}"
                        ${selectedTables.has(name) ? 'checked' : ''}
                    >

                    <span>
                        ${escapeHtml(
                            table.name
                        )}
                    </span>
                `;

                const checkbox =
                    label.querySelector(
                        'input'
                    );

                checkbox.addEventListener(
                    'change',
                    () => {

                        if (
                            checkbox.checked
                        ) {
                            selectedTables.add(
                                name
                            );
                        } else {
                            selectedTables.delete(
                                name
                            );
                        }

                        saveSelection();

                        updateTableSelectorCount();

                        applyVisibility();
                    }
                );

                tableSelector.appendChild(
                    label
                );
            }
        );

        updateTableSelectorCount();
    }

    function updateTableSelectorCount() {
        const total =
            getTables().length;

        const selected =
            selectedTables.size;

        tableSelectorCount.textContent =
            `${selected} / ${total}`;
    }

    function applySelectorSearch() {
        const search =
            tableSelectorSearch.value
                .trim()
                .toLowerCase();

        [
            ...tableSelector.children
        ].forEach(
            item => {

                const visible =
                    !search ||
                    item.dataset.name.includes(
                        search
                    );

                item.classList.toggle(
                    'hidden',
                    !visible
                );
            }
        );
    }

    function selectAllTables() {
        selectedTables =
            new Set(
                getTables().map(
                    table =>
                        normalizeTableName(
                            table.name
                        )
                )
            );

        saveSelection();

        renderTableSelector();

        applyVisibility();
    }

    function clearAllTables() {
        selectedTables =
            new Set();

        saveSelection();

        renderTableSelector();

        applyVisibility();
    }

    function saveSelection() {
        sessionStorage.setItem(
            'laravel-erd-selection',
            JSON.stringify(
                [...selectedTables]
            )
        );
    }

    function loadSelection() {
        try {
            return JSON.parse(
                sessionStorage.getItem(
                    'laravel-erd-selection'
                ) ?? '[]'
            );
        } catch {
            return [];
        }
    }

    function applyViewMode() {
        const current =
            [...selectedTables];

        renderTables();

        selectedTables =
            new Set(
                current
            );

        saveSelection();

        applyVisibility();
    }

    function updateView() {
        stage.style.transform =
            `translate(${panX}px, ${panY}px) scale(${zoom})`;

        zoomValue.textContent =
            `${Math.round(
                zoom * 100
            )}%`;
    }

    function setZoom(
        value
    ) {
        zoom =
            Math.min(
                1.8,
                Math.max(
                    .45,
                    value
                )
            );

        updateView();
    }

    function resetView() {
        zoom = 1;

        const width =
            5000;

        const height =
            5000;

        panX =
            Math.max(
                0,
                (
                    canvas.clientWidth -
                    width
                ) / 2
            );

        panY =
            Math.max(
                0,
                (
                    canvas.clientHeight -
                    height
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
                    '.erd-view-selector'
                ) ||
                event.target.closest(
                    '.erd-table-selector'
                ) ||
                event.target.closest(
                    '.erd-bottom'
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

            if (
                !event.ctrlKey
            ) {
                return;
            }

            event.preventDefault();

            setZoom(
                zoom +
                (
                    event.deltaY < 0
                        ? .1
                        : -.1
                )
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
        applyVisibility
    );

    tableSelectorSearch.addEventListener(
        'input',
        applySelectorSearch
    );

    document.getElementById(
        'selectAllTables'
    ).addEventListener(
        'click',
        selectAllTables
    );

    document.getElementById(
        'clearAllTables'
    ).addEventListener(
        'click',
        clearAllTables
    );

    document
        .querySelectorAll(
            '[data-view-mode]'
        )
        .forEach(
            button => {

                button.addEventListener(
                    'click',
                    () => {

                        viewMode =
                            button.dataset.viewMode;

                        document
                            .querySelectorAll(
                                '[data-view-mode]'
                            )
                            .forEach(
                                item => {
                                    item.classList.toggle(
                                        'active',
                                        item === button
                                    );
                                }
                            );

                        applyViewMode();
                    }
                );
            }
        );

    document
        .querySelectorAll(
            '[data-table-filter]'
        )
        .forEach(
            button => {

                button.addEventListener(
                    'click',
                    () => {

                        tableFilter =
                            button.dataset.tableFilter;

                        document
                            .querySelectorAll(
                                '[data-table-filter]'
                            )
                            .forEach(
                                item => {
                                    item.classList.toggle(
                                        'active',
                                        item === button
                                    );
                                }
                            );

                        applyVisibility();
                    }
                );
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

    function setLoading(
        loading
    ) {
        analyzeButton.disabled =
            loading;

        refreshButton.disabled =
            loading;

        analyzeButton.innerHTML =
            loading
                ? `
                    <i class="fa-solid fa-spinner fa-spin"></i>
                    <span class="erd-button-text">
                        Analyzing...
                    </span>
                `
                : `
                    <i class="fa-solid fa-wand-magic-sparkles"></i>
                    <span class="erd-button-text">
                        Analyze Schema
                    </span>
                `;

        refreshButton.innerHTML =
            loading
                ? `
                    <i class="fa-solid fa-spinner fa-spin"></i>
                    <span class="erd-button-text">
                        Working...
                    </span>
                `
                : `
                    <i class="fa-solid fa-rotate"></i>
                    <span class="erd-button-text">
                        Refresh
                    </span>
                `;
    }

    function showToast(
        message
    ) {
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

    function escapeHtml(
        value
    ) {
        return String(
            value ?? ''
        )
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