<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Factura {{ $invoiceNumber }}</title>
    <style>
        @page {
            margin: 24px 30px 28px;
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 10.5px;
            color: #2f3137;
            margin: 0;
            line-height: 1.35;
        }

        /* Previsualización en navegador: fuerza tamaño tipo PDF (A4) */
        .pdf-page {
            width: 210mm;
            min-height: 297mm;
            margin: 0 auto;
            padding: 24px 30px 28px;
            background: #ffffff;
        }


        .page {
            position: relative;
            z-index: 1;
        }

        .watermark {
            position: fixed;
            top: 35%;
            left: 16%;
            width: 68%;
            opacity: 0.08;
            z-index: -1000;
        }

        .t-full {
            width: 100%;
            border-collapse: collapse;
        }

        .header-table td {
            vertical-align: top;
            display: flex;
            flex-direction: column;
        }

        .brand-title {
            font-size: 21px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0;
            margin: 0 0 10px;
        }

        .logo-main {
            width: 215px;
            max-height: 126px;
        }

        .box {
            border: 1.2px solid #4a4d55;
            background: rgba(255, 255, 255, 0.93);
        }

        .box-pad {
            padding: 8px 9px;
        }

        .box-title {
            background: #eeeeef;
            border-bottom: 1.2px solid #4a4d55;
            color: #2f3137;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
            padding: 5px 8px;
        }

        .header-brand-section {
            display: flex !important;
            justify-content: center !important;
            align-content: center !important;
        }

        .info-line {
            margin-bottom: 4px;
        }

        .info-label {
            display: inline-block;
            min-width: 74px;
            color: #555963;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 9px;
        }

        .client-table td,
        .invoice-table td {
            border: 1.2px solid #4a4d55;
            padding: 7px 8px;
            vertical-align: middle;
        }

        .field-label {
            width: 28%;
            color: #555963;
            font-size: 10px;
            text-transform: uppercase;
        }

        .field-value {
            font-size: 11px;
            font-weight: bold;
        }

        .invoice-number {
            background: #eeeeef;
            font-size: 16px;
            font-weight: bold;
            text-align: center;
        }

        .pill-title {
            background: #3b3d43;
            color: #ffffff;
            border-radius: 16px;
            display: inline-block;
            font-size: 17px;
            font-weight: bold;
            line-height: 1;
            padding: 8px 32px;
            text-transform: uppercase;
        }

        .section-gap {
            height: 14px;
        }

        .services-table {
            width: 100%;
            border-collapse: collapse;
            background: rgba(255, 255, 255, 0.9);
        }

        .services-table th {
            background: #3b3d43;
            color: #ffffff;
            border: 1.2px solid #3b3d43;
            font-size: 10.5px;
            padding: 7px 5px;
            text-align: center;
            text-transform: uppercase;
        }

        .services-table td {
            border: 1.1px solid #4f525a;
            padding: 7px 6px;
            vertical-align: top;
        }

        .services-table .center {
            text-align: center;
        }

        .services-table .right {
            text-align: right;
            white-space: nowrap;
        }

        .service-name {
            font-weight: bold;
            text-transform: uppercase;
        }

        .service-meta {
            color: #696d77;
            font-size: 9px;
            margin-top: 2px;
        }

        .payment-box {
            border: 1.2px solid #4a4d55;
            background: rgba(255, 255, 255, 0.92);
            padding: 8px 9px;
            min-height: 74px;
        }

        .payment-title,
        .notes-title {
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
            color: #3b3d43;
            margin-bottom: 5px;
        }

        .payment-line {
            margin-bottom: 3px;
        }

        .totals-table {
            width: 100%;
            border-collapse: collapse;
            background: rgba(255, 255, 255, 0.94);
        }

        .totals-table td {
            border: 1.2px solid #4a4d55;
            padding: 6px 8px;
        }

        .totals-label {
            width: 56%;
            background: #3b3d43;
            color: #ffffff;
            font-weight: bold;
            text-align: right;
            text-transform: uppercase;
        }

        .totals-value {
            text-align: right;
            white-space: nowrap;
        }

        .totals-discount {
            color: #b42318;
            font-weight: bold;
        }

        .grand-total .totals-label,
        .grand-total .totals-value {
            background: #eeeeef;
            color: #25272d;
            font-size: 15px;
            font-weight: bold;
        }

        .notes-box {
            border: 1.2px solid #4a4d55;
            background: rgba(255, 255, 255, 0.92);
            padding: 8px 9px;
            min-height: 48px;
        }

        .muted {
            color: #777b84;
        }
    </style>
</head>

<body>
    @php
    $company = [
    'name' => 'Lavadero Brillante',
    'owner' => 'Eusebio Borrego Lau',
    'nif' => '28614307F',
    'address' => 'Calle Dr. Fleming, 21',
    'city' => '46960 Aldaya',
    'phone' => '682 145 020 - 620 800 224',
    'email' => 'lavaderobrillante22@gmail.com',
    ];

    $logoPath = public_path('images/logo_alterno.png');
    if (!file_exists($logoPath)) {
    $logoPath = public_path('images/logo.png');
    }

    $client = optional($order->client);
    $clientName = $client->name ?? '--';
    $clientPhone = $client->phone ?? '--';
    $licensePlaque = $client->license_plaque ?? '--';
    $clientAddress = $client->address
    ?? $client->invoice_address
    ?? $client->billing_address
    ?? '--';
    $clientDocument = $client->tax_id
    ?? $client->invoice_tax_id
    ?? $client->nif
    ?? $client->document
    ?? '--';

    $vehicleCat = optional($order->vehicleType)->name ?? '--';
    $operario = optional($order->user)->name ?? '--';

    $entryDate = $order->hour_in
    ? \Carbon\Carbon::parse($order->hour_in)->locale('es')->isoFormat('D [de] MMMM [de] YYYY [a las] HH:mm')
    : '--';
    $exitTime = $order->hour_out
    ? \Carbon\Carbon::parse($order->hour_out)->format('H:i')
    : '--';
    $invoiceDate = $order->creation_date
    ? \Carbon\Carbon::parse($order->creation_date)->format('d/m/Y')
    : ($order->date ? \Carbon\Carbon::parse($order->date)->format('d/m/Y') : '--');

    $dirtLabels = [1 => 'Bajo', 2 => 'Medio', 3 => 'Alto'];
    $dirtLabel = $dirtLabels[$order->dirt_level] ?? '--';

    $subtotal = $order->subtotal ?? 0;
    $total = $order->total ?? 0;
    $discount = $order->discount_value ?? 0;
    $taxesValue = $order->taxes_value ?? 0;
    $taxBase = max(0, (float) $subtotal - (float) $discount);
    $taxRateLabel = $taxesValue > 0 ? '21%' : '0%';
    $partialPayment = $order->partial_payment ?? null;
    $observations = $order->order_notes ?? '';

    $paymentStatus = 1;
    $paymentType = 1;
    $paymentTypeNames = [1 => 'Efectivo', 2 => 'TPV', 3 => 'Transferencia'];
    $paymentStatusNames = [1 => 'Pago pendiente', 2 => 'Abono parcial', 3 => 'Pagado'];

    if ($order->payments && $order->payments->count() > 0) {
    $payment = $order->payments->first();
    $paymentStatus = $payment->status ?? 1;
    $paymentType = $payment->type ?? 1;
    }

    $services = $order->services ?? collect();
    $mainServices = $services->filter(fn($s) => optional($s->category)->cat_name === 'Lavados');
    $extraServices = $services->filter(fn($s) => optional($s->category)->cat_name !== 'Lavados');
    $serviceCount = $services->count();
    $money = fn($value) => number_format((float) $value, 2, ',', '.') . ' &euro;';
    @endphp

    {{-- Marca de agua --}}
    <img src="{{ $logoPath }}" alt="Marca de agua" class="watermark">

    <div class="pdf-page">

        <div class="page">


            {{-- Empresa --}}
            <table class="t-full header-table">
                <tr>
                    <div class="header-brand-section">

                        <div class="brand-title">{{ $company['name'] }}</div>

                        <div class="brand-title-logo">
                            <img src="{{ $logoPath }}" alt="{{ $company['name'] }}" class="logo-main">
                        </div>

                    </div>

                    <div class="box">

                        <div class="box-title">Datos empresa</div>
                        <div class="box-pad">
                            <div class="info-line"><strong>{{ $company['owner'] }}</strong></div>
                            <div class="info-line"><span class="info-label">Direccion</span>{{ $company['address'] }}</div>
                            <div class="info-line"><span class="info-label">Ciudad</span>{{ $company['city'] }}</div>
                            <div class="info-line"><span class="info-label">NIF</span>{{ $company['nif'] }}</div>
                            <div class="info-line"><span class="info-label">Telefono</span>{{ $company['phone'] }}</div>
                            <div class="info-line"><span class="info-label">Email</span>{{ $company['email'] }}</div>
                        </div>

                    </div>

                    </td>

                </tr>

            </table>

            <div class="section-gap"></div>

            {{-- Cliente --}}
            <table class="t-full">
                <tr>
                    <td style="width: 56%; padding-right: 15px; vertical-align: top;">
                        <table class="client-table t-full">
                            <tr>
                                <td class="field-label">Nombre</td>
                                <td class="field-value">{{ $clientName }}</td>
                            </tr>
                            <tr>
                                <td class="field-label">Direccion</td>
                                <td>{{ $clientAddress }}</td>
                            </tr>
                            <tr>
                                <td class="field-label">Documento/NIF</td>
                                <td>{{ $clientDocument }}</td>
                            </tr>
                            <tr>
                                <td class="field-label">Telefono</td>
                                <td>{{ $clientPhone }}</td>
                            </tr>
                        </table>
                    </td>

                    {{-- Factura --}}
                    <td style="width: 44%; vertical-align: top;">
                        <table class="invoice-table t-full">
                            <tr>
                                <td class="field-label">Fecha factura</td>
                                <td class="field-value" style="text-align: center;">{{ $invoiceDate }}</td>
                            </tr>
                            <tr>
                                <td style="text-align: center;">
                                    <span class="pill-title">Factura</span>
                                </td>
                                <td class="invoice-number">{{ $invoiceNumber }}</td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>

            <div class="section-gap"></div>

            {{-- Servicios --}}
            <table class="services-table">
                <thead>
                    <tr>
                        <th style="width: 9%;">Cantidad</th>
                        <th style="width: 35%;">Descripcion</th>
                        <th style="width: 15%;">Matricula</th>
                        <th style="width: 11%;">Descuento</th>
                        <th style="width: 14%;">Precio</th>
                        <th style="width: 16%;">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($services as $service)
                    @php
                    $lineTotal = $service->pivot->total ?? $service->value ?? 0;
                    $quantity = $service->pivot->quantity ?? $order->quantity ?? 1;
                    $quantity = max(1, (int) $quantity);
                    $unitPrice = $quantity > 0 ? ((float) $lineTotal / $quantity) : (float) $lineTotal;
                    $lineDiscount = ($serviceCount === 1 && $discount > 0) ? $money($discount) : '--';
                    $categoryName = optional($service->category)->cat_name;
                    @endphp
                    <tr>
                        <td class="center">{{ $quantity }}</td>
                        <td>
                            <div class="service-name">{{ $service->name }}</div>
                            <div class="service-meta">
                                Categoria: {{ $categoryName ?: 'Servicio' }} |
                                Vehiculo: {{ $vehicleCat }} |
                                Nivel de suciedad: {{ $dirtLabel }}
                            </div>
                        </td>
                        <td class="center">{{ $licensePlaque }}</td>
                        <td class="right">{!! $lineDiscount !!}</td>
                        <td class="right">{!! $money($unitPrice) !!}</td>
                        <td class="right"><strong>{!! $money($lineTotal) !!}</strong></td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="center muted">Sin servicios registrados.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>

            <div class="section-gap"></div>

            {{-- Totales --}}
            <table class="t-full">
                <tr>
                    <td style="width: 56%; padding-right: 20px; vertical-align: top;">
                        <div class="payment-box">
                            <div class="payment-title">Informacion de servicio y pago</div>
                            <div class="payment-line"><strong>Entrada:</strong> {{ $entryDate }}</div>
                            <div class="payment-line"><strong>Entrega:</strong> {{ $exitTime }}</div>
                            <div class="payment-line"><strong>Operario:</strong> {{ $operario }}</div>
                            <div class="payment-line"><strong>Estado:</strong> {{ $paymentStatusNames[$paymentStatus] ?? 'Pago pendiente' }}</div>
                            @if($paymentStatus == 2)
                            <div class="payment-line"><strong>Abono:</strong> {!! $partialPayment ? $money($partialPayment) : '--' !!}</div>
                            @endif
                            <div class="payment-line"><strong>Metodo:</strong> {{ $paymentTypeNames[$paymentType] ?? 'Efectivo' }}</div>
                        </div>
                    </td>
                    <td style="width: 44%; vertical-align: top;">
                        <table class="totals-table">
                            <tr>
                                <td class="totals-label">Base imponible</td>
                                <td class="totals-value">{!! $money($taxBase) !!}</td>
                            </tr>
                            @if($discount > 0)
                            <tr>
                                <td class="totals-label">Descuento</td>
                                <td class="totals-value totals-discount">-{!! $money($discount) !!}</td>
                            </tr>
                            @endif
                            <tr>
                                <td class="totals-label">IVA</td>
                                <td class="totals-value">{{ $taxRateLabel }}</td>
                            </tr>
                            <tr>
                                <td class="totals-label">Total IVA</td>
                                <td class="totals-value">{!! $money($taxesValue) !!}</td>
                            </tr>
                            <tr class="grand-total">
                                <td class="totals-label">TOTAL</td>
                                <td class="totals-value">{!! $money($total) !!}</td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>

            <div class="section-gap"></div>

            {{-- Factura --}}
            <div class="notes-box">
                <div class="notes-title">Observaciones</div>
                {{ $observations ?: 'Sin observaciones.' }}
            </div>
        </div>
    </div>
</body>


</html>