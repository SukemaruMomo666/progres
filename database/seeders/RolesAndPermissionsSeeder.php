<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Bersihkan cache Spatie
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Daftarkan Peran Kerja
        $founderRole   = Role::create(['name' => 'Founder']);
        $coFounderRole = Role::create(['name' => 'Co-Founder']);
        $hrRole        = Role::create(['name' => 'HR']);
        $staffRole     = Role::create(['name' => 'Staff']);

        // HANYA 1 USER: Akun Utama Founder
        $founder = User::create([
            'name' => 'Ari Kurtubi',
            'email' => 'founder@xgrow.com',
            'password' => Hash::make('password'), // Silakan ganti sandi ini nanti
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        $founder->assignRole($founderRole);
    }
}