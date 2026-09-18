<?php

namespace App\Services\Auth;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DeviceTrackingService
{
    const COOKIE_NAME = 'zb_device_token';
    const COOKIE_LIFETIME_MINUTES = 60 * 24 * 365; // 1 Year

    /**
     * Resolve or generate a secure device token from Request.
     */
    public function resolveDeviceToken(Request $request): string
    {
        $token = $request->header('X-Device-Token')
            ?: $request->input('device_token')
            ?: $request->cookie(self::COOKIE_NAME)
            ?: session()->get(self::COOKIE_NAME);

        if (!$token || !is_string($token) || strlen($token) < 10) {
            $token = $this->generateToken();
            session()->put(self::COOKIE_NAME, $token);
        }

        return trim($token);
    }

    /**
     * Generate a cryptographically secure device identifier.
     */
    public function generateToken(): string
    {
        return 'zb_dev_' . (string) Str::uuid();
    }

    /**
     * Attach a device cookie to a Response.
     */
    public function makeCookie(string $deviceToken)
    {
        return Cookie::make(
            self::COOKIE_NAME,
            $deviceToken,
            self::COOKIE_LIFETIME_MINUTES,
            '/',
            null,
            false, // secure
            false, // httpOnly false so frontend JS localStorage can sync
            false,
            'Lax'
        );
    }

    /**
     * Record or update device activity upon checkout using Query Builder.
     */
    public function recordOrder(string $deviceToken, $order, $user = null): object
    {
        $ip = request()->ip() ?? '127.0.0.1';
        $ua = request()->header('User-Agent') ?? 'Browser';

        $existing = DB::table('guest_devices')->where('device_token', $deviceToken)->first();

        $customerPhone = is_object($order) ? ($order->customer_phone ?? null) : ($order['customer_phone'] ?? null);
        $customerName = is_object($order) ? ($order->customer_name ?? null) : ($order['customer_name'] ?? null);
        $total = is_object($order) ? ($order->total ?? 0) : ($order['total'] ?? 0);
        $userId = $user ? (is_object($user) ? $user->id : $user) : null;

        if ($existing) {
            DB::table('guest_devices')->where('device_token', $deviceToken)->update([
                'ip_address' => $ip,
                'user_agent' => $ua,
                'last_phone' => $customerPhone ?: $existing->last_phone,
                'last_name' => $customerName ?: $existing->last_name,
                'total_orders' => ($existing->total_orders ?? 0) + 1,
                'total_spent' => ($existing->total_spent ?? 0) + (float) $total,
                'user_id' => $userId ?: $existing->user_id,
                'last_active_at' => now(),
                'unlinked_at' => null,
                'updated_at' => now(),
            ]);
        } else {
            DB::table('guest_devices')->insert([
                'device_token' => $deviceToken,
                'user_id' => $userId,
                'ip_address' => $ip,
                'user_agent' => $ua,
                'last_phone' => $customerPhone,
                'last_name' => $customerName,
                'total_orders' => 1,
                'total_spent' => (float) $total,
                'last_active_at' => now(),
                'unlinked_at' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return DB::table('guest_devices')->where('device_token', $deviceToken)->first();
    }

    /**
     * Fetch order history for a device token using Query Builder.
     */
    public function getDeviceOrders(string $deviceToken): Collection
    {
        if (empty($deviceToken)) {
            return collect();
        }

        // Check if device is unlinked/cleared
        $device = DB::table('guest_devices')->where('device_token', $deviceToken)->first();
        if ($device && $device->unlinked_at !== null) {
            return collect();
        }

        $orders = DB::table('orders')
            ->where('device_token', $deviceToken)
            ->orderByDesc('id')
            ->get();

        if ($orders->isEmpty()) {
            return collect();
        }

        $orderIds = $orders->pluck('id')->toArray();
        $items = DB::table('order_items')
            ->whereIn('order_id', $orderIds)
            ->get()
            ->groupBy('order_id');

        foreach ($orders as $order) {
            $order->items = $items->get($order->id, collect());
        }

        return $orders;
    }

    /**
     * Clear / unlink device order history on local device using Query Builder.
     */
    public function clearDeviceHistory(string $deviceToken): bool
    {
        if (empty($deviceToken)) {
            return false;
        }

        DB::table('guest_devices')->where('device_token', $deviceToken)->update([
            'unlinked_at' => now(),
            'updated_at' => now(),
        ]);

        return true;
    }

    /**
     * Claim past guest orders to a registered user account using Query Builder.
     */
    public function claimOrdersToUser(int $userId, ?string $deviceToken = null, ?string $phone = null): int
    {
        if (empty($deviceToken) && empty($phone)) {
            return 0;
        }

        return DB::table('orders')
            ->whereNull('user_id')
            ->where(function ($q) use ($deviceToken, $phone) {
                if ($deviceToken) {
                    $q->where('device_token', $deviceToken);
                }
                if ($phone) {
                    $q->orWhere('customer_phone', $phone);
                }
            })
            ->update([
                'user_id' => $userId,
                'is_guest' => 0,
                'updated_at' => now(),
            ]);
    }
}
