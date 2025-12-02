// JS para funcionalidades de órdenes
function ordersModuleActive() {
	return !!document.getElementById('orders-root');
}

if (typeof window !== 'undefined' && ordersModuleActive()) {
	console.log('Orders JS cargado');
}

// Verificar si estamos en la vista de agendamiento
function agendamientoModuleActive() {
    return !!document.getElementById('agendamiento-root');
}

if (typeof window !== 'undefined' && agendamientoModuleActive()) {
    console.log('Agendamiento JS cargado');
}

// Exponer la función globalmente para Alpine - Vista de Agendamiento
window.agendamientoApp = function() {

    return {
        currentTab: 1, // 1 = Pendientes, 2 = En Proceso, 3 = Terminados
        orders: [],
        loading: false,

        async init() {
            await this.loadOrders(this.currentTab);
        },

        async changeTab(status) {
            this.currentTab = status;
            await this.loadOrders(status);
        },

        async loadOrders(status) {
            this.loading = true;
            
            try {
                const response = await fetch(`/agendamiento/status/${status}`, {
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
                    window.notyf.error('Error al cargar los agendamientos');
                }

            } catch (error) {
                console.error('Error:', error);
                window.notyf.error('Ocurrió un error al cargar los agendamientos');
            } finally {
                this.loading = false;
            }
        },

        getStatusText(status) {
            const statuses = {
                1: 'Pendiente',
                2: 'En Proceso',
                3: 'Terminado',
                4: 'Cancelado'
            };
            return statuses[status] || 'Desconocido';
        },

        getStatusBadge(status) {
            const badges = {
                1: 'badge bg-warning',
                2: 'badge bg-info',
                3: 'badge bg-success',
                4: 'badge bg-danger'
            };
            return badges[status] || 'badge bg-secondary';
        },

        formatDate(date) {
            return new Date(date).toLocaleDateString('es-ES', {
                year: 'numeric',
                month: '2-digit',
                day: '2-digit'
            });
        },

        formatTime(time) {
            if (!time) return 'N/A';
            return time.substring(0, 5); // HH:MM
        },

        formatCurrency(amount) {
            return new Intl.NumberFormat('es-ES', {
                style: 'currency',
                currency: 'EUR'
            }).format(amount);
        }
    }

}

// Aquí iría el resto del código específico de órdenes
