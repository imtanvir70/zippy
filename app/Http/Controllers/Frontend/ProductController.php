<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Services\Frontend\FrontendCacheService;
use App\Services\Review\ReviewService;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function show($slug)
    {
        $product = DB::table('products')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->select(
                'products.*',
                'categories.name as cat_name_en',
                'categories.name_bn as cat_name_bn',
                'categories.slug as cat_slug',
                'categories.parent_id as cat_parent_id'
            )
            ->where('products.slug', $slug)
            ->where('products.is_active', 1)
            ->first();

        if (!$product) {
            abort(404, 'প্রোডাক্টটি খুঁজে পাওয়া যায়নি');
        }

        $categoryMap = FrontendCacheService::activeCategories()->keyBy('id');
        $categoryBreadcrumbs = [];
        $currCat = $categoryMap->get($product->category_id);
        while ($currCat) {
            array_unshift($categoryBreadcrumbs, $currCat);
            $currCat = !empty($currCat->parent_id) ? $categoryMap->get($currCat->parent_id) : null;
        }

        $galleryImages = is_array($product->gallery_images) ? $product->gallery_images : (json_decode($product->gallery_images ?? '', true) ?: []);
        if (is_string($galleryImages)) {
            $galleryImages = json_decode($galleryImages, true) ?: [];
        }
        if (!is_array($galleryImages) || empty($galleryImages)) {
            $galleryImages = [$product->main_image];
        }

        $variants = $this->normalizeVariants($product->variants, $product->price, $galleryImages, $product->main_image, $product->stock_qty);


        $specifications = is_array($product->specifications) ? $product->specifications : (json_decode($product->specifications ?? '', true) ?: []);
        if (is_string($specifications)) {
            $specifications = json_decode($specifications, true) ?: [];
        }
        if (!is_array($specifications)) {
            $specifications = [];
        }



        $relatedProducts = DB::table('products')
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
            ->where('products.category_id', $product->category_id)
            ->where('products.id', '!=', $product->id)
            ->where('products.is_active', 1)
            ->limit(6)
            ->get();

        $settings = FrontendCacheService::settings();
        $globalFreeShipping = !empty($settings['free_shipping_enabled']) && $settings['free_shipping_enabled'] == '1';
        $minFreeShippingAmount = (float) ($settings['free_shipping_min_amount'] ?? ($settings['free_shipping_threshold'] ?? 5000));
        $isFreeShipping = !empty($product->is_free_shipping) || ($globalFreeShipping && (float)$product->price >= $minFreeShippingAmount);

        $shippingInside = $isFreeShipping ? 0 : (float) ($settings['shipping_inside_dhaka'] ?? 60);
        $shippingOutside = $isFreeShipping ? 0 : (float) ($settings['shipping_outside_dhaka'] ?? 120);
        $whatsappNumber = $settings['whatsapp_number'] ?? '8801700000000';
        $siteName = $settings['site_name'] ?? 'ZippyBD';
        $supportPhone = $settings['phone'] ?? '01700-000000';
        $supportEmail = $settings['email'] ?? 'support@zippybd.com';

        $verifiedBuyers = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('order_items.product_id', $product->id)
            ->select('orders.customer_name', 'orders.created_at', 'order_items.product_title', 'order_items.quantity')
            ->orderByDesc('orders.id')
            ->limit(10)
            ->get();

        $totalSoldUnits = (int) Cache::remember('fc.product.sold.' . $product->id, FrontendCacheService::TTL, function () use ($product) {
            return (int) DB::table('order_items')->where('product_id', $product->id)->sum('quantity');
        });

        $stats = ReviewService::getProductReviewStats($product->id);
        $reviewsCount = $stats['reviews_count'];
        $avgRating = $stats['rating'];
        $ratingBreakdown = $stats['breakdown'];

        $product->rating = $avgRating;
        $product->reviews_count = $reviewsCount;

        $productReviews = ReviewService::getApprovedReviews($product->id, 20);

        $schemaJsonLd = [
            '@context' => 'https://schema.org/',
            '@type' => 'Product',
            'name' => $product->title,
            'image' => $galleryImages,
            'description' => $product->short_desc ?: $product->title,
            'sku' => $product->sku ?: 'ZB-' . $product->id,
            'brand' => [
                '@type' => 'Brand',
                'name' => $product->brand_name ?? $siteName,
            ],
            'offers' => [
                '@type' => 'Offer',
                'url' => route('product.show', $product->slug),
                'priceCurrency' => 'BDT',
                'price' => (float) $product->price,
                'priceValidUntil' => date('Y') . '-12-31',
                'itemCondition' => 'https://schema.org/NewCondition',
                'availability' => $product->stock_qty > 0 ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock',
                'seller' => [
                    '@type' => 'Organization',
                    'name' => $siteName,
                ],
            ],
        ];

        if ($reviewsCount > 0 && $avgRating > 0) {
            $schemaJsonLd['aggregateRating'] = [
                '@type' => 'AggregateRating',
                'ratingValue' => number_format($avgRating, 1, '.', ''),
                'reviewCount' => $reviewsCount,
                'bestRating' => '5',
                'worstRating' => '1',
            ];
        }

        if ($productReviews->isNotEmpty()) {
            $reviewsSchema = [];
            foreach ($productReviews->take(5) as $r) {
                $reviewsSchema[] = [
                    '@type' => 'Review',
                    'author' => [
                        '@type' => 'Person',
                        'name' => $r->customer_name ?: 'Verified Customer',
                    ],
                    'datePublished' => date('Y-m-d', strtotime($r->created_at)),
                    'reviewBody' => $r->comment,
                    'reviewRating' => [
                        '@type' => 'Rating',
                        'ratingValue' => (string) $r->rating,
                        'bestRating' => '5',
                        'worstRating' => '1',
                    ],
                ];
            }
            $schemaJsonLd['review'] = $reviewsSchema;
        }

        return view('frontend.product.show', compact(
            'product',
            'categoryBreadcrumbs',
            'galleryImages',
            'variants',
            'specifications',
            'relatedProducts',
            'productReviews',
            'avgRating',
            'reviewsCount',
            'ratingBreakdown',
            'schemaJsonLd',
            'settings',
            'shippingInside',
            'shippingOutside',
            'whatsappNumber',
            'siteName',
            'supportPhone',
            'supportEmail',
            'verifiedBuyers',
            'totalSoldUnits',
            'isFreeShipping'
        ));
    }

    public function submitReview(Request $request, $id)
    {
        $product = DB::table('products')->where('id', $id)->where('is_active', 1)->first();
        if (!$product) {
            return response()->json(['success' => false, 'message' => 'Product not found.'], 404);
        }

        $validated = $request->validate([
            'customer_name' => 'required|string|max:191',
            'customer_phone' => 'nullable|string|max:30',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'required|string|max:1000',
            'photos' => 'nullable|array|max:3',
            'photos.*' => 'image|mimes:jpeg,png,jpg,webp|max:5120',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
        ]);

        $optimizer = app(\App\Services\Media\ImageOptimizerService::class);
        $storedImages = [];

        if ($request->hasFile('photos')) {
            $files = array_slice($request->file('photos'), 0, 3);
            foreach ($files as $f) {
                if ($f && $f->isValid()) {
                    $webp = $optimizer->convertToWebp($f, 'reviews', 800, 80);
                    if ($webp) {
                        $storedImages[] = $webp;
                    }
                }
            }
        } elseif ($request->hasFile('photo') && $request->file('photo')->isValid()) {
            $webp = $optimizer->convertToWebp($request->file('photo'), 'reviews', 800, 80);
            if ($webp) {
                $storedImages[] = $webp;
            }
        }

        $photoPath = !empty($storedImages) ? $storedImages[0] : null;
        $imagesJson = !empty($storedImages) ? json_encode($storedImages) : null;

        $reviewId = DB::table('product_reviews')->insertGetId([
            'product_id' => $product->id,
            'customer_name' => $validated['customer_name'],
            'customer_phone' => $validated['customer_phone'] ?? null,
            'rating' => (int) $validated['rating'],
            'comment' => $validated['comment'],
            'photo' => $photoPath,
            'images' => $imagesJson,
            'status' => 'pending',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $stats = ReviewService::getProductReviewStats($product->id);

        return response()->json([
            'success' => true,
            'message' => 'আপনার রিভিউটি সফলভাবে জমা হয়েছে। অ্যাডমিন পর্যালোচনার পর এটি প্রকাশিত হবে।',
            'review' => [
                'id' => $reviewId,
                'customer_name' => $validated['customer_name'],
                'rating' => (int) $validated['rating'],
                'comment' => $validated['comment'],
                'photo' => $photoPath,
                'images' => $storedImages,
                'created_at' => 'Just now',
            ],
            'new_rating' => $stats['rating'],
            'new_count' => $stats['reviews_count'],
        ]);
    }


    protected function getFilterCategories()
    {
        return FrontendCacheService::filterCategories();
    }

    protected function getPerPage(Request $request, int $default = 20): int
    {
        $allowed = [10, 15, 20, 25, 30, 50];
        $perPage = (int) $request->get('per_page', $default);
        return in_array($perPage, $allowed) ? $perPage : $default;
    }

    protected function applyCatalogFilters($query, Request $request)
    {
        if ($request->filled('min_price') && is_numeric($request->min_price)) {
            $query->where('products.price', '>=', (float) $request->min_price);
        }
        if ($request->filled('max_price') && is_numeric($request->max_price)) {
            $query->where('products.price', '<=', (float) $request->max_price);
        }

        if ($request->filled('price_range')) {
            $range = $request->price_range;
            if ($range === '0-1000') {
                $query->where('products.price', '<=', 1000);
            } elseif ($range === '1000-2500') {
                $query->whereBetween('products.price', [1000, 2500]);
            } elseif ($range === '2500-5000') {
                $query->whereBetween('products.price', [2500, 5000]);
            } elseif ($range === '5000+') {
                $query->where('products.price', '>=', 5000);
            }
        }

        if ($request->boolean('in_stock') || $request->get('in_stock') === '1') {
            $query->where('products.stock_qty', '>', 0);
        }

        if ($request->filled('rating') && is_numeric($request->rating)) {
            $query->where('products.rating', '>=', (float) $request->rating);
        }

        if ($request->boolean('has_discount') || $request->get('has_discount') === '1') {
            $query->where(function ($q) {
                $q->whereNotNull('products.old_price')
                    ->whereRaw('products.old_price > products.price');
            });
        }

        if ($request->filled('category')) {
            $catVal = $request->input('category');
            if (is_string($catVal)) {
                $catArray = array_filter(array_map('trim', explode(',', $catVal)));
            } elseif (is_array($catVal)) {
                $catArray = array_filter($catVal);
            } else {
                $catArray = [];
            }

            $cleanSlugs = array_filter($catArray, fn($v) => !empty($v) && $v !== 'all');
            if (!empty($cleanSlugs)) {
                $query->whereIn('categories.slug', $cleanSlugs);
            }
        }

        return $query;
    }

    public function category(Request $request, $slug)
    {
        $category = DB::table('categories')->where('slug', $slug)->where('is_active', 1)->first();
        if (!$category) {
            abort(404, 'ক্যাটাগরি পাওয়া যায়নি');
        }

        $allCategoriesMap = FrontendCacheService::activeCategories()->keyBy('id');
        $childCategories = $allCategoriesMap->where('parent_id', $category->id)->sortBy('sort_order')->values();
        $childCatIds = $childCategories->pluck('id')->toArray();
        $grandChildCatIds = $allCategoriesMap->whereIn('parent_id', $childCatIds)->pluck('id')->toArray();
        $targetCategoryIds = array_unique(array_merge([$category->id], $childCatIds, $grandChildCatIds));

        $categoryBreadcrumbs = [];
        $curr = $category;
        while ($curr) {
            array_unshift($categoryBreadcrumbs, $curr);
            $curr = !empty($curr->parent_id) ? $allCategoriesMap->get($curr->parent_id) : null;
        }

        $query = DB::table('products')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->whereIn('products.category_id', $targetCategoryIds)
            ->where('products.is_active', 1)
            ->select(
                'products.*',
                'categories.name as cat_name_en',
                'categories.name_bn as category_name',
                'categories.slug as category_slug'
            );

        $this->applyCatalogFilters($query, $request);

        $sort = $request->get('sort', 'latest');
        if ($sort === 'price_asc') {
            $query->orderBy('products.price', 'asc');
        } elseif ($sort === 'price_desc') {
            $query->orderBy('products.price', 'desc');
        } elseif ($sort === 'rating') {
            $query->orderBy('products.rating', 'desc');
        } else {
            $query->orderBy('products.id', 'desc');
        }

        $products = $query->paginate($this->getPerPage($request))->withQueryString();
        $filterCategories = $this->getFilterCategories();

        if ($request->ajax() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
            return response()->json([
                'success' => true,
                'html' => view('frontend.product.partials.product-grid-container', compact('products'))->render(),
                'total' => $products->total(),
                'count_text' => $products->total() . ' টি পণ্য',
            ]);
        }

        return view('frontend.product.category', compact('category', 'categoryBreadcrumbs', 'childCategories', 'products', 'sort', 'filterCategories'));
    }

    public function index(Request $request)
    {
        $query = DB::table('products')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->where('products.is_active', 1)
            ->select(
                'products.*',
                'categories.name as cat_name_en',
                'categories.name_bn as category_name',
                'categories.slug as category_slug'
            );

        $this->applyCatalogFilters($query, $request);

        $sort = $request->get('sort', 'latest');
        if ($sort === 'price_asc') {
            $query->orderBy('products.price', 'asc');
        } elseif ($sort === 'price_desc') {
            $query->orderBy('products.price', 'desc');
        } elseif ($sort === 'rating') {
            $query->orderBy('products.rating', 'desc');
        } else {
            $query->orderBy('products.id', 'desc');
        }

        $products = $query->paginate($this->getPerPage($request))->withQueryString();
        $filterCategories = $this->getFilterCategories();

        if ($request->ajax() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
            return response()->json([
                'success' => true,
                'html' => view('frontend.product.partials.product-grid-container', compact('products'))->render(),
                'total' => $products->total(),
                'count_text' => 'মোট ' . $products->total() . ' টি পণ্য',
            ]);
        }

        return view('frontend.product.index', compact('products', 'filterCategories', 'sort'));
    }

    public function newCollection(Request $request)
    {
        $sevenDaysAgo = now()->subDays(7)->startOfDay();

        $query = DB::table('products')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->where('products.is_active', 1)
            ->select(
                'products.*',
                'categories.name as cat_name_en',
                'categories.name_bn as category_name',
                'categories.slug as category_slug'
            );

        $hasRecent = DB::table('products')
            ->where('is_active', 1)
            ->where('created_at', '>=', $sevenDaysAgo)
            ->exists();

        if ($hasRecent) {
            $query->where('products.created_at', '>=', $sevenDaysAgo);
        }

        $this->applyCatalogFilters($query, $request);

        $sort = $request->get('sort', 'latest');
        if ($sort === 'price_asc') {
            $query->orderBy('products.price', 'asc');
        } elseif ($sort === 'price_desc') {
            $query->orderBy('products.price', 'desc');
        } elseif ($sort === 'rating') {
            $query->orderBy('products.rating', 'desc');
        } else {
            $query->orderBy('products.created_at', 'desc')->orderBy('products.id', 'desc');
        }

        $products = $query->paginate($this->getPerPage($request))->withQueryString();
        $filterCategories = $this->getFilterCategories();

        if ($request->ajax() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
            return response()->json([
                'success' => true,
                'html' => view('frontend.product.partials.product-grid-container', compact('products'))->render(),
                'total' => $products->total(),
                'count_text' => $products->total() . ' টি পণ্য',
            ]);
        }

        return view('frontend.product.new-collection', compact('products', 'sort', 'hasRecent', 'filterCategories'));
    }

    public function bestSale(Request $request)
    {
        $sort = $request->get('sort', 'best_selling');

        $salesSub = DB::table('order_items')
            ->selectRaw('product_id, SUM(quantity) as total_sales_count')
            ->groupBy('product_id');

        $query = DB::table('products')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->leftJoinSub($salesSub, 'sales', 'sales.product_id', '=', 'products.id')
            ->where('products.is_active', 1)
            ->select(
                'products.*',
                'categories.name as cat_name_en',
                'categories.name_bn as category_name',
                'categories.slug as category_slug',
                DB::raw('COALESCE(sales.total_sales_count, 0) as total_sales_count')
            );

        $this->applyCatalogFilters($query, $request);

        if ($sort === 'price_asc') {
            $query->orderBy('products.price', 'asc');
        } elseif ($sort === 'price_desc') {
            $query->orderBy('products.price', 'desc');
        } elseif ($sort === 'rating') {
            $query->orderBy('products.rating', 'desc');
        } else {
            $query->orderByDesc('total_sales_count')
                ->orderByDesc('products.is_featured')
                ->orderByDesc('products.rating')
                ->orderByDesc('products.id');
        }

        $products = $query->paginate($this->getPerPage($request))->withQueryString();
        $filterCategories = $this->getFilterCategories();

        if ($request->ajax() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
            return response()->json([
                'success' => true,
                'html' => view('frontend.product.partials.product-grid-container', compact('products'))->render(),
                'total' => $products->total(),
                'count_text' => $products->total() . ' টি পণ্য',
            ]);
        }

        return view('frontend.product.best-sale', compact('products', 'sort', 'filterCategories'));
    }

    public function flashDeals(Request $request)
    {
        $query = DB::table('products')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->where('products.is_active', 1)
            ->where('products.is_flash_deal', 1)
            ->select(
                'products.*',
                'categories.name as cat_name_en',
                'categories.name_bn as category_name',
                'categories.slug as category_slug'
            );

        $this->applyCatalogFilters($query, $request);

        $sort = $request->get('sort', 'latest');
        if ($sort === 'price_asc') {
            $query->orderBy('products.price', 'asc');
        } elseif ($sort === 'price_desc') {
            $query->orderBy('products.price', 'desc');
        } elseif ($sort === 'rating') {
            $query->orderBy('products.rating', 'desc');
        } else {
            $query->orderBy('products.id', 'desc');
        }

        $products = $query->paginate($this->getPerPage($request))->withQueryString();
        $filterCategories = $this->getFilterCategories();

        if ($request->ajax() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
            return response()->json([
                'success' => true,
                'html' => view('frontend.product.partials.product-grid-container', compact('products'))->render(),
                'total' => $products->total(),
                'count_text' => $products->total() . ' টি পণ্য',
            ]);
        }

        return view('frontend.product.flash-deals', compact('products', 'sort', 'filterCategories'));
    }

    public function quickView($id)
    {
        $product = DB::table('products')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->select(
                'products.*',
                'categories.name_bn as category_name'
            )
            ->where('products.id', $id)
            ->first();

        if (!$product) {
            return response()->json(['success' => false, 'message' => 'Product not found'], 404);
        }

        $saveAmt = ($product->old_price && $product->old_price > $product->price)
            ? ($product->old_price - $product->price)
            : 0;

        $galleryImages = is_array($product->gallery_images) ? $product->gallery_images : (json_decode($product->gallery_images ?? '', true) ?: []);
        if (is_string($galleryImages)) {
            $galleryImages = json_decode($galleryImages, true) ?: [];
        }
        if (!is_array($galleryImages) || empty($galleryImages)) {
            $galleryImages = [$product->main_image];
        }

        $variants = $this->normalizeVariants($product->variants, $product->price, $galleryImages, $product->main_image, $product->stock_qty);

        $specifications = is_array($product->specifications) ? $product->specifications : (json_decode($product->specifications ?? '', true) ?: []);
        if (is_string($specifications)) {
            $specifications = json_decode($specifications, true) ?: [];
        }
        if (!is_array($specifications)) {
            $specifications = [];
        }

        $stats = ReviewService::getProductReviewStats($product->id);

        return response()->json([
            'success' => true,
            'product' => [
                'id' => $product->id,
                'title' => $product->title,
                'slug' => $product->slug,
                'category_name' => $product->category_name,
                'price' => (float) $product->price,
                'old_price' => (float) $product->old_price,
                'save_amount' => (float) $saveAmt,
                'discount_percentage' => ($product->old_price && $product->old_price > $product->price) ? (int) round((($product->old_price - $product->price) / $product->old_price) * 100) : 0,
                'tag' => $product->tag,
                'rating' => (float) $stats['rating'],
                'reviews_count' => (int) $stats['reviews_count'],
                'main_image' => $product->main_image,
                'gallery_images' => $galleryImages,
                'variants' => $variants,
                'specifications' => $specifications,
                'short_desc' => $product->short_desc,
                'description' => $product->description,
                'in_stock' => $product->stock_qty > 0,
            ]
        ]);
    }

    private function normalizeVariants($rawVariants, $basePrice, $galleryImages = [], $mainImage = '', $stockQty = 50)
    {
        $variants = is_array($rawVariants) ? $rawVariants : (json_decode($rawVariants ?? '', true) ?: []);
        if (is_string($variants)) {
            $variants = json_decode($variants, true) ?: [];
        }
        if (!is_array($variants)) {
            return [];
        }

        $normalized = [];
        $idx = 0;
        foreach ($variants as $v) {
            $img = $galleryImages[$idx] ?? ($galleryImages[0] ?? $mainImage);
            $stock = $stockQty > 0 ? (int) $stockQty : 50;

            if (is_array($v) || is_object($v)) {
                $name = is_object($v) ? ($v->name ?? '') : ($v['name'] ?? '');
                $price = is_object($v) ? ($v->price ?? $basePrice) : ($v['price'] ?? $basePrice);
                $customImg = is_object($v) ? ($v->image ?? null) : ($v['image'] ?? null);
                $customStock = is_object($v) ? ($v->stock ?? null) : ($v['stock'] ?? null);

                if (!empty($name)) {
                    $priceVal = (float) $price;
                    $normalized[] = [
                        'name' => $name,
                        'price' => $priceVal,
                        'price_diff' => $priceVal - (float) $basePrice,
                        'image' => $customImg ?: $img,
                        'stock' => $customStock !== null ? (int) $customStock : $stock,
                    ];
                    $idx++;
                }
            } elseif (is_string($v) && trim($v) !== '') {
                $normalized[] = [
                    'name' => trim($v),
                    'price' => (float) $basePrice,
                    'price_diff' => 0,
                    'image' => $img,
                    'stock' => $stock,
                ];
                $idx++;
            }
        }
        return $normalized;
    }
}
