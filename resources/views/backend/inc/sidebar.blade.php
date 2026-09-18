@php
    $adminId = (int) session('admin_id', 0);
    $rbac = app(\App\Services\Rbac\PermissionService::class);
    $isSuperAdmin = $adminId > 0 && $rbac->isSuperAdmin($adminId);
    $adminPerms = (!$isSuperAdmin && $adminId > 0) ? $rbac->getUserEffectivePermissionNames($adminId) : [];
    $can = function (string $perm) use ($isSuperAdmin, $adminPerms) {
        return $isSuperAdmin || in_array($perm, $adminPerms);
    };

    $pendingOrdersCount = \Illuminate\Support\Facades\DB::table('orders')->where('order_status', 'pending')->count();
    $flaggedFraudCount = \Illuminate\Support\Facades\DB::table('orders')->where('fraud_status', 'flagged_fraud')->count();
    $lowStockCount = \Illuminate\Support\Facades\DB::table('products')->where('stock_qty', '<', 10)->count();
    $pendingReviewsCount = \Illuminate\Support\Facades\DB::table('product_reviews')->where('status', 'pending')->count();
    $pendingDemandsCount = \Illuminate\Support\Facades\DB::table('product_demands')->where('status', 'pending')->count();
@endphp
<aside class="sidebar" id="sidebar">
    <div class="sidebar-brand">
        <a href="{{ route('admin.dashboard') }}" class="brand-link d-flex align-items-center text-decoration-none overflow-hidden flex-grow-1" title="ZippyBD Dashboard">
            <div class="brand-badge">
                <i class="fa-solid fa-bolt-lightning"></i>
            </div>
            <div class="brand-meta d-flex flex-column">
                <div class="d-flex align-items-center gap-1.5">
                    <span class="brand-text">Zippy<span class="brand-text-accent">BD</span></span>
                    <span class="brand-badge-pill" style="margin-left: 10px;">PRO</span>
                </div>
                <span class="brand-subtitle">Control Center</span>
            </div>
        </a>
        <button type="button" class="sidebar-close-btn d-lg-none" onclick="closeAdminSidebar()" title="Close Sidebar" style="min-width: 44px; min-height: 44px; display: inline-flex; align-items: center; justify-content: center; font-size: 1.25rem;">
            <i class="fa-solid fa-xmark"></i>
        </button>
    </div>

    <div class="px-3 pt-2 pb-1 d-lg-none">
        <a href="{{ route('home') }}" target="_blank" class="btn btn-sm btn-outline-success w-100 d-flex align-items-center justify-content-center gap-2 rounded-3 py-2 fw-semibold text-decoration-none">
            <i class="fa-solid fa-store"></i>
            <span>Open Live Store</span>
            <i class="fa-solid fa-arrow-up-right-from-square fa-xs"></i>
        </a>
    </div>

    <div class="nav-section">
        <div class="nav-group">
            <div class="nav-group-header">
                <i class="fa-solid fa-compass nav-group-icon"></i>
                <span>Core Overview</span>
            </div>
            <div class="nav-group-links">
                <a href="{{ route('admin.dashboard') }}" class="nav-linkx {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" title="Dashboard">
                    <b><i class="fa-solid fa-chart-pie"></i></b>
                    <span class="nav-text">Dashboard</span>
                </a>
            </div>
        </div>

        @if($can('admin.orders.index') || $can('admin.fraud.index') || $can('admin.logistics.index') || $can('admin.refunds.index'))
            <div class="nav-group">
                <div class="nav-group-header">
                    <i class="fa-solid fa-cart-flatbed nav-group-icon"></i>
                    <span>Sales & Fulfillment</span>
                </div>
                <div class="nav-group-links">
                    @if($can('admin.orders.index'))
                        <a href="{{ route('admin.orders.index') }}" class="nav-linkx {{ request()->routeIs('admin.orders.*') ? 'active' : '' }}" title="Customer Orders">
                            <b><i class="fa-solid fa-boxes-stacked"></i></b>
                            <span class="nav-text">Customer Orders</span>
                            @if($pendingOrdersCount > 0)
                                <span class="badge bg-warning text-dark rounded-pill px-2 py-0.5 small ms-auto">{{ $pendingOrdersCount }}</span>
                            @endif
                        </a>
                    @endif

                    @if($can('admin.abandoned.index'))
                        <a href="{{ route('admin.abandoned.index') }}" class="nav-linkx {{ request()->routeIs('admin.abandoned.*') ? 'active' : '' }}" title="Abandoned Carts">
                            <b><i class="fa-solid fa-cart-arrow-down"></i></b>
                            <span class="nav-text">Abandoned Carts</span>
                        </a>
                    @endif

                    @if($can('admin.fraud.index'))
                        <a href="{{ route('admin.fraud.index') }}" class="nav-linkx {{ request()->routeIs('admin.fraud.*') ? 'active' : '' }}" title="Fraud Risk Engine">
                            <b><i class="fa-solid fa-shield-halved"></i></b>
                            <span class="nav-text">Fraud Risk Engine</span>
                            @if($flaggedFraudCount > 0)
                                <span class="badge bg-danger rounded-pill px-2 py-0.5 small ms-auto">{{ $flaggedFraudCount }}</span>
                            @endif
                        </a>
                    @endif

                    @if($can('admin.logistics.index'))
                        <a href="{{ route('admin.logistics.index') }}" class="nav-linkx {{ request()->routeIs('admin.logistics.*') ? 'active' : '' }}" title="Courier Logistics">
                            <b><i class="fa-solid fa-truck-fast"></i></b>
                            <span class="nav-text">Courier Logistics</span>
                        </a>
                    @endif

                    @if($can('admin.refunds.index'))
                        <a href="{{ route('admin.refunds.index') }}" class="nav-linkx {{ request()->routeIs('admin.refunds.*') ? 'active' : '' }}" title="Returns & Refunds (RMA)">
                            <b><i class="fa-solid fa-rotate-left"></i></b>
                            <span class="nav-text">Returns & Refunds</span>
                        </a>
                    @endif
                </div>
            </div>
        @endif

        @if($can('admin.products.index') || $can('admin.categories.index') || $can('admin.inventory.index') || $can('admin.banners.index'))
            <div class="nav-group">
                <div class="nav-group-header">
                    <i class="fa-solid fa-warehouse nav-group-icon"></i>
                    <span>Catalog & Inventory</span>
                </div>
                <div class="nav-group-links">
                    @if($can('admin.products.index'))
                        <a href="{{ route('admin.products.index') }}" class="nav-linkx {{ request()->routeIs('admin.products.*') && !request()->routeIs('admin.products.ai*') ? 'active' : '' }}" title="Manage Products">
                            <b><i class="fa-solid fa-box-open"></i></b>
                            <span class="nav-text">Manage Products</span>
                        </a>
                    @endif

                    @if($can('admin.products.ai') || $can('admin.products.index') || $isSuperAdmin)
                        <a href="{{ route('admin.products.ai') }}" class="nav-linkx {{ request()->routeIs('admin.products.ai*') ? 'active' : '' }}" title="AI Product Generator">
                            <b><i class="fa-solid fa-wand-magic-sparkles text-primary"></i></b>
                            <span class="nav-text">AI Product Generator</span>
                            <span class="badge bg-primary-subtle text-primary rounded-pill px-2 py-0.5 small ms-auto">AI</span>
                        </a>
                    @endif

                    @if($can('admin.products.index') || $isSuperAdmin)
                        <a href="{{ route('admin.product_demands.index') }}" class="nav-linkx {{ request()->routeIs('admin.product_demands.*') ? 'active' : '' }}" title="Customer Demands">
                            <b><i class="fa-solid fa-lightbulb text-warning"></i></b>
                            <span class="nav-text">Customer Demands</span>
                            @if($pendingDemandsCount > 0)
                                <span class="badge bg-danger rounded-pill px-2 py-0.5 small ms-auto">{{ $pendingDemandsCount }}</span>
                            @endif
                        </a>
                    @endif

                    @if($can('admin.inventory.index'))
                        <a href="{{ route('admin.inventory.index') }}" class="nav-linkx {{ request()->routeIs('admin.inventory.*') ? 'active' : '' }}" title="Stock & Suppliers">
                            <b><i class="fa-solid fa-warehouse"></i></b>
                            <span class="nav-text">Stock & Suppliers</span>
                            @if($lowStockCount > 0)
                                <span class="badge bg-warning text-dark rounded-pill px-2 py-0.5 small ms-auto">{{ $lowStockCount }} Low</span>
                            @endif
                        </a>
                    @endif

                    @if($can('admin.categories.index'))
                        <a href="{{ route('admin.categories.index') }}" class="nav-linkx {{ request()->routeIs('admin.categories.*') ? 'active' : '' }}" title="Categories">
                            <b><i class="fa-solid fa-layer-group"></i></b>
                            <span class="nav-text">Categories</span>
                        </a>
                    @endif

                    @if($can('admin.banners.index'))
                        <a href="{{ route('admin.banners.index') }}" class="nav-linkx {{ request()->routeIs('admin.banners.*') ? 'active' : '' }}" title="Hero Banners & Sliders">
                            <b><i class="fa-solid fa-image"></i></b>
                            <span class="nav-text">Hero Banners</span>
                        </a>
                    @endif

                    @if($can('admin.settings.index') || $isSuperAdmin)
                        <a href="{{ route('admin.settings.index') }}#promoCard3Settings" class="nav-linkx" title="Promo Booster Card">
                            <b><i class="fa-solid fa-gift text-warning"></i></b>
                            <span class="nav-text">Promo Booster Card</span>
                        </a>
                    @endif
                </div>
            </div>
        @endif

        @if($can('admin.customers.index') || $can('admin.abandoned.index') || $can('admin.coupons.index') || $can('admin.support.index') || $can('admin.popups.index') || $isSuperAdmin)
            <div class="nav-group">
                <div class="nav-group-header">
                    <i class="fa-solid fa-users-gear nav-group-icon"></i>
                    <span>CRM & Marketing</span>
                </div>
                <div class="nav-group-links">
                    @if($can('admin.customers.index'))
                        <a href="{{ route('admin.customers.index') }}" class="nav-linkx {{ request()->routeIs('admin.customers.*') || request()->routeIs('admin.crm.*') ? 'active' : '' }}" title="Customer 360">
                            <b><i class="fa-solid fa-users"></i></b>
                            <span class="nav-text">Customer 360</span>
                        </a>
                    @endif


                    @if($can('admin.coupons.index'))
                        <a href="{{ route('admin.coupons.index') }}" class="nav-linkx {{ request()->routeIs('admin.coupons.*') ? 'active' : '' }}" title="Coupons & Discounts">
                            <b><i class="fa-solid fa-ticket"></i></b>
                            <span class="nav-text">Coupons & Discounts</span>
                        </a>
                    @endif

                    @if($can('admin.products.index') || $isSuperAdmin)
                        <a href="{{ route('admin.bumps.index') }}" class="nav-linkx {{ request()->routeIs('admin.bumps.*') ? 'active' : '' }}" title="Order Bump Offers">
                            <b><i class="fa-solid fa-tags"></i></b>
                            <span class="nav-text">Order Bumps</span>
                        </a>
                    @endif

                    @if($can('admin.popups.index') || $can('admin.settings.index') || $isSuperAdmin)
                        <a href="{{ route('admin.popups.index') }}" class="nav-linkx {{ request()->routeIs('admin.popups.*') ? 'active' : '' }}" title="Promo Popups">
                            <b><i class="fa-solid fa-window-restore"></i></b>
                            <span class="nav-text">Promo Popups</span>
                        </a>
                    @endif

                    @if($can('admin.support.index'))
                        <a href="{{ route('admin.support.index') }}" class="nav-linkx {{ request()->routeIs('admin.support.*') ? 'active' : '' }}" title="Customer Support">
                            <b><i class="fa-solid fa-headset"></i></b>
                            <span class="nav-text">Customer Support</span>
                        </a>
                    @endif
                </div>
            </div>
        @endif

        @if($can('admin.pages.index') || $can('admin.reviews.index'))
            <div class="nav-group">
                <div class="nav-group-header">
                    <i class="fa-solid fa-pen-nib nav-group-icon"></i>
                    <span>CMS & Engagement</span>
                </div>
                <div class="nav-group-links">
                    @if($can('admin.pages.index'))
                        <a href="{{ route('admin.pages.index') }}" class="nav-linkx {{ request()->routeIs('admin.pages.*') ? 'active' : '' }}" title="Custom Pages" data-turbo="false">
                            <b><i class="fa-solid fa-file-lines"></i></b>
                            <span class="nav-text">Custom Pages</span>
                        </a>
                    @endif

                    @if($can('admin.reviews.index'))
                        <a href="{{ route('admin.reviews.index') }}" class="nav-linkx {{ request()->routeIs('admin.reviews.*') ? 'active' : '' }}" title="Product Reviews">
                            <b><i class="fa-solid fa-star-half-stroke"></i></b>
                            <span class="nav-text">Product Reviews</span>
                            @if($pendingReviewsCount > 0)
                                <span class="badge bg-info text-dark rounded-pill px-2 py-0.5 small ms-auto">{{ $pendingReviewsCount }}</span>
                            @endif
                        </a>
                    @endif
                </div>
            </div>
        @endif

        @if($can('admin.reports.index') || $can('admin.audit.index'))
            <div class="nav-group">
                <div class="nav-group-header">
                    <i class="fa-solid fa-chart-mixed nav-group-icon"></i>
                    <span>Analytics & Logs</span>
                </div>
                <div class="nav-group-links">
                    @if($can('admin.reports.index'))
                        <a href="{{ route('admin.reports.index') }}" class="nav-linkx {{ request()->routeIs('admin.reports.*') ? 'active' : '' }}" title="Financial Analytics">
                            <b><i class="fa-solid fa-chart-line"></i></b>
                            <span class="nav-text">Financial Analytics</span>
                        </a>
                    @endif

                    @if($can('admin.audit.index'))
                        <a href="{{ route('admin.audit.index') }}" class="nav-linkx {{ request()->routeIs('admin.audit.*') ? 'active' : '' }}" title="System Audit Trail">
                            <b><i class="fa-solid fa-clock-rotate-left"></i></b>
                            <span class="nav-text">System Audit Trail</span>
                        </a>
                    @endif
                </div>
            </div>
        @endif

        @if($can('admin.settings.index') || $can('admin.rbac.index') || $isSuperAdmin)
            <div class="nav-group">
                <div class="nav-group-header">
                    <i class="fa-solid fa-sliders nav-group-icon"></i>
                    <span>Settings & Security</span>
                </div>
                <div class="nav-group-links">
                    @if($can('admin.settings.index'))
                        <a href="{{ route('admin.settings.index') }}" class="nav-linkx {{ request()->routeIs('admin.settings.index') ? 'active' : '' }}" title="Store Settings">
                            <b><i class="fa-solid fa-store"></i></b>
                            <span class="nav-text">Store Settings</span>
                        </a>
                    @endif

                    @if($can('admin.theme_settings.index') || $isSuperAdmin)
                        <a href="{{ route('admin.theme_settings.index') }}" class="nav-linkx {{ request()->routeIs('admin.theme_settings.*') ? 'active' : '' }}" title="Theme & Appearance">
                            <b><i class="fa-solid fa-palette"></i></b>
                            <span class="nav-text">Theme & Appearance</span>
                        </a>
                    @endif

                    @if($can('admin.rbac.index'))
                        <a href="{{ route('admin.rbac.index') }}" class="nav-linkx {{ request()->routeIs('admin.rbac.*') ? 'active' : '' }}" title="Staff & Permissions">
                            <b><i class="fa-solid fa-user-shield"></i></b>
                            <span class="nav-text">Staff & Permissions</span>
                        </a>
                    @endif

                    @if($can('admin.fraud.index') || $isSuperAdmin)
                        <a href="{{ route('admin.blocklist.index') }}" class="nav-linkx {{ request()->routeIs('admin.blocklist.*') ? 'active' : '' }}" title="Security Blocklist">
                            <b><i class="fa-solid fa-ban text-danger"></i></b>
                            <span class="nav-text">Security Blocklist</span>
                        </a>
                    @endif
                </div>
            </div>
        @endif

        @if($can('admin.settings.ai') || $can('admin.settings.couriers') || $can('admin.settings.payments') || $can('admin.settings.fraud') || $can('admin.settings.gtm') || $can('admin.settings.meta') || $can('admin.sms.index') || $can('admin.notification_settings.index') || $can('admin.social_settings.index') || $isSuperAdmin)
            <div class="nav-group">
                <div class="nav-group-header">
                    <i class="fa-solid fa-network-wired nav-group-icon"></i>
                    <span>Integrations & APIs</span>
                </div>
                <div class="nav-group-links">
                    @if($can('admin.settings.ai') || $isSuperAdmin)
                        <a href="{{ route('admin.settings.ai') }}" class="nav-linkx {{ request()->routeIs('admin.settings.ai*') ? 'active' : '' }}" title="AI Integrations Hub">
                            <b><i class="fa-solid fa-brain text-info"></i></b>
                            <span class="nav-text">AI Integrations Hub</span>
                        </a>
                    @endif

                    @if($can('admin.settings.couriers') || $isSuperAdmin)
                        <a href="{{ route('admin.settings.couriers') }}" class="nav-linkx {{ request()->routeIs('admin.settings.couriers') ? 'active' : '' }}" title="Courier API Setup">
                            <b><i class="fa-solid fa-truck-fast"></i></b>
                            <span class="nav-text">Courier API Setup</span>
                        </a>
                    @endif

                    @if($can('admin.settings.payments') || $isSuperAdmin)
                        <a href="{{ route('admin.settings.payments') }}" class="nav-linkx {{ request()->routeIs('admin.settings.payments') ? 'active' : '' }}" title="Payment Gateways">
                            <b><i class="fa-solid fa-credit-card"></i></b>
                            <span class="nav-text">Payment Gateways</span>
                        </a>
                    @endif

                    @if($can('admin.settings.fraud') || $isSuperAdmin)
                        <a href="{{ route('admin.settings.fraud') }}" class="nav-linkx {{ request()->routeIs('admin.settings.fraud') ? 'active' : '' }}" title="Fraud Engine API">
                            <b><i class="fa-solid fa-shield-halved"></i></b>
                            <span class="nav-text">Fraud Engine API</span>
                        </a>
                    @endif

                    @if($can('admin.settings.gtm') || $isSuperAdmin)
                        <a href="{{ route('admin.settings.gtm') }}" class="nav-linkx {{ request()->routeIs('admin.settings.gtm') ? 'active' : '' }}" title="GTM & Analytics">
                            <b><i class="fa-brands fa-google"></i></b>
                            <span class="nav-text">GTM & Analytics</span>
                        </a>
                    @endif

                    @if($can('admin.settings.meta') || $isSuperAdmin)
                        <a href="{{ route('admin.settings.meta') }}" class="nav-linkx {{ request()->routeIs('admin.settings.meta*') ? 'active' : '' }}" title="Meta Pixel & CAPI">
                            <b><i class="fa-brands fa-facebook"></i></b>
                            <span class="nav-text">Meta Pixel & CAPI</span>
                        </a>
                    @endif

                    @if($can('admin.settings.smtp') || $isSuperAdmin)
                        <a href="{{ route('admin.settings.smtp') }}" class="nav-linkx {{ request()->routeIs('admin.settings.smtp') ? 'active' : '' }}" title="SMTP & Email">
                            <b><i class="fa-solid fa-envelope"></i></b>
                            <span class="nav-text">SMTP & Email</span>
                        </a>
                    @endif

                    @if($can('admin.sms.index'))
                        <a href="{{ route('admin.sms.index') }}" class="nav-linkx {{ request()->routeIs('admin.sms.*') ? 'active' : '' }}" title="SMS Gateway">
                            <b><i class="fa-solid fa-comment-sms"></i></b>
                            <span class="nav-text">SMS Gateway</span>
                        </a>
                    @endif

                    @if($can('admin.notification_settings.index') || $isSuperAdmin)
                        <a href="{{ route('admin.notification_settings.index') }}" class="nav-linkx {{ request()->routeIs('admin.notification_settings.*') ? 'active' : '' }}" title="WhatsApp Commerce">
                            <b><i class="fa-brands fa-whatsapp text-success"></i></b>
                            <span class="nav-text">WhatsApp & SMS</span>
                        </a>
                    @endif

                    @if($can('admin.social_settings.index') || $isSuperAdmin)
                        <a href="{{ route('admin.social_settings.index') }}" class="nav-linkx {{ request()->routeIs('admin.social_settings.*') ? 'active' : '' }}" title="Social Login (Google)">
                            <b><i class="fa-brands fa-google text-primary"></i></b>
                            <span class="nav-text">Social Login</span>
                        </a>
                    @endif

                    @if($can('admin.system.info') || $can('admin.settings.manage') || $isSuperAdmin)
                        <a href="{{ route('admin.system.info') }}" class="nav-linkx {{ request()->routeIs('admin.system.info') ? 'active' : '' }}" title="System & Server Info">
                            <b><i class="fa-solid fa-server text-info"></i></b>
                            <span class="nav-text">System Info</span>
                        </a>
                    @endif
                </div>
            </div>
        @endif
    </div>
</aside>

<script>
(() => {
    function scrollToActiveModule() {
        var activeLink = document.querySelector("#sidebar .nav-linkx.active");
        if (!activeLink) return;
        var navSection = document.querySelector("#sidebar .nav-section");
        if (!navSection) return;

        var group = activeLink.closest(".nav-group");
        if (group) {
            group.classList.add("active-group");
        }

        var target = group || activeLink;
        var navRect = navSection.getBoundingClientRect();
        var targetRect = target.getBoundingClientRect();
        var currentScroll = navSection.scrollTop;
        var offsetPosition = (targetRect.top - navRect.top) + currentScroll - 30;

        navSection.scrollTo({
            top: Math.max(0, offsetPosition),
            behavior: "smooth"
        });
    }

    document.addEventListener("turbo:load", function () {
        setTimeout(scrollToActiveModule, 120);
    });
    if (document.readyState === 'complete' || document.readyState === 'interactive') {
        setTimeout(scrollToActiveModule, 120);
    }
})();
</script>