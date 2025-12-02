<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            ['name' => 'Administrador', 'type' => 1],
            ['name' => 'Operador', 'type' => 2],
            ['name' => 'Cajero', 'type' => 3],
        ];

        foreach ($roles as $role) {
            DB::table('roles')->insert([
                'id' => (string) Str::uuid(),
                'name' => $role['name'],
                'type' => $role['type'],
                'status' => true,
                'creation_date' => now(),
            ]);
        }
    }
}
