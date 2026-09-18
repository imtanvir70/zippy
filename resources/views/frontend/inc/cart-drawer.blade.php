<div class="offcanvas offcanvas-end responsive-cart-drawer border-0 shadow-lg" tabindex="-1" id="cartDrawer" aria-labelledby="cartDrawerLabel" data-bs-scroll="false" data-bs-backdrop="true" style="overflow-x: hidden !important; z-index: 10585; box-sizing: border-box !important;">
    <div class="sheet-drag-handle mt-2 mb-1 mx-auto rounded-pill d-lg-none" style="width: 40px; height: 4px; background-color: #cbd5e1;"></div>

    <div class="offcanvas-header border-bottom py-3 px-4">
        <h5 class="offcanvas-title fw-bold text-dark d-flex align-items-center gap-2 mb-0" id="cartDrawerLabel" style="font-size: 16px;">
            <i class="fa-solid fa-bag-shopping text-dark"></i>
            <span>শপিং কার্ট (<span id="cartDrawerCount">0</span>)</span>
        </h5>
        <button type="button" class="btn-close text-reset shadow-none" data-bs-dismiss="offcanvas" aria-label="Close" style="font-size: 13px;"></button>
    </div>

    <div class="offcanvas-body p-3 p-sm-4 d-flex flex-column" id="cartDrawerBody" style="scrollbar-width: thin; overflow-x: hidden !important; max-width: 100% !important; box-sizing: border-box !important;">
        <div id="cartItems" class="d-flex flex-column mb-3 w-100" style="overflow-x: hidden !important; max-width: 100% !important;"></div>

        <div id="emptyCartMsg" class="text-center py-5 my-auto" style="display: none;">
            <div class="mb-3 text-secondary" style="font-size: 50px;">
                <i class="fa-solid fa-cart-shopping opacity-50"></i>
            </div>
            <h6 class="fw-bold text-dark mb-1">আপনার কার্ট খালি</h6>
            <p class="text-secondary small mb-4">পছন্দের পণ্য যোগ করতে কালেকশন ব্রাউজ করুন</p>
            <button type="button" class="btn rounded-pill px-4 py-2 text-white fw-medium shadow-sm" style="background-color: #0f172a; font-size: 13px;" data-bs-dismiss="offcanvas">
                শপিং শুরু করুন
            </button>
        </div>
    </div>

    <div class="offcanvas-footer border-top p-3 p-sm-4 bg-white mt-auto" id="cartFooterSection" style="display: none;">
        <div class="card p-3 rounded-3 mb-3 border shadow-xs" style="background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%); border-color: #e2e8f0 !important;">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="text-dark fw-bold d-flex align-items-center gap-2" style="font-size: 12px;">
                    <i class="fa-solid fa-wand-magic-sparkles text-primary" style="font-size: 11px;"></i>
                    <span>কুপন কোড দিন</span>
                </span>
                <span class="text-secondary" style="font-size: 11px;">সেভিংস ছাড়</span>
            </div>
            <div id="drawerCouponApplied" class="d-none">
                <div class="d-flex align-items-center justify-content-between p-2.5 rounded-2" style="background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%); border: 1px dashed #10b981;">
                    <div class="d-flex align-items-center gap-2 min-w-0">
                        <i class="fa-solid fa-circle-check text-success small"></i>
                        <span class="fw-bold text-emerald-800 text-uppercase small text-truncate font-monospace" id="drawerCouponCodeText"></span>
                        <span class="text-success-emphasis fw-bold" style="font-size: 11px;" id="drawerCouponDiscountText"></span>
                    </div>
                    <button type="button" class="btn btn-sm btn-link text-danger p-0 text-decoration-none ms-2" onclick="removeDrawerCoupon()" title="কুপন মুছুন">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
            </div>
            <div id="drawerCouponInputGroup">
                <div class="d-flex align-items-center bg-white rounded-pill p-1 border shadow-xs" style="border-color: #cbd5e1 !important;">
                    <input type="text" id="drawerCouponInput" class="form-control border-0 shadow-none text-uppercase font-monospace px-2 bg-transparent py-1" placeholder="কুপন কোড..." style="font-size: 12px; font-weight: 600;" onkeydown="if(event.key==='Enter'){event.preventDefault();applyDrawerCoupon();}">
                    <button type="button" id="drawerCouponBtn" class="btn btn-dark rounded-pill px-3 py-1 fw-bold flex-shrink-0" style="background-color: #0f172a; font-size: 11px;" onclick="applyDrawerCoupon()">
                        <span>এপ্লাই</span>
                    </button>
                </div>
                <div id="drawerCouponFeedback" class="small mt-1 d-none" style="font-size: 11px;"></div>
            </div>
        </div>

        <div class="d-flex justify-content-between align-items-center mb-2">
            <span class="text-secondary fw-semibold small">সাবটোটাল</span>
            <span class="fw-bold text-dark" id="cartDrawerSubtotal">৳ 0</span>
        </div>
        <div id="drawerDiscountRow" class="d-flex justify-content-between align-items-center mb-2 text-success d-none">
            <span class="fw-semibold small">কুপন ডিসকাউন্ট</span>
            <span class="fw-bold" id="cartDrawerDiscount">- ৳ 0</span>
        </div>
        <div id="drawerTotalRow" class="d-flex justify-content-between align-items-center mb-3 pt-2 border-top d-none">
            <span class="fw-bold text-dark small">সর্বমোট</span>
            <span class="fs-5 fw-bold text-danger" id="cartDrawerTotal">৳ 0</span>
        </div>
        <p class="text-secondary mb-3 d-flex align-items-center justify-content-center gap-1 bg-light rounded-2 py-2" id="cartDrawerShippingNotice" style="font-size: 11px;">
            <i class="fa-solid fa-truck-fast text-success"></i>
            <span class="fw-medium" id="cartDrawerShippingNoticeText">ডেলিভারি চার্জ চেকআউট পেইজে যুক্ত হবে</span>
        </p>
        <a href="{{ route('checkout') }}" onclick="closeCartDrawer()" class="btn w-100 py-3 fw-bold rounded-3 d-flex align-items-center justify-content-center gap-2 shadow-sm" style="background-color: #0f172a; color: #fff; font-size: 14px;">
            <span>অর্ডার সম্পন্ন করুন</span>
            <i class="fa-solid fa-arrow-right" style="font-size: 12px;"></i>
        </a>
    </div>
</div>

<style>
#cartDrawerBody {
    overflow-x: hidden !important;
    overflow-y: auto !important;
    max-width: 100% !important;
    width: 100% !important;
    box-sizing: border-box !important;
}

#cartDrawerBody * {
    max-width: 100%;
}

#cartItems,
#cartFooterSection {
    overflow-x: hidden !important;
    width: 100% !important;
    max-width: 100% !important;
    box-sizing: border-box !important;
}

#cartFooterSection {
    padding-bottom: calc(14px + env(safe-area-inset-bottom, 0px)) !important;
}

/* Mobile & Tablet Full-Width Bottom Sheet (< 992px) */
@media (max-width: 991.98px) {
    #cartDrawer.responsive-cart-drawer,
    .responsive-cart-drawer {
        top: auto !important;
        bottom: 0 !important;
        left: 0 !important;
        right: 0 !important;
        width: 100% !important;
        max-width: 100% !important;
        min-width: 100% !important;
        height: 88vh !important;
        height: 88dvh !important;
        max-height: 90vh !important;
        max-height: 90dvh !important;
        margin: 0 !important;
        transform: translateY(100%) !important;
        border-radius: 28px 28px 0 0 !important;
        box-shadow: 0 -10px 40px rgba(0, 0, 0, 0.25) !important;
    }
    #cartDrawer.showing,
    #cartDrawer.show:not(.hiding),
    .responsive-cart-drawer.showing, 
    .responsive-cart-drawer.show:not(.hiding) {
        transform: translateY(0) !important;
    }
}

/* Desktop Right-Side Drawer (>= 992px) */
@media (min-width: 992px) {
    #cartDrawer.responsive-cart-drawer,
    .responsive-cart-drawer {
        top: 0 !important;
        bottom: 0 !important;
        right: 0 !important;
        left: auto !important;
        width: 420px !important;
        max-width: 420px !important;
        height: 100% !important;
        border-top-left-radius: 24px !important;
        border-bottom-left-radius: 24px !important;
        border-radius: 24px 0 0 24px !important;
        transform: translateX(100%) !important;
    }
    #cartDrawer.showing,
    #cartDrawer.show:not(.hiding),
    .responsive-cart-drawer.showing, 
    .responsive-cart-drawer.show:not(.hiding) {
        transform: translateX(0) !important;
    }
}

.responsive-cart-drawer:not(.show):not(.showing) {
    visibility: hidden !important;
}
</style>
