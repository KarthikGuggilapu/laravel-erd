<header class="erd-navbar">
    <div class="erd-brand">
        <div class="erd-logo">E</div>

        <div class="erd-brand-copy">
            <div class="erd-brand-title">
                {{ config('app.name') }} ERD
            </div>

            <div class="erd-brand-subtitle">
                Database schema visualizer
            </div>
        </div>
    </div>

    <div class="erd-nav-center">
        <button type="button" class="erd-nav-filter active" data-table-filter="all">
            All Tables
        </button>

        <button type="button" class="erd-nav-filter" data-table-filter="relationship">
            Relationship Tables
        </button>

        <button type="button" class="erd-nav-filter" data-table-filter="isolated">
            Non-Relationship Tables
        </button>
    </div>

    <div class="erd-toolbar">
        <button type="button" class="erd-theme-toggle" id="themeToggle" title="Toggle theme">
            <span class="erd-theme-icon" id="themeIcon">☾</span>
            <span id="themeLabel">Dark</span>
        </button>

        <button type="button" class="erd-button" id="refreshButton">
            <span class="erd-button-icon">↻</span>
            <span class="erd-button-text">Refresh</span>
        </button>

        <div class="erd-search-wrap">
            <span class="erd-search-icon">⌕</span>
            <input
                type="search"
                id="tableSearch"
                class="erd-search"
                placeholder="Search tables..."
                autocomplete="off"
            >
        </div>

        <button type="button" class="erd-button primary" id="analyzeButton">
            <span class="erd-button-icon">◈</span>
            <span class="erd-button-text">Analyze Schema</span>
        </button>
    </div>
</header>
