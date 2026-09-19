@extends('backend.layouts.app')

@php
    $isEdit = isset($product);
    $actionUrl = $isEdit ? route('admin.products.update', $product->id) : route('admin.products.store');

    $rawVariants = $isEdit ? (is_array($product->variants) ? $product->variants : (json_decode($product->variants ?? '', true) ?: [])) : [];
    if (is_string($rawVariants)) {
        $rawVariants = json_decode($rawVariants, true) ?: [];
    }

    $rawSpecs = $isEdit ? (is_array($product->specifications) ? $product->specifications : (json_decode($product->specifications ?? '', true) ?: [])) : [];
    if (is_string($rawSpecs)) {
        $rawSpecs = json_decode($rawSpecs, true) ?: [];
    }

    $rawGallery = $isEdit ? (is_array($product->gallery_images) ? $product->gallery_images : (json_decode($product->gallery_images ?? '', true) ?: [])) : [];
    if (is_string($rawGallery)) {
        $rawGallery = json_decode($rawGallery, true) ?: [];
    }
@endphp

@section('title', $isEdit ? 'Edit Product: ' . $product->title : 'Add New Product')

@push('styles')
<style>
    .status-toggle-card {
        border: 1.5px solid rgba(226, 232, 240, 0.9) !important;
        border-radius: 14px !important;
        background: #ffffff;
        padding: 13px 15px;
        cursor: pointer;
        transition: all 0.22s cubic-bezier(0.4, 0, 0.2, 1);
        user-select: none;
    }

    .status-toggle-card:hover {
        border-color: rgba(148, 163, 184, 0.6) !important;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px -2px rgba(15, 23, 42, 0.06) !important;
    }

    .status-toggle-card.active-live {
        border-color: rgba(16, 185, 129, 0.55) !important;
        background: linear-gradient(135deg, rgba(16, 185, 129, 0.05) 0%, #ffffff 100%) !important;
        box-shadow: 0 4px 14px -3px rgba(16, 185, 129, 0.15) !important;
    }

    .status-toggle-card.active-featured {
        border-color: rgba(245, 158, 11, 0.55) !important;
        background: linear-gradient(135deg, rgba(245, 158, 11, 0.05) 0%, #ffffff 100%) !important;
        box-shadow: 0 4px 14px -3px rgba(245, 158, 11, 0.15) !important;
    }

    .status-toggle-card.active-flash {
        border-color: rgba(239, 68, 68, 0.55) !important;
        background: linear-gradient(135deg, rgba(239, 68, 68, 0.05) 0%, #ffffff 100%) !important;
        box-shadow: 0 4px 14px -3px rgba(239, 68, 68, 0.15) !important;
    }

    .status-toggle-card.active-shipping {
        border-color: rgba(6, 182, 212, 0.55) !important;
        background: linear-gradient(135deg, rgba(6, 182, 212, 0.05) 0%, #ffffff 100%) !important;
        box-shadow: 0 4px 14px -3px rgba(6, 182, 212, 0.15) !important;
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

    #isActiveSwitch:checked {
        background-color: #10b981;
        border-color: #10b981;
    }

    #isFeaturedSwitch:checked {
        background-color: #f59e0b;
        border-color: #f59e0b;
    }

    #isFlashSwitch:checked {
        background-color: #ef4444;
        border-color: #ef4444;
    }

    #isFreeShippingSwitch:checked {
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
    }

    .status-draft-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        display: inline-block;
        background-color: #94a3b8;
    }
</style>
@endpush

@section('content')
<div class="card p-3 p-md-4 mb-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <a href="{{ route('admin.products.index') }}" class="text-muted small text-decoration-none"><i class="fa-solid fa-arrow-left me-1"></i> Back to Products</a>
            </div>
            <h3 class="fw-bold mb-0">{{ $isEdit ? 'Edit Product' : 'Add New Product' }}</h3>
            <small class="text-muted">Configure pricing, variants, gallery (Drag, Drop & Paste supported), and specs</small>
        </div>
        <div class="d-flex gap-2">
            @if($isEdit)
                <a href="{{ route('product.show', $product->slug) }}" target="_blank" class="btn btn-outline-secondary btn-sm">
                    <i class="fa-solid fa-arrow-up-right-from-square me-1"></i> Live Store Preview
                </a>
            @endif
        </div>
    </div>
</div>

<form action="{{ $actionUrl }}" method="POST" enctype="multipart/form-data" id="productForm">
    @csrf
    <div class="row g-4 mb-4">
        <!-- Left: Core Info, Variants, Specifications, Descriptions -->
        <div class="col-lg-8">
            <!-- 1. Basic Info -->
            <div class="card p-3 p-md-4 mb-4">
                <h5 class="fw-bold mb-3 border-bottom pb-2">
                    <i class="fa-solid fa-circle-info text-primary me-1"></i> Basic Product Information
                </h5>
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label">Product Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" id="standaloneTitle" class="form-control" placeholder="e.g. Wireless Noise-Cancelling Bluetooth Headphone" value="{{ old('title', $product->title ?? '') }}" required oninput="handleStandaloneTitleForSku(this.value)">
                    </div>
                    <div class="col-md-6">
                        <div class="d-flex align-items-center justify-content-between mb-1">
                            <label class="form-label mb-0">Category <span class="text-danger">*</span></label>
                            <span id="standaloneCatSuggestStatus" class="badge bg-primary-subtle text-primary rounded-pill px-2 py-0.5" style="font-size: 0.7rem; display: none;">
                                <i class="fa-solid fa-wand-magic-sparkles me-1"></i> Auto-Matched
                            </span>
                        </div>
                        <select name="category_id" id="standaloneCategory" class="form-select" required onchange="standaloneCategorySelected = true;">
                            <option value="">Select Category</option>
                            @php
                                $rootFormCats = $categories->whereNull('parent_id');
                            @endphp
                            @foreach($rootFormCats as $rfCat)
                                @php
                                    $subFormCats = $categories->where('parent_id', $rfCat->id);
                                @endphp
                                @if($subFormCats->count() > 0)
                                    <optgroup label="{{ $rfCat->name }} ({{ $rfCat->name_bn }})">
                                        <option value="{{ $rfCat->id }}" {{ old('category_id', $product->category_id ?? '') == $rfCat->id ? 'selected' : '' }}>{{ $rfCat->name }} (Main)</option>
                                        @foreach($subFormCats as $sfCat)
                                            <option value="{{ $sfCat->id }}" {{ old('category_id', $product->category_id ?? '') == $sfCat->id ? 'selected' : '' }}>↳ {{ $sfCat->name }} ({{ $sfCat->name_bn }})</option>
                                        @endforeach
                                    </optgroup>
                                @else
                                    <option value="{{ $rfCat->id }}" {{ old('category_id', $product->category_id ?? '') == $rfCat->id ? 'selected' : '' }}>
                                        {{ $rfCat->name }} ({{ $rfCat->name_bn }})
                                    </option>
                                @endif
                            @endforeach
                        </select>
                        <div id="standaloneCategorySuggestionsBox" class="mt-2 d-flex flex-wrap gap-1 align-items-center" style="min-height: 22px;"></div>
                    </div>
                    <div class="col-md-6">
                        <div class="d-flex align-items-center justify-content-between">
                            <label class="form-label">SKU Code</label>
                            <span class="badge bg-secondary-subtle text-secondary rounded-pill px-2 py-0.5" style="font-size: 0.7rem;">Auto-generates</span>
                        </div>
                        <div class="input-group">
                            <input type="text" name="sku" id="standaloneSku" class="form-control font-monospace" placeholder="e.g. ZB-HD580" value="{{ old('sku', $product->sku ?? '') }}" oninput="standaloneSkuEdited = true;">
                            <button type="button" class="btn btn-outline-secondary" onclick="regenerateStandaloneSku()">
                                <i class="fa-solid fa-arrows-rotate"></i>
                            </button>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-danger fw-semibold">Purchase / Cost Price (৳)</label>
                        <div class="input-group">
                            <span class="input-group-text bg-danger-subtle text-danger fw-bold">৳</span>
                            <input type="number" step="0.01" name="cost_price" class="form-control" placeholder="e.g. 500" value="{{ old('cost_price', $product->cost_price ?? '') }}">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-success fw-semibold">Retail / Selling Price (৳) <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-success-subtle text-success fw-bold">৳</span>
                            <input type="number" step="0.01" name="price" id="formPrice" class="form-control" placeholder="850" value="{{ old('price', $product->price ?? '') }}" required oninput="updateBadgeSuggestions()">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted fw-semibold">MRP / Original Price (৳)</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light">৳</span>
                            <input type="number" step="0.01" name="old_price" id="formOldPrice" class="form-control" placeholder="e.g. 1250" value="{{ old('old_price', $product->old_price ?? '') }}" oninput="updateBadgeSuggestions()">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Stock Quantity <span class="text-danger">*</span></label>
                        <input type="number" name="stock_qty" class="form-control" placeholder="50" value="{{ old('stock_qty', $product->stock_qty ?? 50) }}" required>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Badge / Highlight Tag</label>
                        <input type="text" name="tag" id="formTag" class="form-control" placeholder="e.g. Top Rated, Best Deal" value="{{ old('tag', $product->tag ?? '') }}">
                        <div id="badgeSuggestionsWrap" class="mt-2 d-flex flex-wrap align-items-center gap-1.5" style="display: none;">
                            <span class="text-muted small me-1" style="font-size: 0.75rem;"><i class="fa-solid fa-wand-magic-sparkles text-warning me-1"></i>Suggested:</span>
                            <div id="badgeSuggestionsList" class="d-inline-flex flex-wrap gap-1.5"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 2. Variant Matrix -->
            <div class="card p-3 p-md-4 mb-4">
                <div class="d-flex align-items-center justify-content-between mb-3 border-bottom pb-2">
                    <div>
                        <h5 class="fw-bold m-0"><i class="fa-solid fa-palette text-primary me-1"></i> Color / Variant Matrix</h5>
                        <small class="text-muted">Set custom variant image, price, and stock</small>
                    </div>
                    <button type="button" class="btn btn-sm btn-success" onclick="addVariantRow()">
                        <i class="fa-solid fa-plus me-1"></i> Add Variant
                    </button>
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered table-sm align-middle" id="variantTable">
                        <thead>
                            <tr>
                                <th style="width: 28%;">Color / Name</th>
                                <th style="width: 18%;">Price (৳)</th>
                                <th style="width: 40%;">Variant Image</th>
                                <th style="width: 12%;">Stock</th>
                                <th style="width: 6%;"></th>
                            </tr>
                        </thead>
                        <tbody id="variantTableBody">
                            @if(!empty($rawVariants) && is_array($rawVariants))
                                @foreach($rawVariants as $vIdx => $v)
                                    @php
                                        $vName = is_array($v) ? ($v['name'] ?? '') : '';
                                        $vPrice = is_array($v) ? ($v['price'] ?? ($product->price ?? '')) : '';
                                        $vImg = is_array($v) ? ($v['image'] ?? '') : '';
                                        $vStock = is_array($v) ? ($v['stock'] ?? 50) : 50;
                                    @endphp
                                    <tr>
                                        <td><input type="text" name="var_name[]" class="form-control form-control-sm" value="{{ $vName }}"></td>
                                        <td><input type="number" step="0.01" name="var_price[]" class="form-control form-control-sm" value="{{ $vPrice }}"></td>
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="border rounded-2 bg-light d-flex align-items-center justify-content-center overflow-hidden flex-shrink-0" style="width: 42px; height: 42px;">
                                                    <img id="formVarPreview_{{ $vIdx }}" src="{{ !empty($vImg) ? product_image_url($vImg) : asset('images/product-placeholder.svg') }}" style="width: 100%; height: 100%; object-fit: cover;" onerror="this.onerror=null;this.src='{{ asset('images/product-placeholder.svg') }}';">
                                                </div>
                                                <div class="flex-grow-1">
                                                    <input type="file" name="var_image_file[]" class="form-control form-control-sm mb-1" accept="image/*" onchange="previewStandaloneVarFile(this, '{{ $vIdx }}')">
                                                    <input type="hidden" name="var_image[]" value="{{ $vImg }}">
                                                </div>
                                            </div>
                                        </td>
                                        <td><input type="number" name="var_stock[]" class="form-control form-control-sm" value="{{ $vStock }}"></td>
                                        <td class="text-center"><button type="button" class="btn btn-sm btn-link text-danger p-0" onclick="this.closest('tr').remove()"><i class="fa-solid fa-circle-minus"></i></button></td>
                                    </tr>
                                @endforeach
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- 3. Specifications (Renamed & Optimized) -->
            <div class="card p-3 p-md-4 mb-4">
                <div class="d-flex align-items-center justify-content-between mb-3 border-bottom pb-2 flex-wrap gap-2">
                    <div>
                        <h5 class="fw-bold m-0"><i class="fa-solid fa-list-check text-primary me-1"></i> Specifications</h5>
                        <small class="text-muted">Define product specs or bulk paste raw text</small>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="collapse" data-bs-target="#bulkSpecBox">
                        <i class="fa-solid fa-wand-magic-sparkles me-1"></i> Bulk Paste Parser
                    </button>
                </div>

                <!-- Bulk Paste Spec Box -->
                <div class="collapse mb-3" id="bulkSpecBox">
                    <div class="p-3 border rounded-3 bg-light">
                        <label class="form-label small fw-semibold">Paste raw specifications (e.g. Battery: 500mAh \n Bluetooth: 5.3):</label>
                        <textarea id="bulkSpecText" class="form-control form-control-sm mb-2" rows="3" placeholder="Key: Value"></textarea>
                        <button type="button" class="btn btn-sm btn-primary" onclick="parseAndInsertBulkSpecs()">Parse & Add Rows</button>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered table-sm align-middle" id="specTable">
                        <thead>
                            <tr>
                                <th style="width: 40%;">Key</th>
                                <th style="width: 52%;">Value</th>
                                <th style="width: 8%; text-align: center;"></th>
                            </tr>
                        </thead>
                        <tbody id="specTableBody">
                            @if(!empty($rawSpecs) && is_array($rawSpecs))
                                @foreach($rawSpecs as $k => $val)
                                    <tr>
                                        <td><input type="text" name="spec_key[]" class="form-control form-control-sm" value="{{ $k }}"></td>
                                        <td><input type="text" name="spec_val[]" class="form-control form-control-sm" value="{{ $val }}"></td>
                                        <td class="text-center"><button type="button" class="btn btn-sm btn-link text-danger p-0" onclick="this.closest('tr').remove()"><i class="fa-solid fa-trash"></i></button></td>
                                    </tr>
                                @endforeach
                            @endif
                        </tbody>
                    </table>
                </div>
                <div class="pt-2 border-top">
                    <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3" onclick="addSpecRow()">
                        <i class="fa-solid fa-plus me-1"></i> Add Spec Row
                    </button>
                </div>
            </div>

            <div class="card p-3 p-md-4 mb-4">
                <div class="d-flex align-items-center justify-content-between mb-3 border-bottom pb-2 flex-wrap gap-2">
                    <h5 class="fw-bold mb-0"><i class="fa-solid fa-align-left text-primary me-1"></i> Descriptions</h5>
                    <div id="standaloneAiStatusBadge"></div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Short Summary</label>
                    <textarea name="short_desc" id="standaloneShortDesc" class="form-control" rows="2" placeholder="Brief 1-2 sentence highlight...">{{ old('short_desc', $product->short_desc ?? '') }}</textarea>
                </div>
                <div>
                    <label class="form-label">Detailed Description</label>
                    <textarea name="description" id="standaloneDesc" class="form-control" rows="6" placeholder="Full product details...">{{ old('description', $product->description ?? '') }}</textarea>
                </div>
            </div>
        </div>

        <!-- Right: Media, Visibility, SEO -->
        <div class="col-lg-4">
            <div class="d-flex flex-column gap-4">
                <!-- Media / Images with Drag & Drop & Paste -->
                <div class="card p-3 p-md-4">
                    <div class="d-flex align-items-center justify-content-between mb-3 border-bottom pb-2">
                        <h5 class="fw-bold mb-0"><i class="fa-solid fa-images text-primary me-1"></i> Product Media</h5>
                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2 py-0.5 small">Auto-WebP</span>
                    </div>

                    <!-- Main Thumbnail -->
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Main Thumbnail</label>
                        <div id="thumbDropZone" class="border border-dashed rounded-3 p-3 text-center bg-light position-relative cursor-pointer" style="min-height: 120px; display: flex; align-items: center; justify-content: center;">
                            <input type="file" name="main_image_file" id="thumbFileInput" class="position-absolute top-0 start-0 w-100 h-100 opacity-0 cursor-pointer" accept="image/*" onchange="previewFileAsThumbnail(this)">
                            <div id="thumbPreviewWrap" class="w-100 h-100 d-flex align-items-center justify-content-center">
                                <img src="{{ old('main_image', (isset($product->main_image) && $product->main_image) ? product_image_url($product->main_image) : asset('images/product-placeholder.svg')) }}" id="mainImagePreview" alt="Preview" style="max-height: 110px; max-width: 100%; object-fit: contain;" onerror="this.onerror=null;this.src='{{ asset('images/product-placeholder.svg') }}';">
                            </div>
                        </div>
                        <input type="hidden" name="main_image" value="{{ old('main_image', $product->main_image ?? '') }}">
                        <small class="text-muted d-block mt-1 text-center" style="font-size: 0.72rem;">Drop, browse or paste image (Ctrl+V)</small>
                    </div>

                    <!-- Gallery Images Dropzone & Paste Zone -->
                    <div>
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <label class="form-label fw-bold small mb-0">Gallery Images (Multiple)</label>
                            <button type="button" class="btn btn-xs btn-outline-primary rounded-pill px-2.5 py-0.5" style="font-size: 0.75rem;" onclick="document.getElementById('standaloneGalleryFiles').click()">
                                <i class="fa-solid fa-plus me-1"></i> Browse
                            </button>
                        </div>

                        <!-- Hidden File Input bound to DataTransfer -->
                        <input type="file" id="standaloneGalleryFiles" multiple accept="image/*" class="d-none" onchange="handleStandaloneFilesSelected(this)">
                        
                        <!-- Drag, Drop & Paste Universal Dropzone -->
                        <div id="galleryDropZone" class="border border-dashed rounded-3 p-3 text-center bg-light mb-2 cursor-pointer position-relative">
                            <div class="py-2">
                                <i class="fa-duotone fa-cloud-arrow-up fs-3 text-primary mb-1"></i>
                                <p class="mb-0 small fw-bold text-dark">Drag & Drop or Paste Images Here</p>
                                <span class="text-muted" style="font-size: 0.7rem;">Press Ctrl+V to paste copied images</span>
                            </div>
                        </div>

                        <div id="standaloneExistingGalleryContainer">
                            @if(!empty($rawGallery) && is_array($rawGallery))
                                @foreach($rawGallery as $egImg)
                                    <input type="hidden" name="existing_gallery[]" value="{{ $egImg }}">
                                @endforeach
                            @endif
                        </div>

                        <div class="p-2 border rounded-3 bg-white" style="min-height: 90px;">
                            <div id="standaloneGalleryVisualGrid" class="d-flex flex-wrap gap-2 align-items-center">
                                <span class="text-muted small fst-italic w-100 text-center py-3" id="standaloneGalEmpty">No gallery images uploaded yet.</span>
                            </div>
                        </div>
                    </div>

                    <!-- YouTube Video -->
                    <div class="mt-4 pt-3 border-top">
                        <label class="form-label fw-bold small"><i class="fa-brands fa-youtube text-danger me-1"></i> YouTube Video URL</label>
                        <input type="url" name="video_url" id="standaloneVideoUrl" class="form-control form-control-sm" placeholder="https://youtube.com/watch?v=..." value="{{ old('video_url', $product->video_url ?? '') }}" oninput="updateStandaloneVideoPreview(this.value)">
                    </div>
                </div>

                <!-- Visibility -->
                <div class="card p-3 p-md-4">
                    <div class="d-flex align-items-center justify-content-between mb-3 border-bottom pb-2">
                        <div class="d-flex align-items-center gap-2">
                            <div class="rounded-circle bg-primary-subtle text-primary d-flex align-items-center justify-content-center" style="width: 36px; height: 36px; font-size: 1rem;">
                                <i class="fa-solid fa-sliders"></i>
                            </div>
                            <div>
                                <h5 class="fw-bold mb-0">Status & Visibility</h5>
                                <small class="text-muted" style="font-size: 0.72rem;">Live storefront & merchandising controls</small>
                            </div>
                        </div>
                        <div>
                            <span id="standaloneStatusLiveBadge" class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-1 small fw-semibold d-inline-flex align-items-center gap-1.5">
                                <span class="status-live-dot"></span>
                                <span>Live</span>
                            </span>
                        </div>
                    </div>

                    <div class="d-flex flex-column gap-2.5 mb-3">
                        <div class="status-toggle-card {{ old('is_active', $product->is_active ?? 1) ? 'active-live' : '' }}" id="cardFormIsActive" onclick="toggleStandaloneStatusSwitch('isActiveSwitch')">
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
                                    <input class="form-check-input" type="checkbox" name="is_active" id="isActiveSwitch" value="1" {{ old('is_active', $product->is_active ?? 1) ? 'checked' : '' }} onchange="updateStandaloneStatusCardVisuals()">
                                </div>
                            </div>
                        </div>

                        <div class="status-toggle-card {{ old('is_featured', $product->is_featured ?? 0) ? 'active-featured' : '' }}" id="cardFormIsFeatured" onclick="toggleStandaloneStatusSwitch('isFeaturedSwitch')">
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
                                    <input class="form-check-input" type="checkbox" name="is_featured" id="isFeaturedSwitch" value="1" {{ old('is_featured', $product->is_featured ?? 0) ? 'checked' : '' }} onchange="updateStandaloneStatusCardVisuals()">
                                </div>
                            </div>
                        </div>

                        <div class="status-toggle-card {{ old('is_flash_deal', $product->is_flash_deal ?? 0) ? 'active-flash' : '' }}" id="cardFormIsFlashDeal" onclick="toggleStandaloneStatusSwitch('isFlashSwitch')">
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
                                    <input class="form-check-input" type="checkbox" name="is_flash_deal" id="isFlashSwitch" value="1" {{ old('is_flash_deal', $product->is_flash_deal ?? 0) ? 'checked' : '' }} onchange="updateStandaloneStatusCardVisuals()">
                                </div>
                            </div>
                        </div>

                        <div class="status-toggle-card {{ old('is_free_shipping', $product->is_free_shipping ?? 0) ? 'active-shipping' : '' }}" id="cardFormIsFreeShipping" onclick="toggleStandaloneStatusSwitch('isFreeShippingSwitch')">
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
                                    <input class="form-check-input" type="checkbox" name="is_free_shipping" id="isFreeShippingSwitch" value="1" {{ old('is_free_shipping', $product->is_free_shipping ?? 0) ? 'checked' : '' }} onchange="updateStandaloneStatusCardVisuals()">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="p-3 rounded-3 border bg-light mb-3">
                        <div class="d-flex align-items-center justify-content-between mb-1.5">
                            <span class="small fw-bold text-secondary text-uppercase" style="font-size: 0.68rem; letter-spacing: 0.5px;">Storefront Badges Preview</span>
                            <span class="badge bg-white text-muted border small" style="font-size: 0.65rem;">Real-time</span>
                        </div>
                        <div id="standaloneStatusPreviewBadges" class="d-flex flex-wrap gap-1.5">
                        </div>
                    </div>

                    <div class="d-flex flex-column gap-2 border-top pt-3">
                        <button type="submit" class="btn btn-primary py-2.5 fw-bold w-100 shadow-sm rounded-pill">
                            <i class="fa-solid fa-floppy-disk me-1"></i> {{ $isEdit ? 'Save Changes' : 'Publish Product' }}
                        </button>
                        <a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary py-2 w-100 rounded-pill">Cancel</a>
                    </div>
                </div>

                <div class="card p-3 p-md-4">
                    <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-3 flex-wrap gap-2">
                        <h5 class="fw-bold mb-0"><i class="fa-solid fa-magnifying-glass text-primary me-1"></i> SEO Metadata</h5>
                        <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3 shadow-sm" id="btn-standalone-ai" onclick="generateStandaloneAISEO()">
                            <i class="fa-solid fa-wand-magic-sparkles me-1"></i> AI Auto-Fill
                        </button>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Meta Title</label>
                        <input type="text" name="meta_title" id="standaloneMetaTitle" class="form-control form-control-sm" value="{{ old('meta_title', $product->meta_title ?? '') }}">
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Meta Description</label>
                        <textarea name="meta_description" id="standaloneMetaDesc" class="form-control form-control-sm" rows="2">{{ old('meta_description', $product->meta_description ?? '') }}</textarea>
                    </div>
                    <div>
                        <label class="form-label">Meta Keywords</label>
                        <input type="text" name="meta_keywords" id="standaloneMetaKeywords" class="form-control form-control-sm" value="{{ old('meta_keywords', $product->meta_keywords ?? '') }}" placeholder="e.g. wireless earbuds, bluetooth headphones, zippy">
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection

@push('scripts')
<script>
(() => {
    let dataTransferFiles = new DataTransfer();
    let standaloneExistingImages = {!! json_encode(!empty($rawGallery) && is_array($rawGallery) ? array_values($rawGallery) : []) !!};

    function previewFileAsThumbnail(input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const preview = document.getElementById('mainImagePreview');
                if (preview) preview.src = e.target.result;
            };
            reader.readAsDataURL(input.files[0]);
        }
    }

    function handleStandaloneFilesSelected(inputOrFiles) {
        let files = inputOrFiles.files || inputOrFiles;
        if (files && files.length > 0) {
            Array.from(files).forEach(file => {
                dataTransferFiles.items.add(file);
            });
            const hiddenFileInput = document.getElementById('standaloneGalleryFiles');
            hiddenFileInput.files = dataTransferFiles.files;
            renderStandaloneGalleryGrid();
        }
    }

    function removeStandaloneNewFile(index) {
        const dt = new DataTransfer();
        const currentFiles = dataTransferFiles.files;
        for (let i = 0; i < currentFiles.length; i++) {
            if (i !== index) {
                dt.items.add(currentFiles[i]);
            }
        }
        dataTransferFiles = dt;
        document.getElementById('standaloneGalleryFiles').files = dataTransferFiles.files;
        renderStandaloneGalleryGrid();
    }

    function removeStandaloneExistingImage(idx) {
        if (idx >= 0 && idx < standaloneExistingImages.length) {
            standaloneExistingImages.splice(idx, 1);
            renderStandaloneGalleryGrid();
        }
    }

    function renderStandaloneGalleryGrid() {
        const grid = document.getElementById('standaloneGalleryVisualGrid');
        const container = document.getElementById('standaloneExistingGalleryContainer');
        if (!grid) return;

        if (container) {
            let hiddenHtml = '';
            standaloneExistingImages.forEach(imgUrl => {
                hiddenHtml += `<input type="hidden" name="existing_gallery[]" value="${imgUrl}">`;
            });
            container.innerHTML = hiddenHtml;
        }

        const total = standaloneExistingImages.length + dataTransferFiles.files.length;
        if (total === 0) {
            grid.innerHTML = '<span class="text-muted small fst-italic w-100 text-center py-3" id="standaloneGalEmpty">No gallery images uploaded yet. Drag, browse or paste.</span>';
            return;
        }

        grid.innerHTML = '';

        standaloneExistingImages.forEach((imgUrl, idx) => {
            let previewSrc = imgUrl || '';
            if (previewSrc && !previewSrc.startsWith('http://') && !previewSrc.startsWith('https://') && !previewSrc.startsWith('/') && !previewSrc.startsWith('data:') && !previewSrc.startsWith('blob:')) {
                previewSrc = '/storage/' + previewSrc;
            }
            const card = document.createElement('div');
            card.className = 'position-relative border rounded-3 overflow-hidden bg-white shadow-sm';
            card.style = 'width: 75px; height: 75px; flex-shrink: 0;';
            card.innerHTML = `
                <img src="${previewSrc}" style="width: 100%; height: 100%; object-fit: cover;" onerror="this.onerror=null;this.src='{{ asset('images/product-placeholder.svg') }}';">
                <button type="button" class="btn btn-danger btn-sm p-0 position-absolute top-0 end-0 m-1 rounded-circle d-flex align-items-center justify-content-center shadow" style="width: 20px; height: 20px; font-size: 0.6rem;" onclick="removeStandaloneExistingImage(${idx})" title="Delete">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            `;
            grid.appendChild(card);
        });

        Array.from(dataTransferFiles.files).forEach((file, idx) => {
            const card = document.createElement('div');
            card.className = 'position-relative border rounded-3 overflow-hidden bg-white shadow-sm';
            card.style = 'width: 75px; height: 75px; flex-shrink: 0;';

            const reader = new FileReader();
            reader.onload = function(e) {
                card.innerHTML = `
                    <img src="${e.target.result}" style="width: 100%; height: 100%; object-fit: cover;">
                    <button type="button" class="btn btn-danger btn-sm p-0 position-absolute top-0 end-0 m-1 rounded-circle d-flex align-items-center justify-content-center shadow" style="width: 20px; height: 20px; font-size: 0.6rem;" onclick="removeStandaloneNewFile(${idx})" title="Remove">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                `;
            };
            reader.readAsDataURL(file);
            grid.appendChild(card);
        });
    }

    let currentHoverZone = 'gallery';

    function initDragDropAndPaste() {
        const galleryZone = document.getElementById('galleryDropZone');
        const thumbZone = document.getElementById('thumbDropZone');

        if (galleryZone) {
            ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
                galleryZone.addEventListener(eventName, (e) => { e.preventDefault(); e.stopPropagation(); }, false);
            });
            galleryZone.addEventListener('mouseenter', () => { currentHoverZone = 'gallery'; });
            galleryZone.addEventListener('drop', (e) => {
                if (e.dataTransfer && e.dataTransfer.files) {
                    handleStandaloneFilesSelected(e.dataTransfer.files);
                }
            });
            galleryZone.onclick = () => document.getElementById('standaloneGalleryFiles').click();
        }

        if (thumbZone) {
            ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
                thumbZone.addEventListener(eventName, (e) => { e.preventDefault(); e.stopPropagation(); }, false);
            });
            thumbZone.addEventListener('mouseenter', () => { currentHoverZone = 'thumb'; });
            thumbZone.addEventListener('drop', (e) => {
                if (e.dataTransfer && e.dataTransfer.files && e.dataTransfer.files[0]) {
                    const input = document.getElementById('thumbFileInput');
                    input.files = e.dataTransfer.files;
                    previewFileAsThumbnail(input);
                }
            });
        }

        window.addEventListener('paste', (e) => {
            const clipboardData = e.clipboardData || e.originalEvent?.clipboardData;
            if (!clipboardData) return;
            const items = clipboardData.items || [];
            const pastedFiles = [];
            for (let item of items) {
                if (item.type.indexOf('image') === 0) {
                    const file = item.getAsFile();
                    if (file) pastedFiles.push(file);
                }
            }
            if (pastedFiles.length > 0) {
                e.preventDefault();
                if (currentHoverZone === 'thumb') {
                    const input = document.getElementById('thumbFileInput');
                    const dt = new DataTransfer();
                    dt.items.add(pastedFiles[0]);
                    input.files = dt.files;
                    previewFileAsThumbnail(input);
                } else {
                    handleStandaloneFilesSelected(pastedFiles);
                }
            }
        });
    }

    function parseAndInsertBulkSpecs() {
        const text = document.getElementById('bulkSpecText').value;
        if (!text.trim()) return;

        const rawLines = text.split(/\r?\n/).map(l => l.trim()).filter(l => l.length > 0);
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
                if (key) appendSpecRow(key, val);
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
                    if (key) appendSpecRow(key, val);
                } else if (line.includes(':')) {
                    const parts = line.split(':');
                    const key = parts[0].trim();
                    const val = parts.slice(1).join(':').trim();
                    if (key) appendSpecRow(key, val || '-');
                    i++;
                } else if (line.includes('\t')) {
                    const parts = line.split('\t');
                    const key = parts[0].trim();
                    const val = parts.slice(1).join(' ').trim();
                    if (key) appendSpecRow(key, val || '-');
                    i++;
                } else if (line.includes(' - ')) {
                    const parts = line.split(' - ');
                    const key = parts[0].trim();
                    const val = parts.slice(1).join(' - ').trim();
                    if (key) appendSpecRow(key, val || '-');
                    i++;
                } else if (line.includes('—')) {
                    const parts = line.split('—');
                    const key = parts[0].trim();
                    const val = parts.slice(1).join('—').trim();
                    if (key) appendSpecRow(key, val || '-');
                    i++;
                } else {
                    if (i + 1 < rawLines.length && !isDelimited(rawLines[i + 1])) {
                        appendSpecRow(line, rawLines[i + 1]);
                        i += 2;
                    } else {
                        appendSpecRow(line, '-');
                        i++;
                    }
                }
            }
        }

        document.getElementById('bulkSpecText').value = '';
    }

    function appendSpecRow(key = '', val = '') {
        const tbody = document.getElementById('specTableBody');
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td><input type="text" name="spec_key[]" class="form-control form-control-sm" placeholder="Key" value="${key}"></td>
            <td><input type="text" name="spec_val[]" class="form-control form-control-sm" placeholder="Value" value="${val}"></td>
            <td class="text-center"><button type="button" class="btn btn-sm btn-link text-danger p-0" onclick="this.closest('tr').remove()"><i class="fa-solid fa-trash"></i></button></td>
        `;
        tbody.appendChild(tr);
    }

    function addSpecRow() {
        appendSpecRow();
    }

    let formVarCounter = 200;
    function addVariantRow() {
        const tbody = document.getElementById('variantTableBody');
        const tr = document.createElement('tr');
        const rowId = ++formVarCounter;
        tr.innerHTML = `
            <td><input type="text" name="var_name[]" class="form-control form-control-sm" placeholder="e.g. Matte Black"></td>
            <td><input type="number" step="0.01" name="var_price[]" class="form-control form-control-sm" placeholder="850"></td>
            <td>
                <div class="d-flex align-items-center gap-2">
                    <div class="border rounded-2 bg-light d-flex align-items-center justify-content-center overflow-hidden flex-shrink-0" style="width: 42px; height: 42px;">
                        <img id="formVarPreview_${rowId}" src="{{ asset('images/product-placeholder.svg') }}" style="width: 100%; height: 100%; object-fit: cover;" onerror="this.onerror=null;this.src='{{ asset('images/product-placeholder.svg') }}';">
                    </div>
                    <div class="flex-grow-1">
                        <input type="file" name="var_image_file[]" class="form-control form-control-sm mb-1" accept="image/*" onchange="previewStandaloneVarFile(this, '${rowId}')">
                        <input type="hidden" name="var_image[]" value="">
                    </div>
                </div>
            </td>
            <td><input type="number" name="var_stock[]" class="form-control form-control-sm" placeholder="50" value="50"></td>
            <td class="text-center"><button type="button" class="btn btn-sm btn-link text-danger p-0" onclick="this.closest('tr').remove()"><i class="fa-solid fa-circle-minus"></i></button></td>
        `;
        tbody.appendChild(tr);
    }

    function previewStandaloneVarFile(fileInput, rowId) {
        if (fileInput.files && fileInput.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const img = document.getElementById('formVarPreview_' + rowId);
                if (img) img.src = e.target.result;
            };
            reader.readAsDataURL(fileInput.files[0]);
        }
    }

    function toggleStandaloneStatusSwitch(switchId) {
        const sw = document.getElementById(switchId);
        if (sw) {
            sw.checked = !sw.checked;
            updateStandaloneStatusCardVisuals();
        }
    }

    function updateStandaloneStatusCardVisuals() {
        const activeSw = document.getElementById('isActiveSwitch');
        const featuredSw = document.getElementById('isFeaturedSwitch');
        const flashSw = document.getElementById('isFlashSwitch');
        const shippingSw = document.getElementById('isFreeShippingSwitch');

        const cardActive = document.getElementById('cardFormIsActive');
        const cardFeatured = document.getElementById('cardFormIsFeatured');
        const cardFlash = document.getElementById('cardFormIsFlashDeal');
        const cardShipping = document.getElementById('cardFormIsFreeShipping');

        const liveBadge = document.getElementById('standaloneStatusLiveBadge');
        const badgesPreview = document.getElementById('standaloneStatusPreviewBadges');

        if (cardActive && activeSw) {
            activeSw.checked ? cardActive.classList.add('active-live') : cardActive.classList.remove('active-live');
        }
        if (cardFeatured && featuredSw) {
            featuredSw.checked ? cardFeatured.classList.add('active-featured') : cardFeatured.classList.remove('active-featured');
        }
        if (cardFlash && flashSw) {
            flashSw.checked ? cardFlash.classList.add('active-flash') : cardFlash.classList.remove('active-flash');
        }
        if (cardShipping && shippingSw) {
            shippingSw.checked ? cardShipping.classList.add('active-shipping') : cardShipping.classList.remove('active-shipping');
        }

        if (liveBadge && activeSw) {
            if (activeSw.checked) {
                liveBadge.className = 'badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-1 small fw-semibold d-inline-flex align-items-center gap-1.5';
                liveBadge.innerHTML = '<span class="status-live-dot"></span><span>Live</span>';
            } else {
                liveBadge.className = 'badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill px-2.5 py-1 small fw-semibold d-inline-flex align-items-center gap-1.5';
                liveBadge.innerHTML = '<span class="status-draft-dot"></span><span>Draft</span>';
            }
        }

        if (badgesPreview) {
            let badgesHtml = '';
            if (activeSw && activeSw.checked) {
                badgesHtml += '<span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2 py-0.5 small fw-semibold"><i class="fa-solid fa-circle-check me-1"></i>Visible</span>';
            } else {
                badgesHtml += '<span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill px-2 py-0.5 small fw-semibold"><i class="fa-solid fa-eye-slash me-1"></i>Hidden</span>';
            }
            if (featuredSw && featuredSw.checked) {
                badgesHtml += '<span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill px-2 py-0.5 small fw-semibold"><i class="fa-solid fa-star me-1"></i>Featured</span>';
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

    function initPage() {
        renderStandaloneGalleryGrid();
        initDragDropAndPaste();
        updateStandaloneStatusCardVisuals();
    }

    document.addEventListener('turbo:load', initPage);
    document.addEventListener('DOMContentLoaded', initPage);

    function generateStandaloneAISEO() {
        const titleInput = document.getElementById('standaloneTitle');
        if (!titleInput || !titleInput.value.trim()) {
            alert('Please enter a product title first!');
            if (titleInput) titleInput.focus();
            return;
        }

        const title = titleInput.value.trim();
        const catSelect = document.getElementById('standaloneCategory');
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

        const statusBadge = document.getElementById('standaloneAiStatusBadge');
        const btn = document.getElementById('btn-standalone-ai');

        if (statusBadge) {
            statusBadge.innerHTML = '<span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-2.5 py-1 small fw-semibold d-inline-flex align-items-center gap-1.5"><span class="spinner-border spinner-border-sm" role="status" style="width: 12px; height: 12px;"></span><span>AI Generating...</span></span>';
        }
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-1"></i> Generating...';
        }

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
            if (res && res.success && res.data) {
                const data = res.data;
                const shortDescEl = document.getElementById('standaloneShortDesc');
                const descEl = document.getElementById('standaloneDesc');
                const metaTitleEl = document.getElementById('standaloneMetaTitle');
                const metaDescEl = document.getElementById('standaloneMetaDesc');
                const keywordsEl = document.getElementById('standaloneMetaKeywords');

                if (shortDescEl) shortDescEl.value = data.short_desc || '';
                if (descEl) descEl.value = data.detailed_html_description || '';
                if (metaTitleEl) metaTitleEl.value = data.meta_title || '';
                if (metaDescEl) metaDescEl.value = data.meta_description || '';
                if (keywordsEl) keywordsEl.value = data.meta_keywords || '';

                if (statusBadge) {
                    statusBadge.innerHTML = '<span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-1 small fw-semibold d-inline-flex align-items-center gap-1.5"><i class="fa-solid fa-wand-magic-sparkles"></i><span>AI Generated (Quality Matched)</span></span>';
                }
            }
        })
        .catch(err => {
            if (statusBadge) {
                statusBadge.innerHTML = '';
            }
        })
        .finally(() => {
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = '<i class="fa-solid fa-wand-magic-sparkles me-1"></i> AI Auto-Fill';
            }
        });
    }

    window.generateStandaloneAISEO = generateStandaloneAISEO;
    window.previewFileAsThumbnail = previewFileAsThumbnail;
    window.handleStandaloneFilesSelected = handleStandaloneFilesSelected;
    window.removeStandaloneNewFile = removeStandaloneNewFile;
    window.removeStandaloneExistingImage = removeStandaloneExistingImage;
    window.addVariantRow = addVariantRow;
    window.previewStandaloneVarFile = previewStandaloneVarFile;
    window.addSpecRow = addSpecRow;
    window.parseAndInsertBulkSpecs = parseAndInsertBulkSpecs;
    window.toggleStandaloneStatusSwitch = toggleStandaloneStatusSwitch;
    window.updateStandaloneStatusCardVisuals = updateStandaloneStatusCardVisuals;
    window.regenerateStandaloneSku = function() {
        const title = document.getElementById('standaloneTitle').value;
        const skuInput = document.getElementById('standaloneSku');
        if (skuInput) skuInput.value = 'ZB-' + Math.floor(1000 + Math.random() * 9000);
    };
    window.handleStandaloneTitleForSku = function(title) {
        const skuInput = document.getElementById('standaloneSku');
        if (skuInput && !skuInput.value) {
            skuInput.value = 'ZB-' + Math.floor(1000 + Math.random() * 9000);
        }
    };
    window.updateStandaloneVideoPreview = function(url) {};
})();
</script>
@endpush