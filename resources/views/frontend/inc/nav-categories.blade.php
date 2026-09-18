<nav class="nav-categories d-none d-lg-block bg-white border-bottom position-relative" style="z-index: 1010;">
    <div class="container">
        <ul class="d-flex align-items-center gap-2 list-unstyled p-0 m-0 overflow-x-auto py-2" style="scrollbar-width:none; -ms-overflow-style:none; flex-wrap:nowrap;">

            <li class="flex-shrink-0">
                <a href="{{ route('home') }}"
                   class="nav-cat-pill {{ request()->routeIs('home') ? 'nav-cat-pill--active' : '' }}">
                    <i class="fa-solid fa-house fa-fade" style="--fa-animation-duration: 3s;"></i>
                    <span>হোম</span>
                </a>
            </li>

            <li class="flex-shrink-0">
                <a href="{{ route('product.index') }}"
                   class="nav-cat-pill {{ request()->routeIs('product.index') ? 'nav-cat-pill--active' : '' }}">
                    <i class="fa-solid fa-boxes-stacked fa-bounce" style="--fa-animation-duration: 4s; --fa-bounce-jump-scale-y: 1.05;"></i>
                    <span>প্রোডাক্টস</span>
                </a>
            </li>

            <li class="flex-shrink-0">
                <a href="{{ route('product.best_sale') }}"
                   class="nav-cat-pill {{ request()->routeIs('product.best_sale') ? 'nav-cat-pill--active' : '' }}">
                    <i class="fa-solid fa-fire fa-beat" style="color:#ef4444; --fa-animation-duration: 2s; --fa-beat-scale: 1.15;"></i>
                    <span>বেস্ট সেল</span>
                </a>
            </li>

            <li class="flex-shrink-0">
                <a href="{{ route('product.new_collection') }}"
                   class="nav-cat-pill {{ request()->routeIs('product.new_collection') ? 'nav-cat-pill--active' : '' }}">
                    <i class="fa-solid fa-sparkles fa-flip" style="color:#2563eb; --fa-animation-duration: 3s;"></i>
                    <span>নতুন কালেকশন</span>
                </a>
            </li>

            <li class="flex-shrink-0">
                <a href="{{ route('product.flash_deals') }}"
                   class="nav-cat-pill {{ request()->routeIs('product.flash_deals') ? 'nav-cat-pill--active' : '' }}">
                    <i class="fa-solid fa-bolt fa-shake" style="color:#f59e0b; --fa-animation-duration: 2.5s;"></i>
                    <span>ফ্ল্যাশ ডিল</span>
                </a>
            </li>

            <li class="flex-shrink-0">
                <a href="{{ route('order.track') }}"
                   class="nav-cat-pill {{ request()->routeIs('order.track') ? 'nav-cat-pill--active' : '' }}">
                    <i class="fa-duotone fa-solid fa-truck-fast fa-buzz" style="color:#0891b2; --fa-animation-duration: 3s; --fa-bounce-jump-scale-y: 1.1;"></i>
                    <span>অর্ডার ট্র্যাকিং</span>
                </a>
            </li>

            <li class="flex-shrink-0">
                <a href="https://wa.me/8801700000000" target="_blank" class="nav-cat-pill">
                    <i class="fa-brands fa-whatsapp fa-beat" style="color:#25d366; --fa-animation-duration: 2s; --fa-beat-scale: 1.15;"></i>
                    <span>কাস্টমার সাপোর্ট</span>
                </a>
            </li>

        </ul>
    </div>
</nav>

<style>
.nav-cat-pill {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 14px;
    border-radius: 999px;
    font-size: 12.5px;
    font-weight: 500;
    color: #475569;
    text-decoration: none;
    background: #f1f5f9;
    border: 1px solid #e2e8f0;
    white-space: nowrap;
    transition: background .15s, color .15s, border-color .15s, box-shadow .15s;
    line-height: 1.3;
}
.nav-cat-pill:hover {
    background: #e2e8f0;
    color: #0f172a;
    border-color: #cbd5e1;
}
.nav-cat-pill--active {
    background: #121629;
    color: #ffffff !important;
    box-shadow: inset 0px 0px 10px rgb(255 255 255);
    font-weight: 600;
    border: none;
}
.nav-cat-pill--active i {
    color: #ffffff !important;
}
.nav-cat-pill--active:hover {
    background: #121629;
    color: #ffffff !important;
    box-shadow: inset 0px 0px 10px rgb(255 255 255);
    border: none;
}
</style>
