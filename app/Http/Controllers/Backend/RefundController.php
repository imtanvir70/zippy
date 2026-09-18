<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Services\Audit\AuditLoggerService;
use Yajra\DataTables\Facades\DataTables;

class RefundController extends Controller
{
    protected AuditLoggerService $auditLogger;

    public function __construct(AuditLoggerService $auditLogger)
    {
        $this->auditLogger = $auditLogger;
    }

    public function index(Request $request)
    {
        if ($request->ajax() && !$request->has('_no_dt')) {
            $query = DB::table('refunds')
                ->join('orders', 'refunds.order_id', '=', 'orders.id')
                ->select('refunds.*', 'orders.order_number', 'orders.customer_name', 'orders.customer_phone', 'orders.total as order_total');

            if ($request->filled('status') && $request->status !== 'all') {
                $query->where('refunds.status', $request->status);
            }

            return DataTables::of($query)
                ->addIndexColumn()
                ->editColumn('refund_number', function ($row) {
                    return '<code class="fw-bold text-primary font-monospace">' . e($row->refund_number) . '</code>';
                })
                ->addColumn('order_info', function ($row) {
                    $url = route('admin.orders.show', $row->order_id);
                    return '<a href="' . $url . '" class="fw-bold text-decoration-none text-primary">' . e($row->order_number) . '</a>';
                })
                ->addColumn('customer', function ($row) {
                    return '<div class="fw-semibold text-body">' . e($row->customer_name) . '</div><span class="small text-muted font-monospace"><i class="fa-solid fa-phone fa-xs me-1"></i>' . e($row->customer_phone) . '</span>';
                })
                ->editColumn('amount', function ($row) {
                    return '<div class="fw-bold text-danger">৳ ' . number_format($row->amount, 0) . '</div><span class="small text-muted">' . ucfirst(e($row->refund_type)) . ' Refund</span>';
                })
                ->editColumn('reason', function ($row) {
                    return '<span class="small text-muted">' . e($row->reason) . '</span>';
                })
                ->editColumn('status', function ($row) {
                    $st = strtolower($row->status ?? 'pending');
                    $class = $st === 'completed' ? 'success' : ($st === 'approved' ? 'info' : ($st === 'rejected' ? 'danger' : 'warning'));
                    return '<span class="badge bg-' . $class . '-subtle text-' . $class . ' border border-' . $class . '-subtle rounded-pill px-2.5 py-1">' . ucfirst($st) . '</span>';
                })
                ->addColumn('actions', function ($row) {
                    if ($row->status === 'pending') {
                        return '<div class="d-inline-flex gap-1">
                            <button type="button" class="btn btn-sm btn-success" onclick="updateRefundStatus(' . $row->id . ', \'approved\')" title="Approve"><i class="fa-solid fa-check"></i></button>
                            <button type="button" class="btn btn-sm btn-danger" onclick="updateRefundStatus(' . $row->id . ', \'rejected\')" title="Reject"><i class="fa-solid fa-xmark"></i></button>
                        </div>';
                    } elseif ($row->status === 'approved') {
                        return '<button type="button" class="btn btn-sm btn-primary" onclick="updateRefundStatus(' . $row->id . ', \'completed\')" title="Mark Completed"><i class="fa-solid fa-circle-check me-1"></i> Complete</button>';
                    }
                    return '<span class="text-muted small">No actions</span>';
                })
                ->rawColumns(['refund_number', 'order_info', 'customer', 'amount', 'reason', 'status', 'actions'])
                ->make(true);
        }

        $stats = [
            'total_refunded' => DB::table('refunds')->where('status', 'completed')->sum('amount'),
            'pending_count' => DB::table('refunds')->where('status', 'pending')->count(),
            'approved_count' => DB::table('refunds')->where('status', 'approved')->count(),
            'completed_count' => DB::table('refunds')->where('status', 'completed')->count(),
        ];

        return view('backend.refunds.index', compact('stats'));
    }

    public function store(Request $request, $orderId)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
            'reason' => 'required|string|max:255',
            'refund_type' => 'required|in:full,partial',
            'restock_inventory' => 'nullable'
        ]);

        return DB::transaction(function () use ($request, $orderId) {
            $order = DB::table('orders')->where('id', $orderId)->first();
            if (!$order) {
                return response()->json(['success' => false, 'message' => 'Order not found.'], 404);
            }

            $amount = (float) $request->amount;
            $restock = $request->has('restock_inventory');
            $refundNumber = 'RF-' . strtoupper(Str::random(8));

            $refundId = DB::table('refunds')->insertGetId([
                'refund_number' => $refundNumber,
                'order_id' => $orderId,
                'refund_type' => $request->refund_type,
                'amount' => $amount,
                'reason' => $request->reason,
                'restock_inventory' => $restock ? 1 : 0,
                'status' => 'completed',
                'processed_by' => session('admin_name', 'Administrator'),
                'admin_notes' => $request->admin_notes,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Update order status and refunded amount
            DB::table('orders')->where('id', $orderId)->update([
                'refunded_amount' => DB::raw("refunded_amount + {$amount}"),
                'is_refunded' => 1,
                'order_status' => $request->refund_type === 'full' ? 'cancelled' : $order->order_status,
                'updated_at' => now(),
            ]);

            // Restock inventory if checked
            if ($restock) {
                $items = DB::table('order_items')->where('order_id', $orderId)->get();
                foreach ($items as $item) {
                    if ($item->product_id) {
                        DB::table('products')->where('id', $item->product_id)->increment('stock_qty', $item->quantity);
                    }
                }
            }

            $this->auditLogger->logAction('refund', 'orders', $orderId, [
                'order_number' => $order->order_number,
                'refunded_amount' => $amount,
            ], [
                'refund_number' => $refundNumber,
                'restocked' => $restock
            ], "Processed {$request->refund_type} refund of ৳{$amount} for Order #{$order->order_number}");

            return response()->json([
                'success' => true,
                'message' => "Refund #{$refundNumber} processed successfully! ৳{$amount} refunded.",
                'refund_id' => $refundId
            ]);
        });
    }
}
