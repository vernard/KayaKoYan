<?php

namespace Database\Seeders;

use App\Enums\ListingType;
use App\Models\Listing;
use App\Models\ListingImage;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ListingSeeder extends Seeder
{
    public function run(): void
    {
        // Ensure storage directory exists
        Storage::disk('public')->makeDirectory('listing-images');

        $listings = [
            'maria.santos@example.com' => [
                [
                    'title' => 'Virtual Assistant - 10 Hours',
                    'description' => "Get 10 hours of professional virtual assistance! I'll help you with:\n\n- Email management and organization\n- Calendar scheduling and reminders\n- Data entry and spreadsheet management\n- Research and report compilation\n- Travel booking and arrangements\n\nFast turnaround and excellent communication guaranteed.",
                    'price' => 2500.00,
                    'type' => ListingType::Service,
                    'image_url' => 'https://images.unsplash.com/photo-1551434678-e076c223a692?w=800&h=600&fit=crop',
                ],
                [
                    'title' => 'Admin Support Package',
                    'description' => "Complete admin support package for busy entrepreneurs.\n\nIncludes:\n- Document formatting and organization\n- Basic bookkeeping assistance\n- Client follow-up management\n- Meeting minutes and summaries\n\nPerfect for small business owners who need extra hands.",
                    'price' => 3500.00,
                    'type' => ListingType::Service,
                    'image_url' => 'https://images.unsplash.com/photo-1450101499163-c8848c66ca85?w=800&h=600&fit=crop',
                ],
            ],
            'juan.delacruz@example.com' => [
                [
                    'title' => 'Professional Logo Design',
                    'description' => "Stand out with a unique, professional logo for your brand!\n\nWhat you get:\n- 3 initial concept designs\n- 2 rounds of revisions\n- Final files in PNG, JPG, and vector formats\n- Color and black/white versions\n- Brand color palette guide\n\nDelivery within 3-5 business days.",
                    'price' => 3000.00,
                    'type' => ListingType::Service,
                    'image_url' => 'https://images.unsplash.com/photo-1626785774625-ddcddc3445e9?w=800&h=600&fit=crop',
                ],
                [
                    'title' => 'Canva Social Media Templates Bundle',
                    'description' => "50 professionally designed Canva templates for your social media!\n\nIncludes:\n- 20 Instagram post templates\n- 15 Instagram story templates\n- 10 Facebook post templates\n- 5 LinkedIn post templates\n\nFully editable in free Canva. Just add your brand colors and content!",
                    'price' => 1500.00,
                    'type' => ListingType::DigitalProduct,
                    'image_url' => 'https://images.unsplash.com/photo-1611162617474-5b21e879e113?w=800&h=600&fit=crop',
                ],
            ],
            'anna.reyes@example.com' => [
                [
                    'title' => 'YouTube Video Editing',
                    'description' => "Professional video editing for your YouTube channel!\n\nServices include:\n- Color correction and grading\n- Audio enhancement and sync\n- Transitions and effects\n- Lower thirds and text overlays\n- Intro/outro integration\n- Thumbnail creation\n\nUp to 15 minutes of finished video. Delivery in 3-5 days.",
                    'price' => 2000.00,
                    'type' => ListingType::Service,
                    'image_url' => 'https://images.unsplash.com/photo-1574717024653-61fd2cf4d44d?w=800&h=600&fit=crop',
                ],
                [
                    'title' => 'Premiere Pro Presets Pack',
                    'description' => "Level up your video editing with this preset pack!\n\nIncludes:\n- 20 Color grading presets\n- 10 Transition presets\n- 15 Text animation presets\n- 5 Sound effect presets\n\nCompatible with Adobe Premiere Pro CC 2020 and later.",
                    'price' => 800.00,
                    'type' => ListingType::DigitalProduct,
                    'image_url' => 'https://images.unsplash.com/photo-1536240478700-b869070f9279?w=800&h=600&fit=crop',
                ],
            ],
            'carlos.garcia@example.com' => [
                [
                    'title' => 'Social Media Management - 1 Month',
                    'description' => "Let me handle your social media for a whole month!\n\nPackage includes:\n- 30 posts (Instagram, Facebook, or both)\n- Content calendar planning\n- Hashtag research\n- Basic engagement management\n- Monthly analytics report\n\nContent creation and graphics included!",
                    'price' => 8000.00,
                    'type' => ListingType::Service,
                    'image_url' => 'https://images.unsplash.com/photo-1611162616305-c69b3fa7fbe0?w=800&h=600&fit=crop',
                ],
                [
                    'title' => 'Instagram Growth Guide E-book',
                    'description' => "Learn how to grow your Instagram organically!\n\nThis 50-page guide covers:\n- Optimizing your profile for discovery\n- Content strategy that works\n- Hashtag strategies for maximum reach\n- Engagement techniques\n- Reels and Stories best practices\n- Analytics and tracking growth\n\nIncludes bonus content calendar template!",
                    'price' => 500.00,
                    'type' => ListingType::DigitalProduct,
                    'image_url' => 'https://images.unsplash.com/photo-1432888622747-4eb9a8efeb07?w=800&h=600&fit=crop',
                ],
            ],
            'lisa.mendoza@example.com' => [
                [
                    'title' => 'Blog Article Writing - 1000 Words',
                    'description' => "Get a well-researched, SEO-optimized blog article!\n\nWhat's included:\n- 1000+ words of quality content\n- Keyword research and optimization\n- Proper formatting with headers\n- Meta description\n- 1 round of revisions\n\nPerfect for businesses looking to boost their online presence.",
                    'price' => 1200.00,
                    'type' => ListingType::Service,
                    'image_url' => 'https://images.unsplash.com/photo-1455390582262-044cdead277a?w=800&h=600&fit=crop',
                ],
                [
                    'title' => 'SEO E-book for Beginners',
                    'description' => "Master the basics of SEO with this comprehensive guide!\n\nLearn about:\n- How search engines work\n- Keyword research fundamentals\n- On-page SEO techniques\n- Content optimization\n- Link building basics\n- Measuring SEO success\n\n45 pages of actionable tips and strategies.",
                    'price' => 400.00,
                    'type' => ListingType::DigitalProduct,
                    'image_url' => 'https://images.unsplash.com/photo-1460925895917-afdab827c52f?w=800&h=600&fit=crop',
                ],
            ],
        ];

        foreach ($listings as $email => $workerListings) {
            $user = User::where('email', $email)->first();
            if ($user) {
                foreach ($workerListings as $listingData) {
                    $listing = Listing::create([
                        'user_id' => $user->id,
                        'title' => $listingData['title'],
                        'slug' => Str::slug($listingData['title']) . '-' . Str::random(5),
                        'description' => $listingData['description'],
                        'price' => $listingData['price'],
                        'type' => $listingData['type'],
                        'is_active' => true,
                    ]);

                    // Download and save listing image
                    try {
                        $response = Http::timeout(15)->get($listingData['image_url']);
                        if ($response->successful()) {
                            $filename = 'listing-images/' . $listing->id . '-' . Str::random(8) . '.jpg';
                            Storage::disk('public')->put($filename, $response->body());

                            ListingImage::create([
                                'listing_id' => $listing->id,
                                'path' => $filename,
                                'order' => 0,
                            ]);
                        }
                    } catch (\Exception $e) {
                        // Silently fail, listing will show placeholder
                    }
                }
            }
        }
    }
}
