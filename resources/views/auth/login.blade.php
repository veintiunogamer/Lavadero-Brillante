<!DOCTYPE html>

<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lavadero Brillante - Login</title>

    <link rel="icon" href="{{ asset('/images/icon.png') }}" type="image/x-icon">

    <!-- Font Awesome CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Bootstrap 5 CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/js/login.js'])

</head>

<body class="app-bg">

    <div class="login-container">

        <div class="login-card" x-data="loginForm()">

            <div class="login-logo" style="text-align:center;">
                <img src="{{ asset('/images/logo_alterno.png') }}" alt="Logo" class="login-image" width="60%">
            </div>

            <h3 class="text-dark fw-bold my-3" style="text-align:center;">
                Iniciar Sesión
            </h3>

            <form id="loginForm" @submit.prevent="submit">

                @csrf

                <div class="input-group my-2">
                    <label class="fw-bold text-primary fs-5" for="username">
                        <i class="fa fa-user text-warning"></i>&nbsp;
                        Usuario
                    </label>
                    <input type="text" name="username" id="username" class="input" required>
                </div>

                <div class="input-group my-3">
                    <label class="fw-bold text-primary fs-5" for="password">
                        <i class="fa fa-lock text-warning"></i>&nbsp;
                        Contraseña
                    </label>
                    <input type="password" name="password" id="password" class="input" required>
                </div>

                <template x-if="loading">
                    <div class="spinner">Validando credenciales...</div>
                </template>

                <div class="input-group my-3">
                    <button type="submit" class="login-btn" :disabled="loading">Ingresar</button>
                </div>

                <template x-if="error">
                    <div class="login-error fw-bold p-3 fs-5" x-html="'<i class=\'fa fa-exclamation-triangle\'></i> ' + error">

                    </div>
                </template>

            </form>

        </div>

    </div>

    <script>
        function loginForm() {
            return {
                loading: false,
                error: '',
                submit() {
                    this.loading = true;
                    this.error = '';
                    fetch("{{ route('login') }}", {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({
                                username: document.getElementById('username').value,
                                password: document.getElementById('password').value
                            })
                        })
                        .then(res => res.json())
                        .then(data => {
                            this.loading = false;
                            if (data.success) {
                                window.notyf.success('¡Bienvenido!');
                                window.location.href = '/';
                            } else {
                                this.error = data.message || 'Credenciales incorrectas';
                                window.notyf.error(this.error);
                            }
                        })
                        .catch(() => {
                            this.loading = false;
                            this.error = 'Error de conexión';
                            window.notyf.error('Error de conexión');
                        });
                }
            }
        }
    </script>
</body>

</html>