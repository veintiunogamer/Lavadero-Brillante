/**
 * LicensePlateValidator - Validación y autocompletado por matrícula
 * @module modules/client/LicensePlateValidator
 */

import { checkLicensePlate } from '../../utils/api.js';

export class LicensePlateValidator {

    constructor() {
        this.licensePlateInput = null;
        this.clientNameInput = null;
        this.clientPhoneInput = null;
        this.licensePlateInfo = null;
        this.debounceTimer = null;
        this.debounceDelay = 500;
        this.initialized = false;
    }

    /**
     * Inicializa el validador de matrícula
     */
    init() {

        this.licensePlateInput = document.getElementById('license-plaque-input');
        this.clientNameInput = document.querySelector('input[name="client_name"]');
        this.clientPhoneInput = document.getElementById('telefono-whatsapp');
        this.licensePlateInfo = document.getElementById('license-plate-info');

        if (!this.licensePlateInput || this.licensePlateInput.dataset.initialized) {
            return;
        }

        this.bindEvents();
        this.licensePlateInput.dataset.initialized = 'true';
        this.initialized = true;
    }

    /**
     * Vincula eventos al input de matrícula
     */
    bindEvents() {
        this.licensePlateInput.addEventListener('input', (e) => this.onInput(e));
    }

    /**
     * Maneja el evento input
     * @param {Event} e 
     */
    onInput(e) {

        // Convertir a mayúsculas
        e.target.value = e.target.value.toUpperCase();
        
        // Ocultar info del cliente
        if (this.licensePlateInfo) {
            this.licensePlateInfo.style.display = 'none';
        }

        // Debounce para evitar muchas peticiones
        clearTimeout(this.debounceTimer);

        this.debounceTimer = setTimeout(() => {
            this.validateAndFetch(e.target.value.trim());
        }, this.debounceDelay);
    }

    /**
     * Valida la matrícula y busca el cliente
     * @param {string} licensePlate 
     */
    async validateAndFetch(licensePlate) {

        if (licensePlate.length < 4) return;

        try {

            const result = await checkLicensePlate(licensePlate);

            if (result.exists && result.client) {

                this.fillClientData(result.client);
                window.notyf?.success('Cliente encontrado - datos cargados automáticamente');
            
            } else {
                this.clearClientData();
            }

            // Notificar cambio para actualizar estado del botón
            document.dispatchEvent(new CustomEvent('formFieldChanged'));

        } catch (error) {
            console.error('Error verificando matrícula:', error);
        }
    }

    /**
     * Rellena los datos del cliente encontrado
     * @param {Object} client 
     */
    fillClientData(client) {

        if (this.clientNameInput) {
            this.clientNameInput.value = client.name || '';
        }

        if (this.clientPhoneInput) {
            const phoneValue = client.phone || '';
            // Extraer últimos 9 dígitos (formato español)
            this.clientPhoneInput.value = phoneValue.replace(/\D/g, '').slice(-9);
        }

        if (this.licensePlateInfo) {
            this.licensePlateInfo.style.display = 'block';
        }
    }

    /**
     * Limpia los datos del cliente
     */
    clearClientData() {

        if (this.clientNameInput) {
            this.clientNameInput.value = '';
        }

        if (this.clientPhoneInput) {
            this.clientPhoneInput.value = '';
        }

    }

    /**
     * Obtiene los datos del cliente del formulario
     * @returns {Object}
     */
    getClientData() {

        return {
            name: this.clientNameInput?.value || '',
            phone: this.clientPhoneInput?.value || '',
            licensePlate: this.licensePlateInput?.value || ''
        };

    }

    /**
     * Resetea el validador
     */
    reset() {

        if (this.licensePlateInput) {
            this.licensePlateInput.value = '';
        }

        this.clearClientData();

        if (this.licensePlateInfo) {
            this.licensePlateInfo.style.display = 'none';
        }
        
    }
}

export default LicensePlateValidator;
