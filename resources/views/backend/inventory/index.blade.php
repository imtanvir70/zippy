@extends('backend.layouts.app')

@section('title', 'Inventory & Supplier Management')

@section('content')
<div class="card p-3 p-md-4 mb-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <h3 class="fw-bold mb-1">Inventory & Supplier Management</h3>
            <p class="text-muted small mb-0">Track product stock origins, purchase records, and low stock warnings.</p>
        </div>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-primary btn-sm" onclick="document.getElementById('purchaseModal').style.display='block'">
                <i class="fa-solid fa-plus me-1"></i> New Purchase
            </button>
            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="document.getElementById('supplierModal').style.display='block'">
                <i class="fa-solid fa-truck-field me-1"></i> Add Supplier
            </button>
        </div>
    </div>
</div>

@if($lowStockProducts->isNotEmpty())
<div class="card p-3 mb-4 border-start border-4 border-warning bg-warning-subtle">
    <div class="d-flex align-items-center gap-2 mb-2">
        <i class="fa-solid fa-triangle-exclamation text-warning fs-5"></i>
        <h6 class="fw-bold text-dark mb-0">Low Stock Alert (Stock &lt; 10 Units)</h6>
    </div>
    <div class="table-responsive">
        <table class="table table-sm table-borderless align-middle mb-0">
            <thead>
                <tr class="text-muted small">
                    <th>Product Title</th>
                    <th>SKU</th>
                    <th>Current Stock</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($lowStockProducts as $lp)
                <tr>
                    <td class="fw-semibold text-dark">{{ $lp->title }}</td>
                    <td class="font-monospace small">{{ $lp->sku ?? 'N/A' }}</td>
                    <td>
                        <span class="badge bg-danger rounded-pill px-2.5 py-1">{{ $lp->stock_qty }} Units</span>
                    </td>
                    <td>
                        <span class="badge bg-warning text-dark border border-warning rounded-pill">Restock Needed</span>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

<div class="row g-4 mb-4">
    <div class="col-lg-8">
        <div class="card p-3 p-md-4 h-100">
            <h5 class="fw-bold mb-3">Recent Purchases</h5>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr class="small text-muted">
                            <th>Date</th>
                            <th>Product</th>
                            <th>Supplier</th>
                            <th>Qty</th>
                            <th>Unit Cost</th>
                            <th>Total Cost</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($purchases as $purchase)
                        <tr>
                            <td class="small">{{ \Carbon\Carbon::parse($purchase->purchase_date)->format('d M Y') }}</td>
                            <td class="fw-semibold text-dark">{{ $purchase->product_title ?? 'N/A' }}</td>
                            <td>{{ $purchase->supplier_name ?? 'N/A' }}</td>
                            <td><span class="badge bg-primary-subtle text-primary border rounded-pill">+{{ $purchase->quantity }}</span></td>
                            <td>৳ {{ number_format($purchase->unit_cost, 2) }}</td>
                            <td class="fw-bold text-dark">৳ {{ number_format($purchase->total_cost, 2) }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">No purchase records recorded yet.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-3">
                {{ $purchases->links() }}
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card p-3 p-md-4 h-100">
            <h5 class="fw-bold mb-3">Registered Suppliers</h5>
            <div class="d-flex flex-column gap-2">
                @forelse($suppliers as $sup)
                <div class="p-2.5 border rounded-3 bg-light d-flex justify-content-between align-items-center">
                    <div>
                        <div class="fw-bold text-dark">{{ $sup->name }}</div>
                        <small class="text-muted d-block">{{ $sup->company_name ?? 'Individual' }} | {{ $sup->phone ?? 'No Phone' }}</small>
                    </div>
                    <form action="{{ route('admin.inventory.suppliers.delete', $sup->id) }}" method="POST" onsubmit="return confirm('Delete this supplier?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-outline-danger border-0">
                            <i class="fa-solid fa-trash"></i>
                        </button>
                    </form>
                </div>
                @empty
                <p class="text-muted small mb-0">No suppliers registered.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>

<div class="modal" id="supplierModal" tabindex="-1" style="display:none; background: rgba(0,0,0,0.5);">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="fw-bold mb-0">Add New Supplier</h5>
                <button type="button" class="btn-close" onclick="document.getElementById('supplierModal').style.display='none'"></button>
            </div>
            <form action="{{ route('admin.inventory.suppliers.store') }}" method="POST">
                @csrf
                <div class="modal-body d-flex flex-column gap-3">
                    <div>
                        <label class="form-label small fw-bold">Supplier Name *</label>
                        <input type="text" name="name" class="form-control" required placeholder="e.g. Rahim Trading">
                    </div>
                    <div>
                        <label class="form-label small fw-bold">Company Name</label>
                        <input type="text" name="company_name" class="form-control" placeholder="e.g. BD Gadget Importers Ltd.">
                    </div>
                    <div>
                        <label class="form-label small fw-bold">Contact Phone</label>
                        <input type="text" name="phone" class="form-control" placeholder="017xxxxxxxx">
                    </div>
                    <div>
                        <label class="form-label small fw-bold">Email</label>
                        <input type="email" name="email" class="form-control" placeholder="supplier@example.com">
                    </div>
                    <div>
                        <label class="form-label small fw-bold">Address</label>
                        <textarea name="address" class="form-control" rows="2" placeholder="Office / Warehouse address"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" onclick="document.getElementById('supplierModal').style.display='none'">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm">Save Supplier</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal" id="purchaseModal" tabindex="-1" style="display:none; background: rgba(0,0,0,0.5);">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="fw-bold mb-0">Record Purchase / Restock</h5>
                <button type="button" class="btn-close" onclick="document.getElementById('purchaseModal').style.display='none'"></button>
            </div>
            <form action="{{ route('admin.inventory.purchases.store') }}" method="POST">
                @csrf
                <div class="modal-body d-flex flex-column gap-3">
                    <div>
                        <label class="form-label small fw-bold">Supplier *</label>
                        <select name="supplier_id" class="form-select" required>
                            <option value="">-- Choose Supplier --</option>
                            @foreach($suppliers as $s)
                                <option value="{{ $s->id }}">{{ $s->name }} ({{ $s->company_name ?? 'Individual' }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="form-label small fw-bold">Product *</label>
                        <select name="product_id" class="form-select" required>
                            <option value="">-- Choose Product --</option>
                            @foreach($products as $p)
                                <option value="{{ $p->id }}">{{ $p->title }} (Current Stock: {{ $p->stock_qty }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="row g-2">
                        <div class="col-6">
                            <label class="form-label small fw-bold">Quantity *</label>
                            <input type="number" name="quantity" class="form-control" min="1" required placeholder="Units">
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-bold">Unit Cost (BDT) *</label>
                            <input type="number" step="0.01" name="unit_cost" class="form-control" required placeholder="Cost per unit">
                        </div>
                    </div>
                    <div class="row g-2">
                        <div class="col-6">
                            <label class="form-label small fw-bold">Invoice / Bill #</label>
                            <input type="text" name="invoice_no" class="form-control" placeholder="INV-001">
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-bold">Purchase Date *</label>
                            <input type="date" name="purchase_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>
                    </div>
                    <div>
                        <label class="form-label small fw-bold">Notes</label>
                        <textarea name="note" class="form-control" rows="2" placeholder="Batch details or remarks"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" onclick="document.getElementById('purchaseModal').style.display='none'">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm">Record &amp; Add Stock</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
