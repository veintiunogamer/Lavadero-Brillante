<?php

namespace App\Exports;

use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
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

class ReportClientsExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles, WithCustomStartCell, WithDrawings, WithEvents
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
        return [
            'Cliente',
            'Telefono',
            'Matricula',
            'Modelo',
            'Flota',
            'Citas',
            'Total gastado',
            'Ultima visita',
        ];
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

                $sheet->mergeCells('B1:H1');
                $sheet->mergeCells('B2:H2');
                $sheet->mergeCells('B3:H3');
                $sheet->mergeCells('B4:H4');

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
