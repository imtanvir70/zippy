<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Services\Auth\DeviceTrackingService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class GuestOrderTrackingController extends Controller
{
    protected DeviceTrackingService $deviceTrackingService;

    public function __construct(DeviceTrackingService $deviceTrackingService)
    {
        $this->deviceTrackingService = $deviceTrackingService;
    }

    public function track(Request $request)
    {
        $query = trim($request->input('track_id') ?: ($request->input('query') ?: $request->input('tracking_input', '')));
        $order = null;
        $items = collect();
        $relatedOrders = collect();

        if ($query) {
            $cleanPhone = preg_replace('/[^0-9]/', '', $query);
            if (str_starts_with($cleanPhone, '880') && strlen($cleanPhone) === 13) {
                $cleanPhone = '0' . substr($cleanPhone, 3);
            }

            $order = DB::table('orders')
                ->where(function ($q) use ($query, $cleanPhone) {
                    $q->where('order_number', $query)
                        ->orWhere('customer_phone', $query)
                        ->orWhere('tracking_code', $query)
                        ->orWhere('courier_tracking_code', $query)
                        ->orWhere('consignment_id', $query)
                        ->orWhere('courier_consignment_id', $query);

                    if ($cleanPhone && $cleanPhone !== $query) {
                        $q->orWhere('customer_phone', $cleanPhone);
                    }
                })
                ->orderByDesc('id')
                ->first();

            if ($order) {
                $items = DB::table('order_items')->where('order_id', $order->id)->get();

                $phoneToMatch = $order->customer_phone;
                if ($query === $phoneToMatch || ($cleanPhone && $cleanPhone === $phoneToMatch)) {
                    $related = DB::table('orders')
                        ->where('customer_phone', $phoneToMatch)
                        ->where('id', '!=', $order->id)
                        ->orderByDesc('id')
                        ->limit(5)
                        ->get();

                    if ($related->isNotEmpty()) {
                        $relatedIds = $related->pluck('id')->toArray();
                        $relatedItems = DB::table('order_items')->whereIn('order_id', $relatedIds)->get()->groupBy('order_id');
                        foreach ($related as $rel) {
                            $rel->items = $relatedItems->get($rel->id, collect());
                        }
                    }
                    $relatedOrders = $related;
                }
            }
        }

        $deviceToken = $this->deviceTrackingService->resolveDeviceToken($request);
        $deviceOrders = $this->deviceTrackingService->getDeviceOrders($deviceToken);

        if ($request->wantsJson() && !$request->hasHeader('X-Tracking-HTML')) {
            return response()->json([
                'success' => $order ? true : false,
                'order' => $order,
                'items' => $items,
                'related_orders' => $relatedOrders,
            ]);
        }

        return view('frontend.order.track', compact('order', 'items', 'query', 'relatedOrders', 'deviceOrders', 'deviceToken'));
    }

    public function history(Request $request)
    {
        $deviceToken = $this->deviceTrackingService->resolveDeviceToken($request);
        $user = Auth::user();

        if ($user) {
            $orders = DB::table('orders')
                ->where('user_id', $user->id)
                ->orWhere('customer_phone', $user->phone)
                ->orderByDesc('id')
                ->limit(50)
                ->select(
                    'id',
                    'order_number',
                    'user_id',
                    'customer_name',
                    'customer_phone',
                    'total',
                    'order_status',
                    'payment_method',
                    'payment_status',
                    'created_at'
                )
                ->get();

            if ($orders->isNotEmpty()) {
                $orderIds = $orders->pluck('id')->toArray();
                $items = DB::table('order_items')->whereIn('order_id', $orderIds)->get()->groupBy('order_id');
                foreach ($orders as $order) {
                    $order->items = $items->get($order->id, collect());
                }
            }
            $isGuest = false;
        } else {
            $orders = $this->deviceTrackingService->getDeviceOrders($deviceToken);
            $isGuest = true;
        }

        return view('frontend.order.history', compact('orders', 'isGuest', 'deviceToken', 'user'))
            ->withCookie($this->deviceTrackingService->makeCookie($deviceToken));
    }

    public function apiGetDeviceOrders(Request $request)
    {
        $deviceToken = $request->input('device_token') ?: $this->deviceTrackingService->resolveDeviceToken($request);
        $orders = $this->deviceTrackingService->getDeviceOrders($deviceToken);

        return response()->json([
            'success' => true,
            'device_token' => $deviceToken,
            'count' => $orders->count(),
            'orders' => $orders->map(function ($o) {
                $createdAtFormatted = $o->created_at ? Carbon::parse($o->created_at)->format('d M, Y h:i A') : '';
                return [
                    'id' => $o->id,
                    'order_number' => $o->order_number,
                    'customer_name' => $o->customer_name,
                    'customer_phone' => $o->customer_phone,
                    'total' => (float) $o->total,
                    'status' => $o->order_status,
                    'payment_method' => strtoupper($o->payment_method),
                    'payment_status' => $o->payment_status,
                    'items_count' => isset($o->items) ? $o->items->count() : 0,
                    'created_at' => $createdAtFormatted,
                    'tracking_url' => route('order.track', ['query' => $o->order_number]),
                ];
            }),
        ]);
    }

    public function apiClearDeviceHistory(Request $request)
    {
        $deviceToken = $request->input('device_token') ?: $this->deviceTrackingService->resolveDeviceToken($request);
        $this->deviceTrackingService->clearDeviceHistory($deviceToken);

        $newToken = $this->deviceTrackingService->generateToken();
        session()->put(DeviceTrackingService::COOKIE_NAME, $newToken);

        return response()->json([
            'success' => true,
            'message' => 'এই ডিভাইসের পূর্ববর্তী অর্ডার হিস্ট্রি সফলভাবে ক্লিয়ার করা হয়েছে।',
            'new_device_token' => $newToken,
        ])->withCookie($this->deviceTrackingService->makeCookie($newToken));
    }
}
