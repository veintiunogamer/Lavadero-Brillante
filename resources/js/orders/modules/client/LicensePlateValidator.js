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
        this.tempRegistrationInput = null;
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
        this.tempRegistrationInput = document.getElementById('temp-registration-input');
        this.registrationMode = false;

        if (!this.licensePlateInput || this.licensePlateInput.dataset.initialized) {
            return;
        }

        this.bindEvents();
        this.initVehicleIdentifierToggle();
        this.licensePlateInput.dataset.initialized = 'true';
        this.initialized = true;
    }

    initVehicleIdentifierToggle() {
        const plateButton = document.getElementById('vehicle-id-plate-toggle');
        const registrationButton = document.getElementById('vehicle-id-registration-toggle');
        const plateField = document.getElementById('license-plate-field');
        const registrationField = document.getElementById('temp-registration-field');
        if (!plateButton || !registrationButton || !plateField || !registrationField) return;

        const setMode = (registration) => {
            this.registrationMode = registration;
            plateField.style.display = registration ? 'none' : '';
            registrationField.style.display = registration ? '' : 'none';
            this.licensePlateInput.required = !registration;
            this.licensePlateInput.classList.toggle('required-field', !registration);
            this.tempRegistrationInput.required = registration;
            this.tempRegistrationInput.classList.toggle('required-field', registration);
            this.licensePlateInput.disabled = registration;
            this.tempRegistrationInput.disabled = !registration;
            plateButton.classList.toggle('active', !registration);
            registrationButton.classList.toggle('active', registration);
            if (registration) this.licensePlateInput.value = '';
            else this.tempRegistrationInput.value = '';
            document.dispatchEvent(new CustomEvent('formFieldChanged'));
        };

        plateButton.addEventListener('click', () => setMode(false));
        registrationButton.addEventListener('click', () => setMode(true));
        this.tempRegistrationInput.addEventListener('input', (event) => {
            event.target.value = event.target.value.replace(/[^a-z0-9]/gi, '').toUpperCase();
            clearTimeout(this.debounceTimer);
            this.debounceTimer = setTimeout(() => this.validateAndFetch(event.target.value.trim()), this.debounceDelay);
            document.dispatchEvent(new CustomEvent('formFieldChanged'));
        });
        this.tempRegistrationInput.disabled = true;
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

                if (result.client.temp_registration && !result.client.license_plaque && !this.registrationMode) {
                    document.getElementById('vehicle-id-registration-toggle')?.click();
                }

                this.fillClientData(result.client);
                window.notyf?.success('Cliente encontrado - datos cargados automáticamente');
            
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
        if (this.tempRegistrationInput && this.registrationMode) this.tempRegistrationInput.value = client.temp_registration || '';

        // Rellenar modelo (brand)
        const brandInput = document.getElementById('client-brand-input');
        if (brandInput) {
            brandInput.value = client.brand || '';
        }

        // Rellenar flota y sincronizar periodo de pago
        const fleetSwitch = document.querySelector('input[name="fleet"]');
        if (fleetSwitch) {
            fleetSwitch.checked = !!client.fleet;
            fleetSwitch.dispatchEvent(new Event('change'));
        }

        if (this.licensePlateInfo) {
            this.licensePlateInfo.style.display = 'block';
        }
    }

    /**
     * Limpia los datos del cliente
     */
    clearClientData() {

        if (this.tempRegistrationInput) this.tempRegistrationInput.value = '';

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
