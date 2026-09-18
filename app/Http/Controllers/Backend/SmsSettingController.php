<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class SmsSettingController extends Controller
{
    public function index()
    {
        $setting = DB::table('sms_settings')->first();
        if (!$setting) {
            $id = DB::table('sms_settings')->insertGetId([
                'provider' => 'greenweb',
                'api_url' => 'http://api.greenweb.com.bd/api.php',
                'api_key' => '',
                'sender_id' => '',
                'is_active' => 0,
                'notify_on_order_placed' => 1,
                'notify_on_order_shipped' => 1,
                'notify_on_order_delivered' => 1,
                'order_placed_template' => 'Dear {name}, your order #{order_number} has been received. Total: {total} BDT. Thank you!',
                'order_shipped_template' => 'Dear {name}, your order #{order_number} has been shipped via {courier}. Thank you!',
                'order_delivered_template' => 'Dear {name}, your order #{order_number} has been delivered successfully. Thank you for shopping with us!',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $setting = DB::table('sms_settings')->where('id', $id)->first();
        }

        return view('backend.sms.index', compact('setting'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'provider' => 'required|string|max:50',
            'api_url' => 'nullable|string|max:255',
            'api_key' => 'nullable|string|max:255',
            'sender_id' => 'nullable|string|max:50',
            'is_active' => 'nullable|boolean',
            'notify_on_order_placed' => 'nullable|boolean',
            'notify_on_order_shipped' => 'nullable|boolean',
            'notify_on_order_delivered' => 'nullable|boolean',
            'order_placed_template' => 'nullable|string',
            'order_shipped_template' => 'nullable|string',
            'order_delivered_template' => 'nullable|string',
        ]);

        $data = [
            'provider' => $validated['provider'],
            'api_url' => $validated['api_url'] ?? null,
            'api_key' => $validated['api_key'] ?? null,
            'sender_id' => $validated['sender_id'] ?? null,
            'is_active' => $request->has('is_active') ? 1 : 0,
            'notify_on_order_placed' => $request->has('notify_on_order_placed') ? 1 : 0,
            'notify_on_order_shipped' => $request->has('notify_on_order_shipped') ? 1 : 0,
            'notify_on_order_delivered' => $request->has('notify_on_order_delivered') ? 1 : 0,
            'order_placed_template' => $validated['order_placed_template'] ?? null,
            'order_shipped_template' => $validated['order_shipped_template'] ?? null,
            'order_delivered_template' => $validated['order_delivered_template'] ?? null,
            'updated_at' => now(),
        ];

        $setting = DB::table('sms_settings')->first();
        if ($setting) {
            DB::table('sms_settings')->where('id', $setting->id)->update($data);
        } else {
            $data['created_at'] = now();
            DB::table('sms_settings')->insert($data);
        }

        return redirect()->route('admin.sms.index')->with('success', 'SMS gateway settings updated successfully.');
    }

    public function sendTest(Request $request)
    {
        $request->validate([
            'test_phone' => 'required|string|max:20',
            'test_message' => 'required|string|max:160',
        ]);

        $setting = DB::table('sms_settings')->first();
        if (!$setting || empty($setting->api_key)) {
            return response()->json(['success' => false, 'message' => 'API Key is missing.'], 422);
        }

        $phone = preg_replace('/[^0-9]/', '', $request->test_phone);
        if (strlen($phone) === 11 && str_starts_with($phone, '01')) {
            $phone = '88' . $phone;
        }

        try {
            $apiUrl = $setting->api_url ?: 'http://api.greenweb.com.bd/api.php';
            $response = Http::timeout(6)->get($apiUrl, [
                'token' => $setting->api_key,
                'to' => $phone,
                'message' => $request->test_message,
            ]);

            return response()->json([
                'success' => $response->successful(),
                'response' => $response->body(),
            ]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
