/**
 * Pruebas unitarias del render de placas europeas.
 * Ejecutar con: node --test tests/js/license-plate.test.mjs
 */
import { test } from 'node:test';
import assert from 'node:assert/strict';
import {
    escapeHtml,
    normalizeLicensePlate,
    parseLicensePlate,
    renderLicensePlate
} from '../../resources/js/orders/utils/licensePlate.js';

test('escapeHtml escapa los caracteres dangerousos', () => {
    assert.equal(escapeHtml('<b>"x"</b>'), '&lt;b&gt;&quot;x&quot;&lt;/b&gt;');
    assert.equal(escapeHtml("a & b"), 'a &amp; b');
    assert.equal(escapeHtml(null), '');
    assert.equal(escapeHtml(undefined), '');
});

test('normalizeLicensePlate quita espacios, guiones y pasa a mayúsculas', () => {
    assert.equal(normalizeLicensePlate(' 1234 abc '), '1234ABC');
    assert.equal(normalizeLicensePlate('1234-abc'), '1234ABC');
    assert.equal(normalizeLicensePlate('1234 abc'), '1234ABC');
    assert.equal(normalizeLicensePlate(null), '');
});

test('parseLicensePlate reconoce el formato actual 1234 ABC', () => {
    const parsed = parseLicensePlate('1234 ABC');
    assert.equal(parsed.valid, true);
    assert.equal(parsed.number, '1234');
    assert.equal(parsed.letters, 'ABC');
    assert.equal(parsed.legacy, false);
});

test('parseLicensePlate reconoce el formato histórico ABC 1234', () => {
    const parsed = parseLicensePlate('abc1234');
    assert.equal(parsed.valid, true);
    assert.equal(parsed.number, '1234');
    assert.equal(parsed.letters, 'ABC');
    assert.equal(parsed.legacy, true);
});

test('parseLicensePlate marca como inválido un formato desconocido', () => {
    const parsed = parseLicensePlate('PENDIENTE');
    assert.equal(parsed.valid, false);
    assert.equal(parsed.number, 'PENDIENTE');
    assert.equal(parsed.letters, '');
});

test('renderLicensePlate genera la banda con 12 estrellas de la UE', () => {
    const html = renderLicensePlate('1234 ABC');
    assert.ok(html.includes('class="eu-plate"'));
    assert.ok(html.includes('eu-plate__band'));
    assert.ok(html.includes('eu-plate__stars'));
    assert.equal((html.match(/<polygon/g) || []).length, 12);
});

test('renderLicensePlate muestra número y letras por separado', () => {
    const html = renderLicensePlate('1234 ABC');
    assert.ok(html.includes('<span class="eu-plate__number">1234</span>'));
    assert.ok(html.includes('<span class="eu-plate__letters">ABC</span>'));
});

test('renderLicensePlate escapa contenido malicioso', () => {
    const html = renderLicensePlate('<img src=x onerror=alert(1)>');
    assert.ok(!/<img/i.test(html));
    assert.ok(/&lt;img/i.test(html));
});

test('renderLicensePlate devuelve placeholder cuando no hay matrícula', () => {
    const html = renderLicensePlate(null);
    assert.ok(html.includes('eu-plate--empty'));
    assert.ok(!html.includes('<polygon'));
});

test('renderLicensePlate conserva el texto en formatos no reconocidos', () => {
    const html = renderLicensePlate('PENDIENTE');
    assert.ok(html.includes('eu-plate__raw'));
    assert.ok(html.includes('PENDIENTE'));
});
