<?php

namespace App\Services\Review;

use App\Services\Frontend\FrontendCacheService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ReviewService
{
    public static function syncProductReviewStats(int $productId): array
    {
        $stats = DB::table('product_reviews')
            ->where('product_id', $productId)
            ->where('status', 'approved')
            ->selectRaw('COUNT(*) as total_count, AVG(rating) as avg_rating')
            ->first();

        $count = $stats ? (int) $stats->total_count : 0;
        $rating = ($count > 0 && $stats->avg_rating !== null) ? round((float) $stats->avg_rating, 1) : 0.0;

        DB::table('products')->where('id', $productId)->update([
            'rating' => $rating,
            'reviews_count' => $count,
            'updated_at' => now(),
        ]);

        Cache::forget('fc.product.show.' . $productId);
        Cache::forget('fc.product.reviews.' . $productId);
        Cache::forget('fc.product.review_stats.' . $productId);
        Cache::forget('fc.product.approved_reviews.' . $productId . '.20');

        return [
            'rating' => $rating,
            'reviews_count' => $count,
        ];
    }

    public static function syncAllProductsReviewStats(): int
    {
        $products = DB::table('products')->select('id')->get();
        $updated = 0;

        foreach ($products as $p) {
            self::syncProductReviewStats($p->id);
            $updated++;
        }

        return $updated;
    }

    public static function getProductReviewStats(int $productId): array
    {
        return Cache::remember('fc.product.review_stats.' . $productId, 300, function () use ($productId) {
            $stats = DB::table('product_reviews')
                ->where('product_id', $productId)
                ->where('status', 'approved')
                ->selectRaw('COUNT(*) as total_count, AVG(rating) as avg_rating')
                ->first();

            $count = $stats ? (int) $stats->total_count : 0;
            $rating = ($count > 0 && $stats->avg_rating !== null) ? round((float) $stats->avg_rating, 1) : 0.0;

            $ratingsGroup = DB::table('product_reviews')
                ->where('product_id', $productId)
                ->where('status', 'approved')
                ->select('rating', DB::raw('COUNT(*) as count'))
                ->groupBy('rating')
                ->pluck('count', 'rating')
                ->toArray();

            $breakdown = [
                5 => $ratingsGroup[5] ?? 0,
                4 => $ratingsGroup[4] ?? 0,
                3 => $ratingsGroup[3] ?? 0,
                2 => $ratingsGroup[2] ?? 0,
                1 => $ratingsGroup[1] ?? 0,
            ];

            return [
                'rating' => $rating,
                'reviews_count' => $count,
                'breakdown' => $breakdown,
            ];
        });
    }

    public static function getApprovedReviews(int $productId, int $limit = 20)
    {
        $rows = Cache::remember('fc.product.approved_reviews.' . $productId . '.' . $limit, 300, function () use ($productId, $limit) {
            return DB::table('product_reviews')
                ->where('product_id', $productId)
                ->where('status', 'approved')
                ->orderByDesc('id')
                ->limit($limit)
                ->get()
                ->map(fn($item) => (array) $item)
                ->all();
        });

        return collect(array_map(fn($item) => (object) $item, $rows));
    }
}
