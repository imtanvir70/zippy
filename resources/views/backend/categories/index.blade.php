@extends('backend.layouts.app')

@section('title', 'Category Management')

@section('content')
<div class="card p-3 p-md-4 mb-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <h3 class="fw-bold mb-0">Category Management</h3>
            <small class="text-muted">Multi-level 3-tier nested categories: Category &rarr; Sub Category &rarr; Nested Category</small>
        </div>
        @canPerm('admin.categories.store')
            <button type="button" class="btn btn-success" onclick="openCreateCategoryModal()" id="addBtn">
                <i class="fa-solid fa-circle-plus fa-fade me-1"></i> Add New Category
            </button>
        @endcanPerm
    </div>
</div>

<div class="card p-3 p-md-4 table-card mb-4">
    <div class="table-responsive">
        <table class="table table-striped table-hover table-bordered align-middle" id="categoriesTable" style="width: 100%;">
            <thead>
                <tr>
                    <th style="width: 8%; text-align: center;">Image</th>
                    <th style="width: 28%;">Category Name</th>
                    <th style="width: 22%;">Hierarchy / Level</th>
                    <th style="width: 16%;">Slug URL</th>
                    <th style="width: 10%; text-align: center;">Items / Subs</th>
                    <th style="width: 8%; text-align: center;">Status</th>
                    <th style="width: 8%; text-align: center;">Actions</th>
                </tr>
            </thead>
            <tbody>
            </tbody>
        </table>
    </div>
</div>

<div class="modal fade" id="categoryModal" tabindex="-1" aria-labelledby="categoryModalLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
        <div class="modal-content border-0 shadow-lg">
            <form id="categoryForm" onsubmit="handleCategoryFormSubmit(event)" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="id" id="modalCatId" value="">

                <div class="modal-header border-0 pb-0">
                    <div>
                        <h5 class="fw-bold mb-0" id="categoryModalLabel">Add New Category</h5>
                        <small class="text-muted">Configure hierarchy, bilingual names, and WebP category image</small>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body p-4">
                    <div class="mb-3 p-3 bg-body-secondary rounded-3 border">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <label class="form-label fw-bold small text-primary mb-0">
                                <i class="fa-solid fa-diagram-project me-1"></i> Hierarchy Placement (Select Tier)
                            </label>
                            <span class="badge bg-primary rounded-pill px-2.5 py-1" id="tierDisplayBadge">Tier 1: Main Category</span>
                        </div>

                        <div class="btn-group w-100 mb-3 shadow-xs" role="group" id="tierSelectorGroup">
                            <input type="radio" class="btn-check" name="category_tier" id="tier1Radio" value="1" checked onchange="handleTierChange(1)">
                            <label class="btn btn-outline-primary btn-sm py-2 fw-semibold" for="tier1Radio">
                                <i class="fa-solid fa-folder-tree d-block mb-1 fs-6"></i> Main Category
                            </label>

                            <input type="radio" class="btn-check" name="category_tier" id="tier2Radio" value="2" onchange="handleTierChange(2)">
                            <label class="btn btn-outline-primary btn-sm py-2 fw-semibold" for="tier2Radio">
                                <i class="fa-solid fa-diagram-nested d-block mb-1 fs-6"></i> Sub Category
                            </label>

                            <input type="radio" class="btn-check" name="category_tier" id="tier3Radio" value="3" onchange="handleTierChange(3)">
                            <label class="btn btn-outline-primary btn-sm py-2 fw-semibold" for="tier3Radio">
                                <i class="fa-solid fa-code-branch d-block mb-1 fs-6"></i> Nested Category
                            </label>
                        </div>

                        <input type="hidden" name="parent_id" id="formCatParentId" value="">

                        <div id="tier2Container" class="mb-1" style="display: none;">
                            <label class="form-label small fw-bold text-dark mb-1">
                                Select Parent Category (Tier 1) <span class="text-danger">*</span>
                            </label>
                            <select id="tier2ParentSelect" class="form-select" onchange="handleTier2ParentChange(this.value)">
                                <option value="">-- Choose Main Category --</option>
                                @foreach($rootCategories as $rCat)
                                    <option value="{{ $rCat->id }}">{{ $rCat->name }} ({{ $rCat->name_bn }})</option>
                                @endforeach
                            </select>
                            <small class="text-muted d-block mt-1">This category will be nested directly under the selected Main Category.</small>
                        </div>

                        <div id="tier3Container" style="display: none;">
                            <div class="row g-2">
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-dark mb-1">
                                        Step 1: Main Category <span class="text-danger">*</span>
                                    </label>
                                    <select id="tier3RootSelect" class="form-select" onchange="handleTier3RootChange(this.value)">
                                        <option value="">-- Choose Main Category --</option>
                                        @foreach($rootCategories as $rCat)
                                            <option value="{{ $rCat->id }}">{{ $rCat->name }} ({{ $rCat->name_bn }})</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-dark mb-1">
                                        Step 2: Sub Category <span class="text-danger">*</span>
                                    </label>
                                    <select id="tier3SubSelect" class="form-select" onchange="handleTier3SubChange(this.value)">
                                        <option value="">-- Choose Sub Category --</option>
                                    </select>
                                </div>
                            </div>
                            <small class="text-muted d-block mt-1">This category will be nested as Tier 3 under the chosen Sub Category.</small>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Category Name (English) <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="formCatName" class="form-control" placeholder="e.g. Smart Watch & Wearables" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Category Name (Bangla) <span class="text-danger">*</span></label>
                            <input type="text" name="name_bn" id="formCatNameBn" class="form-control" placeholder="যেমন: স্মার্ট ওয়াচ ও পরিধানযোগ্য" required>
                        </div>
                    </div>

                    <div class="card p-3 mb-3 border bg-light-subtle">
                        <h6 class="fw-bold mb-2 text-dark">
                            <i class="fa-solid fa-image text-primary me-1"></i> Category Image / Banner (WebP Engine)
                        </h6>
                        <div class="row g-3 align-items-center">
                            <div class="col-md-8">
                                <label class="form-label small fw-bold">Upload Category Image File</label>
                                <input type="file" name="image_file" id="formCatImageFile" accept="image/*" class="form-control form-control-sm" onchange="handleCatImageFileChange(this)">
                                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2 py-0.5 mt-1.5 small">
                                    <i class="fa-solid fa-wand-magic-sparkles me-1"></i> Auto-converts to WebP (Optimized)
                                </span>

                                <div class="mt-2">
                                    <label class="form-label small text-muted">Or Direct Image URL</label>
                                    <input type="text" name="image" id="formCatImage" class="form-control form-control-sm" placeholder="https://..." oninput="updateCatImgPreview(this.value)">
                                </div>
                            </div>
                            <div class="col-md-4 text-center">
                                <div class="border rounded-3 p-2 bg-white d-flex align-items-center justify-content-center" style="height: 120px;">
                                    <img src="" id="catImgPreview" alt="Image Preview" style="max-height: 100%; max-width: 100%; object-fit: contain; display: none;">
                                    <div id="noCatImgPlaceholder" class="text-muted small">
                                        <i class="fa-solid fa-image fs-3 d-block mb-1 text-secondary"></i>
                                        No image selected
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row g-2">
                        <div class="col-6">
                            <label class="form-label">Sort Order</label>
                            <input type="number" name="sort_order" id="formCatSort" class="form-control" value="0">
                        </div>
                        <div class="col-6 d-flex align-items-center pt-4">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_active" id="formCatIsActive" value="1" checked>
                                <label class="form-check-label fw-semibold" for="formCatIsActive">Active & Visible</label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary px-4 fw-bold" id="catSaveBtn">
                        <i class="fa-solid fa-check me-1"></i> Save Category
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="safeDeleteCategoryModal" tabindex="-1" aria-labelledby="safeDeleteCategoryModalLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header border-0 pb-0">
                <div class="d-flex align-items-center gap-2">
                    <div class="rounded-circle bg-danger-subtle text-danger d-inline-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                        <i class="fa-solid fa-triangle-exclamation fs-5"></i>
                    </div>
                    <div>
                        <h5 class="fw-bold mb-0 text-danger" id="safeDeleteCategoryModalLabel">Safe Category Deletion</h5>
                        <small class="text-muted">Reassign products before deleting</small>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="p-3 bg-danger-subtle border border-danger-subtle rounded-3 mb-3">
                    <div class="fw-bold text-danger mb-1" id="safeDeleteCategoryName">Category</div>
                    <div class="d-flex flex-wrap gap-2 mt-2">
                        <span class="badge bg-danger text-white rounded-pill px-2.5 py-1" id="safeDeleteProductsBadge">
                            <i class="fa-solid fa-box-open me-1"></i> 0 Products
                        </span>
                        <span class="badge bg-secondary text-white rounded-pill px-2.5 py-1" id="safeDeleteSubsBadge">
                            <i class="fa-solid fa-diagram-nested me-1"></i> 0 Subcategories
                        </span>
                    </div>
                </div>

                <p class="text-secondary small mb-3">
                    Deleting this category will permanently remove it and all of its nested subcategories. To prevent orphan products, please select a safe target category to reassign all affected products.
                </p>

                <div class="mb-3">
                    <label class="form-label fw-bold small text-dark">
                        <i class="fa-solid fa-arrow-right-arrow-left text-primary me-1"></i> Reassign Existing Products To: <span class="text-danger">*</span>
                    </label>
                    <select id="safeDeleteTargetSelect" class="form-select">
                        <option value="">-- Choose Target Category --</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger px-4 fw-bold" id="safeDeleteConfirmBtn" onclick="confirmExecuteSafeDelete()">
                    <i class="fa-solid fa-trash-can me-1"></i> Reassign & Delete
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(() => {
    let catModalInstance = null;
    let safeDeleteModalInstance = null;
    let categoriesTable = null;
    let categoryRoots = @json($rootCategories);
    let categorySubs = @json($subCategories);
    let currentTier = 1;
    let pendingDeleteCatId = null;

    function getCatModal() {
        const modalEl = document.getElementById('categoryModal');
        return modalEl ? bootstrap.Modal.getOrCreateInstance(modalEl) : null;
    }

    function getSafeDeleteModal() {
        const safeDeleteEl = document.getElementById('safeDeleteCategoryModal');
        return safeDeleteEl ? bootstrap.Modal.getOrCreateInstance(safeDeleteEl) : null;
    }

    function initCategoriesPage() {
        const tableEl = document.getElementById('categoriesTable');
        if (!tableEl) return;

        catModalInstance = getCatModal();
        safeDeleteModalInstance = getSafeDeleteModal();

        if (window.VanillaDataTable && VanillaDataTable.isDataTable('#categoriesTable')) {
            VanillaDataTable.getInstance('#categoriesTable').destroy();
        }

        categoriesTable = new VanillaDataTable('#categoriesTable', {
            processing: true,
            serverSide: true,
            ajax: "{{ route('admin.categories.index') }}",
            columns: [
                { data: 'image_preview', orderable: false, searchable: false, className: 'text-center' },
                { data: 'category_name', name: 'c.name' },
                { data: 'hierarchy', name: 'p.name' },
                { data: 'slug_url', name: 'c.slug' },
                { data: 'items_count', searchable: false, className: 'text-center' },
                { data: 'status_badge', name: 'c.is_active', className: 'text-center' },
                { data: 'actions', orderable: false, searchable: false, className: 'text-center' }
            ],
            lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
            pageLength: 10,
            order: [[1, 'asc']],
            language: {
                search: "_INPUT_",
                searchPlaceholder: "Search categories...",
                lengthMenu: "Show _MENU_ entries",
                processing: '<div class="spinner-border spinner-border-sm text-primary" role="status"></div> Loading categories...'
            },
            drawCallback: function () {
                const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
                tooltipTriggerList.forEach(el => new bootstrap.Tooltip(el));
            }
        });
    }

    document.addEventListener('turbo:load', initCategoriesPage);

    function handleTierChange(tier) {
        currentTier = parseInt(tier);
        const tBadge = document.getElementById('tierDisplayBadge');
        const t2Box = document.getElementById('tier2Container');
        const t3Box = document.getElementById('tier3Container');
        const parentIdInput = document.getElementById('formCatParentId');

        if (!tBadge || !t2Box || !t3Box || !parentIdInput) return;

        if (currentTier === 1) {
            tBadge.innerText = 'Tier 1: Main Category';
            tBadge.className = 'badge bg-primary rounded-pill px-2.5 py-1';
            t2Box.style.display = 'none';
            t3Box.style.display = 'none';
            parentIdInput.value = '';
        } else if (currentTier === 2) {
            tBadge.innerText = 'Tier 2: Sub Category';
            tBadge.className = 'badge bg-info text-dark rounded-pill px-2.5 py-1';
            t2Box.style.display = 'block';
            t3Box.style.display = 'none';
            parentIdInput.value = document.getElementById('tier2ParentSelect').value || '';
        } else if (currentTier === 3) {
            tBadge.innerText = 'Tier 3: Nested Category';
            tBadge.className = 'badge bg-secondary rounded-pill px-2.5 py-1';
            t2Box.style.display = 'none';
            t3Box.style.display = 'block';
            parentIdInput.value = document.getElementById('tier3SubSelect').value || '';
        }
    }

    function handleTier2ParentChange(val) {
        const el = document.getElementById('formCatParentId');
        if (el) el.value = val || '';
    }

    function handleTier3RootChange(rootId) {
        const subSelect = document.getElementById('tier3SubSelect');
        if (!subSelect) return;
        subSelect.innerHTML = '<option value="">-- Choose Sub Category --</option>';

        if (!rootId) {
            const el = document.getElementById('formCatParentId');
            if (el) el.value = '';
            return;
        }

        const filtered = categorySubs.filter(s => String(s.parent_id) === String(rootId));
        if (filtered.length === 0) {
            subSelect.innerHTML = '<option value="">-- No Sub Categories Found --</option>';
        } else {
            filtered.forEach(s => {
                const opt = document.createElement('option');
                opt.value = s.id;
                opt.textContent = `${s.name} (${s.name_bn || s.name})`;
                subSelect.appendChild(opt);
            });
        }
        const pEl = document.getElementById('formCatParentId');
        if (pEl) pEl.value = subSelect.value || '';
    }

    function handleTier3SubChange(val) {
        const el = document.getElementById('formCatParentId');
        if (el) el.value = val || '';
    }

    function refreshDropdownOptions() {
        const t2Select = document.getElementById('tier2ParentSelect');
        const t3RootSelect = document.getElementById('tier3RootSelect');
        if (!t2Select || !t3RootSelect) return;
        const prevT2 = t2Select.value;
        const prevT3Root = t3RootSelect.value;

        t2Select.innerHTML = '<option value="">-- Choose Main Category --</option>';
        t3RootSelect.innerHTML = '<option value="">-- Choose Main Category --</option>';

        categoryRoots.forEach(r => {
            const opt1 = document.createElement('option');
            opt1.value = r.id;
            opt1.textContent = `${r.name} (${r.name_bn || r.name})`;
            t2Select.appendChild(opt1);

            const opt2 = document.createElement('option');
            opt2.value = r.id;
            opt2.textContent = `${r.name} (${r.name_bn || r.name})`;
            t3RootSelect.appendChild(opt2);
        });

        if (prevT2) t2Select.value = prevT2;
        if (prevT3Root) {
            t3RootSelect.value = prevT3Root;
            handleTier3RootChange(prevT3Root);
        }
    }

    function handleCatImageFileChange(input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const preview = document.getElementById('catImgPreview');
                const placeholder = document.getElementById('noCatImgPlaceholder');
                if (preview) {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                }
                if (placeholder) {
                    placeholder.style.display = 'none';
                }
            };
            reader.readAsDataURL(input.files[0]);
        }
    }

    function updateCatImgPreview(url) {
        const preview = document.getElementById('catImgPreview');
        const placeholder = document.getElementById('noCatImgPlaceholder');
        if (url && url.trim()) {
            if (preview) {
                preview.src = url.trim();
                preview.style.display = 'block';
            }
            if (placeholder) {
                placeholder.style.display = 'none';
            }
        } else {
            if (preview) {
                preview.style.display = 'none';
            }
            if (placeholder) {
                placeholder.style.display = 'block';
            }
        }
    }

    function openCreateCategoryModal() {
        const form = document.getElementById('categoryForm');
        if (form) form.reset();
        const modalCatId = document.getElementById('modalCatId');
        if (modalCatId) modalCatId.value = '';
        const titleEl = document.getElementById('categoryModalLabel');
        if (titleEl) titleEl.innerText = 'Add New Category';
        const tier1Radio = document.getElementById('tier1Radio');
        if (tier1Radio) tier1Radio.checked = true;
        handleTierChange(1);
        updateCatImgPreview('');
        const modal = getCatModal();
        if (modal) modal.show();
    }

    function quickAddSubcategory(targetTier, rootId, subId) {
        const form = document.getElementById('categoryForm');
        if (form) form.reset();
        const modalCatId = document.getElementById('modalCatId');
        if (modalCatId) modalCatId.value = '';
        updateCatImgPreview('');

        if (targetTier === 2) {
            const titleEl = document.getElementById('categoryModalLabel');
            if (titleEl) titleEl.innerText = 'Add Sub Category';
            const t2Radio = document.getElementById('tier2Radio');
            if (t2Radio) t2Radio.checked = true;
            handleTierChange(2);
            const t2Parent = document.getElementById('tier2ParentSelect');
            if (t2Parent) t2Parent.value = rootId || '';
            const pId = document.getElementById('formCatParentId');
            if (pId) pId.value = rootId || '';
        } else if (targetTier === 3) {
            const titleEl = document.getElementById('categoryModalLabel');
            if (titleEl) titleEl.innerText = 'Add Nested Category';
            const t3Radio = document.getElementById('tier3Radio');
            if (t3Radio) t3Radio.checked = true;
            handleTierChange(3);
            const t3Root = document.getElementById('tier3RootSelect');
            if (t3Root) t3Root.value = rootId || '';
            handleTier3RootChange(rootId);
            const t3Sub = document.getElementById('tier3SubSelect');
            if (t3Sub) t3Sub.value = subId || '';
            const pId = document.getElementById('formCatParentId');
            if (pId) pId.value = subId || '';
        }

        const modal = getCatModal();
        if (modal) modal.show();
    }

    function openEditCategoryModal(catId) {
        const form = document.getElementById('categoryForm');
        if (form) form.reset();
        const modalCatId = document.getElementById('modalCatId');
        if (modalCatId) modalCatId.value = catId;
        const titleEl = document.getElementById('categoryModalLabel');
        if (titleEl) titleEl.innerText = 'Edit Category (#' + catId + ')';

        axios.get(`/admin/categories/${catId}/json`)
            .then(res => {
                const data = res.data;
                const cat = data.category || data;
                const tier = data.tier || 1;
                const parentId = data.parent_id || null;
                const grandparentId = data.grandparent_id || null;

                document.getElementById('formCatName').value = cat.name || '';
                document.getElementById('formCatNameBn').value = cat.name_bn || '';
                document.getElementById('formCatSort').value = cat.sort_order || 0;
                document.getElementById('formCatIsActive').checked = !!cat.is_active;
                document.getElementById('formCatImage').value = cat.image || '';
                updateCatImgPreview(cat.image || '');

                if (tier === 1) {
                    document.getElementById('tier1Radio').checked = true;
                    handleTierChange(1);
                } else if (tier === 2) {
                    document.getElementById('tier2Radio').checked = true;
                    handleTierChange(2);
                    document.getElementById('tier2ParentSelect').value = parentId || '';
                    document.getElementById('formCatParentId').value = parentId || '';
                } else if (tier === 3) {
                    document.getElementById('tier3Radio').checked = true;
                    handleTierChange(3);
                    document.getElementById('tier3RootSelect').value = grandparentId || '';
                    handleTier3RootChange(grandparentId);
                    document.getElementById('tier3SubSelect').value = parentId || '';
                    document.getElementById('formCatParentId').value = parentId || '';
                }

                const modal = getCatModal();
                if (modal) modal.show();
            })
            .catch(err => {
                Swal.fire('Error', 'Failed to load category data', 'error');
            });
    }

    function handleCategoryFormSubmit(e) {
        e.preventDefault();
        const form = document.getElementById('categoryForm');

        if (currentTier === 2 && !document.getElementById('formCatParentId').value) {
            Swal.fire('Required', 'Please select a Main Category for this Sub Category.', 'warning');
            return;
        }
        if (currentTier === 3 && !document.getElementById('formCatParentId').value) {
            Swal.fire('Required', 'Please select both Main Category and Sub Category for this Nested Category.', 'warning');
            return;
        }

        const formData = new FormData(form);
        const submitBtn = document.getElementById('catSaveBtn');
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-1"></i> Saving...';
        }

        axios.post('{{ route('admin.categories.ajax_save') }}', formData, {
            headers: { 'Accept': 'application/json' },
            skipToast: true
        })
        .then(res => {
            const data = res.data;
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="fa-solid fa-check me-1"></i> Save Category';
            }

            if (data.success) {
                const modal = getCatModal();
                if (modal) modal.hide();
                Swal.fire({
                    icon: 'success',
                    title: 'Saved!',
                    text: data.message,
                    timer: 1500,
                    showConfirmButton: false
                });
                if (categoriesTable) {
                    categoriesTable.ajax.reload(null, false);
                }
                axios.get('{{ route('admin.categories.tree_json') }}')
                    .then(treeRes => {
                        if (treeRes.data && treeRes.data.roots) {
                            categoryRoots = treeRes.data.roots;
                            categorySubs = treeRes.data.subs;
                            refreshDropdownOptions();
                        }
                    });
            } else {
                Swal.fire('Error', data.message || 'Validation error', 'error');
            }
        })
        .catch(err => {
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="fa-solid fa-check me-1"></i> Save Category';
            }
            const msg = err.response && err.response.data && err.response.data.message ? err.response.data.message : 'An unexpected error occurred';
            Swal.fire('Error', msg, 'error');
        });
    }

    function deleteCategoryAjax(id) {
        axios.get(`/admin/categories/${id}/delete-check`)
            .then(res => {
                const data = res.data;
                const cat = data.category || {};

                if (!data.has_dependents) {
                    Swal.fire({
                        title: 'Delete this category?',
                        html: `Are you sure you want to delete <strong>${cat.name || 'this category'}</strong>? It has no products or subcategories attached.`,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#ef4444',
                        cancelButtonColor: '#64748b',
                        confirmButtonText: '<i class="fa-solid fa-trash-can me-1"></i> Yes, delete it!'
                    }).then(result => {
                        if (result.isConfirmed) {
                            executeCategoryDelete(id, null);
                        }
                    });
                } else {
                    pendingDeleteCatId = id;
                    document.getElementById('safeDeleteCategoryName').innerText = `${cat.name} (${cat.name_bn || cat.name})`;
                    document.getElementById('safeDeleteProductsBadge').innerHTML = `<i class="fa-solid fa-box-open me-1"></i> ${data.products_count} Products`;
                    document.getElementById('safeDeleteSubsBadge').innerHTML = `<i class="fa-solid fa-diagram-nested me-1"></i> ${data.subcategories_count} Subcategories`;

                    const targetSelect = document.getElementById('safeDeleteTargetSelect');
                    targetSelect.innerHTML = '<option value="">-- Choose Target Category --</option>';

                    if (data.available_targets && data.available_targets.length > 0) {
                        data.available_targets.forEach(t => {
                            const opt = document.createElement('option');
                            opt.value = t.id;
                            const prefix = t.parent_id ? '↳ ' : '● ';
                            opt.textContent = `${prefix}${t.name} (${t.name_bn || t.name})`;
                            targetSelect.appendChild(opt);
                        });
                    }

                    const safeModal = getSafeDeleteModal();
                    if (safeModal) {
                        safeModal.show();
                    }
                }
            })
            .catch(err => {
                Swal.fire('Error', 'Failed to inspect category dependencies.', 'error');
            });
    }

    function confirmExecuteSafeDelete() {
        if (!pendingDeleteCatId) return;

        const targetSelect = document.getElementById('safeDeleteTargetSelect');
        const targetId = targetSelect.value;

        if (!targetId) {
            Swal.fire('Target Required', 'Please select a target category to reassign the affected products before deleting.', 'warning');
            return;
        }

        const confirmBtn = document.getElementById('safeDeleteConfirmBtn');
        confirmBtn.disabled = true;
        confirmBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-1"></i> Reassigning & Deleting...';

        executeCategoryDelete(pendingDeleteCatId, targetId, () => {
            confirmBtn.disabled = false;
            confirmBtn.innerHTML = '<i class="fa-solid fa-trash-can me-1"></i> Reassign & Delete';
            const safeModal = getSafeDeleteModal();
            if (safeModal) {
                safeModal.hide();
            }
            pendingDeleteCatId = null;
        });
    }

    function executeCategoryDelete(id, reassignToId, onComplete) {
        const payload = {};
        if (reassignToId) {
            payload.reassign_to_id = reassignToId;
        }

        axios.delete(`/admin/categories/${id}/ajax-delete`, {
            data: payload,
            headers: { 'Accept': 'application/json' }
        })
        .then(res => {
            if (onComplete) onComplete();
            const data = res.data;
            if (data.success) {
                if (categoriesTable) {
                    categoriesTable.ajax.reload(null, false);
                }
                axios.get('{{ route('admin.categories.tree_json') }}')
                    .then(treeRes => {
                        if (treeRes.data && treeRes.data.roots) {
                            categoryRoots = treeRes.data.roots;
                            categorySubs = treeRes.data.subs;
                            refreshDropdownOptions();
                        }
                    });
                Swal.fire('Deleted!', data.message || 'Category has been removed.', 'success');
            } else {
                Swal.fire('Error', data.message || 'Failed to delete category', 'error');
            }
        })
        .catch(err => {
            if (onComplete) onComplete();
            const msg = err.response && err.response.data && err.response.data.message ? err.response.data.message : 'Failed to delete category';
            Swal.fire('Error', msg, 'error');
        });
    }

    window.handleTierChange = handleTierChange;
    window.handleTier2ParentChange = handleTier2ParentChange;
    window.handleTier3RootChange = handleTier3RootChange;
    window.handleTier3SubChange = handleTier3SubChange;
    window.handleCatImageFileChange = handleCatImageFileChange;
    window.updateCatImgPreview = updateCatImgPreview;
    window.openCreateCategoryModal = openCreateCategoryModal;
    window.quickAddSubcategory = quickAddSubcategory;
    window.openEditCategoryModal = openEditCategoryModal;
    window.handleCategoryFormSubmit = handleCategoryFormSubmit;
    window.deleteCategoryAjax = deleteCategoryAjax;
    window.confirmExecuteSafeDelete = confirmExecuteSafeDelete;
})();
</script>
@endpush