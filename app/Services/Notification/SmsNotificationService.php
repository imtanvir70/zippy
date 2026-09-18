<?php

namespace App\Services\Notification;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SmsNotificationService
{
    public function sendOrderStatusSms(object $order, string $newStatus, ?string $trackingUrl = null, ?string $trackingCode = null): bool
    {
        $setting = DB::table('sms_settings')->first();
        if (!$setting || !$setting->is_active || empty($setting->api_key)) {
            return false;
        }

        $phone = $order->customer_phone ?? null;
        if (empty($phone)) {
            return false;
        }

        $phone = preg_replace('/[^0-9]/', '', $phone);
        if (strlen($phone) === 11 && str_starts_with($phone, '01')) {
            $phone = '88' . $phone;
        }

        $trackUrl = $trackingUrl ?: route('order.track', ['track_id' => $order->order_number ?? '']);
        $trackCode = $trackingCode ?: ($order->courier_tracking_code ?? '');

        $template = null;
        if ($newStatus === 'pending' && $setting->notify_on_order_placed) {
            $template = $setting->order_placed_template ?: 'Dear {name}, your order #{order_number} has been received. Total: {total} BDT. Thank you!';
        } elseif ($newStatus === 'shipped' && $setting->notify_on_order_shipped) {
            $template = $setting->order_shipped_template ?: 'Dear {name}, your order #{order_number} has been shipped via {courier}. Track here: {tracking_url}';
        } elseif ($newStatus === 'delivered' && $setting->notify_on_order_delivered) {
            $template = $setting->order_delivered_template ?: 'Dear {name}, your order #{order_number} has been delivered successfully. Thank you for shopping with us!';
        }

        if (!$template) {
            return false;
        }

        $replacements = [
            '{name}' => $order->customer_name ?? 'Customer',
            '{order_number}' => $order->order_number ?? '',
            '{total}' => number_format((float)($order->total ?? 0), 0),
            '{courier}' => strtoupper($order->courier_provider ?? 'Courier'),
            '{tracking_code}' => $trackCode,
            '{tracking_url}' => $trackUrl,
            '{status}' => ucfirst($newStatus),
        ];

        $message = str_replace(array_keys($replacements), array_values($replacements), $template);

        try {
            $apiUrl = $setting->api_url ?: 'http://api.greenweb.com.bd/api.php';
            $response = Http::timeout(5)->get($apiUrl, [
                'token' => $setting->api_key,
                'to' => $phone,
                'message' => $message,
            ]);

            return $response->successful();
        } catch (\Throwable $e) {
            Log::error('SMS Dispatch Error: ' . $e->getMessage(), ['order_id' => $order->id ?? null]);
            return false;
        }
    }
}
