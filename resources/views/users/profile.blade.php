@extends('layouts.base')

@section('content')

    <script>
        window.userData = @json($user);
    </script>

    <div id="profile-root" class="d-flex justify-content-center align-items-start" style="min-height: 80vh; padding-top: 2rem;" x-data="typeof profileApp === 'function' ? profileApp() : {}" x-init='if (typeof profileApp === "function") initData(window.userData)'>
        
        <!-- Card de perfil -->
        <div class="card shadow-lg rounded-4 bg-white p-4 w-100" style="max-width: 900px;">
        
            <div class="col-12 mb-4 p-4">
                <h3 class="card-title mb-3">
                    <i class="fa-solid fa-user-circle icon color-blue"></i> Mi Perfil
                </h3>
                <p class="fw-bold small text-muted">Actualiza tu información personal y contraseña.</p>
            </div>

            <form @submit.prevent="saveProfile()" class="p-4">

                <div class="row gx-3 gy-4">

                    <!-- Información Personal -->
                    <div class="col-12">
                        <h5 class="fw-bold mb-3 text-primary">
                            <i class="fa-solid fa-id-card me-2"></i>
                            Información Personal
                        </h5>
                    </div>

                    <div class="col-12 col-md-6">
                        <label class="form-label fw-bold">
                            Nombre Completo&nbsp; <span class="text-danger">*</span>
                        </label>
                        <input type="text" x-model="form.name" class="form-control" required>
                    </div>

                    <div class="col-12 col-md-6">
                        <label class="form-label fw-bold">
                            Email&nbsp; <span class="text-danger">*</span>
                        </label>
                        <input type="email" x-model="form.email" class="form-control" required>
                    </div>

                    <div class="col-12 col-md-6">
                        <label class="form-label fw-bold">
                            Teléfono
                        </label>
                        <input type="tel" :value="form.phone" @input="form.phone = formatPhoneInput($event.target.value.replace(/[^0-9\s\+\(\)\-]/g, ''))" @blur="validatePhone()" class="form-control" maxlength="15" inputmode="tel">
                        <span x-text="errors.phone" x-show="errors.phone" class="text-danger small mt-1" style="font-size: 0.75rem;"></span>
                    </div>

                    <div class="col-12 col-md-6">
                        <label class="form-label fw-bold">
                            Usuario&nbsp; <span class="text-danger">*</span>
                        </label>
                        <input type="text" x-model="form.username" class="form-control" required>
                    </div>

                    <div class="col-12 col-md-6">
                        <label class="form-label fw-bold">Rol</label>
                        <input type="text" :value="user.role ? user.role.name : 'N/A'" class="form-control" disabled>
                    </div>

                    <!-- Cambio de Contraseña -->
                    <div class="col-12 mt-4">
                        <h5 class="fw-bold mb-3 text-primary">
                            <i class="fa-solid fa-lock me-2"></i>
                            Cambiar Contraseña
                        </h5>
                        <p class="small text-muted">Deja estos campos vacíos si no deseas cambiar tu contraseña.</p>
                    </div>

                    <div class="col-12 col-md-6">
                        <label class="form-label fw-bold">Contraseña Actual</label>
                        <div class="position-relative">
                            <input :type="showCurrentPassword ? 'text' : 'password'" x-model="form.current_password" class="form-control pe-5">
                            <button type="button" @click="showCurrentPassword = !showCurrentPassword" class="btn btn-outline-secondary btn-sm position-absolute top-50 end-0 translate-middle-y me-1">
                                <i :class="showCurrentPassword ? 'fa fa-eye-slash' : 'fa fa-eye'"></i>
                            </button>
                        </div>
                    </div>

                    <div class="col-12 col-md-6">
                        <label class="form-label fw-bold">Nueva Contraseña</label>
                        <div class="position-relative">
                            <input :type="showNewPassword ? 'text' : 'password'" x-model="form.new_password" @input="validateNewPassword()" class="form-control pe-5">
                            <button type="button" @click="showNewPassword = !showNewPassword" class="btn btn-outline-secondary btn-sm position-absolute top-50 end-0 translate-middle-y me-1">
                                <i :class="showNewPassword ? 'fa fa-eye-slash' : 'fa fa-eye'"></i>
                            </button>
                        </div>
                        <span x-text="errors.new_password" x-show="errors.new_password" class="text-danger small mt-1" style="font-size: 0.75rem;"></span>
                    </div>

                    <!-- Botón de guardar -->
                    <div class="col-12 d-flex justify-content-center mt-4">
                        <button type="submit" class="btn btn-success btn-lg col-lg-4 col-md-6 col-sm-12" style="min-width: 200px;" :disabled="!isFormValid()"> 
                            <i class="fa-solid fa-save me-2"></i>
                            <span>Guardar Cambios</span>
                        </button>
                    </div>

                </div>

            </form>

        </div>

    </div>

@endsection
