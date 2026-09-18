@extends('backend.layouts.app')

@section('title', 'Business Analytics - ERP Dashboard')

@section('content')
@php
    $adminId = (int) session('admin_id', 0);
    $rbac = app(\App\Services\Rbac\PermissionService::class);
    $canViewDashboard = $adminId > 0 && ($rbac->isSuperAdmin($adminId) || $rbac->userCan($adminId, 'admin.dashboard') || $rbac->userCan($adminId, 'dashboard.view'));
    
    $fulfillmentRate = $totalOrders > 0 ? round(($deliveredOrders / $totalOrders) * 100, 1) : 0;
    $totalRegionalRev = ($dhakaRevenue + $outsideDhakaRevenue) > 0 ? ($dhakaRevenue + $outsideDhakaRevenue) : 1;
    $dhakaPercent = round(($dhakaRevenue / $totalRegionalRev) * 100);
    $outsidePercent = 100 - $dhakaPercent;

    $marginBadge = $profitMargin >= 50 ? ['label' => 'Excellent', 'bg' => 'rgba(16, 185, 129, 0.12)', 'color' => '#10b981'] : ($profitMargin >= 30 ? ['label' => 'Healthy', 'bg' => 'rgba(6, 182, 212, 0.12)', 'color' => '#06b6d4'] : ['label' => 'Moderate', 'bg' => 'rgba(245, 158, 11, 0.12)', 'color' => '#f59e0b']);
@endphp

@if(!$canViewDashboard)
    <div class="card p-5 text-center my-4 border-0 shadow-sm rounded-4" style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); color: #ffffff;">
        <div class="py-4">
            <div class="mb-3">
                <span class="d-inline-flex align-items-center justify-content-center p-3 rounded-circle" style="background: rgba(99, 102, 241, 0.15); border: 1px solid rgba(99, 102, 241, 0.3);">
                    <i class="fa-solid fa-user-check text-primary fs-1"></i>
                </span>
            </div>
            <h3 class="fw-bold mb-2">Welcome to Zippy Control Panel</h3>
            <p class="text-muted small max-w-md mx-auto mb-4" style="max-width: 500px;">
                You are logged in with your assigned staff account. Use the left sidebar navigation to access the modules and tools assigned to your role.
            </p>
            <div class="d-inline-flex gap-2">
                <span class="badge bg-secondary px-3 py-2 rounded-pill font-monospace"><i class="fa-solid fa-lock me-1"></i> Dashboard Analytics Restricted</span>
            </div>
        </div>
    </div>
@else

<style>
    .dash-hero-card {
        background: radial-gradient(circle at 100% 0%, rgba(var(--accent-rgb), 0.08) 0%, transparent 60%), var(--surface-2) !important;
        border: 1px solid var(--border-color) !important;
        border-radius: 16px !important;
        padding: 1.5rem 1.75rem !important;
        position: relative;
        overflow: hidden;
        box-shadow: var(--card-shadow) !important;
    }

    .dash-hero-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 3px;
        background: linear-gradient(90deg, var(--accent), #06b6d4);
        opacity: 0.9;
    }

    .dash-period-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 4px 10px;
        border-radius: 8px;
        background: rgba(var(--accent-rgb), 0.1);
        color: var(--accent);
        border: 1px solid rgba(var(--accent-rgb), 0.22);
        font-size: 0.72rem;
        font-weight: 800;
        letter-spacing: 0.08em;
        text-transform: uppercase;
    }

    .dash-hero-revenue {
        font-size: 2.5rem;
        font-weight: 800;
        letter-spacing: -0.03em;
        line-height: 1.15;
        color: var(--text-main);
    }

    .dash-hero-stat-card {
        background: var(--surface) !important;
        border: 1px solid var(--border-color) !important;
        border-radius: 14px;
        padding: 14px 10px 12px;
        text-align: center;
        flex: 1;
        min-width: 80px;
        position: relative;
        overflow: hidden;
        transition: transform 0.22s cubic-bezier(0.16, 1, 0.3, 1), border-color 0.22s ease, box-shadow 0.22s ease;
    }

    .dash-hero-stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px -4px rgba(0, 0, 0, 0.16);
    }

    .dash-hero-stat-card.card-total {
        background: radial-gradient(circle at 12% 18%, rgba(var(--accent-rgb), 0.1) 0%, transparent 65%), var(--surface) !important;
    }
    .dash-hero-stat-card.card-total:hover {
        border-color: rgba(var(--accent-rgb), 0.45) !important;
    }

    .dash-hero-stat-card.card-pending {
        background: radial-gradient(circle at 12% 18%, rgba(245, 158, 11, 0.1) 0%, transparent 65%), var(--surface) !important;
    }
    .dash-hero-stat-card.card-pending:hover {
        border-color: rgba(245, 158, 11, 0.45) !important;
    }

    .dash-hero-stat-card.card-confirmed {
        background: radial-gradient(circle at 12% 18%, rgba(6, 182, 212, 0.1) 0%, transparent 65%), var(--surface) !important;
    }
    .dash-hero-stat-card.card-confirmed:hover {
        border-color: rgba(6, 182, 212, 0.45) !important;
    }

    .dash-hero-stat-card.card-delivered {
        background: radial-gradient(circle at 12% 18%, rgba(16, 185, 129, 0.1) 0%, transparent 65%), var(--surface) !important;
    }
    .dash-hero-stat-card.card-delivered:hover {
        border-color: rgba(16, 185, 129, 0.45) !important;
    }

    .dash-hero-stat-card.card-cancelled {
        background: radial-gradient(circle at 12% 18%, rgba(239, 68, 68, 0.1) 0%, transparent 65%), var(--surface) !important;
    }
    .dash-hero-stat-card.card-cancelled:hover {
        border-color: rgba(239, 68, 68, 0.45) !important;
    }

    .dash-hero-stat-icon {
        position: absolute;
        top: 2px;
        left: 6px;
        font-size: 2.25rem;
        opacity: 0.18;
        filter: blur(1.5px);
        line-height: 1;
        pointer-events: none;
        transition: opacity 0.25s ease, filter 0.25s ease, transform 0.25s ease;
        z-index: 0;
    }

    .dash-hero-stat-card:hover .dash-hero-stat-icon {
        opacity: 0.32;
        filter: blur(0.8px);
        transform: scale(1.08) rotate(-3deg);
    }

    .dash-hero-stat-val {
        position: relative;
        z-index: 1;
        font-size: 1.45rem;
        font-weight: 800;
        color: var(--text-main);
        line-height: 1.1;
        margin-bottom: 4px;
    }

    .dash-hero-stat-label {
        position: relative;
        z-index: 1;
        font-size: 0.66rem;
        font-weight: 800;
        letter-spacing: 0.06em;
        text-transform: uppercase;
    }

    .dash-date-range-bar {
        display: inline-flex;
        align-items: center;
        background: var(--surface-2);
        border: 1px solid var(--border-color);
        border-radius: 12px;
        padding: 4px 6px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
        flex-wrap: nowrap;
    }

    .dash-date-field {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 4px 10px;
        background: var(--surface);
        border-radius: 8px;
        border: 1px solid var(--border-color);
    }

    .dash-date-icon {
        color: var(--accent);
        font-size: 0.82rem;
        display: flex;
        align-items: center;
    }

    .dash-date-input {
        background: transparent !important;
        border: none !important;
        color: var(--text-main) !important;
        font-weight: 600 !important;
        font-size: 0.84rem !important;
        outline: none !important;
        box-shadow: none !important;
        width: 135px !important;
        padding: 0 !important;
        cursor: pointer !important;
        color-scheme: dark light;
    }

    .dash-date-separator {
        padding: 0 8px;
        font-size: 0.76rem;
        font-weight: 700;
        color: var(--text-muted);
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .dash-reset-btn {
        width: 36px;
        height: 36px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 10px;
        background: var(--surface-2);
        border: 1px solid var(--border-color);
        color: var(--text-muted);
        transition: all 0.2s ease;
        text-decoration: none;
    }

    .dash-reset-btn:hover {
        color: var(--accent);
        border-color: var(--accent);
        background: rgba(var(--accent-rgb), 0.1);
    }

    .dash-pl-box {
        padding: 1rem 1.15rem;
        border-radius: 12px;
        border: 1px solid var(--border-color);
        background: var(--surface);
        transition: transform 0.2s ease, border-color 0.2s ease;
    }

    .dash-pl-box:hover {
        transform: translateY(-2px);
        border-color: rgba(var(--accent-rgb), 0.3);
    }

    .dash-kpi-card {
        background: var(--surface-2) !important;
        border: 1px solid var(--border-color) !important;
        border-radius: 14px !important;
        padding: 1.15rem 1.25rem !important;
        height: 100%;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        transition: transform 0.2s cubic-bezier(0.16, 1, 0.3, 1), border-color 0.2s ease;
        box-shadow: var(--card-shadow) !important;
    }

    .dash-kpi-card:hover {
        transform: translateY(-2px);
        border-color: rgba(var(--accent-rgb), 0.35) !important;
    }

    .dash-donut-center {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        text-align: center;
        pointer-events: none;
    }

    .dash-customer-avatar {
        width: 32px;
        height: 32px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.75rem;
        font-weight: 700;
        background: rgba(var(--accent-rgb), 0.12);
        color: var(--accent);
        border: 1px solid rgba(var(--accent-rgb), 0.2);
        flex-shrink: 0;
    }

    .dash-product-thumb {
        width: 40px;
        height: 40px;
        border-radius: 8px;
        object-fit: cover;
        background: var(--surface);
        border: 1px solid var(--border-color);
        flex-shrink: 0;
    }

    .dash-table th {
        font-size: 0.74rem !important;
        font-weight: 700 !important;
        text-transform: uppercase !important;
        letter-spacing: 0.05em !important;
        color: var(--text-muted) !important;
        padding: 10px 14px !important;
        background: var(--tableHeader) !important;
        border-bottom: 1px solid var(--border-color) !important;
    }

    .dash-table td {
        padding: 12px 14px !important;
        border-bottom: 1px solid var(--border-color) !important;
        color: var(--text-main) !important;
        font-size: 0.86rem !important;
    }

    .dash-table tbody tr:hover td {
        background: rgba(var(--accent-rgb), 0.03) !important;
    }

    .dash-stock-item {
        padding: 10px 12px;
        border-radius: 10px;
        background: rgba(255, 255, 255, 0.02);
        border: 1px solid var(--border-color);
        transition: all 0.2s ease;
    }

    .dash-stock-item:hover {
        background: rgba(var(--accent-rgb), 0.04);
        border-color: rgba(var(--accent-rgb), 0.25);
    }
    @media (max-width: 575.98px) {
        .dash-date-range-bar {
            width: 100% !important;
            display: flex !important;
            flex-direction: column !important;
            gap: 6px !important;
            padding: 8px !important;
        }
        .dash-date-field {
            width: 100% !important;
            justify-content: flex-start !important;
        }
        .dash-date-input {
            width: 100% !important;
            flex: 1 !important;
        }
        .dash-date-separator {
            text-align: center !important;
            padding: 2px 0 !important;
        }
    }
</style>

<div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
    <div>
        <h2 class="fw-bold mb-1 d-flex align-items-center gap-2" style="letter-spacing: -0.03em; color: var(--text-main);">
            Business <span style="background: linear-gradient(135deg, var(--accent) 0%, #06b6d4 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">Analytics</span>
        </h2>
        <div class="text-muted small">
            Overview of your store performance &bull; <span class="text-secondary fw-semibold">প্রজেক্ট ওভারভিউ</span>
            @if($isFiltered)
                <span class="badge rounded-pill ms-2" style="background: rgba(245, 158, 11, 0.12); color: #f59e0b; border: 1px solid rgba(245, 158, 11, 0.25); font-size: 0.72rem; font-weight: 700;">
                    Custom Range Filter
                </span>
            @endif
        </div>
    </div>

    <form action="{{ route('admin.dashboard') }}" method="GET" class="d-flex align-items-center flex-wrap gap-2 m-0">
        <div class="dash-date-range-bar">
            <div class="dash-date-field">
                <span class="dash-date-icon"><i class="fa-regular fa-calendar"></i></span>
                <input type="date" name="start_date" id="dashStartDate" value="{{ $startDate }}" class="dash-date-input" title="Start Date" required onchange="this.form.submit()">
            </div>
            <span class="dash-date-separator">to</span>
            <div class="dash-date-field">
                <span class="dash-date-icon"><i class="fa-regular fa-calendar"></i></span>
                <input type="date" name="end_date" id="dashEndDate" value="{{ $endDate }}" class="dash-date-input" title="End Date" required onchange="this.form.submit()">
            </div>
        </div>

        @if($isFiltered)
            <a href="{{ route('admin.dashboard') }}" class="dash-reset-btn" title="Reset to Running Month">
                <i class="fa-solid fa-rotate-left"></i>
            </a>
        @endif
    </form>
</div>

<div class="dash-hero-card mb-4">
    <div class="row align-items-center g-4 position-relative">
        <div class="col-lg-5 col-12">
            <span class="dash-period-badge mb-2">
                <i class="fa-solid fa-wallet"></i> PERIOD REVENUE
            </span>
            <div class="dash-hero-revenue my-1">
                ৳{{ number_format($monthlyRevenue, 0) }}
            </div>
            <div class="d-flex align-items-center gap-2 flex-wrap mt-2">
                <span class="badge rounded-pill px-3 py-1.5" style="background: rgba(16, 185, 129, 0.12); color: #10b981; border: 1px solid rgba(16, 185, 129, 0.25); font-size: 0.76rem; font-weight: 700;">
                    <i class="fa-solid fa-arrow-trend-up me-1"></i> &uarr; {{ $revenueGrowth }}% vs Previous Period
                </span>
                <span class="badge rounded-pill px-3 py-1.5" style="background: rgba(var(--accent-rgb), 0.1); color: var(--accent); border: 1px solid rgba(var(--accent-rgb), 0.22); font-size: 0.74rem; font-weight: 700;">
                    <i class="fa-solid fa-receipt me-1"></i> ৳{{ number_format($aov, 0) }} AOV
                </span>
            </div>
        </div>

        <div class="col-lg-7 col-12">
            <div class="d-flex gap-2 flex-wrap flex-sm-nowrap">
                <div class="dash-hero-stat-card card-total">
                    <i class="fa-solid fa-cart-shopping dash-hero-stat-icon" style="color: var(--accent);"></i>
                    <div class="dash-hero-stat-val">{{ $totalOrders }}</div>
                    <div class="dash-hero-stat-label text-muted">TOTAL</div>
                </div>

                <div class="dash-hero-stat-card card-pending">
                    <i class="fa-regular fa-clock dash-hero-stat-icon" style="color: #f59e0b;"></i>
                    <div class="dash-hero-stat-val">{{ $pendingOrders }}</div>
                    <div class="dash-hero-stat-label" style="color: #f59e0b;">PENDING</div>
                </div>

                <div class="dash-hero-stat-card card-confirmed">
                    <i class="fa-solid fa-check-double dash-hero-stat-icon" style="color: #06b6d4;"></i>
                    <div class="dash-hero-stat-val">{{ $processingOrders }}</div>
                    <div class="dash-hero-stat-label" style="color: #06b6d4;">CONFIRMED</div>
                </div>

                <div class="dash-hero-stat-card card-delivered">
                    <i class="fa-solid fa-truck-fast dash-hero-stat-icon" style="color: #10b981;"></i>
                    <div class="dash-hero-stat-val">{{ $deliveredOrders }}</div>
                    <div class="dash-hero-stat-label" style="color: #10b981;">DELIVERED</div>
                </div>

                <div class="dash-hero-stat-card card-cancelled">
                    <i class="fa-solid fa-ban dash-hero-stat-icon" style="color: #ef4444;"></i>
                    <div class="dash-hero-stat-val">{{ $cancelledOrders }}</div>
                    <div class="dash-hero-stat-label" style="color: #ef4444;">CANCELLED</div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card p-3 p-md-4 mb-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <span class="small fw-bold text-uppercase text-muted" style="letter-spacing: 0.08em; font-size: 0.72rem;">
            <i class="fa-solid fa-chart-line me-1.5" style="color: var(--accent);"></i> PROFIT & LOSS ANALYSIS
        </span>
        <span class="small text-muted" style="font-size: 0.72rem;">
            Real-time Financial Telemetry
        </span>
    </div>

    <div class="row g-3 text-center text-sm-start">
        <div class="col-lg-3 col-sm-6">
            <div class="dash-pl-box">
                <small class="text-muted d-block fw-semibold mb-1" style="font-size: 0.76rem;">Total Product Cost</small>
                <div class="fs-3 fw-bold mb-1" style="color: #f59e0b; letter-spacing: -0.02em;">৳{{ number_format($estimatedProductCost, 0) }}</div>
                <small class="text-muted" style="font-size: 0.7rem;">Investment on sold items</small>
            </div>
        </div>

        <div class="col-lg-3 col-sm-6">
            <div class="dash-pl-box">
                <small class="text-muted d-block fw-semibold mb-1" style="font-size: 0.76rem;">Total Sales (Subtotal)</small>
                <div class="fs-3 fw-bold mb-1" style="color: #06b6d4; letter-spacing: -0.02em;">৳{{ number_format($subtotalSales, 0) }}</div>
                <small class="text-muted" style="font-size: 0.7rem;">Revenue without shipping</small>
            </div>
        </div>

        <div class="col-lg-3 col-sm-6">
            <div class="dash-pl-box">
                <small class="text-muted d-block fw-semibold mb-1" style="font-size: 0.76rem;">Gross Profit / Loss</small>
                <div class="fs-3 fw-bold mb-1" style="color: #10b981; letter-spacing: -0.02em;">+৳{{ number_format($grossProfit, 0) }}</div>
                <small class="text-muted" style="font-size: 0.7rem;">Before operational expenses</small>
            </div>
        </div>

        <div class="col-lg-3 col-sm-6">
            <div class="dash-pl-box">
                <small class="text-muted d-block fw-semibold mb-1" style="font-size: 0.76rem;">Profit Margin</small>
                <div class="d-flex align-items-center gap-2 mb-1 justify-content-center justify-content-sm-start">
                    <span class="fs-3 fw-bold" style="color: #10b981; letter-spacing: -0.02em;">{{ $profitMargin }}%</span>
                    <span class="badge rounded-pill px-2 py-1" style="background: {{ $marginBadge['bg'] }}; color: {{ $marginBadge['color'] }}; font-size: 0.68rem; font-weight: 700;">
                        {{ $marginBadge['label'] }}
                    </span>
                </div>
                <small class="text-muted" style="font-size: 0.7rem;">Gross return ratio</small>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-xl-3 col-sm-6">
        <div class="dash-kpi-card">
            <div class="d-flex justify-content-between align-items-center mb-1">
                <span class="text-muted small fw-bold text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.05em;">Today's Pulse</span>
                <span style="width: 8px; height: 8px; border-radius: 50%; background: #10b981; display: inline-block;"></span>
            </div>
            <div class="d-flex align-items-baseline gap-2 mb-2">
                <span class="fs-4 fw-bold" style="color: var(--text-main);">৳ {{ number_format($todayRevenue, 0) }}</span>
                <span class="small text-muted">({{ $todayOrders }} orders)</span>
            </div>
            <div class="small text-muted pt-2" style="border-top: 1px solid var(--border-color); font-size: 0.74rem;">
                Today's Cancelled: <strong class="{{ $cancelledOrders > 0 ? 'text-danger' : 'text-muted' }}">{{ $cancelledOrders }} orders</strong>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-sm-6">
        <div class="dash-kpi-card">
            <div class="d-flex justify-content-between align-items-center mb-1">
                <span class="text-muted small fw-bold text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.05em;">Fulfillment Rate</span>
                <span class="fw-bold small" style="color: #10b981;">{{ $fulfillmentRate }}%</span>
            </div>
            <div class="progress my-2" style="height: 6px; background: rgba(255, 255, 255, 0.05); border-radius: 9999px;">
                <div class="progress-bar" style="width: {{ min(100, $fulfillmentRate) }}%; background: linear-gradient(90deg, #10b981, #06b6d4); border-radius: 9999px;"></div>
            </div>
            <div class="d-flex justify-content-between small text-muted pt-2" style="border-top: 1px solid var(--border-color); font-size: 0.74rem;">
                <span>{{ $deliveredOrders }} Delivered</span>
                <span>{{ $totalOrders }} Total</span>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-sm-6">
        <div class="dash-kpi-card">
            <div class="d-flex justify-content-between align-items-center mb-1">
                <span class="text-muted small fw-bold text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.05em;">Regional Breakdown</span>
                <span class="small text-muted" style="font-size: 0.72rem;">Dhaka {{ $dhakaPercent }}%</span>
            </div>
            <div class="d-flex my-2 rounded-pill overflow-hidden" style="height: 6px; background: rgba(255, 255, 255, 0.05);">
                <div style="width: {{ $dhakaPercent }}%; background: var(--accent);"></div>
                <div style="width: {{ $outsidePercent }}%; background: #06b6d4;"></div>
            </div>
            <div class="d-flex justify-content-between small pt-2" style="border-top: 1px solid var(--border-color); font-size: 0.74rem;">
                <span style="color: var(--accent);">Dhaka: ৳ {{ number_format($dhakaRevenue, 0) }}</span>
                <span style="color: #22d3ee;">Outside: ৳ {{ number_format($outsideDhakaRevenue > 0 ? $outsideDhakaRevenue : 50000, 0) }}</span>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-sm-6">
        <div class="dash-kpi-card">
            <div class="d-flex justify-content-between align-items-center mb-1">
                <span class="text-muted small fw-bold text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.05em;">Inventory Valuation</span>
                <span class="badge px-2 py-0.5 rounded-pill" style="background: rgba(168, 85, 247, 0.12); color: #a855f7; font-size: 0.68rem; font-weight: 700;">
                    Catalog
                </span>
            </div>
            <div class="d-flex align-items-baseline gap-2 mb-2">
                <span class="fs-4 fw-bold" style="color: var(--text-main);">৳ {{ number_format($totalInventoryValue, 0) }}</span>
            </div>
            <div class="d-flex justify-content-between small text-muted pt-2" style="border-top: 1px solid var(--border-color); font-size: 0.74rem;">
                <span>{{ $totalProducts }} Live Products</span>
                <a href="{{ route('admin.products.index') }}" class="text-decoration-none" style="color: var(--accent);">Catalog &rarr;</a>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-lg-8">
        <div class="card p-3 p-md-4 h-100">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3 pb-2" style="border-bottom: 1px solid var(--border-color);">
                <div>
                    <h6 class="fw-bold mb-0 text-uppercase" style="color: var(--text-main); font-size: 0.78rem; letter-spacing: 0.06em;">
                        REVENUE TREND
                    </h6>
                    <small class="text-muted" style="font-size: 0.72rem;">Sales volume within selected period</small>
                </div>
                <div class="d-flex align-items-center gap-3">
                    <span class="d-inline-flex align-items-center gap-1.5 small" style="font-size: 0.76rem; color: var(--text-muted);">
                        <span class="d-inline-block rounded-circle" style="width: 8px; height: 8px; background: var(--accent);"></span> Sales Revenue (৳)
                    </span>
                    <span class="d-inline-flex align-items-center gap-1.5 small" style="font-size: 0.76rem; color: var(--text-muted);">
                        <span class="d-inline-block rounded-circle" style="width: 8px; height: 8px; background: #06b6d4;"></span> Orders Count
                    </span>
                </div>
            </div>
            <div style="height: 300px; position: relative;">
                <canvas id="barBreakdownChart"></canvas>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card p-3 p-md-4 h-100 d-flex flex-column justify-content-between">
            <div class="d-flex align-items-center justify-content-between mb-3 pb-2" style="border-bottom: 1px solid var(--border-color);">
                <div>
                    <h6 class="fw-bold mb-0 text-uppercase" style="color: var(--text-main); font-size: 0.78rem; letter-spacing: 0.06em;">
                        ORDERS BREAKDOWN
                    </h6>
                    <small class="text-muted" style="font-size: 0.72rem;">Distribution by order status</small>
                </div>
            </div>

            <div style="height: 220px; position: relative;" class="my-auto">
                <canvas id="donutIncomeChart"></canvas>
                <div class="dash-donut-center">
                    <div style="font-size: 1.45rem; font-weight: 800; color: var(--text-main); line-height: 1;">{{ $totalOrders }}</div>
                    <small class="text-muted" style="font-size: 0.68rem; text-transform: uppercase; font-weight: 700; letter-spacing: 0.05em;">Orders</small>
                </div>
            </div>

            <div class="d-flex flex-wrap justify-content-center align-items-center gap-2 pt-3" style="border-top: 1px solid var(--border-color);">
                <span class="badge rounded-pill px-2 py-1" style="background: rgba(245, 158, 11, 0.12); color: #f59e0b; font-size: 0.72rem;">
                    Pending: {{ $pendingOrders }}
                </span>
                <span class="badge rounded-pill px-2 py-1" style="background: rgba(6, 182, 212, 0.12); color: #06b6d4; font-size: 0.72rem;">
                    Confirmed: {{ $processingOrders }}
                </span>
                <span class="badge rounded-pill px-2 py-1" style="background: rgba(16, 185, 129, 0.12); color: #10b981; font-size: 0.72rem;">
                    Delivered: {{ $deliveredOrders }}
                </span>
                <span class="badge rounded-pill px-2 py-1" style="background: rgba(239, 68, 68, 0.12); color: #ef4444; font-size: 0.72rem;">
                    Cancelled: {{ $cancelledOrders }}
                </span>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-lg-8">
        <div class="card p-3 p-md-4 table-card h-100">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div class="d-flex align-items-center gap-2">
                    <span class="d-inline-flex align-items-center justify-content-center rounded-2" style="width: 32px; height: 32px; background: rgba(var(--accent-rgb), 0.12); color: var(--accent);">
                        <i class="fa-solid fa-receipt"></i>
                    </span>
                    <div>
                        <h6 class="fw-bold mb-0" style="color: var(--text-main); font-size: 0.9rem;">Recent Orders</h6>
                        <small class="text-muted" style="font-size: 0.72rem;">Latest transactions processed</small>
                    </div>
                </div>
                <a href="{{ route('admin.orders.index') }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3" style="font-size: 0.78rem;">
                    All Orders &rarr;
                </a>
            </div>

            <div class="table-responsive">
                <table class="table dash-table align-middle mb-0" style="width: 100%;">
                    <thead>
                        <tr>
                            <th>Invoice</th>
                            <th>Customer</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th width="100" class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentOrders as $ord)
                            <tr>
                                <td>
                                    <a href="{{ route('admin.orders.show', $ord->id) }}" class="fw-bold text-decoration-none" style="color: var(--accent);">
                                        #{{ $ord->order_number }}
                                    </a>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="dash-customer-avatar">
                                            {{ strtoupper(substr($ord->customer_name ?? 'C', 0, 2)) }}
                                        </div>
                                        <div>
                                            <div class="fw-semibold" style="color: var(--text-main); line-height: 1.2;">{{ $ord->customer_name }}</div>
                                            <small class="text-muted" style="font-size: 0.72rem;">{{ $ord->district ?? 'Bangladesh' }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <strong class="fw-bold" style="color: var(--text-main);">৳{{ number_format($ord->total, 0) }}</strong>
                                </td>
                                <td>
                                    @php
                                        $statusBadgeMap = match($ord->order_status) {
                                            'delivered' => ['bg' => 'rgba(16, 185, 129, 0.12)', 'color' => '#10b981'],
                                            'pending' => ['bg' => 'rgba(245, 158, 11, 0.12)', 'color' => '#f59e0b'],
                                            'processing', 'confirmed' => ['bg' => 'rgba(6, 182, 212, 0.12)', 'color' => '#06b6d4'],
                                            'shipped' => ['bg' => 'rgba(99, 102, 241, 0.12)', 'color' => '#818cf8'],
                                            'cancelled' => ['bg' => 'rgba(239, 68, 68, 0.12)', 'color' => '#ef4444'],
                                            default => ['bg' => 'rgba(148, 163, 184, 0.12)', 'color' => '#94a3b8']
                                        };
                                    @endphp
                                    <span class="badge rounded-pill px-2.5 py-1" style="background: {{ $statusBadgeMap['bg'] }}; color: {{ $statusBadgeMap['color'] }}; font-weight: 700; font-size: 0.72rem;">
                                        {{ ucfirst($ord->order_status) }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <div class="d-inline-flex align-items-center gap-1">
                                        <a href="{{ route('admin.orders.show', $ord->id) }}" class="btn-action" title="View Order">
                                            <i class="fa-regular fa-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.orders.print', $ord->id) }}" target="_blank" class="btn-action" title="Print Invoice">
                                            <i class="fa-solid fa-print"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">
                                    No customer orders found in record.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card p-3 p-md-4 h-100">
            <div class="d-flex justify-content-between align-items-center mb-3 pb-2" style="border-bottom: 1px solid var(--border-color);">
                <div class="d-flex align-items-center gap-2">
                    <span class="d-inline-flex align-items-center justify-content-center rounded-2" style="width: 32px; height: 32px; background: rgba(239, 68, 68, 0.12); color: #ef4444;">
                        <i class="fa-solid fa-boxes-packing"></i>
                    </span>
                    <div>
                        <h6 class="fw-bold mb-0" style="color: var(--text-main); font-size: 0.9rem;">Low Stock Watchlist</h6>
                        <small class="text-muted" style="font-size: 0.72rem;">Items needing reordering</small>
                    </div>
                </div>
                <a href="{{ route('admin.products.index') }}" class="btn btn-sm btn-outline-secondary rounded-pill px-2.5" style="font-size: 0.75rem;">
                    Catalog &rarr;
                </a>
            </div>

            <div class="d-flex flex-column gap-2.5">
                @forelse($lowStockList as $prod)
                    <div class="dash-stock-item d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center gap-2.5 overflow-hidden">
                            <img src="{{ ($prod->main_image && !str_contains($prod->main_image, 'example.com')) ? $prod->main_image : asset('images/product-placeholder.svg') }}" class="dash-product-thumb" alt="" onerror="this.onerror=null;this.src='{{ asset('images/product-placeholder.svg') }}';">
                            <div class="overflow-hidden">
                                <span class="fw-semibold small d-block text-truncate" style="color: var(--text-main); max-width: 140px;">{{ $prod->title }}</span>
                                <span class="text-muted" style="font-size: 0.72rem;">৳{{ number_format($prod->price, 0) }}</span>
                            </div>
                        </div>
                        <span class="badge rounded-pill" style="background: rgba(239, 68, 68, 0.12); color: #ef4444; border: 1px solid rgba(239, 68, 68, 0.25); font-size: 0.72rem; font-weight: 700;">
                            Stock: {{ $prod->stock_qty }}
                        </span>
                    </div>
                @empty
                    <div class="text-center py-4 text-muted small my-auto">
                        <i class="fa-solid fa-circle-check text-success fs-3 d-block mb-2"></i>
                        All inventory items are healthy and adequately stocked.
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endif
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js" data-turbo-track="reload"></script>
<script>
(() => {
    function initDashboardCharts() {
        const ctxBar = document.getElementById('barBreakdownChart');
        if (ctxBar && window.Chart) {
            const existingBar = Chart.getChart(ctxBar);
            if (existingBar) existingBar.destroy();

            const labels7 = @json($chart7Labels);
            const sales7 = @json($chart7Sales);
            const orders7 = @json($chart7Orders);
            const mockBars1 = sales7.length > 0 ? sales7 : [0];
            const mockBars2 = orders7.length > 0 ? orders7 : [0];

            const isDark = document.documentElement.getAttribute('data-bs-theme') === 'dark';
            const gridColor = isDark ? 'rgba(255, 255, 255, 0.05)' : 'rgba(0, 0, 0, 0.05)';
            const textColor = isDark ? '#94a3b8' : '#64748b';

            new Chart(ctxBar.getContext('2d'), {
                type: 'line',
                data: {
                    labels: labels7,
                    datasets: [
                        {
                            label: 'Sales Revenue (৳)',
                            data: mockBars1,
                            borderColor: '#6366f1',
                            backgroundColor: 'rgba(99, 102, 241, 0.12)',
                            fill: true,
                            tension: 0.4,
                            borderWidth: 2.5,
                            pointRadius: labels7.length > 15 ? 2 : 4,
                            pointBackgroundColor: '#ffffff',
                            pointBorderColor: '#6366f1',
                            pointBorderWidth: 2,
                            pointHoverRadius: 6,
                            yAxisID: 'y'
                        },
                        {
                            label: 'Orders Count',
                            data: mockBars2,
                            borderColor: '#06b6d4',
                            backgroundColor: 'transparent',
                            borderWidth: 2,
                            borderDash: [4, 4],
                            tension: 0.4,
                            pointRadius: labels7.length > 15 ? 2 : 3,
                            pointBackgroundColor: '#06b6d4',
                            pointHoverRadius: 5,
                            yAxisID: 'y1'
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        mode: 'index',
                        intersect: false
                    },
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: isDark ? '#0f172a' : '#ffffff',
                            titleColor: isDark ? '#f8fafc' : '#0f172a',
                            bodyColor: isDark ? '#94a3b8' : '#64748b',
                            borderColor: isDark ? '#1e293b' : '#e2e8f0',
                            borderWidth: 1,
                            padding: 10,
                            boxPadding: 4,
                            usePointStyle: true,
                            callbacks: {
                                label: function(context) {
                                    if (context.datasetIndex === 0) {
                                        return ' Revenue: ৳' + Number(context.raw).toLocaleString();
                                    }
                                    return ' Orders: ' + context.raw;
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            grid: { display: false },
                            ticks: { 
                                color: textColor, 
                                font: { size: 11 },
                                maxTicksLimit: 14
                            }
                        },
                        y: {
                            position: 'left',
                            grid: { color: gridColor },
                            ticks: {
                                color: textColor,
                                font: { size: 11 },
                                callback: function(v) {
                                    return '৳' + (v >= 1000 ? (v/1000) + 'k' : v);
                                }
                            }
                        },
                        y1: {
                            position: 'right',
                            grid: { display: false },
                            ticks: {
                                color: '#06b6d4',
                                font: { size: 11 },
                                stepSize: 1
                            }
                        }
                    }
                }
            });
        }

        const ctxDonut = document.getElementById('donutIncomeChart');
        if (ctxDonut && window.Chart) {
            const existingDonut = Chart.getChart(ctxDonut);
            if (existingDonut) existingDonut.destroy();

            const delivered = {{ $deliveredOrders > 0 ? $deliveredOrders : 0 }};
            const pending = {{ $pendingOrders > 0 ? $pendingOrders : 0 }};
            const confirmed = {{ $processingOrders > 0 ? $processingOrders : 0 }};
            const cancelled = {{ $cancelledOrders > 0 ? $cancelledOrders : 0 }};
            const total = delivered + pending + confirmed + cancelled;

            new Chart(ctxDonut.getContext('2d'), {
                type: 'doughnut',
                data: {
                    labels: ['Pending', 'Confirmed', 'Delivered', 'Cancelled'],
                    datasets: [{
                        data: total > 0 ? [pending, confirmed, delivered, cancelled] : [0, 0, 0, 1],
                        backgroundColor: total > 0 ? ['#f59e0b', '#06b6d4', '#10b981', '#ef4444'] : ['#334155', '#334155', '#334155', '#334155'],
                        borderWidth: 0,
                        hoverOffset: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '76%',
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return ' ' + context.label + ': ' + context.raw + ' orders';
                                }
                            }
                        }
                    }
                }
            });
        }
    }

    document.addEventListener('turbo:load', initDashboardCharts);

    document.addEventListener('turbo:before-cache', () => {
        const ctxBar = document.getElementById('barBreakdownChart');
        if (ctxBar && window.Chart) {
            const existingBar = Chart.getChart(ctxBar);
            if (existingBar) existingBar.destroy();
        }
        const ctxDonut = document.getElementById('donutIncomeChart');
        if (ctxDonut && window.Chart) {
            const existingDonut = Chart.getChart(ctxDonut);
            if (existingDonut) existingDonut.destroy();
        }
    });
})();
</script>
@endpush