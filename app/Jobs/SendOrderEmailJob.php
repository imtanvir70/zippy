<?php

namespace App\Jobs;

use App\Services\Mail\DynamicMailConfigService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class SendOrderEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;
    public int $backoff = 30;

    public int $orderId;

    public function __construct(int $orderId)
    {
        $this->orderId = $orderId;
    }

    public function handle(): void
    {
        $order = DB::table('orders')->where('id', $this->orderId)->first();
        if (!$order) {
            return;
        }

        $items = DB::table('order_items')->where('order_id', $this->orderId)->get();

        $targetEmail = null;
        if (!empty($order->guest_email)) {
            $targetEmail = trim($order->guest_email);
        } elseif (!empty($order->user_id)) {
            $targetEmail = DB::table('users')->where('id', $order->user_id)->value('email');
        }

        if (empty($targetEmail)) {
            $targetEmail = DB::table('settings')->where('key', 'admin_email')->value('value');
        }

        if (empty($targetEmail)) {
            return;
        }

        try {
            DynamicMailConfigService::apply();

            $storeName = DB::table('settings')->where('key', 'store_name')->value('value') ?: 'ZippyBD';
            $subject = "[{$storeName}] Order Confirmation #{$order->order_number}";

            $itemsText = "";
            foreach ($items as $item) {
                $itemsText .= "- {$item->product_title} x {$item->quantity} = ৳" . number_format((float)$item->total_price, 2) . "\n";
            }

            $body = "Hello {$order->customer_name},\n\n"
                . "Thank you for your order with {$storeName}.\n\n"
                . "Order Number: {$order->order_number}\n"
                . "Delivery Address: {$order->customer_address} ({$order->district})\n"
                . "Payment Method: " . strtoupper($order->payment_method ?: 'COD') . "\n\n"
                . "Items Ordered:\n" . $itemsText . "\n"
                . "Subtotal: ৳" . number_format((float)$order->subtotal, 2) . "\n"
                . "Shipping Cost: ৳" . number_format((float)$order->shipping_cost, 2) . "\n"
                . "Total Amount: ৳" . number_format((float)$order->total, 2) . "\n\n"
                . "We will contact you shortly before shipment.\n\n"
                . "Best regards,\n{$storeName} Team";

            Mail::raw($body, function ($message) use ($targetEmail, $subject, $storeName) {
                $message->to($targetEmail)
                    ->subject($subject);
            });
        } catch (Throwable $e) {
            Log::error('SendOrderEmailJob failed: ' . $e->getMessage(), [
                'order_id' => $this->orderId,
                'target_email' => $targetEmail
            ]);
        }
    }
}
