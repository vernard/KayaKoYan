<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Laravel\Facades\Image;

class ImageService
{
    /**
     * Process and resize an avatar image.
     * Creates 600x600 and 200x200 JPEG versions, deletes original.
     *
     * @param UploadedFile $file The uploaded image file
     * @param string|null $oldPath Previous avatar path to delete (optional)
     * @return string The path to the main (600px) avatar
     */
    public function processAvatar(UploadedFile $file, ?string $oldPath = null): string
    {
        // Delete old avatar files if they exist
        if ($oldPath) {
            $this->deleteAvatarFiles($oldPath);
        }

        // Generate unique filename
        $filename = Str::uuid()->toString();
        $mainPath = "avatars/{$filename}.jpg";
        $smallPath = "avatars/{$filename}_200.jpg";

        // Read and process the image
        $image = Image::read($file->getPathname());

        // Create 600x600 version (main)
        $main = $image->cover(600, 600);
        Storage::disk('public')->put($mainPath, $main->toJpeg(85));

        // Create 200x200 version (small/thumbnail)
        $small = $image->cover(200, 200);
        Storage::disk('public')->put($smallPath, $small->toJpeg(85));

        return $mainPath;
    }

    /**
     * Process an avatar from a file path (for Filament uploads).
     * Creates 600x600 and 200x200 JPEG versions, deletes original.
     *
     * @param string $tempPath The temporary file path from Filament upload
     * @param string|null $oldPath Previous avatar path to delete (optional)
     * @return string The path to the main (600px) avatar
     */
    public function processAvatarFromPath(string $tempPath, ?string $oldPath = null): string
    {
        // Delete old avatar files if they exist
        if ($oldPath) {
            $this->deleteAvatarFiles($oldPath);
        }

        // Generate unique filename
        $filename = Str::uuid()->toString();
        $mainPath = "avatars/{$filename}.jpg";
        $smallPath = "avatars/{$filename}_200.jpg";

        // Get the full path to the temp file
        $fullTempPath = Storage::disk('public')->path($tempPath);

        // Read and process the image
        $image = Image::read($fullTempPath);

        // Create 600x600 version (main)
        $main = $image->cover(600, 600);
        Storage::disk('public')->put($mainPath, $main->toJpeg(85));

        // Create 200x200 version (small/thumbnail)
        $small = $image->cover(200, 200);
        Storage::disk('public')->put($smallPath, $small->toJpeg(85));

        // Delete the temporary uploaded file
        Storage::disk('public')->delete($tempPath);

        return $mainPath;
    }

    /**
     * Delete avatar files (both main and small versions).
     *
     * @param string $mainPath The main avatar path
     */
    public function deleteAvatarFiles(string $mainPath): void
    {
        // Delete main file
        Storage::disk('public')->delete($mainPath);

        // Delete small version (derive path from main)
        $smallPath = $this->getSmallPath($mainPath);
        Storage::disk('public')->delete($smallPath);
    }

    /**
     * Get the small (200px) version path from the main path.
     *
     * @param string $mainPath The main avatar path
     * @return string The small avatar path
     */
    public function getSmallPath(string $mainPath): string
    {
        // Convert "avatars/uuid.jpg" to "avatars/uuid_200.jpg"
        $pathInfo = pathinfo($mainPath);
        $dir = $pathInfo['dirname'];
        $name = $pathInfo['filename'];
        $ext = $pathInfo['extension'] ?? 'jpg';

        return "{$dir}/{$name}_200.{$ext}";
    }
}
