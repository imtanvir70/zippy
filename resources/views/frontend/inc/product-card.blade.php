@php
    $pId       = is_object($product) ? ($product->id ?? 0)                           : (is_array($product) ? ($product['id'] ?? 0) : 0);
    $pTitle    = is_object($product) ? ($product->title ?? ($product->name_bn ?? ($product->name ?? ''))) : (is_array($product) ? ($product['title'] ?? ($product['name_bn'] ?? ($product['name'] ?? ''))) : '');
    $pSlug     = is_object($product) ? ($product->slug ?? '')                         : (is_array($product) ? ($product['slug'] ?? '') : '');
    $pPrice    = is_object($product) ? ($product->price ?? 0)                         : (is_array($product) ? ($product['price'] ?? 0) : 0);
    $pOldPrice = is_object($product) ? ($product->old_price ?? ($product->compare_price ?? 0)) : (is_array($product) ? ($product['old_price'] ?? ($product['compare_price'] ?? 0)) : 0);
    $pImg      = is_object($product) ? ($product->main_image ?? ($product->thumbnail ?? ($product->image ?? ''))) : (is_array($product) ? ($product['main_image'] ?? ($product['thumbnail'] ?? ($product['image'] ?? ''))) : '');
    $pTag      = is_object($product) ? ($product->tag ?? ($product->badge ?? ''))     : (is_array($product) ? ($product['tag'] ?? ($product['badge'] ?? '')) : '');
    $pRating   = is_object($product) ? (float)($product->rating ?? 0)                  : (is_array($product) ? (float)($product['rating'] ?? 0) : 0);
    $pReviews  = is_object($product) ? (int)($product->reviews_count ?? ($product->review_count ?? 0)) : (is_array($product) ? (int)($product['reviews_count'] ?? ($product['review_count'] ?? 0)) : 0);

    $fallbackImg = asset('images/product-placeholder.svg');
    if (!$pImg || trim($pImg) === '' || str_contains($pImg, 'example.com')) {
        $pImg = $fallbackImg;
    } elseif (str_contains($pImg, 'images.unsplash.com')) {
        $pImg = preg_replace('/([?&]w=)\d+/', '${1}360', $pImg);
        $pImg = preg_replace('/([?&]q=)\d+/', '${1}70', $pImg);
        if (!str_contains($pImg, 'w=')) {
            $pImg .= (str_contains($pImg, '?') ? '&' : '?') . 'w=360&q=70&fm=webp';
        } elseif (!str_contains($pImg, 'fm=webp')) {
            $pImg .= '&fm=webp';
        }
    }

    $pGalleryRaw = is_object($product) ? ($product->gallery_images ?? null) : (is_array($product) ? ($product['gallery_images'] ?? null) : null);
    $pGallery = is_array($pGalleryRaw) ? $pGalleryRaw : (is_string($pGalleryRaw) ? json_decode($pGalleryRaw, true) : []);
    if (is_string($pGallery)) {
        $pGallery = json_decode($pGallery, true) ?: [];
    }
    $secondImg = null;
    if (is_array($pGallery) && !empty($pGallery)) {
        foreach ($pGallery as $gItem) {
            $gUrl = is_string($gItem) ? $gItem : (is_array($gItem) ? ($gItem['image'] ?? ($gItem['url'] ?? '')) : '');
            if (!empty($gUrl) && $gUrl !== $pImg && trim($gUrl) !== '' && !str_contains($gUrl, 'example.com')) {
                $secondImg = $gUrl;
                break;
            }
        }
        if (!$secondImg && count($pGallery) > 1) {
            $candidate = is_string($pGallery[1]) ? $pGallery[1] : (is_array($pGallery[1]) ? ($pGallery[1]['image'] ?? ($pGallery[1]['url'] ?? '')) : '');
            if (!empty($candidate) && $candidate !== $pImg && !str_contains($candidate, 'example.com')) {
                $secondImg = $candidate;
            }
        }
    }
    if ($secondImg) {
        if (str_contains($secondImg, 'example.com')) {
            $secondImg = null;
        } elseif (str_contains($secondImg, 'images.unsplash.com')) {
            $secondImg = preg_replace('/([?&]w=)\d+/', '${1}360', $secondImg);
            $secondImg = preg_replace('/([?&]q=)\d+/', '${1}70', $secondImg);
            if (!str_contains($secondImg, 'w=')) {
                $secondImg .= (str_contains($secondImg, '?') ? '&' : '?') . 'w=360&q=70&fm=webp';
            } elseif (!str_contains($secondImg, 'fm=webp')) {
                $secondImg .= '&fm=webp';
            }
        } elseif (!str_starts_with($secondImg, 'http://') && !str_starts_with($secondImg, 'https://') && !str_starts_with($secondImg, '/')) {
            $secondImg = asset($secondImg);
        }
    }

    $pVariantsRaw = is_object($product) ? ($product->variants ?? null) : (is_array($product) ? ($product['variants'] ?? null) : null);
    $pVariants = is_array($pVariantsRaw) ? $pVariantsRaw : (is_string($pVariantsRaw) ? json_decode($pVariantsRaw, true) : []);
    if (is_string($pVariants)) {
        $pVariants = json_decode($pVariants, true) ?: [];
    }
    $firstVariantName = null;
    $firstVariantPrice = null;
    $firstVariantColor = '#0ea5e9';
    if (is_array($pVariants) && count($pVariants) > 0) {
        $firstVar = $pVariants[0];
        if (is_array($firstVar)) {
            $firstVariantName = !empty($firstVar['name']) ? trim($firstVar['name']) : null;
            $firstVariantPrice = isset($firstVar['price']) && (float)$firstVar['price'] > 0 ? (float)$firstVar['price'] : null;
        } elseif (is_string($firstVar)) {
            $firstVariantName = trim($firstVar);
        }
        if (!empty($firstVariantName)) {
            $lowerName = strtolower($firstVariantName);
            $firstVariantColor = match(true) {
                str_contains($lowerName, 'orange') => '#f97316',
                str_contains($lowerName, 'white') => '#ffffff',
                str_contains($lowerName, 'black') => '#0f172a',
                str_contains($lowerName, 'red') => '#ef4444',
                str_contains($lowerName, 'blue') => '#3b82f6',
                str_contains($lowerName, 'green') => '#10b981',
                str_contains($lowerName, 'silver') || str_contains($lowerName, 'gray') || str_contains($lowerName, 'grey') => '#94a3b8',
                str_contains($lowerName, 'yellow') => '#eab308',
                str_contains($lowerName, 'gold') => '#d97706',
                str_contains($lowerName, 'pink') || str_contains($lowerName, 'rose') => '#ec4899',
                str_contains($lowerName, 'purple') => '#a855f7',
                default => '#6366f1',
            };
        }
    }
    if ($firstVariantPrice !== null && $firstVariantPrice > 0) {
        $pPrice = $firstVariantPrice;
    }

    $discountPercentage = (!empty($pOldPrice) && $pOldPrice > $pPrice)
        ? round((($pOldPrice - $pPrice) / $pOldPrice) * 100) : 0;

    $badgeColor = match(true) {
        str_contains(strtolower($pTag ?? ''), 'new')   => 'bg-primary',
        str_contains(strtolower($pTag ?? ''), 'hot')   => 'bg-warning text-dark',
        $discountPercentage >= 30                       => 'bg-danger',
        default                                         => 'bg-danger',
    };
@endphp

<div class="zb-product-card" data-product-id="{{ $pId }}">
    <a href="{{ $pSlug ? route('product.show', $pSlug) : '#' }}" class="zb-product-img-wrap {{ $secondImg ? 'has-hover-img' : '' }}">
        <img src="{{ $pImg }}"
             alt="{{ $pTitle }} - Zippy BD"
             class="zb-product-img zb-img-primary"
             width="300"
             height="300"
             decoding="async"
             loading="lazy"
             onerror="this.onerror=null; this.src='{{ asset('images/product-placeholder.svg') }}';">

        @if($secondImg)
            <img src="{{ $secondImg }}"
                 alt="{{ $pTitle }} - Zippy BD"
                 class="zb-product-img zb-img-hover"
                 width="300"
                 height="300"
                 decoding="async"
                 onerror="this.onerror=null; this.style.display='none';">
        @endif

        @if(!empty($pTag))
            <span class="zb-product-badge {{ $badgeColor }}">{{ $pTag }}</span>
        @elseif($discountPercentage > 0)
            <span class="zb-product-badge bg-danger">-{{ $discountPercentage }}%</span>
        @endif

        <button type="button"
                class="zb-quickview-btn"
                onclick="event.preventDefault(); event.stopPropagation(); openQuickView({{ $pId }})"
                aria-label="Quick View">
            <i class="fa-duotone fa-solid fa-eye fa-float"></i>
        </button>
    </a>

    <div class="zb-product-body">
        @if($pReviews > 0 && $pRating > 0)
            <div class="zb-product-rating">
                <i class="fa-solid fa-star text-warning" style="font-size:11px;"></i>
                <span class="fw-semibold">{{ number_format((float)$pRating, 1) }}</span>
                <span class="text-muted">({{ $pReviews }})</span>
            </div>
        @endif

        <a href="{{ $pSlug ? route('product.show', $pSlug) : '#' }}"
           class="zb-product-title"
           title="{{ $pTitle }}">{{ $pTitle }}</a>

        <div class="zb-product-price-row">
            <div class="zb-product-price-group">
                <span class="zb-product-price">৳{{ number_format((float)$pPrice, 0) }}</span>
                @if(!empty($pOldPrice) && $pOldPrice > $pPrice)
                    <span class="zb-product-oldprice">৳{{ number_format((float)$pOldPrice, 0) }}</span>
                @endif
            </div>
            @if(!empty($firstVariantName))
                <span class="zb-product-variant-badge" title="ডিফল্ট ভ্যারিয়েন্ট: {{ $firstVariantName }}">
                    <span class="zb-variant-dot" style="background-color: {{ $firstVariantColor }}; @if(strtolower($firstVariantColor) === '#ffffff') border: 1px solid #cbd5e1; @endif"></span>
                    <span class="zb-variant-text">{{ $firstVariantName }}</span>
                </span>
            @endif
        </div>

        <button type="button"
                class="zb-btn-cart btn-cart-animated"
                onclick="addToCart({{ $pId }}, 1, {{ $firstVariantName ? json_encode($firstVariantName) : 'null' }}, null, this, {{ $firstVariantName ? json_encode($firstVariantName) : 'null' }})">
            <span class="btn-cart-content d-flex align-items-center justify-content-center gap-2">
                <span class="cart-anim-bag-wrap"><i class="fa-duotone fa-solid fa-bag-shopping fa-jello cart-anim-bag"></i></span>
                <span class="cart-anim-text">কার্টে যোগ</span>
            </span>
            <span class="cart-anim-road" aria-hidden="true">
                <span class="cart-anim-track-line"></span>
                <span class="cart-anim-truck-track">
                    <span class="cart-anim-truck-wrap">
                        <span class="cart-anim-speed-line"></span>
                        <i class="fa-solid fa-truck-fast cart-anim-truck-icon"></i>
                    </span>
                </span>
            </span>
            <span class="cart-anim-success" aria-hidden="true">
                <span class="cart-success-badge">
                    <svg class="cart-success-check" viewBox="0 0 16 16" fill="none"><use href="#cart-check-icon"></use></svg>
                    <span class="cart-success-text">কার্টে যুক্ত হয়েছে</span>
                </span>
            </span>
        </button>
    </div>
</div>

@once
@push('styles')
<style>
.zb-product-card {
    background: #fff;
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 4px 16px -2px rgba(15, 23, 42, 0.05), 0 2px 6px -1px rgba(0,0,0,0.03);
    display: flex;
    flex-direction: column;
    height: 100%;
    transition: box-shadow 0.25s cubic-bezier(0.34, 1.56, 0.64, 1), transform 0.25s cubic-bezier(0.34, 1.56, 0.64, 1);
    border: 1px solid rgba(226, 232, 240, 0.8);
}
.zb-product-card:hover {
    box-shadow: 0 12px 30px -4px rgba(15, 23, 42, 0.12);
    transform: translateY(-3px);
}
.zb-product-img-wrap {
    display: block;
    position: relative;
    overflow: hidden;
    background: #f8fafc;
    aspect-ratio: 1 / 1;
    isolation: isolate;
}
.zb-product-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
    backface-visibility: hidden;
    -webkit-backface-visibility: hidden;
    transform: translateZ(0);
}
.zb-product-img-wrap.has-hover-img .zb-img-primary {
    position: relative;
    z-index: 1;
    opacity: 1;
    transition: transform 0.65s cubic-bezier(0.16, 1, 0.3, 1);
    will-change: transform;
}
.zb-product-img-wrap.has-hover-img .zb-img-hover {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    object-fit: cover;
    opacity: 0;
    pointer-events: none;
    z-index: 2;
    transition: opacity 0.48s cubic-bezier(0.25, 1, 0.5, 1), transform 0.65s cubic-bezier(0.16, 1, 0.3, 1);
    will-change: transform, opacity;
}
.zb-product-card:hover .zb-product-img-wrap.has-hover-img .zb-img-hover {
    opacity: 1;
    transform: scale(1.06);
}
.zb-product-card:hover .zb-product-img-wrap.has-hover-img .zb-img-primary {
    transform: scale(1.06);
    opacity: 1;
}
.zb-product-card:hover .zb-product-img-wrap:not(.has-hover-img) .zb-product-img {
    transform: scale(1.06);
}
.zb-product-img-wrap:not(.has-hover-img) .zb-product-img {
    transition: transform 0.65s cubic-bezier(0.16, 1, 0.3, 1);
    will-change: transform;
}
.zb-product-badge {
    position: absolute;
    top: 10px;
    left: 10px;
    font-size: 10.5px;
    font-weight: 700;
    padding: 4px 10px;
    border-radius: 50rem !important;
    color: #fff;
    letter-spacing: 0.02em;
    line-height: 1.3;
    z-index: 3;
    box-shadow: 0 4px 10px rgba(0,0,0,0.15);
}
.zb-quickview-btn {
    position: absolute;
    top: 10px;
    right: 10px;
    width: 34px;
    height: 34px;
    border-radius: 50% !important;
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(8px);
    -webkit-backdrop-filter: blur(8px);
    border: 1px solid rgba(255, 255, 255, 0.8);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 13px;
    color: #334155;
    cursor: pointer;
    transition: all 0.2s cubic-bezier(0.34, 1.56, 0.64, 1);
    z-index: 3;
    box-shadow: 0 3px 12px rgba(0,0,0,.08);
}
.zb-quickview-btn:hover {
    background: #0f172a;
    color: #ffffff;
    box-shadow: 0 6px 16px rgba(15, 23, 42, 0.25);
    transform: scale(1.06);
}
.zb-quickview-btn:active {
    transform: scale(0.9);
}
@media (min-width: 992px) {
    .zb-quickview-btn {
        opacity: 0;
        transform: translateY(8px) scale(0.9);
    }
    .zb-product-card:hover .zb-quickview-btn {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
    .zb-product-card:hover .zb-quickview-btn:hover {
        transform: translateY(-2px) scale(1.06);
    }
}
@media (max-width: 991.98px) {
    .zb-quickview-btn {
        opacity: 1;
        transform: none;
    }
}
.zb-product-body {
    padding: 12px 12px 14px;
    display: flex;
    flex-direction: column;
    flex: 1;
    gap: 5px;
}
@media (min-width: 1260px) {
    .zb-product-body { padding: 14px 16px 16px; gap: 6px; }
}
.zb-product-rating {
    display: flex;
    align-items: center;
    gap: 4px;
    font-size: 11px;
    color: #64748b;
}
.zb-product-title {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    font-size: 13px;
    font-weight: 600;
    color: #0f172a;
    text-decoration: none;
    line-height: 1.35;
    min-height: 35px;
    transition: color 0.15s;
}
.zb-product-title:hover {
    color: #2563eb;
    text-decoration: none;
}
@media (min-width: 1260px) {
    .zb-product-title { font-size: 14px; min-height: 38px; }
}
.zb-product-price-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 6px;
    white-space: nowrap;
    min-height: 24px;
}
.zb-product-price-group {
    display: inline-flex;
    align-items: baseline;
    gap: 6px;
    white-space: nowrap;
}
.zb-product-price {
    font-size: 15px;
    font-weight: 800;
    color: #0f172a;
    line-height: 1.2;
}
.zb-product-oldprice {
    font-size: 11.5px;
    color: #94a3b8;
    text-decoration: line-through;
    line-height: 1.2;
}
.zb-product-variant-badge {
    display: inline-flex;
    align-items: center;
    gap: 4.5px;
    font-size: 10.5px;
    font-weight: 600;
    color: #475569;
    background: #f1f5f9;
    border: 1px solid #e2e8f0;
    padding: 3px 8px;
    border-radius: 50rem !important;
    max-width: 95px;
    line-height: 1.2;
    transition: all 0.2s ease;
    flex-shrink: 0;
}
.zb-product-card:hover .zb-product-variant-badge {
    border-color: #cbd5e1;
    background: #e2e8f0;
    color: #0f172a;
}
.zb-variant-dot {
    width: 6px;
    height: 6px;
    border-radius: 50%;
    flex-shrink: 0;
    display: inline-block;
}
.zb-variant-text {
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
.zb-btn-cart {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    width: 100%;
    padding: 9px 12px;
    border-radius: 50rem !important;
    background: #0f172a;
    color: #fff;
    border: none;
    font-size: 13px;
    font-weight: 700;
    cursor: pointer;
    margin-top: auto;
    transition: background 0.18s ease, transform 0.15s cubic-bezier(0.34, 1.56, 0.64, 1), box-shadow 0.18s ease;
    letter-spacing: 0.01em;
    white-space: nowrap;
    box-shadow: 0 4px 12px rgba(15, 23, 42, 0.12);
}
.zb-btn-cart:hover {
    background: #1e293b;
    color: #fff;
    transform: translateY(-1px);
    box-shadow: 0 6px 16px rgba(15, 23, 42, 0.2);
}
.zb-btn-cart:active {
    transform: scale(0.94);
}
.btn-cart-content {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
/* Granular Fluid Breakpoints for Product Cards (320px to 1400px+) */
@media (max-width: 359.98px) {
    .zb-product-card {
        border-radius: 14px !important;
    }
    .zb-product-body {
        padding: 6px 6px 8px;
        gap: 2px;
    }
    .zb-product-title {
        font-size: 11px;
        min-height: 28px;
        line-height: 1.25;
    }
    .zb-product-rating {
        font-size: 9.5px;
        gap: 2px;
    }
    .zb-product-price-row {
        gap: 3px;
        min-height: 20px;
    }
    .zb-product-price {
        font-size: 12px;
    }
    .zb-product-oldprice {
        font-size: 9.5px;
    }
    .zb-product-variant-badge {
        font-size: 8.5px;
        padding: 1px 4.5px;
        border-radius: 50rem !important;
        max-width: 55px;
        gap: 2.5px;
    }
    .zb-variant-dot {
        width: 4px;
        height: 4px;
    }
    .zb-btn-cart {
        padding: 5px 4px;
        font-size: 10.5px;
        border-radius: 50rem !important;
        gap: 3px;
    }
    .cart-anim-bag {
        font-size: 10px;
    }
    .cart-anim-text, .cart-success-text {
        font-size: 10px;
    }
    .zb-product-badge {
        font-size: 8.5px;
        padding: 2px 5px;
        top: 5px;
        left: 5px;
        border-radius: 50rem !important;
    }
    .zb-quickview-btn {
        width: 24px;
        height: 24px;
        font-size: 10px;
        top: 5px;
        right: 5px;
    }
}

@media (min-width: 360px) and (max-width: 539.98px) {
    .zb-product-card {
        border-radius: 18px !important;
    }
    .zb-product-body {
        padding: 8px 9px 11px;
        gap: 3px;
    }
    .zb-product-title {
        font-size: 12px;
        line-height: 1.3;
        min-height: 31px;
    }
    .zb-product-rating {
        font-size: 10px;
        gap: 2px;
    }
    .zb-product-price-row {
        gap: 4px;
    }
    .zb-product-price {
        font-size: 13.5px;
    }
    .zb-product-oldprice {
        font-size: 10.5px;
    }
    .zb-product-variant-badge {
        font-size: 9.5px;
        padding: 2px 6px;
        border-radius: 50rem !important;
        max-width: 75px;
        gap: 3.5px;
    }
    .zb-variant-dot {
        width: 5px;
        height: 5px;
    }
    .zb-btn-cart {
        padding: 7px 8px;
        font-size: 11.5px;
        border-radius: 50rem !important;
        gap: 4px;
    }
    .cart-anim-bag {
        font-size: 11px;
    }
    .cart-anim-text, .cart-success-text {
        font-size: 11px;
    }
    .zb-product-badge {
        font-size: 9px;
        padding: 2.5px 7px;
        top: 7px;
        left: 7px;
        border-radius: 50rem !important;
    }
    .zb-quickview-btn {
        width: 28px;
        height: 28px;
        font-size: 11px;
        top: 7px;
        right: 7px;
    }
}

@media (min-width: 540px) and (max-width: 719.98px) {
    .zb-product-card {
        border-radius: 18px !important;
    }
    .zb-product-body {
        padding: 9px 10px 12px;
        gap: 3.5px;
    }
    .zb-product-title {
        font-size: 12px;
        line-height: 1.3;
        min-height: 32px;
    }
    .zb-product-rating {
        font-size: 10.5px;
    }
    .zb-product-price {
        font-size: 13.5px;
    }
    .zb-product-oldprice {
        font-size: 10.5px;
    }
    .zb-product-variant-badge {
        font-size: 9.5px;
        max-width: 72px;
    }
    .zb-btn-cart {
        padding: 7px 8px;
        font-size: 11.5px;
    }
    .zb-product-badge {
        font-size: 9.5px;
        padding: 3px 8px;
        top: 8px;
        left: 8px;
    }
    .zb-quickview-btn {
        width: 28px;
        height: 28px;
        font-size: 11px;
        top: 8px;
        right: 8px;
    }
}

@media (min-width: 720px) and (max-width: 959.98px) {
    .zb-product-card {
        border-radius: 18px !important;
    }
    .zb-product-body {
        padding: 10px 10px 12px;
        gap: 4px;
    }
    .zb-product-title {
        font-size: 12.5px;
        line-height: 1.32;
        min-height: 33px;
    }
    .zb-product-price {
        font-size: 14px;
    }
    .zb-product-oldprice {
        font-size: 11px;
    }
    .zb-product-variant-badge {
        font-size: 10px;
        max-width: 80px;
    }
    .zb-btn-cart {
        padding: 7.5px 10px;
        font-size: 12px;
    }
    .zb-product-badge {
        font-size: 10px;
        padding: 3px 8px;
        top: 8px;
        left: 8px;
    }
    .zb-quickview-btn {
        width: 30px;
        height: 30px;
        font-size: 11.5px;
        top: 8px;
        right: 8px;
    }
}

@media (min-width: 960px) and (max-width: 1199.98px) {
    .zb-product-card {
        border-radius: 20px;
    }
    .zb-product-body {
        padding: 11px 12px 14px;
        gap: 4.5px;
    }
    .zb-product-title {
        font-size: 13px;
        line-height: 1.35;
        min-height: 35px;
    }
    .zb-product-price {
        font-size: 14.5px;
    }
    .zb-product-oldprice {
        font-size: 11px;
    }
    .zb-product-variant-badge {
        font-size: 10px;
        max-width: 85px;
    }
    .zb-btn-cart {
        padding: 8px 10px;
        font-size: 12.5px;
    }
    .zb-product-badge {
        font-size: 10px;
        padding: 3.5px 8px;
        top: 9px;
        left: 9px;
    }
    .zb-quickview-btn {
        width: 32px;
        height: 32px;
        font-size: 12px;
        top: 9px;
        right: 9px;
    }
}
</style>
@endpush
@endonce
