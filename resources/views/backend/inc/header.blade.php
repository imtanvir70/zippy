@php
    $adminId = (int) session('admin_id', 0);
    $rbac = app(\App\Services\Rbac\PermissionService::class);
    $isSuperAdmin = $adminId > 0 && $rbac->isSuperAdmin($adminId);
    $roleLabel = $isSuperAdmin ? 'Super Admin' : 'Staff Admin';
@endphp
<header class="topbar">
    <div class="d-flex align-items-center">
        <button type="button" class="topbar-action-btn" onclick="toggleSidebar(event)" title="Toggle Sidebar">
            <i class="fa-solid fa-bars-staggered"></i>
        </button>
        <a href="{{ route('home') }}" target="_blank" class="topbar-store-btn d-none d-md-inline-flex" title="Open Storefront">
            <span class="topbar-live-beacon"></span>
            <span>Live Store</span>
            <i class="fa-solid fa-arrow-up-right-from-square topbar-store-icon"></i>
        </a>
    </div>

    <div class="d-flex align-items-center gap-2">
        <button type="button" class="topbar-action-btn" id="headerClearCacheBtn" onclick="purgeSystemCache(event)" title="Purge System Cache">
            <i class="fa-solid fa-broom" id="headerClearCacheIcon"></i>
        </button>

        <button type="button" class="topbar-action-btn d-none d-sm-inline-flex" onclick="toggleAdminFullscreen(event)" title="Toggle Fullscreen" id="adminFullscreenBtn">
            <i class="fa-solid fa-expand" id="adminFullscreenIcon"></i>
        </button>

        <button type="button" class="topbar-action-btn topbar-theme-btn" id="headerThemeToggleBtn" onclick="toggleThemeQuick(event)" title="Toggle Theme (Light / Dark)">
            <i class="fa-solid fa-sun topbar-theme-icon-sun" id="headerThemeIcon"></i>
        </button>

        <button type="button" class="topbar-studio-btn" onclick="togglePanel(event)" title="Theme & Appearance Studio">
            <span class="topbar-studio-icon">
                <i class="fa-solid fa-wand-magic-sparkles"></i>
            </span>
            <span class="d-none d-sm-inline">Studio</span>
        </button>

        <div class="topbar-divider d-none d-sm-block"></div>

        <div class="topbar-user-card" onclick="toggleUserPanel(event)" title="Admin Account Settings">
            <div class="topbar-user-avatar-wrap">
                <div class="brand-badge topbar-user-avatar">
                    {{ mb_substr(session('admin_name', 'A'), 0, 1) }}
                </div>
                <span class="topbar-user-status" title="Active Status"></span>
            </div>
            <div class="d-none d-md-block text-start">
                <div class="topbar-user-name">{{ session('admin_name', 'Administrator') }}</div>
                <div class="topbar-user-role">{{ $roleLabel }}</div>
            </div>
            <i class="fa-solid fa-chevron-down topbar-user-chevron d-none d-md-inline-block"></i>
        </div>
    </div>
</header>
