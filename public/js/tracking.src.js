/**
 * Zippy E-Commerce - Modular Tracking & Analytics Layer
 * Decoupled Meta Pixel (fbq) & Google Tag Manager (dataLayer) engine.
 * Ensures marketing tags never interfere with core app features.
 */
(function(window, document) {
    'use strict';

    if (window.__zippyTrackingInitialized) {
        return;
    }
    window.__zippyTrackingInitialized = true;

    // ----------------------------------------------------
    // 1. Meta Pixel Guard (Intercepts callMethod to block duplicate inits)
    // ----------------------------------------------------
    var initedPixels = {};

    function syncExistingInits() {
        if (window.fbq && Array.isArray(window.fbq.queue)) {
            var seen = {};
            for (var i = 0; i < window.fbq.queue.length; i++) {
                var item = window.fbq.queue[i];
                if (item && item[0] === 'init') {
                    var pid = String(item[1] || '');
                    if (pid) {
                        initedPixels[pid] = true;
                        if (!seen[pid]) {
                            seen[pid] = true;
                        } else {
                            window.fbq.queue.splice(i, 1);
                            i--;
                        }
                    }
                }
            }
        }
        if (window.fbq && typeof window.fbq.getState === 'function') {
            try {
                var st = window.fbq.getState();
                if (st && Array.isArray(st.pixels)) {
                    st.pixels.forEach(function(p) {
                        if (p && p.id) initedPixels[String(p.id)] = true;
                    });
                }
            } catch(e) {}
        }
    }

    syncExistingInits();

    function setupFbqGuard() {
        syncExistingInits();
        if (typeof window.fbq === 'function' && !window.fbq._zippyPatched) {
            window.fbq._zippyPatched = true;

            var checkAndPatchCallMethod = function() {
                syncExistingInits();
                if (typeof window.fbq.callMethod === 'function' && !window.fbq.callMethod._zippyPatched) {
                    var origCall = window.fbq.callMethod;
                    var patched = function() {
                        var args = Array.prototype.slice.call(arguments);
                        if (args[0] === 'init') {
                            var pid = String(args[1] || '');
                            if (pid && initedPixels[pid]) {
                                return; // Silently ignore duplicate init
                            }
                            if (pid) initedPixels[pid] = true;
                        }
                        return origCall.apply(this, args);
                    };
                    patched._zippyPatched = true;
                    window.fbq.callMethod = patched;
                    return true;
                }
                return false;
            };

            if (!checkAndPatchCallMethod()) {
                var poll = setInterval(function() {
                    if (checkAndPatchCallMethod()) {
                        clearInterval(poll);
                    }
                }, 40);
                setTimeout(function() { clearInterval(poll); }, 6000);
            }
        }
    }

    setupFbqGuard();

    function callFbq() {
        setupFbqGuard();
        if (typeof window.fbq === 'function') {
            try {
                window.fbq.apply(window, arguments);
            } catch (err) {
                console.warn('[Tracking] fbq call error:', err);
            }
        }
    }

    // ----------------------------------------------------
    // 2. DataLayer Initialization
    // ----------------------------------------------------
    window.dataLayer = window.dataLayer || [];

    // ----------------------------------------------------
    // 3. Unified ZippyTracker API
    // ----------------------------------------------------
    window.ZippyTracker = {
        /**
         * PageView tracking for initial and Turbo transitions
         */
        trackPageView: function(customData) {
            try {
                var url = (customData && customData.url) || window.location.href;
                var path = (customData && customData.path) || (window.location.pathname + window.location.search);
                var title = (customData && customData.title) || document.title;

                window.dataLayer = window.dataLayer || [];
                window.dataLayer.push({
                    event: 'turbo_page_view',
                    page_location: url,
                    page_path: path,
                    page_title: title
                });

                callFbq('track', 'PageView');
            } catch (err) {
                console.warn('[Tracking] PageView error:', err);
            }
        },

        /**
         * ViewContent / view_item tracking (Product details)
         */
        trackViewItem: function(payload) {
            try {
                if (!payload) return;
                var pId = String(payload.id || '');
                var pName = payload.name || '';
                var pPrice = Number(payload.price || payload.value || 0);
                var contentIds = payload.content_ids || [pId];

                window.dataLayer = window.dataLayer || [];
                window.dataLayer.push({ ecommerce: null });
                window.dataLayer.push({
                    event: 'view_item',
                    ecommerce: {
                        currency: 'BDT',
                        value: pPrice,
                        items: payload.items || [
                            {
                                item_id: pId,
                                item_name: pName,
                                price: pPrice,
                                quantity: 1
                            }
                        ]
                    },
                    content_type: 'product',
                    content_ids: contentIds,
                    content_name: pName,
                    value: pPrice,
                    currency: 'BDT'
                });

                callFbq('track', 'ViewContent', {
                    content_type: 'product',
                    content_ids: contentIds,
                    content_name: pName,
                    value: pPrice,
                    currency: 'BDT'
                });
            } catch (err) {
                console.warn('[Tracking] ViewItem error:', err);
            }
        },

        /**
         * AddToCart / add_to_cart tracking
         */
        trackAddToCart: function(payload) {
            try {
                if (!payload) return;
                var qty = Number(payload.quantity || 1);
                var price = Number(payload.price || 0);
                var totalVal = price * qty;
                var pId = String(payload.id || '');
                var pName = payload.name || payload.title || ('Product #' + pId);

                window.dataLayer = window.dataLayer || [];
                window.dataLayer.push({ ecommerce: null });
                window.dataLayer.push({
                    event: 'add_to_cart',
                    ecommerce: {
                        currency: 'BDT',
                        value: totalVal,
                        items: [
                            {
                                item_id: pId,
                                item_name: pName,
                                price: price,
                                quantity: qty
                            }
                        ]
                    },
                    content_type: 'product',
                    content_ids: [pId],
                    content_name: pName,
                    value: totalVal,
                    currency: 'BDT'
                });

                callFbq('track', 'AddToCart', {
                    content_type: 'product',
                    content_ids: [pId],
                    content_name: pName,
                    value: totalVal,
                    currency: 'BDT'
                });
            } catch (err) {
                console.warn('[Tracking] AddToCart error:', err);
            }
        },

        /**
         * InitiateCheckout / begin_checkout tracking
         */
        trackBeginCheckout: function(payload) {
            try {
                if (!payload) return;
                var val = Number(payload.value || 0);
                var items = payload.items || [];
                var contentIds = payload.content_ids || [];

                window.dataLayer = window.dataLayer || [];
                window.dataLayer.push({ ecommerce: null });
                window.dataLayer.push({
                    event: 'begin_checkout',
                    ecommerce: {
                        currency: 'BDT',
                        value: val,
                        items: items
                    },
                    content_type: 'product',
                    content_ids: contentIds,
                    value: val,
                    currency: 'BDT'
                });

                callFbq('track', 'InitiateCheckout', {
                    content_type: 'product',
                    content_ids: contentIds,
                    value: val,
                    currency: 'BDT',
                    num_items: items.length || 1
                });
            } catch (err) {
                console.warn('[Tracking] BeginCheckout error:', err);
            }
        },

        /**
         * Purchase / purchase tracking (Order success)
         */
        trackPurchase: function(payload) {
            try {
                if (!payload) return;

                var orderId = payload.order_id || payload.transaction_id || '';
                var dedupeKey = 'zippy_purchased_' + orderId;
                if (orderId && sessionStorage.getItem(dedupeKey)) {
                    return; // Prevent duplicate Purchase fires on manual page refreshes
                }
                if (orderId) {
                    sessionStorage.setItem(dedupeKey, '1');
                }

                var eventId = payload.event_id || ('order_' + orderId);
                var val = Number(payload.value || 0);
                var items = payload.items || [];
                var contentIds = payload.content_ids || [];

                window.dataLayer = window.dataLayer || [];
                window.dataLayer.push({ ecommerce: null });
                window.dataLayer.push({
                    event: 'purchase',
                    event_id: eventId,
                    ecommerce: {
                        transaction_id: eventId,
                        value: val,
                        tax: Number(payload.tax || 0),
                        shipping: Number(payload.shipping || 0),
                        currency: 'BDT',
                        coupon: payload.coupon || '',
                        items: items
                    },
                    content_type: 'product',
                    content_ids: contentIds,
                    value: val,
                    currency: 'BDT',
                    num_items: items.length || 1,
                    user_data: payload.user_data || null
                });

                callFbq('track', 'Purchase', {
                    content_type: 'product',
                    content_ids: contentIds,
                    value: val,
                    currency: 'BDT',
                    num_items: items.length || 1
                }, {
                    eventID: eventId
                });
            } catch (err) {
                console.warn('[Tracking] Purchase error:', err);
            }
        }
    };

    // ----------------------------------------------------
    // 4. Hotwire Turbo Lifecycle Hooks
    // ----------------------------------------------------
    if (!window.__turboTrackingBound) {
        window.__turboTrackingBound = true;
        document.addEventListener('turbo:load', function() {
            setupFbqGuard();
            if (window.ZippyTracker) {
                window.ZippyTracker.trackPageView();
            }
        });
    }

})(window, document);
