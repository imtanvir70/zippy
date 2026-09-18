<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Services\Courier\CourierManager;

class SyncCourierStatusCommand extends Command
{
    protected $signature = 'orders:sync-courier-status {--limit=100} {--provider=}';

    protected $description = 'Poll active courier consignments and synchronize delivery status and history';

    public function handle(CourierManager $courierManager): int
    {
        $limit = (int)$this->option('limit') ?: 100;
        $providerFilter = $this->option('provider');

        $query = DB::table('orders')
            ->where(function ($q) {
                $q->whereNotNull('tracking_code')
                    ->where('tracking_code', '!=', '')
                    ->orWhere(function ($sub) {
                        $sub->whereNotNull('courier_tracking_code')
                            ->where('courier_tracking_code', '!=', '');
                    });
            })
            ->whereNotIn('delivery_status', ['delivered', 'cancelled', 'returned'])
            ->whereNotIn('order_status', ['delivered', 'cancelled'])
            ->orderBy('id', 'asc')
            ->limit($limit);

        if (!empty($providerFilter)) {
            $query->where(function ($q) use ($providerFilter) {
                $q->where('courier_name', $providerFilter)
                    ->orWhere('courier_provider', $providerFilter);
            });
        }

        $orders = $query->get();

        if ($orders->isEmpty()) {
            $this->info('No active courier shipments found for status synchronization.');
            return Command::SUCCESS;
        }

        $settingsRows = DB::table('settings')->get();
        $config = [];
        foreach ($settingsRows as $row) {
            $config[$row->key] = $row->value;
        }

        $this->info("Found {$orders->count()} orders with active shipments to synchronize.");

        $syncedCount = 0;
        $failedCount = 0;

        foreach ($orders as $order) {
            $provider = strtolower(trim($order->courier_name ?: ($order->courier_provider ?: 'steadfast')));
            $trackingCode = $order->tracking_code ?: $order->courier_tracking_code;
            $consignmentId = $order->consignment_id ?: $order->courier_consignment_id;

            try {
                $service = $courierManager->resolve($provider);
                $trackResult = $service->trackConsignment($trackingCode, $config);

                if (!empty($trackResult['success']) && !empty($trackResult['status'])) {
                    $newStatus = $trackResult['status'];
                    $detail = $trackResult['status_detail'] ?? null;

                    $courierManager->updateOrderCourierStatus(
                        $order->id,
                        $provider,
                        $newStatus,
                        $consignmentId,
                        $trackingCode,
                        $detail
                    );

                    $this->line("Synced Order #{$order->order_number} [{$provider}]: {$newStatus}");
                    $syncedCount++;
                } else {
                    $failedCount++;
                }
            } catch (\Throwable $e) {
                $failedCount++;
                $this->error("Error syncing Order #{$order->order_number}: " . $e->getMessage());
            }
        }

        $this->info("Sync completed: {$syncedCount} synchronized, {$failedCount} skipped/failed.");
        return Command::SUCCESS;
    }
}
