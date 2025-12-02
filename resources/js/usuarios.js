// Solo mostrar logs si estamos en la vista de usuarios
function usuariosModuleActive() {
    return !!document.getElementById('usuarios-root');
}

if (typeof window !== 'undefined' && usuariosModuleActive()) {
    console.log('Usuarios JS cargado');
}

// Importar Cleave solo si está disponible
if (typeof window !== 'undefined' && window.Cleave) {

    window.initPhoneMask = function(inputElement) {

        new window.Cleave(inputElement, {
            phone: true
        });

    };

} else {
    window.initPhoneMask = function(inputElement) {

        // Fallback a formatter JS
        inputElement.addEventListener('input', function(e) {
            e.target.value = window.formatPhoneInput(e.target.value);
        });

    };
}

// Exponer la función globalmente para Alpine
window.usuariosApp = function() {

    return {
        users: [],
        roles: [],
        showModal: false,
        isEditing: false,
        currentUserId: null,
        showPassword: false,
        showDeleteModal: false,
        deleteUserId: null,
        errors: {
            phone: '',
            password: ''
        },
        form: {
            name: '',
            email: '',
            phone: '',
            username: '',
            password: '',
            rol: '',
            status: 1
        },

        initData(usersData, rolesData) {
            
            this.users = usersData;
            this.roles = rolesData;

            this.showModal = false;

        },

        openModal() {

            this.showModal = true;
            this.isEditing = false;

            this.resetForm();

            this.$nextTick(() => {
                const first = document.querySelector('#usuarios-root input, #usuarios-root select');
                if (first) first.focus();
            });

        },

        closeModal() {

            this.showModal = false;
            this.resetForm();

            // devolver foco al botón Crear
            this.$nextTick(() => {
                const trigger = document.querySelector('#usuarios-root button[@click="openModal()"], #usuarios-root button.btn-primary');
                if (trigger) trigger.focus();
            });

        },

        resetForm() {
            this.form = {
                name: '',
                email: '',
                phone: '',
                username: '',
                password: '',
                rol: '',
                status: 1
            };
            this.currentUserId = null;
            this.clearErrors();
        },

        clearErrors() {
            this.errors = {
                phone: '',
                password: ''
            };
        },

        validatePhone() {
            if (this.form.phone && !validateSpanishPhoneJS(this.form.phone)) {
                this.errors.phone = 'Número de móvil no válido';
            } else {
                this.errors.phone = '';
            }
        },

        validatePassword() {
            if (this.form.password && this.form.password.length < 8) {
                this.errors.password = 'Mínimo 8 caracteres';
            } else {
                this.errors.password = '';
            }
        },

        validatePhoneForButton() {
            return !this.form.phone || validateSpanishPhoneJS(this.form.phone);
        },

        togglePassword() {
            this.showPassword = !this.showPassword;
        },

        isFormValid() {
            const passwordValid = this.isEditing ? (this.form.password === '' || this.form.password.length >= 8) : (this.form.password && this.form.password.length >= 8);
            return this.form.name && this.form.email && this.form.username && passwordValid && this.validatePhoneForButton();
        },

        editUser(user) {

            this.isEditing = true;
            this.currentUserId = user.id;

            this.form = {
                name: user.name,
                email: user.email,
                phone: window.formatPhoneInput ? window.formatPhoneInput(user.phone || '') : (user.phone || ''),
                username: user.username,
                password: '',
                rol: user.rol,
                status: user.status ? 1 : 0
            };

            this.showModal = true;

        },

        async saveUser() {

            // Validar teléfono antes de enviar
            this.validatePhone();

            if (this.errors.phone) {
                window.notyf.error(this.errors.phone);
                return;
            }

            const url = this.isEditing ? `/usuarios/update/${this.currentUserId}` : '/usuarios/store';
            const method = this.isEditing ? 'PUT' : 'POST';

            try {

                const response = await fetch(url, {
                    method: method,
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify(this.form)
                });

                const result = await response.json();

                if (result.success) {

                    window.notyf.success(result.message || 'Usuario guardado exitosamente');

                    this.closeModal();

                    setTimeout(() => {

                        // Recargar la página para actualizar la lista
                        location.reload();

                    }, 3000);
                    

                } else {
                    window.notyf.error('Error: ' + (result.message || 'Ocurrió un error'));
                }

            } catch (error) {
                console.error('Error:', error);
                window.notyf.error('Ocurrió un error al guardar el usuario');
            }
        },

        deleteUser(id) {

            this.deleteUserId = id;
            this.showDeleteModal = true;

        },

        cancelDelete() {

            this.showDeleteModal = false;
            this.deleteUserId = null;

        },

        async confirmDelete() {

            const id = this.deleteUserId;

            this.showDeleteModal = false;
            this.deleteUserId = null;

            try {

                const response = await fetch(`/usuarios/delete/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });

                const result = await response.json();

                if (result.success) {

                    // Eliminar el usuario del array
                    this.users = this.users.filter(user => user.id !== id);

                    window.notyf.success(result.message || 'Usuario eliminado exitosamente');

                } else {
                    window.notyf.error('Error: ' + (result.message || 'Ocurrió un error'));
                }
                
            } catch (error) {
                console.error('Error:', error);
                window.notyf.error('Ocurrió un error al eliminar el usuario');
            }
        }
    }

}
