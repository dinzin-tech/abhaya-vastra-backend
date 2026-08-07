<?php

namespace App\Helpers;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class ImageHelper
{
    /**
     * Convert uploaded image file to WebP format and store in specified folder.
     *
     * @param UploadedFile $file
     * @param string $folder e.g. 'products', 'banners', 'product-colors', 'gallerys', 'lookbooks'
     * @param string $disk default 'public'
     * @param int $quality default 82
     * @return string relative path to stored webp file
     */
    public static function convertAndStoreToWebp(UploadedFile $file, string $folder = 'products', string $disk = 'public', int $quality = 82): string
    {
        try {
            $filename = Str::random(40) . '.webp';
            $destinationPath = storage_path('app/public/' . trim($folder, '/'));

            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0755, true);
            }

            $targetFile = $destinationPath . '/' . $filename;
            $mimeType = $file->getMimeType();
            $realPath = $file->getRealPath();

            $image = null;

            if (function_exists('imagecreatefromstring')) {
                $contents = @file_get_contents($realPath);
                if ($contents !== false) {
                    $image = @imagecreatefromstring($contents);
                }
            }

            if (!$image) {
                switch ($mimeType) {
                    case 'image/jpeg':
                    case 'image/jpg':
                        $image = @imagecreatefromjpeg($realPath);
                        break;
                    case 'image/png':
                        $image = @imagecreatefrompng($realPath);
                        break;
                    case 'image/webp':
                        $image = @imagecreatefromwebp($realPath);
                        break;
                    case 'image/gif':
                        $image = @imagecreatefromgif($realPath);
                        break;
                }
            }

            if ($image && function_exists('imagewebp')) {
                imagepalettetotruecolor($image);
                imagealphablending($image, true);
                imagesavealpha($image, true);

                imagewebp($image, $targetFile, $quality);
                imagedestroy($image);

                return trim($folder, '/') . '/' . $filename;
            }
        } catch (\Throwable $e) {
            Log::warning("WebP conversion fallback triggered: " . $e->getMessage());
        }

        // Fallback: store directly if WebP conversion fails
        return $file->store($folder, $disk);
    }
}
