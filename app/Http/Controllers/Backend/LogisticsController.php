<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\Courier\CourierManager;
use App\Services\Audit\AuditLoggerService;
use Yajra\DataTables\Facades\DataTables;

class LogisticsController extends Controller
{
    protected CourierManager $courierManager;
    protected AuditLoggerService $auditLogger;

    public function __construct(CourierManager $courierManager, AuditLoggerService $auditLogger)
    {
        $this->courierManager = $courierManager;
        $this->auditLogger = $auditLogger;
    }

    public function index(Request $request)
    {
        if ($request->ajax() && !$request->has('_no_dt')) {
            $query = DB::table('courier_consignments')
                ->join('orders', 'courier_consignments.order_id', '=', 'orders.id')
                ->select('courier_consignments.*', 'orders.order_number', 'orders.customer_name', 'orders.customer_phone', 'orders.district', 'orders.total');

            if ($request->filled('provider') && $request->provider !== 'all') {
                $query->where('courier_consignments.provider', $request->provider);
            }

            return DataTables::of($query)
                ->addIndexColumn()
                ->editColumn('tracking_code', function ($row) {
                    return '<code class="fw-bold text-primary font-monospace">' . e($row->tracking_code) . '</code>';
                })
                ->addColumn('order_info', function ($row) {
                    $url = route('admin.orders.show', $row->order_id);
                    return '<a href="' . $url . '" class="fw-bold text-decoration-none text-primary">' . e($row->order_number) . '</a>';
                })
                ->addColumn('recipient', function ($row) {
                    $name = e($row->customer_name);
                    $phone = e($row->customer_phone);
                    $dist = e($row->district ?? 'Dhaka');
                    return '<div class="fw-semibold text-body">' . $name . '</div><span class="small text-muted font-monospace"><i class="fa-solid fa-phone fa-xs me-1"></i>' . $phone . '</span> <span class="badge bg-body-secondary text-body border ms-1">' . $dist . '</span>';
                })
                ->editColumn('provider', function ($row) {
                    return '<span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-2.5 py-1 text-uppercase font-monospace">' . e($row->provider) . '</span>';
                })
                ->editColumn('status', function ($row) {
                    $st = strtolower($row->status ?? 'pending');
                    $class = $st === 'delivered' ? 'success' : ($st === 'cancelled' ? 'danger' : 'info');
                    return '<span class="badge bg-' . $class . '-subtle text-' . $class . ' border border-' . $class . '-subtle rounded-pill px-2.5 py-1">' . ucfirst($st) . '</span>';
                })
                ->editColumn('created_at', function ($row) {
                    return '<span class="small text-muted">' . date('d M Y, h:i A', strtotime($row->created_at)) . '</span>';
                })
                ->rawColumns(['tracking_code', 'order_info', 'recipient', 'provider', 'status', 'created_at'])
                ->make(true);
        }

        $stats = [
            'total' => DB::table('courier_consignments')->count(),
            'steadfast' => DB::table('courier_consignments')->where('provider', 'steadfast')->count(),
            'pathao' => DB::table('courier_consignments')->where('provider', 'pathao')->count(),
            'redx' => DB::table('courier_consignments')->where('provider', 'redx')->count(),
        ];

        return view('backend.logistics.index', compact('stats'));
    }

    public function dispatch(Request $request, $orderId)
    {
        $request->validate([
            'provider' => 'required|string|in:steadfast,pathao,redx,ecourier,paperfly'
        ]);

        $result = $this->courierManager->dispatchOrder((int)$orderId, $request->provider, $this->auditLogger);

        if ($request->expectsJson()) {
            return response()->json($result);
        }

        return back()->with($result['success'] ? 'success' : 'error', $result['message']);
    }

    public function track(Request $request, $trackingCode)
    {
        $consignment = DB::table('courier_consignments')->where('tracking_code', $trackingCode)->first();
        if (!$consignment) {
            return response()->json(['success' => false, 'message' => 'Tracking record not found.'], 404);
        }

        $service = $this->courierManager->resolve($consignment->provider);
        $result = $service->trackConsignment($trackingCode, []);

        return response()->json($result);
    }

    public function bulkDispatch(Request $request)
    {
        $request->validate([
            'order_ids' => 'required|array|min:1',
            'order_ids.*' => 'integer',
            'provider' => 'required|string|in:steadfast,pathao,redx,ecourier,paperfly'
        ]);

        $orderIds = $request->input('order_ids', []);
        $provider = $request->input('provider');

        $successCount = 0;
        $failedCount = 0;
        $messages = [];

        foreach ($orderIds as $orderId) {
            $result = $this->courierManager->dispatchOrder((int) $orderId, $provider, $this->auditLogger);
            if ($result['success']) {
                $successCount++;
            } else {
                $failedCount++;
                $messages[] = "Order #{$orderId}: " . ($result['message'] ?? 'Failed');
            }
        }

        $message = "{$successCount} টি অর্ডার সফলভাবে {$provider}-এ বুক করা হয়েছে।";
        if ($failedCount > 0) {
            $message .= " {$failedCount} টি অর্ডার বুকিং ব্যর্থ হয়েছে।";
        }

        if ($request->expectsJson()) {
            return response()->json([
                'success' => $successCount > 0,
                'success_count' => $successCount,
                'failed_count' => $failedCount,
                'message' => $message,
                'errors' => $messages
            ]);
        }

        return back()->with($successCount > 0 ? 'success' : 'error', $message);
    }

    public function webhook(Request $request, $provider)
    {
        $result = $this->courierManager->processWebhook($provider, $request->all());
        return response()->json($result);
    }
}
