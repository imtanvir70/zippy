@extends('backend.layouts.app')

@section('title', 'Returns & RMA Refunds')

@section('content')
<!-- 1. Header Card -->
<div class="card p-3 p-md-4 mb-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <h3 class="fw-bold mb-0">Returns & RMA Refunds</h3>
            <small class="text-muted">Manage return merchandise authorizations, full/partial refunds, and inventory restorations</small>
        </div>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="refundsTable.ajax.reload(null, false)">
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
            <small class="text-muted d-block mb-1">Total Refunded</small>
            <h3 class="fw-bold mb-0 text-danger">৳ {{ number_format($stats['total_refunded'], 0) }}</h3>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="card stat-card text-center">
            <small class="text-muted d-block mb-1">Completed Refunds</small>
            <h3 class="fw-bold mb-0 text-success">{{ $stats['completed_count'] }}</h3>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="card stat-card text-center">
            <small class="text-muted d-block mb-1">Approved Requests</small>
            <h3 class="fw-bold mb-0 text-info">{{ $stats['approved_count'] }}</h3>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="card stat-card text-center">
            <small class="text-muted d-block mb-1">Pending Action</small>
            <h3 class="fw-bold mb-0 text-warning">{{ $stats['pending_count'] }}</h3>
        </div>
    </div>
</div>

<div class="filter-card">
    <form id="refundsFilterForm" class="row g-2 align-items-center">
        <div class="col-md-6">
            <div class="input-group">
                <span class="input-group-text"><i class="fa-solid fa-rotate-left"></i></span>
                <select name="status" id="filterRefundStatus" class="form-select filter-control">
                    <option value="all">All Refund Statuses</option>
                    <option value="pending">Pending Approval</option>
                    <option value="approved">Approved</option>
                    <option value="completed">Completed</option>
                    <option value="rejected">Rejected</option>
                </select>
            </div>
        </div>
        <div class="col-md-6">
            <button type="button" class="btn btn-outline-secondary filter-btn w-100" onclick="$('#filterRefundStatus').val('all'); refundsTable.draw();">
                <i class="fa-solid fa-rotate-left me-1"></i> Reset Filters
            </button>
        </div>
    </form>
</div>

<div class="card p-3 p-md-4 table-card mb-4">
    <div class="table-responsive">
        <table class="table table-striped table-hover table-bordered align-middle" id="refundsTable" style="width: 100%;">
            <thead>
                <tr>
                    <th style="width: 16%;">Refund #</th>
                    <th style="width: 14%;">Order #</th>
                    <th style="width: 22%;">Customer</th>
                    <th style="width: 14%;">Amount</th>
                    <th style="width: 18%;">Reason</th>
                    <th style="width: 10%;">Status</th>
                    <th style="width: 6%; text-align: center;">Actions</th>
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
    let refundsTable = null;

    function initRefundsTable() {
        const tableEl = document.getElementById('refundsTable');
        if (!tableEl) return;

        if ($.fn.DataTable.isDataTable('#refundsTable')) {
            $('#refundsTable').DataTable().clear().destroy();
        }

        refundsTable = $('#refundsTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('admin.refunds.index') }}",
                data: function (d) {
                    d.status = $('#filterRefundStatus').val();
                }
            },
            columns: [
                { data: 'refund_number', name: 'refunds.refund_number' },
                { data: 'order_info', name: 'orders.order_number' },
                { data: 'customer', name: 'orders.customer_name' },
                { data: 'amount', name: 'refunds.amount' },
                { data: 'reason', name: 'refunds.reason' },
                { data: 'status', name: 'refunds.status' },
                { data: 'actions', orderable: false, searchable: false, className: 'text-center' }
            ],
            lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
            pageLength: 10,
            order: [[0, 'desc']],
            language: {
                search: "_INPUT_",
                searchPlaceholder: "Search refunds (Number, Order #, Customer)...",
                lengthMenu: "Show _MENU_ entries",
                processing: '<div class="spinner-border spinner-border-sm text-primary" role="status"></div> Loading refunds...'
            }
        });

        $('#filterRefundStatus').off('change').on('change', function () {
            if (refundsTable) refundsTable.draw();
        });
    }

    document.addEventListener('turbo:load', initRefundsTable);

    function updateRefundStatus(id, newStatus) {
        Swal.fire({
            title: `Mark as ${newStatus}?`,
            text: `Update this RMA request status to ${newStatus}.`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes, update'
        }).then(result => {
            if (result.isConfirmed) {
                axios.post(`/admin/refunds/${id}/status`, { status: newStatus }, {
                    headers: { 'Accept': 'application/json' }
                })
                .then(res => {
                    const data = res.data;
                    if (data.success) {
                        Swal.fire('Updated!', data.message, 'success');
                        if (refundsTable) refundsTable.ajax.reload(null, false);
                    } else {
                        Swal.fire('Error', data.message || 'Action failed', 'error');
                    }
                });
            }
        });
    }

    window.updateRefundStatus = updateRefundStatus;
})();
</script>
@endpush