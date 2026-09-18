<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Services\Frontend\FrontendCacheService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderTrackingController extends Controller
{
    public function success($orderNumber)
    {
        $order = DB::table('orders')->where('order_number', $orderNumber)->first();
        if (!$order) {
            abort(404, 'অর্ডার পাওয়া যায়নি');
        }

        $sessionLastOrder = session('last_order_code');
        $sessionDeviceToken = session('device_token') ?: (request()->cookie('zb_device_token') ?: request()->cookie('device_token'));
        $isOrderOwner = auth()->check() && !empty($order->user_id) && ((int) $order->user_id === (int) auth()->id());
        $isSessionOrder = !empty($sessionLastOrder) && $sessionLastOrder === $orderNumber;
        $isDeviceOrder = !empty($sessionDeviceToken) && !empty($order->device_token) && $order->device_token === $sessionDeviceToken;

        if (!$isSessionOrder && !$isOrderOwner && !$isDeviceOrder && !app()->environment('local')) {
            return redirect()->route('order.track', ['query' => $orderNumber])
                ->with('info', 'অর্ডারের বিস্তারিত দেখতে আপনার ফোন নম্বর বা অর্ডার কোড দিয়ে ট্র্যাক করুন।');
        }

        $items = DB::table('order_items')->where('order_id', $order->id)->get();
        $settings = FrontendCacheService::settings();
        $whatsappPhone = $settings['whatsapp_number'] ?? '8801700000000';

        $waText = "হ্যালো ZippyBD! আমি নতুন অর্ডার করেছি।\n"
            . "অর্ডার নং: {$order->order_number}\n"
            . "নাম: {$order->customer_name}\n"
            . "ফোন: {$order->customer_phone}\n"
            . "মোট মূল্য: ৳ " . number_format($order->total, 0) . "\n"
            . "পেমেন্ট: " . strtoupper($order->payment_method);

        $whatsappUrl = "https://wa.me/{$whatsappPhone}?text=" . urlencode($waText);

        $viewName = view()->exists('frontend.order.success') ? 'frontend.order.success' : 'frontend.checkout.success';
        return view($viewName, compact('order', 'items', 'whatsappUrl', 'settings'));
    }

    public function track(Request $request)
    {
        $query = trim((string) ($request->input('query') ?: $request->input('track_id', '')));
        $order = null;
        $items = collect();

        if ($query) {
            $normalizedQuery = strtoupper(trim($query));
            $order = DB::table('orders')
                ->where('order_number', $query)
                ->orWhere('order_number', $normalizedQuery)
                ->orWhere('order_number', 'ZB-' . $normalizedQuery)
                ->orWhere('customer_phone', $query)
                ->orderBy('created_at', 'desc')
                ->first();

            if ($order) {
                $items = DB::table('order_items')->where('order_id', $order->id)->get();
            }
        }

        return view('frontend.order.track', compact('order', 'items', 'query'));
    }
}
