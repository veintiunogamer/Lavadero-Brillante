<?php

namespace App\Exports;

use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class ReportClientsExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    private Collection $clients;

    public function __construct(Collection $clients)
    {
        $this->clients = $clients;
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
            'Citas',
            'Total gastado',
            'Ultima visita',
        ];
    }

    public function map($client): array
    {
        return [
            $client->name,
            $client->phone ?? 'N/A',
            $client->license_plaque ?? 'N/A',
            (int) $client->orders_count,
            round((float) ($client->total_spent ?? 0), 2),
            $client->last_order_date ? Carbon::parse($client->last_order_date)->format('d/m/Y') : 'N/A',
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
