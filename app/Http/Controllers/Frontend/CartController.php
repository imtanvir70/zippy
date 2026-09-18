<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CartController extends Controller
{
    private function resolveProductPrice($product, ?string $selectedVariant, $fallbackPrice = null): float
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
        if ($fallbackPrice !== null && is_numeric($fallbackPrice) && (float) $fallbackPrice > 0) {
            return (float) $fallbackPrice;
        }
        return $basePrice;
    }

    private function resolveVariantImage($product, ?string $selectedVariant): ?string
    {
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
                        $vImg = is_array($v) ? ($v['image'] ?? null) : (is_object($v) ? ($v->image ?? null) : null);
                        if (!empty($vImg)) {
                            return $vImg;
                        }
                    }
                }
            }
        }
        return $product->main_image ?? null;
    }

    public function get()
    {
        $cart = session()->get('cart', []);
        $total = 0;
        $count = 0;
        $hasItemFreeShipping = false;

        if (!empty($cart)) {
            $productIds = array_unique(array_filter(array_column($cart, 'id')));
            $dbProducts = DB::table('products')
                ->whereIn('id', $productIds)
                ->select('id', 'title', 'slug', 'price', 'old_price', 'stock_qty', 'main_image', 'is_free_shipping', 'variants')
                ->get()
                ->keyBy('id');

            foreach ($cart as $key => &$item) {
                $pId = $item['id'] ?? null;
                if ($pId && isset($dbProducts[$pId])) {
                    $dbItem = $dbProducts[$pId];
                    $unitPrice = $this->resolveProductPrice($dbItem, $item['variant'] ?? null, $item['price'] ?? null);
                    $item['price'] = $unitPrice;
                    $item['old_price'] = (float) $dbItem->old_price;
                    $item['stock_qty'] = (int) $dbItem->stock_qty;
                    $item['cart_key'] = $key;
                    $item['image'] = $this->resolveVariantImage($dbItem, $item['variant'] ?? null) ?: ($item['image'] ?? $dbItem->main_image);
                    $item['is_free_shipping'] = !empty($dbItem->is_free_shipping);
                    if ($item['is_free_shipping']) {
                        $hasItemFreeShipping = true;
                    }
                    $item['total'] = $unitPrice * $item['qty'];
                    $total += $item['total'];
                    $count += $item['qty'];
                } else {
                    unset($cart[$key]);
                }
            }
            session()->put('cart', $cart);
        }

        $coupon = session()->get('coupon');
        $discount = 0.0;
        if ($coupon) {
            $couponModel = DB::table('coupons')->where('id', $coupon['id'] ?? 0)->where('is_active', 1)->first();
            if ($couponModel && $total >= (float) $couponModel->min_order_amount) {
                if ($couponModel->type === 'percent') {
                    $discount = ($total * (float) $couponModel->value) / 100;
                    if ($couponModel->max_discount_amount && $discount > (float) $couponModel->max_discount_amount) {
                        $discount = (float) $couponModel->max_discount_amount;
                    }
                } else {
                    $discount = (float) $couponModel->value;
                }
                $discount = min($discount, $total);
                $coupon['discount'] = round($discount, 2);
                session()->put('coupon', $coupon);
            } else {
                $discount = (float) ($coupon['discount'] ?? 0.0);
            }
        }

        $settings = \App\Services\Frontend\FrontendCacheService::settings();
        $globalFreeShipping = !empty($settings['free_shipping_enabled']) && $settings['free_shipping_enabled'] == '1';
        $minFreeShippingAmount = (float) ($settings['free_shipping_min_amount'] ?? ($settings['free_shipping_threshold'] ?? 5000));
        $isFreeShipping = ($globalFreeShipping && $total >= $minFreeShippingAmount) || $hasItemFreeShipping;

        return response()->json([
            'success' => true,
            'cart' => array_values($cart),
            'subtotal' => $total,
            'count' => $count,
            'formatted_subtotal' => '৳ ' . number_format($total, 0),
            'coupon' => $coupon,
            'discount' => $discount,
            'formatted_discount' => '৳ ' . number_format($discount, 0),
            'total' => max(0, $total - $discount),
            'formatted_total' => '৳ ' . number_format(max(0, $total - $discount), 0),
            'is_free_shipping' => $isFreeShipping,
            'free_shipping_min_amount' => $minFreeShippingAmount,
            'free_shipping_enabled' => $globalFreeShipping,
        ]);
    }

    public function add(Request $request)
    {
        $productId = (int) $request->input('product_id');
        $qty = max(1, (int) $request->input('quantity', 1));

        $product = DB::table('products')
            ->where('id', $productId)
            ->where('is_active', 1)
            ->select('id', 'title', 'slug', 'price', 'old_price', 'main_image', 'stock_qty', 'variants')
            ->first();
        if (!$product) {
            return response()->json(['success' => false, 'message' => 'প্রোডাক্টটি পাওয়া যায়নি'], 404);
        }

        $maxStock = max(1, (int) ($product->stock_qty ?? 1));
        $cart = session()->get('cart', []);

        $cartKey = (string) $productId;
        $variant = $request->input('variant') ?: ($request->input('color') ?: $request->input('size'));
        if ($request->filled('color') && $request->filled('size')) {
            $variant = $request->input('color') . ' / ' . $request->input('size');
        }
        if (empty($variant) && !empty($product->variants)) {
            $vars = is_array($product->variants) ? $product->variants : (json_decode($product->variants, true) ?: []);
            if (is_string($vars)) {
                $vars = json_decode($vars, true) ?: [];
            }
            if (is_array($vars) && count($vars) > 0) {
                $first = $vars[0];
                $variant = is_array($first) ? ($first['name'] ?? null) : (is_string($first) ? $first : null);
            }
        }
        $itemPrice = $this->resolveProductPrice($product, $variant);

        if (!empty($variant)) {
            $cartKey = $productId . '_' . substr(md5($variant), 0, 6);
        }

        if (isset($cart[$cartKey])) {
            $newQty = min($maxStock, $cart[$cartKey]['qty'] + $qty);
            $cart[$cartKey]['qty'] = $newQty;
            $cart[$cartKey]['stock_qty'] = $maxStock;
            $cart[$cartKey]['price'] = $itemPrice;
            $cart[$cartKey]['total'] = $itemPrice * $cart[$cartKey]['qty'];
        } else {
            $initialQty = min($maxStock, $qty);
            $cart[$cartKey] = [
                'id' => $product->id,
                'cart_key' => $cartKey,
                'title' => $product->title,
                'slug' => $product->slug,
                'variant' => $variant,
                'price' => $itemPrice,
                'old_price' => (float) $product->old_price,
                'image' => $this->resolveVariantImage($product, $variant) ?: $product->main_image,
                'stock_qty' => $maxStock,
                'qty' => $initialQty,
                'total' => $itemPrice * $initialQty,
            ];
        }

        session()->put('cart', $cart);

        return $this->get();
    }

    public function addBatch(Request $request)
    {
        $items = $request->input('items', []);
        if (empty($items) || !is_array($items)) {
            return response()->json(['success' => false, 'message' => 'কোনো পণ্য নির্বাচন করা হয়নি'], 422);
        }

        $cart = session()->get('cart', []);
        $productIds = array_unique(array_filter(array_map(fn($i) => (int) ($i['product_id'] ?? 0), $items)));
        $dbProducts = DB::table('products')
            ->whereIn('id', $productIds)
            ->where('is_active', 1)
            ->select('id', 'title', 'slug', 'price', 'old_price', 'main_image', 'stock_qty', 'variants')
            ->get()
            ->keyBy('id');

        $addedCount = 0;
        foreach ($items as $item) {
            $pId = (int) ($item['product_id'] ?? 0);
            $qty = (int) ($item['quantity'] ?? 0);
            if ($qty <= 0) {
                continue;
            }

            if (!isset($dbProducts[$pId])) {
                continue;
            }

            $product = $dbProducts[$pId];
            $variant = $item['variant'] ?? null;
            if (empty($variant) && !empty($product->variants)) {
                $vars = is_array($product->variants) ? $product->variants : (json_decode($product->variants, true) ?: []);
                if (is_string($vars)) {
                    $vars = json_decode($vars, true) ?: [];
                }
                if (is_array($vars) && count($vars) > 0) {
                    $first = $vars[0];
                    $variant = is_array($first) ? ($first['name'] ?? null) : (is_string($first) ? $first : null);
                }
            }
            $maxStock = max(1, (int) ($product->stock_qty ?? 1));
            $itemPrice = $this->resolveProductPrice($product, $variant, $item['price'] ?? null);

            $cartKey = (string) $pId;
            if (!empty($variant)) {
                $cartKey = $pId . '_' . substr(md5($variant), 0, 6);
            }

            if (isset($cart[$cartKey])) {
                $newQty = min($maxStock, $cart[$cartKey]['qty'] + $qty);
                $cart[$cartKey]['qty'] = $newQty;
                $cart[$cartKey]['stock_qty'] = $maxStock;
                $cart[$cartKey]['price'] = $itemPrice;
                $cart[$cartKey]['total'] = $itemPrice * $cart[$cartKey]['qty'];
            } else {
                $initialQty = min($maxStock, $qty);
                $cart[$cartKey] = [
                    'id' => $product->id,
                    'cart_key' => $cartKey,
                    'title' => $product->title,
                    'slug' => $product->slug,
                    'variant' => $variant,
                    'price' => $itemPrice,
                    'old_price' => (float) $product->old_price,
                    'image' => (!empty($item['image']) ? $item['image'] : $this->resolveVariantImage($product, $variant)) ?: $product->main_image,
                    'stock_qty' => $maxStock,
                    'qty' => $initialQty,
                    'total' => $itemPrice * $initialQty,
                ];
            }
            $addedCount += $qty;
        }

        if ($addedCount === 0) {
            return response()->json(['success' => false, 'message' => 'কমপক্ষে ১টি পণ্যের পরিমাণ নির্বাচন করুন'], 422);
        }

        session()->put('cart', $cart);

        return $this->get();
    }

    public function update(Request $request)
    {
        $key = (string) ($request->input('cart_key') ?? $request->input('product_id'));
        $delta = (int) $request->input('delta', 0);
        $qty = (int) $request->input('quantity', 0);

        $cart = session()->get('cart', []);

        if (!isset($cart[$key])) {
            foreach ($cart as $k => $item) {
                if ((string)$k === $key || (string)($item['cart_key'] ?? '') === $key || (string)($item['id'] ?? '') === $key) {
                    $key = $k;
                    break;
                }
            }
        }

        if (isset($cart[$key])) {
            $productId = (int) ($cart[$key]['id'] ?? 0);
            $product = DB::table('products')->where('id', $productId)->select('stock_qty')->first();
            $maxStock = $product ? max(1, (int) $product->stock_qty) : (int) ($cart[$key]['stock_qty'] ?? 999);
            $cart[$key]['stock_qty'] = $maxStock;

            if ($qty > 0) {
                $cart[$key]['qty'] = min($maxStock, $qty);
            } elseif ($delta !== 0) {
                $cart[$key]['qty'] = min($maxStock, $cart[$key]['qty'] + $delta);
            }

            if ($cart[$key]['qty'] <= 0) {
                unset($cart[$key]);
            } else {
                $cart[$key]['total'] = $cart[$key]['price'] * $cart[$key]['qty'];
            }
        }

        session()->put('cart', $cart);

        return $this->get();
    }

    public function remove(Request $request)
    {
        $key = (string) ($request->input('cart_key') ?? $request->input('product_id'));
        $cart = session()->get('cart', []);

        if (!isset($cart[$key])) {
            foreach ($cart as $k => $item) {
                if ((string)$k === $key || (string)($item['cart_key'] ?? '') === $key || (string)($item['id'] ?? '') === $key) {
                    $key = $k;
                    break;
                }
            }
        }

        if (isset($cart[$key])) {
            unset($cart[$key]);
            session()->put('cart', $cart);
        }

        return $this->get();
    }

    public function restore(Request $request)
    {
        $productId = (int) $request->input('product_id');
        $qty = max(1, (int) $request->input('quantity', $request->input('qty', 1)));
        $variant = $request->input('variant');

        $product = DB::table('products')
            ->where('id', $productId)
            ->where('is_active', 1)
            ->select('id', 'title', 'slug', 'price', 'old_price', 'main_image', 'variants')
            ->first();
        if (!$product) {
            return response()->json(['success' => false, 'message' => 'পণ্যটি পাওয়া যায়নি'], 404);
        }

        $cart = session()->get('cart', []);
        $cartKey = (string) $productId;
        if (!empty($variant)) {
            $cartKey = $productId . '_' . substr(md5($variant), 0, 6);
        }

        $itemPrice = $this->resolveProductPrice($product, $variant);

        $cart[$cartKey] = [
            'id' => $product->id,
            'cart_key' => $cartKey,
            'title' => $product->title,
            'slug' => $product->slug,
            'variant' => $variant,
            'price' => $itemPrice,
            'old_price' => (float) $product->old_price,
            'image' => $product->main_image,
            'qty' => $qty,
            'total' => $itemPrice * $qty,
        ];

        session()->put('cart', $cart);

        return $this->get();
    }

    public function clear()
    {
        session()->forget('cart');
        session()->forget('coupon');
        return $this->get();
    }

    public function applyCoupon(Request $request, \App\Services\Marketing\CouponService $couponService)
    {
        $code = $request->input('code', '');
        $cart = session()->get('cart', []);
        $subtotal = 0;
        foreach ($cart as $item) {
            $subtotal += ($item['price'] * $item['qty']);
        }

        $result = $couponService->validateAndApply($code, (float)$subtotal);

        if ($result['success']) {
            session()->put('coupon', [
                'id' => $result['coupon_id'],
                'code' => $result['code'],
                'discount' => $result['discount'],
            ]);
        }

        return response()->json($result);
    }

    public function removeCoupon()
    {
        session()->forget('coupon');
        return response()->json(['success' => true, 'message' => 'Coupon removed successfully.']);
    }
}
