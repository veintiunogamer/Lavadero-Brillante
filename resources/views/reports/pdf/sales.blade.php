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
            th { background: #000000; color: #ffffff; text-align: left; }
            .summary { margin-top: 16px; padding-top: 8px; border-top: 1px solid #e5e7eb; }
            .summary strong { font-size: 13px; }
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
                    <div class="subtitle">Periodo: {{ $periodLabel }}</div>
                </td>

            </tr>

        </table>


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
                    <th>Pago</th>
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
                        <td>
                            @php
                                $discountValue = $order->discount ?? 0;
                                $discountPercent = $order->subtotal > 0 ? ($discountValue / $order->subtotal) * 100 : 0;
                            @endphp
                            {{ number_format($discountPercent, 0) }}%
                        </td>
                        <td>{{ number_format($order->total, 2, ',', '.') }} €</td>
                        <td>
                            @php
                                $payment = $order->payments->first();
                                $paymentStatus = $payment ? ($paymentStatusLabels[$payment->status] ?? 'Desconocido') : 'N/A';
                            @endphp
                            {{ $paymentStatus }}
                        </td>
                        <td>{{ $statusLabels[$order->status] ?? 'Desconocido' }}</td>
                    </tr>

                @empty

                    <tr>
                        <td colspan="9">No hay datos para el periodo seleccionado.</td>
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
