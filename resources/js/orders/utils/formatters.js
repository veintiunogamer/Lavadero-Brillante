/**
 * Formatters - Funciones de formateo para fechas, moneda y estados
 * @module utils/formatters
 */

/**
 * Estados de orden con texto y clases de badge
 */
export const ORDER_STATUS = {
    1: { text: 'Pendiente', badge: 'badge bg-warning text-dark' },
    2: { text: 'En Proceso', badge: 'badge bg-info text-white' },
    3: { text: 'Terminado', badge: 'badge bg-success' },
    4: { text: 'Cancelado', badge: 'badge bg-danger' }
};

/**
 * Estados de pago
 */
export const PAYMENT_STATUS = {
    1: { text: 'Pendiente', badge: 'badge bg-warning text-dark' },
    2: { text: 'Parcial', badge: 'badge bg-info text-white' },
    3: { text: 'Pagado', badge: 'badge bg-success' }
};

/**
 * Nombres de los meses en español
 */
export const MONTH_NAMES = [
    'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
    'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'
];

/**
 * Nombres cortos de los meses
 */
export const MONTH_NAMES_SHORT = [
    'Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun',
    'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'
];

/**
 * Días de la semana en español
 */
export const WEEKDAYS = {
    short: ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb'],
    long: ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado']
};

/**
 * Formatea una fecha en formato español
 * @param {Date|string} date - Fecha a formatear
 * @returns {string}
 */
export const formatDate = (date) => {
    return new Date(date).toLocaleDateString('es-ES', {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit'
    });
};

/**
 * Formatea una fecha con nombre del mes
 * @param {Date|string} date - Fecha a formatear
 * @returns {string}
 */
export const formatDateLong = (date) => {
    return new Date(date).toLocaleDateString('es-ES', {
        day: '2-digit',
        month: 'long',
        year: 'numeric'
    });
};

/**
 * Formatea una hora
 * @param {Date|string} time - Hora a formatear
 * @returns {string}
 */
export const formatTime = (time) => {
    if (!time) return 'N/A';
    const date = new Date(time);
    return date.toLocaleTimeString('es-ES', { 
        hour: '2-digit', 
        minute: '2-digit' 
    });
};

/**
 * Formatea un monto en euros
 * @param {number} amount - Monto a formatear
 * @returns {string}
 */
export const formatCurrency = (amount) => {
    return new Intl.NumberFormat('es-ES', {
        style: 'currency',
        currency: 'EUR'
    }).format(amount);
};

/**
 * Formatea un monto simple con símbolo de euro
 * @param {number} amount - Monto a formatear
 * @returns {string}
 */
export const formatPrice = (amount) => {
    return parseFloat(amount).toFixed(2) + '€';
};

/**
 * Obtiene el texto del estado de una orden
 * @param {number} status - Código de estado
 * @returns {string}
 */
export const getStatusText = (status) => {
    return ORDER_STATUS[status]?.text || 'Desconocido';
};

/**
 * Obtiene la clase de badge para un estado
 * @param {number} status - Código de estado
 * @returns {string}
 */
export const getStatusBadge = (status) => {
    return ORDER_STATUS[status]?.badge || 'badge bg-secondary';
};

/**
 * Obtiene el texto del estado de pago
 * @param {number} status - Código de estado de pago
 * @returns {string}
 */
export const getPaymentStatusText = (status) => {
    return PAYMENT_STATUS[status]?.text || 'Desconocido';
};

/**
 * Obtiene la clase de badge para estado de pago
 * @param {number} status - Código de estado de pago
 * @returns {string}
 */
export const getPaymentStatusBadge = (status) => {
    return PAYMENT_STATUS[status]?.badge || 'badge bg-secondary';
};

export default {
    formatDate,
    formatDateLong,
    formatTime,
    formatCurrency,
    formatPrice,
    getStatusText,
    getStatusBadge,
    getPaymentStatusText,
    getPaymentStatusBadge,
    ORDER_STATUS,
    PAYMENT_STATUS,
    MONTH_NAMES,
    MONTH_NAMES_SHORT,
    WEEKDAYS
};
