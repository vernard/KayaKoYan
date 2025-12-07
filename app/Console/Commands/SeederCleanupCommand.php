<?php

namespace App\Console\Commands;

use App\Models\ListingImage;
use App\Models\User;
use App\Models\WorkerProfile;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class SeederCleanupCommand extends Command
{
    protected $signature = 'seeders:cleanup
                            {--dry-run : Show what would be deleted without deleting}
                            {--avatars-only : Only clean up avatars}
                            {--listings-only : Only clean up listing images}';

    protected $description = 'Remove orphaned avatar and listing image files not referenced in the database';

    private int $totalFilesDeleted = 0;
    private int $totalBytesFreed = 0;

    public function handle(): int
    {
        $dryRun = $this->option('dry-run');
        $avatarsOnly = $this->option('avatars-only');
        $listingsOnly = $this->option('listings-only');

        if ($dryRun) {
            $this->components->info('Running in dry-run mode - no files will be deleted');
            $this->line('');
        }

        if (!$listingsOnly) {
            $this->cleanupAvatars($dryRun);
        }

        if (!$avatarsOnly) {
            $this->cleanupListingImages($dryRun);
        }

        $this->printSummary($dryRun);

        return Command::SUCCESS;
    }

    private function cleanupAvatars(bool $dryRun): void
    {
        $this->components->info('Cleaning up Avatars');
        $this->line('');

        $allFiles = Storage::disk('public')->files('avatars');

        if (empty($allFiles)) {
            $this->line('  No avatar files found.');
            $this->line('');
            return;
        }

        $this->line("  Found " . count($allFiles) . " files in avatars directory");

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

        $this->line("  Found " . count($userAvatars) . " user avatars and " . count($workerAvatars) . " worker avatars in database");

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
            $this->line('  No orphaned avatar files found.');
            $this->line('');
            return;
        }

        $this->components->warn("  Found " . count($orphanedFiles) . " orphaned files (" . $this->formatBytes($totalSize) . ")");

        if ($this->output->isVerbose() || $dryRun) {
            $this->line('');
            $this->line('  Orphaned files:');
            foreach (array_slice($orphanedFiles, 0, 10) as $file) {
                $size = Storage::disk('public')->size($file);
                $this->line("    - {$file} (" . $this->formatBytes($size) . ")");
            }
            if (count($orphanedFiles) > 10) {
                $this->line("    ... and " . (count($orphanedFiles) - 10) . " more");
            }
        }

        if (!$dryRun) {
            $deletedCount = 0;
            foreach ($orphanedFiles as $file) {
                if (Storage::disk('public')->delete($file)) {
                    $deletedCount++;
                }
            }

            $this->totalFilesDeleted += $deletedCount;
            $this->totalBytesFreed += $totalSize;

            $this->line("  Deleted {$deletedCount} orphaned avatar files");
        }

        $this->line('');
    }

    private function cleanupListingImages(bool $dryRun): void
    {
        $this->components->info('Cleaning up Listing Images');
        $this->line('');

        $allFiles = Storage::disk('public')->files('listings');

        if (empty($allFiles)) {
            $this->line('  No listing image files found.');
            $this->line('');
            return;
        }

        $this->line("  Found " . count($allFiles) . " files in listings directory");

        // Get all referenced listing image paths
        $referencedPaths = ListingImage::pluck('path')->toArray();

        $referencedFiles = [];
        foreach ($referencedPaths as $path) {
            $referencedFiles[$path] = true;
        }

        $this->line("  Found " . count($referencedPaths) . " listing images in database");

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
            $this->line('  No orphaned listing image files found.');
            $this->line('');
            return;
        }

        $this->components->warn("  Found " . count($orphanedFiles) . " orphaned files (" . $this->formatBytes($totalSize) . ")");

        if ($this->output->isVerbose() || $dryRun) {
            $this->line('');
            $this->line('  Orphaned files:');
            foreach (array_slice($orphanedFiles, 0, 10) as $file) {
                $size = Storage::disk('public')->size($file);
                $this->line("    - {$file} (" . $this->formatBytes($size) . ")");
            }
            if (count($orphanedFiles) > 10) {
                $this->line("    ... and " . (count($orphanedFiles) - 10) . " more");
            }
        }

        if (!$dryRun) {
            $deletedCount = 0;
            foreach ($orphanedFiles as $file) {
                if (Storage::disk('public')->delete($file)) {
                    $deletedCount++;
                }
            }

            $this->totalFilesDeleted += $deletedCount;
            $this->totalBytesFreed += $totalSize;

            $this->line("  Deleted {$deletedCount} orphaned listing image files");
        }

        $this->line('');
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

    private function printSummary(bool $dryRun): void
    {
        if ($dryRun) {
            $this->components->info('Run without --dry-run to actually delete files.');
        } else if ($this->totalFilesDeleted > 0) {
            $this->components->info("Total: Deleted {$this->totalFilesDeleted} files, freed " . $this->formatBytes($this->totalBytesFreed));
        } else {
            $this->components->info('No orphaned files found. All images are in use.');
        }
    }
}
