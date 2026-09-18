<div id="quickViewModal" class="custom-qv-modal" aria-hidden="true">
    <div class="custom-qv-backdrop" onclick="closeQuickView()"></div>
    <div class="custom-qv-container">
        <button type="button" class="qv-float-close-btn" onclick="closeQuickView()" aria-label="Close">
            <i class="fa-solid fa-xmark"></i>
        </button>

        <div class="d-md-none qv-mobile-handle-bar text-center pt-2 pb-1 flex-shrink-0">
            <div class="qv-mobile-handle mx-auto"></div>
        </div>

        <div class="custom-qv-scroll-body" id="quickViewBody">
            <div class="text-center py-5 text-muted">
                <div class="spinner-border text-primary mb-3" role="status" style="width: 2.5rem; height: 2.5rem;"></div>
                <p class="mb-0 fw-medium font-heading">তথ্য লোড হচ্ছে...</p>
            </div>
        </div>
    </div>
</div>

<style>
.custom-qv-modal {
    position: fixed;
    inset: 0;
    width: 100vw;
    height: 100vh;
    z-index: 10650;
    display: flex;
    visibility: hidden;
    opacity: 0;
    pointer-events: none;
    transition: opacity 0.25s ease, visibility 0.25s ease;
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
    backdrop-filter: blur(8px);
    -webkit-backdrop-filter: blur(8px);
}

.custom-qv-container {
    position: relative;
    background: #ffffff;
    z-index: 1;
    display: flex;
    flex-direction: column;
    box-shadow: 0 25px 60px -15px rgba(0, 0, 0, 0.25);
    overflow: hidden;
}

.qv-float-close-btn {
    position: absolute;
    top: 18px;
    right: 18px;
    width: 34px;
    height: 34px;
    border-radius: 50%;
    background: #f1f5f9;
    color: #475569;
    display: flex;
    align-items: center;
    justify-content: center;
    border: none;
    cursor: pointer;
    z-index: 30;
    font-size: 14px;
    transition: all 0.2s ease;
    outline: none;
}

.qv-float-close-btn:hover {
    background: #0f172a;
    color: #ffffff;
    transform: rotate(90deg);
}

.qv-grid {
    display: grid;
    grid-template-columns: 1.05fr 1fr;
    gap: 36px;
    align-items: start;
}

.qv-gallery-col {
    display: flex;
    flex-direction: column;
    width: 100%;
}

.qv-slider-box {
    width: 100%;
    aspect-ratio: 1 / 1;
    border-radius: 20px;
    background: #ffffff;
    border: 1.5px solid #f1f5f9;
    position: relative;
    overflow: hidden;
    display: flex;
    align-items: center;
    justify-content: center;
}

.qv-badge-discount {
    position: absolute;
    top: 14px;
    left: 14px;
    background: #ea3a3a;
    color: #ffffff;
    font-size: 11px;
    font-weight: 700;
    padding: 3px 8px;
    border-radius: 999px;
    z-index: 10;
}

.qv-slider-arrow {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: #ffffff;
    border: 1px solid #e2e8f0;
    color: #334155;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
    cursor: pointer;
    z-index: 10;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.08);
    transition: all 0.2s ease;
}

.qv-slider-arrow:hover {
    background: #0f172a;
    color: #ffffff;
    border-color: #0f172a;
}

.qv-slider-arrow.qv-prev {
    left: 12px;
}

.qv-slider-arrow.qv-next {
    right: 12px;
}

.qv-slide-img-wrap {
    width: 100%;
    height: 100%;
    padding: 24px;
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
}

.qv-dots {
    position: absolute;
    bottom: 12px;
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
    transition: all 0.2s ease;
}

.qv-dot.active {
    width: 16px;
    border-radius: 999px;
    background: #6366f1;
}

.qv-thumbs-wrapper {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-top: 14px;
    overflow-x: auto;
    padding-bottom: 2px;
}

.qv-thumb-btn {
    width: 54px;
    height: 54px;
    flex: 0 0 54px;
    border-radius: 12px;
    border: 1.5px solid #e2e8f0;
    background: #ffffff;
    padding: 3px;
    cursor: pointer;
    overflow: hidden;
    transition: all 0.2s ease;
}

.qv-thumb-btn img {
    width: 100%;
    height: 100%;
    object-fit: contain;
    border-radius: 8px;
    display: block;
}

.qv-thumb-btn.active {
    border-color: #2563eb;
    box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.15);
}

.qv-info-col {
    display: flex;
    flex-direction: column;
}

.qv-top-meta {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 10px;
    padding-right: 32px;
}

.qv-stock-badge {
    background: #ecfdf5;
    color: #059669;
    font-size: 11.5px;
    font-weight: 600;
    padding: 3px 10px;
    border-radius: 999px;
    display: inline-flex;
    align-items: center;
    gap: 5px;
}

.qv-rating-badge {
    font-size: 12.5px;
    color: #0f172a;
}

.qv-product-title {
    font-size: 1.35rem;
    font-weight: 800;
    color: #0f172a;
    line-height: 1.3;
    margin: 0 0 10px 0;
}

.qv-price-row {
    display: flex;
    align-items: baseline;
    gap: 8px;
    margin-bottom: 14px;
}

.qv-current-price {
    font-size: 1.45rem;
    font-weight: 800;
    color: #0f172a;
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
    padding: 3px 8px;
    border-radius: 999px;
}

.qv-variant-section {
    margin-bottom: 14px;
}

.qv-variant-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 8px;
}

.qv-selected-name {
    font-size: 12.5px;
    font-weight: 700;
    color: #0f172a;
}

.qv-variant-pills {
    display: flex;
    gap: 8px;
}

.qv-variant-pill {
    padding: 6px 18px;
    font-size: 12.5px;
    font-weight: 600;
    border-radius: 999px;
    border: 1.5px solid #e2e8f0;
    background: #ffffff;
    color: #334155;
    cursor: pointer;
    transition: all 0.2s ease;
}

.qv-variant-pill.active {
    background: #0f172a;
    color: #ffffff;
    border-color: #0f172a;
}

.qv-short-desc {
    font-size: 12px;
    color: #64748b;
    line-height: 1.55;
    margin-bottom: 18px;
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
    background: #ffffff;
    border-radius: 999px;
    height: 44px;
    padding: 0 4px;
    border: 1.5px solid #e2e8f0;
    flex-shrink: 0;
}

.qv-qty-btn {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    border: none;
    background: transparent;
    color: #0f172a;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
}

.qv-qty-number {
    min-width: 28px;
    text-align: center;
    font-weight: 700;
    font-size: 13.5px;
    color: #0f172a;
}

.qv-add-cart-btn {
    height: 44px;
    border-radius: 999px;
    background: #0f172a;
    color: #ffffff;
    border: none;
    font-size: 13px;
    font-weight: 700;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    flex-grow: 1;
    padding: 0 20px;
    cursor: pointer;
    transition: all 0.2s ease;
}

.qv-add-cart-btn:hover {
    background: #1e293b;
}

.qv-detail-link {
    font-size: 12px;
    font-weight: 700;
    color: #0f172a;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
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
        border-radius: 24px;
        transform: scale(0.96);
        transition: transform 0.25s cubic-bezier(0.16, 1, 0.3, 1);
    }
    .custom-qv-modal.show .custom-qv-container {
        transform: scale(1);
    }
    .custom-qv-scroll-body {
        padding: 28px 30px;
        overflow: hidden;
    }
}

@media (max-width: 767.98px) {
    .custom-qv-modal {
        align-items: flex-end;
    }
    .custom-qv-container {
        width: 100%;
        max-height: 90vh;
        border-radius: 24px 24px 0 0;
    }
    .qv-grid {
        grid-template-columns: 1fr;
        gap: 16px;
    }
    .custom-qv-scroll-body {
        padding: 1rem 1.25rem 2rem;
        overflow-y: auto;
        max-height: calc(90vh - 35px);
    }
}
</style>