<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Laravel\Facades\Image;

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
            ['name' => 'Miguel Torres', 'email' => 'miguel.torres@example.com', 'avatar_url' => 'https://randomuser.me/api/portraits/men/22.jpg'],
            ['name' => 'Sofia Lim', 'email' => 'sofia.lim@example.com', 'avatar_url' => 'https://randomuser.me/api/portraits/women/33.jpg'],
            ['name' => 'David Cruz', 'email' => 'david.cruz@example.com', 'avatar_url' => 'https://randomuser.me/api/portraits/men/45.jpg'],
            ['name' => 'Grace Tan', 'email' => 'grace.tan@example.com', 'avatar_url' => 'https://randomuser.me/api/portraits/women/56.jpg'],
            ['name' => 'Mark Villanueva', 'email' => 'mark.villanueva@example.com', 'avatar_url' => 'https://randomuser.me/api/portraits/men/67.jpg'],
        ];

        // Ensure avatars directory exists
        Storage::disk('public')->makeDirectory('avatars');

        foreach ($customers as $customer) {
            // Download avatar and create resized versions
            $avatarPath = null;
            try {
                $response = Http::timeout(10)->get($customer['avatar_url']);
                if ($response->successful()) {
                    $uuid = Str::uuid()->toString();
                    $mainPath = "avatars/{$uuid}.jpg";
                    $smallPath = "avatars/{$uuid}_200.jpg";

                    // Read and process the image
                    $image = Image::read($response->body());

                    // Create 600x600 version (main)
                    $main = $image->cover(600, 600);
                    Storage::disk('public')->put($mainPath, $main->toJpeg(85));

                    // Create 200x200 version (small)
                    $small = $image->cover(200, 200);
                    Storage::disk('public')->put($smallPath, $small->toJpeg(85));

                    $avatarPath = $mainPath;
                }
            } catch (\Exception $e) {
                // Silently fail, avatar will use UI Avatars fallback
            }

            User::create([
                'name' => $customer['name'],
                'email' => $customer['email'],
                'password' => Hash::make('password'),
                'role' => UserRole::Customer,
                'email_verified_at' => now(),
                'avatar_path' => $avatarPath,
            ]);
        }
    }
}
