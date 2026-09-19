<div id="quickViewModal" class="custom-qv-modal" aria-hidden="true">
    <div class="custom-qv-backdrop" onclick="closeQuickView()"></div>
    <div class="custom-qv-container">
        <!-- Top Drag Handle on Mobile -->
        <div class="qv-top-handle-wrap d-md-none" onclick="closeQuickView()">
            <div class="qv-mobile-handle"></div>
        </div>

        <!-- Floating Circular Close Button -->
        <button type="button" class="qv-float-close-btn" onclick="closeQuickView()" aria-label="বন্ধ করুন" title="Close">
            <i class="fa-solid fa-xmark"></i>
        </button>

        <!-- Main Scrollable Body -->
        <div class="custom-qv-scroll-body" id="quickViewBody">
            <div class="text-center py-5 text-muted">
                <div class="spinner-border text-dark mb-3" role="status" style="width: 2.2rem; height: 2.2rem; border-width: 2.5px;"></div>
                <p class="mb-0 fw-bold font-heading fs-14 text-dark">তথ্য লোড হচ্ছে...</p>
            </div>
        </div>

        <!-- Mobile Dedicated Bottom Action Dock (Native App Island) -->
        <div class="qv-mobile-bottom-dock d-md-none" id="qvMobileBottomDock" style="display: none;">
            <!-- Populated dynamically by openQuickView() -->
        </div>
    </div>
</div>

<style>
.custom-qv-modal {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    width: 100%;
    height: 100%;
    height: 100dvh;
    height: var(--real-vh, 100dvh);
    z-index: 10650;
    display: flex;
    visibility: hidden;
    opacity: 0;
    pointer-events: none;
    transition: opacity 0.28s ease, visibility 0.28s ease;
}

.custom-qv-modal.show {
    visibility: visible;
    opacity: 1;
    pointer-events: auto;
}

.custom-qv-backdrop {
    position: absolute;
    inset: 0;
    background-color: rgba(15, 23, 42, 0.65);
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
    transition: opacity 0.28s ease;
}

.custom-qv-container {
    position: relative;
    background: #ffffff;
    z-index: 1;
    display: flex;
    flex-direction: column;
    overflow: hidden;
}

.qv-top-handle-wrap {
    padding: 10px 0 4px;
    text-align: center;
    flex-shrink: 0;
    cursor: pointer;
    user-select: none;
}

.qv-mobile-handle {
    width: 42px;
    height: 4.5px;
    border-radius: 999px;
    background-color: #cbd5e1;
    margin: 0 auto;
    transition: background 0.2s ease;
}

.qv-top-handle-wrap:hover .qv-mobile-handle {
    background-color: #94a3b8;
}

.qv-float-close-btn {
    position: absolute;
    top: 14px;
    right: 14px;
    width: 36px;
    height: 36px;
    border-radius: 50% !important;
    background: rgba(241, 245, 249, 0.95);
    backdrop-filter: blur(8px);
    -webkit-backdrop-filter: blur(8px);
    color: #0f172a;
    border: 1px solid rgba(226, 232, 240, 0.85);
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    z-index: 40;
    font-size: 14px;
    transition: transform 0.15s cubic-bezier(0.34, 1.56, 0.64, 1), background 0.2s ease, color 0.2s ease;
    outline: none;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
}

.qv-float-close-btn:hover {
    background: #0f172a;
    color: #ffffff;
    border-color: #0f172a;
    transform: scale(1.06);
}

.qv-float-close-btn:active {
    transform: scale(0.9);
}

.qv-grid {
    display: grid;
    grid-template-columns: 1.05fr 1fr;
    gap: 32px;
    align-items: start;
}

.qv-gallery-col {
    display: flex;
    flex-direction: column;
    width: 100%;
    user-select: none;
}

.qv-slider-box {
    width: 100%;
    aspect-ratio: 1 / 1;
    border-radius: 22px;
    background: #f8fafc;
    border: 1px solid #f1f5f9;
    position: relative;
    overflow: hidden;
    display: flex;
    align-items: center;
    justify-content: center;
    touch-action: pan-y;
}

.qv-badge-discount {
    position: absolute;
    top: 12px;
    left: 12px;
    background: #ef4444;
    color: #ffffff;
    font-size: 11px;
    font-weight: 700;
    padding: 3.5px 10px;
    border-radius: 50rem !important;
    z-index: 10;
    box-shadow: 0 4px 10px rgba(239, 68, 68, 0.25);
    letter-spacing: 0.02em;
}

.qv-badge-tag {
    position: absolute;
    top: 12px;
    left: 12px;
    background: #2563eb;
    color: #ffffff;
    font-size: 11px;
    font-weight: 700;
    padding: 3.5px 10px;
    border-radius: 50rem !important;
    z-index: 10;
    box-shadow: 0 4px 10px rgba(37, 99, 235, 0.25);
}

.qv-slider-arrow {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    width: 34px;
    height: 34px;
    border-radius: 50% !important;
    background: rgba(255, 255, 255, 0.94);
    backdrop-filter: blur(8px);
    -webkit-backdrop-filter: blur(8px);
    border: 1px solid rgba(226, 232, 240, 0.9);
    color: #0f172a;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
    cursor: pointer;
    z-index: 10;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
    transition: all 0.15s cubic-bezier(0.34, 1.56, 0.64, 1);
}

.qv-slider-arrow:hover {
    background: #0f172a;
    color: #ffffff;
    border-color: #0f172a;
    transform: translateY(-50%) scale(1.08);
}

.qv-slider-arrow:active {
    transform: translateY(-50%) scale(0.88);
}

.qv-slider-arrow.qv-prev {
    left: 10px;
}

.qv-slider-arrow.qv-next {
    right: 10px;
}

.qv-slide-img-wrap {
    width: 100%;
    height: 100%;
    padding: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    box-sizing: border-box;
}

.qv-slide-img {
    max-width: 100%;
    max-height: 100%;
    width: auto;
    height: auto;
    object-fit: contain;
    display: block;
    transition: opacity 0.2s ease, transform 0.3s cubic-bezier(0.16, 1, 0.3, 1);
}

.qv-dots {
    position: absolute;
    bottom: 10px;
    left: 0;
    right: 0;
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 6px;
    z-index: 10;
}

.qv-dot {
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: #cbd5e1;
    cursor: pointer;
    transition: all 0.25s cubic-bezier(0.34, 1.56, 0.64, 1);
}

.qv-dot.active {
    width: 18px;
    border-radius: 50rem !important;
    background: #0f172a;
}

.qv-thumbs-wrapper {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-top: 12px;
    overflow-x: auto;
    padding-bottom: 4px;
    scrollbar-width: none;
}

.qv-thumbs-wrapper::-webkit-scrollbar {
    display: none;
}

.qv-thumb-btn {
    width: 48px;
    height: 48px;
    flex: 0 0 48px;
    border-radius: 12px !important;
    border: 1.5px solid #e2e8f0;
    background: #ffffff;
    padding: 2px;
    cursor: pointer;
    overflow: hidden;
    transition: all 0.2s cubic-bezier(0.34, 1.56, 0.64, 1);
}

.qv-thumb-btn img {
    width: 100%;
    height: 100%;
    object-fit: contain;
    border-radius: 8px;
    display: block;
}

.qv-thumb-btn.active {
    border-color: #0f172a;
    box-shadow: 0 0 0 2px rgba(15, 23, 42, 0.15);
    transform: scale(1.05);
}

.qv-info-col {
    display: flex;
    flex-direction: column;
}

.qv-top-meta {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 8px;
    padding-right: 36px;
}

.qv-stock-badge {
    background: #ecfdf5;
    color: #059669;
    font-size: 11px;
    font-weight: 700;
    padding: 3.5px 10px;
    border-radius: 50rem !important;
    display: inline-flex;
    align-items: center;
    gap: 4px;
}

.qv-rating-badge {
    font-size: 12px;
    color: #475569;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 3.5px;
}

.qv-product-title {
    font-size: 1.25rem;
    font-weight: 800;
    color: #0f172a;
    line-height: 1.35;
    margin: 0 0 8px 0;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.qv-price-row {
    display: flex;
    align-items: baseline;
    gap: 8px;
    margin-bottom: 12px;
}

.qv-current-price {
    font-size: 1.45rem;
    font-weight: 800;
    color: #0f172a;
    line-height: 1;
}

.qv-old-price {
    font-size: 0.95rem;
    color: #94a3b8;
    text-decoration: line-through;
}

.qv-savings-tag {
    font-size: 11px;
    font-weight: 700;
    background: #fef2f2;
    color: #ef4444;
    padding: 3px 10px;
    border-radius: 50rem !important;
}

.qv-variant-section {
    margin-bottom: 12px;
}

.qv-variant-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 6px;
}

.qv-selected-name {
    font-size: 12.5px;
    font-weight: 700;
    color: #0f172a;
}

.qv-variant-pills {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}

.qv-variant-pill {
    padding: 6px 16px;
    font-size: 12.5px;
    font-weight: 600;
    border-radius: 50rem !important;
    border: 1.5px solid #e2e8f0;
    background: #ffffff;
    color: #1e293b;
    cursor: pointer;
    transition: all 0.2s cubic-bezier(0.34, 1.56, 0.64, 1);
}

.qv-variant-pill:hover {
    border-color: #cbd5e1;
    background: #f8fafc;
}

.qv-variant-pill.active {
    background: #0f172a !important;
    color: #ffffff !important;
    border-color: #0f172a !important;
    box-shadow: 0 4px 14px rgba(15, 23, 42, 0.2) !important;
}

.qv-variant-pill:active {
    transform: scale(0.93);
}

.qv-short-desc {
    font-size: 12.5px;
    color: #64748b;
    line-height: 1.55;
    margin-bottom: 14px;
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.qv-action-row {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 14px;
}

.qv-qty-selector {
    display: inline-flex;
    align-items: center;
    background: #f1f5f9;
    border-radius: 50rem !important;
    height: 46px;
    padding: 3px 4px;
    border: 1px solid #e2e8f0;
    flex-shrink: 0;
}

.qv-qty-btn {
    width: 36px;
    height: 36px;
    border-radius: 50% !important;
    border: 1px solid #e2e8f0;
    background: #ffffff;
    color: #0f172a;
    font-size: 15px;
    font-weight: 700;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.06);
    transition: all 0.15s cubic-bezier(0.34, 1.56, 0.64, 1);
}

.qv-qty-btn:hover {
    background: #0f172a;
    color: #ffffff;
    border-color: #0f172a;
}

.qv-qty-btn:active {
    transform: scale(0.88);
}

.qv-qty-number {
    min-width: 32px;
    text-align: center;
    font-weight: 800;
    font-size: 14px;
    color: #0f172a;
    font-family: monospace, sans-serif;
}

.qv-add-cart-btn {
    height: 46px;
    border-radius: 50rem !important;
    background: #0f172a;
    color: #ffffff;
    border: none;
    font-size: 13.5px;
    font-weight: 700;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    flex-grow: 1;
    padding: 0 20px;
    cursor: pointer;
    box-shadow: 0 6px 18px rgba(15, 23, 42, 0.2);
    letter-spacing: 0.2px;
    transition: transform 0.15s cubic-bezier(0.34, 1.56, 0.64, 1), background 0.2s ease, box-shadow 0.2s ease;
}

.qv-add-cart-btn:hover {
    background: #1e293b;
    color: #ffffff;
    box-shadow: 0 8px 24px rgba(15, 23, 42, 0.26);
}

.qv-add-cart-btn:active {
    transform: scale(0.95);
}

.qv-detail-link {
    font-size: 12px;
    font-weight: 700;
    color: #2563eb;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 6px 14px;
    border-radius: 50rem !important;
    background: #eff6ff;
    width: fit-content;
    transition: all 0.2s ease;
}

.qv-detail-link:hover {
    background: #dbeafe;
    color: #1d4ed8;
}

@media (min-width: 768px) {
    .custom-qv-modal {
        align-items: center;
        justify-content: center;
        padding: 1.5rem;
    }
    .custom-qv-container {
        width: 860px;
        max-width: 95vw;
        border-radius: 26px;
        box-shadow: 0 25px 60px -15px rgba(0, 0, 0, 0.25);
        transform: scale(0.95);
        transition: transform 0.25s cubic-bezier(0.34, 1.56, 0.64, 1);
    }
    .custom-qv-modal.show .custom-qv-container {
        transform: scale(1);
    }
    .custom-qv-scroll-body {
        padding: 28px 30px;
        overflow: hidden;
    }
}

@media (max-width: 991.98px) {
    .custom-qv-modal {
        align-items: flex-end;
        justify-content: center;
        padding: 0;
    }
    .custom-qv-container {
        width: 100%;
        height: auto !important;
        max-height: calc(100dvh - 30px) !important;
        max-height: calc(var(--real-vh, 100dvh) - 30px) !important;
        border-radius: 28px 28px 0 0;
        box-shadow: 0 -12px 40px rgba(0, 0, 0, 0.25);
        transform: translateY(100%);
        transition: transform 0.32s cubic-bezier(0.32, 0.72, 0, 1);
        display: flex;
        flex-direction: column;
        overflow: hidden;
    }
    .custom-qv-modal.show .custom-qv-container {
        transform: translateY(0);
    }
    .qv-float-close-btn {
        top: 10px;
        right: 12px;
        width: 34px;
        height: 34px;
    }
    .qv-grid {
        grid-template-columns: 1fr;
        gap: 12px;
    }
    .qv-slider-box {
        height: clamp(165px, 26dvh, 215px) !important;
        aspect-ratio: auto !important;
        border-radius: 20px !important;
        margin: 0 auto !important;
    }
    .qv-slide-img-wrap {
        padding: 8px !important;
    }
    .qv-thumb-btn {
        width: 42px;
        height: 42px;
        flex: 0 0 42px;
        border-radius: 12px !important;
    }
    .custom-qv-scroll-body {
        padding: 4px 14px 12px;
        overflow-y: auto;
        flex: 0 1 auto;
        min-height: 0;
        -webkit-overflow-scrolling: touch;
    }
    .qv-mobile-bottom-dock {
        flex-shrink: 0;
        background: rgba(255, 255, 255, 0.96);
        backdrop-filter: blur(20px);
        -webkit-backdrop-filter: blur(20px);
        border-top: 1px solid rgba(226, 232, 240, 0.85);
        padding: 8px 14px calc(10px + env(safe-area-inset-bottom, 0px));
        box-shadow: 0 -8px 24px -4px rgba(15, 23, 42, 0.08);
        z-index: 20;
    }
    .qv-mobile-bottom-dock > div {
        display: flex;
        align-items: center;
        gap: 12px !important;
        width: 100%;
    }
    .qv-product-title {
        font-size: 1.15rem;
        margin-bottom: 6px;
    }
    .qv-price-row {
        margin-bottom: 8px;
    }
    .qv-variant-section {
        margin-bottom: 10px;
    }
    .qv-short-desc {
        -webkit-line-clamp: 3;
        margin-bottom: 10px;
    }
}
</style>