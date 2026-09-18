@extends('backend.layouts.app')

@section('title', 'Product Catalog & Inventory')

@push('styles')
<style>
    #productModal .modal-dialog {
        max-width: 1450px;
        width: 96vw;
        height: 94vh;
        margin: 1rem auto;
    }
    #productModal .modal-content {
        height: 100%;
        max-height: 94vh;
        display: flex;
        flex-direction: column;
        border-radius: 24px;
        overflow: hidden;
        border: 1px solid rgba(226, 232, 240, 0.8);
        box-shadow: 0 25px 50px -12px rgba(15, 23, 42, 0.25);
    }
    #productModal form#productForm {
        height: 100%;
        display: flex;
        flex-direction: column;
        overflow: hidden;
    }
    #productModal .modal-header {
        flex-shrink: 0;
        background: linear-gradient(180deg, rgba(248, 250, 252, 0.9) 0%, rgba(241, 245, 249, 0.6) 100%);
        backdrop-filter: blur(8px);
        border-bottom: 1px solid rgba(226, 232, 240, 0.8);
    }
    #productModal .wizard-steps-container {
        flex-shrink: 0;
       
    }
    #productModal .modal-body {
        flex: 1 1 auto;
        overflow-y: auto !important;
        -webkit-overflow-scrolling: touch;
    }
    #productModal .modal-footer {
        flex-shrink: 0;
        z-index: 10;
        background: #ffffff;
        border-top: 1px solid rgba(226, 232, 240, 0.8);
        box-shadow: 0 -4px 16px rgba(0, 0, 0, 0.04);
    }

    .wizard-steps-nav {
        gap: 12px;
        overflow-x: auto;
        white-space: nowrap;
        scrollbar-width: none;
    }
    .wizard-steps-nav::-webkit-scrollbar {
        display: none;
    }
    .wizard-step-btn {
        transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        border: 1px solid transparent;
        border-radius: 14px !important;
        flex-shrink: 0;
        padding: 6px 14px !important;
    }
    .wizard-step-btn:hover {
        background: rgba(13, 110, 253, 0.06);
    }
    .wizard-step-btn.active {
        background: rgba(13, 110, 253, 0.08) !important;
        border-color: rgba(13, 110, 253, 0.25) !important;
        box-shadow: 0 2px 8px rgba(13, 110, 253, 0.1);
    }
    .wizard-step-btn .step-num {
        width: 32px;
        height: 32px;
        font-size: 0.85rem;
        background: #e2e8f0;
        color: #64748b;
        transition: all 0.25s ease;
        box-shadow: inset 0 1px 2px rgba(0, 0, 0, 0.05);
    }
    .wizard-step-btn.active .step-num {
        background: linear-gradient(135deg, #0d6efd 0%, #0b5ed7 100%) !important;
        color: #ffffff !important;
        box-shadow: 0 4px 12px rgba(13, 110, 253, 0.35);
    }
    .wizard-step-line {
        height: 2px;
        background: #e2e8f0;
        min-width: 20px;
        border-radius: 2px;
    }
    .wizard-step-pane {
        animation: fadeInStep 0.25s cubic-bezier(0.4, 0, 0.2, 1);
    }
    @keyframes fadeInStep {
        from { opacity: 0; transform: translateY(8px); }
        to { opacity: 1; transform: translateY(0); }
    }

    #productModal .card {
        border: 1px solid var(--border-color) !important;
        box-shadow: 0 4px 16px -2px rgba(15, 23, 42, 0.04) !important;
        background: #ffffff;
        border-radius: 16px !important;
        transition: border-color 0.2s ease, box-shadow 0.2s ease;
    }

    .dropzone-hover-card {
        border-color: rgba(13, 110, 253, 0.45) !important;
        box-shadow: 0 8px 24px -4px rgba(13, 110, 253, 0.12) !important;
    }

    .dropzone-hover-active {
        border-color: #0d6efd !important;
        box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.18) !important;
        background-color: rgba(13, 110, 253, 0.03) !important;
    }

    .paste-target-badge {
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        font-size: 0.68rem;
        user-select: none;
        letter-spacing: 0.2px;
    }

    .paste-target-badge.active-target {
        background-color: #0d6efd !important;
        color: #ffffff !important;
        border-color: #0d6efd !important;
        box-shadow: 0 2px 8px rgba(13, 110, 253, 0.35) !important;
    }

    .status-toggle-card {
        border: 1.5px solid var(--border-color) !important;
        border-radius: 14px !important;
        background: var(--bg-surface) !important;
        padding: 13px 15px;
        cursor: pointer;
        transition: all 0.22s cubic-bezier(0.4, 0, 0.2, 1);
        user-select: none;
        margin-bottom: 10px;
    }

    .status-toggle-card:hover {
        border-color: var(--border-color) !important;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px -2px rgba(15, 23, 42, 0.06) !important;
    }

    .status-toggle-card.active-live {
        border-color: var(--border-color) !important;
        background: linear-gradient(135deg, rgba(16, 185, 129, 0.05) 0%, var(--bg-surface) 100%) !important;
        box-shadow: 0 4px 14px -3px rgba(16, 185, 129, 0.15) !important;
    }

    .status-toggle-card.active-featured {
        border-color: var(--border-color) !important;
        background: linear-gradient(135deg, rgba(245, 158, 11, 0.05) 0%, var(--bg-surface) 100%) !important;
        box-shadow: 0 4px 14px -3px rgba(245, 158, 11, 0.15) !important;
    }

    .status-toggle-card.active-flash {
        border-color: var(--border-color) !important;
        background: linear-gradient(135deg, rgba(239, 68, 68, 0.05) 0%, var(--bg-surface) 100%) !important;
        box-shadow: 0 4px 14px -3px rgba(239, 68, 68, 0.15) !important;
    }

    .status-toggle-card.active-shipping {
        border-color: var(--border-color) !important;
        background: linear-gradient(135deg, rgba(6, 182, 212, 0.05) 0%, var(--bg-surface) 100%) !important;
        box-shadow: 0 4px 14px -3px rgba(6, 182, 212, 0.15) !important;
    }

    #statusPreviewBadges span {
        display: inline-block;
        margin-right: 6px;
        font-size: 0.75rem;
        padding: 2px 8px;
        border-radius: 12px;
        font-weight: 500;
    }

    .status-icon-box {
        width: 38px;
        height: 38px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        font-size: 1.1rem;
        transition: transform 0.22s ease;
        margin-right: 10px;
    }

    .status-toggle-card:hover .status-icon-box {
        transform: scale(1.06);
    }

    .status-toggle-card .form-check-input {
        width: 2.7rem;
        height: 1.45rem;
        cursor: pointer;
        flex-shrink: 0;
        margin-top: 0;
    }

    #formIsActive:checked {
        background-color: #10b981;
        border-color: #10b981;
    }

    #formIsFeatured:checked {
        background-color: #f59e0b;
        border-color: #f59e0b;
    }

    #formIsFlashDeal:checked {
        background-color: #ef4444;
        border-color: #ef4444;
    }

    #formIsFreeShipping:checked {
        background-color: #06b6d4;
        border-color: #06b6d4;
    }

    @keyframes statusPulseAnimation {
        0% {
            transform: scale(0.95);
            box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.6);
        }
        70% {
            transform: scale(1);
            box-shadow: 0 0 0 6px rgba(16, 185, 129, 0);
        }
        100% {
            transform: scale(0.95);
            box-shadow: 0 0 0 0 rgba(16, 185, 129, 0);
        }
    }

    .status-live-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        display: inline-block;
        background-color: #10b981;
        animation: statusPulseAnimation 2s infinite;
        margin-right: 10px;
    }

    .status-draft-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        display: inline-block;
        background-color: #94a3b8;
    }

    @media (max-width: 768px) {
        #productModal .modal-dialog {
            width: 100vw;
            height: 100vh;
            max-height: 100vh;
            margin: 0;
        }
        #productModal .modal-content {
            border-radius: 0;
            max-height: 100vh;
            height: 100%;
        }
    }
</style>
@endpush

@section('content')
<div class="card p-3 p-md-4 mb-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <h3 class="fw-bold mb-0">Products Catalog & Inventory</h3>
            <small class="text-muted">Real-time stock management, MoveOn color variants, and instant updates</small>
        </div>
        <div class="d-flex align-items-center flex-wrap gap-2">
            @canPerm('admin.products.export_csv')
                <a href="{{ route('admin.products.export_csv') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="fa-solid fa-file-csv me-1"></i> Export CSV
                </a>
            @endcanPerm
            @canPerm('admin.products.import_csv')
                <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#csvImportModal">
                    <i class="fa-solid fa-file-arrow-up me-1"></i> Import CSV
                </button>
            @endcanPerm
            <a href="{{ route('admin.products.ai') }}" class="btn btn-outline-primary d-inline-flex align-items-center gap-1.5 fw-semibold">
                <i class="fa-solid fa-wand-magic-sparkles text-primary"></i> AI Generator
            </a>
            @canPerm('admin.products.store')
                <button type="button" class="btn btn-success" onclick="openCreateProductModal()" id="addBtn">
                    <i class="fa-solid fa-plus fa-fade"></i> Add New Product
                </button>
            @endcanPerm
        </div>
    </div>
</div>

<div class="filter-card">
    <form id="filterForm" class="row g-2 align-items-center">
        <div class="col-md-5">
            <div class="input-group">
                <span class="input-group-text"><i class="fa-solid fa-layer-group"></i></span>
                <select name="category_id" id="filterCategory" class="form-select filter-control">
                    <option value="">All Categories</option>
                    @php
                        $rootFilterCats = $categories->whereNull('parent_id');
                    @endphp
                    @foreach($rootFilterCats as $rfCat)
                        @php
                            $childFilterCats = $categories->where('parent_id', $rfCat->id);
                        @endphp
                        @if($childFilterCats->isNotEmpty())
                            <optgroup label="{{ $rfCat->name }} ({{ $rfCat->name_bn }})">
                                <option value="{{ $rfCat->id }}">● {{ $rfCat->name }} (All {{ $rfCat->name }})</option>
                                @foreach($childFilterCats as $cfCat)
                                    <option value="{{ $cfCat->id }}">↳ {{ $cfCat->name }}</option>
                                @endforeach
                            </optgroup>
                        @else
                            <option value="{{ $rfCat->id }}">{{ $rfCat->name }} ({{ $rfCat->name_bn }})</option>
                        @endif
                    @endforeach
                </select>
            </div>
        </div>
        <div class="col-md-4">
            <div class="input-group">
                <span class="input-group-text"><i class="fa-solid fa-cubes-stacked"></i></span>
                <select name="stock_status" id="filterStockStatus" class="form-select filter-control">
                    <option value="">All Stock Levels</option>
                    <option value="in_stock">In Stock (≥ 10 units)</option>
                    <option value="low_stock">Low Stock (1 - 9 units)</option>
                    <option value="out_of_stock">Out of Stock (0 units)</option>
                </select>
            </div>
        </div>
        <div class="col-md-3">
            <button type="button" class="btn btn-outline-secondary filter-btn w-100" onclick="$('#filterCategory').val(''); $('#filterStockStatus').val(''); productsTable.draw();">
                <i class="fa-solid fa-rotate-left me-1"></i> Reset Filters
            </button>
        </div>
    </form>
</div>

<div class="card p-3 p-md-4 table-card mb-4">
    <div class="table-responsive">
        <table class="table table-striped table-hover table-bordered align-middle" id="productsTable" style="width: 100%;">
            <thead>
                <tr>
                    <th style="width: 36%;">Product Details</th>
                    <th style="width: 16%;">Category</th>
                    <th style="width: 14%;">Price</th>
                    <th style="width: 12%;">Inventory</th>
                    <th style="width: 8%;">Status</th>
                    <th style="width: 14%; text-align: center;">Actions</th>
                </tr>
            </thead>
            <tbody>
            </tbody>
        </table>
    </div>
</div>

<div class="modal fade" id="productModal" tabindex="-1" aria-labelledby="productModalLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable" style="max-width: 1200px;">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 20px; overflow: hidden;">
            <form id="productForm" onsubmit="handleProductFormSubmit(event)" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="id" id="modalProdId" value="">

                <div class="modal-header border-bottom px-4 py-3">
                    <div class="d-flex align-items-center justify-content-between w-100 me-3">
                        <div class="d-flex align-items-center gap-3">
                            <div class="rounded-circle bg-primary-subtle text-primary d-flex align-items-center justify-content-center" style="width: 44px; height: 44px; font-size: 1.25rem;">
                                <i class="fa-solid fa-box-open"></i>
                            </div>
                            <div>
                                <h5 class="fw-bold mb-0" id="productModalLabel">Add New Product</h5>
                                <small class="text-muted">Fill step-by-step to create or update catalog products</small>
                            </div>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="wizard-steps-container  border-bottom px-4 py-2">
                    <div class="wizard-steps-nav d-flex justify-content-between align-items-center position-relative">
                        <button type="button" class="wizard-step-btn active d-flex align-items-center gap-2 btn btn-link text-decoration-none p-2 rounded-3" onclick="goToWizardStep(1)" id="stepTab-1">
                            <span class="step-num rounded-circle d-flex align-items-center justify-content-center fw-bold">1</span>
                            <div class="text-start d-none d-md-block">
                                <div class="step-title fw-semibold small">Basic Details</div>
                                <div class="step-sub text-muted" style="font-size: 0.72rem;">Title, Pricing & Stock</div>
                            </div>
                        </button>
                        <div class="wizard-step-line flex-grow-1 mx-2"></div>
                        <button type="button" class="wizard-step-btn d-flex align-items-center gap-2 btn btn-link text-decoration-none p-2 rounded-3 text-muted" onclick="goToWizardStep(2)" id="stepTab-2">
                            <span class="step-num rounded-circle d-flex align-items-center justify-content-center fw-bold">2</span>
                            <div class="text-start d-none d-md-block">
                                <div class="step-title fw-semibold small">Media & Images</div>
                                <div class="step-sub text-muted" style="font-size: 0.72rem;">Thumbnail & Gallery</div>
                            </div>
                        </button>
                        <div class="wizard-step-line flex-grow-1 mx-2"></div>
                        <button type="button" class="wizard-step-btn d-flex align-items-center gap-2 btn btn-link text-decoration-none p-2 rounded-3 text-muted" onclick="goToWizardStep(3)" id="stepTab-3">
                            <span class="step-num rounded-circle d-flex align-items-center justify-content-center fw-bold">3</span>
                            <div class="text-start d-none d-md-block">
                                <div class="step-title fw-semibold small">Variants & Specs</div>
                                <div class="step-sub text-muted" style="font-size: 0.72rem;">Colors, Matrix & Attributes</div>
                            </div>
                        </button>
                        <div class="wizard-step-line flex-grow-1 mx-2"></div>
                        <button type="button" class="wizard-step-btn d-flex align-items-center gap-2 btn btn-link text-decoration-none p-2 rounded-3 text-muted" onclick="goToWizardStep(4)" id="stepTab-4">
                            <span class="step-num rounded-circle d-flex align-items-center justify-content-center fw-bold">4</span>
                            <div class="text-start d-none d-md-block">
                                <div class="step-title fw-semibold small">Descriptions & SEO</div>
                                <div class="step-sub text-muted" style="font-size: 0.72rem;">AI Generator & Metadata</div>
                            </div>
                        </button>
                    </div>
                </div>

                <div class="modal-body p-4">
                    <div id="modalErrorAlert" class="alert alert-danger alert-dismissible fade show d-none mb-3 border-0 shadow-sm rounded-3" role="alert">
                        <div class="d-flex align-items-start gap-2">
                            <i class="fa-solid fa-circle-exclamation mt-1 flex-shrink-0 text-danger"></i>
                            <div>
                                <strong id="modalErrorTitle">Validation Error</strong>
                                <div id="modalErrorMessage" class="small mt-1"></div>
                            </div>
                        </div>
                        <button type="button" class="btn-close" onclick="document.getElementById('modalErrorAlert').classList.add('d-none')"></button>
                    </div>

                    <div class="wizard-step-pane active" id="wizardStep-1">
                        <div class="card border-0 shadow-sm p-3 p-md-4 mb-3 rounded-4 ">
                            <div class="d-flex align-items-center justify-content-between mb-3 border-bottom pb-2">
                                <h6 class="fw-bold mb-0 ">
                                    <i class="fa-solid fa-circle-info text-primary me-2"></i>General Information
                                </h6>
                                <span class="badge bg-primary-subtle text-primary rounded-pill px-3 py-1 small">Step 1 of 4</span>
                            </div>
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label fw-semibold">Product Title <span class="text-danger">*</span></label>
                                    <input type="text" name="title" id="formTitle" class="form-control form-control-lg fs-6" placeholder="e.g. Dr58 Wireless Noise-Cancelling Bluetooth Headphone" required oninput="handleTitleChanged(this.value)">
                                </div>
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center justify-content-between mb-1">
                                        <label class="form-label fw-semibold mb-0">Category <span class="text-danger">*</span></label>
                                        <span id="catSuggestStatus" class="badge bg-primary-subtle text-primary rounded-pill px-2 py-0.5" style="font-size: 0.7rem; display: none;">
                                            <i class="fa-solid fa-wand-magic-sparkles me-1"></i> Auto-Matched
                                        </span>
                                    </div>
                                    <select name="category_id" id="formCategory" class="form-select form-select-lg fs-6" required onchange="isCategoryManuallySelected = true;">
                                        <option value="">Select Category</option>
                                        @php
                                            $rootModalCats = $categories->whereNull('parent_id');
                                        @endphp
                                        @foreach($rootModalCats as $rmCat)
                                            @php
                                                $childModalCats = $categories->where('parent_id', $rmCat->id);
                                            @endphp
                                            @if($childModalCats->count() > 0)
                                                <optgroup label="{{ $rmCat->name }} ({{ $rmCat->name_bn }})">
                                                    <option value="{{ $rmCat->id }}">{{ $rmCat->name }} (All / Main)</option>
                                                    @foreach($childModalCats as $cmCat)
                                                        <option value="{{ $cmCat->id }}">↳ {{ $cmCat->name }} ({{ $cmCat->name_bn }})</option>
                                                    @endforeach
                                                </optgroup>
                                            @else
                                                <option value="{{ $rmCat->id }}">{{ $rmCat->name }} ({{ $rmCat->name_bn }})</option>
                                            @endif
                                        @endforeach
                                    </select>
                                    <div id="categorySuggestionsBox" class="mt-2 d-flex flex-wrap gap-1 align-items-center" style="min-height: 22px;">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <label class="form-label fw-semibold">SKU Code</label>
                                        <span class="badge bg-secondary-subtle text-secondary rounded-pill px-2 py-0.5" style="font-size: 0.7rem;">Auto-generates</span>
                                    </div>
                                    <div class="input-group">
                                        <input type="text" name="sku" id="formSku" class="form-control form-control-lg fs-6 font-monospace" placeholder="e.g. ZB-DR58-94" oninput="isSkuManuallyEdited = true;">
                                        <button type="button" class="btn btn-outline-secondary" onclick="regenerateSku()" title="Regenerate SKU">
                                            <i class="fa-solid fa-arrows-rotate"></i>
                                        </button>
                                    </div>
                                    <small class="text-muted" style="font-size: 0.72rem;">টাইটেল দিলে স্বয়ংক্রিয়ভাবে তৈরি হবে, চাইলে নিজেও এডিট করতে পারবেন।</small>
                                </div>
                            </div>
                        </div>

                        <div class="card border-0 shadow-sm p-3 p-md-4 rounded-4 ">
                            <div class="d-flex align-items-center justify-content-between mb-3 border-bottom pb-2">
                                <h6 class="fw-bold mb-0 ">
                                    <i class="fa-solid fa-tags text-primary me-2"></i>Pricing, Purchase Cost & Inventory
                                </h6>
                                <span class="text-muted small" id="marginPreviewBadge" style="font-size: 0.78rem;"></span>
                            </div>
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold text-danger">Purchase / Cost Price (৳)</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-danger-subtle text-danger fw-bold">৳</span>
                                        <input type="number" step="0.01" name="cost_price" id="formCostPrice" class="form-control form-control-lg fs-6" placeholder="e.g. 500" oninput="calculateProfitPreview()">
                                    </div>
                                    <small class="text-muted" style="font-size: 0.72rem;">আপনার কেনা খরচ (Profit/Loss রিপোর্টে ব্যবহূত হবে)</small>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold text-success">Retail / Selling Price (৳) <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-success-subtle text-success fw-bold">৳</span>
                                        <input type="number" step="0.01" name="price" id="formPrice" class="form-control form-control-lg fs-6" placeholder="850" required oninput="calculateProfitPreview()">
                                    </div>
                                    <small class="text-muted" style="font-size: 0.72rem;">কাস্টমারদের কাছে বিক্রয় মূল্য</small>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold text-muted">MRP / Original Price (৳)</label>
                                    <div class="input-group">
                                        <span class="input-group-text ">৳</span>
                                        <input type="number" step="0.01" name="old_price" id="formOldPrice" class="form-control form-control-lg fs-6" placeholder="e.g. 1200" oninput="calculateProfitPreview()">
                                    </div>
                                    <small class="text-muted" style="font-size: 0.72rem;">আগের বেশি দাম (ওয়েবসাইটে কাটা দাগ <del>৳১,২০০</del> দেখাবে)</small>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold">Stock Quantity <span class="text-danger">*</span></label>
                                    <input type="number" name="stock_qty" id="formStock" class="form-control form-control-lg fs-6" placeholder="50" value="50" required>
                                    <small class="text-muted" style="font-size: 0.72rem;">বর্তমান মজুত পরিমাণ</small>
                                </div>
                                <div class="col-12">
                                    <div class="p-3 rounded-3  border d-flex align-items-center justify-content-between flex-wrap gap-2">
                                        <div class="d-flex align-items-center gap-2">
                                            <i class="fa-solid fa-chart-line text-primary"></i>
                                            <span class="small fw-semibold">Estimated Gross Profit Per Item:</span>
                                            <span class="badge bg-success fw-bold fs-6" id="estProfitVal">৳ 0.00</span>
                                        </div>
                                        <div class="small text-muted" id="estMarginVal">Margin: 0%</div>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="d-flex align-items-center justify-content-between mb-1">
                                        <label class="form-label fw-semibold mb-0">Badge / Highlight Tag</label>
                                        <div id="tagDiscountHint" class="small" style="display: none;"></div>
                                    </div>
                                    <div class="input-group">
                                        <input type="text" name="tag" id="formTag" class="form-control" placeholder="e.g. Top Rated, Hot Deal, 20% OFF" oninput="updateBadgeSuggestions()">
                                        <button type="button" class="btn btn-outline-secondary btn-sm" id="clearTagBtn" onclick="clearProductTag()" title="Clear Tag" style="display: none;">
                                            <i class="fa-solid fa-xmark"></i>
                                        </button>
                                    </div>
                                    <div id="badgeSuggestionsWrap" class="mt-2 d-flex flex-wrap align-items-center gap-1.5" style="display: none;">
                                        <span class="text-muted small me-1" style="font-size: 0.75rem;"><i class="fa-solid fa-wand-magic-sparkles text-warning me-1"></i>সাজেস্টেড ব্যাজ:</span>
                                        <div id="badgeSuggestionsList" class="d-inline-flex flex-wrap gap-1.5"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="wizard-step-pane d-none" id="wizardStep-2">
                        <div class="row g-4">
                            <div class="col-lg-7">
                                <div class="card border-0 shadow-sm p-3 p-md-4 mb-4" id="mainThumbCard">
                                    <div class="d-flex align-items-center justify-content-between mb-3 border-bottom pb-2">
                                        <div class="d-flex align-items-center gap-2">
                                            <h6 class="fw-bold mb-0">
                                                <i class="fa-solid fa-image text-primary me-2"></i>Main Product Thumbnail
                                            </h6>
                                            <span class="badge bg-body-tertiary text-muted border paste-target-badge" id="thumbPasteBadge" style="font-size: 0.65rem;">
                                                <i class="fa-solid fa-paste me-1"></i>Hover & Ctrl+V
                                            </span>
                                        </div>
                                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-1 small">
                                            <i class="fa-solid fa-wand-magic-sparkles me-1"></i> Auto-converts to WebP
                                        </span>
                                    </div>
                                    <div class="d-flex flex-column flex-sm-row align-items-center gap-3">
                                        <div id="thumbDropZone" class="border p-2 d-flex align-items-center justify-content-center shadow-sm position-relative rounded-3" style="width: 140px; height: 140px; flex-shrink: 0; cursor: pointer; transition: all 0.2s ease;" onclick="document.getElementById('formMainImageFile').click()">
                                            <img id="formMainImgPreview" src="{{ asset('images/product-placeholder.svg') }}" class="rounded-3" style="max-width: 100%; max-height: 100%; object-fit: contain;" alt="">
                                            <div id="thumbDropOverlay" class="position-absolute inset-0 d-none d-flex flex-column align-items-center justify-content-center rounded-3 text-primary fw-semibold small" style="background: rgba(13, 110, 253, 0.12); border: 2px dashed #0d6efd; inset: 0;">
                                                <i class="fa-solid fa-cloud-arrow-up fa-2x mb-1"></i>
                                                <span>Drop Image</span>
                                            </div>
                                        </div>
                                        <div class="flex-grow-1 w-100">
                                            <label class="form-label small fw-bold">Upload / Drop / Paste</label>
                                            <input type="file" name="main_image_file" id="formMainImageFile" accept="image/*" class="form-control" onchange="handleProductMainFile(this)">
                                            <input type="hidden" name="main_image" id="formMainImage" value="">
                                            <div class="d-flex align-items-center justify-content-between mt-1">
                                                <small class="text-muted"><kbd class="bg-light text-dark border">Ctrl+V</kbd> when hovering here to set thumbnail</small>
                                                <button type="button" class="btn btn-link btn-sm text-danger text-decoration-none p-0" id="clearThumbBtn" style="display: none;" onclick="clearProductMainThumbnail()">
                                                    <i class="fa-solid fa-xmark me-1"></i>Reset
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="card border-0 shadow-sm p-3 p-md-4 rounded-4" id="galleryCard">
                                    <div class="d-flex align-items-center justify-content-between mb-3 border-bottom pb-2">
                                        <div class="d-flex align-items-center gap-2">
                                            <h6 class="fw-bold mb-0">
                                                <i class="fa-solid fa-images text-primary me-2"></i>Product Gallery Images
                                            </h6>
                                            <span class="badge bg-body-tertiary text-muted border paste-target-badge" id="galleryPasteBadge" style="font-size: 0.65rem;">
                                                <i class="fa-solid fa-paste me-1"></i>Hover & Ctrl+V
                                            </span>
                                        </div>
                                        <div class="d-flex gap-2">
                                            <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-3" id="clearAllGalleryBtn" onclick="clearAllGalleryImages()" style="display: none;">
                                                <i class="fa-solid fa-trash me-1"></i> Clear All
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3" onclick="document.getElementById('modalGalleryFileInput').click()">
                                                <i class="fa-solid fa-cloud-arrow-up me-1"></i> Add Photos
                                            </button>
                                        </div>
                                    </div>

                                    <input type="file" id="modalGalleryFileInput" multiple accept="image/*" class="d-none" onchange="handleGalleryFilesSelected(this)">
                                    
                                    <div id="existingGalleryContainer"></div>

                                    <div id="galleryDropZone" class="p-3 rounded-4 border position-relative" style="min-height: 140px; transition: all 0.2s ease;">
                                        <div id="galleryDropOverlay" class="position-absolute inset-0 d-none d-flex flex-column align-items-center justify-content-center rounded-4 text-primary fw-bold" style="background: rgba(13, 110, 253, 0.12); border: 2px dashed #0d6efd; inset: 0; z-index: 10;">
                                            <i class="fa-solid fa-cloud-arrow-up fa-3x mb-2"></i>
                                            <span>Drop photos to add to gallery</span>
                                        </div>

                                        <div class="d-flex align-items-center justify-content-between mb-2">
                                            <span class="small fw-semibold text-secondary">Photos List (<span id="galleryCountBadge">0</span> images)</span>
                                            <small class="text-muted" style="font-size: 0.72rem;">Supports JPG, PNG, WEBP, GIF · Hover anywhere & <kbd>Ctrl+V</kbd></small>
                                        </div>
                                        <div id="galleryLivePreviewGrid" class="d-flex flex-wrap gap-2.5 align-items-center">
                                            <div class="w-100 text-center py-4 text-muted small fst-italic" id="galleryEmptyText" onclick="document.getElementById('modalGalleryFileInput').click()" style="cursor: pointer;">
                                                <i class="fa-regular fa-images fa-2x mb-2 d-block text-secondary opacity-50"></i>
                                                Click "Add Photos", drag files here, or hover & paste (<kbd>Ctrl+V</kbd>) from clipboard
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="card border-0 shadow-sm p-3 p-md-4 rounded-4 mt-4">
                                    <div class="d-flex align-items-center justify-content-between mb-3 border-bottom pb-2">
                                        <div>
                                            <h6 class="fw-bold mb-0">
                                                <i class="fa-brands fa-youtube text-danger me-2"></i>YouTube Video
                                            </h6>
                                            <small class="text-muted">ইউটিউব ভিডিও বা শর্টস লিংক দিন (কাস্টমার প্রোডাক্ট পেইজ থেকে সরাসরি দেখতে পারবে)</small>
                                        </div>
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-2.5 py-1 small">
                                            <i class="fa-brands fa-youtube me-1"></i> Video & Promotion
                                        </span>
                                    </div>
                                    <div>
                                        <label class="form-label small fw-bold">YouTube Video URL / Link</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light text-danger"><i class="fa-brands fa-youtube"></i></span>
                                            <input type="url" name="video_url" id="formVideoUrl" class="form-control" placeholder="e.g. https://www.youtube.com/watch?v=... বা https://youtu.be/..." oninput="updateModalVideoPreview(this.value)">
                                            <button type="button" class="btn btn-outline-secondary" onclick="document.getElementById('formVideoUrl').value=''; updateModalVideoPreview('');" title="Clear">
                                                <i class="fa-solid fa-xmark"></i>
                                            </button>
                                        </div>
                                        <small class="text-muted d-block mt-1" style="font-size: 0.72rem;">সাপোর্ট করে: সাধারণ ভিডিও লিংক (watch?v=), শর্ট লিংক (youtu.be), Shorts (youtube.com/shorts/) ইত্যাদি।</small>
                                        
                                        <div id="modalVideoPreviewWrapper" class="mt-3 d-none">
                                            <div class="p-2 border rounded-3 bg-light position-relative">
                                                <div class="ratio ratio-16x9 rounded-2 overflow-hidden shadow-sm">
                                                    <iframe id="modalVideoIframe" src="" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-5">
                                <div class="card border-0 shadow-sm p-3 p-md-4 rounded-4 d-flex flex-column" style="position: sticky; top: 0;">
                                    <div class="d-flex align-items-center justify-content-between mb-3 border-bottom pb-2">
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="rounded-circle bg-primary-subtle text-primary d-flex align-items-center justify-content-center" style="width: 36px; height: 36px; font-size: 1rem;">
                                                <i class="fa-solid fa-sliders"></i>
                                            </div>
                                            <div>
                                                <h6 class="fw-bold mb-0">Status & Visibility</h6>
                                                <small class="text-muted" style="font-size: 0.72rem;">Live storefront & merchandising controls</small>
                                            </div>
                                        </div>
                                        <div id="statusLiveBadgeWrap">
                                            <span id="statusLiveBadge" class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-1 small fw-semibold d-inline-flex align-items-center gap-1.5">
                                                <span class="status-live-dot"></span>
                                                <span>Live on Store</span>
                                            </span>
                                        </div>
                                    </div>

                                    <div class="d-flex flex-column gap-2.5 mb-3">
                                        <div class="status-toggle-card active-live" id="cardIsActive" onclick="toggleStatusSwitch('formIsActive')">
                                            <div class="d-flex align-items-center justify-content-between">
                                                <div class="d-flex align-items-center gap-2.5">
                                                    <div class="status-icon-box bg-success-subtle text-success">
                                                        <i class="fa-solid fa-store"></i>
                                                    </div>
                                                    <div>
                                                        <div class="d-flex align-items-center gap-1.5">
                                                            <span class="fw-bold text-dark" style="font-size: 0.9rem;">Active & Published</span>
                                                            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2 py-0.5" style="font-size: 0.65rem;">Live</span>
                                                        </div>
                                                        <div class="text-muted" style="font-size: 0.73rem;">Show product to customers immediately</div>
                                                    </div>
                                                </div>
                                                <div class="form-check form-switch mb-0" onclick="event.stopPropagation()">
                                                    <input class="form-check-input" type="checkbox" name="is_active" id="formIsActive" value="1" checked onchange="updateStatusCardVisuals()">
                                                </div>
                                            </div>
                                        </div>

                                        <div class="status-toggle-card" id="cardIsFeatured" onclick="toggleStatusSwitch('formIsFeatured')">
                                            <div class="d-flex align-items-center justify-content-between">
                                                <div class="d-flex align-items-center gap-2.5">
                                                    <div class="status-icon-box bg-warning-subtle text-warning">
                                                        <i class="fa-solid fa-star"></i>
                                                    </div>
                                                    <div>
                                                        <div class="d-flex align-items-center gap-1.5">
                                                            <span class="fw-bold text-dark" style="font-size: 0.9rem;">Featured Product</span>
                                                            <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill px-2 py-0.5" style="font-size: 0.65rem;">Homepage</span>
                                                        </div>
                                                        <div class="text-muted" style="font-size: 0.73rem;">Pin to featured section on landing page</div>
                                                    </div>
                                                </div>
                                                <div class="form-check form-switch mb-0" onclick="event.stopPropagation()">
                                                    <input class="form-check-input" type="checkbox" name="is_featured" id="formIsFeatured" value="1" onchange="updateStatusCardVisuals()">
                                                </div>
                                            </div>
                                        </div>

                                        <div class="status-toggle-card" id="cardIsFlashDeal" onclick="toggleStatusSwitch('formIsFlashDeal')">
                                            <div class="d-flex align-items-center justify-content-between">
                                                <div class="d-flex align-items-center gap-2.5">
                                                    <div class="status-icon-box bg-danger-subtle text-danger">
                                                        <i class="fa-solid fa-bolt-lightning"></i>
                                                    </div>
                                                    <div>
                                                        <div class="d-flex align-items-center gap-1.5">
                                                            <span class="fw-bold text-dark" style="font-size: 0.9rem;">Flash Deal Sale</span>
                                                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-2 py-0.5" style="font-size: 0.65rem;">Promotion</span>
                                                        </div>
                                                        <div class="text-muted" style="font-size: 0.73rem;">Display in flash sale countdown section</div>
                                                    </div>
                                                </div>
                                                <div class="form-check form-switch mb-0" onclick="event.stopPropagation()">
                                                    <input class="form-check-input" type="checkbox" name="is_flash_deal" id="formIsFlashDeal" value="1" onchange="updateStatusCardVisuals()">
                                                </div>
                                            </div>
                                        </div>

                                        <div class="status-toggle-card" id="cardIsFreeShipping" onclick="toggleStatusSwitch('formIsFreeShipping')">
                                            <div class="d-flex align-items-center justify-content-between">
                                                <div class="d-flex align-items-center gap-2.5">
                                                    <div class="status-icon-box bg-info-subtle text-info">
                                                        <i class="fa-solid fa-truck-fast"></i>
                                                    </div>
                                                    <div>
                                                        <div class="d-flex align-items-center gap-1.5">
                                                            <span class="fw-bold text-dark" style="font-size: 0.9rem;">Free Shipping</span>
                                                            <span class="badge bg-info-subtle text-info border border-info-subtle rounded-pill px-2 py-0.5" style="font-size: 0.65rem;">0৳ Delivery</span>
                                                        </div>
                                                        <div class="text-muted" style="font-size: 0.73rem;">Display 'Free Shipping' badge on store</div>
                                                    </div>
                                                </div>
                                                <div class="form-check form-switch mb-0" onclick="event.stopPropagation()">
                                                    <input class="form-check-input" type="checkbox" name="is_free_shipping" id="formIsFreeShipping" value="1" onchange="updateStatusCardVisuals()">
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="p-3 rounded-3 border bg-light">
                                        <div class="d-flex align-items-center justify-content-between mb-1.5">
                                            <span class="small fw-bold text-secondary text-uppercase" style="font-size: 0.68rem; letter-spacing: 0.5px;">Storefront Badges Preview</span>
                                            <span class="badge border small" style="font-size: 0.65rem;">Real-time</span>
                                        </div>
                                        <div id="statusPreviewBadges" class="d-flex flex-wrap gap-1.5">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="wizard-step-pane d-none" id="wizardStep-3">
                        <div class="card border-0 shadow-sm p-3 p-md-4 mb-4 rounded-4 ">
                            <div class="d-flex align-items-center justify-content-between mb-3 border-bottom pb-2">
                                <div>
                                    <h6 class="fw-bold mb-0 ">
                                        <i class="fa-solid fa-palette text-primary me-2"></i> Color / Variant Matrix
                                    </h6>
                                    <small class="text-muted">Specify distinct color choices, prices, stock, and thumbnail preview for each</small>
                                </div>
                                <button type="button" class="btn btn-sm btn-success rounded-pill px-3" onclick="addModalVariantRow()">
                                    <i class="fa-solid fa-plus me-1"></i> Add Variant
                                </button>
                            </div>

                            <div class="p-2 mb-3 bg-light rounded-3 border">
                                <div class="row g-2 align-items-center">
                                    <div class="col-md-4">
                                        <input type="text" id="fastVarName" class="form-control form-control-sm" placeholder="Color/Option (e.g. Matte Black)" onkeydown="if(event.key==='Enter'){event.preventDefault();addFastVariant();}">
                                    </div>
                                    <div class="col-md-3">
                                        <input type="number" step="0.01" id="fastVarPrice" class="form-control form-control-sm" placeholder="Price (৳)" onkeydown="if(event.key==='Enter'){event.preventDefault();addFastVariant();}">
                                    </div>
                                    <div class="col-md-3">
                                        <input type="number" id="fastVarStock" class="form-control form-control-sm" placeholder="Stock" value="50" onkeydown="if(event.key==='Enter'){event.preventDefault();addFastVariant();}">
                                    </div>
                                    <div class="col-md-2">
                                        <button type="button" class="btn btn-sm btn-primary w-100 rounded-pill" onclick="addFastVariant()">
                                            <i class="fa-solid fa-plus me-1"></i> Add
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-bordered table-sm align-middle mb-0" id="modalVariantTable">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="width: 24%;">Color / Name</th>
                                            <th style="width: 18%;">Price (৳)</th>
                                            <th style="width: 40%;">Variant Image (Upload & Preview)</th>
                                            <th style="width: 13%;">Stock</th>
                                            <th style="width: 5%; text-align: center;"></th>
                                        </tr>
                                    </thead>
                                    <tbody id="modalVariantTableBody">
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="card border-0 shadow-sm p-3 p-md-4 rounded-4 ">
                            <div class="d-flex align-items-center justify-content-between mb-3 border-bottom pb-2">
                                <div>
                                    <h6 class="fw-bold mb-0 ">
                                        <i class="fa-solid fa-list-check text-primary me-2"></i>Specifications
                                    </h6>
                                    <small class="text-muted">Add Key/Value pairs (e.g. Battery: 500mAh, Color: Red, Material: Steel)</small>
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3" id="specBulkModeBtn" onclick="toggleSpecBulkMode()">
                                    <i class="fa-solid fa-paste me-1"></i> Bulk Paste
                                </button>
                            </div>

                            <div id="specBulkPanel" class="d-none mb-3">
                                <div class="p-3 rounded-3 border border-warning-subtle bg-warning-subtle">
                                    <label class="form-label small fw-semibold text-warning-emphasis mb-1"><i class="fa-solid fa-wand-magic-sparkles me-1"></i>Bulk Paste Mode</label>
                                    <textarea id="specBulkTextarea" class="form-control form-control-sm font-monospace mb-2" rows="6" placeholder="Paste raw specs here, one per line:&#10;Color: Matte Black&#10;Battery: 5000mAh&#10;Weight: 250g&#10;Material: Aluminium&#10;&#10;Supports: Key: Value, Key - Value, or tab-separated"></textarea>
                                    <div class="d-flex gap-2">
                                        <button type="button" class="btn btn-sm btn-warning fw-semibold rounded-pill px-3" onclick="parseBulkSpecs()">
                                            <i class="fa-solid fa-wand-magic-sparkles me-1"></i> Parse into Rows
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3" onclick="toggleSpecBulkMode()">
                                            <i class="fa-solid fa-table-list me-1"></i> Table Mode
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div id="modalSpecContainer" class="d-flex flex-column gap-2 mb-3">
                            </div>

                            <div class="pt-2 border-top d-flex align-items-center gap-2">
                                <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3 shadow-sm" onclick="addModalSpecRow()">
                                    <i class="fa-solid fa-plus me-1"></i> Add Row
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-3" onclick="clearAllSpecRows()" title="Clear all spec rows">
                                    <i class="fa-solid fa-trash me-1"></i> Clear All
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="wizard-step-pane d-none" id="wizardStep-4">
                        <div class="card border-0 shadow-sm p-3 mb-4 rounded-4 bg-primary-subtle border-primary-subtle">
                            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-2">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                                        <i class="fa-solid fa-wand-magic-sparkles"></i>
                                    </div>
                                    <div>
                                        <h6 class="fw-bold mb-0 text-primary-emphasis">Smart Paste & Auto-Extract</h6>
                                        <small class="text-muted">Paste unformatted supplier/catalog text to auto-populate Title, Specs, Summary & SEO</small>
                                    </div>
                                </div>
                                <button type="button" class="btn btn-sm btn-primary rounded-pill px-3 shadow-sm fw-semibold" onclick="parseSmartRawText()">
                                    <i class="fa-solid fa-bolt me-1"></i> Auto-Extract
                                </button>
                            </div>
                            <textarea id="smartRawInput" class="form-control form-control-sm font-monospace" rows="3" placeholder="Paste unformatted description or raw product page text here... (e.g. copied from Daraz, Amazon, or supplier spec list)"></textarea>
                        </div>

                        <div class="row g-4">
                            <div class="col-lg-7">
                                <div class="card border-0 shadow-sm p-3 p-md-4 rounded-4 h-100">
                                    <div class="d-flex align-items-center justify-content-between mb-3 border-bottom pb-2 flex-wrap gap-2">
                                        <h6 class="fw-bold mb-0">
                                            <i class="fa-solid fa-align-left text-primary me-2"></i>Descriptions
                                        </h6>
                                        <div id="aiDescriptionStatusBadge"></div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Short Summary</label>
                                        <textarea name="short_desc" id="formShortDesc" class="form-control" rows="2" placeholder="Brief 1-2 sentence highlight for quick view modal..."></textarea>
                                    </div>
                                    <div>
                                        <label class="form-label fw-semibold">Detailed Full Description</label>
                                        <textarea name="description" id="formDesc" class="form-control font-monospace" rows="8" placeholder="Comprehensive product specifications, features, warranty, and benefits..."></textarea>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-5">
                                <div class="card border-0 shadow-sm p-3 p-md-4 rounded-4 h-100">
                                    <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-3 flex-wrap gap-2">
                                        <h6 class="fw-bold mb-0">
                                            <i class="fa-solid fa-magnifying-glass text-primary me-2"></i>SEO & Metadata
                                        </h6>
                                        <button type="button" id="btn-generate-ai" class="btn btn-sm btn-primary rounded-pill px-3 shadow-sm" onclick="window.generateAISEO(true)">
                                            <i class="fa-solid fa-wand-magic-sparkles me-1"></i> AI Auto-Fill
                                        </button>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label small fw-semibold">URL Slug</label>
                                        <input type="text" name="slug" id="formSlug" class="form-control form-control-sm" placeholder="e.g. product-name-here">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label small fw-semibold">Meta Title</label>
                                        <input type="text" name="meta_title" id="formMetaTitle" class="form-control form-control-sm" placeholder="Google Search Title (<60 chars)">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label small fw-semibold">Meta Description</label>
                                        <textarea name="meta_description" id="formMetaDesc" class="form-control form-control-sm" rows="3" placeholder="Google snippet description (<160 chars)"></textarea>
                                    </div>
                                    <div>
                                        <label class="form-label small fw-semibold">Meta Keywords</label>
                                        <input type="text" name="meta_keywords" id="formMetaKeywords" class="form-control form-control-sm" placeholder="e.g. wireless earbuds, bluetooth headphones, zippy">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                <div class="modal-footer border-top  px-4 py-3 d-flex justify-content-between align-items-center">
                    <div>
                        <button type="button" class="btn btn-outline-secondary px-3 rounded-pill" id="wizardPrevBtn" onclick="navigateWizard(-1)" style="display: none;">
                            <i class="fa-solid fa-arrow-left me-1"></i> Previous
                        </button>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-light px-3 rounded-pill text-muted" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-primary px-4 rounded-pill fw-semibold shadow-sm" id="wizardNextBtn" onclick="navigateWizard(1)">
                            Next <i class="fa-solid fa-arrow-right ms-1"></i>
                        </button>
                        <button type="submit" class="btn btn-success px-4 rounded-pill fw-bold shadow-sm" id="modalSaveBtn" style="display: none;">
                            <i class="fa-solid fa-check me-1"></i> Save Product
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="csvImportModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0">
            <form action="{{ route('admin.products.import_csv') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header border-0">
                    <h5 class="fw-bold mb-0">Bulk Import Products (CSV)</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted small mb-3">Upload a CSV formatted file with columns: <code>title, category_id, sku, price, old_price, stock_qty, tag, main_image</code></p>
                    <div class="mb-3">
                        <label class="form-label">Select CSV File</label>
                        <input type="file" name="csv_file" class="form-control" accept=".csv, .txt" required>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fa-solid fa-upload me-1"></i> Upload & Import
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
    let productModalInstance = null;
    let productsTable = null;

    function getProductModal() {
        const modalEl = document.getElementById('productModal');
        return modalEl ? bootstrap.Modal.getOrCreateInstance(modalEl) : null;
    }

    function initProductsIndex() {
        const tableEl = document.getElementById('productsTable');
        if (!tableEl) return;

        productModalInstance = getProductModal();

        if ($.fn.DataTable.isDataTable('#productsTable')) {
            $('#productsTable').DataTable().clear().destroy();
        }

        productsTable = $('#productsTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('admin.products.index') }}",
                data: function (d) {
                    d.category_id = $('#filterCategory').val();
                    d.stock_status = $('#filterStockStatus').val();
                }
            },
            columns: [
                { data: 'product_details', name: 'products.title' },
                { data: 'category', name: 'categories.name' },
                { data: 'price_formatted', name: 'products.price' },
                { data: 'inventory_badge', name: 'products.stock_qty' },
                { data: 'status_toggle', name: 'products.is_active', orderable: false, searchable: false, className: 'text-center' },
                { data: 'actions', orderable: false, searchable: false, className: 'text-center' }
            ],
            lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
            pageLength: 10,
            order: [[0, 'desc']],
            language: {
                search: "_INPUT_",
                searchPlaceholder: "Search products...",
                lengthMenu: "Show _MENU_ entries",
                processing: '<div class="spinner-border spinner-border-sm text-primary" role="status"></div> Loading products...'
            }
        });

        $('#filterCategory, #filterStockStatus').off('change').on('change', function () {
            if (productsTable) productsTable.draw();
        });

        initDragDropAndPaste();
        updateStatusCardVisuals();
    }

    document.addEventListener('turbo:load', initProductsIndex);

    let currentWizardStep = 1;
    const totalWizardSteps = 4;
    let currentHoveredImageZone = 'gallery';
    let currentMainThumbFile = null;

    function toggleStatusSwitch(switchId) {
        const sw = document.getElementById(switchId);
        if (sw) {
            sw.checked = !sw.checked;
            updateStatusCardVisuals();
        }
    }

    function updateStatusCardVisuals() {
        const activeSw = document.getElementById('formIsActive');
        const featuredSw = document.getElementById('formIsFeatured');
        const flashSw = document.getElementById('formIsFlashDeal');
        const shippingSw = document.getElementById('formIsFreeShipping');

        const cardActive = document.getElementById('cardIsActive');
        const cardFeatured = document.getElementById('cardIsFeatured');
        const cardFlash = document.getElementById('cardIsFlashDeal');
        const cardShipping = document.getElementById('cardIsFreeShipping');

        const liveBadge = document.getElementById('statusLiveBadge');
        const badgesPreview = document.getElementById('statusPreviewBadges');

        if (cardActive && activeSw) {
            if (activeSw.checked) {
                cardActive.classList.add('active-live');
            } else {
                cardActive.classList.remove('active-live');
            }
        }

        if (cardFeatured && featuredSw) {
            if (featuredSw.checked) {
                cardFeatured.classList.add('active-featured');
            } else {
                cardFeatured.classList.remove('active-featured');
            }
        }

        if (cardFlash && flashSw) {
            if (flashSw.checked) {
                cardFlash.classList.add('active-flash');
            } else {
                cardFlash.classList.remove('active-flash');
            }
        }

        if (cardShipping && shippingSw) {
            if (shippingSw.checked) {
                cardShipping.classList.add('active-shipping');
            } else {
                cardShipping.classList.remove('active-shipping');
            }
        }

        if (liveBadge && activeSw) {
            if (activeSw.checked) {
                liveBadge.className = 'badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-1 small fw-semibold d-inline-flex align-items-center gap-1.5';
                liveBadge.innerHTML = '<span class="status-live-dot"></span><span>Live on Store</span>';
            } else {
                liveBadge.className = 'badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill px-2.5 py-1 small fw-semibold d-inline-flex align-items-center gap-1.5';
                liveBadge.innerHTML = '<span class="status-draft-dot"></span><span>Draft / Hidden</span>';
            }
        }

        if (badgesPreview) {
            let badgesHtml = '';
            if (activeSw && activeSw.checked) {
                badgesHtml += '<span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2 py-0.5 small fw-semibold"><i class="fa-solid fa-circle-check me-1"></i>Visible Online</span>';
            } else {
                badgesHtml += '<span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill px-2 py-0.5 small fw-semibold"><i class="fa-solid fa-eye-slash me-1"></i>Hidden from Store</span>';
            }

            if (featuredSw && featuredSw.checked) {
                badgesHtml += '<span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill px-2 py-0.5 small fw-semibold"><i class="fa-solid fa-star me-1"></i>Featured Section</span>';
            }

            if (flashSw && flashSw.checked) {
                badgesHtml += '<span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-2 py-0.5 small fw-semibold"><i class="fa-solid fa-bolt-lightning me-1"></i>Flash Deal</span>';
            }

            if (shippingSw && shippingSw.checked) {
                badgesHtml += '<span class="badge bg-info-subtle text-info border border-info-subtle rounded-pill px-2 py-0.5 small fw-semibold"><i class="fa-solid fa-truck-fast me-1"></i>Free Shipping</span>';
            }

            badgesPreview.innerHTML = badgesHtml;
        }
    }

    function updateHoverZoneVisuals() {
        const thumbCard = document.getElementById('mainThumbCard');
        const thumbDropZone = document.getElementById('thumbDropZone');
        const thumbBadge = document.getElementById('thumbPasteBadge');
        const galleryCard = document.getElementById('galleryCard');
        const galleryDropZone = document.getElementById('galleryDropZone');
        const galleryBadge = document.getElementById('galleryPasteBadge');

        if (currentHoveredImageZone === 'thumb') {
            if (thumbCard) thumbCard.classList.add('dropzone-hover-card');
            if (thumbDropZone) thumbDropZone.classList.add('dropzone-hover-active');
            if (thumbBadge) {
                thumbBadge.classList.add('active-target');
                thumbBadge.classList.remove('bg-body-tertiary', 'text-muted');
            }
            if (galleryCard) galleryCard.classList.remove('dropzone-hover-card');
            if (galleryDropZone) galleryDropZone.classList.remove('dropzone-hover-active');
            if (galleryBadge) {
                galleryBadge.classList.remove('active-target');
                galleryBadge.classList.add('bg-body-tertiary', 'text-muted');
            }
        } else {
            if (galleryCard) galleryCard.classList.add('dropzone-hover-card');
            if (galleryDropZone) galleryDropZone.classList.add('dropzone-hover-active');
            if (galleryBadge) {
                galleryBadge.classList.add('active-target');
                galleryBadge.classList.remove('bg-body-tertiary', 'text-muted');
            }
            if (thumbCard) thumbCard.classList.remove('dropzone-hover-card');
            if (thumbDropZone) thumbDropZone.classList.remove('dropzone-hover-active');
            if (thumbBadge) {
                thumbBadge.classList.remove('active-target');
                thumbBadge.classList.add('bg-body-tertiary', 'text-muted');
            }
        }
    }

    function updateWizardUI() {
        for (let i = 1; i <= totalWizardSteps; i++) {
            const pane = document.getElementById(`wizardStep-${i}`);
            const tab = document.getElementById(`stepTab-${i}`);
            if (pane) {
                if (i === currentWizardStep) {
                    pane.classList.remove('d-none');
                    pane.classList.add('active');
                } else {
                    pane.classList.add('d-none');
                    pane.classList.remove('active');
                }
            }
            if (tab) {
                if (i === currentWizardStep) {
                    tab.classList.add('active', 'text-primary');
                    tab.classList.remove('text-muted');
                    tab.querySelector('.step-num').classList.add('bg-primary', 'text-white');
                    tab.querySelector('.step-num').classList.remove('bg-light', 'text-muted');
                } else if (i < currentWizardStep) {
                    tab.classList.remove('active', 'text-primary');
                    tab.classList.add('text-success');
                    tab.querySelector('.step-num').classList.add('bg-success-subtle', 'text-success');
                    tab.querySelector('.step-num').classList.remove('bg-primary', 'text-white', 'bg-light', 'text-muted');
                } else {
                    tab.classList.remove('active', 'text-primary', 'text-success');
                    tab.classList.add('text-muted');
                    tab.querySelector('.step-num').classList.add('bg-light', 'text-muted');
                    tab.querySelector('.step-num').classList.remove('bg-primary', 'text-white', 'bg-success-subtle', 'text-success');
                }
            }
        }

        const prevBtn = document.getElementById('wizardPrevBtn');
        const nextBtn = document.getElementById('wizardNextBtn');
        const saveBtn = document.getElementById('modalSaveBtn');

        if (prevBtn) prevBtn.style.display = currentWizardStep > 1 ? 'inline-block' : 'none';
        if (nextBtn) nextBtn.style.display = currentWizardStep < totalWizardSteps ? 'inline-block' : 'none';
        if (saveBtn) saveBtn.style.display = currentWizardStep === totalWizardSteps ? 'inline-block' : 'none';
    }

    function goToWizardStep(step) {
        if (step > currentWizardStep) {
            if (!validateWizardStep(currentWizardStep)) return;
            if (currentWizardStep === 1 && step > 1) {
                triggerAiSeoAndDescriptionAutoGeneration();
            }
        }
        currentWizardStep = step;
        updateWizardUI();
    }

    function navigateWizard(direction) {
        const nextStep = currentWizardStep + direction;
        if (direction > 0) {
            if (!validateWizardStep(currentWizardStep)) return;
            if (currentWizardStep === 1) {
                triggerAiSeoAndDescriptionAutoGeneration();
            }
        }
        if (nextStep >= 1 && nextStep <= totalWizardSteps) {
            currentWizardStep = nextStep;
            updateWizardUI();
        }
    }

    function validateWizardStep(step) {
        if (step === 1) {
            const title = document.getElementById('formTitle');
            const cat = document.getElementById('formCategory');
            const price = document.getElementById('formPrice');
            const stock = document.getElementById('formStock');

            if (!title.value.trim()) {
                Swal.fire({ icon: 'warning', title: 'Product Title Required', text: 'Please enter a product title to proceed.', timer: 2000, showConfirmButton: false });
                title.focus();
                return false;
            }
            if (!cat.value) {
                Swal.fire({ icon: 'warning', title: 'Category Required', text: 'Please choose a category.', timer: 2000, showConfirmButton: false });
                cat.focus();
                return false;
            }
            if (!price.value || parseFloat(price.value) < 0) {
                Swal.fire({ icon: 'warning', title: 'Price Required', text: 'Please specify a valid base price.', timer: 2000, showConfirmButton: false });
                price.focus();
                return false;
            }
            if (!stock.value || parseInt(stock.value) < 0) {
                Swal.fire({ icon: 'warning', title: 'Stock Required', text: 'Please enter a valid stock quantity.', timer: 2000, showConfirmButton: false });
                stock.focus();
                return false;
            }
        }
        return true;
    }

    function previewMainModalImg(url) {
        const preview = document.getElementById('formMainImgPreview');
        const clearBtn = document.getElementById('clearThumbBtn');
        if (preview) {
            preview.src = url || '{{ asset('images/product-placeholder.svg') }}';
            preview.onerror = () => { preview.onerror = null; preview.src = '{{ asset('images/product-placeholder.svg') }}'; };
        }
        if (clearBtn) {
            clearBtn.style.display = (url && url !== '{{ asset('images/product-placeholder.svg') }}') ? 'inline-block' : 'none';
        }
    }

    function setProductMainImageFile(file) {
        if (!file || !file.type.startsWith('image/')) return;
        currentMainThumbFile = file;
        const input = document.getElementById('formMainImageFile');
        const dt = new DataTransfer();
        dt.items.add(file);
        if (input) input.files = dt.files;
        const objectUrl = URL.createObjectURL(file);
        previewMainModalImg(objectUrl);
    }

    function handleProductMainFile(input) {
        if (input.files && input.files[0]) {
            setProductMainImageFile(input.files[0]);
        }
    }

    function clearProductMainThumbnail() {
        currentMainThumbFile = null;
        const input = document.getElementById('formMainImageFile');
        const hidden = document.getElementById('formMainImage');
        if (input) input.value = '';
        if (hidden) hidden.value = '';
        previewMainModalImg('');
    }

    function calculateProfitPreview() {
        const costInput = document.getElementById('formCostPrice');
        const priceInput = document.getElementById('formPrice');
        const profitEl = document.getElementById('estProfitVal');
        const marginEl = document.getElementById('estMarginVal');

        const cost = parseFloat(costInput.value) || 0;
        const price = parseFloat(priceInput.value) || 0;

        if (price > 0) {
            const profit = price - cost;
            const margin = cost > 0 ? ((profit / price) * 100).toFixed(1) : 100;

            if (profitEl) {
                profitEl.innerText = '৳ ' + profit.toFixed(2);
                if (profit >= 0) {
                    profitEl.className = 'badge bg-success fw-bold fs-6';
                } else {
                    profitEl.className = 'badge bg-danger fw-bold fs-6';
                }
            }
            if (marginEl) {
                marginEl.innerText = `Gross Margin: ${margin}%`;
                marginEl.className = profit >= 0 ? 'small text-success fw-semibold' : 'small text-danger fw-semibold';
            }
        } else {
            if (profitEl) {
                profitEl.innerText = '৳ 0.00';
                profitEl.className = 'badge bg-secondary fw-bold fs-6';
            }
            if (marginEl) {
                marginEl.innerText = 'Margin: 0%';
                marginEl.className = 'small text-muted';
            }
        }

        updateBadgeSuggestions();
    }

    function updateBadgeSuggestions() {
        const priceInput = document.getElementById('formPrice');
        const oldPriceInput = document.getElementById('formOldPrice');
        const tagInput = document.getElementById('formTag');
        const wrapEl = document.getElementById('badgeSuggestionsWrap');
        const listEl = document.getElementById('badgeSuggestionsList');
        const hintEl = document.getElementById('tagDiscountHint');
        const clearBtn = document.getElementById('clearTagBtn');

        if (!priceInput || !oldPriceInput || !wrapEl || !listEl) return;

        const price = parseFloat(priceInput.value) || 0;
        const oldPrice = parseFloat(oldPriceInput.value) || 0;
        const currentTag = tagInput ? tagInput.value.trim() : '';

        if (clearBtn) {
            clearBtn.style.display = currentTag ? 'inline-block' : 'none';
        }

        let suggestions = [];

        if (oldPrice > price && price > 0) {
            const discountAmount = Math.round(oldPrice - price);
            const discountPercent = Math.round(((oldPrice - price) / oldPrice) * 100);

            if (discountPercent > 0) {
                suggestions.push({ label: `${discountPercent}% OFF`, value: `${discountPercent}% OFF`, theme: 'danger' });
                suggestions.push({ label: `-${discountPercent}%`, value: `-${discountPercent}%`, theme: 'danger' });
                suggestions.push({ label: `${discountPercent}% ছাড়`, value: `${discountPercent}% ছাড়`, theme: 'danger' });
            }
            if (discountAmount > 0) {
                suggestions.push({ label: `৳${discountAmount} OFF`, value: `৳${discountAmount} OFF`, theme: 'warning' });
                suggestions.push({ label: `৳${discountAmount} ছাড়`, value: `৳${discountAmount} ছাড়`, theme: 'warning' });
            }
            suggestions.push({ label: 'Hot Deal', value: 'Hot Deal', theme: 'dark' });
            suggestions.push({ label: 'Special Offer', value: 'Special Offer', theme: 'primary' });

            if (hintEl) {
                hintEl.innerHTML = `<span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1"><i class="fa-solid fa-fire me-1"></i>${discountPercent}% ছাড় (৳${discountAmount} সাশ্রয়)</span>`;
                hintEl.style.display = 'inline-block';
            }
        } else {
            suggestions.push({ label: 'Hot Deal', value: 'Hot Deal', theme: 'danger' });
            suggestions.push({ label: 'Best Seller', value: 'Best Seller', theme: 'warning' });
            suggestions.push({ label: 'New Arrival', value: 'New Arrival', theme: 'primary' });
            suggestions.push({ label: 'Limited Stock', value: 'Limited Stock', theme: 'dark' });

            if (hintEl) {
                hintEl.innerHTML = '';
                hintEl.style.display = 'none';
            }
        }

        listEl.innerHTML = '';
        suggestions.forEach(item => {
            const btn = document.createElement('button');
            btn.type = 'button';
            const isSelected = currentTag.toLowerCase() === item.value.toLowerCase();

            btn.className = `btn btn-sm rounded-pill px-2.5 py-0.5 fw-semibold transition-all m-1 ${isSelected ? `btn-${item.theme} text-white shadow-sm` : `btn-outline-${item.theme}`}`;
            btn.style.fontSize = '0.74rem';
            btn.innerText = item.label;
            btn.onclick = function() {
                if (tagInput) {
                    if (tagInput.value.trim().toLowerCase() === item.value.toLowerCase()) {
                        tagInput.value = '';
                    } else {
                        tagInput.value = item.value;
                    }
                    updateBadgeSuggestions();
                }
            };
            listEl.appendChild(btn);
        });

        wrapEl.style.display = 'flex';
    }

    function clearProductTag() {
        const tagInput = document.getElementById('formTag');
        if (tagInput) {
            tagInput.value = '';
            updateBadgeSuggestions();
        }
    }

    let isSkuManuallyEdited = false;
    let isCategoryManuallySelected = false;

    let availableCategories = [];
    function initAvailableCategories() {
        const select = document.getElementById('formCategory');
        if (!select) return;
        availableCategories = [];
        const options = select.querySelectorAll('option');
        options.forEach(opt => {
            if (opt.value && opt.value !== '') {
                const rawText = opt.textContent.replace(/^[↳●\s]+/, '').trim();
                const englishPart = rawText.split('(')[0].trim();
                const tokens = rawText.toLowerCase()
                    .replace(/[^a-z0-9\u0980-\u09FF\s]/g, ' ')
                    .split(/\s+/)
                    .filter(t => t.length >= 2);
                
                availableCategories.push({
                    id: opt.value,
                    name: englishPart || rawText,
                    fullName: rawText,
                    tokens: tokens
                });
            }
        });
    }

    function generateSkuFromTitle(title) {
        if (!title || !title.trim()) return '';
        const clean = title.trim().replace(/[^a-zA-Z0-9\s]/g, '');
        const words = clean.split(/\s+/).filter(w => w.length > 0);
        let prefix = 'ZB';
        if (words.length >= 2) {
            prefix += '-' + (words[0].substring(0, 3) + words[1].substring(0, 3)).toUpperCase();
        } else if (words.length === 1) {
            prefix += '-' + words[0].substring(0, 6).toUpperCase();
        } else {
            prefix += '-PROD';
        }
        const rand = Math.floor(100 + Math.random() * 900);
        return `${prefix}-${rand}`;
    }

    function handleTitleChanged(title) {
        const modalId = document.getElementById('modalProdId').value;
        const skuInput = document.getElementById('formSku');
        
        if (!modalId && !isSkuManuallyEdited && skuInput) {
            skuInput.value = generateSkuFromTitle(title);
        }

        suggestCategoryFromTitle(title);
    }

    function normalizeWord(word) {
        if (!word) return '';
        let w = word.toLowerCase().trim();
        if (w.endsWith('ies') && w.length > 4) return w.slice(0, -3) + 'y';
        if (w.endsWith('es') && w.length > 4) return w.slice(0, -2);
        if (w.endsWith('s') && !w.endsWith('ss') && w.length > 3) return w.slice(0, -1);
        return w;
    }

    function suggestCategoryFromTitle(title) {
        const box = document.getElementById('categorySuggestionsBox');
        const badge = document.getElementById('catSuggestStatus');
        const select = document.getElementById('formCategory');
        if (!box || !select) return;

        if (!availableCategories || availableCategories.length === 0) {
            initAvailableCategories();
        }

        if (!title || title.trim().length < 2) {
            box.innerHTML = '';
            if (badge) badge.style.display = 'none';
            return;
        }

        const inputClean = title.toLowerCase().replace(/[^a-z0-9\u0980-\u09FF\s]/g, ' ');
        const inputWords = inputClean.split(/\s+/).filter(w => w.length >= 2);
        const stemmedInputWords = inputWords.map(normalizeWord);

        const scored = availableCategories.map(cat => {
            let score = 0;
            const catNameLower = cat.name.toLowerCase();
            const catTokensStemmed = cat.tokens.map(normalizeWord);

            if (inputClean.includes(catNameLower) && catNameLower.length >= 3) {
                score += 25;
            }

            stemmedInputWords.forEach((inWord, i) => {
                const originalInWord = inputWords[i];

                catTokensStemmed.forEach((cToken, j) => {
                    const originalCToken = cat.tokens[j];

                    if (inWord === cToken) {
                        score += 12;
                    } else if (inWord.includes(cToken) || cToken.includes(inWord)) {
                        if (cToken.length >= 3 && inWord.length >= 3) {
                            score += 7;
                        }
                    }
                });
            });

            return { ...cat, score };
        }).filter(c => c.score > 0).sort((a, b) => b.score - a.score);

        if (scored.length > 0) {
            const topSuggestions = scored.slice(0, 3);
            let html = '<span class="text-muted small me-1 fw-semibold" style="font-size: 0.72rem;">Suggested:</span>';
            topSuggestions.forEach((s, idx) => {
                const isTop = idx === 0;
                html += `
                    <button type="button" class="btn btn-sm rounded-pill py-0 px-2 fw-medium ${isTop ? 'btn-primary-subtle text-primary border border-primary-subtle' : 'btn-light border '}" 
                            style="font-size: 0.72rem; transition: all 0.15s;" 
                            onclick="applySuggestedCategory('${s.id}')">
                        ${s.name} ${isTop ? '<i class="fa-solid fa-check ms-1"></i>' : ''}
                    </button>
                `;
            });
            box.innerHTML = html;

            const modalId = document.getElementById('modalProdId').value;
            if (!modalId && !isCategoryManuallySelected && topSuggestions[0].score >= 5) {
                select.value = topSuggestions[0].id;
                if (badge) {
                    badge.style.display = 'inline-block';
                    badge.innerHTML = '<i class="fa-solid fa-wand-magic-sparkles me-1"></i> Auto-Matched: ' + topSuggestions[0].name;
                }
            }
        } else {
            box.innerHTML = '';
            if (badge) badge.style.display = 'none';
        }
    }

    function applySuggestedCategory(categoryId) {
        const select = document.getElementById('formCategory');
        const badge = document.getElementById('catSuggestStatus');
        if (select) {
            select.value = categoryId;
            isCategoryManuallySelected = true;
            if (badge) {
                badge.style.display = 'inline-block';
                badge.innerHTML = '<i class="fa-solid fa-check me-1"></i> Selected';
            }
        }
    }

    function regenerateSku() {
        const title = document.getElementById('formTitle').value;
        const skuInput = document.getElementById('formSku');
        if (skuInput) {
            skuInput.value = generateSkuFromTitle(title);
            isSkuManuallyEdited = false;
        }
    }

    function openCreateProductModal() {
        clearModalError();
        document.getElementById('productForm').reset();
        document.getElementById('modalProdId').value = '';
        document.getElementById('productModalLabel').innerText = 'Add New Product';
        currentMainThumbFile = null;
        currentHoveredImageZone = 'gallery';
        updateHoverZoneVisuals();
        previewMainModalImg('');
        document.getElementById('modalVariantTableBody').innerHTML = '';
        document.getElementById('modalSpecContainer').innerHTML = '';
        galleryDataTransfer = new DataTransfer();
        existingGalleryImages = [];
        renderAllGalleryPreviews();

        const catSuggestions = document.getElementById('categorySuggestionsBox');
        if (catSuggestions) catSuggestions.innerHTML = '';
        const catBadge = document.getElementById('catSuggestStatus');
        if (catBadge) catBadge.style.display = 'none';
        addModalVariantRow();
        addModalSpecRow();
        calculateProfitPreview();
        if (document.getElementById('formVideoUrl')) {
            document.getElementById('formVideoUrl').value = '';
            updateModalVideoPreview('');
        }
        isSkuManuallyEdited = false;
        isCategoryManuallySelected = false;
        initAvailableCategories();
        aiGenerationDoneForTitle = '';
        aiGenerationInProgress = false;
        const statusBadge = document.getElementById('aiDescriptionStatusBadge');
        if (statusBadge) statusBadge.innerHTML = '';
        if (document.getElementById('formIsActive')) document.getElementById('formIsActive').checked = true;
        if (document.getElementById('formIsFeatured')) document.getElementById('formIsFeatured').checked = false;
        if (document.getElementById('formIsFlashDeal')) document.getElementById('formIsFlashDeal').checked = false;
        if (document.getElementById('formIsFreeShipping')) document.getElementById('formIsFreeShipping').checked = false;
        updateStatusCardVisuals();
        currentWizardStep = 1;
        updateWizardUI();
        if (productModalInstance) productModalInstance.show();
    }

    function openEditProductModal(productId) {
        clearModalError();
        document.getElementById('productForm').reset();
        document.getElementById('modalProdId').value = productId;
        document.getElementById('productModalLabel').innerText = 'Edit Product (#' + productId + ')';
        currentMainThumbFile = null;
        currentHoveredImageZone = 'gallery';
        updateHoverZoneVisuals();
        galleryDataTransfer = new DataTransfer();
        existingGalleryImages = [];
        renderAllGalleryPreviews();
        const catSuggestions = document.getElementById('categorySuggestionsBox');
        if (catSuggestions) catSuggestions.innerHTML = '';
        const catBadge = document.getElementById('catSuggestStatus');
        if (catBadge) catBadge.style.display = 'none';
        isSkuManuallyEdited = true;
        isCategoryManuallySelected = true;
        initAvailableCategories();
        currentWizardStep = 1;
        updateWizardUI();

        axios.get(`/admin/products/${productId}/json`)
            .then(res => {
                const data = res.data;
                const p = data.product || data;
                document.getElementById('formTitle').value = p.title || '';
                document.getElementById('formCategory').value = p.category_id || '';
                document.getElementById('formSku').value = p.sku || '';
                document.getElementById('formCostPrice').value = p.cost_price || '';
                document.getElementById('formPrice').value = p.price || '';
                document.getElementById('formOldPrice').value = p.old_price || '';
                document.getElementById('formStock').value = p.stock_qty || '0';
                document.getElementById('formTag').value = p.tag || '';
                document.getElementById('formShortDesc').value = p.short_desc || p.short_description || '';
                document.getElementById('formDesc').value = p.description || '';
                document.getElementById('formMainImage').value = p.main_image || '';
                if (document.getElementById('formVideoUrl')) {
                    document.getElementById('formVideoUrl').value = p.video_url || '';
                    updateModalVideoPreview(p.video_url || '');
                }
                calculateProfitPreview();
                
                document.getElementById('formSlug').value = p.slug || '';
                document.getElementById('formMetaTitle').value = p.meta_title || '';
                document.getElementById('formMetaDesc').value = p.meta_description || '';
                if (document.getElementById('formMetaKeywords')) {
                    document.getElementById('formMetaKeywords').value = p.meta_keywords || '';
                }
                aiGenerationDoneForTitle = p.title || '';
                aiGenerationInProgress = false;
                const statusBadge = document.getElementById('aiDescriptionStatusBadge');
                if (statusBadge) {
                    if (p.short_desc || p.description) {
                        statusBadge.innerHTML = '<span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill px-2.5 py-1 small fw-semibold d-inline-flex align-items-center gap-1.5"><i class="fa-solid fa-file-lines"></i><span>Saved Content</span></span>';
                    } else {
                        statusBadge.innerHTML = '';
                    }
                }

                previewMainModalImg(p.main_image);

                document.getElementById('formIsActive').checked = !!p.is_active;
                document.getElementById('formIsFeatured').checked = !!p.is_featured;
                document.getElementById('formIsFlashDeal').checked = !!p.is_flash_deal;
                if (document.getElementById('formIsFreeShipping')) {
                    document.getElementById('formIsFreeShipping').checked = !!p.is_free_shipping;
                }
                updateStatusCardVisuals();

                const variantBody = document.getElementById('modalVariantTableBody');
                variantBody.innerHTML = '';
                const variants = data.variants || p.variants;
                if (variants && Array.isArray(variants) && variants.length > 0) {
                    variants.forEach(v => addModalVariantRow(v.color || v.name, v.price, v.image, v.stock));
                } else {
                    addModalVariantRow();
                }

                const specContainer = document.getElementById('modalSpecContainer');
                specContainer.innerHTML = '';
                const specs = data.specifications || p.specifications;
                if (specs && typeof specs === 'object' && !Array.isArray(specs)) {
                    for (const [k, v] of Object.entries(specs)) {
                        addModalSpecRow(k, v);
                    }
                } else {
                    addModalSpecRow();
                }

                galleryDataTransfer = new DataTransfer();
                existingGalleryImages = [];
                const gallery = data.gallery_images || p.gallery_images || p.gallery;
                if (gallery && Array.isArray(gallery) && gallery.length > 0) {
                    existingGalleryImages = [...gallery];
                }
                renderAllGalleryPreviews();

                currentWizardStep = 1;
                updateWizardUI();

                if (productModalInstance) productModalInstance.show();
            })
            .catch(err => {
                Swal.fire('Error', 'Failed to load product data', 'error');
            });
    }

    let variantRowCounter = 0;
    function addModalVariantRow(name = '', price = '', image = '', stock = '') {
        const tbody = document.getElementById('modalVariantTableBody');
        const tr = document.createElement('tr');
        const rowId = ++variantRowCounter;
        const initialPreview = image ? image : '{{ asset('images/product-placeholder.svg') }}';

        tr.innerHTML = `
            <td><input type="text" name="var_name[]" class="form-control form-control-sm" placeholder="e.g. Matte Black" value="${name}"></td>
            <td><input type="number" step="0.01" name="var_price[]" class="form-control form-control-sm" placeholder="850" value="${price}"></td>
            <td>
                <div class="d-flex align-items-center gap-2">
                    <div class="border rounded-2 d-flex align-items-center justify-content-center overflow-hidden flex-shrink-0" style="width: 44px; height: 44px;">
                        <img id="varPreview_${rowId}" src="${initialPreview}" style="width: 100%; height: 100%; object-fit: cover;" onerror="this.onerror=null;this.src='{{ asset('images/product-placeholder.svg') }}';">
                    </div>
                    <div class="flex-grow-1">
                        <input type="file" name="var_image_file[]" class="form-control form-control-sm mb-1" accept="image/*" onchange="previewVariantFile(this, '${rowId}')" title="Upload from Device">
                        <input type="hidden" name="var_image[]" id="varImgUrl_${rowId}" value="${image}">
                    </div>
                </div>
            </td>
            <td><input type="number" name="var_stock[]" class="form-control form-control-sm" placeholder="50" value="${stock || 50}"></td>
            <td class="text-center"><button type="button" class="btn btn-sm btn-link text-danger p-0" onclick="this.closest('tr').remove()"><i class="fa-solid fa-circle-minus"></i></button></td>
        `;
        tbody.appendChild(tr);
    }

    function previewVariantFile(fileInput, rowId) {
        if (fileInput.files && fileInput.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const img = document.getElementById('varPreview_' + rowId);
                if (img) img.src = e.target.result;
            };
            reader.readAsDataURL(fileInput.files[0]);
        }
    }

    function addModalSpecRow(key = '', val = '') {
        const container = document.getElementById('modalSpecContainer');
        const row = document.createElement('div');
        row.className = 'd-flex gap-2 align-items-center';
        row.innerHTML = `
            <input type="text" name="spec_key[]" class="form-control form-control-sm" placeholder="Key (e.g. Battery)" value="${key}" style="width: 40%;">
            <input type="text" name="spec_val[]" class="form-control form-control-sm" placeholder="Value (e.g. 500mAh / 40hrs)" value="${val}" style="width: 55%;">
            <button type="button" class="btn btn-sm btn-link text-danger p-0" onclick="this.parentElement.remove()"><i class="fa-solid fa-trash"></i></button>
        `;
        container.appendChild(row);
    }

    let galleryDataTransfer = new DataTransfer();
    let existingGalleryImages = [];

    function appendGalleryFiles(files) {
        if (!files || files.length === 0) return;
        Array.from(files).forEach(file => {
            if (file.type.startsWith('image/')) {
                galleryDataTransfer.items.add(file);
            }
        });
        renderAllGalleryPreviews();
    }

    function handleGalleryFilesSelected(input) {
        if (input.files && input.files.length > 0) {
            appendGalleryFiles(input.files);
            input.value = '';
        }
    }

    function removeNewGalleryFile(index) {
        const newDt = new DataTransfer();
        const currentFiles = galleryDataTransfer.files;
        for (let i = 0; i < currentFiles.length; i++) {
            if (i !== index) {
                newDt.items.add(currentFiles[i]);
            }
        }
        galleryDataTransfer = newDt;
        renderAllGalleryPreviews();
    }

    function removeExistingGalleryImage(index) {
        if (index >= 0 && index < existingGalleryImages.length) {
            existingGalleryImages.splice(index, 1);
            renderAllGalleryPreviews();
        }
    }

    function clearAllGalleryImages() {
        galleryDataTransfer = new DataTransfer();
        existingGalleryImages = [];
        renderAllGalleryPreviews();
    }

    function renderAllGalleryPreviews() {
        const grid = document.getElementById('galleryLivePreviewGrid');
        const countBadge = document.getElementById('galleryCountBadge');
        const container = document.getElementById('existingGalleryContainer');
        const clearBtn = document.getElementById('clearAllGalleryBtn');
        if (!grid) return;

        const totalCount = existingGalleryImages.length + galleryDataTransfer.files.length;
        if (countBadge) countBadge.innerText = totalCount;
        if (clearBtn) clearBtn.style.display = totalCount > 0 ? 'inline-block' : 'none';

        if (container) {
            let hiddenHtml = '';
            existingGalleryImages.forEach(imgUrl => {
                hiddenHtml += `<input type="hidden" name="existing_gallery[]" value="${imgUrl}">`;
            });
            container.innerHTML = hiddenHtml;
        }

        if (totalCount === 0) {
            grid.innerHTML = `
                <div class="w-100 text-center py-4 text-muted small fst-italic" id="galleryEmptyText" onclick="document.getElementById('modalGalleryFileInput').click()" style="cursor: pointer;">
                    <i class="fa-regular fa-images fa-2x mb-2 d-block text-secondary opacity-50"></i>
                    Click "Add Photos", drag files here, or paste (<kbd>Ctrl+V</kbd>) from clipboard
                </div>
            `;
            return;
        }

        grid.innerHTML = '';

        existingGalleryImages.forEach((imgUrl, idx) => {
            const card = document.createElement('div');
            card.className = 'position-relative border rounded-3 overflow-hidden shadow-sm';
            card.style = 'width: 90px; height: 90px; flex-shrink: 0;';
            card.innerHTML = `
                <img src="${imgUrl}" style="width: 100%; height: 100%; object-fit: cover;" onerror="this.onerror=null;this.src='{{ asset('images/product-placeholder.svg') }}';">
                <span class="badge bg-secondary position-absolute bottom-0 start-0 w-100 text-center text-truncate" style="font-size: 0.6rem; border-radius: 0; padding: 2px;">Saved</span>
                <button type="button" class="btn btn-danger btn-sm p-0 position-absolute top-0 end-0 m-1 rounded-circle d-flex align-items-center justify-content-center shadow" 
                        style="width: 22px; height: 22px; font-size: 0.65rem;" 
                        onclick="removeExistingGalleryImage(${idx})" 
                        title="Delete this image">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            `;
            grid.appendChild(card);
        });

        Array.from(galleryDataTransfer.files).forEach((file, idx) => {
            const card = document.createElement('div');
            card.className = 'position-relative border rounded-3 overflow-hidden shadow-sm';
            card.style = 'width: 90px; height: 90px; flex-shrink: 0;';

            const objectUrl = URL.createObjectURL(file);
            card.innerHTML = `
                <img src="${objectUrl}" style="width: 100%; height: 100%; object-fit: cover;">
                <span class="badge bg-success position-absolute bottom-0 start-0 w-100 text-center text-truncate" style="font-size: 0.6rem; border-radius: 0; padding: 2px;">New</span>
                <button type="button" class="btn btn-danger btn-sm p-0 position-absolute top-0 end-0 m-1 rounded-circle d-flex align-items-center justify-content-center shadow" 
                        style="width: 22px; height: 22px; font-size: 0.65rem;" 
                        onclick="removeNewGalleryFile(${idx})" 
                        title="Remove this photo">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            `;
            grid.appendChild(card);
        });
    }

    function toggleSpecBulkMode() {
        const panel = document.getElementById('specBulkPanel');
        const btn = document.getElementById('specBulkModeBtn');
        if (!panel) return;
        const isHidden = panel.classList.contains('d-none');
        if (isHidden) {
            panel.classList.remove('d-none');
            if (btn) btn.innerHTML = '<i class="fa-solid fa-table-list me-1"></i> Table Mode';
            document.getElementById('specBulkTextarea')?.focus();
        } else {
            panel.classList.add('d-none');
            if (btn) btn.innerHTML = '<i class="fa-solid fa-paste me-1"></i> Bulk Paste';
        }
    }

    function parseBulkSpecs() {
        const textarea = document.getElementById('specBulkTextarea');
        if (!textarea || !textarea.value.trim()) return;

        const rawLines = textarea.value.split(/\r?\n/).map(l => l.trim()).filter(l => l.length > 0);
        if (rawLines.length === 0) return;

        const isDelimited = (l) => {
            if (l.includes('\t') || l.includes(' - ') || l.includes('—')) return true;
            if (l.includes(':')) {
                const parts = l.split(':');
                const val = parts.slice(1).join(':').trim();
                return val.length > 0;
            }
            return false;
        };

        const singleLineCount = rawLines.filter(isDelimited).length;
        const isAlternatingMode = singleLineCount === 0 || (singleLineCount < rawLines.length * 0.35 && rawLines.length >= 2);

        const parsedPairs = [];
        let i = 0;

        if (isAlternatingMode) {
            while (i < rawLines.length) {
                let key = rawLines[i].replace(/[:\-—\t]+$/, '').trim();
                let val = '';
                if (i + 1 < rawLines.length) {
                    val = rawLines[i + 1].trim();
                    i += 2;
                } else {
                    val = '-';
                    i += 1;
                }
                if (key) {
                    parsedPairs.push({ key, val });
                }
            }
        } else {
            while (i < rawLines.length) {
                const line = rawLines[i];
                if (line.endsWith(':') || (line.includes(':') && line.split(':')[1].trim() === '')) {
                    const key = line.replace(/[:]+$/, '').trim();
                    let val = '';
                    if (i + 1 < rawLines.length && !rawLines[i + 1].includes(':')) {
                        val = rawLines[i + 1].trim();
                        i += 2;
                    } else {
                        val = '-';
                        i += 1;
                    }
                    if (key) parsedPairs.push({ key, val });
                } else if (line.includes(':')) {
                    const parts = line.split(':');
                    const key = parts[0].trim();
                    const val = parts.slice(1).join(':').trim();
                    if (key) parsedPairs.push({ key, val: val || '-' });
                    i++;
                } else if (line.includes('\t')) {
                    const parts = line.split('\t');
                    const key = parts[0].trim();
                    const val = parts.slice(1).join(' ').trim();
                    if (key) parsedPairs.push({ key, val: val || '-' });
                    i++;
                } else if (line.includes(' - ')) {
                    const parts = line.split(' - ');
                    const key = parts[0].trim();
                    const val = parts.slice(1).join(' - ').trim();
                    if (key) parsedPairs.push({ key, val: val || '-' });
                    i++;
                } else if (line.includes('—')) {
                    const parts = line.split('—');
                    const key = parts[0].trim();
                    const val = parts.slice(1).join('—').trim();
                    if (key) parsedPairs.push({ key, val: val || '-' });
                    i++;
                } else {
                    if (i + 1 < rawLines.length && !isDelimited(rawLines[i + 1])) {
                        parsedPairs.push({ key: line, val: rawLines[i + 1] });
                        i += 2;
                    } else {
                        parsedPairs.push({ key: line, val: '-' });
                        i++;
                    }
                }
            }
        }

        let addedCount = 0;
        parsedPairs.forEach(pair => {
            if (pair.key) {
                addModalSpecRow(pair.key, pair.val);
                addedCount++;
            }
        });

        textarea.value = '';
        toggleSpecBulkMode();
        if (window.showToast) window.showToast(addedCount + ' specification rows added!', 'success');
    }

    function clearAllSpecRows() {
        const container = document.getElementById('modalSpecContainer');
        if (container) container.innerHTML = '';
    }

    function addFastVariant() {
        const nameInput = document.getElementById('fastVarName');
        const priceInput = document.getElementById('fastVarPrice');
        const stockInput = document.getElementById('fastVarStock');

        if (!nameInput) return;
        const name = nameInput.value.trim();
        if (!name) {
            nameInput.focus();
            return;
        }

        const price = priceInput ? priceInput.value.trim() : '';
        const stock = stockInput && stockInput.value.trim() ? stockInput.value.trim() : '50';

        addModalVariantRow(name, price, '', stock);

        nameInput.value = '';
        if (priceInput) priceInput.value = '';
        nameInput.focus();
    }

    function parseSmartRawText() {
        const rawEl = document.getElementById('smartRawInput');
        if (!rawEl || !rawEl.value.trim()) {
            if (window.showToast) window.showToast('Please paste some product text first!', 'warning');
            return;
        }

        const rawText = rawEl.value.trim();
        const lines = rawText.split(/\r?\n/).map(l => l.trim()).filter(l => l.length > 0);
        if (lines.length === 0) return;

        let detectedTitle = '';
        let detectedSpecs = [];
        let descLines = [];

        const specRegex = /^([A-Za-z0-9\u0980-\u09FF\s\/\-_&]{2,35})\s*[:\t\-—]\s*(.+)$/;

        let i = 0;
        while (i < lines.length) {
            const line = lines[i];

            if (!detectedTitle) {
                if (line.toLowerCase().startsWith('title:') || line.toLowerCase().startsWith('product:') || line.toLowerCase().startsWith('name:')) {
                    detectedTitle = line.replace(/^(title|product|name)\s*:\s*/i, '').trim();
                    i++;
                    continue;
                } else if (i === 0 && line.length < 120 && !line.includes(':')) {
                    detectedTitle = line;
                    i++;
                    continue;
                }
            }

            const specMatch = line.match(specRegex);
            if (specMatch && !line.toLowerCase().startsWith('http') && !line.includes('www.')) {
                detectedSpecs.push({
                    key: specMatch[1].trim(),
                    val: specMatch[2].trim()
                });
                i++;
            } else if (line.endsWith(':') && i + 1 < lines.length && !lines[i + 1].includes(':')) {
                detectedSpecs.push({
                    key: line.replace(/[:]+$/, '').trim(),
                    val: lines[i + 1].trim()
                });
                i += 2;
            } else {
                descLines.push(line);
                i++;
            }
        }

        if (detectedSpecs.length === 0 && descLines.length >= 2) {
            const possiblePairs = [];
            let canPair = true;
            for (let j = 0; j < descLines.length; j += 2) {
                if (j + 1 < descLines.length && descLines[j].length <= 35 && !descLines[j].includes('.') && descLines[j + 1].length <= 150) {
                    possiblePairs.push({ key: descLines[j], val: descLines[j + 1] });
                } else {
                    canPair = false;
                    break;
                }
            }
            if (canPair && possiblePairs.length > 0) {
                detectedSpecs = possiblePairs;
                descLines = [];
            }
        }

        if (!detectedTitle && lines.length > 0) {
            detectedTitle = lines[0].replace(/^[#*\-•\s]+/, '').substring(0, 100).trim();
        }

        const titleInput = document.getElementById('formTitle');
        if (detectedTitle && titleInput && (!titleInput.value.trim() || titleInput.value === '')) {
            titleInput.value = detectedTitle;
            handleTitleChanged(detectedTitle);
        }

        if (detectedSpecs.length > 0) {
            detectedSpecs.forEach(s => {
                addModalSpecRow(s.key, s.val);
            });
        }

        const descText = descLines.join('\n\n');
        const descInput = document.getElementById('formDesc');
        if (descInput) {
            descInput.value = descText || rawText;
        }

        const shortDescInput = document.getElementById('formShortDesc');
        if (shortDescInput && (!shortDescInput.value || shortDescInput.value.trim() === '')) {
            const firstSentence = (descLines[0] || rawText).split(/[.!?\n]/)[0].trim();
            shortDescInput.value = firstSentence ? firstSentence + '.' : '';
        }

        const effectiveTitle = titleInput ? titleInput.value.trim() : detectedTitle;
        const slugInput = document.getElementById('formSlug');
        if (slugInput && (!slugInput.value || slugInput.value.trim() === '')) {
            slugInput.value = effectiveTitle.toLowerCase().replace(/[^a-z0-9\u0980-\u09FF]+/g, '-').replace(/(^-|-$)+/g, '');
        }

        const metaTitleInput = document.getElementById('formMetaTitle');
        if (metaTitleInput && (!metaTitleInput.value || metaTitleInput.value.trim() === '')) {
            metaTitleInput.value = effectiveTitle.substring(0, 60);
        }

        const metaDescInput = document.getElementById('formMetaDesc');
        if (metaDescInput && (!metaDescInput.value || metaDescInput.value.trim() === '')) {
            const shortVal = shortDescInput ? shortDescInput.value : '';
            metaDescInput.value = (shortVal || effectiveTitle).substring(0, 160);
        }

        if (window.showToast) {
            window.showToast(`Extracted: Title, ${detectedSpecs.length} specs, Description & SEO!`, 'success');
        }
    }

    function duplicateProductAjax(id) {
        Swal.fire({
            title: 'Duplicate Product?',
            text: 'This will create an exact copy of this product with all images, variants, and specifications.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#0d6efd',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Yes, Duplicate!'
        }).then(result => {
            if (result.isConfirmed) {
                axios.post(`/admin/products/${id}/duplicate`, null, {
                    headers: { 'Accept': 'application/json' },
                    skipToast: true
                })
                .then(res => {
                    const data = res.data;
                    if (data.success) {
                        if (productsTable) {
                            productsTable.ajax.reload(null, false);
                        }
                        Swal.fire({
                            title: 'Duplicated!',
                            text: 'Product cloned successfully. Would you like to edit it now?',
                            icon: 'success',
                            showCancelButton: true,
                            confirmButtonColor: '#0d6efd',
                            cancelButtonColor: '#64748b',
                            confirmButtonText: 'Edit Now',
                            cancelButtonText: 'Later'
                        }).then(editRes => {
                            if (editRes.isConfirmed && data.id) {
                                openEditProductModal(data.id);
                            }
                        });
                    } else {
                        Swal.fire('Error', data.message || 'Failed to duplicate product', 'error');
                    }
                })
                .catch(err => {
                    Swal.fire('Error', err.response?.data?.message || 'Failed to duplicate product', 'error');
                });
            }
        });
    }

    function initDragDropAndPaste() {
        const thumbDropZone = document.getElementById('thumbDropZone');
        const thumbOverlay = document.getElementById('thumbDropOverlay');
        const galleryDropZone = document.getElementById('galleryDropZone');
        const galleryOverlay = document.getElementById('galleryDropOverlay');
        const modalEl = document.getElementById('productModal');

        if (thumbDropZone && thumbOverlay) {
            ['dragenter', 'dragover'].forEach(eventName => {
                thumbDropZone.addEventListener(eventName, (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    thumbOverlay.classList.remove('d-none');
                });
            });
            ['dragleave', 'drop'].forEach(eventName => {
                thumbDropZone.addEventListener(eventName, (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    thumbOverlay.classList.add('d-none');
                });
            });
            thumbDropZone.addEventListener('drop', (e) => {
                const files = e.dataTransfer.files;
                if (files && files.length > 0 && files[0].type.startsWith('image/')) {
                    setProductMainImageFile(files[0]);
                }
            });
        }

        if (galleryDropZone && galleryOverlay) {
            ['dragenter', 'dragover'].forEach(eventName => {
                galleryDropZone.addEventListener(eventName, (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    galleryOverlay.classList.remove('d-none');
                });
            });
            ['dragleave', 'drop'].forEach(eventName => {
                galleryDropZone.addEventListener(eventName, (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    galleryOverlay.classList.add('d-none');
                });
            });
            galleryDropZone.addEventListener('drop', (e) => {
                const files = e.dataTransfer.files;
                if (files && files.length > 0) {
                    appendGalleryFiles(files);
                }
            });
        }

        const mainCard = document.getElementById('mainThumbCard');
        const galleryCard = document.getElementById('galleryCard');

        if (mainCard) {
            mainCard.addEventListener('mouseenter', () => {
                currentHoveredImageZone = 'thumb';
                updateHoverZoneVisuals();
            });
        }
        if (thumbDropZone) {
            thumbDropZone.addEventListener('mouseenter', () => {
                currentHoveredImageZone = 'thumb';
                updateHoverZoneVisuals();
            });
        }
        if (galleryCard) {
            galleryCard.addEventListener('mouseenter', () => {
                currentHoveredImageZone = 'gallery';
                updateHoverZoneVisuals();
            });
        }
        if (galleryDropZone) {
            galleryDropZone.addEventListener('mouseenter', () => {
                currentHoveredImageZone = 'gallery';
                updateHoverZoneVisuals();
            });
        }

        updateHoverZoneVisuals();

        if (modalEl) {
            modalEl.removeEventListener('paste', handleGlobalPaste);
            modalEl.addEventListener('paste', handleGlobalPaste);
            modalEl.removeEventListener('keydown', handleModalEnterNavigation);
            modalEl.addEventListener('keydown', handleModalEnterNavigation);
        }
    }

    function handleGlobalPaste(e) {
        const modalEl = document.getElementById('productModal');
        if (!modalEl || !modalEl.classList.contains('show')) return;

        const clipboardData = e.clipboardData;
        if (!clipboardData) return;

        const items = clipboardData.items || [];
        const imageFiles = [];

        for (let i = 0; i < items.length; i++) {
            if (items[i].type.startsWith('image/')) {
                const f = items[i].getAsFile();
                if (f) imageFiles.push(f);
            }
        }

        if (imageFiles.length === 0 && clipboardData.files) {
            Array.from(clipboardData.files).forEach(f => {
                if (f.type.startsWith('image/')) imageFiles.push(f);
            });
        }

        if (imageFiles.length > 0) {
            e.preventDefault();
            if (currentHoveredImageZone === 'thumb') {
                setProductMainImageFile(imageFiles[0]);
                if (window.showToast) window.showToast('Main thumbnail pasted successfully!', 'success');
            } else {
                appendGalleryFiles(imageFiles);
                if (window.showToast) window.showToast(imageFiles.length + ' image(s) added to gallery!', 'success');
            }
        }
    }

    function handleModalEnterNavigation(e) {
        if (e.key !== 'Enter') return;
        const target = e.target;
        if (!target || target.tagName === 'TEXTAREA' || target.type === 'submit' || target.id === 'fastVarName' || target.id === 'fastVarPrice' || target.id === 'fastVarStock') return;

        e.preventDefault();

        if (target.id === 'formTitle') {
            document.getElementById('formCategory')?.focus();
        } else if (target.id === 'formCategory') {
            document.getElementById('formSku')?.focus();
        } else if (target.id === 'formSku') {
            document.getElementById('formCostPrice')?.focus();
        } else if (target.id === 'formCostPrice') {
            document.getElementById('formPrice')?.focus();
        } else if (target.id === 'formPrice') {
            document.getElementById('formOldPrice')?.focus();
        } else if (target.id === 'formOldPrice') {
            document.getElementById('formStock')?.focus();
        } else if (target.id === 'formStock') {
            navigateWizard(1);
        }
    }

    function showModalError(title, message) {
        const alertEl = document.getElementById('modalErrorAlert');
        const titleEl = document.getElementById('modalErrorTitle');
        const msgEl = document.getElementById('modalErrorMessage');
        if (alertEl && titleEl && msgEl) {
            titleEl.innerText = title;
            msgEl.innerHTML = message;
            alertEl.classList.remove('d-none');
            const modalBody = document.querySelector('#productModal .modal-body');
            if (modalBody) modalBody.scrollTo({ top: 0, behavior: 'smooth' });
        }
    }

    function clearModalError() {
        const alertEl = document.getElementById('modalErrorAlert');
        if (alertEl) alertEl.classList.add('d-none');
    }

    function handleProductFormSubmit(e) {
        e.preventDefault();
        clearModalError();
        const form = document.getElementById('productForm');
        const formData = new FormData(form);

        if (currentMainThumbFile) {
            formData.set('main_image_file', currentMainThumbFile);
        }

        Array.from(galleryDataTransfer.files).forEach(file => {
            formData.append('gallery_files[]', file);
        });

        const submitBtn = document.getElementById('modalSaveBtn');
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-1"></i> Saving...';

        axios.post('{{ route('admin.products.ajax_save') }}', formData, {
            headers: { 'Accept': 'application/json' },
            skipToast: true
        })
        .then(res => {
            const data = res.data;
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fa-solid fa-check me-1"></i> Save Product';

            if (data.success) {
                clearModalError();
                if (productModalInstance) productModalInstance.hide();
                Swal.fire({
                    icon: 'success',
                    title: 'Saved!',
                    text: data.message,
                    timer: 1500,
                    showConfirmButton: false
                });
                if (productsTable) {
                    productsTable.ajax.reload(null, false);
                }
            } else {
                showModalError('Error Saving Product', data.message || 'Validation error');
            }
        })
        .catch(err => {
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fa-solid fa-check me-1"></i> Save Product';
            
            let errMsg = 'An unexpected error occurred. Please check your inputs and try again.';
            if (err.response && err.response.data) {
                const resData = err.response.data;
                if (resData.errors) {
                    let list = '<ul class="mb-0 ps-3 mt-1">';
                    for (const [key, msgs] of Object.entries(resData.errors)) {
                        list += `<li><strong>${key.replace('_', ' ')}:</strong> ${Array.isArray(msgs) ? msgs.join(', ') : msgs}</li>`;
                    }
                    list += '</ul>';
                    showModalError('Please fix the following validation errors:', list);
                    return;
                } else if (resData.message) {
                    errMsg = resData.message;
                }
            }
            showModalError('Submission Failed', errMsg);
        });
    }

    function toggleProductActive(id) {
        axios.post(`/admin/products/${id}/toggle`, null, {
            headers: { 'Accept': 'application/json' },
            skipToast: true
        })
        .then(res => {
            const data = res.data;
            if (data.success) {
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'success',
                    title: data.is_active ? 'Product is now Live' : 'Product is Hidden',
                    showConfirmButton: false,
                    timer: 1800
                });
                if (productsTable) {
                    productsTable.ajax.reload(null, false);
                }
            }
        });
    }

    function deleteProductAjax(id) {
        Swal.fire({
            title: 'Delete this product?',
            text: 'This will permanently remove the product and its variants.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Yes, delete!'
        }).then(result => {
            if (result.isConfirmed) {
                axios.delete(`/admin/products/${id}/ajax-delete`, {
                    headers: { 'Accept': 'application/json' },
                    skipToast: true
                })
                .then(res => {
                    const data = res.data;
                    if (data.success) {
                        if (productsTable) {
                            productsTable.ajax.reload(null, false);
                        }
                        Swal.fire('Deleted!', 'Product has been removed.', 'success');
                    } else {
                        Swal.fire('Error', data.message || 'Failed to delete product', 'error');
                    }
                });
            }
        });
    }

    function getYouTubeEmbedUrl(url) {
        if (!url) return null;
        const trimmed = url.trim();
        const regExp = /(?:youtube(?:-nocookie)?\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=|shorts\/)|youtu\.be\/)([^"&?\/ ]{11})/i;
        const match = trimmed.match(regExp);
        if (match && match[1]) {
            return 'https://www.youtube-nocookie.com/embed/' + match[1] + '?rel=0';
        }
        if (trimmed.length === 11 && /^[a-zA-Z0-9_-]{11}$/.test(trimmed)) {
            return 'https://www.youtube-nocookie.com/embed/' + trimmed + '?rel=0';
        }
        return null;
    }

    function updateModalVideoPreview(url) {
        const previewWrapper = document.getElementById('modalVideoPreviewWrapper');
        const iframe = document.getElementById('modalVideoIframe');
        if (!previewWrapper || !iframe) return;
        const embedUrl = getYouTubeEmbedUrl(url);
        if (embedUrl) {
            iframe.src = embedUrl;
            previewWrapper.classList.remove('d-none');
        } else {
            iframe.src = '';
            previewWrapper.classList.add('d-none');
        }
    }

    window.updateWizardUI = updateWizardUI;
    window.goToWizardStep = goToWizardStep;
    window.navigateWizard = navigateWizard;
    window.validateWizardStep = validateWizardStep;
    window.previewMainModalImg = previewMainModalImg;
    window.handleProductMainFile = handleProductMainFile;
    window.calculateProfitPreview = calculateProfitPreview;
    window.initAvailableCategories = initAvailableCategories;
    window.generateSkuFromTitle = generateSkuFromTitle;
    window.handleTitleChanged = handleTitleChanged;
    window.normalizeWord = normalizeWord;
    window.suggestCategoryFromTitle = suggestCategoryFromTitle;
    window.applySuggestedCategory = applySuggestedCategory;
    window.regenerateSku = regenerateSku;
    window.openCreateProductModal = openCreateProductModal;
    window.openEditProductModal = openEditProductModal;
    window.addModalVariantRow = addModalVariantRow;
    window.previewVariantFile = previewVariantFile;
    window.addModalSpecRow = addModalSpecRow;
    window.handleGalleryFilesSelected = handleGalleryFilesSelected;
    window.removeNewGalleryFile = removeNewGalleryFile;
    window.removeExistingGalleryImage = removeExistingGalleryImage;
    window.renderAllGalleryPreviews = renderAllGalleryPreviews;
    window.showModalError = showModalError;
    window.clearModalError = clearModalError;
    window.handleProductFormSubmit = handleProductFormSubmit;
    window.toggleProductActive = toggleProductActive;
    window.deleteProductAjax = deleteProductAjax;
    window.getYouTubeEmbedUrl = getYouTubeEmbedUrl;
    window.updateModalVideoPreview = updateModalVideoPreview;
    window.updateBadgeSuggestions = updateBadgeSuggestions;
    window.clearProductTag = clearProductTag;
    window.clearProductMainThumbnail = clearProductMainThumbnail;
    window.setProductMainImageFile = setProductMainImageFile;
    window.clearAllGalleryImages = clearAllGalleryImages;
    window.appendGalleryFiles = appendGalleryFiles;
    window.toggleSpecBulkMode = toggleSpecBulkMode;
    window.parseBulkSpecs = parseBulkSpecs;
    window.clearAllSpecRows = clearAllSpecRows;
    window.addFastVariant = addFastVariant;
    window.parseSmartRawText = parseSmartRawText;
    window.duplicateProductAjax = duplicateProductAjax;
    window.initDragDropAndPaste = initDragDropAndPaste;
    window.updateHoverZoneVisuals = updateHoverZoneVisuals;
    let aiGenerationInProgress = false;
    let aiGenerationDoneForTitle = '';

    function triggerAiSeoAndDescriptionAutoGeneration(force = false) {
        const titleInput = document.getElementById('formTitle');
        if (!titleInput) return;
        const title = titleInput.value.trim();
        if (!title) {
            if (force && window.showToast) {
                window.showToast('Please enter a product title first!', 'warning');
            }
            return;
        }

        if (!force && aiGenerationDoneForTitle === title) {
            return;
        }

        const catSelect = document.getElementById('formCategory');
        const categoryName = catSelect && catSelect.selectedIndex >= 0 ? catSelect.options[catSelect.selectedIndex].text.replace(/^↳\s*/, '').trim() : '';
        const priceInput = document.getElementById('formPrice');
        const oldPriceInput = document.getElementById('formOldPrice');
        const tagInput = document.getElementById('formTag');

        const specs = [];
        const specKeys = document.querySelectorAll('input[name="spec_key[]"]');
        const specVals = document.querySelectorAll('input[name="spec_value[]"]');
        specKeys.forEach((keyEl, idx) => {
            const k = keyEl.value.trim();
            const v = specVals[idx] ? specVals[idx].value.trim() : '';
            if (k) specs.push({ key: k, value: v });
        });

        const statusBadge = document.getElementById('aiDescriptionStatusBadge');
        const btn = document.getElementById('btn-generate-ai');

        if (statusBadge) {
            statusBadge.innerHTML = '<span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-2.5 py-1 small fw-semibold d-inline-flex align-items-center gap-1.5"><span class="spinner-border spinner-border-sm" role="status" style="width: 12px; height: 12px;"></span><span>AI Generating Copy...</span></span>';
        }
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-1"></i> Generating...';
        }

        aiGenerationInProgress = true;
        aiGenerationDoneForTitle = title;

        axios.post("{{ route('admin.products.ai.seo_description') }}", {
            title: title,
            category: categoryName,
            price: priceInput ? priceInput.value : '',
            old_price: oldPriceInput ? oldPriceInput.value : '',
            tag: tagInput ? tagInput.value : '',
            specs: specs,
            language: 'mixed'
        })
        .then(response => {
            const res = response.data;
            aiGenerationInProgress = false;
            if (res && res.success && res.data) {
                const data = res.data;
                const shortDescEl = document.getElementById('formShortDesc');
                const descEl = document.getElementById('formDesc');
                const metaTitleEl = document.getElementById('formMetaTitle');
                const metaDescEl = document.getElementById('formMetaDesc');
                const slugEl = document.getElementById('formSlug');
                const keywordsEl = document.getElementById('formMetaKeywords');

                if (shortDescEl && (force || !shortDescEl.value.trim())) {
                    shortDescEl.value = data.short_desc || '';
                }
                if (descEl && (force || !descEl.value.trim())) {
                    descEl.value = data.detailed_html_description || '';
                }
                if (metaTitleEl && (force || !metaTitleEl.value.trim())) {
                    metaTitleEl.value = data.meta_title || '';
                }
                if (metaDescEl && (force || !metaDescEl.value.trim())) {
                    metaDescEl.value = data.meta_description || '';
                }
                if (slugEl && (force || !slugEl.value.trim())) {
                    slugEl.value = data.url_slug || '';
                }
                if (keywordsEl && (force || !keywordsEl.value.trim())) {
                    keywordsEl.value = data.meta_keywords || '';
                }

                if (statusBadge) {
                    statusBadge.innerHTML = '<span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-1 small fw-semibold d-inline-flex align-items-center gap-1.5"><i class="fa-solid fa-wand-magic-sparkles"></i><span>AI Generated (Realistic Copy)</span></span>';
                }
                if (force && window.showToast) {
                    window.showToast('Descriptions & SEO successfully generated!', 'success');
                }
            } else {
                if (statusBadge) {
                    statusBadge.innerHTML = '<span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill px-2.5 py-1 small"><i class="fa-solid fa-triangle-exclamation me-1"></i>AI Notice</span>';
                }
            }
        })
        .catch(err => {
            aiGenerationInProgress = false;
            if (statusBadge) {
                statusBadge.innerHTML = '';
            }
            if (force && window.showToast) {
                window.showToast('Failed to generate AI content. Please verify AI provider settings.', 'error');
            }
        })
        .finally(() => {
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = '<i class="fa-solid fa-wand-magic-sparkles me-1"></i> AI Auto-Fill';
            }
        });
    }

    window.triggerAiSeoAndDescriptionAutoGeneration = triggerAiSeoAndDescriptionAutoGeneration;
    window.generateAISEO = function(force = true) {
        triggerAiSeoAndDescriptionAutoGeneration(force);
    };
    window.toggleStatusSwitch = toggleStatusSwitch;
    window.updateStatusCardVisuals = updateStatusCardVisuals;
})();
</script>
@endpush