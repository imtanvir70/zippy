@extends('backend.layouts.app')

@section('title', 'Customer CRM & Profiles Directory')

@section('content')
<!-- 1. Header Card -->
<div class="card p-3 p-md-4 mb-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <h3 class="fw-bold mb-0">Customers Directory</h3>
            <small class="text-muted">Customer profiles, total lifetime spending, loyalty wallets, and transaction histories</small>
        </div>
        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="crmTable.ajax.reload(null, false)">
            <i class="fa-solid fa-arrows-rotate me-1"></i> Refresh
        </button>
    </div>
</div>

<!-- 2. Customers Table Card -->
<div class="card p-3 p-md-4 table-card mb-4">
    <div class="table-responsive">
        <table class="table table-striped table-hover table-bordered align-middle" id="crmTable" style="width: 100%;">
            <thead>
                <tr>
                    <th style="width: 26%;">Customer Profile</th>
                    <th style="width: 22%;">District / Address</th>
                    <th style="width: 14%; text-align: center;">Total Orders</th>
                    <th style="width: 14%;">Lifetime Spend</th>
                    <th style="width: 14%;">Last Order</th>
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
    let crmTable = null;

    function initCrmTable() {
        const tableEl = document.getElementById('crmTable');
        if (!tableEl) return;

        if ($.fn.DataTable.isDataTable('#crmTable')) {
            $('#crmTable').DataTable().clear().destroy();
        }

        crmTable = $('#crmTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('admin.customers.index') }}",
            columns: [
                { data: 'customer_profile', name: 'orders.customer_name' },
                { data: 'location', name: 'orders.district' },
                { data: 'orders_count', name: 'orders_count', searchable: false, className: 'text-center' },
                { data: 'total_spend', name: 'total_spend', searchable: false },
                { data: 'last_order_date', name: 'last_order_date', searchable: false },
                { data: 'actions', orderable: false, searchable: false, className: 'text-center' }
            ],
            lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
            pageLength: 10,
            order: [[3, 'desc']],
            language: {
                search: "_INPUT_",
                searchPlaceholder: "Search CRM (Name, Phone, District)...",
                lengthMenu: "Show _MENU_ entries",
                processing: '<div class="spinner-border spinner-border-sm text-primary" role="status"></div> Loading CRM profiles...'
            }
        });
    }

    document.addEventListener('turbo:load', initCrmTable);
})();
</script>
@endpush