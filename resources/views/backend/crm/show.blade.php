@extends('backend.layouts.app')

@section('title', 'Customer 360 Profile')

@section('content')
<!-- 1. Header Card -->
<div class="card p-3 p-md-4 mb-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <h3 class="fw-bold mb-0">Customer 360 Profile</h3>
            <small class="text-muted">{{ $profile['name'] }} &bull; {{ $profile['phone'] }}</small>
        </div>
        <a href="{{ route('admin.customers.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fa-solid fa-arrow-left me-1"></i> Back to Customers
        </a>
    </div>
</div>

<!-- 2. Stats Row -->
<div class="row g-3 mb-4">
    <div class="col-md-3 col-6">
        <div class="card stat-card text-center">
            <small class="text-muted d-block mb-1">Lifetime Spend</small>
            <h3 class="fw-bold mb-0 text-success">৳ {{ number_format($profile['total_spend'], 0) }}</h3>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="card stat-card text-center">
            <small class="text-muted d-block mb-1">Total Orders</small>
            <h3 class="fw-bold mb-0 text-primary">{{ $profile['total_orders'] }}</h3>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="card stat-card text-center">
            <small class="text-muted d-block mb-1">Wallet Balance</small>
            <h3 class="fw-bold mb-0 text-warning">৳ {{ number_format($wallet->balance ?? 0, 0) }}</h3>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="card stat-card text-center">
            <small class="text-muted d-block mb-1">Reward Points</small>
            <h3 class="fw-bold mb-0 text-info">{{ $wallet->reward_points ?? 0 }} pts</h3>
        </div>
    </div>
</div>

<!-- 3. Form & Order History Row -->
<div class="row g-4 mb-4">
    <!-- Left: Wallet Adjustment -->
    <div class="col-lg-4">
        <div class="card p-3 p-md-4 h-100">
            <h6 class="fw-bold mb-3 pb-2 border-bottom">
                <i class="fa-solid fa-wallet text-warning me-2"></i> Adjust Wallet / Points
            </h6>
            @canPerm('admin.crm.wallet')
                <form action="{{ route('admin.crm.wallet', $profile['phone']) }}" method="POST" class="d-flex flex-column gap-3">
                    @csrf
                    <div>
                        <label class="form-label">Adjustment Type</label>
                        <select name="type" class="form-select" required>
                            <option value="credit">Credit (Add Balance)</option>
                            <option value="debit">Debit (Deduct Balance)</option>
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Amount (BDT) <span class="text-danger">*</span></label>
                        <input type="number" name="amount" step="0.01" class="form-control" placeholder="0.00" required>
                    </div>
                    <div>
                        <label class="form-label">Reward Points (Optional)</label>
                        <input type="number" name="points" class="form-control" placeholder="0">
                    </div>
                    <div>
                        <label class="form-label">Reason / Description <span class="text-danger">*</span></label>
                        <input type="text" name="description" class="form-control" placeholder="e.g. Loyalty reward" required>
                    </div>
                    <button type="submit" class="btn btn-primary py-2.5 fw-bold mt-2">
                        <i class="fa-solid fa-check me-1"></i> Submit Adjustment
                    </button>
                </form>
            @else
                <div class="text-center py-4 text-muted">
                    <i class="fa-solid fa-lock fs-2 d-block mb-2 text-secondary"></i>
                    <small>Wallet adjustments restricted for your role.</small>
                </div>
            @endcanPerm
        </div>
    </div>

    <!-- Right: Order History -->
    <div class="col-lg-8">
        <div class="card p-3 p-md-4 table-card h-100">
            <h6 class="fw-bold mb-3">
                <i class="fa-solid fa-clock-rotate-left text-primary me-2"></i> Order History
            </h6>
            <div class="table-responsive">
                <table class="table table-striped table-hover table-bordered align-middle" style="width: 100%;">
                    <thead>
                        <tr>
                            <th>Order #</th>
                            <th>Date</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Courier</th>
                            <th width="80" style="text-align: center;">View</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($customerOrders as $ord)
                            <tr>
                                <td><a href="{{ route('admin.orders.show', $ord->id) }}" class="fw-bold text-decoration-none text-primary">#{{ $ord->order_number }}</a></td>
                                <td><small class="text-muted">{{ $ord->created_at }}</small></td>
                                <td><strong class="text-danger">৳ {{ number_format($ord->total, 0) }}</strong></td>
                                <td>
                                    <span class="badge bg-{{ $ord->order_status === 'delivered' ? 'success' : ($ord->order_status === 'pending' ? 'warning' : 'primary') }}-subtle text-{{ $ord->order_status === 'delivered' ? 'success' : ($ord->order_status === 'pending' ? 'dark' : 'primary') }} rounded-pill px-2.5 py-1">
                                        {{ ucfirst($ord->order_status) }}
                                    </span>
                                </td>
                                <td><span class="badge bg-secondary font-monospace">{{ $ord->courier_tracking_code ?? 'Not dispatched' }}</span></td>
                                <td class="text-center">
                                    <a href="{{ route('admin.orders.show', $ord->id) }}" class="btn btn-sm btn-light border" title="View Order">
                                        <i class="fa-regular fa-eye text-primary"></i>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection