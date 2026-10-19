<?php

namespace App\Exports;

use App\Services\OrderReportService;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithDrawings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class ReportSalesExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles, WithCustomStartCell, WithDrawings, WithEvents, WithColumnWidths
{
    private Collection $orders;
    private OrderReportService $reportService;
    private array $company;
    private string $periodLabel;
    private array $summary;

    public function __construct(Collection $orders, OrderReportService $reportService, array $company, string $periodLabel, array $summary = [])
    {
        $this->orders = $orders;
        $this->reportService = $reportService;
        $this->company = $company;
        $this->periodLabel = $periodLabel;
        $this->summary = $summary;
    }

    public function collection(): Collection
    {
        return $this->orders;
    }

    public function headings(): array
    {
        return OrderReportService::headings();
    }

    public function startCell(): string
    {
        return 'A6';
    }

    /**
     * Fila de datos: los 17 campos del informe, en el orden de los encabezados.
     * Los importes se devuelven como números (sin símbolos monetarios) para
     * que Excel los almacene como celdas numéricas.
     */
    public function map($order): array
    {
        return array_values($this->reportService->row($order));
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
                $dataEndRow = 6 + $this->orders->count();
                $summaryStartRow = $dataEndRow + 2;

                $sheet->mergeCells('B1:Q1');
                $sheet->mergeCells('B2:Q2');
                $sheet->mergeCells('B3:Q3');
                $sheet->mergeCells('B4:Q4');

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
                $sheet->freezePane('A7');
                $sheet->setAutoFilter('A6:Q6');
                $sheet->getSheetView()->setZoomScale(90);

                $sheet->mergeCells("A{$summaryStartRow}:Q{$summaryStartRow}");
                $sheet->setCellValue("A{$summaryStartRow}", 'RESUMEN DEL REPORTE');
                $sheet->getStyle("A{$summaryStartRow}")->getFont()->setBold(true)->setSize(12);
                $sheet->getStyle("A{$summaryStartRow}")->getFill()->setFillType(Fill::FILL_SOLID);
                $sheet->getStyle("A{$summaryStartRow}")->getFill()->getStartColor()->setRGB('D1FAE5');

                $summaryRows = [
                    ['label' => 'Efectivo', 'value' => $this->summary['cash'] ?? 0],
                    ['label' => 'TPV', 'value' => $this->summary['card'] ?? 0],
                    ['label' => 'Transferencia', 'value' => $this->summary['transfer'] ?? 0],
                    ['label' => 'Total facturado', 'value' => $this->summary['total'] ?? 0],
                    ['label' => 'Órdenes', 'value' => $this->summary['orders'] ?? 0],
                ];

                $row = $summaryStartRow + 1;
                foreach ($summaryRows as $item) {
                    $sheet->mergeCells("A{$row}:E{$row}");
                    $sheet->mergeCells("F{$row}:Q{$row}");
                    $sheet->setCellValue("A{$row}", $item['label']);
                    $sheet->setCellValue("F{$row}", is_numeric($item['value'])
                        ? ($item['label'] === 'Órdenes'
                            ? number_format((float) $item['value'], 0, ',', '.')
                            : number_format((float) $item['value'], 2, ',', '.') . ' €')
                        : $item['value']);

                    $sheet->getStyle("A{$row}:Q{$row}")->getFont()->setBold(true);
                    $sheet->getStyle("A{$row}:Q{$row}")->getBorders()->getAllBorders()
                        ->setBorderStyle(Border::BORDER_THIN)
                        ->getColor()->setRGB('D1D5DB');

                    if ($item['label'] === 'Órdenes') {
                        $sheet->getStyle("A{$row}:Q{$row}")->getFill()->setFillType(Fill::FILL_SOLID);
                        $sheet->getStyle("A{$row}:Q{$row}")->getFill()->getStartColor()->setRGB('ECFDF5');
                    } else {
                        $sheet->getStyle("A{$row}:Q{$row}")->getFill()->setFillType(Fill::FILL_SOLID);
                        $sheet->getStyle("A{$row}:Q{$row}")->getFill()->getStartColor()->setRGB('F8FAFC');
                    }

                    $row++;
                }
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
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 12, // Fecha
            'B' => 20, // Entrada - Salida
            'C' => 30, // Cliente
            'D' => 14, // Teléfono
            'E' => 14, // Matrícula
            'F' => 26, // Categoría
            'G' => 9,  // Flota
            'H' => 50, // Servicios
            'I' => 20, // Detallador
            'J' => 13, // Subtotal
            'K' => 11, // IVA
            'L' => 15, // Descuento ($)
            'M' => 13, // Pago
            'N' => 15, // Método
            'O' => 13, // Estado
            'P' => 13, // Total
            'Q' => 60, // Observaciones
        ];
    }
}
