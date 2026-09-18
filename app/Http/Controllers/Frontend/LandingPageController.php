<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Services\Auth\DeviceTrackingService;
use App\Services\Frontend\FrontendCacheService;
use App\Services\Review\ReviewService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LandingPageController extends Controller
{
    protected DeviceTrackingService $deviceTrackingService;

    public function __construct(DeviceTrackingService $deviceTrackingService)
    {
        $this->deviceTrackingService = $deviceTrackingService;
    }

    public function show(Request $request, $slug)
    {
        $product = DB::table('products')
            ->where('slug', $slug)
            ->where('is_active', 1)
            ->first();

        if (!$product) {
            abort(404, 'প্রোডাক্টটি পাওয়া যায়নি।');
        }

        $sessionCart = [
            $product->id => [
                'id' => $product->id,
                'cart_key' => (string) $product->id,
                'title' => $product->title,
                'name' => $product->title,
                'price' => (float) $product->price,
                'quantity' => 1,
                'qty' => 1,
                'image' => $product->main_image,
                'attributes' => ['image' => $product->main_image],
                'variant' => null,
            ]
        ];
        session()->put('cart', $sessionCart);

        $galleryImages = is_array($product->gallery_images) ? $product->gallery_images : (json_decode($product->gallery_images ?? '', true) ?: []);
        if (is_string($galleryImages)) {
            $galleryImages = json_decode($galleryImages, true) ?: [];
        }
        if (!is_array($galleryImages) || empty($galleryImages)) {
            $galleryImages = [$product->main_image];
        }

        $specifications = is_array($product->specifications) ? $product->specifications : (json_decode($product->specifications ?? '', true) ?: []);
        if (is_string($specifications)) {
            $specifications = json_decode($specifications, true) ?: [];
        }
        if (!is_array($specifications)) {
            $specifications = [];
        }

        $stats = ReviewService::getProductReviewStats($product->id);
        $product->rating = $stats['rating'];
        $product->reviews_count = $stats['reviews_count'];
        $reviews = ReviewService::getApprovedReviews($product->id, 8);

        $orderBump = DB::table('order_bumps')
            ->join('products', 'products.id', '=', 'order_bumps.bump_product_id')
            ->where('order_bumps.is_active', 1)
            ->where('products.is_active', 1)
            ->where('products.stock_qty', '>', 0)
            ->where(function ($q) use ($product) {
                $q->where('order_bumps.product_id', $product->id)
                    ->orWhereNull('order_bumps.product_id');
            })
            ->where('order_bumps.bump_product_id', '!=', $product->id)
            ->select(
                'order_bumps.id',
                'order_bumps.bump_product_id',
                'order_bumps.title',
                'order_bumps.description',
                'order_bumps.price',
                'products.title as bump_product_title',
                'products.price as original_price',
                'products.main_image as bump_image'
            )
            ->orderByRaw('CASE WHEN order_bumps.product_id IS NOT NULL THEN 0 ELSE 1 END')
            ->orderBy('order_bumps.sort_order', 'asc')
            ->first();

        $settings = FrontendCacheService::settings();
        $shippingInside = (float) ($settings['shipping_inside_dhaka'] ?? 60);
        $shippingOutside = (float) ($settings['shipping_outside_dhaka'] ?? 120);
        $divisions = DB::table('divisions')->select('id', 'name', 'bn_name')->orderBy('id')->get();
        $deviceToken = $this->deviceTrackingService->resolveDeviceToken($request);

        return view('frontend.landing.show', compact(
            'product',
            'galleryImages',
            'specifications',
            'reviews',
            'orderBump',
            'settings',
            'shippingInside',
            'shippingOutside',
            'divisions',
            'deviceToken'
        ))->withCookie($this->deviceTrackingService->makeCookie($deviceToken));
    }
}
