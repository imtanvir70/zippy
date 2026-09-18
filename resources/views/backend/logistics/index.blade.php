@extends('backend.layouts.app')

@section('title', 'Logistics & Multi-Courier BD Hub')

@section('content')
<!-- 1. Header Card -->
<div class="card p-3 p-md-4 mb-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <h3 class="fw-bold mb-0">Multi-Courier Logistics Ecosystem</h3>
            <small class="text-muted">Steadfast, Pathao, RedX API dispatch, one-click booking and live tracking status sync</small>
        </div>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="logisticsTable.ajax.reload(null, false)">
                <i class="fa-solid fa-arrows-rotate me-1"></i> Refresh
            </button>
            <a href="{{ route('admin.orders.index') }}" class="btn btn-primary btn-sm">
                <i class="fa-solid fa-boxes-stacked me-1"></i> View Orders
            </a>
        </div>
    </div>
</div>

<!-- 2. KPI Stats Cards -->
<div class="row g-3 mb-4">
    <div class="col-md-3 col-6">
        <div class="card stat-card text-center">
            <small class="text-muted d-block mb-1">Total Consignments</small>
            <h3 class="fw-bold mb-0 text-primary">{{ $stats['total'] }}</h3>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="card stat-card text-center">
            <small class="text-muted d-block mb-1">Steadfast Courier</small>
            <h3 class="fw-bold mb-0 text-info">{{ $stats['steadfast'] }}</h3>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="card stat-card text-center">
            <small class="text-muted d-block mb-1">Pathao Courier</small>
            <h3 class="fw-bold mb-0 text-danger">{{ $stats['pathao'] }}</h3>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="card stat-card text-center">
            <small class="text-muted d-block mb-1">RedX Logistics</small>
            <h3 class="fw-bold mb-0 text-warning">{{ $stats['redx'] }}</h3>
        </div>
    </div>
</div>

<div class="filter-card">
    <form id="logisticsFilterForm" class="row g-2 align-items-center">
        <div class="col-md-6">
            <div class="input-group">
                <span class="input-group-text"><i class="fa-solid fa-truck"></i></span>
                <select name="provider" id="filterCourierProvider" class="form-select filter-control">
                    <option value="all">All Courier Providers</option>
                    <option value="steadfast">Steadfast Courier</option>
                    <option value="pathao">Pathao Courier</option>
                    <option value="redx">RedX Logistics</option>
                </select>
            </div>
        </div>
        <div class="col-md-6">
            <button type="button" class="btn btn-outline-secondary filter-btn w-100" onclick="document.getElementById('filterCourierProvider').value='all'; if(window.logisticsTable) window.logisticsTable.draw();">
                <i class="fa-solid fa-rotate-left me-1"></i> Reset Filters
            </button>
        </div>
    </form>
</div>

<div class="card p-3 p-md-4 table-card mb-4">
    <div class="table-responsive">
        <table class="table table-striped table-hover table-bordered align-middle" id="logisticsTable" style="width: 100%;">
            <thead>
                <tr>
                    <th style="width: 18%;">Tracking Code</th>
                    <th style="width: 16%;">Order #</th>
                    <th style="width: 26%;">Recipient</th>
                    <th style="width: 12%;">Courier</th>
                    <th style="width: 14%;">Status</th>
                    <th style="width: 14%;">Dispatched At</th>
                </tr>
            </thead>
            <tbody>
                <!-- Populated via Yajra DataTables Server-Side AJAX -->
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script>
(() => {
    let logisticsTable = null;

    function initLogisticsTable() {
        const tableEl = document.getElementById('logisticsTable');
        if (!tableEl) return;

        if (window.VanillaDataTable && VanillaDataTable.isDataTable('#logisticsTable')) {
            VanillaDataTable.getInstance('#logisticsTable').destroy();
        }

        logisticsTable = new VanillaDataTable('#logisticsTable', {
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('admin.logistics.index') }}",
                data: function (d) {
                    const p = document.getElementById('filterCourierProvider');
                    d.provider = p ? p.value : 'all';
                }
            },
            columns: [
                { data: 'tracking_code', name: 'courier_consignments.tracking_code' },
                { data: 'order_info', name: 'orders.order_number' },
                { data: 'recipient', name: 'orders.customer_name' },
                { data: 'provider', name: 'courier_consignments.provider' },
                { data: 'status', name: 'courier_consignments.status' },
                { data: 'created_at', name: 'courier_consignments.created_at' }
            ],
            lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
            pageLength: 10,
            order: [[5, 'desc']],
            language: {
                search: "_INPUT_",
                searchPlaceholder: "Search tracking code, invoice, recipient...",
                lengthMenu: "Show _MENU_ entries",
                processing: '<div class="spinner-border spinner-border-sm text-primary" role="status"></div> Loading consignments...'
            }
        });
        window.logisticsTable = logisticsTable;

        const fProv = document.getElementById('filterCourierProvider');
        if (fProv) {
            fProv.onchange = () => {
                if (logisticsTable) logisticsTable.draw();
            };
        }
    }

    document.addEventListener('turbo:load', initLogisticsTable);
})();
</script>
@endpush