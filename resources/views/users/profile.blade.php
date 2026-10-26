@extends('layouts.base')

@section('content')
    <script>
        window.userData = @json($user);
    </script>

    <div id="profile-root" class="profile-page d-flex justify-content-center align-items-start" x-data="typeof profileApp === 'function' ? profileApp() : {}"
        x-init='if (typeof profileApp === "function") initData(window.userData)'>

        <!-- Card de perfil -->
        <div class="profile-shell card shadow-lg rounded-4 bg-white p-4 w-100" style="max-width: 1400px;">

            <div class="profile-heading">

                <h1 class="fw-bold mb-1"><i class="fa-solid fa-user-circle profile-title-icon" aria-hidden="true"></i>Mi Perfil
                </h1>
                <p class="text-muted mb-0">Administra tu cuenta, datos personales y seguridad</p>

            </div>

            <form @submit.prevent="saveProfile()" class="form">

                <div class="profile-grid">

                    <section class="profile-panel">

                        <!-- Información Personal -->
                        <div class="profile-panel-heading">
                            <span class="profile-heading-icon profile-heading-icon--blue"><i
                                    class="fa-regular fa-id-card"></i></span>
                            <div>
                                <h2>Información Personal</h2>
                                <p>Actualiza tu información personal. Los campos marcados con <span
                                        class="text-danger">*</span> son obligatorios.</p>
                            </div>
                        </div>

                        <!-- Nombre completo -->
                        <div class="profile-field">
                            <label class="form-label">
                                Nombre Completo&nbsp; <span class="text-danger">*</span>
                            </label>
                            <input type="text" x-model="form.name" class="input form-control" required>
                        </div>

                        <!-- Email -->
                        <div class="profile-field">
                            <label class="form-label">
                                Email&nbsp; <span class="text-danger">*</span>
                            </label>
                            <input type="email" x-model="form.email" class="input form-control" required>
                        </div>

                        <!-- Telefono -->
                        <div class="profile-field">
                            <label class="form-label">
                                Teléfono
                            </label>
                            <input type="tel" :value="form.phone"
                                @input="form.phone = formatPhoneInput($event.target.value.replace(/[^0-9\s\+\(\)\-]/g, ''))"
                                @blur="validatePhone()" class="input form-control" maxlength="15" inputmode="tel">
                            <span x-text="errors.phone" x-show="errors.phone" class="text-danger small mt-1"
                                style="font-size: 0.75rem;"></span>
                        </div>

                        <!-- Usuario -->
                        <div class="profile-field">
                            <label class="form-label">
                                Usuario&nbsp; <span class="text-danger">*</span>
                            </label>
                            <input type="text" x-model="form.username" class="input form-control pe-5" required>
                        </div>

                    </section>

                    <section class="profile-panel">

                        <!-- Cambio de Contraseña -->
                        <div class="profile-panel-heading">
                            <span class="profile-heading-icon profile-heading-icon--yellow"><i
                                    class="fa-solid fa-lock"></i></span>
                            <div>
                                <h2>Cambiar Contraseña</h2>
                                <p>Deja estos campos vacíos si no deseas cambiar tu contraseña.</p>
                            </div>
                        </div>

                        <!-- Nueva Contraseña -->
                        <div class="profile-field">
                            <label class="form-label">Contraseña Actual</label>
                            <div class="position-relative">
                                <input :type="showCurrentPassword ? 'text' : 'password'" x-model="form.current_password"
                                    class="input form-control pe-5">
                                <button type="button" @click="showCurrentPassword = !showCurrentPassword"
                                    class="profile-password-toggle position-absolute top-50 end-0 translate-middle-y">
                                    <i :class="showCurrentPassword ? 'fa fa-eye-slash' : 'fa fa-eye'"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Nueva Contraseña (Validacion) -->
                        <div class="profile-field">
                            <label class="form-label">Nueva Contraseña</label>
                            <div class="position-relative">
                                <input :type="showNewPassword ? 'text' : 'password'" x-model="form.new_password"
                                    @input="validateNewPassword()" class="input form-control pe-5">
                                <button type="button" @click="showNewPassword = !showNewPassword"
                                    class="profile-password-toggle position-absolute top-50 end-0 translate-middle-y">
                                    <i :class="showNewPassword ? 'fa fa-eye-slash' : 'fa fa-eye'"></i>
                                </button>
                            </div>
                            <span x-text="errors.new_password" x-show="errors.new_password" class="text-danger small mt-1"
                                style="font-size: 0.75rem;"></span>
                        </div>

                        <!-- Roles -->
                        <div class="profile-field">
                            <label class="form-label">Rol</label>
                            <input type="text" :value="user.role ? user.role.name : '--'" class="input form-control"
                                disabled>
                        </div>

                    </section>
                </div>

                <!-- Botón de guardar -->
                <div class="profile-submit">
                    <button type="submit" class="btn-action btn-action--large" :disabled="!isFormValid()">
                        <i class="fa-solid fa-save me-2"></i>
                        <span>Guardar Cambios</span>
                    </button>
                </div>

            </form>

        </div>

    </div>
@endsection
