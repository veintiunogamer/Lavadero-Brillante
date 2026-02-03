/**
 * API Utilities - Funciones centralizadas para peticiones HTTP
 * @module utils/api
 */

/**
 * Obtiene el token CSRF del meta tag
 * @returns {string|null}
 */
const getCsrfToken = () => {
    return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
};

/**
 * Configuración base para peticiones fetch
 * @param {Object} options - Opciones adicionales
 * @returns {Object}
 */
const getBaseConfig = (options = {}) => ({
    headers: {
        'Accept': 'application/json',
        'X-CSRF-TOKEN': getCsrfToken(),
        ...options.headers
    },
    ...options
});

/**
 * Realiza una petición GET
 * @param {string} url - URL del endpoint
 * @returns {Promise<Object>}
 */
export const apiGet = async (url) => {
    const response = await fetch(url, getBaseConfig());
    return response.json();
};

/**
 * Realiza una petición POST con JSON
 * @param {string} url - URL del endpoint
 * @param {Object} data - Datos a enviar
 * @returns {Promise<Object>}
 */
export const apiPost = async (url, data) => {
    const response = await fetch(url, {
        method: 'POST',
        headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': getCsrfToken()
        },
        body: JSON.stringify(data)
    });
    return response.json();
};

/**
 * Carga servicios por categoría
 * @param {string} categoryId - ID de la categoría
 * @returns {Promise<Object>}
 */
export const fetchServicesByCategory = async (categoryId) => {
    return apiGet(`/api/services/category/${categoryId}`);
};

/**
 * Verifica si existe un cliente por matrícula
 * @param {string} licensePlate - Matrícula a verificar
 * @returns {Promise<Object>}
 */
export const checkLicensePlate = async (licensePlate) => {
    return apiGet(`/api/clients/check-license-plate?license_plate=${encodeURIComponent(licensePlate)}`);
};

/**
 * Carga órdenes por pestaña
 * @param {string} tab - Nombre de la pestaña (pending, in_progress, completed)
 * @returns {Promise<Object>}
 */
export const fetchOrdersByTab = async (tab) => {
    return apiGet(`/orders/tab/${tab}`);
};

/**
 * Crea una nueva orden
 * @param {Object} orderData - Datos de la orden
 * @returns {Promise<Object>}
 */
export const createOrder = async (orderData) => {
    return apiPost('/orders/store', orderData);
};

export default {
    get: apiGet,
    post: apiPost,
    fetchServicesByCategory,
    checkLicensePlate,
    fetchOrdersByTab,
    createOrder
};
