@extends('backend.layouts.app')

@section('title', 'System & Server Diagnostics')

@section('content')
<style>
    .sys-command-bar {
        background: var(--card-bg);
        border: 1px solid var(--border-color);
        border-radius: 16px;
        position: relative;
        overflow: hidden;
        box-shadow: var(--card-shadow);
    }
    .sys-command-bar::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 3px;
        background: linear-gradient(90deg, #6366f1, #06b6d4, #10b981, #f59e0b);
    }
    .sys-pulse-container {
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }
    .sys-pulse-dot {
        position: relative;
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background-color: #10b981;
        box-shadow: 0 0 8px #10b981;
    }
    .sys-pulse-dot::after {
        content: '';
        position: absolute;
        width: 100%;
        height: 100%;
        top: 0;
        left: 0;
        border-radius: 50%;
        background-color: #10b981;
        animation: sysPulseRing 1.8s cubic-bezier(0.215, 0.61, 0.355, 1) infinite;
    }
    @keyframes sysPulseRing {
        0% { transform: scale(0.95); opacity: 0.9; }
        50% { transform: scale(2.4); opacity: 0; }
        100% { transform: scale(2.4); opacity: 0; }
    }
    .sys-kpi-card {
        background: var(--card-bg);
        border: 1px solid var(--border-color);
        border-radius: 16px;
        padding: 1.25rem 1.35rem;
        position: relative;
        overflow: hidden;
        height: 100%;
        transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
        box-shadow: var(--card-shadow);
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }
    .sys-kpi-card:hover {
        transform: translateY(-3px);
        border-color: rgba(var(--accent-rgb), 0.45);
        box-shadow: 0 12px 28px -6px rgba(0, 0, 0, 0.14);
    }
    .sys-kpi-glow {
        position: absolute;
        top: -24px;
        right: -24px;
        width: 90px;
        height: 90px;
        border-radius: 50%;
        opacity: 0.12;
        filter: blur(20px);
        pointer-events: none;
    }
    .sys-kpi-icon {
        width: 46px;
        height: 46px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.3rem;
        flex-shrink: 0;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
    }
    .sys-hero-chip {
        font-size: 0.7rem;
        font-weight: 700;
        letter-spacing: 0.06em;
        text-transform: uppercase;
    }
    .sys-panel {
        background: var(--card-bg);
        border: 1px solid var(--border-color);
        border-radius: 16px;
        padding: 1.35rem 1.5rem;
        box-shadow: var(--card-shadow);
    }
    .sys-terminal-window {
        background: #090d16;
        border: 1px solid rgba(255, 255, 255, 0.09);
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 6px 20px -4px rgba(0, 0, 0, 0.3);
    }
    .sys-terminal-header {
        background: rgba(255, 255, 255, 0.03);
        border-bottom: 1px solid rgba(255, 255, 255, 0.07);
        padding: 0.55rem 0.9rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .sys-terminal-dots {
        display: flex;
        gap: 6px;
    }
    .sys-terminal-dot {
        width: 10px;
        height: 10px;
        border-radius: 50%;
    }
    .sys-terminal-body {
        padding: 0.95rem 1.15rem;
        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
        font-size: 0.82rem;
        color: #e2e8f0;
        line-height: 1.6;
        word-break: break-all;
    }
    .sys-spec-card {
        background: var(--input-group-bg);
        border: 1px solid var(--border-color);
        border-radius: 14px;
        padding: 1.15rem;
        height: 100%;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
    }
    .sys-spec-card:hover {
        border-color: rgba(var(--accent-rgb), 0.4);
        transform: translateY(-2px);
        box-shadow: 0 6px 16px -2px rgba(0, 0, 0, 0.07);
    }
    .sys-disk-gauge {
        background: var(--input-group-bg);
        border: 1px solid var(--border-color);
        border-radius: 14px;
        padding: 1.25rem;
    }
    .sys-progress-track {
        height: 14px;
        border-radius: 999px;
        overflow: hidden;
        background: rgba(148, 163, 184, 0.2);
        position: relative;
    }
    [data-bs-theme="dark"] .sys-progress-track {
        background: rgba(255, 255, 255, 0.08);
    }
    .sys-progress-fill {
        height: 100%;
        border-radius: 999px;
        transition: width 0.8s cubic-bezier(0.16, 1, 0.3, 1);
        position: relative;
    }
    .sys-progress-fill::after {
        content: '';
        position: absolute;
        top: 0; left: 0; bottom: 0; right: 0;
        background-image: linear-gradient(
            45deg,
            rgba(255, 255, 255, 0.15) 25%,
            transparent 25%,
            transparent 50%,
            rgba(255, 255, 255, 0.15) 50%,
            rgba(255, 255, 255, 0.15) 75%,
            transparent 75%,
            transparent
        );
        background-size: 1rem 1rem;
        animation: sysProgressStripes 1.5s linear infinite;
    }
    @keyframes sysProgressStripes {
        from { background-position: 1rem 0; }
        to { background-position: 0 0; }
    }
    .sys-directive-card {
        background: var(--input-group-bg);
        border: 1px solid var(--border-color);
        border-radius: 12px;
        padding: 0.95rem 1rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
        transition: all 0.2s ease;
        height: 100%;
    }
    .sys-directive-card:hover {
        border-color: rgba(var(--accent-rgb), 0.4);
        background: rgba(var(--accent-rgb), 0.03);
        transform: translateY(-2px);
    }
    .sys-arch-box {
        background: var(--input-group-bg);
        border: 1px solid var(--border-color);
        border-radius: 14px;
        padding: 1.15rem;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        height: 100%;
        transition: all 0.2s ease;
    }
    .sys-arch-box:hover {
        border-color: rgba(var(--accent-rgb), 0.4);
        transform: translateY(-2px);
    }
    .sys-ext-card {
        background: var(--input-group-bg);
        border: 1px solid var(--border-color);
        border-radius: 14px;
        padding: 1.15rem;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        height: 100%;
        transition: all 0.22s cubic-bezier(0.16, 1, 0.3, 1);
        position: relative;
    }
    .sys-ext-card:hover {
        transform: translateY(-3px);
        border-color: rgba(var(--accent-rgb), 0.45);
        box-shadow: 0 10px 24px -4px rgba(0, 0, 0, 0.1);
    }
    .sys-copy-btn {
        background: rgba(255, 255, 255, 0.08);
        border: 1px solid rgba(255, 255, 255, 0.15);
        color: #cbd5e1;
        border-radius: 8px;
        padding: 0.3rem 0.65rem;
        font-size: 0.75rem;
        cursor: pointer;
        transition: all 0.15s ease;
        display: inline-flex;
        align-items: center;
        gap: 5px;
        flex-shrink: 0;
    }
    .sys-copy-btn:hover {
        background: var(--accent);
        color: #ffffff;
        border-color: var(--accent);
    }
    .sys-search-input {
        background: var(--input-group-bg);
        border: 1px solid var(--border-color);
        color: var(--text-main);
        border-radius: 10px;
        padding: 0.5rem 1rem 0.5rem 2.35rem;
        font-size: 0.84rem;
        transition: all 0.15s ease;
        width: 100%;
        max-width: 280px;
    }
    .sys-search-input:focus {
        outline: none;
        border-color: var(--accent);
        box-shadow: 0 0 0 3px rgba(var(--accent-rgb), 0.16);
        background: var(--card-bg);
    }
    .sys-filter-pill {
        border: 1px solid var(--border-color);
        background: transparent;
        color: var(--text-muted);
        font-size: 0.75rem;
        font-weight: 600;
        padding: 0.35rem 0.75rem;
        border-radius: 999px;
        cursor: pointer;
        transition: all 0.15s ease;
    }
    .sys-filter-pill:hover,
    .sys-filter-pill.active {
        background: var(--accent);
        color: #ffffff;
        border-color: var(--accent);
    }
</style>

<div class="d-flex flex-column gap-4">
    <div class="sys-command-bar p-3 p-md-4">
        <div class="d-flex flex-column flex-xl-row justify-content-between align-items-xl-center gap-3">
            <div class="d-flex align-items-start align-items-sm-center gap-3">
                <div class="d-flex align-items-center justify-content-center rounded-3 text-white shadow-sm flex-shrink-0" style="width: 52px; height: 52px; background: linear-gradient(135deg, #4f46e5 0%, #06b6d4 100%);">
                    <i class="fa-solid fa-server-waveform fs-4"></i>
                </div>
                <div>
                    <div class="d-flex align-items-center gap-2 mb-1 flex-wrap">
                        <h4 class="fw-bold mb-0 text-body">System &amp; Server Diagnostics</h4>
                        <div class="sys-pulse-container px-2 py-1 rounded-pill border" style="background: rgba(16, 185, 129, 0.08); border-color: rgba(16, 185, 129, 0.25) !important;">
                            <span class="sys-pulse-dot"></span>
                            <span class="small fw-semibold text-success" style="font-size: 0.72rem; letter-spacing: 0.04em;">LIVE TELEMETRY ACTIVE</span>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-2 text-secondary small flex-wrap">
                        <span>Node Host: <strong class="text-body font-monospace">{{ $telemetry['server']['hostname'] }}</strong></span>
                        <span>&bull;</span>
                        <span>IP: <strong class="text-body font-monospace">{{ $telemetry['server']['server_ip'] }}</strong></span>
                        <span>&bull;</span>
                        <span>Environment: <span class="badge {{ $telemetry['app']['env'] === 'production' ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-warning-subtle text-warning border border-warning-subtle' }} rounded-pill px-2 py-1">{{ strtoupper($telemetry['app']['env']) }}</span></span>
                    </div>
                </div>
            </div>

            <div class="d-flex align-items-center gap-2 flex-wrap">
                <button type="button" class="btn btn-outline-secondary px-3 py-2 fw-semibold d-inline-flex align-items-center gap-2 rounded-3" onclick="copyFullDiagnostics(event)">
                    <i class="fa-regular fa-clipboard"></i>
                    <span>Copy Diagnostic Report</span>
                </button>
                <button type="button" class="btn btn-primary px-3 py-2 fw-semibold d-inline-flex align-items-center gap-2 rounded-3 shadow-sm" onclick="purgeSystemCache(event)" id="btnPurgeCache">
                    <i class="fa-solid fa-bolt-lightning"></i>
                    <span>Purge &amp; Optimize All</span>
                </button>
                <button type="button" class="btn btn-outline-secondary px-3 py-2 fw-semibold d-inline-flex align-items-center gap-2 rounded-3" onclick="window.location.reload()">
                    <i class="fa-solid fa-rotate-right"></i>
                    <span>Refresh</span>
                </button>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="sys-kpi-card">
                <div class="sys-kpi-glow" style="background: #6366f1;"></div>
                <div class="d-flex align-items-start justify-content-between mb-3">
                    <div>
                        <span class="sys-hero-chip text-secondary">PHP Runtime</span>
                        <div class="fs-3 fw-bold text-body font-monospace mt-1">{{ $telemetry['overview']['php_version'] }}</div>
                    </div>
                    <div class="sys-kpi-icon" style="background: rgba(99, 102, 241, 0.12); color: #6366f1;">
                        <i class="fa-brands fa-php fs-4"></i>
                    </div>
                </div>
                <div class="pt-3 border-top d-flex align-items-center justify-content-between">
                    <span class="small text-secondary">SAPI Engine</span>
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill font-monospace px-2 py-1 small">
                        {{ $telemetry['overview']['php_sapi'] }}
                    </span>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="sys-kpi-card">
                <div class="sys-kpi-glow" style="background: #ef4444;"></div>
                <div class="d-flex align-items-start justify-content-between mb-3">
                    <div>
                        <span class="sys-hero-chip text-secondary">Framework Engine</span>
                        <div class="fs-3 fw-bold text-body font-monospace mt-1">v{{ $telemetry['app']['laravel_version'] }}</div>
                    </div>
                    <div class="sys-kpi-icon" style="background: rgba(239, 68, 68, 0.12); color: #ef4444;">
                        <i class="fa-brands fa-laravel fs-4"></i>
                    </div>
                </div>
                <div class="pt-3 border-top d-flex align-items-center justify-content-between">
                    <span class="small text-secondary">Mode &amp; Health</span>
                    <span class="badge {{ $telemetry['app']['env'] === 'production' ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-warning-subtle text-warning border border-warning-subtle' }} rounded-pill px-2 py-1 small">
                        {{ strtoupper($telemetry['app']['env']) }} &bull; {{ $telemetry['app']['debug'] ? 'Dev' : 'Safe' }}
                    </span>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="sys-kpi-card">
                <div class="sys-kpi-glow" style="background: #10b981;"></div>
                <div class="d-flex align-items-start justify-content-between mb-3">
                    <div>
                        <span class="sys-hero-chip text-secondary">Compute Processing</span>
                        <div class="fs-3 fw-bold text-body font-monospace mt-1">{{ $telemetry['overview']['cpu_cores'] }}</div>
                    </div>
                    <div class="sys-kpi-icon" style="background: rgba(16, 185, 129, 0.12); color: #10b981;">
                        <i class="fa-solid fa-microchip"></i>
                    </div>
                </div>
                <div class="pt-3 border-top d-flex align-items-center justify-content-between">
                    <span class="small text-secondary">Architecture</span>
                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill font-monospace px-2 py-1 small">
                        {{ $telemetry['server']['arch'] }} SMP
                    </span>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="sys-kpi-card">
                <div class="sys-kpi-glow" style="background: #f59e0b;"></div>
                <div class="d-flex align-items-start justify-content-between mb-3">
                    <div>
                        <span class="sys-hero-chip text-secondary">RAM &amp; Memory Limit</span>
                        <div class="fs-3 fw-bold text-body font-monospace mt-1">{{ $telemetry['overview']['memory_limit'] }}</div>
                    </div>
                    <div class="sys-kpi-icon" style="background: rgba(245, 158, 11, 0.12); color: #f59e0b;">
                        <i class="fa-solid fa-memory"></i>
                    </div>
                </div>
                <div class="pt-3 border-top d-flex align-items-center justify-content-between">
                    <span class="small text-secondary">Physical Host</span>
                    <span class="badge bg-warning-subtle text-warning border border-warning-subtle rounded-pill font-monospace px-2 py-1 small text-truncate" style="max-width: 140px;" title="{{ $telemetry['hardware']['node_total_ram'] }}">
                        {{ $telemetry['hardware']['node_total_ram'] }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    <div class="sys-panel">
        <div class="d-flex flex-column flex-sm-row align-items-sm-center justify-content-between gap-2 mb-4 pb-3 border-bottom">
            <div class="d-flex align-items-center gap-3">
                <div class="d-flex align-items-center justify-content-center rounded-3 text-primary" style="width: 40px; height: 40px; background: rgba(var(--accent-rgb), 0.12);">
                    <i class="fa-solid fa-server fs-5"></i>
                </div>
                <div>
                    <h5 class="fw-bold mb-0 text-body">System &amp; Hardware Specifications</h5>
                    <small class="text-secondary">Host machine kernel, processor instruction set, virtualization memory boundary, and partition storage.</small>
                </div>
            </div>
            <span class="badge bg-secondary-subtle text-secondary rounded-pill px-3 py-1 font-monospace small align-self-start align-self-sm-center">
                HOST INFRASTRUCTURE
            </span>
        </div>

        <div class="sys-terminal-window mb-4">
            <div class="sys-terminal-header">
                <div class="d-flex align-items-center gap-2">
                    <div class="sys-terminal-dots">
                        <span class="sys-terminal-dot" style="background: #ef4444;"></span>
                        <span class="sys-terminal-dot" style="background: #f59e0b;"></span>
                        <span class="sys-terminal-dot" style="background: #10b981;"></span>
                    </div>
                    <span class="text-white-50 small ms-2 font-monospace" style="font-size: 0.74rem;">root@host: ~ /proc/version</span>
                </div>
                <button type="button" class="sys-copy-btn" onclick="copySnippet('osEnvText', 'OS Environment copied!')">
                    <i class="fa-regular fa-copy"></i>
                    <span>Copy</span>
                </button>
            </div>
            <div class="sys-terminal-body" id="osEnvText">
                {{ $telemetry['hardware']['os_environment'] }}
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-12 col-md-4">
                <div class="sys-spec-card">
                    <div class="d-flex align-items-start justify-content-between mb-3">
                        <div class="d-flex align-items-center gap-2">
                            <div class="d-flex align-items-center justify-content-center rounded text-primary" style="width: 32px; height: 32px; background: rgba(var(--accent-rgb), 0.12);">
                                <i class="fa-solid fa-microchip"></i>
                            </div>
                            <span class="fw-bold small text-body">CPU Architecture</span>
                        </div>
                        <button type="button" class="sys-copy-btn" style="color: var(--text-muted); background: transparent; border-color: var(--border-color);" onclick="copySnippet('cpuArchText', 'CPU Architecture copied!')">
                            <i class="fa-regular fa-copy"></i>
                        </button>
                    </div>
                    <div class="fw-bold font-monospace text-body small mb-3" id="cpuArchText">{{ $telemetry['hardware']['cpu_architecture'] }}</div>
                    <div class="d-flex align-items-center justify-content-between pt-2 border-top text-secondary small">
                        <span>Cores &amp; Instruction:</span>
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill font-monospace px-2 py-1">{{ $telemetry['overview']['cpu_cores'] }} &bull; {{ $telemetry['server']['arch'] }}</span>
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-4">
                <div class="sys-spec-card">
                    <div class="d-flex align-items-start justify-content-between mb-3">
                        <div class="d-flex align-items-center gap-2">
                            <div class="d-flex align-items-center justify-content-center rounded text-info" style="width: 32px; height: 32px; background: rgba(6, 182, 212, 0.12);">
                                <i class="fa-solid fa-box-open"></i>
                            </div>
                            <span class="fw-bold small text-body">Container RAM Boundary</span>
                        </div>
                        <span class="badge bg-info-subtle text-info border border-info-subtle rounded-pill px-2 py-1 small">Virtual Isolation</span>
                    </div>
                    <div class="fw-bold font-monospace text-body small mb-3 text-truncate" title="{{ $telemetry['hardware']['account_ram_limit'] }}">{{ $telemetry['hardware']['account_ram_limit'] }}</div>
                    <div class="d-flex align-items-center justify-content-between pt-2 border-top text-secondary small">
                        <span>Main Node Physical:</span>
                        <span class="font-monospace fw-bold text-body">{{ $telemetry['hardware']['node_total_ram'] }}</span>
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-4">
                <div class="sys-spec-card">
                    <div class="d-flex align-items-start justify-content-between mb-3">
                        <div class="d-flex align-items-center gap-2">
                            <div class="d-flex align-items-center justify-content-center rounded text-success" style="width: 32px; height: 32px; background: rgba(16, 185, 129, 0.12);">
                                <i class="fa-solid fa-users-gear"></i>
                            </div>
                            <span class="fw-bold small text-body">Hosted Users &amp; Gateway</span>
                        </div>
                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2 py-1 small">Multi-Tenant</span>
                    </div>
                    <div class="fw-bold font-monospace text-body small mb-3">{{ $telemetry['hardware']['hosted_users'] }}</div>
                    <div class="d-flex align-items-center justify-content-between pt-2 border-top text-secondary small">
                        <span>Web Gateway:</span>
                        <span class="fw-bold text-body font-monospace text-truncate" style="max-width: 140px;" title="{{ $telemetry['overview']['web_server_full'] }}">{{ $telemetry['overview']['web_server'] }}</span>
                    </div>
                </div>
            </div>
        </div>

        @php
            $diskPct = (float) $telemetry['hardware']['disk_usage_percent'];
            $isCritical = $diskPct > 85;
            $isWarning = $diskPct > 70 && $diskPct <= 85;
            $diskGradient = $isCritical ? 'linear-gradient(90deg, #ef4444, #dc2626)' : ($isWarning ? 'linear-gradient(90deg, #f59e0b, #d97706)' : 'linear-gradient(90deg, #10b981, #06b6d4)');
            $diskBadgeClass = $isCritical ? 'bg-danger-subtle text-danger border border-danger-subtle' : ($isWarning ? 'bg-warning-subtle text-warning border border-warning-subtle' : 'bg-success-subtle text-success border border-success-subtle');
        @endphp

        <div class="sys-disk-gauge">
            <div class="d-flex flex-column flex-sm-row align-items-sm-center justify-content-between gap-2 mb-3">
                <div class="d-flex align-items-center gap-2">
                    <div class="d-flex align-items-center justify-content-center rounded-3 text-primary shadow-sm" style="width: 36px; height: 36px; background: rgba(var(--accent-rgb), 0.15);">
                        <i class="fa-solid fa-hard-drive fs-6"></i>
                    </div>
                    <div>
                        <div class="fw-bold text-body">Server Disk Partition Volume</div>
                        <small class="text-secondary">Primary storage block allocated to host filesystem</small>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="fw-bold font-monospace text-body small">{{ $telemetry['hardware']['disk_usage'] }}</span>
                    <span class="badge {{ $diskBadgeClass }} rounded-pill px-3 py-1 small font-monospace">{{ $diskPct }}% ALLOCATED</span>
                </div>
            </div>

            <div class="sys-progress-track mb-3">
                <div class="sys-progress-fill" style="width: {{ min(100, $diskPct) }}%; background: {{ $diskGradient }};"></div>
            </div>

            <div class="row g-3 text-center">
                <div class="col-12 col-md-4">
                    <div class="p-3 rounded-3 border text-start h-100" style="background: var(--card-bg);">
                        <div class="d-flex align-items-center justify-content-between mb-1">
                            <span class="text-secondary" style="font-size: 0.72rem; font-weight: 700; letter-spacing: 0.05em;">FREE DISK</span>
                            <i class="fa-solid fa-circle-check text-success"></i>
                        </div>
                        <div class="fw-bold text-success font-monospace fs-4">{{ $telemetry['hardware']['disk_free_gb'] }} <span class="fs-6 fw-normal">GB</span></div>
                    </div>
                </div>
                <div class="col-12 col-md-4">
                    <div class="p-3 rounded-3 border text-start h-100" style="background: var(--card-bg);">
                        <div class="d-flex align-items-center justify-content-between mb-1">
                            <span class="text-secondary" style="font-size: 0.72rem; font-weight: 700; letter-spacing: 0.05em;">USED DISK</span>
                            <i class="fa-solid fa-chart-pie text-primary"></i>
                        </div>
                        <div class="fw-bold text-body font-monospace fs-4">{{ $telemetry['hardware']['disk_used_gb'] }} <span class="fs-6 fw-normal">GB</span></div>
                    </div>
                </div>
                <div class="col-12 col-md-4">
                    <div class="p-3 rounded-3 border text-start h-100" style="background: var(--card-bg);">
                        <div class="d-flex align-items-center justify-content-between mb-1">
                            <span class="text-secondary" style="font-size: 0.72rem; font-weight: 700; letter-spacing: 0.05em;">TOTAL CAPACITY</span>
                            <i class="fa-solid fa-database text-secondary"></i>
                        </div>
                        <div class="fw-bold text-body font-monospace fs-4">{{ $telemetry['hardware']['disk_total_gb'] }} <span class="fs-6 fw-normal">GB</span></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="sys-panel">
        <div class="d-flex flex-column flex-sm-row align-items-sm-center justify-content-between gap-2 mb-4 pb-3 border-bottom">
            <div class="d-flex align-items-center gap-3">
                <div class="d-flex align-items-center justify-content-center rounded-3 text-primary" style="width: 40px; height: 40px; background: rgba(var(--accent-rgb), 0.12);">
                    <i class="fa-solid fa-sliders fs-5"></i>
                </div>
                <div>
                    <h5 class="fw-bold mb-0 text-body">PHP Directives &amp; Execution Limits</h5>
                    <small class="text-secondary">Core engine configurations defined via php.ini, FPM master pool, or web server SAPI.</small>
                </div>
            </div>
            <span class="badge bg-secondary-subtle text-secondary rounded-pill px-3 py-1 font-monospace small align-self-start align-self-sm-center">
                ENGINE LIMITS
            </span>
        </div>

        <div class="row g-3">
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="sys-directive-card">
                    <div class="d-flex align-items-center gap-2 min-w-0">
                        <div class="d-flex align-items-center justify-content-center rounded text-primary flex-shrink-0" style="width: 32px; height: 32px; background: rgba(var(--accent-rgb), 0.12);">
                            <i class="fa-solid fa-stopwatch" style="font-size: 0.85rem;"></i>
                        </div>
                        <div class="min-w-0">
                            <div class="text-secondary" style="font-size: 0.72rem; font-weight: 600;">Max Execution Time</div>
                            <div class="fw-bold font-monospace text-body small mt-1">{{ $telemetry['php_directives']['max_execution_time'] }}</div>
                        </div>
                    </div>
                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2 py-1 small">Optimal</span>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-xl-3">
                <div class="sys-directive-card">
                    <div class="d-flex align-items-center gap-2 min-w-0">
                        <div class="d-flex align-items-center justify-content-center rounded text-primary flex-shrink-0" style="width: 32px; height: 32px; background: rgba(var(--accent-rgb), 0.12);">
                            <i class="fa-solid fa-cloud-arrow-up" style="font-size: 0.85rem;"></i>
                        </div>
                        <div class="min-w-0">
                            <div class="text-secondary" style="font-size: 0.72rem; font-weight: 600;">Upload Max Filesize</div>
                            <div class="fw-bold font-monospace text-body small mt-1">{{ $telemetry['php_directives']['upload_max_filesize'] }}</div>
                        </div>
                    </div>
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-2 py-1 small">Enterprise</span>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-xl-3">
                <div class="sys-directive-card">
                    <div class="d-flex align-items-center gap-2 min-w-0">
                        <div class="d-flex align-items-center justify-content-center rounded text-primary flex-shrink-0" style="width: 32px; height: 32px; background: rgba(var(--accent-rgb), 0.12);">
                            <i class="fa-solid fa-file-export" style="font-size: 0.85rem;"></i>
                        </div>
                        <div class="min-w-0">
                            <div class="text-secondary" style="font-size: 0.72rem; font-weight: 600;">Post Max Size</div>
                            <div class="fw-bold font-monospace text-body small mt-1">{{ $telemetry['php_directives']['post_max_size'] }}</div>
                        </div>
                    </div>
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-2 py-1 small">Sync</span>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-xl-3">
                <div class="sys-directive-card">
                    <div class="d-flex align-items-center gap-2 min-w-0">
                        <div class="d-flex align-items-center justify-content-center rounded text-primary flex-shrink-0" style="width: 32px; height: 32px; background: rgba(var(--accent-rgb), 0.12);">
                            <i class="fa-solid fa-list-ol" style="font-size: 0.85rem;"></i>
                        </div>
                        <div class="min-w-0">
                            <div class="text-secondary" style="font-size: 0.72rem; font-weight: 600;">Max Input Variables</div>
                            <div class="fw-bold font-monospace text-body small mt-1">{{ $telemetry['php_directives']['max_input_vars'] }}</div>
                        </div>
                    </div>
                    <span class="badge bg-info-subtle text-info border border-info-subtle rounded-pill px-2 py-1 small">Bulk Safe</span>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-xl-3">
                <div class="sys-directive-card">
                    <div class="d-flex align-items-center gap-2 min-w-0">
                        <div class="d-flex align-items-center justify-content-center rounded text-primary flex-shrink-0" style="width: 32px; height: 32px; background: rgba(var(--accent-rgb), 0.12);">
                            <i class="fa-solid fa-hourglass-half" style="font-size: 0.85rem;"></i>
                        </div>
                        <div class="min-w-0">
                            <div class="text-secondary" style="font-size: 0.72rem; font-weight: 600;">Max Input Time</div>
                            <div class="fw-bold font-monospace text-body small mt-1">{{ $telemetry['php_directives']['max_input_time'] }}</div>
                        </div>
                    </div>
                    <span class="badge bg-secondary-subtle text-secondary rounded-pill px-2 py-1 small">Default</span>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-xl-3">
                <div class="sys-directive-card">
                    <div class="d-flex align-items-center gap-2 min-w-0">
                        <div class="d-flex align-items-center justify-content-center rounded text-primary flex-shrink-0" style="width: 32px; height: 32px; background: rgba(var(--accent-rgb), 0.12);">
                            <i class="fa-solid fa-bolt-lightning" style="font-size: 0.85rem;"></i>
                        </div>
                        <div class="min-w-0">
                            <div class="text-secondary" style="font-size: 0.72rem; font-weight: 600;">OPcache Accelerator</div>
                            <div class="fw-bold font-monospace text-body small mt-1">{{ $telemetry['php_directives']['opcache_installed'] }}</div>
                        </div>
                    </div>
                    @if($telemetry['php_directives']['opcache_installed'] === 'Enabled')
                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2 py-1 small">Active</span>
                    @else
                        <span class="badge bg-secondary-subtle text-secondary rounded-pill px-2 py-1 small">Inactive</span>
                    @endif
                </div>
            </div>

            <div class="col-12 col-sm-6 col-xl-3">
                <div class="sys-directive-card">
                    <div class="d-flex align-items-center gap-2 min-w-0">
                        <div class="d-flex align-items-center justify-content-center rounded text-primary flex-shrink-0" style="width: 32px; height: 32px; background: rgba(var(--accent-rgb), 0.12);">
                            <i class="fa-solid fa-ethernet" style="font-size: 0.85rem;"></i>
                        </div>
                        <div class="min-w-0">
                            <div class="text-secondary" style="font-size: 0.72rem; font-weight: 600;">Socket Timeout</div>
                            <div class="fw-bold font-monospace text-body small mt-1">{{ $telemetry['php_directives']['default_socket_timeout'] }}</div>
                        </div>
                    </div>
                    <span class="badge bg-secondary-subtle text-secondary rounded-pill px-2 py-1 small">Network</span>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-xl-3">
                <div class="sys-directive-card">
                    <div class="d-flex align-items-center gap-2 min-w-0">
                        <div class="d-flex align-items-center justify-content-center rounded text-primary flex-shrink-0" style="width: 32px; height: 32px; background: rgba(var(--accent-rgb), 0.12);">
                            <i class="fa-solid fa-shield-halved" style="font-size: 0.85rem;"></i>
                        </div>
                        <div class="min-w-0">
                            <div class="text-secondary" style="font-size: 0.72rem; font-weight: 600;">Display Errors</div>
                            <div class="fw-bold font-monospace text-body small mt-1">{{ $telemetry['php_directives']['display_errors'] }}</div>
                        </div>
                    </div>
                    <span class="badge {{ $telemetry['php_directives']['display_errors'] === 'Off' ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-warning-subtle text-warning border border-warning-subtle' }} rounded-pill px-2 py-1 small font-monospace">
                        {{ $telemetry['php_directives']['display_errors'] === 'Off' ? 'Production Safe' : 'Warning' }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    <div class="sys-panel">
        <div class="d-flex flex-column flex-sm-row align-items-sm-center justify-content-between gap-2 mb-4 pb-3 border-bottom">
            <div class="d-flex align-items-center gap-3">
                <div class="d-flex align-items-center justify-content-center rounded-3 text-primary" style="width: 40px; height: 40px; background: rgba(var(--accent-rgb), 0.12);">
                    <i class="fa-solid fa-layer-group fs-5"></i>
                </div>
                <div>
                    <h5 class="fw-bold mb-0 text-body">Application Architecture &amp; Persistence Services</h5>
                    <small class="text-secondary">Framework core, relational database engine, in-memory cache, symlinks, and mailer.</small>
                </div>
            </div>
            <span class="badge bg-secondary-subtle text-secondary rounded-pill px-3 py-1 font-monospace small align-self-start align-self-sm-center">
                SERVICES STACK
            </span>
        </div>

        <div class="row g-3">
            <div class="col-12 col-md-6 col-xl-4">
                <div class="sys-arch-box">
                    <div>
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <div class="d-flex align-items-center gap-2">
                                <div class="d-flex align-items-center justify-content-center rounded text-primary" style="width: 32px; height: 32px; background: rgba(var(--accent-rgb), 0.12);">
                                    <i class="fa-solid fa-database"></i>
                                </div>
                                <span class="fw-bold small text-body">Database Connectivity</span>
                            </div>
                            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2 py-1 small">
                                <i class="fa-solid fa-check me-1"></i> Active
                            </span>
                        </div>
                        <div class="fs-5 fw-bold font-monospace text-body mb-1">{{ ucfirst($telemetry['database']['driver']) }}</div>
                        <div class="text-secondary small font-monospace">{{ $telemetry['database']['version'] }}</div>
                    </div>
                    <div class="pt-3 border-top mt-3 text-secondary small d-flex justify-content-between align-items-center">
                        <span>Database Name:</span>
                        <span class="fw-bold text-body font-monospace">{{ $telemetry['database']['database'] }}</span>
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-6 col-xl-4">
                <div class="sys-arch-box">
                    <div>
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <div class="d-flex align-items-center gap-2">
                                <div class="d-flex align-items-center justify-content-center rounded text-info" style="width: 32px; height: 32px; background: rgba(6, 182, 212, 0.12);">
                                    <i class="fa-solid fa-memory"></i>
                                </div>
                                <span class="fw-bold small text-body">Cache &amp; Session Layer</span>
                            </div>
                            <span class="badge bg-info-subtle text-info border border-info-subtle rounded-pill px-2 py-1 small font-monospace">Synchronous</span>
                        </div>
                        <div class="fs-5 fw-bold font-monospace text-body mb-1">{{ ucfirst($telemetry['services']['cache_driver']) }} / {{ ucfirst($telemetry['services']['session_driver']) }}</div>
                        <div class="text-secondary small">Queue Provider: <strong class="text-body font-monospace">{{ ucfirst($telemetry['services']['queue_driver']) }}</strong></div>
                    </div>
                    <div class="pt-3 border-top mt-3 text-secondary small d-flex justify-content-between align-items-center">
                        <span>Drivers:</span>
                        <span class="badge bg-secondary-subtle text-secondary rounded-pill px-2 py-1 font-monospace">Cache &amp; Session</span>
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-6 col-xl-4">
                <div class="sys-arch-box">
                    <div>
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <div class="d-flex align-items-center gap-2">
                                <div class="d-flex align-items-center justify-content-center rounded text-danger" style="width: 32px; height: 32px; background: rgba(239, 68, 68, 0.12);">
                                    <i class="fa-solid fa-bolt-lightning"></i>
                                </div>
                                <span class="fw-bold small text-body">Redis In-Memory Engine</span>
                            </div>
                            @if($telemetry['services']['redis_ping'])
                                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2 py-1 small">
                                    <i class="fa-solid fa-check me-1"></i> Connected (PONG)
                                </span>
                            @elseif(!empty($telemetry['extensions']['redis']['loaded']))
                                <span class="badge bg-warning-subtle text-warning border border-warning-subtle rounded-pill px-2 py-1 small">
                                    Standby
                                </span>
                            @else
                                <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill px-2 py-1 small">
                                    File Store
                                </span>
                            @endif
                        </div>
                        <div class="fs-5 fw-bold font-monospace text-body mb-1">{{ $telemetry['services']['redis_status'] }}</div>
                        <div class="text-secondary small">Low-latency memory caching &amp; key-value clustering</div>
                    </div>
                    <div class="pt-3 border-top mt-3 text-secondary small d-flex justify-content-between align-items-center">
                        <span>Client Engine:</span>
                        <span class="badge bg-secondary-subtle text-secondary rounded-pill px-2 py-1 font-monospace">phpredis</span>
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-6 col-xl-4">
                <div class="sys-arch-box">
                    <div>
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <div class="d-flex align-items-center gap-2">
                                <div class="d-flex align-items-center justify-content-center rounded text-primary" style="width: 32px; height: 32px; background: rgba(var(--accent-rgb), 0.12);">
                                    <i class="fa-solid fa-link"></i>
                                </div>
                                <span class="fw-bold small text-body">Storage Symlink</span>
                            </div>
                            @if(!empty($telemetry['infrastructure']['symlink_active']) || !empty($telemetry['storage']['symlink_active']))
                                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2 py-1 small">
                                    <i class="fa-solid fa-check me-1"></i> Active &amp; Linked
                                </span>
                            @else
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-2 py-1 small">
                                    <i class="fa-solid fa-xmark me-1"></i> Missing
                                </span>
                            @endif
                        </div>
                        <div class="fs-6 fw-bold font-monospace text-body mb-1 text-truncate">public/storage</div>
                        <div class="text-secondary small">Direct symbolic linkage from storage/app/public</div>
                    </div>
                    <div class="pt-3 border-top mt-3 text-secondary small d-flex justify-content-between align-items-center">
                        <span>Upload Path:</span>
                        <span class="badge bg-secondary-subtle text-secondary rounded-pill px-2 py-1 font-monospace">Accessible</span>
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-6 col-xl-4">
                <div class="sys-arch-box">
                    <div>
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <div class="d-flex align-items-center gap-2">
                                <div class="d-flex align-items-center justify-content-center rounded text-primary" style="width: 32px; height: 32px; background: rgba(var(--accent-rgb), 0.12);">
                                    <i class="fa-solid fa-clock"></i>
                                </div>
                                <span class="fw-bold small text-body">System Timezone</span>
                            </div>
                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill font-monospace px-2 py-1 small">UTC{{ date('P') }}</span>
                        </div>
                        <div class="fs-6 fw-bold font-monospace text-body mb-1">{{ $telemetry['app']['timezone'] }}</div>
                        <div class="text-secondary small">Server clock synchronization &amp; schedule timezone</div>
                    </div>
                    <div class="pt-3 border-top mt-3 text-secondary small d-flex justify-content-between align-items-center">
                        <span>Current Time:</span>
                        <span class="fw-bold text-body font-monospace">{{ date('Y-m-d H:i:s') }}</span>
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-6 col-xl-4">
                <div class="sys-arch-box">
                    <div>
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <div class="d-flex align-items-center gap-2">
                                <div class="d-flex align-items-center justify-content-center rounded text-primary" style="width: 32px; height: 32px; background: rgba(var(--accent-rgb), 0.12);">
                                    <i class="fa-solid fa-envelope"></i>
                                </div>
                                <span class="fw-bold small text-body">Mail Protocol</span>
                            </div>
                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill font-monospace px-2 py-1 small">{{ strtoupper($telemetry['services']['mail_mailer']) }}</span>
                        </div>
                        <div class="fs-6 fw-bold font-monospace text-body mb-1">{{ strtoupper($telemetry['services']['mail_mailer']) }} Transport</div>
                        <div class="text-secondary small">Outbound notifications and transactional delivery</div>
                    </div>
                    <div class="pt-3 border-top mt-3 text-secondary small d-flex justify-content-between align-items-center">
                        <span>Locale:</span>
                        <span class="fw-bold text-body font-monospace">{{ strtoupper($telemetry['app']['locale']) }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="sys-panel">
        <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3 mb-4 pb-3 border-bottom">
            <div>
                <div class="d-flex align-items-center gap-2 mb-1">
                    <h5 class="fw-bold mb-0 d-flex align-items-center gap-2 text-body">
                        <i class="fa-solid fa-puzzle-piece text-primary"></i>
                        <span>Laravel Required Extensions Matrix</span>
                    </h5>
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-2 py-1 fw-semibold small">
                        {{ collect($telemetry['extensions'])->where('loaded', true)->count() }} / {{ count($telemetry['extensions']) }} Active
                    </span>
                </div>
                <small class="text-secondary">Cryptographic, database, image processing, string parsing, and compression modules required for maximum throughput.</small>
            </div>

            <div class="d-flex align-items-center gap-2 flex-wrap">
                <div class="position-relative w-100 w-sm-auto">
                    <i class="fa-solid fa-magnifying-glass position-absolute text-muted" style="left: 12px; top: 50%; transform: translateY(-50%); font-size: 0.8rem;"></i>
                    <input type="text" id="extensionSearchInput" class="sys-search-input" placeholder="Search extensions..." oninput="filterExtensionCards()">
                </div>
                <div class="d-flex align-items-center gap-1 flex-wrap" id="extensionFilterPills">
                    <button type="button" class="sys-filter-pill active" onclick="setExtensionFilter('all', this)">All ({{ count($telemetry['extensions']) }})</button>
                    <button type="button" class="sys-filter-pill" onclick="setExtensionFilter('active', this)">Active ({{ collect($telemetry['extensions'])->where('loaded', true)->count() }})</button>
                    <button type="button" class="sys-filter-pill" onclick="setExtensionFilter('Core & Framework', this)">Core</button>
                    <button type="button" class="sys-filter-pill" onclick="setExtensionFilter('Security & Cryptography', this)">Security</button>
                    <button type="button" class="sys-filter-pill" onclick="setExtensionFilter('Database & Drivers', this)">Database</button>
                    <button type="button" class="sys-filter-pill" onclick="setExtensionFilter('Media & Images', this)">Media</button>
                </div>
            </div>
        </div>

        <div class="row g-3" id="extensionGrid">
            @foreach($telemetry['extensions'] as $key => $ext)
                <div class="col-12 col-sm-6 col-md-4 col-xl-3 extension-item"
                     data-name="{{ strtolower($ext['name']) }}"
                     data-category="{{ $ext['category'] }}"
                     data-loaded="{{ $ext['loaded'] ? 'true' : 'false' }}">
                    <div class="sys-ext-card">
                        <div>
                            <div class="d-flex align-items-start justify-content-between gap-2 mb-2">
                                <div>
                                    <div class="fw-bold text-body small d-flex align-items-center gap-1">
                                        <span>{{ $ext['name'] }}</span>
                                    </div>
                                    <span class="badge bg-secondary-subtle text-secondary rounded-pill px-2 py-1 mt-1" style="font-size: 0.68rem;">
                                        {{ $ext['category'] }}
                                    </span>
                                </div>
                                @if($ext['loaded'])
                                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2 py-1 small flex-shrink-0">
                                        <i class="fa-solid fa-check me-1"></i> Active
                                    </span>
                                @else
                                    <span class="badge {{ $ext['required'] ? 'bg-danger-subtle text-danger border border-danger-subtle' : 'bg-warning-subtle text-warning border border-warning-subtle' }} rounded-pill px-2 py-1 small flex-shrink-0">
                                        <i class="fa-solid fa-xmark me-1"></i> {{ $ext['required'] ? 'Missing' : 'Optional' }}
                                    </span>
                                @endif
                            </div>
                            <div class="text-secondary mb-3" style="font-size: 0.76rem; line-height: 1.45;">
                                {{ $ext['desc'] }}
                            </div>
                        </div>
                        <div class="d-flex align-items-center justify-content-between pt-2 border-top text-secondary" style="font-size: 0.72rem;">
                            <span>Module Version:</span>
                            <span class="font-monospace fw-bold text-body">{{ $ext['version'] }}</span>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div id="noExtensionFound" class="text-center py-5 d-none">
            <div class="d-flex align-items-center justify-content-center mx-auto rounded-circle mb-3" style="width: 50px; height: 50px; background: rgba(var(--accent-rgb), 0.1); color: var(--accent);">
                <i class="fa-solid fa-magnifying-glass fs-5"></i>
            </div>
            <h6 class="fw-bold text-body mb-1">No extensions matching criteria</h6>
            <p class="text-secondary small mb-0">Try adjusting your search query or filter options.</p>
        </div>
    </div>
</div>

<script>
    let activeCategoryFilter = 'all';

    function setExtensionFilter(category, button) {
        activeCategoryFilter = category;
        const buttons = document.querySelectorAll('#extensionFilterPills .sys-filter-pill');
        buttons.forEach(b => b.classList.remove('active'));
        button.classList.add('active');
        filterExtensionCards();
    }

    function filterExtensionCards() {
        const query = (document.getElementById('extensionSearchInput').value || '').trim().toLowerCase();
        const items = document.querySelectorAll('#extensionGrid .extension-item');
        let visibleCount = 0;

        items.forEach(item => {
            const name = item.getAttribute('data-name');
            const category = item.getAttribute('data-category');
            const loaded = item.getAttribute('data-loaded');

            let matchesFilter = true;
            if (activeCategoryFilter === 'active') {
                matchesFilter = (loaded === 'true');
            } else if (activeCategoryFilter !== 'all') {
                matchesFilter = (category.toLowerCase() === activeCategoryFilter.toLowerCase());
            }

            let matchesQuery = true;
            if (query.length > 0) {
                matchesQuery = name.includes(query) || category.toLowerCase().includes(query);
            }

            if (matchesFilter && matchesQuery) {
                item.classList.remove('d-none');
                visibleCount++;
            } else {
                item.classList.add('d-none');
            }
        });

        const noFoundEl = document.getElementById('noExtensionFound');
        if (visibleCount === 0) {
            noFoundEl.classList.remove('d-none');
        } else {
            noFoundEl.classList.add('d-none');
        }
    }

    function copySnippet(elementId, successMsg) {
        const el = document.getElementById(elementId);
        if (!el) return;
        const text = el.innerText.trim();
        navigator.clipboard.writeText(text).then(() => {
            if (typeof window.showToast === 'function') {
                window.showToast(successMsg || 'Copied to clipboard!', 'success');
            } else if (typeof window.showAdminToast === 'function') {
                window.showAdminToast('success', successMsg || 'Copied to clipboard!');
            }
        }).catch(() => {
            const ta = document.createElement('textarea');
            ta.value = text;
            document.body.appendChild(ta);
            ta.select();
            document.execCommand('copy');
            document.body.removeChild(ta);
            if (typeof window.showToast === 'function') {
                window.showToast(successMsg || 'Copied to clipboard!', 'success');
            }
        });
    }

    function copyFullDiagnostics(e) {
        const specs = [
            '# System & Server Diagnostics Report',
            'PHP Version: ' + '{{ $telemetry['overview']['php_version'] }}' + ' (' + '{{ $telemetry['overview']['php_sapi'] }}' + ')',
            'Laravel Version: v' + '{{ $telemetry['app']['laravel_version'] }}' + ' (' + '{{ strtoupper($telemetry['app']['env']) }}' + ')',
            'CPU Cores: ' + '{{ $telemetry['overview']['cpu_cores'] }}',
            'CPU Architecture: ' + '{{ $telemetry['hardware']['cpu_architecture'] }}',
            'RAM Script Limit: ' + '{{ $telemetry['overview']['memory_limit'] }}',
            'Node Total RAM: ' + '{{ $telemetry['hardware']['node_total_ram'] }}',
            'Container RAM Limit: ' + '{{ $telemetry['hardware']['account_ram_limit'] }}',
            'Web Server: ' + '{{ $telemetry['overview']['web_server_full'] }}',
            'OS Environment: ' + '{{ $telemetry['hardware']['os_environment'] }}',
            'Total Hosted Users: ' + '{{ $telemetry['hardware']['hosted_users'] }}',
            'Server Disk Usage: ' + '{{ $telemetry['hardware']['disk_usage'] }}',
            'Max Execution Time: ' + '{{ $telemetry['php_directives']['max_execution_time'] }}',
            'Upload Max Filesize: ' + '{{ $telemetry['php_directives']['upload_max_filesize'] }}',
            'Post Max Size: ' + '{{ $telemetry['php_directives']['post_max_size'] }}',
            'Max Input Vars: ' + '{{ $telemetry['php_directives']['max_input_vars'] }}',
            'OPcache: ' + '{{ $telemetry['php_directives']['opcache_installed'] }}',
            'Database: ' + '{{ ucfirst($telemetry['database']['driver']) }}' + ' (' + '{{ $telemetry['database']['version'] }}' + ')',
            'Cache / Session: ' + '{{ ucfirst($telemetry['services']['cache_driver']) }}' + ' / ' + '{{ ucfirst($telemetry['services']['session_driver']) }}'
        ].join('\n');

        navigator.clipboard.writeText(specs).then(() => {
            if (typeof window.showToast === 'function') {
                window.showToast('Full system diagnostic report copied to clipboard!', 'success');
            } else if (typeof window.showAdminToast === 'function') {
                window.showAdminToast('success', 'Full system diagnostic report copied to clipboard!');
            }
        }).catch(() => {
            const ta = document.createElement('textarea');
            ta.value = specs;
            document.body.appendChild(ta);
            ta.select();
            document.execCommand('copy');
            document.body.removeChild(ta);
            if (typeof window.showToast === 'function') {
                window.showToast('Full system diagnostic report copied to clipboard!', 'success');
            }
        });
    }

    function purgeSystemCache(e) {
        if (e && e.preventDefault) e.preventDefault();
        const btn = document.getElementById('btnPurgeCache');
        const origHtml = btn ? btn.innerHTML : '';
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status"></span>Purging...';
        }

        axios.post('{{ route("admin.system.clear_cache") }}')
        .then(res => {
            const data = res.data;
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = origHtml;
            }
            if (data.status) {
                if (typeof window.showToast === 'function') {
                    window.showToast(data.message || 'System caches successfully purged!', 'success');
                } else if (typeof window.showAdminToast === 'function') {
                    window.showAdminToast('success', data.message || 'System caches successfully purged!');
                }
            } else {
                if (typeof window.showToast === 'function') {
                    window.showToast(data.message || 'Failed to purge caches.', 'error');
                }
            }
        })
        .catch(err => {
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = origHtml;
            }
            if (typeof window.showToast === 'function') {
                window.showToast('Network error while purging caches.', 'error');
            }
        });
    }
</script>
@endsection
