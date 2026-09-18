<?php

namespace App\Services\Courier;

interface CourierServiceInterface
{
    public function createConsignment(object $order, array $config): array;

    public function trackConsignment(string $trackingCode, array $config): array;

    public function cancelConsignment(string $trackingCode, array $config): array;
}
