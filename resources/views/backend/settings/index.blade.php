@extends('backend.layouts.app')

@section('title', 'Store Settings')

@section('content')
<div class="card p-3 p-md-4 mb-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <h3 class="fw-bold mb-1">Store Settings & Configuration</h3>
            <small class="text-muted">General store profile, shipping rates, hotline, announcement bar, and social connections</small>
        </div>
        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-1.5 small fw-semibold">
                <i class="fa-solid fa-bolt me-1 text-success"></i> Auto-Save Active
            </span>
            @canPerm('admin.settings.update')
                <button type="button" class="btn btn-primary btn-sm px-3.5 py-2 fw-semibold rounded-3" onclick="document.getElementById('settingsForm').requestSubmit()">
                    <i class="fa-solid fa-floppy-disk me-1.5"></i> Save Changes
                </button>
            @endcanPerm
        </div>
    </div>
</div>

<form id="settingsForm" data-autosave="true" enctype="multipart/form-data" onsubmit="handleSettingsSubmit(event)">
    @csrf
    <div class="row g-4 mb-4">
        <div class="col-lg-6">
            <div class="card p-3 p-md-4 h-100">
                <h5 class="fw-bold mb-3 border-bottom pb-2">
                    <i class="fa-solid fa-store text-primary me-1"></i> Store Profile & Contact Info
                </h5>
                <div class="d-flex flex-column gap-3">
                    <div class="p-3 bg-light rounded-3 border">
                        <label class="form-label fw-bold d-flex align-items-center justify-content-between mb-2">
                            <span><i class="fa-solid fa-image text-primary me-1"></i> Site Brand Logo (লোগো)</span>
                            <span class="badge bg-secondary-subtle text-secondary small">PNG, WebP, SVG, JPG</span>
                        </label>
                        <div class="d-flex align-items-center gap-3 flex-wrap">
                            <div class="rounded-3 border bg-white d-flex align-items-center justify-content-center p-2 position-relative" style="width: 140px; height: 60px; min-width: 140px; overflow: hidden; background: repeating-conic-gradient(#f1f5f9 0% 25%, #ffffff 0% 50%) 50% / 12px 12px;">
                                @php
                                    $currentLogo = $settings['site_logo'] ?? ($settings['store_logo'] ?? '');
                                @endphp
                                <img id="siteLogoPreview" src="{{ !empty($currentLogo) ? asset($currentLogo) : '' }}" alt="Logo" style="max-height: 100%; max-width: 100%; object-fit: contain; display: {{ !empty($currentLogo) ? 'block' : 'none' }};">
                                <span id="siteLogoPlaceholder" class="text-muted small fw-medium" style="display: {{ empty($currentLogo) ? 'block' : 'none' }}; font-size: 11px;">
                                    <i class="fa-solid fa-cloud-arrow-up me-1"></i> No Logo
                                </span>
                            </div>
                            <div class="flex-grow-1">
                                <input type="file" name="site_logo" id="siteLogoInput" class="form-control form-control-sm" accept="image/png,image/jpeg,image/webp,image/svg+xml,image/x-icon" onchange="previewSiteLogo(this)">
                                <input type="hidden" name="remove_site_logo" id="removeSiteLogoInput" value="0">
                                <div class="d-flex align-items-center justify-content-between mt-1">
                                    <small class="text-muted" style="font-size: 11px;">Recommended: Transparent PNG or SVG (height 40-50px)</small>
                                    <button type="button" id="removeLogoBtn" class="btn btn-link text-danger p-0 small text-decoration-none" style="font-size: 11px; display: {{ !empty($currentLogo) ? 'inline-block' : 'none' }};" onclick="handleRemoveSiteLogo()">
                                        <i class="fa-solid fa-trash-can me-1"></i> Remove Logo
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div>
                        <label class="form-label">Store Brand Name <span class="text-danger">*</span></label>
                        <input type="text" name="store_name" class="form-control" value="{{ $settings['store_name'] ?? 'Zippy' }}" required>
                    </div>
                    <div>
                        <label class="form-label">Store Tagline</label>
                        <input type="text" name="store_tagline" class="form-control" value="{{ $settings['store_tagline'] ?? 'Premium & Trendy Lifestyle Tech Store' }}">
                    </div>
                    <div class="row g-2">
                        <div class="col-md-6">
                            <label class="form-label">Hotline Phone Number <span class="text-danger">*</span></label>
                            <input type="text" name="store_phone" class="form-control" value="{{ $settings['store_phone'] ?? '01700000000' }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">WhatsApp Order Hotline <span class="text-danger">*</span></label>
                            <input type="text" name="store_whatsapp" class="form-control" value="{{ $settings['store_whatsapp'] ?? '01700000000' }}" required>
                        </div>
                    </div>
                    <div>
                        <label class="form-label">Support Email Address</label>
                        <input type="email" name="store_email" class="form-control" value="{{ $settings['store_email'] ?? 'support@Zippy.com' }}">
                    </div>
                    <div>
                        <label class="form-label">Physical Showroom / Hub Address</label>
                        <textarea name="store_address" class="form-control" rows="2">{{ $settings['store_address'] ?? 'House #12, Road #05, Mirpur-10, Dhaka-1216' }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="d-flex flex-column gap-4">
                <div class="card p-3 p-md-4">
                    <h5 class="fw-bold mb-3 border-bottom pb-2">
                        <i class="fa-solid fa-truck-fast text-primary me-1"></i> Shipping & Delivery Rates
                    </h5>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Inside Dhaka Fee (৳) <span class="text-danger">*</span></label>
                            <input type="number" name="shipping_dhaka" class="form-control" value="{{ $settings['shipping_dhaka'] ?? 60 }}" required>
                            <small class="text-muted">Applied when selecting Dhaka district</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Outside Dhaka Fee (৳) <span class="text-danger">*</span></label>
                            <input type="number" name="shipping_outside" class="form-control" value="{{ $settings['shipping_outside'] ?? 120 }}" required>
                            <small class="text-muted">Applied for 63 other districts</small>
                        </div>
                        <div class="col-12">
                            <div class="p-3 bg-light rounded-3 border">
                                <div class="form-check form-switch mb-2">
                                    <input class="form-check-input" type="checkbox" name="free_shipping_enabled" id="freeShippingEnabledSwitch" value="1" {{ (!empty($settings['free_shipping_enabled']) && $settings['free_shipping_enabled'] == '1') ? 'checked' : '' }}>
                                    <label class="form-check-label fw-bold" for="freeShippingEnabledSwitch">Enable Global Free Shipping</label>
                                </div>
                                <label class="form-label small fw-semibold">Minimum Order Amount for Free Shipping (৳)</label>
                                <input type="number" name="free_shipping_min_amount" class="form-control" value="{{ $settings['free_shipping_min_amount'] ?? ($settings['free_shipping_threshold'] ?? 5000) }}" placeholder="e.g. 5000">
                                <small class="text-muted">Orders with subtotal reaching or exceeding this amount receive ৳ 0 shipping</small>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card p-3 p-md-4">
                    <h5 class="fw-bold mb-3 border-bottom pb-2">
                        <i class="fa-solid fa-bullhorn text-primary me-1"></i> Top Announcement Banner Bar
                    </h5>
                    <div class="mb-3">
                        <label class="form-label">Top Notice Text</label>
                        <textarea name="announcement_text" class="form-control" rows="2" placeholder="Cash on delivery available across all 64 districts!">{{ $settings['announcement_text'] ?? 'Cash on delivery available across all 64 districts with superfast express delivery!' }}</textarea>
                    </div>
                    <div>
                        <button type="submit" id="saveSettingsBtn" class="btn btn-primary w-100 py-2.5 fw-bold">
                            <i class="fa-solid fa-floppy-disk me-1"></i> Save Store Settings
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="card p-3 p-md-4" id="promoCard3Settings">
                <div class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2">
                    <h5 class="fw-bold mb-0">
                        <i class="fa-solid fa-gift text-primary me-1"></i> Hero Right Promo Card (Delivery / Combo Booster)
                    </h5>
                    <div class="form-check form-switch mb-0">
                        <input class="form-check-input" type="checkbox" name="promo_card3_status" id="promoCard3StatusSwitch" value="1" {{ (!isset($settings['promo_card3_status']) || $settings['promo_card3_status'] == '1') ? 'checked' : '' }}>
                        <label class="form-check-label fw-bold small" for="promoCard3StatusSwitch">Display on Storefront</label>
                    </div>
                </div>
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Badge Text</label>
                        <input type="text" name="promo_card3_badge" class="form-control" value="{{ $settings['promo_card3_badge'] ?? '🔥 স্পেশাল অফার' }}" placeholder="e.g. 🔥 স্পেশাল অফার">
                    </div>
                    <div class="col-md-8">
                        <label class="form-label">Headline / Title <span class="text-danger">*</span></label>
                        <input type="text" name="promo_card3_title" class="form-control" value="{{ $settings['promo_card3_title'] ?? '২টি প্রোডাক্ট নিলেই সারা দেশে ডেলিভারি ফ্রি!' }}" placeholder="e.g. ২টি প্রোডাক্ট নিলেই সারা দেশে ডেলিভারি ফ্রি!">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Sub-headline / Highlight Text</label>
                        <input type="text" name="promo_card3_subtitle" class="form-control" value="{{ $settings['promo_card3_subtitle'] ?? 'ডেলিভারি চার্জ একদম ০ টাকা | যেকোনো গ্যাজেট কার্টে যোগ করুন।' }}" placeholder="e.g. ডেলিভারি চার্জ একদম ০ টাকা...">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Button Text</label>
                        <input type="text" name="promo_card3_btn_text" class="form-control" value="{{ $settings['promo_card3_btn_text'] ?? 'কম্বো অফার দেখুন' }}" placeholder="e.g. কম্বো অফার দেখুন">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Button Target URL</label>
                        <input type="text" name="promo_card3_btn_url" class="form-control" value="{{ $settings['promo_card3_btn_url'] ?? '/products' }}" placeholder="e.g. /products">
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
    function previewSiteLogo(input) {
        const preview = document.getElementById('siteLogoPreview');
        const placeholder = document.getElementById('siteLogoPlaceholder');
        const removeBtn = document.getElementById('removeLogoBtn');
        const removeInput = document.getElementById('removeSiteLogoInput');
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                if (preview) {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                }
                if (placeholder) placeholder.style.display = 'none';
                if (removeBtn) removeBtn.style.display = 'inline-block';
                if (removeInput) removeInput.value = '0';
            };
            reader.readAsDataURL(input.files[0]);
        }
    }

    function handleRemoveSiteLogo() {
        const preview = document.getElementById('siteLogoPreview');
        const placeholder = document.getElementById('siteLogoPlaceholder');
        const input = document.getElementById('siteLogoInput');
        const removeBtn = document.getElementById('removeLogoBtn');
        const removeInput = document.getElementById('removeSiteLogoInput');
        if (input) input.value = '';
        if (preview) {
            preview.src = '';
            preview.style.display = 'none';
        }
        if (placeholder) placeholder.style.display = 'block';
        if (removeBtn) removeBtn.style.display = 'none';
        if (removeInput) removeInput.value = '1';
    }

    function handleSettingsSubmit(e) {
        e.preventDefault();
        const form = document.getElementById('settingsForm');
        const formData = new FormData(form);
        const submitBtn = document.getElementById('saveSettingsBtn');
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-1"></i> Saving...';
        }

        axios.post('{{ route('admin.settings.ajax_update') }}', formData, {
            headers: { 'Accept': 'application/json' }
        })
        .then(res => {
            const data = res.data;
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="fa-solid fa-floppy-disk me-1"></i> Save Store Settings';
            }

            if (data.success) {
                if (data.site_logo) {
                    const preview = document.getElementById('siteLogoPreview');
                    const placeholder = document.getElementById('siteLogoPlaceholder');
                    const removeBtn = document.getElementById('removeLogoBtn');
                    if (preview) {
                        preview.src = data.site_logo;
                        preview.style.display = 'block';
                    }
                    if (placeholder) placeholder.style.display = 'none';
                    if (removeBtn) removeBtn.style.display = 'inline-block';
                }
                Swal.fire({
                    icon: 'success',
                    title: 'Saved!',
                    text: data.message,
                    timer: 1500,
                    showConfirmButton: false
                });
            } else {
                Swal.fire('Error', data.message || 'Validation error', 'error');
            }
        })
        .catch(err => {
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="fa-solid fa-floppy-disk me-1"></i> Save Store Settings';
            }
            Swal.fire('Error', 'An unexpected error occurred', 'error');
        });
    }

    window.previewSiteLogo = previewSiteLogo;
    window.handleRemoveSiteLogo = handleRemoveSiteLogo;
    window.handleSettingsSubmit = handleSettingsSubmit;
})();
</script>
@endpush
