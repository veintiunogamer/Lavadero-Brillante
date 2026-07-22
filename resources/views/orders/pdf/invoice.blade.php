<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Factura {{ $invoiceNumber }}</title>
    <style>
        @page {
            margin: 20px;
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
            width: 100%;
            margin: 0;
            padding: 0;
            background: #ffffff;
        }


        .page {
            position: relative;
            z-index: 1;
        }

        .col-1 {
            width: 8.33333333%;
        }

        .col-2 {
            width: 16.66666667%;
        }

        .col-3 {
            width: 25%;
        }

        .col-4 {
            width: 33.33333333%;
        }

        .col-5 {
            width: 41.66666667%;
        }

        .col-6 {
            width: 50%;
        }

        .col-7 {
            width: 58.33333333%;
        }

        .col-8 {
            width: 66.66666667%;
        }

        .col-9 {
            width: 75%;
        }

        .col-10 {
            width: 83.33333333%;
        }

        .col-11 {
            width: 91.66666667%;
        }

        .col-12 {
            width: 100%;
        }

        .float-end {
            float: right !important;
        }

        .float-start {
            float: left !important
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

        .color-blue {
            color: #044688 !important;
        }

        .fw-bold {
            font-weight: bold !important;
        }

        .border {
            border: 1.2px solid #4a4d55 !important;
        }

        .mt-1 {
            margin-top: 0.6em !important
        }

        .mt-2 {
            margin-top: 1em !important
        }

        .mt-3 {
            margin-top: 1.8em !important
        }

        .mt-4 {
            margin-top: 2.1em !important
        }

        .mt-5 {
            margin-top: 2.5em !important
        }

        .mb-1 {
            margin-bottom: 0.6em !important;
        }

        .mb-2 {
            margin-bottom: 1em !important;
        }

        .mb-3 {
            margin-bottom: 1.8em !important;
        }

        .mb-4 {
            margin-bottom: 2.1em !important;
        }

        .mb-5 {
            margin-bottom: 2.5em !important;
        }

        .info-line {
            margin-bottom: 7px;
        }

        .p-2 {
            padding: 1em !important;
        }

        .p-3 {
            padding: 2em !important;
        }


        .info-label {
            display: inline-block;
            min-width: 74px;
            color: #555963;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 9px;
        }

        .client-table td {
            border: 1.2px solid #4a4d55;
            padding: 11px 10px;
            vertical-align: middle;
        }

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
            table-layout: fixed;
            border-collapse: collapse;
            background: rgba(255, 255, 255, 0.94);
        }

        .totals-table td {
            border: 1.2px solid #4a4d55;
            padding: 6px 8px;
        }

        .totals-label {
            width: 55%;
            background: #3b3d43;
            color: #ffffff;
            font-weight: bold;
            text-align: right;
            text-transform: uppercase;
        }

        .totals-value {
            text-align: right;
            font-size: 13px;
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

        .services-wrapper {
            position: relative;
        }

        .services-watermark {
            position: absolute;
            top: 90px;
            left: 50%;
            transform: translateX(-50%);
            width: 520px;
            opacity: 0.08;
            z-index: 0;
        }

        .services-table {
            position: relative;
            z-index: 2;
            background: transparent;
        }
    </style>
</head>

<body>
    @php
    $company = [
    'name' => 'Lavadero Brillante',
    'owner' => 'Eusebio Borrego Lao',
    'nif' => '28.614.307-F',
    'address' => 'Calle Dr. Fleming, 21',
    'city' => '46960/Aldaya - Valencia',
    'phone' => '682 145 020 - 620 800 224',
    'email' => 'lavaderobrillante22@gmail.com',
    ];

    $logoPath = public_path('images/logo_alterno.png');

    if (!file_exists($logoPath)) {
    $logoPath = public_path('images/logo.png');
    }

    $client = optional($order->client);
    $invoice = optional($order->invoice);

    $clientName = $invoice->business_name ?? $client->name ?? '--';
    $clientPhone = $client->phone ?? '--';
    $licensePlaque = $client->license_plaque ?? '--';

    $clientAddress = $invoice->address ?? $client->address ?? '--';
    $clientDocument = $invoice->nif ?? '--';

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

    <div class="pdf-page">

        <div class="page">

            <!-- CABECERA -->
            <table width="100%" cellspacing="0" cellpadding="0" style="margin-bottom:20px;">
                <tr>

                    <td width="50%" valign="middle">
                        <div class="brand-title">
                            <span class="color-blue">
                                {{ strtoupper($company['name']) }}
                            </span>
                        </div>
                    </td>

                    <td width="50%" align="right" valign="middle">
                        <img src="{{ $logoPath }}"
                            alt="{{ $company['name'] }}"
                            class="logo-main">
                    </td>

                </tr>
            </table>

            <!-- EMPRESA + CLIENTE -->
            <table width="100%" cellspacing="0" cellpadding="0">
                <tr>

                    <!-- EMPRESA -->
                    <td width="48%" valign="top">

                        <div class="border">

                            <div class="box-pad mt-2 mb-2">
                                <div class="info-line">
                                    <strong>{{ $company['owner'] }}</strong>
                                </div>

                                <div class="info-line">
                                    {{ $company['address'] }}
                                </div>

                                <div class="info-line">
                                    {{ $company['city'] }} - Valencia
                                </div>

                                <div class="info-line">
                                    {{ $company['nif'] }}
                                </div>
                            </div>

                        </div>

                        <div class="border mt-4 p-2">
                            <div class="info-line">
                                {{ $company['phone'] }}
                            </div>

                            <div class="info-line">
                                {{ $company['email'] }}
                            </div>
                        </div>

                    </td>

                    <td width="3%"></td>

                    <!-- CLIENTE -->
                    <td width="49%" valign="top">

                        <table class="client-table t-full mb-4">
                            <tr>
                                <td class="field-label fw-bold">Nombre</td>
                                <td class="field-value">{{ $clientName }}</td>
                            </tr>

                            <tr>
                                <td class="field-label fw-bold">Direccion</td>
                                <td>{{ $clientAddress }}</td>
                            </tr>

                            <tr>
                                <td class="field-label fw-bold">CIF - NIF - DNI</td>
                                <td>{{ $clientDocument }}</td>
                            </tr>
                        </table>

                        <table class="invoice-table t-full">
                            <tr>
                                <td class="field-label fw-bold">Fecha factura</td>
                                <td class="field-value" style="text-align:center;">
                                    {{ $invoiceDate }}
                                </td>
                            </tr>

                            <tr>
                                <td style="text-align:center;">
                                    <span class="pill-title">
                                        Factura
                                    </span>
                                </td>

                                <td class="invoice-number">
                                    {{ $invoiceNumber }}
                                </td>
                            </tr>

                        </table>

                    </td>
                </tr>

            </table>

            <div class="section-gap"></div>

            {{-- Cliente --}}
            <div class="section-gap"></div>

            {{-- Servicios --}}
            <div class="services-wrapper">

                <img
                    src="{{ $logoPath }}"
                    class="services-watermark"
                    alt="Logo">

                <table class="services-table">
                    <thead>
                        <tr>
                            <th style="width: 9%;">Cantidad</th>
                            <th style="width: 35%;">Descripcion</th>
                            <th style="width: 15%;">Matricula</th>
                            <th style="width: 11%;">Dto</th>
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

                        <tr>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                        </tr>

                        <tr>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                        </tr>

                        <tr>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                        </tr>

                        <tr>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                        </tr>

                        <tr>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                        </tr>

                        <tr>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                        </tr>

                        <tr>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                        </tr>

                        <tr>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                        </tr>

                        <tr>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                        </tr>

                    </tbody>

                </table>

            </div>

            <div class="section-gap"></div>

            <table class="t-full">
                <tr>

                    <!-- DATOS BANCARIOS -->
                    <td style="width:55%; vertical-align:bottom;">

                        <table style="width:260px;border-collapse:collapse;border:1px solid #4a4d55;margin-top:15px;">

                            <tr>
                                <td style="padding:6px 8px;">
                                    ES70 2100 1639 2002 0058 1160
                                </td>
                            </tr>

                            <tr>
                                <td style="padding:6px 8px;">
                                    Caixabank
                                </td>
                            </tr>

                        </table>

                    </td>

                    <td style="width: 45%; vertical-align: top;">

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
                                <td class="totals-label">Tipo de IVA</td>
                                <td class="totals-value">{{ $taxRateLabel }}</td>
                            </tr>

                            <tr>
                                <td class="totals-label">Total IVA</td>
                                <td class="totals-value">{!! $money($taxesValue) !!}</td>
                            </tr>

                            <tr class="grand-total">
                                <td class="totals-label">&nbsp;</td>
                                <td class="totals-value">{!! $money($total) !!}</td>
                            </tr>

                        </table>

                    </td>

                </tr>

            </table>

            <div class="section-gap"></div>

            <!-- <td style="width: 56%; padding-right: 20px; vertical-align: top;">
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
            </td> -->

            {{-- Observaciones --}}
            <!-- <div class="notes-box">
                <div class="notes-title">Observaciones</div>
                {{ $observations ?: 'Sin observaciones.' }}
            </div> -->

        </div>

    </div>

</body>


</html>