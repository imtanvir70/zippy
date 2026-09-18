<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InventoryController extends Controller
{
    public function index()
    {
        $suppliers = DB::table('suppliers')
            ->leftJoin('purchases', 'suppliers.id', '=', 'purchases.supplier_id')
            ->select('suppliers.*', DB::raw('COUNT(purchases.id) as purchases_count'))
            ->groupBy('suppliers.id')
            ->orderByDesc('suppliers.id')
            ->get();

        $purchases = DB::table('purchases')
            ->leftJoin('suppliers', 'purchases.supplier_id', '=', 'suppliers.id')
            ->leftJoin('products', 'purchases.product_id', '=', 'products.id')
            ->select('purchases.*', 'suppliers.name as supplier_name', 'products.title as product_title')
            ->orderByDesc('purchases.id')
            ->paginate(15);

        $products = DB::table('products')
            ->select('id', 'title', 'sku', 'stock_qty', 'price')
            ->orderBy('title')
            ->get();

        $lowStockProducts = DB::table('products')
            ->where('stock_qty', '<', 10)
            ->orderBy('stock_qty')
            ->get();

        return view('backend.inventory.index', compact('suppliers', 'purchases', 'products', 'lowStockProducts'));
    }

    public function storeSupplier(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'company_name' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
        ]);

        DB::table('suppliers')->insert([
            'name' => $validated['name'],
            'company_name' => $validated['company_name'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'email' => $validated['email'] ?? null,
            'address' => $validated['address'] ?? null,
            'is_active' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('admin.inventory.index')->with('success', 'Supplier created successfully.');
    }

    public function deleteSupplier($id)
    {
        DB::table('suppliers')->where('id', $id)->delete();

        return redirect()->route('admin.inventory.index')->with('success', 'Supplier deleted successfully.');
    }

    public function storePurchase(Request $request)
    {
        $validated = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'product_id' => 'required|exists:products,id',
            'invoice_no' => 'nullable|string|max:100',
            'quantity' => 'required|integer|min:1',
            'unit_cost' => 'required|numeric|min:0',
            'purchase_date' => 'required|date',
            'note' => 'nullable|string',
        ]);

        $totalCost = $validated['quantity'] * $validated['unit_cost'];

        DB::transaction(function () use ($validated, $totalCost) {
            DB::table('purchases')->insert([
                'supplier_id' => $validated['supplier_id'],
                'product_id' => $validated['product_id'],
                'invoice_no' => $validated['invoice_no'] ?? null,
                'quantity' => $validated['quantity'],
                'unit_cost' => $validated['unit_cost'],
                'total_cost' => $totalCost,
                'purchase_date' => $validated['purchase_date'],
                'note' => $validated['note'] ?? null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('products')->where('id', $validated['product_id'])->increment('stock_qty', $validated['quantity'], [
                'cost_price' => $validated['unit_cost'],
                'updated_at' => now(),
            ]);
        });

        \App\Services\Frontend\FrontendCacheService::flushProducts((int) $validated['product_id']);

        return redirect()->route('admin.inventory.index')->with('success', 'Purchase recorded and stock updated.');
    }
}
