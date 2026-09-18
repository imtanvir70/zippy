<?php

namespace App\Services\Notification;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class NotificationCommerceService
{
    public function sendOrderStatusUpdate(object $order, string $newStatus): bool
    {
        $setting = DB::table('notification_settings')->first();
        if (!$setting || !$setting->notify_on_order_status) {
            return false;
        }

        $phone = $this->formatPhoneNumber($order->customer_phone ?? '');
        if (empty($phone)) {
            return false;
        }

        $template = $setting->order_status_template ?: 'Hello {name}, your order #{order_number} status is now {status}. Total: ৳{total}. Thank you!';
        $replacements = [
            '{name}' => $order->customer_name ?? 'Customer',
            '{order_number}' => $order->order_number ?? (string)($order->id ?? ''),
            '{status}' => ucfirst($newStatus),
            '{total}' => number_format((float)($order->total ?? 0), 0),
        ];
        $message = str_replace(array_keys($replacements), array_values($replacements), $template);

        $sent = false;
        if ($setting->sms_enabled && !empty($setting->sms_api_key)) {
            $sent = $this->sendSms($setting, $phone, $message) || $sent;
        }
        if ($setting->whatsapp_enabled && !empty($setting->whatsapp_api_url)) {
            $sent = $this->sendWhatsApp($setting, $phone, $message) || $sent;
        }

        return $sent;
    }

    public function sendAbandonedCartReminder(object $cart): bool
    {
        $setting = DB::table('notification_settings')->first();
        if (!$setting || !$setting->notify_on_abandoned_cart) {
            return false;
        }

        $phone = $this->formatPhoneNumber($cart->customer_phone ?? '');
        if (empty($phone)) {
            return false;
        }

        $template = $setting->abandoned_cart_template ?: 'Hi {name}, you left items in your cart! Complete your order now and enjoy special discounts: {checkout_url}';
        $replacements = [
            '{name}' => $cart->customer_name ?? 'Customer',
            '{checkout_url}' => url('/checkout'),
        ];
        $message = str_replace(array_keys($replacements), array_values($replacements), $template);

        $sent = false;
        if ($setting->sms_enabled && !empty($setting->sms_api_key)) {
            $sent = $this->sendSms($setting, $phone, $message) || $sent;
        }
        if ($setting->whatsapp_enabled && !empty($setting->whatsapp_api_url)) {
            $sent = $this->sendWhatsApp($setting, $phone, $message) || $sent;
        }

        return $sent;
    }

    protected function sendSms(object $setting, string $phone, string $message): bool
    {
        try {
            $url = $setting->sms_api_url ?: 'http://api.greenweb.com.bd/api.php';
            $response = Http::timeout(6)->get($url, [
                'token' => $setting->sms_api_key,
                'to' => $phone,
                'message' => $message,
            ]);
            return $response->successful();
        } catch (\Throwable $e) {
            Log::error('SMS Notification Error: ' . $e->getMessage());
            return false;
        }
    }

    protected function sendWhatsApp(object $setting, string $phone, string $message): bool
    {
        try {
            $url = rtrim($setting->whatsapp_api_url, '/');
            $response = Http::timeout(6)
                ->withToken($setting->whatsapp_api_token)
                ->post($url, [
                    'to' => $phone,
                    'message' => $message,
                ]);
            return $response->successful();
        } catch (\Throwable $e) {
            Log::error('WhatsApp Notification Error: ' . $e->getMessage());
            return false;
        }
    }

    protected function formatPhoneNumber(string $phone): string
    {
        $phone = preg_replace('/[^0-9]/', '', $phone);
        if (strlen($phone) === 11 && str_starts_with($phone, '01')) {
            $phone = '88' . $phone;
        }
        return $phone;
    }
}
