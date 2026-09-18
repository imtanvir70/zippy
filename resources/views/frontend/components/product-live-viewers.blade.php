@if(theme_setting('live_viewers_enabled', true))
<div class="d-inline-flex align-items-center gap-2 py-1 px-2.5 rounded-pill bg-light border border-slate-200 small" style="border-color: #e2e8f0 !important; font-size: 0.78rem; padding: 15px;">
    <span class="position-relative d-inline-flex align-items-center justify-content-center" style="width: 10px; height: 10px;">
        <span class="position-absolute w-100 h-100 rounded-circle bg-success opacity-75 live-viewers-ping"></span>
        <span class="rounded-circle bg-success" style="width: 7px; height: 7px;"></span>
    </span>
    <span class="text-secondary fw-semibold">
        <strong class="text-dark font-monospace" id="liveViewersCount">12</strong> জন এই প্রোডাক্টটি এখন দেখছেন
    </span>
</div>

<style>
@keyframes liveViewerPulse {
    0% { transform: scale(0.95); opacity: 0.8; }
    50% { transform: scale(1.6); opacity: 0; }
    100% { transform: scale(0.95); opacity: 0; }
}
.live-viewers-ping {
    animation: liveViewerPulse 2s cubic-bezier(0, 0, 0.2, 1) infinite;
}
</style>

<script>
(function() {
    if (window.__liveViewersInterval) {
        clearInterval(window.__liveViewersInterval);
        window.__liveViewersInterval = null;
    }

    const min = {{ (int) theme_setting('live_viewers_min', 8) }};
    const max = {{ (int) theme_setting('live_viewers_max', 22) }};
    const el = document.getElementById('liveViewersCount');
    if (!el) return;

    let current = Math.floor(Math.random() * (max - min + 1)) + min;
    el.textContent = current;

    window.__liveViewersInterval = setInterval(function() {
        const countEl = document.getElementById('liveViewersCount');
        if (!countEl) {
            clearInterval(window.__liveViewersInterval);
            window.__liveViewersInterval = null;
            return;
        }
        const delta = Math.floor(Math.random() * 5) - 2;
        current = Math.min(max, Math.max(min, current + delta));
        countEl.textContent = current;
    }, 4500);
})();
</script>
@endif

