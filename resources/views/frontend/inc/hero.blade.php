
<section class="container py-2 py-md-3">
    <div class="row g-2 g-md-3 align-items-stretch">
        <div class="col-12 col-lg-8 d-flex flex-column">
            <div class="splide overflow-hidden position-relative w-100" id="heroSlider">
                <div class="splide__track w-100 h-100">
                    <ul class="splide__list">
                        @forelse($heroSlides ?? [] as $slide)
                            @php
                                $slideImg = is_object($slide) ? ($slide->image_url ?? '') : (is_array($slide) ? ($slide['image_url'] ?? '') : (is_string($slide) ? $slide : ''));
                                if (!empty($slideImg)) {
                                    if (!str_starts_with($slideImg, 'http://') && !str_starts_with($slideImg, 'https://')) {
                                        $trimmed = ltrim($slideImg, '/');
                                        if (!file_exists(public_path($trimmed))) {
                                            $slideImg = asset('images/banner-placeholder.svg');
                                        } else {
                                            $slideImg = asset($trimmed);
                                        }
                                    }
                                } else {
                                    $slideImg = '';
                                }

                                $displayMode = is_object($slide) ? ($slide->display_mode ?? 'both') : (is_array($slide) ? ($slide['display_mode'] ?? 'both') : 'both');
                                $slideBadge = is_object($slide) ? ($slide->badge_text ?? '') : (is_array($slide) ? ($slide['badge_text'] ?? '') : '');
                                $slideTitle = is_object($slide) ? ($slide->title ?? '') : (is_array($slide) ? ($slide['title'] ?? '') : '');
                                $slideSub = is_object($slide) ? ($slide->subtitle ?? '') : (is_array($slide) ? ($slide['subtitle'] ?? '') : '');
                                $slideBtnText = is_object($slide) ? ($slide->btn_text ?? '') : (is_array($slide) ? ($slide['btn_text'] ?? '') : '');
                                $slideBtnLink = is_object($slide) ? ($slide->btn_link ?? '') : (is_array($slide) ? ($slide['btn_link'] ?? '') : '');
                                if (empty($slideBtnLink)) { $slideBtnLink = route('product.index'); }

                                $overlayEnabled = is_object($slide) ? !empty($slide->overlay_enabled) : (is_array($slide) ? !empty($slide['overlay_enabled']) : false);
                                $overlayColor = is_object($slide) ? ($slide->overlay_color ?? '#000000') : (is_array($slide) ? ($slide['overlay_color'] ?? '#000000') : '#000000');
                                $overlayOpacity = is_object($slide) ? ($slide->overlay_opacity ?? 40) : (is_array($slide) ? ($slide['overlay_opacity'] ?? 40) : 40);
                                $overlayOpacityDecimal = ((int)$overlayOpacity) / 100;

                                $textAlign = is_object($slide) ? ($slide->text_align ?? 'left') : (is_array($slide) ? ($slide['text_align'] ?? 'left') : 'left');
                                $textColor = is_object($slide) ? ($slide->text_color ?? '#ffffff') : (is_array($slide) ? ($slide['text_color'] ?? '#ffffff') : '#ffffff');

                                $alignClass = 'justify-content-start text-start';
                                if ($textAlign === 'center') {
                                    $alignClass = 'justify-content-center text-center';
                                } elseif ($textAlign === 'right') {
                                    $alignClass = 'justify-content-end text-end';
                                }
                            @endphp
                            <li class="splide__slide position-relative overflow-hidden" style="background: transparent;">
                                @if($displayMode !== 'text_only' && !empty($slideImg))
                                    <img src="{{ $slideImg }}" alt="{{ $slideTitle ?: 'Hero Banner' }}" class="hs-banner-img" width="1920" height="830" decoding="async" @if($loop->first) fetchpriority="high" @else loading="lazy" @endif onerror="this.onerror=null;this.src='{{ asset('images/banner-placeholder.svg') }}';">
                                @endif

                                @if($overlayEnabled)
                                    <div class="hs-overlay" style="background-color: {{ $overlayColor }}; opacity: {{ $overlayOpacityDecimal }};"></div>
                                @endif

                                @if($displayMode === 'image_only')
                                    <a href="{{ $slideBtnLink }}" class="position-absolute top-0 start-0 w-100 h-100 z-3" aria-label="{{ $slideTitle ?: 'Shop Now' }}"></a>
                                @else
                                    <div class="hs-slide-content position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center {{ $alignClass }}">
                                        <div class="hs-text-card" style="color: {{ $textColor }};">
                                            @if($slideBadge)
                                                <span class="hero-slide-badge">{{ $slideBadge }}</span>
                                            @endif
                                            @if($slideTitle)
                                                @if($loop->first)
                                                    <h1 class="hero-slide-title" style="color: {{ $textColor }};">{{ $slideTitle }}</h1>
                                                @else
                                                    <h2 class="hero-slide-title" style="color: {{ $textColor }};">{{ $slideTitle }}</h2>
                                                @endif
                                            @endif
                                            @if($slideSub)
                                                <p class="hero-slide-sub d-none d-sm-block">{{ $slideSub }}</p>
                                            @endif
                                            @if($slideBtnText)
                                                <div class="mt-2 mt-md-3">
                                                    <a href="{{ $slideBtnLink }}" class="hero-slide-btn">
                                                        {{ $slideBtnText }} <i class="fa-solid fa-arrow-right" style="font-size: 8.5px;"></i>
                                                    </a>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                @endif
                            </li>
                        @empty
                            <li class="splide__slide position-relative overflow-hidden" style="background: transparent;">
                                <img src="{{ file_exists(public_path('images/banners/hero-2.jpg')) ? asset('images/banners/hero-2.jpg') : (file_exists(public_path('images/banners/hero-1.webp')) ? asset('images/banners/hero-1.webp') : asset('images/banner-placeholder.svg')) }}" alt="Zippy Premium" class="hs-banner-img" width="1920" height="830" decoding="async" fetchpriority="high" onerror="this.onerror=null;this.src='{{ asset('images/banner-placeholder.svg') }}';">
                                <div class="hs-slide-content position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-start">
                                    <div class="hs-text-card">
                                        <span class="hero-slide-badge">হট ডিল</span>
                                        <h1 class="hero-slide-title">প্রিমিয়াম ওয়্যারলেস গ্যাজেট</h1>
                                        <p class="hero-slide-sub d-none d-sm-block">হাই-কোয়ালিটি অডিও ও সুপারফাস্ট ডেলিভারি সুবিধা</p>
                                        <div class="mt-2 mt-md-3">
                                            <a href="{{ route('product.index') }}" class="hero-slide-btn">
                                                এখনই কিনুন <i class="fa-solid fa-arrow-right" style="font-size: 8.5px;"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-4 d-none d-lg-flex flex-column">
            @php
                $fc = $featuredCoupon ?? \Illuminate\Support\Facades\DB::table('coupons')
                    ->where('is_active', 1)
                    ->where(function ($q) {
                        $q->whereNull('start_date')->orWhere('start_date', '<=', now());
                    })
                    ->where(function ($q) {
                        $q->whereNull('end_date')->orWhere('end_date', '>=', now());
                    })
                    ->orderBy('id', 'asc')
                    ->first();

                $cpCode = $fc ? $fc->code : 'ZIPPY100';
                $cpType = $fc ? $fc->type : 'fixed';
                $cpVal = $fc ? (float) $fc->value : 100;
                $cpDiscountText = ($cpType === 'percent') ? ($cpVal . '% ছাড়') : (number_format($cpVal, 0) . '৳ ছাড়');
                $cpMinOrder = $fc && $fc->min_order_amount > 0 ? ('মিনিমাম ' . number_format($fc->min_order_amount, 0) . '৳ অর্ডারে') : 'সকল পণ্যে প্রযোজ্য';

                $card3Status = ($settings['promo_card3_status'] ?? '1') != '0';
                $card3Badge = $settings['promo_card3_badge'] ?? 'স্পেশাল অফার';
                $card3Title = $settings['promo_card3_title'] ?? '২টি প্রোডাক্ট নিলেই সারা দেশে ডেলিভারি ফ্রি!';
                $card3Subtitle = $settings['promo_card3_subtitle'] ?? 'ডেলিভারি চার্জ একদম ০ টাকা | যেকোনো গ্যাজেট কার্টে যোগ করুন।';
                $card3BtnText = $settings['promo_card3_btn_text'] ?? 'কম্বো অফার দেখুন';
                $card3BtnUrl = !empty($settings['promo_card3_btn_url']) ? $settings['promo_card3_btn_url'] : route('product.index');
            @endphp

            @if(!$card3Status)
                <div class="hero-side-2cards h-100">
                    <div class="promo-card-zb promo-card-voucher-expanded">
                        <div class="promo-card-glow" style="background: radial-gradient(circle at 85% 20%, rgba(251,191,36,0.18) 0%, transparent 65%);"></div>
                        <div class="d-flex align-items-center justify-content-between">
                            <span class="p-badge-amber">
                                <i class="fa-solid fa-tag me-1"></i>স্পেশাল ভাউচার
                            </span>
                            <span class="p-pill-amber">{{ $cpDiscountText }} সেভ করুন</span>
                        </div>
                        <div class="p-middle-block">
                            <div class="d-flex align-items-center p-icon-row">
                                <div class="p-icon-box p-icon-amber">
                                    <i class="fa-solid fa-ticket"></i>
                                </div>
                                <div class="d-flex flex-column justify-content-center min-w-0" style="gap: 2px;">
                                    <div class="card-p-title text-truncate">
                                        কুপন কোডে <span style="color: #fbbf24;">নিশ্চিত ছাড়!</span>
                                    </div>
                                    <div class="p-subtitle text-truncate">
                                        {{ $cpMinOrder }} • ইনস্ট্যান্ট ক্যাশব্যাক
                                    </div>
                                </div>
                            </div>
                            <div class="p-coupon-action" onclick="copyCouponCode('{{ $cpCode }}', this)" title="কপি করতে ক্লিক করুন">
                                <div class="d-flex align-items-center gap-1.5 min-w-0">
                                    <span class="text-white-50 p-code-label">কুপন কোড:</span>
                                    <span class="font-monospace fw-bold text-warning p-code-value">{{ $cpCode }}</span>
                                </div>
                                <button type="button" class="btn btn-warning btn-sm py-0.5 px-2 rounded-pill fw-bold text-dark coupon-copy-btn d-inline-flex align-items-center gap-1">
                                    <i class="fa-regular fa-copy"></i><span>কপি করুন</span>
                                </button>
                            </div>
                        </div>
                        <div class="p-footer d-flex align-items-center justify-content-between pt-1.5">
                            <span class="text-white-50 p-footer-text"><i class="fa-solid fa-bolt me-1 text-warning"></i>সীমিত সময়ের অফার</span>
                            <a href="{{ route('product.index') }}" class="text-decoration-none text-warning fw-bold d-inline-flex align-items-center gap-1 p-footer-link">
                                <span>শপিং করুন</span> <i class="fa-solid fa-arrow-right" style="font-size: 8.5px;"></i>
                            </a>
                        </div>
                    </div>

                    <div class="promo-card-zb promo-card-trust-expanded">
                        <div class="promo-card-glow" style="background: radial-gradient(circle at 85% 20%, rgba(34,197,94,0.16) 0%, transparent 65%);"></div>
                        <div class="d-flex align-items-center justify-content-between">
                            <span class="p-badge-emerald">
                                <i class="fa-solid fa-shield-check me-1"></i>১০০% জেনুইন গ্যাজেট
                            </span>
                            <span class="p-badge-muted">
                                <i class="fa-solid fa-award text-success me-1"></i>অফিসিয়াল শপ
                            </span>
                        </div>
                        <div class="p-middle-block">
                            <div class="d-flex align-items-center p-icon-row">
                                <div class="p-icon-box p-icon-emerald">
                                    <i class="fa-solid fa-rotate-left"></i>
                                </div>
                                <div class="d-flex flex-column justify-content-center min-w-0" style="gap: 2px;">
                                    <div class="card-p-title text-truncate">
                                        ৭ দিনের <span style="color: #4ade80;">সহজ রিপ্লেসমেন্ট</span>
                                    </div>
                                    <div class="p-subtitle text-truncate">
                                        পণ্য চেক করে নেওয়ার সুবিধা ও গ্যারান্টি
                                    </div>
                                </div>
                            </div>
                            <div class="p-stats-bar">
                                <div class="p-stat-unit">
                                    <span class="fw-bold text-warning p-stat-num">৪.৯★</span>
                                    <span class="text-white-50 p-stat-txt">রেটিং</span>
                                </div>
                                <div class="p-stat-sep"></div>
                                <div class="p-stat-unit">
                                    <span class="fw-bold text-white p-stat-num">১০K+</span>
                                    <span class="text-white-50 p-stat-txt">ডেলিভারি</span>
                                </div>
                                <div class="p-stat-sep"></div>
                                <div class="p-stat-unit">
                                    <span class="fw-bold p-stat-num" style="color: #67e8f9;">২৪/৭</span>
                                    <span class="text-white-50 p-stat-txt">সাপোর্ট</span>
                                </div>
                            </div>
                        </div>
                        <div class="p-footer d-flex align-items-center justify-content-between pt-1.5">
                            <span class="text-white-50 p-footer-text"><i class="fa-solid fa-truck-fast me-1 text-success"></i>সারাদেশে হোম ডেলিভারি</span>
                            <a href="{{ route('order.track') }}" class="text-decoration-none text-success fw-bold d-inline-flex align-items-center gap-1 p-footer-link">
                                <span>পার্সেল ট্র্যাক</span> <i class="fa-solid fa-arrow-right" style="font-size: 8.5px;"></i>
                            </a>
                        </div>
                    </div>
                </div>
            @else
                <div class="hero-side-3cards h-100">
                    <div class="c3-top-row">
                        <div class="c3-top-card c3-card-voucher">
                            <div class="c3-glow-amber"></div>
                            <div class="d-flex align-items-center justify-content-between w-100 gap-1">
                                <span class="c3-badge-amber">
                                    <i class="fa-solid fa-tag me-0.5"></i>ভাউচার
                                </span>
                                <span class="c3-pill-amber">{{ $cpDiscountText }}</span>
                            </div>
                            <div class="c3-top-center">
                                <div class="c3-icon-box c3-icon-amber">
                                    <i class="fa-solid fa-ticket"></i>
                                </div>
                                <div class="c3-card-title text-truncate w-100">কুপন অফার</div>
                                <div class="c3-card-desc text-truncate w-100">{{ $cpMinOrder }}</div>
                            </div>
                            <div class="c3-copy-pill" onclick="copyCouponCode('{{ $cpCode }}', this)" title="কপি করুন">
                                <span class="c3-code-val">{{ $cpCode }}</span>
                                <button type="button" class="c3-btn-icon coupon-copy-btn">
                                    <i class="fa-regular fa-copy"></i>
                                </button>
                            </div>
                        </div>

                        <div class="c3-top-card c3-card-trust">
                            <div class="c3-glow-emerald"></div>
                            <div class="d-flex align-items-center justify-content-between w-100 gap-1">
                                <span class="c3-badge-emerald">
                                    <i class="fa-solid fa-shield-check me-0.5"></i>জেনুইন
                                </span>
                                <span class="c3-pill-muted">অফিসিয়াল</span>
                            </div>
                            <div class="c3-top-center">
                                <div class="c3-icon-box c3-icon-emerald">
                                    <i class="fa-solid fa-rotate-left"></i>
                                </div>
                                <div class="c3-card-title text-truncate w-100">৭ দিনের রিপ্লেসমেন্ট</div>
                                <div class="c3-card-desc text-truncate w-100">১০০% অথেনটিক পণ্য</div>
                            </div>
                            <div class="c3-trust-grid">
                                <div class="c3-trust-cell">
                                    <span class="text-warning fw-bold c3-t-num">৪.৯★</span>
                                    <span class="c3-trust-lbl">রেটিং</span>
                                </div>
                                <div class="c3-trust-cell">
                                    <span class="text-white fw-bold c3-t-num">১০K+</span>
                                    <span class="c3-trust-lbl">অর্ডার</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="c3-bottom-card">
                        <div class="c3-glow-blue"></div>
                        <div class="d-flex align-items-center justify-content-between">
                            <span class="c3-badge-rose">
                                <i class="fa-solid fa-fire me-1"></i>{{ $card3Badge }}
                            </span>
                            <span class="c3-badge-ship">
                                <i class="fa-solid fa-truck-fast me-1"></i>০৳ শিপিং
                            </span>
                        </div>
                        <div class="d-flex align-items-center gap-2.5 my-auto py-1">
                            <div class="c3-deal-icon flex-shrink-0">
                                <i class="fa-solid fa-gift"></i>
                            </div>
                            <div class="min-w-0 flex-grow-1">
                                <div class="c3-deal-title text-truncate">{{ $card3Title }}</div>
                                <div class="c3-deal-subtitle text-truncate">{{ $card3Subtitle }}</div>
                            </div>
                        </div>
                        <div class="pt-0.5">
                            <a href="{{ $card3BtnUrl }}" class="c3-deal-action">
                                <span>{{ $card3BtnText }}</span>
                                <i class="fa-solid fa-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</section>

<style>
div[id="heroSlider"] {
    width: 100% !important;
    max-width: 100% !important;
    position: relative !important;
    background-color: transparent !important;
    border: none !important;
    box-shadow: none !important;
    outline: none !important;
    aspect-ratio: 1920 / 830 !important;
    height: auto !important;
    min-height: unset !important;
    max-height: none !important;
    flex-grow: 0 !important;
    flex-shrink: 0 !important;
    overflow: hidden !important;
    -webkit-mask-image: none !important;
    mask-image: none !important;
    filter: none !important;
}

@supports not (aspect-ratio: 1920 / 830) {
    div[id="heroSlider"] {
        height: 0 !important;
        padding-bottom: calc(830 / 1920 * 100%) !important;
    }
}

div[id="heroSlider"] .splide__track {
    height: 100% !important;
    width: 100% !important;
    border-radius: inherit;
    border: none !important;
    box-shadow: none !important;
    background-color: transparent !important;
    overflow: hidden !important;
    margin: 0 !important;
    padding: 0 !important;
    -webkit-mask-image: none !important;
    mask-image: none !important;
    filter: none !important;
}

div[id="heroSlider"] .splide__list {
    height: 100% !important;
    width: 100% !important;
    margin: 0 !important;
    padding: 0 !important;
    border: none !important;
    box-shadow: none !important;
    background-color: transparent !important;
}

div[id="heroSlider"] .splide__slide {
    height: 100% !important;
    width: 100% !important;
    aspect-ratio: 1920 / 830 !important;
    position: relative !important;
    overflow: hidden !important;
    border-radius: inherit;
    border: none !important;
    box-shadow: none !important;
    background: transparent !important;
    background-color: transparent !important;
    margin: 0 !important;
    padding: 0 !important;
    -webkit-mask-image: none !important;
    mask-image: none !important;
    filter: none !important;
}

.hs-banner-img {
    position: absolute !important;
    inset: 0 !important;
    width: 100% !important;
    height: 100% !important;
    object-fit: fill !important;
    object-position: center center !important;
    z-index: 1;
    display: block !important;
    border-radius: inherit;
    border: none !important;
    box-shadow: none !important;
    filter: none !important;
    margin: 0 !important;
    padding: 0 !important;
}

.hs-overlay {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    z-index: 2;
    pointer-events: none;
    border-radius: inherit;
}

.hs-slide-content {
    z-index: 3;
    padding: 16px;
}

.hs-text-card {
    max-width: 440px;
    background: rgba(9, 13, 22, 0.55);
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
    border: 1px solid rgba(255, 255, 255, 0.12);
    border-radius: 16px;
    padding: 16px 20px;
    animation: zbSlideIn 0.55s cubic-bezier(.22,.68,0,1.2) both;
}

.hero-slide-badge {
    display: inline-block;
    background: rgba(255, 255, 255, 0.2);
    backdrop-filter: blur(8px);
    -webkit-backdrop-filter: blur(8px);
    color: #ffffff;
    border: 1px solid rgba(255, 255, 255, 0.28);
    border-radius: 999px;
    font-weight: 600;
    font-size: 10px;
    padding: 2.5px 10px;
    margin-bottom: 6px;
}

.hero-slide-title {
    font-weight: 800;
    letter-spacing: -0.02em;
    line-height: 1.25;
    text-shadow: 0 2px 8px rgba(0, 0, 0, 0.5);
    margin: 0 0 6px;
    font-size: 16px;
}

.hero-slide-sub {
    color: rgba(255, 255, 255, 0.85);
    font-size: 11px;
    line-height: 1.45;
    margin-bottom: 8px;
    text-shadow: 0 1px 4px rgba(0, 0, 0, 0.6);
}

.hero-slide-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: #ffffff;
    color: #0f172a;
    font-weight: 700;
    border-radius: 999px;
    text-decoration: none;
    box-shadow: 0 3px 10px rgba(0, 0, 0, 0.25);
    transition: all 0.2s ease;
    font-size: 11px;
    padding: 5px 14px;
}

.hero-slide-btn:hover {
    background: #f1f5f9;
    color: #0f172a;
    transform: translateY(-1px);
    box-shadow: 0 5px 14px rgba(0, 0, 0, 0.3);
}

div[id="heroSlider"] .splide__pagination {
    position: absolute !important;
    bottom: 12px !important;
    left: 50% !important;
    transform: translateX(-50%) !important;
    z-index: 10 !important;
    margin: 0 !important;
    padding: 0 !important;
    display: inline-flex !important;
    align-items: center !important;
    gap: 6px !important;
    list-style: none !important;
}

div[id="heroSlider"] .splide__pagination__page {
    width: 7px !important;
    height: 7px !important;
    border-radius: 999px !important;
    background: rgb(73, 159, 1) !important;
    border: none !important;
    margin: 0 2px !important;
    padding: 0 !important;
    outline: none !important;
    box-shadow: none !important;
    cursor: pointer !important;
    transition: width 0.3s ease, background-color 0.25s ease !important;
}

div[id="heroSlider"] .splide__pagination__page.is-active {
    width: 20px !important;
    background: #3800ff !important;
    box-shadow: none !important;
}

@media (max-width: 299.98px) {
    div[id="heroSlider"] { border-radius: 6px; }
    div[id="heroSlider"] .splide__pagination { bottom: 4px !important; gap: 2.5px !important; }
    div[id="heroSlider"] .splide__pagination__page { width: 4px !important; height: 4px !important; margin: 0 1px !important; }
    div[id="heroSlider"] .splide__pagination__page.is-active { width: 11px !important; }
    .hs-slide-content { padding: 6px 8px; }
    .hs-text-card { max-width: 180px; padding: 6px 8px; border-radius: 8px; }
    .hero-slide-badge { font-size: 8px; padding: 1.5px 6px; margin-bottom: 3px; }
    .hero-slide-title { font-size: 11px; margin-bottom: 3px; }
    .hero-slide-sub { font-size: 9px; margin-bottom: 4px; }
    .hero-slide-btn { font-size: 8.5px; padding: 3px 8px; }
}

@media (min-width: 300px) and (max-width: 349.98px) {
    div[id="heroSlider"] { border-radius: 8px; }
    div[id="heroSlider"] .splide__pagination { bottom: 5px !important; gap: 3px !important; }
    div[id="heroSlider"] .splide__pagination__page { width: 4.5px !important; height: 4.5px !important; margin: 0 1.5px !important; }
    div[id="heroSlider"] .splide__pagination__page.is-active { width: 13px !important; }
    .hs-slide-content { padding: 8px 10px; }
    .hs-text-card { max-width: 200px; padding: 7px 9px; border-radius: 9px; }
    .hero-slide-badge { font-size: 8.5px; padding: 2px 7px; margin-bottom: 3px; }
    .hero-slide-title { font-size: 12px; margin-bottom: 3px; }
    .hero-slide-sub { font-size: 9.5px; margin-bottom: 4px; }
    .hero-slide-btn { font-size: 9px; padding: 3.5px 9px; }
}

@media (min-width: 350px) and (max-width: 399.98px) {
    div[id="heroSlider"] { border-radius: 10px; }
    div[id="heroSlider"] .splide__pagination { bottom: 6px !important; gap: 3.5px !important; }
    div[id="heroSlider"] .splide__pagination__page { width: 5px !important; height: 5px !important; margin: 0 1.5px !important; }
    div[id="heroSlider"] .splide__pagination__page.is-active { width: 14px !important; }
    .hs-slide-content { padding: 10px 12px; }
    .hs-text-card { max-width: 220px; padding: 8px 10px; border-radius: 10px; }
    .hero-slide-badge { font-size: 9px; padding: 2px 8px; margin-bottom: 4px; }
    .hero-slide-title { font-size: 13px; margin-bottom: 4px; }
    .hero-slide-sub { font-size: 10px; margin-bottom: 5px; }
    .hero-slide-btn { font-size: 9.5px; padding: 4px 10px; }
}

@media (min-width: 400px) and (max-width: 449.98px) {
    div[id="heroSlider"] { border-radius: 12px; }
    div[id="heroSlider"] .splide__pagination { bottom: 7px !important; gap: 4px !important; }
    div[id="heroSlider"] .splide__pagination__page { width: 5px !important; height: 5px !important; margin: 0 2px !important; }
    div[id="heroSlider"] .splide__pagination__page.is-active { width: 15px !important; }
    .hs-slide-content { padding: 12px 14px; }
    .hs-text-card { max-width: 240px; padding: 9px 12px; border-radius: 11px; }
    .hero-slide-badge { font-size: 9.5px; padding: 2px 8px; margin-bottom: 4px; }
    .hero-slide-title { font-size: 14px; margin-bottom: 4px; }
    .hero-slide-sub { font-size: 10.5px; margin-bottom: 5px; }
    .hero-slide-btn { font-size: 10px; padding: 4px 11px; }
}

@media (min-width: 450px) and (max-width: 499.98px) {
    div[id="heroSlider"] { border-radius: 13px; }
    div[id="heroSlider"] .splide__pagination { bottom: 7px !important; gap: 4px !important; }
    div[id="heroSlider"] .splide__pagination__page { width: 5.5px !important; height: 5.5px !important; margin: 0 2px !important; }
    div[id="heroSlider"] .splide__pagination__page.is-active { width: 16px !important; }
    .hs-slide-content { padding: 13px 16px; }
    .hs-text-card { max-width: 260px; padding: 10px 13px; border-radius: 12px; }
    .hero-slide-badge { font-size: 9.5px; padding: 2px 8px; margin-bottom: 4px; }
    .hero-slide-title { font-size: 15px; margin-bottom: 5px; }
    .hero-slide-sub { font-size: 11px; margin-bottom: 6px; }
    .hero-slide-btn { font-size: 10.5px; padding: 4.5px 12px; }
}

@media (min-width: 500px) and (max-width: 549.98px) {
    div[id="heroSlider"] { border-radius: 14px; }
    div[id="heroSlider"] .splide__pagination { bottom: 8px !important; gap: 4.5px !important; }
    div[id="heroSlider"] .splide__pagination__page { width: 5.5px !important; height: 5.5px !important; margin: 0 2px !important; }
    div[id="heroSlider"] .splide__pagination__page.is-active { width: 17px !important; }
    .hs-slide-content { padding: 14px 18px; }
    .hs-text-card { max-width: 280px; padding: 11px 15px; border-radius: 13px; }
    .hero-slide-badge { font-size: 10px; padding: 2.5px 9px; margin-bottom: 5px; }
    .hero-slide-title { font-size: 16px; margin-bottom: 5px; }
    .hero-slide-sub { font-size: 11px; margin-bottom: 6px; }
    .hero-slide-btn { font-size: 10.5px; padding: 4.5px 12px; }
}

@media (min-width: 550px) and (max-width: 599.98px) {
    div[id="heroSlider"] { border-radius: 15px; }
    div[id="heroSlider"] .splide__pagination { bottom: 8px !important; gap: 4.5px !important; }
    div[id="heroSlider"] .splide__pagination__page { width: 6px !important; height: 6px !important; margin: 0 2px !important; }
    div[id="heroSlider"] .splide__pagination__page.is-active { width: 18px !important; }
    .hs-slide-content { padding: 16px 20px; }
    .hs-text-card { max-width: 300px; padding: 12px 16px; border-radius: 13px; }
    .hero-slide-badge { font-size: 10px; padding: 2.5px 9px; margin-bottom: 5px; }
    .hero-slide-title { font-size: 17px; margin-bottom: 5px; }
    .hero-slide-sub { font-size: 11.5px; margin-bottom: 7px; }
    .hero-slide-btn { font-size: 11px; padding: 5px 13px; }
}

@media (min-width: 600px) and (max-width: 649.98px) {
    div[id="heroSlider"] { border-radius: 16px; }
    div[id="heroSlider"] .splide__pagination { bottom: 9px !important; gap: 5px !important; }
    div[id="heroSlider"] .splide__pagination__page { width: 6px !important; height: 6px !important; margin: 0 2px !important; }
    div[id="heroSlider"] .splide__pagination__page.is-active { width: 18px !important; }
    .hs-slide-content { padding: 17px 22px; }
    .hs-text-card { max-width: 320px; padding: 13px 17px; border-radius: 14px; }
    .hero-slide-badge { font-size: 10px; padding: 2.5px 10px; margin-bottom: 5px; }
    .hero-slide-title { font-size: 18px; margin-bottom: 6px; }
    .hero-slide-sub { font-size: 11.5px; margin-bottom: 7px; }
    .hero-slide-btn { font-size: 11px; padding: 5px 13px; }
}

@media (min-width: 650px) and (max-width: 699.98px) {
    div[id="heroSlider"] { border-radius: 16px; }
    div[id="heroSlider"] .splide__pagination { bottom: 9px !important; gap: 5px !important; }
    div[id="heroSlider"] .splide__pagination__page { width: 6px !important; height: 6px !important; margin: 0 2px !important; }
    div[id="heroSlider"] .splide__pagination__page.is-active { width: 19px !important; }
    .hs-slide-content { padding: 18px 24px; }
    .hs-text-card { max-width: 340px; padding: 14px 18px; border-radius: 14px; }
    .hero-slide-badge { font-size: 10.5px; padding: 2.5px 10px; margin-bottom: 6px; }
    .hero-slide-title { font-size: 19px; margin-bottom: 6px; }
    .hero-slide-sub { font-size: 12px; margin-bottom: 8px; }
    .hero-slide-btn { font-size: 11px; padding: 5px 14px; }
}

@media (min-width: 700px) and (max-width: 749.98px) {
    div[id="heroSlider"] { border-radius: 17px; }
    div[id="heroSlider"] .splide__pagination { bottom: 10px !important; gap: 5px !important; }
    div[id="heroSlider"] .splide__pagination__page { width: 6.5px !important; height: 6.5px !important; margin: 0 2px !important; }
    div[id="heroSlider"] .splide__pagination__page.is-active { width: 20px !important; }
    .hs-slide-content { padding: 20px 25px; }
    .hs-text-card { max-width: 350px; padding: 14px 18px; border-radius: 15px; }
    .hero-slide-badge { font-size: 10.5px; padding: 2.5px 10px; margin-bottom: 6px; }
    .hero-slide-title { font-size: 20px; margin-bottom: 6px; }
    .hero-slide-sub { font-size: 12px; margin-bottom: 8px; }
    .hero-slide-btn { font-size: 11.5px; padding: 5px 14px; }
}

@media (min-width: 750px) and (max-width: 799.98px) {
    div[id="heroSlider"] { border-radius: 18px; }
    div[id="heroSlider"] .splide__pagination { bottom: 10px !important; gap: 5.5px !important; }
    div[id="heroSlider"] .splide__pagination__page { width: 6.5px !important; height: 6.5px !important; margin: 0 2px !important; }
    div[id="heroSlider"] .splide__pagination__page.is-active { width: 20px !important; }
    .hs-slide-content { padding: 22px 26px; }
    .hs-text-card { max-width: 360px; padding: 15px 19px; border-radius: 15px; }
    .hero-slide-badge { font-size: 10.5px; padding: 2.5px 10px; margin-bottom: 6px; }
    .hero-slide-title { font-size: 21px; margin-bottom: 6px; }
    .hero-slide-sub { font-size: 12px; margin-bottom: 8px; }
    .hero-slide-btn { font-size: 11.5px; padding: 5.5px 14px; }
}

@media (min-width: 800px) and (max-width: 849.98px) {
    div[id="heroSlider"] { border-radius: 18px; }
    div[id="heroSlider"] .splide__pagination { bottom: 11px !important; gap: 5.5px !important; }
    div[id="heroSlider"] .splide__pagination__page { width: 7px !important; height: 7px !important; margin: 0 2px !important; }
    div[id="heroSlider"] .splide__pagination__page.is-active { width: 21px !important; }
    .hs-slide-content { padding: 24px 28px; }
    .hs-text-card { max-width: 380px; padding: 15px 20px; border-radius: 16px; }
    .hero-slide-badge { font-size: 11px; padding: 3px 10px; margin-bottom: 6px; }
    .hero-slide-title { font-size: 22px; margin-bottom: 7px; }
    .hero-slide-sub { font-size: 12.5px; margin-bottom: 9px; }
    .hero-slide-btn { font-size: 12px; padding: 5.5px 15px; }
}

@media (min-width: 850px) and (max-width: 899.98px) {
    div[id="heroSlider"] { border-radius: 19px; }
    div[id="heroSlider"] .splide__pagination { bottom: 11px !important; gap: 5.5px !important; }
    div[id="heroSlider"] .splide__pagination__page { width: 7px !important; height: 7px !important; margin: 0 2px !important; }
    div[id="heroSlider"] .splide__pagination__page.is-active { width: 21px !important; }
    .hs-slide-content { padding: 25px 30px; }
    .hs-text-card { max-width: 390px; padding: 16px 20px; border-radius: 16px; }
    .hero-slide-badge { font-size: 11px; padding: 3px 11px; margin-bottom: 6px; }
    .hero-slide-title { font-size: 23px; margin-bottom: 7px; }
    .hero-slide-sub { font-size: 12.5px; margin-bottom: 9px; }
    .hero-slide-btn { font-size: 12px; padding: 6px 15px; }
}

@media (min-width: 900px) and (max-width: 949.98px) {
    div[id="heroSlider"] { border-radius: 19px; }
    div[id="heroSlider"] .splide__pagination { bottom: 12px !important; gap: 6px !important; }
    div[id="heroSlider"] .splide__pagination__page { width: 7px !important; height: 7px !important; margin: 0 2px !important; }
    div[id="heroSlider"] .splide__pagination__page.is-active { width: 22px !important; }
    .hs-slide-content { padding: 26px 32px; }
    .hs-text-card { max-width: 400px; padding: 16px 21px; border-radius: 16px; }
    .hero-slide-badge { font-size: 11px; padding: 3px 11px; margin-bottom: 6px; }
    .hero-slide-title { font-size: 23px; margin-bottom: 7px; }
    .hero-slide-sub { font-size: 12.5px; margin-bottom: 9px; }
    .hero-slide-btn { font-size: 12px; padding: 6px 15px; }
}

@media (min-width: 950px) and (max-width: 991.98px) {
    div[id="heroSlider"] { border-radius: 20px; }
    div[id="heroSlider"] .splide__pagination { bottom: 12px !important; gap: 6px !important; }
    div[id="heroSlider"] .splide__pagination__page { width: 7px !important; height: 7px !important; margin: 0 2px !important; }
    div[id="heroSlider"] .splide__pagination__page.is-active { width: 22px !important; }
    .hs-slide-content { padding: 28px 34px; }
    .hs-text-card { max-width: 420px; padding: 17px 22px; border-radius: 17px; }
    .hero-slide-badge { font-size: 11px; padding: 3px 11px; margin-bottom: 6px; }
    .hero-slide-title { font-size: 24px; margin-bottom: 8px; }
    .hero-slide-sub { font-size: 13px; margin-bottom: 10px; }
    .hero-slide-btn { font-size: 12.5px; padding: 6px 16px; }
}

@media (min-width: 992px) and (max-width: 1049.98px) {
    div[id="heroSlider"] { border-radius: 18px; }
    div[id="heroSlider"] .splide__pagination { bottom: 10px !important; gap: 5px !important; }
    div[id="heroSlider"] .splide__pagination__page { width: 6px !important; height: 6px !important; margin: 0 2px !important; }
    div[id="heroSlider"] .splide__pagination__page.is-active { width: 20px !important; }
    .hs-slide-content { padding: 20px 24px; }
    .hs-text-card { max-width: 340px; padding: 14px 18px; border-radius: 15px; }
    .hero-slide-badge { font-size: 10px; padding: 2.5px 9px; margin-bottom: 5px; }
    .hero-slide-title { font-size: 19px; margin-bottom: 6px; }
    .hero-slide-sub { font-size: 11.5px; margin-bottom: 8px; }
    .hero-slide-btn { font-size: 11.5px; padding: 5px 14px; }
}

@media (min-width: 1050px) and (max-width: 1099.98px) {
    div[id="heroSlider"] { border-radius: 18px; }
    div[id="heroSlider"] .splide__pagination { bottom: 11px !important; gap: 5px !important; }
    div[id="heroSlider"] .splide__pagination__page { width: 6.5px !important; height: 6.5px !important; margin: 0 2px !important; }
    div[id="heroSlider"] .splide__pagination__page.is-active { width: 20px !important; }
    .hs-slide-content { padding: 22px 26px; }
    .hs-text-card { max-width: 360px; padding: 15px 19px; border-radius: 15px; }
    .hero-slide-badge { font-size: 10.5px; padding: 2.5px 10px; margin-bottom: 5px; }
    .hero-slide-title { font-size: 20px; margin-bottom: 6px; }
    .hero-slide-sub { font-size: 12px; margin-bottom: 8px; }
    .hero-slide-btn { font-size: 12px; padding: 5.5px 15px; }
}

@media (min-width: 1100px) and (max-width: 1149.98px) {
    div[id="heroSlider"] { border-radius: 19px; }
    div[id="heroSlider"] .splide__pagination { bottom: 11px !important; gap: 5.5px !important; }
    div[id="heroSlider"] .splide__pagination__page { width: 6.5px !important; height: 6.5px !important; margin: 0 2px !important; }
    div[id="heroSlider"] .splide__pagination__page.is-active { width: 21px !important; }
    .hs-slide-content { padding: 24px 28px; }
    .hs-text-card { max-width: 380px; padding: 16px 20px; border-radius: 16px; }
    .hero-slide-badge { font-size: 10.5px; padding: 2.5px 10px; margin-bottom: 6px; }
    .hero-slide-title { font-size: 21px; margin-bottom: 6px; }
    .hero-slide-sub { font-size: 12px; margin-bottom: 8px; }
    .hero-slide-btn { font-size: 12px; padding: 5.5px 15px; }
}

@media (min-width: 1150px) and (max-width: 1199.98px) {
    div[id="heroSlider"] { border-radius: 19px; }
    div[id="heroSlider"] .splide__pagination { bottom: 12px !important; gap: 5.5px !important; }
    div[id="heroSlider"] .splide__pagination__page { width: 7px !important; height: 7px !important; margin: 0 2px !important; }
    div[id="heroSlider"] .splide__pagination__page.is-active { width: 22px !important; }
    .hs-slide-content { padding: 26px 30px; }
    .hs-text-card { max-width: 400px; padding: 17px 21px; border-radius: 16px; }
    .hero-slide-badge { font-size: 11px; padding: 3px 10px; margin-bottom: 6px; }
    .hero-slide-title { font-size: 22px; margin-bottom: 7px; }
    .hero-slide-sub { font-size: 12.5px; margin-bottom: 9px; }
    .hero-slide-btn { font-size: 12.5px; padding: 6px 16px; }
}

@media (min-width: 1200px) and (max-width: 1249.98px) {
    div[id="heroSlider"] { border-radius: 20px; }
    div[id="heroSlider"] .splide__pagination { bottom: 12px !important; gap: 6px !important; }
    div[id="heroSlider"] .splide__pagination__page { width: 7px !important; height: 7px !important; margin: 0 2px !important; }
    div[id="heroSlider"] .splide__pagination__page.is-active { width: 22px !important; }
    .hs-slide-content { padding: 28px 32px; }
    .hs-text-card { max-width: 410px; padding: 18px 22px; border-radius: 17px; }
    .hero-slide-badge { font-size: 11px; padding: 3px 11px; margin-bottom: 6px; }
    .hero-slide-title { font-size: 23px; margin-bottom: 7px; }
    .hero-slide-sub { font-size: 12.5px; margin-bottom: 9px; }
    .hero-slide-btn { font-size: 12.5px; padding: 6px 16px; }
}

@media (min-width: 1250px) and (max-width: 1299.98px) {
    div[id="heroSlider"] { border-radius: 20px; }
    div[id="heroSlider"] .splide__pagination { bottom: 13px !important; gap: 6px !important; }
    div[id="heroSlider"] .splide__pagination__page { width: 7px !important; height: 7px !important; margin: 0 2px !important; }
    div[id="heroSlider"] .splide__pagination__page.is-active { width: 23px !important; }
    .hs-slide-content { padding: 30px 34px; }
    .hs-text-card { max-width: 420px; padding: 18px 22px; border-radius: 17px; }
    .hero-slide-badge { font-size: 11px; padding: 3px 11px; margin-bottom: 6px; }
    .hero-slide-title { font-size: 24px; margin-bottom: 8px; }
    .hero-slide-sub { font-size: 13px; margin-bottom: 10px; }
    .hero-slide-btn { font-size: 13px; padding: 6.5px 16px; }
}

@media (min-width: 1300px) and (max-width: 1349.98px) {
    div[id="heroSlider"] { border-radius: 21px; }
    div[id="heroSlider"] .splide__pagination { bottom: 13px !important; gap: 6px !important; }
    div[id="heroSlider"] .splide__pagination__page { width: 7.5px !important; height: 7.5px !important; margin: 0 2px !important; }
    div[id="heroSlider"] .splide__pagination__page.is-active { width: 24px !important; }
    .hs-slide-content { padding: 32px 36px; }
    .hs-text-card { max-width: 430px; padding: 19px 23px; border-radius: 18px; }
    .hero-slide-badge { font-size: 11.5px; padding: 3px 11px; margin-bottom: 6px; }
    .hero-slide-title { font-size: 25px; margin-bottom: 8px; }
    .hero-slide-sub { font-size: 13px; margin-bottom: 10px; }
    .hero-slide-btn { font-size: 13px; padding: 6.5px 17px; }
}

@media (min-width: 1350px) and (max-width: 1399.98px) {
    div[id="heroSlider"] { border-radius: 21px; }
    div[id="heroSlider"] .splide__pagination { bottom: 14px !important; gap: 6px !important; }
    div[id="heroSlider"] .splide__pagination__page { width: 7.5px !important; height: 7.5px !important; margin: 0 2px !important; }
    div[id="heroSlider"] .splide__pagination__page.is-active { width: 24px !important; }
    .hs-slide-content { padding: 34px 38px; }
    .hs-text-card { max-width: 440px; padding: 20px 24px; border-radius: 18px; }
    .hero-slide-badge { font-size: 11.5px; padding: 3px 12px; margin-bottom: 6px; }
    .hero-slide-title { font-size: 26px; margin-bottom: 8px; }
    .hero-slide-sub { font-size: 13.5px; margin-bottom: 10px; }
    .hero-slide-btn { font-size: 13px; padding: 7px 18px; }
}

@media (min-width: 1400px) and (max-width: 1449.98px) {
    div[id="heroSlider"] { border-radius: 22px; }
    div[id="heroSlider"] .splide__pagination { bottom: 14px !important; gap: 6.5px !important; }
    div[id="heroSlider"] .splide__pagination__page { width: 8px !important; height: 8px !important; margin: 0 2.5px !important; }
    div[id="heroSlider"] .splide__pagination__page.is-active { width: 25px !important; }
    .hs-slide-content { padding: 36px 40px; }
    .hs-text-card { max-width: 450px; padding: 20px 24px; border-radius: 18px; }
    .hero-slide-badge { font-size: 12px; padding: 3.5px 12px; margin-bottom: 7px; }
    .hero-slide-title { font-size: 27px; margin-bottom: 8px; }
    .hero-slide-sub { font-size: 14px; margin-bottom: 11px; }
    .hero-slide-btn { font-size: 13.5px; padding: 7px 18px; }
}

@media (min-width: 1450px) and (max-width: 1499.98px) {
    div[id="heroSlider"] { border-radius: 22px; }
    div[id="heroSlider"] .splide__pagination { bottom: 15px !important; gap: 6.5px !important; }
    div[id="heroSlider"] .splide__pagination__page { width: 8px !important; height: 8px !important; margin: 0 2.5px !important; }
    div[id="heroSlider"] .splide__pagination__page.is-active { width: 26px !important; }
    .hs-slide-content { padding: 38px 42px; }
    .hs-text-card { max-width: 460px; padding: 21px 25px; border-radius: 19px; }
    .hero-slide-badge { font-size: 12px; padding: 3.5px 12px; margin-bottom: 7px; }
    .hero-slide-title { font-size: 28px; margin-bottom: 9px; }
    .hero-slide-sub { font-size: 14px; margin-bottom: 11px; }
    .hero-slide-btn { font-size: 13.5px; padding: 7.5px 19px; }
}

@media (min-width: 1500px) and (max-width: 1549.98px) {
    div[id="heroSlider"] { border-radius: 23px; }
    div[id="heroSlider"] .splide__pagination { bottom: 15px !important; gap: 6.5px !important; }
    div[id="heroSlider"] .splide__pagination__page { width: 8px !important; height: 8px !important; margin: 0 2.5px !important; }
    div[id="heroSlider"] .splide__pagination__page.is-active { width: 26px !important; }
    .hs-slide-content { padding: 40px 44px; }
    .hs-text-card { max-width: 470px; padding: 22px 26px; border-radius: 19px; }
    .hero-slide-badge { font-size: 12px; padding: 3.5px 13px; margin-bottom: 7px; }
    .hero-slide-title { font-size: 29px; margin-bottom: 9px; }
    .hero-slide-sub { font-size: 14px; margin-bottom: 12px; }
    .hero-slide-btn { font-size: 14px; padding: 7.5px 20px; }
}

@media (min-width: 1550px) and (max-width: 1599.98px) {
    div[id="heroSlider"] { border-radius: 23px; }
    div[id="heroSlider"] .splide__pagination { bottom: 16px !important; gap: 7px !important; }
    div[id="heroSlider"] .splide__pagination__page { width: 8px !important; height: 8px !important; margin: 0 2.5px !important; }
    div[id="heroSlider"] .splide__pagination__page.is-active { width: 27px !important; }
    .hs-slide-content { padding: 42px 46px; }
    .hs-text-card { max-width: 480px; padding: 22px 26px; border-radius: 20px; }
    .hero-slide-badge { font-size: 12.5px; padding: 3.5px 13px; margin-bottom: 7px; }
    .hero-slide-title { font-size: 30px; margin-bottom: 9px; }
    .hero-slide-sub { font-size: 14.5px; margin-bottom: 12px; }
    .hero-slide-btn { font-size: 14px; padding: 8px 20px; }
}

@media (min-width: 1600px) {
    div[id="heroSlider"] { border-radius: 24px; }
    div[id="heroSlider"] .splide__pagination { bottom: 16px !important; gap: 7px !important; }
    div[id="heroSlider"] .splide__pagination__page { width: 8.5px !important; height: 8.5px !important; margin: 0 3px !important; }
    div[id="heroSlider"] .splide__pagination__page.is-active { width: 28px !important; }
    .hs-slide-content { padding: 44px 48px; }
    .hs-text-card { max-width: 500px; padding: 24px 28px; border-radius: 20px; }
    .hero-slide-badge { font-size: 13px; padding: 4px 14px; margin-bottom: 8px; }
    .hero-slide-title { font-size: 32px; margin-bottom: 10px; }
    .hero-slide-sub { font-size: 15px; margin-bottom: 12px; }
    .hero-slide-btn { font-size: 14.5px; padding: 8px 22px; }
}

.hero-side-2cards {
    display: flex;
    flex-direction: column;
    gap: 10px;
    height: 100%;
}
.promo-card-zb {
    border-radius: 16px;
    position: relative;
    overflow: hidden;
    padding: 14px 16px;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    box-sizing: border-box;
    transition: transform 0.2s ease, border-color 0.2s ease;
    flex: 1 1 0;
    min-height: 0;
}
.promo-card-zb:hover {
    transform: translateY(-2px);
}
.promo-card-glow {
    position: absolute;
    inset: 0;
    pointer-events: none;
    z-index: 0;
}
.promo-card-zb > * {
    position: relative;
    z-index: 1;
}
.promo-card-voucher-expanded {
    background: linear-gradient(135deg, #131926 0%, #0d111a 100%);
    border: 1px solid rgba(251, 191, 36, 0.22);
}
.promo-card-voucher-expanded:hover {
    border-color: rgba(251, 191, 36, 0.45);
}
.promo-card-trust-expanded {
    background: linear-gradient(135deg, #0e1c18 0%, #0b1317 100%);
    border: 1px solid rgba(34, 197, 94, 0.22);
}
.promo-card-trust-expanded:hover {
    border-color: rgba(34, 197, 94, 0.45);
}

.p-middle-block {
    display: flex;
    flex-direction: column;
    justify-content: center;
    margin-top: auto;
    margin-bottom: auto;
    padding: 2px 0;
    width: 100%;
}
.p-icon-row {
    margin-bottom: 8px;
    gap: 8px;
}
.p-icon-box {
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}
.p-icon-amber {
    background: rgba(251, 191, 36, 0.15);
    color: #fbbf24;
    border: 1px solid rgba(251, 191, 36, 0.3);
}
.p-icon-emerald {
    background: rgba(34, 197, 94, 0.15);
    color: #4ade80;
    border: 1px solid rgba(34, 197, 94, 0.3);
}
.card-p-title {
    color: #ffffff;
    font-weight: 700;
    line-height: 1.25;
    margin: 0 !important;
    padding: 0 !important;
}
.p-subtitle {
    color: rgba(255, 255, 255, 0.55);
    line-height: 1.25;
    margin: 0 !important;
    padding: 0 !important;
}
.p-coupon-action {
    background: rgba(251, 191, 36, 0.07);
    border: 1.5px dashed rgba(251, 191, 36, 0.4);
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    cursor: pointer;
    transition: background 0.15s ease;
}
.p-coupon-action:hover {
    background: rgba(251, 191, 36, 0.12);
}
.p-stats-bar {
    background: rgba(255, 255, 255, 0.04);
    border: 1px solid rgba(255, 255, 255, 0.08);
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.p-stat-unit {
    display: flex;
    align-items: center;
    gap: 4px;
}
.p-stat-sep {
    width: 1px;
    background: rgba(255, 255, 255, 0.12);
}
.p-footer {
    border-top: 1px solid rgba(255, 255, 255, 0.08);
}

.hero-side-3cards {
    display: flex;
    flex-direction: column;
    gap: 10px;
    height: 100%;
}
.c3-top-row {
    display: flex;
    gap: 10px;
    width: 100%;
    height: calc(50% - 5px);
}
.c3-top-card {
    flex: 1 1 0;
    width: 50%;
    min-width: 0;
    border-radius: 16px;
    position: relative;
    overflow: hidden;
    padding: 10px 9px;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    box-sizing: border-box;
    transition: transform 0.2s ease, border-color 0.2s ease;
}
.c3-top-card:hover, .c3-bottom-card:hover {
    transform: translateY(-2px);
}
.c3-top-card > *, .c3-bottom-card > * {
    position: relative;
    z-index: 1;
}
.c3-card-voucher {
    background: linear-gradient(145deg, #131926 0%, #0c1017 100%);
    border: 1px solid rgba(251, 191, 36, 0.22);
}
.c3-card-voucher:hover {
    border-color: rgba(251, 191, 36, 0.45);
}
.c3-card-trust {
    background: linear-gradient(145deg, #0e1a17 0%, #0a1114 100%);
    border: 1px solid rgba(34, 197, 94, 0.22);
}
.c3-card-trust:hover {
    border-color: rgba(34, 197, 94, 0.45);
}
.c3-bottom-card {
    border-radius: 16px;
    position: relative;
    overflow: hidden;
    padding: 12px 14px;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    box-sizing: border-box;
    background: linear-gradient(135deg, #111a2b 0%, #0b111d 100%);
    border: 1px solid rgba(34, 197, 94, 0.22);
    transition: transform 0.2s ease, border-color 0.2s ease;
    height: calc(50% - 5px);
}
.c3-bottom-card:hover {
    border-color: rgba(34, 197, 94, 0.45);
}

.c3-glow-amber {
    position: absolute;
    inset: 0;
    pointer-events: none;
    z-index: 0;
    background: radial-gradient(circle at 85% 15%, rgba(251, 191, 36, 0.16) 0%, transparent 65%);
}
.c3-glow-emerald {
    position: absolute;
    inset: 0;
    pointer-events: none;
    z-index: 0;
    background: radial-gradient(circle at 85% 15%, rgba(34, 197, 94, 0.16) 0%, transparent 65%);
}
.c3-glow-blue {
    position: absolute;
    inset: 0;
    pointer-events: none;
    z-index: 0;
    background: radial-gradient(circle at 85% 50%, rgba(34, 197, 94, 0.14) 0%, transparent 65%);
}

.c3-top-center {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    text-align: center;
    margin-top: auto;
    margin-bottom: auto;
    padding: 2px 0;
}
.c3-icon-box {
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}
.c3-icon-amber {
    background: rgba(251, 191, 36, 0.15);
    color: #fbbf24;
    border: 1px solid rgba(251, 191, 36, 0.3);
}
.c3-icon-emerald {
    background: rgba(34, 197, 94, 0.15);
    color: #4ade80;
    border: 1px solid rgba(34, 197, 94, 0.3);
}
.c3-card-title {
    font-weight: 700;
    color: #ffffff;
    line-height: 1.2;
}
.c3-card-desc {
    color: rgba(255, 255, 255, 0.55);
    line-height: 1.2;
    margin-top: 1px;
}
.c3-copy-pill {
    background: rgba(251, 191, 36, 0.08);
    border: 1.2px dashed rgba(251, 191, 36, 0.45);
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    cursor: pointer;
    width: 100%;
    box-sizing: border-box;
    transition: background 0.15s ease;
}
.c3-copy-pill:hover {
    background: rgba(251, 191, 36, 0.14);
}
.c3-code-val {
    font-family: monospace;
    font-weight: 800;
    color: #fbbf24;
    letter-spacing: 0.04em;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
.c3-btn-icon {
    background: #fbbf24;
    color: #0f172a;
    border: none;
    border-radius: 6px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    cursor: pointer;
}
.c3-trust-grid {
    background: rgba(255, 255, 255, 0.04);
    border: 1px solid rgba(255, 255, 255, 0.08);
    border-radius: 8px;
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 4px;
    width: 100%;
    box-sizing: border-box;
}
.c3-trust-cell {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 3px;
    white-space: nowrap;
}
.c3-trust-lbl {
    color: rgba(255, 255, 255, 0.55);
}
.c3-deal-icon {
    border-radius: 9px;
    background: rgba(34, 197, 94, 0.15);
    color: #4ade80;
    border: 1px solid rgba(34, 197, 94, 0.3);
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 10px;
}
.c3-deal-title {
    font-weight: 700;
    color: #ffffff;
    line-height: 1.25;
}
.c3-deal-subtitle {
    color: rgba(255, 255, 255, 0.55);
    line-height: 1.3;
}
.c3-deal-action {
    background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
    color: #ffffff;
    border: none;
    border-radius: 999px;
    font-weight: 700;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    text-decoration: none;
    box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
    transition: all 0.2s ease;
}
.c3-deal-action:hover {
    color: #ffffff;
    background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
    transform: translateY(-1px);
}
.c3-deal-action i {
    font-size: 8.5px;
}

.p-badge-amber, .p-badge-emerald, .p-badge-muted {
    border-radius: 999px;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
}
.p-badge-amber {
    background: rgba(251, 191, 36, 0.12);
    color: #fbbf24;
    border: 1px solid rgba(251, 191, 36, 0.3);
}
.p-badge-emerald {
    background: rgba(34, 197, 94, 0.12);
    color: #4ade80;
    border: 1px solid rgba(34, 197, 94, 0.3);
}
.p-badge-muted {
    background: rgba(255, 255, 255, 0.08);
    color: rgba(255, 255, 255, 0.6);
    border: 1px solid rgba(255, 255, 255, 0.12);
}
.p-pill-amber {
    background: #fbbf24;
    color: #0f172a;
    font-weight: 800;
    border-radius: 999px;
}
.c3-badge-amber, .c3-badge-emerald, .c3-pill-muted {
    font-weight: 700;
    border-radius: 999px;
    white-space: nowrap;
    line-height: 1.2;
}
.c3-badge-amber {
    background: rgba(251, 191, 36, 0.12);
    color: #fbbf24;
    border: 1px solid rgba(251, 191, 36, 0.3);
}
.c3-badge-emerald {
    background: rgba(34, 197, 94, 0.12);
    color: #4ade80;
    border: 1px solid rgba(34, 197, 94, 0.28);
}
.c3-pill-muted {
    background: rgba(255, 255, 255, 0.08);
    color: rgba(255, 255, 255, 0.6);
    border: 1px solid rgba(255, 255, 255, 0.12);
}
.c3-pill-amber {
    font-weight: 800;
    border-radius: 999px;
    background: #fbbf24;
    color: #0f172a;
    white-space: nowrap;
    line-height: 1.2;
}
.c3-badge-rose, .c3-badge-ship {
    font-weight: 600;
    border-radius: 999px;
    white-space: nowrap;
}
.c3-badge-rose {
    background: rgba(239, 68, 68, 0.15);
    color: #f87171;
    border: 1px solid rgba(239, 68, 68, 0.3);
}
.c3-badge-ship {
    background: rgba(34, 197, 94, 0.16);
    color: #4ade80;
    border: 1px solid rgba(34, 197, 94, 0.35);
}

@media (min-width: 992px) and (max-width: 1049.98px) {
    .promo-card-zb { padding: 12px 14px; }
    .p-badge-amber, .p-badge-emerald, .p-badge-muted { font-size: 10px; padding: 2px 7px; }
    .p-pill-amber { font-size: 10.5px; padding: 2px 7px; }
    .p-icon-box { width: 32px; height: 32px; min-width: 32px; font-size: 13px; }
    .card-p-title { font-size: 13px; }
    .p-subtitle { font-size: 10px; }
    .p-coupon-action { padding: 4px 8px; }
    .p-code-label { font-size: 10px; }
    .p-code-value { font-size: 11.5px; }
    .p-stats-bar { padding: 4px 8px; }
    .p-stat-num { font-size: 11px; }
    .p-stat-txt { font-size: 9px; }
    .p-stat-sep { height: 12px; }
    .p-footer-text, .p-footer-link { font-size: 10px; }

    .c3-top-card { padding: 8px 8px; }
    .c3-badge-amber, .c3-badge-emerald, .c3-pill-muted { font-size: 8.5px; padding: 1.5px 5px; }
    .c3-pill-amber { font-size: 9px; padding: 1.5px 5px; }
    .c3-icon-box { width: 28px; height: 28px; font-size: 12px; margin-bottom: 2px; }
    .c3-card-title { font-size: 16px; }
    .c3-card-desc { font-size: 14px; }
    .c3-copy-pill { padding: 2.5px 5px; }
    .c3-code-val { font-size: 10px; }
    .c3-btn-icon { width: 18px; height: 18px; font-size: 9px; }
    .c3-trust-grid { padding: 2.5px 3px; }
    .c3-t-num { font-size: 9.5px; }
    .c3-trust-lbl { font-size: 8px; }
    .c3-bottom-card { padding: 10px 12px; }
    .c3-badge-rose, .c3-badge-ship { font-size: 9.5px; padding: 2px 7px; }
    .c3-deal-icon { width: 30px; height: 30px; font-size: 13px; }
    .c3-deal-title { font-size: 16px; }
    .c3-deal-subtitle { font-size: 9.5px; }
    .c3-deal-action { font-size: 10.5px; padding: 4.5px 10px; }
}

@media (min-width: 1050px) and (max-width: 1099.98px) {
    .promo-card-zb { padding: 13px 15px; }
    .p-badge-amber, .p-badge-emerald, .p-badge-muted { font-size: 10.5px; padding: 2.5px 8px; }
    .p-pill-amber { font-size: 11px; padding: 2.5px 8px; }
    .p-icon-box { width: 34px; height: 34px; min-width: 34px; font-size: 13.5px; }
    .card-p-title { font-size: 13.5px; }
    .p-subtitle { font-size: 10.5px; }
    .p-coupon-action { padding: 4.5px 9px; }
    .p-code-label { font-size: 10.5px; }
    .p-code-value { font-size: 12px; }
    .p-stats-bar { padding: 5px 9px; }
    .p-stat-num { font-size: 11.5px; }
    .p-stat-txt { font-size: 9.5px; }
    .p-stat-sep { height: 13px; }
    .p-footer-text, .p-footer-link { font-size: 10.5px; }

    .c3-top-card { padding: 9px 8.5px; }
    .c3-badge-amber, .c3-badge-emerald, .c3-pill-muted { font-size: 9px; padding: 1.5px 5.5px; }
    .c3-pill-amber { font-size: 9.5px; padding: 1.5px 5.5px; }
    .c3-icon-box { width: 30px; height: 30px; font-size: 13px; margin-bottom: 2.5px; }
    .c3-card-title { font-size: 16px; }
    .c3-card-desc { font-size: 14px; }
    .c3-copy-pill { padding: 3px 6px; }
    .c3-code-val { font-size: 10.5px; }
    .c3-btn-icon { width: 19px; height: 18.5px; font-size: 9.5px; }
    .c3-trust-grid { padding: 3px 3.5px; }
    .c3-t-num { font-size: 10px; }
    .c3-trust-lbl { font-size: 8.5px; }
    .c3-bottom-card { padding: 11px 13px; }
    .c3-badge-rose, .c3-badge-ship { font-size: 10px; padding: 2px 7.5px; }
    .c3-deal-icon { width: 32px; height: 32px; font-size: 13.5px; }
    .c3-deal-title { font-size: 16px; }
    .c3-deal-subtitle { font-size: 14px; }
    .c3-deal-action { font-size: 11px; padding: 5px 11px; }
}

@media (min-width: 1100px) and (max-width: 1149.98px) {
    .promo-card-zb { padding: 14px 16px; }
    .p-badge-amber, .p-badge-emerald, .p-badge-muted { font-size: 10.5px; padding: 2.5px 8.5px; }
    .p-pill-amber { font-size: 11px; padding: 2.5px 8.5px; }
    .p-icon-box { width: 35px; height: 35px; min-width: 35px; font-size: 14px; }
    .card-p-title { font-size: 13.5px; }
    .p-subtitle { font-size: 10.5px; }
    .p-coupon-action { padding: 5px 10px; }
    .p-code-label { font-size: 10.5px; }
    .p-code-value { font-size: 12.5px; }
    .p-stats-bar { padding: 5px 10px; }
    .p-stat-num { font-size: 11.5px; }
    .p-stat-txt { font-size: 9.5px; }
    .p-stat-sep { height: 14px; }
    .p-footer-text, .p-footer-link { font-size: 10.5px; }

    .c3-top-card { padding: 10px 9px; }
    .c3-badge-amber, .c3-badge-emerald, .c3-pill-muted { font-size: 9px; padding: 2px 6px; }
    .c3-pill-amber { font-size: 9.5px; padding: 2px 6px; }
    .c3-icon-box { width: 32px; height: 32px; font-size: 13.5px; margin-bottom: 3px; }
    .c3-card-title { font-size: 14px; }
    .c3-card-desc { font-size: 12px; }
    .c3-copy-pill { padding: 3px 6px; }
    .c3-code-val { font-size: 11px; }
    .c3-btn-icon { width: 20px; height: 19px; font-size: 9.5px; }
    .c3-trust-grid { padding: 3px 4px; }
    .c3-t-num { font-size: 10.5px; }
    .c3-trust-lbl { font-size: 8.5px; }
    .c3-bottom-card { padding: 12px 14px; }
    .c3-badge-rose, .c3-badge-ship { font-size: 10.5px; padding: 2px 8px; }
    .c3-deal-icon { width: 34px; height: 34px; font-size: 14px; }
    .c3-deal-title { font-size: 13px; }
    .c3-deal-subtitle { font-size: 10px; }
    .c3-deal-action { font-size: 11px; padding: 5.5px 12px; }
}

@media (min-width: 1150px) and (max-width: 1199.98px) {
    .promo-card-zb { padding: 14px 17px; }
    .p-badge-amber, .p-badge-emerald, .p-badge-muted { font-size: 10.5px; padding: 2.5px 9px; }
    .p-pill-amber { font-size: 11px; padding: 2.5px 9px; }
    .p-icon-box { width: 36px; height: 36px; min-width: 36px; font-size: 14px; }
    .card-p-title { font-size: 14px; }
    .p-subtitle { font-size: 11px; }
    .p-coupon-action { padding: 5px 10.5px; }
    .p-code-label { font-size: 10.5px; }
    .p-code-value { font-size: 12.5px; }
    .p-stats-bar { padding: 5.5px 11px; }
    .p-stat-num { font-size: 12px; }
    .p-stat-txt { font-size: 10px; }
    .p-stat-sep { height: 14px; }
    .p-footer-text, .p-footer-link { font-size: 10.5px; }

    .c3-top-card { padding: 10.5px 10px; }
    .c3-badge-amber, .c3-badge-emerald, .c3-pill-muted { font-size: 9.5px; padding: 2px 6px; }
    .c3-pill-amber { font-size: 10px; padding: 2px 6.5px; }
    .c3-icon-box { width: 33px; height: 33px; font-size: 14px; margin-bottom: 3px; }
    .c3-card-title { font-size: 16px; }
    .c3-card-desc { font-size: 14px; }
    .c3-copy-pill { padding: 3.5px 7px; }
    .c3-code-val { font-size: 11px; }
    .c3-btn-icon { width: 20px; height: 19px; font-size: 9.5px; }
    .c3-trust-grid { padding: 3.5px 4px; }
    .c3-t-num { font-size: 10.5px; }
    .c3-trust-lbl { font-size: 8.5px; }
    .c3-bottom-card { padding: 12px 14px; }
    .c3-badge-rose, .c3-badge-ship { font-size: 10.5px; padding: 2.5px 8px; }
    .c3-deal-icon { width: 35px; height: 35px; font-size: 14.5px; }
    .c3-deal-title { font-size: 16px; }
    .c3-deal-subtitle {
        font-size: 11px;
        margin-top: 3px;
    }
    .c3-deal-action { font-size: 11.5px; padding: 6px 12.5px; }
}

@media (min-width: 1200px) and (max-width: 1249.98px) {
    .promo-card-zb { padding: 15px 18px; }
    .p-badge-amber, .p-badge-emerald, .p-badge-muted { font-size: 11px; padding: 2.5px 9.5px; }
    .p-pill-amber { font-size: 11.5px; padding: 2.5px 9.5px; }
    .p-icon-box { width: 37px; height: 37px; min-width: 37px; font-size: 14.5px; }
    .card-p-title { font-size: 14px; }
    .p-subtitle { font-size: 11px; }
    .p-coupon-action { padding: 5.5px 11px; }
    .p-code-label { font-size: 11px; }
    .p-code-value { font-size: 13px; }
    .p-stats-bar { padding: 6px 12px; }
    .p-stat-num { font-size: 12px; }
    .p-stat-txt { font-size: 10px; }
    .p-stat-sep { height: 14px; }
    .p-footer-text, .p-footer-link { font-size: 11px; }

    .c3-top-card { padding: 11px 11px; }
    .c3-badge-amber, .c3-badge-emerald, .c3-pill-muted { font-size: 9.5px; padding: 2px 6.5px; }
    .c3-pill-amber { font-size: 10px; padding: 2px 7px; }
    .c3-icon-box { width: 34px; height: 34px; font-size: 14.5px; margin-bottom: 3.5px; }
    .c3-card-title { font-size: 15px; }
    .c3-card-desc { font-size: 14px; }
    .c3-copy-pill { padding: 4px 7.5px; }
    .c3-code-val { font-size: 11.5px; }
    .c3-btn-icon { width: 21px; height: 20px; font-size: 10px; }
    .c3-trust-grid { padding: 4px 5px; }
    .c3-t-num { font-size: 11px; }
    .c3-trust-lbl { font-size: 9px; }
    .c3-bottom-card { padding: 13px 15px; }
    .c3-badge-rose, .c3-badge-ship { font-size: 11px; padding: 2.5px 8.5px; }
    .c3-deal-icon { width: 36px; height: 36px; font-size: 15px; }
    .c3-deal-title { font-size: 16px; }
    .c3-deal-subtitle {
        font-size: 11px;
        margin-top: 1px;
    }
    .c3-deal-action { font-size: 12px; padding: 6px 13px; }
}

@media (min-width: 1250px) and (max-width: 1299.98px) {
    .promo-card-zb { padding: 15px 18px; }
    .p-badge-amber, .p-badge-emerald, .p-badge-muted { font-size: 11px; padding: 3px 9.5px; }
    .p-pill-amber { font-size: 11.5px; padding: 3px 9.5px; }
    .p-icon-box { width: 38px; height: 38px; min-width: 38px; font-size: 15px; }
    .card-p-title { font-size: 14.5px; }
    .p-subtitle { font-size: 11px; }
    .p-coupon-action { padding: 6px 12px; }
    .p-code-label { font-size: 11px; }
    .p-code-value { font-size: 13px; }
    .p-stats-bar { padding: 6px 12.5px; }
    .p-stat-num { font-size: 12px; }
    .p-stat-txt { font-size: 10px; }
    .p-stat-sep { height: 15px; }
    .p-footer-text, .p-footer-link { font-size: 11px; }

    .c3-top-card { padding: 11.5px 12px; }
    .c3-badge-amber, .c3-badge-emerald, .c3-pill-muted { font-size: 10px; padding: 2.5px 7px; }
    .c3-pill-amber { font-size: 10px; padding: 2.5px 7px; }
    .c3-icon-box { width: 36px; height: 36px; font-size: 15px; margin-bottom: 3.5px; }
    .c3-card-title { font-size: 16px; }
    .c3-card-desc { font-size: 12px; }
    .c3-copy-pill { padding: 4.5px 8px; }
    .c3-code-val { font-size: 11.5px; }
    .c3-btn-icon { width: 21px; height: 20px; font-size: 10px; }
    .c3-trust-grid { padding: 4.5px 5px; }
    .c3-t-num { font-size: 11px; }
    .c3-trust-lbl { font-size: 9px; }
    .c3-bottom-card { padding: 13.5px 15.5px; }
    .c3-badge-rose, .c3-badge-ship { font-size: 11px; padding: 2.5px 8.5px; }
    .c3-deal-icon { width: 38px; height: 38px; font-size: 16px; }
    .c3-deal-title { font-size: 16px; }
    .c3-deal-subtitle {
        font-size: 12px;
        margin-top: 3px;
    }
    .c3-deal-action { font-size: 12px; padding: 6.5px 13.5px; }
}

@media (min-width: 1300px) and (max-width: 1349.98px) {
    .promo-card-zb { padding: 16px 19px; }
    .p-badge-amber, .p-badge-emerald, .p-badge-muted { font-size: 11px; padding: 3px 10px; }
    .p-pill-amber { font-size: 11.5px; padding: 3px 10px; }
    .p-icon-box { width: 38px; height: 38px; min-width: 38px; font-size: 15px; }
    .card-p-title { font-size: 14.5px; }
    .p-subtitle { font-size: 11px; }
    .p-coupon-action { padding: 6px 12px; }
    .p-code-label { font-size: 11px; }
    .p-code-value { font-size: 13.5px; }
    .p-stats-bar { padding: 6px 13px; }
    .p-stat-num { font-size: 12.5px; }
    .p-stat-txt { font-size: 10.5px; }
    .p-stat-sep { height: 15px; }
    .p-footer-text, .p-footer-link { font-size: 11px; }

    .c3-top-card { padding: 12px 13px; }
    .c3-badge-amber, .c3-badge-emerald, .c3-pill-muted { font-size: 10px; padding: 2.5px 7px; }
    .c3-pill-amber { font-size: 10.5px; padding: 2.5px 7.5px; }
    .c3-icon-box { width: 38px; height: 38px; font-size: 16px; margin-bottom: 4px; }
    .c3-card-title { font-size: 16px; }
    .c3-card-desc { font-size: 14px; }
    .c3-copy-pill { padding: 5px 9px; }
    .c3-code-val { font-size: 12px; }
    .c3-btn-icon { width: 22px; height: 21px; font-size: 10.5px; }
    .c3-trust-grid { padding: 5px 6px; }
    .c3-t-num { font-size: 11.5px; }
    .c3-trust-lbl { font-size: 9.5px; }
    .c3-bottom-card { padding: 14px 16px; }
    .c3-badge-rose, .c3-badge-ship { font-size: 11.5px; padding: 2.5px 9px; }
    .c3-deal-icon { width: 40px; height: 40px; font-size: 17px; }
    .c3-deal-title { font-size: 18px; }
    .c3-deal-subtitle { font-size: 12px; }
    .c3-deal-action { font-size: 12.5px; padding: 7px 14px; }
}

@media (min-width: 1350px) and (max-width: 1399.98px) {
    .promo-card-zb { padding: 17px 20px; }
    .p-badge-amber, .p-badge-emerald, .p-badge-muted { font-size: 11.5px; padding: 3px 10px; }
    .p-pill-amber { font-size: 12px; padding: 3px 10px; }
    .p-icon-box { width: 40px; height: 40px; min-width: 40px; font-size: 15.5px; }
    .card-p-title { font-size: 15px; }
    .p-subtitle { font-size: 11.5px; }
    .p-coupon-action { padding: 6.5px 13px; }
    .p-code-label { font-size: 11px; }
    .p-code-value { font-size: 14px; }
    .p-stats-bar { padding: 6.5px 14px; }
    .p-stat-num { font-size: 13px; }
    .p-stat-txt { font-size: 10.5px; }
    .p-stat-sep { height: 16px; }
    .p-footer-text, .p-footer-link { font-size: 11.5px; }

    .c3-top-card { padding: 12px 14px; }
    .c3-badge-amber, .c3-badge-emerald, .c3-pill-muted { font-size: 10.5px; padding: 2.5px 7.5px; }
    .c3-pill-amber { font-size: 10.5px; padding: 2.5px 7.5px; }
    .c3-icon-box { width: 40px; height: 40px; font-size: 17px; margin-bottom: 4px; }
    .c3-card-title { font-size: 20px; }
    .c3-card-desc { font-size: 16px; }
    .c3-copy-pill { padding: 5px 10px; }
    .c3-code-val { font-size: 12px; }
    .c3-btn-icon { width: 22px; height: 21px; font-size: 10.5px; }
    .c3-trust-grid { padding: 5px 6px; }
    .c3-t-num { font-size: 12px; }
    .c3-trust-lbl { font-size: 9.5px; }
    .c3-bottom-card { padding: 14px 17px; }
    .c3-badge-rose, .c3-badge-ship { font-size: 12px; padding: 2.5px 9px; }
    .c3-deal-icon { width: 42px; height: 42px; font-size: 18px; }
    .c3-deal-title { font-size: 18px; }
    .c3-deal-subtitle { font-size: 12px; }
    .c3-deal-action { font-size: 13px; padding: 7px 15px; }
}

@media (min-width: 1400px) {
    .promo-card-zb { padding: 22px 24px; }
    .p-badge-amber, .p-badge-emerald, .p-badge-muted { font-size: 12px; padding: 4px 12px; }
    .p-pill-amber { font-size: 12.5px; padding: 4px 12px; }
    .p-icon-box { width: 46px; height: 46px; min-width: 46px; font-size: 18px; }
    .card-p-title { font-size: 17px; }
    .p-subtitle { font-size: 12.5px; }
    .p-coupon-action { padding: 8px 16px; border-radius: 12px; }
    .p-code-label { font-size: 12px; }
    .p-code-value { font-size: 15.5px; }
    .p-stats-bar { padding: 8px 18px; border-radius: 12px; }
    .p-stat-num { font-size: 14px; }
    .p-stat-txt { font-size: 11.5px; }
    .p-stat-sep { height: 18px; }
    .p-footer-text, .p-footer-link { font-size: 12px; }

    .c3-top-card { padding: 16px 18px; }
    .c3-badge-amber, .c3-badge-emerald, .c3-pill-muted { font-size: 12px; padding: 3.5px 10px; }
    .c3-pill-amber { font-size: 12px; padding: 3.5px 10px; }
    .c3-icon-box { width: 56px; height: 56px; font-size: 26px; margin-bottom: 6px; border-radius: 12px; }
    .c3-card-title { font-size: 18px; font-weight: 800; }
    .c3-card-desc { font-size: 13.5px; margin-top: 3px; }
    .c3-copy-pill { padding: 8px 14px; border-radius: 10px; }
    .c3-code-val { font-size: 14.5px; }
    .c3-btn-icon { width: 28px; height: 26px; font-size: 13px; border-radius: 7px; }
    .c3-trust-grid { padding: 8px 12px; border-radius: 10px; }
    .c3-trust-cell { gap: 5px; }
    .c3-t-num { font-size: 14.5px; }
    .c3-trust-lbl { font-size: 12px; }
    .c3-bottom-card { padding: 18px 22px; }
    .c3-badge-rose, .c3-badge-ship { font-size: 13.5px; padding: 4px 12px; }
    .c3-deal-icon { width: 54px; height: 54px; font-size: 26px; border-radius: 12px; }
    .c3-deal-title { font-size: 19px; }
    .c3-deal-subtitle { font-size: 13.5px; margin-top: 4px; }
    .c3-deal-action { font-size: 15px; padding: 10px 18px; border-radius: 999px; }
}

.coupon-copy-btn {
    transition: all 0.15s ease-in-out;
}
.coupon-copy-btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(251, 191, 36, 0.35);
}

@keyframes zbSlideIn {
    from {
        opacity: 0;
        transform: translateY(14px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
</style>

@push('scripts')
<script>
    (function () {
        function cleanupHeroSlider(el) {
            if (window.__heroSplide) {
                try {
                    window.__heroSplide.destroy(true);
                } catch (e) {}
                window.__heroSplide = null;
            }
            if (!el) {
                el = document.getElementById('heroSlider');
            }
            if (el) {
                el.classList.remove('is-initialized', 'is-active');
                el.querySelectorAll('.splide__pagination').forEach(function (p) {
                    p.remove();
                });
                el.querySelectorAll('.splide__slide--clone').forEach(function (c) {
                    c.remove();
                });
            }
        }

        function mountHeroSlider() {
            var el = document.getElementById('heroSlider');
            if (!el) return;
            if (typeof Splide === 'undefined') {
                setTimeout(mountHeroSlider, 50);
                return;
            }
            cleanupHeroSlider(el);
            var slides = el.querySelectorAll('.splide__list > .splide__slide');
            if (slides.length === 0) return;
            var isSingle = slides.length === 1;

            try {
                window.__heroSplide = new Splide(el, {
                    type: isSingle ? 'slide' : 'loop',
                    perPage: 1,
                    perMove: 1,
                    autoplay: !isSingle,
                    interval: 4500,
                    arrows: false,
                    pagination: !isSingle,
                    pauseOnHover: true,
                    speed: 600,
                    width: '100%',
                    autoHeight: false
                });
                window.__heroSplide.mount();
                el.querySelectorAll('.splide__slide--clone h1.hero-slide-title').forEach(function (h) {
                    h.outerHTML = '<div class="hero-slide-title">' + h.innerHTML + '</div>';
                });
            } catch (e) {}
        }

        if (!window.__heroSliderTurboBound) {
            window.__heroSliderTurboBound = true;
            document.addEventListener('turbo:load', mountHeroSlider);
            document.addEventListener('turbo:before-cache', function () {
                cleanupHeroSlider();
            });
            document.addEventListener('turbo:before-render', function () {
                cleanupHeroSlider();
            });
        }
        mountHeroSlider();
    })();

    function copyCouponCode(code, el) {
        if (navigator.clipboard) {
            navigator.clipboard.writeText(code).then(function () {
                if (typeof showToast === 'function') {
                    showToast('কুপন কোড "' + code + '" কপি হয়েছে!');
                }
                var btn = el.querySelector('.coupon-copy-btn');
                if (btn) {
                    var originalHtml = btn.innerHTML;
                    var hasSpan = btn.querySelector('span');
                    if (hasSpan) {
                        btn.innerHTML = '<i class="fa-solid fa-check me-1"></i><span>কপি</span>';
                    } else {
                        btn.innerHTML = '<i class="fa-solid fa-check"></i>';
                    }
                    setTimeout(function () {
                        btn.innerHTML = originalHtml;
                    }, 2000);
                }
            });
        } else if (typeof showToast === 'function') {
            showToast('কুপন কোড: ' + code);
        }
    }
</script>
@endpush