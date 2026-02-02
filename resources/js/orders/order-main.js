/**
 * Order Main JS - Gestión del formulario de órdenes (index principal)
 * Este archivo maneja toda la lógica del formulario de creación de órdenes en la vista principal
 */

(function() {
    'use strict';

    // Verificar si estamos en la vista de órdenes
    function ordersModuleActive() {
        return !!document.getElementById('orders-root');
    }

    // Solo ejecutar si estamos en la vista correcta
    if (!ordersModuleActive()) return;

    // ==================== VARIABLES GLOBALES DEL MÓDULO ====================
    let isInitialized = false;
    let currentDate = new Date();
    let selectedDate = new Date();
    window.selectedOrderDate = new Date();

    // ==================== FUNCIONES AUXILIARES ====================

    async function loadServicesByCategory(categorySelect, serviceSelect) {
        const categoryId = categorySelect.value;
        const serviceRow = categorySelect.closest('.service-item');

        serviceSelect.innerHTML = '<option value="">Selecciona un servicio</option>';
        serviceSelect.disabled = true;

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

    function updateServicePrice(serviceSelect, priceInput) {
        const selectedOption = serviceSelect.options[serviceSelect.selectedIndex];
        const basePrice = selectedOption?.dataset?.value || 0;
        const serviceRow = serviceSelect.closest('.service-item');
        const quantityInput = serviceRow?.querySelector('.service-quantity');
        const quantity = parseFloat(quantityInput?.value || 1);

        if (priceInput) {
            priceInput.dataset.basePrice = basePrice;
            priceInput.value = (parseFloat(basePrice) * quantity).toFixed(2);
        }

        calculateTotals();
        updateOrderDescription();
    }

    function updateQuantityPrice(quantityInput, priceInput) {
        const quantity = parseFloat(quantityInput.value || 1);
        const basePrice = parseFloat(priceInput.dataset.basePrice || 0);
        priceInput.value = (quantity * basePrice).toFixed(2);
        calculateTotals();
        updateOrderDescription();
    }

    function calculateTotals() {
        const allServiceItems = document.querySelectorAll('.service-item');
        let subtotal = 0;

        allServiceItems.forEach(item => {
            const quantity = parseFloat(item.querySelector('.service-quantity')?.value || 0);
            const price = parseFloat(item.querySelector('.service-price')?.value || 0);
            subtotal += quantity * price;
        });

        const discountSelect = document.getElementById('discount-select');

        if (discountSelect) {
            discountSelect.disabled = subtotal <= 0;
            if (subtotal <= 0) discountSelect.value = '';
        }

        const discountPercent = parseFloat(discountSelect?.value || 0);
        const discountAmount = (subtotal * discountPercent) / 100;
        const total = subtotal - discountAmount;

        // Actualizar displays
        const subtotalSection = document.querySelector('.subtotal-section');
        const subtotalInput = document.querySelector('.subtotal-value');
        if (subtotalSection) subtotalSection.textContent = subtotal.toFixed(2) + '€';
        if (subtotalInput) subtotalInput.value = subtotal.toFixed(2);

        const discountSection = document.querySelector('.discount-section');
        const discountInput = document.querySelector('.discount-value');
        if (discountSection) discountSection.textContent = '-' + discountAmount.toFixed(2) + '€';
        if (discountInput) discountInput.value = discountAmount.toFixed(2);

        const totalSection = document.querySelector('.total-section');
        const totalInput = document.querySelector('.total-value');
        if (totalSection) totalSection.textContent = total.toFixed(2) + '€';
        if (totalInput) totalInput.value = total.toFixed(2);
    }

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

            if (serviceName && serviceName !== 'Selecciona un servicio' && serviceName !== 'Seleccionar servicio') {
                lines.push('• ' + serviceName + ' x' + quantity);
            }
        });

        let serviciosText = lines.length > 0 ? lines.join('\n') + '\n' : '';
        descriptionTextarea.value = serviciosText + 'Ninguno de nuestros precios incluye IVA.';
    }

    function initServiceRowEvents(serviceRow) {
        const categorySelect = serviceRow.querySelector('.service-category');
        const serviceSelect = serviceRow.querySelector('.service-select');
        const quantityInput = serviceRow.querySelector('.service-quantity');
        const priceInput = serviceRow.querySelector('.service-price');

        // Usar flag para evitar listeners duplicados
        if (categorySelect && !categorySelect.dataset.initialized) {
            categorySelect.addEventListener('change', () => loadServicesByCategory(categorySelect, serviceSelect));
            categorySelect.dataset.initialized = 'true';
        }

        if (serviceSelect && !serviceSelect.dataset.initialized) {
            serviceSelect.addEventListener('change', () => updateServicePrice(serviceSelect, priceInput));
            serviceSelect.dataset.initialized = 'true';
        }

        if (quantityInput && !quantityInput.dataset.initialized) {
            quantityInput.addEventListener('input', () => updateQuantityPrice(quantityInput, priceInput));
            quantityInput.dataset.initialized = 'true';
        }
    }

    // ==================== CALENDARIO ====================

    function initCalendar() {
        const calendarBox = document.querySelector('.calendar-box');
        if (!calendarBox || calendarBox.dataset.initialized) return;

        const calendarMonthSpan = calendarBox.querySelector('.calendar-month');
        const calendarYearSpan = calendarBox.querySelector('.calendar-year');
        const calendarTbody = calendarBox.querySelector('tbody');
        const navBtns = calendarBox.querySelectorAll('.calendar-nav');
        const prevBtn = navBtns[0];
        const nextBtn = navBtns[1];

        function renderCalendar(date) {
            const year = date.getFullYear();
            const month = date.getMonth();

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

            const firstDay = new Date(year, month, 1);
            const lastDay = new Date(year, month + 1, 0);
            const daysInMonth = lastDay.getDate();

            let startDayOfWeek = firstDay.getDay();
            startDayOfWeek = startDayOfWeek === 0 ? 6 : startDayOfWeek - 1;

            if (calendarTbody) {
                calendarTbody.innerHTML = '';
            }

            let day = 1;

            for (let week = 0; week < 6; week++) {
                const row = document.createElement('tr');

                for (let dayOfWeek = 0; dayOfWeek < 7; dayOfWeek++) {
                    const cell = document.createElement('td');

                    if (week === 0 && dayOfWeek < startDayOfWeek) {
                        cell.textContent = '';
                    } else if (day > daysInMonth) {
                        cell.textContent = '';
                    } else {
                        cell.textContent = day;
                        cell.style.cursor = 'pointer';

                        const currentDay = day;

                        if (selectedDate &&
                            selectedDate.getDate() === day &&
                            selectedDate.getMonth() === month &&
                            selectedDate.getFullYear() === year) {
                            cell.classList.add('calendar-active');
                        }

                        cell.addEventListener('click', function() {
                            calendarTbody.querySelectorAll('td').forEach(td => td.classList.remove('calendar-active'));
                            cell.classList.add('calendar-active');

                            selectedDate = new Date(year, month, currentDay);
                            window.selectedOrderDate = selectedDate;

                            const calendarFooter = calendarBox.querySelector('.calendar-footer .calendar-tip');
                            if (calendarFooter) {
                                const formattedDate = selectedDate.toLocaleDateString('es-ES', {
                                    day: '2-digit',
                                    month: 'long',
                                    year: 'numeric'
                                });
                                calendarFooter.textContent = 'Fecha seleccionada: ' + formattedDate;
                            }
                        });

                        day++;
                    }

                    row.appendChild(cell);
                }

                calendarTbody.appendChild(row);
                if (day > daysInMonth) break;
            }
        }

        if (prevBtn) {
            prevBtn.addEventListener('click', () => {
                currentDate.setMonth(currentDate.getMonth() - 1);
                renderCalendar(currentDate);
            });
        }

        if (nextBtn) {
            nextBtn.addEventListener('click', () => {
                currentDate.setMonth(currentDate.getMonth() + 1);
                renderCalendar(currentDate);
            });
        }

        renderCalendar(currentDate);
        calendarBox.dataset.initialized = 'true';
    }

    // ==================== BOTONES DE ESTADO DE PAGO ====================

    function initPaymentStatusButtons() {
        const payStatusButtons = document.querySelectorAll('.pay-status-btn');
        const paymentStatusInput = document.querySelector('.payment-status-input');
        const partialPaymentContainer = document.getElementById('partial-payment-container');
        const partialPaymentInput = document.getElementById('partial-payment-input');

        payStatusButtons.forEach(btn => {
            if (btn.dataset.initialized) return;

            btn.addEventListener('click', function(e) {
                e.preventDefault();

                payStatusButtons.forEach(b => b.classList.remove('pay-status-active'));
                this.classList.add('pay-status-active');

                if (paymentStatusInput) {
                    paymentStatusInput.value = this.getAttribute('data-value');
                }

                const paymentStatus = this.getAttribute('data-value');

                if (partialPaymentContainer && partialPaymentInput) {
                    if (paymentStatus === '2') {
                        partialPaymentContainer.style.display = 'block';
                        partialPaymentInput.required = true;
                        partialPaymentInput.classList.add('required-field');
                    } else {
                        partialPaymentContainer.style.display = 'none';
                        partialPaymentInput.value = '';
                        partialPaymentInput.required = false;
                        partialPaymentInput.classList.remove('required-field', 'is-invalid', 'is-valid');
                    }
                }

                // Disparar evento para actualizar estado del botón
                document.dispatchEvent(new CustomEvent('formFieldChanged'));
            });

            btn.dataset.initialized = 'true';
        });
    }

    // ==================== TIME PICKERS ====================

    function initTimePickers() {
        if (typeof flatpickr === 'undefined') {
            console.warn('Flatpickr no disponible, activando fallback');
            activateFallback();
            return;
        }

        try {
            document.querySelectorAll('.time-picker').forEach(input => {
                if (input._flatpickr) return; // Ya inicializado

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
                        instance.calendarContainer.style.zIndex = 10000;
                    },
                    onChange: function(selectedDates, dateStr, instance) {
                        validateTimeRange(instance);
                        document.dispatchEvent(new CustomEvent('formFieldChanged'));
                    }
                });
            });
        } catch (error) {
            console.error('Error inicializando Flatpickr:', error);
            activateFallback();
        }
    }

    function validateTimeRange(instance) {
        const horaEntradaEl = document.getElementById('hora-entrada');
        const horaSalidaEl = document.getElementById('hora-salida');

        if (!horaEntradaEl?._flatpickr || !horaSalidaEl?._flatpickr) return;

        const entrada = horaEntradaEl._flatpickr.selectedDates[0];
        const salida = horaSalidaEl._flatpickr.selectedDates[0];

        if (entrada && salida && salida <= entrada) {
            window.notyf?.error('La hora de salida debe ser posterior a la hora de entrada');
            if (instance.element.id === 'hora-salida') {
                instance.clear();
            } else {
                horaSalidaEl._flatpickr.clear();
            }
        }
    }

    function activateFallback() {
        document.querySelectorAll('.time-picker').forEach(input => {
            input.style.display = 'none';
        });

        document.querySelectorAll('.time-picker-fallback').forEach(select => {
            select.style.display = 'block';
        });

        const horaEntradaFallback = document.getElementById('hora-entrada-fallback');
        const horaSalidaFallback = document.getElementById('hora-salida-fallback');

        if (horaEntradaFallback && horaSalidaFallback && !horaSalidaFallback.dataset.initialized) {
            horaSalidaFallback.addEventListener('change', function() {
                const entrada = horaEntradaFallback.value;
                const salida = this.value;

                if (entrada && salida && salida <= entrada) {
                    window.notyf?.error('La hora de salida debe ser posterior a la hora de entrada');
                    this.value = '';
                }
            });
            horaSalidaFallback.dataset.initialized = 'true';
        }
    }

    // ==================== DESCUENTO ====================

    function initDiscountSelect() {
        const discountSelect = document.getElementById('discount-select');
        if (!discountSelect || discountSelect.dataset.initialized) return;

        discountSelect.addEventListener('change', function() {
            calculateTotals();
        });

        // Inicialmente deshabilitar
        discountSelect.disabled = true;
        discountSelect.dataset.initialized = 'true';
    }

    // ==================== COMPONENTE ALPINE ====================

    window.orderFormApp = function() {
        return {
            currentTab: 'pending',
            orders: [],
            loadingOrders: false,
            submitting: false,
            originalService: null,
            addServiceBtn: null,
            serviceRowCounter: 1,

            async init() {
                // Prevenir inicialización múltiple
                if (isInitialized) return;
                isInitialized = true;

                // Inicializar componentes
                initCalendar();
                initPaymentStatusButtons();
                initTimePickers();
                initDiscountSelect();

                await this.loadOrders();
                this.setupServiceRows();
                this.setupFormSubmit();
                this.setupLicensePlateValidation();
            },

            setupServiceRows() {
                this.addServiceBtn = document.querySelector('.add-service-btn');
                this.originalService = document.querySelector('.service-item');

                if (this.originalService) {
                    initServiceRowEvents(this.originalService);
                }

                if (this.addServiceBtn && !this.addServiceBtn.dataset.initialized) {
                    this.addServiceBtn.addEventListener('click', () => this.addNewServiceRow());
                    this.addServiceBtn.dataset.initialized = 'true';
                }
            },

            addNewServiceRow() {
                if (!this.originalService) return;

                const clone = this.originalService.cloneNode(true);

                // Limpiar valores y flags de inicialización
                clone.querySelectorAll('input, select, textarea').forEach(el => {
                    el.value = el.defaultValue || '';
                    delete el.dataset.initialized;
                });

                // Actualizar data-service-row
                this.serviceRowCounter++;
                clone.querySelectorAll('[data-service-row]').forEach(el => {
                    el.dataset.serviceRow = this.serviceRowCounter;
                });

                // Agregar botón eliminar
                let btnCol = clone.querySelector('.col-lg-1.d-flex');
                if (!btnCol) {
                    btnCol = document.createElement('div');
                    btnCol.className = 'col-lg-1 d-flex align-items-center px-2';
                    btnCol.style.paddingTop = '1.7rem';
                    clone.appendChild(btnCol);
                } else {
                    btnCol.innerHTML = '';
                }

                const removeBtn = document.createElement('button');
                removeBtn.className = 'remove-btn btn btn-sm btn-danger';
                removeBtn.type = 'button';
                removeBtn.innerHTML = '<i class="fa-solid fa-times"></i>';
                removeBtn.addEventListener('click', () => {
                    clone.remove();
                    calculateTotals();
                    updateOrderDescription();
                });

                btnCol.appendChild(removeBtn);

                // Inicializar eventos
                initServiceRowEvents(clone);

                // Insertar después del último service-item
                const serviceItems = document.querySelectorAll('.service-item');
                const lastItem = serviceItems[serviceItems.length - 1];
                lastItem.insertAdjacentElement('afterend', clone);
            },

            setupLicensePlateValidation() {
                const licensePlateInput = document.getElementById('license-plaque-input');
                const clientNameInput = document.querySelector('input[name="client_name"]');
                const clientPhoneInput = document.getElementById('telefono-whatsapp');
                const licensePlateInfo = document.getElementById('license-plate-info');

                if (!licensePlateInput || licensePlateInput.dataset.initialized) return;

                let debounceTimer;

                licensePlateInput.addEventListener('input', async function(e) {
                    e.target.value = e.target.value.toUpperCase();
                    clearTimeout(debounceTimer);

                    if (licensePlateInfo) licensePlateInfo.style.display = 'none';

                    debounceTimer = setTimeout(async () => {
                        const licensePlate = e.target.value.trim();
                        if (licensePlate.length >= 4) {
                            try {
                                const response = await fetch('/api/clients/check-license-plate?license_plate=' + encodeURIComponent(licensePlate));
                                if (!response.ok) return;

                                const result = await response.json();
                                if (result.exists && result.client) {
                                    if (clientNameInput) clientNameInput.value = result.client.name;
                                    if (clientPhoneInput) {
                                        const phoneValue = result.client.phone || '';
                                        clientPhoneInput.value = phoneValue.replace(/\D/g, '').slice(-9);
                                    }
                                    if (licensePlateInfo) licensePlateInfo.style.display = 'block';
                                    window.notyf?.success('Cliente encontrado - datos cargados automáticamente');
                                } else {
                                    if (clientNameInput) clientNameInput.value = '';
                                    if (clientPhoneInput) clientPhoneInput.value = '';
                                }
                                
                                // Disparar evento para actualizar estado del botón
                                document.dispatchEvent(new CustomEvent('formFieldChanged'));
                            } catch (error) {
                                console.error('Error verificando matrícula:', error);
                            }
                        }
                    }, 500);
                });

                licensePlateInput.dataset.initialized = 'true';
            },

            setupFormSubmit() {
                const confirmBtn = document.querySelector('.confirm-btn');
                const termsCheckbox = document.getElementById('terms-checkbox');

                if (!confirmBtn || confirmBtn.dataset.initialized) return;

                const self = this;

                // Función para validar si el formulario está completo
                const isFormValid = () => {
                    const requiredFields = document.querySelectorAll('.required-field');
                    for (let field of requiredFields) {
                        // Ignorar campos ocultos o deshabilitados
                        if (field.offsetParent === null || field.disabled) continue;
                        if (field.closest('[style*="display: none"]') || field.closest('[style*="display:none"]')) continue;

                        const value = field.value?.trim();
                        if (!value) return false;
                    }
                    return true;
                };

                // Función para actualizar estado del botón
                const updateConfirmBtnState = () => {
                    const checkbox = document.getElementById('terms-checkbox');
                    const termsChecked = checkbox?.checked || false;
                    const btn = document.querySelector('.confirm-btn');
                    if (btn) {
                        btn.disabled = !(termsChecked && isFormValid());
                    }
                };

                // Evento del checkbox
                if (termsCheckbox && !termsCheckbox.dataset.initialized) {
                    termsCheckbox.addEventListener('change', updateConfirmBtnState);
                    termsCheckbox.dataset.initialized = 'true';
                }

                // Escuchar cambios en campos requeridos
                document.querySelectorAll('.required-field').forEach(field => {
                    if (!field.dataset.formListener) {
                        field.addEventListener('input', updateConfirmBtnState);
                        field.addEventListener('change', updateConfirmBtnState);
                        field.dataset.formListener = 'true';
                    }
                });

                // Escuchar evento personalizado para cambios en el formulario
                document.addEventListener('formFieldChanged', updateConfirmBtnState);

                // Evento del botón de confirmar
                confirmBtn.addEventListener('click', async (e) => {
                    e.preventDefault();
                    await self.submitOrder();
                });

                confirmBtn.dataset.initialized = 'true';

                // Estado inicial
                updateConfirmBtnState();
            },

            async changeTab(tab) {
                this.currentTab = tab;
                await this.loadOrders();
            },

            async loadOrders() {
                this.loadingOrders = true;

                try {
                    const response = await fetch('/orders/tab/' + this.currentTab, {
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

            collectFormData() {
                const clientName = document.querySelector('input[name="client_name"]')?.value;
                const clientPhone = document.getElementById('telefono-whatsapp')?.value;
                const licensePlaque = document.querySelector('input[name="license_plaque"]')?.value;
                const assignedUser = document.querySelector('select[name="assigned_user"]')?.value;
                const vehicleTypeId = document.querySelector('select[name="vehicle_type_id"]')?.value;
                const dirtLevel = document.querySelector('select[name="dirt_level"]')?.value;

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

                const vehicleNotes = document.querySelector('textarea[name="vehicle_notes"]')?.value || '';
                const orderNotes = document.querySelector('.service-box textarea[name="order_notes"]')?.value || '';
                const extraNotes = document.querySelector('textarea[name="extra_notes"]')?.value || '';

                const discountSelect = document.getElementById('discount-select');
                const discountPercent = parseFloat(discountSelect?.value || 0);
                const subtotal = parseFloat(document.querySelector('.subtotal-value')?.value || 0);
                const total = parseFloat(document.querySelector('.total-value')?.value || 0);

                const selectedDateValue = window.selectedOrderDate ?
                    window.selectedOrderDate.toISOString().split('T')[0] :
                    new Date().toISOString().split('T')[0];

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

                if (hourIn && hourIn.length > 5) hourIn = hourIn.substring(0, 5);
                if (hourOut && hourOut.length > 5) hourOut = hourOut.substring(0, 5);

                const activePayBtn = document.querySelector('.pay-status-btn.pay-status-active');
                const paymentStatus = parseInt(activePayBtn?.dataset?.value) || 1;

                const partialPaymentInput = document.getElementById('partial-payment-input');
                const partialPayment = (paymentStatus === 2 && partialPaymentInput?.value)
                    ? parseFloat(partialPaymentInput.value)
                    : null;

                const paymentMethod = document.querySelector('select[name="payment_method"]')?.value || '1';
                const orderStatus = document.querySelector('select[name="status"]')?.value || '1';

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
                    selected_date: selectedDateValue,
                    hour_in: hourIn,
                    hour_out: hourOut,
                    payment_status: paymentStatus,
                    partial_payment: partialPayment,
                    payment_method: parseInt(paymentMethod),
                    order_status: parseInt(orderStatus),
                    ...invoiceData
                };
            },

            async submitOrder() {
                if (this.submitting) return;

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
                        await this.loadOrders();
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

            resetForm() {
                // Limpiar campos de texto
                document.querySelectorAll('#orders-root input[type="text"], #orders-root input[type="email"], #orders-root textarea').forEach(input => {
                    if (!input.readOnly) input.value = '';
                });

                // Resetear servicios extra (dejar solo el primero)
                const serviceItems = document.querySelectorAll('.service-item');
                for (let i = serviceItems.length - 1; i > 0; i--) {
                    serviceItems[i].remove();
                }

                const firstService = serviceItems[0];
                if (firstService) {
                    firstService.querySelectorAll('select').forEach(select => select.value = '');
                    const quantityInput = firstService.querySelector('.service-quantity');
                    const priceInput = firstService.querySelector('.service-price');
                    if (quantityInput) quantityInput.value = 1;
                    if (priceInput) priceInput.value = '0.00';
                }

                // Estado de pago a "Pendiente"
                document.querySelectorAll('.pay-status-btn').forEach((btn, index) => {
                    btn.classList.toggle('pay-status-active', index === 0);
                });

                const partialPaymentContainer = document.getElementById('partial-payment-container');
                const partialPaymentInput = document.getElementById('partial-payment-input');
                if (partialPaymentContainer) partialPaymentContainer.style.display = 'none';
                if (partialPaymentInput) {
                    partialPaymentInput.value = '';
                    partialPaymentInput.required = false;
                }

                // Checkbox y botón
                const termsCheckbox = document.getElementById('terms-checkbox');
                if (termsCheckbox) termsCheckbox.checked = false;

                const confirmBtn = document.querySelector('.confirm-btn');
                if (confirmBtn) confirmBtn.disabled = true;

                // Fecha a hoy
                window.selectedOrderDate = new Date();

                // Facturación
                const solicitarFactura = document.getElementById('solicitar-factura');
                const datosFacturacion = document.getElementById('datos-facturacion');
                if (solicitarFactura) solicitarFactura.checked = false;
                if (datosFacturacion) datosFacturacion.style.display = 'none';

                // Recalcular totales
                calculateTotals();
            },

            // Utilidades de formato para la tabla de órdenes
            formatDate(date) {
                return new Date(date).toLocaleDateString('es-ES', {
                    year: 'numeric',
                    month: '2-digit',
                    day: '2-digit'
                });
            },

            formatTime(time) {
                if (!time) return 'N/A';
                const date = new Date(time);
                return date.toLocaleTimeString('es-ES', { hour: '2-digit', minute: '2-digit' });
            },

            formatCurrency(amount) {
                return new Intl.NumberFormat('es-ES', {
                    style: 'currency',
                    currency: 'EUR'
                }).format(amount);
            },

            getStatusText(status) {
                const statuses = { 1: 'Pendiente', 2: 'En Proceso', 3: 'Terminado', 4: 'Cancelado' };
                return statuses[status] || 'Desconocido';
            },

            getStatusBadge(status) {
                const badges = {
                    1: 'badge bg-warning text-dark',
                    2: 'badge bg-info text-dark',
                    3: 'badge bg-success',
                    4: 'badge bg-danger'
                };
                return badges[status] || 'badge bg-secondary';
            }
        };
    };

})();
