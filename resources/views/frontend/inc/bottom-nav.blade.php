<style>
.zk-bottom-nav {
    position: fixed;
    bottom: 0;
    left: 0;
    right: 0;
    height: calc(64px + env(safe-area-inset-bottom, 0px));
    display: flex;
    align-items: stretch;
    background: transparent;
    z-index: 1000;
    user-select: none;
    filter: drop-shadow(0 -8px 20px rgba(0, 0, 0, 0.08));
    pointer-events: none;
}
.zk-nav-wing {
    flex: 1;
    background: #ffffff;
    display: flex;
    align-items: center;
    justify-content: space-around;
    height: 100%;
    padding-bottom: env(safe-area-inset-bottom, 0px);
    pointer-events: auto;
}
.zk-nav-wing.left {
	border-top-left-radius: 24px;
	margin-right: -1px;
	border-top-right-radius: 13px;
}
.zk-nav-wing.right {
    border-top-right-radius: 24px;
    margin-left: -1px;
	border-top-left-radius: 13px;
}
.zk-nav-item {
    flex: 1;
    height: 100%;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    color: #475569;
    text-decoration: none;
    font-size: 11px;
    font-weight: 500;
    gap: 5px;
    background: transparent;
    border: none;
    padding: 0;
    margin: 0;
    cursor: pointer;
    outline: none;
    transition: transform 0.15s ease, color 0.15s ease;
    -webkit-tap-highlight-color: transparent;
}
.zk-nav-item i {
    font-size: 18px;
    line-height: 1;
    color: #475569;
    transition: color 0.15s ease;
}
.zk-nav-item.active {
    color: #7c3aed;
}
.zk-nav-item.active i {
    color: #7c3aed;
}
.zk-nav-center-slot {
    width: 80px !important;
    height: 100%;
    position: relative;
    flex-shrink: 0;
    display: flex;
    justify-content: center;
    pointer-events: auto;
}
.zk-nav-center-slot::before {
	content: "";
	position: absolute;
	top: 0;
	left: -10px;
	right: 0;
	height: 55px;
	background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 122.651 64' preserveAspectRatio='none'%3E%3Cpath fill='%23fff' d='M 0,0 C 16.138,0 19.366,6 25.821,20 C 35.504,48 87.147,48 96.83,20 C 103.285,6 106.513,0 122.651,0 L 122.651,64 L 0,64 Z' /%3E%3C/svg%3E");
	background-size: 100% 110%;
	background-repeat: no-repeat;
	z-index: 1;
	width: 100px;
}
.zk-nav-center-slot::after {
	content: "";
	position: absolute;
	top: 50px;
	left: -2px;
	right: -2px;
	bottom: 0;
	background: #fff;
	z-index: 1;
}
.zk-fab-btn {
    position: absolute;
    top: -22px;
    width: 56px;
    height: 56px;
    border-radius: 50%;
    background: #0f172a;
    border: none;
    color: #ffffff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    box-shadow: 0 10px 24px rgba(124, 58, 237, 0.31);
    text-decoration: none;
    cursor: pointer;
    padding: 0;
    margin: 0;
    outline: none;
    transition: transform 0.18s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.18s ease;
    -webkit-tap-highlight-color: transparent;
    z-index: 5;
}
.zk-fab-btn:active {
    transform: translateY(2px) scale(0.95);
    box-shadow: 0 6px 16px rgba(124, 58, 237, 0.55);
}
.zk-badge {
	position: absolute;
	top: -2px;
	right: -2px;
	background: #0f172a;
	color: #ffffff;
	font-size: 10px;
	font-weight: 700;
	min-width: 20px;
	height: 20px;
	padding: 0px 5px;
	border-radius: 10px;
	display: flex;
	align-items: center;
	justify-content: center;
	border: 1px solid #d59c72;
	line-height: 1;
	pointer-events: none;
	z-index: 6;
}
@media (min-width: 992px) {
    .zk-bottom-nav {
        display: none !important;
    }
}
</style>

<nav class="zk-bottom-nav d-flex d-lg-none" aria-label="Mobile Navigation">
    <div class="zk-nav-wing left">
        <a href="{{ route('home') }}" class="zk-nav-item {{ request()->routeIs('home') ? 'active' : '' }}">
            <i class="fa-duotone fa-solid fa-house fa-jello"></i>
            <span>হোম</span>
        </a>
        <button type="button" onclick="openCategorySheet()" class="zk-nav-item">
            <i class="fa-duotone fa-solid fa-grid-2 fa-wag"></i>
            <span>ক্যাটাগরি</span>
        </button>
    </div>

    <div class="zk-nav-center-slot">
        @if(request()->routeIs('checkout*'))
            <a href="#checkoutForm" class="zk-fab-btn" aria-label="চেকআউট">
                <i class="fa-jelly fa-regular fa-bag-shopping fa-float"></i>
            </a>
        @else
            <button type="button" onclick="openCartDrawer()" class="zk-fab-btn" aria-label="কার্ট">
                <i class="fa-jelly fa-regular fa-bag-shopping fa-float"></i>
                <span class="zk-badge cart-count-badge">0</span>
            </button>
        @endif
    </div>

    <div class="zk-nav-wing right">
        <a href="{{ route('order.track') }}" class="zk-nav-item {{ request()->routeIs('order.track') ? 'active' : '' }}">
            <i class="fa-duotone fa-solid fa-truck-bolt fa-buzz"></i>
            <span>ট্র্যাকিং</span>
        </a>
        @auth
            <a href="{{ route('customer.account') }}" class="zk-nav-item {{ request()->routeIs('customer.account') ? 'active' : '' }}">
                <i class="fa-duotone fa-solid fa-user fa-wag"></i>
                <span>প্রোফাইল</span>
            </a>
        @else
            <a href="{{ route('login') }}" class="zk-nav-item {{ request()->routeIs('login') ? 'active' : '' }}">
                <i class="fa-duotone fa-solid fa-user fa-wag"></i>
                <span>লগইন</span>
            </a>
        @endauth
    </div>
</nav>