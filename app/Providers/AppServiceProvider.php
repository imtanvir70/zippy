<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;

class AppServiceProvider extends ServiceProvider
{
    private static $navCategories = null;
    private static $megaMenuProducts = null;
    private static $storeSettings = null;

    public function register(): void
    {
        if (file_exists(app_path('Helpers/helpers.php'))) {
            require_once app_path('Helpers/helpers.php');
        }
    }

    public function boot(): void
    {
        Paginator::useBootstrapFive();
        \Illuminate\Support\Facades\Blade::anonymousComponentPath(resource_path('views/frontend/components'));
        \App\Services\Mail\DynamicMailConfigService::apply();

        RateLimiter::for('checkout', function (Request $request) {
            return Limit::perMinute(5)->by($request->ip());
        });

        RateLimiter::for('otp', function (Request $request) {
            return Limit::perMinute(5)->by($request->ip());
        });

        View::composer(['frontend.*', 'frontend.layouts.app', 'backend.layouts.app', 'backend.*'], function ($view) {
            if (self::$storeSettings === null) {
                if (Schema::hasTable('settings')) {
                    $raw = DB::table('settings')->pluck('value', 'key')->toArray();
                    $storeName = $raw['store_name'] ?? $raw['site_name'] ?? 'Zippy';
                    $storePhone = $raw['store_phone'] ?? $raw['phone'] ?? '01700000000';
                    $storeWhatsapp = $raw['store_whatsapp'] ?? $raw['whatsapp_number'] ?? '01700000000';
                    $storeEmail = $raw['store_email'] ?? $raw['email'] ?? 'support@Zippy.com';
                    $storeTagline = $raw['store_tagline'] ?? $raw['site_tagline'] ?? 'প্রিমিয়াম গ্যাজেট ও লাইফস্টাইল স্টোর বাংলাদেশ';
                    $storeAddress = $raw['store_address'] ?? 'ঢাকা, বাংলাদেশ';
                    $shippingDhaka = $raw['shipping_dhaka'] ?? $raw['shipping_inside_dhaka'] ?? '60';
                    $shippingOutside = $raw['shipping_outside'] ?? $raw['shipping_outside_dhaka'] ?? '120';
                    $freeShipping = $raw['free_shipping_threshold'] ?? '5000';
                    $announcement = $raw['announcement_text'] ?? $raw['announcement_bar_text'] ?? 'Cash on delivery available across all 64 districts with superfast express delivery!';
                    $currencySymbol = $raw['currency_symbol'] ?? $raw['currency'] ?? '৳';

                    self::$storeSettings = array_merge($raw, [
                        'store_name' => $storeName,
                        'site_name' => $storeName,
                        'store_phone' => $storePhone,
                        'phone' => $storePhone,
                        'store_whatsapp' => $storeWhatsapp,
                        'whatsapp_number' => $storeWhatsapp,
                        'store_email' => $storeEmail,
                        'email' => $storeEmail,
                        'store_tagline' => $storeTagline,
                        'site_tagline' => $storeTagline,
                        'store_address' => $storeAddress,
                        'shipping_dhaka' => $shippingDhaka,
                        'shipping_outside' => $shippingOutside,
                        'free_shipping_threshold' => $freeShipping,
                        'announcement_text' => $announcement,
                        'currency_symbol' => $currencySymbol,
                    ]);
                } else {
                    self::$storeSettings = [
                        'store_name' => 'Zippy',
                        'site_name' => 'Zippy',
                        'store_phone' => '01700000000',
                        'phone' => '01700000000',
                        'store_whatsapp' => '01700000000',
                        'whatsapp_number' => '01700000000',
                        'store_email' => 'support@Zippy.com',
                        'email' => 'support@Zippy.com',
                        'store_tagline' => 'প্রিমিয়াম গ্যাজেট ও লাইফস্টাইল স্টোর বাংলাদেশ',
                        'site_tagline' => 'প্রিমিয়াম গ্যাজেট ও লাইফস্টাইল স্টোর বাংলাদেশ',
                        'store_address' => 'ঢাকা, বাংলাদেশ',
                        'shipping_dhaka' => '60',
                        'shipping_outside' => '120',
                        'free_shipping_threshold' => '5000',
                        'announcement_text' => 'Cash on delivery available across all 64 districts with superfast express delivery!',
                        'currency_symbol' => '৳',
                    ];
                }
            }

            if (self::$navCategories === null) {
                if (Schema::hasTable('categories')) {
                    $allCats = DB::table('categories')
                        ->where('is_active', 1)
                        ->orderBy('sort_order', 'asc')
                        ->orderBy('name', 'asc')
                        ->get();

                    $rootCats = $allCats->whereNull('parent_id')->values();
                    foreach ($rootCats as $root) {
                        $subs = $allCats->where('parent_id', $root->id)->values();
                        $descendantIds = [$root->id];
                        foreach ($subs as $sub) {
                            $nested = $allCats->where('parent_id', $sub->id)->values();
                            $sub->nested = $nested;
                            $descendantIds[] = $sub->id;
                            foreach ($nested as $n) {
                                $descendantIds[] = $n->id;
                            }
                        }
                        $root->subs = $subs;
                        $root->descendant_ids = array_unique($descendantIds);
                    }

                    self::$megaMenuProducts = [];
                    $allCatIds = $allCats->pluck('id')->toArray();
                    if (!empty($allCatIds) && Schema::hasTable('products')) {
                        $allMegaProducts = DB::table('products')
                            ->whereIn('category_id', $allCatIds)
                            ->where('is_active', 1)
                            ->select('id', 'category_id', 'title', 'slug', 'price', 'old_price', 'main_image', 'tag', 'rating', 'reviews_count', 'is_featured')
                            ->orderByDesc('is_featured')
                            ->orderByDesc('rating')
                            ->orderByDesc('reviews_count')
                            ->orderByDesc('id')
                            ->get();

                        foreach ($rootCats as $root) {
                            $rootProds = $allMegaProducts->whereIn('category_id', $root->descendant_ids)->values();
                            $root->products_count = $rootProds->count();
                            self::$megaMenuProducts[$root->slug] = $rootProds->take(8)->values();
                        }
                    } else {
                        foreach ($rootCats as $root) {
                            $root->products_count = 0;
                            self::$megaMenuProducts[$root->slug] = collect([]);
                        }
                    }

                    self::$navCategories = $rootCats;
                } else {
                    self::$navCategories = collect([]);
                    self::$megaMenuProducts = [];
                }
            }

            $socialLogin = null;
            if (Schema::hasTable('social_login_settings')) {
                $socialLogin = DB::table('social_login_settings')->first();
            }

            $themeSettings = Cache::rememberForever('theme_settings', function () {
                if (Schema::hasTable('theme_settings')) {
                    $record = DB::table('theme_settings')->first();
                    if ($record) {
                        $words = [];
                        if (!empty($record->hero_typing_words)) {
                            $parsed = json_decode($record->hero_typing_words, true);
                            $words = is_array($parsed) ? $parsed : array_map('trim', explode(',', $record->hero_typing_words));
                        }
                        if (empty($words)) {
                            $words = ['Zippy BD', 'শপিং মানেই'];
                        }

                        return [
                            'hero_typing_words' => $words,
                            'primary_color' => $record->primary_color ?: '#0f172a',
                            'accent_color' => $record->accent_color ?: '#ff385c',
                            'font_family' => $record->font_family ?: 'Outfit',
                        ];
                    }
                }

                return [
                    'hero_typing_words' => ['Zippy BD', 'শপিং মানেই'],
                    'primary_color' => '#0f172a',
                    'accent_color' => '#ff385c',
                    'font_family' => 'Outfit',
                ];
            });

            $view->with('settings', self::$storeSettings);
            $view->with('storeSettings', self::$storeSettings);
            $view->with('navCategories', self::$navCategories);
            $view->with('megaMenuProducts', self::$megaMenuProducts);
            $view->with('socialLoginSetting', $socialLogin);
            $view->with('themeSettings', $themeSettings);
        });

        \Illuminate\Support\Facades\Blade::if('canPerm', function (string $permissionName) {
            $adminId = (int) session('admin_id', 0);
            if (!$adminId) {
                return false;
            }
            $permissionService = app(\App\Services\Rbac\PermissionService::class);
            return $permissionService->isSuperAdmin($adminId) || $permissionService->userCan($adminId, $permissionName);
        });
    }
}
