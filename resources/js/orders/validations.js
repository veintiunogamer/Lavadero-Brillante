/**
 * Validations JS - Sistema de validaciones para formularios
 * Maneja la validación de campos usando clases CSS
 * 
 * CLASES DISPONIBLES:
 * - required-field: Campo obligatorio
 * - email-field: Validar formato email
 * - phone-field: Validar formato teléfono español
 * - number-field: Validar números
 * - data-field-name: Nombre del campo para mensajes de error
 */

/**
 * Clase para gestionar validaciones
 */
class FormValidator {
    
    constructor(formElement) {
        this.form = formElement;
        this.errors = [];
    }

    /**
     * Valida todos los campos del formulario
     * @returns {boolean} true si todo es válido
     */
    validateAll() {

        this.errors = [];
        
        // Validar campos obligatorios
        const requiredFields = this.form.querySelectorAll('.required-field');

        requiredFields.forEach(field => {

            if (!this.validateRequired(field)) {

                const fieldName = field.dataset.fieldName || 'Este campo';

                this.errors.push(`${fieldName} es obligatorio`);
                this.markFieldAsError(field);

            } else {
                this.markFieldAsValid(field);
            }

        });

        // Validar emails
        const emailFields = this.form.querySelectorAll('.email-field');

        emailFields.forEach(field => {

            if (field.value && !this.validateEmail(field.value)) {

                const fieldName = field.dataset.fieldName || 'Email';

                this.errors.push(`${fieldName} no tiene un formato válido`);
                this.markFieldAsError(field);

            } else if (field.value) {
                this.markFieldAsValid(field);
            }

        });

        // Validar teléfonos
        const phoneFields = this.form.querySelectorAll('.phone-field');

        phoneFields.forEach(field => {

            if (field.value && !this.validatePhone(field.value)) {

                const fieldName = field.dataset.fieldName || 'Teléfono';
                this.errors.push(`${fieldName} no tiene un formato válido`);

                this.markFieldAsError(field);

            } else if (field.value) {
                this.markFieldAsValid(field);
            }

        });

        // Validar números
        const numberFields = this.form.querySelectorAll('.number-field');

        numberFields.forEach(field => {

            if (field.value && !this.validateNumber(field.value)) {

                const fieldName = field.dataset.fieldName || 'Este campo';
                this.errors.push(`${fieldName} debe ser un número válido`);

                this.markFieldAsError(field);

            } else if (field.value) {
                this.markFieldAsValid(field);
            }

        });

        return this.errors.length === 0;
    }

    /**
     * Valida campo obligatorio
     */
    validateRequired(field) {

        if (field.tagName === 'SELECT') {
            return field.value !== '' && field.value !== null;
        }

        return field.value.trim() !== '';

    }

    /**
     * Valida formato de email
     */
    validateEmail(email) {
        const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return regex.test(email);
    }

    /**
     * Valida teléfono español
     */
    validatePhone(phone) {

        // Permitir solo dígitos, espacios, +, (, ), -
        if (!/^[\d\s\+\(\)\-]+$/.test(phone)) {
            return false;
        }

        const cleaned = phone.replace(/\D/g, '');

        // 9 dígitos o 11 con prefijo 34
        return cleaned.length === 9 || (cleaned.length === 11 && cleaned.startsWith('34'));

    }

    /**
     * Valida que sea un número
     */
    validateNumber(value) {
        return !isNaN(parseFloat(value)) && isFinite(value);
    }

    /**
     * Marca un campo como error
     */
    markFieldAsError(field) {
        field.classList.add('is-invalid');
        field.classList.remove('is-valid');
    }

    /**
     * Marca un campo como válido
     */
    markFieldAsValid(field) {
        field.classList.remove('is-invalid');
        field.classList.add('is-valid');
    }

    /**
     * Limpia todos los estados de validación
     */
    clearValidationStates() {

        const allFields = this.form.querySelectorAll('.is-invalid, .is-valid');

        allFields.forEach(field => {
            field.classList.remove('is-invalid', 'is-valid');
        });

    }

    /**
     * Obtiene todos los errores
     */
    getErrors() {
        return this.errors;
    }

    /**
     * Muestra los errores con Notyf
     */
    showErrors() {

        if (this.errors.length > 0 && window.notyf) {

            this.errors.forEach(error => {
                window.notyf.error(error);
            });

        }

    }
}

/**
 * Validador específico para el formulario de órdenes
 */
class OrderFormValidator extends FormValidator {
    
    constructor(formElement) {
        super(formElement);
    }

    /**
     * Validación adicional específica de órdenes
     */
    validateOrderSpecific() {

        let valid = true;

        // Validar que haya al menos un servicio seleccionado
        const serviceSelects = this.form.querySelectorAll('.service-select');
        let hasService = false;
        
        serviceSelects.forEach(select => {
            if (select.value && select.value !== '') {
                hasService = true;
            }
        });

        if (!hasService) {
            this.errors.push('Debes seleccionar al menos un servicio');
            valid = false;
        }

        // Validar que si solicita factura, tenga los datos completos
        const solicitaFactura = document.getElementById('solicitar-factura');

        if (solicitaFactura && solicitaFactura.checked) {
            
            const razonSocial = document.getElementById('razon-social');
            const nifCif = document.getElementById('nif-cif');
            const direccionCalle = document.getElementById('direccion-calle');
            const direccionCp = document.getElementById('direccion-cp');
            const direccionCiudad = document.getElementById('direccion-ciudad');

            if (!razonSocial?.value) {
                this.errors.push('La Razón Social es obligatoria para facturar');
                this.markFieldAsError(razonSocial);
                valid = false;
            }

            if (!nifCif?.value) {
                this.errors.push('El NIF/CIF es obligatorio para facturar');
                this.markFieldAsError(nifCif);
                valid = false;
            }

            if (!direccionCalle?.value) {
                this.errors.push('La Dirección es obligatoria para facturar');
                this.markFieldAsError(direccionCalle);
                valid = false;
            }

            if (!direccionCp?.value) {
                this.errors.push('El Código Postal es obligatorio para facturar');
                this.markFieldAsError(direccionCp);
                valid = false;
            }

            if (!direccionCiudad?.value) {
                this.errors.push('La Ciudad es obligatoria para facturar');
                this.markFieldAsError(direccionCiudad);
                valid = false;
            }
        }

        // Validar fecha seleccionada (si existe una variable global)
        if (typeof window.selectedOrderDate === 'undefined' || !window.selectedOrderDate) {
            this.errors.push('Debes seleccionar una fecha en el calendario');
            valid = false;
        }

        // Validar horas
        const horaEntrada = document.getElementById('hora-entrada')?.value || document.getElementById('hora-entrada-fallback')?.value;
        const horaSalida = document.getElementById('hora-salida')?.value || document.getElementById('hora-salida-fallback')?.value;

        if (!horaEntrada) {
            this.errors.push('Debes seleccionar la hora de entrada');
            valid = false;
        }

        if (!horaSalida) {
            this.errors.push('Debes seleccionar la hora de salida');
            valid = false;
        }

        return valid;
    }

    /**
     * Valida el formulario completo de orden
     */
    validateOrderForm() {
        // Primero las validaciones básicas
        const basicValid = this.validateAll();
        
        // Luego las validaciones específicas
        const specificValid = this.validateOrderSpecific();

        return basicValid && specificValid;
    }
}

// Exportar para usar en otros archivos
if (typeof window !== 'undefined') {
    window.FormValidator = FormValidator;
    window.OrderFormValidator = OrderFormValidator;
}
