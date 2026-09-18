<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class OrderBumpController extends Controller
{
    public function index(): View
    {
        $bumps = DB::table('order_bumps')
            ->leftJoin('products as primary_prod', 'order_bumps.product_id', '=', 'primary_prod.id')
            ->leftJoin('products as bump_prod', 'order_bumps.bump_product_id', '=', 'bump_prod.id')
            ->select(
                'order_bumps.*',
                'primary_prod.title as primary_product_title',
                'bump_prod.title as bump_product_title',
                'bump_prod.price as bump_original_price',
                'bump_prod.main_image as bump_image'
            )
            ->orderByDesc('order_bumps.id')
            ->paginate(15);

        $products = DB::table('products')
            ->where('is_active', 1)
            ->select('id', 'title', 'price')
            ->orderBy('title')
            ->get();

        return view('backend.promotions.bumps', compact('bumps', 'products'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'product_id' => 'nullable|integer',
            'bump_product_id' => 'required|integer',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
        ]);

        DB::table('order_bumps')->insert([
            'product_id' => !empty($validated['product_id']) ? (int) $validated['product_id'] : null,
            'bump_product_id' => (int) $validated['bump_product_id'],
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'price' => (float) $validated['price'],
            'is_active' => 1,
            'sort_order' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('admin.bumps.index')->with('success', 'Order bump created successfully.');
    }

    public function toggle($id): RedirectResponse
    {
        $bump = DB::table('order_bumps')->where('id', $id)->first();
        if ($bump) {
            DB::table('order_bumps')->where('id', $id)->update([
                'is_active' => $bump->is_active ? 0 : 1,
                'updated_at' => now(),
            ]);
        }

        return back()->with('success', 'Order bump status updated.');
    }

    public function destroy($id): RedirectResponse
    {
        DB::table('order_bumps')->where('id', $id)->delete();
        return back()->with('success', 'Order bump deleted successfully.');
    }
}
