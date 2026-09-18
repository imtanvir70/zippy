@extends('backend.layouts.app')

@section('title', 'AI Product Generator Studio')

@push('styles')
<style>
.status-toggle-card {
    border: 1px solid rgba(226, 232, 240, 0.9);
    border-radius: 12px;
    background: #ffffff;
    padding: 10px 12px;
    cursor: pointer;
    transition: all 0.22s cubic-bezier(0.4, 0, 0.2, 1);
    user-select: none;
}
.status-toggle-card:hover {
    border-color: rgba(148, 163, 184, 0.6);
    transform: translateY(-1px);
    box-shadow: 0 4px 12px -2px rgba(15, 23, 42, 0.06);
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
    width: 34px;
    height: 34px;
    border-radius: 9px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    font-size: 1rem;
    transition: transform 0.22s ease;
}
.status-toggle-card:hover .status-icon-box {
    transform: scale(1.06);
}
.status-toggle-card .form-check-input {
    width: 2.5rem;
    height: 1.35rem;
    cursor: pointer;
    flex-shrink: 0;
    margin-top: 0;
}
#aiIsActiveSwitch:checked {
    background-color: #10b981;
    border-color: #10b981;
}
#aiIsFeaturedSwitch:checked {
    background-color: #f59e0b;
    border-color: #f59e0b;
}
#aiIsFlashSwitch:checked {
    background-color: #ef4444;
    border-color: #ef4444;
}
#aiIsFreeShippingSwitch:checked {
    background-color: #06b6d4;
    border-color: #06b6d4;
}
@keyframes statusPulseAnimation {
    0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.6); }
    70% { transform: scale(1); box-shadow: 0 0 0 6px rgba(16, 185, 129, 0); }
    100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
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
<div class="card p-3 p-md-4 mb-4 border-0 shadow-sm">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
        <div class="d-flex align-items-center gap-3">
            <div class="stat-icon d-flex align-items-center justify-content-center text-white" style="width: 52px; height: 52px; border-radius: 14px; background: linear-gradient(135deg, #6366f1 0%, #a855f7 100%); font-size: 22px;">
                <i class="fa-solid fa-wand-magic-sparkles"></i>
            </div>
            <div>
                <div class="d-flex align-items-center gap-2">
                    <h3 class="fw-bold mb-0">Vision AI Product Studio</h3>
                    <span class="badge bg-primary-subtle text-primary rounded-pill px-2.5 py-1 small fw-semibold">
                        <i class="fa-solid fa-sparkles me-1"></i> Zero-Input Vision Merchandising
                    </span>
                </div>
                <small class="text-muted">Drop a product photo to automatically deduce SKU details, descriptions, specifications, variants, and pricing.</small>
            </div>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary btn-sm px-3 py-2 fw-semibold rounded-3">
                <i class="fa-solid fa-arrow-left me-1"></i> Back to Products
            </a>
            <a href="{{ route('admin.settings.ai') }}" class="btn btn-outline-primary btn-sm px-3 py-2 fw-semibold rounded-3">
                <i class="fa-solid fa-brain me-1"></i> AI Hub
            </a>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-5">
        <div class="card p-3 p-md-4 border-0 shadow-sm h-100 d-flex flex-column">
            <div class="d-flex align-items-center justify-content-between pb-3 mb-3 border-bottom">
                <h6 class="fw-bold mb-0 text-primary d-flex align-items-center gap-2">
                    <i class="fa-solid fa-camera-viewfinder"></i>
                    <span>Product Photo &amp; Vision AI</span>
                </h6>
                <span class="badge bg-secondary-subtle text-secondary rounded-pill px-2 py-0.5 small">Step 1: Vision Input</span>
            </div>

            <div class="d-flex flex-column gap-3 flex-grow-1">
                <div>
                    <input type="file" id="imageFileInput" accept="image/*" class="d-none" onchange="handleFileSelected(this)">
                    
                    <div id="dropzoneArea" class="p-4 text-center rounded-4 border border-2 border-dashed d-flex flex-column align-items-center justify-content-center" style="min-height: 250px; background: rgba(99, 102, 241, 0.03); cursor: pointer; transition: all 0.25s ease;" onclick="document.getElementById('imageFileInput').click()">
                        <div id="dropzoneIdle">
                            <div class="stat-icon d-inline-flex align-items-center justify-content-center text-primary mb-3" style="width: 64px; height: 64px; border-radius: 50%; background: rgba(99, 102, 241, 0.1); font-size: 26px;">
                                <i class="fa-solid fa-cloud-arrow-up"></i>
                            </div>
                            <h6 class="fw-bold mb-1 text-body">Drag, Drop or Paste Product Image</h6>
                            <p class="text-muted small mb-3">or click to browse / press <kbd class="px-1.5 py-0.5 border rounded bg-body-tertiary">Ctrl+V</kbd> (PNG, JPG, WEBP)</p>
                            <button type="button" class="btn btn-outline-primary btn-sm rounded-pill px-3 py-1.5 fw-semibold">
                                <i class="fa-solid fa-folder-open me-1"></i> Browse Photo
                            </button>
                        </div>

                        <div id="dropzonePreview" class="d-none w-100 text-center">
                            <div class="position-relative d-inline-block mb-3">
                                <img id="previewImg" src="" alt="Product Preview" class="rounded-3 border shadow-sm" style="max-height: 220px; max-width: 100%; object-fit: contain;">
                                <button type="button" class="btn btn-sm btn-danger rounded-circle position-absolute top-0 end-0 m-1 shadow-sm" style="width: 28px; height: 28px; padding: 0;" onclick="event.stopPropagation(); removeUploadedImage()" title="Remove image">
                                    <i class="fa-solid fa-xmark"></i>
                                </button>
                            </div>
                            <div>
                                <span class="badge bg-success-subtle text-success rounded-pill px-2.5 py-1 small fw-semibold">
                                    <i class="fa-solid fa-check-circle me-1"></i> Photo Ready for Vision Scan
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <div>
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <label class="form-label small fw-bold mb-0">Or Paste Public Image URL</label>
                        <small class="text-muted" style="font-size: 11px;">Direct web link</small>
                    </div>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-body-secondary"><i class="fa-solid fa-link"></i></span>
                        <input type="text" id="imageUrlInput" class="form-control font-monospace" placeholder="https://example.com/product.jpg" oninput="handleUrlInput(this.value)">
                        <button type="button" class="btn btn-outline-secondary" onclick="loadImageUrl()">Load</button>
                    </div>
                </div>

                <div>
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <label class="form-label small fw-bold mb-0 d-flex align-items-center gap-1">
                            <i class="fa-solid fa-images text-primary"></i>
                            <span>Gallery Images (Max 10)</span>
                        </label>
                        <span class="text-muted fw-normal" style="font-size: 11px;" id="galleryCountBadge">0/10 added</span>
                    </div>

                    <input type="file" id="galleryFilesInput" accept="image/*" multiple class="d-none" onchange="handleGalleryFilesSelected(this)">

                    <div id="galleryDropzoneArea" class="p-3 text-center rounded-3 border border-2 border-dashed d-flex flex-column align-items-center justify-content-center" style="min-height: 80px; background: rgba(99, 102, 241, 0.02); cursor: pointer; transition: all 0.2s ease;" onclick="document.getElementById('galleryFilesInput').click()">
                        <div class="d-flex align-items-center justify-content-center gap-2 text-primary">
                            <i class="fa-solid fa-cloud-arrow-up fs-5"></i>
                            <span class="small fw-semibold">Click, drop or paste (<kbd class="px-1 py-0.5 border rounded bg-body-tertiary">Ctrl+V</kbd>) gallery images</span>
                        </div>
                        <small class="text-muted" style="font-size: 11px;">Upload up to 10 additional product angles, close-ups, or labels</small>
                    </div>

                    <div id="galleryPreviewContainer" class="d-flex flex-wrap gap-2 mt-2 d-none"></div>
                </div>

                <div>
                    <label class="form-label small fw-bold mb-1 d-flex align-items-center justify-content-between">
                        <span><i class="fa-solid fa-language text-primary me-1"></i> Content Language</span>
                        <span class="text-muted fw-normal" style="font-size: 11px;">Output copywriting language</span>
                    </label>
                    <select id="contentLanguageSelect" class="form-select form-select-sm">
                        <option value="Mixed (English & Bengali)" selected>Mixed (English & Bengali) - Recommended</option>
                        <option value="Bengali">Bengali (বাংলা)</option>
                        <option value="English">English</option>
                    </select>
                </div>

                <div>
                    <label class="form-label small fw-bold mb-1 d-flex align-items-center justify-content-between">
                        <span><i class="fa-solid fa-sparkles text-primary me-1"></i> Quick Hint / Context (Optional)</span>
                        <span class="text-muted fw-normal" style="font-size: 11px;">E.g. Target price, brand, key highlights</span>
                    </label>
                    <textarea id="quickHintInput" class="form-control" rows="3" placeholder="e.g. Set selling price to 2400, highlight water resistance, breathable mesh, suitable for daily gym and running."></textarea>
                    <small class="text-muted d-block mt-1" style="font-size: 11px;">Leave blank to let AI deduce all values, realistic pricing, specifications, and variants automatically.</small>
                </div>

                <div class="mt-auto pt-3 border-top">
                    <button type="button" class="btn btn-primary w-100 py-3 fw-bold fs-6 rounded-3 shadow d-flex align-items-center justify-content-center gap-2" id="btnScanGenerate" onclick="startVisionScan()">
                        <i class="fa-solid fa-wand-magic-sparkles"></i>
                        <span>✨ Scan Image &amp; Generate Entire Product</span>
                    </button>
                    <small class="text-muted d-block text-center mt-2" style="font-size: 11px;">
                        <i class="fa-solid fa-shield-heart text-danger me-1"></i> Powered by Vision Engine with Instant Failover Hot Backup
                    </small>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-7">
        <div class="card p-3 p-md-4 border-0 shadow-sm h-100 d-flex flex-column">
            <div class="d-flex align-items-center justify-content-between pb-3 mb-3 border-bottom flex-wrap gap-2">
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <h6 class="fw-bold mb-0 text-primary d-flex align-items-center gap-2">
                        <i class="fa-solid fa-file-lines"></i>
                        <span>Catalog Output &amp; Editor</span>
                    </h6>
                    <span id="outputEngineBadge" class="badge bg-secondary-subtle text-secondary rounded-pill px-2.5 py-1 small">Awaiting Vision Scan</span>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <button type="button" class="btn btn-outline-primary btn-sm px-2.5 py-1.5 fw-semibold rounded-3 d-none align-items-center gap-1.5" id="btnQuickRegenerateCopy" onclick="regenerateCopyAndSeo()" title="Re-run copywriting using current Title, Price, Category, and Specs">
                        <i class="fa-solid fa-wand-magic-sparkles text-warning"></i>
                        <span>✨ Re-write Copy &amp; SEO</span>
                    </button>
                    <button type="button" class="btn btn-success btn-sm px-3 py-1.5 fw-bold rounded-3 shadow-sm d-flex align-items-center gap-1.5" id="btnSaveTop" onclick="saveProductToCatalog()" disabled>
                        <i class="fa-solid fa-floppy-disk"></i>
                        <span>💾 Save to Catalog</span>
                    </button>
                </div>
            </div>

            <div id="outputEmptyPlaceholder" class="p-5 text-center my-auto">
                <div class="stat-icon d-inline-flex align-items-center justify-content-center text-muted mb-3" style="width: 72px; height: 72px; border-radius: 50%; background: var(--bs-tertiary-bg); font-size: 32px;">
                    <i class="fa-solid fa-sparkles text-primary"></i>
                </div>
                <h5 class="fw-bold">AI Vision Engine Ready</h5>
                <p class="text-muted small mx-auto mb-0" style="max-width: 420px; line-height: 1.6;">
                    Drop or paste a product photo on the left and click <strong>"Scan Image &amp; Generate Entire Product"</strong>. The AI will inspect the photo, detect product identity, compose SEO title, description, specifications, color variants, and calculate market pricing.
                </p>
            </div>

            <div id="outputFormContainer" class="d-none d-flex flex-column gap-3 flex-grow-1">
                <div>
                    <label class="form-label small fw-bold mb-1">Product Title <span class="text-danger">*</span></label>
                    <input type="text" id="fieldTitle" class="form-control form-control-lg fw-semibold fs-6" placeholder="Product Title">
                </div>

                <div class="row g-2">
                    <div class="col-md-6">
                        <label class="form-label small fw-bold mb-1">Generated SKU <span class="text-danger">*</span></label>
                        <input type="text" id="fieldSku" class="form-control font-monospace" placeholder="ZB-...">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold mb-1">URL Slug</label>
                        <input type="text" id="fieldSlug" class="form-control font-monospace" placeholder="product-slug">
                    </div>
                </div>

                <div class="row g-2">
                    <div class="col-md-7">
                        <div class="d-flex align-items-center justify-content-between mb-1">
                            <label class="form-label small fw-bold mb-0">Category <span class="text-danger">*</span></label>
                            <button type="button" id="btnCreateCategory" class="btn btn-sm btn-outline-primary py-0 px-2 rounded-2 fw-semibold d-none align-items-center gap-1" style="font-size: 11px;" onclick="createAndSelectCategory()">
                                <i class="fa-solid fa-bolt text-warning"></i>
                                <span id="btnCreateCategoryText">⚡ Create &amp; Select</span>
                            </button>
                        </div>
                        <select id="fieldCategory" class="form-select">
                            <option value="">Select Category</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" data-name="{{ strtolower($cat->name) }}" data-hierarchy="{{ strtolower($cat->hierarchy_name ?? $cat->name) }}">
                                    {{ $cat->hierarchy_name ?? $cat->name }} {{ !empty($cat->name_bn) ? '('.$cat->name_bn.')' : '' }}
                                </option>
                            @endforeach
                        </select>
                        <small id="categoryAiSuggested" class="d-block mt-1 small" style="font-size: 11px;"></small>
                    </div>
                    <div class="col-md-5">
                        <label class="form-label small fw-bold mb-1">Promotion Tag / Badge</label>
                        <input type="text" id="fieldTag" class="form-control" placeholder="e.g. HOT, NEW, 20% OFF">
                    </div>
                </div>

                <div class="p-3 rounded-3 border bg-body-tertiary">
                    <div class="row g-2">
                        <div class="col-6 col-md-3">
                            <label class="form-label small fw-bold mb-1">Selling Price (৳) <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" id="fieldSellingPrice" class="form-control font-monospace fw-bold text-primary">
                        </div>
                        <div class="col-6 col-md-3">
                            <label class="form-label small fw-bold mb-1">MRP Price (৳)</label>
                            <input type="number" step="0.01" id="fieldMrpPrice" class="form-control font-monospace text-muted">
                        </div>
                        <div class="col-6 col-md-3">
                            <label class="form-label small fw-bold mb-1">Purchase Price (৳)</label>
                            <input type="number" step="0.01" id="fieldPurchasePrice" class="form-control font-monospace text-muted">
                        </div>
                        <div class="col-6 col-md-3">
                            <label class="form-label small fw-bold mb-1">Stock Quantity <span class="text-danger">*</span></label>
                            <input type="number" id="fieldStockQuantity" class="form-control font-monospace fw-semibold">
                        </div>
                    </div>
                </div>

                <div class="p-3 rounded-3 border bg-body-tertiary">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <label class="form-label small fw-bold mb-0">Color / Product Variants</label>
                        <button type="button" class="btn btn-outline-primary btn-sm py-0.5 px-2 small" onclick="addVariantRow('', '', '')">
                            <i class="fa-solid fa-plus me-1"></i> Add Variant
                        </button>
                    </div>
                    <div id="variantsContainer" class="d-flex flex-column gap-1.5" style="max-height: 180px; overflow-y: auto;"></div>
                </div>

                <div>
                    <label class="form-label small fw-bold mb-1">Short Summary</label>
                    <textarea id="fieldShortSummary" class="form-control" rows="2" placeholder="1-2 sentence quick summary"></textarea>
                </div>

                <div>
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <label class="form-label small fw-bold mb-0">Detailed HTML Description</label>
                        <button type="button" class="btn btn-link btn-sm p-0 text-decoration-none small" onclick="toggleHtmlPreview()">Toggle Preview</button>
                    </div>
                    <textarea id="fieldDetailedDescription" class="form-control font-monospace small" rows="5" placeholder="Detailed HTML description"></textarea>
                    <div id="fieldDescriptionPreview" class="p-3 rounded-3 border bg-body d-none overflow-auto small" style="max-height: 180px;"></div>
                </div>

                <div>
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <label class="form-label small fw-bold mb-0">Technical Specifications</label>
                        <button type="button" class="btn btn-outline-primary btn-sm py-0.5 px-2 small" onclick="addSpecRow('', '')">
                            <i class="fa-solid fa-plus me-1"></i> Add Spec
                        </button>
                    </div>
                    <div id="specsContainer" class="d-flex flex-column gap-1.5 p-2 rounded-3 border bg-body-tertiary" style="max-height: 190px; overflow-y: auto;"></div>
                </div>

                <div class="row g-2">
                    <div class="col-md-4">
                        <label class="form-label small fw-bold mb-1">SEO Meta Title</label>
                        <input type="text" id="fieldMetaTitle" class="form-control small" placeholder="Under 60 chars">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-bold mb-1">SEO Meta Description</label>
                        <input type="text" id="fieldMetaDescription" class="form-control small font-monospace" placeholder="Snippet under 155 chars">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-bold mb-1">SEO Meta Keywords</label>
                        <input type="text" id="fieldMetaKeywords" class="form-control small" placeholder="e.g. tag1, tag2, tag3">
                    </div>
                </div>

                <div class="card p-3 border rounded-3 bg-light shadow-xs">
                    <div class="d-flex align-items-center justify-content-between mb-3 border-bottom pb-2">
                        <div class="d-flex align-items-center gap-2">
                            <div class="rounded-circle bg-primary-subtle text-primary d-flex align-items-center justify-content-center" style="width: 34px; height: 34px; font-size: 0.95rem;">
                                <i class="fa-solid fa-sliders"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold mb-0 text-dark">Status &amp; Visibility</h6>
                                <small class="text-muted" style="font-size: 0.72rem;">Storefront visibility &amp; homepage merchandising</small>
                            </div>
                        </div>
                        <div>
                            <span id="aiStatusLiveBadge" class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-1 small fw-semibold d-inline-flex align-items-center gap-1.5">
                                <span class="status-live-dot"></span>
                                <span>Live</span>
                            </span>
                        </div>
                    </div>

                    <div class="row g-2">
                        <div class="col-md-6">
                            <div class="status-toggle-card active-live" id="cardAiIsActive" onclick="toggleAiStatusSwitch('aiIsActiveSwitch')">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="status-icon-box bg-success-subtle text-success">
                                            <i class="fa-solid fa-store"></i>
                                        </div>
                                        <div>
                                            <div class="d-flex align-items-center gap-1.5">
                                                <span class="fw-bold text-dark small">Active &amp; Published</span>
                                                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-1.5 py-0.5" style="font-size: 0.65rem;">Live</span>
                                            </div>
                                            <div class="text-muted" style="font-size: 0.7rem;">Show product to customers</div>
                                        </div>
                                    </div>
                                    <div class="form-check form-switch mb-0" onclick="event.stopPropagation()">
                                        <input class="form-check-input" type="checkbox" id="aiIsActiveSwitch" checked onchange="updateAiStatusCardVisuals()">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="status-toggle-card active-featured" id="cardAiIsFeatured" onclick="toggleAiStatusSwitch('aiIsFeaturedSwitch')">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="status-icon-box bg-warning-subtle text-warning">
                                            <i class="fa-solid fa-star"></i>
                                        </div>
                                        <div>
                                            <div class="d-flex align-items-center gap-1.5">
                                                <span class="fw-bold text-dark small">Featured Product</span>
                                                <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill px-1.5 py-0.5" style="font-size: 0.65rem;">Homepage</span>
                                            </div>
                                            <div class="text-muted" style="font-size: 0.7rem;">Show on homepage featured section</div>
                                        </div>
                                    </div>
                                    <div class="form-check form-switch mb-0" onclick="event.stopPropagation()">
                                        <input class="form-check-input" type="checkbox" id="aiIsFeaturedSwitch" checked onchange="updateAiStatusCardVisuals()">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="status-toggle-card" id="cardAiIsFlashDeal" onclick="toggleAiStatusSwitch('aiIsFlashSwitch')">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="status-icon-box bg-danger-subtle text-danger">
                                            <i class="fa-solid fa-bolt-lightning"></i>
                                        </div>
                                        <div>
                                            <div class="d-flex align-items-center gap-1.5">
                                                <span class="fw-bold text-dark small">Flash Deal Sale</span>
                                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-1.5 py-0.5" style="font-size: 0.65rem;">Promo</span>
                                            </div>
                                            <div class="text-muted" style="font-size: 0.7rem;">Show in flash sale section</div>
                                        </div>
                                    </div>
                                    <div class="form-check form-switch mb-0" onclick="event.stopPropagation()">
                                        <input class="form-check-input" type="checkbox" id="aiIsFlashSwitch" onchange="updateAiStatusCardVisuals()">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="status-toggle-card" id="cardAiIsFreeShipping" onclick="toggleAiStatusSwitch('aiIsFreeShippingSwitch')">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="status-icon-box bg-info-subtle text-info">
                                            <i class="fa-solid fa-truck-fast"></i>
                                        </div>
                                        <div>
                                            <div class="d-flex align-items-center gap-1.5">
                                                <span class="fw-bold text-dark small">Free Shipping</span>
                                                <span class="badge bg-info-subtle text-info border border-info-subtle rounded-pill px-1.5 py-0.5" style="font-size: 0.65rem;">0৳ Delivery</span>
                                            </div>
                                            <div class="text-muted" style="font-size: 0.7rem;">Display free delivery badge</div>
                                        </div>
                                    </div>
                                    <div class="form-check form-switch mb-0" onclick="event.stopPropagation()">
                                        <input class="form-check-input" type="checkbox" id="aiIsFreeShippingSwitch" onchange="updateAiStatusCardVisuals()">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-auto pt-3 border-top d-flex align-items-center justify-content-between">
                    <small class="text-muted">Review or modify details above before saving.</small>
                    <button type="button" class="btn btn-success px-4 py-2.5 fw-bold rounded-3 shadow d-flex align-items-center gap-2" id="btnSaveBottom" onclick="saveProductToCatalog()">
                        <i class="fa-solid fa-floppy-disk"></i>
                        <span>💾 Save to Catalog</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
(() => {
    let featuredImageFile = null;
    let galleryFiles = [];
    let activeImageBase64 = null;
    let activeImageMime = null;
    let activeImageUrl = null;
    let currentSuggestedCategory = null;
    let savedGalleryUrls = [];
    let currentHoverZone = 'main';

    const dropzone = document.getElementById('dropzoneArea');
    if (dropzone) {
        dropzone.addEventListener('mouseenter', () => { currentHoverZone = 'main'; });
        ['dragenter', 'dragover'].forEach(eventName => {
            dropzone.addEventListener(eventName, function (e) {
                e.preventDefault();
                e.stopPropagation();
                currentHoverZone = 'main';
                dropzone.style.borderColor = 'var(--bs-primary)';
                dropzone.style.background = 'rgba(99, 102, 241, 0.08)';
            }, false);
        });

        ['dragleave', 'drop'].forEach(eventName => {
            dropzone.addEventListener(eventName, function (e) {
                e.preventDefault();
                e.stopPropagation();
                dropzone.style.borderColor = '';
                dropzone.style.background = 'rgba(99, 102, 241, 0.03)';
            }, false);
        });

        dropzone.addEventListener('drop', function (e) {
            const dt = e.dataTransfer;
            const files = dt.files;
            if (files && files.length > 0) {
                processFile(files[0]);
            }
        });
    }

    const galleryDropzone = document.getElementById('galleryDropzoneArea');
    if (galleryDropzone) {
        galleryDropzone.addEventListener('mouseenter', () => { currentHoverZone = 'gallery'; });
        galleryDropzone.addEventListener('mouseleave', () => { currentHoverZone = 'main'; });
        ['dragenter', 'dragover'].forEach(eventName => {
            galleryDropzone.addEventListener(eventName, function (e) {
                e.preventDefault();
                e.stopPropagation();
                currentHoverZone = 'gallery';
                galleryDropzone.style.borderColor = 'var(--bs-primary)';
                galleryDropzone.style.background = 'rgba(99, 102, 241, 0.08)';
            }, false);
        });

        ['dragleave', 'drop'].forEach(eventName => {
            galleryDropzone.addEventListener(eventName, function (e) {
                e.preventDefault();
                e.stopPropagation();
                galleryDropzone.style.borderColor = '';
                galleryDropzone.style.background = 'rgba(99, 102, 241, 0.02)';
            }, false);
        });

        galleryDropzone.addEventListener('drop', function (e) {
            const dt = e.dataTransfer;
            const files = dt.files;
            if (files && files.length > 0) {
                appendGalleryFiles(files);
            }
        });
    }

    window.addEventListener('paste', function (e) {
        const clipboardData = e.clipboardData || e.originalEvent?.clipboardData;
        if (!clipboardData) return;

        const items = clipboardData.items || [];
        const imageFiles = [];

        for (let i = 0; i < items.length; i++) {
            if (items[i].type && items[i].type.startsWith('image/')) {
                const f = items[i].getAsFile();
                if (f) imageFiles.push(f);
            }
        }

        if (imageFiles.length === 0 && clipboardData.files) {
            Array.from(clipboardData.files).forEach(f => {
                if (f.type && f.type.startsWith('image/')) imageFiles.push(f);
            });
        }

        if (imageFiles.length > 0) {
            e.preventDefault();
            if (currentHoverZone === 'gallery') {
                appendGalleryFiles(imageFiles);
                if (window.showToast) window.showToast(imageFiles.length + ' image(s) pasted to gallery!', 'success');
            } else {
                processFile(imageFiles[0]);
                if (imageFiles.length > 1) {
                    appendGalleryFiles(imageFiles.slice(1));
                    if (window.showToast) window.showToast('Main photo pasted and ' + (imageFiles.length - 1) + ' added to gallery!', 'success');
                } else {
                    if (window.showToast) window.showToast('Product photo pasted from clipboard!', 'success');
                }
            }
            return;
        }

        const activeEl = document.activeElement;
        const isTyping = activeEl && (activeEl.tagName === 'INPUT' || activeEl.tagName === 'TEXTAREA' || activeEl.isContentEditable);
        if (!isTyping) {
            const text = clipboardData.getData('text/plain');
            if (text && (text.match(/\.(jpeg|jpg|gif|png|webp|svg)($|\?)/i) || (text.startsWith('http://') || text.startsWith('https://')) && text.includes('/'))) {
                const urlInput = document.getElementById('imageUrlInput');
                if (urlInput) {
                    urlInput.value = text.trim();
                    loadImageUrl();
                }
            }
        }
    });

    function handleFileSelected(input) {
        if (input.files && input.files[0]) {
            processFile(input.files[0]);
        }
    }

    function processFile(file) {
        if (!file.type.startsWith('image/')) {
            if (window.showToast) window.showToast('Please select a valid image file.', 'warning');
            return;
        }

        featuredImageFile = file;
        activeImageMime = file.type;
        const reader = new FileReader();
        reader.onload = function (e) {
            const dataUrl = e.target.result;
            const parts = dataUrl.split(',');
            activeImageBase64 = parts[1];
            activeImageUrl = dataUrl;

            document.getElementById('previewImg').src = dataUrl;
            document.getElementById('dropzoneIdle').classList.add('d-none');
            document.getElementById('dropzonePreview').classList.remove('d-none');
            document.getElementById('imageUrlInput').value = '';
        };
        reader.readAsDataURL(file);
    }

    function removeUploadedImage() {
        featuredImageFile = null;
        activeImageBase64 = null;
        activeImageMime = null;
        activeImageUrl = null;
        document.getElementById('imageFileInput').value = '';
        document.getElementById('previewImg').src = '';
        document.getElementById('dropzonePreview').classList.add('d-none');
        document.getElementById('dropzoneIdle').classList.remove('d-none');
    }

    function handleGalleryFilesSelected(input) {
        if (input.files && input.files.length > 0) {
            appendGalleryFiles(input.files);
            input.value = '';
        }
    }

    function appendGalleryFiles(files) {
        for (let i = 0; i < files.length; i++) {
            if (galleryFiles.length >= 10) break;
            const file = files[i];
            if (file.type.startsWith('image/')) {
                galleryFiles.push(file);
            }
        }
        renderGalleryPreviews();
    }

    function removeGalleryFile(index) {
        galleryFiles.splice(index, 1);
        renderGalleryPreviews();
    }

    function renderGalleryPreviews() {
        const container = document.getElementById('galleryPreviewContainer');
        const badge = document.getElementById('galleryCountBadge');
        if (badge) badge.textContent = galleryFiles.length + '/10 added';
        if (!container) return;

        if (galleryFiles.length === 0) {
            container.innerHTML = '';
            container.classList.add('d-none');
            return;
        }

        container.classList.remove('d-none');
        container.innerHTML = '';

        galleryFiles.forEach((file, idx) => {
            const thumbUrl = URL.createObjectURL(file);
            const card = document.createElement('div');
            card.className = 'position-relative d-inline-block border rounded-2 overflow-hidden shadow-xs';
            card.style.width = '60px';
            card.style.height = '60px';
            card.innerHTML = `
                <img src="${thumbUrl}" style="width: 100%; height: 100%; object-fit: cover;">
                <button type="button" class="btn btn-danger btn-xs position-absolute top-0 end-0 m-0.5 rounded-circle p-0 d-flex align-items-center justify-content-center" style="width: 18px; height: 18px; font-size: 10px;" onclick="removeGalleryFile(${idx})">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            `;
            container.appendChild(card);
        });
    }

    function handleUrlInput(val) {
        const trimmed = val.trim();
        if (trimmed && (trimmed.startsWith('http://') || trimmed.startsWith('https://'))) {
            activeImageUrl = trimmed;
            activeImageBase64 = null;
            activeImageMime = null;
            featuredImageFile = null;
        }
    }

    function loadImageUrl() {
        const url = document.getElementById('imageUrlInput').value.trim();
        if (!url) {
            if (window.showToast) window.showToast('Please enter an image URL.', 'warning');
            return;
        }

        const img = new Image();
        img.onload = function () {
            activeImageUrl = url;
            activeImageBase64 = null;
            activeImageMime = null;
            featuredImageFile = null;
            document.getElementById('previewImg').src = url;
            document.getElementById('dropzoneIdle').classList.add('d-none');
            document.getElementById('dropzonePreview').classList.remove('d-none');
            if (window.showToast) window.showToast('Image loaded successfully.', 'success');
        };
        img.onerror = function () {
            if (window.showToast) window.showToast('Failed to load image from URL. Ensure it is publicly accessible.', 'error');
        };
        img.src = url;
    }

    function startVisionScan() {
        const hint = document.getElementById('quickHintInput').value.trim();
        const btn = document.getElementById('btnScanGenerate');
        const badge = document.getElementById('outputEngineBadge');
        const placeholder = document.getElementById('outputEmptyPlaceholder');
        const container = document.getElementById('outputFormContainer');
        const btnSaveTop = document.getElementById('btnSaveTop');

        if (!featuredImageFile && !activeImageBase64 && !activeImageUrl && galleryFiles.length === 0 && !hint) {
            if (window.showToast) {
                window.showToast('Please provide a featured image, gallery images, or a quick context hint.', 'warning');
            }
            return;
        }

        const totalImagesCount = (featuredImageFile || activeImageBase64 || activeImageUrl ? 1 : 0) + galleryFiles.length;
        const origBtnHtml = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status"></span> Scanning ' + (totalImagesCount > 0 ? totalImagesCount + ' Photos' : 'Context') + ' &amp; Deducing Details...';

        badge.className = 'badge bg-primary-subtle text-primary rounded-pill px-2.5 py-1 small';
        badge.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin me-1"></i> Running Vision AI' + (totalImagesCount > 1 ? ' (' + totalImagesCount + ' Images)' : '');

        const language = document.getElementById('contentLanguageSelect') ? document.getElementById('contentLanguageSelect').value : 'English';

        const formData = new FormData();
        formData.append('hint', hint);
        formData.append('language', language);

        if (featuredImageFile) {
            formData.append('featured_image', featuredImageFile);
        } else if (activeImageBase64) {
            formData.append('image_base64', activeImageBase64);
            formData.append('image_mime', activeImageMime);
        } else if (activeImageUrl) {
            formData.append('image_url', activeImageUrl);
        }

        for (let i = 0; i < galleryFiles.length; i++) {
            formData.append('gallery_images[]', galleryFiles[i]);
        }

        axios.post('{{ route("admin.products.ai.generate") }}', formData, {
            headers: { 'Content-Type': 'multipart/form-data' }
        })
        .then(function (response) {
            const res = response.data;
            if (res.success && res.data) {
                const d = res.data;
                const engineText = res.was_failover ? '🛡️ Failover: ' + res.provider_used : '✨ Vision Engine: ' + res.provider_used;
                badge.className = 'badge ' + (res.was_failover ? 'bg-warning-subtle text-warning' : 'bg-success-subtle text-success') + ' rounded-pill px-2.5 py-1 small fw-semibold';
                badge.innerHTML = engineText;

                if (d.main_image) {
                    activeImageUrl = d.main_image;
                }
                if (Array.isArray(d.gallery_images)) {
                    savedGalleryUrls = d.gallery_images;
                }

                document.getElementById('fieldTitle').value = d.title || '';
                document.getElementById('fieldSku').value = d.sku || '';
                document.getElementById('fieldSlug').value = d.url_slug || '';
                document.getElementById('fieldSellingPrice').value = d.selling_price || (d.price || 1450);
                document.getElementById('fieldMrpPrice').value = d.mrp_price || (d.old_price || 1850);
                document.getElementById('fieldPurchasePrice').value = d.purchase_price || (d.cost_price || 950);
                document.getElementById('fieldStockQuantity').value = d.stock_quantity || (d.stock_qty || 50);
                document.getElementById('fieldShortSummary').value = d.short_summary || (d.short_description || '');
                document.getElementById('fieldDetailedDescription').value = d.detailed_html_description || (d.description_html || '');
                document.getElementById('fieldMetaTitle').value = d.meta_title || (d.title || '');
                document.getElementById('fieldMetaDescription').value = d.meta_description || (d.short_summary || '');
                if (document.getElementById('fieldMetaKeywords')) {
                    document.getElementById('fieldMetaKeywords').value = d.meta_keywords || '';
                }

                const btnQuickRegen = document.getElementById('btnQuickRegenerateCopy');
                if (btnQuickRegen) {
                    btnQuickRegen.classList.remove('d-none');
                    btnQuickRegen.classList.add('d-inline-flex');
                }

                if (Array.isArray(d.tags) && d.tags.length > 0) {
                    document.getElementById('fieldTag').value = d.tags[0].toUpperCase();
                }

                const catSuggestion = d.suggested_category || d.category_suggestion;
                matchCategorySuggestion(catSuggestion, d.matched_category);
                renderSpecifications(d.specifications || []);
                renderVariants(d.variants || []);

                placeholder.classList.add('d-none');
                container.classList.remove('d-none');
                btnSaveTop.disabled = false;
                updateAiStatusCardVisuals();

                if (window.showToast) {
                    window.showToast('Product analyzed and comprehensive details ready!', 'success');
                }
            } else {
                badge.className = 'badge bg-danger-subtle text-danger rounded-pill px-2.5 py-1 small';
                badge.textContent = 'Scan Failed';
                if (window.showToast) {
                    window.showToast(res.error || 'Failed to analyze product image.', 'error');
                }
            }
        })
        .catch(function (error) {
            badge.className = 'badge bg-danger-subtle text-danger rounded-pill px-2.5 py-1 small';
            badge.textContent = 'Execution Error';
            const msg = (error.response && error.response.data && error.response.data.message) ? error.response.data.message : 'Error generating product details.';
            if (window.showToast) {
                window.showToast(msg, 'error');
            }
        })
        .finally(function () {
            btn.disabled = false;
            btn.innerHTML = origBtnHtml;
        });
    }

    function toggleAiStatusSwitch(switchId) {
        const el = document.getElementById(switchId);
        if (el) {
            el.checked = !el.checked;
            updateAiStatusCardVisuals();
        }
    }

    function updateAiStatusCardVisuals() {
        const activeSw = document.getElementById('aiIsActiveSwitch');
        const featSw = document.getElementById('aiIsFeaturedSwitch');
        const flashSw = document.getElementById('aiIsFlashSwitch');
        const shipSw = document.getElementById('aiIsFreeShippingSwitch');

        const cardActive = document.getElementById('cardAiIsActive');
        const cardFeat = document.getElementById('cardAiIsFeatured');
        const cardFlash = document.getElementById('cardAiIsFlashDeal');
        const cardShip = document.getElementById('cardAiIsFreeShipping');

        if (cardActive && activeSw) {
            if (activeSw.checked) {
                cardActive.classList.add('active-live');
            } else {
                cardActive.classList.remove('active-live');
            }
        }
        if (cardFeat && featSw) {
            if (featSw.checked) {
                cardFeat.classList.add('active-featured');
            } else {
                cardFeat.classList.remove('active-featured');
            }
        }
        if (cardFlash && flashSw) {
            if (flashSw.checked) {
                cardFlash.classList.add('active-flash');
            } else {
                cardFlash.classList.remove('active-flash');
            }
        }
        if (cardShip && shipSw) {
            if (shipSw.checked) {
                cardShip.classList.add('active-shipping');
            } else {
                cardShip.classList.remove('active-shipping');
            }
        }

        const liveBadge = document.getElementById('aiStatusLiveBadge');
        if (liveBadge && activeSw) {
            if (activeSw.checked) {
                liveBadge.className = 'badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-1 small fw-semibold d-inline-flex align-items-center gap-1.5';
                liveBadge.innerHTML = '<span class="status-live-dot"></span><span>Live</span>';
            } else {
                liveBadge.className = 'badge bg-secondary-subtle text-secondary border rounded-pill px-2.5 py-1 small fw-semibold d-inline-flex align-items-center gap-1.5';
                liveBadge.innerHTML = '<span class="status-draft-dot"></span><span>Draft / Hidden</span>';
            }
        }
    }

    function matchCategorySuggestion(suggestedName, matchedCategory) {
        const catSelect = document.getElementById('fieldCategory');
        const infoEl = document.getElementById('categoryAiSuggested');
        const btnCreate = document.getElementById('btnCreateCategory');
        const btnCreateText = document.getElementById('btnCreateCategoryText');

        if (!catSelect || !infoEl) return;

        currentSuggestedCategory = suggestedName ? suggestedName.trim() : null;

        if (matchedCategory && matchedCategory.id) {
            let optExists = false;
            for (let i = 0; i < catSelect.options.length; i++) {
                if (catSelect.options[i].value == matchedCategory.id) {
                    catSelect.selectedIndex = i;
                    optExists = true;
                    break;
                }
            }
            if (!optExists) {
                const label = matchedCategory.hierarchy_name || matchedCategory.name;
                const newOpt = new Option(label, matchedCategory.id, true, true);
                newOpt.setAttribute('data-name', (matchedCategory.name || '').toLowerCase());
                newOpt.setAttribute('data-hierarchy', (matchedCategory.hierarchy_name || '').toLowerCase());
                catSelect.add(newOpt);
            }
            const displayLabel = matchedCategory.hierarchy_name || matchedCategory.name;
            infoEl.className = 'text-success d-block mt-1 small';
            infoEl.innerHTML = '<i class="fa-solid fa-circle-check me-1"></i> Auto-selected category: <strong>' + escapeHtml(displayLabel) + '</strong>';
            if (btnCreate) {
                btnCreate.classList.add('d-none');
                btnCreate.classList.remove('d-inline-flex');
            }
            return;
        }

        if (!suggestedName) {
            infoEl.innerHTML = '';
            if (btnCreate) {
                btnCreate.classList.add('d-none');
                btnCreate.classList.remove('d-inline-flex');
            }
            return;
        }

        const norm = suggestedName.toLowerCase().trim();
        const normTokens = norm.split(/[^a-z0-9]+/).filter(t => t.length >= 3);
        let bestIdx = -1;
        let highestMatchScore = 0;

        for (let i = 0; i < catSelect.options.length; i++) {
            const opt = catSelect.options[i];
            if (!opt.value) continue;
            const optName = (opt.getAttribute('data-name') || '').toLowerCase();
            const optHierarchy = (opt.getAttribute('data-hierarchy') || opt.text || '').toLowerCase();

            let score = 0;
            if (norm === optName) score += 100;
            if (norm.includes(optName) || optName.includes(norm)) score += 60;
            if (optHierarchy.includes(norm) || norm.includes(optHierarchy)) score += 40;

            normTokens.forEach(t => {
                if (optName.includes(t)) score += 25;
                if (optHierarchy.includes(t)) score += 15;
            });

            if (score > highestMatchScore) {
                highestMatchScore = score;
                bestIdx = i;
            }
        }

        if (bestIdx > 0 && highestMatchScore >= 20) {
            catSelect.selectedIndex = bestIdx;
            const opt = catSelect.options[bestIdx];
            infoEl.className = 'text-success d-block mt-1 small';
            infoEl.innerHTML = '<i class="fa-solid fa-circle-check me-1"></i> Auto-selected related category: <strong>' + escapeHtml(opt.text.trim()) + '</strong>';
            if (btnCreate) {
                btnCreate.classList.add('d-none');
                btnCreate.classList.remove('d-inline-flex');
            }
        } else {
            catSelect.value = '';
            infoEl.className = 'text-muted d-block mt-1 small';
            infoEl.innerHTML = '<i class="fa-solid fa-circle-info me-1"></i> AI suggested: "<strong>' + escapeHtml(suggestedName) + '</strong>" (No existing match)';
            if (btnCreate && btnCreateText) {
                btnCreateText.innerHTML = '⚡ Create &amp; Select: <strong>' + escapeHtml(suggestedName) + '</strong>';
                btnCreate.classList.remove('d-none');
                btnCreate.classList.add('d-inline-flex');
            }
        }
    }

    function createAndSelectCategory() {
        if (!currentSuggestedCategory) {
            if (window.showToast) window.showToast('No suggested category available.', 'warning');
            return;
        }

        const btn = document.getElementById('btnCreateCategory');
        const origHtml = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Creating...';

        axios.post('{{ route("admin.products.ai.category.create") }}', {
            name: currentSuggestedCategory
        })
        .then(function (response) {
            const res = response.data;
            if (res.success && res.category) {
                const cat = res.category;
                const catSelect = document.getElementById('fieldCategory');
                const infoEl = document.getElementById('categoryAiSuggested');

                let optExists = false;
                for (let i = 0; i < catSelect.options.length; i++) {
                    if (catSelect.options[i].value == cat.id) {
                        catSelect.selectedIndex = i;
                        optExists = true;
                        break;
                    }
                }
                if (!optExists) {
                    const opt = new Option(cat.name, cat.id, true, true);
                    opt.setAttribute('data-name', cat.name.toLowerCase());
                    catSelect.add(opt);
                }

                btn.classList.add('d-none');
                btn.classList.remove('d-inline-flex');

                infoEl.className = 'text-success d-block mt-1 small';
                infoEl.innerHTML = '<i class="fa-solid fa-circle-check me-1"></i> ' + (res.already_existed ? 'Selected existing category: ' : 'Created and selected: ') + '<strong>' + escapeHtml(cat.name) + '</strong>';

                if (window.showToast) {
                    window.showToast(res.message || 'Category ready and selected!', 'success');
                }
            } else {
                if (window.showToast) {
                    window.showToast(res.message || 'Failed to create category.', 'error');
                }
            }
        })
        .catch(function (error) {
            const msg = (error.response && error.response.data && error.response.data.message) ? error.response.data.message : 'Error creating category.';
            if (window.showToast) {
                window.showToast(msg, 'error');
            }
        })
        .finally(function () {
            btn.disabled = false;
            btn.innerHTML = origHtml;
        });
    }

    function renderSpecifications(specs) {
        const container = document.getElementById('specsContainer');
        container.innerHTML = '';

        if (!specs || (Array.isArray(specs) && specs.length === 0) || (typeof specs === 'object' && Object.keys(specs).length === 0)) {
            addSpecRow('Material', 'Imported Premium Grade');
            addSpecRow('Warranty', 'Official Store Warranty');
            return;
        }

        if (Array.isArray(specs)) {
            specs.forEach(item => {
                if (item && item.key) {
                    addSpecRow(item.key, item.value || '');
                }
            });
        } else if (typeof specs === 'object') {
            for (const [k, v] of Object.entries(specs)) {
                addSpecRow(k, v);
            }
        }
    }

    function addSpecRow(key, val) {
        const container = document.getElementById('specsContainer');
        const row = document.createElement('div');
        row.className = 'd-flex align-items-center gap-1.5 spec-row';
        row.innerHTML = `
            <input type="text" class="form-control form-control-sm spec-key fw-semibold" style="max-width: 140px;" placeholder="Property" value="${escapeHtml(key)}">
            <input type="text" class="form-control form-control-sm spec-val flex-grow-1" placeholder="Value" value="${escapeHtml(val)}">
            <button type="button" class="btn btn-outline-danger btn-sm py-0.5 px-2" onclick="this.closest('.spec-row').remove()" title="Delete"><i class="fa-solid fa-xmark"></i></button>
        `;
        container.appendChild(row);
    }

    function renderVariants(variants) {
        const container = document.getElementById('variantsContainer');
        container.innerHTML = '';

        if (Array.isArray(variants) && variants.length > 0) {
            variants.forEach(v => {
                addVariantRow(v.color_name || '', v.price || '', v.stock || '');
            });
        } else {
            addVariantRow('Default', '', '');
        }
    }

    function addVariantRow(color, price, stock) {
        const container = document.getElementById('variantsContainer');
        const row = document.createElement('div');
        row.className = 'd-flex align-items-center gap-1.5 variant-row';
        row.innerHTML = `
            <input type="text" class="form-control form-control-sm var-color fw-semibold" style="max-width: 150px;" placeholder="Color / Style" value="${escapeHtml(color)}">
            <input type="number" step="0.01" class="form-control form-control-sm var-price font-monospace" style="max-width: 120px;" placeholder="Price (৳)" value="${escapeHtml(price)}">
            <input type="number" class="form-control form-control-sm var-stock font-monospace" style="max-width: 100px;" placeholder="Stock" value="${escapeHtml(stock)}">
            <button type="button" class="btn btn-outline-danger btn-sm py-0.5 px-2" onclick="this.closest('.variant-row').remove()" title="Delete"><i class="fa-solid fa-xmark"></i></button>
        `;
        container.appendChild(row);
    }

    function escapeHtml(str) {
        return (str || '').toString().replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/'/g, '&#039;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
    }

    function toggleHtmlPreview() {
        const raw = document.getElementById('fieldDetailedDescription');
        const prev = document.getElementById('fieldDescriptionPreview');
        if (prev.classList.contains('d-none')) {
            prev.innerHTML = raw.value;
            prev.classList.remove('d-none');
            raw.classList.add('d-none');
        } else {
            prev.classList.add('d-none');
            raw.classList.remove('d-none');
        }
    }

    function saveProductToCatalog() {
        const title = document.getElementById('fieldTitle').value.trim();
        const sku = document.getElementById('fieldSku').value.trim();
        const slug = document.getElementById('fieldSlug').value.trim();
        const categoryId = document.getElementById('fieldCategory').value;
        const sellingPrice = document.getElementById('fieldSellingPrice').value.trim();
        const mrpPrice = document.getElementById('fieldMrpPrice').value.trim();
        const purchasePrice = document.getElementById('fieldPurchasePrice').value.trim();
        const stockQuantity = document.getElementById('fieldStockQuantity').value.trim();
        const shortSummary = document.getElementById('fieldShortSummary').value.trim();
        const detailedDesc = document.getElementById('fieldDetailedDescription').value.trim();
        const metaTitle = document.getElementById('fieldMetaTitle').value.trim();
        const metaDescription = document.getElementById('fieldMetaDescription').value.trim();
        const metaKeywords = document.getElementById('fieldMetaKeywords') ? document.getElementById('fieldMetaKeywords').value.trim() : '';
        const tag = document.getElementById('fieldTag').value.trim();

        if (!title || !categoryId || !sellingPrice || !stockQuantity) {
            if (window.showToast) {
                window.showToast('Please provide Title, Category, Selling Price, and Stock Quantity.', 'warning');
            }
            return;
        }

        const specs = {};
        document.querySelectorAll('#specsContainer .spec-row').forEach(row => {
            const k = row.querySelector('.spec-key').value.trim();
            const v = row.querySelector('.spec-val').value.trim();
            if (k && v) specs[k] = v;
        });

        const variants = [];
        document.querySelectorAll('#variantsContainer .variant-row').forEach(row => {
            const c = row.querySelector('.var-color').value.trim();
            const p = row.querySelector('.var-price').value.trim();
            const s = row.querySelector('.var-stock').value.trim();
            if (c) {
                variants.push({
                    name: c,
                    price: p ? parseFloat(p) : parseFloat(sellingPrice),
                    stock: s ? parseInt(s) : parseInt(stockQuantity),
                });
            }
        });

        const imageToSave = activeImageUrl || 'https://images.unsplash.com/photo-1580481077198-c847ad4360a0?w=600&auto=format&fit=crop&q=80';

        const btnTop = document.getElementById('btnSaveTop');
        const btnBottom = document.getElementById('btnSaveBottom');
        const origTopHtml = btnTop.innerHTML;
        const origBottomHtml = btnBottom.innerHTML;

        btnTop.disabled = true;
        btnBottom.disabled = true;
        btnTop.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Saving...';
        btnBottom.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Saving...';

        const isFeatured = document.getElementById('aiIsFeaturedSwitch') ? (document.getElementById('aiIsFeaturedSwitch').checked ? 1 : 0) : 1;
        const isActive = document.getElementById('aiIsActiveSwitch') ? (document.getElementById('aiIsActiveSwitch').checked ? 1 : 0) : 1;
        const isFlashDeal = document.getElementById('aiIsFlashSwitch') ? (document.getElementById('aiIsFlashSwitch').checked ? 1 : 0) : 0;
        const isFreeShipping = document.getElementById('aiIsFreeShippingSwitch') ? (document.getElementById('aiIsFreeShippingSwitch').checked ? 1 : 0) : 0;

        axios.post('{{ route("admin.products.ai.save") }}', {
            title: title,
            sku: sku,
            slug: slug,
            category_id: categoryId,
            price: sellingPrice,
            old_price: mrpPrice || null,
            cost_price: purchasePrice || null,
            stock_qty: stockQuantity,
            main_image: imageToSave,
            gallery_images: savedGalleryUrls,
            short_desc: shortSummary,
            description: detailedDesc,
            meta_title: metaTitle,
            meta_description: metaDescription,
            meta_keywords: metaKeywords || metaTitle,
            tag: tag,
            specifications: specs,
            variants: variants,
            is_featured: isFeatured,
            is_active: isActive,
            is_flash_deal: isFlashDeal,
            is_free_shipping: isFreeShipping
        })
        .then(function (response) {
            const data = response.data;
            if (data.success) {
                if (window.showToast) {
                    window.showToast(data.message || 'Product saved to Catalog successfully!', 'success');
                }
                setTimeout(function () {
                    window.location.href = data.redirect_url || '{{ route("admin.products.index") }}';
                }, 800);
            } else {
                if (window.showToast) {
                    window.showToast(data.message || 'Error saving product.', 'error');
                }
            }
        })
        .catch(function (error) {
            const msg = (error.response && error.response.data && error.response.data.message) ? error.response.data.message : 'Error publishing product.';
            if (window.showToast) {
                window.showToast(msg, 'error');
            }
        })
        .finally(function () {
            btnTop.disabled = false;
            btnBottom.disabled = false;
            btnTop.innerHTML = origTopHtml;
            btnBottom.innerHTML = origBottomHtml;
        });
    }

    function regenerateCopyAndSeo() {
        const title = document.getElementById('fieldTitle').value.trim();
        if (!title) {
            if (window.showToast) window.showToast('Please enter a product title first.', 'warning');
            return;
        }

        const catSelect = document.getElementById('fieldCategory');
        const categoryId = catSelect ? catSelect.value : '';
        const categoryName = (catSelect && catSelect.selectedIndex > 0) ? catSelect.options[catSelect.selectedIndex].text.trim() : '';
        const price = document.getElementById('fieldSellingPrice').value.trim();
        const oldPrice = document.getElementById('fieldMrpPrice').value.trim();
        const tag = document.getElementById('fieldTag').value.trim();
        const language = document.getElementById('contentLanguageSelect') ? document.getElementById('contentLanguageSelect').value : 'Mixed (English & Bengali)';

        const specs = [];
        document.querySelectorAll('#specsContainer .spec-row').forEach(row => {
            const k = row.querySelector('.spec-key').value.trim();
            const v = row.querySelector('.spec-val').value.trim();
            if (k && v) specs.push({ key: k, value: v });
        });

        const btn = document.getElementById('btnQuickRegenerateCopy');
        const origHtml = btn ? btn.innerHTML : '';
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Writing Copy...';
        }

        axios.post('{{ route("admin.products.ai.seo_description") }}', {
            title: title,
            category_id: categoryId,
            category: categoryName,
            price: price || null,
            old_price: oldPrice || null,
            tag: tag || null,
            specs: specs,
            language: language
        })
        .then(function (response) {
            const res = response.data;
            if (res.success && res.data) {
                const d = res.data;
                if (d.short_desc) document.getElementById('fieldShortSummary').value = d.short_desc;
                if (d.detailed_html_description) {
                    document.getElementById('fieldDetailedDescription').value = d.detailed_html_description;
                    const prev = document.getElementById('fieldDescriptionPreview');
                    if (prev && !prev.classList.contains('d-none')) {
                        prev.innerHTML = d.detailed_html_description;
                    }
                }
                if (d.meta_title) document.getElementById('fieldMetaTitle').value = d.meta_title;
                if (d.meta_description) document.getElementById('fieldMetaDescription').value = d.meta_description;
                if (d.meta_keywords && document.getElementById('fieldMetaKeywords')) {
                    document.getElementById('fieldMetaKeywords').value = d.meta_keywords;
                }
                if (d.url_slug && !document.getElementById('fieldSlug').value.trim()) {
                    document.getElementById('fieldSlug').value = d.url_slug;
                }
                if (window.showToast) {
                    window.showToast('Copywriting and SEO metadata enriched successfully!', 'success');
                }
            } else {
                if (window.showToast) {
                    window.showToast(res.error || 'Failed to regenerate copy.', 'error');
                }
            }
        })
        .catch(function (error) {
            const msg = (error.response && error.response.data && error.response.data.message) ? error.response.data.message : 'Error regenerating copywriting.';
            if (window.showToast) window.showToast(msg, 'error');
        })
        .finally(function () {
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = origHtml;
            }
        });
    }

    window.handleFileSelected = handleFileSelected;
    window.processFile = processFile;
    window.removeUploadedImage = removeUploadedImage;
    window.handleGalleryFilesSelected = handleGalleryFilesSelected;
    window.appendGalleryFiles = appendGalleryFiles;
    window.removeGalleryFile = removeGalleryFile;
    window.renderGalleryPreviews = renderGalleryPreviews;
    window.handleUrlInput = handleUrlInput;
    window.loadImageUrl = loadImageUrl;
    window.startVisionScan = startVisionScan;
    window.matchCategorySuggestion = matchCategorySuggestion;
    window.createAndSelectCategory = createAndSelectCategory;
    window.renderSpecifications = renderSpecifications;
    window.addSpecRow = addSpecRow;
    window.renderVariants = renderVariants;
    window.addVariantRow = addVariantRow;
    window.escapeHtml = escapeHtml;
    window.toggleHtmlPreview = toggleHtmlPreview;
    window.saveProductToCatalog = saveProductToCatalog;
    window.regenerateCopyAndSeo = regenerateCopyAndSeo;
    window.toggleAiStatusSwitch = toggleAiStatusSwitch;
    window.updateAiStatusCardVisuals = updateAiStatusCardVisuals;
})();
</script>
@endsection
