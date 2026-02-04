/**
 * OrderFormApp - Componente Alpine principal para el formulario de órdenes
 * @module OrderFormApp
 */

import CalendarManager from './modules/calendar/CalendarManager.js';
import ServiceManager from './modules/services/ServiceManager.js';
import PaymentManager from './modules/payment/PaymentManager.js';
import TimePickerManager from './modules/timepicker/TimePickerManager.js';
import LicensePlateValidator from './modules/client/LicensePlateValidator.js';
import FormDataCollector from './modules/form/FormDataCollector.js';
import FormSubmitHandler from './modules/form/FormSubmitHandler.js';
import { fetchOrdersByTab, createOrder } from './utils/api.js';
import { formatDate, formatTime, formatCurrency, getStatusText, getStatusBadge } from './utils/formatters.js';

export class OrderFormApp {

    constructor() {

        // Módulos
        this.calendar = new CalendarManager();
        this.services = new ServiceManager();
        this.payment = new PaymentManager();
        this.timepicker = new TimePickerManager();
        this.licensePlate = new LicensePlateValidator();
        this.dataCollector = new FormDataCollector();
        this.submitHandler = null;
        
        // Estado
        this.state = {
            currentTab: 'pending',
            orders: [],
            loadingOrders: false,
            submitting: false
        };
    }

    /**
     * Convierte la instancia a un componente Alpine.js
     * @returns {Object}
     */
    toAlpineComponent() {

        const self = this;
        
        return {

            // Estado reactivo
            currentTab: self.state.currentTab,
            orders: self.state.orders,
            loadingOrders: self.state.loadingOrders,
            submitting: self.state.submitting,

            /**
             * Inicialización del componente Alpine
             */
            async init() {

                // Inicializar módulos
                self.calendar.init();
                self.services.init();
                self.payment.init();
                self.timepicker.init();
                self.licensePlate.init();
                
                // Inicializar manejador de envío
                self.submitHandler = new FormSubmitHandler(() => this.submitOrder());
                self.submitHandler.init();

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

                    const result = await fetchOrdersByTab(this.currentTab);

                    if (result.success) {
                        this.orders = result.data;
                    } else {
                        window.notyf?.error('Error al cargar las órdenes');
                    }

                } catch (error) {

                    console.error('Error:', error);
                    window.notyf?.error('Error al cargar las órdenes');

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

                    const formData = self.dataCollector.collect();
                    const result = await createOrder(formData);

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

                // Limpiar campos de texto
                document.querySelectorAll('#orders-root input[type="text"], #orders-root input[type="email"], #orders-root textarea').forEach(input => {
                    
                    // Limpiar valor
                    if (!input.readOnly) input.value = '';
                    
                    // Remover clases de validación
                    if (input.classList.contains('is-valid')) {
                        input.classList.remove('is-valid');
                    }

                    if (input.classList.contains('is-invalid')) {
                        input.classList.remove('is-invalid');
                    }

                });

                // Resetear módulos
                self.services.reset();
                self.payment.reset();
                self.timepicker.reset();
                self.licensePlate.reset();
                self.calendar.reset();
                self.submitHandler.reset();

                // Resetear facturación
                const solicitarFactura = document.getElementById('solicitar-factura');
                const datosFacturacion = document.getElementById('datos-facturacion');
                
                if (solicitarFactura) solicitarFactura.checked = false;
                if (datosFacturacion) datosFacturacion.style.display = 'none';

                // Sumar el numero del consecutivo
                const consecutiveNumberInput = document.querySelector('input[name="consecutive_number"]');

                if (consecutiveNumberInput) {
                    let currentNumber = parseInt(consecutiveNumberInput.value) || 0;
                    consecutiveNumberInput.value = (currentNumber + 1).toString().padStart(6, '0');
                }

            },

            // Métodos de formateo (delegados a utils)
            formatDate: (date) => formatDate(date),
            formatTime: (time) => formatTime(time),
            formatCurrency: (amount) => formatCurrency(amount),
            getStatusText: (status) => getStatusText(status),
            getStatusBadge: (status) => getStatusBadge(status)
            
        };
    }
}

export default OrderFormApp;
