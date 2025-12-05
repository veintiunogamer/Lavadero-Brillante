<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            'Lavados',
            'Tapicería',
            'Pulidos',
            'Desinfección',
            'Accesorios',
            'Mecánica / Limpiezas',
        ];

        foreach ($categories as $cat_name) {
            DB::table('category')->insert([
                'id' => (string) Str::uuid(),
                'cat_name' => $cat_name,
                'status' => true,
                'creation_date' => now(),
            ]);
        }
    }
}
