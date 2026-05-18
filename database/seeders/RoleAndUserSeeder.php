<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class RoleAndUserSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Buat Roles
        $roleFounder = Role::create(['name' => 'Founder']);
        $roleCoFounder = Role::create(['name' => 'Co-Founder']);
        $roleHR = Role::create(['name' => 'HR']);
        $roleDev = Role::create(['name' => 'Dev']);
        $roleQA = Role::create(['name' => 'QA']);

        // 2. Buat Akun Founder Utama
        $founder = User::create([
            'name' => 'Qisty Sauva',
            'email' => 'qisty@xgrow.com',
            'phone' => '081234567890',
            'password' => Hash::make('password123'),
            'is_active' => true,
        ]);
        
        // 3. Assign Role ke User
        $founder->assignRole($roleFounder);
    }
}