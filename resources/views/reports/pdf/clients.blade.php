<!doctype html>
<html lang="es">
    <head>
        <meta charset="utf-8">
        <title>{{ $title }}</title>
        <style>
            body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #111827; }
            h1 { font-size: 18px; margin-bottom: 4px; }
            .subtitle { color: #6b7280; margin-bottom: 16px; }
            table { width: 100%; border-collapse: collapse; margin-top: 8px; }
            th, td { border: 1px solid #e5e7eb; padding: 6px 8px; vertical-align: top; }
            th { background: #f3f4f6; text-align: left; }
        </style>
    </head>
    <body>
        <h1>{{ $title }}</h1>
        <div class="subtitle">{{ $periodLabel }}</div>

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
