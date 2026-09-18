@extends('backend.layouts.app')

@section('title', 'Customer Support Desk')

@section('content')
<!-- 1. Header Card -->
<div class="card p-3 p-md-4 mb-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <h3 class="fw-bold mb-0">Customer Support Desk</h3>
            <small class="text-muted">Customer inquiries, order support tickets, and direct replies</small>
        </div>
        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="supportTable.ajax.reload(null, false)">
            <i class="fa-solid fa-arrows-rotate me-1"></i> Refresh
        </button>
    </div>
</div>

<!-- 2. KPI Stats Cards -->
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card stat-card text-center">
            <small class="text-muted d-block mb-1">Open Inquiries</small>
            <h3 class="fw-bold mb-0 text-danger">{{ $stats['open_count'] }}</h3>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card stat-card text-center">
            <small class="text-muted d-block mb-1">In Progress</small>
            <h3 class="fw-bold mb-0 text-warning">{{ $stats['in_progress_count'] }}</h3>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card stat-card text-center">
            <small class="text-muted d-block mb-1">Resolved Tickets</small>
            <h3 class="fw-bold mb-0 text-success">{{ $stats['resolved_count'] }}</h3>
        </div>
    </div>
</div>

<div class="filter-card">
    <form id="supportFilterForm" class="row g-2 align-items-center">
        <div class="col-md-6">
            <div class="input-group">
                <span class="input-group-text"><i class="fa-solid fa-headset"></i></span>
                <select name="status" id="filterSupportStatus" class="form-select filter-control">
                    <option value="all">All Ticket Statuses</option>
                    <option value="open">Open</option>
                    <option value="in_progress">In Progress</option>
                    <option value="resolved">Resolved</option>
                    <option value="closed">Closed</option>
                </select>
            </div>
        </div>
        <div class="col-md-6">
            <button type="button" class="btn btn-outline-secondary filter-btn w-100" onclick="$('#filterSupportStatus').val('all'); supportTable.draw();">
                <i class="fa-solid fa-rotate-left me-1"></i> Reset Filters
            </button>
        </div>
    </form>
</div>

<div class="card p-3 p-md-4 table-card mb-4">
    <div class="table-responsive">
        <table class="table table-striped table-hover table-bordered align-middle" id="supportTable" style="width: 100%;">
            <thead>
                <tr>
                    <th style="width: 16%;">Ticket #</th>
                    <th style="width: 24%;">Customer</th>
                    <th style="width: 26%;">Subject</th>
                    <th style="width: 12%;">Priority</th>
                    <th style="width: 12%;">Status</th>
                    <th style="width: 10%; text-align: center;">Action</th>
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
    let supportTable = null;

    function initSupportTable() {
        const tableEl = document.getElementById('supportTable');
        if (!tableEl) return;

        if ($.fn.DataTable.isDataTable('#supportTable')) {
            $('#supportTable').DataTable().clear().destroy();
        }

        supportTable = $('#supportTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('admin.support.index') }}",
                data: function (d) {
                    d.status = $('#filterSupportStatus').val();
                }
            },
            columns: [
                { data: 'ticket_number', name: 'support_tickets.ticket_number' },
                { data: 'customer', name: 'support_tickets.customer_name' },
                { data: 'subject', name: 'support_tickets.subject' },
                { data: 'priority', name: 'support_tickets.priority' },
                { data: 'status', name: 'support_tickets.status' },
                { data: 'actions', orderable: false, searchable: false, className: 'text-center' }
            ],
            lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
            pageLength: 10,
            order: [[0, 'desc']],
            language: {
                search: "_INPUT_",
                searchPlaceholder: "Search tickets (Number, Customer, Subject)...",
                lengthMenu: "Show _MENU_ entries",
                processing: '<div class="spinner-border spinner-border-sm text-primary" role="status"></div> Loading tickets...'
            }
        });

        $('#filterSupportStatus').off('change').on('change', function () {
            if (supportTable) supportTable.draw();
        });
    }

    document.addEventListener('turbo:load', initSupportTable);
})();
</script>
@endpush