<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <!-- Modularized Analytics & Pixel Engine -->
    {!! \App\Services\Marketing\MetaPixelService::renderHeadScript() !!}
    <script src="{{ asset('js/tracking.js') }}?v=1.1" data-turbo-track="reload" data-turbo-eval="false"></script>
    {!! \App\Services\Marketing\GtmService::renderHeadScript() !!}
    @stack('preload')
    
    @php
        $siteName = $settings['store_name'] ?? 'ZippyBD';
        $siteTagline = $settings['store_tagline'] ?? 'প্রিমিয়াম গ্যাজেট, মেকানিক্যাল কিবোর্ড ও লাইফস্টাইল স্টোর বাংলাদেশ';
        $defaultTitle = $siteName . ' - ' . $siteTagline;
        $defaultDesc = $settings['meta_description'] ?? ($siteName . ' - বাংলাদেশের বিশ্বস্ত অনলাইন শপ। মেকানিক্যাল কিবোর্ড, ডেস্ক সেটআপ এক্সেসরিজ, অডিও ডিভাইস ও প্রিমিয়াম গ্যাজেট কিনুন সবচেয়ে সাশ্রয়ী মূল্যে।');
    @endphp

    <title>@yield('title', $defaultTitle)</title>
    <meta name="description" content="@yield('meta_description', $defaultDesc)">
    <meta name="keywords" content="@yield('meta_keywords', 'gadget bd, mechanical keyboard bangladesh, desk setup bd, audio gadget, online shopping bangladesh')">
    <link rel="canonical" href="@yield('canonical', url()->current())">
    <meta name="robots" content="index, follow">

    <meta property="og:type" content="@yield('og_type', 'website')">
    <meta property="og:title" content="@yield('title', $defaultTitle)">
    <meta property="og:description" content="@yield('meta_description', $defaultDesc)">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:image" content="@yield('og_image', 'https://images.unsplash.com/photo-1518455027359-f3f8164ba6bd?auto=format&fit=crop&w=1200&q=85')">
    <meta property="og:site_name" content="{{ $siteName }}">

    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="@yield('title', $defaultTitle)">
    <meta name="twitter:description" content="@yield('meta_description', $defaultDesc)">
    <meta name="twitter:image" content="@yield('og_image', 'https://images.unsplash.com/photo-1518455027359-f3f8164ba6bd?auto=format&fit=crop&w=1200&q=85')">

    <script type="application/ld+json">
    {!! json_encode([
        '@'.'context' => 'https://schema.org',
        '@type' => 'Organization',
        'name' => $siteName,
        'url' => url('/'),
        'logo' => asset('favicon.ico'),
        'contactPoint' => [
            '@type' => 'ContactPoint',
            'telephone' => $settings['phone_number'] ?? '+8801700000000',
            'contactType' => 'customer service',
            'areaServed' => 'BD',
            'availableLanguage' => ['bn', 'en']
        ]
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
    </script>
    <script type="application/ld+json">
    {!! json_encode([
        '@'.'context' => 'https://schema.org',
        '@type' => 'WebSite',
        'name' => $siteName,
        'url' => url('/'),
        'potentialAction' => [
            '@type' => 'SearchAction',
            'target' => [
                '@type' => 'EntryPoint',
                'urlTemplate' => route('product.index') . '?q={search_term_string}'
            ],
            'query-input' => 'required name=search_term_string'
        ]
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
    </script>
    @stack('extra_meta')
    @stack('schema')
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="shortcut icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="apple-touch-icon" href="{{ asset('favicon.ico') }}">
    <meta name="theme-color" content="#0f172a">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="{{ $siteName }}">

    <meta name="turbo-cache-control" content="no-preview">

    <script src="https://cdn.jsdelivr.net/npm/@hotwired/turbo@7.3.0/dist/turbo.es2017-umd.js" defer data-turbo-track="reload"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" defer data-turbo-track="reload"></script>
    <script src="https://cdn.jsdelivr.net/npm/@splidejs/splide@4.1.4/dist/js/splide.min.js" defer data-turbo-track="reload"></script>
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js" defer data-turbo-track="reload"></script>
    <script src="{{ asset('lib/axios.min.js') }}?v=1.7.9" defer data-turbo-track="reload"></script>
    <script src="{{ asset('js/frontend.js') }}?v=4.2" defer data-turbo-track="reload"></script>
    <style>
        .turbo-progress-bar {
            height: 2.5px;
            background: linear-gradient(90deg, #38bdf8, #2563eb, #ff385c);
            box-shadow: 0 0 8px rgba(56, 189, 248, 0.6);
            z-index: 99999;
        }
        html, body {
            background-color: #f8fafc !important;
        }
    </style>

    <script data-turbo-eval="false">
        window.ZIPPY_BRAND_FALLBACK_SVG = "{{ asset('images/product-placeholder.svg') }}";
        window.addEventListener('error', function(e) {
            if (e.target && e.target.tagName === 'IMG') {
                if (e.target.dataset.hasFallback) return;
                e.target.dataset.hasFallback = '1';
                e.target.onerror = null;
                if (e.target.closest('#heroSlider') || e.target.classList.contains('banner-img')) {
                    e.target.src = '{{ asset('images/banner-placeholder.svg') }}';
                } else {
                    e.target.src = window.ZIPPY_BRAND_FALLBACK_SVG || '{{ asset('images/product-placeholder.svg') }}';
                }
            }
        }, true);
        window.ZippyTheme = {
            heroWords: {!! json_encode($themeSettings['hero_typing_words'] ?? ['Zippy BD', 'শপিং মানেই'], JSON_UNESCAPED_UNICODE) !!},
            primaryColor: "{{ $themeSettings['primary_color'] ?? '#0f172a' }}",
            accentColor: "{{ $themeSettings['accent_color'] ?? '#ff385c' }}",
            fontFamily: "{{ $themeSettings['font_family'] ?? 'Outfit' }}"
        };
    </script>

    <link rel="dns-prefetch" href="//cdn.jsdelivr.net">
    <link rel="preconnect" href="https://cdn.jsdelivr.net" crossorigin>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Anek+Bangla:wght@400;500;600;700;800&family=Hind+Siliguri:wght@400;500;600;700&family=Inter:wght@400;500;600;700;800&family=Manrope:wght@400;500;600;700;800&family=Mina:wght@400;700&family=Montserrat:wght@400;500;600;700;800&family=Noto+Sans+Bengali:wght@400;500;600;700;800&family=Outfit:wght@400;500;600;700;800;900&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Poppins:wght@400;500;600;700;800&family=Tiro+Bangla:ital@0;1&family=Urbanist:wght@400;500;600;700;800&display=swap" media="print" onload="this.media='all'">
    <noscript><link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Anek+Bangla:wght@400;500;600;700;800&family=Hind+Siliguri:wght@400;500;600;700&family=Inter:wght@400;500;600;700;800&family=Manrope:wght@400;500;600;700;800&family=Mina:wght@400;700&family=Montserrat:wght@400;500;600;700;800&family=Noto+Sans+Bengali:wght@400;500;600;700;800&family=Outfit:wght@400;500;600;700;800;900&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Poppins:wght@400;500;600;700;800&family=Tiro+Bangla:ital@0;1&family=Urbanist:wght@400;500;600;700;800&display=swap"></noscript>
    <link rel="stylesheet" href="{{ asset('lib/all.min.css') }}" media="print" onload="this.media='all'">
    <noscript><link rel="stylesheet" href="{{ asset('lib/all.min.css') }}"></noscript>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@splidejs/splide@4.1.4/dist/css/splide.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">


    <style>
        @font-face {
            font-family: 'Font Awesome 6 Free';
            font-style: normal;
            font-weight: 900;
            font-display: block;
            src: url("{{ asset('webfonts/fa-solid-900.woff2') }}") format("woff2");
        }
        @font-face {
            font-family: 'Font Awesome 6 Free';
            font-style: normal;
            font-weight: 400;
            font-display: block;
            src: url("{{ asset('webfonts/fa-regular-400.woff2') }}") format("woff2");
        }
        :root {
            --app-primary: {{ $themeSettings['primary_color'] ?? '#000000' }};
            --app-accent: {{ $themeSettings['accent_color'] ?? '#0f172a' }};
            --primary: {{ $themeSettings['primary_color'] ?? '#000000' }};
            --primary-dark: #000000;
            --accent: {{ $themeSettings['accent_color'] ?? '#0f172a' }};
            --brand-dark: #000000;
            --font-custom: '{{ $themeSettings['font_family'] ?? 'Outfit' }}', 'Hind Siliguri', 'Noto Sans Bengali', 'Anek Bangla', 'Inter', system-ui, sans-serif;
            --font-sans: var(--font-custom);
            --font-heading: var(--font-custom);
            --app-bg: #f8fafc;
            --app-surface: #ffffff;
            --app-glass: rgba(255, 255, 255, 0.88);
            --app-radius-sm: 0.5rem;
            --app-radius-md: 0.875rem;
            --app-radius-lg: 1.25rem;
            --app-radius-xl: 1.5rem;
            --app-shadow-soft: 0 10px 25px -5px rgba(0, 0, 0, 0.04), 0 8px 10px -6px rgba(0, 0, 0, 0.02);
            --app-shadow-card: 0 12px 30px -4px rgba(15, 23, 42, 0.06), 0 4px 6px -2px rgba(15, 23, 42, 0.02);
            --app-shadow-float: 0 20px 40px -10px rgba(15, 23, 42, 0.12);
        }

        * {
            scrollbar-width: none;
            -ms-overflow-style: none;
        }
        *::-webkit-scrollbar {
            display: none !important;
            width: 0 !important;
            height: 0 !important;
        }

        html, body {
            overflow-x: clip;
        }

        body {
            background-color: var(--app-bg);
            font-family: var(--font-custom);
            color: var(--app-primary);
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        .container, .container-fluid, .container-xxl, .container-xl, .container-lg, .container-md, .container-sm {
            max-width: 1600px !important;
            width: 100% !important;
            margin-left: auto !important;
            margin-right: auto !important;
            padding-left: clamp(8px, 2vw, 24px) !important;
            padding-right: clamp(8px, 2vw, 24px) !important;
        }

        .main-header {
            background: var(--app-glass) !important;
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border-bottom: none !important;
            box-shadow: none !important;
            transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .main-header.is-scrolled {
            box-shadow: 0 4px 20px -2px rgba(0, 0, 0, 0.06) !important;
            border-bottom: 1px solid rgba(226, 232, 240, 0.7) !important;
        }

        .card, .product-card, .promo-card, .category-card {
            border: none !important;
            box-shadow: var(--app-shadow-card) !important;
            border-radius: var(--app-radius-lg) !important;
            transition: transform 0.25s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.25s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .product-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--app-shadow-float) !important;
        }

        .btn, .nav-app-item, .product-card, .category-pill-item {
            transition: transform 0.15s ease, opacity 0.15s ease;
        }
        .btn:active, .nav-app-item:active, .category-pill-item:active {
            transform: scale(0.96) !important;
        }

        .offcanvas-bottom.native-bottom-sheet {
            border-radius: 1.5rem 1.5rem 0 0 !important;
            border: none !important;
            box-shadow: 0 -15px 40px rgba(0, 0, 0, 0.15) !important;
            max-height: 85vh;
        }
        .sheet-drag-handle {
            width: 44px;
            height: 5px;
            background-color: #cbd5e1;
            border-radius: 9999px;
            margin: 10px auto 14px auto;
        }

        .mobile-bottom-nav {
            background: rgba(255, 255, 255, 0.92) !important;
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            box-shadow: 0 -4px 25px rgba(0, 0, 0, 0.08) !important;
            border-top: 1px solid rgba(226, 232, 240, 0.6) !important;
            z-index: 1040;
            padding-bottom: env(safe-area-inset-bottom, 0px);
        }

        .native-toast-pill {
            opacity: 0;
            transform: translateY(30px);
            transition: all 0.35s cubic-bezier(0.2, 0.8, 0.2, 1);
        }
        .native-toast-pill.show-toast {
            opacity: 1;
            transform: translateY(0);
        }
        .native-toast-pill.hide-toast {
            opacity: 0;
            transform: translateY(30px);
        }
        
        .glass-toast {
            background: rgba(15, 23, 42, 0.8) !important;
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.15) !important;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2) !important;
        }

        .modal-backdrop.show,
        .offcanvas-backdrop.show {
            opacity: 1 !important;
            background-color: rgba(15, 23, 42, 0.4) !important;
            backdrop-filter: blur(6px);
            -webkit-backdrop-filter: blur(6px);
        }

        html.scroll-locked,
        body.scroll-locked {
            overflow: hidden !important;
            touch-action: none !important;
            overscroll-behavior: none !important;
        }

        .offcanvas.show,
        .offcanvas-body,
        .sheet-sidebar,
        .sheet-content-area,
        .modal.show,
        .modal-body,
        .custom-qv-container,
        .custom-qv-scroll-body,
        .zippy-chat-window,
        .zippy-chat-body,
        .zippy-checkout-drawer,
        .zippy-checkout-body {
            touch-action: pan-y !important;
            overscroll-behavior: contain !important;
        }

        .native-app-toast-container {
            bottom: 24px;
        }

        @media (max-width: 991.98px) {
            body {
                padding-bottom: calc(76px + env(safe-area-inset-bottom, 0px));
            }
            body:has(.mobile-floating-action-sheet),
            body:has(.zk-mobile-bottom-bar) {
                padding-bottom: 0 !important;
            }
            .native-app-toast-container {
                bottom: calc(85px + env(safe-area-inset-bottom, 0px));
                width: 100%;
                display: flex;
                justify-content: center;
                padding-left: 16px;
                padding-right: 16px;
            }
        }
    </style>

    <link rel="stylesheet" href="{{ asset('css/frontend.css') }}?v=4.0">

    @stack('styles')
</head>
<body class="bg-light text-dark font-sans min-vh-100 d-flex flex-column">
    <svg xmlns="http://www.w3.org/2000/svg" style="display: none;" aria-hidden="true">
        <symbol id="cart-check-icon" viewBox="0 0 16 16" fill="none">
            <path d="M3.5 8.5L6.5 11.5L12.5 4.5" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/>
        </symbol>
    </svg>
    <div data-turbo-permanent id="marketing-noscript-container">
        {!! \App\Services\Marketing\GtmService::renderBodyScript() !!}
        {!! \App\Services\Marketing\MetaPixelService::renderBodyScript() !!}
    </div>
    @include('frontend.inc.topbar')
    @include('frontend.inc.header')
    @include('frontend.inc.nav-categories')

    <main class="flex-grow-1">
        @yield('content')
    </main>

    @include('frontend.inc.footer')
    @if(!request()->routeIs('product.show') && !request()->is('product/*') && !request()->routeIs('checkout') && !request()->routeIs('checkout.*') && !request()->is('checkout*'))
        @include('frontend.inc.bottom-nav')
    @endif
    <div id="cart-drawer-container" data-turbo-permanent></div>
    <div id="bottom-sheet-container" data-turbo-permanent></div>
    <div id="quick-view-container" data-turbo-permanent></div>
    <div id="chatbot-container" data-turbo-permanent>@include('frontend.inc.chatbot')</div>
    <div id="recent-sales-container" data-turbo-permanent></div>


    <div class="native-app-toast-container position-fixed start-50 translate-middle-x p-3" style="z-index: 1150; pointer-events: none;">
        <div id="nativeAppToast" class="native-toast-pill glass-toast d-none align-items-center gap-3 px-4 py-3 rounded-pill" style="pointer-events: auto;">
            <div class="toast-icon-box rounded-circle bg-success text-white d-flex align-items-center justify-content-center flex-shrink-0" style="width: 24px; height: 24px; font-size: 12px;">
                <i class="fa-solid fa-check"></i>
            </div>
            <span id="toastMsg" class="fw-bold text-white font-heading mb-0" style="font-size: 14px;">সফলভাবে যুক্ত হয়েছে</span>
        </div>

        <div id="nativeUndoToast" class="native-toast-pill glass-toast d-none flex-column p-4 rounded-4" style="pointer-events: auto; min-width: 320px;">
            <div class="d-flex align-items-center justify-content-between gap-3 mb-2">
                <div class="d-flex align-items-center gap-2">
                    <div class="toast-icon-box rounded-circle bg-danger text-white d-flex align-items-center justify-content-center" style="width: 24px; height: 24px; font-size: 12px;">
                        <i class="fa-solid fa-trash-can"></i>
                    </div>
                    <span id="undoToastMsg" class="small fw-bold text-white font-heading">আইটেম সরানো হয়েছে</span>
                </div>
                <button type="button" class="btn btn-sm btn-outline-danger text-white border-danger rounded-pill px-3 py-0.5 fw-bold font-heading" onclick="undoRemoveCartItem()">
                    UNDO
                </button>
            </div>
            <div class="progress rounded-pill bg-white bg-opacity-20" style="height: 3px;">
                <div class="progress-bar bg-danger rounded-pill" id="undoToastProgress" style="width: 100%;"></div>
            </div>
        </div>
    </div>


    @stack('scripts')
    <script data-turbo-eval="false">
        window.alert = function(msg) {
            if (typeof showToast === 'function') {
                showToast(msg, 'error');
            } else if (typeof window.showToast === 'function') {
                window.showToast(msg, 'error');
            } else {
                console.warn(msg);
            }
        };

        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js').catch(() => {});
            });
        }

        function initBootstrapComponents() {
            if (typeof bootstrap === 'undefined') return;

            document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => {
                bootstrap.Tooltip.getOrCreateInstance(el);
            });

            document.querySelectorAll('[data-bs-toggle="popover"]').forEach(el => {
                bootstrap.Popover.getOrCreateInstance(el);
            });

            document.querySelectorAll('[data-bs-toggle="dropdown"]').forEach(el => {
                bootstrap.Dropdown.getOrCreateInstance(el);
            });
        }

        document.addEventListener('turbo:click', () => {
            if (document.activeElement && typeof document.activeElement.blur === 'function') {
                document.activeElement.blur();
            }
            if (typeof window.unlockPageScroll === 'function') {
                window.unlockPageScroll(true);
            }
            document.documentElement.classList.remove('scroll-locked');
            document.body.classList.remove('scroll-locked', 'modal-open', 'overflow-hidden', 'qv-modal-open', 'offcanvas-open-locked');
            document.documentElement.style.overflow = '';
            document.documentElement.style.touchAction = '';
            document.documentElement.removeAttribute('style');
            document.body.style.overflow = '';
            document.body.style.touchAction = '';
            document.body.removeAttribute('style');
        });

        document.addEventListener('turbo:load', () => {

            if (typeof window.unlockPageScroll === 'function') {
                window.unlockPageScroll(true);
            }
            document.documentElement.classList.remove('scroll-locked');
            document.body.classList.remove('scroll-locked', 'modal-open', 'overflow-hidden', 'qv-modal-open', 'offcanvas-open-locked');
            document.documentElement.style.overflow = '';
            document.documentElement.style.touchAction = '';
            document.documentElement.removeAttribute('style');
            document.body.style.overflow = '';
            document.body.style.touchAction = '';
            document.body.removeAttribute('style');
            document.querySelectorAll('.offcanvas-backdrop, .modal-backdrop').forEach(el => el.remove());
            initBootstrapComponents();
        });

        document.addEventListener('turbo:before-cache', () => {
            if (document.activeElement && typeof document.activeElement.blur === 'function') {
                document.activeElement.blur();
            }

            if (typeof closeQuickView === 'function') closeQuickView();

            if (typeof bootstrap !== 'undefined') {
                document.querySelectorAll('.offcanvas.show').forEach(el => {
                    const inst = bootstrap.Offcanvas.getInstance(el);
                    if (inst) {
                        try { inst.hide(); } catch (e) {}
                    }
                });

                document.querySelectorAll('.modal.show').forEach(el => {
                    const inst = bootstrap.Modal.getInstance(el);
                    if (inst) {
                        try { inst.hide(); } catch (e) {}
                    }
                });

                document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => {
                    const tip = bootstrap.Tooltip.getInstance(el);
                    if (tip) tip.dispose();
                });

                document.querySelectorAll('[data-bs-toggle="popover"]').forEach(el => {
                    const pop = bootstrap.Popover.getInstance(el);
                    if (pop) pop.dispose();
                });
            }

            document.querySelectorAll('.offcanvas.show, .modal.show').forEach(el => {
                el.classList.remove('show', 'showing');
                el.setAttribute('aria-hidden', 'true');
                el.style.visibility = '';
            });

            document.querySelectorAll('.offcanvas-backdrop, .modal-backdrop').forEach(el => {
                el.remove();
            });

            if (typeof window.unlockPageScroll === 'function') {
                window.unlockPageScroll(true);
            }
            document.documentElement.classList.remove('scroll-locked');
            document.body.classList.remove('scroll-locked', 'modal-open', 'overflow-hidden', 'qv-modal-open', 'offcanvas-open-locked');
            document.documentElement.style.overflow = '';
            document.documentElement.style.touchAction = '';
            document.documentElement.removeAttribute('style');
            document.body.removeAttribute('style');

            const toastEl = document.getElementById('nativeAppToast');
            if (toastEl) {
                toastEl.classList.remove('show-toast', 'd-flex');
                toastEl.classList.add('d-none', 'hide-toast');
            }

            const undoToastEl = document.getElementById('nativeUndoToast');
            if (undoToastEl) {
                undoToastEl.classList.remove('show-toast', 'd-flex');
                undoToastEl.classList.add('d-none', 'hide-toast');
            }

            const salesToastEl = document.getElementById('recentSalesToastWrapper') || document.getElementById('recentSalesToast');
            if (salesToastEl) {
                salesToastEl.classList.remove('is-visible', 'show');
                salesToastEl.style.display = 'none';
            }

            const heroSliderEl = document.getElementById('heroSlider');
            if (heroSliderEl) {
                if (window.__heroSplide) {
                    try { window.__heroSplide.destroy(true); } catch(e) {}
                    window.__heroSplide = null;
                }
                heroSliderEl.querySelectorAll('.splide__pagination').forEach(el => el.remove());
                heroSliderEl.querySelectorAll('.splide__slide--clone').forEach(el => el.remove());
                heroSliderEl.classList.remove('is-initialized', 'is-active');
            }

            if (window.mainSwiper && typeof window.mainSwiper.destroy === 'function') {
                try { window.mainSwiper.destroy(true, true); } catch (e) {}
                window.mainSwiper = null;
            }
            if (window.thumbSwiper && typeof window.thumbSwiper.destroy === 'function') {
                try { window.thumbSwiper.destroy(true, true); } catch (e) {}
                window.thumbSwiper = null;
            }
            if (window.__liveViewersInterval) {
                clearInterval(window.__liveViewersInterval);
                window.__liveViewersInterval = null;
            }
            document.querySelectorAll('.zk-ss-dropdown, .zk-ss-container, .select2-container').forEach(el => el.remove());
        });

        document.addEventListener('turbo:before-render', () => {
            if (window.__heroSplide) {
                try { window.__heroSplide.destroy(true); } catch(e) {}
                window.__heroSplide = null;
            }
            if (window.mainSwiper && typeof window.mainSwiper.destroy === 'function') {
                try { window.mainSwiper.destroy(true, true); } catch (e) {}
                window.mainSwiper = null;
            }
            if (window.thumbSwiper && typeof window.thumbSwiper.destroy === 'function') {
                try { window.thumbSwiper.destroy(true, true); } catch (e) {}
                window.thumbSwiper = null;
            }
            if (window.__liveViewersInterval) {
                clearInterval(window.__liveViewersInterval);
                window.__liveViewersInterval = null;
            }
            if (document.activeElement && typeof document.activeElement.blur === 'function') {
                document.activeElement.blur();
            }
            if (typeof closeQuickView === 'function') closeQuickView();
            document.querySelectorAll('.offcanvas.show, .modal.show').forEach(el => {
                el.classList.remove('show');
            });
            document.querySelectorAll('.offcanvas-backdrop, .modal-backdrop').forEach(el => {
                el.remove();
            });
            document.querySelectorAll('.zk-ss-dropdown, .zk-ss-container, .select2-container').forEach(el => el.remove());
            if (typeof window.unlockPageScroll === 'function') {
                window.unlockPageScroll(true);
            }
            document.documentElement.classList.remove('scroll-locked');
            document.body.classList.remove('scroll-locked', 'modal-open', 'overflow-hidden', 'qv-modal-open', 'offcanvas-open-locked');
            document.documentElement.style.overflow = '';
            document.documentElement.style.touchAction = '';
            document.documentElement.removeAttribute('style');
            document.body.removeAttribute('style');
        });
    </script>
</body>
</html>
