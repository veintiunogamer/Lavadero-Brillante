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
            .summary { margin-top: 16px; padding-top: 8px; border-top: 1px solid #e5e7eb; }
            .summary strong { font-size: 13px; }
        </style>
    </head>
    <body>
        <h1>{{ $title }}</h1>
        <div class="subtitle">Periodo: {{ $periodLabel }}</div>

        <table>
            <thead>
                <tr>
                    <th>Orden</th>
                    <th>Cliente</th>
                    <th>Servicios</th>
                    <th>Fecha</th>
                    <th>Subtotal</th>
                    <th>Descuento %</th>
                    <th>Total</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>
                @forelse($orders as $order)
                    <tr>
                        <td>
                            @php
                                $orderNumber = $order->consecutive_serial && $order->consecutive_number
                                    ? $order->consecutive_serial . '-' . $order->consecutive_number
                                    : strtoupper(substr($order->id, 0, 8));
                            @endphp
                            {{ $orderNumber }}
                        </td>
                        <td>{{ optional($order->client)->name ?? 'N/A' }}</td>
                        <td>{{ $order->services->pluck('name')->join(', ') }}</td>
                        <td>{{ \Carbon\Carbon::parse($order->creation_date)->format('d/m/Y') }}</td>
                        <td>{{ number_format($order->subtotal, 2, ',', '.') }} €</td>
                        <td>{{ number_format($order->discount ?? 0, 0) }}%</td>
                        <td>{{ number_format($order->total, 2, ',', '.') }} €</td>
                        <td>{{ $statusLabels[$order->status] ?? 'Desconocido' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8">No hay datos para el periodo seleccionado.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="summary">
            <div>Total facturado: <strong>{{ number_format($summary['total'] ?? 0, 2, ',', '.') }} €</strong></div>
            <div>Órdenes: <strong>{{ $summary['orders'] ?? 0 }}</strong></div>
        </div>
    </body>
</html>
