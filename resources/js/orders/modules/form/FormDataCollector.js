/**
 * FormDataCollector - Recolección de datos del formulario de orden
 * @module modules/form/FormDataCollector
 */

export class FormDataCollector {
    constructor(modules) {
        this.modules = modules;
    }

    /**
     * Recolecta todos los datos del formulario
     * @returns {Object}
     */
    collect() {
        return {
            ...this.getConsecutiveData(),
            ...this.getClientData(),
            ...this.getVehicleData(),
            ...this.getServicesData(),
            ...this.getNotesData(),
            ...this.getPricingData(),
            ...this.getScheduleData(),
            ...this.getPaymentData(),
            ...this.getInvoiceData()
        };
    }

    /**
     * Obtiene los consecutivos del formulario
     * @returns {Object}
     */
    getConsecutiveData() {
        return {
            consecutive_serial: document.querySelector('input[name="consecutive_serial"]')?.value || '',
            consecutive_number: document.querySelector('input[name="consecutive_number"]')?.value || ''
        };
    }

    /**
     * Obtiene datos del cliente
     * @returns {Object}
     */
    getClientData() {
        return {
            client_name: document.querySelector('input[name="client_name"]')?.value || '',
            client_phone: document.getElementById('telefono-whatsapp')?.value || '',
            license_plaque: document.querySelector('input[name="license_plaque"]')?.value || ''
        };
    }

    /**
     * Obtiene datos del vehículo
     * @returns {Object}
     */
    getVehicleData() {
        return {
            assigned_user: document.querySelector('select[name="assigned_user"]')?.value || '',
            vehicle_type_id: document.querySelector('select[name="vehicle_type_id"]')?.value || '',
            dirt_level: parseInt(document.querySelector('select[name="dirt_level"]')?.value) || 1
        };
    }

    /**
     * Obtiene datos de servicios
     * @returns {Object}
     */
    getServicesData() {
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

        return { services };
    }

    /**
     * Obtiene notas de la orden
     * @returns {Object}
     */
    getNotesData() {
        return {
            vehicle_notes: document.querySelector('textarea[name="vehicle_notes"]')?.value || '',
            order_notes: document.querySelector('.service-box textarea[name="order_notes"]')?.value || '',
            extra_notes: document.querySelector('textarea[name="extra_notes"]')?.value || ''
        };
    }

    /**
     * Obtiene datos de precios
     * @returns {Object}
     */
    getPricingData() {
        const discountValue = document.querySelector('.discount-value');

        return {
            discount: parseFloat(discountValue?.value || 0),
            subtotal: parseFloat(document.querySelector('.subtotal-value')?.value || 0),
            total: parseFloat(document.querySelector('.total-value')?.value || 0)
        };
    }

    /**
     * Obtiene datos de programación (fecha y horas)
     * @returns {Object}
     */
    getScheduleData() {
        const selectedDateValue = window.selectedOrderDate
            ? window.selectedOrderDate.toISOString().split('T')[0]
            : new Date().toISOString().split('T')[0];

        // Determinar si usar inputs o fallbacks
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

        // Asegurar formato HH:MM
        if (hourIn && hourIn.length > 5) hourIn = hourIn.substring(0, 5);
        if (hourOut && hourOut.length > 5) hourOut = hourOut.substring(0, 5);

        return {
            selected_date: selectedDateValue,
            hour_in: hourIn || '',
            hour_out: hourOut || ''
        };
    }

    /**
     * Obtiene datos de pago
     * @returns {Object}
     */
    getPaymentData() {
        const activePayBtn = document.querySelector('.pay-status-btn.pay-status-active');
        const paymentStatus = parseInt(activePayBtn?.dataset?.value) || 1;

        const partialPaymentInput = document.getElementById('partial-payment-input');
        const partialPayment = (paymentStatus === 2 && partialPaymentInput?.value)
            ? parseFloat(partialPaymentInput.value)
            : null;

        const paymentMethod = document.querySelector('select[name="payment_method"]')?.value || '1';
        const orderStatus = document.querySelector('select[name="status"]')?.value || '1';

        return {
            payment_status: paymentStatus,
            partial_payment: partialPayment,
            payment_method: parseInt(paymentMethod),
            order_status: parseInt(orderStatus)
        };
    }

    /**
     * Obtiene datos de facturación
     * @returns {Object}
     */
    getInvoiceData() {
        const invoiceRequired = document.getElementById('solicitar-factura')?.checked;

        if (invoiceRequired) {
            return {
                invoice_required: true,
                invoice_business_name: document.getElementById('razon-social')?.value || '',
                invoice_tax_id: document.getElementById('nif-cif')?.value || '',
                invoice_email: document.getElementById('email-factura')?.value || '',
                invoice_address: document.getElementById('direccion-calle')?.value || '',
                invoice_postal_code: document.getElementById('direccion-cp')?.value || '',
                invoice_city: document.getElementById('direccion-ciudad')?.value || ''
            };
        }

        return { invoice_required: false };
    }
}

export default FormDataCollector;
