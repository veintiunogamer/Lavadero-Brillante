@extends('layouts.base')

@section('content')

<script>
    window.userData = @json($user);
</script>

<div id="profile-root" class="d-flex justify-content-center align-items-start" style="min-height: 80vh; padding-top: 2rem;" x-data="typeof profileApp === 'function' ? profileApp() : {}" x-init='if (typeof profileApp === "function") initData(window.userData)'>

    <!-- Card de perfil -->
    <div class="card shadow-lg rounded-4 bg-white p-4 w-100" style="max-width: 1400px;">

        <div class="col-lg-12 col-md-12 col-sm-12 mb-4 p-2 border-bottom border-2 d-flex flex-column align-items-start">
            <h1 class="flex align-items-center fw-bold mb-2">
                <i class="fa-solid fa-user-circle icon color-blue"></i>
                <span>Mi Perfil -</span>
                <span class="badge bg-primary">{{ $user->name }}</span>
            </h1>
            <p class="fw-bold text-muted">Actualiza tu información personal y contraseña.</p>
        </div>

        <form @submit.prevent="saveProfile()" class="form">

            <div class="col-lg-12 col-md-12 col-sm-12 d-flex gap-4 p-4">

                <div class="col-lg-6 col-md-6 col-sm-12 d-flex flex-wrap bg-light p-4">

                    <!-- Información Personal -->
                    <div class="col-12 border-bottom border-2 mb-2">
                        <h2 class="fw-bold">
                            <i class="fa-solid fa-id-card me-2 text-primary"></i>
                            Información Personal
                        </h2>
                        <p class="text-muted">Actualiza tu información personal. Los campos marcados con <span class="text-danger">*</span> son obligatorios.</p>
                    </div>

                    <!-- Nombre completo -->
                    <div class="col-lg-12 col-md-12 col-sm-12">
                        <label class="form-label fw-bold">
                            Nombre Completo&nbsp; <span class="text-danger">*</span>
                        </label>
                        <input type="text" x-model="form.name" class="input form-control" required>
                    </div>

                    <!-- Email -->
                    <div class="col-lg-12 col-md-12 col-sm-12">
                        <label class="form-label fw-bold">
                            Email&nbsp; <span class="text-danger">*</span>
                        </label>
                        <input type="email" x-model="form.email" class="input form-control" required>
                    </div>

                    <!-- Telefono -->
                    <div class="col-lg-12 col-md-12 col-sm-12">
                        <label class="form-label fw-bold">
                            Teléfono
                        </label>
                        <input type="tel" :value="form.phone" @input="form.phone = formatPhoneInput($event.target.value.replace(/[^0-9\s\+\(\)\-]/g, ''))" @blur="validatePhone()" class="input form-control" maxlength="15" inputmode="tel">
                        <span x-text="errors.phone" x-show="errors.phone" class="text-danger small mt-1" style="font-size: 0.75rem;"></span>
                    </div>

                    <!-- Usuario -->
                    <div class="col-lg-6 col-md-6 col-sm-12">
                        <label class="form-label fw-bold">
                            Usuario&nbsp; <span class="text-danger">*</span>
                        </label>
                        <input type="text" x-model="form.username" class="input form-control pe-5" required>
                    </div>

                </div>

                <div class="col-lg-6 col-md-6 col-sm-12 d-flex flex-wrap bg-light p-4">

                    <!-- Cambio de Contraseña -->
                    <div class="col-12 border-bottom border-2 mb-2">
                        <h2 class="fw-bold">
                            <i class="fa-solid fa-lock me-2 text-primary"></i>
                            Cambiar Contraseña
                        </h2>
                        <p class="text-muted">Deja estos campos vacíos si no deseas cambiar tu contraseña.</p>
                    </div>

                    <!-- Nueva Contraseña -->
                    <div class="col-lg-12 col-md-12 col-sm-12">
                        <label class="form-label fw-bold">Contraseña Actual</label>
                        <div class="position-relative">
                            <input :type="showCurrentPassword ? 'text' : 'password'" x-model="form.current_password" class="input form-control pe-5">
                            <button type="button" @click="showCurrentPassword = !showCurrentPassword" class="btn btn-success btn-lg position-absolute top-50 end-0 translate-middle-y">
                                <i :class="showCurrentPassword ? 'fa fa-eye-slash' : 'fa fa-eye'"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Nueva Contraseña (Validacion) -->
                    <div class="col-lg-12 col-md-12 col-sm-12">
                        <label class="form-label fw-bold">Nueva Contraseña</label>
                        <div class="position-relative">
                            <input :type="showNewPassword ? 'text' : 'password'" x-model="form.new_password" @input="validateNewPassword()" class="input form-control pe-5">
                            <button type="button" @click="showNewPassword = !showNewPassword" class="btn btn-success btn-lg position-absolute top-50 end-0 translate-middle-y">
                                <i :class="showNewPassword ? 'fa fa-eye-slash' : 'fa fa-eye'"></i>
                            </button>
                        </div>
                        <span x-text="errors.new_password" x-show="errors.new_password" class="text-danger small mt-1" style="font-size: 0.75rem;"></span>
                    </div>

                    <!-- Roles -->
                    <div class="col-lg-12 col-md-12 col-sm-12">
                        <label class="form-label fw-bold">Rol</label>
                        <input type="text" :value="user.role ? user.role.name : '--'" class="input form-control" disabled>
                    </div>

                </div>
            </div>

            <!-- Botón de guardar -->
            <div class="col-lg-12 col-md-12 col-sm-12 d-flex justify-content-center my-5 p-2">
                <button type="submit" class="btn btn-success btn-lg col-lg-4 col-md-6 col-sm-12" :disabled="!isFormValid()">
                    <i class="fa-solid fa-save me-2"></i>
                    <span>Guardar Cambios</span>
                </button>
            </div>

        </form>

    </div>

</div>

@endsection