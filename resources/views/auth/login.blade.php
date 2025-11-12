<!DOCTYPE html>

<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lavadero Brillante - Login</title>

    @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/js/login.js'])
    
</head>

<body class="app-bg">

    <div class="login-container">

        <div class="login-card" x-data="loginForm()" style="max-width:500px;">
            <div class="login-logo" style="text-align:center; font-size:1.5rem;">
                <span class="lavadero">LAVADERO</span> <span class="brillante">BRILLANTE</span>
            </div>

            <h2 class="login-title" style="text-align:center; text-transform:uppercase;">Iniciar Sesión</h2>

            <form id="loginForm" @submit.prevent="submit">

                @csrf
                <div class="input-group">
                    <label for="username">Usuario</label>
                    <input type="text" name="username" id="username" class="input" required>
                </div>

                <div class="input-group">
                    <label for="password">Contraseña</label>
                    <input type="password" name="password" id="password" class="input" required>
                </div>

                <template x-if="loading">
                    <div class="spinner">Validando credenciales...</div>
                </template>
                <div class="input-group" style="margin-top:1rem;">
                    <button type="submit" class="login-btn" :disabled="loading">Acceder</button>
                </div>

                <template x-if="error">
                    <div class="login-error" x-text="error"></div>
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
