@extends('backend.layouts.app')

@section('title', 'Customers Directory')

@section('content')
<!-- 1. Header Card -->
<div class="card p-3 p-md-4 mb-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <h3 class="fw-bold mb-0">Customers Directory</h3>
            <small class="text-muted">Customer profiles, lifetime purchase volume, order histories, and contact access</small>
        </div>
        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="customersTable.ajax.reload(null, false)">
            <i class="fa-solid fa-arrows-rotate me-1"></i> Refresh
        </button>
    </div>
</div>

<!-- 2. Customers Table Card -->
<div class="card p-3 p-md-4 table-card mb-4">
    <div class="table-responsive">
        <table class="table table-striped table-hover table-bordered align-middle" id="customersTable" style="width: 100%;">
            <thead>
                <tr>
                    <th style="width: 26%;">Customer Profile</th>
                    <th style="width: 22%;">Location / District</th>
                    <th style="width: 14%; text-align: center;">Total Orders</th>
                    <th style="width: 14%;">Total Spent</th>
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
    let customersTable = null;

    function initCustomersTable() {
        const tableEl = document.getElementById('customersTable');
        if (!tableEl) return;

        if (window.VanillaDataTable && VanillaDataTable.isDataTable('#customersTable')) {
            VanillaDataTable.getInstance('#customersTable').destroy();
        }

        customersTable = new VanillaDataTable('#customersTable', {
            processing: true,
            serverSide: true,
            ajax: "{{ route('admin.customers.index') }}",
            columns: [
                { data: 'customer_profile', name: 'orders.customer_name' },
                { data: 'location', name: 'orders.district' },
                { data: 'orders_badge', name: 'orders_count', searchable: false, className: 'text-center' },
                { data: 'spend_formatted', name: 'total_spend', searchable: false },
                { data: 'last_active', name: 'last_order_date', searchable: false },
                { data: 'actions', orderable: false, searchable: false, className: 'text-center' }
            ],
            lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
            pageLength: 10,
            order: [[3, 'desc']],
            language: {
                search: "_INPUT_",
                searchPlaceholder: "Search customers (Name, Phone, District)...",
                lengthMenu: "Show _MENU_ entries",
                processing: '<div class="spinner-border spinner-border-sm text-primary" role="status"></div> Loading customers...'
            }
        });
    }

    document.addEventListener('turbo:load', initCustomersTable);
})();
</script>
@endpush
