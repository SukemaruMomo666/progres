<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Panggil pendaftaran peran Spatie dan akun master di sini
        $this->call([
            RolesAndPermissionsSeeder::class,
        ]);
    }
}