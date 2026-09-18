<div class="top-bar d-none d-md-block w-100 py-1 border-bottom" style="background-color: #090d16;color: #cbd5e1;font-size: 12px;z-index: 20;border-color: rgba(255,255,255,0.1) !important;padding: 8px 0 !important;">
    <div class="container d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center gap-2 gap-lg-3 min-w-0">
            <span class="d-flex align-items-center gap-1 text-truncate">
                <i class="fa-duotone fa-solid fa-truck-fast fa-buzz flex-shrink-0" style="--fa-animation-duration: 3s; --fa-bounce-jump-scale-y: 1.1;"></i>
                <span class="text-truncate" style="max-width: 320px;">{{ $settings['announcement_text'] ?? 'সারা দেশে ক্যাশ অন ডেলিভারি' }}</span>
            </span>
            <div class="d-none d-xl-block flex-shrink-0" style="width: 1px; height: 12px; background: rgba(255,255,255,0.2);"></div>
            <span class="d-none d-xl-flex align-items-center gap-1 flex-shrink-0">
                <i class="fa-solid fa-shield-halved fa-beat-fade text-info" style="--fa-animation-duration: 3s;"></i>
                <span>১০০% জেনুইন প্রোডাক্ট</span>
            </span>
            <div class="d-none d-lg-block flex-shrink-0" style="width: 1px; height: 12px; background: rgba(255,255,255,0.2);"></div>
            <span class="d-none d-lg-flex align-items-center gap-1 flex-shrink-0">
                <i class="fa-solid fa-rotate-left fa-spin-pulse text-success" style="--fa-animation-duration: 4s;"></i>
                <span>৭ দিন রিপ্লেসমেন্ট</span>
            </span>
        </div>

        <div class="d-flex align-items-center gap-2 gap-lg-3 flex-shrink-0">
            @php
                $topPhone = $settings['store_phone'] ?? '01700-000000';
                $topWa = preg_replace('/[^0-9]/', '', $settings['store_whatsapp'] ?? '01700000000');
                if (strlen($topWa) === 11 && str_starts_with($topWa, '01')) {
                    $topWa = '88' . $topWa;
                }
            @endphp
            <a href="https://wa.me/{{ $topWa }}" target="_blank" class="text-decoration-none text-light d-flex align-items-center gap-1">
                <i class="fa-brands fa-whatsapp fa-beat text-success" style="--fa-animation-duration: 2s; --fa-beat-scale: 1.15;"></i>
                <span>{{ $topPhone }}</span>
            </a>
            <div style="width: 1px; height: 12px; background: rgba(255,255,255,0.2);"></div>
            <a href="{{ route('order.history') }}" class="text-decoration-none text-light d-none d-lg-flex align-items-center gap-1">
                <i class="fa-solid fa-clock-rotate-left fa-flip text-warning" style="--fa-animation-duration: 4s;"></i>
                <span>অর্ডার হিস্ট্রি</span>
            </a>
            <div class="d-none d-lg-block" style="width: 1px; height: 12px; background: rgba(255,255,255,0.2);"></div>
            <a href="{{ route('order.track') }}" class="text-decoration-none text-light">
                <span>ট্র্যাকিং</span>
            </a>
            <div style="width: 1px; height: 12px; background: rgba(255,255,255,0.2);"></div>
            @auth
                <a href="{{ route('customer.account') }}" class="text-decoration-none text-light d-flex align-items-center gap-1">
                    <i class="fa-solid fa-user fa-fade text-secondary" style="--fa-animation-duration: 2.5s;"></i>
                    <span>{{ auth()->user()->name }}</span>
                </a>
            @else
                <a href="{{ route('login') }}" class="text-decoration-none text-light d-flex align-items-center gap-1">
                    <i class="fa-solid fa-right-to-bracket fa-fade text-secondary" style="--fa-animation-duration: 2s;"></i>
                    <span>লগইন</span>
                </a>
            @endauth
        </div>
    </div>
</div>
