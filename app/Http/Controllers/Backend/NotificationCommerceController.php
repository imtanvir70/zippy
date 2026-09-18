<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class NotificationCommerceController extends Controller
{
    public function index()
    {
        $setting = DB::table('notification_settings')->first();
        if (!$setting) {
            $id = DB::table('notification_settings')->insertGetId([
                'whatsapp_enabled' => 0,
                'sms_enabled' => 0,
                'notify_on_order_status' => 1,
                'notify_on_abandoned_cart' => 1,
                'sms_provider' => 'greenweb',
                'sms_api_url' => 'http://api.greenweb.com.bd/api.php',
                'order_status_template' => 'Hello {name}, your order #{order_number} status is now {status}. Total: ৳{total}. Thank you for shopping with us!',
                'abandoned_cart_template' => 'Hi {name}, you left items in your shopping cart! Return to checkout to finish your order: {checkout_url}',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $setting = DB::table('notification_settings')->where('id', $id)->first();
        }

        return view('backend.notifications.index', compact('setting'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'whatsapp_api_url' => 'nullable|string|max:255',
            'whatsapp_api_token' => 'nullable|string|max:255',
            'whatsapp_from_phone' => 'nullable|string|max:50',
            'sms_provider' => 'nullable|string|max:50',
            'sms_api_url' => 'nullable|string|max:255',
            'sms_api_key' => 'nullable|string|max:255',
            'sms_sender_id' => 'nullable|string|max:50',
            'order_status_template' => 'nullable|string',
            'abandoned_cart_template' => 'nullable|string',
        ]);

        $data = [
            'whatsapp_api_url' => $validated['whatsapp_api_url'] ?? null,
            'whatsapp_api_token' => $validated['whatsapp_api_token'] ?? null,
            'whatsapp_from_phone' => $validated['whatsapp_from_phone'] ?? null,
            'whatsapp_enabled' => $request->has('whatsapp_enabled') ? 1 : 0,
            'sms_provider' => $validated['sms_provider'] ?? 'greenweb',
            'sms_api_url' => $validated['sms_api_url'] ?? null,
            'sms_api_key' => $validated['sms_api_key'] ?? null,
            'sms_sender_id' => $validated['sms_sender_id'] ?? null,
            'sms_enabled' => $request->has('sms_enabled') ? 1 : 0,
            'notify_on_order_status' => $request->has('notify_on_order_status') ? 1 : 0,
            'notify_on_abandoned_cart' => $request->has('notify_on_abandoned_cart') ? 1 : 0,
            'order_status_template' => $validated['order_status_template'] ?? null,
            'abandoned_cart_template' => $validated['abandoned_cart_template'] ?? null,
            'updated_at' => now(),
        ];

        $setting = DB::table('notification_settings')->first();
        if ($setting) {
            DB::table('notification_settings')->where('id', $setting->id)->update($data);
        } else {
            $data['created_at'] = now();
            DB::table('notification_settings')->insert($data);
        }

        return redirect()->route('admin.notification_settings.index')->with('success', 'Notification settings updated successfully.');
    }
}
