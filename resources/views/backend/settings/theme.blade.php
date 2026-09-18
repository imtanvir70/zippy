@extends('backend.layouts.app')

@section('title', 'Theme & Appearance Customization')

@section('content')
<div class="card p-3 p-md-4 mb-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
        <div class="d-flex align-items-center gap-3">
            <div class="stat-icon" style="width: 48px; height: 48px; border-radius: 14px;">
                <i class="fa-solid fa-palette"></i>
            </div>
            <div>
                <h3 class="fw-bold mb-1">Theme & Appearance</h3>
                <small class="text-muted">Customize storefront brand typography, dynamic typing words, and primary accent palette.</small>
            </div>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('home') }}" target="_blank" class="btn btn-outline-secondary btn-sm px-3 py-2 fw-semibold rounded-3">
                <i class="fa-solid fa-arrow-up-right-from-square me-1.5"></i> Live Storefront
            </a>
        </div>
    </div>
</div>

<form action="{{ route('admin.theme_settings.update') }}" method="POST">
    @csrf
    <div class="row g-4">
        <div class="col-lg-7">
            <div class="card p-3 p-md-4 h-100">
                <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                    <h6 class="fw-bold mb-0 text-primary">
                        <i class="fa-solid fa-sliders me-2"></i> Design & Palette Controls
                    </h6>
                    <span class="badge bg-primary-subtle text-primary rounded-pill px-2.5 py-1 small">Instant Cache Clear</span>
                </div>

                <div class="d-flex flex-column gap-3">
                    <div>
                        <label class="form-label small fw-bold mb-1">
                            Hero Dynamic Typing Words
                        </label>
                        <textarea name="hero_typing_words" id="heroTypingWordsInput" class="form-control" rows="4" placeholder="Zippy BD&#10;শপিং মানেই">{{ $wordsText }}</textarea>
                        <small class="text-muted d-block mt-1">Enter each phrase on a new line or separated by commas. These will cycle in the header brand typing animation.</small>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold mb-1">
                                Primary Theme Color
                            </label>
                            <div class="input-group">
                                <input type="color" class="form-control form-control-color" id="primaryColorPicker" value="{{ $theme->primary_color ?? '#0f172a' }}" oninput="document.getElementById('primaryColorText').value = this.value; updatePreview();">
                                <input type="text" name="primary_color" id="primaryColorText" class="form-control font-monospace" value="{{ $theme->primary_color ?? '#0f172a' }}" oninput="document.getElementById('primaryColorPicker').value = this.value; updatePreview();">
                            </div>
                            <small class="text-muted d-block mt-1">Navbar badges, dark surfaces, and core typography.</small>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-bold mb-1">
                                Accent Theme Color
                            </label>
                            <div class="input-group">
                                <input type="color" class="form-control form-control-color" id="accentColorPicker" value="{{ $theme->accent_color ?? '#ff385c' }}" oninput="document.getElementById('accentColorText').value = this.value; updatePreview();">
                                <input type="text" name="accent_color" id="accentColorText" class="form-control font-monospace" value="{{ $theme->accent_color ?? '#ff385c' }}" oninput="document.getElementById('accentColorPicker').value = this.value; updatePreview();">
                            </div>
                            <small class="text-muted d-block mt-1">Highlights, discounts, interactive call-to-actions.</small>
                        </div>
                    </div>

                    <div>
                        <label class="form-label small fw-bold mb-1">
                            Storefront Font Family
                        </label>
                        <select name="font_family" id="fontFamilySelect" class="form-select" onchange="updatePreview();">
                            <option value="Outfit" {{ ($theme->font_family ?? '') === 'Outfit' ? 'selected' : '' }}>Outfit (Modern Sans-Serif - Recommended)</option>
                            <option value="Inter" {{ ($theme->font_family ?? '') === 'Inter' ? 'selected' : '' }}>Inter (Clean Neo-Grotesque)</option>
                            <option value="Plus Jakarta Sans" {{ ($theme->font_family ?? '') === 'Plus Jakarta Sans' ? 'selected' : '' }}>Plus Jakarta Sans (Crisp Geometric)</option>
                            <option value="Poppins" {{ ($theme->font_family ?? '') === 'Poppins' ? 'selected' : '' }}>Poppins (Friendly Geometric)</option>
                            <option value="Hind Siliguri" {{ ($theme->font_family ?? '') === 'Hind Siliguri' ? 'selected' : '' }}>Hind Siliguri (Bangla Typography)</option>
                            <option value="Anek Bangla" {{ ($theme->font_family ?? '') === 'Anek Bangla' ? 'selected' : '' }}>Anek Bangla (Modern Stylistic Bengali)</option>
                        </select>
                        <small class="text-muted d-block mt-1">Select the global font family applied to headings and storefront body copy.</small>
                    </div>

                    <div class="pt-3 border-top">
                        <h6 class="fw-bold mb-3 text-dark">
                            <i class="fa-solid fa-chart-line me-2"></i> Conversion Boosters & Social Proof
                        </h6>

                        <div class="p-3 bg-light rounded-3 border mb-3">
                            <div class="form-check form-switch d-flex align-items-center justify-content-between ps-0 mb-2">
                                <label class="form-check-label fw-bold text-dark" for="recentSalesToastSwitch">
                                    Show Recent Sales Toast (Bottom-Left)
                                </label>
                                <input class="form-check-input ms-auto" type="checkbox" role="switch" id="recentSalesToastSwitch" name="recent_sales_toast_enabled" value="1" {{ !isset($theme->recent_sales_toast_enabled) || $theme->recent_sales_toast_enabled ? 'checked' : '' }}>
                            </div>
                            <small class="text-muted d-block">Subtle, non-intrusive bottom-left social proof toast displaying recent customer purchases.</small>
                        </div>

                        <div class="p-3 bg-light rounded-3 border">
                            <div class="form-check form-switch d-flex align-items-center justify-content-between ps-0 mb-2">
                                <label class="form-check-label fw-bold text-dark" for="liveViewersSwitch">
                                    Show Live Viewing Counter on Product Page
                                </label>
                                <input class="form-check-input ms-auto" type="checkbox" role="switch" id="liveViewersSwitch" name="live_viewers_enabled" value="1" {{ !isset($theme->live_viewers_enabled) || $theme->live_viewers_enabled ? 'checked' : '' }}>
                            </div>
                            <small class="text-muted d-block">Shows realistic live viewing indicator ("৮ জন এই প্রোডাক্টটি এখন দেখছেন") on single product pages.</small>
                        </div>
                    </div>

                    <div class="pt-3 border-top d-flex align-items-center gap-2">
                        <button type="submit" class="btn btn-primary px-4 fw-bold">
                            <i class="fa-solid fa-floppy-disk me-1"></i> Save Configurations
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card p-3 p-md-4 h-100">
                <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                    <h6 class="fw-bold mb-0">
                        <i class="fa-solid fa-eye me-2"></i> Live Visual Preview
                    </h6>
                    <span class="badge bg-secondary-subtle text-secondary rounded-pill px-2.5 py-1 small">Real-Time Simulation</span>
                </div>

                <div class="p-3 rounded-4 border" id="previewContainer" style="background: #f8fafc; transition: all 0.2s ease;">
                    <div class="d-flex align-items-center justify-content-between pb-3 border-bottom mb-3">
                        <div class="d-flex align-items-center gap-2">
                            <span class="d-inline-flex align-items-center justify-content-center text-white rounded-circle fw-bold" id="previewLogoInitial" style="width: 32px; height: 32px; background: {{ $theme->primary_color ?? '#0f172a' }}; font-size: 14px;">Z</span>
                            <span class="fw-bolder fs-5" id="previewBrandText" style="color: {{ $theme->primary_color ?? '#0f172a' }};">Zippy BD</span>
                        </div>
                        <span class="badge text-white px-2.5 py-1 rounded-pill" id="previewBadge" style="background: {{ $theme->accent_color ?? '#ff385c' }}; font-size: 11px;">Save 25%</span>
                    </div>

                    <div class="p-3 bg-white rounded-3 border mb-3">
                        <div class="text-muted small mb-1">Sample Product Card</div>
                        <h6 class="fw-bold mb-2" id="previewHeading" style="color: {{ $theme->primary_color ?? '#0f172a' }};">Wireless RGB Gaming Keyboard</h6>
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <span class="fw-bold fs-5" id="previewPrice" style="color: {{ $theme->primary_color ?? '#0f172a' }};">৳ 3,450</span>
                                <span class="text-decoration-line-through text-muted small ms-1">৳ 4,600</span>
                            </div>
                            <button type="button" class="btn btn-sm text-white rounded-pill px-3 py-1 fw-semibold" id="previewButton" style="background: {{ $theme->primary_color ?? '#0f172a' }}; border: none;">
                                Buy Now
                            </button>
                        </div>
                    </div>

                    <div class="p-2.5 rounded-3 d-flex align-items-center gap-2 text-white" id="previewBanner" style="background: {{ $theme->accent_color ?? '#ff385c' }}; font-size: 12px;padding:10px;">
                        <i class="fa-solid fa-bolt"></i>
                        <span>Flash Deal: Free express delivery across all 64 districts!</span>
                    </div>
                </div>

                <div class="mt-4 p-3 rounded-3 bg-light border">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <i class="fa-solid fa-gauge-high text-success"></i>
                        <span class="fw-bold small">Zero Query Cache Invalidation</span>
                    </div>
                    <p class="text-muted mb-0" style="font-size: 12px; line-height: 1.5;">
                        Storefront theme settings are permanently memoized with <code>Cache::rememberForever</code>. When you save your adjustments here, the cache is instantly cleared and regenerated without any database queries on front page visits.
                    </p>
                </div>
            </div>
        </div>
    </div>
</form>

<script>
(() => {
    function updatePreview() {
        const primary = document.getElementById('primaryColorText').value || '#0f172a';
        const accent = document.getElementById('accentColorText').value || '#ff385c';
        const font = document.getElementById('fontFamilySelect').value || 'Outfit';

        const previewContainer = document.getElementById('previewContainer');
        if (previewContainer) {
            previewContainer.style.fontFamily = font + ', system-ui, sans-serif';
        }

        const brandText = document.getElementById('previewBrandText');
        if (brandText) brandText.style.color = primary;

        const logoInitial = document.getElementById('previewLogoInitial');
        if (logoInitial) logoInitial.style.background = primary;

        const heading = document.getElementById('previewHeading');
        if (heading) heading.style.color = primary;

        const price = document.getElementById('previewPrice');
        if (price) price.style.color = primary;

        const button = document.getElementById('previewButton');
        if (button) button.style.background = primary;

        const badge = document.getElementById('previewBadge');
        if (badge) badge.style.background = accent;

        const banner = document.getElementById('previewBanner');
        if (banner) banner.style.background = accent;
    }

    window.updatePreview = updatePreview;
})();
</script>
@endsection

