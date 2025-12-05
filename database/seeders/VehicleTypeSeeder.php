<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class VehicleTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $types = [
            'Mini Cooper',
            'Coche (Pequeño)',
            'Automóvil Mediano y SUV Pequeño',
            'Camioneta / SUV Grande',
            'Furgoneta Corta',
            'Furgoneta Larga',
            'Camión / Cabeza Tractora',
            'Caja de Camión',
            'Moto',
        ];

        foreach ($types as $name) {
            DB::table('vehicle_type')->insert([
                'id' => (string) Str::uuid(),
                'name' => $name,
                'creation_date' => now(),
            ]);
        }
    }
}
