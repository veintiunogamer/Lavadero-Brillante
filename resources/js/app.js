import './bootstrap';
import './usuarios.js';
import './orders.js';
import './header.js';
import './alpine.js';
import './notyf.js';

import Cleave from 'cleave.js';

// JS global para toda la app
console.log('App JS cargado');

// Funciones de validación y formato para España

/**
 * Valida un número de teléfono español.
 * Formato esperado: +34 600 123 456 (o variantes sin espacios/prefijo).
 * @param {string} phone - El número de teléfono a validar.
 * @returns {boolean} - True si es válido.
 */
window.validateSpanishPhoneJS = function(phone) {
    const cleaned = phone.replace(/\D/g, '');
    return cleaned.length === 9 || (cleaned.length === 11 && cleaned.startsWith('34'));
};

/**
 * Formatea un monto a formato de euro español.
 * Ejemplo: 1234.56 -> 1.234,56 €
 * @param {number} amount - El monto a formatear.
 * @returns {string} - El monto formateado.
 */
window.formatEuroJS = function(amount) {
    return new Intl.NumberFormat('es-ES', {
        style: 'currency',
        currency: 'EUR'
    }).format(amount);
};


/**
 * Formatea un teléfono español en tiempo real.
 * @param {string} value - El valor del input.
 * @returns {string} - El valor formateado.
 */
window.formatPhoneInput = function(value) {
    // Extraer dígitos, ignorando +34 si ya está
    let digits = value.replace(/\D/g, '');
    if (value.startsWith('+34') && digits.length >= 2) {
        digits = digits.slice(2); // Quitar 34 del prefijo
    }
    // Si vacío, devolver vacío
    if (digits.length === 0) return '';
    // Formatear paso a paso
    if (digits.length <= 3) return '+34 ' + digits;
    if (digits.length <= 6) return '+34 ' + digits.slice(0, 3) + ' ' + digits.slice(3);
    return '+34 ' + digits.slice(0, 3) + ' ' + digits.slice(3, 6) + ' ' + digits.slice(6, 9);
};