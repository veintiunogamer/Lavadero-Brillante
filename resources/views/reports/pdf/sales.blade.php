<!doctype html>

<html lang="es">

<head>

    <meta charset="utf-8">
    <title>{{ $title }}</title>

    <!-- Usamos DejaVu Sans para asegurar compatibilidad con caracteres especiales en PDF -->
    <style>
        @page {
            size: A4 landscape;
            margin: 10mm 8mm 12mm 8mm;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
            color: #111827;
        }

        .header {
            width: 100%;
            margin-bottom: 10px;
            table-layout: fixed;
        }

        .logo {
            width: 105px;
        }

        .company-name {
            font-size: 16px;
            font-weight: bold;
            margin: 0;
        }

        .company-owner {
            color: #374151;
            margin: 2px 0 0;
        }

        .company-meta {
            color: #4b4c4d;
            margin: 2px 0 0;
        }

        .title {
            font-size: 18px;
            margin: 0;
        }

        .subtitle {
            color: #4b4c4d;
            margin: 2px 0 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
            table-layout: fixed;
        }

        th,
        td {
            border: 1px solid #e5e7eb;
            padding: 4px 5px;
            vertical-align: top;
            word-break: break-word;
            overflow-wrap: break-word;
            line-height: 1.25;
        }

        th {
            background: #000000;
            color: #ffffff;
            text-align: left;
            font-size: 8.5px;
        }

        tbody tr {
            page-break-inside: avoid;
        }

        .summary {
            margin-top: 16px;
            padding-top: 8px;
            border-top: 1px solid #e5e7eb;
        }

        .summary strong {
            font-size: 13px;
        }

        .summary-table {
            margin-top: 14px;
            width: 100%;
            border-collapse: separate;
            border-spacing: 8px 0;
        }

        .summary-table td {
            border: 1px solid #dbe3ea;
            border-radius: 10px;
            padding: 10px 12px;
            background: #f8fafc;
            text-align: center;
            vertical-align: middle;
        }

        .summary-label {
            display: block;
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            color: #64748b;
            margin-bottom: 4px;
            font-weight: 700;
        }

        .summary-value {
            display: block;
            font-size: 12px;
            font-weight: 800;
            color: #0f172a;
        }

        .summary-table tr:last-child td {
            background: #ecfdf5;
            border-color: #a7f3d0;
        }

        .sales-table th:nth-child(1),
        .sales-table td:nth-child(1) { width: 8%; }
        .sales-table th:nth-child(2),
        .sales-table td:nth-child(2) { width: 8%; }
        .sales-table th:nth-child(3),
        .sales-table td:nth-child(3) { width: 12%; }
        .sales-table th:nth-child(4),
        .sales-table td:nth-child(4) { width: 5%; }
        .sales-table th:nth-child(5),
        .sales-table td:nth-child(5) { width: 18%; }
        .sales-table th:nth-child(6),
        .sales-table td:nth-child(6) { width: 8%; }
        .sales-table th:nth-child(7),
        .sales-table td:nth-child(7) { width: 6%; }
        .sales-table th:nth-child(8),
        .sales-table td:nth-child(8) { width: 8%; }
        .sales-table th:nth-child(9),
        .sales-table td:nth-child(9) { width: 8%; }
        .sales-table th:nth-child(10),
        .sales-table td:nth-child(10) { width: 8%; }
        .sales-table th:nth-child(11),
        .sales-table td:nth-child(11) { width: 9%; }
        .sales-table th:nth-child(12),
        .sales-table td:nth-child(12) { width: 8%; }

        .sales-table td:nth-child(5) {
            white-space: normal;
        }
    </style>

</head>

<body>

    @php
        $company = $company ?? [
            'name' => 'Lavadero Brillante',
            'owner' => 'Eusebio Borrego Lau',
            'nif' => '28614307F',
            'address' => 'Calle Dr. Fleming, 21',
            'city' => '46960 Aldaya',
            'logo' => public_path('images/logo_alterno.png'),
        ];
    @endphp

    <table class="header">

        <tr>
            <td style="width: 140px;">
                <img src="{{ $company['logo'] }}" alt="Logo" class="logo">
            </td>

            <td style="text-align: left;">
                <h1 class="company-name">{{ $company['name'] }}</h1>
                <div class="company-owner">{{ $company['owner'] }}</div>
                <div class="company-meta">{{ $company['nif'] }} | {{ $company['address'] }} | {{ $company['city'] }}</div>
                <div class="subtitle">{{ $title }}</div>
                <div class="subtitle">Periodo: {{ $periodLabel }}</div>
            </td>

        </tr>

    </table>


    <table class="sales-table">

        <thead>
            <tr>
                <th># Orden</th>
                <th>Fecha</th>
                <th>Cliente</th>
                <th>Flota</th>
                <th>Servicios</th>

                <th>Subtotal</th>
                <th>IVA</th>
                <th>Descuento (€)</th>

                <th>Pago</th>
                <th>Método</th>
                <th>Estado</th>

                <th>Total</th>
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
                <td>{{ \Carbon\Carbon::parse($order->creation_date)->format('d/m/Y') }}</td>

                <td>{{ optional($order->client)->name ?? '--' }}</td>
                <td>{{ optional($order->client)->fleet == 1 ? 'Sí' : 'No' }}</td>

                <td>{{ $order->services->pluck('name')->join(', ') }}</td>

                <td>{{ number_format($order->subtotal, 2, ',', '.') }} €</td>
                <td>{{ number_format($order->taxes_value ?? 0, 2, ',', '.') }} €</td>
                <td>
                    @php
                    $discountValue = $order->discount_value ?? 0;
                    @endphp
                    {{ number_format($discountValue, 2, ',', '.') }} €
                </td>

                <td>
                    @php
                    $payment = $order->payments->first();
                    $paymentStatus = $payment ? ($paymentStatusLabels[$payment->status] ?? 'Desconocido') : '--';
                    @endphp
                    {{ $paymentStatus }}
                </td>
                <td>
                    @php
                    $paymentMethod = $payment ? ($paymentMethodLabels[$payment->type] ?? 'Desconocido') : '--';
                    @endphp
                    {{ $paymentMethod }}
                </td>
                <td>{{ $statusLabels[$order->status] ?? 'Desconocido' }}</td>

                <td>{{ number_format($order->total, 2, ',', '.') }} €</td>
            </tr>

            @empty

            <tr>
                <td colspan="12">No hay datos para el periodo seleccionado.</td>
            </tr>

            @endforelse

        </tbody>

    </table>

    <table class="summary-table">
        <tr>
            <td>
                <span class="summary-label">Efectivo</span>
                <span class="summary-value">{{ number_format($summary['cash'] ?? 0, 2, ',', '.') }} €</span>
            </td>
            <td>
                <span class="summary-label">TPV</span>
                <span class="summary-value">{{ number_format($summary['card'] ?? 0, 2, ',', '.') }} €</span>
            </td>
            <td>
                <span class="summary-label">Transferencia</span>
                <span class="summary-value">{{ number_format($summary['transfer'] ?? 0, 2, ',', '.') }} €</span>
            </td>
            <td>
                <span class="summary-label">Total facturado</span>
                <span class="summary-value">{{ number_format($summary['total'] ?? 0, 2, ',', '.') }} €</span>
            </td>
            <td>
                <span class="summary-label">Órdenes</span>
                <span class="summary-value">{{ $summary['orders'] ?? 0 }}</span>
            </td>
        </tr>
    </table>

</body>

</html>
