/**
 * PaymentManager - Gestión de estado de pago y pago parcial
 * @module modules/payment/PaymentManager
 */

export class PaymentManager {
    constructor() {
        this.buttons = null;
        this.statusInput = null;
        this.partialContainer = null;
        this.partialInput = null;
        this.initialized = false;
    }

    /**
     * Inicializa el gestor de pagos
     */
    init() {
        if (this.initialized) return;

        this.buttons = document.querySelectorAll('.pay-status-btn');
        this.statusInput = document.querySelector('.payment-status-input');
        this.partialContainer = document.getElementById('partial-payment-container');
        this.partialInput = document.getElementById('partial-payment-input');

        this.bindEvents();
        this.initialized = true;
    }

    /**
     * Vincula eventos a los botones de estado de pago
     */
    bindEvents() {
        this.buttons.forEach(btn => {
            if (btn.dataset.initialized) return;

            btn.addEventListener('click', (e) => this.onStatusClick(e, btn));
            btn.dataset.initialized = 'true';
        });
    }

    /**
     * Maneja el clic en un botón de estado
     * @param {Event} e 
     * @param {HTMLElement} btn 
     */
    onStatusClick(e, btn) {
        e.preventDefault();

        // Actualizar clases activas
        this.buttons.forEach(b => b.classList.remove('pay-status-active'));
        btn.classList.add('pay-status-active');

        // Actualizar input oculto
        const status = btn.getAttribute('data-value');
        if (this.statusInput) {
            this.statusInput.value = status;
        }

        // Mostrar/ocultar pago parcial
        this.togglePartialPayment(status);

        // Disparar evento para actualizar formulario
        document.dispatchEvent(new CustomEvent('formFieldChanged'));
    }

    /**
     * Muestra/oculta el campo de pago parcial
     * @param {string} status 
     */
    togglePartialPayment(status) {
        if (!this.partialContainer || !this.partialInput) return;

        if (status === '2') {
            // Pago parcial
            this.partialContainer.style.display = 'block';
            this.partialInput.required = true;
            this.partialInput.classList.add('required-field');
        } else {
            this.partialContainer.style.display = 'none';
            this.partialInput.value = '';
            this.partialInput.required = false;
            this.partialInput.classList.remove('required-field', 'is-invalid', 'is-valid');
        }
    }

    /**
     * Obtiene el estado de pago actual
     * @returns {number}
     */
    getPaymentStatus() {
        const activeBtn = document.querySelector('.pay-status-btn.pay-status-active');
        return parseInt(activeBtn?.dataset?.value) || 1;
    }

    /**
     * Obtiene el monto de pago parcial
     * @returns {number|null}
     */
    getPartialPayment() {
        const status = this.getPaymentStatus();
        if (status === 2 && this.partialInput?.value) {
            return parseFloat(this.partialInput.value);
        }
        return null;
    }

    /**
     * Obtiene el método de pago seleccionado
     * @returns {number}
     */
    getPaymentMethod() {
        const select = document.querySelector('select[name="payment_method"]');
        return parseInt(select?.value) || 1;
    }

    /**
     * Resetea el estado de pago a pendiente
     */
    reset() {
        this.buttons.forEach((btn, index) => {
            btn.classList.toggle('pay-status-active', index === 0);
        });

        if (this.statusInput) {
            this.statusInput.value = '1';
        }

        if (this.partialContainer) {
            this.partialContainer.style.display = 'none';
        }

        if (this.partialInput) {
            this.partialInput.value = '';
            this.partialInput.required = false;
            this.partialInput.classList.remove('required-field', 'is-invalid', 'is-valid');
        }
    }
}

export default PaymentManager;
