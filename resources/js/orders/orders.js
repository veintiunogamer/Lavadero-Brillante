/**
 * Orders JS - Gestión de la sección de agendamiento/agenda
 * Este archivo maneja la lógica de la vista de agendamiento con Alpine.js
 */

// Verificar si estamos en la vista de agendamiento
function agendamientoModuleActive() {
    return !!document.getElementById('agendamiento-root');
}

if (typeof window !== 'undefined' && agendamientoModuleActive()) {
    console.log('Orders JS cargado - Sección de Agendamiento');
}

/**
 * Componente Alpine para la vista de Agendamiento
 * Exponer la función globalmente para que Alpine la pueda usar
 */
window.agendamientoApp = function() {

    return {
        
        currentTab: 1, // 1 = Pendientes, 2 = En Proceso, 3 = Terminados
        orders: [],
        loading: false,
        perPage: 10,
        currentPage: 1,
        searchTerm: '',
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

        /**
         * Inicializa el componente y carga las órdenes
         */
        async init() {
            await this.loadOrders(this.currentTab);
        },

        /**
         * Cambia de tab y recarga las órdenes
         */
        async changeTab(status) {
            this.currentTab = status;
            this.resetPagination();
            await this.loadOrders(status);
        },

        /**
         * Carga las órdenes según el estado
         */
        async loadOrders(status) {

            this.loading = true;
            
            try {
                const response = await fetch(`/orders/status/${status}`, {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });

                const result = await response.json();

                if (result.success) {
                    this.orders = this.normalizePayments(result.data || []);
                    this.ensurePageInRange();
                } else {
                    window.notyf?.error('Error al cargar los agendamientos');
                }

            } catch (error) {
                console.error('Error:', error);
                window.notyf?.error('Ocurrió un error al cargar los agendamientos');
            } finally {
                this.loading = false;
            }
        },

        /**
         * Obtiene el texto del estado
         */
        getStatusText(status) {
            const statuses = {
                1: 'Pendiente',
                2: 'En Proceso',
                3: 'Terminado',
                4: 'Cancelado'
            };
            return statuses[status] || 'Desconocido';
        },

        /**
         * Obtiene la clase CSS del badge según el estado
         */
        getStatusBadge(status) {
            const badges = {
                1: 'badge bg-warning text-dark',
                2: 'badge bg-info text-white',
                3: 'badge bg-success',
                4: 'badge bg-danger'
            };
            return badges[status] || 'badge bg-secondary';
        },

        /**
         * Obtiene el texto del estado de pago
         */
        getPaymentStatusText(status) {
            const statuses = {
                1: 'Pendiente',
                2: 'Parcial',
                3: 'Pagado'
            };
            return statuses[status] || 'N/A';
        },

        /**
         * Obtiene la clase CSS del badge según el estado de pago
         */
        getPaymentStatusBadge(status) {
            const badges = {
                1: 'badge bg-warning text-dark',
                2: 'badge bg-info text-white',
                3: 'badge bg-success'
            };
            return badges[status] || 'badge bg-secondary';
        },

        /**
         * Obtiene el texto del método de pago
         */
        getPaymentMethodText(method) {
            const methods = {
                1: 'Efectivo',
                2: 'Tarjeta',
                3: 'Transferencia',
                4: 'Bizum'
            };
            return methods[method] || 'N/A';
        },

        normalizePayments(orders = []) {
            return orders.map(order => {
                if (!order.payment && Array.isArray(order.payments)) {
                    order.payment = order.payments[0] || null;
                }
                return order;
            });
        },

        // ====================
        // MODAL VISTA RÁPIDA
        // ====================

        openQuickView(order) {
            this.selectedOrder = order;
            this.showQuickViewModal = true;
        },

        closeQuickViewModal() {
            this.showQuickViewModal = false;
            this.selectedOrder = null;
        },

        printOrder(orderId) {
            window.open(`/orders/${orderId}/print`, '_blank');
        },

        // ====================
        // MODAL CAMBIO DE ESTADO
        // ====================

        openStatusModal(order) {
            this.statusModalOrder = order;
            this.newStatus = order.status;
            this.statusChangeNote = '';
            this.showStatusModal = true;
        },

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
                    const response = await fetch(`/orders/${baseOrder.id}`, {
                        headers: { 'Accept': 'application/json' }
                    });
                    const result = await response.json();
                    if (response.ok && result.success && result.data) {
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

                const response = await fetch(`/orders/${this.paymentModalOrder.id}/payment`, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify(payload)
                });

                const result = await response.json();

                if (result.success) {
                    window.notyf?.success('Estado de pago actualizado');
                    await this.loadOrders(this.currentTab);
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

        async confirmStatusChange() {
            if (!this.statusModalOrder || !this.newStatus) return;
            if (this.newStatus === this.statusModalOrder.status) return;

            this.changingStatus = true;

            try {
                const response = await fetch(`/orders/${this.statusModalOrder.id}/status`, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        status: this.newStatus,
                        note: this.statusChangeNote
                    })
                });

                const result = await response.json();

                if (result.success) {
                    window.notyf?.success('Estado actualizado correctamente');
                    await this.loadOrders(this.currentTab);
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

        /**
         * Formatea una fecha
         */
        formatDate(date) {
            return new Date(date).toLocaleDateString('es-ES', {
                year: 'numeric',
                month: '2-digit',
                day: '2-digit'
            });
        },

        /**
         * Formatea una hora
         */
        formatTime(time) {
            if (!time) return 'N/A';

            if (time instanceof Date) {
                return time.toLocaleTimeString('es-ES', { hour: '2-digit', minute: '2-digit' });
            }

            const value = String(time);
            let timePart = value;

            if (value.includes('T')) {
                timePart = value.split('T')[1] || value;
            } else if (value.includes(' ')) {
                timePart = value.split(' ')[1] || value;
            }

            timePart = timePart.replace('Z', '');

            if (timePart.includes('+')) {
                timePart = timePart.split('+')[0];
            }

            return timePart.substring(0, 5); // HH:MM
        },

        /**
         * Formatea moneda
         */
        formatCurrency(amount) {
            return new Intl.NumberFormat('es-ES', {
                style: 'currency',
                currency: 'EUR'
            }).format(amount);
        },

        // ====================
        // BÚSQUEDA
        // ====================

        getFilteredOrders() {
            const term = (this.searchTerm || '').toLowerCase().trim();
            if (!term) return this.orders;

            return this.orders.filter(order => {
                const clientName = order.client?.name || '';
                const licensePlaque = order.client?.license_plaque || '';
                const services = Array.isArray(order.services)
                    ? order.services.map(service => service.name).join(' ')
                    : '';
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
                    statusText,
                    creationDate,
                    hourIn,
                    hourOut,
                    total,
                    orderId
                ].map(value => String(value).toLowerCase());

                return haystack.some(value => value.includes(term));
            });
        },

        // ====================
        // PAGINACIÓN
        // ====================

        getPaginatedOrders() {
            const filtered = this.getFilteredOrders();
            const start = (this.currentPage - 1) * this.perPage;
            const end = start + this.perPage;
            return filtered.slice(start, end);
        },

        getTotalPages() {
            const total = this.getFilteredOrders().length;
            return Math.ceil(total / this.perPage);
        },

        goToPage(page) {
            const totalPages = this.getTotalPages();
            if (page >= 1 && page <= totalPages) {
                this.currentPage = page;
            }
        },

        resetPagination() {
            this.currentPage = 1;
        },

        ensurePageInRange() {
            const totalPages = this.getTotalPages();
            if (totalPages === 0) {
                this.currentPage = 1;
                return;
            }
            if (this.currentPage > totalPages) {
                this.currentPage = totalPages;
            }
            if (this.currentPage < 1) {
                this.currentPage = 1;
            }
        }
    }

}
