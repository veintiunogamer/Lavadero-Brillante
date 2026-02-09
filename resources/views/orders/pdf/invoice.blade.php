<!doctype html>
<html lang="es">
    <head>
        <meta charset="utf-8">
        <title>Factura {{ $invoiceNumber }}</title>
        <style>
            body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #111827; }
            .header { width: 100%; margin-bottom: 12px; }
            .logo { width: 120px; }
            .title { font-size: 18px; margin: 0; }
            .subtle { color: #6b7280; margin: 2px 0 0; }
            .section { margin-bottom: 12px; }
            .section-title { font-weight: 700; margin-bottom: 4px; }
            table { width: 100%; border-collapse: collapse; margin-top: 8px; }
            th, td { border: 1px solid #e5e7eb; padding: 6px 8px; vertical-align: top; }
            th { background: #f3f4f6; text-align: left; }
            .totals { width: 100%; margin-top: 12px; }
            .totals td { padding: 4px 0; border: none; }
            .text-right { text-align: right; }
            .text-muted { color: #6b7280; }
        </style>
    </head>
    <body>
        @php
            $clientName = $order->invoice_business_name ?: (optional($order->client)->name ?? 'N/A');
            $taxId = $order->invoice_tax_id ?? '';
            $address = $order->invoice_address ?? '';
            $postal = $order->invoice_postal_code ?? '';
            $city = $order->invoice_city ?? '';
            $email = $order->invoice_email ?? '';
            $clientPhone = optional($order->client)->phone ?? '';
            $invoiceDate = $order->creation_date ? \Carbon\Carbon::parse($order->creation_date)->format('d/m/Y') : 'N/A';
            $subtotal = $order->subtotal ?? 0;
            $discount = $order->discount ?? 0;
            $total = $order->total ?? 0;
            $discountPercent = $subtotal > 0 ? ($discount / $subtotal) * 100 : 0;
        @endphp

        <table class="header">
            <tr>
                <td style="width: 140px;">
                    <img src="{{ public_path('images/logo.png') }}" alt="Logo" class="logo">
                </td>
                <td style="text-align: right;">
                    <h1 class="title">Factura</h1>
                    <div class="subtle">No. {{ $invoiceNumber }}</div>
                    <div class="subtle">Fecha: {{ $invoiceDate }}</div>
                </td>
            </tr>
        </table>

        <div class="section">
            <div class="section-title">Datos del cliente</div>
            <div><strong>Nombre/Razon social:</strong> {{ $clientName }}</div>
            @if($taxId)
                <div><strong>NIF/CIF:</strong> {{ $taxId }}</div>
            @endif
            @if($clientPhone)
                <div><strong>Telefono:</strong> {{ $clientPhone }}</div>
            @endif
            @if($email)
                <div><strong>Email:</strong> {{ $email }}</div>
            @endif
            @if($address || $postal || $city)
                <div><strong>Direccion:</strong> {{ trim($address . ' ' . $postal . ' ' . $city) }}</div>
            @endif
        </div>

        <table>
            <thead>
                <tr>
                    <th>Servicio</th>
                    <th class="text-right">Cantidad</th>
                    <th class="text-right">Precio</th>
                    <th class="text-right">Total</th>
                </tr>
            </thead>
            <tbody>
                @forelse($order->services as $service)
                    @php
                        $quantity = $service->pivot->quantity ?? 1;
                        $lineTotal = $service->pivot->total ?? $service->value ?? 0;
                        $unitPrice = $quantity > 0 ? $lineTotal / $quantity : $lineTotal;
                    @endphp
                    <tr>
                        <td>{{ $service->name }}</td>
                        <td class="text-right">{{ $quantity }}</td>
                        <td class="text-right">{{ number_format($unitPrice, 2, ',', '.') }} €</td>
                        <td class="text-right">{{ number_format($lineTotal, 2, ',', '.') }} €</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4">No hay servicios registrados.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <table class="totals">
            <tr>
                <td class="text-right"><strong>Subtotal:</strong></td>
                <td class="text-right" style="width: 140px;">{{ number_format($subtotal, 2, ',', '.') }} €</td>
            </tr>
            @if($discount > 0)
                <tr>
                    <td class="text-right text-muted"><strong>Descuento:</strong></td>
                    <td class="text-right text-muted">
                        -{{ number_format($discount, 2, ',', '.') }} € ({{ number_format($discountPercent, 0) }}%)
                    </td>
                </tr>
            @endif
            <tr>
                <td class="text-right"><strong>Total:</strong></td>
                <td class="text-right"><strong>{{ number_format($total, 2, ',', '.') }} €</strong></td>
            </tr>
        </table>
    </body>
</html>
