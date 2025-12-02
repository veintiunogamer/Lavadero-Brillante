@extends('layouts.base')

@section('content')

    <div id="usuarios-root" class="d-flex justify-content-center align-items-start" style="min-height: 80vh; padding-top: 2rem;" x-data="typeof usuariosApp === 'function' ? usuariosApp() : {}" x-init='if (typeof usuariosApp === "function") initData(@json($users), @json($roles))'>
        
        <div class="card shadow-lg rounded-4 bg-white p-4 w-100" style="max-width: 1200px;">
        
            <div class="col-12 d-flex justify-content-between align-items-center mb-3">

                <div class="col-6">
                    <h2 class="card-title mb-3"><i class="fa-solid fa-user-cog icon color-blue"></i> Usuarios</h2>
                    <p class="fw-bold">Listado y gestión de usuarios del sistema.</p>
                </div>
                
                <div class="col-6">
                    <button @click="openModal()" class="btn btn-primary mb-3 float-end">
                        <i class="fa-solid fa-plus me-2"></i>
                        Crear Nuevo Usuario
                    </button>
                </div>

            </div>

            <div class="table-responsive mt-4">
                <table class="table table-striped table-bordered align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>Nombre</th>
                            <th>Email</th>
                            <th>Teléfono</th>
                            <th>Usuario</th>
                            <th>Rol</th>
                            <th>Estado</th>
                            <th>Fecha Creación</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>

                    <tbody>
                        <template x-for="user in users" :key="user.id">
                            <tr>
                                <td x-text="user.name"></td>
                                <td x-text="user.email"></td>
                                <td x-text="user.phone || 'N/A'"></td>
                                <td x-text="user.username"></td>
                                <td x-text="user.role ? user.role.name : 'N/A'"></td>
                                <td>
                                    <span :class="user.status ? 'badge bg-success' : 'badge bg-danger'" x-text="user.status ? 'Activo' : 'Inactivo'"></span>
                                </td>
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

        <!-- Modal para Crear/Editar Usuario (moved inside x-data scope) -->
        <div x-cloak @click.self="closeModal()" @keydown.escape.window="closeModal()" :class="showModal ? 'position-fixed top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center' : 'd-none'" style="background: rgba(0,0,0,0.5); z-index: 9999;" x-transition>
            <div class="bg-white rounded-4 p-4 shadow-lg" style="max-width: 900px; width: 95%;" tabindex="-1">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0" x-text="isEditing ? 'Editar Usuario' : 'Crear Usuario'"></h5>
                    <button @click="closeModal()" type="button" class="btn-close" aria-label="Close"></button>
                </div>

                <form @submit.prevent="saveUser()">

                    <div class="row gx-3 gy-3 justify-content-center">

                        <div class="col-12 col-lg-3">
                            <label class="form-label fw-bold">Nombre</label>
                            <input type="text" x-model="form.name" class="form-control" required>
                        </div>

                        <div class="col-12 col-lg-3">
                            <label class="form-label fw-bold">Email</label>
                            <input type="email" x-model="form.email" class="form-control" required>
                        </div>

                        <div class="col-12 col-lg-3">
                            <label class="form-label fw-bold">Teléfono</label>
                            <input type="text" :value="form.phone" @input="form.phone = formatPhoneInput($event.target.value)" @blur="validatePhone()" x-ref="phoneInput" class="form-control" maxlength="15">
                            <span x-text="errors.phone" x-show="errors.phone" class="text-danger small mt-1" style="font-size: 0.75rem;"></span>
                        </div>

                        <div class="col-12 col-lg-3">
                            <label class="form-label fw-bold">Usuario</label>
                            <input type="text" x-model="form.username" class="form-control" required>
                        </div>

                        <div class="col-12 col-lg-3" x-show="!isEditing">
                            <label class="form-label fw-bold">Contraseña</label>
                            <input type="password" x-model="form.password" class="form-control" required>
                        </div>

                        <div class="col-12 col-lg-3" x-show="isEditing">
                            <label class="form-label fw-bold">Nueva Contraseña (opcional)</label>
                            <input type="password" x-model="form.password" class="form-control">
                        </div>

                        <div class="col-12 col-lg-3">
                            <label class="form-label fw-bold">Rol</label>
                            <select x-model="form.rol" class="form-select" required>
                                <option value="">Seleccionar Rol</option>
                                <template x-for="role in roles" :key="role.id">
                                    <option :value="role.id" x-text="role.name"></option>
                                </template>
                            </select>
                        </div>

                        <div class="col-12 col-lg-3" x-show="isEditing">
                            <label class="form-label">Estado</label>
                            <select x-model="form.status" class="form-select">
                                <option value="1">Activo</option>
                                <option value="0">Inactivo</option>
                            </select>
                        </div>

                        <div class="col-12 d-flex justify-content-center mt-3">
                            <button type="submit" class="btn btn-success" style="min-width: 180px;" :disabled="!isFormValid()"> 
                                <span x-text="isEditing ? 'Actualizar' : 'Crear'"></span>
                            </button>
                        </div>

                    </div>

                </form>

            </div>

        </div>

    </div>

@endsection