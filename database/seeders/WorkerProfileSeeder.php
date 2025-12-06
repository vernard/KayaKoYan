<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\WorkerProfile;
use App\Enums\UserRole;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class WorkerProfileSeeder extends Seeder
{
    public function run(): void
    {
        // Ensure storage directory exists
        Storage::disk('public')->makeDirectory('avatars');

        $profiles = [
            'maria.santos@example.com' => [
                'bio' => 'Experienced Virtual Assistant with 5+ years helping entrepreneurs manage their businesses. Specialized in email management, calendar scheduling, and data entry.',
                'phone' => '09171234567',
                'location' => 'Quezon City, Metro Manila',
                'gcash_number' => '09171234567',
                'gcash_name' => 'Maria Santos',
                'bank_name' => 'BDO',
                'bank_account_number' => '001234567890',
                'bank_account_name' => 'Maria S. Santos',
                'avatar_url' => 'https://randomuser.me/api/portraits/women/44.jpg',
                'gender' => 'female',
            ],
            'juan.delacruz@example.com' => [
                'bio' => 'Professional Graphic Designer creating stunning visuals for brands. Logo design, social media graphics, and marketing materials are my expertise.',
                'phone' => '09182345678',
                'location' => 'Makati City, Metro Manila',
                'gcash_number' => '09182345678',
                'gcash_name' => 'Juan Dela Cruz',
                'bank_name' => 'BPI',
                'bank_account_number' => '002345678901',
                'bank_account_name' => 'Juan D. Dela Cruz',
                'avatar_url' => 'https://randomuser.me/api/portraits/men/32.jpg',
                'gender' => 'male',
            ],
            'anna.reyes@example.com' => [
                'bio' => 'Creative Video Editor with expertise in YouTube content, TikTok videos, and promotional materials. I turn your raw footage into engaging stories.',
                'phone' => '09193456789',
                'location' => 'Cebu City, Cebu',
                'gcash_number' => '09193456789',
                'gcash_name' => 'Anna Reyes',
                'bank_name' => 'Metrobank',
                'bank_account_number' => '003456789012',
                'bank_account_name' => 'Anna M. Reyes',
                'avatar_url' => 'https://randomuser.me/api/portraits/women/68.jpg',
                'gender' => 'female',
            ],
            'carlos.garcia@example.com' => [
                'bio' => 'Social Media Manager helping businesses grow their online presence. Strategy, content creation, and community management all in one package.',
                'phone' => '09204567890',
                'location' => 'Davao City, Davao del Sur',
                'gcash_number' => '09204567890',
                'gcash_name' => 'Carlos Garcia',
                'bank_name' => 'UnionBank',
                'bank_account_number' => '004567890123',
                'bank_account_name' => 'Carlos R. Garcia',
                'avatar_url' => 'https://randomuser.me/api/portraits/men/75.jpg',
                'gender' => 'male',
            ],
            'lisa.mendoza@example.com' => [
                'bio' => 'Professional Content Writer specializing in blog posts, articles, and SEO content. Let me help you tell your brand story.',
                'phone' => '09215678901',
                'location' => 'Iloilo City, Iloilo',
                'gcash_number' => '09215678901',
                'gcash_name' => 'Lisa Mendoza',
                'bank_name' => 'Security Bank',
                'bank_account_number' => '005678901234',
                'bank_account_name' => 'Lisa A. Mendoza',
                'avatar_url' => 'https://randomuser.me/api/portraits/women/90.jpg',
                'gender' => 'female',
            ],
        ];

        foreach ($profiles as $email => $profileData) {
            $user = User::where('email', $email)->first();
            if ($user) {
                // Download and save avatar
                $avatarPath = null;
                try {
                    $response = Http::timeout(10)->get($profileData['avatar_url']);
                    if ($response->successful()) {
                        $filename = 'avatars/' . Str::slug($user->name) . '.jpg';
                        Storage::disk('public')->put($filename, $response->body());
                        $avatarPath = $filename;
                    }
                } catch (\Exception $e) {
                    // Silently fail, avatar will use UI Avatars fallback
                }

                WorkerProfile::create([
                    'user_id' => $user->id,
                    'slug' => Str::slug($user->name),
                    'bio' => $profileData['bio'],
                    'phone' => $profileData['phone'],
                    'location' => $profileData['location'],
                    'gcash_number' => $profileData['gcash_number'],
                    'gcash_name' => $profileData['gcash_name'],
                    'bank_name' => $profileData['bank_name'],
                    'bank_account_number' => $profileData['bank_account_number'],
                    'bank_account_name' => $profileData['bank_account_name'],
                    'avatar_path' => $avatarPath,
                    'is_active' => true,
                ]);
            }
        }
    }
}
