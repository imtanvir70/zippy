<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Services\Frontend\FrontendCacheService;
use App\Services\Media\ImageOptimizerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PopupController extends Controller
{
    public function index(): View
    {
        $popups = DB::table('popups')
            ->orderByDesc('id')
            ->get();

        return view('backend.popups.index', compact('popups'));
    }

    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|in:both,image_only,text_only',
            'image' => 'nullable|file|mimes:jpeg,png,jpg,gif,svg,webp|max:10240',
            'heading' => 'nullable|string|max:255',
            'content' => 'nullable|string',
            'btn_text' => 'nullable|string|max:100',
            'btn_link' => 'nullable|string|max:255',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $optimizer = app(ImageOptimizerService::class);
            $imagePath = $optimizer->convertToWebp($request->file('image'), 'popups', 1000, 85);
        }

        $isActive = $request->has('is_active') && in_array($request->input('is_active'), ['1', 1, 'on', true], true) ? 1 : 0;

        DB::table('popups')->insert([
            'title' => $validated['title'],
            'type' => $validated['type'],
            'image' => $imagePath,
            'heading' => $validated['heading'] ?? null,
            'content' => $validated['content'] ?? null,
            'btn_text' => $validated['btn_text'] ?? null,
            'btn_link' => $validated['btn_link'] ?? null,
            'is_active' => $isActive,
            'start_date' => $validated['start_date'] ?? null,
            'end_date' => $validated['end_date'] ?? null,
            'impressions_count' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Cache::forget('fc.home.data');
        FrontendCacheService::flush();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Promotional popup created successfully.',
            ]);
        }

        return redirect()->route('admin.popups.index')->with('success', 'Promotional popup created successfully.');
    }

    public function json(int $id): JsonResponse
    {
        $popup = DB::table('popups')->where('id', $id)->first();
        if (!$popup) {
            return response()->json(['success' => false, 'message' => 'Popup not found.'], 404);
        }

        return response()->json(['success' => true, 'popup' => $popup]);
    }

    public function update(Request $request, int $id): JsonResponse|RedirectResponse
    {
        $popup = DB::table('popups')->where('id', $id)->first();
        if (!$popup) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Popup not found.'], 404);
            }
            return redirect()->route('admin.popups.index')->with('error', 'Popup not found.');
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|in:both,image_only,text_only',
            'image' => 'nullable|file|mimes:jpeg,png,jpg,gif,svg,webp|max:10240',
            'heading' => 'nullable|string|max:255',
            'content' => 'nullable|string',
            'btn_text' => 'nullable|string|max:100',
            'btn_link' => 'nullable|string|max:255',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        $imagePath = $popup->image;
        if ($request->hasFile('image')) {
            $optimizer = app(ImageOptimizerService::class);
            $imagePath = $optimizer->convertToWebp($request->file('image'), 'popups', 1000, 85);
        }

        $isActive = $request->has('is_active') && in_array($request->input('is_active'), ['1', 1, 'on', true], true) ? 1 : 0;

        DB::table('popups')->where('id', $id)->update([
            'title' => $validated['title'],
            'type' => $validated['type'],
            'image' => $imagePath,
            'heading' => $validated['heading'] ?? null,
            'content' => $validated['content'] ?? null,
            'btn_text' => $validated['btn_text'] ?? null,
            'btn_link' => $validated['btn_link'] ?? null,
            'is_active' => $isActive,
            'start_date' => $validated['start_date'] ?? null,
            'end_date' => $validated['end_date'] ?? null,
            'updated_at' => now(),
        ]);

        Cache::forget('fc.home.data');
        FrontendCacheService::flush();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Promotional popup updated successfully.',
            ]);
        }

        return redirect()->route('admin.popups.index')->with('success', 'Promotional popup updated successfully.');
    }

    public function toggleStatus(int $id): JsonResponse
    {
        $popup = DB::table('popups')->where('id', $id)->first();
        if (!$popup) {
            return response()->json(['success' => false, 'message' => 'Popup not found.'], 404);
        }

        $newStatus = $popup->is_active ? 0 : 1;
        DB::table('popups')->where('id', $id)->update([
            'is_active' => $newStatus,
            'updated_at' => now(),
        ]);

        Cache::forget('fc.home.data');
        FrontendCacheService::flush();

        return response()->json([
            'success' => true,
            'is_active' => $newStatus,
            'message' => $newStatus ? 'Popup activated successfully.' : 'Popup deactivated successfully.',
        ]);
    }

    public function destroy(int $id): JsonResponse|RedirectResponse
    {
        $deleted = DB::table('popups')->where('id', $id)->delete();

        Cache::forget('fc.home.data');
        FrontendCacheService::flush();

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'success' => (bool) $deleted,
                'message' => 'Popup deleted successfully.',
            ]);
        }

        return redirect()->route('admin.popups.index')->with('success', 'Popup deleted successfully.');
    }

    public function trackImpression(int $id): JsonResponse
    {
        DB::table('popups')->where('id', $id)->increment('impressions_count');
        return response()->json(['success' => true]);
    }
}
