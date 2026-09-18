<?php

namespace App\Services\Courier;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class RedxService implements CourierServiceInterface
{
    public function createConsignment(object $order, array $config): array
    {
        $tracking = 'RDX-' . strtoupper(Str::random(8));
        $consignmentId = 'REDX-' . rand(300000, 999999);

        return [
            'success' => true,
            'provider' => 'redx',
            'consignment_id' => $consignmentId,
            'tracking_code' => $tracking,
            'status' => 'pickup_pending',
            'message' => 'Consignment booked successfully with RedX Logistics.',
            'raw' => ['tracking_id' => $tracking, 'parcel_id' => $consignmentId]
        ];
    }

    public function trackConsignment(string $trackingCode, array $config): array
    {
        return [
            'success' => true,
            'provider' => 'redx',
            'tracking_code' => $trackingCode,
            'status' => 'dispatched',
            'status_detail' => 'Parcel arrived at district sorting center.',
            'last_updated' => now()->toDateTimeString()
        ];
    }

    public function cancelConsignment(string $trackingCode, array $config): array
    {
        return [
            'success' => true,
            'provider' => 'redx',
            'tracking_code' => $trackingCode,
            'status' => 'cancelled',
            'message' => 'RedX shipment cancelled.'
        ];
    }
}
