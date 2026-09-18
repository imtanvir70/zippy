<?php

namespace App\Services\Media;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\Encoders\WebpEncoder;

class ImageOptimizerService
{
    protected ImageManager $manager;

    public function __construct()
    {
        $this->manager = new ImageManager(new Driver());
    }

    protected function isGif(UploadedFile|string $file): bool
    {
        if ($file instanceof UploadedFile) {
            $ext = strtolower($file->getClientOriginalExtension());
            $mime = strtolower((string)$file->getMimeType());
            return $ext === 'gif' || $mime === 'image/gif';
        }

        if (is_string($file) && file_exists($file)) {
            $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
            $mime = function_exists('mime_content_type') ? strtolower((string)mime_content_type($file)) : '';
            return $ext === 'gif' || $mime === 'image/gif';
        }

        return false;
    }

    public function convertToWebp(UploadedFile|string $file, string $folder = 'uploads', int $maxWidth = 1200, int $quality = 85): string
    {
        $targetDir = storage_path('app/public/' . trim($folder, '/'));
        if (!File::exists($targetDir)) {
            File::makeDirectory($targetDir, 0755, true, true);
        }

        $publicDir = public_path('storage/' . trim($folder, '/'));
        if (!File::exists($publicDir)) {
            File::makeDirectory($publicDir, 0755, true, true);
        }

        if ($this->isGif($file)) {
            $baseName = Str::slug(pathinfo($file instanceof UploadedFile ? $file->getClientOriginalName() : $file, PATHINFO_FILENAME));
            $filename = ($baseName ?: 'media') . '_' . time() . '_' . Str::random(6) . '.gif';
            $fullPath = $targetDir . DIRECTORY_SEPARATOR . $filename;
            $publicFullPath = $publicDir . DIRECTORY_SEPARATOR . $filename;

            $sourcePath = $file instanceof UploadedFile ? $file->getRealPath() : $file;
            File::copy($sourcePath, $fullPath);
            if ($fullPath !== $publicFullPath) {
                @copy($fullPath, $publicFullPath);
            }

            return '/storage/' . trim($folder, '/') . '/' . $filename;
        }

        $filename = Str::slug(pathinfo($file instanceof UploadedFile ? $file->getClientOriginalName() : 'image', PATHINFO_FILENAME));
        $filename = ($filename ?: 'media') . '_' . time() . '_' . Str::random(6) . '.webp';
        $fullPath = $targetDir . DIRECTORY_SEPARATOR . $filename;
        $publicFullPath = $publicDir . DIRECTORY_SEPARATOR . $filename;

        if ($file instanceof UploadedFile) {
            $image = $this->manager->decodePath($file->getRealPath());
        } elseif (is_string($file) && file_exists($file)) {
            $image = $this->manager->decodePath($file);
        } else {
            $image = $this->manager->decode($file);
        }

        $image->orient();

        if ($image->width() > $maxWidth) {
            $image->resizeDown(width: $maxWidth);
        }

        $encoded = $image->encode(new WebpEncoder(quality: $quality));
        $encoded->save($fullPath);
        if ($fullPath !== $publicFullPath) {
            @copy($fullPath, $publicFullPath);
        }

        return '/storage/' . trim($folder, '/') . '/' . $filename;
    }

    public function convertProductImageToWebp(UploadedFile|string $file, string $folder = 'products', int $dimension = 800, int $quality = 85, string $bg = 'ffffff'): string
    {
        $targetDir = storage_path('app/public/' . trim($folder, '/'));
        if (!File::exists($targetDir)) {
            File::makeDirectory($targetDir, 0755, true, true);
        }

        $publicDir = public_path('storage/' . trim($folder, '/'));
        if (!File::exists($publicDir)) {
            File::makeDirectory($publicDir, 0755, true, true);
        }

        if ($this->isGif($file)) {
            $baseName = Str::slug(pathinfo($file instanceof UploadedFile ? $file->getClientOriginalName() : $file, PATHINFO_FILENAME));
            $filename = ($baseName ?: 'product') . '_' . time() . '_' . Str::random(6) . '.gif';
            $fullPath = $targetDir . DIRECTORY_SEPARATOR . $filename;
            $publicFullPath = $publicDir . DIRECTORY_SEPARATOR . $filename;

            $sourcePath = $file instanceof UploadedFile ? $file->getRealPath() : $file;
            File::copy($sourcePath, $fullPath);
            if ($fullPath !== $publicFullPath) {
                @copy($fullPath, $publicFullPath);
            }

            return '/storage/' . trim($folder, '/') . '/' . $filename;
        }

        $filename = Str::slug(pathinfo($file instanceof UploadedFile ? $file->getClientOriginalName() : 'product', PATHINFO_FILENAME));
        $filename = ($filename ?: 'product') . '_' . time() . '_' . Str::random(6) . '.webp';
        $fullPath = $targetDir . DIRECTORY_SEPARATOR . $filename;
        $publicFullPath = $publicDir . DIRECTORY_SEPARATOR . $filename;

        if ($file instanceof UploadedFile) {
            $image = $this->manager->decodePath($file->getRealPath());
        } elseif (is_string($file) && file_exists($file)) {
            $image = $this->manager->decodePath($file);
        } else {
            $image = $this->manager->decode($file);
        }

        $image->orient();
        $image->contain(width: $dimension, height: $dimension, background: $bg);

        $encoded = $image->encode(new WebpEncoder(quality: $quality));
        $encoded->save($fullPath);
        if ($fullPath !== $publicFullPath) {
            @copy($fullPath, $publicFullPath);
        }

        return '/storage/' . trim($folder, '/') . '/' . $filename;
    }

    public function convertBase64ToWebp(string $base64String, string $folder = 'uploads', int $maxWidth = 1200, int $quality = 85): ?string
    {
        if (preg_match('/^data:image\/(\w+);base64,/', $base64String, $type)) {
            $targetDir = storage_path('app/public/' . trim($folder, '/'));
            if (!File::exists($targetDir)) {
                File::makeDirectory($targetDir, 0755, true, true);
            }

            $publicDir = public_path('storage/' . trim($folder, '/'));
            if (!File::exists($publicDir)) {
                File::makeDirectory($publicDir, 0755, true, true);
            }

            $filename = 'b64_' . time() . '_' . Str::random(8) . '.webp';
            $fullPath = $targetDir . DIRECTORY_SEPARATOR . $filename;
            $publicFullPath = $publicDir . DIRECTORY_SEPARATOR . $filename;

            $image = $this->manager->decodeBase64($base64String);
            $image->orient();
            if ($image->width() > $maxWidth) {
                $image->resizeDown(width: $maxWidth);
            }

            $encoded = $image->encode(new WebpEncoder(quality: $quality));
            $encoded->save($fullPath);
            if ($fullPath !== $publicFullPath) {
                @copy($fullPath, $publicFullPath);
            }

            return '/storage/' . trim($folder, '/') . '/' . $filename;
        }

        return null;
    }

    public function convertBase64ProductImageToWebp(string $base64String, string $folder = 'products', int $dimension = 800, int $quality = 85, string $bg = 'ffffff'): ?string
    {
        if (preg_match('/^data:image\/(\w+);base64,/', $base64String, $type)) {
            $targetDir = storage_path('app/public/' . trim($folder, '/'));
            if (!File::exists($targetDir)) {
                File::makeDirectory($targetDir, 0755, true, true);
            }

            $publicDir = public_path('storage/' . trim($folder, '/'));
            if (!File::exists($publicDir)) {
                File::makeDirectory($publicDir, 0755, true, true);
            }

            $filename = 'b64_prod_' . time() . '_' . Str::random(8) . '.webp';
            $fullPath = $targetDir . DIRECTORY_SEPARATOR . $filename;
            $publicFullPath = $publicDir . DIRECTORY_SEPARATOR . $filename;

            $image = $this->manager->decodeBase64($base64String);
            $image->orient();
            $image->contain(width: $dimension, height: $dimension, background: $bg);

            $encoded = $image->encode(new WebpEncoder(quality: $quality));
            $encoded->save($fullPath);
            if ($fullPath !== $publicFullPath) {
                @copy($fullPath, $publicFullPath);
            }

            return '/storage/' . trim($folder, '/') . '/' . $filename;
        }

        return null;
    }

    public function deleteMedia(?string $url): bool
    {
        if (empty($url) || !str_starts_with($url, '/storage/')) {
            return false;
        }

        $relativePath = str_replace('/storage/', '', $url);
        $storagePath = storage_path('app/public/' . $relativePath);
        $publicPath = public_path('storage/' . $relativePath);

        $deleted = false;
        if (File::exists($storagePath)) {
            $deleted = File::delete($storagePath);
        }
        if (File::exists($publicPath)) {
            File::delete($publicPath);
            $deleted = true;
        }

        return $deleted;
    }

    public function cloneMedia(?string $url, string $folder = 'products'): ?string
    {
        if (empty($url) || !is_string($url)) {
            return $url;
        }

        $cleanUrl = trim($url);
        $appUrl = config('app.url');
        if (!empty($appUrl) && str_starts_with($cleanUrl, $appUrl)) {
            $cleanUrl = substr($cleanUrl, strlen($appUrl));
        }

        if (preg_match('#^https?://#i', $cleanUrl) || str_starts_with($cleanUrl, 'data:')) {
            return $url;
        }

        $cleanUrl = '/' . ltrim($cleanUrl, '/');
        $sourceFile = null;
        $detectedFolder = trim($folder, '/');

        if (str_starts_with($cleanUrl, '/storage/')) {
            $relativePath = substr($cleanUrl, 9);
            $storagePath = storage_path('app/public/' . $relativePath);
            $publicPath = public_path('storage/' . $relativePath);

            if (File::exists($storagePath)) {
                $sourceFile = $storagePath;
            } elseif (File::exists($publicPath)) {
                $sourceFile = $publicPath;
            }

            $dir = dirname($relativePath);
            if (!empty($dir) && $dir !== '.' && $dir !== '/') {
                $detectedFolder = str_replace('\\', '/', $dir);
            }
        } else {
            $publicCandidate = public_path(ltrim($cleanUrl, '/'));
            if (File::exists($publicCandidate)) {
                $sourceFile = $publicCandidate;
            }
        }

        if (!$sourceFile || !File::exists($sourceFile)) {
            return $url;
        }

        $targetDir = storage_path('app/public/' . $detectedFolder);
        if (!File::exists($targetDir)) {
            File::makeDirectory($targetDir, 0755, true, true);
        }

        $publicDir = public_path('storage/' . $detectedFolder);
        if (!File::exists($publicDir)) {
            File::makeDirectory($publicDir, 0755, true, true);
        }

        $ext = pathinfo($sourceFile, PATHINFO_EXTENSION) ?: 'webp';
        $baseName = Str::slug(pathinfo($sourceFile, PATHINFO_FILENAME));
        $newFilename = ($baseName ?: 'product') . '_copy_' . time() . '_' . Str::random(6) . '.' . $ext;

        $destStoragePath = $targetDir . DIRECTORY_SEPARATOR . $newFilename;
        $destPublicPath = $publicDir . DIRECTORY_SEPARATOR . $newFilename;

        File::copy($sourceFile, $destStoragePath);
        if ($destStoragePath !== $destPublicPath) {
            File::copy($sourceFile, $destPublicPath);
        }

        return '/storage/' . $detectedFolder . '/' . $newFilename;
    }
}
