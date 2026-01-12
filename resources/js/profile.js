// Solo mostrar logs si estamos en la vista de perfil
function profileModuleActive() {
    return !!document.getElementById('profile-root');
}

if (typeof window !== 'undefined' && profileModuleActive()) {
    console.log('Profile JS cargado');
}

// Exponer la función globalmente para Alpine
window.profileApp = function() {

    return {
        user: {},
        showCurrentPassword: false,
        showNewPassword: false,
        errors: {
            phone: '',
            new_password: ''
        },
        form: {
            name: '',
            email: '',
            phone: '',
            username: '',
            current_password: '',
            new_password: ''
        },

        initData(userData) {
            
            this.user = userData;

            this.form = {
                name: userData.name,
                email: userData.email,
                phone: window.formatPhoneInput ? window.formatPhoneInput(userData.phone || '') : (userData.phone || ''),
                username: userData.username,
                current_password: '',
                new_password: ''
            };

        },

        clearErrors() {
            this.errors = {
                phone: '',
                new_password: ''
            };
        },

        validatePhone() {
            if (this.form.phone && !validateSpanishPhoneJS(this.form.phone)) {
                this.errors.phone = 'Número de móvil no válido';
            } else {
                this.errors.phone = '';
            }
        },

        validateNewPassword() {
            if (this.form.new_password && this.form.new_password.length < 8) {
                this.errors.new_password = 'Mínimo 8 caracteres';
            } else {
                this.errors.new_password = '';
            }
        },

        validatePhoneForButton() {
            return !this.form.phone || validateSpanishPhoneJS(this.form.phone);
        },

        isFormValid() {
            const basicInfoValid = this.form.name && this.form.email && this.form.username && this.validatePhoneForButton();
            
            // Si se intenta cambiar la contraseña, validar que ambos campos estén llenos
            if (this.form.current_password || this.form.new_password) {
                return basicInfoValid && this.form.current_password && this.form.new_password && this.form.new_password.length >= 8;
            }
            
            return basicInfoValid;
        },

        async saveProfile() {

            // Validar teléfono antes de enviar
            this.validatePhone();

            if (this.errors.phone) {
                window.notyf.error(this.errors.phone);
                return;
            }

            // Validar contraseña si se está cambiando
            if (this.form.new_password) {

                this.validateNewPassword();
                
                if (this.errors.new_password) {
                    window.notyf.error(this.errors.new_password);
                    return;
                }
            }

            try {

                const response = await fetch('/profile/update', {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify(this.form)
                });

                const result = await response.json();

                if (result.success) {

                    window.notyf.success(result.message || 'Perfil actualizado exitosamente');

                    // Actualizar el objeto user con los nuevos datos
                    this.user = result.user;

                    // Limpiar los campos de contraseña
                    this.form.current_password = '';
                    this.form.new_password = '';

                    // Actualizar el nombre de usuario en el header si cambió
                    const usernameElement = document.querySelector('.user-name');
                    if (usernameElement) {
                        usernameElement.textContent = result.user.username;
                    }

                } else {
                    window.notyf.error('Error: ' + (result.message || 'Ocurrió un error'));
                }

            } catch (error) {
                console.error('Error:', error);
                window.notyf.error('Ocurrió un error al actualizar el perfil');
            }
        }
    }

}
