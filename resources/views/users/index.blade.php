@extends('layouts.base')

@section('content')

<script>
    window.activeUsersData = @json($activeUsers);
    window.inactiveUsersData = @json($inactiveUsers);
    window.rolesData = @json($roles);
</script>

<div id="usuarios-root" class="d-flex justify-content-center align-items-start" style="min-height: 80vh; padding-top: 2rem;" x-data="typeof usuariosApp === 'function' ? usuariosApp() : {}" x-init='if (typeof usuariosApp === "function") initData(window.activeUsersData, window.inactiveUsersData, window.rolesData)'>

    <!-- Tabla con los usuarios -->
    <div class="card shadow-lg rounded-4 bg-white p-4 w-100" style="max-width: 1400px;">

        <div class="col-12 d-flex justify-content-between align-items-center mb-3 p-4">

            <div class="col-6">

                <h1 class="m-0 fw-bold">
                    <i class="fa-solid fa-user-cog icon color-blue"></i>
                    LISTADO DE USUARIOS
                </h1>

                <span class="fw-bold text-muted">Listado y gestión de usuarios del sistema.</span>

            </div>

            <div class="col-6">
                <button @click="openModal()" class="btn-action btn-lg mb-3 float-end">
                    <i class="fa-solid fa-plus me-2"></i>
                    Crear Usuario
                </button>
            </div>

        </div>

        <!-- Tabs para Usuarios Activos e Inactivos -->
        <ul class="nav nav-tabs px-4" role="tablist">

            <li class="nav-item" role="presentation">
                <button class="nav-link" :class="activeTab === 'active' ? 'active' : ''" @click="activeTab = 'active'" type="button" role="tab">
                    <i class="fa-solid fa-check-circle me-1"></i>
                    Usuarios Activos (<span x-text="activeUsers.length"></span>)
                </button>
            </li>

            <li class="nav-item" role="presentation">
                <button class="nav-link" :class="activeTab === 'inactive' ? 'active' : ''" @click="activeTab = 'inactive'" type="button" role="tab">
                    <i class="fa-solid fa-times-circle me-1"></i>
                    Usuarios Inactivos (<span x-text="inactiveUsers.length"></span>)
                </button>
            </li>

        </ul>

        <!-- Tab Content: Usuarios Activos -->
        <div class="table-responsive mt-4 p-4" x-show="activeTab === 'active'">

            <table class="table table-striped table-bordered align-middle">

                <thead class="table-dark">
                    <tr>
                        <th>Nombre</th>
                        <th>Email</th>
                        <th>Teléfono</th>
                        <th>Usuario</th>
                        <th>Rol</th>
                        <th>Fecha Creación</th>
                        <th>Acciones</th>
                    </tr>
                </thead>

                <tbody>
                    <template x-for="(user, index) in activeUsers" :key="index">
                        <tr>
                            <td x-text="user.name"></td>
                            <td x-text="user.email"></td>
                            <td x-text="user.phone || '--'"></td>
                            <td x-text="user.username"></td>
                            <td x-text="user.role ? user.role.name : '--'"></td>
                            <td x-text="new Date(user.creation_date).toLocaleDateString()"></td>
                            <td class="text-center">

                                <div class="btn-group btn-group-md">

                                    <button @click="editUser(user)" class="btn btn-primary">
                                        <i class="fa-solid fa-edit"></i>
                                    </button>

                                    <button @click="deleteUser(user.id)" class="btn btn-danger">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>

                                </div>

                            </td>

                        </tr>

                    </template>

                    <tr x-show="activeUsers.length === 0">
                        <td colspan="7">
                            <div class="citas-empty-state">
                                <i class="fa-solid fa-user-check citas-empty-icon text-warning"></i>
                                <p class="citas-empty-title">Sin usuarios activos</p>
                                <p class="citas-empty-sub">No hay usuarios activos en el sistema.<br>Crea uno nuevo usando el botón <strong>Crear Usuario</strong>.</p>
                            </div>
                        </td>
                    </tr>

                </tbody>

            </table>

        </div>

        <!-- Tab Content: Usuarios Inactivos -->
        <div class="table-responsive mt-4 p-4" x-show="activeTab === 'inactive'">
            <table class="table table-striped table-bordered align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>Nombre</th>
                        <th>Email</th>
                        <th>Teléfono</th>
                        <th>Usuario</th>
                        <th>Rol</th>
                        <th>Fecha Creación</th>
                        <th>Acciones</th>
                    </tr>
                </thead>

                <tbody>
                    <template x-for="(user, index) in inactiveUsers" :key="index">
                        <tr class="table-secondary">
                            <td x-text="user.name"></td>
                            <td x-text="user.email"></td>
                            <td x-text="user.phone || '--'"></td>
                            <td x-text="user.username"></td>
                            <td x-text="user.role ? user.role.name : '--'"></td>
                            <td x-text="new Date(user.creation_date).toLocaleDateString()"></td>
                            <td>
                                <button @click="activateUser(user.id)" class="btn btn-sm btn-success">
                                    <i class="fa-solid fa-check"></i> Activar
                                </button>
                            </td>
                        </tr>
                    </template>
                    <tr x-show="inactiveUsers.length === 0">
                        <td colspan="7">
                            <div class="citas-empty-state">
                                <i class="fa-solid fa-user-xmark citas-empty-icon text-warning"></i>
                                <p class="citas-empty-title">Sin usuarios inactivos</p>
                                <p class="citas-empty-sub">No hay usuarios desactivados.<br>Los usuarios desactivados aparecerán en esta lista.</p>
                            </div>
                        </td>
                    </tr>
                </tbody>

            </table>

        </div>

    </div>

    <!-- Modal para Crear/Editar Usuario (moved inside x-data scope) -->
    <div x-cloak @click.self="closeModal()" @keydown.escape.window="closeModal()" :class="showModal ? 'position-fixed top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center' : 'd-none'" style="background: rgba(0,0,0,0.5); z-index: 9999;" x-transition>

        <div class="user-form-modal" tabindex="-1">

            <div class="user-form-header">
                <div>
                    <h2 class="user-form-title">
                        <i class="fa-solid" :class="isEditing ? 'fa-user-pen' : 'fa-user-plus'" aria-hidden="true"></i>
                        <span x-text="isEditing ? 'Editar Usuario' : 'Crear Usuario'"></span>
                    </h2>
                    <p x-text="isEditing ? 'Mantén actualizada la información de tu perfil.' : 'Completa la información para crear una cuenta de usuario.'"></p>
                </div>
                <button @click="closeModal()" type="button" class="user-form-close" aria-label="Cerrar">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <form @submit.prevent="saveUser()" class="user-form-content">

                <div class="user-form-fields">

                    <div class="user-form-field">
                        <label class="form-label">
                            Nombre Completo <span class="text-danger">*</span>
                        </label>
                        <input type="text" x-model="form.name" class="form-control" maxlength="30" required>
                    </div>

                    <div class="user-form-field">
                        <label class="form-label">
                            Correo Electrónico <span class="text-danger">*</span>
                        </label>
                        <input type="email" x-model="form.email" class="form-control" maxlength="40" required>
                    </div>

                    <div class="user-form-field">
                        <label class="form-label">
                            Teléfono Movil <span class="text-danger">*</span>
                        </label>
                        <input type="tel" :value="form.phone" @input="form.phone = formatPhoneInput($event.target.value.replace(/[^0-9\s\+\(\)\-]/g, ''))" @blur="validatePhone()" x-ref="phoneInput" class="form-control" maxlength="15" inputmode="tel">
                        <span x-text="errors.phone" x-show="errors.phone" class="text-danger small mt-1" style="font-size: 0.75rem;"></span>
                    </div>

                    <div class="user-form-field">
                        <label class="form-label">
                            Usuario <span class="text-danger">*</span>
                        </label>
                        <input type="text" x-model="form.username" class="form-control" maxlength="20" required>
                    </div>

                    <div class="user-form-field">
                        <label class="form-label">
                            Rol <span class="text-danger">*</span>
                        </label>
                        <select x-model="form.rol" class="form-select" required>
                            <option value="">Seleccionar Rol</option>
                            <template x-for="role in roles" :key="role.id">
                                <option :value="role.id" x-text="role.name"></option>
                            </template>
                        </select>
                    </div>

                    <div class="user-form-field" x-show="isEditing">
                        <label class="form-label">Estado</label>
                        <select x-model="form.status" class="form-select">
                            <option value="1">Activo</option>
                            <option value="0">Inactivo</option>
                        </select>
                    </div>

                </div>

                <section class="user-security-panel">
                    <div class="user-security-heading">
                        <span class="user-security-icon"><i class="fa-solid fa-lock"></i></span>
                        <div>
                            <h3>Seguridad de acceso</h3>
                            <p x-text="isEditing ? 'Deja la contraseña vacía si no quieres realizar cambios.' : 'Define una contraseña segura para esta cuenta.'"></p>
                        </div>
                    </div>
                    <div class="user-form-field user-password-field">
                        <label class="form-label">
                            <span x-text="isEditing ? 'Nueva Contraseña' : 'Contraseña'"></span>
                            <span class="text-danger" x-show="!isEditing">*</span>
                        </label>
                        <div class="user-password-input">
                            <input :type="showPassword ? 'text' : 'password'" x-model="form.password" @input="validatePassword()" class="form-control" :required="!isEditing" maxlength="20" :placeholder="isEditing ? 'Introduce una nueva contraseña' : 'Crea una contraseña'">
                            <button type="button" @click="togglePassword()" aria-label="Mostrar u ocultar contraseña">
                                <i :class="showPassword ? 'fa fa-eye-slash' : 'fa fa-eye'"></i>
                            </button>
                        </div>
                        <span x-text="errors.password" x-show="errors.password" class="text-danger small mt-1"></span>
                    </div>
                </section>

                <footer class="user-form-footer">
                    <span class="user-form-required"><span class="text-danger">*</span> Campos obligatorios</span>
                    <div class="user-form-actions">
                        <button @click="closeModal()" type="button" class="user-form-cancel">Cancelar</button>
                        <button type="submit" class="btn-action" :disabled="!isFormValid()">
                            <i class="fa-solid" :class="isEditing ? 'fa-floppy-disk' : 'fa-user-plus'"></i>
                            <span x-text="isEditing ? 'Actualizar Información' : 'Crear Usuario'"></span>
                        </button>
                    </div>
                </footer>

            </form>

        </div>

    </div>

    <!-- Modal de Confirmación de Eliminación -->
    <div x-cloak :class="showDeleteModal ? 'position-fixed top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center' : 'd-none'" style="background: rgba(0,0,0,0.5); z-index: 10000;" x-transition>

        <div class="bg-white rounded-4 p-4 shadow-lg" style="max-width: 400px; width: 95%;" tabindex="-1">

            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0 fw-bold text-danger">
                    <i class="fa-solid fa-triangle-exclamation me-2"></i>
                    Confirmar Eliminación
                </h5>
                <button @click="cancelDelete()" type="button" class="btn-close" aria-label="Close"></button>
            </div>

            <p class="mb-4">¿Estás seguro de que deseas desactivar este usuario? Podrás reactivarlo desde la pestaña de Inactivos.</p>

            <div class="d-flex justify-content-end gap-2">
                <button @click="cancelDelete()" class="btn btn-secondary">Cancelar</button>
                <button @click="confirmDelete()" class="btn btn-danger">
                    <i class="fa-solid fa-ban me-2"></i>
                    Desactivar
                </button>
            </div>

        </div>

    </div>

</div>

@endsection
