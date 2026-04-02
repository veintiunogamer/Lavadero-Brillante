<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Orden de Servicio {{ $invoiceNumber }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 11px;
            color: #1f2937;
            margin: 24px 32px;
        }

        /* ── Header ── */
        .t-full { width: 100%; border-collapse: collapse; }
        .brand-blue   { color: #1e40af; font-size: 26px; font-weight: bold; line-height: 1.1; }
        .brand-orange { color: #f59e0b; font-size: 26px; font-weight: bold; line-height: 1.1; }
        .brand-owner  { font-size: 9px; color: #374151; font-weight: bold; margin-top: 5px; }
        .brand-info   { font-size: 9px; color: #6b7280; margin-top: 3px; }
        .doc-title    { font-size: 18px; font-weight: bold; color: #1e40af; text-align: right; }
        .doc-number   { font-size: 13px; font-weight: bold; text-align: right; margin-top: 4px; }

        /* ── Dividers ── */
        .hr-main    { border: none; border-top: 2px solid #1e40af; margin: 12px 0; }
        .hr-section { border: none; border-top: 1px solid #e5e7eb; margin: 10px 0; }

        /* ── Info sections ── */
        .section-label { color: #1e40af; font-weight: bold; font-size: 10px; margin-bottom: 6px; }
        .info-row      { font-size: 11px; margin-bottom: 3px; }

        /* ── Services ── */
        .services-title {
            color: #1e40af;
            font-weight: bold;
            font-size: 13px;
            border-bottom: 2px solid #f59e0b;
            padding-bottom: 4px;
            margin: 14px 0 10px;
        }
        .svc-name   { font-weight: bold; font-size: 11px; }
        .svc-price  { font-weight: bold; font-size: 11px; text-align: right; white-space: nowrap; }
        .svc-sub-l  { color: #6b7280; font-size: 9px; }
        .svc-sub-r  { color: #6b7280; font-size: 9px; text-align: right; white-space: nowrap; }
        .extras-lbl { font-weight: bold; font-size: 11px; margin: 10px 0 5px; }
        .extra-price{ text-align: right; font-weight: bold; white-space: nowrap; }

        /* ── Payment ── */
        .pay-label  { color: #1e40af; font-weight: bold; font-size: 10px; margin-bottom: 8px; }
        .radio-row  { margin-bottom: 5px; font-size: 11px; }
        .method-row { font-size: 11px; margin-top: 6px; }
        .sub-row    { text-align: right; font-size: 11px; color: #374151; margin-bottom: 3px; }
        .total-lbl  { text-align: right; font-size: 13px; font-weight: bold; margin-top: 6px; }
        .total-amt  { color: #f59e0b; font-size: 20px; font-weight: bold; }

        /* ── Observations ── */
        .obs-label { color: #1e40af; font-weight: bold; font-size: 10px; margin: 14px 0 5px; }
        .obs-box   { border: 1px solid #d1d5db; padding: 8px 10px; font-size: 10px; color: #6b7280; }
    </style>
</head>
<body>

@php
    $clientName    = optional($order->client)->name         ?? 'N/A';
    $clientPhone   = optional($order->client)->phone        ?? 'N/A';
    $licensePlaque = optional($order->client)->license_plaque ?? 'N/A';
    $vehicleCat    = optional($order->vehicleType)->name    ?? 'N/A';
    $operario      = optional($order->user)->name           ?? 'N/A';

    $entryDate = $order->hour_in
        ? \Carbon\Carbon::parse($order->hour_in)->locale('es')
            ->isoFormat('D [de] MMMM [de] YYYY [a las] HH:mm')
        : 'N/A';
    $exitTime  = $order->hour_out
        ? \Carbon\Carbon::parse($order->hour_out)->format('H:i')
        : 'N/A';

    $dirtLabels = [1 => 'Bajo', 2 => 'Medio', 3 => 'Alto'];
    $dirtLabel  = $dirtLabels[$order->dirt_level] ?? 'N/A';

    $subtotal       = $order->subtotal       ?? 0;
    $total          = $order->total          ?? 0;
    $discount       = $order->discount       ?? 0;
    $partialPayment = $order->partial_payment ?? null;
    $observations   = $order->order_notes    ?? '';

    $paymentStatus = 1;
    $paymentType   = 1;
    $paymentTypeNames = [1 => 'Efectivo', 2 => 'Tarjeta', 3 => 'Transferencia'];

    if ($order->payments && $order->payments->count() > 0) {
        $payment       = $order->payments->first();
        $paymentStatus = $payment->status ?? 1;
        $paymentType   = $payment->type   ?? 1;
    }

    $mainServices  = $order->services->filter(fn($s) => optional($s->category)->cat_name === 'Lavados');
    $extraServices = $order->services->filter(fn($s) => optional($s->category)->cat_name !== 'Lavados');
@endphp

{{-- ═══════════════ HEADER ═══════════════ --}}
<table class="t-full">
    <tr>
        <td style="width:50%; vertical-align:top;">
            <div class="brand-blue">LAVADERO</div>
            <div class="brand-orange">BRILLANTE</div>
            <div class="brand-owner">Eusebio Borrego Lau</div>
            <div class="brand-info">NIF: 28614307F</div>
            <div class="brand-info">Calle Dr. Fleming, 21</div>
            <div class="brand-info">46960 Aldaya</div>
        </td>
        <td style="width:50%; vertical-align:top; text-align:right;">
            <div class="doc-title">ORDEN DE SERVICIO</div>
            <div class="doc-number">{{ $invoiceNumber }}</div>
        </td>
    </tr>
</table>

<hr class="hr-main">

{{-- ═══════════════ CLIENTE + VEHÍCULO ═══════════════ --}}
<table class="t-full">
    <tr>
        <td style="width:50%; padding-right:20px; vertical-align:top;">
            <div class="section-label">Datos del Cliente</div>
            <div class="info-row"><strong>Nombre:</strong> {{ $clientName }}</div>
            <div class="info-row"><strong>Teléfono:</strong> {{ $clientPhone }}</div>
        </td>
        <td style="width:50%; vertical-align:top;">
            <div class="section-label">Datos del Vehículo</div>
            <div class="info-row"><strong>Categoría:</strong> {{ $vehicleCat }}</div>
            <div class="info-row"><strong>Matrícula:</strong> {{ $licensePlaque }}</div>
        </td>
    </tr>
</table>

<hr class="hr-section">

{{-- ═══════════════ FECHA + OPERARIO ═══════════════ --}}
<table class="t-full">
    <tr>
        <td style="width:50%; padding-right:20px; vertical-align:top;">
            <div class="section-label">Fecha y Hora</div>
            <div class="info-row"><strong>Entrada:</strong> {{ $entryDate }}</div>
            <div class="info-row"><strong>Entrega:</strong> {{ $exitTime }}</div>
        </td>
        <td style="width:50%; vertical-align:top;">
            <div class="section-label">Operario</div>
            <div class="info-row">{{ $operario }}</div>
        </td>
    </tr>
</table>

{{-- ═══════════════ SERVICIOS ═══════════════ --}}
<div class="services-title">Servicios Contratados</div>

@forelse($mainServices as $service)
    @php $lineTotal = $service->pivot->total ?? $service->value ?? 0; @endphp
    <table class="t-full" style="margin-bottom:6px;">
        <tr>
            <td class="svc-name">{{ $service->name }}</td>
            <td class="svc-price">{{ number_format($lineTotal, 2, '.', '.') }}€</td>
        </tr>
        <tr>
            <td class="svc-sub-l">Nivel de suciedad: {{ $dirtLabel }}</td>
            <td class="svc-sub-r"></td>
        </tr>
    </table>
@empty
    <div style="color:#6b7280; font-size:11px; margin-bottom:6px;">Sin servicios de lavado.</div>
@endforelse

@if($extraServices->count() > 0)
    <div class="extras-lbl">Extras y Suplementos:</div>
    @foreach($extraServices as $service)
        @php $lineTotal = $service->pivot->total ?? $service->value ?? 0; @endphp
        <table class="t-full" style="margin-bottom:4px;">
            <tr>
                <td style="font-size:11px;">{{ $service->name }}</td>
                <td class="extra-price" style="font-size:11px;">+{{ number_format($lineTotal, 2, '.', '.') }}€</td>
            </tr>
        </table>
    @endforeach
@endif

<hr class="hr-section">

{{-- ═══════════════ PAGO + TOTAL ═══════════════ --}}
<table class="t-full" style="margin-top:4px;">
    <tr>
        <td style="width:50%; vertical-align:top;">
            <div class="pay-label">Estado del Pago</div>

            <div class="radio-row">
                @if($paymentStatus == 1)
                    <span style="color:#1e40af;">&#9679;</span>
                @else
                    <span style="color:#9ca3af;">&#9675;</span>
                @endif
                &nbsp;Pago Pendiente
            </div>
            <div class="radio-row">
                @if($paymentStatus == 2)
                    <span style="color:#1e40af;">&#9679;</span>
                @else
                    <span style="color:#9ca3af;">&#9675;</span>
                @endif
                &nbsp;Abono Parcial:&nbsp;{{ $partialPayment ? number_format($partialPayment, 2, ',', '.') . '€' : '............€' }}
            </div>
            <div class="radio-row">
                @if($paymentStatus == 3)
                    <span style="color:#1e40af;">&#9679;</span>
                @else
                    <span style="color:#9ca3af;">&#9675;</span>
                @endif
                &nbsp;Pagado:&nbsp;............€
            </div>

            <div class="method-row"><strong>Método:</strong> {{ $paymentTypeNames[$paymentType] ?? 'Efectivo' }}</div>
        </td>
        <td style="width:50%; vertical-align:top;">
            <div class="sub-row">Subtotal: {{ number_format($subtotal, 2, ',', '.') }}€</div>
            @if($discount > 0)
                <div class="sub-row" style="color:#6b7280;">
                    Descuento: -{{ number_format($discount, 2, ',', '.') }}€
                </div>
            @endif
            <div class="total-lbl">
                Total: <span class="total-amt">{{ number_format($total, 2, ',', '.') }}€</span>
            </div>
        </td>
    </tr>
</table>

<hr class="hr-section">

{{-- ═══════════════ OBSERVACIONES ═══════════════ --}}
<div class="obs-label">Observaciones</div>
<div class="obs-box">{{ $observations ?: 'Sin observaciones.' }}</div>

</body>
</html>
