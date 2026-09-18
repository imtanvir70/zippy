<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Services\Review\ReviewService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CmsReviewController extends Controller
{
    public function pages()
    {
        $pages = DB::table('pages')->orderByDesc('id')->get();
        return view('backend.cms.pages', compact('pages'));
    }

    public function storePage(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:pages,slug',
            'content' => 'nullable|string',
            'is_published' => 'nullable|boolean',
        ]);

        $slug = !empty($validated['slug']) ? Str::slug($validated['slug']) : Str::slug($validated['title']);
        $isPublished = $request->has('is_published') ? 1 : 0;

        DB::table('pages')->insert([
            'title' => $validated['title'],
            'slug' => $slug,
            'content' => $validated['content'] ?? null,
            'is_published' => $isPublished,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('admin.pages.index')->with('success', 'Page created successfully.');
    }

    public function updatePage(Request $request, $id)
    {
        $page = DB::table('pages')->where('id', $id)->first();
        if (!$page) {
            return redirect()->route('admin.pages.index')->with('error', 'Page not found.');
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:pages,slug,' . $id,
            'content' => 'nullable|string',
            'is_published' => 'nullable|boolean',
        ]);

        $slug = !empty($validated['slug']) ? Str::slug($validated['slug']) : Str::slug($validated['title']);
        $isPublished = $request->has('is_published') ? 1 : 0;

        DB::table('pages')->where('id', $id)->update([
            'title' => $validated['title'],
            'slug' => $slug,
            'content' => $validated['content'] ?? null,
            'is_published' => $isPublished,
            'updated_at' => now(),
        ]);

        return redirect()->route('admin.pages.index')->with('success', 'Page updated successfully.');
    }

    public function deletePage($id)
    {
        DB::table('pages')->where('id', $id)->delete();

        return redirect()->route('admin.pages.index')->with('success', 'Page deleted successfully.');
    }

    public function reviews()
    {
        $reviews = DB::table('product_reviews')
            ->leftJoin('products', 'product_reviews.product_id', '=', 'products.id')
            ->select('product_reviews.*', 'products.title as product_title')
            ->orderByDesc('product_reviews.id')
            ->paginate(20);

        return view('backend.cms.reviews', compact('reviews'));
    }

    public function toggleReviewStatus(Request $request, $id)
    {
        $review = DB::table('product_reviews')->where('id', $id)->first();
        if (!$review) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Review not found.'], 404);
            }
            return back()->with('error', 'Review not found.');
        }

        $newStatus = $request->status;
        if (!in_array($newStatus, ['approved', 'rejected', 'pending'])) {
            $newStatus = $review->status === 'approved' ? 'rejected' : 'approved';
        }

        DB::table('product_reviews')->where('id', $id)->update([
            'status' => $newStatus,
            'updated_at' => now(),
        ]);

        if (!empty($review->product_id)) {
            ReviewService::syncProductReviewStats($review->product_id);
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'status' => $newStatus,
                'message' => 'Review status updated to ' . ucfirst($newStatus),
            ]);
        }

        return back()->with('success', 'Review status updated.');
    }

    public function deleteReview($id)
    {
        $review = DB::table('product_reviews')->where('id', $id)->first();
        DB::table('product_reviews')->where('id', $id)->delete();

        if ($review && !empty($review->product_id)) {
            ReviewService::syncProductReviewStats($review->product_id);
        }

        return redirect()->route('admin.reviews.index')->with('success', 'Review deleted successfully.');
    }
}
