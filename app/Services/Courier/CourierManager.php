<?php

namespace App\Services\Courier;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use Illuminate\Http\Request;
use App\Services\Audit\AuditLoggerService;

class CourierManager
{
    protected array $providers = [
        'steadfast' => SteadfastService::class,
        'pathao' => PathaoService::class,
        'redx' => RedxService::class,
    ];

    public function resolve(string $providerName): CourierServiceInterface
    {
        $providerName = strtolower(trim($providerName));
        $class = $this->providers[$providerName] ?? SteadfastService::class;
        return new $class();
    }

    public function getProviders(): array
    {
        return array_keys($this->providers);
    }

    public function normalizeStatus(string $provider, string $rawStatus): array
    {
        $provider = strtolower(trim($provider));
        $statusKey = strtolower(trim($rawStatus));
        $statusKey = str_replace(['-', ' '], '_', $statusKey);

        $normalized = [
            'delivery_status' => 'in_transit',
            'order_status' => 'shipped',
            'title' => 'In Transit',
            'message' => 'Parcel is on the way to the delivery address.'
        ];

        if ($provider === 'steadfast') {
            switch ($statusKey) {
                case 'in_review':
                case 'pending':
                    $normalized = [
                        'delivery_status' => 'processing',
                        'order_status' => 'processing',
                        'title' => 'Processing at Courier',
                        'message' => 'Consignment received by Steadfast and is being prepared.'
                    ];
                    break;
                case 'picked_up':
                case 'in_transit':
                case 'dispatched':
                case 'hold':
                    $normalized = [
                        'delivery_status' => 'in_transit',
                        'order_status' => 'shipped',
                        'title' => 'Handed to Courier',
                        'message' => 'Parcel is in transit between Steadfast distribution hubs.'
                    ];
                    break;
                case 'out_for_delivery':
                case 'rider_assigned':
                    $normalized = [
                        'delivery_status' => 'out_for_delivery',
                        'order_status' => 'shipped',
                        'title' => 'Out for Delivery',
                        'message' => 'Steadfast delivery hero is en route to your address.'
                    ];
                    break;
                case 'delivered':
                case 'partial_delivered':
                case 'completed':
                case 'successful':
                    $normalized = [
                        'delivery_status' => 'delivered',
                        'order_status' => 'delivered',
                        'title' => 'Delivered',
                        'message' => 'Parcel successfully delivered to the recipient.'
                    ];
                    break;
                case 'cancelled':
                case 'canceled':
                    $normalized = [
                        'delivery_status' => 'cancelled',
                        'order_status' => 'cancelled',
                        'title' => 'Cancelled',
                        'message' => 'Consignment delivery was cancelled.'
                    ];
                    break;
                case 'return':
                case 'returned':
                case 'return_in_transit':
                case 'return_completed':
                case 'failed':
                    $normalized = [
                        'delivery_status' => 'returned',
                        'order_status' => 'cancelled',
                        'title' => 'Returned',
                        'message' => 'Parcel could not be delivered and is returned.'
                    ];
                    break;
            }
        } elseif ($provider === 'pathao') {
            switch ($statusKey) {
                case 'order_created':
                    $normalized = [
                        'delivery_status' => 'pending',
                        'order_status' => 'confirmed',
                        'title' => 'Order Created',
                        'message' => 'Booking request placed with Pathao Logistics.'
                    ];
                    break;
                case 'pickup_requested':
                case 'pickup_pending':
                    $normalized = [
                        'delivery_status' => 'processing',
                        'order_status' => 'processing',
                        'title' => 'Pickup Pending',
                        'message' => 'Waiting for Pathao hero to pick up from warehouse.'
                    ];
                    break;
                case 'in_transit':
                case 'picked_up':
                case 'received_at_sorting_hub':
                case 'on_the_way':
                    $normalized = [
                        'delivery_status' => 'in_transit',
                        'order_status' => 'shipped',
                        'title' => 'Handed to Courier',
                        'message' => 'Parcel is traveling through Pathao logistics network.'
                    ];
                    break;
                case 'out_for_delivery':
                case 'assigned_for_delivery':
                    $normalized = [
                        'delivery_status' => 'out_for_delivery',
                        'order_status' => 'shipped',
                        'title' => 'Out for Delivery',
                        'message' => 'Pathao rider is out for delivery to destination.'
                    ];
                    break;
                case 'delivered':
                case 'successful':
                case 'paid':
                    $normalized = [
                        'delivery_status' => 'delivered',
                        'order_status' => 'delivered',
                        'title' => 'Delivered',
                        'message' => 'Parcel safely handed over by Pathao rider.'
                    ];
                    break;
                case 'return':
                case 'returned':
                case 'return_received_at_hub':
                case 'returned_to_merchant':
                    $normalized = [
                        'delivery_status' => 'returned',
                        'order_status' => 'cancelled',
                        'title' => 'Returned',
                        'message' => 'Consignment returned back to merchant.'
                    ];
                    break;
                case 'cancelled':
                case 'failed':
                    $normalized = [
                        'delivery_status' => 'cancelled',
                        'order_status' => 'cancelled',
                        'title' => 'Cancelled',
                        'message' => 'Pathao shipment was cancelled.'
                    ];
                    break;
            }
        } elseif ($provider === 'redx') {
            switch ($statusKey) {
                case 'pickup_pending':
                case 'ready_for_pickup':
                    $normalized = [
                        'delivery_status' => 'processing',
                        'order_status' => 'processing',
                        'title' => 'Pickup Pending',
                        'message' => 'RedX rider dispatch requested for package pickup.'
                    ];
                    break;
                case 'picked_up':
                case 'dispatched':
                case 'sorting_hub':
                case 'in_transit':
                case 'hub_received':
                    $normalized = [
                        'delivery_status' => 'in_transit',
                        'order_status' => 'shipped',
                        'title' => 'Handed to Courier',
                        'message' => 'Parcel received at RedX sorting hub and dispatched.'
                    ];
                    break;
                case 'out_for_delivery':
                case 'out_for_delivery_hub':
                    $normalized = [
                        'delivery_status' => 'out_for_delivery',
                        'order_status' => 'shipped',
                        'title' => 'Out for Delivery',
                        'message' => 'RedX delivery executive is on the way.'
                    ];
                    break;
                case 'delivered':
                case 'completed':
                    $normalized = [
                        'delivery_status' => 'delivered',
                        'order_status' => 'delivered',
                        'title' => 'Delivered',
                        'message' => 'Parcel delivered by RedX logistics.'
                    ];
                    break;
                case 'returned':
                case 'return_completed':
                case 'return_hub':
                    $normalized = [
                        'delivery_status' => 'returned',
                        'order_status' => 'cancelled',
                        'title' => 'Returned',
                        'message' => 'Parcel returned to warehouse through RedX.'
                    ];
                    break;
                case 'cancelled':
                case 'failed':
                    $normalized = [
                        'delivery_status' => 'cancelled',
                        'order_status' => 'cancelled',
                        'title' => 'Cancelled',
                        'message' => 'RedX delivery request was cancelled.'
                    ];
                    break;
            }
        }

        return $normalized;
    }

    public function getTrackingUrl(string $provider, ?string $trackingCode, ?string $consignmentId = null): string
    {
        $provider = strtolower(trim($provider));
        if ($provider === 'steadfast' && $trackingCode) {
            return "https://steadfast.com.bd/t/" . urlencode($trackingCode);
        }
        if ($provider === 'pathao') {
            $id = $consignmentId ?: $trackingCode;
            if ($id) {
                return "https://merchant.pathao.com/tracking?consignment_id=" . urlencode($id);
            }
        }
        if ($provider === 'redx') {
            $id = $trackingCode ?: $consignmentId;
            if ($id) {
                return "https://redx.com.bd/track-order?trackingId=" . urlencode($id);
            }
        }
        return route('order.track') . ($trackingCode ? '?track_id=' . urlencode($trackingCode) : '');
    }

    public function dispatchOrder(int $orderId, string $providerName, ?AuditLoggerService $auditLogger = null): array
    {
        return DB::transaction(function () use ($orderId, $providerName, $auditLogger) {
            $order = DB::table('orders')->where('id', $orderId)->lockForUpdate()->first();
            if (!$order) {
                return ['success' => false, 'message' => 'Order not found.'];
            }

            $settingsRows = DB::table('settings')->get();
            $config = [];
            foreach ($settingsRows as $row) {
                $config[$row->key] = $row->value;
            }

            $service = $this->resolve($providerName);
            $result = $service->createConsignment($order, $config);

            if ($result['success']) {
                $trackingCode = $result['tracking_code'] ?? null;
                $consignmentId = $result['consignment_id'] ?? null;
                $rawStatus = $result['status'] ?? 'in_transit';

                $normalized = $this->normalizeStatus($providerName, $rawStatus);

                $history = [];
                if (!empty($order->courier_history)) {
                    $decoded = json_decode($order->courier_history, true);
                    if (is_array($decoded)) {
                        $history = $decoded;
                    }
                }

                if (empty($history)) {
                    $history[] = [
                        'status' => 'pending',
                        'delivery_status' => 'pending',
                        'title' => 'Order Placed',
                        'message' => 'Order was placed and confirmed.',
                        'location' => 'Online Store',
                        'timestamp' => $order->created_at ? Carbon::parse($order->created_at)->format('Y-m-d H:i:s') : now()->format('Y-m-d H:i:s')
                    ];
                }

                $history[] = [
                    'status' => $rawStatus,
                    'delivery_status' => $normalized['delivery_status'],
                    'title' => 'Handed to Courier (' . ucfirst($providerName) . ')',
                    'message' => 'Parcel booked with tracking code: ' . $trackingCode,
                    'location' => 'Central Warehouse',
                    'timestamp' => now()->format('Y-m-d H:i:s')
                ];

                DB::table('orders')->where('id', $orderId)->update([
                    'courier_name' => $providerName,
                    'courier_provider' => $providerName,
                    'consignment_id' => $consignmentId,
                    'courier_consignment_id' => $consignmentId,
                    'tracking_code' => $trackingCode,
                    'courier_tracking_code' => $trackingCode,
                    'courier_status' => $rawStatus,
                    'delivery_status' => $normalized['delivery_status'],
                    'order_status' => 'shipped',
                    'courier_history' => json_encode($history),
                    'updated_at' => now(),
                ]);

                DB::table('courier_consignments')->insert([
                    'order_id' => $orderId,
                    'provider' => $providerName,
                    'consignment_id' => $consignmentId,
                    'tracking_code' => $trackingCode,
                    'status' => $rawStatus,
                    'cod_amount' => $order->payment_method === 'cod' ? $order->total : 0.00,
                    'request_payload' => json_encode(['order_number' => $order->order_number, 'amount' => $order->total]),
                    'response_payload' => json_encode($result['raw'] ?? []),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                if ($auditLogger) {
                    $auditLogger->logAction('courier_push', 'orders', $orderId, [
                        'previous_status' => $order->order_status,
                    ], [
                        'courier_provider' => $providerName,
                        'courier_name' => $providerName,
                        'tracking_code' => $trackingCode,
                        'consignment_id' => $consignmentId,
                        'order_status' => 'shipped'
                    ], "Dispatched order #{$order->order_number} to {$providerName} (Tracking: {$trackingCode})");
                }

                try {
                    $trackingUrl = route('order.track', ['track_id' => $order->order_number]);
                    \App\Jobs\SendOrderDispatchedSmsJob::dispatch((int) $orderId, $trackingUrl, $trackingCode);
                } catch (\Throwable $e) {
                }

                return [
                    'success' => true,
                    'message' => "Order successfully dispatched to {$providerName}. Tracking Code: {$trackingCode}",
                    'tracking_code' => $trackingCode,
                    'consignment_id' => $consignmentId,
                    'status' => $rawStatus
                ];
            }

            return $result;
        });
    }

    public function updateOrderCourierStatus(
        $orderIdentifier,
        string $provider,
        string $rawStatus,
        ?string $consignmentId = null,
        ?string $trackingCode = null,
        ?string $note = null,
        ?string $location = null
    ): array {
        return DB::transaction(function () use ($orderIdentifier, $provider, $rawStatus, $consignmentId, $trackingCode, $note, $location) {
            $query = DB::table('orders');
            if (is_numeric($orderIdentifier)) {
                $query->where('id', (int)$orderIdentifier);
            } else {
                $query->where(function ($q) use ($orderIdentifier) {
                    $q->where('tracking_code', $orderIdentifier)
                        ->orWhere('courier_tracking_code', $orderIdentifier)
                        ->orWhere('consignment_id', $orderIdentifier)
                        ->orWhere('courier_consignment_id', $orderIdentifier)
                        ->orWhere('order_number', $orderIdentifier);
                });
            }

            $order = $query->lockForUpdate()->first();

            if (!$order && $trackingCode) {
                $order = DB::table('orders')
                    ->where('tracking_code', $trackingCode)
                    ->orWhere('courier_tracking_code', $trackingCode)
                    ->lockForUpdate()
                    ->first();
            }

            if (!$order && $consignmentId) {
                $order = DB::table('orders')
                    ->where('consignment_id', $consignmentId)
                    ->orWhere('courier_consignment_id', $consignmentId)
                    ->lockForUpdate()
                    ->first();
            }

            if (!$order) {
                return ['success' => false, 'message' => 'Order not found for status update.'];
            }

            $normalized = $this->normalizeStatus($provider, $rawStatus);

            $history = [];
            if (!empty($order->courier_history)) {
                $decoded = json_decode($order->courier_history, true);
                if (is_array($decoded)) {
                    $history = $decoded;
                }
            }

            if (empty($history)) {
                $history[] = [
                    'status' => 'pending',
                    'delivery_status' => 'pending',
                    'title' => 'Order Placed',
                    'message' => 'Order was placed by customer.',
                    'location' => 'Online Store',
                    'timestamp' => $order->created_at ? Carbon::parse($order->created_at)->format('Y-m-d H:i:s') : now()->format('Y-m-d H:i:s')
                ];
            }

            $lastEntry = end($history);
            $newEntryTitle = $normalized['title'];
            $newEntryMessage = $note ?: $normalized['message'];

            $isDuplicate = false;
            if ($lastEntry && ($lastEntry['status'] ?? '') === $rawStatus && ($lastEntry['delivery_status'] ?? '') === $normalized['delivery_status']) {
                $isDuplicate = true;
            }

            if (!$isDuplicate) {
                $history[] = [
                    'status' => $rawStatus,
                    'delivery_status' => $normalized['delivery_status'],
                    'title' => $newEntryTitle,
                    'message' => $newEntryMessage,
                    'location' => $location ?: (ucfirst($provider) . ' Distribution Network'),
                    'timestamp' => now()->format('Y-m-d H:i:s')
                ];
            }

            $updateData = [
                'courier_name' => $provider,
                'courier_provider' => $provider,
                'courier_status' => $rawStatus,
                'delivery_status' => $normalized['delivery_status'],
                'order_status' => $normalized['order_status'],
                'courier_history' => json_encode($history),
                'updated_at' => now(),
            ];

            if ($trackingCode && empty($order->tracking_code)) {
                $updateData['tracking_code'] = $trackingCode;
                $updateData['courier_tracking_code'] = $trackingCode;
            }
            if ($consignmentId && empty($order->consignment_id)) {
                $updateData['consignment_id'] = $consignmentId;
                $updateData['courier_consignment_id'] = $consignmentId;
            }

            DB::table('orders')->where('id', $order->id)->update($updateData);

            $targetTracking = $trackingCode ?: ($order->tracking_code ?: $order->courier_tracking_code);
            if ($targetTracking) {
                DB::table('courier_consignments')
                    ->where('tracking_code', $targetTracking)
                    ->update([
                        'status' => $rawStatus,
                        'updated_at' => now()
                    ]);
            }

            return [
                'success' => true,
                'message' => "Order #{$order->order_number} status updated to {$normalized['delivery_status']}.",
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'delivery_status' => $normalized['delivery_status'],
                'order_status' => $normalized['order_status']
            ];
        });
    }

    public function processWebhook(string $provider, array $payload): array
    {
        $provider = strtolower(trim($provider));
        $trackingCode = null;
        $consignmentId = null;
        $rawStatus = null;
        $note = null;
        $location = null;

        if ($provider === 'steadfast') {
            $trackingCode = $payload['tracking_code'] ?? $payload['consignment']['tracking_code'] ?? null;
            $consignmentId = $payload['consignment_id'] ?? $payload['consignment']['consignment_id'] ?? null;
            $rawStatus = $payload['status'] ?? $payload['consignment']['status'] ?? 'unknown';
            $note = $payload['note'] ?? $payload['message'] ?? null;
            $location = $payload['location'] ?? null;
        } elseif ($provider === 'pathao') {
            $trackingCode = $payload['consignment_id'] ?? $payload['merchant_order_id'] ?? null;
            $consignmentId = $payload['consignment_id'] ?? null;
            $rawStatus = $payload['order_status'] ?? $payload['status'] ?? 'unknown';
            $note = $payload['reason'] ?? $payload['order_status_slug'] ?? null;
            $location = $payload['hub_name'] ?? null;
        } elseif ($provider === 'redx') {
            $trackingCode = $payload['tracking_id'] ?? $payload['trackingId'] ?? null;
            $consignmentId = $payload['parcel_id'] ?? $payload['parcelId'] ?? null;
            $rawStatus = $payload['status'] ?? 'unknown';
            $note = $payload['message'] ?? $payload['reason'] ?? null;
            $location = $payload['hub_name'] ?? null;
        } else {
            $trackingCode = $payload['tracking_code'] ?? $payload['tracking_id'] ?? null;
            $consignmentId = $payload['consignment_id'] ?? null;
            $rawStatus = $payload['status'] ?? 'unknown';
            $note = $payload['note'] ?? null;
            $location = $payload['location'] ?? null;
        }

        DB::table('courier_webhooks')->insert([
            'provider' => $provider,
            'event_type' => $payload['event'] ?? ($payload['event_type'] ?? 'status_update'),
            'tracking_code' => $trackingCode,
            'payload' => json_encode($payload),
            'processed' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $identifier = $trackingCode ?: $consignmentId;
        if (!$identifier && isset($payload['invoice'])) {
            $identifier = $payload['invoice'];
        }
        if (!$identifier && isset($payload['order_number'])) {
            $identifier = $payload['order_number'];
        }

        if ($identifier && $rawStatus) {
            return $this->updateOrderCourierStatus(
                $identifier,
                $provider,
                $rawStatus,
                $consignmentId,
                $trackingCode,
                $note,
                $location
            );
        }

        return ['success' => false, 'message' => 'No matching consignment identifier found in payload.'];
    }

    public function verifyWebhookSignature(string $provider, Request $request): bool
    {
        $provider = strtolower(trim($provider));
        $expectedToken = config("services.{$provider}.webhook_token");

        if (!$expectedToken) {
            $row = DB::table('settings')->where('key', "courier_{$provider}_webhook_token")->first();
            if ($row && !empty($row->value)) {
                $expectedToken = $row->value;
            }
        }

        if (!$expectedToken) {
            return true;
        }

        $headerToken = $request->header('X-Webhook-Token')
            ?: $request->header('X-Courier-Secret')
            ?: $request->header('X-Pathao-Signature')
            ?: $request->header('Authorization')
            ?: $request->query('secret');

        if ($headerToken && str_starts_with($headerToken, 'Bearer ')) {
            $headerToken = substr($headerToken, 7);
        }

        return hash_equals((string)$expectedToken, (string)$headerToken);
    }
}
