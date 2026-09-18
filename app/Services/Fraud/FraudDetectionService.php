<?php

namespace App\Services\Fraud;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FraudDetectionService
{
    protected string $apiUrl;
    protected string $apiKey;

    protected bool $isEnabled;
    protected int $cacheSeconds;

    public function __construct()
    {
        $dbSettings = DB::table('settings')->whereIn('key', [
            'bdcourier_api_url',
            'bdcourier_api_key',
            'bdcourier_is_enabled',
            'fraud_auto_flag_threshold',
            'fraud_cache_seconds',
        ])->pluck('value', 'key');

        $this->apiUrl = !empty($dbSettings['bdcourier_api_url'])
            ? $dbSettings['bdcourier_api_url']
            : config('services.bdcourier.url', env('BDCOURIER_API_URL', 'https://api.bdcourier.com'));

        $this->apiKey = !empty($dbSettings['bdcourier_api_key'])
            ? $dbSettings['bdcourier_api_key']
            : config('services.bdcourier.key', env('BDCOURIER_API_KEY', 'jHxRx7kq1EpPt10ZHxOXyr0dltuhs6djfLDelYnhUBvN3CJCZwbybIj8fHeb'));

        $this->isEnabled = isset($dbSettings['bdcourier_is_enabled']) ? (bool)$dbSettings['bdcourier_is_enabled'] : true;
        $this->cacheSeconds = !empty($dbSettings['fraud_cache_seconds']) ? (int)$dbSettings['fraud_cache_seconds'] : 7200;
    }

    /**
     * Query bdcourier live API for telephone delivery success stats and merchant reports.
     * Caches response per phone number to prevent rate limits.
     */
    public function checkCourierProfile(string $phone): ?array
    {
        if (!$this->isEnabled || empty($this->apiKey)) {
            return null;
        }

        $cleanedPhone = preg_replace('/[^0-9]/', '', $phone);
        if (strlen($cleanedPhone) > 11 && str_starts_with($cleanedPhone, '880')) {
            $cleanedPhone = substr($cleanedPhone, 2);
        }
        if (strlen($cleanedPhone) < 10) {
            return null;
        }

        $cacheKey = 'bdcourier_check_' . $cleanedPhone;

        return Cache::remember($cacheKey, $this->cacheSeconds, function () use ($cleanedPhone) {
            try {
                $response = Http::timeout(6)
                    ->withHeaders([
                        'Authorization' => 'Bearer ' . $this->apiKey,
                        'Accept' => 'application/json',
                        'Content-Type' => 'application/json',
                    ])
                    ->post(rtrim($this->apiUrl, '/') . '/courier-check', [
                        'phone' => $cleanedPhone,
                    ]);

                if ($response->successful()) {
                    return $response->json();
                }

                Log::warning('bdcourier API call returned non-200', [
                    'phone' => $cleanedPhone,
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
            } catch (\Throwable $e) {
                Log::error('bdcourier API connection error: ' . $e->getMessage(), ['phone' => $cleanedPhone]);
            }

            return null;
        });
    }

    /**
     * Calculate automated risk score (0 to 100) combining live BDCourier delivery accuracy
     * and local heuristics (order velocity, address checks, IP repetition).
     */
    public function evaluateOrder(array $orderData): array
    {
        $score = 0;
        $reasons = [];

        $phone = $orderData['customer_phone'] ?? '';
        $name = $orderData['customer_name'] ?? '';
        $address = $orderData['customer_address'] ?? '';
        $total = (float) ($orderData['total'] ?? 0);
        $paymentMethod = $orderData['payment_method'] ?? 'cod';
        $ip = $orderData['ip_address'] ?? null;

        // 1. LIVE BDCOURIER REAL-TIME CHECK
        $courierData = null;
        if (!empty($phone)) {
            $courierRes = $this->checkCourierProfile($phone);
            if ($courierRes && isset($courierRes['status']) && $courierRes['status'] === 'success') {
                $courierData = $courierRes;
                $summary = $courierRes['data']['summary'] ?? null;
                $reports = $courierRes['reports'] ?? [];
                $reportsCount = count($reports);

                // A. Fraud Reports Check
                if ($reportsCount > 0) {
                    // Critical hit for verified merchant fraud complaints
                    $score += min(60, $reportsCount * 25);
                    $reasons[] = "🚨 {$reportsCount} fraud complaint(s) reported by courier merchants nationwide!";
                }

                // B. Success Rate & Delivery Ratio
                if ($summary && isset($summary['total_parcel']) && $summary['total_parcel'] > 0) {
                    $totalParcel = (int) $summary['total_parcel'];
                    $cancelled = (int) ($summary['cancelled_parcel'] ?? 0);
                    $successRatio = (float) ($summary['success_ratio'] ?? 0.0);

                    if ($totalParcel >= 2) {
                        if ($successRatio < 40) {
                            $score += 45;
                            $reasons[] = "Extreme Return Rate: Success ratio is only {$successRatio}% across couriers ({$cancelled}/{$totalParcel} returned).";
                        } elseif ($successRatio < 65) {
                            $score += 25;
                            $reasons[] = "Low Delivery Success: Delivery ratio is {$successRatio}% ({$cancelled}/{$totalParcel} parcels cancelled).";
                        } elseif ($successRatio >= 85 && $totalParcel >= 5 && $reportsCount === 0) {
                            // Trust bonus: Proven loyal buyer with >85% success rate
                            $score = max(0, $score - 20);
                            $reasons[] = "Verified Reliable Buyer: High courier delivery success rate ({$successRatio}%) across {$totalParcel} orders.";
                        }
                    }
                }
            }
        }

        // 2. Phone Velocity Check (Too many orders in short window)
        if (!empty($phone)) {
            $recent1HourOrders = DB::table('orders')
                ->where('customer_phone', $phone)
                ->where('created_at', '>=', now()->subHour())
                ->count();

            if ($recent1HourOrders >= 2) {
                $score += 30;
                $reasons[] = "High velocity: {$recent1HourOrders} orders placed within the last 1 hour from this phone number.";
            }

            // Past cancellation / return history in our own store
            $pastCancelled = DB::table('orders')
                ->where('customer_phone', $phone)
                ->where('order_status', 'cancelled')
                ->count();

            if ($pastCancelled >= 2) {
                $score += 20;
                $reasons[] = "Store Return History: Customer has {$pastCancelled} previously cancelled/returned orders on ZippyBD.";
            }
        }

        // 3. Junk Address & Suspicious String Heuristics
        $trimmedAddress = trim($address);
        if (strlen($trimmedAddress) < 8) {
            $score += 20;
            $reasons[] = "Extremely short address provided (less than 8 characters).";
        }

        $suspiciousPatterns = ['asdf', 'test', 'fake', 'xxxx', '1111', '0000', 'qwerty', 'aaaa', 'null', 'undefined'];
        foreach ($suspiciousPatterns as $pattern) {
            if (stripos($trimmedAddress, $pattern) !== false || stripos($name, $pattern) !== false) {
                $score += 30;
                $reasons[] = "Suspicious test/junk keyword '{$pattern}' detected in name or delivery address.";
                break;
            }
        }

        // 4. High-Value COD Threshold Check
        if ($paymentMethod === 'cod' && $total >= 10000) {
            $score += 15;
            $reasons[] = "High-value Cash on Delivery order exceeding ৳10,000 without advance verification.";
        }

        // 5. IP Repetition with Different Phones Check
        if (!empty($ip)) {
            $distinctPhonesOnIp = DB::table('orders')
                ->where('ip_address', $ip)
                ->where('created_at', '>=', now()->subDay())
                ->distinct('customer_phone')
                ->count('customer_phone');

            if ($distinctPhonesOnIp >= 3) {
                $score += 25;
                $reasons[] = "Multiple different phone numbers ({$distinctPhonesOnIp}) used from the same IP address in 24 hours.";
            }
        }

        // Normalize Score to max 100
        $score = max(0, min(100, $score));

        $threshold = (int) DB::table('settings')->where('key', 'fraud_auto_flag_threshold')->value('value') ?: 65;
        $moderateThreshold = max(20, (int)($threshold * 0.55));

        // Determine Status & Recommendation
        if ($score >= $threshold) {
            $status = 'flagged_fraud';
            $riskLevel = 'HIGH RISK';
            $recommendation = 'DO NOT DISPATCH without advance delivery charge or phone call confirmation.';
        } elseif ($score >= $moderateThreshold) {
            $status = 'suspicious';
            $riskLevel = 'MODERATE RISK';
            $recommendation = 'Requires manual phone verification before courier parcel creation.';
        } else {
            $status = 'safe';
            $riskLevel = 'SAFE / LOW RISK';
            $recommendation = 'Safe order. Automated processing and courier dispatch recommended.';
        }

        return [
            'fraud_score' => $score,
            'fraud_status' => $status,
            'risk_level' => $riskLevel,
            'reasons' => $reasons,
            'recommendation' => $recommendation,
            'courier_data' => $courierData
        ];
    }
}
