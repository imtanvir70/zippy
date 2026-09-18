@extends('backend.layouts.app')

@section('title', 'Coupons & Marketing Engine')

@section('content')
<!-- 1. Header Card -->
<div class="card p-3 p-md-4 mb-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <h3 class="fw-bold mb-0">Coupons & Marketing Engine</h3>
            <small class="text-muted">Create coupon discount codes, minimum order rules, and usage thresholds</small>
        </div>
        @canPerm('admin.coupons.save')
            <button type="button" class="btn btn-success" onclick="openCreateCouponModal()" id="addBtn">
                <i class="fa-solid fa-plus fa-fade"></i> Add New Coupon
            </button>
        @endcanPerm
    </div>
</div>

<!-- 2. Table Card -->
<div class="card p-3 p-md-4 table-card mb-4">
    <div class="table-responsive">
        <table class="table table-striped table-hover table-bordered align-middle" id="couponsTable" style="width: 100%;">
            <thead>
                <tr>
                    <th style="width: 18%;">Coupon Code</th>
                    <th style="width: 18%;">Discount</th>
                    <th style="width: 20%;">Conditions</th>
                    <th style="width: 18%;">Validity Period</th>
                    <th style="width: 8%; text-align: center;">Usage</th>
                    <th style="width: 8%; text-align: center;">Status</th>
                    <th style="width: 10%; text-align: center;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <!-- Populated via Yajra DataTables Server-Side AJAX -->
            </tbody>
        </table>
    </div>
</div>

<!-- ========================================================================= -->
<!-- COUPON MODAL -->
<!-- ========================================================================= -->
<div class="modal fade" id="couponModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0">
            <div class="modal-header border-0 pb-0">
                <div>
                    <h5 class="fw-bold mb-0" id="couponModalTitle"><i class="fa-solid fa-ticket text-primary me-2"></i> Add Coupon</h5>
                    <small class="text-muted">Configure discount rates and rules</small>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="couponForm" onsubmit="saveCoupon(event)">
                @csrf
                <input type="hidden" name="id" id="couponId" value="">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label">Coupon Code <span class="text-danger">*</span></label>
                        <input type="text" name="code" id="couponCode" class="form-control text-uppercase font-monospace" placeholder="e.g. SUMMER50" required>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label">Discount Type</label>
                            <select name="type" id="couponType" class="form-select">
                                <option value="fixed">Fixed BDT Amount (৳)</option>
                                <option value="percent">Percentage (%)</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label">Discount Value <span class="text-danger">*</span></label>
                            <input type="number" name="value" id="couponValue" step="0.01" class="form-control" placeholder="100" required>
                        </div>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label">Min Order (BDT)</label>
                            <input type="number" name="min_order_amount" id="couponMinOrder" step="0.01" class="form-control" placeholder="1000">
                        </div>
                        <div class="col-6">
                            <label class="form-label">Usage Limit</label>
                            <input type="number" name="usage_limit" id="couponUsageLimit" class="form-control" placeholder="100">
                        </div>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label">Start Date</label>
                            <input type="date" name="start_date" id="couponStartDate" class="form-control">
                        </div>
                        <div class="col-6">
                            <label class="form-label">End Date</label>
                            <input type="date" name="end_date" id="couponEndDate" class="form-control">
                        </div>
                    </div>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="is_active" id="couponIsActive" value="1" checked>
                        <label class="form-check-label fw-semibold" for="couponIsActive">Coupon is Active</label>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary px-4" id="couponSaveBtn">
                        <i class="fa-solid fa-check me-1"></i> Save Coupon
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(() => {
    let couponsTable = null;

    function getCouponModal() {
        const modalEl = document.getElementById('couponModal');
        return modalEl ? bootstrap.Modal.getOrCreateInstance(modalEl) : null;
    }

    function initCouponsTable() {
        const tableEl = document.getElementById('couponsTable');
        if (!tableEl) return;

        if ($.fn.DataTable.isDataTable('#couponsTable')) {
            $('#couponsTable').DataTable().clear().destroy();
        }

        couponsTable = $('#couponsTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('admin.coupons.index') }}",
            columns: [
                { data: 'code', name: 'coupons.code' },
                { data: 'discount', searchable: false },
                { data: 'rules', searchable: false },
                { data: 'validity', searchable: false },
                { data: 'usage', searchable: false, className: 'text-center' },
                { data: 'is_active', name: 'coupons.is_active', className: 'text-center' },
                { data: 'actions', orderable: false, searchable: false, className: 'text-center' }
            ],
            lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
            pageLength: 10,
            order: [[0, 'asc']],
            language: {
                search: "_INPUT_",
                searchPlaceholder: "Search coupon codes...",
                lengthMenu: "Show _MENU_ entries",
                processing: '<div class="spinner-border spinner-border-sm text-primary" role="status"></div> Loading coupons...'
            }
        });
    }

    document.addEventListener('turbo:load', initCouponsTable);

    function openCreateCouponModal() {
        const form = document.getElementById('couponForm');
        if (form) form.reset();
        const idEl = document.getElementById('couponId');
        if (idEl) idEl.value = '';
        const titleEl = document.getElementById('couponModalTitle');
        if (titleEl) titleEl.innerHTML = '<i class="fa-solid fa-ticket text-primary me-2"></i> Add Coupon';
        const modal = getCouponModal();
        if (modal) modal.show();
    }

    function openEditCouponModal(cp) {
        const form = document.getElementById('couponForm');
        if (form) form.reset();
        document.getElementById('couponId').value = cp.id;
        document.getElementById('couponCode').value = cp.code;
        document.getElementById('couponType').value = cp.type;
        document.getElementById('couponValue').value = cp.value;
        document.getElementById('couponMinOrder').value = cp.min_order_amount || '';
        document.getElementById('couponUsageLimit').value = cp.usage_limit || '';
        document.getElementById('couponStartDate').value = cp.start_date ? cp.start_date.substring(0, 10) : '';
        document.getElementById('couponEndDate').value = cp.end_date ? cp.end_date.substring(0, 10) : '';
        document.getElementById('couponIsActive').checked = !!cp.is_active;
        document.getElementById('couponModalTitle').innerHTML = '<i class="fa-solid fa-ticket text-primary me-2"></i> Edit Coupon (' + cp.code + ')';
        const modal = getCouponModal();
        if (modal) modal.show();
    }

    function saveCoupon(e) {
        e.preventDefault();
        const form = document.getElementById('couponForm');
        const formData = new FormData(form);
        const btn = document.getElementById('couponSaveBtn');
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-1"></i> Saving...';
        }

        axios.post('{{ route('admin.coupons.save') }}', formData, {
            headers: { 'Accept': 'application/json' },
            skipToast: true
        })
        .then(res => {
            const data = res.data;
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = '<i class="fa-solid fa-check me-1"></i> Save Coupon';
            }
            if (data.success) {
                const modal = getCouponModal();
                if (modal) modal.hide();
                Swal.fire({
                    icon: 'success',
                    title: 'Saved!',
                    text: data.message,
                    timer: 1500,
                    showConfirmButton: false
                });
                if (couponsTable) couponsTable.ajax.reload(null, false);
            } else {
                Swal.fire('Error', data.message || 'Validation error', 'error');
            }
        })
        .catch(err => {
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = '<i class="fa-solid fa-check me-1"></i> Save Coupon';
            }
            Swal.fire('Error', 'An unexpected error occurred', 'error');
        });
    }

    function deleteCoupon(id) {
        Swal.fire({
            title: 'Delete this coupon?',
            text: 'This action cannot be undone.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                axios.post(`/admin/promotions/coupons/${id}/delete`, null, {
                    headers: { 'Accept': 'application/json' }
                })
                .then(res => {
                    const data = res.data;
                    if (data.success) {
                        Swal.fire('Deleted!', data.message, 'success');
                        if (couponsTable) couponsTable.ajax.reload(null, false);
                    } else {
                        Swal.fire('Error', data.message || 'Failed to delete', 'error');
                    }
                });
            }
        });
    }

    window.openCreateCouponModal = openCreateCouponModal;
    window.openEditCouponModal = openEditCouponModal;
    window.saveCoupon = saveCoupon;
    window.deleteCoupon = deleteCoupon;
})();
</script>
@endpush