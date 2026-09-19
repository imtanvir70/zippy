<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Services\Frontend\FrontendCacheService;
use App\Services\MetaCapiService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class MetaSettingController extends Controller
{
    public function index(): View
    {
        $settings = MetaCapiService::getSettings();

        return view('backend.settings.meta', compact('settings'));
    }

    public function update(Request $request): RedirectResponse
    {
        $request->validate([
            'meta_capi_status' => 'nullable',
            'meta_pixel_id' => 'nullable|string|max:100',
            'fb_app_id' => 'nullable|string|max:100',
            'meta_capi_access_token' => 'nullable|string',
            'meta_capi_test_event_code' => 'nullable|string|max:100',
        ]);

        $status = $request->has('meta_capi_status') ? '1' : '0';
        $pixelId = trim($request->input('meta_pixel_id', ''));
        $appId = trim($request->input('fb_app_id', ''));
        $accessToken = trim($request->input('meta_capi_access_token', ''));
        $testEventCode = trim($request->input('meta_capi_test_event_code', ''));

        $records = [
            'meta_capi_status' => $status,
            'meta_pixel_id' => $pixelId,
            'fb_app_id' => $appId,
            'meta_capi_access_token' => $accessToken,
            'meta_capi_test_event_code' => $testEventCode,
        ];

        DB::transaction(function () use ($records) {
            foreach ($records as $key => $value) {
                DB::table('settings')->updateOrInsert(
                    ['key' => $key],
                    ['value' => $value, 'updated_at' => now()]
                );
            }
        });

        MetaCapiService::clearCache();
        FrontendCacheService::flush();

        return redirect()->route('admin.settings.meta')->with('success', 'Meta Pixel এবং Conversions API সেটিংস সফলভাবে সংরক্ষণ করা হয়েছে।');
    }
}
