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
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class ReportSalesExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles, WithCustomStartCell, WithDrawings, WithEvents, WithColumnWidths
{
    private Collection $orders;
    private OrderReportService $reportService;
    private array $company;
    private string $periodLabel;
    private array $summary;
    private bool $isDaily;
    private bool $hideDate;

    public function __construct(Collection $orders, OrderReportService $reportService, array $company, string $periodLabel, array $summary = [], bool $isDaily = false, bool $hideDate = false)
    {
        $this->orders = $orders;
        $this->reportService = $reportService;
        $this->company = $company;
        $this->periodLabel = $periodLabel;
        $this->summary = $summary;
        $this->isDaily = $isDaily;
        $this->hideDate = $hideDate;
    }

    public function collection(): Collection
    {
        return $this->orders;
    }

    public function headings(): array
    {
        $headings = OrderReportService::headings();
        $headings[1] = 'Entrada';
        array_splice($headings, 2, 0, ['Salida']);
        $headings[3] = 'Nombre';
        $headings[10] = 'Importe';
        $headings[12] = 'Descuento';
        $headings[13] = 'Estado de pago';
        $headings[14] = 'Forma de pago';
        if ($this->hideDate) {
            array_shift($headings);
        }
        return array_map(static fn ($heading) => mb_strtoupper($heading, 'UTF-8'), $headings);
    }

    public function startCell(): string
    {
        return 'A6';
    }

    /**
     * Fila de datos: los campos del informe en el orden de los encabezados.
     * Los importes se devuelven como números (sin símbolos monetarios) para
     * que Excel los almacene como celdas numéricas.
     */
    public function map($order): array
    {
        $row = array_values($this->reportService->row($order));
        if (!empty($row[0]) && $row[0] !== '--') {
            $date = \DateTime::createFromFormat('!d/m/Y', $row[0]);
            if ($date !== false) {
                $row[0] = ExcelDate::PHPToExcel($date);
            }
        }

        $hours = array_splice($row, 1, 1)[0] ?? '--';
        $parts = explode(' - ', $hours, 2);
        $entry = $this->excelTime($parts[0] ?? '--');
        $exit = $this->excelTime($parts[1] ?? '--');
        array_splice($row, 1, 0, [$entry, $exit]);
        if ($this->hideDate) {
            array_shift($row);
        }
        return $row;
    }

    private function excelTime(string $time): string|float
    {
        $value = \DateTime::createFromFormat('!H:i', $time);
        return $value === false ? $time : ExcelDate::PHPToExcel($value);
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
        $drawing->setResizeProportional(true);
        $drawing->setHeight(76);
        $drawing->setCoordinates('A1');
        $drawing->setOffsetX(8);
        $drawing->setOffsetY(22);

        return [$drawing];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $dataEndRow = 6 + $this->orders->count();
                $summaryStartRow = $dataEndRow + 2;

                $lastColumn = $this->hideDate ? 'Q' : 'R';

                $sheet->mergeCells('A1:B4');
                $sheet->mergeCells("C1:{$lastColumn}1");
                $sheet->mergeCells("C2:{$lastColumn}2");
                $sheet->mergeCells("C3:{$lastColumn}3");
                $sheet->mergeCells("C4:{$lastColumn}4");

                $sheet->setCellValue('C1', $this->company['name'] ?? 'Lavadero Brillante');
                $sheet->setCellValue('C2', $this->company['owner'] ?? '');
                $sheet->setCellValue('C3', ($this->company['nif'] ?? '') . ' | ' . ($this->company['address'] ?? '') . ' | ' . ($this->company['city'] ?? ''));
                $sheet->setCellValue('C4', 'Fecha: ' . $this->periodLabel);

                $sheet->getStyle('C1')->getFont()->setBold(true)->setSize(16);
                $sheet->getStyle('C2:C4')->getFont()->setSize(10);
                $sheet->getStyle('C1:C4')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

                $sheet->getRowDimension(1)->setRowHeight(30);
                $sheet->getRowDimension(2)->setRowHeight(20);
                $sheet->getRowDimension(3)->setRowHeight(20);
                $sheet->getRowDimension(4)->setRowHeight(20);
                $sheet->getRowDimension(5)->setRowHeight(10);
                $sheet->freezePane('A7');
                $sheet->setAutoFilter("A6:{$lastColumn}6");
                $sheet->getRowDimension(6)->setRowHeight(30);
                $sheet->getSheetView()->setZoomScale(90);

                $lastDataRow = max(7, $dataEndRow);
                $dateColumn = $this->hideDate ? null : 'A';
                $entryColumn = $this->hideDate ? 'A' : 'B';
                $exitColumn = $this->hideDate ? 'B' : 'C';
                if ($dateColumn !== null) {
                    $sheet->getStyle("{$dateColumn}7:{$dateColumn}{$lastDataRow}")
                        ->getNumberFormat()->setFormatCode('dd/mm/yyyy');
                }
                $sheet->getStyle("{$entryColumn}7:{$entryColumn}{$lastDataRow}")->getNumberFormat()->setFormatCode('hh:mm');
                $sheet->getStyle("{$exitColumn}7:{$exitColumn}{$lastDataRow}")->getNumberFormat()->setFormatCode('hh:mm');

                $sheet->mergeCells("A{$summaryStartRow}:{$lastColumn}{$summaryStartRow}");
                $sheet->setCellValue("A{$summaryStartRow}", 'RESUMEN DEL REPORTE');
                $sheet->getStyle("A{$summaryStartRow}:{$lastColumn}{$summaryStartRow}")->getFont()->setBold(true)->setSize(14);
                $sheet->getStyle("A{$summaryStartRow}:{$lastColumn}{$summaryStartRow}")->getFill()->setFillType(Fill::FILL_SOLID);
                $sheet->getStyle("A{$summaryStartRow}:{$lastColumn}{$summaryStartRow}")->getFill()->getStartColor()->setRGB('DBEAFE');
                $sheet->getRowDimension($summaryStartRow)->setRowHeight(30);

                $summaryRows = [
                    ['label' => 'Efectivo', 'value' => $this->summary['cash'] ?? 0],
                    ['label' => 'TPV', 'value' => $this->summary['card'] ?? 0],
                    ['label' => 'Transferencia', 'value' => $this->summary['transfer'] ?? 0],
                    ['label' => 'Otros gastos', 'value' => 0],
                    ['label' => $this->isDaily ? 'Total día' : 'Total facturado', 'value' => $this->summary['total'] ?? 0],
                    ['label' => 'Total órdenes', 'value' => $this->summary['orders'] ?? 0],
                ];

                $row = $summaryStartRow + 1;
                foreach ($summaryRows as $item) {
                    $sheet->mergeCells("A{$row}:E{$row}");
                    $sheet->mergeCells("F{$row}:{$lastColumn}{$row}");
                    $sheet->setCellValue("A{$row}", $item['label']);
                    $sheet->setCellValue("F{$row}", is_numeric($item['value'])
                        ? ($item['label'] === 'Total órdenes'
                            ? number_format((float) $item['value'], 0, ',', '.')
                            : number_format((float) $item['value'], 2, ',', '.') . ' €')
                        : $item['value']);

                    $sheet->getStyle("A{$row}:{$lastColumn}{$row}")->getFont()->setBold(true);
                    $sheet->getRowDimension($row)->setRowHeight(24);
                    $sheet->getStyle("A{$row}:{$lastColumn}{$row}")->getBorders()->getAllBorders()
                        ->setBorderStyle(Border::BORDER_THIN)
                        ->getColor()->setRGB('D1D5DB');

                    if ($item['label'] === 'Total órdenes') {
                        $sheet->getStyle("A{$row}:{$lastColumn}{$row}")->getFill()->setFillType(Fill::FILL_SOLID);
                        $sheet->getStyle("A{$row}:{$lastColumn}{$row}")->getFill()->getStartColor()->setRGB('DBEAFE');
                    } else {
                        $sheet->getStyle("A{$row}:{$lastColumn}{$row}")->getFill()->setFillType(Fill::FILL_SOLID);
                        $sheet->getStyle("A{$row}:{$lastColumn}{$row}")->getFill()->getStartColor()->setRGB('F8FAFC');
                    }

                    $row++;
                }

                // Horas de entrada y salida centradas y la fila de órdenes alineada a la izquierda.
                $hoursColumns = $this->hideDate ? 'A7:B' : 'B7:C';
                $sheet->getStyle($hoursColumns . max(7, $dataEndRow))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $ordersRow = $summaryStartRow + 6;
                $sheet->getStyle("F{$ordersRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
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
            'A' => $this->hideDate ? 12 : 16, // Fecha
            'B' => 12, // Entrada
            'C' => 12, // Salida
            'D' => 30, // Nombre
            'E' => 14, // Teléfono
            'F' => 14, // Matrícula
            'G' => 26, // Categoría
            'H' => 9,  // Flota
            'I' => 50, // Servicios
            'J' => 20, // Detallador
            'K' => 13, // Importe
            'L' => 11, // IVA
            'M' => 14, // Descuento
            'N' => 18, // Estado de pago
            'O' => 18, // Forma de pago
            'P' => 13, // Estado
            'Q' => 13, // Total
            'R' => 60, // Observaciones
        ];
    }
}
