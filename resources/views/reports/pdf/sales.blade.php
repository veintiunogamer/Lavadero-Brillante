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

        /* Repite los encabezados en cada página cuando la tabla continúa */
        thead {
            display: table-header-group;
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

        /* Tabla de 17 columnas: tipografía y padding reducidos para legibilidad */
        .sales-table {
            font-size: 8px;
        }

        .sales-table th,
        .sales-table td {
            padding: 3px 4px;
        }

        .sales-table th {
            font-size: 7.5px;
            text-align: center;
            vertical-align: middle;
        }

        .sales-table td:nth-child(10),
        .sales-table td:nth-child(11),
        .sales-table td:nth-child(12),
        .sales-table td:nth-child(16) {
            text-align: right;
        }

        tbody tr {
            page-break-inside: avoid;
        }

        /* Badges de entrada y salida: cada hora en su propia píldora */
        .hour-badges {
            white-space: nowrap;
        }

        .hour-badge {
            display: inline-block;
            padding: 1px 5px;
            border: 1px solid;
            border-radius: 9px;
            font-size: 7.5px;
            font-weight: bold;
            line-height: 1.3;
            text-align: center;
        }

        .hour-badge--in {
            background-color: #d1fae5;
            border-color: #6ee7b7;
            color: #065f46;
        }

        .hour-badge--out {
            background-color: #fee2e2;
            border-color: #fca5a5;
            color: #991b1b;
        }

        .hour-badge--empty {
            background-color: #f3f4f6;
            border-color: #d1d5db;
            color: #6b7280;
        }

        .hour-sep {
            color: #9ca3af;
            padding: 0 2px;
        }

        /* Placa europea con la matrícula (versión compacta para tablas) */
        .eu-plate {
            display: inline-block;
            border: 1px solid #b3bcc6;
            border-radius: 4px;
            background-color: #ffffff;
            font-weight: bold;
            line-height: 1;
            white-space: nowrap;
            vertical-align: middle;
        }

        .eu-plate__band {
            display: inline-block;
            width: 20px;
            height: 14px;
            background-color: #003399;
            border-right: 1px solid rgba(255, 255, 255, 0.55);
            vertical-align: top;
        }

        .eu-plate__band img {
            display: block;
            width: 20px;
            height: 14px;
        }

        .eu-plate__body {
            display: inline-block;
            padding: 2px 5px;
            color: #111111;
            font-size: 8px;
            letter-spacing: 0.04em;
            vertical-align: top;
        }

        .eu-plate__number {
            font-weight: 800;
            margin-right: 4px;
        }

        .eu-plate__letters {
            font-weight: 700;
        }

        .eu-plate__raw {
            font-weight: 700;
        }

        .eu-plate--empty {
            padding: 1px 4px;
            color: #6b7280;
            background-color: #f3f4f6;
            border-color: #d1d5db;
            font-size: 7.5px;
            font-weight: 600;
        }

        .eu-plate--empty .eu-plate__band {
            display: none;
        }

        /* Variante compacta para la tabla de 17 columnas */
        .eu-plate--sm .eu-plate__band {
            width: 16px;
            height: 12px;
        }

        .eu-plate--sm .eu-plate__band img {
            width: 16px;
            height: 12px;
        }

        .eu-plate--sm .eu-plate__body {
            padding: 1px 3px;
            font-size: 7.5px;
            letter-spacing: 0.02em;
        }

        .eu-plate--sm .eu-plate__number {
            margin-right: 3px;
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
        .sales-table td:nth-child(1) { width: 6%; }   /* Fecha */
        .sales-table th:nth-child(2),
        .sales-table td:nth-child(2) { width: 8%; }   /* Entrada - Salida */
        .sales-table th:nth-child(3),
        .sales-table td:nth-child(3) { width: 6%; }   /* Cliente */
        .sales-table th:nth-child(4),
        .sales-table td:nth-child(4) { width: 6%; }   /* Teléfono */
        .sales-table th:nth-child(5),
        .sales-table td:nth-child(5) { width: 6%; }   /* Matrícula */
        .sales-table th:nth-child(6),
        .sales-table td:nth-child(6) { width: 6%; }   /* Categoría */
        .sales-table th:nth-child(7),
        .sales-table td:nth-child(7) { width: 4%; }   /* Flota */
        .sales-table th:nth-child(8),
        .sales-table td:nth-child(8) { width: 9%; }   /* Servicios */
        .sales-table th:nth-child(9),
        .sales-table td:nth-child(9) { width: 5.5%; } /* Detallador */
        .sales-table th:nth-child(10),
        .sales-table td:nth-child(10) { width: 5%; }  /* Subtotal */
        .sales-table th:nth-child(11),
        .sales-table td:nth-child(11) { width: 4%; }  /* IVA */
        .sales-table th:nth-child(12),
        .sales-table td:nth-child(12) { width: 4.5%; } /* Descuento ($) */
        .sales-table th:nth-child(13),
        .sales-table td:nth-child(13) { width: 4.5%; } /* Pago */
        .sales-table th:nth-child(14),
        .sales-table td:nth-child(14) { width: 5%; }  /* Método */
        .sales-table th:nth-child(15),
        .sales-table td:nth-child(15) { width: 4.5%; } /* Estado */
        .sales-table th:nth-child(16),
        .sales-table td:nth-child(16) { width: 5%; }  /* Total */
        .sales-table th:nth-child(17),
        .sales-table td:nth-child(17) { width: 11%; } /* Observaciones */
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
                <div class="subtitle">Generado: {{ now()->format('d/m/Y H:i') }}</div>
            </td>

        </tr>

    </table>


    <table class="sales-table">

        <!-- Anchos explícitos: la tabla tiene 17 columnas y la placa y los
             badges necesitan más espacio que el reparto por igual -->
        <colgroup>
            <col style="width: 5%">
            <col style="width: 8%">
            <col style="width: 7%">
            <col style="width: 5%">
            <col style="width: 9%">
            <col style="width: 6%">
            <col style="width: 4%">
            <col style="width: 8%">
            <col style="width: 7%">
            <col style="width: 5%">
            <col style="width: 4%">
            <col style="width: 5%">
            <col style="width: 5%">
            <col style="width: 5%">
            <col style="width: 5%">
            <col style="width: 5%">
            <col style="width: 7%">
        </colgroup>

        <thead>
            <tr>
                @foreach(\App\Services\OrderReportService::headings() as $heading)
                    <th>{{ $heading }}</th>
                @endforeach
            </tr>
        </thead>

        <tbody>

            @forelse($orders as $order)

            @php
                $row = $report->row($order);
            @endphp

            <tr>
                @foreach($row as $field => $value)
                    <td>
                        @if(in_array($field, \App\Services\OrderReportService::AMOUNT_FIELDS, true) && is_numeric($value))
                            {{ number_format((float) $value, 2, ',', '.') }} €
                        @elseif($field === 'entrada_salida')
                            <span class="hour-badges">
                                <span class="hour-badge hour-badge--in">{{ $report->entryHour($order) }}</span>
                                <span class="hour-sep">-</span>
                                <span class="hour-badge hour-badge--out">{{ $report->exitHour($order) }}</span>
                            </span>
                        @elseif($field === 'matricula')
                            {!! \App\Helpers\LicensePlateHelper::render($value, true) !!}
                        @else
                            {{ $value }}
                        @endif
                    </td>
                @endforeach
            </tr>

            @empty

            <tr>
                <td colspan="{{ count(\App\Services\OrderReportService::headings()) }}">No hay datos para el periodo seleccionado.</td>
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
