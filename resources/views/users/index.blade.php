@extends('layouts.base')

@section('content')

    <script>
        window.usersData = @json($users);
        window.rolesData = @json($roles);
    </script>

    <div id="usuarios-root" class="d-flex justify-content-center align-items-start" style="min-height: 80vh; padding-top: 2rem;" x-data="typeof usuariosApp === 'function' ? usuariosApp() : {}" x-init='if (typeof usuariosApp === "function") initData(window.usersData, window.rolesData)'>
        
        <!-- Tabla con los usuarios -->
        <div class="card shadow-lg rounded-4 bg-white p-4 w-100" style="max-width: 1400px;">
        
            <div class="col-12 d-flex justify-content-between align-items-center mb-3 p-4">

                <div class="col-6">
                    <h3 class="card-title mb-3"><i class="fa-solid fa-user-cog icon color-blue"></i> Usuarios</h3>
                    <p class="fw-bold small text-muted">Listado y gestión de usuarios del sistema.</p>
                </div>
                
                <div class="col-6">
                    <button @click="openModal()" class="btn btn-success mb-3 float-end">
                        <i class="fa-solid fa-plus me-2"></i>
                        Crear Usuario
                    </button>
                </div>

            </div>

            <div class="table-responsive mt-4 p-4">
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
                        <template x-for="(user, index) in users" :key="index">
                            <tr>
                                <td x-text="user.name"></td>
                                <td x-text="user.email"></td>
                                <td x-text="user.phone || 'N/A'"></td>
                                <td x-text="user.username"></td>
                                <td x-text="user.role ? user.role.name : 'N/A'"></td>
                                <td x-text="new Date(user.creation_date).toLocaleDateString()"></td>
                                <td>
                                    <button @click="editUser(user)" class="btn btn-sm btn-warning me-1">Editar</button>
                                    <button @click="deleteUser(user.id)" class="btn btn-sm btn-danger">Eliminar</button>
                                </td>
                            </tr>
                        </template>
                    </tbody>

                </table>

            </div>

        </div>

        <!-- Modal para Crear/Editar Usuario (moved inside x-data scope) -->
        <div x-cloak @click.self="closeModal()" @keydown.escape.window="closeModal()" :class="showModal ? 'position-fixed top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center' : 'd-none'" style="background: rgba(0,0,0,0.5); z-index: 9999;" x-transition>
            
            <div class="bg-white rounded-4 p-4 shadow-lg" style="max-width: 900px; width: 95%;" tabindex="-1">
                
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h4 class="mb-0 fw-bold" x-text="isEditing ? 'Editar Usuario' : 'Crear Usuario'">
                        <i class="fa-solid color-blue" :class="typeof isEditing !== 'undefined' && isEditing ? 'fa-edit' : 'fa-plus'"></i>
                    </h4>
                    <button @click="closeModal()" type="button" class="btn-close" aria-label="Close"></button>
                </div>

                <form @submit.prevent="saveUser()">

                    <div class="row gx-3 gy-3 justify-content-center">

                        <div class="col-12 col-lg-3">
                            <label class="form-label fw-bold">
                                Nombre&nbsp; <span class="text-danger">*</span>
                            </label>
                            <input type="text" x-model="form.name" class="form-control" required>
                        </div>

                        <div class="col-12 col-lg-3">
                            <label class="form-label fw-bold">
                                Email&nbsp; <span class="text-danger">*</span>
                            </label>
                            <input type="email" x-model="form.email" class="form-control" required>
                        </div>

                        <div class="col-12 col-lg-3">
                            <label class="form-label fw-bold">
                                Teléfono&nbsp; <span class="text-danger">*</span>
                            </label>
                            <input type="tel" :value="form.phone" @input="form.phone = formatPhoneInput($event.target.value.replace(/[^0-9\s\+\(\)\-]/g, ''))" @blur="validatePhone()" x-ref="phoneInput" class="form-control" maxlength="15" inputmode="tel">
                            <span x-text="errors.phone" x-show="errors.phone" class="text-danger small mt-1" style="font-size: 0.75rem;"></span>
                        </div>

                        <div class="col-12 col-lg-3">
                            <label class="form-label fw-bold">
                                Usuario&nbsp; <span class="text-danger">*</span>
                            </label>
                            <input type="text" x-model="form.username" class="form-control" required>
                        </div>

                        <div class="col-12 col-lg-3">
                            <label class="form-label fw-bold">
                                Rol&nbsp; <span class="text-danger">*</span>
                            </label>
                            <select x-model="form.rol" class="form-select" required>
                                <option value="">Seleccionar Rol</option>
                                <template x-for="role in roles" :key="role.id">
                                    <option :value="role.id" x-text="role.name"></option>
                                </template>
                            </select>
                        </div>

                        <div class="col-12 col-lg-3" x-show="isEditing">
                            <label class="form-label fw-bold">Estado</label>
                            <select x-model="form.status" class="form-select">
                                <option value="1">Activo</option>
                                <option value="0">Inactivo</option>
                            </select>
                        </div>

                        <div class="col-12 col-lg-3" x-show="!isEditing">
                            <label class="form-label fw-bold">
                                Contraseña&nbsp; <span class="text-danger">*</span>
                            </label>
                            <div class="position-relative">
                                <input :type="showPassword ? 'text' : 'password'" x-model="form.password" @input="validatePassword()" class="form-control pe-5" :required="!isEditing">
                                <button type="button" @click="togglePassword()" class="btn btn-outline-secondary btn-sm position-absolute top-50 end-0 translate-middle-y me-1">
                                    <i :class="showPassword ? 'fa fa-eye-slash' : 'fa fa-eye'"></i>
                                </button>
                            </div>
                            <span x-text="errors.password" x-show="errors.password" class="text-danger small mt-1" style="font-size: 0.75rem;"></span>
                        </div>

                        <div class="col-12 col-lg-3" x-show="isEditing">
                            <label class="form-label fw-bold">Nueva Contraseña</label>
                            <div class="position-relative">
                                <input :type="showPassword ? 'text' : 'password'" x-model="form.password" @input="validatePassword()" class="form-control pe-5">
                                <button type="button" @click="togglePassword()" class="btn btn-outline-secondary btn-sm position-absolute top-50 end-0 translate-middle-y me-1">
                                    <i :class="showPassword ? 'fa fa-eye-slash' : 'fa fa-eye'"></i>
                                </button>
                            </div>
                            <span x-text="errors.password" x-show="errors.password" class="text-danger small mt-1" style="font-size: 0.75rem;"></span>
                        </div>

                        <div class="col-12 d-flex justify-content-center mt-3">
                            <button type="submit" class="btn btn-success btn-lg col-lg-4 col-md-6 col-sm-12" style="min-width: 180px;" :disabled="!isFormValid()"> 
                                <span x-text="isEditing ? 'Actualizar Informacion' : 'Crear Usuario'"></span>
                            </button>
                        </div>

                    </div>

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

                <p class="mb-4">¿Estás seguro de que deseas eliminar este usuario? Esta acción no se puede deshacer.</p>

                <div class="d-flex justify-content-end gap-2">
                    <button @click="cancelDelete()" class="btn btn-secondary">Cancelar</button>
                    <button @click="confirmDelete()" class="btn btn-danger">
                        <i class="fa-solid fa-trash me-2"></i>
                        Eliminar
                    </button>
                </div>

            </div>

        </div>

    </div>

@endsection