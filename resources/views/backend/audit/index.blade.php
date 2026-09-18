@extends('backend.layouts.app')

@section('title', 'System Audit Trail & Activity Logs')

@section('content')
<!-- 1. Header Card -->
<div class="card p-3 p-md-4 mb-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <h3 class="fw-bold mb-0">System Audit Trail & Activity Logs</h3>
            <small class="text-muted">Comprehensive immutable audit trail tracking all administrative actions with IP and timestamps</small>
        </div>
        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="auditTable.ajax.reload(null, false)">
            <i class="fa-solid fa-arrows-rotate me-1"></i> Refresh
        </button>
    </div>
</div>

<div class="filter-card">
    <form id="auditFilterForm" class="row g-2 align-items-center">
        <div class="col-md-5">
            <div class="input-group">
                <span class="input-group-text"><i class="fa-solid fa-cubes"></i></span>
                <select name="module" id="filterAuditModule" class="form-select filter-control">
                    <option value="all">All Modules</option>
                    @foreach($modules as $m)
                        <option value="{{ $m }}">{{ ucfirst($m) }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="col-md-4">
            <div class="input-group">
                <span class="input-group-text"><i class="fa-solid fa-bolt"></i></span>
                <select name="action" id="filterAuditAction" class="form-select filter-control">
                    <option value="all">All Actions</option>
                    @foreach($actions as $a)
                        <option value="{{ $a }}">{{ ucfirst($a) }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="col-md-3">
            <button type="button" class="btn btn-outline-secondary filter-btn w-100" onclick="document.getElementById('filterAuditModule').value='all'; document.getElementById('filterAuditAction').value='all'; if(window.auditTable) window.auditTable.draw();">
                <i class="fa-solid fa-rotate-left me-1"></i> Reset
            </button>
        </div>
    </form>
</div>

<div class="card p-3 p-md-4 table-card mb-4">
    <div class="table-responsive">
        <table class="table table-striped table-hover table-bordered align-middle" id="auditTable" style="width: 100%;">
            <thead>
                <tr>
                    <th style="width: 16%;">User</th>
                    <th style="width: 14%;">Module</th>
                    <th style="width: 12%;">Action</th>
                    <th style="width: 30%;">Description</th>
                    <th style="width: 14%;">Metadata (IP)</th>
                    <th style="width: 14%;">Timestamp</th>
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
    let auditTable = null;

    function initAuditTable() {
        const tableEl = document.getElementById('auditTable');
        if (!tableEl) return;

        if (window.VanillaDataTable && VanillaDataTable.isDataTable('#auditTable')) {
            VanillaDataTable.getInstance('#auditTable').destroy();
        }

        auditTable = new VanillaDataTable('#auditTable', {
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('admin.audit.index') }}",
                data: function (d) {
                    const modEl = document.getElementById('filterAuditModule');
                    const actEl = document.getElementById('filterAuditAction');
                    d.module = modEl ? modEl.value : 'all';
                    d.action = actEl ? actEl.value : 'all';
                }
            },
            columns: [
                { data: 'user_name', name: 'audit_logs.user_name' },
                { data: 'module', name: 'audit_logs.module' },
                { data: 'action', name: 'audit_logs.action' },
                { data: 'description', name: 'audit_logs.description' },
                { data: 'technical_meta', orderable: false, searchable: false },
                { data: 'created_at', name: 'audit_logs.created_at' }
            ],
            lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
            pageLength: 10,
            order: [[5, 'desc']],
            language: {
                search: "_INPUT_",
                searchPlaceholder: "Search audit logs (User, Description, IP)...",
                lengthMenu: "Show _MENU_ entries",
                processing: '<div class="spinner-border spinner-border-sm text-primary" role="status"></div> Loading audit trail...'
            }
        });
        window.auditTable = auditTable;

        const modEl = document.getElementById('filterAuditModule');
        const actEl = document.getElementById('filterAuditAction');
        if (modEl) modEl.onchange = () => { if (auditTable) auditTable.draw(); };
        if (actEl) actEl.onchange = () => { if (auditTable) auditTable.draw(); };
    }

    document.addEventListener('turbo:load', initAuditTable);
})();
</script>
@endpush