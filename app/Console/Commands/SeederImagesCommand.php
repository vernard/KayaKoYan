<?php

namespace App\Console\Commands;

use App\Enums\ListingType;
use App\Enums\UserRole;
use App\Models\Listing;
use App\Models\ListingImage;
use App\Models\User;
use App\Models\WorkerProfile;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Laravel\Facades\Image;

class SeederImagesCommand extends Command
{
    protected $signature = 'seeders:images
                            {--force : Overwrite existing images}
                            {--avatars-only : Only download avatars}
                            {--listings-only : Only download listing images}';

    protected $description = 'Download placeholder avatars for users and relevant images for listings';

    private array $stats = [
        'avatars' => ['downloaded' => 0, 'skipped' => 0, 'failed' => 0],
        'listings' => ['downloaded' => 0, 'skipped' => 0, 'failed' => 0],
    ];

    public function handle(): int
    {
        $force = $this->option('force');
        $avatarsOnly = $this->option('avatars-only');
        $listingsOnly = $this->option('listings-only');

        // Ensure directories exist
        Storage::disk('public')->makeDirectory('avatars');
        Storage::disk('public')->makeDirectory('listings');

        if (!$listingsOnly) {
            $this->downloadAvatars($force);
        }

        if (!$avatarsOnly) {
            $this->downloadListingImages($force);
        }

        $this->printSummary($avatarsOnly, $listingsOnly);

        return Command::SUCCESS;
    }

    private function downloadAvatars(bool $force): void
    {
        $this->info('');
        $this->components->info('Downloading Avatars');
        $this->line('');

        // Process customers
        $customers = User::where('role', UserRole::Customer)->get();
        $this->info("Processing {$customers->count()} customers...");

        $customerBar = $this->output->createProgressBar($customers->count());
        $customerBar->start();

        foreach ($customers as $customer) {
            $result = $this->processUserAvatar($customer, $force);
            $this->stats['avatars'][$result]++;
            $customerBar->advance();
        }

        $customerBar->finish();
        $this->newLine();

        // Process workers
        $workerProfiles = WorkerProfile::with('user')->get();
        $this->info("Processing {$workerProfiles->count()} workers...");

        $workerBar = $this->output->createProgressBar($workerProfiles->count());
        $workerBar->start();

        foreach ($workerProfiles as $profile) {
            $result = $this->processWorkerAvatar($profile, $force);
            $this->stats['avatars'][$result]++;
            $workerBar->advance();
        }

        $workerBar->finish();
        $this->newLine();
    }

    private function processUserAvatar(User $user, bool $force): string
    {
        if ($user->avatar_path && !$force) {
            return 'skipped';
        }

        $oldPath = $force ? $user->avatar_path : null;
        $gender = $this->guessGender($user->name);
        $avatarPath = $this->downloadAvatar($gender, $user->id, $oldPath);

        if (!$avatarPath) {
            return 'failed';
        }

        $user->update(['avatar_path' => $avatarPath]);
        return 'downloaded';
    }

    private function processWorkerAvatar(WorkerProfile $profile, bool $force): string
    {
        if ($profile->avatar_path && !$force) {
            return 'skipped';
        }

        $oldPath = $force ? $profile->avatar_path : null;
        $gender = $profile->gender ?? $this->guessGender($profile->user->name);
        $avatarPath = $this->downloadAvatar($gender, $profile->id, $oldPath);

        if (!$avatarPath) {
            return 'failed';
        }

        $profile->update(['avatar_path' => $avatarPath]);
        return 'downloaded';
    }

    private function downloadAvatar(string $gender, int $seed, ?string $oldPath): ?string
    {
        if ($oldPath) {
            $this->deleteAvatarFiles($oldPath);
        }

        $portraitId = ($seed % 99) + 1;
        $genderPath = $gender === 'female' ? 'women' : 'men';
        $url = "https://randomuser.me/api/portraits/{$genderPath}/{$portraitId}.jpg";

        try {
            $response = Http::timeout(15)->get($url);

            if (!$response->successful()) {
                return null;
            }

            $uuid = Str::uuid()->toString();
            $mainPath = "avatars/{$uuid}.jpg";
            $smallPath = "avatars/{$uuid}_200.jpg";

            $image = Image::read($response->body());

            $main = $image->cover(600, 600);
            Storage::disk('public')->put($mainPath, $main->toJpeg(85));

            $small = $image->cover(200, 200);
            Storage::disk('public')->put($smallPath, $small->toJpeg(85));

            return $mainPath;
        } catch (\Exception $e) {
            return null;
        }
    }

    private function deleteAvatarFiles(string $mainPath): void
    {
        Storage::disk('public')->delete($mainPath);

        $pathInfo = pathinfo($mainPath);
        $smallPath = "{$pathInfo['dirname']}/{$pathInfo['filename']}_200.{$pathInfo['extension']}";
        Storage::disk('public')->delete($smallPath);
    }

    private function guessGender(string $name): string
    {
        $firstName = strtolower(explode(' ', $name)[0]);

        $femaleNames = ['maria', 'anna', 'lisa', 'sofia', 'grace', 'rose', 'joy', 'mary', 'jane', 'ana'];
        $maleNames = ['juan', 'carlos', 'miguel', 'david', 'mark', 'john', 'jose', 'antonio', 'luis', 'pedro'];

        if (in_array($firstName, $femaleNames)) {
            return 'female';
        }

        if (in_array($firstName, $maleNames)) {
            return 'male';
        }

        return crc32($name) % 2 === 0 ? 'male' : 'female';
    }

    private function downloadListingImages(bool $force): void
    {
        $this->info('');
        $this->components->info('Downloading Listing Images');
        $this->line('');

        $listings = Listing::with('images')->get();
        $this->info("Processing {$listings->count()} listings...");

        $bar = $this->output->createProgressBar($listings->count());
        $bar->start();

        foreach ($listings as $listing) {
            $result = $this->processListingImages($listing, $force);
            $this->stats['listings'][$result]++;
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
    }

    private function processListingImages(Listing $listing, bool $force): string
    {
        // Skip if already has images and not forcing
        if ($listing->images->isNotEmpty() && !$force) {
            return 'skipped';
        }

        // Delete existing images if forcing
        if ($force && $listing->images->isNotEmpty()) {
            foreach ($listing->images as $image) {
                Storage::disk('public')->delete($image->path);
            }
            $listing->images()->delete();
        }

        // Extract search terms from listing title
        $searchQuery = $this->extractSearchTerms($listing);

        // Download 1-3 images based on listing type
        $imageCount = $listing->type === ListingType::DigitalProduct ? 1 : rand(1, 3);
        $downloadedPaths = [];

        for ($i = 0; $i < $imageCount; $i++) {
            $path = $this->downloadListingImage($searchQuery, $listing->id, $i);
            if ($path) {
                $downloadedPaths[] = $path;
            }
        }

        if (empty($downloadedPaths)) {
            return 'failed';
        }

        // Create ListingImage records
        foreach ($downloadedPaths as $order => $path) {
            ListingImage::create([
                'listing_id' => $listing->id,
                'path' => $path,
                'order' => $order,
            ]);
        }

        return 'downloaded';
    }

    private function extractSearchTerms(Listing $listing): string
    {
        // Common keywords to search for based on listing type and title
        $title = strtolower($listing->title);

        // Service-related keyword mappings
        $keywordMap = [
            // Tech services
            'web' => 'website design',
            'website' => 'website design',
            'app' => 'mobile app',
            'software' => 'software development',
            'programming' => 'coding',
            'developer' => 'programming',
            'code' => 'programming',
            'graphic' => 'graphic design',
            'logo' => 'logo design',
            'design' => 'design creative',
            'ui' => 'user interface',
            'ux' => 'user experience',

            // Writing/Content
            'writing' => 'writing',
            'content' => 'content writing',
            'copywriting' => 'copywriting',
            'article' => 'writing article',
            'blog' => 'blogging',
            'translation' => 'translation',
            'editing' => 'editing document',

            // Video/Photo
            'video' => 'video production',
            'photo' => 'photography',
            'photography' => 'camera photography',
            'editing' => 'video editing',
            'animation' => 'animation',

            // Marketing
            'marketing' => 'digital marketing',
            'seo' => 'seo marketing',
            'social media' => 'social media',
            'advertising' => 'advertising',
            'ads' => 'advertising',

            // Business
            'consulting' => 'business consulting',
            'business' => 'business office',
            'finance' => 'finance',
            'accounting' => 'accounting',
            'legal' => 'legal document',

            // Creative
            'art' => 'digital art',
            'illustration' => 'illustration',
            'music' => 'music production',
            'audio' => 'audio sound',
            'voice' => 'voiceover microphone',

            // Digital products
            'template' => 'template design',
            'ebook' => 'ebook digital',
            'course' => 'online course',
            'tutorial' => 'tutorial learning',
            'preset' => 'photo preset',
            'font' => 'typography font',
            'icon' => 'icon design',
            'mockup' => 'mockup design',

            // Lifestyle/Services
            'tutor' => 'tutoring education',
            'teaching' => 'teaching',
            'fitness' => 'fitness workout',
            'health' => 'health wellness',
            'cooking' => 'cooking food',
            'cleaning' => 'cleaning service',
            'repair' => 'repair service',
            'handyman' => 'handyman tools',
        ];

        // Find matching keywords
        foreach ($keywordMap as $keyword => $searchTerm) {
            if (str_contains($title, $keyword)) {
                return $searchTerm;
            }
        }

        // Fallback: use listing type
        if ($listing->type === ListingType::DigitalProduct) {
            return 'digital product download';
        }

        return 'freelance service work';
    }

    private function downloadListingImage(string $searchQuery, int $listingId, int $index): ?string
    {
        // Use Unsplash Source API with search term
        // Add variation using listing ID and index for different images
        $seed = $listingId * 10 + $index;
        $encodedQuery = urlencode($searchQuery);

        // Try Unsplash Source API first
        $url = "https://source.unsplash.com/800x600/?{$encodedQuery}&sig={$seed}";

        try {
            // Unsplash redirects to actual image
            $response = Http::timeout(20)
                ->withOptions(['allow_redirects' => true])
                ->get($url);

            if (!$response->successful()) {
                // Fallback to Lorem Picsum
                return $this->downloadFromPicsum($seed);
            }

            $uuid = Str::uuid()->toString();
            $path = "listings/{$uuid}.jpg";

            $image = Image::read($response->body());
            $processed = $image->cover(800, 600);
            Storage::disk('public')->put($path, $processed->toJpeg(85));

            return $path;
        } catch (\Exception $e) {
            // Fallback to Lorem Picsum
            return $this->downloadFromPicsum($seed);
        }
    }

    private function downloadFromPicsum(int $seed): ?string
    {
        $url = "https://picsum.photos/seed/{$seed}/800/600";

        try {
            $response = Http::timeout(15)
                ->withOptions(['allow_redirects' => true])
                ->get($url);

            if (!$response->successful()) {
                return null;
            }

            $uuid = Str::uuid()->toString();
            $path = "listings/{$uuid}.jpg";

            $image = Image::read($response->body());
            $processed = $image->cover(800, 600);
            Storage::disk('public')->put($path, $processed->toJpeg(85));

            return $path;
        } catch (\Exception $e) {
            return null;
        }
    }

    private function printSummary(bool $avatarsOnly, bool $listingsOnly): void
    {
        $this->newLine();
        $this->components->info('Summary');
        $this->line('');

        if (!$listingsOnly) {
            $this->line('Avatars:');
            $this->line("  Downloaded: {$this->stats['avatars']['downloaded']}");
            $this->line("  Skipped:    {$this->stats['avatars']['skipped']}");
            if ($this->stats['avatars']['failed'] > 0) {
                $this->components->warn("  Failed:     {$this->stats['avatars']['failed']}");
            }
            $this->line('');
        }

        if (!$avatarsOnly) {
            $this->line('Listing Images:');
            $this->line("  Downloaded: {$this->stats['listings']['downloaded']}");
            $this->line("  Skipped:    {$this->stats['listings']['skipped']}");
            if ($this->stats['listings']['failed'] > 0) {
                $this->components->warn("  Failed:     {$this->stats['listings']['failed']}");
            }
        }
    }
}
