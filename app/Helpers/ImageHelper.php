<?php

namespace App\Helpers;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class ImageHelper
{
    /**
     * Convert uploaded image file (JPEG, PNG, HEIC, HEIF, WEBP, GIF, BMP, AVIF) 
     * to WebP format and store in specified folder.
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
            $extension = strtolower($file->getClientOriginalExtension());
            $mimeType  = strtolower($file->getMimeType() ?: '');
            $realPath  = $file->getRealPath();

            $isHeic = in_array($extension, ['heic', 'heif']) || 
                      str_contains($mimeType, 'heic') || 
                      str_contains($mimeType, 'heif');

            $filename = Str::random(40) . '.webp';
            $destinationPath = storage_path('app/public/' . trim($folder, '/'));

            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0755, true);
            }

            $targetFile = $destinationPath . '/' . $filename;

            // ── Method 1: Imagick (Supports HEIC, PNG, JPEG, WEBP, AVIF out-of-the-box) ──
            if (class_exists('\Imagick')) {
                try {
                    $imagick = new \Imagick($realPath);
                    $imagick->setImageFormat('webp');
                    $imagick->setImageCompressionQuality($quality);

                    // Preserve transparency for PNG/WEBP
                    $imagick->setImageAlphaChannel(\Imagick::ALPHACHANNEL_SET);

                    $imagick->writeImage($targetFile);
                    $imagick->clear();
                    $imagick->destroy();

                    if (file_exists($targetFile) && filesize($targetFile) > 0) {
                        return trim($folder, '/') . '/' . $filename;
                    }
                } catch (\Throwable $imErr) {
                    Log::warning("Imagick conversion failed for {$extension}: " . $imErr->getMessage());
                }
            }

            // ── Method 2: HEIC Convert via CLI tool if file is HEIC ──
            $sourceForGd = $realPath;
            $tempJpgCreated = false;

            if ($isHeic && function_exists('exec')) {
                $tempJpg = sys_get_temp_dir() . '/' . Str::random(20) . '.jpg';
                @exec("heif-convert " . escapeshellarg($realPath) . " " . escapeshellarg($tempJpg) . " 2>&1", $out, $ret);
                if ($ret === 0 && file_exists($tempJpg)) {
                    $sourceForGd = $tempJpg;
                    $tempJpgCreated = true;
                }
            }

            // ── Method 3: GD Extension ──
            $image = null;

            if (function_exists('imagecreatefromstring')) {
                $contents = @file_get_contents($sourceForGd);
                if ($contents !== false) {
                    $image = @imagecreatefromstring($contents);
                }
            }

            if (!$image) {
                switch ($mimeType) {
                    case 'image/jpeg':
                    case 'image/jpg':
                        if (function_exists('imagecreatefromjpeg')) {
                            $image = @imagecreatefromjpeg($sourceForGd);
                        }
                        break;
                    case 'image/png':
                        if (function_exists('imagecreatefrompng')) {
                            $image = @imagecreatefrompng($sourceForGd);
                        }
                        break;
                    case 'image/webp':
                        if (function_exists('imagecreatefromwebp')) {
                            $image = @imagecreatefromwebp($sourceForGd);
                        }
                        break;
                    case 'image/gif':
                        if (function_exists('imagecreatefromgif')) {
                            $image = @imagecreatefromgif($sourceForGd);
                        }
                        break;
                    case 'image/bmp':
                        if (function_exists('imagecreatefrombmp')) {
                            $image = @imagecreatefrombmp($sourceForGd);
                        }
                        break;
                }
            }

            // Clean up temporary JPG if created
            if ($tempJpgCreated && file_exists($sourceForGd)) {
                @unlink($sourceForGd);
            }

            if ($image && function_exists('imagewebp')) {
                @imagepalettetotruecolor($image);
                @imagealphablending($image, true);
                @imagesavealpha($image, true);

                imagewebp($image, $targetFile, $quality);
                imagedestroy($image);

                if (file_exists($targetFile) && filesize($targetFile) > 0) {
                    return trim($folder, '/') . '/' . $filename;
                }
            }
        } catch (\Throwable $e) {
            Log::warning("WebP conversion fallback triggered for {$file->getClientOriginalName()}: " . $e->getMessage());
        }

        // ── Fallback: Store original file directly if conversion is unsupported ──
        return $file->store($folder, $disk);
    }
}
