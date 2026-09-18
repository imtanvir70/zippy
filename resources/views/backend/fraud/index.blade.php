@extends('backend.layouts.app')

@section('title', 'Real-Time Fraud Risk Checker')

@section('content')
<!-- 1. Header Card -->
<div class="card p-3 p-md-4 mb-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <h3 class="fw-bold mb-0">Real-Time Fraud Risk Engine</h3>
            <small class="text-muted">Automated risk scoring based on order velocity, cancellation ratios, junk address heuristics, and IP analysis</small>
        </div>
        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="fraudTable.ajax.reload(null, false)">
            <i class="fa-solid fa-arrows-rotate me-1"></i> Refresh
        </button>
    </div>
</div>

<!-- 2. KPI Stats Cards -->
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card stat-card text-center">
            <small class="text-muted d-block mb-1">Flagged High Risk</small>
            <h3 class="fw-bold mb-0 text-danger">{{ $stats['flagged_count'] }}</h3>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card stat-card text-center">
            <small class="text-muted d-block mb-1">Suspicious / Moderate Risk</small>
            <h3 class="fw-bold mb-0 text-warning">{{ $stats['suspicious_count'] }}</h3>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card stat-card text-center">
            <small class="text-muted d-block mb-1">Verified Safe Orders</small>
            <h3 class="fw-bold mb-0 text-success">{{ $stats['safe_count'] }}</h3>
        </div>
    </div>
</div>

<div class="filter-card">
    <form id="fraudFilterForm" class="row g-2 align-items-center">
        <div class="col-md-5">
            <div class="input-group">
                <span class="input-group-text"><i class="fa-solid fa-shield-halved"></i></span>
                <select name="status" id="filterFraudStatus" class="form-select filter-control">
                    <option value="all">All Risk Levels</option>
                    <option value="flagged_fraud">High Risk (Flagged)</option>
                    <option value="suspicious">Moderate (Suspicious)</option>
                    <option value="safe">Safe / Verified</option>
                </select>
            </div>
        </div>
        <div class="col-md-4">
            <div class="input-group">
                <span class="input-group-text"><i class="fa-solid fa-gauge-high"></i></span>
                <select name="min_score" id="filterFraudMinScore" class="form-select filter-control">
                    <option value="">All Scores</option>
                    <option value="70">Score &ge; 70 (Critical)</option>
                    <option value="35">Score &ge; 35 (Warning)</option>
                </select>
            </div>
        </div>
        <div class="col-md-3">
            <button type="button" class="btn btn-outline-secondary filter-btn w-100" onclick="$('#filterFraudStatus').val('all'); $('#filterFraudMinScore').val(''); fraudTable.draw();">
                <i class="fa-solid fa-rotate-left me-1"></i> Reset
            </button>
        </div>
    </form>
</div>

<div class="card p-3 p-md-4 table-card mb-4">
    <div class="table-responsive">
        <table class="table table-striped table-hover table-bordered align-middle" id="fraudTable" style="width: 100%;">
            <thead>
                <tr>
                    <th style="width: 16%;">Order #</th>
                    <th style="width: 24%;">Customer</th>
                    <th style="width: 14%;">Total (COD)</th>
                    <th style="width: 20%;">Risk Score</th>
                    <th style="width: 14%;">Risk Level</th>
                    <th style="width: 12%; text-align: center;">Actions</th>
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
    let fraudTable = null;

    function initFraudTable() {
        const tableEl = document.getElementById('fraudTable');
        if (!tableEl) return;

        if ($.fn.DataTable.isDataTable('#fraudTable')) {
            $('#fraudTable').DataTable().clear().destroy();
        }

        fraudTable = $('#fraudTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('admin.fraud.index') }}",
                data: function (d) {
                    d.status = $('#filterFraudStatus').val();
                    d.min_score = $('#filterFraudMinScore').val();
                }
            },
            columns: [
                { data: 'order_number', name: 'orders.order_number' },
                { data: 'customer', name: 'orders.customer_name' },
                { data: 'total', name: 'orders.total' },
                { data: 'fraud_score', name: 'orders.fraud_score' },
                { data: 'fraud_status', name: 'orders.fraud_status' },
                { data: 'actions', orderable: false, searchable: false, className: 'text-center' }
            ],
            lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
            pageLength: 10,
            order: [[3, 'desc']],
            language: {
                search: "_INPUT_",
                searchPlaceholder: "Search risk checks (Order #, Name, Phone)...",
                lengthMenu: "Show _MENU_ entries",
                processing: '<div class="spinner-border spinner-border-sm text-primary" role="status"></div> Scanning orders...'
            }
        });

        $('#filterFraudStatus, #filterFraudMinScore').off('change').on('change', function () {
            if (fraudTable) fraudTable.draw();
        });
    }

    document.addEventListener('turbo:load', initFraudTable);

    function reEvaluateFraud(orderId) {
        Swal.fire({
            title: 'Recalculating Risk...',
            didOpen: () => {
                Swal.showLoading();
            }
        });

        axios.post(`/admin/fraud-checker/evaluate/${orderId}`, null, {
            headers: { 'Accept': 'application/json' }
        })
        .then(res => {
            const data = res.data;
            if (data.success && data.evaluation) {
                Swal.fire({
                    icon: 'success',
                    title: 'Risk Evaluated!',
                    text: `Score: ${data.evaluation.fraud_score}/100 (${data.evaluation.fraud_status})`,
                    timer: 2000,
                    showConfirmButton: false
                });
                if (fraudTable) fraudTable.ajax.reload(null, false);
            } else {
                Swal.fire('Error', data.message || 'Evaluation failed', 'error');
            }
        });
    }

    window.reEvaluateFraud = reEvaluateFraud;
})();
</script>
@endpush