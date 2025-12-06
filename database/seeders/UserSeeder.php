<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Admin user
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@kayakoyan.com',
            'password' => Hash::make('password'),
            'role' => UserRole::Admin,
            'email_verified_at' => now(),
        ]);

        // Workers
        $workers = [
            ['name' => 'Maria Santos', 'email' => 'maria.santos@example.com'],
            ['name' => 'Juan Dela Cruz', 'email' => 'juan.delacruz@example.com'],
            ['name' => 'Anna Reyes', 'email' => 'anna.reyes@example.com'],
            ['name' => 'Carlos Garcia', 'email' => 'carlos.garcia@example.com'],
            ['name' => 'Lisa Mendoza', 'email' => 'lisa.mendoza@example.com'],
        ];

        foreach ($workers as $worker) {
            User::create([
                'name' => $worker['name'],
                'email' => $worker['email'],
                'password' => Hash::make('password'),
                'role' => UserRole::Worker,
                'email_verified_at' => now(),
            ]);
        }

        // Customers
        $customers = [
            ['name' => 'Miguel Torres', 'email' => 'miguel.torres@example.com'],
            ['name' => 'Sofia Lim', 'email' => 'sofia.lim@example.com'],
            ['name' => 'David Cruz', 'email' => 'david.cruz@example.com'],
            ['name' => 'Grace Tan', 'email' => 'grace.tan@example.com'],
            ['name' => 'Mark Villanueva', 'email' => 'mark.villanueva@example.com'],
        ];

        foreach ($customers as $customer) {
            User::create([
                'name' => $customer['name'],
                'email' => $customer['email'],
                'password' => Hash::make('password'),
                'role' => UserRole::Customer,
                'email_verified_at' => now(),
            ]);
        }
    }
}
