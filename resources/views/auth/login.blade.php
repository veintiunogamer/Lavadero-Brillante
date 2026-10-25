<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lavadero Brillante - Iniciar sesión</title>
    <link rel="icon" href="{{ asset('/images/icon.png') }}" type="image/x-icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/js/login.js'])
</head>

<body class="app-bg">
    <main class="login-container" style="background-image: url('{{ asset('/images/bg_login.png') }}')">
        <section class="login-card" x-data="loginForm()" x-init="init()" aria-labelledby="login-title">
            <div class="login-logo">
                <img src="{{ asset('/images/logo_alterno.png') }}" alt="Lavadero Brillante" class="login-image">
            </div>

            <h1 id="login-title" class="login-title">Iniciar Sesión</h1>
            <p class="login-desc">Ingresa tus credenciales para acceder al sistema</p>

            <form id="loginForm" class="login-form" @submit.prevent="submit">
                @csrf

                <div class="login-field">
                    <label for="username">Usuario</label>
                    <div class="login-input-wrap">
                        <i class="fa-solid fa-user login-field-icon" aria-hidden="true"></i>
                        <input type="text" name="username" id="username" class="input login-input"
                            x-model="username" autocomplete="username" required>
                    </div>
                </div>

                <div class="login-field">
                    <div class="login-label-row">
                        <label for="password">Contraseña</label>
                    </div>
                    <div class="login-input-wrap">
                        <i class="fa-solid fa-lock login-field-icon" aria-hidden="true"></i>
                        <input :type="showPassword ? 'text' : 'password'" name="password" id="password"
                            class="input login-input login-password-input" x-model="password"
                            autocomplete="current-password" required>
                        <button class="password-toggle" type="button" @click="showPassword = !showPassword"
                            :aria-label="showPassword ? 'Ocultar contraseña' : 'Mostrar contraseña'"
                            :aria-pressed="showPassword.toString()">
                            <i :class="showPassword ? 'fa-solid fa-eye-slash' : 'fa-solid fa-eye'" aria-hidden="true"></i>
                        </button>
                    </div>
                </div>

                <label class="remember-option" for="remember-user">
                    <input type="checkbox" id="remember-user" x-model="rememberUser">
                    <span>Recordar sesión</span>
                </label>

                <template x-if="loading">
                    <p class="login-status" role="status">Validando credenciales...</p>
                </template>

                <button type="submit" class="login-btn" :disabled="loading">
                    <span x-text="loading ? 'Ingresando...' : 'Ingresar'"></span>
                    <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
                </button>

                <template x-if="error">
                    <div class="login-error" role="alert" x-text="error"></div>
                </template>
            </form>

            <footer class="login-footer">© {{ now()->year }} Lavadero Brillante. Todos los derechos reservados.</footer>
        </section>
    </main>

    <script>
        function loginForm() {
            return {
                username: '',
                password: '',
                showPassword: false,
                rememberUser: false,
                loading: false,
                error: '',
                init() {
                    try {
                        const rememberedUsername = localStorage.getItem('lavadero_remembered_username');
                        if (rememberedUsername) {
                            this.username = rememberedUsername;
                            this.rememberUser = true;
                        }
                    } catch (error) {
                        // El almacenamiento puede estar deshabilitado; el login sigue funcionando sin recordar usuario.
                    }
                },
                async submit() {
                    this.loading = true;
                    this.error = '';

                    try {
                        const response = await fetch("{{ route('login') }}", {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({
                                username: this.username,
                                password: this.password
                            })
                        });
                        const data = await response.json();

                        if (response.ok && data.success) {
                            try {
                                if (this.rememberUser) {
                                    localStorage.setItem('lavadero_remembered_username', this.username);
                                } else {
                                    localStorage.removeItem('lavadero_remembered_username');
                                }
                            } catch (error) {
                                // El almacenamiento es opcional y nunca debe impedir una autenticación exitosa.
                            }
                            window.notyf.success('¡Bienvenido!');
                            window.location.href = '/';
                            return;
                        }

                        this.error = data.message || 'Credenciales incorrectas';
                        window.notyf.error(this.error);
                    } catch (error) {
                        this.error = 'Error de conexión';
                        window.notyf.error(this.error);
                    } finally {
                        this.loading = false;
                    }
                }
            };
        }
    </script>
</body>

</html>
