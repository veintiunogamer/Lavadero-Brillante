/**
 * Pagination Items - utilidad pura de paginación client-side.
 *
 * Calcula la ventana de páginas visibles y los puntos suspensivos para el
 * paginador reutilizable (ver resources/views/partials/_pagination.blade.php).
 * No depende de Alpine ni del DOM: es una función pura, testeable con Node.
 *
 * Reglas (documentadas y cubiertas por tests en tests/js/pagination.test.mjs):
 *
 *  1. Entradas normalizadas: `current` y `total` se convierten a enteros.
 *     - total <= 0            -> [] (listado vacío; el paginador se oculta).
 *     - current fuera de rango -> se acota a [1, total].
 *  2. Totales pequeños: si total <= (2 * siblings + 3) se muestran todas las
 *     páginas SIN puntos suspensivos (con siblings=2: hasta 7 páginas seguidas).
 *  3. Ventana: [current - siblings, current + siblings] acotada a [1, total].
 *  4. Estructura: [1] + gap? + ventana + gap? + [total], donde:
 *     - El gap izquierdo solo existe si la ventana empieza en > 2 (es decir,
 *       la página 2 NO está visible => se omiten páginas reales).
 *     - El gap derecho solo existe si la ventana termina en < total - 1
 *       (la penúltima NO está visible => se omiten páginas reales).
 *     - Los números 1 y `total` ya renderizados se excluyen de la ventana
 *       (sin duplicados). La página actual SIEMPRE queda incluida.
 *  5. `distant`: marca números a 2 o más de distancia de la actual (excluyendo
 *     los extremos 1/total) para que CSS pueda ocultarlos en pantallas
 *     medianas sin romper la semántica de los gaps (sigue habiendo páginas
 *     omitidas reales entre lo visible).
 *
 * Máximo absoluto con siblings=2: 7 números + 2 gaps = 9 ítems, con 47, 100
 * o 10 000 páginas.
 */

/**
 * Convierte un valor a entero acotado.
 * @param {*} value
 * @param {number} min
 * @param {number} fallback
 * @returns {number}
 */
function toInt(value, min, fallback) {
    const n = Math.floor(Number(value));

    return Number.isFinite(n) ? Math.max(min, n) : fallback;
}

/**
 * Construye un ítem de página.
 * @param {number} n - Número de página.
 * @param {number} current - Página actual (ya acotada).
 * @param {number} total - Total de páginas.
 * @returns {{type: string, n: number, key: string, distant: boolean}}
 */
function pageItem(n, current, total) {
    const isEdge = n === 1 || n === total;

    return {
        type: 'page',
        n,
        key: `p-${n}`,
        distant: !isEdge && Math.abs(n - current) >= 2
    };
}

/**
 * Construye un ítem de puntos suspensivos (gap).
 * @param {number} from - Primera página omitida (realfact-checked por el caller).
 * @returns {{type: string, from: number, key: string}}
 */
function gapItem(from) {
    return { type: 'gap', from, key: `g-${from}` };
}

/**
 * Calcula los ítems del paginador.
 * @param {number} current - Página actual.
 * @param {number} total - Total de páginas.
 * @param {{siblings?: number}} [options] - Número de páginas visibles a cada
 *   lado de la actual (por defecto 2).
 * @returns {Array<{type: string, key: string}>}
 */
export function paginationItems(current, total, options = {}) {
    const siblings = toInt(options.siblings, 1, 2);
    const t = toInt(total, 0, 0);

    if (t <= 0) {
        return [];
    }

    const c = Math.min(toInt(current, 1, 1), t);

    // Totales pequeños: sin gaps, todas las páginas visibles.
    if (t <= 2 * siblings + 3) {
        const items = [];

        for (let n = 1; n <= t; n++) {
            items.push(pageItem(n, c, t));
        }

        return items;
    }

    const windowStart = Math.max(1, c - siblings);
    const windowEnd = Math.min(t, c + siblings);
    const items = [pageItem(1, c, t)];

    // Gap izquierdo: solo si la página 2 no está visible (omisión real).
    if (windowStart > 2) {
        items.push(gapItem(2));
    }

    for (let n = windowStart; n <= windowEnd; n++) {
        if (n === 1 || n === t) {
            continue; // Ya renderizados como extremos: sin duplicados.
        }

        items.push(pageItem(n, c, t));
    }

    // Gap derecho: solo si la penúltima no está visible (omisión real).
    if (windowEnd < t - 1) {
        items.push(gapItem(windowEnd + 1));
    }

    if (t > 1) {
        items.push(pageItem(t, c, t));
    }

    return items;
}

// Exponer como global para que Alpine pueda usarlo en los atributos x-for
// (las vistas Blade evalúan expresiones en el scope global del navegador).
if (typeof window !== 'undefined') {
    window.paginationItems = paginationItems;
}
