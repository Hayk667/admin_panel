<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ImageService
{
    /**
     * Upload and crop image to thumbnail
     */
    public static function uploadAndCrop(UploadedFile $file, string $path = 'posts', int $thumbnailWidth = 100, int $thumbnailHeight = 100): array
    {
        // Generate unique filename
        $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
        $thumbnailFilename = 'thumb_' . $filename;
        
        // Full path for original and thumbnail
        $originalPath = $path . '/' . $filename;
        $thumbnailPath = $path . '/' . $thumbnailFilename;
        
        // Store original image
        $file->storeAs('public/' . $path, $filename);
        
        // Create thumbnail
        $sourcePath = storage_path('app/public/' . $originalPath);
        $thumbnailFullPath = storage_path('app/public/' . $thumbnailPath);
        
        self::createThumbnail($sourcePath, $thumbnailFullPath, $thumbnailWidth, $thumbnailHeight);
        
        return [
            'image' => $originalPath,
            'thumbnail' => $thumbnailPath,
        ];
    }

    /**
     * Create thumbnail from source image
     */
    protected static function createThumbnail(string $sourcePath, string $destinationPath, int $width, int $height): void
    {
        // Get image info
        $imageInfo = getimagesize($sourcePath);
        if (!$imageInfo) {
            return;
        }

        $sourceWidth = $imageInfo[0];
        $sourceHeight = $imageInfo[1];
        $mimeType = $imageInfo['mime'];

        // Create source image resource
        switch ($mimeType) {
            case 'image/jpeg':
                $sourceImage = imagecreatefromjpeg($sourcePath);
                break;
            case 'image/png':
                $sourceImage = imagecreatefrompng($sourcePath);
                break;
            case 'image/gif':
                $sourceImage = imagecreatefromgif($sourcePath);
                break;
            default:
                return;
        }

        if (!$sourceImage) {
            return;
        }

        // Calculate aspect ratio and crop dimensions
        $sourceAspect = $sourceWidth / $sourceHeight;
        $thumbAspect = $width / $height;

        if ($sourceAspect > $thumbAspect) {
            // Source is wider
            $newHeight = $sourceHeight;
            $newWidth = $sourceHeight * $thumbAspect;
            $x = ($sourceWidth - $newWidth) / 2;
            $y = 0;
        } else {
            // Source is taller
            $newWidth = $sourceWidth;
            $newHeight = $sourceWidth / $thumbAspect;
            $x = 0;
            $y = ($sourceHeight - $newHeight) / 2;
        }

        // Create thumbnail
        $thumbnail = imagecreatetruecolor($width, $height);
        
        // Preserve transparency for PNG
        if ($mimeType === 'image/png') {
            imagealphablending($thumbnail, false);
            imagesavealpha($thumbnail, true);
            $transparent = imagecolorallocatealpha($thumbnail, 255, 255, 255, 127);
            imagefill($thumbnail, 0, 0, $transparent);
        }

        // Resize and crop
        imagecopyresampled(
            $thumbnail,
            $sourceImage,
            0, 0, $x, $y,
            $width, $height,
            $newWidth, $newHeight
        );

        // Save thumbnail
        $directory = dirname($destinationPath);
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        switch ($mimeType) {
            case 'image/jpeg':
                imagejpeg($thumbnail, $destinationPath, 90);
                break;
            case 'image/png':
                imagepng($thumbnail, $destinationPath, 9);
                break;
            case 'image/gif':
                imagegif($thumbnail, $destinationPath);
                break;
        }

        imagedestroy($sourceImage);
        imagedestroy($thumbnail);
    }

    /**
     * Delete image and thumbnail
     */
    public static function delete(string $imagePath, string $thumbnailPath = null): void
    {
        if ($imagePath && Storage::disk('public')->exists($imagePath)) {
            Storage::disk('public')->delete($imagePath);
        }
        
        if ($thumbnailPath && Storage::disk('public')->exists($thumbnailPath)) {
            Storage::disk('public')->delete($thumbnailPath);
        }
    }
}

