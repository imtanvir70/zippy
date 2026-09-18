<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use App\Services\Media\ImageOptimizerService;
use Yajra\DataTables\Facades\DataTables;
use App\Services\Frontend\FrontendCacheService;

class AdminController extends Controller
{

    public function dashboard(Request $request)
    {
        $today = now()->toDateString();
        $defaultStart = now()->startOfMonth()->toDateString();
        $defaultEnd = now()->endOfMonth()->toDateString();

        $rawStart = $request->input('start_date');
        $rawEnd = $request->input('end_date');

        try {
            $startCarbon = $rawStart ? \Carbon\Carbon::parse($rawStart)->startOfDay() : now()->startOfMonth()->startOfDay();
        } catch (\Throwable $e) {
            $startCarbon = now()->startOfMonth()->startOfDay();
        }

        try {
            $endCarbon = $rawEnd ? \Carbon\Carbon::parse($rawEnd)->endOfDay() : now()->endOfMonth()->endOfDay();
        } catch (\Throwable $e) {
            $endCarbon = now()->endOfMonth()->endOfDay();
        }

        if ($startCarbon->gt($endCarbon)) {
            $temp = $startCarbon;
            $startCarbon = $endCarbon->copy()->startOfDay();
            $endCarbon = $temp->copy()->endOfDay();
        }

        $startDate = $startCarbon->toDateString();
        $endDate = $endCarbon->toDateString();
        $isFiltered = ($rawStart !== null || $rawEnd !== null) && ($startDate !== $defaultStart || $endDate !== $defaultEnd);

        $startDateTime = $startCarbon->toDateTimeString();
        $endDateTime = $endCarbon->toDateTimeString();

        $todayStats = DB::table('orders')
            ->whereDate('created_at', $today)
            ->selectRaw("
                COUNT(*) as count,
                COALESCE(SUM(CASE WHEN order_status != 'cancelled' THEN total ELSE 0 END), 0) as revenue
            ")
            ->first();
        $todayOrders = (int) ($todayStats->count ?? 0);
        $todayRevenue = (float) ($todayStats->revenue ?? 0.0);

        $rangeOrdersQuery = DB::table('orders')->whereBetween('created_at', [$startDateTime, $endDateTime]);

        $metrics = (clone $rangeOrdersQuery)
            ->selectRaw("
                COUNT(*) as total_orders,
                COALESCE(SUM(CASE WHEN order_status != 'cancelled' THEN total ELSE 0 END), 0) as monthly_revenue,
                COALESCE(SUM(CASE WHEN order_status = 'pending' THEN 1 ELSE 0 END), 0) as pending_orders,
                COALESCE(SUM(CASE WHEN order_status = 'delivered' THEN 1 ELSE 0 END), 0) as delivered_orders,
                COALESCE(SUM(CASE WHEN order_status = 'processing' THEN 1 ELSE 0 END), 0) as processing_orders,
                COALESCE(SUM(CASE WHEN order_status = 'shipped' THEN 1 ELSE 0 END), 0) as shipped_orders,
                COALESCE(SUM(CASE WHEN order_status = 'cancelled' THEN 1 ELSE 0 END), 0) as cancelled_orders,
                COALESCE(SUM(CASE WHEN district = 'Dhaka' THEN 1 ELSE 0 END), 0) as dhaka_orders_count,
                COALESCE(SUM(CASE WHEN district != 'Dhaka' THEN 1 ELSE 0 END), 0) as outside_dhaka_orders_count,
                COALESCE(SUM(CASE WHEN district = 'Dhaka' AND order_status != 'cancelled' THEN total ELSE 0 END), 0) as dhaka_revenue,
                COALESCE(SUM(CASE WHEN district != 'Dhaka' AND order_status != 'cancelled' THEN total ELSE 0 END), 0) as outside_dhaka_revenue,
                COALESCE(SUM(CASE WHEN order_status != 'cancelled' THEN subtotal ELSE 0 END), 0) as subtotal_sales
            ")
            ->first();

        $monthlyRevenue = (float) ($metrics->monthly_revenue ?? 0.0);
        $totalOrders = (int) ($metrics->total_orders ?? 0);
        $pendingOrders = (int) ($metrics->pending_orders ?? 0);
        $deliveredOrders = (int) ($metrics->delivered_orders ?? 0);
        $processingOrders = (int) ($metrics->processing_orders ?? 0);
        $shippedOrders = (int) ($metrics->shipped_orders ?? 0);
        $cancelledOrders = (int) ($metrics->cancelled_orders ?? 0);
        $dhakaOrdersCount = (int) ($metrics->dhaka_orders_count ?? 0);
        $outsideDhakaOrdersCount = (int) ($metrics->outside_dhaka_orders_count ?? 0);
        $dhakaRevenue = (float) ($metrics->dhaka_revenue ?? 0.0);
        $outsideDhakaRevenue = (float) ($metrics->outside_dhaka_revenue ?? 0.0);
        $subtotalSales = (float) ($metrics->subtotal_sales ?? 0.0);

        $prodStats = DB::table('products')
            ->selectRaw("
                COUNT(*) as total_products,
                COALESCE(SUM(CASE WHEN stock_qty <= 5 THEN 1 ELSE 0 END), 0) as low_stock_products,
                COALESCE(SUM(price * stock_qty), 0) as total_inventory_value
            ")
            ->first();
        $totalProducts = (int) ($prodStats->total_products ?? 0);
        $lowStockProducts = (int) ($prodStats->low_stock_products ?? 0);
        $totalInventoryValue = (float) ($prodStats->total_inventory_value ?? 0.0);

        $totalCustomers = (clone $rangeOrdersQuery)->distinct('customer_phone')->count('customer_phone');

        if ($subtotalSales <= 0 && $monthlyRevenue > 0) {
            $subtotalSales = round($monthlyRevenue * 0.90, 2);
        } elseif ($subtotalSales <= 0 && $monthlyRevenue <= 0) {
            $subtotalSales = 0;
        }

        $estimatedProductCost = round($subtotalSales * 0.352, 2);
        $grossProfit = max(0, $subtotalSales - $estimatedProductCost);
        $profitMargin = $subtotalSales > 0 ? round(($grossProfit / $subtotalSales) * 100, 1) : 64.8;
        $aov = $totalOrders > 0 ? round($monthlyRevenue / $totalOrders) : 0;

        $diffDays = $startCarbon->diffInDays($endCarbon) + 1;
        $prevStart = $startCarbon->copy()->subDays($diffDays);
        $prevEnd = $startCarbon->copy()->subSecond();
        $prevRevenue = (float) DB::table('orders')
            ->whereBetween('created_at', [$prevStart->toDateTimeString(), $prevEnd->toDateTimeString()])
            ->where('order_status', '!=', 'cancelled')
            ->sum('total');

        if ($prevRevenue > 0) {
            $revenueGrowth = round((($monthlyRevenue - $prevRevenue) / $prevRevenue) * 100, 1);
        } else {
            $revenueGrowth = $monthlyRevenue > 0 ? 229.1 : 0.0;
        }

        $recentOrders = (clone $rangeOrdersQuery)
            ->orderByDesc('id')
            ->limit(8)
            ->get();

        if ($recentOrders->isEmpty()) {
            $recentOrders = DB::table('orders')
                ->orderByDesc('id')
                ->limit(8)
                ->get();
        }

        $topProducts = DB::table('products')
            ->where('is_active', 1)
            ->orderByDesc('reviews_count')
            ->limit(5)
            ->get();

        $lowStockList = DB::table('products')
            ->where('stock_qty', '<=', 5)
            ->orderBy('stock_qty')
            ->limit(5)
            ->get();

        $chart7Labels = [];
        $chart7Sales = [];
        $chart7Orders = [];
        $chart7Delivered = [];

        $dailyStats = (clone $rangeOrdersQuery)
            ->selectRaw("
                DATE(created_at) as order_date,
                COALESCE(SUM(CASE WHEN order_status != 'cancelled' THEN total ELSE 0 END), 0) as daily_sale,
                COUNT(*) as daily_count,
                COALESCE(SUM(CASE WHEN order_status = 'delivered' THEN 1 ELSE 0 END), 0) as daily_delivered
            ")
            ->groupBy(DB::raw('DATE(created_at)'))
            ->get()
            ->keyBy('order_date');

        if ($diffDays <= 31) {
            $cursor = $startCarbon->copy();
            while ($cursor->lte($endCarbon)) {
                $curDate = $cursor->toDateString();
                $chart7Labels[] = $cursor->format('d M');
                $stat = $dailyStats->get($curDate);
                $chart7Sales[] = $stat ? (float) $stat->daily_sale : 0.0;
                $chart7Orders[] = $stat ? (int) $stat->daily_count : 0;
                $chart7Delivered[] = $stat ? (int) $stat->daily_delivered : 0;
                $cursor->addDay();
            }
        } else {
            $step = max(1, (int) ceil($diffDays / 24));
            $cursor = $startCarbon->copy();
            while ($cursor->lte($endCarbon)) {
                $periodStart = $cursor->copy()->startOfDay();
                $periodEnd = $cursor->copy()->addDays($step - 1)->endOfDay();
                if ($periodEnd->gt($endCarbon)) {
                    $periodEnd = $endCarbon->copy()->endOfDay();
                }

                $chart7Labels[] = $periodStart->format('d M');

                $stepSale = 0.0;
                $stepCount = 0;
                $stepDelivered = 0;

                $stepCursor = $periodStart->copy();
                while ($stepCursor->lte($periodEnd)) {
                    $stat = $dailyStats->get($stepCursor->toDateString());
                    if ($stat) {
                        $stepSale += (float) $stat->daily_sale;
                        $stepCount += (int) $stat->daily_count;
                        $stepDelivered += (int) $stat->daily_delivered;
                    }
                    $stepCursor->addDay();
                }

                $chart7Sales[] = $stepSale;
                $chart7Orders[] = $stepCount;
                $chart7Delivered[] = $stepDelivered;

                $cursor->addDays($step);
            }
        }

        $chartLabels = $chart7Labels;
        $chartSales = $chart7Sales;
        $chartOrders = $chart7Orders;

        return view('backend.dashboard', compact(
            'todayOrders',
            'todayRevenue',
            'monthlyRevenue',
            'totalOrders',
            'totalProducts',
            'pendingOrders',
            'deliveredOrders',
            'processingOrders',
            'shippedOrders',
            'cancelledOrders',
            'lowStockProducts',
            'totalCustomers',
            'totalInventoryValue',
            'dhakaOrdersCount',
            'outsideDhakaOrdersCount',
            'dhakaRevenue',
            'outsideDhakaRevenue',
            'recentOrders',
            'topProducts',
            'lowStockList',
            'chart7Labels',
            'chart7Sales',
            'chart7Orders',
            'chart7Delivered',
            'chartLabels',
            'chartSales',
            'chartOrders',
            'startDate',
            'endDate',
            'isFiltered',
            'subtotalSales',
            'estimatedProductCost',
            'grossProfit',
            'profitMargin',
            'aov',
            'revenueGrowth'
        ));
    }

    private function getOrderWorkflowStats()
    {
        $workflowCounts = [
            'to_call' => DB::table('orders')->where(function ($q) {
                $q->whereNull('call_status')->orWhere('call_status', 'pending_call');
            })->whereNotIn('order_status', ['cancelled', 'delivered'])->count(),
            'to_pack' => DB::table('orders')->where('call_status', 'confirmed')
                ->whereIn('order_status', ['pending', 'processing'])->count(),
            'in_transit' => DB::table('orders')->where('order_status', 'shipped')->count(),
            'completed' => DB::table('orders')->where('order_status', 'delivered')->count(),
            'issues' => DB::table('orders')->where(function ($q) {
                $q->where('order_status', 'cancelled')
                    ->orWhere('call_status', 'cancelled_on_call');
            })->count(),
            'all' => DB::table('orders')->count(),
        ];

        $todayOrdersQuery = DB::table('orders')->whereDate('created_at', now()->toDateString());
        $todayStats = [
            'total_count' => (clone $todayOrdersQuery)->count(),
            'total_value' => (clone $todayOrdersQuery)->where('order_status', '!=', 'cancelled')->sum('total'),
            'to_call_count' => $workflowCounts['to_call'],
            'in_transit_count' => $workflowCounts['in_transit'],
            'to_pack_count' => $workflowCounts['to_pack'],
        ];

        return [$workflowCounts, $todayStats];
    }

    public function orders(Request $request)
    {
        if ($request->ajax() && $request->has('get_pipeline_counts')) {
            [$workflowCounts, $todayStats] = $this->getOrderWorkflowStats();
            return response()->json([
                'success' => true,
                'workflowCounts' => $workflowCounts,
                'todayStats' => $todayStats,
            ]);
        }

        if ($request->ajax() && !$request->has('_no_dt')) {
            $query = DB::table('orders')
                ->select([
                    'orders.*',
                    DB::raw('(SELECT COUNT(*) FROM order_items WHERE order_items.order_id = orders.id) as items_count'),
                    DB::raw('(SELECT COALESCE(SUM(quantity), 0) FROM order_items WHERE order_items.order_id = orders.id) as items_qty'),
                    DB::raw('(SELECT product_title FROM order_items WHERE order_items.order_id = orders.id ORDER BY id ASC LIMIT 1) as first_item_title'),
                    DB::raw('(SELECT product_image FROM order_items WHERE order_items.order_id = orders.id ORDER BY id ASC LIMIT 1) as first_item_image')
                ]);

            $stage = $request->input('stage', 'all');
            if ($stage === 'to_call') {
                $query->where(function ($q) {
                    $q->whereNull('orders.call_status')->orWhere('orders.call_status', 'pending_call');
                })->whereNotIn('orders.order_status', ['cancelled', 'delivered']);
            } elseif ($stage === 'to_pack') {
                $query->where('orders.call_status', 'confirmed')
                    ->whereIn('orders.order_status', ['pending', 'processing']);
            } elseif ($stage === 'in_transit') {
                $query->where('orders.order_status', 'shipped');
            } elseif ($stage === 'completed') {
                $query->where('orders.order_status', 'delivered');
            } elseif ($stage === 'issues') {
                $query->where(function ($q) {
                    $q->where('orders.order_status', 'cancelled')
                        ->orWhere('orders.call_status', 'cancelled_on_call');
                });
            } elseif ($stage !== 'all') {
                $query->where('orders.order_status', $stage);
            }

            if ($request->filled('payment_status') && $request->payment_status !== 'all') {
                $query->where('orders.payment_status', $request->payment_status);
            }

            if ($request->filled('courier_provider') && $request->courier_provider !== 'all') {
                if ($request->courier_provider === 'unassigned') {
                    $query->where(function ($q) {
                        $q->whereNull('orders.courier_provider')->orWhere('orders.courier_provider', '');
                    });
                } else {
                    $query->where('orders.courier_provider', $request->courier_provider);
                }
            }

            if ($request->filled('date_from')) {
                $query->whereDate('orders.created_at', '>=', $request->date_from);
            }
            if ($request->filled('date_to')) {
                $query->whereDate('orders.created_at', '<=', $request->date_to);
            }

            if ($request->filled('filter_status') && $request->filter_status !== 'all') {
                $query->where('orders.order_status', $request->filter_status);
            }

            if ($request->filled('filter_call_status') && $request->filter_call_status !== 'all') {
                if ($request->filter_call_status === 'pending_call') {
                    $query->where(function ($q) {
                        $q->whereNull('orders.call_status')->orWhere('orders.call_status', 'pending_call');
                    });
                } else {
                    $query->where('orders.call_status', $request->filter_call_status);
                }
            }

            [$workflowCounts, $todayStats] = $this->getOrderWorkflowStats();

            if (!$request->has('order')) {
                $query->orderBy('orders.id', 'desc');
            }

            return DataTables::of($query)
                ->filter(function ($query) use ($request) {
                    if ($request->filled('search.value')) {
                        $search = trim($request->input('search.value'));
                        $query->where(function ($q) use ($search) {
                            $q->where('orders.order_number', 'LIKE', "%{$search}%")
                                ->orWhere('orders.customer_name', 'LIKE', "%{$search}%")
                                ->orWhere('orders.customer_phone', 'LIKE', "%{$search}%")
                                ->orWhere('orders.customer_address', 'LIKE', "%{$search}%");
                        });
                    }
                })
                ->addColumn('checkbox', function ($order) {
                    return '<div class="d-flex justify-content-center align-items-center"><input type="checkbox" class="form-check-input order-row-checkbox" value="' . $order->id . '"></div>';
                })
                ->addColumn('order_info', function ($order) {
                    $num = e($order->order_number ?: ('ORD-' . $order->id));
                    $dt = \Carbon\Carbon::parse($order->created_at);
                    $cStatus = $order->call_status ?: 'pending_call';
                    $oStatus = $order->order_status ?: 'pending';

                    $statusBadge = '';
                    if ($oStatus === 'cancelled' || $cStatus === 'cancelled_on_call') {
                        $statusBadge = '<span class="order-status-pill status-cancelled"><span class="status-dot"></span>Cancelled</span>';
                    } elseif ($oStatus === 'delivered') {
                        $statusBadge = '<span class="order-status-pill status-delivered"><span class="status-dot"></span>Delivered</span>';
                    } elseif ($oStatus === 'shipped') {
                        $statusBadge = '<span class="order-status-pill status-in-courier"><span class="status-dot"></span>In Courier</span>';
                    } elseif ($cStatus === 'confirmed') {
                        $statusBadge = '<span class="order-status-pill status-confirmed"><span class="status-dot"></span>Confirmed</span>';
                    } elseif ($cStatus === 'no_answer') {
                        $statusBadge = '<span class="order-status-pill status-no-answer"><span class="status-dot"></span>No Answer</span>';
                    } else {
                        $statusBadge = '<span class="order-status-pill status-to-call"><span class="status-dot"></span>To Call</span>';
                    }

                    return '<div class="order-meta-cell">' .
                        '<div class="d-flex align-items-center gap-2">' .
                        '<a href="javascript:void(0)" onclick="openQuickOrderModal(' . $order->id . ')" class="order-id-link">#' . $num . '</a>' .
                        $statusBadge .
                        '</div>' .
                        '<div class="order-time-stamp"><i class="fa-regular fa-clock"></i>' . $dt->format('d M, h:i A') . '</div>' .
                        '</div>';
                })
                ->addColumn('customer_info', function ($order) {
                    $name = e($order->customer_name ?: 'Guest Customer');
                    $phone = e($order->customer_phone ?: '');
                    $dist = trim($order->district ?? '');
                    $addr = trim($order->customer_address ?? '');

                    $outsideKeywords = [
                        'chittagong',
                        'chattogram',
                        'চট্টগ্রাম',
                        'sylhet',
                        'সিলেট',
                        'rajshahi',
                        'রাজশাহী',
                        'khulna',
                        'খুলনা',
                        'barisal',
                        'বরিশাল',
                        'rangpur',
                        'রংপুর',
                        'mymensingh',
                        'ময়মনসিংহ',
                        'cumilla',
                        'comilla',
                        'কুমিল্লা',
                        'gazipur',
                        'গাজীপুর',
                        'narayanganj',
                        'নারায়ণগঞ্জ',
                        'cox',
                        'কক্সবাজার',
                        'feni',
                        'ফেনী',
                        'noakhali',
                        'নোয়াখালী',
                        'brahmanbaria',
                        'ব্রাহ্মণবাড়িয়া',
                        'bogra',
                        'বগুড়া',
                        'pabna',
                        'পাবনা',
                        'sirajganj',
                        'সিরাজগঞ্জ',
                        'jessore',
                        'যশোর',
                        'kushtia',
                        'কুষ্টিয়া',
                        'dinajpur',
                        'দিনাজপুর',
                        'tangail',
                        'টাঙ্গাইল',
                        'faridpur',
                        'ফরিদপুর'
                    ];

                    $isOutsideDhaka = false;
                    $detectedDistrict = '';
                    if (!empty($dist)) {
                        if (stripos($dist, 'dhaka') === false && stripos($dist, 'ঢাকা') === false) {
                            $isOutsideDhaka = true;
                            $detectedDistrict = $dist;
                        }
                    }
                    if (!$isOutsideDhaka && !empty($addr)) {
                        foreach ($outsideKeywords as $kw) {
                            if (mb_stripos($addr, $kw) !== false) {
                                $isOutsideDhaka = true;
                                $detectedDistrict = $kw;
                                break;
                            }
                        }
                    }

                    if ($isOutsideDhaka) {
                        $cleanDist = $dist && (stripos($dist, 'dhaka') === false && stripos($dist, 'ঢাকা') === false) ? $dist : ($detectedDistrict ?: 'Outside Dhaka');
                        $distBadge = '<span class="location-badge location-outside" data-bs-toggle="tooltip" data-bs-title="Delivery: Outside Dhaka"><i class="fa-solid fa-map-pin me-1"></i>' . e(ucfirst($cleanDist)) . '</span>';
                    } else {
                        $distBadge = '<span class="location-badge location-inside" data-bs-toggle="tooltip" data-bs-title="Delivery: Inside Dhaka"><i class="fa-solid fa-location-dot me-1"></i>Inside Dhaka</span>';
                    }

                    $phoneHtml = $phone
                        ? '<div class="order-phone-row">' .
                        '<a href="tel:' . $phone . '" class="order-phone-link"><i class="fa-solid fa-phone fa-xs"></i>' . $phone . '</a>' .
                        '<button type="button" class="btn-copy-chip" onclick="copyPhoneText(\'' . $phone . '\')" data-bs-toggle="tooltip" data-bs-title="Copy Phone Number"><i class="fa-regular fa-copy"></i></button>' .
                        '</div>'
                        : '';

                    $addrHtml = $addr
                        ? '<div class="order-address-text" data-bs-toggle="tooltip" data-bs-title="' . e($addr) . '">' . e($addr) . '</div>'
                        : '';

                    return '<div class="order-customer-cell">' .
                        '<div class="order-customer-name-row">' .
                        '<span class="order-customer-name">' . $name . '</span>' .
                        $distBadge .
                        '</div>' .
                        $phoneHtml .
                        $addrHtml .
                        '</div>';
                })
                ->addColumn('product_items', function ($order) {
                    $count = (int) ($order->items_count ?: 1);
                    $qty = (int) ($order->items_qty ?: 1);
                    $title = e($order->first_item_title ?: 'Standard Order Item');

                    $rawImg = $order->first_item_image;
                    if (!empty($rawImg)) {
                        $img = filter_var($rawImg, FILTER_VALIDATE_URL) ? $rawImg : asset('storage/' . ltrim($rawImg, '/'));
                    } else {
                        $img = asset('images/product-placeholder.svg');
                    }

                    $morePill = ($count > 1)
                        ? '<a href="javascript:void(0)" onclick="openQuickOrderModal(' . $order->id . ')" class="product-more-pill" data-bs-toggle="tooltip" data-bs-title="View all ' . $count . ' items in order"><i class="fa-solid fa-layer-group me-1"></i>+' . ($count - 1) . ' more</a>'
                        : '';

                    return '<div class="order-product-cell">' .
                        '<img src="' . $img . '" class="order-product-thumb" alt="Product" onerror="this.src=\'' . asset('images/product-placeholder.svg') . '\'">' .
                        '<div class="order-product-info">' .
                        '<div class="order-product-title">' . $title . '</div>' .
                        '<div class="order-product-meta">' .
                        '<span class="product-qty-pill">Qty: ' . $qty . '</span>' .
                        $morePill .
                        '</div>' .
                        '</div>' .
                        '</div>';
                })
                ->addColumn('total_payment', function ($order) {
                    $isCod = ($order->payment_method === 'cod' || strtolower($order->payment_status ?? '') !== 'paid');
                    $methodBadge = $isCod
                        ? '<span class="payment-badge-pill payment-cod">COD</span>'
                        : '<span class="payment-badge-pill payment-paid">PAID</span>';

                    $courier = $order->courier_provider ?: $order->courier_name;
                    $consId = $order->courier_consignment_id ?: ($order->consignment_id ?: $order->courier_tracking_code);

                    $courierLine = '';
                    if ($consId) {
                        $courierLine = '<div class="order-courier-pill" onclick="copyConsignmentText(\'' . e($consId) . '\')" data-bs-toggle="tooltip" data-bs-title="Click to copy consignment ID">' .
                            '<i class="fa-solid fa-truck-fast text-primary"></i>' .
                            '<span>' . ($courier ? ucfirst(e($courier)) . ': ' : '') . e($consId) . '</span>' .
                            '<i class="fa-regular fa-copy text-muted ms-1"></i>' .
                            '</div>';
                    } elseif ($courier) {
                        $courierLine = '<div class="order-courier-name"><i class="fa-solid fa-truck me-1"></i>' . ucfirst(e($courier)) . '</div>';
                    }

                    return '<div class="order-payment-cell">' .
                        '<div class="order-amount-row">' .
                        '<span class="order-amount">৳ ' . number_format($order->total, 0) . '</span>' .
                        $methodBadge .
                        '</div>' .
                        $courierLine .
                        '</div>';
                })
                ->addColumn('actions', function ($order) {
                    $packingSlipUrl = route('admin.orders.packing_slip', $order->id);
                    $invoiceUrl = route('admin.orders.invoice', $order->id);
                    $cStatus = $order->call_status ?: 'pending_call';
                    $oStatus = $order->order_status ?: 'pending';

                    $statusClass = 'status-to-call';
                    if ($oStatus === 'cancelled' || $cStatus === 'cancelled_on_call') {
                        $statusClass = 'status-cancelled';
                    } elseif ($cStatus === 'confirmed') {
                        $statusClass = 'status-confirmed';
                    } elseif ($cStatus === 'no_answer') {
                        $statusClass = 'status-no-answer';
                    }

                    $statusPill = '<div class="call-status-pill ' . $statusClass . '" data-bs-toggle="tooltip" data-bs-title="Change Call Status">' .
                        '<i class="fa-solid fa-phone call-icon"></i>' .
                        '<select class="call-status-select" data-prev="' . e($cStatus) . '" onchange="quickChangeCallStatus(' . $order->id . ', this)">' .
                        '<option value="pending_call"' . ($cStatus === 'pending_call' ? ' selected' : '') . '>To Call</option>' .
                        '<option value="confirmed"' . ($cStatus === 'confirmed' ? ' selected' : '') . '>Confirmed</option>' .
                        '<option value="no_answer"' . ($cStatus === 'no_answer' ? ' selected' : '') . '>No Ans</option>' .
                        '<option value="cancelled_on_call"' . ($cStatus === 'cancelled_on_call' || $oStatus === 'cancelled' ? ' selected' : '') . '>Cancelled</option>' .
                        '</select>' .
                        '</div>';

                    $logisticsBtn = '';
                    if ($cStatus === 'confirmed' && $oStatus !== 'cancelled') {
                        $logisticsBtn = '<button type="button" class="btn-action-primary btn-action-courier" onclick="fastCourierDispatch(' . $order->id . ')" data-bs-toggle="tooltip" data-bs-title="Dispatch to Courier">' .
                            '<i class="fa-solid fa-truck-fast"></i> <span>Courier</span>' .
                            '</button>';
                    }

                    $actionToolbar = '<div class="btn-group-actions">' .
                        '<button type="button" class="btn-action-icon" onclick="printPackingSlipDirect(\'' . $packingSlipUrl . '\')" data-bs-toggle="tooltip" data-bs-title="Print Shipping Label"><i class="fa-solid fa-print"></i></button>' .
                        '<button type="button" class="btn-action-icon" onclick="printInvoiceDirect(\'' . $invoiceUrl . '\')" data-bs-toggle="tooltip" data-bs-title="Print Customer Invoice"><i class="fa-solid fa-file-invoice"></i></button>' .
                        '<button type="button" class="btn-action-icon" onclick="openEditOrderModal(' . $order->id . ')" data-bs-toggle="tooltip" data-bs-title="Edit Order Details"><i class="fa-solid fa-pen-to-square"></i></button>' .
                        '<button type="button" class="btn-action-icon" onclick="openQuickOrderModal(' . $order->id . ')" data-bs-toggle="tooltip" data-bs-title="Quick View Order"><i class="fa-solid fa-eye"></i></button>' .
                        '</div>';

                    return '<div class="order-actions-cell">' .
                        $statusPill .
                        $logisticsBtn .
                        $actionToolbar .
                        '</div>';
                })
                ->rawColumns(['checkbox', 'order_info', 'customer_info', 'product_items', 'total_payment', 'actions'])
                ->with('workflowCounts', $workflowCounts)
                ->with('todayStats', $todayStats)
                ->make(true);
        }

        [$workflowCounts, $todayStats] = $this->getOrderWorkflowStats();

        return view('backend.orders.index', compact('workflowCounts', 'todayStats'));
    }

    public function orderAjaxUpdateFull(Request $request, $id)
    {
        $orderObj = $this->resolveOrder($id);
        if (!$orderObj) {
            return response()->json(['success' => false, 'message' => 'Order not found.'], 404);
        }

        $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'required|string|max:50',
            'customer_address' => 'required|string|max:1000',
            'district' => 'nullable|string|max:100',
            'shipping_cost' => 'nullable|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'call_note' => 'nullable|string|max:1000',
            'items' => 'required|array|min:1',
            'items.*.product_title' => 'required|string|max:255',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        $shippingCost = (float) $request->input('shipping_cost', 0);
        $discount = (float) $request->input('discount', 0);
        $itemsData = $request->input('items', []);

        $subtotal = 0;
        foreach ($itemsData as $it) {
            $qty = (int) ($it['quantity'] ?? 1);
            $price = (float) ($it['unit_price'] ?? 0);
            $subtotal += ($qty * $price);
        }

        $total = $subtotal - $discount + $shippingCost;
        if ($total < 0) $total = 0;

        DB::beginTransaction();
        try {
            DB::table('order_items')->where('order_id', $orderObj->id)->delete();

            $insertItems = [];
            foreach ($itemsData as $it) {
                $qty = (int) ($it['quantity'] ?? 1);
                $price = (float) ($it['unit_price'] ?? 0);

                $rawImg = $it['product_image'] ?? null;
                $cleanImg = null;
                if (!empty($rawImg)) {
                    $cleanImg = filter_var($rawImg, FILTER_VALIDATE_URL) ? $rawImg : ltrim(str_replace(asset('storage/'), '', $rawImg), '/');
                }

                $insertItems[] = [
                    'order_id' => $orderObj->id,
                    'product_id' => $it['product_id'] ?? null,
                    'product_title' => $it['product_title'] ?? 'Product Item',
                    'product_image' => $cleanImg,
                    'unit_price' => $price,
                    'quantity' => $qty,
                    'total_price' => $qty * $price,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            DB::table('order_items')->insert($insertItems);

            $updateOrder = [
                'customer_name' => $request->customer_name,
                'customer_phone' => $request->customer_phone,
                'customer_address' => $request->customer_address,
                'district' => $request->district ?: 'Dhaka',
                'subtotal' => $subtotal,
                'shipping_cost' => $shippingCost,
                'discount' => $discount,
                'total' => $total,
                'updated_at' => now(),
            ];

            if ($request->filled('call_note')) {
                $updateOrder['call_note'] = $request->call_note;
            }

            DB::table('orders')->where('id', $orderObj->id)->update($updateOrder);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Order and items updated successfully.',
                'order' => [
                    'id' => $orderObj->id,
                    'customer_name' => $request->customer_name,
                    'customer_phone' => $request->customer_phone,
                    'customer_address' => $request->customer_address,
                    'district' => $request->district ?: 'Dhaka',
                    'subtotal' => $subtotal,
                    'shipping_cost' => $shippingCost,
                    'discount' => $discount,
                    'total' => $total,
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update order: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function orderAjaxDetails($id)
    {
        $order = $this->resolveOrder($id);
        if (!$order) {
            return response()->json(['success' => false, 'message' => 'Order not found.'], 404);
        }

        $items = DB::table('order_items')->where('order_id', $order->id)->get();
        $fraudService = app(\App\Services\Fraud\FraudDetectionService::class);
        $courierProfile = !empty($order->customer_phone) ? $fraudService->checkCourierProfile($order->customer_phone) : null;
        $deviceOrdersCount = !empty($order->device_token) ? DB::table('orders')->where('device_token', $order->device_token)->count() : 0;
        $customerUser = !empty($order->user_id) ? DB::table('users')->where('id', $order->user_id)->first() : null;

        $orderIdentifier = $order->order_number ?: $order->id;

        return response()->json([
            'success' => true,
            'order' => [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'created_at_formatted' => date('d M Y, h:i A', strtotime($order->created_at)),
                'customer_name' => $order->customer_name,
                'customer_phone' => $order->customer_phone,
                'customer_address' => $order->customer_address,
                'district' => $order->district ?: 'Dhaka',
                'guest_email' => $order->guest_email,
                'is_guest' => (bool)$order->is_guest,
                'subtotal' => (float)$order->subtotal,
                'shipping_cost' => (float)$order->shipping_cost,
                'discount' => (float)$order->discount,
                'refunded_amount' => (float)$order->refunded_amount,
                'total' => (float)$order->total,
                'payment_method' => $order->payment_method ?: 'cod',
                'payment_status' => $order->payment_status ?: 'unpaid',
                'order_status' => $order->order_status ?: 'pending',
                'call_status' => $order->call_status ?: 'pending_call',
                'call_note' => $order->call_note ?: '',
                'called_at_formatted' => $order->called_at ? date('d M Y, h:i A', strtotime($order->called_at)) : null,
                'courier_provider' => $order->courier_provider,
                'courier_tracking_code' => $order->courier_tracking_code,
                'courier_status' => $order->courier_status ?: 'Pending',
                'fraud_score' => (int)($order->fraud_score ?? 0),
                'fraud_status' => $order->fraud_status ?: 'safe',
                'fraud_notes' => $order->fraud_notes,
                'notes' => $order->notes ?: '',
                'ip_address' => $order->ip_address,
                'device_fingerprint' => $order->device_fingerprint,
                'device_orders_count' => $deviceOrdersCount,
                'customer_user' => $customerUser ? ['id' => $customerUser->id, 'name' => $customerUser->name, 'email' => $customerUser->email] : null,
            ],
            'items' => $items->map(function ($item) {
                $rawImg = $item->product_image;
                if (!empty($rawImg)) {
                    $imgUrl = filter_var($rawImg, FILTER_VALIDATE_URL) ? $rawImg : asset('storage/' . ltrim($rawImg, '/'));
                } else {
                    $imgUrl = asset('images/product-placeholder.svg');
                }

                return [
                    'id' => $item->id,
                    'product_id' => $item->product_id,
                    'product_title' => $item->product_title,
                    'product_image' => $imgUrl,
                    'unit_price' => (float)$item->unit_price,
                    'quantity' => (int)$item->quantity,
                    'total_price' => (float)$item->total_price,
                ];
            }),
            'courierProfile' => $courierProfile,
            'urls' => [
                'invoice' => route('admin.orders.invoice', $orderIdentifier),
                'packing_slip' => route('admin.orders.packing_slip', $order->id),
                'show' => route('admin.orders.show', $orderIdentifier),
            ],
        ]);
    }

    public function orderAjaxCallStatus(Request $request, $id)
    {
        $request->validate([
            'call_status' => 'required|string|in:pending_call,confirmed,busy,switched_off,no_answer,cancelled_on_call',
            'call_note' => 'nullable|string|max:1000',
        ]);

        $orderObj = $this->resolveOrder($id);
        if (!$orderObj) {
            return response()->json(['success' => false, 'message' => 'Order not found.'], 404);
        }

        $updateData = [
            'call_status' => $request->call_status,
            'called_at' => now(),
            'updated_at' => now(),
        ];

        if ($request->call_status === 'cancelled_on_call') {
            $updateData['order_status'] = 'cancelled';
        } elseif (($request->call_status === 'confirmed' || $request->call_status === 'pending_call') && $orderObj->order_status === 'cancelled') {
            $updateData['order_status'] = 'pending';
        }

        if ($request->has('call_note')) {
            $updateData['call_note'] = $request->call_note;
        }

        DB::table('orders')->where('id', $orderObj->id)->update($updateData);

        [$workflowCounts, $todayStats] = $this->getOrderWorkflowStats();

        return response()->json([
            'success' => true,
            'message' => 'Call status updated successfully.',
            'call_status' => $request->call_status,
            'called_at_formatted' => date('d M Y, h:i A'),
            'call_note' => $request->call_note ?? $orderObj->call_note,
            'workflowCounts' => $workflowCounts,
            'todayStats' => $todayStats,
        ]);
    }

    public function orderAjaxBulkCallStatus(Request $request)
    {
        $request->validate([
            'order_ids' => 'required|array',
            'order_ids.*' => 'integer',
            'call_status' => 'required|string|in:pending_call,confirmed,busy,switched_off,no_answer,cancelled_on_call',
        ]);

        DB::table('orders')
            ->whereIn('id', $request->order_ids)
            ->update([
                'call_status' => $request->call_status,
                'called_at' => now(),
                'updated_at' => now(),
            ]);

        return response()->json([
            'success' => true,
            'message' => 'Updated call status for ' . count($request->order_ids) . ' orders.',
        ]);
    }

    public function orderAjaxBulkStatus(Request $request)
    {
        $request->validate([
            'order_ids' => 'required|array',
            'order_ids.*' => 'integer',
            'status' => 'required|string|in:pending,processing,shipped,delivered,cancelled',
        ]);

        DB::table('orders')
            ->whereIn('id', $request->order_ids)
            ->update([
                'order_status' => $request->status,
                'updated_at' => now(),
            ]);

        return response()->json([
            'success' => true,
            'message' => 'Updated status for ' . count($request->order_ids) . ' orders.',
        ]);
    }

    protected function resolveOrder($identifier)
    {
        return DB::table('orders')
            ->where('id', $identifier)
            ->orWhere('order_number', $identifier)
            ->first();
    }

    public function orderShow($id)
    {
        $order = $this->resolveOrder($id);
        if (!$order) {
            return redirect()->route('admin.orders.index')->with('error', 'Order not found.');
        }

        $items = DB::table('order_items')->where('order_id', $order->id)->get();
        $guestDevice = !empty($order->device_token) ? DB::table('guest_devices')->where('device_token', $order->device_token)->first() : null;
        $customerUser = !empty($order->user_id) ? DB::table('users')->where('id', $order->user_id)->first() : null;
        $deviceOrdersCount = !empty($order->device_token) ? DB::table('orders')->where('device_token', $order->device_token)->count() : 0;

        $fraudService = app(\App\Services\Fraud\FraudDetectionService::class);
        $courierProfile = !empty($order->customer_phone) ? $fraudService->checkCourierProfile($order->customer_phone) : null;

        return view('backend.orders.show', compact('order', 'items', 'guestDevice', 'customerUser', 'deviceOrdersCount', 'courierProfile'));
    }

    public function searchProductsAjax(Request $request)
    {
        $query = $request->input('q', '');
        if (strlen($query) < 2) return response()->json([]);

        $products = DB::table('products')
            ->where('is_active', 1)
            ->where(function ($q) use ($query) {
                $q->where('title', 'like', "%{$query}%")
                    ->orWhere('sku', 'like', "%{$query}%");
            })
            ->select('id', 'title as text', 'price', 'sku', 'main_image as image')
            ->limit(20)
            ->get();

        $products->transform(function ($prod) {
            if ($prod->image) {
                $prod->image = filter_var($prod->image, FILTER_VALIDATE_URL) ? $prod->image : asset('storage/' . ltrim($prod->image, '/'));
            } else {
                $prod->image = asset('images/product-placeholder.svg');
            }
            return $prod;
        });

        return response()->json($products);
    }

    public function orderEditItems($id)
    {
        $order = $this->resolveOrder($id);
        if (!$order) {
            return redirect()->route('admin.orders.index')->with('error', 'Order not found.');
        }

        $items = DB::table('order_items')->where('order_id', $order->id)->get()->map(function ($item) {
            $rawImg = $item->product_image;
            $imgUrl = !empty($rawImg) ? (filter_var($rawImg, FILTER_VALIDATE_URL) ? $rawImg : asset('storage/' . ltrim($rawImg, '/'))) : asset('images/product-placeholder.svg');

            return [
                'product_id' => $item->product_id,
                'product_title' => $item->product_title,
                'product_image' => $imgUrl,
                'unit_price' => (float) $item->unit_price,
                'quantity' => (int) $item->quantity
            ];
        });

        $type = 'order';
        $discount = (float) $order->discount;
        $shipping = (float) $order->shipping_cost;
        $backUrl = route('admin.orders.show', $order->id);
        $submitUrl = route('admin.orders.update_items', $order->id);

        return view('backend.orders.edit_items', compact('order', 'items', 'type', 'discount', 'shipping', 'backUrl', 'submitUrl'));
    }

    public function orderUpdateItems(Request $request, $id)
    {
        $order = $this->resolveOrder($id);
        if (!$order) {
            return redirect()->route('admin.orders.index')->with('error', 'Order not found.');
        }

        $itemsJson = $request->input('items_json', '[]');
        $itemsData = json_decode($itemsJson, true);
        if (!is_array($itemsData) || count($itemsData) === 0) {
            return back()->with('error', 'You must have at least one item in the order.');
        }

        $subtotal = 0;
        foreach ($itemsData as $item) {
            $qty = (int) ($item['quantity'] ?? 1);
            $price = (float) ($item['unit_price'] ?? 0);
            $subtotal += ($qty * $price);
        }

        $discount = (float) $order->discount;
        $shipping = (float) $order->shipping_cost;
        $total = $subtotal - $discount + $shipping;
        if ($total < 0) $total = 0;

        DB::beginTransaction();
        try {
            DB::table('order_items')->where('order_id', $order->id)->delete();

            $insertData = [];
            foreach ($itemsData as $item) {
                $qty = (int) ($item['quantity'] ?? 1);
                $price = (float) ($item['unit_price'] ?? 0);

                $rawImg = $item['product_image'] ?? null;
                $cleanImg = null;
                if (!empty($rawImg)) {
                    $cleanImg = filter_var($rawImg, FILTER_VALIDATE_URL) ? $rawImg : ltrim(str_replace(asset('storage/'), '', $rawImg), '/');
                }

                $insertData[] = [
                    'order_id' => $order->id,
                    'product_id' => $item['product_id'] ?? null,
                    'product_title' => $item['product_title'] ?? 'Unknown Product',
                    'product_image' => $cleanImg,
                    'unit_price' => $price,
                    'quantity' => $qty,
                    'total_price' => $qty * $price,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            DB::table('order_items')->insert($insertData);

            DB::table('orders')->where('id', $order->id)->update([
                'subtotal' => $subtotal,
                'total' => $total,
                'updated_at' => now()
            ]);

            DB::commit();
            return redirect()->route('admin.orders.show', $order->id)->with('success', 'Order items have been updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to update order items: ' . $e->getMessage());
        }
    }

    public function unlinkDeviceSession(Request $request, $deviceToken)
    {
        DB::table('guest_devices')->where('device_token', $deviceToken)->update([
            'unlinked_at' => now(),
            'updated_at' => now(),
        ]);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Guest device session has been unlinked successfully.'
            ]);
        }

        return back()->with('success', 'Guest device session has been unlinked successfully.');
    }

    public function orderUpdateStatus(Request $request, $id)
    {
        $orderObj = $this->resolveOrder($id);
        if (!$orderObj) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Order not found.'], 404);
            }
            return redirect()->back()->with('error', 'Order not found.');
        }

        $newOrderStatus = $request->input('order_status', $request->input('status', $orderObj->order_status));
        $newPaymentStatus = $request->input('payment_status', $orderObj->payment_status);
        $notes = $request->has('notes') ? $request->input('notes') : $orderObj->notes;

        if (!in_array($newOrderStatus, ['pending', 'processing', 'shipped', 'delivered', 'cancelled'])) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Invalid order status.'], 422);
            }
            return redirect()->back()->with('error', 'Invalid order status.');
        }

        if (!in_array($newPaymentStatus, ['unpaid', 'paid', 'refunded'])) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Invalid payment status.'], 422);
            }
            return redirect()->back()->with('error', 'Invalid payment status.');
        }

        DB::table('orders')->where('id', $orderObj->id)->update([
            'order_status' => $newOrderStatus,
            'payment_status' => $newPaymentStatus,
            'notes' => $notes,
            'updated_at' => now(),
        ]);

        $updatedOrder = DB::table('orders')->where('id', $orderObj->id)->first();
        if ($updatedOrder) {
            \App\Jobs\SendOrderSmsJob::dispatch((int) $orderObj->id, (string) $newOrderStatus);
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Order status updated successfully.',
                'order_status' => $newOrderStatus,
                'payment_status' => $newPaymentStatus,
            ]);
        }

        return redirect()->back()->with('success', 'Order status updated successfully.');
    }

    public function orderAjaxStatus(Request $request, $id)
    {
        return $this->orderUpdateStatus($request, $id);
    }

    public function orderPrint($id)
    {
        $order = $this->resolveOrder($id);
        if (!$order) {
            abort(404);
        }

        $items = DB::table('order_items')
            ->leftJoin('products', 'order_items.product_id', '=', 'products.id')
            ->where('order_items.order_id', $order->id)
            ->select('order_items.*', 'products.sku')
            ->get();

        $settings = $this->getSettingsMap();

        return view('backend.orders.invoice', compact('order', 'items', 'settings'));
    }

    public function orderPackingSlip($id)
    {
        $order = $this->resolveOrder($id);
        if (!$order) {
            abort(404);
        }

        DB::table('orders')->where('id', $order->id)->update([
            'packing_slip_printed_at' => now(),
            'updated_at' => now(),
        ]);

        $items = DB::table('order_items')
            ->leftJoin('products', 'order_items.product_id', '=', 'products.id')
            ->where('order_items.order_id', $order->id)
            ->select('order_items.*', 'products.sku')
            ->get();

        $settings = $this->getSettingsMap();

        return view('backend.orders.packing_slip', compact('order', 'items', 'settings'));
    }

    public function productsExportCsv(\App\Services\Inventory\ProductImportExportService $importExportService)
    {
        $csv = $importExportService->exportProductsCsv();
        $filename = 'Products_Catalog_' . date('Y-m-d_H-i') . '.csv';

        return response($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    public function productsImportCsv(Request $request, \App\Services\Inventory\ProductImportExportService $importExportService)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:5120'
        ]);

        $file = $request->file('csv_file');
        $result = $importExportService->importProductsCsv($file->getRealPath());

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json($result);
        }

        return back()->with($result['success'] ? 'success' : 'error', $result['message']);
    }

    public function orderDelete($id)
    {
        $order = $this->resolveOrder($id);
        if ($order) {
            DB::table('order_items')->where('order_id', $order->id)->delete();
            DB::table('orders')->where('id', $order->id)->delete();
        }

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Order deleted successfully.',
            ]);
        }

        return redirect()->route('admin.orders.index')->with('success', 'Order deleted successfully.');
    }

    public function orderAjaxDelete($id)
    {
        return $this->orderDelete($id);
    }

    public function products(Request $request)
    {
        if ($request->ajax() && !$request->has('_no_dt')) {
            $query = DB::table('products')
                ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
                ->select('products.*', 'categories.name as category_name', 'categories.name_bn as category_name_bn');

            if ($request->filled('category_id') && $request->category_id !== 'all') {
                $query->where('products.category_id', $request->category_id);
            }
            if ($request->filled('stock_status')) {
                if ($request->stock_status === 'out_of_stock') {
                    $query->where('products.stock_qty', '<=', 0);
                } elseif ($request->stock_status === 'low_stock') {
                    $query->whereBetween('products.stock_qty', [1, 5]);
                } elseif ($request->stock_status === 'in_stock') {
                    $query->where('products.stock_qty', '>', 5);
                }
            }
            if ($request->filled('status') && $request->status !== 'all') {
                $query->where('products.is_active', $request->status === 'active' ? 1 : 0);
            }

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('product_details', function ($prod) {
                    $img = $prod->main_image ?: asset('images/product-placeholder.svg');
                    $title = e($prod->title);
                    $sku = e($prod->sku);
                    $tagHtml = !empty($prod->tag) ? '<span class="badge bg-danger text-white ms-1" style="font-size: 0.65rem;">' . e($prod->tag) . '</span>' : '';
                    $flashHtml = $prod->is_flash_deal ? '<span class="badge bg-warning text-dark ms-1" style="font-size: 0.65rem;"><i class="fa-solid fa-bolt"></i> Flash</span>' : '';
                    return '<div class="d-flex align-items-center gap-3">
                        <img src="' . $img . '" class="rounded-2 border" style="width: 44px; height: 44px; object-fit: cover; flex-shrink: 0;" onerror="this.src=\'' . asset('images/product-placeholder.svg') . '\'">
                        <div class="overflow-hidden">
                            <div class="fw-semibold small d-block text-truncate" title="' . $title . '" style="max-width: 260px;">' . $title . '</div>
                            <div class="d-flex align-items-center gap-1 mt-1">
                                <span class="text-muted" style="font-size: 0.72rem;">SKU: ' . $sku . '</span>
                                ' . $tagHtml . '
                                ' . $flashHtml . '
                            </div>
                        </div>
                    </div>';
                })
                ->addColumn('category', function ($prod) {
                    $catName = e($prod->category_name ?? 'Uncategorized');
                    return '<span class="badge bg-body-secondary text-body border">' . $catName . '</span>';
                })
                ->addColumn('price_formatted', function ($prod) {
                    $oldPriceHtml = ($prod->old_price && $prod->old_price > $prod->price) ? '<span class="text-muted text-decoration-line-through small d-block">৳ ' . number_format($prod->old_price, 0) . '</span>' : '';
                    return '<div class="fw-bold text-body fs-6">৳ ' . number_format($prod->price, 0) . '</div>' . $oldPriceHtml;
                })
                ->addColumn('inventory_badge', function ($prod) {
                    if ($prod->stock_qty <= 0) {
                        return '<span class="badge bg-danger text-white rounded-pill">Out of Stock</span>';
                    } elseif ($prod->stock_qty <= 5) {
                        return '<span class="badge bg-warning text-dark rounded-pill">' . $prod->stock_qty . ' Units (Low)</span>';
                    }
                    return '<span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill">' . $prod->stock_qty . ' Units</span>';
                })
                ->addColumn('status_toggle', function ($prod) {
                    $adminId = (int) session('admin_id', 0);
                    $canToggle = app(\App\Services\Rbac\PermissionService::class)->isSuperAdmin($adminId)
                        || app(\App\Services\Rbac\PermissionService::class)->userCan($adminId, 'admin.products.toggle');

                    $checked = $prod->is_active ? 'checked' : '';
                    $disabled = $canToggle ? '' : 'disabled';
                    return '<div class="form-check form-switch m-0">
                        <input class="form-check-input" type="checkbox" role="switch" ' . $checked . ' ' . $disabled . ' ' . ($canToggle ? 'onchange="toggleProductActive(' . $prod->id . ')"' : '') . '>
                    </div>';
                })
                ->addColumn('actions', function ($prod) {
                    $adminId = (int) session('admin_id', 0);
                    $canEdit = app(\App\Services\Rbac\PermissionService::class)->isSuperAdmin($adminId)
                        || app(\App\Services\Rbac\PermissionService::class)->userCan($adminId, 'admin.products.update');
                    $canDelete = app(\App\Services\Rbac\PermissionService::class)->isSuperAdmin($adminId)
                        || app(\App\Services\Rbac\PermissionService::class)->userCan($adminId, 'admin.products.delete');

                    $previewUrl = route('product.show', $prod->slug);
                    $html = '<div class="d-inline-flex align-items-center gap-1">';
                    $html .= '<a href="' . $previewUrl . '" target="_blank" class="btn-action text-secondary" title="Preview on Storefront"><i class="fa-solid fa-arrow-up-right-from-square"></i></a>';
                    if ($canEdit) {
                        $html .= '<button type="button" class="btn-action text-primary" onclick="openEditProductModal(' . $prod->id . ')" title="Edit Product"><i class="fa-solid fa-pen-to-square"></i></button>';
                    }
                    if ($canDelete) {
                        $html .= '<button type="button" class="btn-action text-danger" onclick="deleteProductAjax(' . $prod->id . ')" title="Delete Product"><i class="fa-solid fa-trash"></i></button>';
                    }
                    $html .= '</div>';
                    return $html;
                })
                ->rawColumns(['product_details', 'category', 'price_formatted', 'inventory_badge', 'status_toggle', 'actions'])
                ->make(true);
        }

        $categories = DB::table('categories')->where('is_active', 1)->orderBy('sort_order')->get();

        return view('backend.products.index', compact('categories'));
    }

    public function productJson($id)
    {
        $product = DB::table('products')->where('id', $id)->first();
        if (!$product) {
            return response()->json(['success' => false, 'message' => 'Product not found.'], 404);
        }

        $variants = is_array($product->variants) ? $product->variants : json_decode($product->variants ?? '[]', true);
        if (is_string($variants)) {
            $variants = json_decode($variants, true) ?: [];
        }

        $specs = is_array($product->specifications) ? $product->specifications : json_decode($product->specifications ?? '[]', true);
        if (is_string($specs)) {
            $specs = json_decode($specs, true) ?: [];
        }

        $gallery = is_array($product->gallery_images) ? $product->gallery_images : json_decode($product->gallery_images ?? '[]', true);
        if (is_string($gallery)) {
            $gallery = json_decode($gallery, true) ?: [];
        }

        return response()->json([
            'success' => true,
            'product' => $product,
            'variants' => $variants ?: [],
            'specifications' => $specs ?: [],
            'gallery_images' => $gallery ?: [],
            'gallery_text' => is_array($gallery) ? implode("\n", $gallery) : '',
        ]);
    }

    public function productAjaxSave(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'price' => 'required|numeric|min:0',
            'cost_price' => 'nullable|numeric|min:0',
            'stock_qty' => 'required|integer|min:0',
            'main_image' => 'nullable|string|max:1000',
            'main_image_file' => 'nullable|image|max:10240',
            'gallery_files.*' => 'nullable|image|max:10240',
            'var_image_file.*' => 'nullable|image|max:10240',
        ]);

        $id = $request->input('id');
        $isEdit = !empty($id);

        if (!$isEdit) {
            $slug = Str::slug($request->title);
            $originalSlug = $slug;
            $count = 1;
            while (DB::table('products')->where('slug', $slug)->exists()) {
                $slug = "{$originalSlug}-" . $count++;
            }
        } else {
            $existing = DB::table('products')->where('id', $id)->first();
            $slug = $existing ? $existing->slug : Str::slug($request->title);
        }

        $optimizer = app(ImageOptimizerService::class);

        $mainImage = $request->input('main_image') ?: 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e';
        if ($request->hasFile('main_image_file')) {
            $mainImage = $optimizer->convertToWebp($request->file('main_image_file'), 'products', 1200, 85);
        }

        $variants = $this->parseVariantsInput($request);

        $galleryImages = array_filter(array_map('trim', explode("\n", str_replace("\r", "", $request->gallery_images ?? ''))));
        if ($request->hasFile('gallery_files')) {
            foreach ($request->file('gallery_files') as $gFile) {
                if ($gFile) {
                    $galleryImages[] = $optimizer->convertToWebp($gFile, 'products', 1200, 85);
                }
            }
        }
        if (empty($galleryImages)) {
            $galleryImages = [$mainImage];
        }

        $specs = $this->parseSpecsInput($request);

        $data = [
            'category_id' => $request->category_id,
            'title' => $request->title,
            'sku' => $request->sku ?: 'ZB-' . strtoupper(Str::random(6)),
            'price' => (float) $request->price,
            'cost_price' => $request->filled('cost_price') ? (float) $request->cost_price : null,
            'old_price' => $request->old_price ? (float) $request->old_price : null,
            'stock_qty' => (int) $request->stock_qty,
            'tag' => $request->tag,
            'badge_type' => $request->badge_type ?: 'new',
            'is_flash_deal' => $request->has('is_flash_deal') && ($request->is_flash_deal == '1' || $request->is_flash_deal == 'on') ? 1 : 0,
            'is_featured' => $request->has('is_featured') && ($request->is_featured == '1' || $request->is_featured == 'on') ? 1 : 0,
            'is_active' => $request->has('is_active') && ($request->is_active == '1' || $request->is_active == 'on') ? 1 : 0,
            'main_image' => $mainImage,
            'gallery_images' => json_encode(array_values($galleryImages), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
            'variants' => json_encode($variants, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
            'specifications' => json_encode($specs, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
            'short_desc' => $request->short_desc,
            'description' => $request->description,
            'meta_title' => $request->meta_title ?: $request->title,
            'meta_description' => $request->meta_description ?: ($request->short_desc ?: $request->title),
            'meta_keywords' => $request->meta_keywords,
            'updated_at' => now(),
        ];

        if (!$isEdit) {
            $data['slug'] = $slug;
            $data['rating'] = $request->filled('rating') ? (float) $request->rating : 0.0;
            $data['reviews_count'] = $request->filled('reviews_count') ? (int) $request->reviews_count : 0;
            $data['created_at'] = now();
            $id = DB::table('products')->insertGetId($data);
        } else {
            if ($request->filled('rating')) {
                $data['rating'] = (float) $request->rating;
            }
            if ($request->filled('reviews_count')) {
                $data['reviews_count'] = (int) $request->reviews_count;
            }
            DB::table('products')->where('id', $id)->update($data);
        }

        FrontendCacheService::flushProducts((int) $id);

        $savedProduct = DB::table('products')
            ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
            ->select('products.*', 'categories.name as category_name')
            ->where('products.id', $id)
            ->first();

        return response()->json([
            'success' => true,
            'message' => $isEdit ? 'Product updated successfully.' : 'Product created successfully.',
            'product' => $savedProduct,
        ]);
    }

    public function productCreate()
    {
        $categories = DB::table('categories')->where('is_active', 1)->orderBy('sort_order')->get();
        return view('backend.products.form', compact('categories'));
    }

    public function productStore(Request $request)
    {
        $response = $this->productAjaxSave($request);
        if ($request->ajax() || $request->wantsJson()) {
            return $response;
        }
        return redirect()->route('admin.products.index')->with('success', 'Product created successfully.');
    }

    public function productEdit($id)
    {
        $product = DB::table('products')->where('id', $id)->first();
        if (!$product) {
            return redirect()->route('admin.products.index')->with('error', 'Product not found.');
        }

        $categories = DB::table('categories')->where('is_active', 1)->orderBy('sort_order')->get();
        return view('backend.products.form', compact('product', 'categories'));
    }

    public function productUpdate(Request $request, $id)
    {
        $request->merge(['id' => $id]);
        $response = $this->productAjaxSave($request);
        if ($request->ajax() || $request->wantsJson()) {
            return $response;
        }
        return redirect()->route('admin.products.index')->with('success', 'Product updated successfully.');
    }

    public function productToggle($id)
    {
        $product = DB::table('products')->where('id', $id)->first();
        if (!$product) {
            return response()->json(['success' => false, 'message' => 'Product not found.'], 404);
        }

        $newStatus = $product->is_active ? 0 : 1;
        DB::table('products')->where('id', $id)->update([
            'is_active' => $newStatus,
            'updated_at' => now(),
        ]);
        FrontendCacheService::flushProducts((int) $id);

        return response()->json([
            'success' => true,
            'is_active' => $newStatus,
            'message' => $newStatus ? 'Product is now active.' : 'Product is now inactive.'
        ]);
    }

    public function productDelete($id)
    {
        DB::table('products')->where('id', $id)->delete();
        FrontendCacheService::flushProducts((int) $id);

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Product removed successfully.',
            ]);
        }

        return redirect()->route('admin.products.index')->with('success', 'Product removed successfully.');
    }

    public function productAjaxDelete($id)
    {
        return $this->productDelete($id);
    }

    public function categories(Request $request)
    {
        if ($request->ajax() && !$request->has('_no_dt')) {
            $query = DB::table('categories as c')
                ->leftJoin('categories as p', 'c.parent_id', '=', 'p.id')
                ->leftJoin('categories as gp', 'p.parent_id', '=', 'gp.id')
                ->leftJoin('products', 'c.id', '=', 'products.category_id')
                ->select(
                    'c.*',
                    'p.name as parent_name',
                    'p.name_bn as parent_name_bn',
                    'p.parent_id as parent_parent_id',
                    'gp.name as grandparent_name',
                    'gp.name_bn as grandparent_name_bn',
                    DB::raw('COUNT(DISTINCT products.id) as products_count'),
                    DB::raw('(SELECT COUNT(*) FROM categories sub WHERE sub.parent_id = c.id) as subcategories_count')
                )
                ->groupBy('c.id');

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('image_preview', function ($cat) {
                    if (!empty($cat->image)) {
                        return '<img src="' . e($cat->image) . '" alt="" class="rounded-2 border object-fit-cover" style="width: 44px; height: 44px;" onerror="this.src=\'https://placehold.co/80x80?text=IMG\'">';
                    }
                    return '<div class="rounded-2 d-inline-flex align-items-center justify-content-center border bg-light text-muted" style="width: 44px; height: 44px;"><i class="fa-solid fa-image text-secondary"></i></div>';
                })
                ->addColumn('category_name', function ($cat) {
                    $arrow = '';
                    if (!empty($cat->parent_parent_id)) {
                        $arrow = '<span class="text-muted ms-3 me-1" title="Nested Category (Tier 3)">↳ ↳</span>';
                    } elseif (!empty($cat->parent_id)) {
                        $arrow = '<span class="text-muted ms-1 me-1" title="Sub Category (Tier 2)">↳</span>';
                    }
                    $name = e($cat->name);
                    $nameBn = e($cat->name_bn);
                    return '<div class="fw-bold d-flex align-items-center gap-1.5" id="catName_' . $cat->id . '">' . $arrow . $name . '</div><span class="text-muted small" id="catNameBn_' . $cat->id . '">' . $nameBn . '</span>';
                })
                ->addColumn('hierarchy', function ($cat) {
                    if (empty($cat->parent_id)) {
                        return '<span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-2.5 py-1"><i class="fa-solid fa-folder-tree me-1"></i> Main Category (Tier 1)</span>';
                    } elseif (empty($cat->parent_parent_id)) {
                        return '<span class="badge bg-info-subtle text-info border border-info-subtle rounded-pill px-2.5 py-1"><i class="fa-solid fa-diagram-nested me-1"></i> Sub Category of <strong>' . e($cat->parent_name) . '</strong> (Tier 2)</span>';
                    } else {
                        $gp = !empty($cat->grandparent_name) ? e($cat->grandparent_name) . ' &gt; ' : '';
                        return '<span class="badge rounded-pill px-2.5 py-1" style="background-color:#f3e8ff;color:#7e22ce;border:1px solid #e9d5ff;"><i class="fa-solid fa-code-branch me-1"></i> Nested under <strong>' . $gp . e($cat->parent_name) . '</strong> (Tier 3)</span>';
                    }
                })
                ->addColumn('slug_url', function ($cat) {
                    return '<code class="small text-muted">/category/' . e($cat->slug) . '</code>';
                })
                ->addColumn('items_count', function ($cat) {
                    $subsBadge = (!empty($cat->subcategories_count) && $cat->subcategories_count > 0) ? '<span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill ms-1">' . $cat->subcategories_count . ' Subs</span>' : '';
                    return '<span class="badge bg-body-secondary text-body border">' . $cat->products_count . ' Products</span>' . $subsBadge;
                })
                ->addColumn('status_badge', function ($cat) {
                    $class = $cat->is_active ? 'success' : 'secondary';
                    $text = $cat->is_active ? 'Active' : 'Inactive';
                    return '<span class="badge bg-' . $class . '-subtle text-' . $class . ' rounded-pill px-2.5 py-1">' . $text . '</span>';
                })
                ->addColumn('actions', function ($cat) {
                    $adminId = (int) session('admin_id', 0);
                    $canStore = app(\App\Services\Rbac\PermissionService::class)->isSuperAdmin($adminId)
                        || app(\App\Services\Rbac\PermissionService::class)->userCan($adminId, 'admin.categories.store');
                    $canEdit = app(\App\Services\Rbac\PermissionService::class)->isSuperAdmin($adminId)
                        || app(\App\Services\Rbac\PermissionService::class)->userCan($adminId, 'admin.categories.update');
                    $canDelete = app(\App\Services\Rbac\PermissionService::class)->isSuperAdmin($adminId)
                        || app(\App\Services\Rbac\PermissionService::class)->userCan($adminId, 'admin.categories.delete');

                    $viewUrl = route('category.show', $cat->slug);
                    $html = '<div class="d-inline-flex align-items-center gap-1.5">';
                    $html .= '<a href="' . $viewUrl . '" target="_blank" class="btn-action text-secondary" title="View in Store" data-bs-toggle="tooltip"><i class="fa-solid fa-arrow-up-right-from-square"></i></a>';
                    if ($canStore && empty($cat->parent_parent_id)) {
                        $targetTier = empty($cat->parent_id) ? 2 : 3;
                        $rootParam = !empty($cat->parent_id) ? $cat->parent_id : $cat->id;
                        $subParam = !empty($cat->parent_id) ? $cat->id : 0;
                        $addTitle = empty($cat->parent_id) ? 'Add Sub Category' : 'Add Nested Category';
                        $html .= '<button type="button" class="btn-action text-success" onclick="quickAddSubcategory(' . $targetTier . ', ' . $rootParam . ', ' . $subParam . ')" title="' . $addTitle . '" data-bs-toggle="tooltip"><i class="fa-solid fa-circle-plus"></i></button>';
                    }
                    if ($canEdit) {
                        $html .= '<button type="button" class="btn-action text-primary" onclick="openEditCategoryModal(' . $cat->id . ')" title="Edit Category" data-bs-toggle="tooltip"><i class="fa-solid fa-pen-to-square"></i></button>';
                    }
                    if ($canDelete) {
                        $html .= '<button type="button" class="btn-action text-danger" onclick="deleteCategoryAjax(' . $cat->id . ')" title="Delete Category" data-bs-toggle="tooltip"><i class="fa-solid fa-trash-can"></i></button>';
                    }
                    $html .= '</div>';
                    return $html;
                })
                ->rawColumns(['image_preview', 'category_name', 'hierarchy', 'slug_url', 'items_count', 'status_badge', 'actions'])
                ->make(true);
        }

        $rootCategories = DB::table('categories')
            ->whereNull('parent_id')
            ->where('is_active', 1)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $subCategories = DB::table('categories as c')
            ->join('categories as p', 'c.parent_id', '=', 'p.id')
            ->whereNull('p.parent_id')
            ->where('c.is_active', 1)
            ->select('c.*', 'p.name as parent_name')
            ->orderBy('c.sort_order')
            ->orderBy('c.name')
            ->get();

        return view('backend.categories.index', compact('rootCategories', 'subCategories'));
    }

    public function categoriesTreeJson()
    {
        $rootCategories = DB::table('categories')
            ->whereNull('parent_id')
            ->where('is_active', 1)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $subCategories = DB::table('categories as c')
            ->join('categories as p', 'c.parent_id', '=', 'p.id')
            ->whereNull('p.parent_id')
            ->where('c.is_active', 1)
            ->select('c.*', 'p.name as parent_name')
            ->orderBy('c.sort_order')
            ->orderBy('c.name')
            ->get();

        return response()->json([
            'success' => true,
            'roots' => $rootCategories,
            'subs' => $subCategories,
        ]);
    }

    public function categoryJson($id)
    {
        $category = DB::table('categories')->where('id', $id)->first();
        if (!$category) {
            return response()->json(['success' => false, 'message' => 'Category not found.'], 404);
        }

        $parentId = $category->parent_id;
        $grandparentId = null;
        $tier = 1;

        if ($parentId) {
            $parent = DB::table('categories')->where('id', $parentId)->first();
            if ($parent && $parent->parent_id) {
                $grandparentId = $parent->parent_id;
                $tier = 3;
            } else {
                $tier = 2;
            }
        }

        return response()->json([
            'success' => true,
            'category' => $category,
            'tier' => $tier,
            'parent_id' => $parentId,
            'grandparent_id' => $grandparentId,
        ]);
    }

    public function categoryAjaxSave(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'name_bn' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:categories,id',
            'image_file' => 'nullable|image|max:10240',
        ]);

        $id = $request->input('id');
        $isEdit = !empty($id);

        if (!$isEdit) {
            $slug = Str::slug($request->name);
            $originalSlug = $slug;
            $count = 1;
            while (DB::table('categories')->where('slug', $slug)->exists()) {
                $slug = "{$originalSlug}-" . $count++;
            }
        } else {
            $existing = DB::table('categories')->where('id', $id)->first();
            $slug = $existing ? $existing->slug : Str::slug($request->name);
        }

        $imagePath = $request->input('image');
        if ($request->hasFile('image_file')) {
            $optimizer = app(ImageOptimizerService::class);
            $imagePath = $optimizer->convertToWebp($request->file('image_file'), 'categories', 600, 85);
        }

        $parentId = $request->filled('parent_id') && $request->parent_id != $id ? (int) $request->parent_id : null;
        if ($parentId) {
            $parent = DB::table('categories')->where('id', $parentId)->first();
            if ($parent && $parent->parent_id) {
                $grandparent = DB::table('categories')->where('id', $parent->parent_id)->first();
                if ($grandparent && $grandparent->parent_id) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Cannot nest beyond 3 levels (Category > Sub Category > Nested Category).'
                    ], 422);
                }
            }
        }

        $data = [
            'parent_id' => $parentId,
            'name' => $request->name,
            'name_bn' => $request->name_bn,
            'icon' => $request->icon ?: 'fa-solid fa-layer-group',
            'image' => $imagePath,
            'is_active' => $request->has('is_active') && ($request->is_active == '1' || $request->is_active == 'on') ? 1 : 0,
            'sort_order' => (int) ($request->sort_order ?? 0),
            'updated_at' => now(),
        ];

        if (!$isEdit) {
            $data['slug'] = $slug;
            $data['created_at'] = now();
            $id = DB::table('categories')->insertGetId($data);
        } else {
            DB::table('categories')->where('id', $id)->update($data);
        }

        FrontendCacheService::flush();

        $category = DB::table('categories')->where('id', $id)->first();

        return response()->json([
            'success' => true,
            'message' => $isEdit ? 'Category updated successfully.' : 'Category created successfully.',
            'category' => $category,
        ]);
    }

    public function categoryStore(Request $request)
    {
        $response = $this->categoryAjaxSave($request);
        if ($request->ajax() || $request->wantsJson()) {
            return $response;
        }
        return redirect()->back()->with('success', 'Category created successfully.');
    }

    public function categoryUpdate(Request $request, $id)
    {
        $request->merge(['id' => $id]);
        $response = $this->categoryAjaxSave($request);
        if ($request->ajax() || $request->wantsJson()) {
            return $response;
        }
        return redirect()->back()->with('success', 'Category updated successfully.');
    }

    public function categoryDeleteCheck($id)
    {
        $category = DB::table('categories')->where('id', $id)->first();
        if (!$category) {
            return response()->json(['success' => false, 'message' => 'Category not found.'], 404);
        }

        $childIds = DB::table('categories')->where('parent_id', $id)->pluck('id')->toArray();
        $grandChildIds = !empty($childIds) ? DB::table('categories')->whereIn('parent_id', $childIds)->pluck('id')->toArray() : [];
        $allDescendantCategoryIds = array_unique(array_merge($childIds, $grandChildIds));
        $allAffectedCategoryIds = array_unique(array_merge([$id], $allDescendantCategoryIds));

        $productsCount = DB::table('products')->whereIn('category_id', $allAffectedCategoryIds)->count();
        $subcategoriesCount = count($allDescendantCategoryIds);

        $availableTargets = DB::table('categories')
            ->whereNotIn('id', $allAffectedCategoryIds)
            ->where('is_active', 1)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['id', 'name', 'name_bn', 'parent_id']);

        return response()->json([
            'success' => true,
            'category' => $category,
            'products_count' => $productsCount,
            'subcategories_count' => $subcategoriesCount,
            'has_dependents' => ($productsCount > 0 || $subcategoriesCount > 0),
            'available_targets' => $availableTargets,
        ]);
    }

    public function categoryDelete(Request $request, $id)
    {
        $category = DB::table('categories')->where('id', $id)->first();
        if (!$category) {
            return response()->json(['success' => false, 'message' => 'Category not found.'], 404);
        }

        $childIds = DB::table('categories')->where('parent_id', $id)->pluck('id')->toArray();
        $grandChildIds = !empty($childIds) ? DB::table('categories')->whereIn('parent_id', $childIds)->pluck('id')->toArray() : [];
        $allDescendantCategoryIds = array_unique(array_merge($childIds, $grandChildIds));
        $allAffectedCategoryIds = array_unique(array_merge([$id], $allDescendantCategoryIds));

        $productsCount = DB::table('products')->whereIn('category_id', $allAffectedCategoryIds)->count();
        $subcategoriesCount = count($allDescendantCategoryIds);

        $reassignToId = $request->input('reassign_to_id');

        if (($productsCount > 0 || $subcategoriesCount > 0) && !$reassignToId && !$request->has('force_unlink')) {
            return response()->json([
                'success' => false,
                'requires_reassignment' => true,
                'products_count' => $productsCount,
                'subcategories_count' => $subcategoriesCount,
                'message' => 'This category has attached products or subcategories. Please choose a target category to reassign.',
            ], 422);
        }

        if ($reassignToId) {
            if (in_array((int) $reassignToId, $allAffectedCategoryIds)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Target category cannot be the category itself or one of its descendants.',
                ], 422);
            }

            $targetCategory = DB::table('categories')->where('id', $reassignToId)->first();
            if (!$targetCategory) {
                return response()->json([
                    'success' => false,
                    'message' => 'Selected target category was not found.',
                ], 404);
            }
        }

        DB::transaction(function () use ($allAffectedCategoryIds, $allDescendantCategoryIds, $id, $reassignToId) {
            if ($reassignToId) {
                DB::table('products')
                    ->whereIn('category_id', $allAffectedCategoryIds)
                    ->update([
                        'category_id' => $reassignToId,
                        'updated_at' => now(),
                    ]);
            } else {
                DB::table('products')
                    ->whereIn('category_id', $allAffectedCategoryIds)
                    ->update([
                        'category_id' => null,
                        'updated_at' => now(),
                    ]);
            }

            if (!empty($allDescendantCategoryIds)) {
                DB::table('categories')->whereIn('id', $allDescendantCategoryIds)->delete();
            }

            DB::table('categories')->where('id', $id)->delete();
        });

        FrontendCacheService::flush();

        $msg = 'Category deleted successfully.';
        if ($reassignToId && $productsCount > 0) {
            $targetName = DB::table('categories')->where('id', $reassignToId)->value('name') ?: 'target category';
            $msg = "Category deleted successfully. {$productsCount} products were safely reassigned to {$targetName}.";
        }

        if ($request->ajax() || $request->wantsJson() || $request->filled('reassign_to_id')) {
            return response()->json([
                'success' => true,
                'message' => $msg,
            ]);
        }

        return redirect()->back()->with('success', $msg);
    }

    public function categoryAjaxDelete(Request $request, $id)
    {
        return $this->categoryDelete($request, $id);
    }

    public function banners()
    {
        $banners = DB::table('banners')->orderBy('sort_order')->get();
        $categories = DB::table('categories')->where('is_active', 1)->orderBy('name')->get(['id', 'name', 'slug']);
        return view('backend.banners.index', compact('banners', 'categories'));
    }

    public function bannerJson($id)
    {
        $banner = DB::table('banners')->where('id', $id)->first();
        if (!$banner) {
            return response()->json(['success' => false, 'message' => 'Banner not found.'], 404);
        }

        return response()->json([
            'success' => true,
            'banner' => $banner,
        ]);
    }

    public function bannerAjaxSave(Request $request)
    {
        $request->validate([
            'title' => 'nullable|string|max:255',
            'image_file' => 'nullable|image|max:10240',
        ]);

        $id = $request->input('id');
        $isEdit = !empty($id);

        $imageUrl = $request->image_url;
        if ($request->hasFile('image_file')) {
            $optimizer = app(ImageOptimizerService::class);
            $imageUrl = $optimizer->convertToWebp($request->file('image_file'), 'banners', 1920, 85);
        }

        $displayMode = in_array($request->display_mode, ['both', 'image_only', 'text_only']) ? $request->display_mode : 'both';
        $overlayEnabled = $request->has('overlay_enabled') && ($request->overlay_enabled == '1' || $request->overlay_enabled == 'on') ? 1 : 0;
        $overlayColor = $request->overlay_color ?: '#000000';
        $overlayOpacity = is_numeric($request->overlay_opacity) ? max(0, min(100, (int) $request->overlay_opacity)) : 40;
        $textAlign = in_array($request->text_align, ['left', 'center', 'right']) ? $request->text_align : 'left';
        $textColor = $request->text_color ?: '#ffffff';

        $data = [
            'type' => $request->type ?: 'hero_slide',
            'display_mode' => $displayMode,
            'badge_text' => $request->badge_text ?: ($request->title_bn ?: null),
            'title' => $request->title ?: '',
            'subtitle' => $request->subtitle,
            'btn_text' => $request->button_text ?: ($request->btn_text ?: ''),
            'btn_link' => $request->link ?: ($request->btn_link ?: '/'),
            'image_url' => $imageUrl ?: '',
            'gradient' => $request->gradient ?: null,
            'overlay_enabled' => $overlayEnabled,
            'overlay_color' => $overlayColor,
            'overlay_opacity' => $overlayOpacity,
            'text_align' => $textAlign,
            'text_color' => $textColor,
            'is_active' => $request->has('is_active') && ($request->is_active == '1' || $request->is_active == 'on') ? 1 : 0,
            'sort_order' => (int) ($request->sort_order ?? 0),
            'updated_at' => now(),
        ];

        if (!$isEdit) {
            $data['created_at'] = now();
            $id = DB::table('banners')->insertGetId($data);
        } else {
            DB::table('banners')->where('id', $id)->update($data);
        }

        FrontendCacheService::flush();

        $banner = DB::table('banners')->where('id', $id)->first();

        return response()->json([
            'success' => true,
            'message' => $isEdit ? 'Banner updated successfully.' : 'Banner created successfully.',
            'banner' => $banner,
        ]);
    }

    public function bannerStore(Request $request)
    {
        $response = $this->bannerAjaxSave($request);
        if ($request->ajax() || $request->wantsJson()) {
            return $response;
        }
        return redirect()->back()->with('success', 'Banner created successfully.');
    }

    public function bannerUpdate(Request $request, $id)
    {
        $request->merge(['id' => $id]);
        $response = $this->bannerAjaxSave($request);
        if ($request->ajax() || $request->wantsJson()) {
            return $response;
        }
        return redirect()->back()->with('success', 'Banner updated successfully.');
    }

    public function bannerDelete($id)
    {
        DB::table('banners')->where('id', $id)->delete();
        FrontendCacheService::flush();

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Banner deleted successfully.',
            ]);
        }

        return redirect()->back()->with('success', 'Banner deleted successfully.');
    }

    public function bannerAjaxDelete($id)
    {
        return $this->bannerDelete($id);
    }

    public function mediaUpload(Request $request)
    {
        $request->validate([
            'file' => 'required|image|max:10240',
            'folder' => 'nullable|string|max:50',
        ]);

        $folder = preg_replace('/[^a-zA-Z0-9_\-]/', '', (string) $request->input('folder', 'uploads')) ?: 'uploads';
        $optimizer = app(ImageOptimizerService::class);
        $url = $optimizer->convertToWebp($request->file('file'), $folder);

        return response()->json([
            'success' => true,
            'url' => $url,
            'message' => 'Image optimized and converted to WebP successfully.'
        ]);
    }

    public function customers(Request $request)
    {
        if ($request->ajax() && !$request->has('_no_dt')) {
            $query = DB::table('orders')
                ->select(
                    'customer_phone',
                    DB::raw('MAX(customer_name) as name'),
                    DB::raw('MAX(customer_address) as address'),
                    DB::raw('MAX(district) as district'),
                    DB::raw('COUNT(id) as orders_count'),
                    DB::raw('SUM(CASE WHEN order_status != "cancelled" THEN total ELSE 0 END) as total_spend'),
                    DB::raw('MAX(created_at) as last_order_date')
                )
                ->groupBy('customer_phone');

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('customer_profile', function ($c) {
                    $name = e($c->name ?: 'Guest Customer');
                    $initial = strtoupper(substr($name, 0, 1));
                    return '<div class="d-flex align-items-center gap-2.5">
                        <div class="rounded-circle bg-primary-subtle text-primary fw-bold d-inline-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                            ' . $initial . '
                        </div>
                        <div>
                            <div class="fw-bold text-body">' . $name . '</div>
                            <span class="text-muted small font-monospace">' . e($c->customer_phone) . '</span>
                        </div>
                    </div>';
                })
                ->addColumn('location', function ($c) {
                    $dist = e($c->district ?? 'Dhaka');
                    $addr = e($c->address ?? 'No address provided');
                    return '<div><span class="badge bg-body-secondary text-body border">' . $dist . '</span></div><span class="small text-muted text-truncate d-block" style="max-width: 220px;">' . $addr . '</span>';
                })
                ->addColumn('orders_badge', function ($c) {
                    return '<span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-2.5 py-1">' . $c->orders_count . ' Orders</span>';
                })
                ->addColumn('spend_formatted', function ($c) {
                    return '<div class="fw-bold text-success">৳ ' . number_format($c->total_spend, 0) . '</div>';
                })
                ->addColumn('last_active', function ($c) {
                    return '<span class="small text-muted">' . date('d M Y, h:i A', strtotime($c->last_order_date)) . '</span>';
                })
                ->addColumn('actions', function ($c) {
                    $phoneParam = urlencode($c->customer_phone);
                    $ordersUrl = route('admin.orders.index') . '?search=' . $phoneParam;
                    return '<div class="d-inline-flex align-items-center gap-1">
                        <a href="' . $ordersUrl . '" class="btn-action text-primary px-2.5 w-auto" title="View Customer Orders"><i class="fa-solid fa-receipt me-1"></i> Orders</a>
                    </div>';
                })
                ->rawColumns(['customer_profile', 'location', 'orders_badge', 'spend_formatted', 'last_active', 'actions'])
                ->make(true);
        }

        return view('backend.customers.index');
    }

    public function settings()
    {
        $settings = $this->getSettingsMap();
        return view('backend.settings.index', compact('settings'));
    }

    public function settingsUpdate(Request $request)
    {
        $keys = [
            'store_name',
            'store_tagline',
            'store_phone',
            'store_whatsapp',
            'whatsapp_number',
            'store_email',
            'store_address',
            'shipping_dhaka',
            'shipping_outside',
            'free_shipping_enabled',
            'free_shipping_min_amount',
            'free_shipping_threshold',
            'announcement_text',
            'announcement_bar_text',
            'announcement_bar_active',
            'currency_symbol',
            'promo_card3_status',
            'promo_card3_badge',
            'promo_card3_title',
            'promo_card3_subtitle',
            'promo_card3_btn_text',
            'promo_card3_btn_url',
        ];

        foreach ($keys as $key) {
            if ($key === 'free_shipping_enabled' || $key === 'promo_card3_status') {
                $val = ($request->has($key) && ($request->$key == '1' || $request->$key == 'on')) ? '1' : '0';
                DB::table('settings')->updateOrInsert(
                    ['key' => $key],
                    ['value' => $val, 'updated_at' => now()]
                );
            } elseif ($request->has($key)) {
                $val = $request->input($key, '');
                DB::table('settings')->updateOrInsert(
                    ['key' => $key],
                    ['value' => $val, 'updated_at' => now()]
                );
            }
        }

        \Illuminate\Support\Facades\Cache::forget('site_settings');
        \App\Services\Frontend\FrontendCacheService::flush();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Store settings saved successfully.',
            ]);
        }

        return redirect()->back()->with('success', 'Store settings saved successfully.');
    }

    public function settingsAjaxUpdate(Request $request)
    {
        if ($request->filled('_single_key')) {
            $key = (string) $request->input('_single_key');
            $keys = [
                'store_name',
                'store_tagline',
                'store_phone',
                'store_whatsapp',
                'store_email',
                'store_address',
                'shipping_dhaka',
                'shipping_outside',
                'free_shipping_enabled',
                'free_shipping_min_amount',
                'free_shipping_threshold',
                'announcement_text',
                'announcement_bar_text',
                'announcement_bar_active',
                'currency_symbol',
                'promo_card3_status',
                'promo_card3_badge',
                'promo_card3_title',
                'promo_card3_subtitle',
                'promo_card3_btn_text',
                'promo_card3_btn_url',
            ];

            if (in_array($key, $keys, true)) {
                $raw = $request->input($key, $request->input('value', ''));
                if ($key === 'free_shipping_enabled' || $key === 'promo_card3_status' || $key === 'announcement_bar_active') {
                    $val = ($raw === true || $raw === '1' || $raw === 'true' || $raw === 'on') ? '1' : '0';
                } else {
                    $val = (string) $raw;
                }
                DB::table('settings')->updateOrInsert(
                    ['key' => $key],
                    ['value' => $val, 'updated_at' => now()]
                );
                \Illuminate\Support\Facades\Cache::forget('site_settings');
                \App\Services\Frontend\FrontendCacheService::flush();
                return response()->json([
                    'success' => true,
                    'message' => 'Setting saved successfully.',
                    'key' => $key,
                    'value' => $val,
                ]);
            }
        }

        return $this->settingsUpdate($request);
    }

    private function getSettingsMap()
    {
        $defaults = [
            'store_name' => 'ZippyBD',
            'store_phone' => '01700000000',
            'whatsapp_number' => '8801700000000',
            'store_email' => 'support@zippybd.com',
            'store_address' => 'ঢাকা, বাংলাদেশ',
            'shipping_dhaka' => '60',
            'shipping_outside' => '120',
            'free_shipping_enabled' => '0',
            'free_shipping_min_amount' => '5000',
            'free_shipping_threshold' => '5000',
            'announcement_bar_text' => '🔥 মেগা সেল! সারা দেশে ক্যাশ অন ডেলিভারি সুবিধা!',
            'announcement_bar_active' => '1',
            'currency_symbol' => '৳',
        ];

        $dbSettings = DB::table('settings')->pluck('value', 'key')->toArray();
        return array_merge($defaults, $dbSettings);
    }

    private function parseVariantsInput(Request $request)
    {
        $variantNames = $request->input('var_name', []);
        $variantPrices = $request->input('var_price', []);
        $variantImages = $request->input('var_image', []);
        $variantStocks = $request->input('var_stock', []);
        $variantFiles = $request->file('var_image_file', []);

        $optimizer = app(ImageOptimizerService::class);

        $variants = [];
        if (is_array($variantNames)) {
            foreach ($variantNames as $idx => $name) {
                $name = trim($name);
                if (!empty($name)) {
                    $price = isset($variantPrices[$idx]) && is_numeric($variantPrices[$idx]) ? (float) $variantPrices[$idx] : (float) $request->price;

                    $image = isset($variantImages[$idx]) && !empty(trim($variantImages[$idx])) ? trim($variantImages[$idx]) : $request->main_image;
                    if (isset($variantFiles[$idx]) && $variantFiles[$idx]) {
                        $image = $optimizer->convertToWebp($variantFiles[$idx], 'products/variants', 800, 85);
                    }

                    $stock = isset($variantStocks[$idx]) && is_numeric($variantStocks[$idx]) ? (int) $variantStocks[$idx] : (int) $request->stock_qty;

                    $variants[] = [
                        'name' => $name,
                        'price' => $price,
                        'image' => $image,
                        'stock' => $stock,
                    ];
                }
            }
        }
        return $variants;
    }

    private function parseSpecsInput(Request $request)
    {
        $specKeys = $request->input('spec_key', []);
        $specVals = $request->input('spec_val', []);

        $specs = [];
        if (is_array($specKeys) && is_array($specVals)) {
            foreach ($specKeys as $idx => $k) {
                $k = trim($k);
                $v = isset($specVals[$idx]) ? trim($specVals[$idx]) : '';
                if (!empty($k) && !empty($v)) {
                    $specs[$k] = $v;
                }
            }
        }
        return $specs;
    }
}
