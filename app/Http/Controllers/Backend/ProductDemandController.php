<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductDemandController extends Controller
{
    public function index(Request $request)
    {
        $status = trim((string) $request->input('status', ''));
        $search = trim((string) $request->input('search', ''));
        $sort = trim((string) $request->input('sort', 'hits_desc'));

        $query = DB::table('product_demands');

        if ($status !== '' && in_array($status, ['pending', 'under_review', 'planned', 'stocked', 'rejected'])) {
            $query->where('status', $status);
        }

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('query', 'LIKE', "%{$search}%")
                    ->orWhere('normalized_query', 'LIKE', "%{$search}%")
                    ->orWhere('category_hint', 'LIKE', "%{$search}%")
                    ->orWhere('customer_name', 'LIKE', "%{$search}%")
                    ->orWhere('customer_phone', 'LIKE', "%{$search}%")
                    ->orWhere('admin_notes', 'LIKE', "%{$search}%");
            });
        }

        if ($sort === 'recent') {
            $query->orderByDesc('last_requested_at');
        } elseif ($sort === 'oldest') {
            $query->orderBy('last_requested_at');
        } else {
            $query->orderByDesc('hits_count')->orderByDesc('last_requested_at');
        }

        $demands = $query->paginate(20)->withQueryString();

        $metrics = [
            'total' => DB::table('product_demands')->count(),
            'total_hits' => (int) DB::table('product_demands')->sum('hits_count'),
            'pending' => DB::table('product_demands')->where('status', 'pending')->count(),
            'under_review' => DB::table('product_demands')->where('status', 'under_review')->count(),
            'planned' => DB::table('product_demands')->where('status', 'planned')->count(),
            'stocked' => DB::table('product_demands')->where('status', 'stocked')->count(),
            'rejected' => DB::table('product_demands')->where('status', 'rejected')->count(),
        ];

        return view('backend.product_demands.index', compact('demands', 'metrics', 'status', 'search', 'sort'));
    }

    public function updateStatus(Request $request, int $id)
    {
        $request->validate([
            'status' => 'required|string|in:pending,under_review,planned,stocked,rejected',
        ]);

        $demand = DB::table('product_demands')->where('id', $id)->first();
        if (!$demand) {
            return response()->json(['success' => false, 'message' => 'Record not found'], 404);
        }

        DB::table('product_demands')->where('id', $id)->update([
            'status' => $request->input('status'),
            'updated_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Status updated successfully',
            'new_status' => $request->input('status'),
        ]);
    }

    public function updateNotes(Request $request, int $id)
    {
        $request->validate([
            'admin_notes' => 'nullable|string|max:2000',
        ]);

        $demand = DB::table('product_demands')->where('id', $id)->first();
        if (!$demand) {
            return response()->json(['success' => false, 'message' => 'Record not found'], 404);
        }

        DB::table('product_demands')->where('id', $id)->update([
            'admin_notes' => $request->input('admin_notes'),
            'updated_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Notes saved successfully',
            'notes' => $request->input('admin_notes'),
        ]);
    }

    public function destroy(Request $request, int $id)
    {
        $demand = DB::table('product_demands')->where('id', $id)->first();
        if (!$demand) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Record not found'], 404);
            }
            return redirect()->back()->with('error', 'Record not found');
        }

        DB::table('product_demands')->where('id', $id)->delete();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Demand record deleted successfully']);
        }

        return redirect()->route('admin.product_demands.index')->with('success', 'Demand record deleted successfully');
    }
}
