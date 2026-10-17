/**
 * Pruebas unitarias de paginationItems (resources/js/pagination.js).
 * Ejecutar con: node --test tests/js/pagination.test.mjs
 */
import { test } from 'node:test';
import assert from 'node:assert/strict';
import { paginationItems } from '../../resources/js/pagination.js';

// ---------------------------------------------------------------------------
// Helpers
// ---------------------------------------------------------------------------

const pages = (items) => items.filter((i) => i.type === 'page').map((i) => i.n);
const gaps = (items) => items.filter((i) => i.type === 'gap');

/**
 * Invariantes globales que deben cumplirse SIEMPRE, para cualquier total y
 * cualquier página actual:
 *  1. La página actual está incluida (si total >= 1).
 *  2. Sin números duplicados.
 *  3. Primer ítem = página 1, último ítem = página total.
 *  4. Cada gap está estrictamente entre dos páginas a<b con b-a >= 2
 *     (los puntos suspensivos representan páginas omitidas REALES).
 *  5. Nunca hay dos gaps consecutivos.
 *  6. Máximo 2 gaps y máximo 2*siblings+3 números (7 con siblings=2).
 *  7. Los ítems con type 'gap' no traen número de página y declaran `from`.
 */
function assertInvariants(items, current, total, siblings = 2) {
    const numbers = pages(items);

    if (total <= 0) {
        assert.equal(items.length, 0);
        return;
    }

    assert.ok(numbers.includes(current), `falta la pagina actual ${current} (total ${total})`);
    assert.equal(new Set(numbers).size, numbers.length, `duplicados en ${JSON.stringify(numbers)}`);
    assert.equal(numbers[0], 1, 'el primer item debe ser la pagina 1');
    assert.equal(numbers[numbers.length - 1], total, 'el ultimo item debe ser la ultima pagina');
    assert.ok(numbers.length <= 2 * siblings + 3, `demasiados numeros: ${numbers.length}`);
    assert.ok(gaps(items).length <= 2, 'maximo 2 gaps');

    for (let idx = 0; idx < items.length; idx++) {
        const item = items[idx];

        if (item.type === 'gap') {
            const prev = items[idx - 1];
            const next = items[idx + 1];

            assert.ok(prev && prev.type === 'page', 'gap sin pagina anterior');
            assert.ok(next && next.type === 'page', 'gap sin pagina siguiente');
            assert.notEqual(next.type, 'gap', 'gaps consecutivos');
            assert.ok(next.n - prev.n >= 2, `gap sin omision real entre ${prev.n} y ${next.n}`);
            assert.equal(item.n, undefined, 'gap no debe traer numero de pagina');
            assert.equal(item.from, prev.n + 1, `gap debe declarar la primera pagina omitida (${prev.n + 1})`);
        } else {
            assert.equal(item.type, 'page');
            assert.equal(typeof item.n, 'number');
        }
    }
}

// ---------------------------------------------------------------------------
// Casos extremos de total
// ---------------------------------------------------------------------------

test('total 0 y negativos: sin items (el paginador se oculta)', () => {
    assert.deepEqual(paginationItems(1, 0), []);
    assert.deepEqual(paginationItems(1, -3), []);
    assert.deepEqual(paginationItems(1, 'no-num'), []);
});

test('total 1: un solo item, sin gaps', () => {
    const items = paginationItems(1, 1);
    assert.deepEqual(pages(items), [1]);
    assert.equal(gaps(items).length, 0);
    assertInvariants(items, 1, 1);
});

test('totales pequeños (2..7): todas las paginas visibles, sin gaps', () => {
    for (let total = 2; total <= 7; total++) {
        for (let current = 1; current <= total; current++) {
            const items = paginationItems(current, total);
            assert.deepEqual(pages(items), Array.from({ length: total }, (_, i) => i + 1));
            assert.equal(gaps(items).length, 0);
            assertInvariants(items, current, total);
        }
    }
});

// ---------------------------------------------------------------------------
// Casos pedidos: 47 paginas (el escenario real del proyecto)
// ---------------------------------------------------------------------------

test('total 47, pagina 1: [1, 2, 3, gap, 47]', () => {
    const items = paginationItems(1, 47);
    assert.deepEqual(pages(items), [1, 2, 3, 47]);
    assert.equal(gaps(items).length, 1);
    assert.equal(gaps(items)[0].from, 4);
    assertInvariants(items, 1, 47);
});

test('total 47, pagina 2: [1, 2, 3, 4, gap, 47] (sin gap izquierdo: 2 es contigua a 1)', () => {
    const items = paginationItems(2, 47);
    assert.deepEqual(pages(items), [1, 2, 3, 4, 47]);
    assertInvariants(items, 2, 47);
});

test('total 47, pagina 3: [1..5, gap, 47] (ventana fundida con el borde izquierdo)', () => {
    const items = paginationItems(3, 47);
    assert.deepEqual(pages(items), [1, 2, 3, 4, 5, 47]);
    assertInvariants(items, 3, 47);
});

test('total 47, pagina intermedia 24: [1, gap, 22..26, gap, 47]', () => {
    const items = paginationItems(24, 47);
    assert.deepEqual(pages(items), [1, 22, 23, 24, 25, 26, 47]);
    assert.deepEqual(gaps(items).map((g) => g.from), [2, 27]);
    assertInvariants(items, 24, 47);
});

test('total 47, penultima (46): [1, gap, 44..47] (sin gap derecho: 46 es contigua a 47)', () => {
    const items = paginationItems(46, 47);
    assert.deepEqual(pages(items), [1, 44, 45, 46, 47]);
    assert.equal(gaps(items).length, 1);
    assertInvariants(items, 46, 47);
});

test('total 47, ultima (47): [1, gap, 45, 46, 47]', () => {
    const items = paginationItems(47, 47);
    assert.deepEqual(pages(items), [1, 45, 46, 47]);
    assertInvariants(items, 47, 47);
});

// ---------------------------------------------------------------------------
// Escalabilidad y cambios de total (filtrado)
// ---------------------------------------------------------------------------

test('totales grandes (100, 999, 10000): longitud acotada e invariantes', () => {
    for (const total of [100, 999, 10000]) {
        for (let current = 1; current <= total; current += Math.ceil(total / 57)) {
            const items = paginationItems(current, total);
            assert.ok(items.length <= 9, `total ${total} actual ${current}: ${items.length} items`);
            assertInvariants(items, current, total);
        }
        assertInvariants(paginationItems(1, total), 1, total);
        assertInvariants(paginationItems(total, total), total, total);
    }
});

test('cambio de total tras filtrar: la actual fuera de rango se acota sin romper nada', () => {
    // Estaba en la pagina 30 de 47; el filtro reduce el listado a 5 paginas.
    const before = paginationItems(30, 47);
    assert.ok(pages(before).includes(30));

    const after = paginationItems(30, 5);
    assert.deepEqual(pages(after), [1, 2, 3, 4, 5]); // 5 <= umbral: sin gaps
    assertInvariants(after, 5, 5); // acotada a la nueva ultima pagina

    // Filtro sin resultados
    assert.deepEqual(paginationItems(30, 0), []);
});

// ---------------------------------------------------------------------------
// Normalizacion de entradas
// ---------------------------------------------------------------------------

test('entradas no enteras o invalidas se normalizan', () => {
    assertInvariants(paginationItems('24', '47'), 24, 47);
    assertInvariants(paginationItems(24.9, 47.9), 24, 47);
    assertInvariants(paginationItems(NaN, 47), 1, 47);
    assertInvariants(paginationItems(0, 47), 1, 47);
    assertInvariants(paginationItems(-5, 47), 1, 47);
    // Actual mayor que total (estado desactualizado): se acota a la ultima
    const items = paginationItems(60, 47);
    assert.ok(pages(items).includes(47));
    assertInvariants(items, 47, 47);
});

// ---------------------------------------------------------------------------
// Opcion siblings (densidad configurable)
// ---------------------------------------------------------------------------

test('siblings=1 reduce la ventana a current±1', () => {
    const items = paginationItems(24, 47, { siblings: 1 });
    assert.deepEqual(pages(items), [1, 23, 24, 25, 47]);
    assertInvariants(items, 24, 47, 1);
});

test('siblings=3 amplia la ventana a current±3', () => {
    const items = paginationItems(24, 47, { siblings: 3 });
    assert.deepEqual(pages(items), [1, 21, 22, 23, 24, 25, 26, 27, 47]);
    assertInvariants(items, 24, 47, 3);
});

test('flag distant: solo numeros a >=2 de la actual, nunca los extremos', () => {
    const items = paginationItems(24, 47); // ventana 22..26
    const distant = items.filter((i) => i.distant).map((i) => i.n);
    assert.deepEqual(distant, [22, 26]); // 23/25 pegados, 24 es la actual

    const edge = paginationItems(3, 47); // ventana 1..5
    const edgeDistant = edge.filter((i) => i.distant).map((i) => i.n);
    assert.deepEqual(edgeDistant, [5]); // el 1 nunca se marca (es extremo)
});

// ---------------------------------------------------------------------------
// Barrido completo y keys
// ---------------------------------------------------------------------------

test('barrido total: invariantes cumplidas para total 0..130 y toda pagina', () => {
    for (let total = 0; total <= 130; total++) {
        for (let current = 1; current <= Math.max(total, 1); current++) {
            assertInvariants(paginationItems(current, total), current, total);
        }
        assertInvariants(paginationItems(total + 25, total), Math.max(total, 1), total);
        assertInvariants(paginationItems(-10, total), 1, total);
    }
});

test('keys estables y unicas (para :key de Alpine)', () => {
    const items = paginationItems(24, 47);
    const keys = items.map((i) => i.key);
    assert.equal(new Set(keys).size, keys.length, 'keys duplicadas');
    keys.forEach((k) => assert.match(k, /^(p-\d+|g-\d+)$/));
});
