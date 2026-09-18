@extends('backend.layouts.app')

@section('title', 'Customer Product Demands & Sourcing Insights')

@push('styles')
<style>
.demands-metric-card {
    border-radius: 18px;
    border: 1px solid var(--border-color, #e2e8f0);
    background: var(--surface, #ffffff);
    padding: 1.25rem 1.5rem;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
}
.demands-metric-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
}
.metric-icon-wrap {
    width: 46px;
    height: 46px;
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.25rem;
}
.demand-table-card {
    border-radius: 20px;
    border: 1px solid var(--border-color, #e2e8f0);
    background: var(--surface, #ffffff);
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.05);
    overflow: hidden;
}
.demand-table-card .table {
    color: var(--text-main, #0f172a);
}
.demand-table-card .table thead th {
    background: var(--surface-2, #f8fafc);
    color: var(--text-muted, #64748b);
    border-bottom: 1px solid var(--border-color, #e2e8f0);
}
.demand-table-card .table tbody tr {
    border-bottom: 1px solid var(--border-color, #e2e8f0);
}
.demand-table-card .table tbody td {
    color: var(--text-main, #0f172a);
}
.demand-hits-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 4px 10px;
    border-radius: 999px;
    font-weight: 700;
    font-size: 0.8rem;
}
.demand-hits-high {
    background-color: #fee2e2;
    color: #dc2626;
    border: 1px solid #fca5a5;
}
.demand-hits-med {
    background-color: #fef3c7;
    color: #d97706;
    border: 1px solid #fcd34d;
}
.demand-hits-normal {
    background-color: var(--surface-2, #f1f5f9);
    color: var(--text-muted, #475569);
    border: 1px solid var(--border-color, #e2e8f0);
}
.status-badge-select {
    font-size: 0.8rem;
    font-weight: 600;
    border-radius: 8px;
    padding: 4px 8px;
    cursor: pointer;
    border: 1px solid transparent;
}
.status-badge-pending {
    background-color: #fef3c7;
    color: #92400e;
    border-color: #fde68a;
}
.status-badge-under_review {
    background-color: #e0e7ff;
    color: #3730a3;
    border-color: #c7d2fe;
}
.status-badge-planned {
    background-color: #e0f2fe;
    color: #0369a1;
    border-color: #bae6fd;
}
.status-badge-stocked {
    background-color: #dcfce7;
    color: #166534;
    border-color: #bbf7d0;
}
.status-badge-rejected {
    background-color: var(--surface-2, #f1f5f9);
    color: var(--text-muted, #64748b);
    border-color: var(--border-color, #e2e8f0);
}
.demand-search-pill {
    padding: 6px 14px;
    border-radius: 999px;
    font-size: 0.85rem;
    font-weight: 600;
    text-decoration: none;
    color: var(--text-muted, #64748b);
    background: var(--surface, #f8fafc);
    border: 1px solid var(--border-color, #e2e8f0);
    transition: all 0.2s ease;
}
.demand-search-pill:hover,
.demand-search-pill.active {
    background: var(--accent, #2563eb);
    color: #ffffff;
    border-color: var(--accent, #2563eb);
}
</style>
@endpush

@section('content')
<div class="container-fluid px-3 px-md-4 py-4">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1 small">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}" class="text-decoration-none text-muted">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.products.index') }}" class="text-decoration-none text-muted">Catalog</a></li>
                    <li class="breadcrumb-item active text-dark fw-semibold" aria-current="page">Customer Demands</li>
                </ol>
            </nav>
            <h1 class="h3 fw-bold text-dark mb-1 d-flex align-items-center gap-2">
                <i class="fa-solid fa-lightbulb text-warning"></i>
                <span>Customer Product Demands</span>
            </h1>
            <p class="text-muted mb-0 small">
                Track products customers searched for via the AI Chatbot that were unlisted or out of stock. Prioritize inventory procurement based on real demand.
            </p>
        </div>

        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('admin.products.ai') }}" class="btn btn-outline-primary rounded-pill px-3 py-2 fw-semibold d-inline-flex align-items-center gap-2 font-heading shadow-sm" style="font-size: 0.85rem;">
                <i class="fa-solid fa-wand-magic-sparkles"></i>
                <span>AI Generator</span>
            </a>
            <a href="{{ route('admin.products.create') }}" class="btn btn-dark rounded-pill px-3.5 py-2 fw-semibold d-inline-flex align-items-center gap-2 font-heading shadow-sm" style="font-size: 0.85rem;">
                <i class="fa-solid fa-plus"></i>
                <span>Create Product</span>
            </a>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="demands-metric-card d-flex align-items-center justify-content-between">
                <div>
                    <div class="text-muted small fw-semibold mb-1">Total Demand Queries</div>
                    <div class="fs-4 fw-bold text-dark">{{ number_format($metrics['total']) }}</div>
                    <div class="text-secondary small mt-0.5">{{ number_format($metrics['total_hits']) }} total customer hits</div>
                </div>
                <div class="metric-icon-wrap bg-primary-subtle text-primary">
                    <i class="fa-solid fa-chart-line"></i>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="demands-metric-card d-flex align-items-center justify-content-between">
                <div>
                    <div class="text-muted small fw-semibold mb-1">Pending Review</div>
                    <div class="fs-4 fw-bold text-warning">{{ number_format($metrics['pending']) }}</div>
                    <div class="text-secondary small mt-0.5">Needs admin action</div>
                </div>
                <div class="metric-icon-wrap bg-warning-subtle text-warning">
                    <i class="fa-solid fa-clock"></i>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="demands-metric-card d-flex align-items-center justify-content-between">
                <div>
                    <div class="text-muted small fw-semibold mb-1">Planned / Sourcing</div>
                    <div class="fs-4 fw-bold text-info">{{ number_format($metrics['planned'] + $metrics['under_review']) }}</div>
                    <div class="text-secondary small mt-0.5">{{ $metrics['planned'] }} planned, {{ $metrics['under_review'] }} in review</div>
                </div>
                <div class="metric-icon-wrap bg-info-subtle text-info">
                    <i class="fa-solid fa-boxes-packing"></i>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="demands-metric-card d-flex align-items-center justify-content-between">
                <div>
                    <div class="text-muted small fw-semibold mb-1">Stocked & Fulfilled</div>
                    <div class="fs-4 fw-bold text-success">{{ number_format($metrics['stocked']) }}</div>
                    <div class="text-secondary small mt-0.5">Successfully sourced</div>
                </div>
                <div class="metric-icon-wrap bg-success-subtle text-success">
                    <i class="fa-solid fa-circle-check"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="demand-table-card">
        <div class="p-3 border-bottom bg-light bg-opacity-50">
            <form action="{{ route('admin.product_demands.index') }}" method="GET" class="row g-2 align-items-center">
                <div class="col-12 col-lg-auto d-flex flex-wrap gap-1.5 align-items-center">
                    <a href="{{ route('admin.product_demands.index', ['search' => $search, 'sort' => $sort]) }}" class="demand-search-pill {{ $status === '' ? 'active' : '' }}">
                        All ({{ $metrics['total'] }})
                    </a>
                    <a href="{{ route('admin.product_demands.index', ['status' => 'pending', 'search' => $search, 'sort' => $sort]) }}" class="demand-search-pill {{ $status === 'pending' ? 'active' : '' }}">
                        Pending ({{ $metrics['pending'] }})
                    </a>
                    <a href="{{ route('admin.product_demands.index', ['status' => 'under_review', 'search' => $search, 'sort' => $sort]) }}" class="demand-search-pill {{ $status === 'under_review' ? 'active' : '' }}">
                        Reviewing ({{ $metrics['under_review'] }})
                    </a>
                    <a href="{{ route('admin.product_demands.index', ['status' => 'planned', 'search' => $search, 'sort' => $sort]) }}" class="demand-search-pill {{ $status === 'planned' ? 'active' : '' }}">
                        Planned ({{ $metrics['planned'] }})
                    </a>
                    <a href="{{ route('admin.product_demands.index', ['status' => 'stocked', 'search' => $search, 'sort' => $sort]) }}" class="demand-search-pill {{ $status === 'stocked' ? 'active' : '' }}">
                        Stocked ({{ $metrics['stocked'] }})
                    </a>
                </div>

                <div class="col-12 col-md-auto ms-lg-auto d-flex align-items-center gap-2">
                    <div class="input-group input-group-sm" style="max-width: 260px;">
                        <span class="input-group-text bg-white border-end-0 text-muted"><i class="fa-solid fa-magnifying-glass"></i></span>
                        <input type="text" name="search" value="{{ $search }}" class="form-control border-start-0 ps-0" placeholder="Search query or phone...">
                    </div>

                    <select name="sort" class="form-select form-select-sm" style="width: auto;" onchange="this.form.submit()">
                        <option value="hits_desc" {{ $sort === 'hits_desc' ? 'selected' : '' }}>Most Searched (Hits)</option>
                        <option value="recent" {{ $sort === 'recent' ? 'selected' : '' }}>Most Recent</option>
                        <option value="oldest" {{ $sort === 'oldest' ? 'selected' : '' }}>Oldest</option>
                    </select>

                    <button type="submit" class="btn btn-sm btn-dark rounded-pill px-3">Filter</button>
                    @if($search !== '' || $status !== '' || $sort !== 'hits_desc')
                        <a href="{{ route('admin.product_demands.index') }}" class="btn btn-sm btn-outline-secondary rounded-pill px-2.5" title="Reset Filters">
                            <i class="fa-solid fa-xmark"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" style="font-size: 0.88rem;">
                <thead class="table-light text-uppercase text-secondary" style="font-size: 0.75rem; letter-spacing: 0.5px;">
                    <tr>
                        <th class="ps-3 py-3" style="width: 110px;">Hits</th>
                        <th class="py-3">Demanded Product / Query</th>
                        <th class="py-3">Category Hint</th>
                        <th class="py-3">Customer Contact</th>
                        <th class="py-3">Last Searched</th>
                        <th class="py-3" style="width: 140px;">Status</th>
                        <th class="py-3 text-end pe-3" style="width: 180px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($demands as $demand)
                        @php
                            $hitsClass = $demand->hits_count >= 5 ? 'demand-hits-high' : ($demand->hits_count >= 2 ? 'demand-hits-med' : 'demand-hits-normal');
                        @endphp
                        <tr id="demand-row-{{ $demand->id }}">
                            <td class="ps-3">
                                <span class="demand-hits-badge {{ $hitsClass }}">
                                    <i class="fa-solid fa-fire fa-xs"></i>
                                    <span>{{ $demand->hits_count }}</span>
                                </span>
                            </td>

                            <td>
                                <div class="fw-bold text-dark font-heading">{{ $demand->query }}</div>
                                @if($demand->admin_notes)
                                    <div class="small text-muted mt-1 d-flex align-items-center gap-1" id="notes-preview-{{ $demand->id }}">
                                        <i class="fa-solid fa-note-sticky text-warning fa-xs"></i>
                                        <span class="text-truncate" style="max-width: 280px;">{{ $demand->admin_notes }}</span>
                                    </div>
                                @else
                                    <div class="small text-muted mt-1 d-none" id="notes-preview-{{ $demand->id }}">
                                        <i class="fa-solid fa-note-sticky text-warning fa-xs"></i>
                                        <span class="text-truncate" style="max-width: 280px;"></span>
                                    </div>
                                @endif
                            </td>

                            <td>
                                @if($demand->category_hint)
                                    <span class="badge bg-secondary-subtle text-secondary rounded-pill px-2.5 py-1 text-capitalize">
                                        {{ $demand->category_hint }}
                                    </span>
                                @else
                                    <span class="text-muted small">—</span>
                                @endif
                            </td>

                            <td>
                                @if($demand->customer_phone || $demand->customer_name)
                                    <div class="fw-semibold text-dark">{{ $demand->customer_name ?: 'Guest' }}</div>
                                    @if($demand->customer_phone)
                                        <div class="small text-muted font-monospace"><i class="fa-solid fa-phone fa-xs me-1"></i>{{ $demand->customer_phone }}</div>
                                    @endif
                                @else
                                    <span class="text-muted small"><i class="fa-solid fa-globe fa-xs me-1"></i>{{ $demand->ip_address ?: 'Unknown IP' }}</span>
                                @endif
                            </td>

                            <td>
                                <div class="text-dark">{{ \Carbon\Carbon::parse($demand->last_requested_at)->format('d M, Y') }}</div>
                                <div class="small text-muted">{{ \Carbon\Carbon::parse($demand->last_requested_at)->diffForHumans() }}</div>
                            </td>

                            <td>
                                <select class="form-select form-select-sm status-badge-select status-badge-{{ $demand->status }}" 
                                        data-id="{{ $demand->id }}" 
                                        onchange="updateDemandStatus({{ $demand->id }}, this.value, this)">
                                    <option value="pending" {{ $demand->status === 'pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="under_review" {{ $demand->status === 'under_review' ? 'selected' : '' }}>In Review</option>
                                    <option value="planned" {{ $demand->status === 'planned' ? 'selected' : '' }}>Planned</option>
                                    <option value="stocked" {{ $demand->status === 'stocked' ? 'selected' : '' }}>Stocked</option>
                                    <option value="rejected" {{ $demand->status === 'rejected' ? 'selected' : '' }}>Discarded</option>
                                </select>
                            </td>

                            <td class="text-end pe-3">
                                <div class="d-inline-flex align-items-center gap-1">
                                    <a href="{{ route('admin.products.create', ['title' => $demand->query]) }}" 
                                       class="btn btn-sm btn-outline-success rounded-pill px-2 py-1" 
                                       title="Create this product in catalog">
                                        <i class="fa-solid fa-plus fa-xs me-1"></i>Create
                                    </a>

                                    <button type="button" 
                                            class="btn btn-sm btn-outline-secondary rounded-circle" 
                                            style="width: 30px; height: 30px; padding: 0;" 
                                            title="Edit Admin Notes" 
                                            onclick="openNotesModal({{ $demand->id }}, '{{ addslashes($demand->query) }}', '{{ addslashes($demand->admin_notes ?? '') }}')">
                                        <i class="fa-regular fa-comment-dots"></i>
                                    </button>

                                    <button type="button" 
                                            class="btn btn-sm btn-outline-danger rounded-circle" 
                                            style="width: 30px; height: 30px; padding: 0;" 
                                            title="Delete record" 
                                            onclick="deleteDemand({{ $demand->id }})">
                                        <i class="fa-solid fa-trash-can fa-xs"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5">
                                <div class="text-muted">
                                    <i class="fa-solid fa-box-open fs-1 text-secondary opacity-50 mb-3"></i>
                                    <h6 class="fw-bold text-dark">No Customer Demands Recorded</h6>
                                    <p class="small text-muted mb-0">When customers ask the AI Chatbot for unlisted items, they will automatically appear here.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($demands->hasPages())
            <div class="p-3 border-top d-flex align-items-center justify-content-between">
                <div class="small text-muted">
                    Showing {{ $demands->firstItem() }} to {{ $demands->lastItem() }} of {{ $demands->total() }} entries
                </div>
                <div>
                    {{ $demands->links() }}
                </div>
            </div>
        @endif
    </div>
</div>

<div class="modal fade" id="demandNotesModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow rounded-4">
            <div class="modal-header border-bottom px-4 py-3">
                <h6 class="modal-title fw-bold text-dark font-heading">
                    <i class="fa-solid fa-note-sticky text-warning me-2"></i>Procurement Notes
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body px-4 py-3">
                <div class="mb-3">
                    <label class="form-label small fw-semibold text-secondary mb-1">Product Query:</label>
                    <div class="fw-bold text-dark fs-6" id="modalDemandQuery"></div>
                </div>
                <div class="mb-2">
                    <label for="modalDemandNotes" class="form-label small fw-semibold text-secondary mb-1">Internal Notes / Sourcing Plan:</label>
                    <textarea class="form-control" id="modalDemandNotes" rows="4" placeholder="e.g. Contacted supplier, shipment expected in 2 weeks..."></textarea>
                </div>
                <input type="hidden" id="modalDemandId" value="">
            </div>
            <div class="modal-footer border-top px-4 py-3">
                <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-sm btn-dark rounded-pill px-4" id="saveNotesBtn" onclick="saveDemandNotes()">Save Notes</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function getMetaCsrf() {
    const meta = document.querySelector('meta[name="csrf-token"]');
    return meta ? meta.getAttribute('content') : '';
}

function updateDemandStatus(id, newStatus, selectEl) {
    selectEl.disabled = true;
    axios.post(`/admin/product-demands/${id}/status`, { status: newStatus })
    .then(r => {
        selectEl.disabled = false;
        const data = r.data;
        if (data.success) {
            selectEl.className = 'form-select form-select-sm status-badge-select status-badge-' + newStatus;
        } else {
            alert(data.message || 'Failed to update status');
        }
    })
    .catch(() => {
        selectEl.disabled = false;
        alert('An error occurred while updating status');
    });
}

function openNotesModal(id, query, notes) {
    document.getElementById('modalDemandId').value = id;
    document.getElementById('modalDemandQuery').innerText = query;
    document.getElementById('modalDemandNotes').value = notes;
    const modal = new bootstrap.Modal(document.getElementById('demandNotesModal'));
    modal.show();
}

function saveDemandNotes() {
    const id = document.getElementById('modalDemandId').value;
    const notes = document.getElementById('modalDemandNotes').value;
    const btn = document.getElementById('saveNotesBtn');
    btn.disabled = true;

    axios.post(`/admin/product-demands/${id}/notes`, { admin_notes: notes })
    .then(r => {
        btn.disabled = false;
        const data = r.data;
        if (data.success) {
            const previewEl = document.getElementById('notes-preview-' + id);
            if (previewEl) {
                if (notes.trim()) {
                    previewEl.classList.remove('d-none');
                    previewEl.querySelector('span').innerText = notes;
                } else {
                    previewEl.classList.add('d-none');
                }
            }
            const modalEl = document.getElementById('demandNotesModal');
            const modal = bootstrap.Modal.getInstance(modalEl);
            if (modal) modal.hide();
        } else {
            alert(data.message || 'Failed to save notes');
        }
    })
    .catch(() => {
        btn.disabled = false;
        alert('An error occurred while saving notes');
    });
}

function deleteDemand(id) {
    if (!confirm('Are you sure you want to delete this demand record?')) return;

    axios.delete(`/admin/product-demands/${id}`)
    .then(r => {
        const data = r.data;
        if (data.success) {
            const row = document.getElementById('demand-row-' + id);
            if (row) {
                row.remove();
            }
        } else {
            alert(data.message || 'Failed to delete record');
        }
    })
    .catch(() => {
        alert('An error occurred while deleting record');
    });
}
</script>
@endpush
