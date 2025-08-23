<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = [
            [
                'name' => 'Super Admin',
                'email' => 'superadmin@sumselweddingexpo.com',
                'password' => Hash::make('superadmin123'),
                'email_verified_at' => now(),
            ],
            [
                'name' => 'Admin Expo',
                'email' => 'admin@sumselweddingexpo.com',
                'password' => Hash::make('admin123'),
                'email_verified_at' => now(),
            ],
            [
                'name' => 'Operator',
                'email' => 'operator@sumselweddingexpo.com',
                'password' => Hash::make('operator123'),
                'email_verified_at' => now(),
            ],
            [
                'name' => 'Staff Keuangan',
                'email' => 'keuangan@sumselweddingexpo.com',
                'password' => Hash::make('keuangan123'),
                'email_verified_at' => now(),
            ],
        ];

        foreach ($users as $userData) {
            User::create($userData);
        }

        // Membuat user dummy untuk testing
        User::factory(10)->create();
    }
}
