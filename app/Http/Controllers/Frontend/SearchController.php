<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Services\Frontend\FrontendCacheService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SearchController extends Controller
{
    /**
     * Comprehensive Bilingual Transliteration & Synonym Map
     */
    protected static array $bilingualMap = [
        // Keyboards
        'keyboard' => ['keyboard', 'keyboards', 'কীবোর্ড', 'কিবোর্ড', 'মেকানিক্যাল'],
        'keyboards' => ['keyboard', 'keyboards', 'কীবোর্ড', 'কিবোর্ড'],
        'কীবোর্ড' => ['keyboard', 'keyboards', 'কীবোর্ড', 'কিবোর্ড'],
        'কিবোর্ড' => ['keyboard', 'keyboards', 'কীবোর্ড', 'কিবোর্ড'],
        'mechanical' => ['mechanical', 'মেকানিক্যাল', 'কীবোর্ড'],
        'মেকানিক্যাল' => ['mechanical', 'মেকানিক্যাল', 'কীবোর্ড'],

        // Mouse
        'mouse' => ['mouse', 'mice', 'মাউস'],
        'mice' => ['mouse', 'mice', 'মাউস'],
        'মাউস' => ['mouse', 'mice', 'মাউস'],

        // Audio & Headphones
        'headphone' => ['headphone', 'headphones', 'earphone', 'হেডফোন', 'ইয়ারফোন', 'অডিও'],
        'headphones' => ['headphone', 'headphones', 'earphone', 'হেডফোন', 'ইয়ারফোন', 'অডিও'],
        'earphone' => ['earphone', 'earphones', 'headphone', 'ইয়ারফোন', 'হেডফোন'],
        'হেডফোন' => ['headphone', 'headphones', 'হেডফোন', 'ইয়ারফোন', 'অডিও'],
        'ইয়ারফোন' => ['earphone', 'headphone', 'ইয়ারফোন', 'হেডফোন'],
        'audio' => ['audio', 'sound', 'speaker', 'অডিও', 'সাউন্ড', 'স্পিকার', 'হেডফোন'],
        'sound' => ['sound', 'audio', 'সাউন্ড', 'অডিও'],
        'speaker' => ['speaker', 'speakers', 'স্পিকার', 'অডিও'],
        'অডিও' => ['audio', 'sound', 'অডিও', 'সাউন্ড', 'হেডফোন'],
        'সাউন্ড' => ['sound', 'audio', 'সাউন্ড', 'অডিও'],
        'স্পিকার' => ['speaker', 'স্পিকার', 'অডিও'],

        // Desk & Setup
        'desk' => ['desk', 'table', 'setup', 'ডেস্ক', 'টেবিল', 'সেটআপ'],
        'table' => ['table', 'desk', 'টেবিল', 'ডেস্ক'],
        'setup' => ['setup', 'desk', 'সেটআপ', 'ডেস্ক'],
        'ডেস্ক' => ['desk', 'table', 'setup', 'ডেস্ক', 'টেবিল'],
        'টেবিল' => ['table', 'desk', 'টেবিল', 'ডেস্ক'],
        'সেটআপ' => ['setup', 'desk', 'সেটআপ', 'ডেস্ক'],

        // Chair
        'chair' => ['chair', 'chairs', 'চেয়ার', 'চেয়ার'],
        'chairs' => ['chair', 'chairs', 'চেয়ার', 'চেয়ার'],
        'চেয়ার' => ['chair', 'chairs', 'চেয়ার', 'চেয়ার'],
        'চেয়ার' => ['chair', 'chairs', 'চেয়ার', 'চেয়ার'],

        // Lamp & Lighting
        'lamp' => ['lamp', 'light', 'lighting', 'ল্যাম্প', 'লাইট', 'বাতি', 'লাইটিং'],
        'light' => ['light', 'lamp', 'lighting', 'লাইট', 'ল্যাম্প', 'বাতি', 'লাইটিং'],
        'lighting' => ['lighting', 'light', 'lamp', 'লাইটিং', 'লাইট', 'ল্যাম্প'],
        'ল্যাম্প' => ['lamp', 'light', 'ল্যাম্প', 'লাইট', 'বাতি'],
        'লাইট' => ['light', 'lamp', 'লাইট', 'ল্যাম্প', 'বাতি'],
        'বাতি' => ['lamp', 'light', 'বাতি', 'লাইট', 'ল্যাম্প'],
        'লাইটিং' => ['lighting', 'light', 'লাইটিং', 'লাইট'],
        'ambient' => ['ambient', 'অ্যাম্বিয়েন্ট', 'অ্যাম্বিয়েন্ট', 'লাইট'],
        'অ্যাম্বিয়েন্ট' => ['ambient', 'অ্যাম্বিয়েন্ট', 'অ্যাম্বিয়েন্ট', 'লাইট'],
        'অ্যাম্বিয়েন্ট' => ['ambient', 'অ্যাম্বিয়েন্ট', 'অ্যাম্বিয়েন্ট', 'লাইট'],

        // Stand & Holder
        'stand' => ['stand', 'holder', 'স্ট্যান্ড', 'হোল্ডার'],
        'holder' => ['holder', 'stand', 'হোল্ডার', 'স্ট্যান্ড'],
        'স্ট্যান্ড' => ['stand', 'holder', 'স্ট্যান্ড', 'হোল্ডার'],
        'হোল্ডার' => ['holder', 'stand', 'হোল্ডার', 'স্ট্যান্ড'],

        // Pad & Mat
        'mat' => ['mat', 'pad', 'ম্যাট', 'প্যাড'],
        'pad' => ['pad', 'mat', 'প্যাড', 'ম্যাট'],
        'ম্যাট' => ['mat', 'pad', 'ম্যাট', 'প্যাড'],
        'প্যাড' => ['pad', 'mat', 'প্যাড', 'ম্যাট'],

        // Cable & Wire
        'cable' => ['cable', 'cables', 'wire', 'ক্যাবল', 'কেবল', 'তার', 'অর্গানাইজার'],
        'organizer' => ['organizer', 'অর্গানাইজার', 'কেবল', 'স্ট্যান্ড'],
        'ক্যাবল' => ['cable', 'cables', 'ক্যাবল', 'কেবল', 'তার'],
        'কেবল' => ['cable', 'cables', 'কেবল', 'ক্যাবল', 'তার'],
        'তার' => ['cable', 'wire', 'তার', 'ক্যাবল'],
        'অর্গানাইজার' => ['organizer', 'অর্গানাইজার', 'ক্যাবল'],

        // Wireless & Bluetooth
        'wireless' => ['wireless', 'bluetooth', 'ওয়্যারলেস', 'ওয়্যারলেস', 'ব্লুটুথ'],
        'bluetooth' => ['bluetooth', 'wireless', 'ব্লুটুথ', 'ওয়্যারলেস'],
        'ওয়্যারলেস' => ['wireless', 'bluetooth', 'ওয়্যারলেস', 'ওয়্যারলেস'],
        'ওয়্যারলেস' => ['wireless', 'bluetooth', 'ওয়্যারলেস', 'ওয়্যারলেস'],
        'ব্লুটুথ' => ['bluetooth', 'wireless', 'ব্লুটুথ', 'ওয়্যারলেস'],

        // Materials & Colors
        'aluminum' => ['aluminum', 'aluminium', 'অ্যালুমিনিয়াম', 'এলুমিনিয়াম'],
        'aluminium' => ['aluminium', 'aluminum', 'অ্যালুমিনিয়াম', 'এলুমিনিয়াম'],
        'অ্যালুমিনিয়াম' => ['aluminum', 'aluminium', 'অ্যালুমিনিয়াম', 'এলুমিনিয়াম'],
        'এলুমিনিয়াম' => ['aluminum', 'aluminium', 'এলুমিনিয়াম', 'অ্যালুমিনিয়াম'],
        'leather' => ['leather', 'লেদার', 'চামড়া', 'চামড়া'],
        'লেদার' => ['leather', 'লেদার', 'চামড়া'],
        'oak' => ['oak', 'wood', 'ওক', 'কাঠ'],
        'wood' => ['wood', 'oak', 'কাঠ', 'ওক'],
        'ওক' => ['oak', 'wood', 'ওক', 'কাঠ'],
        'কাঠ' => ['wood', 'oak', 'কাঠ', 'ওক'],
        'black' => ['black', 'ব্ল্যাক', 'কালো'],
        'ব্ল্যাক' => ['black', 'ব্ল্যাক', 'কালো'],
        'কালো' => ['black', 'কালো', 'ব্ল্যাক'],
        'white' => ['white', 'হোয়াইট', 'সাদা'],
        'হোয়াইট' => ['white', 'হোয়াইট', 'সাদা'],
        'সাদা' => ['white', 'সাদা', 'হোয়াইট'],

        // Gadgets & EDC
        'gadget' => ['gadget', 'gadgets', 'গ্যাজেট', 'গ্যাজেটস'],
        'gadgets' => ['gadget', 'gadgets', 'গ্যাজেট', 'গ্যাজেটস'],
        'গ্যাজেট' => ['gadget', 'gadgets', 'গ্যাজেট', 'গ্যাজেটস'],
        'গ্যাজেটস' => ['gadget', 'gadgets', 'গ্যাজেটস', 'গ্যাজেট'],
        'edc' => ['edc', 'minimal', 'মিনিমাল', 'গিয়ার', 'গিয়ার'],
        'minimal' => ['minimal', 'minimalist', 'মিনিমাল'],
        'মিনিমাল' => ['minimal', 'minimalist', 'মিনিমাল'],
        'ergonomic' => ['ergonomic', 'এরগনোমিক', 'অ্যারগোনমিক'],
        'এরগনোমিক' => ['ergonomic', 'এরগনোমিক', 'অ্যারগোনমিক'],
        'অ্যারগোনমিক' => ['ergonomic', 'এরগনোমিক', 'অ্যারগোনমিক'],
        'custom' => ['custom', 'কাস্টম'],
        'কাস্টম' => ['custom', 'কাস্টম'],
        'noise' => ['noise', 'নয়েজ', 'নয়েজ', 'হেডফোন'],
        'cancelling' => ['cancelling', 'ক্যান্সেলিং', 'হেডফোন'],
        'নয়েজ' => ['noise', 'নয়েজ', 'হেডফোন'],
        'নয়েজ' => ['noise', 'নয়েজ', 'হেডফোন'],
        'smart' => ['smart', 'স্মার্ট'],
        'স্মার্ট' => ['smart', 'স্মার্ট'],

        // Watch & Smartwatches
        'watch' => ['watch', 'smartwatch', 'ঘড়ি', 'ঘড়ি', 'স্মার্টওয়াচ', 'স্মার্টওয়াচ'],
        'smartwatch' => ['smartwatch', 'watch', 'স্মার্টওয়াচ', 'স্মার্টওয়াচ', 'ঘড়ি', 'ঘড়ি'],
        'ঘড়ি' => ['watch', 'smartwatch', 'ঘড়ি', 'ঘড়ি', 'স্মার্টওয়াচ'],
        'ঘড়ি' => ['watch', 'smartwatch', 'ঘড়ি', 'ঘড়ি', 'স্মার্টওয়াচ'],
        'স্মার্টওয়াচ' => ['smartwatch', 'watch', 'স্মার্টওয়াচ', 'স্মার্টওয়াচ', 'ঘড়ি'],
        'স্মার্টওয়াচ' => ['smartwatch', 'watch', 'স্মার্টওয়াচ', 'স্মার্টওয়াচ', 'ঘড়ি'],

        // Power, Charger & Accessories
        'charger' => ['charger', 'adapter', 'চার্জার', 'এডাপ্টার', 'ক্যাবল'],
        'চার্জার' => ['charger', 'adapter', 'চার্জার', 'এডাপ্টার'],
        'adapter' => ['adapter', 'charger', 'এডাপ্টার', 'চার্জার'],
        'এডাপ্টার' => ['adapter', 'charger', 'এডাপ্টার', 'চার্জার'],
        'powerbank' => ['powerbank', 'battery', 'পাওয়ারব্যাংক', 'পাওয়ারব্যাংক', 'ব্যাটারি'],
        'পাওয়ারব্যাংক' => ['powerbank', 'battery', 'পাওয়ারব্যাংক', 'পাওয়ারব্যাংক'],
        'পাওয়ারব্যাংক' => ['powerbank', 'battery', 'পাওয়ারব্যাংক', 'পাওয়ারব্যাংক'],
        'battery' => ['battery', 'powerbank', 'ব্যাটারি', 'পাওয়ারব্যাংক'],
        'ব্যাটারি' => ['battery', 'powerbank', 'ব্যাটারি', 'পাওয়ারব্যাংক'],
        'case' => ['case', 'cover', 'কেস', 'কভার'],
        'cover' => ['cover', 'case', 'কভার', 'কেস'],
        'কেস' => ['case', 'cover', 'কেস', 'কভার'],
        'কভার' => ['cover', 'case', 'কভার', 'কেস'],
        'hub' => ['hub', 'dock', 'হাব', 'ডক'],
        'dock' => ['dock', 'hub', 'ডক', 'হাব'],
        'webcam' => ['webcam', 'camera', 'ওয়েবক্যাম', 'ওয়েবক্যাম', 'ক্যামেরা'],
        'camera' => ['camera', 'webcam', 'ক্যামেরা', 'ওয়েবক্যাম'],
        'গেমিং' => ['gaming', 'game', 'গেমিং', 'গেম'],
        'gaming' => ['gaming', 'game', 'গেমিং', 'গেম'],
    ];

    /**
     * Expand search query tokens with synonyms in both languages.
     */
    protected function expandQueryTokens(string $q): array
    {
        $rawWords = preg_split('/[\s\-_,]+/u', mb_strtolower(trim($q)));
        $expanded = [];

        foreach ($rawWords as $word) {
            $w = trim($word);
            if (mb_strlen($w) < 1) continue;
            $expanded[] = $w;

            if (isset(self::$bilingualMap[$w])) {
                foreach (self::$bilingualMap[$w] as $syn) {
                    $expanded[] = $syn;
                }
            }

            // Substring partial match in dictionary keys
            foreach (self::$bilingualMap as $key => $synonyms) {
                if (mb_strpos($key, $w) !== false || mb_strpos($w, $key) !== false) {
                    foreach ($synonyms as $syn) {
                        $expanded[] = $syn;
                    }
                }
            }
        }

        return array_unique(array_filter($expanded));
    }

    /**
     * Build the query for bilingual matching.
     */
    protected function buildSearchQuery(string $q, string $categorySlug = 'all')
    {
        $tokens = $this->expandQueryTokens($q);
        $cleanQ = trim($q);

        $query = DB::table('products')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->select(
                'products.id',
                'products.title',
                'products.slug',
                'products.sku',
                'products.price',
                'products.old_price',
                'products.rating',
                'products.reviews_count',
                'products.is_flash_deal',
                'products.is_featured',
                'products.tag',
                'products.badge_type',
                'products.main_image as img',
                'products.main_image',
                'products.gallery_images',
                'products.variants',
                'categories.name_bn as cat',
                'categories.name as cat_en',
                'categories.slug as cat_slug'
            )
            ->where('products.is_active', 1);

        if ($categorySlug !== 'all' && !empty($categorySlug)) {
            $query->where('categories.slug', $categorySlug);
        }

        if (mb_strlen($cleanQ) > 0) {
            $query->where(function ($masterSub) use ($tokens, $cleanQ) {
                // Direct full query match
                $masterSub->where('products.title', 'like', "%{$cleanQ}%")
                    ->orWhere('products.slug', 'like', "%{$cleanQ}%")
                    ->orWhere('products.sku', 'like', "%{$cleanQ}%")
                    ->orWhere('products.short_desc', 'like', "%{$cleanQ}%")
                    ->orWhere('products.meta_keywords', 'like', "%{$cleanQ}%")
                    ->orWhere('categories.name_bn', 'like', "%{$cleanQ}%")
                    ->orWhere('categories.name', 'like', "%{$cleanQ}%")
                    ->orWhere('categories.slug', 'like', "%{$cleanQ}%");

                // Expanded bilingual tokens
                foreach ($tokens as $token) {
                    $masterSub->orWhere('products.title', 'like', "%{$token}%")
                        ->orWhere('products.slug', 'like', "%{$token}%")
                        ->orWhere('products.sku', 'like', "%{$token}%")
                        ->orWhere('products.short_desc', 'like', "%{$token}%")
                        ->orWhere('products.meta_keywords', 'like', "%{$token}%")
                        ->orWhere('categories.name_bn', 'like', "%{$token}%")
                        ->orWhere('categories.name', 'like', "%{$token}%")
                        ->orWhere('categories.slug', 'like', "%{$token}%");
                }
            });
        }

        return $query;
    }

    /**
     * Full Search Results Page (GET /search).
     */
    public function index(Request $request)
    {
        $q = trim($request->input('q', ''));
        $categorySlug = $request->input('category', 'all');
        $sort = $request->input('sort', 'latest');

        $query = $this->buildSearchQuery($q, $categorySlug);

        // Price Filters
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

        // Stock Availability
        if ($request->boolean('in_stock') || $request->get('in_stock') === '1') {
            $query->where('products.stock_qty', '>', 0);
        }

        // Customer Rating
        if ($request->filled('rating') && is_numeric($request->rating)) {
            $query->where('products.rating', '>=', (float) $request->rating);
        }

        // Discounted Products
        if ($request->boolean('has_discount') || $request->get('has_discount') === '1') {
            $query->where(function ($q) {
                $q->whereNotNull('products.old_price')
                    ->whereRaw('products.old_price > products.price');
            });
        }

        // Category Filter (support comma-separated string or array of slugs)
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

        // Sorting
        switch ($sort) {
            case 'price_asc':
                $query->orderBy('products.price', 'asc');
                break;
            case 'price_desc':
                $query->orderBy('products.price', 'desc');
                break;
            case 'rating':
                $query->orderBy('products.rating', 'desc')->orderBy('products.reviews_count', 'desc');
                break;
            default:
                $query->orderBy('products.id', 'desc');
                break;
        }

        $allowedPerPage = [10, 15, 20, 25, 30, 50];
        $perPage = (int) $request->get('per_page', 20);
        $perPage = in_array($perPage, $allowedPerPage) ? $perPage : 20;

        $products = $query->paginate($perPage)->withQueryString();

        // Cached (10 min) — no repeated correlated subquery per search request.
        $filterCategories = FrontendCacheService::filterCategories();

        if ($request->ajax() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
            return response()->json([
                'success' => true,
                'html' => view('frontend.product.partials.product-grid-container', compact('products'))->render(),
                'total' => $products->total(),
                'count_text' => $products->total() . ' টি পণ্য পাওয়া গেছে',
            ]);
        }

        return view('frontend.product.search', [
            'products' => $products,
            'queryText' => $q,
            'categorySlug' => $categorySlug,
            'sort' => $sort,
            'filterCategories' => $filterCategories,
        ]);
    }

    /**
     * Fast AJAX Instant Search Autocomplete Endpoint.
     */
    public function autocomplete(Request $request)
    {
        $q = trim($request->input('q', ''));
        $categorySlug = $request->input('category', 'all');

        if (mb_strlen($q) < 1) {
            return response()->json(['suggestions' => []]);
        }

        $query = $this->buildSearchQuery($q, $categorySlug);

        // Short-lived cache (60s) — repeated typing of the same query is instant.
        $suggestions = Cache::remember(
            'fc.search.ac.' . md5($q . '|' . $categorySlug),
            60,
            function () use ($query) {
                // Plain scalar arrays only — the database cache driver turns
                // every object into __PHP_Incomplete_Class on read.
                return FrontendCacheService::toCacheable($query->limit(8)->get()->all());
            }
        );

        return response()->json([
            'suggestions' => $suggestions,
        ]);
    }
}
