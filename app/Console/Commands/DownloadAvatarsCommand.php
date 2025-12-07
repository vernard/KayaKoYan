<?php

namespace App\Console\Commands;

use App\Enums\UserRole;
use App\Models\User;
use App\Models\WorkerProfile;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Laravel\Facades\Image;

class DownloadAvatarsCommand extends Command
{
    protected $signature = 'avatars:download {--force : Overwrite existing avatars}';

    protected $description = 'Download placeholder profile images for all customers and workers';

    public function handle(): int
    {
        $force = $this->option('force');

        // Ensure avatars directory exists
        Storage::disk('public')->makeDirectory('avatars');

        $this->info('Downloading placeholder avatars...');

        $downloadedCount = 0;
        $skippedCount = 0;
        $failedCount = 0;

        // Process customers
        $customers = User::where('role', UserRole::Customer)->get();
        $this->info("Processing {$customers->count()} customers...");

        $customerBar = $this->output->createProgressBar($customers->count());
        $customerBar->start();

        foreach ($customers as $customer) {
            $result = $this->processUser($customer, $force, 'customer');
            match ($result) {
                'downloaded' => $downloadedCount++,
                'skipped' => $skippedCount++,
                'failed' => $failedCount++,
            };
            $customerBar->advance();
        }

        $customerBar->finish();
        $this->newLine();

        // Process workers (via WorkerProfile)
        $workerProfiles = WorkerProfile::with('user')->get();
        $this->info("Processing {$workerProfiles->count()} workers...");

        $workerBar = $this->output->createProgressBar($workerProfiles->count());
        $workerBar->start();

        foreach ($workerProfiles as $profile) {
            $result = $this->processWorkerProfile($profile, $force);
            match ($result) {
                'downloaded' => $downloadedCount++,
                'skipped' => $skippedCount++,
                'failed' => $failedCount++,
            };
            $workerBar->advance();
        }

        $workerBar->finish();
        $this->newLine(2);

        // Summary
        $this->components->info("Downloaded: {$downloadedCount}");
        $this->components->info("Skipped: {$skippedCount}");

        if ($failedCount > 0) {
            $this->components->warn("Failed: {$failedCount}");
        }

        return Command::SUCCESS;
    }

    private function processUser(User $user, bool $force, string $type): string
    {
        // Skip if already has avatar and not forcing
        if ($user->avatar_path && !$force) {
            return 'skipped';
        }

        // Delete old avatar if forcing
        $oldPath = $force ? $user->avatar_path : null;

        $gender = $this->guessGender($user->name);
        $avatarPath = $this->downloadAndProcessAvatar($gender, $user->id, $oldPath);

        if (!$avatarPath) {
            return 'failed';
        }

        $user->update(['avatar_path' => $avatarPath]);
        return 'downloaded';
    }

    private function processWorkerProfile(WorkerProfile $profile, bool $force): string
    {
        // Skip if already has avatar and not forcing
        if ($profile->avatar_path && !$force) {
            return 'skipped';
        }

        // Delete old avatar if forcing
        $oldPath = $force ? $profile->avatar_path : null;

        // Use gender from profile if available
        $gender = $profile->gender ?? $this->guessGender($profile->user->name);
        $avatarPath = $this->downloadAndProcessAvatar($gender, $profile->id, $oldPath);

        if (!$avatarPath) {
            return 'failed';
        }

        $profile->update(['avatar_path' => $avatarPath]);
        return 'downloaded';
    }

    private function downloadAndProcessAvatar(string $gender, int $seed, ?string $oldPath): ?string
    {
        // Delete old files if provided
        if ($oldPath) {
            $this->deleteAvatarFiles($oldPath);
        }

        // Use seed to get consistent but varied portrait IDs (1-99)
        $portraitId = ($seed % 99) + 1;
        $genderPath = $gender === 'female' ? 'women' : 'men';
        $url = "https://randomuser.me/api/portraits/{$genderPath}/{$portraitId}.jpg";

        try {
            $response = Http::timeout(15)->get($url);

            if (!$response->successful()) {
                $this->components->warn("Failed to download from {$url}");
                return null;
            }

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

            return $mainPath;
        } catch (\Exception $e) {
            $this->components->warn("Error processing avatar: {$e->getMessage()}");
            return null;
        }
    }

    private function deleteAvatarFiles(string $mainPath): void
    {
        Storage::disk('public')->delete($mainPath);

        // Derive small path
        $pathInfo = pathinfo($mainPath);
        $smallPath = "{$pathInfo['dirname']}/{$pathInfo['filename']}_200.{$pathInfo['extension']}";
        Storage::disk('public')->delete($smallPath);
    }

    private function guessGender(string $name): string
    {
        // Simple heuristic based on common Filipino name patterns
        $firstName = strtolower(explode(' ', $name)[0]);

        $femaleNames = ['maria', 'anna', 'lisa', 'sofia', 'grace', 'rose', 'joy', 'mary', 'jane', 'ana'];
        $maleNames = ['juan', 'carlos', 'miguel', 'david', 'mark', 'john', 'jose', 'antonio', 'luis', 'pedro'];

        if (in_array($firstName, $femaleNames)) {
            return 'female';
        }

        if (in_array($firstName, $maleNames)) {
            return 'male';
        }

        // Random fallback based on name hash for consistency
        return crc32($name) % 2 === 0 ? 'male' : 'female';
    }
}
