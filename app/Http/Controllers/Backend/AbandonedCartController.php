<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Services\Order\OrderNumberService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class AbandonedCartController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax() && !$request->has('_no_dt')) {
            $query = DB::table('abandoned_carts')->select('abandoned_carts.*');

            if ($request->filled('recovered') && $request->recovered !== 'all') {
                $query->where('is_recovered', $request->recovered === '1' ? 1 : 0);
            }

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('lead_info', function ($row) {
                    $name = e($row->customer_name ?: 'Guest Lead');
                    $phone = e($row->customer_phone ?: ($row->phone ?? 'No phone'));
                    $session = e($row->session_id ?: 'N/A');
                    return '<div class="fw-bold text-dark">' . $name . '</div><div class="small font-monospace text-primary"><i class="fa-solid fa-phone fa-xs me-1 text-muted"></i>' . $phone . '</div><code class="small text-muted" style="font-size: 0.72rem;">' . substr($session, 0, 16) . '...</code>';
                })
                ->addColumn('cart_preview', function ($row) {
                    $items = json_decode($row->cart_data, true) ?: [];
                    $count = is_array($items) ? count($items) : 0;
                    $firstItem = is_array($items) && !empty($items) ? reset($items) : null;
                    $title = $firstItem ? e($firstItem['title'] ?? ($firstItem['name'] ?? 'Product')) : 'Cart Items';
                    return '<span class="badge bg-light text-dark border">' . $count . ' Items</span><div class="small text-muted text-truncate mt-1" style="max-width: 200px;">' . $title . ($count > 1 ? ' +' . ($count - 1) . ' more' : '') . '</div>';
                })
                ->editColumn('total_amount', function ($row) {
                    return '<div class="fw-bold text-danger">৳ ' . number_format($row->total_amount, 0) . '</div>';
                })
                ->editColumn('last_activity_at', function ($row) {
                    return '<span class="small text-muted">' . date('d M Y, h:i A', strtotime($row->last_activity_at)) . '</span>';
                })
                ->editColumn('is_recovered', function ($row) {
                    if ($row->is_recovered) {
                        return '<span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-1"><i class="fa-solid fa-circle-check me-1"></i> Recovered</span>';
                    }
                    return '<span class="badge bg-warning-subtle text-warning border border-warning-subtle rounded-pill px-2.5 py-1"><i class="fa-solid fa-clock me-1"></i> Abandoned</span>';
                })
                ->addColumn('actions', function ($row) {
                    $phoneRaw = $row->customer_phone ?: ($row->phone ?? '');
                    $digits = preg_replace('/[^0-9]/', '', $phoneRaw);
                    $cleanPhone = str_starts_with($digits, '880') ? $digits : (str_starts_with($digits, '0') ? '88' . $digits : '880' . $digits);

                    $items = json_decode($row->cart_data, true) ?: [];
                    $firstItem = is_array($items) && !empty($items) ? reset($items) : null;
                    $itemTitle = $firstItem ? ($firstItem['title'] ?? ($firstItem['name'] ?? 'পণ্য')) : 'আপনার পছন্দের গ্যাজেট';
                    $custName = trim($row->customer_name ?: 'সম্মানিত গ্রাহক');

                    $waMessage = "আসসালামু আলাইকুম {$custName}! ZippyBD-তে আপনার কার্টে '{$itemTitle}' অর্ডারটি অসম্পূর্ণ রয়েছে। আপনার অর্ডারটি এখনই কনফার্ম করতে বা কোনো সহায়তা লাগলে আমাদের জানান। আমরা দিচ্ছি দ্রুততম হোম ডেলিভারি ও ক্যাশ অন ডেলিভারি সুবিধা! চেকআউট সম্পন্ন করতে ক্লিক করুন: " . url('/checkout');

                    $waBtn = ($digits && strlen($digits) >= 10) ? '<a href="https://wa.me/' . $cleanPhone . '?text=' . urlencode($waMessage) . '" target="_blank" class="btn btn-sm btn-success text-white" title="WhatsApp Recovery Message"><i class="fa-brands fa-whatsapp me-1"></i> WhatsApp</a>' : '';
                    $recoverBtn = !$row->is_recovered ? '<button type="button" class="btn btn-sm btn-light border text-primary" onclick="markCartRecovered(' . $row->id . ')" title="Mark Recovered"><i class="fa-solid fa-check"></i></button>' : '';
                    $editBtn = '<a href="' . route('admin.abandoned.edit_items', $row->id) . '" class="btn btn-sm btn-outline-primary" title="Edit Items"><i class="fa-solid fa-pen-to-square"></i></a>';
                    return '<div class="d-inline-flex gap-1">' . $waBtn . $editBtn . $recoverBtn . '</div>';
                })
                ->rawColumns(['lead_info', 'cart_preview', 'total_amount', 'last_activity_at', 'is_recovered', 'actions'])
                ->make(true);
        }

        $stats = [
            'total_abandoned' => DB::table('abandoned_carts')->where('is_recovered', 0)->count(),
            'total_value' => DB::table('abandoned_carts')->where('is_recovered', 0)->sum('total_amount'),
            'recovered_count' => DB::table('abandoned_carts')->where('is_recovered', 1)->count(),
        ];

        return view('backend.abandoned.index', compact('stats'));
    }

    public function markRecovered($id)
    {
        $cart = DB::table('abandoned_carts')->where('id', $id)->first();
        if (!$cart) {
            return response()->json(['success' => false, 'message' => 'Cart not found.']);
        }

        if ($cart->is_recovered) {
            return response()->json(['success' => false, 'message' => 'Cart is already recovered.']);
        }

        DB::beginTransaction();
        try {
            $orderNumber = OrderNumberService::generate();

            $orderId = DB::table('orders')->insertGetId([
                'order_number' => $orderNumber,
                'customer_name' => $cart->customer_name ?: 'Guest',
                'customer_phone' => $cart->customer_phone ?: '',
                'customer_address' => 'Update Address',
                'subtotal' => $cart->total_amount,
                'shipping_cost' => 0,
                'total' => $cart->total_amount,
                'payment_method' => 'cod',
                'payment_status' => 'unpaid',
                'order_status' => 'pending',
                'call_status' => 'pending_call',
                'is_guest' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $cartData = json_decode($cart->cart_data, true) ?: [];
            $insertItems = [];
            foreach ($cartData as $item) {
                $qty = (int) ($item['qty'] ?? ($item['quantity'] ?? 1));
                $price = (float) ($item['price'] ?? 0);

                $insertItems[] = [
                    'order_id' => $orderId,
                    'product_id' => $item['id'] ?? null,
                    'product_title' => $item['title'] ?? 'Unknown',
                    'product_image' => $item['image'] ?? null,
                    'unit_price' => $price,
                    'quantity' => $qty,
                    'total_price' => $qty * $price,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            if (!empty($insertItems)) {
                DB::table('order_items')->insert($insertItems);
            }

            DB::table('abandoned_carts')->where('id', $id)->update([
                'is_recovered' => 1,
                'updated_at' => now(),
            ]);

            DB::commit();

            return response()->json(['success' => true, 'message' => 'Cart recovered and moved to orders successfully!']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    }

    public function editCartItems($id)
    {
        $cart = DB::table('abandoned_carts')->where('id', $id)->first();
        if (!$cart) {
            return redirect()->route('admin.abandoned.index')->with('error', 'Cart not found.');
        }

        $cartData = json_decode($cart->cart_data, true) ?: [];
        $items = [];
        foreach ($cartData as $item) {
            $items[] = [
                'product_id' => $item['id'] ?? null,
                'product_title' => $item['title'] ?? ($item['name'] ?? 'Unknown'),
                'product_image' => $item['image'] ?? null,
                'unit_price' => (float) ($item['price'] ?? 0),
                'quantity' => (int) ($item['qty'] ?? ($item['quantity'] ?? 1))
            ];
        }

        $type = 'abandoned';
        $discount = 0; // Abandoned carts typically don't have coupon logic applied in the same way, or it's embedded.
        $shipping = 0; // Same for shipping, typically calculated at checkout step 2
        $backUrl = route('admin.abandoned.index');
        $submitUrl = route('admin.abandoned.update_items', $cart->id);

        // Pass a dummy order object to avoid view error since view expects $order or $cart
        $order = (object) ['order_number' => 'Cart ' . $cart->id];

        return view('backend.orders.edit_items', compact('cart', 'order', 'items', 'type', 'discount', 'shipping', 'backUrl', 'submitUrl'));
    }

    public function updateCartItems(Request $request, $id)
    {
        $cart = DB::table('abandoned_carts')->where('id', $id)->first();
        if (!$cart) {
            return redirect()->route('admin.abandoned.index')->with('error', 'Cart not found.');
        }

        $itemsJson = $request->input('items_json', '[]');
        $itemsData = json_decode($itemsJson, true);
        if (!is_array($itemsData) || count($itemsData) === 0) {
            return back()->with('error', 'Cart must have at least one item.');
        }

        $newCartData = [];
        $totalAmount = 0;
        foreach ($itemsData as $item) {
            $qty = (int) ($item['quantity'] ?? 1);
            $price = (float) ($item['unit_price'] ?? 0);
            $totalAmount += ($qty * $price);

            $pid = $item['product_id'] ?? null;
            $newCartData[$pid . '_' . uniqid()] = [
                'id' => $pid,
                'title' => $item['product_title'] ?? 'Unknown Product',
                'image' => $item['product_image'] ?? null,
                'price' => $price,
                'qty' => $qty,
                'total' => $qty * $price,
            ];
        }

        DB::table('abandoned_carts')->where('id', $id)->update([
            'cart_data' => json_encode($newCartData, JSON_UNESCAPED_UNICODE),
            'total_amount' => $totalAmount,
            'updated_at' => now()
        ]);

        return redirect()->route('admin.abandoned.index')->with('success', 'Abandoned cart items updated successfully.');
    }
}
