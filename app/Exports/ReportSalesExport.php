<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class ReportSalesExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    private Collection $orders;
    private array $statusLabels;
    private array $paymentStatusLabels;
    private array $paymentMethodLabels;

    public function __construct(Collection $orders, array $statusLabels, array $paymentStatusLabels, array $paymentMethodLabels)
    {
        $this->orders = $orders;
        $this->statusLabels = $statusLabels;
        $this->paymentStatusLabels = $paymentStatusLabels;
        $this->paymentMethodLabels = $paymentMethodLabels;
    }

    public function collection(): Collection
    {
        return $this->orders;
    }

    public function headings(): array
    {
        return [
            '# Orden',
            'Fecha',
            'Cliente',
            'Flota',
            'Servicios',
            'Subtotal',
            'IVA',
            'Descuento %',
            'Pago',
            'Método',
            'Estado',
            'Total',
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
        $paymentMethod = $payment ? ($this->paymentMethodLabels[$payment->type] ?? 'Desconocido') : 'N/A';

        $subtotal = (float) ($order->subtotal ?? 0);
        $tax = (float) ($order->tax ?? 0);
        $discount = (float) ($order->discount ?? 0);
        $discountPercent = $subtotal > 0 ? ($discount / $subtotal) * 100 : 0;

        return [
            $orderNumber,
            $order->creation_date ? Carbon::parse($order->creation_date)->format('d/m/Y') : 'N/A',
            optional($order->client)->name ?? 'N/A',
            optional($order->client)->fleet ?? 'N/A',
            $services ?: 'N/A',
            round($subtotal, 2),
            round($tax, 2),
            round($discountPercent, 0),
            $paymentStatus,
            $paymentMethod,
            $this->statusLabels[$order->status] ?? 'Desconocido',
            round((float) ($order->total ?? 0), 2),
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['color' => ['rgb' => 'FFFFFF'], 'bold' => true],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '000000']
                ],
            ],
        ];
    }
}
