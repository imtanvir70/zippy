<?php

namespace App\Services\Frontend;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class FrontendCacheService
{
    public const TTL = 60;

    public static function settings(): array
    {
        return Cache::remember('fc.settings', self::TTL, function () {
            return DB::table('settings')->pluck('value', 'key')->all();
        });
    }

    public static function setting(string $key, $default = null)
    {
        return self::settings()[$key] ?? $default;
    }

    public static function activeCategories()
    {
        $items = Cache::remember('fc.categories.active', self::TTL, function () {
            return self::toCacheable(
                DB::table('categories')
                    ->where('is_active', 1)
                    ->orderBy('sort_order', 'asc')
                    ->get(['id', 'parent_id', 'name', 'name_bn', 'slug', 'image', 'sort_order'])
                    ->all()
            );
        });

        return collect(self::toRows($items));
    }

    public static function filterCategories()
    {
        $items = Cache::remember('fc.categories.filter', self::TTL, function () {
            return self::toCacheable(
                DB::table('categories')
                    ->where('is_active', 1)
                    ->whereNull('parent_id')
                    ->select('categories.id', 'categories.name', 'categories.name_bn', 'categories.slug', 'categories.sort_order')
                    ->selectSub(function ($q) {
                        $q->from('products')
                            ->whereColumn('products.category_id', 'categories.id')
                            ->where('products.is_active', 1)
                            ->selectRaw('count(*)');
                    }, 'products_count')
                    ->orderBy('sort_order', 'asc')
                    ->get()
                    ->all()
            );
        });

        return collect(self::toRows($items));
    }

    public static function bannersGrouped(): array
    {
        $grouped = Cache::remember('fc.banners.grouped', self::TTL, function () {
            $banners = self::toCacheable(
                DB::table('banners')
                    ->where('is_active', 1)
                    ->orderBy('sort_order', 'asc')
                    ->get()
                    ->all()
            );

            return [
                'hero_slides' => array_values(array_filter($banners, fn($b) => ($b['type'] ?? null) === 'hero_slide')),
                'promo_cards' => array_values(array_filter($banners, fn($b) => ($b['type'] ?? null) === 'promo_card')),
            ];
        });

        return [
            'hero_slides' => collect(self::toRows($grouped['hero_slides'])),
            'promo_cards' => collect(self::toRows($grouped['promo_cards'])),
        ];
    }

    public static function toCacheable(array $rows): array
    {
        return array_map(fn($row) => (array) $row, $rows);
    }

    public static function toRows(array $arrays): array
    {
        return array_map(fn($item) => (object) $item, $arrays);
    }

    public static function categorySlugMap(): array
    {
        return Cache::remember('fc.categories.slug_map', self::TTL, function () {
            return DB::table('categories')->pluck('id', 'slug')->all();
        });
    }

    public static function activeCoupons()
    {
        $items = Cache::remember('fc.coupons.active', self::TTL, function () {
            return self::toCacheable(
                DB::table('coupons')
                    ->where('is_active', 1)
                    ->where(function ($q) {
                        $q->whereNull('start_date')->orWhere('start_date', '<=', now());
                    })
                    ->where(function ($q) {
                        $q->whereNull('end_date')->orWhere('end_date', '>=', now());
                    })
                    ->orderBy('id', 'asc')
                    ->get()
                    ->all()
            );
        });

        return collect(self::toRows($items));
    }

    public static function flush(): void
    {
        Cache::forget('fc.settings');
        Cache::forget('fc.categories.active');
        Cache::forget('fc.categories.filter');
        Cache::forget('fc.banners.grouped');
        Cache::forget('fc.categories.slug_map');
        Cache::forget('fc.coupons.active');
        Cache::forget('fc.home.data');
        Cache::forget('home_payload_v1');
        Cache::forget('site_global_navigation_data');
        Cache::forget('nav_categories_tree');
        Cache::forget('fc.sitemap.xml');
        Cache::forget('theme_settings');
        Cache::forget('theme_settings_all');
    }

    public static function flushProducts(?int $productId = null): void
    {
        Cache::forget('fc.home.data');
        Cache::forget('home_payload_v1');
        Cache::forget('site_global_navigation_data');
        Cache::forget('fc.categories.filter');
        Cache::forget('fc.sitemap.xml');
        if ($productId) {
            Cache::forget('fc.product.sold.' . $productId);
        }
    }
}
