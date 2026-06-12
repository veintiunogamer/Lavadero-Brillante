<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso denegado - Lavadero Brillante</title>

    <link rel="icon" href="{{ asset('/images/icon.png') }}" type="image/x-icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        .error-403-bg {
            min-height: 100vh;
            background: radial-gradient(circle at 20% 10%, #0a5fb5 0%, var(--color-azul-bg) 45%, #03366a 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
        }

        .error-403-card {
            width: 100%;
            max-width: 700px;
            background: #ffffff;
            border-radius: 20px;
            box-shadow: 0 24px 52px rgba(2, 21, 46, 0.34);
            border: 1px solid rgba(255, 255, 255, 0.35);
            overflow: hidden;
        }

        .error-403-top {
            background: linear-gradient(120deg, var(--color-azul-logo) 0%, var(--color-azul-bg) 68%, #0e3c72 100%);
            border-bottom: 4px solid var(--color-amarillo-logo);
            color: #fff;
            padding: 2rem;
            text-align: center;
        }

        .error-403-code {
            font-size: 3.2rem;
            font-weight: 800;
            letter-spacing: 0.06em;
            margin: 0;
            line-height: 1;
        }

        .error-403-body {
            padding: 1.6rem 1.6rem 2rem;
            text-align: center;
        }

        .error-403-message {
            margin-top: 0.8rem;
            color: #475569;
            max-width: 560px;
            margin-left: auto;
            margin-right: auto;
        }

        .btn-home {
            background: #1f9f5f;
            border-color: #1f9f5f;
            color: #fff;
        }

        .btn-home:hover {
            background: #18824d;
            border-color: #18824d;
            color: #fff;
        }

        .btn-agenda {
            background: var(--color-amarillo-logo);
            border-color: var(--color-amarillo-logo);
            color: #1f2937;
            font-weight: 600;
        }

        .btn-agenda:hover {
            background: #d79612;
            border-color: #d79612;
            color: #111827;
        }

        .btn-back {
            background: var(--color-azul-logo);
            border-color: var(--color-azul-logo);
            color: #fff;
        }

        .btn-back:hover {
            background: #02478c;
            border-color: #02478c;
            color: #fff;
        }

        .error-403-actions {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 0.75rem;
            margin-top: 1.2rem;
        }

        @media (max-width: 576px) {
            .error-403-code {
                font-size: 2.5rem;
            }

            .error-403-top {
                padding: 1.4rem;
            }

            .error-403-body {
                padding: 1.1rem 1rem 1.3rem;
            }
        }
    </style>
</head>

<body>
    <div class="error-403-bg">
        <div class="error-403-card">
            <div class="error-403-top">
                <div class="mb-2" style="font-size:2rem;"><i class="fa-solid fa-shield-halved"></i></div>
                <p class="error-403-code">403</p>
                <h1 class="h4 mb-0">Acceso denegado</h1>
            </div>

            <div class="error-403-body">
                <p class="mb-2"><strong>No tienes permisos para entrar a esta sección.</strong></p>
                <p class="error-403-message mb-0">{{ $message ?? 'Si crees que esto es un error, contacta al administrador del sistema.' }}</p>

                <div class="error-403-actions">
                    <a href="{{ route('home') }}" class="btn btn-home">
                        <i class="fa-solid fa-house me-1"></i> Ir a Inicio
                    </a>
                </div>
            </div>
        </div>
    </div>
</body>

</html>