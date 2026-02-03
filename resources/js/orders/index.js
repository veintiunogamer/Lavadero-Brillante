/**
 * Orders Module - Punto de entrada principal
 * 
 * Este módulo orquesta todos los componentes del formulario de órdenes.
 * Exporta la función orderFormApp para ser usada con Alpine.js
 * 
 * @module orders
 */

import { CalendarManager } from './modules/calendar/CalendarManager.js';
import { ServiceManager } from './modules/services/ServiceManager.js';
import { PaymentManager } from './modules/payment/PaymentManager.js';
import { TimePickerManager } from './modules/timepicker/TimePickerManager.js';
import { LicensePlateValidator } from './modules/client/LicensePlateValidator.js';
import { FormDataCollector } from './modules/form/FormDataCollector.js';
import { FormSubmitHandler } from './modules/form/FormSubmitHandler.js';
import { apiGet, apiPost } from './utils/api.js';
import * as formatters from './utils/formatters.js';

// Flag para prevenir inicialización múltiple
let isInitialized = false;

/**
 * Verifica si estamos en la vista de órdenes
 * @returns {boolean}
 */
function isOrdersView() {
    return !!document.getElementById('orders-root');
}

/**
 * Crea el componente Alpine para el formulario de órdenes
 * @returns {Object}
 */
function createOrderFormApp() {

    // Módulos (se inicializan una sola vez)
    const calendar = new CalendarManager();
    const services = new ServiceManager();
    const payment = new PaymentManager();
    const timepicker = new TimePickerManager();
    const licensePlate = new LicensePlateValidator();
    const dataCollector = new FormDataCollector();
    let submitHandler = null;

    return {

        // Estado reactivo
        currentTab: 'pending',
        orders: [],
        loadingOrders: false,
        submitting: false,

        /**
         * Inicialización del componente Alpine
         */
        async init() {

            // Inicializar módulos
            calendar.init();
            services.init();
            payment.init();
            timepicker.init();
            licensePlate.init();
            
            // Inicializar manejador de envío
            const self = this;
            submitHandler = new FormSubmitHandler(() => self.submitOrder());
            submitHandler.init();

            // Cargar órdenes
            await this.loadOrders();
        },

        /**
         * Cambia la pestaña activa
         * @param {string} tab 
         */
        async changeTab(tab) {
            this.currentTab = tab;
            await this.loadOrders();
        },

        /**
         * Carga las órdenes de la pestaña actual
         */
        async loadOrders() {

            this.loadingOrders = true;

            try {

                const result = await apiGet('/orders/tab/' + this.currentTab);
                // console.log('📦 Órdenes cargadas:', result);

                if (result && result.success) {

                    this.orders = result.data || [];
                    // console.log('✅ Total órdenes:', this.orders.length);

                } else if (result && result.message === 'Unauthenticated.') {

                    // Usuario no autenticado - redirigir a login
                    // console.warn('⚠️ Usuario no autenticado');
                    this.orders = [];

                } else {

                    // console.warn('⚠️ Respuesta inesperada:', result);
                    this.orders = [];

                }

            } catch (error) {

                // console.error('❌ Error cargando órdenes:', error);
                this.orders = [];

            } finally {

                this.loadingOrders = false;

            }
        },

        /**
         * Envía el formulario de orden
         */
        async submitOrder() {

            if (this.submitting) return;

            // Validar formulario
            const form = document.getElementById('orders-root');
            const validator = new window.OrderFormValidator(form);

            if (!validator.validateOrderForm()) {
                validator.showErrors();
                return;
            }

            this.submitting = true;

            try {

                const formData = dataCollector.collect();
                const result = await apiPost('/orders/store', formData);

                if (result.success) {

                    window.notyf?.success('¡Orden creada exitosamente!');
                    await this.loadOrders();
                    this.resetForm();

                } else {
                    this.handleSubmitErrors(result);
                }

            } catch (error) {

                console.error('Error:', error);
                window.notyf?.error('Error al enviar la orden');

            } finally {

                this.submitting = false;
            }
        },

        /**
         * Maneja errores del envío
         * @param {Object} result 
         */
        handleSubmitErrors(result) {

            if (result.errors) {

                Object.values(result.errors).forEach(errors => {
                    errors.forEach(error => window.notyf?.error(error));
                });

            } else {
                window.notyf?.error(result.message || 'Error al crear la orden');
            }

        },

        /**
         * Resetea el formulario completo
         */
        resetForm() {

            // Limpiar campos de texto y email
            document.querySelectorAll('#orders-root input[type="text"], #orders-root input[type="email"], #orders-root textarea').forEach(input => {
                if (!input.readOnly) input.value = '';
            });

            // Resetear campos numéricos (excepto cantidad que va a 1)
            document.querySelectorAll('#orders-root input[type="number"]').forEach(input => {
                
                if (input.classList.contains('service-quantity')) {
                    input.value = 1;
                } else {
                    input.value = '';
                }

                // Quitar las clases de .is-valid / .is-invalid
                input.classList.remove('is-valid', 'is-invalid');

            });

            // Resetear todos los selects a su primera opción
            document.querySelectorAll('#orders-root select').forEach(select => {

                // No resetear si es un select del sistema (como hora fallback)
                if (!select.classList.contains('time-picker-fallback')) {
                    select.selectedIndex = 0;
                }

                // Quitar las clases de .is-valid / .is-invalid
                select.classList.remove('is-valid', 'is-invalid');
                
            });

            // Resetear módulos
            services.reset();
            payment.reset();
            timepicker.reset();
            licensePlate.reset();
            calendar.reset();
            submitHandler?.reset();

            // Resetear facturación
            const solicitarFactura = document.getElementById('solicitar-factura');
            const datosFacturacion = document.getElementById('datos-facturacion');

            if (solicitarFactura) solicitarFactura.checked = false;
            if (datosFacturacion) datosFacturacion.style.display = 'none';

            // Scroll al inicio del formulario
            document.getElementById('orders-root')?.scrollIntoView({ behavior: 'smooth', block: 'start' });

        },

        // Métodos de formateo
        formatDate: (date) => formatters.formatDate(date),
        formatTime: (time) => formatters.formatTime(time),
        formatCurrency: (amount) => formatters.formatCurrency(amount),
        getStatusText: (status) => formatters.getStatusText(status),
        getStatusBadge: (status) => formatters.getStatusBadge(status)
    };
}

/**
 * Inicializa el módulo de órdenes
 */
function initOrdersModule() {

    // Solo ejecutar si estamos en la vista correcta
    if (!isOrdersView()) return;
    
    // Prevenir inicialización múltiple
    if (isInitialized) return;
    isInitialized = true;

    // Exponer el componente Alpine globalmente
    window.orderFormApp = createOrderFormApp;

    console.log('Orders module loaded');
}

// Ejecutar inmediatamente (Vite ya maneja el orden de carga)
initOrdersModule();

// Exportar para uso como módulo ES6
export { createOrderFormApp };
export default initOrdersModule;
