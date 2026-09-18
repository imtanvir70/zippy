<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

if (!function_exists('theme_setting')) {
    function theme_setting($key, $default = null)
    {
        $settings = Cache::rememberForever('theme_settings_all', function () {
            if (Schema::hasTable('theme_settings')) {
                $record = DB::table('theme_settings')->first();
                if ($record) {
                    return (array) $record;
                }
            }
            return [];
        });

        return $settings[$key] ?? $default;
    }
}

if (!function_exists('youtube_video_id')) {
    function youtube_video_id(?string $url): ?string
    {
        if (!$url) {
            return null;
        }

        $trimmed = trim($url);
        if (preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=|shorts/)|youtu\.be/)([^"&?/ ]{11})%i', $trimmed, $matches)) {
            return $matches[1];
        }

        if (strlen($trimmed) === 11 && preg_match('/^[a-zA-Z0-9_-]{11}$/', $trimmed)) {
            return $trimmed;
        }

        return null;
    }
}

if (!function_exists('youtube_embed_url')) {
    function youtube_embed_url(?string $url): ?string
    {
        $id = youtube_video_id($url);
        return $id ? "https://www.youtube-nocookie.com/embed/{$id}?rel=0" : null;
    }
}

if (!function_exists('product_image_url')) {
    function product_image_url(?string $path): string
    {
        $raw = trim((string)($path ?? ''));
        if (empty($raw) || str_contains($raw, 'example.com')) {
            return asset('images/product-placeholder.svg');
        }
        if (str_starts_with($raw, 'http://') || str_starts_with($raw, 'https://')) {
            return $raw;
        }
        if (str_starts_with($raw, '/storage/') || str_starts_with($raw, 'storage/')) {
            return asset(ltrim($raw, '/'));
        }
        if (str_starts_with($raw, '/images/') || str_starts_with($raw, 'images/')) {
            return asset(ltrim($raw, '/'));
        }
        if (str_starts_with($raw, '/')) {
            return asset(ltrim($raw, '/'));
        }
        return asset('storage/' . $raw);
    }
}
