<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Services\Frontend\FrontendCacheService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    /**
     * Display the home page with high-performance cached DB queries.
     */
    public function index()
    {
        // Everything below is served from cache on repeat visits (TTL 10 min).
        $data = Cache::remember('fc.home.data', FrontendCacheService::TTL, function () {
            // 1+2. Hero Sliders & Promo Cards — ONE combined cached query instead of two.
            $banners = FrontendCacheService::bannersGrouped();
            $heroSlides = $banners['hero_slides'];
            $promoCards = $banners['promo_cards'];

            // 3. Flash Deals — only needed columns, not products.* (much lighter rows).
            $flashProducts = DB::table('products')
                ->join('categories', 'products.category_id', '=', 'categories.id')
                ->select(
                    'products.id',
                    'products.title',
                    'products.slug',
                    'products.price',
                    'products.old_price',
                    'products.main_image',
                    'products.gallery_images',
                    'products.rating',
                    'products.reviews_count',
                    'products.tag',
                    'products.badge_type',
                    'products.stock_qty',
                    'products.variants',
                    'categories.name_bn as category_name',
                    'categories.slug as category_slug'
                )
                ->where('products.is_active', 1)
                ->where('products.is_flash_deal', 1)
                ->orderByDesc('products.rating')
                ->limit(6)
                ->get();

            // 4. All Categories (cached service).
            $categories = FrontendCacheService::activeCategories();

            // 5. Category-wise products — single indexed query with slim columns.
            $categoryIds = $categories->pluck('id')->toArray();
            $allCategoryProducts = DB::table('products')
                ->join('categories', 'products.category_id', '=', 'categories.id')
                ->select(
                    'products.id',
                    'products.category_id',
                    'products.title',
                    'products.slug',
                    'products.price',
                    'products.old_price',
                    'products.main_image',
                    'products.gallery_images',
                    'products.rating',
                    'products.reviews_count',
                    'products.tag',
                    'products.badge_type',
                    'products.stock_qty',
                    'products.variants',
                    'categories.name_bn as category_name',
                    'categories.slug as category_slug'
                )
                ->whereIn('products.category_id', $categoryIds)
                ->where('products.is_active', 1)
                ->orderBy('products.id', 'asc')
                ->get();

            $categoryProducts = [];
            foreach ($categories as $cat) {
                $categoryProducts[$cat->slug] = $allCategoryProducts->where('category_id', $cat->id)->take(6)->values();
            }

            // 6. Settings (cached service).
            $settings = FrontendCacheService::settings();

            // 7. Active Featured Coupon for Hero Promo Card.
            $featuredCoupon = DB::table('coupons')
                ->where('is_active', 1)
                ->where(function ($q) {
                    $q->whereNull('start_date')->orWhere('start_date', '<=', now());
                })
                ->where(function ($q) {
                    $q->whereNull('end_date')->orWhere('end_date', '>=', now());
                })
                ->orderBy('id', 'asc')
                ->first();

            // Store ONLY plain scalar arrays — the database cache driver turns
            $featuredProducts = DB::table('products')
                ->join('categories', 'products.category_id', '=', 'categories.id')
                ->select(
                    'products.id',
                    'products.category_id',
                    'products.title',
                    'products.slug',
                    'products.price',
                    'products.old_price',
                    'products.main_image',
                    'products.gallery_images',
                    'products.rating',
                    'products.reviews_count',
                    'products.tag',
                    'products.badge_type',
                    'products.stock_qty',
                    'products.variants',
                    'products.is_featured',
                    'products.is_flash_deal',
                    'categories.name_bn as category_name',
                    'categories.slug as category_slug'
                )
                ->where('products.is_active', 1)
                ->where('products.is_featured', 1)
                ->orderByDesc('products.id')
                ->limit(12)
                ->get();

            $activePopup = DB::table('popups')
                ->where('is_active', 1)
                ->where(function ($q) {
                    $q->whereNull('start_date')->orWhere('start_date', '<=', now());
                })
                ->where(function ($q) {
                    $q->whereNull('end_date')->orWhere('end_date', '>=', now());
                })
                ->orderBy('id', 'desc')
                ->first();

            return [
                'heroSlides' => FrontendCacheService::toCacheable($heroSlides->all()),
                'promoCards' => FrontendCacheService::toCacheable($promoCards->all()),
                'flashProducts' => FrontendCacheService::toCacheable($flashProducts->all()),
                'featuredProducts' => FrontendCacheService::toCacheable($featuredProducts->all()),
                'categories' => FrontendCacheService::toCacheable($categories->all()),
                'categoryProducts' => array_map(fn($c) => FrontendCacheService::toCacheable($c->all()), $categoryProducts),
                'featuredCoupon' => $featuredCoupon ? (array) $featuredCoupon : null,
                'activePopup' => $activePopup ? (array) $activePopup : null,
                'settings' => $settings,
            ];
        });

        $data['heroSlides'] = collect(FrontendCacheService::toRows($data['heroSlides']));
        $data['promoCards'] = collect(FrontendCacheService::toRows($data['promoCards']));
        $data['flashProducts'] = collect(FrontendCacheService::toRows($data['flashProducts']));
        $data['featuredProducts'] = collect(FrontendCacheService::toRows($data['featuredProducts'] ?? []));
        $data['categories'] = collect(FrontendCacheService::toRows($data['categories']));
        $data['categoryProducts'] = array_map(fn($c) => collect(FrontendCacheService::toRows($c)), $data['categoryProducts']);
        $data['featuredCoupon'] = !empty($data['featuredCoupon']) ? (object) $data['featuredCoupon'] : null;
        $data['activePopup'] = !empty($data['activePopup']) ? (object) $data['activePopup'] : null;

        return view('frontend.home', $data);
    }

    /**
     * High-speed AJAX infinite scroll / load more endpoint.
     * Supports keyset pagination (WHERE id < last_id) for O(1) deep-page
     * performance instead of OFFSET scans.
     */
    public function loadMore(Request $request)
    {
        $page = max(1, (int) $request->input('page', 1));
        $limit = min(24, max(6, (int) $request->input('limit', 12)));
        $categorySlug = $request->input('category');

        $query = DB::table('products')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->select(
                'products.id',
                'products.title',
                'products.slug',
                'products.price',
                'products.old_price',
                'products.main_image',
                'products.gallery_images',
                'products.rating',
                'products.reviews_count',
                'products.tag',
                'products.badge_type',
                'products.is_flash_deal',
                'categories.name_bn as category_name',
                'categories.slug as category_slug'
            )
            ->where('products.is_active', 1);

        // Keyset pagination: skip the OFFSET row scan entirely when possible.
        $lastId = (int) $request->input('last_id', 0);
        if ($lastId > 0) {
            $query->where('products.id', '<', $lastId);
        } else {
            $offset = ($page - 1) * $limit;
            $query->offset($offset);
        }

        if ($categorySlug && $categorySlug !== 'all') {
            $query->where('categories.slug', $categorySlug);
        }

        $products = $query->orderBy('products.id', 'desc')
            ->limit($limit)
            ->get();

        $hasMore = count($products) === $limit;

        return response()->json([
            'success' => true,
            'products' => $products,
            'page' => $page,
            'last_id' => $products->isNotEmpty() ? $products->last()->id : $lastId,
            'has_more' => $hasMore
        ]);
    }

    /**
     * Display public custom CMS page (e.g. Terms, Privacy, Delivery).
     */
    public function showPage($slug)
    {
        $page = DB::table('pages')
            ->where('slug', $slug)
            ->where('is_published', 1)
            ->first();

        if (!$page) {
            abort(404);
        }

        return view('frontend.page', compact('page'));
    }

    public function recentSales(Request $request)
    {
        $sales = [];

        $recentOrders = DB::table('orders')
            ->join('order_items', 'orders.id', '=', 'order_items.order_id')
            ->leftJoin('products', 'order_items.product_id', '=', 'products.id')
            ->where('orders.order_status', '!=', 'cancelled')
            ->select(
                'orders.customer_name',
                'orders.district',
                'orders.created_at',
                'order_items.product_title',
                'order_items.product_image',
                'products.slug',
                'products.price'
            )
            ->orderByDesc('orders.id')
            ->limit(10)
            ->get();

        $recentTimes = ['এইমাত্র', '১ মিনিট আগে', '২ মিনিট আগে'];

        foreach ($recentOrders as $idx => $ord) {
            $rawName = trim($ord->customer_name ?: 'ক্রেতা');
            $nameParts = preg_split('/\s+/', $rawName);
            $first = $nameParts[0] ?? 'ক্রেতা';
            if (in_array(mb_strtolower($first), ['md', 'md.', 'mohammad', 'muhammad', 'মো:', 'মোঃ', 'মোহাম্মদ']) && isset($nameParts[1])) {
                $first = $nameParts[1];
            }
            $shortName = mb_substr($first, 0, min(2, mb_strlen($first))) . '***';

            $district = $ord->district ?: 'ঢাকা';
            if (str_contains($district, ',')) {
                $distParts = explode(',', $district);
                $district = trim($distParts[0]);
            }

            $timeAgo = $recentTimes[$idx % count($recentTimes)];

            // Validate product image — skip 403-causing stale paths
            $productImage = $ord->product_image;
            if ($productImage) {
                if (!str_starts_with($productImage, 'http://') && !str_starts_with($productImage, 'https://')) {
                    $trimmed = ltrim($productImage, '/');
                    $productImage = file_exists(public_path($trimmed)) ? asset($trimmed) : asset('images/product-placeholder.svg');
                }
            } else {
                $productImage = asset('images/product-placeholder.svg');
            }

            $sales[] = [
                'name' => $shortName . ' (' . $district . ')',
                'location' => '',
                'product_title' => $ord->product_title,
                'product_image' => $productImage,
                'product_url' => $ord->slug ? route('product.show', $ord->slug) : url('/products'),
                'price' => $ord->price ? '৳ ' . number_format($ord->price, 0) : '',
                'time_ago' => $timeAgo,
            ];
        }

        if (count($sales) < 3) {
            $fallbackProducts = DB::table('products')
                ->where('is_active', 1)
                ->orderByDesc('reviews_count')
                ->limit(6)
                ->get();

            $districts = ['ঢাকা', 'মিরপুর', 'উত্তরা', 'চট্টগ্রাম', 'সিলেট', 'ধানমন্ডি'];
            $names = ['তা***', 'সা***', 'আ***', 'রা***', 'মে***', 'ই***'];

            foreach ($fallbackProducts as $idx => $p) {
                $fallbackImg = $p->main_image;
                if ($fallbackImg) {
                    if (!str_starts_with($fallbackImg, 'http://') && !str_starts_with($fallbackImg, 'https://')) {
                        $fallbackImg = asset(ltrim($fallbackImg, '/'));
                    }
                } else {
                    $fallbackImg = asset('images/product-placeholder.svg');
                }

                $sales[] = [
                    'name' => $names[$idx % count($names)] . ' (' . $districts[$idx % count($districts)] . ')',
                    'location' => '',
                    'product_title' => $p->title,
                    'product_image' => $fallbackImg,
                    'product_url' => route('product.show', $p->slug),
                    'price' => '৳ ' . number_format($p->price, 0),
                    'time_ago' => $recentTimes[$idx % count($recentTimes)],
                ];
            }
        }

        return response()->json([
            'success' => true,
            'sales' => $sales,
        ]);
    }
}
