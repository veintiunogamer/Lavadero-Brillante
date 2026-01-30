
/**
 * Order Main JS - Gestión del formulario de órdenes (index principal)
 * Este archivo maneja toda la lógica del formulario de creación de órdenes en la vista principal
 */

// Verificar si estamos en la vista de órdenes (formulario principal)
function ordersModuleActive() {
	return !!document.getElementById('orders-root');
}

if (typeof window !== 'undefined' && ordersModuleActive()) {

	console.log('Order Main JS cargado');

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

		// ==================== VALIDACIÓN DE MATRÍCULA ====================
		const licensePlateInput = document.getElementById('license-plaque-input');
		const clientNameInput = document.querySelector('input[name="client_name"]');
		const clientPhoneInput = document.getElementById('telefono-whatsapp');
		const licensePlateInfo = document.getElementById('license-plate-info');
		let debounceTimer;

		if (licensePlateInput) {

			licensePlateInput.addEventListener('input', function(e) {

				// Convertir a mayúsculas
				e.target.value = e.target.value.toUpperCase();

				// Limpiar el temporizador anterior
				clearTimeout(debounceTimer);

				// Ocultar mensaje
				if (licensePlateInfo) {
					licensePlateInfo.style.display = 'none';
				}

				// Esperar 500ms después de que el usuario deje de escribir
				debounceTimer = setTimeout(async () => {
					
					const licensePlate = e.target.value.trim();

					if (licensePlate.length >= 4) {

						try {

							const response = await fetch(`/api/clients/check-license-plate?license_plate=${encodeURIComponent(licensePlate)}`);
							
							if (!response.ok) {

								console.error('Error en la respuesta:', response.status);
								return;

							}

							const result = await response.json();
						
							if (result.exists && result.client) {

								// Auto-completar campos del cliente
								if (clientNameInput) {
									clientNameInput.value = result.client.name;
								}

								if (clientPhoneInput) {

									// Establecer el valor del teléfono
									const phoneValue = result.client.phone || '';
									
									// Si Cleave ya está inicializado, necesitamos destruirlo y reinicializar
									if (clientPhoneInput.dataset.cleaveInitialized) {
										delete clientPhoneInput.dataset.cleaveInitialized;
									}
									
									// Limpiar el input primero
									clientPhoneInput.value = '';
									
									// Reinicializar la máscara
									if (typeof window.initPhoneMasks === 'function') {
										window.initPhoneMasks(clientPhoneInput.parentElement);
									}
									
									// Establecer el valor después de inicializar Cleave
									// Cleave formateará automáticamente el valor
									clientPhoneInput.value = phoneValue.replace(/\D/g, '').slice(-9); // Solo los últimos 9 dígitos
								}

								// Mostrar mensaje de éxito
								if (licensePlateInfo) {
									licensePlateInfo.style.display = 'block';
								}

								if (window.notyf) {
									window.notyf.success('Cliente encontrado - datos cargados automáticamente');
								}

							} else {

								// No se encontró cliente
								clientNameInput.value = '';
								clientPhoneInput.value = '';

							}

						} catch (error) {

							console.error('Error verificando matrícula:', error);

							if (window.notyf) {
								window.notyf.error('Error al verificar la matrícula');
							}

						}

					}

				}, 500);
			});
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
			updateOrderDescription();
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
			updateOrderDescription();
		}

		/**
		 * Calcula los totales generales
		 */
		function calculateTotals() {

			const allServiceItems = document.querySelectorAll('.service-item');
			let subtotal = 0;

			// Calcular subtotal
			allServiceItems.forEach(item => {

				const quantity = parseFloat(item.querySelector('.service-quantity')?.value || 0);
				const price = parseFloat(item.querySelector('.service-price')?.value || 0);

				subtotal += quantity * price;

			});

			// Habilitar/deshabilitar el select de descuento según si hay servicios
			const discountSelect = document.getElementById('discount-select');
			
			if (discountSelect) {

				if (subtotal > 0) {
					discountSelect.disabled = false;
				} else {
					discountSelect.disabled = true;
					discountSelect.value = ''; // Resetear el descuento
				}

			}

			// Obtener el porcentaje de descuento seleccionado
			const discountPercent = parseFloat(discountSelect?.value || 0);
			const discountAmount = (subtotal * discountPercent) / 100;
			const total = subtotal - discountAmount;

			// Actualizar subtotal - display y input hidden
			const subtotalSection = document.querySelector('.subtotal-section');
			const subtotalInput = document.querySelector('.subtotal-value');

			if (subtotalSection) subtotalSection.textContent = subtotal.toFixed(2) + '€';
			if (subtotalInput) subtotalInput.value = subtotal.toFixed(2);

			// Actualizar descuento - display y input hidden
			const discountSection = document.querySelector('.discount-section');
			const discountInput = document.querySelector('.discount-value');

			if (discountSection) discountSection.textContent = '-' + discountAmount.toFixed(2) + '€';
			if (discountInput) discountInput.value = discountAmount.toFixed(2);

			// Actualizar total - display y input hidden
			const totalSection = document.querySelector('.total-section');
			const totalInput = document.querySelector('.total-value');

			if (totalSection) totalSection.textContent = total.toFixed(2) + '€';
			if (totalInput) totalInput.value = total.toFixed(2);
		}

		// Actualiza el textarea de descripción de la cita con los servicios seleccionados
		function updateOrderDescription() {

			const descriptionTextarea = document.querySelector('.service-box textarea[name="order_notes"]');

			if (!descriptionTextarea) return;

			const serviceItems = document.querySelectorAll('.service-item');
			let lines = [];

			serviceItems.forEach(item => {

				const serviceSelect = item.querySelector('.service-select');
				const quantityInput = item.querySelector('.service-quantity');
				const serviceName = serviceSelect?.options[serviceSelect.selectedIndex]?.textContent?.trim();
				const quantity = quantityInput?.value || 1;

				if (serviceName && serviceName !== 'Seleccionar servicio') {
					lines.push(`• ${serviceName} x${quantity}`);
				}

			});

			let serviciosText = lines.length > 0 ? lines.join('\n') + '\n' : '';

			descriptionTextarea.value = serviciosText + 'Ninguno de nuestros precios incluye IVA.';
		}

		/**
		 * Inicializa los eventos de una fila de servicio
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

		// ==================== INICIALIZACIÓN DE EVENTOS ====================

		// Inicializar eventos para el servicio original
		initServiceRowEvents(originalService);

		// Evento del select de descuento
		const discountSelect = document.getElementById('discount-select');

		if (discountSelect) {

			discountSelect.addEventListener('change', function() {
				calculateTotals();
			});

			// Inicialmente deshabilitar el select de descuento
			discountSelect.disabled = true;

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
				updateOrderDescription();
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
		let selectedDate = new Date(); // Inicializar con fecha actual
		window.selectedOrderDate = new Date(); // Variable global para Alpine - fecha actual por defecto

		function renderCalendar(date) {

			const year = date.getFullYear();
			const month = date.getMonth();

			// Actualizar cabecera
			const monthNames = [
				'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 
				'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'
			];
			
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
							window.selectedOrderDate = selectedDate; // Actualizar variable global

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
		const paymentStatusInput = document.querySelector('.payment-status-input');
		const partialPaymentContainer = document.getElementById('partial-payment-container');
		const partialPaymentInput = document.getElementById('partial-payment-input');
	
		payStatusButtons.forEach(btn => {

			btn.addEventListener('click', function(e) {
				e.preventDefault();
				
				// Remover clase activa de todos los botones
				payStatusButtons.forEach(b => b.classList.remove('pay-status-active'));
				
				// Agregar clase activa al botón clickeado
				this.classList.add('pay-status-active');
				
				// Actualizar el valor del input hidden con el valor numérico del botón
				if (paymentStatusInput) {
					paymentStatusInput.value = this.getAttribute('data-value');
				}

				// Mostrar/ocultar campo de abono parcial según el estado seleccionado
				const paymentStatus = this.getAttribute('data-value');
				
				if (partialPaymentContainer && partialPaymentInput) {
					if (paymentStatus === '2') { // Estado "Parcial"
						partialPaymentContainer.style.display = 'block';
						partialPaymentInput.required = true;
						partialPaymentInput.classList.add('required-field');
					} else {
						partialPaymentContainer.style.display = 'none';
						partialPaymentInput.value = ''; // Limpiar el valor
						partialPaymentInput.required = false;
						partialPaymentInput.classList.remove('required-field', 'is-invalid', 'is-valid');
					}
				}

			});

		});

		// ==================== TIME PICKER CON FLATPICKR Y FALLBACK ====================
		function initTimePickers() {

			if (typeof flatpickr !== 'undefined') {

				try {

					flatpickr(".time-picker", {
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

			} else {
				console.warn('Flatpickr no está disponible, activando fallback de selects');
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

/**
 * Componente Alpine.js para el formulario de órdenes
 * Maneja el estado del formulario, validación y envío
 */
window.orderFormApp = function() {

    return {

        // Estado del formulario
        currentTab: 'pending',
        orders: [],
        loadingOrders: false,
        submitting: false,

        /**
         * Inicializa el componente
         */
        async init() {
            await this.loadOrders();
            this.setupFormSubmit();
        },

        /**
         * Cambia el tab y recarga las órdenes
         */
        async changeTab(tab) {
            this.currentTab = tab;
            await this.loadOrders();
        },

        /**
         * Carga las órdenes según el tab actual
         */
        async loadOrders() {
            this.loadingOrders = true;
            
            try {
                const response = await fetch(`/orders/tab/${this.currentTab}`, {
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
         * Configura el evento de envío del formulario
         */
        setupFormSubmit() {
            const confirmBtn = document.querySelector('.confirm-btn');
            const termsCheckbox = document.querySelector('input[type="checkbox"]');
            
            // Habilitar/deshabilitar botón según checkbox
            termsCheckbox?.addEventListener('change', function() {
                if (confirmBtn) {
                    confirmBtn.disabled = !this.checked;
                }
            });

            // Evento del botón de confirmación
            confirmBtn?.addEventListener('click', async () => {
                await this.submitOrder();
            });
        },

        /**
         * Recopila los datos del formulario
         */
        collectFormData() {

            // Datos del cliente
            const clientName = document.querySelector('input[name="client_name"]')?.value;
            const clientPhone = document.getElementById('telefono-whatsapp')?.value;
            const licensePlaque = document.querySelector('input[name="license_plaque"]')?.value;
            const assignedUser = document.querySelector('select[name="assigned_user"]')?.value;
            const vehicleTypeId = document.querySelector('select[name="vehicle_type_id"]')?.value;
            const dirtLevel = document.querySelector('select[name="dirt_level"]')?.value;

            // Datos de servicios
            const services = [];
            document.querySelectorAll('.service-item').forEach(item => {
                const serviceId = item.querySelector('.service-select')?.value;
                const quantity = item.querySelector('.service-quantity')?.value;
                const price = item.querySelector('.service-price')?.value;

                if (serviceId) {
                    services.push({
                        service_id: serviceId,
                        quantity: parseInt(quantity),
                        price: parseFloat(price)
                    });
                }
            });

            // Notas
            const vehicleNotes = document.querySelector('textarea[name="vehicle_notes"]')?.value || '';
            const orderNotes = document.querySelector('.service-box textarea[name="order_notes"]')?.value || '';
            const extraNotes = document.querySelector('textarea[name="extra_notes"]')?.value || '';

            // Totales
            const discountSelect = document.getElementById('discount-select');
            const discountPercent = parseFloat(discountSelect?.value || 0);
            const subtotal = parseFloat(document.querySelector('.subtotal-value')?.value || 0);
            const total = parseFloat(document.querySelector('.total-value')?.value || 0);

            // Fecha y horas
            const selectedDate = window.selectedOrderDate ? 
                window.selectedOrderDate.toISOString().split('T')[0] : 
                new Date().toISOString().split('T')[0];
            
            // Obtener horas del input visible o del fallback
            const horaEntradaInput = document.getElementById('hora-entrada');
            const horaEntradaFallback = document.getElementById('hora-entrada-fallback');
            const horaSalidaInput = document.getElementById('hora-salida');
            const horaSalidaFallback = document.getElementById('hora-salida-fallback');
            
            let hourIn = (horaEntradaInput && horaEntradaInput.style.display !== 'none') 
                ? horaEntradaInput.value 
                : horaEntradaFallback?.value;
            
            let hourOut = (horaSalidaInput && horaSalidaInput.style.display !== 'none') 
                ? horaSalidaInput.value 
                : horaSalidaFallback?.value;
            
            // Formatear horas a HH:mm si vienen con segundos
            if (hourIn && hourIn.length > 5) hourIn = hourIn.substring(0, 5);
            if (hourOut && hourOut.length > 5) hourOut = hourOut.substring(0, 5);

            // Estado de pago (1=Pendiente, 2=Parcial, 3=Pagado según backend)
            const activePayBtn = document.querySelector('.pay-status-btn.pay-status-active');
            const paymentStatusValue = activePayBtn?.dataset?.value;
            const paymentStatus = parseInt(paymentStatusValue) || 1;

            // Obtener abono parcial si el estado de pago es "Parcial" (2)
            const partialPaymentInput = document.getElementById('partial-payment-input');
            const partialPayment = (paymentStatus === 2 && partialPaymentInput?.value) 
                ? parseFloat(partialPaymentInput.value) 
                : null;

            const paymentMethod = document.querySelector('select[name="payment_method"]')?.value || 'efectivo';
            const orderStatus = document.querySelector('select[name="status"]')?.value || 1;

            // Facturación (SI/NO)
            const invoiceRequired = document.getElementById('solicitar-factura')?.checked;

            const invoiceData = invoiceRequired ? {
                invoice_required: true,
                invoice_business_name: document.getElementById('razon-social')?.value,
                invoice_tax_id: document.getElementById('nif-cif')?.value,
                invoice_email: document.getElementById('email-factura')?.value,
                invoice_address: document.getElementById('direccion-calle')?.value,
                invoice_postal_code: document.getElementById('direccion-cp')?.value,
                invoice_city: document.getElementById('direccion-ciudad')?.value,
            } : { invoice_required: false };

            return {
                client_name: clientName,
                client_phone: clientPhone,
                license_plaque: licensePlaque,
                assigned_user: assignedUser,
                vehicle_type_id: vehicleTypeId,
                dirt_level: parseInt(dirtLevel) || 1,
                services: services,
                vehicle_notes: vehicleNotes,
                order_notes: orderNotes,
                extra_notes: extraNotes,
                discount: discountPercent,
                subtotal: subtotal,
                total: total,
                selected_date: selectedDate,
                hour_in: hourIn,
                hour_out: hourOut,
                payment_status: paymentStatus,
                partial_payment: partialPayment,
                payment_method: paymentMethod,
                order_status: parseInt(orderStatus),
                ...invoiceData
            };
        },

        /**
         * Envía el formulario de orden
         */
        async submitOrder() {
            if (this.submitting) return;

            // Validar con OrderFormValidator
            const form = document.getElementById('orders-root');
            const validator = new window.OrderFormValidator(form);

            if (!validator.validateOrderForm()) {
                validator.showErrors();
                return;
            }

            this.submitting = true;

            try {
                const formData = this.collectFormData();

                const response = await fetch('/orders/store', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify(formData)
                });

                const result = await response.json();

                if (result.success) {
                    window.notyf?.success('¡Orden creada exitosamente!');
                    
                    // Recargar las órdenes
                    await this.loadOrders();
                    
                    // Limpiar formulario
                    this.resetForm();
                } else {
                    if (result.errors) {
                        Object.values(result.errors).forEach(errors => {
                            errors.forEach(error => window.notyf?.error(error));
                        });
                    } else {
                        window.notyf?.error(result.message || 'Error al crear la orden');
                    }
                }

            } catch (error) {
                console.error('Error:', error);
                window.notyf?.error('Error al enviar la orden');
            } finally {
                this.submitting = false;
            }
        },

        /**
         * Limpia el formulario después de enviar
         */
        resetForm() {

            // Limpiar campos de cliente
            document.querySelectorAll('input[type="text"], input[type="email"], textarea').forEach(input => {
                input.value = '';
            });

            // Resetear servicios
            const serviceItems = document.querySelectorAll('.service-item');

            if (serviceItems.length > 1) {
                // Eliminar servicios adicionales
                for (let i = 1; i < serviceItems.length; i++) {
                    serviceItems[i].remove();
                }
            }

            // Resetear primer servicio
            const firstService = serviceItems[0];

            if (firstService) {
                firstService.querySelectorAll('select').forEach(select => select.value = '');
                firstService.querySelector('.service-quantity').value = 1;
                firstService.querySelector('.service-price').value = '0.00';
            }

            // Limpiar estado de pago
            document.querySelectorAll('.pay-status-btn').forEach(btn => {
                btn.classList.remove('pay-status-active');
            });

            document.querySelector('.pay-status-btn')?.classList.add('pay-status-active');

            // Ocultar y limpiar campo de abono parcial
            const partialPaymentContainer = document.getElementById('partial-payment-container');
            const partialPaymentInput = document.getElementById('partial-payment-input');

            if (partialPaymentContainer) {
                partialPaymentContainer.style.display = 'none';
            }

            if (partialPaymentInput) {
                partialPaymentInput.value = '';
                partialPaymentInput.required = false;
            }

            // Limpiar checkbox de términos
            const termsCheckbox = document.querySelector('input[type="checkbox"]');

            if (termsCheckbox) {
                termsCheckbox.checked = false;
            }

            // Deshabilitar botón de confirmación
            const confirmBtn = document.querySelector('.confirm-btn');

            if (confirmBtn) {
                confirmBtn.disabled = true;
            }

            // Limpiar fecha seleccionada
            window.selectedOrderDate = null;

            // Limpiar facturación
            document.getElementById('solicitar-factura').checked = false;
            document.getElementById('datos-facturacion').style.display = 'none';

            window.notyf?.success('Formulario limpiado');
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
            const date = new Date(time);
            return date.toLocaleTimeString('es-ES', { hour: '2-digit', minute: '2-digit' });
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
                2: 'badge bg-info text-dark',
                3: 'badge bg-success',
                4: 'badge bg-danger'
            };
            return badges[status] || 'badge bg-secondary';
        }
    }
}
