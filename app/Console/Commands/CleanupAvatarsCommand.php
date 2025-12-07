<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\WorkerProfile;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class CleanupAvatarsCommand extends Command
{
    protected $signature = 'avatars:cleanup {--dry-run : Show what would be deleted without deleting}';

    protected $description = 'Remove orphaned avatar files not referenced by any user or worker profile';

    public function handle(): int
    {
        $dryRun = $this->option('dry-run');

        if ($dryRun) {
            $this->components->info('Running in dry-run mode - no files will be deleted');
        }

        // Get all files in avatars directory
        $allFiles = Storage::disk('public')->files('avatars');

        if (empty($allFiles)) {
            $this->components->info('No avatar files found.');
            return Command::SUCCESS;
        }

        $this->info("Found " . count($allFiles) . " files in avatars directory");

        // Get all referenced avatar paths
        $userAvatars = User::whereNotNull('avatar_path')
            ->pluck('avatar_path')
            ->toArray();

        $workerAvatars = WorkerProfile::whereNotNull('avatar_path')
            ->pluck('avatar_path')
            ->toArray();

        // Build set of all referenced files (both main and _200 versions)
        $referencedFiles = [];

        foreach (array_merge($userAvatars, $workerAvatars) as $mainPath) {
            $referencedFiles[$mainPath] = true;

            // Also mark the _200 version as referenced
            $smallPath = $this->getSmallPath($mainPath);
            $referencedFiles[$smallPath] = true;
        }

        $this->info("Found " . count($userAvatars) . " user avatars and " . count($workerAvatars) . " worker avatars in database");

        // Find orphaned files
        $orphanedFiles = [];
        $totalSize = 0;

        foreach ($allFiles as $file) {
            if (!isset($referencedFiles[$file])) {
                $orphanedFiles[] = $file;
                $totalSize += Storage::disk('public')->size($file);
            }
        }

        if (empty($orphanedFiles)) {
            $this->components->info('No orphaned files found. All avatars are in use.');
            return Command::SUCCESS;
        }

        $this->newLine();
        $this->components->warn("Found " . count($orphanedFiles) . " orphaned files (" . $this->formatBytes($totalSize) . ")");

        if ($this->output->isVerbose() || $dryRun) {
            $this->newLine();
            $this->info('Orphaned files:');
            foreach ($orphanedFiles as $file) {
                $size = Storage::disk('public')->size($file);
                $this->line("  - {$file} (" . $this->formatBytes($size) . ")");
            }
        }

        // Delete orphaned files
        if (!$dryRun) {
            $this->newLine();
            $bar = $this->output->createProgressBar(count($orphanedFiles));
            $bar->start();

            $deletedCount = 0;
            foreach ($orphanedFiles as $file) {
                if (Storage::disk('public')->delete($file)) {
                    $deletedCount++;
                }
                $bar->advance();
            }

            $bar->finish();
            $this->newLine(2);

            $this->components->info("Deleted {$deletedCount} orphaned files, freed " . $this->formatBytes($totalSize));
        } else {
            $this->newLine();
            $this->components->info("Would delete " . count($orphanedFiles) . " files and free " . $this->formatBytes($totalSize));
            $this->info('Run without --dry-run to actually delete files.');
        }

        return Command::SUCCESS;
    }

    private function getSmallPath(string $mainPath): string
    {
        $pathInfo = pathinfo($mainPath);
        $dir = $pathInfo['dirname'];
        $name = $pathInfo['filename'];
        $ext = $pathInfo['extension'] ?? 'jpg';

        return "{$dir}/{$name}_200.{$ext}";
    }

    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $power = $bytes > 0 ? floor(log($bytes, 1024)) : 0;
        $power = min($power, count($units) - 1);

        return number_format($bytes / pow(1024, $power), 2) . ' ' . $units[$power];
    }
}
