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

class SendOrderConfirmationSmsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 15;

    public int $orderId;

    public function __construct(int $orderId)
    {
        $this->orderId = $orderId;
    }

    public function handle(SmsNotificationService $smsService): void
    {
        $order = DB::table('orders')->where('id', $this->orderId)->first();
        if (!$order) {
            return;
        }

        try {
            $smsService->sendOrderStatusSms($order, 'pending');
        } catch (Throwable $e) {
            Log::error('SendOrderConfirmationSmsJob failure: ' . $e->getMessage(), [
                'order_id' => $this->orderId,
            ]);
            throw $e;
        }
    }
}
