/**
 * Pruebas de integración Blade <-> Alpine del paginador reutilizable.
 *
 * Extrae LAS 8 configuraciones REALES de @include('partials._pagination')
 * desde las vistas Blade, reconstruye exactamente las expresiones Alpine
 * que genera el partial y las evalúa con mocks contra el contexto de cada
 * módulo, verificando que cada instancia:
 *   1. Genera expresiones JS válidas (parseables).
 *   2. Conserva su contexto Alpine y la firma ORIGINAL de su goToPage.
 *   3. Produce la ventana correcta vía paginationItems.
 * Ejecutar con: node --test tests/js/pagination-instances.test.mjs
 */
import { test } from 'node:test';
import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';
import { fileURLToPath } from 'node:url';
import path from 'node:path';
import { paginationItems } from '../../resources/js/pagination.js';

const root = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '../..');

const TOTAL = 47;
const COUNT = 467;
const PER_PAGE = 10;

// ---------------------------------------------------------------------------
// Extracción de las instancias reales desde las vistas Blade
// ---------------------------------------------------------------------------

function unquote(phpStr) {
    if (phpStr.startsWith('"')) {
        return phpStr.slice(1, -1).replace(/\\(["\\])/g, '$1');
    }

    return phpStr.slice(1, -1).replace(/\\'/g, "'");
}

function extractInstances() {
    const views = [
        'resources/views/index.blade.php',
        'resources/views/orders/index.blade.php',
        'resources/views/reports/index.blade.php',
        'resources/views/settings/index.blade.php',
    ];
    const instances = [];

    for (const rel of views) {
        const src = readFileSync(path.join(root, rel), 'utf8');
        const includeRe = /@include\('partials\._pagination',\s*\[([\s\S]*?)\]\)/g;
        let m;

        while ((m = includeRe.exec(src)) !== null) {
            const params = {};
            const pairRe = /'(\w+)' =>\s*("(?:[^"\\]|\\.)*"|'(?:[^'\\]|\\.)*')/g;
            let p;

            while ((p = pairRe.exec(m[1])) !== null) {
                params[p[1]] = unquote(p[2]);
            }

            instances.push({ view: rel, params });
        }
    }

    return instances;
}

// ---------------------------------------------------------------------------
// Contextos Alpine simulados (uno por módulo), espejo de los estados reales
// ---------------------------------------------------------------------------

function makeContext(view) {
    const calls = [];
    const goToPage = (...args) => calls.push(args);

    let ctx;

    switch (view) {
        case 'resources/views/index.blade.php':
            // orderFormApp (resources/js/orders/index.js): pestañas numéricas
            ctx = {
                currentPage: { 1: 24, 2: 1, 3: 1 },
                currentTab: 1,
                getTotalPages: () => TOTAL,
                getFilteredOrders: () => ({ length: COUNT }),
                loadingOrders: false,
                perPage: PER_PAGE,
            };
            break;

        case 'resources/views/orders/index.blade.php':
            // agendamientoApp (resources/js/orders/orders.js): página escalar
            ctx = {
                currentPage: 24,
                getTotalPages: () => TOTAL,
                getFilteredOrders: () => ({ length: COUNT }),
                perPage: PER_PAGE,
            };
            break;

        case 'resources/views/reports/index.blade.php':
            // reportsApp (resources/js/reports/index.js): pestañas sales/clients
            ctx = {
                currentPage: { sales: 24, clients: 24 },
                getTotalPages: (type) => {
                    assert.ok(['sales', 'clients'].includes(type), `tipo inesperado: ${type}`);
                    return TOTAL;
                },
                getFilteredData: (type) => {
                    assert.ok(['sales', 'clients'].includes(type), `tipo inesperado: ${type}`);
                    return { length: COUNT };
                },
                perPage: PER_PAGE,
            };
            break;

        case 'resources/views/settings/index.blade.php':
            // settingsApp (resources/js/settings.js): 4 colecciones
            ctx = {
                currentPage: { categories: 24, services: 24, vehicleTypes: 24, clients: 24 },
                getTotalPages: (type) => {
                    assert.ok(['categories', 'services', 'vehicleTypes', 'clients'].includes(type), `tipo inesperado: ${type}`);
                    return TOTAL;
                },
                getFilteredData: (type) => {
                    assert.ok(['categories', 'services', 'vehicleTypes', 'clients'].includes(type), `tipo inesperado: ${type}`);
                    return { length: COUNT };
                },
                perPage: PER_PAGE,
            };
            break;

        default:
            throw new Error(`Vista sin contexto de prueba: ${view}`);
    }

    ctx.goToPage = goToPage;
    ctx.paginationItems = paginationItems;

    return { ctx, calls };
}

// Evalúa una expresión Alpine tal como la inyectaría el partial.
function evaluate(expr, ctx, extra = {}) {
    const names = [...Object.keys(ctx), ...Object.keys(extra)];
    const values = [...Object.values(ctx), ...Object.values(extra)];
    const fn = new Function(...names, `return (${expr});`);

    return fn(...values);
}

// Expresiones EXACTAS que genera partials/_pagination.blade.php.
const partialExprs = (p) => ({
    prev: `(${p.go})(${p.current} - 1)`,
    next: `(${p.go})(${p.current} + 1)`,
    jump: `(${p.go})(item.n)`,
    window: `paginationItems(${p.current}, ${p.total})`,
    show: p.show ?? `${p.total} > 1`,
    prevDisabled: `(${p.current}) <= 1`,
    nextDisabled: `(${p.current}) >= (${p.total})`,
    indicatorCurrent: p.current,
    indicatorTotal: p.total,
    rangeFrom: `(((${p.current}) - 1) * (${p.perPage ?? 'perPage'})) + 1`,
    rangeTo: `Math.min((${p.current}) * (${p.perPage ?? 'perPage'}), (${p.count}))`,
});

// Firma ORIGINAL de goToPage que cada instancia DEBE conservar.
const EXPECTED_SIGNATURES = {
    'resources/views/index.blade.php::currentPage[currentTab]': (page) => [1, page], // goToPage(tab, page)
    'resources/views/orders/index.blade.php::currentPage': (page) => [page], // goToPage(page)
    'resources/views/reports/index.blade.php::currentPage.sales': (page) => ['sales', page],
    'resources/views/reports/index.blade.php::currentPage.clients': (page) => ['clients', page],
    'resources/views/settings/index.blade.php::currentPage.categories': (page) => ['categories', page],
    'resources/views/settings/index.blade.php::currentPage.services': (page) => ['services', page],
    'resources/views/settings/index.blade.php::currentPage.vehicleTypes': (page) => ['vehicleTypes', page],
    'resources/views/settings/index.blade.php::currentPage.clients': (page) => ['clients', page],
};

// Coloca la página actual del mock en `value` para la instancia.
function setCurrent(ctx, currentExpr, value) {
    if (currentExpr === 'currentPage') {
        ctx.currentPage = value; // agendamiento: escalar
    } else if (currentExpr.endsWith('[currentTab]')) {
        ctx.currentPage[ctx.currentTab] = value; // dashboard por pestañas
    } else {
        ctx.currentPage[currentExpr.split('.')[1]] = value; // reports/settings
    }
}

const sigKey = (inst) => `${inst.view}::${inst.params.current}`;

// ---------------------------------------------------------------------------
// Tests
// ---------------------------------------------------------------------------

const instances = extractInstances();

test('las vistas contienen exactamente 8 instancias del partial', () => {
    assert.equal(instances.length, 8, JSON.stringify(instances.map((i) => sigKey(i)), null, 2));
});

test('toda instancia declara current, total y go (y go usa goToPage)', () => {
    for (const inst of instances) {
        const p = inst.params;
        assert.ok(p.current, `${sigKey(inst)} sin current`);
        assert.ok(p.total, `${sigKey(inst)} sin total`);
        assert.ok(p.go, `${sigKey(inst)} sin go`);
        assert.match(p.go, /^\(p\)\s*=>\s*goToPage\(/, `${sigKey(inst)}: go debe ser una flecha sobre goToPage`);
        assert.ok(sigKey(inst) in EXPECTED_SIGNATURES, `firma no registrada: ${sigKey(inst)}`);
    }
});

test('las 8 firmas cubren dashboard, agendamiento, reports y settings', () => {
    const keys = instances.map(sigKey);
    assert.equal(new Set(keys).size, 8, 'firmas duplicadas entre instancias');
    for (const key of Object.keys(EXPECTED_SIGNATURES)) {
        assert.ok(keys.includes(key), `falta la instancia ${key}`);
    }
});

test('las expresiones generadas por el partial son JS válido en cada instancia', () => {
    for (const inst of instances) {
        const { ctx } = makeContext(inst.view);
        const e = partialExprs(inst.params);

        // No deben lanzar (parse + evaluación con el contexto del módulo).
        evaluate(e.indicatorCurrent, ctx);
        evaluate(e.indicatorTotal, ctx);
        evaluate(e.prevDisabled, ctx);
        evaluate(e.nextDisabled, ctx);
        evaluate(e.window, ctx);
        evaluate(e.show, ctx);
    }
});

test('navegación: cada instancia llama al goToPage ORIGINAL con su firma', () => {
    for (const inst of instances) {
        const { ctx, calls } = makeContext(inst.view);
        const e = partialExprs(inst.params);
        const expect = EXPECTED_SIGNATURES[sigKey(inst)];

        // Anterior desde la página 5
        setCurrent(ctx, inst.params.current, 5);
        evaluate(e.prev, ctx);
        assert.deepEqual(calls.at(-1), expect(4), `${sigKey(inst)}: prev`);

        // Siguiente desde la página 5
        evaluate(e.next, ctx);
        assert.deepEqual(calls.at(-1), expect(6), `${sigKey(inst)}: next`);

        // Salto directo a un número (item.n del x-for)
        evaluate(e.jump, ctx, { item: { n: 12, type: 'page' } });
        assert.deepEqual(calls.at(-1), expect(12), `${sigKey(inst)}: jump`);
    }
});

test('estados deshabilitados: expresiones booleanas correctas en extremos', () => {
    for (const inst of instances) {
        const { ctx } = makeContext(inst.view);
        const e = partialExprs(inst.params);

        setCurrent(ctx, inst.params.current, 1);
        assert.equal(evaluate(e.prevDisabled, ctx), true, `${sigKey(inst)}: prev debe deshabilitarse en la 1`);
        assert.equal(evaluate(e.nextDisabled, ctx), false);

        setCurrent(ctx, inst.params.current, TOTAL);
        assert.equal(evaluate(e.nextDisabled, ctx), true, `${sigKey(inst)}: next debe deshabilitarse en la última`);
        assert.equal(evaluate(e.prevDisabled, ctx), false);
    }
});

test('ventana: paginationItems con el contexto real de cada instancia', () => {
    for (const inst of instances) {
        const { ctx } = makeContext(inst.view);
        const e = partialExprs(inst.params);

        setCurrent(ctx, inst.params.current, 24);
        const items = evaluate(e.window, ctx);
        const numbers = items.filter((i) => i.type === 'page').map((i) => i.n);

        assert.deepEqual(numbers, [1, 22, 23, 24, 25, 26, 47], sigKey(inst));
        assert.ok(numbers.includes(24), sigKey(inst));
        assert.ok(items.length <= 9, `${sigKey(inst)}: ${items.length} items`);
    }
});

test('indicador y rango: expresiones correctas con contexto', () => {
    for (const inst of instances) {
        const { ctx } = makeContext(inst.view);
        const e = partialExprs(inst.params);

        setCurrent(ctx, inst.params.current, 3);
        assert.equal(evaluate(e.indicatorCurrent, ctx), 3, sigKey(inst));
        assert.equal(evaluate(e.indicatorTotal, ctx), TOTAL, sigKey(inst));

        if (inst.params.count) {
            assert.equal(evaluate(e.rangeFrom, ctx), 21, sigKey(inst));
            assert.equal(evaluate(e.rangeTo, ctx), 30, sigKey(inst));
        }
    }
});

test('visibilidad: el show del dashboard respeta loadingOrders', () => {
    const inst = instances.find((i) => i.params.show);
    assert.ok(inst, 'el dashboard debe pasar show explicito');
    const { ctx } = makeContext(inst.view);
    const e = partialExprs(inst.params);

    assert.equal(evaluate(e.show, ctx), true);
    ctx.loadingOrders = true;
    assert.equal(evaluate(e.show, ctx), false);
});
