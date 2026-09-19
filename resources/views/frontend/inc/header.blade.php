<style>
.search-container { position: relative; }
.z-icon,
.brand-logo .z-icon,
.brand-logo:hover .z-icon {
    box-shadow: none !important;
}
.mobile-search-wrapper {
    margin-top: 8px;
    margin-bottom: 3px;
}
.mobile-search-box {
    position: relative;
    display: flex;
    align-items: center;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 9999px;
    height: 36px;
    padding: 0 12px 0 13px;
    transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
    box-shadow: 0 1px 2px rgba(15, 23, 42, 0.03);
}
.mobile-search-box:focus-within {
    background: #ffffff;
    border-color: #0f172a;
    box-shadow: 0 0 0 3px rgba(15, 23, 42, 0.08), 0 2px 6px rgba(15, 23, 42, 0.04);
}
.mobile-search-icon {
    font-size: 13px;
    color: #94a3b8;
    margin-right: 8px;
    flex-shrink: 0;
    transition: color 0.2s ease;
}
.mobile-search-box:focus-within .mobile-search-icon {
    color: #0f172a;
}
.mobile-search-input {
    flex: 1;
    min-width: 0;
    border: none !important;
    background: transparent !important;
    font-size: 12.5px;
    font-weight: 500;
    color: #0f172a;
    outline: none !important;
    box-shadow: none !important;
    padding: 0 !important;
    height: 100%;
    line-height: normal;
}
.mobile-search-input::placeholder {
    color: #94a3b8;
    font-weight: 400;
    font-size: 12px;
}
.mobile-search-input::-webkit-search-decoration,
.mobile-search-input::-webkit-search-cancel-button,
.mobile-search-input::-webkit-search-results-button,
.mobile-search-input::-webkit-search-results-decoration {
    -webkit-appearance: none;
    display: none;
}
.mobile-search-clear {
    display: none;
    background: none;
    border: none;
    padding: 0;
    margin-left: 6px;
    color: #94a3b8;
    font-size: 13.5px;
    cursor: pointer;
    line-height: 1;
    flex-shrink: 0;
    transition: color 0.15s ease;
}
.mobile-search-clear:hover,
.mobile-search-clear:focus {
    color: #0f172a;
    outline: none;
}
.mobile-search-clear.is-visible {
    display: inline-flex;
    align-items: center;
    justify-content: center;
}
.search-suggestions-dropdown {
    position: absolute;
    top: calc(100% + 6px);
    left: 0;
    right: 0;
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 16px;
    box-shadow: 0 20px 35px -5px rgba(15, 23, 42, 0.15), 0 0 0 1px rgba(0, 0, 0, 0.04);
    z-index: 1060;
    display: none;
    max-height: 420px;
    overflow-y: auto;
    scrollbar-width: thin;
    overscroll-behavior: contain;
}
.search-suggestions-dropdown.show {
    display: block;
    animation: searchFadeSlide 0.18s ease-out forwards;
}
@keyframes searchFadeSlide {
    from { opacity: 0; transform: translateY(-5px); }
    to { opacity: 1; transform: translateY(0); }
}
.search-suggestion-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 10px 14px;
    text-decoration: none;
    color: #1e293b;
    border-bottom: 1px solid #f1f5f9;
    transition: background 0.15s ease, color 0.15s ease;
}
.search-suggestion-item:last-child {
    border-bottom: none;
}
.search-suggestion-item:hover,
.search-suggestion-item.active {
    background-color: #f8fafc;
}
.search-suggestion-thumb {
    width: 44px;
    height: 44px;
    min-width: 44px;
    border-radius: 8px;
    object-fit: cover;
    background: #f1f5f9;
    border: 1px solid #e2e8f0;
}
.search-suggestion-title {
    font-size: 13px;
    font-weight: 600;
    color: #0f172a;
    line-height: 1.35;
    display: -webkit-box;
    -webkit-line-clamp: 1;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
.search-suggestion-price {
    font-size: 12.5px;
    font-weight: 700;
    color: #2563eb;
}
.search-suggestion-oldprice {
    font-size: 10.5px;
    color: #94a3b8;
    text-decoration: line-through;
}
.search-suggestion-badge {
    font-size: 10px;
    padding: 2px 6px;
    border-radius: 6px;
    background: #eff6ff;
    color: #2563eb;
    font-weight: 600;
}
.search-suggestion-footer {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    padding: 10px 14px;
    background: #f8fafc;
    border-top: 1px solid #e2e8f0;
    font-size: 12.5px;
    font-weight: 700;
    color: #2563eb;
    text-decoration: none;
    border-bottom-left-radius: 16px;
    border-bottom-right-radius: 16px;
    transition: background 0.15s ease;
}
.search-suggestion-footer:hover {
    background: #eff6ff;
    color: #1d4ed8;
}
.action-btn:focus,
.action-btn:focus-visible,
.action-btn:active {
    box-shadow: none !important;
    outline: none !important;
}
</style>

<header class="main-header sticky-top bg-white py-2 {{ request()->routeIs('login', 'register') ? 'd-none d-md-block' : '' }}" id="mainHeader" style="z-index: 1040; transition: box-shadow 0.2s ease, border-color 0.2s ease;">
    <div class="container d-flex align-items-center justify-content-between gap-3 position-relative">
        
        <div class="d-flex align-items-center gap-3 flex-shrink-0">
            @php
                $brandTitle = $settings['store_name'] ?? 'ZippyBD';
                $brandInitial = strtoupper(substr($brandTitle, 0, 1));
            @endphp
            <a href="{{ route('home') }}" class="brand-logo d-inline-flex align-items-center gap-2 text-decoration-none select-none" id="mainBrandLogo" title="{{ $brandTitle }} - {{ $settings['store_tagline'] ?? '' }}">
                <span class="z-icon">{{ $brandInitial }}</span>
                <span class="brand-name-wrap d-inline-flex align-items-center gap-1">
                    <span class="brand-name fw-bolder fs-4" id="brandTypingText">{{ $brandTitle }}</span>
                    <span class="brand-pulse-dot" title="অনলাইন স্টোর"></span>
                </span>
            </a>

            <div class="header-mega-wrapper d-none d-lg-block" id="headerMegaWrapper">
                <button type="button" class="btn btn-dark rounded-pill px-3.5 py-1.5 fw-bold d-flex align-items-center gap-2 shadow-sm btn-mega-trigger" id="megaMenuBtn" onclick="toggleMegaMenu(event)" style="background-color: #0f172a !important; color: #ffffff !important; border: 1px solid #0f172a; font-size: 13px;">
                    <i class="fa-solid fa-bars text-white"></i>
                    <span>Categories</span>
                    <i class="fa-solid fa-chevron-down ms-1 text-white" id="megaMenuChevron" style="font-size: 10px; transition: transform 0.25s ease;"></i>
                </button>
                @include('frontend.inc.mega-menu')
            </div>
        </div>

        <div class="search-container d-none d-lg-block flex-grow-1 min-w-0 position-relative" style="max-width: 680px;" id="desktopSearchContainer">
            <form action="{{ route('search') }}" method="GET" class="search-wrapper d-flex align-items-center bg-white border border-2 border-dark rounded-pill overflow-hidden" style="height: 42px;">
                <i class="fa-solid fa-magnifying-glass text-secondary ms-3 me-2 flex-shrink-0"></i>
                <input type="text" name="q" class="search-input flex-grow-1 border-0 bg-transparent px-2 small text-dark outline-none shadow-none" id="liveSearchInput" value="{{ request('q', '') }}" placeholder="প্রোডাক্টের বাংলা বা ইংরেজি নাম লিখে সার্চ করুন..." autocomplete="off">
                <button type="submit" class="btn btn-dark rounded-0 px-4 h-100 fw-bold small d-flex align-items-center justify-content-center flex-shrink-0" style="background-color: #0f172a; transform: none !important; box-shadow: none !important;">
                    <span>সার্চ</span>
                </button>
            </form>
            <div class="search-suggestions-dropdown" id="searchSuggestionsDropdown"></div>
        </div>

        <div class="header-actions d-flex align-items-center gap-3 flex-shrink-0">
            <a href="{{ route('order.history') }}" class="action-btn d-none d-xl-flex flex-column align-items-center text-secondary text-decoration-none group-icon" title="অর্ডার হিস্ট্রি">
                <i class="fa-duotone fa-solid fa-clock-rotate-left fs-5 mb-1 text-secondary fa-spin-pulse" style="--fa-animation-duration: 4s;"></i>
                <span style="font-size: 10px; font-weight: 600;">অর্ডার হিস্ট্রি</span>
            </a>

            <a href="{{ route('order.track') }}" class="action-btn d-none d-xl-flex flex-column align-items-center text-secondary text-decoration-none group-icon" title="অর্ডার ট্র্যাকিং">
                <i class="fa-duotone fa-solid fa-truck-fast fa-buzz"></i>
                <span style="font-size: 10px; font-weight: 600;">অর্ডার ট্র্যাকিং</span>
            </a>

            @auth
                <div class="dropdown">
                    <button class="btn btn-link p-0 d-flex align-items-center gap-1 text-decoration-none border-0" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <img src="{{ auth()->user()->avatar ?: 'https://ui-avatars.com/api/?name=' . urlencode(auth()->user()->name) . '&background=0f172a&color=ffffff&bold=true' }}" alt="{{ auth()->user()->name }}" class="rounded-circle border border-2 border-dark" style="width: 34px; height: 34px; object-fit: cover;">
                        <span class="d-none d-xl-inline fw-bold text-dark small text-truncate" style="max-width: 85px;">{{ auth()->user()->name }}</span>
                        <i class="fa-solid fa-chevron-down text-secondary" style="font-size: 10px;"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow-lg rounded-4 border-0 p-2 mt-2" style="min-width: 220px; z-index: 1050;">
                        <li class="px-3 py-2 border-bottom">
                            <span class="d-block fw-bold text-dark text-truncate">{{ auth()->user()->name }}</span>
                            <span class="d-block text-secondary small text-truncate">{{ auth()->user()->email }}</span>
                        </li>
                        <li>
                            <a class="dropdown-item py-2 d-flex align-items-center gap-2 rounded-3 mt-1" href="{{ route('customer.account') }}">
                                <i class="fa-duotone fa-solid fa-user text-primary" style="width: 18px;"></i>
                                <span>অ্যাকাউন্ট</span>
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item py-2 d-flex align-items-center gap-2 rounded-3" href="{{ route('order.history') }}">
                                <i class="fa-duotone fa-solid fa-bag-shopping text-warning" style="width: 18px;"></i>
                                <span>অর্ডার হিস্ট্রি</span>
                            </a>
                        </li>
                        <li><hr class="dropdown-divider my-1"></li>
                        <li>
                            <a class="dropdown-item py-2 text-danger d-flex align-items-center gap-2 rounded-3" href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('headerLogoutForm').submit();">
                                <i class="fa-solid fa-right-from-bracket" style="width: 18px;"></i>
                                <span>লগআউট</span>
                            </a>
                            <form id="headerLogoutForm" action="{{ route('logout') }}" method="POST" class="d-none">@csrf</form>
                        </li>
                    </ul>
                </div>
            @else
                <a href="{{ route('login') }}" class="action-btn d-none d-sm-flex flex-column align-items-center text-secondary text-decoration-none group-icon" title="লগইন">
                    <i class="fa-duotone fa-solid fa-user fs-5 mb-1 text-secondary fa-fade" style="--fa-animation-duration: 2.5s;"></i>
                    <span style="font-size: 10px; font-weight: 600;">লগইন</span>
                </a>
            @endauth

            <button type="button" class="btn btn-dark rounded-pill p-1 pe-3 d-flex align-items-center gap-2 border-0 shadow-sm flex-shrink-0 btn-native-cart" onclick="openCartDrawer()" aria-label="শপিং কার্ট" style="background-color: #0f172a;">
                <div class="rounded-circle bg-white text-dark d-flex align-items-center justify-content-center position-relative flex-shrink-0" style="width: 30px; height: 30px;">
                    <i class="fa-solid fa-bag-shopping fa-beat-fade" style="font-size: 13px; --fa-animation-duration: 2.5s;"></i>
                    <span class="cart-count-badge position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 9px; padding: 2px 5px;">0</span>
                </div>
                <span class="cart-price-text fw-bold text-white small text-nowrap">৳ 0</span>
            </button>
        </div>
    </div>

    <div class="d-block d-lg-none container position-relative mobile-search-wrapper">
        <form action="{{ route('search') }}" method="GET" class="mobile-search-box">
            <i class="fa-solid fa-magnifying-glass mobile-search-icon"></i>
            <input type="search" name="q" id="mobileSearchInput" class="mobile-search-input" value="{{ request('q', '') }}" placeholder="বাংলা বা ইংরেজিতে পণ্য সার্চ করুন..." autocomplete="off">
            <button type="button" class="mobile-search-clear" id="mobileSearchClear" aria-label="Clear search">
                <i class="fa-solid fa-circle-xmark"></i>
            </button>
        </form>
        <div class="search-suggestions-dropdown" id="mobileSearchSuggestions"></div>
    </div>
</header>

<script>
(function() {
    function initMobileSearchClear() {
        var input = document.getElementById('mobileSearchInput');
        var clearBtn = document.getElementById('mobileSearchClear');
        if (!input || !clearBtn) return;

        function updateState() {
            if (input.value && input.value.trim().length > 0) {
                clearBtn.classList.add('is-visible');
            } else {
                clearBtn.classList.remove('is-visible');
            }
        }

        input.addEventListener('input', updateState);
        clearBtn.addEventListener('click', function() {
            input.value = '';
            updateState();
            input.focus();
            var dd = document.getElementById('mobileSearchSuggestions');
            if (dd) {
                dd.classList.remove('show');
                dd.style.display = 'none';
            }
        });
        updateState();
    }

    if (!window.__mobileSearchClearBound) {
        window.__mobileSearchClearBound = true;
        document.addEventListener('turbo:load', initMobileSearchClear);
    }
    initMobileSearchClear();
})();
</script>
