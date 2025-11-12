window.usuariosApp = function() {
    return {
        users: [],
        roles: [],
        showModal: false,
        isEditing: false,
        currentUserId: null,
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
        },

        openModal() {
            this.showModal = true;
            this.isEditing = false;
            this.resetForm();
        },

        closeModal() {
            this.showModal = false;
            this.resetForm();
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
        },

        editUser(user) {
            this.isEditing = true;
            this.currentUserId = user.id;
            this.form = {
                name: user.name,
                email: user.email,
                phone: user.phone || '',
                username: user.username,
                password: '',
                rol: user.rol,
                status: user.status ? 1 : 0
            };
            this.showModal = true;
        },

        async saveUser() {
            const url = this.isEditing ? `/usuarios/${this.currentUserId}` : '/usuarios';
            const method = this.isEditing ? 'PUT' : 'POST';

            try {
                const response = await fetch(url, {
                    method: method,
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify(this.form)
                });

                const result = await response.json();

                if (result.success) {
                    this.closeModal();
                    // Recargar la página para actualizar la lista
                    location.reload();
                } else {
                    alert('Error: ' + (result.message || 'Ocurrió un error'));
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Ocurrió un error al guardar el usuario');
            }
        },

        async deleteUser(id) {
            if (!confirm('¿Estás seguro de que deseas eliminar este usuario?')) {
                return;
            }

            try {
                const response = await fetch(`/usuarios/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });

                const result = await response.json();

                if (result.success) {
                    // Recargar la página para actualizar la lista
                    location.reload();
                } else {
                    alert('Error: ' + (result.message || 'Ocurrió un error'));
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Ocurrió un error al eliminar el usuario');
            }
        }
    }
}
