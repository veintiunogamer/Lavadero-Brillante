<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
class RoleSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('roles')->insert([
            'id' => (string) Str::uuid(),
            'name' => 'Administrador',
            'type' => 1,
            'status' => true,
            'creation_date' => now(),
        ]);
    }
}
