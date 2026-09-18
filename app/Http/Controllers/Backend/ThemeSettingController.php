<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class ThemeSettingController extends Controller
{
    public function index()
    {
        $theme = DB::table('theme_settings')->first();

        if (!$theme) {
            $defaultWords = json_encode(['Zippy BD', 'শপিং মানেই']);
            $id = DB::table('theme_settings')->insertGetId([
                'hero_typing_words' => $defaultWords,
                'primary_color' => '#0f172a',
                'accent_color' => '#ff385c',
                'font_family' => 'Outfit',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $theme = DB::table('theme_settings')->where('id', $id)->first();
        }

        $decodedWords = [];
        if (!empty($theme->hero_typing_words)) {
            $parsed = json_decode($theme->hero_typing_words, true);
            if (is_array($parsed)) {
                $decodedWords = $parsed;
            } else {
                $decodedWords = array_map('trim', explode(',', $theme->hero_typing_words));
            }
        }

        $wordsText = implode("\n", $decodedWords);

        return view('backend.settings.theme', compact('theme', 'wordsText'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'hero_typing_words' => 'nullable|string',
            'primary_color' => 'required|string|max:30',
            'accent_color' => 'required|string|max:30',
            'font_family' => 'required|string|max:100',
            'recent_sales_toast_enabled' => 'nullable|boolean',
            'recent_sales_interval' => 'nullable|integer|min:5|max:120',
            'live_viewers_enabled' => 'nullable|boolean',
            'live_viewers_min' => 'nullable|integer|min:1|max:100',
            'live_viewers_max' => 'nullable|integer|min:1|max:100',
        ]);

        $rawWords = $request->input('hero_typing_words', '');
        $lines = preg_split('/\r\n|\r|\n|,/', $rawWords);
        $cleanWords = [];
        foreach ($lines as $line) {
            $trimmed = trim($line);
            if ($trimmed !== '') {
                $cleanWords[] = $trimmed;
            }
        }

        if (empty($cleanWords)) {
            $cleanWords = ['Zippy BD', 'শপিং মানেই'];
        }

        $wordsJson = json_encode(array_values($cleanWords), JSON_UNESCAPED_UNICODE);

        $primaryColor = $validated['primary_color'];
        $accentColor = $validated['accent_color'];
        $fontFamily = $validated['font_family'];

        $recentSalesToastEnabled = $request->has('recent_sales_toast_enabled') ? 1 : 0;
        $recentSalesInterval = (int) ($request->input('recent_sales_interval') ?: 45);
        $liveViewersEnabled = $request->has('live_viewers_enabled') ? 1 : 0;
        $liveViewersMin = (int) ($request->input('live_viewers_min') ?: 8);
        $liveViewersMax = (int) ($request->input('live_viewers_max') ?: 22);

        $payload = [
            'hero_typing_words' => $wordsJson,
            'primary_color' => $primaryColor,
            'accent_color' => $accentColor,
            'font_family' => $fontFamily,
            'recent_sales_toast_enabled' => $recentSalesToastEnabled,
            'recent_sales_interval' => $recentSalesInterval,
            'live_viewers_enabled' => $liveViewersEnabled,
            'live_viewers_min' => $liveViewersMin,
            'live_viewers_max' => $liveViewersMax,
            'updated_at' => now(),
        ];

        $existing = DB::table('theme_settings')->first();

        if ($existing) {
            DB::table('theme_settings')->where('id', $existing->id)->update($payload);
        } else {
            $payload['created_at'] = now();
            DB::table('theme_settings')->insert($payload);
        }

        Cache::forget('theme_settings');
        Cache::forget('theme_settings_all');
        \App\Services\Frontend\FrontendCacheService::flush();

        return redirect()->back()->with('success', 'Theme settings updated successfully.');
    }
}
