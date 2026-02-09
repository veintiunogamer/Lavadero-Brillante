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
                1: 'badge bg-warning',
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

        normalizePayments(orders = []) {
            return orders.map(order => {
                if (!order.payment && Array.isArray(order.payments)) {
                    order.payment = order.payments[0] || null;
                }
                return order;
            });
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
