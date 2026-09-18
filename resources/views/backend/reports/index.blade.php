@extends('backend.layouts.app')

@section('title', 'Financial, Sales & Reconciliation Reports')

@section('content')
<!-- 1. Header Card -->
<div class="card p-3 p-md-4 mb-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <h3 class="fw-bold mb-0">Financial & Sales Reports</h3>
            <small class="text-muted">Gross revenue, net profit, shipping collections, and payment gateway reconciliation</small>
        </div>
        <a href="{{ route('admin.reports.export_csv', ['start_date' => $startDate, 'end_date' => $endDate]) }}" class="btn btn-success btn-sm">
            <i class="fa-solid fa-file-csv me-1"></i> Export Financial CSV
        </a>
    </div>
</div>

<!-- 2. Date Range Filter Card -->
<div class="card p-3 mb-4">
    <form action="{{ route('admin.reports.index') }}" method="GET" class="row g-2 align-items-end">
        <div class="col-md-4">
            <label class="form-label">Start Date</label>
            <input type="date" name="start_date" class="form-control" value="{{ $startDate }}">
        </div>
        <div class="col-md-4">
            <label class="form-label">End Date</label>
            <input type="date" name="end_date" class="form-control" value="{{ $endDate }}">
        </div>
        <div class="col-md-4">
            <button type="submit" class="btn btn-primary w-100">
                <i class="fa-solid fa-filter me-1"></i> Filter Date Range
            </button>
        </div>
    </form>
</div>

<!-- 3. Financial KPI Stat Cards -->
<div class="row g-3 mb-4">
    <div class="col-xl-3 col-sm-6">
        <div class="card p-3 h-100">
            <small class="text-muted fw-bold text-uppercase d-block">Gross Sales Revenue</small>
            <h3 class="fw-bold mb-1 text-success">৳ {{ number_format($summary['gross_sales'], 0) }}</h3>
            <small class="text-muted">{{ $summary['successful_orders'] }} Successful Orders</small>
        </div>
    </div>
    <div class="col-xl-3 col-sm-6">
        <div class="card p-3 h-100">
            <small class="text-muted fw-bold text-uppercase d-block">Estimated Net Profit</small>
            <h3 class="fw-bold mb-1 text-primary">৳ {{ number_format($summary['net_profit'], 0) }}</h3>
            <small class="text-muted">After COGS & Gateway fees</small>
        </div>
    </div>
    <div class="col-xl-3 col-sm-6">
        <div class="card p-3 h-100">
            <small class="text-muted fw-bold text-uppercase d-block">Average Order Value</small>
            <h3 class="fw-bold mb-1 text-warning">৳ {{ number_format($summary['aov'], 0) }}</h3>
            <small class="text-muted">Per fulfilled transaction</small>
        </div>
    </div>
    <div class="col-xl-3 col-sm-6">
        <div class="card p-3 h-100">
            <small class="text-muted fw-bold text-uppercase d-block">Refunds & Cancellations</small>
            <h3 class="fw-bold mb-1 text-danger">৳ {{ number_format($summary['refunded_amount'], 0) }}</h3>
            <small class="text-muted">{{ $summary['cancelled_orders'] }} Cancelled Orders</small>
        </div>
    </div>
</div>

<!-- 4. Tables Breakdown Row -->
<div class="row g-4 mb-4">
    <div class="col-lg-6">
        <div class="card p-3 p-md-4 table-card h-100">
            <h6 class="fw-bold mb-3">
                <i class="fa-solid fa-credit-card text-primary me-2"></i> Payment Gateway Reconciliation
            </h6>
            <div class="table-responsive">
                <table class="table table-striped table-hover table-bordered align-middle" style="width: 100%;">
                    <thead>
                        <tr>
                            <th>Payment Gateway</th>
                            <th>Total Orders</th>
                            <th>Total Amount Collected</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($summary['payment_methods'] as $pm)
                            <tr>
                                <td><span class="badge bg-dark text-uppercase">{{ $pm->payment_method }}</span></td>
                                <td><strong>{{ $pm->count }}</strong></td>
                                <td><strong class="text-success">৳ {{ number_format($pm->total_amount, 0) }}</strong></td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="text-center py-4 text-muted">No transactions in selected range.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card p-3 p-md-4 table-card h-100">
            <h6 class="fw-bold mb-3">
                <i class="fa-solid fa-map-location-dot text-info me-2"></i> Regional Sales Distribution
            </h6>
            <div class="table-responsive">
                <table class="table table-striped table-hover table-bordered align-middle" style="width: 100%;">
                    <thead>
                        <tr>
                            <th>Region / Territory</th>
                            <th>Orders</th>
                            <th>Sales Volume</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><span class="fw-semibold">Inside Dhaka</span></td>
                            <td>{{ $summary['dhaka_orders'] }}</td>
                            <td><strong class="text-dark">৳ {{ number_format($summary['dhaka_sales'], 0) }}</strong></td>
                        </tr>
                        <tr>
                            <td><span class="fw-semibold">Outside Dhaka (63 Districts)</span></td>
                            <td>{{ $summary['outside_dhaka_orders'] }}</td>
                            <td><strong class="text-dark">৳ {{ number_format($summary['outside_dhaka_sales'], 0) }}</strong></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection