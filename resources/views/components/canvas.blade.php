<div class="erd-stage" id="erdStage">
    <svg
        class="erd-relations"
        id="erdRelations"
        width="5000"
        height="5000"
        viewBox="0 0 5000 5000"
        preserveAspectRatio="none"
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
                markerUnits="userSpaceOnUse"
            >
                <path d="M0,0 L8,4 L0,8 Z" fill="var(--relation)"></path>
            </marker>

            <filter id="erd-particle-glow" x="-100%" y="-100%" width="300%" height="300%">
                <feGaussianBlur stdDeviation="2.4" result="blur"></feGaussianBlur>
                <feMerge>
                    <feMergeNode in="blur"></feMergeNode>
                    <feMergeNode in="SourceGraphic"></feMergeNode>
                </feMerge>
            </filter>
        </defs>

        <g id="erdRelationLines"></g>
    </svg>

    <div class="erd-workspace" id="erdWorkspace"></div>

    <div class="erd-empty" id="erdEmptyState">
        <div class="erd-empty-icon">E</div>
        <div class="erd-empty-title">No schema analyzed yet</div>
        <div class="erd-empty-text">
            Analyze your Laravel migrations to generate the database schema visualization.
        </div>
        <button type="button" class="erd-button primary" id="emptyAnalyzeButton">
            <span>◈</span>
            Analyze Schema
        </button>
    </div>
</div>
