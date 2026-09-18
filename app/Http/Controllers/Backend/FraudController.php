<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\Fraud\FraudDetectionService;
use App\Services\Audit\AuditLoggerService;
use Yajra\DataTables\Facades\DataTables;

class FraudController extends Controller
{
    protected FraudDetectionService $fraudService;
    protected AuditLoggerService $auditLogger;

    public function __construct(FraudDetectionService $fraudService, AuditLoggerService $auditLogger)
    {
        $this->fraudService = $fraudService;
        $this->auditLogger = $auditLogger;
    }

    public function index(Request $request)
    {
        if ($request->ajax() && !$request->has('_no_dt')) {
            $query = DB::table('orders')->select('orders.*');

            if ($request->filled('status') && $request->status !== 'all') {
                $query->where('fraud_status', $request->status);
            }

            if ($request->filled('min_score')) {
                $query->where('fraud_score', '>=', (int) $request->min_score);
            }

            return DataTables::of($query)
                ->addIndexColumn()
                ->editColumn('order_number', function ($order) {
                    $url = route('admin.orders.show', $order->id);
                    return '<a href="' . $url . '" class="fw-bold text-decoration-none text-primary">' . e($order->order_number) . '</a>';
                })
                ->addColumn('customer', function ($order) {
                    $name = e($order->customer_name);
                    $phone = e($order->customer_phone);
                    $dist = e($order->district ?? 'Dhaka');
                    return '<div class="fw-semibold text-body">' . $name . '</div><span class="small text-muted font-monospace"><i class="fa-solid fa-phone fa-xs me-1"></i>' . $phone . '</span> <span class="badge bg-body-secondary text-body border ms-1">' . $dist . '</span>';
                })
                ->editColumn('total', function ($order) {
                    return '<div class="fw-bold text-body fs-6">৳ ' . number_format($order->total, 0) . '</div>';
                })
                ->editColumn('fraud_score', function ($order) {
                    $score = (int) ($order->fraud_score ?? 0);
                    $color = $score >= 70 ? 'danger' : ($score >= 35 ? 'warning' : 'success');
                    return '<div class="d-flex align-items-center gap-2"><div class="progress flex-grow-1" style="height: 6px;"><div class="progress-bar bg-' . $color . '" style="width: ' . $score . '%"></div></div><span class="badge bg-' . $color . '-subtle text-' . $color . ' border border-' . $color . '-subtle font-monospace">' . $score . '/100</span></div>';
                })
                ->editColumn('fraud_status', function ($order) {
                    $st = $order->fraud_status ?? 'safe';
                    $badgeClass = $st === 'flagged_fraud' ? 'bg-danger text-white' : ($st === 'suspicious' ? 'bg-warning text-dark' : 'bg-success-subtle text-success border border-success-subtle');
                    return '<span class="badge ' . $badgeClass . ' rounded-pill px-2.5 py-1">' . ucwords(str_replace('_', ' ', $st)) . '</span>';
                })
                ->addColumn('actions', function ($order) {
                    $showUrl = route('admin.orders.show', $order->id);
                    return '<div class="d-inline-flex gap-1">
                        <button type="button" class="btn-action text-primary px-2.5 w-auto" onclick="reEvaluateFraud(' . $order->id . ')" title="Re-scan Risk Engine"><i class="fa-solid fa-arrows-rotate me-1"></i> Re-Scan</button>
                        <a href="' . $showUrl . '" class="btn-action text-secondary" title="View Order Details"><i class="fa-solid fa-eye"></i></a>
                    </div>';
                })
                ->rawColumns(['order_number', 'customer', 'total', 'fraud_score', 'fraud_status', 'actions'])
                ->make(true);
        }

        $stats = [
            'flagged_count' => DB::table('orders')->where('fraud_status', 'flagged_fraud')->count(),
            'suspicious_count' => DB::table('orders')->where('fraud_status', 'suspicious')->count(),
            'safe_count' => DB::table('orders')->where('fraud_status', 'safe')->count(),
        ];

        return view('backend.fraud.index', compact('stats'));
    }

    public function evaluate($orderId)
    {
        $order = DB::table('orders')->where('id', $orderId)->first();
        if (!$order) {
            return response()->json(['success' => false, 'message' => 'Order not found.'], 404);
        }

        $evaluation = $this->fraudService->evaluateOrder((array) $order);

        // Update order with new risk score
        DB::table('orders')->where('id', $orderId)->update([
            'fraud_score' => $evaluation['fraud_score'],
            'fraud_status' => $evaluation['fraud_status'],
            'fraud_notes' => implode(' | ', $evaluation['reasons']),
            'updated_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'evaluation' => $evaluation
        ]);
    }

    public function updateStatus(Request $request, $orderId)
    {
        $request->validate([
            'fraud_status' => 'required|in:safe,suspicious,flagged_fraud,verified',
            'fraud_notes' => 'nullable|string|max:500'
        ]);

        DB::table('orders')->where('id', $orderId)->update([
            'fraud_status' => $request->fraud_status,
            'fraud_notes' => $request->fraud_notes,
            'updated_at' => now(),
        ]);

        $this->auditLogger->logAction('update', 'fraud', $orderId, null, [
            'fraud_status' => $request->fraud_status,
            'fraud_notes' => $request->fraud_notes
        ], "Updated fraud status to {$request->fraud_status} for Order ID {$orderId}");

        return response()->json([
            'success' => true,
            'message' => "Order fraud status updated to {$request->fraud_status}."
        ]);
    }
}
