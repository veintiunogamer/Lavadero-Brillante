<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Tax;
use Illuminate\Support\Str;

class TaxSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $taxes = [
            [
                'id' => Str::uuid(),
                'percent' => 5.00,
                'status' => Tax::ACTIVE,
                'creation_date' => now(),
            ],
            [
                'id' => Str::uuid(),
                'percent' => 10.00,
                'status' => Tax::ACTIVE,
                'creation_date' => now(),
            ],
            [
                'id' => Str::uuid(),
                'percent' => 15.00,
                'status' => Tax::ACTIVE,
                'creation_date' => now(),
            ],
        ];

        foreach ($taxes as $tax) {
            Tax::create($tax);
        }
    }
}
