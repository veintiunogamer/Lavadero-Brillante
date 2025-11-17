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

        </div>

    </div>

    <!-- Modal para Crear/Editar Usuario -->
    <div x-show="showModal" class="modal fade show" style="display: none; background: rgba(0,0,0,0.5);" x-transition>
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" x-text="isEditing ? 'Editar Usuario' : 'Crear Usuario'"></h5>
                    <button @click="closeModal()" type="button" class="btn-close" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form @submit.prevent="saveUser()">
                        <div class="mb-3">
                            <label for="name" class="form-label">Nombre</label>
                            <input type="text" x-model="form.name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" x-model="form.email" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="phone" class="form-label">Teléfono</label>
                            <input type="text" x-model="form.phone" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label for="username" class="form-label">Usuario</label>
                            <input type="text" x-model="form.username" class="form-control" required>
                        </div>
                        <div class="mb-3" x-show="!isEditing">
                            <label for="password" class="form-label">Contraseña</label>
                            <input type="password" x-model="form.password" class="form-control" required>
                        </div>
                        <div class="mb-3" x-show="isEditing">
                            <label for="password" class="form-label">Nueva Contraseña (opcional)</label>
                            <input type="password" x-model="form.password" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label for="rol" class="form-label">Rol</label>
                            <select x-model="form.rol" class="form-select" required>
                                <option value="">Seleccionar Rol</option>
                                <template x-for="role in roles" :key="role.id">
                                    <option :value="role.id" x-text="role.name"></option>
                                </template>
                            </select>
                        </div>
                        <div class="mb-3" x-show="isEditing">
                            <label for="status" class="form-label">Estado</label>
                            <select x-model="form.status" class="form-select">
                                <option value="1">Activo</option>
                                <option value="0">Inactivo</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary w-100" x-text="isEditing ? 'Actualizar' : 'Crear'"></button>
                    </form>
                </div>
            </div>
        </div>
    </div>

@endsection