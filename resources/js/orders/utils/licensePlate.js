/**
 * LicensePlate - Utilidades de presentación de matrículas
 *
 * Genera el marcado de una placa europea (estilo placa española) para poder
 * mostrarse como badge dentro de las tablas de órdenes.
 * Solo es presentación: no modifica datos ni la validación del formulario.
 *
 * @module utils/licensePlate
 */

// Puntos de una estrella de 5 puntas (R=8.5, r=3.6) centrada en (0,0)
const STAR_POINTS = '0,-8.5 2.017,-2.777 8.083,-2.627 3.266,1.06 4.995,6.874 0,3.433 -4.995,6.874 -3.266,1.06 -8.083,-2.627 -2.017,-2.777';

// Posiciones de las 12 estrellas en círculo (viewBox 0 0 100 100, radio 38)
const STAR_POSITIONS = [
    [50, 12], [69, 17.09], [82.91, 31], [88, 50],
    [82.91, 69], [69, 82.91], [50, 88], [31, 82.91],
    [17.09, 69], [12, 50], [17.09, 31], [31, 17.09]
];

/**
 * SVG con las 12 estrellas de la Unión Europea.
 * @returns {string}
 */
const EU_STARS_SVG = `<svg class="eu-plate__stars" viewBox="0 0 100 100" preserveAspectRatio="xMidYMid meet" aria-hidden="true" focusable="false">` +
    STAR_POSITIONS.map(([x, y]) =>
        `<polygon points="${STAR_POINTS}" transform="translate(${x} ${y})"/>`
    ).join('') +
    `</svg>`;

/**
 * Escapa el texto para poder insertarlo dentro de HTML.
 * @param {string} value
 * @returns {string}
 */
export const escapeHtml = (value) => {
    return String(value ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
};

/**
 * Normaliza una matrícula: mayúsculas, sin guiones ni espacios sobrantes.
 * @param {string} plate
 * @returns {string}
 */
export const normalizeLicensePlate = (plate) => {
    return String(plate ?? '')
        .trim()
        .toUpperCase()
        .replace(/[\s\-_.]+/g, '');
};

/**
 * Descompone la matrícula en sus partes visibles.
 * Formato actual español: 1234 ABC. Formato histórico: ABC 1234.
 * @param {string} plate
 * @returns {{number: string, letters: string, legacy: boolean, valid: boolean}}
 */
export const parseLicensePlate = (plate) => {

    const normalized = normalizeLicensePlate(plate);

    const modern = normalized.match(/^(\d{4})([A-Z]{3})$/);

    if (modern) {
        return { number: modern[1], letters: modern[2], legacy: false, valid: true };
    }

    const legacy = normalized.match(/^([A-Z]{3})(\d{4})$/);

    if (legacy) {
        return { number: legacy[2], letters: legacy[1], legacy: true, valid: true };
    }

    return { number: normalized, letters: '', legacy: false, valid: false };
};

/**
 * Genera el marcado HTML de la placa europea (badge estilo placa española).
 *
 * Estructura:
 *   <span class="eu-plate">
 *     <span class="eu-plate__band">  <-- banda azul con las 12 estrellas
 *     <span class="eu-plate__body">  <-- cuerpo blanco con la matrícula
 *   </span>
 *
 * @param {string} plate - Matrícula a mostrar.
 * @returns {string} - HTML listo para insertar (x-html).
 */
export const renderLicensePlate = (plate) => {

    const parsed = parseLicensePlate(plate);

    if (!parsed.number) {
        return '<span class="eu-plate eu-plate--empty">--</span>';
    }

    const number = escapeHtml(parsed.number);
    const letters = escapeHtml(parsed.letters);

    const body = parsed.valid
        ? `<span class="eu-plate__number">${number}</span><span class="eu-plate__letters">${letters}</span>`
        : `<span class="eu-plate__raw">${number}</span>`;

    return `<span class="eu-plate" title="${number}${letters ? ' ' + letters : ''}">` +
        `<span class="eu-plate__band">${EU_STARS_SVG}</span>` +
        `<span class="eu-plate__body">${body}</span>` +
        `</span>`;
};

export default {
    escapeHtml,
    normalizeLicensePlate,
    parseLicensePlate,
    renderLicensePlate
};
