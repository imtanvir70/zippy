<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class MetaCapiService
{
    protected ?string $pixelId;
    protected ?string $accessToken;
    protected ?string $testEventCode;
    protected bool $isActive;

    public function __construct()
    {
        $settings = self::getSettings();

        $this->pixelId = !empty($settings['meta_pixel_id'])
            ? (string) $settings['meta_pixel_id']
            : (string) config('services.meta.pixel_id');

        $this->accessToken = !empty($settings['meta_capi_access_token'])
            ? (string) $settings['meta_capi_access_token']
            : (string) config('services.meta.access_token');

        $this->testEventCode = !empty($settings['meta_capi_test_event_code'])
            ? (string) $settings['meta_capi_test_event_code']
            : (string) config('services.meta.test_event_code');

        $status = $settings['meta_capi_status'] ?? null;
        if ($status !== null && $status !== '') {
            $this->isActive = (bool) ((int) $status);
        } else {
            $this->isActive = true;
        }
    }

    public static function getSettings(): array
    {
        return Cache::rememberForever('meta_tracking_settings', function () {
            return DB::table('settings')
                ->whereIn('key', [
                    'meta_pixel_id',
                    'meta_capi_access_token',
                    'meta_capi_test_event_code',
                    'meta_capi_status',
                ])
                ->pluck('value', 'key')
                ->all();
        });
    }

    public static function clearCache(): void
    {
        Cache::forget('meta_tracking_settings');
    }

    public function isConfigured(): bool
    {
        return $this->isActive && !empty($this->pixelId) && !empty($this->accessToken);
    }

    public function sendPurchase(array $orderData): bool
    {
        return $this->sendEvent('Purchase', $orderData);
    }

    public function sendInitiateCheckout(array $checkoutData): bool
    {
        return $this->sendEvent('InitiateCheckout', $checkoutData);
    }

    public function sendAddToCart(array $cartData): bool
    {
        return $this->sendEvent('AddToCart', $cartData);
    }

    public function sendEvent(string $eventName, array $data): bool
    {
        if (!$this->isConfigured()) {
            return false;
        }

        $eventId = (string) ($data['event_id'] ?? ('order_' . ($data['order_number'] ?? uniqid())));
        $eventTime = (int) ($data['event_time'] ?? time());
        $eventSourceUrl = (string) ($data['event_source_url'] ?? config('app.url'));

        $userData = $this->buildUserData($data);
        $customData = $this->buildCustomData($data);

        $eventPayload = [
            'event_name' => $eventName,
            'event_time' => $eventTime,
            'event_id' => $eventId,
            'event_source_url' => $eventSourceUrl,
            'action_source' => 'website',
            'user_data' => $userData,
            'custom_data' => $customData,
        ];

        $rootPayload = [
            'data' => [
                $eventPayload,
            ],
        ];

        $testCode = $data['test_event_code'] ?? $this->testEventCode;
        if (!empty($testCode)) {
            $rootPayload['test_event_code'] = $testCode;
        }

        try {
            $response = Http::timeout(10)
                ->withToken($this->accessToken)
                ->post("https://graph.facebook.com/v19.0/{$this->pixelId}/events", $rootPayload);

            if ($response->successful()) {
                return true;
            }

            Log::channel('single')->error('Meta CAPI Request Failed', [
                'event_name' => $eventName,
                'event_id' => $eventId,
                'status' => $response->status(),
                'response' => $response->json() ?: $response->body(),
            ]);

            throw new \RuntimeException('Meta CAPI API Error: ' . $response->body());
        } catch (Throwable $e) {
            Log::channel('single')->error('Meta CAPI Exception: ' . $e->getMessage(), [
                'event_name' => $eventName,
                'event_id' => $eventId,
            ]);
            throw $e;
        }
    }

    protected function buildUserData(array $data): array
    {
        $userData = [];

        if (!empty($data['email'])) {
            $userData['em'] = [$this->hashEmail($data['email'])];
        }

        if (!empty($data['phone'])) {
            $userData['ph'] = [$this->hashPhone($data['phone'])];
        }

        if (!empty($data['name'])) {
            $parts = explode(' ', trim($data['name']));
            $userData['fn'] = [$this->hashString(array_shift($parts))];
            if (!empty($parts)) {
                $userData['ln'] = [$this->hashString(implode(' ', $parts))];
            }
        }

        if (!empty($data['city'])) {
            $userData['ct'] = [$this->hashString($data['city'])];
        }

        $country = $data['country'] ?? 'bd';
        $userData['country'] = [$this->hashString($country)];

        if (!empty($data['client_ip_address'])) {
            $userData['client_ip_address'] = $data['client_ip_address'];
        }

        if (!empty($data['client_user_agent'])) {
            $userData['client_user_agent'] = $data['client_user_agent'];
        }

        if (!empty($data['fbp'])) {
            $userData['fbp'] = $data['fbp'];
        }

        if (!empty($data['fbc'])) {
            $userData['fbc'] = $data['fbc'];
        }

        return $userData;
    }

    protected function buildCustomData(array $data): array
    {
        $customData = [
            'currency' => (string) ($data['currency'] ?? 'BDT'),
            'value' => (float) ($data['value'] ?? 0.0),
            'content_type' => 'product',
        ];

        if (!empty($data['contents']) && is_array($data['contents'])) {
            $customData['contents'] = array_map(function ($item) {
                return [
                    'id' => (string) ($item['id'] ?? $item['product_id'] ?? ''),
                    'quantity' => (int) ($item['quantity'] ?? $item['qty'] ?? 1),
                    'item_price' => (float) ($item['item_price'] ?? $item['price'] ?? 0.0),
                ];
            }, $data['contents']);
            $customData['num_items'] = count($customData['contents']);
        }

        return $customData;
    }

    protected function hashEmail(string $email): string
    {
        return hash('sha256', strtolower(trim($email)));
    }

    protected function hashPhone(string $phone): string
    {
        $digits = preg_replace('/[^\d]/', '', $phone);
        if (str_starts_with($digits, '01') && strlen($digits) === 11) {
            $digits = '88' . $digits;
        } elseif (strlen($digits) === 10 && str_starts_with($digits, '1')) {
            $digits = '880' . $digits;
        }
        return hash('sha256', $digits);
    }

    protected function hashString(string $value): string
    {
        return hash('sha256', strtolower(trim($value)));
    }
}
