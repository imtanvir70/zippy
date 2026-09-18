<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Courier\CourierManager;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class CourierWebhookController extends Controller
{
    protected CourierManager $courierManager;

    public function __construct(CourierManager $courierManager)
    {
        $this->courierManager = $courierManager;
    }

    public function handle(Request $request, string $provider): JsonResponse
    {
        $provider = strtolower(trim($provider));

        if (!in_array($provider, ['steadfast', 'pathao', 'redx', 'ecourier', 'paperfly'])) {
            return response()->json([
                'success' => false,
                'message' => "Unsupported courier provider: {$provider}"
            ], 400);
        }

        if (!$this->courierManager->verifyWebhookSignature($provider, $request)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized: Invalid webhook signature or token.'
            ], 401);
        }

        $payload = $request->all();
        if (empty($payload)) {
            $json = json_decode($request->getContent(), true);
            if (is_array($json)) {
                $payload = $json;
            }
        }

        $result = $this->courierManager->processWebhook($provider, $payload);

        $status = ($result['success'] ?? false) ? 200 : 422;
        return response()->json($result, $status);
    }
}
