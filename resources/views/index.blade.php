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
            background: linear-gradient(
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
            display: flex;
            flex-direction: column;
            gap: 2px;
            min-width: 0;
        }

        .erd-brand-title {
            color: #edf3fc;
            font-size: 15px;
            font-weight: 750;
            line-height: 1.2;
            white-space: nowrap;
        }

        .erd-brand-subtitle {
            color: #687996;
            font-size: 10px;
            line-height: 1.2;
            white-space: nowrap;
        }

        .erd-navbar-filters {
            display: flex;
            align-items: center;
            gap: 4px;
            margin-left: 10px;
            padding: 3px;
            border: 1px solid rgba(151,176,220,.08);
            border-radius: 9px;
            background: rgba(255,255,255,.018);
        }

        .erd-navbar-filter {
            position: relative;
            width: 34px;
            height: 32px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: 1px solid transparent;
            border-radius: 7px;
            background: transparent;
            color: #687995;
            cursor: pointer;
            transition:
                background .16s ease,
                color .16s ease,
                border-color .16s ease,
                transform .16s ease;
        }

        .erd-navbar-filter i {
            font-size: 13px;
        }

        .erd-navbar-filter:hover {
            background: rgba(255,255,255,.045);
            color: #dce6f5;
        }

        .erd-navbar-filter:active {
            transform: translateY(1px);
        }

        .erd-navbar-filter.active {
            background: rgba(77,131,232,.13);
            border-color: rgba(77,131,232,.26);
            color: #9dbdff;
        }

        .erd-navbar-filter.active i {
            color: #78a5f5;
        }

        .erd-navbar-filter[data-tooltip]::after {
            content: attr(data-tooltip);
            position: absolute;
            left: 50%;
            top: calc(100% + 9px);
            transform: translateX(-50%) translateY(-3px);
            padding: 6px 8px;
            border: 1px solid rgba(151,176,220,.12);
            border-radius: 6px;
            background: #17233a;
            color: #dce6f5;
            box-shadow: 0 8px 25px rgba(0,0,0,.35);
            font-size: 9px;
            font-weight: 600;
            line-height: 1;
            white-space: nowrap;
            opacity: 0;
            visibility: hidden;
            pointer-events: none;
            transition:
                opacity .15s ease,
                transform .15s ease;
            z-index: 500;
        }

        .erd-navbar-filter[data-tooltip]::before {
            content: "";
            position: absolute;
            left: 50%;
            top: calc(100% + 5px);
            width: 7px;
            height: 7px;
            border-left: 1px solid rgba(151,176,220,.12);
            border-top: 1px solid rgba(151,176,220,.12);
            background: #17233a;
            transform: translateX(-50%) rotate(45deg);
            opacity: 0;
            visibility: hidden;
            transition: opacity .15s ease;
            z-index: 501;
        }

        .erd-navbar-filter:hover[data-tooltip]::after,
        .erd-navbar-filter:hover[data-tooltip]::before {
            opacity: 1;
            visibility: visible;
        }

        .erd-navbar-filter:hover[data-tooltip]::after {
            transform: translateX(-50%) translateY(0);
        }

        .erd-toolbar {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 9px;
            min-width: 0;
        }

        .erd-search-wrap {
            position: relative;
        }

        .erd-search-icon {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #647593;
            font-size: 13px;
            pointer-events: none;
        }

        .erd-search {
            width: 240px;
            height: 38px;
            padding: 0 13px 0 34px;
            outline: none;
            border: 1px solid rgba(151,176,220,.11);
            border-radius: 8px;
            background: #0a1222;
            color: #edf3fc;
            font-size: 11px;
            transition:
                border-color .16s ease,
                background .16s ease;
        }

        .erd-search::placeholder {
            color: #596a86;
        }

        .erd-search:focus {
            border-color: rgba(116,153,216,.42);
            background: #0c1527;
        }

        .erd-button {
            height: 38px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 7px;
            padding: 0 14px;
            border: 1px solid rgba(151,176,220,.11);
            border-radius: 8px;
            background: #182640;
            color: #edf3fc;
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
            z-index: 1;
            pointer-events: none;
            overflow: visible;
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
            background: #182640;
            border-bottom: 1px solid rgba(151,176,220,.08);
            cursor: move;
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
            color: #edf3fc;
            font-size: 14px;
            font-weight: 750;
        }

        .erd-table-operation {
            flex: 0 0 auto;
            padding: 3px 6px;
            border-radius: 4px;
            background: rgba(255,255,255,.055);
            color: #7f91ae;
            font-size: 8px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .3px;
        }

        .erd-table-meta {
            margin-top: 4px;
            color: #667895;
            font-size: 9px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .erd-columns {
            padding: 4px 0;
        }

        .erd-column {
            display: grid;
            grid-template-columns: minmax(0,1fr) auto;
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

        .erd-badges {
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

        .erd-badge.primary {
            background: rgba(82,130,226,.15);
            color: #8db5ff;
        }

        .erd-badge.unique {
            background: rgba(85,201,138,.12);
            color: #7ee0a9;
        }

        .erd-table-footer {
            padding: 7px 13px;
            border-top: 1px solid rgba(255,255,255,.06);
            color: #596982;
            font-size: 10px;
        }

        .erd-table.simple {
            width: 280px;
        }

        .erd-table.simple .erd-table-header {
            min-height: 54px;
            border-bottom: 0;
        }

        .erd-table.simple .erd-table-operation,
        .erd-table.simple .erd-table-meta,
        .erd-table.simple .erd-columns,
        .erd-table.simple .erd-table-footer {
            display: none;
        }

        .erd-view-selector {
            position: fixed;
            left: 18px;
            top: 88px;
            width: 145px;
            padding: 11px;
            border: 1px solid rgba(151,176,220,.12);
            border-radius: 10px;
            background: rgba(14,22,39,.94);
            box-shadow: 0 14px 35px rgba(0,0,0,.25);
            backdrop-filter: blur(10px);
            z-index: 30;
        }

        .erd-selector-title {
            margin-bottom: 8px;
            color: #8192ad;
            font-size: 9px;
            font-weight: 750;
            text-transform: uppercase;
            letter-spacing: .6px;
        }

        .erd-view-options {
            display: flex;
            gap: 4px;
        }

        .erd-view-option {
            flex: 1;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            border: 1px solid transparent;
            border-radius: 6px;
            background: transparent;
            color: #6e7f9b;
            cursor: pointer;
            font-size: 9px;
            font-weight: 650;
        }

        .erd-view-option:hover {
            background: rgba(255,255,255,.035);
            color: #dce6f5;
        }

        .erd-view-option.active {
            background: rgba(77,131,232,.12);
            border-color: rgba(77,131,232,.22);
            color: #a4c2ff;
        }

        .erd-view-radio {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            border: 1px solid #5e7190;
        }

        .erd-view-option.active .erd-view-radio {
            border-color: #78a5f5;
            background: #78a5f5;
            box-shadow: 0 0 0 2px rgba(120,165,245,.12);
        }

        .erd-table-selector {
            position: fixed;
            top: 88px;
            right: 18px;
            width: 220px;
            max-height: calc(100vh - 155px);
            display: flex;
            flex-direction: column;
            border: 1px solid rgba(151,176,220,.12);
            border-radius: 10px;
            background: rgba(14,22,39,.95);
            box-shadow: 0 14px 35px rgba(0,0,0,.28);
            backdrop-filter: blur(10px);
            z-index: 30;
        }

        .erd-table-selector-header {
            padding: 11px;
            border-bottom: 1px solid rgba(151,176,220,.08);
        }

        .erd-table-selector-title-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 8px;
        }

        .erd-table-selector-title {
            color: #dce6f5;
            font-size: 10px;
            font-weight: 750;
        }

        .erd-table-selector-count {
            color: #647693;
            font-size: 9px;
        }

        .erd-selector-search {
            width: 100%;
            height: 30px;
            padding: 0 9px;
            outline: none;
            border: 1px solid rgba(151,176,220,.1);
            border-radius: 6px;
            background: #0a1222;
            color: #eaf1fb;
            font-size: 9px;
        }

        .erd-selector-search::placeholder {
            color: #586984;
        }

        .erd-selector-search:focus {
            border-color: rgba(116,153,216,.35);
        }

        .erd-selector-actions {
            display: flex;
            gap: 5px;
            margin-top: 7px;
        }

        .erd-selector-action {
            flex: 1;
            height: 26px;
            border: 1px solid rgba(151,176,220,.09);
            border-radius: 5px;
            background: rgba(255,255,255,.025);
            color: #7f91ad;
            cursor: pointer;
            font-size: 8px;
            font-weight: 650;
        }

        .erd-selector-action:hover {
            background: rgba(255,255,255,.055);
            color: #dce6f5;
        }

        .erd-table-list {
            overflow-y: auto;
            padding: 5px;
            scrollbar-width: thin;
            scrollbar-color: #273754 transparent;
        }

        .erd-table-list::-webkit-scrollbar {
            width: 5px;
        }

        .erd-table-list::-webkit-scrollbar-track {
            background: transparent;
        }

        .erd-table-list::-webkit-scrollbar-thumb {
            background: #273754;
            border-radius: 10px;
        }

        .erd-table-item {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 7px 7px;
            border-radius: 6px;
            color: #9baac0;
            cursor: pointer;
            font-size: 9px;
        }

        .erd-table-item:hover {
            background: rgba(255,255,255,.035);
            color: #e3ebf8;
        }

        .erd-table-item input {
            width: 12px;
            height: 12px;
            margin: 0;
            accent-color: #5f8ee5;
        }

        .erd-table-item-name {
            min-width: 0;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .erd-table-item.filtered {
            display: none;
        }

        .erd-bottom {
            position: fixed;
            left: 0;
            right: 0;
            bottom: 32px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 18px;
            pointer-events: none;
            z-index: 20;
        }

        .erd-info {
            display: flex;
            align-items: center;
            gap: 7px;
            padding: 7px 10px;
            border: 1px solid rgba(151,176,220,.09);
            border-radius: 7px;
            background: rgba(11,18,33,.88);
            color: #62728c;
            font-size: 9px;
            backdrop-filter: blur(8px);
        }

        .erd-info-dot {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: #55c98a;
            box-shadow: 0 0 8px rgba(85,201,138,.45);
        }

        .erd-controls {
            display: flex;
            align-items: center;
            gap: 3px;
            padding: 4px;
            border: 1px solid rgba(151,176,220,.1);
            border-radius: 8px;
            background: rgba(11,18,33,.9);
            pointer-events: auto;
            backdrop-filter: blur(8px);
        }

        .erd-control {
            width: 27px;
            height: 27px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 5px;
            background: transparent;
            color: #71829d;
            cursor: pointer;
            font-size: 11px;
        }

        .erd-control:hover {
            background: rgba(255,255,255,.05);
            color: #dce6f5;
        }

        .erd-zoom-value {
            min-width: 40px;
            text-align: center;
            color: #687996;
            font-size: 8px;
            font-weight: 650;
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
        }

        .erd-empty-icon {
            width: 48px;
            height: 48px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 13px;
            border: 1px solid rgba(116,153,216,.16);
            border-radius: 13px;
            background: #111d32;
            color: #8fb4ff;
            font-size: 19px;
            font-weight: 800;
        }

        .erd-empty-title {
            margin-bottom: 6px;
            color: #dce6f5;
            font-size: 15px;
            font-weight: 700;
        }

        .erd-empty-text {
            max-width: 360px;
            margin-bottom: 15px;
            color: #61728d;
            font-size: 10px;
            line-height: 1.6;
        }

        .erd-toast {
            position: fixed;
            right: 20px;
            bottom: 85px;
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

        .erd-footer {
            height: 32px;
            min-height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-top: 1px solid rgba(255,255,255,.06);
            background: #0d1425;
            color: #596982;
            font-size: 9px;
            z-index: 100;
        }

        .erd-footer strong {
            margin-left: 4px;
            color: #8192ad;
            font-weight: 600;
        }

        .erd-relation-line {
            fill: none;
            stroke: #5f7fae;
            stroke-width: 1.5;
            opacity: .55;
            marker-end: url(#erd-arrow);
            transition:
                opacity .15s ease,
                stroke-width .15s ease;
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

        @media (max-width: 1100px) {
            .erd-brand-subtitle {
                display: none;
            }

            .erd-table-selector {
                width: 190px;
            }

            .erd-search {
                width: 180px;
            }
        }

        @media (max-width: 800px) {
            .erd-brand {
                min-width: auto;
            }

            .erd-brand-copy {
                display: none;
            }

            .erd-navbar-filters {
                margin-left: 0;
            }

            .erd-table-selector {
                width: 180px;
            }

            .erd-view-selector {
                left: 10px;
            }

            .erd-table-selector {
                right: 10px;
            }

            .erd-search {
                width: 150px;
            }
        }

        @media (max-width: 600px) {
            .erd-header {
                padding: 0 10px;
                gap: 8px;
            }

            .erd-logo {
                width: 34px;
                height: 34px;
                flex-basis: 34px;
            }

            .erd-navbar-filter {
                width: 31px;
                height: 30px;
            }

            .erd-search {
                width: 120px;
            }

            .erd-button {
                padding: 0 10px;
            }

            .erd-button-text {
                display: none;
            }

            .erd-table-selector {
                width: 165px;
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

            <div class="erd-navbar-filters">

                <button
                    type="button"
                    class="erd-navbar-filter active"
                    data-table-filter="relational"
                    data-tooltip="Relational tables"
                    aria-label="Show relational tables"
                >
                    <i class="fa-solid fa-code-branch"></i>
                </button>

                <button
                    type="button"
                    class="erd-navbar-filter"
                    data-table-filter="non-relational"
                    data-tooltip="Non-relational tables"
                    aria-label="Show non-relational tables"
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
                <span class="erd-button-icon">
                    <i class="fa-solid fa-wand-magic-sparkles"></i>
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
                    <i class="fa-solid fa-arrows-rotate"></i>
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
                    <i class="fa-solid fa-minus"></i>
                </button>

                <span
                    class="erd-zoom-value"
                    id="zoomValue"
                >
                    100%
                </span>

                <button
                    type="button"
                    class="erd-control"
                    id="zoomIn"
                    title="Zoom in"
                >
                    <i class="fa-solid fa-plus"></i>
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

    <div
        class="erd-toast"
        id="erdToast"
    ></div>

    <footer class="erd-footer">
        Developed by
        <strong>
            Karthik Guggilapu
        </strong>
    </footer>

</div>

<script>
    window.ERD = {!! json_encode([
        'metadata' => $metadata ?? [],
        'migrations' => $migrations ?? [],
        'models' => $models ?? [],
        'relations' => $relations ?? [],
        'history' => $history ?? [],
        'layout' => $layout ?? [],
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

    const tableSelector =
        document.getElementById('erdTableSelector');

    const tableList =
        document.getElementById('erdTableList');

    const tableSelectorSearch =
        document.getElementById('tableSelectorSearch');

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

    const navbarFilterButtons =
        document.querySelectorAll(
            '[data-table-filter]'
        );

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

    const SVG_NS =
        'http://www.w3.org/2000/svg';


    function normalizeTableName(
        value
    ) {
        return String(
            value ?? ''
        )
            .trim()
            .toLowerCase();
    }


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
                            tables.get(key);

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
                                            normalizeTableName(
                                                column.name
                                            )
                                    )
                                );

                            table.columns.forEach(
                                column => {

                                    const columnName =
                                        normalizeTableName(
                                            column.name
                                        );

                                    if (
                                        columnName &&
                                        !existingNames.has(
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


    function getRelationEndpoint(
        relation,
        side
    ) {
        if (side === 'from') {

            return normalizeTableName(
                relation.from_table ??
                relation.from ??
                relation.table
            );
        }

        return normalizeTableName(
            relation.to_table ??
            relation.to ??
            relation.referenced_table
        );
    }


    function getRelationshipTables() {
        const relationshipTables =
            new Set();

        getRelations().forEach(
            relation => {

                const from =
                    getRelationEndpoint(
                        relation,
                        'from'
                    );

                const to =
                    getRelationEndpoint(
                        relation,
                        'to'
                    );

                if (from) {
                    relationshipTables.add(
                        from
                    );
                }

                if (to) {
                    relationshipTables.add(
                        to
                    );
                }
            }
        );

        return relationshipTables;
    }


    function matchesTableFilter(
        tableName
    ) {
        const relationshipTables =
            getRelationshipTables();

        const normalized =
            normalizeTableName(
                tableName
            );

        if (
            tableFilter ===
            'relational'
        ) {
            return relationshipTables.has(
                normalized
            );
        }

        if (
            tableFilter ===
            'non-relational'
        ) {
            return !relationshipTables.has(
                normalized
            );
        }

        return true;
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

        relationCount.textContent =
            `${relations.length} relation${relations.length === 1 ? '' : 's'}`;

        if (!tables.length) {

            renderEmptyState();

            refreshButton.style.display =
                'none';

            renderTableSelector();

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

        selectedTables =
            new Set(
                [...selectedTables]
                    .filter(
                        name =>
                            currentNames.has(
                                name
                            )
                    )
            );

        if (
            selectedTables.size === 0
        ) {
            tables.forEach(
                table => {
                    selectedTables.add(
                        normalizeTableName(
                            table.name
                        )
                    );
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
                        )
                });
            }
        );

        renderTableSelector();

        layoutTables();

        applyViewMode(false);

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
                    <span class="erd-button-icon">
                        <i class="fa-solid fa-wand-magic-sparkles"></i>
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
                                className: ''
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
                                                        .join('')
                                                    }

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

        const columnCount =
            (table.columns ?? []).length;

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
                ${columnCount}
                column${columnCount === 1 ? '' : 's'}
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


    function applyViewMode(
        relayout = true
    ) {
        viewModeButtons.forEach(
            button => {

                button.classList.toggle(
                    'active',
                    button.dataset.viewMode ===
                    viewMode
                );
            }
        );

        tableElements.forEach(
            item => {

                item.element.classList.toggle(
                    'simple',
                    viewMode ===
                    'simple'
                );
            }
        );

        if (relayout) {
            layoutTables();
        }

        drawRelations();
    }


    function renderTableSelector() {
        const tables =
            getTables();

        tableList.innerHTML = '';

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

                item.dataset.name =
                    name;

                const checkbox =
                    document.createElement(
                        'input'
                    );

                checkbox.type =
                    'checkbox';

                checkbox.checked =
                    selectedTables.has(
                        name
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

                        updateTableSelectorCount();

                        applySearch();
                    }
                );

                const label =
                    document.createElement(
                        'span'
                    );

                label.className =
                    'erd-table-item-name';

                label.textContent =
                    table.name;

                item.appendChild(
                    checkbox
                );

                item.appendChild(
                    label
                );

                tableList.appendChild(
                    item
                );
            }
        );

        updateTableSelectorCount();

        applyTableSelectorSearch();
    }


    function updateTableSelectorCount() {
        const total =
            getTables().length;

        const selected =
            selectedTables.size;

        tableSelectorCount.textContent =
            `${selected} / ${total}`;
    }


    function applyTableSelectorSearch() {
        const search =
            tableSelectorSearch.value
                .trim()
                .toLowerCase();

        tableList
            .querySelectorAll(
                '.erd-table-item'
            )
            .forEach(
                item => {

                    item.classList.toggle(
                        'filtered',
                        !!search &&
                        !item.dataset.name.includes(
                            search
                        )
                    );
                }
            );
    }


    function selectAll() {
        getTables().forEach(
            table => {

                selectedTables.add(
                    normalizeTableName(
                        table.name
                    )
                );
            }
        );

        tableList
            .querySelectorAll(
                'input[type="checkbox"]'
            )
            .forEach(
                checkbox => {
                    checkbox.checked = true;
                }
            );

        updateTableSelectorCount();

        applySearch();
    }


    function clearAll() {
        selectedTables.clear();

        tableList
            .querySelectorAll(
                'input[type="checkbox"]'
            )
            .forEach(
                checkbox => {
                    checkbox.checked = false;
                }
            );

        updateTableSelectorCount();

        applySearch();
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

                const matchesSearch =
                    !search ||
                    item.name.includes(
                        search
                    );

                const matchesFilter =
                    matchesTableFilter(
                        item.name
                    );

                item.element.classList.toggle(
                    'hidden',
                    !(
                        selected &&
                        matchesSearch &&
                        matchesFilter
                    )
                );
            }
        );

        drawRelations();
    }


    function clearRelations() {
        relationLines.innerHTML = '';
    }


    function getTableElement(
        tableName
    ) {
        const normalized =
            normalizeTableName(
                tableName
            );

        const item =
            tableElements.find(
                table =>
                    table.name ===
                    normalized
            );

        return item?.element ?? null;
    }


    function getElementRect(
        element
    ) {
        return {
            left:
                parseFloat(
                    element.style.left
                ) || 0,

            top:
                parseFloat(
                    element.style.top
                ) || 0,

            width:
                element.offsetWidth,

            height:
                element.offsetHeight
        };
    }


    function getConnectionPoint(
        fromElement,
        toElement
    ) {
        const from =
            getElementRect(
                fromElement
            );

        const to =
            getElementRect(
                toElement
            );

        const fromCenterX =
            from.left +
            from.width / 2;

        const fromCenterY =
            from.top +
            from.height / 2;

        const toCenterX =
            to.left +
            to.width / 2;

        const toCenterY =
            to.top +
            to.height / 2;

        const deltaX =
            toCenterX -
            fromCenterX;

        const deltaY =
            toCenterY -
            fromCenterY;

        if (
            Math.abs(deltaX) >=
            Math.abs(deltaY)
        ) {

            if (deltaX >= 0) {
                return {
                    x:
                        from.left +
                        from.width,

                    y:
                        fromCenterY,

                    side:
                        'right'
                };
            }

            return {
                x:
                    from.left,

                y:
                    fromCenterY,

                side:
                    'left'
            };
        }

        if (deltaY >= 0) {
            return {
                x:
                    fromCenterX,

                y:
                    from.top +
                    from.height,

                side:
                    'bottom'
            };
        }

        return {
            x:
                fromCenterX,

            y:
                from.top,

            side:
                'top'
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

        let c1x =
            from.x;

        let c1y =
            from.y;

        let c2x =
            to.x;

        let c2y =
            to.y;

        if (
            from.side ===
            'right'
        ) {
            c1x += curve;
        } else if (
            from.side ===
            'left'
        ) {
            c1x -= curve;
        } else if (
            from.side ===
            'bottom'
        ) {
            c1y += curve;
        } else {
            c1y -= curve;
        }

        if (
            to.side ===
            'right'
        ) {
            c2x += curve;
        } else if (
            to.side ===
            'left'
        ) {
            c2x -= curve;
        } else if (
            to.side ===
            'bottom'
        ) {
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


    function drawRelations() {
        clearRelations();

        const relations =
            getRelations();

        if (!relations.length) {
            return;
        }

        relations.forEach(
            (relation, relationIndex) => {

                const fromName =
                    getRelationEndpoint(
                        relation,
                        'from'
                    );

                const toName =
                    getRelationEndpoint(
                        relation,
                        'to'
                    );

                if (
                    !fromName ||
                    !toName
                ) {
                    return;
                }

                if (
                    !selectedTables.has(
                        fromName
                    ) ||
                    !selectedTables.has(
                        toName
                    )
                ) {
                    return;
                }

                const fromElement =
                    getTableElement(
                        fromName
                    );

                const toElement =
                    getTableElement(
                        toName
                    );

                if (
                    !fromElement ||
                    !toElement
                ) {
                    return;
                }

                if (
                    fromElement.classList.contains(
                        'hidden'
                    ) ||
                    toElement.classList.contains(
                        'hidden'
                    )
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
                    fromName
                );

                path.setAttribute(
                    'data-to',
                    toName
                );

                relationLines.appendChild(
                    path
                );

                createFlowParticles(
                    path,
                    relationIndex
                );
            }
        );
    }


    function createFlowParticles(
        path,
        index
    ) {
        const duration =
            2.8 +
            (
                index % 4
            ) * .35;

        const particle =
            document.createElementNS(
                SVG_NS,
                'circle'
            );

        particle.setAttribute(
            'r',
            '3'
        );

        particle.setAttribute(
            'class',
            'erd-flow-particle'
        );

        const animation =
            document.createElementNS(
                SVG_NS,
                'animateMotion'
            );

        animation.setAttribute(
            'dur',
            `${duration}s`
        );

        animation.setAttribute(
            'repeatCount',
            'indefinite'
        );

        animation.setAttribute(
            'rotate',
            'auto'
        );

        const mpath =
            document.createElementNS(
                SVG_NS,
                'mpath'
            );

        mpath.setAttribute(
            'href',
            `#${path.id}`
        );

        mpath.setAttributeNS(
            'http://www.w3.org/1999/xlink',
            'xlink:href',
            `#${path.id}`
        );

        animation.appendChild(
            mpath
        );

        particle.appendChild(
            animation
        );

        relationLines.appendChild(
            particle
        );

        const halo =
            document.createElementNS(
                SVG_NS,
                'circle'
            );

        halo.setAttribute(
            'r',
            '6'
        );

        halo.setAttribute(
            'class',
            'erd-flow-particle-halo'
        );

        const haloAnimation =
            document.createElementNS(
                SVG_NS,
                'animateMotion'
            );

        haloAnimation.setAttribute(
            'dur',
            `${duration}s`
        );

        haloAnimation.setAttribute(
            'repeatCount',
            'indefinite'
        );

        const haloPath =
            document.createElementNS(
                SVG_NS,
                'mpath'
            );

        haloPath.setAttribute(
            'href',
            `#${path.id}`
        );

        haloPath.setAttributeNS(
            'http://www.w3.org/1999/xlink',
            'xlink:href',
            `#${path.id}`
        );

        haloAnimation.appendChild(
            haloPath
        );

        halo.appendChild(
            haloAnimation
        );

        relationLines.appendChild(
            halo
        );
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
                    '.erd-view-selector'
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

            if (
                event.target.closest(
                    '.erd-table-selector'
                )
            ) {
                return;
            }

            if (
                event.target.closest(
                    '.erd-view-selector'
                )
            ) {
                return;
            }

            if (!event.ctrlKey) {
                return;
            }

            event.preventDefault();

            const direction =
                event.deltaY < 0
                    ? .1
                    : -.1;

            setZoom(
                zoom +
                direction
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
                zoom +
                .1
            );
        }
    );


    zoomOutButton.addEventListener(
        'click',
        () => {
            setZoom(
                zoom -
                .1
            );
        }
    );


    resetViewButton.addEventListener(
        'click',
        resetView
    );


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


    navbarFilterButtons.forEach(
        button => {

            button.addEventListener(
                'click',
                event => {

                    event.stopPropagation();

                    tableFilter =
                        button.dataset.tableFilter;

                    navbarFilterButtons.forEach(
                        item => {

                            item.classList.toggle(
                                'active',
                                item === button
                            );
                        }
                    );

                    applySearch();
                }
            );
        }
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


    [
        tableSelector,
        tableSelectorSearch,
        selectAllTables,
        clearAllTables
    ].forEach(
        element => {

            element.addEventListener(
                'mousedown',
                event => {
                    event.stopPropagation();
                }
            );

            element.addEventListener(
                'wheel',
                event => {
                    event.stopPropagation();
                },
                {
                    passive: true
                }
            );
        }
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
                `Schema analyzed: ${data.migrations} migrations, ${data.models} models.`
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
        try {

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
                    <span class="erd-button-icon">
                        <i class="fa-solid fa-spinner fa-spin"></i>
                    </span>

                    <span class="erd-button-text">
                        Analyzing...
                    </span>
                `
                : `
                    <span class="erd-button-icon">
                        <i class="fa-solid fa-wand-magic-sparkles"></i>
                    </span>

                    <span class="erd-button-text">
                        Analyze Schema
                    </span>
                `;

        refreshButton.innerHTML =
            loading
                ? `
                    <span class="erd-button-icon">
                        <i class="fa-solid fa-spinner fa-spin"></i>
                    </span>

                    <span class="erd-button-text">
                        Working...
                    </span>
                `
                : `
                    <span class="erd-button-icon">
                        <i class="fa-solid fa-arrows-rotate"></i>
                    </span>

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