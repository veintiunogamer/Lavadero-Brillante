/**
 * PriceCalculator - Cálculo de totales, subtotales y descuentos
 * @module modules/services/PriceCalculator
 */

import { formatPrice } from '../../utils/formatters.js';

export class PriceCalculator {

    constructor() {
        this.initialized = false;
        this.discountSelect = null;
    }

    /**
     * Inicializa el calculador de precios
     */
    init() {

        this.discountSelect = document.getElementById('discount-select');
        
        if (this.discountSelect && !this.discountSelect.dataset.initialized) {
            this.discountSelect.addEventListener('change', () => this.recalculate());
            this.discountSelect.disabled = true;
            this.discountSelect.dataset.initialized = 'true';
        }
        
        this.initialized = true;
    }

    /**
     * Actualiza el precio cuando cambia el servicio seleccionado
     * @param {HTMLSelectElement} serviceSelect 
     * @param {HTMLInputElement} priceInput 
     */
    updateServicePrice(serviceSelect, priceInput) {

        const selectedOption = serviceSelect.options[serviceSelect.selectedIndex];
        const basePrice = selectedOption?.dataset?.value || 0;
        const serviceRow = serviceSelect.closest('.service-item');
        const quantityInput = serviceRow?.querySelector('.service-quantity');
        const quantity = parseFloat(quantityInput?.value || 1);

        if (priceInput) {
            priceInput.dataset.basePrice = basePrice;
            priceInput.value = (parseFloat(basePrice) * quantity).toFixed(2);
        }

        this.recalculate();
        this.updateOrderDescription();
    }

    /**
     * Actualiza el precio cuando cambia la cantidad
     * @param {HTMLInputElement} quantityInput 
     * @param {HTMLInputElement} priceInput 
     */
    updateQuantityPrice(quantityInput, priceInput) {

        const quantity = parseFloat(quantityInput.value || 1);
        const basePrice = parseFloat(priceInput.dataset.basePrice || 0);
        priceInput.value = (quantity * basePrice).toFixed(2);

        this.recalculate();
        this.updateOrderDescription();

    }

    /**
     * Recalcula subtotal, descuento y total
     */
    recalculate() {

        const allServiceItems = document.querySelectorAll('.service-item');
        let subtotal = 0;

        allServiceItems.forEach(item => {
            const price = parseFloat(item.querySelector('.service-price')?.value || 0);
            subtotal += price;
        });

        // Habilitar/deshabilitar descuento
        if (this.discountSelect) {

            this.discountSelect.disabled = subtotal <= 0;
            if (subtotal <= 0) this.discountSelect.value = '';

        }

        // Calcular descuento
        const discountPercent = parseFloat(this.discountSelect?.value || 0);
        const discountAmount = (subtotal * discountPercent) / 100;
        const total = subtotal - discountAmount;

        // Actualizar displays
        this.updateDisplays(subtotal, discountAmount, total);

        return { subtotal, discountAmount, total, discountPercent };
    }

    /**
     * Actualiza los elementos de display de precios
     * @param {number} subtotal 
     * @param {number} discountAmount 
     * @param {number} total 
     */
    updateDisplays(subtotal, discountAmount, total) {

        // Subtotal
        const subtotalSection = document.querySelector('.subtotal-section');
        const subtotalInput = document.querySelector('.subtotal-value');

        if (subtotalSection) subtotalSection.textContent = formatPrice(subtotal);
        if (subtotalInput) subtotalInput.value = subtotal.toFixed(2);

        // Descuento
        const discountSection = document.querySelector('.discount-section');
        const discountInput = document.querySelector('.discount-value');

        if (discountSection) discountSection.textContent = '-' + formatPrice(discountAmount);
        if (discountInput) discountInput.value = discountAmount.toFixed(2);

        // Total
        const totalSection = document.querySelector('.total-section');
        const totalInput = document.querySelector('.total-value');

        if (totalSection) totalSection.textContent = formatPrice(total);
        if (totalInput) totalInput.value = total.toFixed(2);
    }

    /**
     * Actualiza la descripción de la orden con los servicios seleccionados
     */
    updateOrderDescription() {

        const descriptionTextarea = document.querySelector('.service-box textarea[name="order_notes"]');
        if (!descriptionTextarea) return;

        const serviceItems = document.querySelectorAll('.service-item');
        let lines = [];

        serviceItems.forEach(item => {

            const serviceSelect = item.querySelector('.service-select');
            const quantityInput = item.querySelector('.service-quantity');
            const serviceName = serviceSelect?.options[serviceSelect.selectedIndex]?.textContent?.trim();
            const quantity = quantityInput?.value || 1;

            if (serviceName && 
                serviceName !== 'Selecciona un servicio' && 
                serviceName !== 'Seleccionar servicio') {
                lines.push('• ' + serviceName + ' x' + quantity);
            }

        });

        let serviciosText = lines.length > 0 ? lines.join('\n') + '\n' : '';
        descriptionTextarea.value = serviciosText + 'Ninguno de nuestros precios incluye IVA.';
    }

    /**
     * Obtiene los totales actuales
     * @returns {Object}
     */
    getTotals() {

        return {
            subtotal: parseFloat(document.querySelector('.subtotal-value')?.value || 0),
            discount: parseFloat(document.querySelector('.discount-value')?.value || 0),
            total: parseFloat(document.querySelector('.total-value')?.value || 0),
            discountPercent: parseFloat(this.discountSelect?.value || 0)
        };

    }

    /**
     * Resetea todos los totales a cero
     */
    reset() {

        if (this.discountSelect) {
            this.discountSelect.value = '';
            this.discountSelect.disabled = true;
        }

        this.updateDisplays(0, 0, 0);
        
    }
}

export default PriceCalculator;
