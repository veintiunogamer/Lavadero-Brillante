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
import { apiGet, apiPost, apiPut, apiPatch } from './utils/api.js';
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
        isEditMode: false,
        editOrderId: null,
        editDataLoaded: false,

        // Búsqueda y paginación (por pestaña)
        searchTerms: {
            pending: '',
            history: ''
        },
        currentPage: {
            pending: 1,
            history: 1
        },
        perPage: 10,

        // Estado para modales
        showQuickViewModal: false,
        selectedOrder: null,
        showStatusModal: false,
        statusModalOrder: null,
        newStatus: null,
        statusChangeNote: '',
        changingStatus: false,
        showStatusTypeModal: false,
        statusTypeOrder: null,
        showPaymentModal: false,
        paymentModalOrder: null,
        newPaymentStatus: null,
        paymentPartialAmount: null,
        changingPayment: false,
        showInvoiceModal: false,
        invoiceOrderId: null,

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

            // Verificar si hay una orden para editar
            if (window.editOrderData && !this.editDataLoaded) {
                await this.loadEditOrder(window.editOrderData);
                this.editDataLoaded = true;
            }
        },

        /**
         * Carga los datos de una orden para edición
         * @param {Object} order 
         */
        async loadEditOrder(order) {

            if (!order || !order.id) return;
            if (this.isEditMode && this.editOrderId === order.id) return;

            this.isEditMode = true;
            this.editOrderId = order.id;

            order.payment = order.payment || (Array.isArray(order.payments) ? order.payments[0] : null);

            const orderDateValue = order.date || order.creation_date || null;

            if (orderDateValue) {

                const parsedOrderDate = new Date(orderDateValue);

                if (!Number.isNaN(parsedOrderDate.getTime())) {
                    calendar.setSelectedDate(parsedOrderDate);
                }
                
            }

            timepicker.setHours(order.hour_in || '', order.hour_out || '');

            // Pre-llenar datos del cliente
            const clientName = document.querySelector('[name="client_name"]');
            const clientPhone = document.querySelector('[name="client_phone"]');
            const licensePlateInput = document.querySelector('[name="license_plaque"]');
            
            if (clientName && order.client) clientName.value = order.client.name || '';
            if (clientPhone && order.client) clientPhone.value = order.client.phone || '';
            if (licensePlateInput && order.client) licensePlateInput.value = order.client.license_plaque || '';

            // Pre-llenar tipo de vehículo
            const vehicleType = document.querySelector('[name="vehicle_type_id"]');
            if (vehicleType) vehicleType.value = order.vehicle_type_id || '';

            // Pre-llenar suciedad
            const dirtLevel = document.querySelector('[name="dirt_level"]');
            if (dirtLevel) dirtLevel.value = order.dirt_level || 1;

            // Pre-llenar detallador asignado
            const assignedUser = document.querySelector('[name="assigned_user"]');
            if (assignedUser) assignedUser.value = order.user_id || '';

            // Pre-llenar consecutivo de la orden editada
            const serialInput = document.querySelector('input[name="consecutive_serial"]');
            const numberInput = document.querySelector('input[name="consecutive_number"]');
            if (serialInput) serialInput.value = order.consecutive_serial || serialInput.value || '';
            if (numberInput) numberInput.value = order.consecutive_number || numberInput.value || '';

            // Pre-llenar observaciones del vehículo
            const vehicleNotes = document.querySelector('[name="vehicle_notes"]');
            if (vehicleNotes) vehicleNotes.value = order.vehicle_notes || '';

            // Pre-llenar notas
            const orderNotes = document.querySelector('[name="order_notes"]');
            const extraNotes = document.querySelector('[name="extra_notes"]');
            if (orderNotes) orderNotes.value = order.order_notes || '';
            if (extraNotes) extraNotes.value = order.extra_notes || '';

            // Pre-llenar descuento (almacenado en euros, mostrar porcentaje)
            const discountSelect = document.querySelector('[name="discount"]');
            if (discountSelect) {
                const subtotal = Number(order.subtotal || 0);
                const discountAmount = Number(order.discount || 0);
                let discountPercent = 0;

                if (subtotal > 0 && discountAmount > 0) {
                    discountPercent = Math.round((discountAmount / subtotal) * 100);
                }

                const optionExists = Array.from(discountSelect.options)
                    .some(option => option.value === String(discountPercent));

                discountSelect.value = optionExists ? String(discountPercent) : '';
            }

            // Pre-llenar estado de pago
            const paymentStatus = document.querySelector('[name="payment_status"]');
            if (paymentStatus) paymentStatus.value = order.payment?.status || 1;

            // Activar el botón de estado de pago correspondiente
            document.querySelectorAll('.pay-status-btn').forEach(btn => {
                btn.classList.remove('pay-status-active');
                if (parseInt(btn.dataset.value) === (order.payment?.status || 1)) {
                    btn.classList.add('pay-status-active');
                }
            });

            // Pre-llenar pago parcial si aplica
            if (order.partial_payment) {
                const partialPayment = document.querySelector('[name="partial_payment"]');
                const partialContainer = document.getElementById('partial-payment-container');
                if (partialPayment) partialPayment.value = order.partial_payment;
                if (partialContainer) partialContainer.style.display = 'block';
            }

            // Pre-llenar método de pago
            const paymentMethod = document.querySelector('[name="payment_method"]');
            if (paymentMethod) paymentMethod.value = order.payment?.type || 1;

            // Pre-llenar estado de la cita
            const status = document.querySelector('[name="status"]');
            if (status) status.value = order.status || 1;

            // Pre-llenar servicios (primero el existente)
            if (order.services && order.services.length > 0) {
                const normalizedServices = this.normalizeServicesForEdit(order.services);
                await services.loadExistingServices(normalizedServices);
            }

            // Actualizar totales
            services.getCalculator().recalculate();

            // Cambiar texto del botón
            const confirmBtn = document.querySelector('.confirm-btn');
            if (confirmBtn) {
                confirmBtn.innerHTML = '<i class="fa-solid fa-save icon"></i> Guardar Cambios';
            }

            // Auto-check términos en edición
            const termsCheckbox = document.getElementById('terms-checkbox');
            if (termsCheckbox) {
                termsCheckbox.checked = true;
                termsCheckbox.dispatchEvent(new Event('change'));
            }

            console.log('📝 Modo edición activado para orden:', order.id);
        },

        normalizeServicesForEdit(rawServices = []) {
            if (!Array.isArray(rawServices) || rawServices.length === 0) return [];

            const grouped = new Map();

            rawServices.forEach(service => {
                if (!service?.id) return;

                const key = String(service.id);
                const pivotQuantity = Number(service?.pivot?.quantity ?? 1);
                const safeQuantity = Number.isFinite(pivotQuantity) && pivotQuantity > 0 ? pivotQuantity : 1;
                const pivotTotal = Number(service?.pivot?.total ?? service?.value ?? 0);
                const safeTotal = Number.isFinite(pivotTotal) ? pivotTotal : 0;

                if (!grouped.has(key)) {
                    grouped.set(key, {
                        ...service,
                        pivot: {
                            ...(service?.pivot || {}),
                            quantity: safeQuantity,
                            total: safeTotal
                        }
                    });
                    return;
                }

                const existing = grouped.get(key);
                const existingQuantity = Number(existing?.pivot?.quantity ?? 1) || 1;
                const existingTotal = Number(existing?.pivot?.total ?? 0) || 0;

                grouped.set(key, {
                    ...existing,
                    pivot: {
                        ...(existing?.pivot || {}),
                        quantity: existingQuantity + safeQuantity,
                        total: existingTotal + safeTotal
                    }
                });
            });

            return Array.from(grouped.values());
        },

        /**
         * Cambia la pestaña activa
         * @param {string} tab 
         */
        async changeTab(tab) {
            this.currentTab = tab;
            this.resetPagination(tab);
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

                    this.orders = this.normalizePayments(result.data || []);
                    // console.log('✅ Total órdenes:', this.orders.length);
                    this.ensurePageInRange(this.currentTab);

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

        // ====================
        // BÚSQUEDA Y PAGINACIÓN
        // ====================

        getFilteredOrders(tab = this.currentTab) {

            const searchTerm = (this.searchTerms[tab] || '').toLowerCase().trim();

            if (!searchTerm) return this.orders;

            return this.orders.filter(order => {

                const clientName = order.client?.name || '';
                const licensePlaque = order.client?.license_plaque || '';
                const services = Array.isArray(order.services)
                ? order.services.map(service => service.name).join(' ')
                : '';

                const userName = order.user?.name || '';
                const statusText = this.getStatusText(order.status) || '';
                const creationDate = order.creation_date || '';
                const hourIn = order.hour_in || '';
                const hourOut = order.hour_out || '';
                const total = order.total !== undefined && order.total !== null ? String(order.total) : '';
                const orderId = order.id !== undefined && order.id !== null ? String(order.id) : '';

                const haystack = [
                    clientName,
                    licensePlaque,
                    services,
                    userName,
                    statusText,
                    creationDate,
                    hourIn,
                    hourOut,
                    total,
                    orderId
                ].map(value => String(value).toLowerCase());

                return haystack.some(value => value.includes(searchTerm));
            });
        },

        getPaginatedOrders(tab = this.currentTab) {

            const filteredData = this.getFilteredOrders(tab);
            const start = (this.currentPage[tab] - 1) * this.perPage;
            const end = start + this.perPage;

            return filteredData.slice(start, end);
        },

        getTotalPages(tab = this.currentTab) {

            const filteredData = this.getFilteredOrders(tab);
            return Math.ceil(filteredData.length / this.perPage);

        },

        goToPage(tab, page) {

            const totalPages = this.getTotalPages(tab);

            if (page >= 1 && page <= totalPages) {
                this.currentPage[tab] = page;
            }

        },

        resetPagination(tab) {

            if (this.currentPage[tab] !== undefined) {
                this.currentPage[tab] = 1;
            }

        },

        ensurePageInRange(tab = this.currentTab) {

            const totalPages = this.getTotalPages(tab);

            if (totalPages === 0) {
                this.currentPage[tab] = 1;
                return;
            }

            if (this.currentPage[tab] > totalPages) {
                this.currentPage[tab] = totalPages;
            }
            
            if (this.currentPage[tab] < 1) {
                this.currentPage[tab] = 1;
            }

        },

        normalizePayments(orders = []) {
            return orders.map(order => {
                if (!order.payment && Array.isArray(order.payments)) {
                    order.payment = order.payments[0] || null;
                }
                return order;
            });
        },

        // ==================== MODAL VISTA RÁPIDA ====================

        /**
         * Abre el modal de vista rápida
         * @param {Object} order 
         */
        openQuickView(order) {
            this.selectedOrder = order;
            this.showQuickViewModal = true;
        },

        /**
         * Cierra el modal de vista rápida
         */
        closeQuickViewModal() {
            this.showQuickViewModal = false;
            this.selectedOrder = null;
        },

        /**
         * Imprime la orden
         * @param {string} orderId 
         */
        printOrder(orderId) {
            window.open(`/orders/${orderId}/print`, '_blank');
        },

        // ==================== MODAL CAMBIO DE ESTADO ====================

        /**
         * Abre el modal de cambio de estado
         * @param {Object} order 
         */
        openStatusModal(order) {
            this.statusModalOrder = order;
            this.newStatus = order.status;
            this.statusChangeNote = '';
            this.showStatusModal = true;
        },

        /**
         * Cierra el modal de cambio de estado
         */
        closeStatusModal() {
            this.showStatusModal = false;
            this.statusModalOrder = null;
            this.newStatus = null;
            this.statusChangeNote = '';
        },

        // ==================== MODAL TIPO DE ESTADO ====================

        openStatusTypeModal(order) {
            const baseOrder = this.orders.find(item => item.id === order?.id) || order;
            if (baseOrder && !baseOrder.payment && Array.isArray(baseOrder.payments)) {
                baseOrder.payment = baseOrder.payments[0] || null;
            }
            this.statusTypeOrder = baseOrder;
            this.showStatusTypeModal = true;
        },

        closeStatusTypeModal() {
            this.showStatusTypeModal = false;
            this.statusTypeOrder = null;
        },

        openOrderStatusFromType() {
            if (!this.statusTypeOrder) return;
            const order = this.statusTypeOrder;
            this.closeStatusTypeModal();
            this.openStatusModal(order);
        },

        openPaymentStatusFromType() {
            if (!this.statusTypeOrder) return;
            const order = this.statusTypeOrder;
            this.closeStatusTypeModal();
            this.openPaymentModal(order);
        },

        // ==================== MODAL CAMBIO DE PAGO ====================

        async openPaymentModal(order) {
            const baseOrder = order || {};

            if (baseOrder?.id && !baseOrder?.client) {
                try {
                    const result = await apiGet(`/orders/${baseOrder.id}`);
                    if (result && result.success && result.data) {
                        this.setPaymentModalData(result.data);
                    } else {
                        this.setPaymentModalData(baseOrder);
                    }
                } catch (error) {
                    this.setPaymentModalData(baseOrder);
                }
            } else {
                this.setPaymentModalData(baseOrder);
            }

            this.showPaymentModal = true;
        },

        closePaymentModal() {
            this.showPaymentModal = false;
            this.paymentModalOrder = null;
            this.newPaymentStatus = null;
            this.paymentPartialAmount = null;
        },

        setPaymentModalData(order) {
            if (order && !order.payment && Array.isArray(order.payments)) {
                order.payment = order.payments[0] || null;
            }
            this.paymentModalOrder = order;
            this.newPaymentStatus = order?.payment?.status || 1;
            this.paymentPartialAmount = order?.partial_payment || null;
        },

        async confirmPaymentChange() {
            if (!this.paymentModalOrder || !this.newPaymentStatus) return;
            if (this.newPaymentStatus === this.paymentModalOrder?.payment?.status) return;

            this.changingPayment = true;

            try {
                const payload = { status: this.newPaymentStatus };

                if (this.newPaymentStatus === 2) {
                    payload.partial_payment = this.paymentPartialAmount;
                }

                const result = await apiPatch(`/orders/${this.paymentModalOrder.id}/payment`, payload);

                if (result.success) {
                    window.notyf?.success('Estado de pago actualizado');
                    await this.loadOrders();
                    this.closePaymentModal();
                } else {
                    window.notyf?.error(result.message || 'Error al actualizar el pago');
                }
            } catch (error) {
                console.error('Error:', error);
                window.notyf?.error('Error al actualizar el pago');
            } finally {
                this.changingPayment = false;
            }
        },

        /**
         * Confirma el cambio de estado
         */
        async confirmStatusChange() {

            if (!this.statusModalOrder || !this.newStatus) return;
            if (this.newStatus === this.statusModalOrder.status) return;

            this.changingStatus = true;

            try {

                const result = await apiPatch(`/orders/${this.statusModalOrder.id}/status`, {
                    status: this.newStatus,
                    note: this.statusChangeNote
                });

                if (result.success) {
                    window.notyf?.success('Estado actualizado correctamente');
                    await this.loadOrders();
                    this.closeStatusModal();
                } else {
                    window.notyf?.error(result.message || 'Error al actualizar el estado');
                }

            } catch (error) {
                console.error('Error:', error);
                window.notyf?.error('Error al actualizar el estado');
            } finally {
                this.changingStatus = false;
            }
        },

        // ==================== MODAL FACTURA ====================

        openInvoiceModal(orderId) {
            this.invoiceOrderId = orderId || null;
            this.showInvoiceModal = true;
        },

        closeInvoiceModal() {
            this.showInvoiceModal = false;
            this.invoiceOrderId = null;
        },

        downloadInvoice() {
            if (!this.invoiceOrderId) {
                this.closeInvoiceModal();
                return;
            }

            window.open(`/orders/${this.invoiceOrderId}/invoice`, '_blank');
            this.closeInvoiceModal();
        },

        // ==================== HELPERS DE PAGO ====================

        /**
         * Obtiene el texto del estado de pago
         * @param {number} status 
         * @returns {string}
         */
        getPaymentStatusText(status) {
            const statuses = { 1: 'Pendiente', 2: 'Parcial', 3: 'Pagado' };
            return statuses[status] || 'Desconocido';
        },

        /**
         * Obtiene la clase del badge de estado de pago
         * @param {number} status 
         * @returns {string}
         */
        getPaymentStatusBadge(status) {
            const badges = {
                1: 'bg-warning text-dark',
                2: 'bg-info text-white',
                3: 'bg-success'
            };
            return badges[status] || 'bg-secondary';
        },

        /**
         * Obtiene el texto del método de pago
         * @param {number} method 
         * @returns {string}
         */
        getPaymentMethodText(method) {
            const methods = { 1: 'Efectivo', 2: 'Tarjeta', 3: 'Transferencia', 4: 'Bizum' };
            return methods[method] || 'N/A';
        },

        // ==================== ENVÍO DE FORMULARIO ====================

        /**
         * Envía el formulario de orden (crear o actualizar)
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
                let result;

                if (this.isEditMode && this.editOrderId) {

                    // Modo edición: usar PUT
                    result = await apiPut(`/orders/${this.editOrderId}`, formData);
                    
                } else {

                    // Modo creación: usar POST
                    result = await apiPost('/orders/store', formData);
                }

                if (result.success) {

                    const message = this.isEditMode 
                        ? '¡Orden actualizada exitosamente!' 
                        : '¡Orden creada exitosamente!';
                    
                    window.notyf?.success(message);

                    if (this.isEditMode) {

                        // Redirigir al inicio después de editar
                        setTimeout(() => {
                            window.location.href = '/';
                        }, 1500);

                    } else {

                        await this.loadOrders();
                        this.resetForm();
                        this.updateConsecutive(result.data?.consecutive);
                        const orderId = result.data?.order?.id;
                        if (orderId) {
                            this.openInvoiceModal(orderId);
                        }

                    }

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
                input.classList.remove('is-valid', 'is-invalid');
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

            // Asegurar que no queden estados de validación en otros campos
            document.querySelectorAll('#orders-root .is-valid, #orders-root .is-invalid').forEach(field => {
                field.classList.remove('is-valid', 'is-invalid');
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

        updateConsecutive(consecutive) {

            if (!consecutive) return;

            const serialInput = document.querySelector('input[name="consecutive_serial"]');
            const numberInput = document.querySelector('input[name="consecutive_number"]');

            if (serialInput && consecutive.date_code) {
                serialInput.value = consecutive.date_code;
            }

            if (numberInput && consecutive.sequence) {
                numberInput.value = consecutive.sequence;
            }
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
 * Crea el componente Alpine para EDITAR órdenes
 * @returns {Object}
 */
function createOrderEditApp() {

    // Módulos (se inicializan una sola vez)
    const calendar = new CalendarManager();
    const services = new ServiceManager();
    const payment = new PaymentManager();
    const timepicker = new TimePickerManager();
    const licensePlate = new LicensePlateValidator();
    const dataCollector = new FormDataCollector();

    return {

        // Estado reactivo
        submitting: false,
        orderId: null,

        /**
         * Inicialización del componente Alpine para edición
         */
        async init() {

            // Obtener datos de la orden desde window
            const orderData = window.orderData;
            this.orderId = orderData?.id;

            // Inicializar módulos
            calendar.init();
            services.init();
            payment.init();
            timepicker.init();
            licensePlate.init();

            // Precargar servicios de la orden si existen
            if (window.orderServices && window.orderServices.length > 0) {
                await this.preloadServices();
            }

            // Configurar botón de guardar
            const confirmBtn = document.querySelector('.confirm-btn');
            if (confirmBtn) {
                confirmBtn.addEventListener('click', (e) => {
                    e.preventDefault();
                    this.updateOrder();
                });
            }
        },

        /**
         * Precarga los servicios de la orden existente
         */
        async preloadServices() {
            // Los servicios ya están renderizados por Blade
            // Solo necesitamos asegurar que los precios estén actualizados
            console.log('Servicios precargados:', window.orderServices.length);
        },

        /**
         * Envía la actualización de la orden
         */
        async updateOrder() {

            if (this.submitting || !this.orderId) return;

            // Validar formulario
            const form = document.getElementById('orders-root');
            if (window.OrderFormValidator) {
                const validator = new window.OrderFormValidator(form);
                if (!validator.validateOrderForm()) {
                    validator.showErrors();
                    return;
                }
            }

            this.submitting = true;

            try {

                const formData = dataCollector.collect();
                
                const response = await fetch(`/orders/${this.orderId}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                    },
                    body: JSON.stringify(formData)
                });

                const result = await response.json();

                if (result.success) {
                    window.notyf?.success('¡Orden actualizada exitosamente!');
                    // Redirigir a la lista después de un momento
                    setTimeout(() => {
                        window.location.href = '/orders';
                    }, 1500);
                } else {
                    this.handleSubmitErrors(result);
                }

            } catch (error) {

                console.error('Error:', error);
                window.notyf?.error('Error al actualizar la orden');

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
                window.notyf?.error(result.message || 'Error al actualizar la orden');
            }

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

    // Exponer los componentes Alpine globalmente SIEMPRE
    // (Alpine los necesita disponibles antes de parsear el DOM)
    window.orderFormApp = createOrderFormApp;

    // Solo continuar si estamos en la vista correcta
    if (!isOrdersView()) return;
    
    // Prevenir inicialización múltiple
    if (isInitialized) return;
    isInitialized = true;

    console.log('Orders module loaded');
}

// Ejecutar inmediatamente (Vite ya maneja el orden de carga)
initOrdersModule();

// Exportar para uso como módulo ES6
export { createOrderFormApp, createOrderEditApp };
export default initOrdersModule;
