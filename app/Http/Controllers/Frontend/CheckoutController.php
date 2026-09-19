<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Services\Auth\DeviceTrackingService;
use App\Services\Fraud\FraudDetectionService;
use App\Services\Frontend\FrontendCacheService;
use App\Services\Marketing\CouponService;
use App\Jobs\SendOrderEmailJob;
use App\Jobs\SendOrderSmsJob;
use App\Jobs\SendOrderConfirmationSmsJob;
use App\Jobs\SendMetaCapiEventJob;
use App\Services\Order\OrderNumberService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Throwable;

class CheckoutController extends Controller
{
    protected DeviceTrackingService $deviceTrackingService;

    public function __construct(DeviceTrackingService $deviceTrackingService)
    {
        $this->deviceTrackingService = $deviceTrackingService;
    }

    public function index(Request $request)
    {
        $cart = session()->get('cart', []);

        if (empty($cart)) {
            return redirect()->route('home')->with('info', 'আপনার কার্টে কোনো প্রোডাক্ট নেই।');
        }

        $subtotal = 0;
        $productIds = array_unique(array_filter(array_column($cart, 'id')));
        $dbProducts = DB::table('products')
            ->whereIn('id', $productIds)
            ->select('id', 'price', 'variants', 'is_free_shipping')
            ->get()
            ->keyBy('id');

        $hasFreeShippingItem = false;
        foreach ($cart as $key => &$item) {
            $pId = $item['id'] ?? null;
            if ($pId && isset($dbProducts[$pId])) {
                $p = $dbProducts[$pId];
                if (!empty($p->is_free_shipping)) {
                    $hasFreeShippingItem = true;
                }
                $qty = max(1, (int) ($item['qty'] ?? 1));
                $unitPrice = $this->resolveProductPrice($p, $item['variant'] ?? null);
                $item['price'] = $unitPrice;
                $subtotal += $unitPrice * $qty;
            } else {
                unset($cart[$key]);
            }
        }
        unset($item);

        session()->put('cart', $cart);

        if (empty($cart)) {
            return redirect()->route('home')->with('info', 'আপনার কার্টে কোনো প্রোডাক্ট নেই।');
        }

        $settings = FrontendCacheService::settings();
        $shippingInside = (float) ($settings['shipping_inside_dhaka'] ?? 60);
        $shippingOutside = (float) ($settings['shipping_outside_dhaka'] ?? 120);
        $globalFreeShipping = !empty($settings['free_shipping_enabled']) && $settings['free_shipping_enabled'] == '1';
        $minFreeShippingAmount = (float) ($settings['free_shipping_min_amount'] ?? ($settings['free_shipping_threshold'] ?? 5000));
        $qualifiesForFreeShipping = ($globalFreeShipping && $subtotal >= $minFreeShippingAmount) || $hasFreeShippingItem;

        $user = Auth::user();

        $customerDefault = (object) [
            'name' => $user->name ?? '',
            'phone' => $user->phone ?? '',
            'address' => $user->address ?? '',
            'district' => $user->district ?? 'ঢাকা',
        ];

        $deviceToken = $this->deviceTrackingService->resolveDeviceToken($request);

        if (!$user) {
            $lastGuestOrder = DB::table('orders')
                ->where('device_token', $deviceToken)
                ->orderByDesc('id')
                ->first();

            if ($lastGuestOrder) {
                $customerDefault->name = $lastGuestOrder->customer_name ?: '';
                $customerDefault->phone = $lastGuestOrder->customer_phone ?: '';
                $rawOrderAddr = $lastGuestOrder->customer_address ?: '';
                $tokens = array_filter(array_map('trim', explode(',', $rawOrderAddr)));
                $dedupedTokens = [];
                foreach ($tokens as $t) {
                    if (!in_array(mb_strtolower($t), array_map('mb_strtolower', $dedupedTokens), true)) {
                        $dedupedTokens[] = $t;
                    }
                }
                $customerDefault->address = implode(', ', $dedupedTokens);
                $customerDefault->district = $lastGuestOrder->district ?: 'ঢাকা';
            } else {
                $deviceInfo = DB::table('guest_devices')->where('device_token', $deviceToken)->first();
                if ($deviceInfo) {
                    $customerDefault->name = $deviceInfo->last_name ?: '';
                    $customerDefault->phone = $deviceInfo->last_phone ?: '';
                }
            }
        }

        $standardShippingCost = ($customerDefault->district === 'ঢাকার বাইরে') ? $shippingOutside : $shippingInside;
        $shippingCost = $qualifiesForFreeShipping ? 0 : $standardShippingCost;

        $couponSession = session()->get('coupon');
        $discount = 0.00;

        if ($couponSession && !empty($couponSession['code'])) {
            $couponService = new CouponService();
            $couponValidation = $couponService->validateAndApply(
                (string) $couponSession['code'],
                $subtotal,
                $customerDefault->phone ?: null
            );

            if ($couponValidation['success']) {
                $discount = (float) $couponValidation['discount'];
                $couponSession['discount'] = $discount;
                session()->put('coupon', $couponSession);
            } else {
                session()->forget('coupon');
                $couponSession = null;
            }
        }

        $grandTotal = max(0, $subtotal + $shippingCost - $discount);
        $coupon = $couponSession;

        $divisions = Cache::remember('fc.geo.divisions_hierarchy', 86400, function () {
            $divs = DB::table('divisions')->select('id', 'name', 'bn_name')->orderBy('id')->get();
            $districts = DB::table('districts')->select('id', 'division_id', 'name', 'bn_name')->orderBy('name')->get()->groupBy('division_id');
            $upazilas = DB::table('upazilas')->select('id', 'district_id', 'name', 'bn_name')->orderBy('name')->get()->groupBy('district_id');

            foreach ($divs as $div) {
                $divDistricts = $districts->get($div->id, collect());
                foreach ($divDistricts as $dist) {
                    $dist->upazilas = $upazilas->get($dist->id, collect())->values()->all();
                }
                $div->districts = $divDistricts->values()->all();
            }

            return json_decode(json_encode($divs), true);
        });

        $availableCoupons = Cache::remember('fc.coupons.checkout_available', 300, function () {
            $now = now();
            return DB::table('coupons')
                ->where('is_active', 1)
                ->where(function ($q) use ($now) {
                    $q->whereNull('start_date')->orWhere('start_date', '<=', $now);
                })
                ->where(function ($q) use ($now) {
                    $q->whereNull('end_date')->orWhere('end_date', '>=', $now);
                })
                ->where(function ($q) {
                    $q->whereNull('usage_limit')->orWhereRaw('used_count < usage_limit');
                })
                ->select('id', 'code', 'type', 'value', 'min_order_amount', 'max_discount_amount')
                ->orderBy('min_order_amount', 'asc')
                ->get()
                ->toArray();
        });

        if (!is_iterable($availableCoupons) || $availableCoupons instanceof \__PHP_Incomplete_Class) {
            Cache::forget('fc.coupons.checkout_available');
            $availableCoupons = [];
        }

        $orderBump = DB::table('order_bumps')
            ->join('products', 'products.id', '=', 'order_bumps.bump_product_id')
            ->where('order_bumps.is_active', 1)
            ->where('products.is_active', 1)
            ->where('products.stock_qty', '>', 0)
            ->where(function ($q) use ($productIds) {
                $q->whereIn('order_bumps.product_id', $productIds)
                    ->orWhereNull('order_bumps.product_id');
            })
            ->whereNotIn('order_bumps.bump_product_id', $productIds)
            ->select(
                'order_bumps.id',
                'order_bumps.product_id as target_product_id',
                'order_bumps.bump_product_id',
                'order_bumps.title',
                'order_bumps.description',
                'order_bumps.price',
                'products.title as bump_product_title',
                'products.price as original_price',
                'products.main_image'
            )
            ->orderByRaw('CASE WHEN order_bumps.product_id IS NOT NULL THEN 0 ELSE 1 END')
            ->orderBy('order_bumps.sort_order', 'asc')
            ->first();

        return view('frontend.checkout.index', compact(
            'cart',
            'subtotal',
            'shippingInside',
            'shippingOutside',
            'shippingCost',
            'discount',
            'grandTotal',
            'settings',
            'user',
            'customerDefault',
            'deviceToken',
            'coupon',
            'qualifiesForFreeShipping',
            'divisions',
            'availableCoupons',
            'orderBump'
        ))->withCookie($this->deviceTrackingService->makeCookie($deviceToken));
    }

    private function resolveProductPrice(object $product, ?string $selectedVariant): float
    {
        $basePrice = (float) $product->price;
        if (!empty($selectedVariant) && !empty($product->variants)) {
            $variants = is_array($product->variants) ? $product->variants : (json_decode($product->variants, true) ?: []);
            if (is_string($variants)) {
                $variants = json_decode($variants, true) ?: [];
            }
            if (is_array($variants)) {
                $cleanSelected = trim($selectedVariant);
                foreach ($variants as $v) {
                    $vName = is_array($v) ? ($v['name'] ?? '') : (is_object($v) ? ($v->name ?? '') : (is_string($v) ? $v : ''));
                    if (trim($vName) === $cleanSelected) {
                        $vPrice = is_array($v) ? ($v['price'] ?? null) : (is_object($v) ? ($v->price ?? null) : null);
                        if ($vPrice !== null && is_numeric($vPrice) && (float) $vPrice > 0) {
                            return (float) $vPrice;
                        }
                    }
                }
            }
        }
        return $basePrice;
    }

    public function process(Request $request)
    {
        $validated = $request->validate([
            'customer_name' => 'required|string|max:191',
            'customer_phone' => ['required', 'string', 'max:30', 'regex:/^(?:\+?88)?01[3-9]\d{8}$/'],
            'customer_address' => 'required|string|max:500',
            'district' => 'required|string|in:ঢাকা,ঢাকার বাইরে',
            'payment_method' => 'required|string|in:cod,bkash',
            'notes' => 'nullable|string|max:500',
            'device_token' => 'nullable|string',
            'guest_email' => 'nullable|email|max:191',
        ], [
            'customer_name.required' => 'আপনার পুরো নাম লিখুন',
            'customer_phone.required' => 'সঠিক মোবাইল নম্বর লিখুন',
            'customer_phone.regex' => 'সঠিক বাংলাদেশি মোবাইল নম্বর দিন (যেমন 017XXXXXXXX)',
            'customer_address.required' => 'ডেলিভারি ঠিকানা লিখুন',
            'district.required' => 'ডেলিভারি এরিয়া সিলেক্ট করুন',
        ]);

        $validated['customer_name'] = strip_tags($validated['customer_name']);
        $rawAddress = strip_tags($validated['customer_address']);
        $tokens = array_filter(array_map('trim', explode(',', $rawAddress)));
        $dedupedTokens = [];
        foreach ($tokens as $t) {
            if (!in_array(mb_strtolower($t), array_map('mb_strtolower', $dedupedTokens), true)) {
                $dedupedTokens[] = $t;
            }
        }
        $validated['customer_address'] = implode(', ', $dedupedTokens);
        if (!empty($validated['notes'])) {
            $validated['notes'] = strip_tags($validated['notes']);
        }

        $clientIp = $request->ip() ?? '127.0.0.1';
        $cleanPhone = preg_replace('/[\s\-\(\)]/', '', $validated['customer_phone']);
        if (str_starts_with($cleanPhone, '+88')) {
            $cleanPhone = substr($cleanPhone, 3);
        } elseif (str_starts_with($cleanPhone, '88')) {
            $cleanPhone = substr($cleanPhone, 2);
        }

        $isBlocked = DB::table('blocklists')
            ->where('is_blocked', 1)
            ->where(function ($q) use ($cleanPhone, $clientIp) {
                $q->where(function ($q2) use ($cleanPhone) {
                    $q2->where('type', 'phone')
                        ->where(function ($q3) use ($cleanPhone) {
                            $q3->where('value', $cleanPhone)
                                ->orWhere('value', '0' . ltrim($cleanPhone, '0'))
                                ->orWhere('value', '+88' . $cleanPhone)
                                ->orWhere('value', '88' . $cleanPhone);
                        });
                })->orWhere(function ($q2) use ($clientIp) {
                    $q2->where('type', 'ip')
                        ->where('value', $clientIp);
                });
            })
            ->exists();

        if ($isBlocked) {
            return response()->json([
                'success' => false,
                'message' => 'নিরাপত্তা জনিত কারণে এই অর্ডারটি গ্রহণ করা সম্ভব হচ্ছে না। বিস্তারিত জানতে কাস্টমার সাপোর্টে যোগাযোগ করুন।',
            ], 403);
        }

        $cart = session()->get('cart', []);

        if (empty($cart)) {
            return response()->json([
                'success' => false,
                'message' => 'আপনার কার্ট খালি। অনুগ্রহ করে পণ্য যোগ করুন।',
            ], 422);
        }

        $lockKey = 'checkout_lock_' . md5($validated['customer_phone'] . serialize($cart));
        $lock = Cache::lock($lockKey, 10);

        if (!$lock->get()) {
            return response()->json([
                'success' => false,
                'message' => 'আপনার অর্ডারটি প্রক্রিয়াধীন আছে। অনুগ্রহ করে কয়েক সেকেন্ড অপেক্ষা করুন।',
            ], 429);
        }

        try {
            $productIds = array_unique(array_filter(array_column($cart, 'id')));
            if (empty($productIds)) {
                return response()->json([
                    'success' => false,
                    'message' => 'কার্টে কোনো সঠিক পণ্য পাওয়া যায়নি।',
                ], 422);
            }

            $preliminaryProducts = DB::table('products')
                ->whereIn('id', $productIds)
                ->select('id', 'price', 'variants', 'is_free_shipping', 'stock_qty', 'is_active')
                ->get()
                ->keyBy('id');

            $subtotal = 0;
            $hasFreeShippingItem = false;

            foreach ($cart as $item) {
                $pId = $item['id'] ?? null;
                if (!$pId || !isset($preliminaryProducts[$pId])) {
                    return response()->json([
                        'success' => false,
                        'message' => 'নির্বাচিত একটি পণ্য ক্যাটালগে পাওয়া যায়নি।',
                    ], 422);
                }

                $p = $preliminaryProducts[$pId];
                if (!empty($p->is_free_shipping)) {
                    $hasFreeShippingItem = true;
                }

                $qty = max(1, (int) ($item['qty'] ?? 1));
                $price = $this->resolveProductPrice($p, $item['variant'] ?? null);
                $subtotal += $price * $qty;
            }

            $settings = FrontendCacheService::settings();
            $shippingInside = (float) ($settings['shipping_inside_dhaka'] ?? 60);
            $shippingOutside = (float) ($settings['shipping_outside_dhaka'] ?? 120);
            $globalFreeShipping = !empty($settings['free_shipping_enabled']) && $settings['free_shipping_enabled'] == '1';
            $minFreeShippingAmount = (float) ($settings['free_shipping_min_amount'] ?? ($settings['free_shipping_threshold'] ?? 5000));
            $qualifiesForFreeShipping = ($globalFreeShipping && $subtotal >= $minFreeShippingAmount) || $hasFreeShippingItem;

            $standardShippingCost = ($validated['district'] === 'ঢাকা') ? $shippingInside : $shippingOutside;
            $shippingCost = $qualifiesForFreeShipping ? 0 : $standardShippingCost;

            $couponSession = session()->get('coupon');
            $discount = 0.00;
            $couponId = null;

            if ($couponSession && !empty($couponSession['code'])) {
                $couponService = new CouponService();
                $couponResult = $couponService->validateAndApply(
                    (string) $couponSession['code'],
                    $subtotal,
                    $validated['customer_phone']
                );

                if ($couponResult['success']) {
                    $couponId = (int) $couponResult['coupon_id'];
                    $discount = (float) $couponResult['discount'];
                } else {
                    session()->forget('coupon');
                }
            }

            $total = max(0, $subtotal + $shippingCost - $discount);
            $todayPrefix = 'ZB-' . date('ymd') . '-';
            $orderNumber = '';

            $ip = $request->ip() ?? '127.0.0.1';
            $userAgent = $request->header('User-Agent') ?? 'Browser';

            $deviceToken = $request->input('device_token') ?: $this->deviceTrackingService->resolveDeviceToken($request);
            $user = Auth::user();
            $userId = $user ? $user->id : null;
            $isGuest = !$user;
            $guestEmail = $validated['guest_email'] ?? ($user ? $user->email : null);

            $fraudService = new FraudDetectionService();
            $fraudEvaluation = $fraudService->evaluateOrder([
                'customer_phone' => $validated['customer_phone'],
                'customer_name' => $validated['customer_name'],
                'customer_address' => $validated['customer_address'],
                'district' => $validated['district'],
                'total' => $total,
                'payment_method' => $validated['payment_method'],
                'ip_address' => $ip,
            ]);

            $bumpRecord = null;
            if ($request->boolean('include_bump') && $request->filled('bump_id')) {
                $bumpRecord = DB::table('order_bumps')
                    ->join('products', 'products.id', '=', 'order_bumps.bump_product_id')
                    ->where('order_bumps.id', (int) $request->input('bump_id'))
                    ->where('order_bumps.is_active', 1)
                    ->where('products.is_active', 1)
                    ->where('products.stock_qty', '>', 0)
                    ->select(
                        'order_bumps.id',
                        'order_bumps.bump_product_id',
                        'order_bumps.title as bump_headline',
                        'order_bumps.price as bump_price',
                        'products.id as product_id',
                        'products.title as product_title',
                        'products.main_image',
                        'products.stock_qty'
                    )
                    ->first();
            }

            $orderItemsData = [];
            $atomicTotal = 0;
            $orderId = DB::transaction(function () use (
                $validated,
                $todayPrefix,
                $userId,
                $deviceToken,
                $isGuest,
                $guestEmail,
                $shippingCost,
                $discount,
                $couponId,
                $ip,
                $userAgent,
                $fraudEvaluation,
                $cart,
                $productIds,
                $bumpRecord,
                &$orderNumber,
                &$orderItemsData,
                &$atomicTotal
            ) {
                $lockedProducts = DB::table('products')
                    ->whereIn('id', $productIds)
                    ->lockForUpdate()
                    ->get()
                    ->keyBy('id');

                $atomicSubtotal = 0;
                $orderItemsData = [];

                foreach ($cart as $item) {
                    $pId = $item['id'] ?? null;
                    if (!$pId || !isset($lockedProducts[$pId])) {
                        throw new \RuntimeException('নির্বাচিত একটি পণ্য ক্যাটালগে পাওয়া যায়নি।');
                    }

                    $p = $lockedProducts[$pId];
                    if (!$p->is_active) {
                        throw new \RuntimeException("'{$p->title}' পণ্যটি বর্তমানে বিক্রয়ের জন্য সক্রিয় নয়।");
                    }

                    $qty = max(1, (int) ($item['qty'] ?? 1));
                    if ((int) $p->stock_qty < $qty) {
                        $avail = max(0, (int) $p->stock_qty);
                        throw new \RuntimeException("দুঃখিত, '{$p->title}' পর্যাপ্ত স্টকে নেই (স্টক অবশিষ্ট: {$avail} টি)।");
                    }

                    $variant = $item['variant'] ?? null;
                    if (empty($variant) && !empty($p->variants)) {
                        $vars = is_array($p->variants) ? $p->variants : (json_decode($p->variants, true) ?: []);
                        if (is_string($vars)) {
                            $vars = json_decode($vars, true) ?: [];
                        }
                        if (is_array($vars) && count($vars) > 0) {
                            $first = $vars[0];
                            $variant = is_array($first) ? ($first['name'] ?? null) : (is_string($first) ? $first : null);
                        }
                    }
                    $unitPrice = $this->resolveProductPrice($p, $variant);
                    $lineTotal = $unitPrice * $qty;
                    $atomicSubtotal += $lineTotal;

                    $productTitle = $variant ? $p->title . ' - ' . $variant : $p->title;

                    $orderItemsData[] = [
                        'product_id' => $p->id,
                        'product_title' => $productTitle,
                        'variant' => $variant,
                        'product_image' => (!empty($item['image']) ? $item['image'] : $p->main_image),
                        'unit_price' => $unitPrice,
                        'quantity' => $qty,
                        'total_price' => $lineTotal,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }

                if ($bumpRecord) {
                    $lockedBump = DB::table('products')
                        ->where('id', $bumpRecord->product_id)
                        ->lockForUpdate()
                        ->first();

                    if ($lockedBump && $lockedBump->is_active && (int) $lockedBump->stock_qty >= 1) {
                        $bumpUnitPrice = (float) $bumpRecord->bump_price;
                        $atomicSubtotal += $bumpUnitPrice;
                        $orderItemsData[] = [
                            'product_id' => $bumpRecord->product_id,
                            'product_title' => $bumpRecord->product_title . ' (' . $bumpRecord->bump_headline . ')',
                            'variant' => null,
                            'product_image' => $bumpRecord->main_image,
                            'unit_price' => $bumpUnitPrice,
                            'quantity' => 1,
                            'total_price' => $bumpUnitPrice,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                    }
                }

                $atomicTotal = max(0, $atomicSubtotal + $shippingCost - $discount);

                $orderNumber = OrderNumberService::generate();

                $id = DB::table('orders')->insertGetId([
                    'order_number' => $orderNumber,
                    'user_id' => $userId,
                    'device_token' => $deviceToken,
                    'is_guest' => $isGuest,
                    'guest_email' => $guestEmail,
                    'customer_name' => $validated['customer_name'],
                    'customer_phone' => $validated['customer_phone'],
                    'customer_address' => $validated['customer_address'],
                    'district' => $validated['district'],
                    'subtotal' => $atomicSubtotal,
                    'shipping_cost' => $shippingCost,
                    'discount' => $discount,
                    'total' => $atomicTotal,
                    'payment_method' => $validated['payment_method'],
                    'payment_status' => 'pending',
                    'order_status' => 'pending',
                    'fraud_score' => $fraudEvaluation['fraud_score'],
                    'fraud_status' => $fraudEvaluation['fraud_status'],
                    'fraud_notes' => implode(' | ', $fraudEvaluation['reasons']),
                    'ip_address' => $ip,
                    'user_agent' => $userAgent,
                    'notes' => $validated['notes'] ?? null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                foreach ($orderItemsData as $itemData) {
                    $itemData['order_id'] = $id;
                    DB::table('order_items')->insert($itemData);

                    DB::table('products')
                        ->where('id', $itemData['product_id'])
                        ->where('stock_qty', '>=', $itemData['quantity'])
                        ->decrement('stock_qty', $itemData['quantity']);
                }

                if ($couponId && $discount > 0) {
                    $couponService = new CouponService();
                    $couponService->recordUsage($couponId, $id, $validated['customer_phone'], $discount);
                }

                return $id;
            });

            $orderRecord = DB::table('orders')->where('id', $orderId)->first();
            $this->deviceTrackingService->recordOrder($deviceToken, $orderRecord, $user);

            $sessionId = session()->getId();
            DB::table('abandoned_carts')
                ->where('session_id', $sessionId)
                ->orWhere('customer_phone', $validated['customer_phone'])
                ->update([
                    'is_recovered' => 1,
                    'updated_at' => now(),
                ]);

            session()->forget('cart');
            session()->forget('coupon');
            session(['last_order_code' => $orderNumber]);

            try {
                SendOrderSmsJob::dispatch((int) $orderId, 'pending');
                SendOrderConfirmationSmsJob::dispatch((int) $orderId);
                SendOrderEmailJob::dispatch((int) $orderId);

                $capiContents = array_map(function ($item) {
                    return [
                        'id' => (string) ($item['product_id'] ?? ''),
                        'quantity' => (int) ($item['quantity'] ?? 1),
                        'item_price' => (float) ($item['unit_price'] ?? 0.0),
                    ];
                }, $orderItemsData);

                SendMetaCapiEventJob::dispatch('Purchase', [
                    'order_number' => $orderNumber,
                    'event_id' => 'order_' . $orderNumber,
                    'email' => $guestEmail ?: ($user ? $user->email : null),
                    'phone' => $validated['customer_phone'],
                    'name' => $validated['customer_name'],
                    'city' => $validated['district'],
                    'value' => (float) ($orderRecord->total ?? $atomicTotal),
                    'currency' => 'BDT',
                    'client_ip_address' => $ip,
                    'client_user_agent' => $userAgent,
                    'fbp' => $request->cookie('_fbp'),
                    'fbc' => $request->cookie('_fbc') ?: ($request->input('fbclid') ? 'fb.1.' . time() . '.' . $request->input('fbclid') : null),
                    'event_source_url' => $request->fullUrl(),
                    'contents' => $capiContents,
                ]);
            } catch (Throwable $e) {
            }

            return response()->json([
                'success' => true,
                'order_number' => $orderNumber,
                'device_token' => $deviceToken,
                'redirect_url' => route('order.success', $orderNumber),
            ])->withCookie($this->deviceTrackingService->makeCookie($deviceToken));
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage() ?: 'অর্ডার প্রক্রিয়া সম্পন্ন করা সম্ভব হয়নি। অনুগ্রহ করে পুনরায় চেষ্টা করুন।',
            ], 422);
        } finally {
            $lock->release();
        }
    }

    public function saveAbandonedLead(Request $request)
    {
        $name = trim($request->input('customer_name', ''));
        $phone = trim($request->input('customer_phone', ''));

        if (empty($phone) && empty($name)) {
            return response()->json(['success' => false, 'message' => 'No info to capture.']);
        }

        $cart = session()->get('cart', []);
        $subtotal = 0;
        if (!empty($cart)) {
            $productIds = array_unique(array_filter(array_column($cart, 'id')));
            $dbProducts = DB::table('products')
                ->whereIn('id', $productIds)
                ->select('id', 'price', 'variants')
                ->get()
                ->keyBy('id');

            foreach ($cart as $item) {
                $pId = $item['id'] ?? null;
                if ($pId && isset($dbProducts[$pId])) {
                    $unitPrice = $this->resolveProductPrice($dbProducts[$pId], $item['variant'] ?? null);
                    $subtotal += $unitPrice * max(1, (int) ($item['qty'] ?? 1));
                }
            }
        }

        $sessionId = session()->getId();
        $existing = DB::table('abandoned_carts')->where('session_id', $sessionId)->first();

        if ($existing) {
            DB::table('abandoned_carts')->where('session_id', $sessionId)->update([
                'customer_name' => $name ?: $existing->customer_name,
                'customer_phone' => $phone ?: $existing->customer_phone,
                'cart_data' => json_encode($cart, JSON_UNESCAPED_UNICODE),
                'total_amount' => $subtotal,
                'last_activity_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            DB::table('abandoned_carts')->insert([
                'session_id' => $sessionId,
                'customer_name' => $name,
                'customer_phone' => $phone,
                'cart_data' => json_encode($cart, JSON_UNESCAPED_UNICODE),
                'total_amount' => $subtotal,
                'is_recovered' => 0,
                'last_activity_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $deviceToken = $this->deviceTrackingService->resolveDeviceToken($request);
        $deviceRecord = DB::table('guest_devices')->where('device_token', $deviceToken)->first();
        if ($deviceRecord) {
            DB::table('guest_devices')->where('device_token', $deviceToken)->update([
                'last_name' => $name ?: $deviceRecord->last_name,
                'last_phone' => $phone ?: $deviceRecord->last_phone,
                'last_active_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            DB::table('guest_devices')->insert([
                'device_token' => $deviceToken,
                'ip_address' => $request->ip() ?? '127.0.0.1',
                'user_agent' => $request->header('User-Agent') ?? 'Browser',
                'last_phone' => $phone,
                'last_name' => $name,
                'total_orders' => 0,
                'total_spent' => 0,
                'last_active_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return response()->json(['success' => true]);
    }
}
