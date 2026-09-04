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
        rel="icon"
        href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 64 64'%3E%3Crect width='64' height='64' rx='14' fill='%23315ea8'/%3E%3Ctext x='32' y='43' text-anchor='middle' font-family='Arial' font-size='34' font-weight='700' fill='white'%3EE%3C/text%3E%3C/svg%3E"
    >

    <style>
        :root {
            --bg: #080d19;
            --panel: #0e1627;
            --panel-2: #121d32;
            --panel-3: #17233b;
            --border: rgba(255,255,255,.08);
            --border-strong: rgba(255,255,255,.14);
            --text: #f3f7ff;
            --muted: #72819b;
            --muted-2: #56657f;
            --accent: #4d83e8;
            --accent-hover: #5b91f5;
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
            scrollbar-width: none;
            -ms-overflow-style: none;
        }

        html::-webkit-scrollbar,
        body::-webkit-scrollbar {
            width: 0;
            height: 0;
            display: none;
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
            background: linear-gradient(
                145deg,
                #263b65,
                #172746
            );
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
            color: #647795;
            font-size: 15px;
            pointer-events: none;
        }

        .erd-button {
            height: 38px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 7px;
            padding: 0 13px;
            border: 1px solid var(--border);
            border-radius: 8px;
            background: #17233a;
            color: #dce6f6;
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
            grid-template-columns: minmax(0, 1fr) auto;
            align-items: center;
            gap: 10px;
            padding: 0 13px;
            border-bottom: 1px solid rgba(255,255,255,.045);
        }

        .erd-column:last-child {
            border-bottom: 0;
        }

        .erd-column-name {
            min-width: 0;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            color: #dce5f5;
            font-size: 10px;
        }

        .erd-column-type {
            color: #7486a3;
            white-space: nowrap;
            font-size: 9px;
        }

        .erd-badges {
            display: inline-flex;
            align-items: center;
            gap: 3px;
            margin-left: 5px;
        }

        .erd-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 2px 4px;
            border-radius: 4px;
            background: rgba(255,255,255,.07);
            color: #8fa1bd;
            font-size: 7px;
            font-weight: 750;
            line-height: 1;
        }

        .erd-badge.primary {
            background: rgba(77,131,232,.16);
            color: #8eb5ff;
        }

        .erd-badge.unique {
            background: rgba(85,201,138,.12);
            color: #79d9a3;
        }

        .erd-table-footer {
            padding: 7px 13px;
            border-top: 1px solid rgba(255,255,255,.06);
            color: #596982;
            font-size: 9px;
        }

        .erd-table.erd-simple-mode {
            min-height: 48px;
        }

        .erd-table.erd-simple-mode .erd-table-header {
            min-height: 48px;
            border-bottom: 0;
        }

        .erd-table.erd-simple-mode .erd-table-operation,
        .erd-table.erd-simple-mode .erd-table-meta,
        .erd-table.erd-simple-mode .erd-table-details {
            display: none;
        }

        .erd-table-details {
            display: block;
        }

        .erd-view-selector,
        .erd-table-selector {
            position: absolute;
            top: 16px;
            z-index: 70;
            border: 1px solid rgba(151,176,220,.14);
            border-radius: 10px;
            background: rgba(9,15,28,.9);
            box-shadow:
                0 15px 40px rgba(0,0,0,.25),
                0 2px 8px rgba(0,0,0,.15);
            backdrop-filter: blur(14px);
            -webkit-backdrop-filter: blur(14px);
        }

        .erd-view-selector {
            left: 16px;
            width: 145px;
        }

        .erd-table-selector {
            right: 16px;
            width: 240px;
            max-height: min(520px, calc(100% - 100px));
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        .erd-selector-title {
            padding: 10px 12px 8px;
            color: #dce6f5;
            font-size: 9px;
            font-weight: 750;
            text-transform: uppercase;
            letter-spacing: .7px;
        }

        .erd-view-options {
            padding: 4px 7px 8px;
            display: flex;
            flex-direction: column;
            gap: 3px;
        }

        .erd-view-option {
            width: 100%;
            min-height: 34px;
            display: flex;
            align-items: center;
            gap: 9px;
            padding: 0 9px;
            border: 1px solid transparent;
            border-radius: 7px;
            background: transparent;
            color: #8190a8;
            cursor: pointer;
            text-align: left;
            font-size: 10px;
            transition:
                background .15s ease,
                border-color .15s ease,
                color .15s ease;
        }

        .erd-view-option:hover {
            background: rgba(255,255,255,.045);
            color: #dbe5f5;
        }

        .erd-view-option.active {
            background: rgba(77,131,232,.11);
            border-color: rgba(77,131,232,.22);
            color: #e8f0ff;
        }

        .erd-view-radio {
            width: 13px;
            height: 13px;
            flex-shrink: 0;
            border: 1px solid #53657f;
            border-radius: 50%;
            position: relative;
        }

        .erd-view-option.active .erd-view-radio {
            border-color: #6e9bf0;
        }

        .erd-view-option.active .erd-view-radio::after {
            content: "";
            position: absolute;
            left: 3px;
            top: 3px;
            width: 5px;
            height: 5px;
            border-radius: 50%;
            background: #6e9bf0;
        }

        .erd-table-selector-header {
            padding: 11px 11px 9px;
            border-bottom: 1px solid rgba(255,255,255,.07);
        }

        .erd-table-selector-title-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
        }

        .erd-table-selector-title {
            color: #e2eaf7;
            font-size: 10px;
            font-weight: 750;
        }

        .erd-table-selector-count {
            color: #667895;
            font-size: 8px;
        }

        .erd-selector-search {
            width: 100%;
            height: 30px;
            margin-top: 8px;
            padding: 0 9px;
            border: 1px solid rgba(255,255,255,.08);
            border-radius: 7px;
            outline: none;
            background: #0a1220;
            color: #e5edf9;
            font-size: 9px;
        }

        .erd-selector-search:focus {
            border-color: rgba(77,131,232,.45);
        }

        .erd-selector-search::placeholder {
            color: #56657d;
        }

        .erd-selector-actions {
            display: flex;
            gap: 5px;
            margin-top: 7px;
        }

        .erd-selector-action {
            flex: 1;
            height: 27px;
            border: 1px solid rgba(255,255,255,.07);
            border-radius: 6px;
            background: rgba(255,255,255,.035);
            color: #7d8da6;
            cursor: pointer;
            font-size: 8px;
            font-weight: 650;
        }

        .erd-selector-action:hover {
            background: rgba(255,255,255,.07);
            color: #dce6f5;
        }

        .erd-table-list {
            flex: 1;
            overflow-y: auto;
            padding: 5px;
            scrollbar-width: thin;
            scrollbar-color: #263650 transparent;
        }

        .erd-table-list::-webkit-scrollbar {
            width: 5px;
        }

        .erd-table-list::-webkit-scrollbar-track {
            background: transparent;
        }

        .erd-table-list::-webkit-scrollbar-thumb {
            background: #263650;
            border-radius: 10px;
        }

        .erd-table-item {
            display: flex;
            align-items: center;
            gap: 8px;
            min-height: 31px;
            padding: 0 7px;
            border-radius: 6px;
            color: #8998af;
            cursor: pointer;
            transition: background .12s ease;
        }

        .erd-table-item:hover {
            background: rgba(255,255,255,.045);
            color: #dce6f5;
        }

        .erd-table-item.hidden-by-search {
            display: none;
        }

        .erd-table-checkbox {
            width: 13px;
            height: 13px;
            flex-shrink: 0;
            margin: 0;
            accent-color: var(--accent);
            cursor: pointer;
        }

        .erd-table-item-name {
            min-width: 0;
            flex: 1;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            font-size: 9px;
        }

        .erd-table-selector-empty {
            padding: 18px 10px;
            text-align: center;
            color: #5d6d87;
            font-size: 9px;
        }

        .erd-empty {
            position: absolute;
            inset: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            text-align: center;
            color: #647795;
        }

        .erd-empty-icon {
            width: 46px;
            height: 46px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 13px;
            border: 1px solid rgba(116,153,216,.18);
            border-radius: 12px;
            background: #111d32;
            color: #7fa7ed;
            font-size: 17px;
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
                width: 210px;
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

            .erd-view-selector {
                left: 10px;
                top: 10px;
            }

            .erd-table-selector {
                right: 10px;
                top: 10px;
                width: 190px;
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

        <div
            class="erd-stage"
            id="erdStage"
        >

            <svg
                class="erd-relations"
                id="erdRelations"
                width="5000"
                height="5000"
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
                            stdDeviation="2"
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
                    id="zoomLevel"
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

    const analyzeButton =
        document.getElementById('analyzeButton');

    const refreshButton =
        document.getElementById('refreshButton');

    const toast =
        document.getElementById('erdToast');

    const tableCount =
        document.getElementById('tableCount');

    const modelCount =
        document.getElementById('modelCount');

    const relationCount =
        document.getElementById('relationCount');

    const zoomLevel =
        document.getElementById('zoomLevel');

    const zoomIn =
        document.getElementById('zoomIn');

    const zoomOut =
        document.getElementById('zoomOut');

    const resetViewButton =
        document.getElementById('resetView');

    const viewSelector =
        document.getElementById('erdViewSelector');

    const tableSelector =
        document.getElementById('erdTableSelector');

    const tableSelectorSearch =
        document.getElementById('tableSelectorSearch');

    const tableSelectorList =
        document.getElementById('erdTableList');

    const tableSelectorCount =
        document.getElementById('tableSelectorCount');

    const selectAllTables =
        document.getElementById('selectAllTables');

    const clearAllTables =
        document.getElementById('clearAllTables');

    const viewModeButtons =
        document.querySelectorAll(
            '[data-view-mode]'
        );

    let tableElements = [];

    let selectedTables =
        new Set();

    let tableSelectionInitialized =
        false;

    let viewMode =
        'simple';

    let zoom =
        1;

    let panX =
        0;

    let panY =
        0;

    let isPanning =
        false;

    let panStartX =
        0;

    let panStartY =
        0;

    let initialPanX =
        0;

    let initialPanY =
        0;

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
                                    name:
                                        table.name,

                                    columns:
                                        table.columns ?? [],

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
                            tables.get(
                                table.name
                            );

                        if (
                            table.operation ===
                            'create'
                        ) {
                            existing.columns =
                                table.columns ??
                                existing.columns;
                        }

                        if (
                            table.columns?.length
                        ) {

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

        return [
            ...tables.values()
        ];
    }

    function getRelations() {
        return (
            window.ERD.relations?.relations ??
            []
        );
    }

    function normalizeTableName(value) {
        return String(
            value ?? ''
        )
            .trim()
            .toLowerCase();
    }

    function getVisibleRelations() {
        const relations =
            getRelations();

        return relations.filter(
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

        tableCount.textContent =
            `${tables.length} table${tables.length === 1 ? '' : 's'}`;

        const models =
            window.ERD.models?.models ?? [];

        modelCount.textContent =
            `${models.length} model${models.length === 1 ? '' : 's'}`;

        if (!tables.length) {

            renderEmptyState();

            refreshButton.style.display =
                'none';

            renderTableSelector([]);

            return;
        }

        const currentNames =
            new Set(
                tables.map(
                    table =>
                        normalizeTableName(
                            table.name
                        )
                )
            );

        if (!tableSelectionInitialized) {

            selectedTables =
                new Set(currentNames);

            tableSelectionInitialized =
                true;

        } else {

            selectedTables =
                new Set(
                    [...selectedTables].filter(
                        name =>
                            currentNames.has(name)
                    )
                );

            tables.forEach(
                table => {

                    const name =
                        normalizeTableName(
                            table.name
                        );

                    if (
                        !selectedTables.has(name)
                    ) {
                        return;
                    }
                }
            );
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
                        ),
                    originalName:
                        table.name
                });
            }
        );

        renderTableSelector(tables);

        applyViewMode();

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
            document.createElement(
                'div'
            );

        element.className =
            'erd-table';

        const columns =
            (table.columns ?? [])
                .map(
                    column => {

                        const badges = [];

                        if (column.primary) {

                            badges.push({
                                text: 'PK',
                                className:
                                    'primary'
                            });

                        }

                        if (column.unique) {

                            badges.push({
                                text: 'UQ',
                                className:
                                    'unique'
                            });

                        }

                        if (column.nullable) {

                            badges.push({
                                text: 'NULL',
                                className:
                                    ''
                            });

                        }

                        return `

                            <div class="erd-column">

                                <div class="erd-column-name">

                                    ${escapeHtml(
                                        column.name ??
                                        ''
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
                                                        .join('')}

                                                </span>
                                            `
                                            : ''
                                    }

                                </div>

                                <div class="erd-column-type">
                                    ${escapeHtml(
                                        column.type ??
                                        ''
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
                            table.operation ??
                            'table'
                        )}
                    </div>

                </div>

                <div class="erd-table-meta">
                    ${escapeHtml(
                        table.migration ??
                        ''
                    )}
                </div>

            </div>

            <div class="erd-table-details">

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
                    ${(table.columns ?? []).length}
                    column${(table.columns ?? []).length === 1 ? '' : 's'}
                </div>

            </div>

        `;

        makeDraggable(
            element
        );

        workspace.appendChild(
            element
        );

        return element;
    }

    function layoutTables() {

        const gapX =
            80;

        const gapY =
            70;

        const startX =
            100;

        const startY =
            100;

        const columns =
            4;

        const columnWidth =
            380;

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

                columnHeights[column] +=
                    element.offsetHeight +
                    gapY;
            }
        );
    }

    function applyViewMode() {

        viewModeButtons.forEach(
            button => {

                const active =
                    button.dataset.viewMode ===
                    viewMode;

                button.classList.toggle(
                    'active',
                    active
                );
            }
        );

        tableElements.forEach(
            item => {

                item.element.classList.toggle(
                    'erd-simple-mode',
                    viewMode === 'simple'
                );
            }
        );

        layoutTables();

        drawRelations();
    }

    function renderTableSelector(
        tables
    ) {

        tableSelectorList.innerHTML = '';

        if (!tables.length) {

            tableSelectorList.innerHTML = `
                <div class="erd-table-selector-empty">
                    No tables available
                </div>
            `;

            updateTableSelectorCount();

            return;
        }

        tables.forEach(
            table => {

                const name =
                    normalizeTableName(
                        table.name
                    );

                const item =
                    document.createElement(
                        'label'
                    );

                item.className =
                    'erd-table-item';

                item.dataset.tableName =
                    name;

                item.innerHTML = `

                    <input
                        type="checkbox"
                        class="erd-table-checkbox"
                        data-table-name="${escapeHtml(name)}"
                        ${selectedTables.has(name) ? 'checked' : ''}
                    >

                    <span class="erd-table-item-name">
                        ${escapeHtml(table.name)}
                    </span>

                `;

                const checkbox =
                    item.querySelector(
                        '.erd-table-checkbox'
                    );

                checkbox.addEventListener(
                    'change',
                    event => {

                        event.stopPropagation();

                        if (
                            event.target.checked
                        ) {

                            selectedTables.add(
                                name
                            );

                        } else {

                            selectedTables.delete(
                                name
                            );

                        }

                        applyTableSelection();
                    }
                );

                item.addEventListener(
                    'mousedown',
                    event => {
                        event.stopPropagation();
                    }
                );

                item.addEventListener(
                    'wheel',
                    event => {
                        event.stopPropagation();
                    },
                    {
                        passive: true
                    }
                );

                tableSelectorList.appendChild(
                    item
                );
            }
        );

        updateTableSelectorCount();
        applyTableSelectorSearch();
    }

    function applyTableSelection() {

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

        updateTableSelectorCount();

        drawRelations();
    }

    function updateTableSelectorCount() {

        const total =
            tableElements.length;

        let selected =
            0;

        tableElements.forEach(
            item => {

                if (
                    selectedTables.has(
                        item.name
                    )
                ) {
                    selected++;
                }

            }
        );

        tableSelectorCount.textContent =
            `${selected} / ${total}`;
    }

    function applyTableSelectorSearch() {

        const search =
            tableSelectorSearch.value
                .trim()
                .toLowerCase();

        tableSelectorList
            .querySelectorAll(
                '.erd-table-item'
            )
            .forEach(
                item => {

                    const name =
                        item.dataset.tableName ??
                        '';

                    item.classList.toggle(
                        'hidden-by-search',
                        Boolean(
                            search &&
                            !name.includes(search)
                        )
                    );
                }
            );
    }

    function selectAll() {

        tableElements.forEach(
            item => {

                selectedTables.add(
                    item.name
                );
            }
        );

        tableSelectorList
            .querySelectorAll(
                '.erd-table-checkbox'
            )
            .forEach(
                checkbox => {
                    checkbox.checked =
                        true;
                }
            );

        applyTableSelection();
    }

    function clearAll() {

        selectedTables.clear();

        tableSelectorList
            .querySelectorAll(
                '.erd-table-checkbox'
            )
            .forEach(
                checkbox => {
                    checkbox.checked =
                        false;
                }
            );

        applyTableSelection();
    }

    function clearRelations() {

        relationLines.innerHTML =
            '';

        relationCount.textContent =
            '0 relations';
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

        const visibleItems =
            new Map();

        tableElements.forEach(
            item => {

                if (
                    item.element.classList.contains(
                        'hidden'
                    )
                ) {
                    return;
                }

                visibleItems.set(
                    item.name,
                    item.element
                );
            }
        );

        relations.forEach(
            (relation, index) => {

                const fromName =
                    normalizeTableName(
                        relation.from_table ??
                        relation.from ??
                        relation.table
                    );

                const toName =
                    normalizeTableName(
                        relation.to_table ??
                        relation.to ??
                        relation.referenced_table
                    );

                const fromElement =
                    visibleItems.get(
                        fromName
                    );

                const toElement =
                    visibleItems.get(
                        toName
                    );

                if (
                    !fromElement ||
                    !toElement
                ) {
                    return;
                }

                const path =
                    createRelationPath(
                        fromElement,
                        toElement,
                        index
                    );

                if (!path) {
                    return;
                }

                relationLines.appendChild(
                    path
                );

                createFlowParticles(
                    path,
                    index
                );
            }
        );
    }

    function createRelationPath(
        fromElement,
        toElement,
        index
    ) {

        const fromRect =
            getElementRect(
                fromElement
            );

        const toRect =
            getElementRect(
                toElement
            );

        if (
            !fromRect ||
            !toRect
        ) {
            return null;
        }

        const fromCenterX =
            fromRect.left +
            fromRect.width / 2;

        const fromCenterY =
            fromRect.top +
            fromRect.height / 2;

        const toCenterX =
            toRect.left +
            toRect.width / 2;

        const toCenterY =
            toRect.top +
            toRect.height / 2;

        let startX;
        let startY;
        let endX;
        let endY;

        const deltaX =
            toCenterX -
            fromCenterX;

        const deltaY =
            toCenterY -
            fromCenterY;

        if (
            Math.abs(deltaX) >
            Math.abs(deltaY)
        ) {

            if (deltaX >= 0) {

                startX =
                    fromRect.left +
                    fromRect.width;

                startY =
                    fromCenterY;

                endX =
                    toRect.left;

                endY =
                    toCenterY;

            } else {

                startX =
                    fromRect.left;

                startY =
                    fromCenterY;

                endX =
                    toRect.left +
                    toRect.width;

                endY =
                    toCenterY;
            }

        } else {

            if (deltaY >= 0) {

                startX =
                    fromCenterX;

                startY =
                    fromRect.top +
                    fromRect.height;

                endX =
                    toCenterX;

                endY =
                    toRect.top;

            } else {

                startX =
                    fromCenterX;

                startY =
                    fromRect.top;

                endX =
                    toCenterX;

                endY =
                    toRect.top +
                    toRect.height;
            }
        }

        const distance =
            Math.sqrt(
                Math.pow(
                    endX - startX,
                    2
                ) +
                Math.pow(
                    endY - startY,
                    2
                )
            );

        const curve =
            Math.max(
                45,
                Math.min(
                    180,
                    distance * .35
                )
            );

        let c1x =
            startX;

        let c1y =
            startY;

        let c2x =
            endX;

        let c2y =
            endY;

        if (
            Math.abs(deltaX) >
            Math.abs(deltaY)
        ) {

            if (deltaX >= 0) {

                c1x += curve;
                c2x -= curve;

            } else {

                c1x -= curve;
                c2x += curve;
            }

        } else {

            if (deltaY >= 0) {

                c1y += curve;
                c2y -= curve;

            } else {

                c1y -= curve;
                c2y += curve;
            }
        }

        const path =
            document.createElementNS(
                SVG_NS,
                'path'
            );

        path.id =
            `erd-relation-${index}-${Date.now()}`;

        path.setAttribute(
            'class',
            'erd-relation-line'
        );

        path.setAttribute(
            'd',
            `
                M ${startX} ${startY}
                C ${c1x} ${c1y},
                  ${c2x} ${c2y},
                  ${endX} ${endY}
            `
        );

        return path;
    }

    function getElementRect(
        element
    ) {

        const left =
            parseFloat(
                element.style.left
            ) || 0;

        const top =
            parseFloat(
                element.style.top
            ) || 0;

        return {
            left,
            top,
            width:
                element.offsetWidth,
            height:
                element.offsetHeight
        };
    }

    function createFlowParticles(
        path,
        relationIndex
    ) {

        const particleCount =
            3;

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

            motion.appendChild(
                mpath
            );

            halo.appendChild(
                motion
            );

            relationLines.appendChild(
                halo
            );

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

        let dragging =
            false;

        let startX =
            0;

        let startY =
            0;

        let startLeft =
            0;

        let startTop =
            0;

        header.addEventListener(
            'mousedown',
            event => {

                event.stopPropagation();

                dragging =
                    true;

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

                dragging =
                    false;

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
                    !(
                        selected &&
                        matches
                    )
                );
            }
        );

        drawRelations();
    }

    function updateStage() {

        stage.style.transform =
            `translate(${panX}px, ${panY}px) scale(${zoom})`;

        zoomLevel.textContent =
            `${Math.round(zoom * 100)}%`;
    }

    function resetView() {

        zoom =
            1;

        const rect =
            canvas.getBoundingClientRect();

        panX =
            Math.max(
                30,
                (
                    rect.width -
                    5000
                ) / 2
            );

        panY =
            Math.max(
                30,
                (
                    rect.height -
                    5000
                ) / 2
            );

        updateStage();
    }

    function setZoom(
        nextZoom,
        centerX = canvas.clientWidth / 2,
        centerY = canvas.clientHeight / 2
    ) {

        const oldZoom =
            zoom;

        zoom =
            Math.min(
                2,
                Math.max(
                    .35,
                    nextZoom
                )
            );

        if (
            zoom === oldZoom
        ) {
            return;
        }

        const worldX =
            (
                centerX -
                panX
            ) / oldZoom;

        const worldY =
            (
                centerY -
                panY
            ) / oldZoom;

        panX =
            centerX -
            worldX * zoom;

        panY =
            centerY -
            worldY * zoom;

        updateStage();
    }

    function analyzeSchema() {

        setLoading(
            analyzeButton,
            true
        );

        fetch(
            '{{ url(config("erd.route.prefix") . "/refresh") }}',
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
                        'application/json',

                    'Content-Type':
                        'application/json'
                },

                body:
                    JSON.stringify({})
            }
        )
            .then(
                response => {

                    if (!response.ok) {
                        throw new Error(
                            'Schema analysis failed.'
                        );
                    }

                    return response.json();
                }
            )
            .then(
                response => {

                    if (
                        !response.success
                    ) {
                        throw new Error(
                            response.message ??
                            'Schema analysis failed.'
                        );
                    }

                    showToast(
                        response.message ??
                        'Schema analyzed successfully.'
                    );

                    return fetch(
                        window.location.href,
                        {
                            headers: {
                                'X-Requested-With':
                                    'XMLHttpRequest'
                            }
                        }
                    );
                }
            )
            .then(
                response => {

                    if (!response.ok) {
                        throw new Error(
                            'Failed to reload schema.'
                        );
                    }

                    return response.text();
                }
            )
            .then(
                html => {

                    const parser =
                        new DOMParser();

                    const document =
                        parser.parseFromString(
                            html,
                            'text/html'
                        );

                    const script =
                        [...document.scripts]
                            .find(
                                script =>
                                    script.textContent.includes(
                                        'window.ERD ='
                                    )
                            );

                    if (!script) {
                        throw new Error(
                            'Unable to reload ERD data.'
                        );
                    }

                    const match =
                        script.textContent.match(
                            /window\.ERD\s*=\s*(\{[\s\S]*?\});/
                        );

                    if (!match) {
                        throw new Error(
                            'Unable to parse ERD data.'
                        );
                    }

                    window.ERD =
                        Function(
                            `"use strict"; return (${match[1]});`
                        )();

                    renderTables();
                }
            )
            .catch(
                error => {

                    showToast(
                        error.message ??
                        'Something went wrong.'
                    );
                }
            )
            .finally(
                () => {

                    setLoading(
                        analyzeButton,
                        false
                    );
                }
            );
    }

    function reloadRegistry() {

        fetch(
            '{{ url(config("erd.route.prefix") . "/refresh") }}',
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
                        'application/json',

                    'Content-Type':
                        'application/json'
                },

                body:
                    JSON.stringify({})
            }
        )
            .then(
                response =>
                    response.json()
            )
            .then(
                response => {

                    if (
                        !response.success
                    ) {
                        throw new Error(
                            response.message ??
                            'Refresh failed.'
                        );
                    }

                    window.location.reload();
                }
            )
            .catch(
                error => {

                    showToast(
                        error.message ??
                        'Refresh failed.'
                    );
                }
            );
    }

    function setLoading(
        button,
        loading
    ) {

        if (!button) {
            return;
        }

        button.disabled =
            loading;

        if (loading) {

            button.dataset.originalHtml =
                button.innerHTML;

            button.innerHTML =
                `
                    <span class="erd-button-icon">
                        ◌
                    </span>

                    <span class="erd-button-text">
                        Analyzing...
                    </span>
                `;

        } else {

            button.innerHTML =
                button.dataset.originalHtml ??
                button.innerHTML;
        }
    }

    function showToast(
        message
    ) {

        toast.textContent =
            message;

        toast.classList.add(
            'show'
        );

        clearTimeout(
            showToast.timer
        );

        showToast.timer =
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

    viewModeButtons.forEach(
        button => {

            button.addEventListener(
                'click',
                event => {

                    event.stopPropagation();

                    viewMode =
                        button.dataset.viewMode;

                    applyViewMode();
                }
            );
        }
    );

    searchInput.addEventListener(
        'input',
        applySearch
    );

    tableSelectorSearch.addEventListener(
        'input',
        applyTableSelectorSearch
    );

    selectAllTables.addEventListener(
        'click',
        event => {

            event.stopPropagation();

            selectAll();
        }
    );

    clearAllTables.addEventListener(
        'click',
        event => {

            event.stopPropagation();

            clearAll();
        }
    );

    analyzeButton.addEventListener(
        'click',
        event => {

            event.stopPropagation();

            analyzeSchema();
        }
    );

    refreshButton.addEventListener(
        'click',
        event => {

            event.stopPropagation();

            reloadRegistry();
        }
    );

    viewSelector.addEventListener(
        'mousedown',
        event => {
            event.stopPropagation();
        }
    );

    viewSelector.addEventListener(
        'wheel',
        event => {
            event.stopPropagation();
        },
        {
            passive: true
        }
    );

    tableSelector.addEventListener(
        'mousedown',
        event => {
            event.stopPropagation();
        }
    );

    tableSelector.addEventListener(
        'wheel',
        event => {
            event.stopPropagation();
        },
        {
            passive: true
        }
    );

    zoomIn.addEventListener(
        'click',
        event => {

            event.stopPropagation();

            setZoom(
                zoom + .1
            );
        }
    );

    zoomOut.addEventListener(
        'click',
        event => {

            event.stopPropagation();

            setZoom(
                zoom - .1
            );
        }
    );

    resetViewButton.addEventListener(
        'click',
        event => {

            event.stopPropagation();

            resetView();
        }
    );

    canvas.addEventListener(
        'mousedown',
        event => {

            if (
                event.button !== 0
            ) {
                return;
            }

            if (
                event.target.closest(
                    '.erd-table-selector'
                ) ||
                event.target.closest(
                    '.erd-view-selector'
                ) ||
                event.target.closest(
                    '.erd-controls'
                ) ||
                event.target.closest(
                    '.erd-button'
                ) ||
                event.target.closest(
                    'input'
                )
            ) {
                return;
            }

            isPanning =
                true;

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

            updateStage();
        }
    );

    document.addEventListener(
        'mouseup',
        () => {

            if (!isPanning) {
                return;
            }

            isPanning =
                false;

            canvas.classList.remove(
                'is-panning'
            );
        }
    );

    canvas.addEventListener(
        'wheel',
        event => {

            if (
                event.target.closest(
                    '.erd-table-selector'
                ) ||
                event.target.closest(
                    '.erd-view-selector'
                )
            ) {
                return;
            }

            event.preventDefault();

            const rect =
                canvas.getBoundingClientRect();

            const centerX =
                event.clientX -
                rect.left;

            const centerY =
                event.clientY -
                rect.top;

            const direction =
                event.deltaY < 0
                    ? 1
                    : -1;

            setZoom(
                zoom +
                direction * .08,
                centerX,
                centerY
            );
        },
        {
            passive: false
        }
    );

    window.addEventListener(
        'resize',
        () => {

            updateStage();
            drawRelations();
        }
    );

    renderTables();

    updateStage();
</script>

</body>
</html>