export function createLayoutEngine(getTables, getVisibleTables) {
    const NODE_W = 300;
    const GAP = 70;
    const MIN_X = 80;
    const MIN_Y = 90;

    function apply(tables, tableElements, relations) {
        const visible = getVisibleTables();
        if (!visible.length) return;

        const names = new Set(visible.map(t => t.name));
        const graph = new Map(visible.map(t => [t.name, new Set()]));
        const degree = new Map(visible.map(t => [t.name, 0]));

        relations.forEach(r => {
            if (!names.has(r.from_table) || !names.has(r.to_table) || r.from_table === r.to_table) return;
            graph.get(r.from_table).add(r.to_table);
            graph.get(r.to_table).add(r.from_table);
            degree.set(r.from_table, (degree.get(r.from_table) || 0) + 1);
            degree.set(r.to_table, (degree.get(r.to_table) || 0) + 1);
        });

        const positions = new Map();
        const components = connectedComponents(graph);
        components.sort((a, b) => scoreComponent(b, degree) - scoreComponent(a, degree));
        let componentY = MIN_Y;

        components.forEach(component => {
            const root = [...component].sort((a, b) => (degree.get(b) || 0) - (degree.get(a) || 0))[0];
            const levels = bfsLevels(root, component, graph);
            const levelKeys = [...levels.keys()].sort((a, b) => a - b);

            orderLevels(levels, graph, degree, 8);

            levelKeys.forEach(level => {
                const nodes = levels.get(level);
                const totalWidth = Math.max(0, (nodes.length - 1) * (NODE_W + GAP));
                const startX = Math.max(MIN_X, 760 - totalWidth / 2);

                nodes.forEach((name, index) => {
                    positions.set(name, {
                        x: startX + index * (NODE_W + GAP),
                        y: componentY + level * 245,
                    });
                });
            });
            componentY += Math.max(420, boundsHeight(positions, component) + 170);
        });

        const isolated = visible.filter(table => !(graph.get(table.name)?.size));
        isolated.forEach((table, index) => {
            positions.set(table.name, {
                x: 100 + (index % 4) * (NODE_W + GAP),
                y: componentY + Math.floor(index / 4) * 250,
            });
        });

        resolveCollisions(positions, visible, tableElements);
        applyPositions(positions, tableElements);
        requestAnimationFrame(() => window.erdRefreshRelations?.());
    }

    function connectedComponents(graph) {
        const seen = new Set();
        const result = [];
        graph.forEach((_, start) => {
            if (seen.has(start)) return;
            const queue = [start];
            const component = [];
            seen.add(start);
            while (queue.length) {
                const current = queue.shift();
                component.push(current);
                graph.get(current).forEach(next => {
                    if (seen.has(next)) return;
                    seen.add(next);
                    queue.push(next);
                });
            }
            result.push(component);
        });
        return result;
    }

    function bfsLevels(root, component, graph) {
        const levels = new Map([[0, [root]]]);
        const distances = new Map([[root, 0]]);
        const queue = [root];
        const allowed = new Set(component);
        while (queue.length) {
            const current = queue.shift();
            const nextDistance = distances.get(current) + 1;
            graph.get(current).forEach(next => {
                if (!allowed.has(next) || distances.has(next)) return;
                distances.set(next, nextDistance);
                if (!levels.has(nextDistance)) levels.set(nextDistance, []);
                levels.get(nextDistance).push(next);
                queue.push(next);
            });
        }
        return levels;
    }

    function orderLevels(levels, graph, degree, sweeps) {
        const keys = [...levels.keys()].sort((a, b) => a - b);
        const order = new Map();

        keys.forEach(level => {
            levels.get(level).sort((a, b) => (degree.get(b) || 0) - (degree.get(a) || 0));
            levels.get(level).forEach((name, index) => order.set(name, index));
        });

        for (let sweep = 0; sweep < sweeps; sweep++) {
            for (const level of keys.slice(1)) {
                const nodes = levels.get(level);
                nodes.sort((a, b) => barycenter(a, level, levels, graph, order) - barycenter(b, level, levels, graph, order));
                nodes.forEach((name, index) => order.set(name, index));
            }

            for (const level of keys.slice(0, -1).reverse()) {
                const nodes = levels.get(level);
                nodes.sort((a, b) => barycenter(a, level, levels, graph, order) - barycenter(b, level, levels, graph, order));
                nodes.forEach((name, index) => order.set(name, index));
            }
        }
    }

    function barycenter(name, level, levels, graph, order) {
        const neighbours = [...(graph.get(name) || [])].filter(next => {
            for (const [candidateLevel, nodes] of levels) {
                if (candidateLevel !== level && nodes.includes(next)) return true;
            }
            return false;
        });

        if (!neighbours.length) return order.get(name) || 0;
        return neighbours.reduce((sum, next) => sum + (order.get(next) ?? 0), 0) / neighbours.length;
    }

    function resolveCollisions(positions, visible, tableElements) {
        for (let pass = 0; pass < 12; pass++) {
            for (let i = 0; i < visible.length; i++) {
                for (let j = i + 1; j < visible.length; j++) {
                    const a = visible[i].name;
                    const b = visible[j].name;
                    const pa = positions.get(a);
                    const pb = positions.get(b);
                    if (!pa || !pb) continue;
                    const ea = tableElements.find(item => item.name === a)?.element;
                    const eb = tableElements.find(item => item.name === b)?.element;
                    const wa = ea?.offsetWidth || NODE_W;
                    const wb = eb?.offsetWidth || NODE_W;
                    const ha = ea?.offsetHeight || 160;
                    const hb = eb?.offsetHeight || 160;
                    const overlapX = (wa + wb) / 2 + 28 - Math.abs((pa.x + wa / 2) - (pb.x + wb / 2));
                    const overlapY = (ha + hb) / 2 + 28 - Math.abs((pa.y + ha / 2) - (pb.y + hb / 2));
                    if (overlapX <= 0 || overlapY <= 0) continue;

                    if (overlapX < overlapY) {
                        const push = overlapX / 2;
                        if (pa.x <= pb.x) { pa.x -= push; pb.x += push; } else { pa.x += push; pb.x -= push; }
                    } else {
                        const push = overlapY / 2;
                        if (pa.y <= pb.y) { pa.y -= push; pb.y += push; } else { pa.y += push; pb.y -= push; }
                    }
                }
            }
        }
        positions.forEach(position => {
            position.x = Math.max(MIN_X, position.x);
            position.y = Math.max(MIN_Y, position.y);
        });
    }

    function applyPositions(positions, tableElements) {
        tableElements.forEach(item => {
            const position = positions.get(item.name);
            if (!position) return;
            item.element.style.left = `${Math.round(position.x)}px`;
            item.element.style.top = `${Math.round(position.y)}px`;
        });
    }

    function scoreComponent(component, degree) {
        return component.reduce((sum, name) => sum + (degree.get(name) || 0), 0);
    }

    function boundsHeight(positions, component) {
        const ys = component.map(name => positions.get(name)?.y || 0);
        return Math.max(...ys) - Math.min(...ys) + 220;
    }

    return { apply };
}
