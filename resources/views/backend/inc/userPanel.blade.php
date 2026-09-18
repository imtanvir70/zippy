<div class="user-panel" id="userPanel">
    <div class="p-3 border-bottom d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center gap-2">
            <i class="fa-solid fa-user-gear text-primary fs-5"></i>
            <h5 class="fw-bold mb-0">Admin Account</h5>
        </div>
        <button type="button" class="topbar-action-btn" onclick="toggleUserPanel(event)" title="Close Panel">
            <i class="fa-solid fa-xmark"></i>
        </button>
    </div>

    <div class="p-3 p-md-4 flex-grow-1 overflow-y-auto">
        <div class="card p-3 mb-4 text-center">
            <div class="brand-badge mx-auto mb-2" style="width: 56px; height: 56px; border-radius: 18px; font-size: 1.5rem;">
                {{ mb_substr(session('admin_name', 'A'), 0, 1) }}
            </div>
            <h5 class="fw-bold mb-1">{{ session('admin_name', 'Administrator') }}</h5>
            <p class="text-muted small mb-2">{{ session('admin_email', 'admin@zippybd.com') }}</p>
            <div>
                <span class="badge bg-success-subtle text-success border border-success-subtle px-2.5 py-1 rounded-pill small fw-bold">Super Admin</span>
            </div>
        </div>

        <div class="card p-2 mb-4">
            <div class="list-group list-group-flush">
                <a href="{{ route('admin.dashboard') }}" class="list-group-item list-group-item-action d-flex align-items-center gap-2 py-2.5 rounded-3 border-0">
                    <i class="fa-solid fa-chart-pie text-primary w-5 text-center"></i>
                    <span class="small fw-semibold">Dashboard Overview</span>
                </a>
                <a href="{{ route('home') }}" target="_blank" class="list-group-item list-group-item-action d-flex align-items-center gap-2 py-2.5 rounded-3 border-0">
                    <i class="fa-solid fa-store text-info w-5 text-center"></i>
                    <span class="small fw-semibold">Visit Live Storefront</span>
                </a>
                <a href="{{ route('admin.audit.index') }}" class="list-group-item list-group-item-action d-flex align-items-center gap-2 py-2.5 rounded-3 border-0">
                    <i class="fa-solid fa-clipboard-list text-warning w-5 text-center"></i>
                    <span class="small fw-semibold">Audit Logs</span>
                </a>
                <a href="{{ route('admin.settings.enterprise') }}" class="list-group-item list-group-item-action d-flex align-items-center gap-2 py-2.5 rounded-3 border-0">
                    <i class="fa-solid fa-sliders text-success w-5 text-center"></i>
                    <span class="small fw-semibold">Enterprise Settings</span>
                </a>
            </div>
        </div>

        <div class="pt-2">
            <form action="{{ route('logout') }}" method="POST" data-turbo="false">
                @csrf
                <button type="submit" class="btn btn-danger w-100 py-2.5 rounded-3 fw-bold text-sm d-flex align-items-center justify-content-center gap-2 shadow-sm">
                    <i class="fa-solid fa-right-from-bracket"></i> Sign Out
                </button>
            </form>
        </div>
    </div>
</div>

