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
            'Modelo',
            'Flota',
            'Citas',
            'Total gastado',
            'Ultima visita',
        ];
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
