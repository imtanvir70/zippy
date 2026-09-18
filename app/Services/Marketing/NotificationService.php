<?php

namespace App\Services\Marketing;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    /**
     * Send Order Confirmation Notification.
     */
    public function sendOrderPlacedNotification(object $order): array
    {
        $settings = $this->getSettings();
        $template = $settings['sms_order_placed_template'] ?? 'Dear {name}, your order #{order_number} of ৳{total} has been confirmed!';

        $message = str_replace(
            ['{name}', '{order_number}', '{total}'],
            [$order->customer_name, $order->order_number, number_format($order->total, 0)],
            $template
        );

        return $this->dispatchSms($order->customer_phone, $message, $settings);
    }

    /**
     * Send Order Shipped Notification with Tracking Info.
     */
    public function sendOrderShippedNotification(object $order): array
    {
        $settings = $this->getSettings();
        $template = $settings['sms_order_shipped_template'] ?? 'Dear {name}, your order #{order_number} has been dispatched via {courier}. Tracking: {tracking}.';

        $message = str_replace(
            ['{name}', '{order_number}', '{courier}', '{tracking}'],
            [$order->customer_name, $order->order_number, strtoupper($order->courier_provider ?? 'Courier'), $order->courier_tracking_code ?? 'N/A'],
            $template
        );

        return $this->dispatchSms($order->customer_phone, $message, $settings);
    }

    protected function dispatchSms(string $phone, string $message, array $settings): array
    {
        Log::info("SMS DISPATCH to [{$phone}]: {$message}");

        return [
            'success' => true,
            'phone' => $phone,
            'message' => $message,
            'provider' => $settings['sms_provider'] ?? 'bulksms',
            'status' => 'SENT_SIMULATED'
        ];
    }

    protected function getSettings(): array
    {
        $rows = DB::table('settings')->get();
        $settings = [];
        foreach ($rows as $r) {
            $settings[$r->key] = $r->value;
        }
        return $settings;
    }
}