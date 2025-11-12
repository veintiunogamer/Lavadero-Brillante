<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $rolAdmin = DB::table('roles')->where('type', 1)->first();
        DB::table('users')->insert([
            'id' => (string) Str::uuid(),
            'name' => 'Administrador',
            'email' => 'admin@lavadero.com',
            'phone' => null,
            'username' => 'admin',
            'password' => Hash::make('admin'),
            'rol' => $rolAdmin ? $rolAdmin->id : null,
            'status' => true,
            'creation_date' => now(),
        ]);
    }
}
