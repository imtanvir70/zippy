<?php

namespace App\Jobs;

use App\Services\Notification\SmsNotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class SendOrderSmsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 15;

    public int $orderId;
    public string $status;

    public function __construct(int $orderId, string $status = 'pending')
    {
        $this->orderId = $orderId;
        $this->status = $status;
    }

    public function handle(SmsNotificationService $smsService): void
    {
        $order = DB::table('orders')->where('id', $this->orderId)->first();
        if (!$order) {
            return;
        }

        try {
            $setting = DB::table('sms_settings')->first();
            if (!$setting || !$setting->is_active || empty($setting->api_key)) {
                return;
            }

            $sent = $smsService->sendOrderStatusSms($order, $this->status);
            if (!$sent) {
                throw new \RuntimeException("SMS dispatch failed for order #{$this->orderId}");
            }
        } catch (Throwable $e) {
            Log::error('SendOrderSmsJob failure: ' . $e->getMessage(), [
                'order_id' => $this->orderId,
                'status' => $this->status
            ]);
            throw $e;
        }
    }
}
