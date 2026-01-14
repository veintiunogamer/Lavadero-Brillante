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
		let serviceRowCounter = 1;

		// Eliminar completamente el botón eliminar del original si existe
		const originalRemoveBtn = originalService.querySelector('.remove-btn');

		if (originalRemoveBtn) {
			originalRemoveBtn.parentNode.removeChild(originalRemoveBtn);
		}

		// ==================== FUNCIONES AUXILIARES ====================

		/**
		 * Carga los servicios según la categoría seleccionada
		 */
		async function loadServicesByCategory(categorySelect, serviceSelect) {
			const categoryId = categorySelect.value;
			const serviceRow = categorySelect.closest('.service-item');

			// Limpiar el select de servicios
			serviceSelect.innerHTML = '<option value="">Selecciona un servicio</option>';
			serviceSelect.disabled = true;

			// Resetear cantidad a 1 y precio a 0
			if (serviceRow) {
				const quantityInput = serviceRow.querySelector('.service-quantity');
				const priceInput = serviceRow.querySelector('.service-price');
				if (quantityInput) quantityInput.value = 1;
				if (priceInput) priceInput.value = '0.00';
			}

			if (!categoryId) {
				calculateTotals();
				return;
			}

			try {
				const response = await fetch(`/api/services/category/${categoryId}`, {
					headers: {
						'Accept': 'application/json',
						'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
					}
				});

				const result = await response.json();

				if (result.success && result.data.length > 0) {
					result.data.forEach(service => {
						const option = document.createElement('option');
						option.value = service.id;
						option.textContent = service.name;
						option.dataset.value = service.value;
						serviceSelect.appendChild(option);
					});
					serviceSelect.disabled = false;
				} else {
					serviceSelect.innerHTML = '<option value="">No hay servicios disponibles</option>';
				}
			} catch (error) {
				console.error('Error cargando servicios:', error);
				serviceSelect.innerHTML = '<option value="">Error al cargar servicios</option>';
			}
		}

		/**
		 * Actualiza el precio cuando se selecciona un servicio
		 */
		function updateServicePrice(serviceSelect, priceInput) {
			const selectedOption = serviceSelect.options[serviceSelect.selectedIndex];
			const basePrice = selectedOption?.dataset?.value || 0;
			const serviceRow = serviceSelect.closest('.service-item');
			const quantityInput = serviceRow?.querySelector('.service-quantity');
			const quantity = parseFloat(quantityInput?.value || 1);
			
			// Guardar el precio base como data attribute
			if (priceInput) {
				priceInput.dataset.basePrice = basePrice;
				priceInput.value = (parseFloat(basePrice) * quantity).toFixed(2);
			}
			calculateTotals();
		}

		/**
		 * Calcula precio al cambiar cantidad
		 */
		function updateQuantityPrice(quantityInput, priceInput) {
			const quantity = parseFloat(quantityInput.value || 1);
			const basePrice = parseFloat(priceInput.dataset.basePrice || 0);
			
			// Calcular el precio total: cantidad * precio base
			priceInput.value = (quantity * basePrice).toFixed(2);
			calculateTotals();
		}

		/**
		 * Calcula los totales generales
		 */
		function calculateTotals() {
			const allServiceItems = document.querySelectorAll('.service-item');
			let subtotal = 0;

			allServiceItems.forEach(item => {
				const quantity = parseFloat(item.querySelector('.service-quantity')?.value || 0);
				const price = parseFloat(item.querySelector('.service-price')?.value || 0);
				subtotal += quantity * price;
			});

			// Buscar y actualizar subtotal y total
			const inputGroups = document.querySelectorAll('.input-group');
			inputGroups.forEach(group => {
				const label = group.querySelector('label');
				const display = group.querySelector('div');
				
				if (label && display) {
					if (label.textContent.includes('Subtotal')) {
						display.textContent = subtotal.toFixed(2) + '€';
					}
					if (label.textContent.includes('Total')) {
						display.textContent = subtotal.toFixed(2) + '€';
					}
				}
			});
		}

		/**
		 * Inicializa eventos para una fila de servicio
		 */
		function initServiceRowEvents(serviceRow) {
			const categorySelect = serviceRow.querySelector('.service-category');
			const serviceSelect = serviceRow.querySelector('.service-select');
			const quantityInput = serviceRow.querySelector('.service-quantity');
			const priceInput = serviceRow.querySelector('.service-price');

			// Evento: cambio de categoría
			categorySelect?.addEventListener('change', function() {
				loadServicesByCategory(categorySelect, serviceSelect);
			});

			// Evento: cambio de servicio
			serviceSelect?.addEventListener('change', function() {
				updateServicePrice(serviceSelect, priceInput);
			});

			// Evento: cambio de cantidad
			quantityInput?.addEventListener('input', function() {
				updateQuantityPrice(quantityInput, priceInput);
			});
		}

		// Inicializar eventos para el servicio original
		initServiceRowEvents(originalService);

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

			// Actualizar data-service-row
			serviceRowCounter++;
			clone.querySelectorAll('[data-service-row]').forEach(el => {
				el.dataset.serviceRow = serviceRowCounter;
			});

			// Agregar botón eliminar solo al clon
			let removeBtn = document.createElement('button');
			removeBtn.className = 'remove-btn btn btn-sm btn-danger';
			removeBtn.type = 'button';
			removeBtn.innerHTML = '<i class="fa-solid fa-times"></i>';

			// Buscar el contenedor adecuado para el botón
			let btnCol = clone.querySelector('.col-lg-1.d-flex');

			if (!btnCol) {

				// Si no existe, crear uno
				btnCol = document.createElement('div');
				btnCol.className = 'col-lg-1 d-flex align-items-center px-2';
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
				calculateTotals();
			};

			// Inicializar eventos para el nuevo clon
			initServiceRowEvents(clone);

			// Insertar el clon justo después del último .service-item
			const serviceItems = serviceContainer.querySelectorAll('.service-item');
			const lastItem = serviceItems[serviceItems.length - 1];
			lastItem.insertAdjacentElement('afterend', clone);

		});

		// ==================== CALENDARIO FUNCIONAL ====================

		const calendarBox = document.querySelector('.calendar-box');
		const calendarMonthSpan = calendarBox?.querySelector('.calendar-month');
		const calendarYearSpan = calendarBox?.querySelector('.calendar-year');
		const calendarTbody = calendarBox?.querySelector('tbody');
		const prevBtn = calendarBox?.querySelectorAll('.calendar-nav')[0];
		const nextBtn = calendarBox?.querySelectorAll('.calendar-nav')[1];

		let currentDate = new Date();
		let selectedDate = null;

		function renderCalendar(date) {
			const year = date.getFullYear();
			const month = date.getMonth();

			// Actualizar cabecera
			const monthNames = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 
			                    'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
			
			if (calendarMonthSpan) {
				calendarMonthSpan.childNodes[0].textContent = monthNames[month] + ' ';
			}

			if (calendarYearSpan) {
				calendarYearSpan.textContent = year;
			}

			// Obtener primer día del mes y días totales
			const firstDay = new Date(year, month, 1);
			const lastDay = new Date(year, month + 1, 0);
			const daysInMonth = lastDay.getDate();
			
			// Ajustar para que lunes sea el primer día (0 = domingo, 1 = lunes)
			let startDayOfWeek = firstDay.getDay();
			startDayOfWeek = startDayOfWeek === 0 ? 6 : startDayOfWeek - 1;

			// Limpiar tbody
			if (calendarTbody) {
				calendarTbody.innerHTML = '';
			}

			let day = 1;
			let nextMonthDay = 1;

			// Generar 6 semanas para cubrir todos los casos
			for (let week = 0; week < 6; week++) {
				const row = document.createElement('tr');

				for (let dayOfWeek = 0; dayOfWeek < 7; dayOfWeek++) {
					const cell = document.createElement('td');

					if (week === 0 && dayOfWeek < startDayOfWeek) {
						// Días del mes anterior (vacíos o marcados como muted)
						cell.textContent = '';
					} else if (day > daysInMonth) {
						// Días del mes siguiente (opcional: mostrarlos como muted)
						cell.textContent = '';
						nextMonthDay++;
					} else {
						// Días del mes actual
						cell.textContent = day;
						cell.style.cursor = 'pointer';

						// Verificar si es el día seleccionado
						if (selectedDate && 
						    selectedDate.getDate() === day && 
						    selectedDate.getMonth() === month && 
						    selectedDate.getFullYear() === year) {
							cell.classList.add('calendar-active');
						}

						// Evento click
						cell.addEventListener('click', function() {
							// Remover clase activa de todas las celdas
							calendarTbody.querySelectorAll('td').forEach(td => {
								td.classList.remove('calendar-active');
							});

							// Agregar clase activa a la celda clickeada
							cell.classList.add('calendar-active');

							// Guardar fecha seleccionada
							selectedDate = new Date(year, month, parseInt(cell.textContent));

							// Actualizar el footer del calendario
							const calendarFooter = calendarBox.querySelector('.calendar-footer .calendar-tip');
							if (calendarFooter) {
								const formattedDate = selectedDate.toLocaleDateString('es-ES', {
									day: '2-digit',
									month: 'long',
									year: 'numeric'
								});
								calendarFooter.textContent = `Fecha seleccionada: ${formattedDate}`;
							}
						});

						day++;
					}

					row.appendChild(cell);
				}

				calendarTbody.appendChild(row);

				// Si ya terminamos todos los días del mes, salir del loop
				if (day > daysInMonth) {
					break;
				}
			}
		}

		// Eventos de navegación del calendario
		prevBtn?.addEventListener('click', function() {
			currentDate.setMonth(currentDate.getMonth() - 1);
			renderCalendar(currentDate);
		});

		nextBtn?.addEventListener('click', function() {
			currentDate.setMonth(currentDate.getMonth() + 1);
			renderCalendar(currentDate);
		});

		// Renderizar calendario inicial
		if (calendarBox) {
			renderCalendar(currentDate);
		}

		// ==================== BOTONES DE ESTADO DE PAGO ====================
		
		const payStatusButtons = document.querySelectorAll('.pay-status-btn');
		
		payStatusButtons.forEach(btn => {
			btn.addEventListener('click', function(e) {
				e.preventDefault();
				
				// Remover clase activa de todos los botones
				payStatusButtons.forEach(b => b.classList.remove('pay-status-active'));
				
				// Agregar clase activa al botón clickeado
				this.classList.add('pay-status-active');
			});
		});

		// ==================== TIME PICKER CON FLATPICKR Y FALLBACK ====================

		/**
		 * Inicializa Flatpickr para los time pickers
		 * Si Flatpickr no está disponible (CDN falla), usa el fallback de selects
		 */
		function initTimePickers() {
			const timePickers = document.querySelectorAll('.time-picker');
			
			// Verificar si Flatpickr está disponible
			if (typeof flatpickr !== 'undefined') {
				console.log('Flatpickr cargado correctamente - Usando time picker visual');
				
				timePickers.forEach(input => {
					try {
						flatpickr(input, {
							enableTime: true,
							noCalendar: true,
							dateFormat: "H:i",
							time_24hr: true,
							minuteIncrement: 30,
							minTime: "08:00",
							maxTime: "20:30",
							locale: {
								firstDayOfWeek: 1,
								weekdays: {
									shorthand: ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb'],
									longhand: ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado']
								},
								months: {
									shorthand: ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'],
									longhand: ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre']
								}
							},
							onReady: function(dateObj, dateStr, instance) {
								// Personalización adicional si es necesaria
								instance.calendarContainer.style.zIndex = 10000;
							},
							onChange: function(selectedDates, dateStr, instance) {
								// Validación: la hora de salida debe ser mayor que la hora de entrada
								if (instance.element.id === 'hora-entrada') {
									const horaSalida = document.getElementById('hora-salida')._flatpickr;
									if (horaSalida && horaSalida.selectedDates.length > 0) {
										const entrada = selectedDates[0];
										const salida = horaSalida.selectedDates[0];
										
										if (salida <= entrada) {
											window.notyf?.error('La hora de salida debe ser posterior a la hora de entrada');
											horaSalida.clear();
										}
									}
								}
								
								if (instance.element.id === 'hora-salida') {
									const horaEntrada = document.getElementById('hora-entrada')._flatpickr;
									if (horaEntrada && horaEntrada.selectedDates.length > 0) {
										const entrada = horaEntrada.selectedDates[0];
										const salida = selectedDates[0];
										
										if (salida <= entrada) {
											window.notyf?.error('La hora de salida debe ser posterior a la hora de entrada');
											instance.clear();
										}
									}
								}
							}
						});
					} catch (error) {
						console.error('Error inicializando Flatpickr:', error);
						activateFallback();
					}
				});
			} else {
				console.warn('Flatpickr no disponible - Usando fallback de selects');
				activateFallback();
			}
		}

		/**
		 * Activa el fallback de selects cuando Flatpickr no está disponible
		 */
		function activateFallback() {
			const timePickers = document.querySelectorAll('.time-picker');
			const fallbackSelects = document.querySelectorAll('.time-picker-fallback');
			
			timePickers.forEach(input => {
				input.style.display = 'none';
			});
			
			fallbackSelects.forEach(select => {
				select.style.display = 'block';
			});
			
			// Agregar validación para los selects de fallback
			const horaEntradaFallback = document.getElementById('hora-entrada-fallback');
			const horaSalidaFallback = document.getElementById('hora-salida-fallback');
			
			if (horaEntradaFallback && horaSalidaFallback) {
				horaSalidaFallback.addEventListener('change', function() {
					const entrada = horaEntradaFallback.value;
					const salida = this.value;
					
					if (entrada && salida && salida <= entrada) {
						window.notyf?.error('La hora de salida debe ser posterior a la hora de entrada');
						this.value = '';
					}
				});
			}
		}

		// Inicializar time pickers
		initTimePickers();

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
