@if(theme_setting('recent_sales_toast_enabled', true))
<div id="recentSalesToastWrapper" style="display: none;" aria-live="polite">
    <div class="sales-toast-card" id="recentSalesToastCard">
        <div class="d-flex align-items-center gap-2">
            <div class="toast-thumb-wrap">
                <img src="{{ asset('images/product-placeholder.svg') }}" id="toastProductThumb" alt="Recent Product" width="36" height="36" loading="lazy" onerror="this.onerror=null;this.src='{{ asset('images/product-placeholder.svg') }}';">
                <span class="toast-thumb-check"><i class="fa-solid fa-check"></i></span>
            </div>
            <div class="flex-grow-1 min-w-0" style="min-width: 0;">
                <div class="d-flex align-items-center justify-content-between mb-0">
                    <div class="d-flex align-items-center gap-1 min-w-0 pe-1" style="min-width: 0;">
                        <span class="sales-live-dot flex-shrink-0"></span>
                        <span class="toast-buyer-text text-truncate">
                            <span class="toast-buyer-name" id="toastCustomerInfo">তা*** (ঢাকা)</span>
                            <span class="toast-buyer-action d-none d-sm-inline">অর্ডার করেছেন</span>
                        </span>
                    </div>
                    <button type="button" class="toast-close-btn flex-shrink-0" id="toastDismissBtn" aria-label="Close">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
                <div class="text-truncate fw-medium toast-product-name my-0" id="toastProductTitle">
                    প্রোডাক্টের নাম
                </div>
                <div class="d-flex align-items-center gap-1.5 toast-meta-row mt-0">
                    <span class="toast-meta-price" id="toastProductPrice">৳ ০</span>
                    <span class="text-muted opacity-50">•</span>
                    <span id="toastTimeAgo">এইমাত্র</span>
                </div>
            </div>
        </div>
        <div class="toast-progress" id="toastProgressBar"></div>
    </div>
</div>

<style>
#recentSalesToastWrapper {
    position: fixed;
    z-index: 1;
    transition: opacity 0.35s cubic-bezier(0.16, 1, 0.3, 1), transform 0.35s cubic-bezier(0.16, 1, 0.3, 1);
    transform: translateY(16px) scale(0.98);
    opacity: 0;
    pointer-events: none;
}
#recentSalesToastWrapper.is-visible {
    opacity: 1;
    transform: translateY(0) scale(1);
    pointer-events: auto;
}
@media (max-width: 767.98px) {
    #recentSalesToastWrapper {
        bottom: calc(90px + env(safe-area-inset-bottom, 0px));
        left: 8px;
        right: auto;
        max-width: 230px;
        width: auto;
        margin: 0;
    }
}
@media (min-width: 768px) {
    #recentSalesToastWrapper {
        bottom: 20px;
        left: 20px;
        width: 250px;
        max-width: 250px;
    }
}
.sales-toast-card {
    background: rgba(255, 255, 255, 0.96);
    backdrop-filter: blur(16px);
    -webkit-backdrop-filter: blur(16px);
    border: 1px solid rgba(15, 23, 42, 0.08);
    box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.1), 0 3px 8px -2px rgba(15, 23, 42, 0.04);
    position: relative;
    overflow: hidden;
    cursor: pointer;
    width: 100%;
    transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
    z-index: 1036;
}
@media (max-width: 767.98px) {
    .sales-toast-card {
        padding: 5px 8px;
        border-radius: 9px;
        box-shadow: 0 8px 18px -4px rgba(15, 23, 42, 0.1), 0 2px 6px -2px rgba(15, 23, 42, 0.04);
        max-width: 230px;
    }
    .toast-thumb-wrap {
        width: 32px;
        height: 32px;
        border-radius: 6px;
    }
    .toast-thumb-check {
        width: 11px;
        height: 11px;
        font-size: 5.5px;
        bottom: -1px;
        right: -1px;
        border-width: 1px;
    }
    .sales-live-dot {
        width: 5px;
        height: 5px;
    }
    .toast-buyer-text {
        font-size: 9.5px;
    }
    .toast-product-name {
        font-size: 10.5px;
        line-height: 1.2;
    }
    .toast-meta-row {
        font-size: 9px;
    }
    .toast-meta-price {
        font-size: 10px;
    }
    .toast-close-btn {
        width: 15px;
        height: 15px;
        font-size: 7.5px;
    }
}
@media (min-width: 768px) {
    .sales-toast-card {
        padding: 6px 10px;
        border-radius: 11px;
        max-width: 250px;
    }
    .toast-thumb-wrap {
        width: 36px;
        height: 36px;
        border-radius: 8px;
    }
    .toast-thumb-check {
        width: 12px;
        height: 12px;
        font-size: 6px;
        bottom: -2px;
        right: -2px;
        border-width: 1.5px;
    }
    .sales-live-dot {
        width: 6px;
        height: 6px;
    }
    .toast-buyer-text {
        font-size: 10.5px;
    }
    .toast-product-name {
        font-size: 11.5px;
        line-height: 1.25;
    }
    .toast-meta-row {
        font-size: 10px;
    }
    .toast-meta-price {
        font-size: 11px;
    }
    .toast-close-btn {
        width: 17px;
        height: 17px;
        font-size: 8.5px;
    }
}
.sales-toast-card:hover {
    transform: translateY(-2px);
    border-color: rgba(15, 23, 42, 0.15);
    box-shadow: 0 14px 28px -6px rgba(15, 23, 42, 0.14), 0 4px 10px -2px rgba(15, 23, 42, 0.05);
}
.min-w-0 {
    min-width: 0;
}
.toast-thumb-wrap {
    background: #f8fafc;
    border: 1px solid rgba(15, 23, 42, 0.06);
    overflow: hidden;
    flex-shrink: 0;
    position: relative;
}
.toast-thumb-wrap img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.toast-thumb-check {
    position: absolute;
    background: #0f172a;
    color: #ffffff;
    border-style: solid;
    border-color: #ffffff;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}
.sales-live-dot {
    background: #10b981;
    border-radius: 50%;
    display: inline-block;
    box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7);
    animation: salesToastPulse 2s infinite;
}
@keyframes salesToastPulse {
    0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7); }
    70% { transform: scale(1); box-shadow: 0 0 0 5px rgba(16, 185, 129, 0); }
    100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
}
.toast-buyer-text {
    line-height: 1.2;
    color: #475569;
}
.toast-buyer-name {
    font-weight: 700;
    color: #0f172a;
}
.toast-buyer-action {
    color: #64748b;
    margin-left: 2px;
}
.toast-product-name {
    font-weight: 600;
    color: #0f172a;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.toast-meta-row {
    color: #64748b;
    line-height: 1;
}
.toast-meta-price {
    font-weight: 700;
    color: #0f172a;
}
.toast-close-btn {
    border-radius: 50%;
    border: none;
    background: transparent;
    color: #94a3b8;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 0;
    transition: all 0.2s ease;
}
.toast-close-btn:hover {
    background: rgba(15, 23, 42, 0.08);
    color: #0f172a;
}
.toast-progress {
    position: absolute;
    bottom: 0;
    left: 0;
    height: 2px;
    background: #0f172a;
    width: 0%;
    border-radius: 0 0 16px 16px;
    opacity: 0.85;
}
</style>

<script>
(function() {
    const DISMISS_KEY = 'zippy_recent_sales_dismissed';
    let salesQueue = [];
    let currentIndex = 0;
    let autoHideTimer = null;
    let cycleTimer = null;
    let isHovered = false;
    let activeProductUrl = '';

    const INITIAL_DELAY = 6000;
    const DISPLAY_DURATION = 5000;
    const CYCLE_INTERVAL = 35000;

    function getElements() {
        return {
            wrapper: document.getElementById('recentSalesToastWrapper'),
            card: document.getElementById('recentSalesToastCard'),
            thumbImg: document.getElementById('toastProductThumb'),
            customerInfo: document.getElementById('toastCustomerInfo'),
            productTitle: document.getElementById('toastProductTitle'),
            productPrice: document.getElementById('toastProductPrice'),
            timeAgo: document.getElementById('toastTimeAgo'),
            dismissBtn: document.getElementById('toastDismissBtn'),
            progressBar: document.getElementById('toastProgressBar')
        };
    }

    function bindEvents() {
        const els = getElements();
        if (!els.wrapper || !els.card) return;

        if (els.dismissBtn) {
            els.dismissBtn.onclick = function(e) {
                e.stopPropagation();
                sessionStorage.setItem(DISMISS_KEY, '1');
                hideToast();
                if (cycleTimer) clearInterval(cycleTimer);
                if (autoHideTimer) clearTimeout(autoHideTimer);
            };
        }

        els.card.onclick = function(e) {
            if (e.target.closest('#toastDismissBtn')) return;
            if (activeProductUrl && activeProductUrl !== '#') {
                if (typeof Turbo !== 'undefined') {
                    Turbo.visit(activeProductUrl);
                } else {
                    window.location.href = activeProductUrl;
                }
            }
        };

        els.card.onmouseenter = function() {
            isHovered = true;
            if (autoHideTimer) clearTimeout(autoHideTimer);
            if (els.progressBar) {
                const computedWidth = window.getComputedStyle(els.progressBar).width;
                els.progressBar.style.transition = 'none';
                els.progressBar.style.width = computedWidth;
            }
        };

        els.card.onmouseleave = function() {
            isHovered = false;
            if (els.wrapper.classList.contains('is-visible')) {
                autoHideTimer = setTimeout(hideToast, 1500);
            }
        };
    }

    function showToast(item) {
        if (!item || sessionStorage.getItem(DISMISS_KEY) === '1') return;
        const els = getElements();
        if (!els.wrapper || !els.card) return;

        activeProductUrl = item.product_url || '#';
        if (els.thumbImg) {
            els.thumbImg.src = item.product_image || '{{ asset('images/product-placeholder.svg') }}';
        }

        let nameDisplay = item.name || 'তা*** (ঢাকা)';
        if (item.location) {
            nameDisplay += ', ' + item.location.replace(/,\s*ঢাকা/, '').trim();
        }
        if (els.customerInfo) els.customerInfo.textContent = nameDisplay;
        if (els.productTitle) els.productTitle.textContent = item.product_title || 'প্রিমিয়াম প্রোডাক্ট';
        if (els.productPrice) els.productPrice.textContent = item.price || '';

        const allowedTimes = ['এইমাত্র', '১ মিনিট আগে', '২ মিনিট আগে', 'Just now', 'a min ago', '2 min ago'];
        let displayTime = item.time_ago || 'এইমাত্র';
        if (!allowedTimes.includes(displayTime)) {
            displayTime = 'এইমাত্র';
        }
        if (els.timeAgo) els.timeAgo.textContent = displayTime;

        els.wrapper.style.display = 'block';
        requestAnimationFrame(function() {
            requestAnimationFrame(function() {
                els.wrapper.classList.add('is-visible');
                if (els.progressBar) {
                    els.progressBar.style.transition = 'none';
                    els.progressBar.style.width = '0%';
                    requestAnimationFrame(function() {
                        els.progressBar.style.transition = 'width ' + (DISPLAY_DURATION / 1000) + 's linear';
                        els.progressBar.style.width = '100%';
                    });
                }
            });
        });

        if (autoHideTimer) clearTimeout(autoHideTimer);
        autoHideTimer = setTimeout(function() {
            if (!isHovered) {
                hideToast();
            }
        }, DISPLAY_DURATION);
    }

    function hideToast() {
        const els = getElements();
        if (!els.wrapper) return;
        els.wrapper.classList.remove('is-visible');
        if (els.progressBar) {
            els.progressBar.style.transition = 'none';
            els.progressBar.style.width = '0%';
        }
        setTimeout(function() {
            if (!els.wrapper.classList.contains('is-visible')) {
                els.wrapper.style.display = 'none';
            }
        }, 360);
    }

    function displayNextSale() {
        if (sessionStorage.getItem(DISMISS_KEY) === '1') return;
        if (!salesQueue || salesQueue.length === 0) return;

        showToast(salesQueue[currentIndex]);
        currentIndex = (currentIndex + 1) % salesQueue.length;
    }

    function loadSalesData() {
        if (sessionStorage.getItem(DISMISS_KEY) === '1') return;
        axios.get("{{ route('api.recent_sales') }}", {
            headers: { 'Accept': 'application/json' }
        })
        .then(function(response) {
            var data = response.data;
            if (data && data.success && Array.isArray(data.sales) && data.sales.length > 0) {
                salesQueue = data.sales;
                bindEvents();
                if (!cycleTimer) {
                    setTimeout(function() {
                        displayNextSale();
                        cycleTimer = setInterval(displayNextSale, CYCLE_INTERVAL);
                    }, INITIAL_DELAY);
                }
            }
        })
        .catch(function() {});
    }

    function initToast() {
        bindEvents();
        if (salesQueue.length === 0) {
            loadSalesData();
        }
    }

    if (!window.__recentOrderToastBound) {
        window.__recentOrderToastBound = true;
        document.addEventListener('turbo:load', function() {
            if (typeof initToast === 'function') initToast();
        });
        document.addEventListener('turbo:before-cache', function() {
            const els = getElements();
            if (els.wrapper) {
                els.wrapper.classList.remove('is-visible');
                els.wrapper.style.display = 'none';
            }
            if (autoHideTimer) clearTimeout(autoHideTimer);
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initToast, { once: true });
    } else {
        initToast();
    }
})();
</script>
@endif
