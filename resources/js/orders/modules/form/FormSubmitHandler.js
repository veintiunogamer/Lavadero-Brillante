/**
 * FormSubmitHandler - Manejo del envío del formulario y validación del botón
 * @module modules/form/FormSubmitHandler
 */

export class FormSubmitHandler {
    constructor(onSubmit) {
        this.onSubmit = onSubmit;
        this.confirmBtn = null;
        this.termsCheckbox = null;
        this.initialized = false;
    }

    /**
     * Inicializa el manejador de envío
     */
    init() {
        this.confirmBtn = document.querySelector('.confirm-btn');
        this.termsCheckbox = document.getElementById('terms-checkbox');

        if (!this.confirmBtn || this.confirmBtn.dataset.initialized) {
            return;
        }

        this.bindEvents();
        this.updateButtonState();
        
        this.confirmBtn.dataset.initialized = 'true';
        this.initialized = true;
    }

    /**
     * Vincula eventos al formulario
     */
    bindEvents() {
        // Evento del checkbox de términos
        if (this.termsCheckbox && !this.termsCheckbox.dataset.initialized) {
            this.termsCheckbox.addEventListener('change', () => this.updateButtonState());
            this.termsCheckbox.dataset.initialized = 'true';
        }

        // Evento del botón de confirmar
        this.confirmBtn.addEventListener('click', async (e) => {
            e.preventDefault();
            if (this.onSubmit) {
                await this.onSubmit();
            }
        });

        // Escuchar cambios en campos requeridos
        document.querySelectorAll('.required-field').forEach(field => {
            if (!field.dataset.formListener) {
                field.addEventListener('input', () => this.updateButtonState());
                field.addEventListener('change', () => this.updateButtonState());
                field.dataset.formListener = 'true';
            }
        });

        // Escuchar evento personalizado para cambios en el formulario
        document.addEventListener('formFieldChanged', () => this.updateButtonState());
    }

    /**
     * Valida si el formulario está completo
     * @returns {boolean}
     */
    isFormValid() {
        const requiredFields = document.querySelectorAll('.required-field');
        
        for (let field of requiredFields) {
            // Ignorar campos ocultos o deshabilitados
            if (field.offsetParent === null || field.disabled) continue;
            
            // Ignorar campos dentro de contenedores ocultos
            if (field.closest('[style*="display: none"]') || 
                field.closest('[style*="display:none"]')) {
                continue;
            }

            const value = field.value?.trim();
            if (!value) return false;
        }
        
        return true;
    }

    /**
     * Actualiza el estado del botón de confirmar
     */
    updateButtonState() {
        const termsChecked = this.termsCheckbox?.checked || false;
        const formValid = this.isFormValid();
        
        if (this.confirmBtn) {
            this.confirmBtn.disabled = !(termsChecked && formValid);
        }
    }

    /**
     * Resetea el estado del formulario
     */
    reset() {
        if (this.termsCheckbox) {
            this.termsCheckbox.checked = false;
        }
        
        if (this.confirmBtn) {
            this.confirmBtn.disabled = true;
        }
    }
}

export default FormSubmitHandler;
