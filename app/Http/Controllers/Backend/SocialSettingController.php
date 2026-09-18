<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SocialSettingController extends Controller
{
    public function index()
    {
        $setting = DB::table('social_login_settings')->first();

        if (!$setting) {
            $id = DB::table('social_login_settings')->insertGetId([
                'google_client_id' => '',
                'google_client_secret' => '',
                'google_redirect_url' => url('/auth/google/callback'),
                'is_google_active' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $setting = DB::table('social_login_settings')->where('id', $id)->first();
        }

        return view('backend.settings.social', compact('setting'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'google_client_id' => 'nullable|string|max:255',
            'google_client_secret' => 'nullable|string|max:255',
            'google_redirect_url' => 'nullable|string|max:255',
            'is_google_active' => 'nullable|boolean',
        ]);

        $isGoogleActive = $request->has('is_google_active') ? 1 : 0;
        $googleRedirectUrl = !empty($validated['google_redirect_url']) ? $validated['google_redirect_url'] : url('/auth/google/callback');

        $setting = DB::table('social_login_settings')->first();

        if ($setting) {
            DB::table('social_login_settings')->where('id', $setting->id)->update([
                'google_client_id' => $validated['google_client_id'] ?? null,
                'google_client_secret' => $validated['google_client_secret'] ?? null,
                'google_redirect_url' => $googleRedirectUrl,
                'is_google_active' => $isGoogleActive,
                'updated_at' => now(),
            ]);
        } else {
            DB::table('social_login_settings')->insert([
                'google_client_id' => $validated['google_client_id'] ?? null,
                'google_client_secret' => $validated['google_client_secret'] ?? null,
                'google_redirect_url' => $googleRedirectUrl,
                'is_google_active' => $isGoogleActive,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return redirect()->route('admin.social_settings.index')->with('success', 'Social login settings updated successfully.');
    }
}
