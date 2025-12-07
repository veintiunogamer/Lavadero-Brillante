// JS para funcionalidades de órdenes
function ordersModuleActive() {
	return !!document.getElementById('orders-root');
}

if (typeof window !== 'undefined' && ordersModuleActive()) {

	console.log('Orders JS cargado');

    document.addEventListener('DOMContentLoaded', function () {

		const addServiceBtn = document.querySelector('.add-service-btn');
		const serviceContainer = document.querySelector('.service-item').parentNode;
		const originalService = document.querySelector('.service-item');

		// Eliminar completamente el botón eliminar del original si existe
		const originalRemoveBtn = originalService.querySelector('.remove-btn');

		if (originalRemoveBtn) {
			originalRemoveBtn.parentNode.removeChild(originalRemoveBtn);
		}

        // Evento para agregar un nuevo servicio
		addServiceBtn.addEventListener('click', function () {

			// Clonar el bloque de servicio
			const clone = originalService.cloneNode(true);

			// Limpiar los valores de los campos del clon
			clone.querySelectorAll('input, select, textarea').forEach(el => {
				if (el.tagName === 'SELECT' || el.tagName === 'TEXTAREA') {
					el.value = '';
				} else if (el.type === 'number' || el.type === 'text') {
					el.value = el.defaultValue || '';
				}
			});

			// Agregar botón eliminar solo al clon
			let removeBtn = document.createElement('button');
			removeBtn.className = 'remove-btn btn btn-sm btn-danger';
			removeBtn.type = 'button';
			removeBtn.innerHTML = '<i class="fa-solid fa-times"></i>';

			// Buscar el contenedor adecuado para el botón
			let btnCol = clone.querySelector('.col-1.d-flex');

			if (!btnCol) {

				// Si no existe, crear uno
				btnCol = document.createElement('div');
				btnCol.className = 'col-1 d-flex align-items-center';
				btnCol.style.paddingTop = '1.7rem';
				clone.appendChild(btnCol);

			} else {

				// Limpiar cualquier botón previo
				btnCol.innerHTML = '';

			}

			btnCol.appendChild(removeBtn);

			// Evento para eliminar el clon
			removeBtn.onclick = function () {
				clone.remove();
			};

			// Insertar el clon justo después del último .service-item
			const serviceItems = serviceContainer.querySelectorAll('.service-item');
			const lastItem = serviceItems[serviceItems.length - 1];
			lastItem.insertAdjacentElement('afterend', clone);

		});

	});

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
