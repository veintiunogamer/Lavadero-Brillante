<!doctype html>

<html lang="es">

    <head>

        <meta charset="utf-8">
        <title>{{ $title }}</title>

        <!-- Usamos DejaVu Sans para asegurar compatibilidad con caracteres especiales en PDF -->
        <style>
            body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #111827; }
            .header { width: 100%; margin-bottom: 12px; }
            .logo { width: 120px; }
            .title { font-size: 18px; margin: 0; }
            .subtitle { color: #4b4c4d; margin: 2px 0 0; }
            table { width: 100%; border-collapse: collapse; margin-top: 8px; }
            th, td { border: 1px solid #e5e7eb; padding: 6px 8px; vertical-align: top; }
            th { background: #f3f4f6; text-align: left; }
        </style>

    </head>

    <body>

        <table class="header">

            <tr>
                <td style="width: 140px;">
                    <img src="{{ public_path('images/logo_alterno.png') }}" alt="Logo" class="logo">
                </td>
                <td style="text-align: left;">
                    <h1 class="title">{{ $title }}</h1>
                    <div class="subtitle">{{ $periodLabel }}</div>
                </td>
            </tr>

        </table>


        <table>

            <thead>
                <tr>
                    <th>Cliente</th>
                    <th>Teléfono</th>
                    <th>Matrícula</th>
                    <th>Citas</th>
                    <th>Total gastado</th>
                    <th>Última visita</th>
                </tr>
            </thead>

            <tbody>

                @forelse($clients as $client)
                    <tr>
                        <td>{{ $client->name }}</td>
                        <td>{{ $client->phone ?? 'N/A' }}</td>
                        <td>{{ $client->license_plaque ?? 'N/A' }}</td>
                        <td>{{ $client->orders_count }}</td>
                        <td>{{ number_format($client->total_spent ?? 0, 2, ',', '.') }} €</td>
                        <td>
                            {{ $client->last_order_date ? \Carbon\Carbon::parse($client->last_order_date)->format('d/m/Y') : 'N/A' }}
                        </td>
                    </tr>
                @empty

                    <tr>
                        <td colspan="6">No hay datos para mostrar.</td>
                    </tr>

                @endforelse

            </tbody>

        </table>

    </body>

</html>
