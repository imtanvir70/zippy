@extends('backend.layouts.app')

@section('title', 'Abandoned Carts Recovery Hub')

@section('content')
<!-- 1. Header Card -->
<div class="card p-3 p-md-4 mb-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <h3 class="fw-bold mb-0">Abandoned Carts Recovery Hub</h3>
            <small class="text-muted">Identify abandoned checkouts, engage customers via WhatsApp/SMS, and track recoveries</small>
        </div>
        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="abandonedTable.ajax.reload(null, false)">
            <i class="fa-solid fa-arrows-rotate me-1"></i> Refresh
        </button>
    </div>
</div>

<!-- 2. KPI Stats Cards -->
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card stat-card text-center">
            <small class="text-muted d-block mb-1">Unrecovered Carts</small>
            <h3 class="fw-bold mb-0 text-danger">{{ $stats['total_abandoned'] }}</h3>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card stat-card text-center">
            <small class="text-muted d-block mb-1">Lost Potential Revenue</small>
            <h3 class="fw-bold mb-0 text-warning">৳ {{ number_format($stats['total_value'], 0) }}</h3>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card stat-card text-center">
            <small class="text-muted d-block mb-1">Recovered Orders</small>
            <h3 class="fw-bold mb-0 text-success">{{ $stats['recovered_count'] }}</h3>
        </div>
    </div>
</div>

<div class="filter-card">
    <form id="abandonedFilterForm" class="row g-2 align-items-center">
        <div class="col-md-6">
            <div class="input-group">
                <span class="input-group-text"><i class="fa-solid fa-cart-arrow-down"></i></span>
                <select name="recovered" id="filterRecovered" class="form-select filter-control">
                    <option value="all">All Recovery Statuses</option>
                    <option value="0">Pending Recovery (Abandoned)</option>
                    <option value="1">Recovered</option>
                </select>
            </div>
        </div>
        <div class="col-md-6">
            <button type="button" class="btn btn-outline-secondary filter-btn w-100" onclick="document.getElementById('filterRecovered').value='all'; if(window.abandonedTable) window.abandonedTable.draw();">
                <i class="fa-solid fa-rotate-left me-1"></i> Reset Filters
            </button>
        </div>
    </form>
</div>

<div class="card p-3 p-md-4 table-card mb-4">
    <div class="table-responsive">
        <table class="table table-striped table-hover table-bordered align-middle" id="abandonedTable" style="width: 100%;">
            <thead>
                <tr>
                    <th style="width: 28%;">Lead / Customer</th>
                    <th style="width: 24%;">Cart Items Preview</th>
                    <th style="width: 14%;">Cart Value</th>
                    <th style="width: 14%;">Last Active</th>
                    <th style="width: 10%;">Status</th>
                    <th style="width: 10%; text-align: center;">Actions</th>
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
    let abandonedTable = null;

    function initAbandonedTable() {
        const tableEl = document.getElementById('abandonedTable');
        if (!tableEl) return;

        if (window.VanillaDataTable && VanillaDataTable.isDataTable('#abandonedTable')) {
            VanillaDataTable.getInstance('#abandonedTable').destroy();
        }

        abandonedTable = new VanillaDataTable('#abandonedTable', {
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('admin.abandoned.index') }}",
                data: function (d) {
                    const el = document.getElementById('filterRecovered');
                    d.recovered = el ? el.value : 'all';
                }
            },
            columns: [
                { data: 'lead_info', name: 'abandoned_carts.phone' },
                { data: 'cart_preview', orderable: false, searchable: false },
                { data: 'total_amount', name: 'abandoned_carts.total_amount' },
                { data: 'last_activity_at', name: 'abandoned_carts.last_activity_at' },
                { data: 'is_recovered', name: 'abandoned_carts.is_recovered' },
                { data: 'actions', orderable: false, searchable: false, className: 'text-center' }
            ],
            lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
            pageLength: 10,
            order: [[3, 'desc']],
            language: {
                search: "_INPUT_",
                searchPlaceholder: "Search abandoned leads (Phone, Email)...",
                lengthMenu: "Show _MENU_ entries",
                processing: '<div class="spinner-border spinner-border-sm text-primary" role="status"></div> Loading abandoned carts...'
            }
        });
        window.abandonedTable = abandonedTable;

        const filterRec = document.getElementById('filterRecovered');
        if (filterRec) {
            filterRec.onchange = function () {
                if (abandonedTable) abandonedTable.draw();
            };
        }
    }

    document.addEventListener('turbo:load', initAbandonedTable);

    function markCartRecovered(id) {
        Swal.fire({
            title: 'Mark as Recovered?',
            text: 'This will update the cart recovery state.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes, mark recovered'
        }).then((res) => {
            if (res.isConfirmed) {
                axios.post(`/admin/abandoned-carts/${id}/recover`)
                .then(res => {
                    const data = res.data;
                    if (data.success) {
                        Swal.fire('Updated!', data.message, 'success');
                        if (abandonedTable) abandonedTable.ajax.reload(null, false);
                    }
                });
            }
        });
    }

    window.markCartRecovered = markCartRecovered;
})();
</script>
@endpush