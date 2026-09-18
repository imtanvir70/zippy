<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\Audit\AuditLoggerService;
use Yajra\DataTables\Facades\DataTables;

class CustomerCrmController extends Controller
{
    protected AuditLoggerService $auditLogger;

    public function __construct(AuditLoggerService $auditLogger)
    {
        $this->auditLogger = $auditLogger;
    }

    public function index(Request $request)
    {
        if ($request->ajax() && !$request->has('_no_dt')) {
            $query = DB::table('orders')
                ->select(
                    'customer_phone',
                    DB::raw('MAX(customer_name) as name'),
                    DB::raw('MAX(customer_address) as address'),
                    DB::raw('MAX(district) as district'),
                    DB::raw('COUNT(*) as orders_count'),
                    DB::raw("SUM(CASE WHEN order_status != 'cancelled' THEN total ELSE 0 END) as total_spend"),
                    DB::raw('MAX(created_at) as last_order_date')
                )
                ->groupBy('customer_phone');

            $cleanUtf8 = function ($value) {
                return is_string($value) ? mb_convert_encoding($value, 'UTF-8', 'UTF-8') : $value;
            };

            $response = DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('customer_profile', function ($row) use ($cleanUtf8) {
                    $name = e($cleanUtf8($row->name) ?: 'Guest Customer');
                    $phone = e($cleanUtf8($row->customer_phone));
                    $initial = strtoupper(mb_substr($name, 0, 1, 'UTF-8'));
                    return '<div class="d-flex align-items-center gap-2.5">
                        <div class="rounded-circle bg-primary-subtle text-primary fw-bold d-inline-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                            ' . $initial . '
                        </div>
                        <div>
                            <div class="fw-bold text-body">' . $name . '</div>
                            <span class="text-muted small font-monospace">' . $phone . '</span>
                        </div>
                    </div>';
                })
                ->addColumn('location', function ($row) use ($cleanUtf8) {
                    $dist = e($cleanUtf8($row->district) ?? 'Dhaka');
                    $addr = e($cleanUtf8($row->address) ?? 'No address');
                    return '<div><span class="badge bg-body-secondary text-body border">' . $dist . '</span></div><span class="small text-muted text-truncate d-block" style="max-width: 220px;">' . $addr . '</span>';
                })
                ->editColumn('orders_count', function ($row) {
                    return '<span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-2.5 py-1">' . $row->orders_count . ' Orders</span>';
                })
                ->editColumn('total_spend', function ($row) {
                    return '<div class="fw-bold text-success">৳ ' . number_format($row->total_spend, 0) . '</div>';
                })
                ->editColumn('last_order_date', function ($row) {
                    return '<span class="small text-muted">' . date('d M Y, h:i A', strtotime($row->last_order_date)) . '</span>';
                })
                ->addColumn('actions', function ($row) use ($cleanUtf8) {
                    $phone = $cleanUtf8($row->customer_phone);
                    $showUrl = route('admin.crm.show', $phone);
                    return '<div class="d-inline-flex gap-1">
                        <a href="' . $showUrl . '" class="btn-action text-primary px-2.5 w-auto" title="View CRM 360 Profile"><i class="fa-solid fa-user-check me-1"></i> Profile</a>
                    </div>';
                })
                ->rawColumns(['customer_profile', 'location', 'orders_count', 'total_spend', 'last_order_date', 'actions'])
                ->make(true);

            return response()->json($response->getData(true), 200, [], JSON_INVALID_UTF8_SUBSTITUTE);
        }

        return view('backend.crm.index');
    }

    public function show($phone)
    {
        $customerOrders = DB::table('orders')->where('customer_phone', $phone)->orderByDesc('id')->get();
        if ($customerOrders->isEmpty()) {
            return redirect()->route('admin.customers.index')->with('error', 'Customer not found.');
        }

        $profile = [
            'phone' => $phone,
            'name' => $customerOrders->first()->customer_name,
            'address' => $customerOrders->first()->customer_address,
            'district' => $customerOrders->first()->district,
            'total_orders' => $customerOrders->count(),
            'total_spend' => $customerOrders->where('order_status', '!=', 'cancelled')->sum('total'),
            'last_order' => $customerOrders->first()->created_at,
        ];

        $wallet = DB::table('customer_wallets')->where('customer_phone', $phone)->first();
        $transactions = DB::table('wallet_transactions')->where('customer_phone', $phone)->orderByDesc('id')->get();

        return view('backend.crm.show', compact('profile', 'customerOrders', 'wallet', 'transactions'));
    }

    public function adjustWallet(Request $request, $phone)
    {
        $request->validate([
            'type' => 'required|in:credit,debit',
            'amount' => 'required|numeric|min:0',
            'points' => 'nullable|integer|min:0',
            'description' => 'required|string|max:255'
        ]);

        return DB::transaction(function () use ($request, $phone) {
            $amount = (float) $request->amount;
            $points = (int) ($request->points ?? 0);
            $isCredit = $request->type === 'credit';

            // Ensure wallet exists
            $wallet = DB::table('customer_wallets')->where('customer_phone', $phone)->first();
            if (!$wallet) {
                DB::table('customer_wallets')->insert([
                    'customer_phone' => $phone,
                    'balance' => $isCredit ? $amount : 0.00,
                    'reward_points' => $isCredit ? $points : 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } else {
                DB::table('customer_wallets')->where('customer_phone', $phone)->update([
                    'balance' => $isCredit ? DB::raw("balance + {$amount}") : DB::raw("GREATEST(0, balance - {$amount})"),
                    'reward_points' => $isCredit ? DB::raw("reward_points + {$points}") : DB::raw("GREATEST(0, reward_points - {$points})"),
                    'updated_at' => now(),
                ]);
            }

            // Insert Transaction Log
            DB::table('wallet_transactions')->insert([
                'customer_phone' => $phone,
                'type' => $request->type,
                'amount' => $amount,
                'points' => $points,
                'description' => $request->description,
                'created_at' => now(),
            ]);

            $this->auditLogger->logAction('update', 'crm', $phone, null, [
                'type' => $request->type,
                'amount' => $amount,
                'points' => $points,
            ], "Adjusted wallet for customer {$phone}: {$request->type} ৳{$amount}");

            return back()->with('success', 'Customer wallet adjusted successfully!');
        });
    }
}
