<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ReportSalesExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    private Collection $orders;
    private array $statusLabels;
    private array $paymentStatusLabels;

    public function __construct(Collection $orders, array $statusLabels, array $paymentStatusLabels)
    {
        $this->orders = $orders;
        $this->statusLabels = $statusLabels;
        $this->paymentStatusLabels = $paymentStatusLabels;
    }

    public function collection(): Collection
    {
        return $this->orders;
    }

    public function headings(): array
    {
        return [
            'Orden',
            'Cliente',
            'Servicios',
            'Fecha',
            'Subtotal',
            'Descuento %',
            'Total',
            'Pago',
            'Estado',
        ];
    }

    public function map($order): array
    {
        $orderNumber = $order->consecutive_serial && $order->consecutive_number
            ? $order->consecutive_serial . '-' . $order->consecutive_number
            : strtoupper(substr($order->id, 0, 8));

        $services = $order->services->pluck('name')->join(', ');
        $payment = $order->payments->first();
        $paymentStatus = $payment ? ($this->paymentStatusLabels[$payment->status] ?? 'Desconocido') : 'N/A';

        $subtotal = (float) ($order->subtotal ?? 0);
        $discount = (float) ($order->discount ?? 0);
        $discountPercent = $subtotal > 0 ? ($discount / $subtotal) * 100 : 0;

        return [
            $orderNumber,
            optional($order->client)->name ?? 'N/A',
            $services ?: 'N/A',
            $order->creation_date ? Carbon::parse($order->creation_date)->format('d/m/Y') : 'N/A',
            round($subtotal, 2),
            round($discountPercent, 0),
            round((float) ($order->total ?? 0), 2),
            $paymentStatus,
            $this->statusLabels[$order->status] ?? 'Desconocido',
        ];
    }
}
