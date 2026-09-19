@extends('backend.layouts.app')

@section('title', 'Theme & Appearance Customization')

@push('styles')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Anek+Bangla:wght@400;500;600;700;800&family=Hind+Siliguri:wght@400;500;600;700&family=Inter:wght@400;500;600;700;800&family=Manrope:wght@400;500;600;700;800&family=Mina:wght@400;700&family=Montserrat:wght@400;500;600;700;800&family=Noto+Sans+Bengali:wght@400;500;600;700;800&family=Outfit:wght@400;500;600;700;800;900&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Poppins:wght@400;500;600;700;800&family=Tiro+Bangla:ital@0;1&family=Urbanist:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
    .preview-container-isolated {
        isolation: isolate;
        font-size: 13px;
        line-height: 1.5;
        border-radius: 16px;
        transition: all 0.25s ease;
    }
    .preview-container-isolated.preview-theme-light {
        background: #ffffff !important;
        color: #1e293b !important;
        border: 1px solid #e2e8f0 !important;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
    }
    .preview-container-isolated.preview-theme-light .preview-inner-card {
        background: #f8fafc !important;
        border: 1px solid #e2e8f0 !important;
        border-radius: 12px;
        padding: 14px;
        margin-bottom: 12px;
    }
    .preview-container-isolated.preview-theme-light .preview-box {
        background: #ffffff !important;
        border: 1px solid #e2e8f0 !important;
        border-radius: 8px;
        padding: 10px 12px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.03);
    }
    .preview-container-isolated.preview-theme-light .preview-text-main {
        color: #0f172a !important;
    }
    .preview-container-isolated.preview-theme-light .preview-text-muted {
        color: #64748b !important;
    }
    .preview-container-isolated.preview-theme-light .preview-text-sub {
        color: #475569 !important;
    }
    .preview-container-isolated.preview-theme-light .preview-live-box {
        background: #fff1f2 !important;
        border: 1px solid #fecdd3 !important;
        color: #9f1239 !important;
    }
    .preview-container-isolated.preview-theme-light .preview-toast-card {
        background: #ffffff !important;
        border: 1px solid #e2e8f0 !important;
        color: #1e293b !important;
        box-shadow: 0 4px 14px rgba(0, 0, 0, 0.06) !important;
    }
    .preview-container-isolated.preview-theme-light .preview-sec-btn {
        background: #ffffff !important;
        color: #1e293b !important;
        border: 1px solid #cbd5e1 !important;
    }

    .preview-container-isolated.preview-theme-dark {
        background: #0f172a !important;
        color: #f1f5f9 !important;
        border: 1px solid #334155 !important;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.25);
    }
    .preview-container-isolated.preview-theme-dark .preview-inner-card {
        background: #1e293b !important;
        border: 1px solid #334155 !important;
        border-radius: 12px;
        padding: 14px;
        margin-bottom: 12px;
    }
    .preview-container-isolated.preview-theme-dark .preview-box {
        background: #0f172a !important;
        border: 1px solid #334155 !important;
        border-radius: 8px;
        padding: 10px 12px;
    }
    .preview-container-isolated.preview-theme-dark .preview-text-main {
        color: #f8fafc !important;
    }
    .preview-container-isolated.preview-theme-dark .preview-text-muted {
        color: #94a3b8 !important;
    }
    .preview-container-isolated.preview-theme-dark .preview-text-sub {
        color: #cbd5e1 !important;
    }
    .preview-container-isolated.preview-theme-dark .preview-live-box {
        background: rgba(225, 29, 72, 0.15) !important;
        border: 1px solid rgba(225, 29, 72, 0.3) !important;
        color: #fda4af !important;
    }
    .preview-container-isolated.preview-theme-dark .preview-toast-card {
        background: #1e293b !important;
        border: 1px solid #334155 !important;
        color: #f8fafc !important;
        box-shadow: 0 4px 14px rgba(0, 0, 0, 0.3) !important;
    }
    .preview-container-isolated.preview-theme-dark .preview-sec-btn {
        background: #1e293b !important;
        color: #f8fafc !important;
        border: 1px solid #475569 !important;
    }

    .preview-digits-strip {
        letter-spacing: 2.5px;
        font-weight: 700;
        font-size: 13px;
    }
</style>
@endpush

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
                            <optgroup label="Bengali Native Fonts (বাংলা টাইপোগ্রাফি)">
                                <option value="Hind Siliguri" {{ ($theme->font_family ?? '') === 'Hind Siliguri' ? 'selected' : '' }}>Hind Siliguri (হিন্দ শিলিগুড়ি - সর্বাধিক জনপ্রিয় ও সুস্পষ্ট)</option>
                                <option value="Anek Bangla" {{ ($theme->font_family ?? '') === 'Anek Bangla' ? 'selected' : '' }}>Anek Bangla (অনেক বাংলা - আধুনিক ভেরিয়েবল)</option>
                                <option value="Noto Sans Bengali" {{ ($theme->font_family ?? '') === 'Noto Sans Bengali' ? 'selected' : '' }}>Noto Sans Bengali (নোটো সান্স বাংলা - গুগল স্ট্যান্ডার্ড)</option>
                                <option value="Mina" {{ ($theme->font_family ?? '') === 'Mina' ? 'selected' : '' }}>Mina (মীনা - ক্লিন ও জ্যামিতিক)</option>
                                <option value="Tiro Bangla" {{ ($theme->font_family ?? '') === 'Tiro Bangla' ? 'selected' : '' }}>Tiro Bangla (তিরো বাংলা - ক্লাসিক ফরমাল সেরিফ)</option>
                            </optgroup>
                            <optgroup label="Modern English & Global Fonts (আন্তর্জাতিক ও রিটেইল)">
                                <option value="Outfit" {{ ($theme->font_family ?? '') === 'Outfit' ? 'selected' : '' }}>Outfit (Modern Sans - Recommended)</option>
                                <option value="Plus Jakarta Sans" {{ ($theme->font_family ?? '') === 'Plus Jakarta Sans' ? 'selected' : '' }}>Plus Jakarta Sans (Crisp Modern Geometric)</option>
                                <option value="Inter" {{ ($theme->font_family ?? '') === 'Inter' ? 'selected' : '' }}>Inter (Clean Universal UI)</option>
                                <option value="Poppins" {{ ($theme->font_family ?? '') === 'Poppins' ? 'selected' : '' }}>Poppins (Friendly Rounded Geometric)</option>
                                <option value="Montserrat" {{ ($theme->font_family ?? '') === 'Montserrat' ? 'selected' : '' }}>Montserrat (Bold & Premium Retail)</option>
                                <option value="Manrope" {{ ($theme->font_family ?? '') === 'Manrope' ? 'selected' : '' }}>Manrope (Modern Grotesque Legibility)</option>
                                <option value="Urbanist" {{ ($theme->font_family ?? '') === 'Urbanist' ? 'selected' : '' }}>Urbanist (Ultra Modern Digital)</option>
                            </optgroup>
                        </select>
                        <small class="text-muted d-block mt-1">Select the global font family applied to headings and storefront body copy across English and Bengali.</small>
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
                            <small class="text-muted d-block mb-2">Subtle, non-intrusive bottom-left social proof toast displaying recent customer purchases.</small>
                            <div class="row g-2 align-items-center mt-1">
                                <div class="col-sm-6">
                                    <label class="form-label small mb-0 text-muted">Toast Interval (Seconds)</label>
                                </div>
                                <div class="col-sm-6">
                                    <div class="input-group input-group-sm">
                                        <input type="number" name="recent_sales_interval" class="form-control" min="5" max="120" value="{{ $theme->recent_sales_interval ?? 45 }}">
                                        <span class="input-group-text">sec</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="p-3 bg-light rounded-3 border">
                            <div class="form-check form-switch d-flex align-items-center justify-content-between ps-0 mb-2">
                                <label class="form-check-label fw-bold text-dark" for="liveViewersSwitch">
                                    Show Live Viewing Counter on Product Page
                                </label>
                                <input class="form-check-input ms-auto" type="checkbox" role="switch" id="liveViewersSwitch" name="live_viewers_enabled" value="1" {{ !isset($theme->live_viewers_enabled) || $theme->live_viewers_enabled ? 'checked' : '' }}>
                            </div>
                            <small class="text-muted d-block mb-2">Shows realistic live viewing indicator ("৮ জন এই প্রোডাক্টটি এখন দেখছেন") on single product pages.</small>
                            <div class="row g-2 align-items-center mt-1">
                                <div class="col-6">
                                    <label class="form-label small mb-1 text-muted">Min Viewers</label>
                                    <input type="number" name="live_viewers_min" class="form-control form-control-sm" min="1" max="100" value="{{ $theme->live_viewers_min ?? 8 }}">
                                </div>
                                <div class="col-6">
                                    <label class="form-label small mb-1 text-muted">Max Viewers</label>
                                    <input type="number" name="live_viewers_max" class="form-control form-control-sm" min="1" max="100" value="{{ $theme->live_viewers_max ?? 22 }}">
                                </div>
                            </div>
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
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3 pb-2 border-bottom">
                    <div class="d-flex align-items-center gap-2">
                        <h6 class="fw-bold mb-0">
                            <i class="fa-solid fa-eye me-1.5"></i> Live Visual Preview
                        </h6>
                        <span class="badge bg-primary-subtle text-primary rounded-pill px-2.5 py-1 small fw-bold" id="activeFontBadge">{{ $theme->font_family ?? 'Outfit' }}</span>
                    </div>

                    <div class="btn-group btn-group-sm" role="group">
                        <button type="button" class="btn btn-sm btn-outline-secondary active" id="btnPreviewLight" onclick="setPreviewMode('light')">
                            <i class="fa-solid fa-sun me-1"></i> Light
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-secondary" id="btnPreviewDark" onclick="setPreviewMode('dark')">
                            <i class="fa-solid fa-moon me-1"></i> Dark
                        </button>
                    </div>
                </div>

                <div class="p-3 preview-container-isolated preview-theme-light" id="previewContainer" data-bs-theme="light">
                    <div class="d-flex align-items-center justify-content-between pb-3 border-bottom mb-3" style="border-color: inherit;">
                        <div class="d-flex align-items-center gap-2">
                            <span class="d-inline-flex align-items-center justify-content-center text-white rounded-circle fw-bold" id="previewLogoInitial" style="width: 34px; height: 34px; background: {{ $theme->primary_color ?? '#0f172a' }}; font-size: 15px;">Z</span>
                            <div>
                                <span class="fw-bolder fs-5 d-block lh-1 preview-text-main" id="previewBrandText" style="color: {{ $theme->primary_color ?? '#0f172a' }};">Zippy BD</span>
                                <span class="preview-text-muted" style="font-size: 11px;">জিপ্পি বিডি অনলাইন শপ</span>
                            </div>
                        </div>
                        <span class="badge text-white px-2.5 py-1 rounded-pill" id="previewBadge" style="background: {{ $theme->accent_color ?? '#ff385c' }}; font-size: 11px;">Save 25% ছাড়</span>
                    </div>

                    <div class="rounded-3 d-flex align-items-center gap-2 text-white mb-3" id="previewBanner" style="background: {{ $theme->accent_color ?? '#ff385c' }}; font-size: 11.5px; padding: 10px 14px; font-weight: 500;">
                        <i class="fa-solid fa-bolt flex-shrink-0"></i>
                        <span>ফ্ল্যাশ অফার: আজ রাত ১২টা পর্যন্ত ফ্রি হোম ডেলিভারি! কোড: <strong>ZIPPY2026</strong></span>
                    </div>

                    <div class="preview-inner-card">
                        <div class="d-flex justify-content-between align-items-center mb-1.5">
                            <span class="preview-text-muted small">Gadgets & Accessories • গ্যাজেটস</span>
                            <span class="text-warning small" style="font-size: 11px;">
                                <i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star-half-stroke"></i>
                                <strong class="preview-text-main ms-1">4.9/5.0</strong>
                            </span>
                        </div>

                        <h6 class="fw-bold mb-1 preview-heading-color" id="previewHeadingEn" style="color: {{ $theme->primary_color ?? '#0f172a' }}; font-size: 15px;">T900 Ultra 2 Bluetooth Calling Smartwatch</h6>
                        <h6 class="fw-bold mb-2 preview-heading-color" id="previewHeadingBn" style="color: {{ $theme->primary_color ?? '#0f172a' }}; font-size: 14.5px;">টি৯০০ আল্ট্রা ২ ব্লুটুথ কলিং স্মার্টওয়াচ</h6>

                        <p class="mb-1.5 preview-text-sub" style="font-size: 12px; line-height: 1.45;">AMOLED curved display with 48h standby, SpO2 sensor & wireless fast charging.</p>
                        <p class="mb-3 preview-text-sub" style="font-size: 12px; line-height: 1.45;">ফুল টাচ কালার ডিসপ্লে, হার্ট রেট মনিটর এবং দীর্ঘস্থায়ী ব্যাটারি ব্যাকআপ সহ ১০০% অরিজিনাল গ্যাজেট।</p>

                        <div class="row g-2 mb-3">
                            <div class="col-12">
                                <div class="preview-box">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <span class="small fw-bold text-uppercase preview-text-muted" style="font-size: 10.5px; letter-spacing: 0.5px;">English Numbers & Pricing</span>
                                        <span class="badge bg-danger-subtle text-danger" style="font-size: 10px;">-29% OFF</span>
                                    </div>
                                    <div class="d-flex align-items-baseline gap-2 mb-1">
                                        <span class="fw-bold fs-5 preview-price-color" id="previewPriceEn" style="color: {{ $theme->primary_color ?? '#0f172a' }};">৳ 2,490</span>
                                        <span class="text-decoration-line-through small preview-text-muted">৳ 3,500</span>
                                    </div>
                                    <div class="preview-digits-strip mb-1" id="previewDigitsEn" style="color: {{ $theme->accent_color ?? '#ff385c' }};">0 1 2 3 4 5 6 7 8 9</div>
                                    <div class="preview-text-muted" style="font-size: 11px;">Order #ZP-84920 • Hotline: 01712-345678</div>
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="preview-box">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <span class="small fw-bold text-uppercase preview-text-muted" style="font-size: 10.5px; letter-spacing: 0.5px;">বাংলা সংখ্যা ও মূল্য তালিকা</span>
                                        <span class="badge bg-danger-subtle text-danger" style="font-size: 10px;">-২৯% ছাড়</span>
                                    </div>
                                    <div class="d-flex align-items-baseline gap-2 mb-1">
                                        <span class="fw-bold fs-5 preview-price-color" id="previewPriceBn" style="color: {{ $theme->primary_color ?? '#0f172a' }};">৳ ২,৪৯০</span>
                                        <span class="text-decoration-line-through small preview-text-muted">৳ ৩,৫০০</span>
                                    </div>
                                    <div class="preview-digits-strip mb-1" id="previewDigitsBn" style="color: {{ $theme->accent_color ?? '#ff385c' }};">০ ১ ২ ৩ ৪ ৫ ৬ ৭ ৮ ৯</div>
                                    <div class="preview-text-muted" style="font-size: 11px;">অর্ডার কোড: #৮৯৪২০ • হেল্পলাইন: ০১৭১২-৩৪৫৬৭৮</div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex gap-2 mb-1">
                            <button type="button" class="btn btn-sm text-white rounded-pill px-3 py-2 fw-semibold flex-fill preview-btn-primary" id="previewButtonPrimary" style="background: {{ $theme->primary_color ?? '#0f172a' }}; border: none;">
                                <i class="fa-solid fa-bolt me-1"></i> অর্ডার করুন (Order Now)
                            </button>
                            <button type="button" class="btn btn-sm rounded-pill px-3 py-2 fw-semibold preview-sec-btn" id="previewButtonSecondary">
                                <i class="fa-solid fa-cart-shopping me-1"></i> কার্ট (Cart)
                            </button>
                        </div>
                    </div>

                    <div class="p-2 rounded-3 d-flex align-items-center gap-2 small mb-2 preview-live-box" id="previewLiveViewers" style="font-size: 11.5px;">
                        <span class="spinner-grow spinner-grow-sm text-danger" style="width: 7px; height: 7px;"></span>
                        <span><strong>১২ জন ক্রেতা</strong> এই মুহূর্তে দেখছেন • <strong>12 shoppers</strong> active</span>
                    </div>

                    <div class="p-2.5 rounded-3 d-flex align-items-center gap-2 small preview-toast-card" id="previewSalesToast" style="font-size: 11.5px;">
                        <div class="preview-accent-bg rounded-circle text-white d-flex align-items-center justify-content-center flex-shrink-0" style="width: 26px; height: 26px; background: {{ $theme->accent_color ?? '#ff385c' }}; font-size: 11px;">
                            <i class="fa-solid fa-bag-shopping"></i>
                        </div>
                        <div style="line-height: 1.35;">
                            <strong class="preview-text-main">তানভীর (মিরপুর, ঢাকা)</strong> ৩ মিনিট আগে অর্ডার করেছেন (Order #4921)
                        </div>
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
            previewContainer.style.fontFamily = `'${font}', 'Hind Siliguri', 'Noto Sans Bengali', 'Anek Bangla', 'Inter', system-ui, sans-serif`;
        }

        const activeFontBadge = document.getElementById('activeFontBadge');
        if (activeFontBadge) {
            activeFontBadge.textContent = font;
        }

        const isDark = previewContainer && previewContainer.classList.contains('preview-theme-dark');

        const brandText = document.getElementById('previewBrandText');
        if (brandText) brandText.style.color = isDark ? '#f8fafc' : primary;

        const logoInitial = document.getElementById('previewLogoInitial');
        if (logoInitial) logoInitial.style.background = primary;

        const headings = document.querySelectorAll('.preview-heading-color');
        headings.forEach(h => {
            h.style.color = isDark ? '#f8fafc' : primary;
        });

        const prices = document.querySelectorAll('.preview-price-color');
        prices.forEach(p => {
            p.style.color = isDark ? '#38bdf8' : primary;
        });

        const primaryBtns = document.querySelectorAll('.preview-btn-primary');
        primaryBtns.forEach(b => {
            b.style.background = primary;
            b.style.borderColor = primary;
        });

        const digits = document.querySelectorAll('.preview-digits-strip');
        digits.forEach(d => {
            d.style.color = accent;
        });

        const badge = document.getElementById('previewBadge');
        if (badge) badge.style.background = accent;

        const banner = document.getElementById('previewBanner');
        if (banner) banner.style.background = accent;

        const accentBgElements = document.querySelectorAll('.preview-accent-bg');
        accentBgElements.forEach(el => el.style.background = accent);
    }

    function setPreviewMode(mode) {
        const container = document.getElementById('previewContainer');
        const btnLight = document.getElementById('btnPreviewLight');
        const btnDark = document.getElementById('btnPreviewDark');
        if (!container) return;

        if (mode === 'dark') {
            container.classList.remove('preview-theme-light');
            container.classList.add('preview-theme-dark');
            container.setAttribute('data-bs-theme', 'dark');
            if (btnDark) btnDark.classList.add('active');
            if (btnLight) btnLight.classList.remove('active');
        } else {
            container.classList.remove('preview-theme-dark');
            container.classList.add('preview-theme-light');
            container.setAttribute('data-bs-theme', 'light');
            if (btnLight) btnLight.classList.add('active');
            if (btnDark) btnDark.classList.remove('active');
        }
        updatePreview();
    }

    window.updatePreview = updatePreview;
    window.setPreviewMode = setPreviewMode;
    document.addEventListener('DOMContentLoaded', updatePreview);
    document.addEventListener('turbo:load', updatePreview);
    updatePreview();
})();
</script>
@endsection
