@extends('layouts.base')
@section('content')
<div id="usuarios-root" class="card-form" x-data="typeof usuariosApp === 'function' ? usuariosApp() : {}" x-init='if (typeof usuariosApp === "function") initData(@json($users), @json($roles))'>
    <h2 class="card-title"><i class="fa-solid fa-user-cog icon"></i> Usuarios</h2>
    <p>Listado y gestión de usuarios del sistema.</p>

    <div class="mb-4">
        <button @click="openModal()" class="btn btn-primary">Crear Nuevo Usuario</button>
    </div>

    <div class="table-responsive">
        <table class="table table-striped">
            <thead>
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
                            <span :class="user.status ? 'badge badge-success' : 'badge badge-danger'" x-text="user.status ? 'Activo' : 'Inactivo'"></span>
                        </td>
                        <td x-text="new Date(user.creation_date).toLocaleDateString()"></td>
                        <td>
                            <button @click="editUser(user)" class="btn btn-sm btn-warning">Editar</button>
                            <button @click="deleteUser(user.id)" class="btn btn-sm btn-danger">Eliminar</button>
                        </td>
                    </tr>
                </template>
            </tbody>
        </table>
    </div>

    <!-- Modal para Crear/Editar Usuario -->
    <div x-show="showModal" class="modal fade show" style="display: none;" x-transition>
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" x-text="isEditing ? 'Editar Usuario' : 'Crear Usuario'"></h5>
                    <button @click="closeModal()" type="button" class="close">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form @submit.prevent="saveUser()">
                        <div class="form-group">
                            <label for="name">Nombre</label>
                            <input type="text" x-model="form.name" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" x-model="form.email" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="phone">Teléfono</label>
                            <input type="text" x-model="form.phone" class="form-control">
                        </div>
                        <div class="form-group">
                            <label for="username">Usuario</label>
                            <input type="text" x-model="form.username" class="form-control" required>
                        </div>
                        <div class="form-group" x-show="!isEditing">
                            <label for="password">Contraseña</label>
                            <input type="password" x-model="form.password" class="form-control" required>
                        </div>
                        <div class="form-group" x-show="isEditing">
                            <label for="password">Nueva Contraseña (opcional)</label>
                            <input type="password" x-model="form.password" class="form-control">
                        </div>
                        <div class="form-group">
                            <label for="rol">Rol</label>
                            <select x-model="form.rol" class="form-control" required>
                                <option value="">Seleccionar Rol</option>
                                <template x-for="role in roles" :key="role.id">
                                    <option :value="role.id" x-text="role.name"></option>
                                </template>
                            </select>
                        </div>
                        <div class="form-group" x-show="isEditing">
                            <label for="status">Estado</label>
                            <select x-model="form.status" class="form-control">
                                <option value="1">Activo</option>
                                <option value="0">Inactivo</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary" x-text="isEditing ? 'Actualizar' : 'Crear'"></button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <div x-show="showModal" class="modal-backdrop fade show"></div>
</div>

@endsection