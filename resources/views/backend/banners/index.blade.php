@extends('backend.layouts.app')

@section('title', 'Hero Banners & Sliders')

@section('content')
<div class="card p-3 p-md-4 mb-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <h3 class="fw-bold mb-0">Hero Banners & Promotional Sliders</h3>
            <small class="text-muted">Fully customized hero slider with 1920x830 resolution support, display modes, and overlay controls</small>
        </div>
        @canPerm('admin.banners.store')
            <button type="button" class="btn btn-success" onclick="openCreateBannerModal()" id="addBtn">
                <i class="fa-solid fa-plus fa-fade"></i> Add New Banner
            </button>
        @endcanPerm
    </div>
</div>

<div class="row g-4 mb-4" id="bannersGrid">
    @forelse($banners as $banner)
        <div class="col-12 col-md-6 col-xl-4" id="bannerCard_{{ $banner->id }}">
            <div class="card h-100 p-0 overflow-hidden d-flex flex-column shadow-sm border">
                <div class="position-relative bg-dark" style="aspect-ratio: 1920/830; overflow: hidden;">
                    @if(!empty($banner->image_url))
                        <img src="{{ $banner->image_url }}" alt="{{ $banner->title ?? '' }}" class="w-100 h-100 object-fit-cover" onerror="this.onerror=null;this.src='{{ asset('images/banner-placeholder.svg') }}';">
                    @else
                        <div class="w-100 h-100 d-flex align-items-center justify-content-center text-white" style="background: linear-gradient(135deg, #090d16 0%, #1e293b 100%);">
                            <i class="fa-solid fa-font fs-1 text-secondary"></i>
                        </div>
                    @endif

                    @if(!empty($banner->overlay_enabled))
                        <div class="position-absolute inset-0 top-0 start-0 w-100 h-100 pointer-events-none" style="background-color: {{ $banner->overlay_color ?? '#000000' }}; opacity: {{ ((int)($banner->overlay_opacity ?? 40)) / 100 }};"></div>
                    @endif

                    <span class="position-absolute top-0 end-0 m-2 badge bg-{{ !empty($banner->is_active) ? 'success' : 'secondary' }} shadow-sm">
                        {{ !empty($banner->is_active) ? 'Active' : 'Inactive' }}
                    </span>
                    <span class="position-absolute top-0 start-0 m-2 badge bg-dark text-white border border-secondary shadow-sm">
                        Sort: {{ $banner->sort_order ?? 0 }}
                    </span>

                    <span class="position-absolute bottom-0 start-0 m-2 badge bg-primary text-white shadow-sm">
                        @if(($banner->display_mode ?? 'both') === 'image_only')
                            <i class="fa-solid fa-image me-1"></i> Image Only
                        @elseif(($banner->display_mode ?? 'both') === 'text_only')
                            <i class="fa-solid fa-font me-1"></i> Text Only
                        @else
                            <i class="fa-solid fa-layer-group me-1"></i> Image + Text
                        @endif
                    </span>
                </div>
                <div class="p-3 d-flex flex-column flex-grow-1">
                    <h6 class="fw-bold mb-1">{{ !empty($banner->title) ? $banner->title : 'Untitled Banner' }}</h6>
                    @if(!empty($banner->badge_text))
                        <div class="text-muted small mb-1">{{ $banner->badge_text }}</div>
                    @endif
                    @if(!empty($banner->subtitle))
                        <p class="text-secondary small mb-2 text-truncate">{{ $banner->subtitle }}</p>
                    @endif
                    <div class="text-muted small mb-3">
                        <i class="fa-solid fa-link me-1"></i> <code>{{ !empty($banner->btn_link) ? $banner->btn_link : '/' }}</code>
                    </div>

                    <div class="mt-auto pt-2 border-top d-flex align-items-center justify-content-between">
                        <span class="badge bg-light text-dark border">
                            {{ !empty($banner->btn_text) ? $banner->btn_text : 'No CTA text' }}
                        </span>
                        <div class="d-flex gap-1">
                            @canPerm('admin.banners.update')
                                <button type="button" class="btn-action text-primary" onclick="openEditBannerModal({{ $banner->id }})" title="Edit Banner">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </button>
                            @endcanPerm
                            @canPerm('admin.banners.delete')
                                <button type="button" class="btn-action text-danger" onclick="deleteBannerAjax({{ $banner->id }})" title="Delete Banner">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            @endcanPerm
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @empty
        <div class="col-12">
            <div class="card text-center py-5 text-muted">
                <i class="fa-solid fa-images fs-1 d-block mb-2 text-secondary"></i>
                No promotional banners found.
            </div>
        </div>
    @endforelse
</div>

<div class="modal fade" id="bannerModal" tabindex="-1" aria-labelledby="bannerModalLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
        <div class="modal-content border-0">
            <form id="bannerForm" onsubmit="handleBannerFormSubmit(event)" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="id" id="modalBannerId" value="">

                <div class="modal-header border-0 pb-0">
                    <div>
                        <h5 class="fw-bold mb-0" id="bannerModalLabel">Hero Banner Customizer</h5>
                        <small class="text-muted">Target Resolution: <strong>1920 × 830 px</strong> (Aspect ratio 2.31:1)</small>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body p-4">
                    <div class="card p-3 mb-3 bg-light border">
                        <label class="form-label fw-bold mb-2 text-dark"><i class="fa-solid fa-sliders text-primary me-1"></i> Display Mode</label>
                        <div class="row g-2">
                            <div class="col-4">
                                <input type="radio" class="btn-check" name="display_mode" id="modeBoth" value="both" checked onchange="toggleBannerDisplayMode(this.value)">
                                <label class="btn btn-outline-primary w-100 py-2 d-flex flex-column align-items-center justify-content-center gap-1" for="modeBoth">
                                    <i class="fa-solid fa-layer-group fs-5"></i>
                                    <span class="small fw-semibold">Image + Text</span>
                                </label>
                            </div>
                            <div class="col-4">
                                <input type="radio" class="btn-check" name="display_mode" id="modeImageOnly" value="image_only" onchange="toggleBannerDisplayMode(this.value)">
                                <label class="btn btn-outline-primary w-100 py-2 d-flex flex-column align-items-center justify-content-center gap-1" for="modeImageOnly">
                                    <i class="fa-solid fa-image fs-5"></i>
                                    <span class="small fw-semibold">Image Only</span>
                                </label>
                            </div>
                            <div class="col-4">
                                <input type="radio" class="btn-check" name="display_mode" id="modeTextOnly" value="text_only" onchange="toggleBannerDisplayMode(this.value)">
                                <label class="btn btn-outline-primary w-100 py-2 d-flex flex-column align-items-center justify-content-center gap-1" for="modeTextOnly">
                                    <i class="fa-solid fa-font fs-5"></i>
                                    <span class="small fw-semibold">Text Only</span>
                                </label>
                            </div>
                        </div>
                        <div class="form-text mt-2 text-muted" id="modeHelpText">
                            Shows high-res background image with readable title, subtitle and CTA button.
                        </div>
                    </div>

                    <div class="card p-3 mb-3 bg-light border" id="bannerGraphicCard">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="fw-bold mb-0 text-dark"><i class="fa-solid fa-image text-primary me-1"></i> Banner Graphic (1920 × 830)</h6>
                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-2 py-0.5 small">
                                Optimal 1920x830 px
                            </span>
                        </div>
                        <div class="row g-3 align-items-center">
                            <div class="col-md-7">
                                <label class="form-label small fw-bold">Upload Banner Image</label>
                                <input type="file" name="image_file" id="formBannerImageFile" accept="image/*" class="form-control form-control-sm" onchange="previewBannerFile(this)">
                                <div class="mt-2">
                                    <label class="form-label small text-muted">Or Direct Image URL</label>
                                    <input type="text" name="image_url" id="formBannerImage" class="form-control form-control-sm" placeholder="https://..." oninput="updateBannerImgPreview(this.value)">
                                </div>
                            </div>
                            <div class="col-md-5 text-center">
                                <div class="border rounded-3 p-1 bg-dark position-relative overflow-hidden d-flex align-items-center justify-content-center" style="aspect-ratio: 1920/830; max-height: 120px;">
                                    <img src="" id="bannerImgPreview" alt="Banner Preview" style="width: 100%; height: 100%; object-fit: cover; display: none;">
                                    <div id="bannerModalOverlayPreview" class="position-absolute top-0 start-0 w-100 h-100 pointer-events-none" style="display: none; background-color: #000000; opacity: 0.4;"></div>
                                    <div id="noBannerPlaceholder" class="text-white-50 small p-2 text-center">
                                        <i class="fa-solid fa-image fs-4 d-block mb-1 text-secondary"></i>
                                        1920 × 830 Preview
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card p-3 mb-3 bg-light border" id="bannerOverlayCard">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="fw-bold mb-0 text-dark"><i class="fa-solid fa-circle-half-stroke text-primary me-1"></i> Overlay Controller</h6>
                                <small class="text-muted">Add custom translucent color layer over the image or disable completely</small>
                            </div>
                            <div class="form-check form-switch m-0">
                                <input class="form-check-input" type="checkbox" name="overlay_enabled" id="formBannerOverlayEnabled" value="1" onchange="toggleOverlayFields(this.checked)">
                                <label class="form-check-label fw-semibold" for="formBannerOverlayEnabled">Enable Overlay</label>
                            </div>
                        </div>

                        <div id="overlayControlsWrapper" class="row g-3 mt-1 pt-2 border-top" style="display: none;">
                            <div class="col-md-5">
                                <label class="form-label small fw-bold">Overlay Color</label>
                                <div class="input-group input-group-sm">
                                    <input type="color" class="form-control form-control-color" id="formBannerOverlayColorPicker" value="#000000" title="Choose color" onchange="onOverlayColorPickerChange(this.value)">
                                    <input type="text" name="overlay_color" id="formBannerOverlayColor" class="form-control" value="#000000" oninput="onOverlayColorTextChange(this.value)">
                                </div>
                            </div>
                            <div class="col-md-7">
                                <div class="d-flex justify-content-between align-items-center">
                                    <label class="form-label small fw-bold mb-1">Overlay Opacity</label>
                                    <span class="badge bg-dark" id="opacityDisplay">40%</span>
                                </div>
                                <input type="range" name="overlay_opacity" id="formBannerOverlayOpacity" class="form-range" min="0" max="100" step="5" value="40" oninput="onOverlayOpacityChange(this.value)">
                            </div>
                        </div>
                    </div>

                    <div id="textFieldsSection">
                        <div class="card p-3 mb-3 bg-light border">
                            <h6 class="fw-bold mb-2 text-dark"><i class="fa-solid fa-align-left text-primary me-1"></i> Text Typography & Alignment</h6>
                            <div class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold">Text Alignment</label>
                                    <div class="btn-group btn-group-sm w-100" role="group">
                                        <input type="radio" class="btn-check" name="text_align" id="alignLeft" value="left" checked>
                                        <label class="btn btn-outline-secondary" for="alignLeft"><i class="fa-solid fa-align-left me-1"></i> Left</label>

                                        <input type="radio" class="btn-check" name="text_align" id="alignCenter" value="center">
                                        <label class="btn btn-outline-secondary" for="alignCenter"><i class="fa-solid fa-align-center me-1"></i> Center</label>

                                        <input type="radio" class="btn-check" name="text_align" id="alignRight" value="right">
                                        <label class="btn btn-outline-secondary" for="alignRight"><i class="fa-solid fa-align-right me-1"></i> Right</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold">Text Color</label>
                                    <div class="input-group input-group-sm">
                                        <input type="color" class="form-control form-control-color" id="formBannerTextColorPicker" value="#ffffff" title="Text Color" onchange="document.getElementById('formBannerTextColor').value = this.value">
                                        <input type="text" name="text_color" id="formBannerTextColor" class="form-control" value="#ffffff" oninput="document.getElementById('formBannerTextColorPicker').value = this.value">
                                    </div>
                                </div>
                            </div>

                            <div class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Banner Title (English)</label>
                                    <input type="text" name="title" id="formBannerTitle" class="form-control" placeholder="e.g. Next-Gen Wireless Audio">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Badge Text / Bengali Subhead</label>
                                    <input type="text" name="badge_text" id="formBannerTitleBn" class="form-control" placeholder="যেমন: হট ডিল অথবা প্রিমিয়াম হেডফোন">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Subtitle / Promotional Offer</label>
                                <input type="text" name="subtitle" id="formBannerSubtitle" class="form-control" placeholder="e.g. Up to 40% Off on Best-Selling Gadgets">
                            </div>

                            <div class="mb-0">
                                <label class="form-label">Button Text</label>
                                <input type="text" name="btn_text" id="formBannerBtnText" class="form-control" placeholder="e.g. Shop Now / এখনই কিনুন">
                            </div>
                        </div>
                    </div>

                    <div class="card p-3 mb-3 bg-light border">
                        <label class="form-label fw-bold mb-1 text-dark"><i class="fa-solid fa-link text-primary me-1"></i> Destination Link <span class="text-danger">*</span></label>
                        <div class="mb-2">
                            <select id="formBannerLinkCategory" class="form-select form-select-sm" onchange="onBannerCategoryPick(this)">
                                <option value="">— Pick a category (optional) —</option>
                                @foreach($categories as $cat)
                                    <option value="/category/{{ $cat->slug }}">{{ $cat->name }}</option>
                                @endforeach
                                <option value="/products">All Products</option>
                                <option value="/flash-deals">Flash Deals</option>
                                <option value="/new-collection">New Collection</option>
                                <option value="/best-sale">Best Sale</option>
                                <option value="custom">✎ Custom URL...</option>
                            </select>
                        </div>
                        <input type="text" name="btn_link" id="formBannerLink" class="form-control form-control-sm" placeholder="e.g. /category/electronics-gadgets" required>
                        <div class="form-text text-muted">When clicked, visitors navigate to this page. In "Image Only" mode, clicking the banner opens this link.</div>
                    </div>

                    <div class="row g-2">
                        <div class="col-6">
                            <label class="form-label">Display Sort Order</label>
                            <input type="number" name="sort_order" id="formBannerSort" class="form-control" value="0">
                        </div>
                        <div class="col-6 d-flex align-items-center pt-4">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_active" id="formBannerIsActive" value="1" checked>
                                <label class="form-check-label fw-semibold" for="formBannerIsActive">Active & Live</label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary px-4 fw-bold" id="bannerSaveBtn">
                        <i class="fa-solid fa-check me-1"></i> Save Banner
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function() {
    let bannerModalInstance = null;

    function getBannerModal() {
        const modalEl = document.getElementById('bannerModal');
        if (!modalEl) return null;
        if (!bannerModalInstance) {
            bannerModalInstance = new bootstrap.Modal(modalEl);
        }
        return bannerModalInstance;
    }

    function toggleBannerDisplayMode(mode) {
        const textSection = document.getElementById('textFieldsSection');
        const graphicCard = document.getElementById('bannerGraphicCard');
        const overlayCard = document.getElementById('bannerOverlayCard');
        const helpText = document.getElementById('modeHelpText');

        if (mode === 'image_only') {
            if (textSection) textSection.style.display = 'none';
            if (graphicCard) graphicCard.style.display = 'block';
            if (overlayCard) overlayCard.style.display = 'block';
            if (helpText) helpText.innerText = 'Displays raw 1920x830 banner image. Clicking anywhere on the banner opens the destination link.';
        } else if (mode === 'text_only') {
            if (textSection) textSection.style.display = 'block';
            if (graphicCard) graphicCard.style.display = 'none';
            if (overlayCard) overlayCard.style.display = 'none';
            if (helpText) helpText.innerText = 'Displays typography and CTA button over a styled background without requiring an image upload.';
        } else {
            if (textSection) textSection.style.display = 'block';
            if (graphicCard) graphicCard.style.display = 'block';
            if (overlayCard) overlayCard.style.display = 'block';
            if (helpText) helpText.innerText = 'Shows 1920x830 background image with readable title, subtitle, badge and CTA button.';
        }
    }

    function toggleOverlayFields(enabled) {
        const wrapper = document.getElementById('overlayControlsWrapper');
        const preview = document.getElementById('bannerModalOverlayPreview');
        if (wrapper) {
            wrapper.style.display = enabled ? 'flex' : 'none';
        }
        if (preview) {
            preview.style.display = enabled ? 'block' : 'none';
        }
    }

    function onOverlayColorPickerChange(color) {
        document.getElementById('formBannerOverlayColor').value = color;
        updateOverlayPreview();
    }

    function onOverlayColorTextChange(color) {
        document.getElementById('formBannerOverlayColorPicker').value = color;
        updateOverlayPreview();
    }

    function onOverlayOpacityChange(val) {
        const badge = document.getElementById('opacityDisplay');
        if (badge) badge.innerText = val + '%';
        updateOverlayPreview();
    }

    function updateOverlayPreview() {
        const preview = document.getElementById('bannerModalOverlayPreview');
        const color = document.getElementById('formBannerOverlayColor').value || '#000000';
        const opacity = (parseInt(document.getElementById('formBannerOverlayOpacity').value || 40, 10)) / 100;
        if (preview) {
            preview.style.backgroundColor = color;
            preview.style.opacity = opacity;
        }
    }

    function previewBannerFile(input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const preview = document.getElementById('bannerImgPreview');
                const placeholder = document.getElementById('noBannerPlaceholder');
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

    function updateBannerImgPreview(url) {
        const preview = document.getElementById('bannerImgPreview');
        const placeholder = document.getElementById('noBannerPlaceholder');
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

    function onBannerCategoryPick(sel) {
        const val = sel.value;
        const linkEl = document.getElementById('formBannerLink');
        if (!linkEl) return;
        if (val && val !== 'custom') {
            linkEl.value = val;
        }
        if (!val) {
            linkEl.value = '';
        }
    }

    function syncCategoryDropdown(url) {
        const sel = document.getElementById('formBannerLinkCategory');
        if (!sel || !url) return;
        let matched = false;
        for (let i = 0; i < sel.options.length; i++) {
            if (sel.options[i].value === url) {
                sel.selectedIndex = i;
                matched = true;
                break;
            }
        }
        if (!matched) {
            sel.value = url ? 'custom' : '';
        }
    }

    function openCreateBannerModal() {
        const form = document.getElementById('bannerForm');
        if (form) form.reset();
        const bannerIdEl = document.getElementById('modalBannerId');
        if (bannerIdEl) bannerIdEl.value = '';
        const titleEl = document.getElementById('bannerModalLabel');
        if (titleEl) titleEl.innerText = 'Add Hero Banner';
        const catSel = document.getElementById('formBannerLinkCategory');
        if (catSel) catSel.selectedIndex = 0;

        document.getElementById('modeBoth').checked = true;
        toggleBannerDisplayMode('both');

        document.getElementById('formBannerOverlayEnabled').checked = false;
        toggleOverlayFields(false);

        document.getElementById('formBannerOverlayColor').value = '#000000';
        document.getElementById('formBannerOverlayColorPicker').value = '#000000';
        document.getElementById('formBannerOverlayOpacity').value = 40;
        document.getElementById('opacityDisplay').innerText = '40%';
        document.getElementById('alignLeft').checked = true;
        document.getElementById('formBannerTextColor').value = '#ffffff';
        document.getElementById('formBannerTextColorPicker').value = '#ffffff';

        updateBannerImgPreview('');
        updateOverlayPreview();

        const modal = getBannerModal();
        if (modal) modal.show();
    }

    function openEditBannerModal(bannerId) {
        const form = document.getElementById('bannerForm');
        if (form) form.reset();
        const bannerIdEl = document.getElementById('modalBannerId');
        if (bannerIdEl) bannerIdEl.value = bannerId;
        const titleEl = document.getElementById('bannerModalLabel');
        if (titleEl) titleEl.innerText = 'Edit Hero Banner (#' + bannerId + ')';

        axios.get(`/admin/banners/${bannerId}/json`)
            .then(res => {
                const data = res.data;
                const b = data.banner || data;

                const mode = b.display_mode || 'both';
                if (mode === 'image_only') {
                    document.getElementById('modeImageOnly').checked = true;
                } else if (mode === 'text_only') {
                    document.getElementById('modeTextOnly').checked = true;
                } else {
                    document.getElementById('modeBoth').checked = true;
                }
                toggleBannerDisplayMode(mode);

                document.getElementById('formBannerTitle').value = b.title || '';
                document.getElementById('formBannerTitleBn').value = b.badge_text || '';
                document.getElementById('formBannerSubtitle').value = b.subtitle || '';
                document.getElementById('formBannerImage').value = b.image_url || '';
                document.getElementById('formBannerBtnText').value = b.btn_text || '';
                document.getElementById('formBannerLink').value = b.btn_link || '';
                syncCategoryDropdown(b.btn_link || '');
                document.getElementById('formBannerSort').value = b.sort_order || 0;
                document.getElementById('formBannerIsActive').checked = !!b.is_active;

                const hasOverlay = !!b.overlay_enabled;
                document.getElementById('formBannerOverlayEnabled').checked = hasOverlay;
                toggleOverlayFields(hasOverlay);

                const overlayColor = b.overlay_color || '#000000';
                document.getElementById('formBannerOverlayColor').value = overlayColor;
                document.getElementById('formBannerOverlayColorPicker').value = overlayColor;

                const opacity = b.overlay_opacity !== undefined && b.overlay_opacity !== null ? b.overlay_opacity : 40;
                document.getElementById('formBannerOverlayOpacity').value = opacity;
                document.getElementById('opacityDisplay').innerText = opacity + '%';

                const align = b.text_align || 'left';
                if (align === 'center') {
                    document.getElementById('alignCenter').checked = true;
                } else if (align === 'right') {
                    document.getElementById('alignRight').checked = true;
                } else {
                    document.getElementById('alignLeft').checked = true;
                }

                const textColor = b.text_color || '#ffffff';
                document.getElementById('formBannerTextColor').value = textColor;
                document.getElementById('formBannerTextColorPicker').value = textColor;

                updateBannerImgPreview(b.image_url || '');
                updateOverlayPreview();

                const modal = getBannerModal();
                if (modal) modal.show();
            })
            .catch(err => {
                Swal.fire('Error', 'Failed to load banner data', 'error');
            });
    }

    function handleBannerFormSubmit(e) {
        e.preventDefault();
        const form = document.getElementById('bannerForm');
        const formData = new FormData(form);
        const submitBtn = document.getElementById('bannerSaveBtn');
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-1"></i> Saving...';
        }

        axios.post('{{ route('admin.banners.ajax_save') }}', formData, {
            headers: { 'Accept': 'application/json' },
            skipToast: true
        })
        .then(res => {
            const data = res.data;
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="fa-solid fa-check me-1"></i> Save Banner';
            }

            if (data.success) {
                const modal = getBannerModal();
                if (modal) modal.hide();
                Swal.fire({
                    icon: 'success',
                    title: 'Saved!',
                    text: data.message,
                    timer: 1500,
                    showConfirmButton: false
                }).then(() => {
                    if (window.Turbo) {
                        window.Turbo.visit(window.location.href, { action: 'replace' });
                    } else {
                        location.reload();
                    }
                });
            } else {
                Swal.fire('Error', data.message || 'Validation error', 'error');
            }
        })
        .catch(err => {
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="fa-solid fa-check me-1"></i> Save Banner';
            }
            Swal.fire('Error', 'An unexpected error occurred', 'error');
        });
    }

    function deleteBannerAjax(id) {
        Swal.fire({
            title: 'Delete this banner?',
            text: 'This will remove the banner from the storefront slider.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Yes, delete it!'
        }).then(result => {
            if (result.isConfirmed) {
                axios.delete(`/admin/banners/${id}/ajax-delete`, {
                    headers: { 'Accept': 'application/json' }
                })
                .then(res => {
                    const data = res.data;
                    if (data.success) {
                        const card = document.getElementById(`bannerCard_${id}`);
                        if (card) card.remove();
                        Swal.fire('Deleted!', 'Banner has been removed.', 'success');
                    } else {
                        Swal.fire('Error', data.message || 'Failed to delete banner', 'error');
                    }
                });
            }
        });
    }

    window.toggleBannerDisplayMode = toggleBannerDisplayMode;
    window.toggleOverlayFields = toggleOverlayFields;
    window.onOverlayColorPickerChange = onOverlayColorPickerChange;
    window.onOverlayColorTextChange = onOverlayColorTextChange;
    window.onOverlayOpacityChange = onOverlayOpacityChange;
    window.previewBannerFile = previewBannerFile;
    window.updateBannerImgPreview = updateBannerImgPreview;
    window.onBannerCategoryPick = onBannerCategoryPick;
    window.openCreateBannerModal = openCreateBannerModal;
    window.openEditBannerModal = openEditBannerModal;
    window.handleBannerFormSubmit = handleBannerFormSubmit;
    window.deleteBannerAjax = deleteBannerAjax;
})();
</script>
@endpush