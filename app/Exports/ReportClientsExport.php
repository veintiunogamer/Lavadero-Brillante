<?php

namespace App\Exports;

use Illuminate\Support\Carbon;
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
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class ReportClientsExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles, WithCustomStartCell, WithDrawings, WithEvents, WithColumnWidths
{
    private Collection $clients;
    private array $company;
    private string $periodLabel;

    public function __construct(Collection $clients, array $company, string $periodLabel)
    {
        $this->clients = $clients;
        $this->company = $company;
        $this->periodLabel = $periodLabel;
    }

    public function collection(): Collection
    {
        return $this->clients;
    }

    public function headings(): array
    {
        return array_map(static fn ($heading) => mb_strtoupper($heading, 'UTF-8'), [
            'Nombre',
            'Teléfono',
            'Matrícula',
            'Modelo',
            'Flota',
            'Citas',
            'Total gastado',
            'Última visita',
        ]);
    }

    public function startCell(): string
    {
        return 'A6';
    }

    public function map($client): array
    {
        return [
            $client->name,
            $client->phone ?? '--',
            $client->license_plaque ?? '--',
            $client->brand ?? '--',
            $client->fleet == 1 ? 'Sí' : 'No',
            (int) (isset($client->orders_count) ? $client->orders_count : '0'),
            round((float) (isset($client->total_spent) ? $client->total_spent : '0'), 2),
            $client->last_order_date ? Carbon::parse($client->last_order_date)->format('d/m/Y') : '--',
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
        $drawing->setResizeProportional(true);
        $drawing->setHeight(76);
        $drawing->setCoordinates('A1');
        $drawing->setOffsetX(42);
        $drawing->setOffsetY(22);

        return [$drawing];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                $sheet->mergeCells('A1:B4');
                $sheet->mergeCells('C1:H1');
                $sheet->mergeCells('C2:H2');
                $sheet->mergeCells('C3:H3');
                $sheet->mergeCells('C4:H4');

                $sheet->setCellValue('C1', $this->company['name'] ?? 'Lavadero Brillante');
                $sheet->setCellValue('C2', $this->company['owner'] ?? '');
                $sheet->setCellValue('C3', ($this->company['nif'] ?? '') . ' | ' . ($this->company['address'] ?? '') . ' | ' . ($this->company['city'] ?? ''));
                $sheet->setCellValue('C4', 'Periodo: ' . $this->periodLabel);

                $sheet->getStyle('C1')->getFont()->setBold(true)->setSize(16);
                $sheet->getStyle('C2:C4')->getFont()->setSize(10);
                $sheet->getStyle('C1:C4')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

                $sheet->getRowDimension(1)->setRowHeight(30);
                $sheet->getRowDimension(2)->setRowHeight(20);
                $sheet->getRowDimension(3)->setRowHeight(20);
                $sheet->getRowDimension(4)->setRowHeight(20);
                $sheet->getRowDimension(5)->setRowHeight(10);
                $sheet->freezePane('A7');
                $sheet->setAutoFilter('A6:H6');
                $sheet->getSheetView()->setZoomScale(90);
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
            'A' => 24,
            'B' => 14,
            'C' => 16,
            'D' => 18,
            'E' => 10,
            'F' => 10,
            'G' => 16,
            'H' => 14,
        ];
    }
}
