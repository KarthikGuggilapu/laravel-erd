export function createRelationRenderer({ svg, layer, getTableElements, getVisibleTables, getViewMode, getZoom }) {
    let visibleRelationCount = 0;

    function draw() {
        layer.innerHTML = '';
        visibleRelationCount = 0;
        const visibleNames = new Set(getVisibleTables().map(table => table.name));
        const relations = getRelations().filter(r => visibleNames.has(r.from_table) && visibleNames.has(r.to_table));
        const offsets = groupOffsets(relations);

        relations.forEach((relation, index) => {
            const from = findTable(relation.from_table);
            const to = findTable(relation.to_table);
            if (!from || !to) return;

            const detailed = getViewMode() === 'detailed';
            const columnRouted = detailed && relation.from_column && relation.to_column
                && findColumn(from.element, relation.from_column) !== from.element
                && findColumn(to.element, relation.to_column) !== to.element;
            const source = columnRouted ? findColumn(from.element, relation.from_column) : from.element;
            const target = columnRouted ? findColumn(to.element, relation.to_column) : to.element;
            const start = connectionPoint(source, target);
            const end = connectionPoint(target, source);
            const pathId = `erd-path-${index}`;
            const path = svgElement('path');
            path.id = pathId;
            path.setAttribute('d', buildPath(start, end, offsets.get(relationKey(relation)) || 0));
            path.setAttribute('class', `erd-relation-line ${columnRouted ? 'column-routed' : 'table-routed'}`);
            path.setAttribute('marker-end', 'url(#erd-arrow)');
            path.dataset.from = relation.from_table;
            path.dataset.to = relation.to_table;
            path.dataset.fromColumn = relation.from_column || '';
            path.dataset.toColumn = relation.to_column || '';
            layer.appendChild(path);
            createParticles(pathId, index);
            visibleRelationCount++;
        });
    }

    function getRelations() {
        return (window.ERD.relations?.relations ?? []).map(r => ({
            from_table: r.from_table ?? r.fromTable ?? r.table ?? null,
            from_column: r.from_column ?? r.fromColumn ?? r.local_column ?? r.local ?? null,
            to_table: r.to_table ?? r.toTable ?? r.referenced_table ?? r.references_table ?? r.on ?? null,
            to_column: r.to_column ?? r.toColumn ?? r.referenced_column ?? r.references_column ?? 'id',
            type: r.type ?? 'belongsTo',
        })).filter(r => r.from_table && r.to_table);
    }

    function findTable(name) {
        return getTableElements().find(item => item.name === name) || null;
    }

    function findColumn(table, name) {
        const target = String(name).toLowerCase();
        return [...table.querySelectorAll('.erd-column')].find(column =>
            String(column.dataset.columnName || '').toLowerCase() === target
        ) || table;
    }

    function connectionPoint(source, target) {
        const sourceRect = source.getBoundingClientRect();
        const targetRect = target.getBoundingClientRect();
        const svgRect = svg.getBoundingClientRect();
        const zoom = getZoom() || 1;
        const sc = { x: sourceRect.left + sourceRect.width / 2, y: sourceRect.top + sourceRect.height / 2 };
        const tc = { x: targetRect.left + targetRect.width / 2, y: targetRect.top + targetRect.height / 2 };
        const dx = tc.x - sc.x;
        const dy = tc.y - sc.y;
        let x = sc.x;
        let y = sc.y;

        if (Math.abs(dx) >= Math.abs(dy)) {
            x += dx >= 0 ? sourceRect.width / 2 : -sourceRect.width / 2;
            y += clamp(dy * 0.16, -sourceRect.height / 2, sourceRect.height / 2);
        } else {
            y += dy >= 0 ? sourceRect.height / 2 : -sourceRect.height / 2;
            x += clamp(dx * 0.16, -sourceRect.width / 2, sourceRect.width / 2);
        }

        return { x: (x - svgRect.left) / zoom, y: (y - svgRect.top) / zoom };
    }

    function buildPath(start, end, offset) {
        const dx = end.x - start.x;
        const dy = end.y - start.y;
        const horizontal = Math.abs(dx) >= Math.abs(dy);
        const spread = offset * 36;
        if (horizontal) {
            const direction = dx >= 0 ? 1 : -1;
            const bend = Math.max(90, Math.abs(dx) * 0.34);
            return `M ${start.x} ${start.y} C ${start.x + direction * bend} ${start.y + spread}, ${end.x - direction * bend} ${end.y - spread}, ${end.x} ${end.y}`;
        }
        const direction = dy >= 0 ? 1 : -1;
        const bend = Math.max(90, Math.abs(dy) * 0.34);
        return `M ${start.x} ${start.y} C ${start.x + spread} ${start.y + direction * bend}, ${end.x - spread} ${end.y - direction * bend}, ${end.x} ${end.y}`;
    }

    function groupOffsets(relations) {
        const groups = new Map();
        relations.forEach(r => {
            const key = [r.from_table, r.to_table].sort().join('|');
            if (!groups.has(key)) groups.set(key, []);
            groups.get(key).push(r);
        });
        const offsets = new Map();
        groups.forEach(group => {
            const center = (group.length - 1) / 2;
            group.forEach((r, i) => offsets.set(relationKey(r), i - center));
        });
        return offsets;
    }

    function relationKey(r) {
        return [r.from_table, r.from_column || '', r.to_table, r.to_column || ''].join('|');
    }

    function createParticles(pathId, index) {
        for (let i = 0; i < 3; i++) {
            const circle = svgElement('circle');
            circle.setAttribute('r', i === 1 ? '3.2' : '2.2');
            circle.setAttribute('class', 'erd-flow-particle');
            const motion = svgElement('animateMotion');
            motion.setAttribute('dur', `${2.1 + (index % 4) * 0.28}s`);
            motion.setAttribute('repeatCount', 'indefinite');
            motion.setAttribute('begin', `${i * 0.7}s`);
            const mpath = svgElement('mpath');
            mpath.setAttributeNS('http://www.w3.org/1999/xlink', 'href', `#${pathId}`);
            motion.appendChild(mpath);
            circle.appendChild(motion);
            layer.appendChild(circle);
        }
    }

    function svgElement(tag) {
        return document.createElementNS('http://www.w3.org/2000/svg', tag);
    }

    function clamp(value, min, max) {
        return Math.max(min, Math.min(max, value));
    }

    return { draw, getVisibleRelationCount: () => visibleRelationCount };
}
