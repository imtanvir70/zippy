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

class SendOrderDispatchedSmsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 15;

    public int $orderId;
    public ?string $trackingUrl;
    public ?string $trackingCode;

    public function __construct(int $orderId, ?string $trackingUrl = null, ?string $trackingCode = null)
    {
        $this->orderId = $orderId;
        $this->trackingUrl = $trackingUrl;
        $this->trackingCode = $trackingCode;
    }

    public function handle(SmsNotificationService $smsService): void
    {
        $order = DB::table('orders')->where('id', $this->orderId)->first();
        if (!$order) {
            return;
        }

        try {
            $smsService->sendOrderStatusSms($order, 'shipped', $this->trackingUrl, $this->trackingCode);
        } catch (Throwable $e) {
            Log::error('SendOrderDispatchedSmsJob failure: ' . $e->getMessage(), [
                'order_id' => $this->orderId,
            ]);
            throw $e;
        }
    }
}
