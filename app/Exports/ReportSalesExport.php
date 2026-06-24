<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithDrawings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class ReportSalesExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles, WithCustomStartCell, WithDrawings, WithEvents
{
    private Collection $orders;
    private array $statusLabels;
    private array $paymentStatusLabels;
    private array $paymentMethodLabels;
    private array $company;
    private string $periodLabel;

    public function __construct(Collection $orders, array $statusLabels, array $paymentStatusLabels, array $paymentMethodLabels, array $company, string $periodLabel)
    {
        $this->orders = $orders;
        $this->statusLabels = $statusLabels;
        $this->paymentStatusLabels = $paymentStatusLabels;
        $this->paymentMethodLabels = $paymentMethodLabels;
        $this->company = $company;
        $this->periodLabel = $periodLabel;
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

    public function startCell(): string
    {
        return 'A6';
    }

    public function map($order): array
    {
        $orderNumber = $order->consecutive_serial && $order->consecutive_number
            ? $order->consecutive_serial . '-' . $order->consecutive_number
            : strtoupper(substr($order->id, 0, 8));

        $services = $order->services->pluck('name')->join(', ');
        $payment = $order->payments->first();
        $paymentStatus = $payment ? ($this->paymentStatusLabels[$payment->status] ?? 'Desconocido') : '--';
        $paymentMethod = $payment ? ($this->paymentMethodLabels[$payment->type] ?? 'Desconocido') : '--';

        $subtotal = (float) ($order->subtotal ?? 0);
        $taxesValue = (float) ($order->taxes_value ?? 0);
        $discountValue = (float) ($order->discount_value ?? 0);
        $discountPercent = $subtotal > 0 ? ($discountValue / $subtotal) * 100 : 0;

        return [
            $orderNumber,
            $order->creation_date ? Carbon::parse($order->creation_date)->format('d/m/Y') : '--',
            optional($order->client)->name ?? '--',
            optional($order->client)->fleet ?? '--',
            $services ?: '--',
            round($subtotal, 2),
            round($taxesValue, 2),
            round($discountPercent, 0),
            $paymentStatus,
            $paymentMethod,
            $this->statusLabels[$order->status] ?? 'Desconocido',
            round((float) ($order->total ?? 0), 2),
        ];
    }

    public function drawings(): array
    {
        if (empty($this->company['logo']) || !file_exists($this->company['logo'])) {
            return [];
        }

        $drawing = new Drawing();
        $drawing->setName('Logo');
        $drawing->setDescription('Lavadero Brillante');
        $drawing->setPath($this->company['logo']);
        $drawing->setHeight(70);
        $drawing->setCoordinates('A1');
        $drawing->setOffsetX(8);
        $drawing->setOffsetY(8);

        return [$drawing];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                $sheet->mergeCells('B1:L1');
                $sheet->mergeCells('B2:L2');
                $sheet->mergeCells('B3:L3');
                $sheet->mergeCells('B4:L4');

                $sheet->setCellValue('B1', $this->company['name'] ?? 'Lavadero Brillante');
                $sheet->setCellValue('B2', $this->company['owner'] ?? '');
                $sheet->setCellValue('B3', ($this->company['nif'] ?? '') . ' | ' . ($this->company['address'] ?? '') . ' | ' . ($this->company['city'] ?? ''));
                $sheet->setCellValue('B4', 'Periodo: ' . $this->periodLabel);

                $sheet->getStyle('B1')->getFont()->setBold(true)->setSize(16);
                $sheet->getStyle('B2:B4')->getFont()->setSize(10);
                $sheet->getStyle('B1:B4')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

                $sheet->getRowDimension(1)->setRowHeight(24);
                $sheet->getRowDimension(2)->setRowHeight(18);
                $sheet->getRowDimension(3)->setRowHeight(18);
                $sheet->getRowDimension(4)->setRowHeight(18);
                $sheet->getRowDimension(5)->setRowHeight(10);
            },
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            6 => [
                'font' => ['color' => ['rgb' => 'FFFFFF'], 'bold' => true],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '000000']
                ],
            ],
        ];
    }
}
