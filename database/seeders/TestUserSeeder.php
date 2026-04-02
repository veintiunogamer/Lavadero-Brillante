<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class TestUserSeeder extends Seeder
{
    public function run(): void
    {
        $rolAdmin = DB::table('roles')->where('type', 1)->first();

        $exists = DB::table('users')->where('username', 'soporte')->exists();

        if (!$exists) {
            DB::table('users')->insert([
                'id'            => (string) Str::uuid(),
                'name'          => 'Soporte',
                'email'         => 'soporte@lavadero.com',
                'phone'         => null,
                'username'      => 'soporte',
                'password'      => Hash::make('soporte'),
                'rol'           => $rolAdmin ? $rolAdmin->id : null,
                'status'        => true,
                'creation_date' => now(),
            ]);

            $this->command->info('✓ Usuario soporte creado correctamente.');
        } else {
            $this->command->warn('⚠️  El usuario soporte ya existe, se omitió la creación.');
        }
    }
}
