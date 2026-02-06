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
                    this.orders = result.data;
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
                2: 'badge bg-info',
                3: 'badge bg-success',
                4: 'badge bg-danger'
            };
            return badges[status] || 'badge bg-secondary';
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
        }
    }

}
