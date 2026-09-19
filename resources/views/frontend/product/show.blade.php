@extends('frontend.layouts.app')

@php
    $siteName = $siteName ?? 'Zippy BD';
    $productTitle = $product->title ?? '';
    $productPrice = (float) str_replace(',', '', (string)$product->price);
    $priceText = '৳' . number_format($productPrice, 0);
    $seoTitle = $productTitle . ' Price in Bangladesh | ' . $siteName;

    $cleanShortDesc = trim(preg_replace('/\s+/', ' ', strip_tags($product->short_desc ?: '')));
    $badgeText = ' 100% authentic, fast home delivery & 7-day warranty.';
    $maxLead = 158 - strlen($badgeText);
    if (!empty($cleanShortDesc)) {
        $lead = Str::limit("Buy {$productTitle} at {$priceText} in Bangladesh. {$cleanShortDesc}", $maxLead, '...');
        $productSeoDesc = $lead . $badgeText;
    } else {
        $productSeoDesc = Str::limit("Buy {$productTitle} at {$priceText} in Bangladesh. 100% original product with fast cash on delivery, official warranty & 7-day easy replacement at {$siteName}.", 158, '...');
    }

    $productCanonical = route('product.show', $product->slug);
    $productImg = !empty($product->main_image) ? (filter_var($product->main_image, FILTER_VALIDATE_URL) ? $product->main_image : asset($product->main_image)) : asset('images/product-placeholder.svg');
    $inStock = ($product->stock_qty > 0);

    $galleryList = is_array($galleryImages ?? null) ? $galleryImages : (json_decode($product->gallery_images ?? '', true) ?: []);
    $allImages = array_values(array_unique(array_filter([
        $productImg,
        ...array_map(function($img) {
            return !empty($img) ? (filter_var($img, FILTER_VALIDATE_URL) ? $img : asset($img)) : null;
        }, (array)$galleryList)
    ])));

    $dbReviewCount = (int)($reviewsCount ?? ($product->reviews_count ?? 0));
    $dbRatingValue = (float)($avgRating ?? ($product->rating ?? 0));
    $finalRatingValue = ($dbReviewCount > 0 && $dbRatingValue > 0) ? number_format($dbRatingValue, 1, '.', '') : null;
    $finalReviewCount = $dbReviewCount;

    $productSchema = [
        '@context' => 'https://schema.org/',
        '@type' => 'Product',
        'name' => $productTitle,
        'image' => !empty($allImages) ? $allImages : [$productImg],
        'description' => $productSeoDesc,
        'sku' => (string)($product->sku ?: 'ZB-' . $product->id),
        'mpn' => (string)($product->sku ?: 'ZB-' . $product->id),
        'brand' => [
            '@type' => 'Brand',
            'name' => $product->brand_name ?? $siteName,
        ],
        'offers' => [
            '@type' => 'Offer',
            'url' => $productCanonical,
            'priceCurrency' => 'BDT',
            'price' => $productPrice,
            'priceValidUntil' => date('Y') . '-12-31',
            'itemCondition' => 'https://schema.org/NewCondition',
            'availability' => $inStock ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock',
            'seller' => [
                '@type' => 'Organization',
                'name' => $siteName,
            ],
        ],
    ];

    if ($finalReviewCount > 0 && $finalRatingValue) {
        $productSchema['aggregateRating'] = [
            '@type' => 'AggregateRating',
            'ratingValue' => (string)$finalRatingValue,
            'reviewCount' => (int)$finalReviewCount,
            'bestRating' => '5',
            'worstRating' => '1',
        ];
    }

    if (isset($productReviews) && $productReviews->isNotEmpty()) {
        $reviewsSchema = [];
        foreach ($productReviews->take(5) as $r) {
            $reviewsSchema[] = [
                '@type' => 'Review',
                'author' => [
                    '@type' => 'Person',
                    'name' => $r->customer_name ?: 'Verified Customer',
                ],
                'datePublished' => date('Y-m-d', strtotime($r->created_at)),
                'reviewBody' => $r->comment,
                'reviewRating' => [
                    '@type' => 'Rating',
                    'ratingValue' => (string)$r->rating,
                    'bestRating' => '5',
                    'worstRating' => '1',
                ],
            ];
        }
        $productSchema['review'] = $reviewsSchema;
    }

    $breadcrumbItems = [
        [
            '@type' => 'ListItem',
            'position' => 1,
            'name' => 'হোম',
            'item' => url('/'),
        ]
    ];
    $bPos = 2;
    if (!empty($categoryBreadcrumbs) && is_iterable($categoryBreadcrumbs)) {
        foreach ($categoryBreadcrumbs as $bCat) {
            $breadcrumbItems[] = [
                '@type' => 'ListItem',
                'position' => $bPos++,
                'name' => $bCat->name_bn ?: $bCat->name,
                'item' => route('category.show', $bCat->slug),
            ];
        }
    } elseif (!empty($product->cat_slug)) {
        $breadcrumbItems[] = [
            '@type' => 'ListItem',
            'position' => $bPos++,
            'name' => $product->cat_name_bn ?: ($product->cat_name_en ?: 'ক্যাটাগরি'),
            'item' => route('category.show', $product->cat_slug),
        ];
    }
    $breadcrumbItems[] = [
        '@type' => 'ListItem',
        'position' => $bPos,
        'name' => $productTitle,
        'item' => $productCanonical,
    ];

    $breadcrumbSchema = [
        '@context' => 'https://schema.org',
        '@type' => 'BreadcrumbList',
        'itemListElement' => $breadcrumbItems,
    ];

    $faqSchema = [
        '@context' => 'https://schema.org',
        '@type' => 'FAQPage',
        'mainEntity' => [
            [
                '@type' => 'Question',
                'name' => $productTitle . ' এর ডেলিভারি পেতে কত দিন সময় লাগবে?',
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text' => 'ঢাকা শহরের মধ্যে ২৪ থেকে ৪৮ ঘণ্টার মধ্যে এবং ঢাকার বাইরে ২ থেকে ৩ কার্যদিবসের মধ্যে দ্রুত ডেলিভারি সম্পন্ন করা হয়।'
                ]
            ],
            [
                '@type' => 'Question',
                'name' => 'আমি কি ক্যাশ অন ডেলিভারি (Cash on Delivery) সুবিধা পাব?',
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text' => 'হ্যাঁ, পণ্য হাতে পেয়ে দেখে সম্পূর্ণ মূল্য পরিশোধ করার ক্যাশ অন ডেলিভারি সুবিধা রয়েছে।'
                ]
            ],
            [
                '@type' => 'Question',
                'name' => 'পণ্যটি কি শতভাগ অরিজিনাল এবং জেনুইন?',
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text' => 'জি, ' . $siteName . ' এর প্রতিটি পণ্য শতভাগ অথেনটিক, নতুন এবং অফিসিয়াল ইনট্যাক্ট প্যাক।'
                ]
            ],
            [
                '@type' => 'Question',
                'name' => 'ত্রুটিযুক্ত বা ভুল পণ্য পেলে পরিবর্তনের নিয়ম কি?',
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text' => 'পণ্য পাওয়ার পর কোনো সমস্যা বা ত্রুটি থাকলে তাৎক্ষণিক ৭ দিনের ফ্রি রিটার্ন ও রিপ্লেসমেন্ট সুবিধা পাবেন।'
                ]
            ],
        ]
    ];
@endphp

@section('title', $seoTitle)
@section('meta_description', $productSeoDesc)
@section('meta_keywords', $productTitle . ', ' . ($product->cat_name_bn ?? '') . ', ' . $productTitle . ' price in bd, buy ' . $productTitle . ' bangladesh, ' . strtolower($siteName))
@section('og_image', $productImg)
@section('og_type', 'product')
@section('canonical', $productCanonical)

@push('extra_meta')
    <meta property="og:title" content="{{ $seoTitle }}">
    <meta property="og:description" content="{{ $productSeoDesc }}">
    <meta property="og:url" content="{{ $productCanonical }}">
    <meta property="og:type" content="product">
    <meta property="og:site_name" content="{{ $siteName }}">
    <meta property="og:image" content="{{ $productImg }}">
    <meta property="og:image:secure_url" content="{{ $productImg }}">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="product:price:amount" content="{{ $productPrice }}">
    <meta property="product:price:currency" content="BDT">
    <meta property="og:price:amount" content="{{ $productPrice }}">
    <meta property="og:price:currency" content="BDT">
    <meta property="product:availability" content="{{ $inStock ? 'in stock' : 'out of stock' }}">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $seoTitle }}">
    <meta name="twitter:description" content="{{ $productSeoDesc }}">
    <meta name="twitter:image" content="{{ $productImg }}">
@endpush

@push('schema')
    <script type="application/ld+json">
        {!! json_encode($productSchema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}
    </script>
    <script type="application/ld+json">
        {!! json_encode($breadcrumbSchema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}
    </script>
    <script type="application/ld+json">
        {!! json_encode($faqSchema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}
    </script>
@endpush

@php
    $galleryList = is_array($galleryImages ?? null) ? $galleryImages : (json_decode($product->gallery_images ?? '', true) ?: []);
    if (!is_array($galleryList) || empty($galleryList)) {
        $galleryList = [$product->main_image ?? ''];
    }
    $galleryList = array_values(array_filter(array_map(function($img) {
        return (empty($img) || str_contains($img, 'example.com')) ? null : $img;
    }, $galleryList)));
    if (empty($galleryList)) {
        $galleryList = [$product->main_image && !str_contains($product->main_image, 'example.com') ? $product->main_image : asset('images/product-placeholder.svg')];
    }

    $variantList = is_array($variants ?? null) ? $variants : (json_decode($product->variants ?? '', true) ?: []);
    if (!is_array($variantList)) {
        $variantList = [];
    }

    $specList = is_array($specifications ?? null) ? $specifications : (json_decode($product->specifications ?? '', true) ?: []);
    if (!is_array($specList)) {
        $specList = [];
    }

    $relatedList = is_countable($relatedProducts ?? null) ? $relatedProducts : [];

    $discountPercent = 0;
    if ($product->old_price && $product->old_price > $product->price) {
        $discountPercent = round((($product->old_price - $product->price) / $product->old_price) * 100);
    }

    $brandFallbackSvg = 'data:image/svg+xml;utf8,' . rawurlencode('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 400 400" width="100%" height="100%"><rect width="100%" height="100%" fill="#f1f5f9"/><text x="50%" y="46%" font-family="system-ui, -apple-system, sans-serif" font-size="22" font-weight="900" fill="#0f172a" text-anchor="middle" letter-spacing="2">' . strtoupper($siteName ?? 'ZIPPY BD') . '</text><text x="50%" y="54%" font-family="system-ui, -apple-system, sans-serif" font-size="11" font-weight="600" fill="#64748b" text-anchor="middle" letter-spacing="1">PREMIUM STORE</text></svg>');

    $shortDescLines = array_filter(array_map('trim', preg_split('/[\r\n]+/', strip_tags($product->short_desc ?: ''))));
    if (empty($shortDescLines)) {
        $shortDescLines = [
            '১০০% জেনুইন ও অথেনটিক প্রিমিয়াম প্রোডাক্ট',
            'দ্রুততম হোম ডেলিভারি ও ক্যাশ অন ডেলিভারি সুবিধা',
            '৭ দিনের ঝামেলাহীন রিপ্লেসমেন্ট গ্যারান্টি'
        ];
    }
@endphp

@section('content')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />

<style>
    
    .product-master-box {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 20px;
        box-shadow: 0 4px 20px -2px rgba(0, 0, 0, 0.04);
    }

    
    .p-gallery-sticky {
        position: sticky;
        top: 85px;
        z-index: 10;
    }

    .p-gallery-box {
        position: relative;
        border-radius: 16px;
        overflow: hidden;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        width: 100%;
        aspect-ratio: 1 / 1;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 0;
        user-select: none;
    }

    .productMainSwiper {
        width: 100%;
        height: 100%;
        position: relative;
    }

    .productMainSwiper .swiper-slide {
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        background: #f8fafc;
    }

    .zoom-slide-container {
        width: 100%;
        height: 100%;
        position: relative;
        overflow: hidden;
        cursor: crosshair;
    }

    @media (max-width: 767.98px) {
        .zoom-slide-container {
            cursor: pointer;
        }
    }

    .zoom-slide-container img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.15s ease-out;
        pointer-events: none;
    }

    
    .main-swiper-arrow {
        width: 36px !important;
        height: 36px !important;
        border-radius: 50% !important;
        background: rgba(255, 255, 255, 0.9) !important;
        backdrop-filter: blur(8px) !important;
        border: 1px solid #e2e8f0 !important;
        color: #0f172a !important;
        font-size: 0.82rem !important;
        box-shadow: 0 4px 14px rgba(0, 0, 0, 0.12) !important;
        opacity: 0;
        transition: all 0.25s ease !important;
        z-index: 15 !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        margin-top: 0 !important;
        transform: translateY(-50%) !important;
    }

    .main-swiper-arrow::after {
        display: none !important;
    }

    .p-gallery-box:hover .main-swiper-arrow {
        opacity: 1;
    }

    .main-swiper-arrow:hover {
        background: #0f172a !important;
        color: #ffffff !important;
        transform: translateY(-50%) scale(1.08) !important;
    }

    
    .productThumbSwiper {
        width: 100%;
        padding: 8px 4px 8px;
    }

    .p-thumb-slide {
        width: 64px !important;
        height: 64px !important;
        flex-shrink: 0;
        cursor: pointer;
        opacity: 0.75;
        transition: opacity 0.22s ease, transform 0.22s cubic-bezier(0.2, 0.8, 0.2, 1);
        box-sizing: border-box;
    }

    .p-thumb-slide:hover {
        opacity: 1;
    }

    .p-thumb-slide .thumb-inner {
        width: 100%;
        height: 100%;
        border-radius: 14px;
        border: 2px solid #e2e8f0;
        overflow: hidden;
        background: #ffffff;
        padding: 2px;
        box-sizing: border-box;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.06);
        transition: border-color 0.22s ease, box-shadow 0.22s ease, background-color 0.22s ease, transform 0.22s cubic-bezier(0.2, 0.8, 0.2, 1);
    }

    .p-thumb-slide .thumb-inner img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        border-radius: 10px;
        transition: transform 0.22s cubic-bezier(0.2, 0.8, 0.2, 1);
    }

    .p-thumb-slide.swiper-slide-thumb-active,
    .p-thumb-slide.active {
        opacity: 1 !important;
    }

    .p-thumb-slide.swiper-slide-thumb-active .thumb-inner,
    .p-thumb-slide.active .thumb-inner {
        border-color: #2563eb !important;
        background: #eff6ff !important;
        box-shadow: 0 0 0 1.5px rgba(37, 99, 235, 0.3), 0 4px 12px rgba(37, 99, 235, 0.12) !important;
        transform: scale(1.03);
    }

    .p-thumb-slide.swiper-slide-thumb-active .thumb-inner img,
    .p-thumb-slide.active .thumb-inner img {
        transform: scale(1.02);
    }

    .p-thumb-slide:hover:not(.swiper-slide-thumb-active):not(.active) .thumb-inner {
        border-color: #93c5fd;
        background: #f8fafc;
        transform: translateY(-2px);
        box-shadow: 0 4px 10px rgba(37, 99, 235, 0.08);
    }

    .p-thumb-slide:hover:not(.swiper-slide-thumb-active):not(.active) .thumb-inner img {
        transform: scale(1.05);
    }

    .btn-gallery-action {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.9);
        backdrop-filter: blur(8px);
        border: 1px solid #e2e8f0;
        color: #0f172a;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        transition: all 0.2s ease;
        z-index: 15;
    }

    .btn-gallery-action:hover {
        background: #0f172a;
        color: #ffffff;
        transform: scale(1.08);
    }

    
    .bulk-variant-item {
        background: #ffffff;
        border: 1.5px solid #e2e8f0;
        border-radius: 18px;
        padding: 10px 14px;
        transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02);
    }

    .bulk-variant-item:hover {
        border-color: #cbd5e1;
        background: #f8fafc;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
    }

    .bulk-variant-item.item-active {
        background: #f0fdf4 !important;
        border-color: #10b981 !important;
        box-shadow: 0 4px 14px rgba(16, 185, 129, 0.15);
    }

    .bulk-variant-thumb {
        width: 46px;
        height: 46px;
        border-radius: 12px;
        border: 1px solid #e2e8f0;
        overflow: hidden;
        flex-shrink: 0;
        background: #ffffff;
        padding: 2px;
        cursor: pointer;
        margin-right: 10px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
        transition: transform 0.2s ease;
    }

    .bulk-variant-thumb:hover {
        transform: scale(1.06);
    }

    .bulk-variant-thumb img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        border-radius: 9px;
    }

    .bulk-qty-box {
        display: inline-flex;
        align-items: center;
        background: #f1f5f9;
        border: 1px solid #e2e8f0;
        border-radius: 9999px;
        padding: 3px;
        box-shadow: inset 0 1px 2px rgba(0, 0, 0, 0.03);
    }

    .bulk-qty-btn {
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        border: none;
        background: #ffffff;
        color: #0f172a;
        border-radius: 50%;
        font-weight: 700;
        font-size: 0.9rem;
        cursor: pointer;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
        transition: all 0.2s cubic-bezier(0.34, 1.56, 0.64, 1);
    }

    .bulk-qty-btn:hover {
        background: #0f172a;
        color: #ffffff;
        transform: scale(1.08);
    }

    .bulk-qty-btn:active {
        transform: scale(0.9);
    }

    .bulk-qty-val {
        min-width: 34px;
        text-align: center;
        font-family: var(--font-heading);
        font-weight: 800;
        font-size: 0.95rem;
        color: #0f172a;
    }

    
    .live-sidebar-summary {
        position: sticky;
        top: 85px;
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 24px;
        padding: 20px 18px;
        box-shadow: 0 12px 32px -6px rgba(15, 23, 42, 0.06), 0 2px 8px rgba(0, 0, 0, 0.02);
        transition: all 0.2s ease;
    }

    .summary-header-title {
        font-family: var(--font-heading);
        font-size: 0.95rem;
        font-weight: 700;
        color: #0f172a;
    }

    .summary-header-badge {
        font-family: var(--font-heading);
        font-size: 0.72rem;
        font-weight: 700;
        padding: 4px 11px;
        background: #0f172a;
        color: #ffffff;
        border-radius: 50rem;
        letter-spacing: 0.2px;
    }

    
    .delivery-zone-card {
        border: 1.5px solid #e2e8f0;
        border-radius: 16px;
        padding: 11px 14px;
        background: #ffffff;
        cursor: pointer;
        transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        user-select: none;
    }

    .delivery-zone-card:hover {
        border-color: #cbd5e1;
        background: #f8fafc;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.04);
    }

    .delivery-zone-card.active-zone {
        border-color: #0f172a !important;
        background: #f8fafc !important;
        box-shadow: 0 4px 14px rgba(15, 23, 42, 0.08);
    }
        box-shadow: 0 3px 12px rgba(15, 23, 42, 0.08);
    }

    .custom-zone-radio {
        width: 18px;
        height: 18px;
        min-width: 18px;
        border-radius: 50%;
        border: 2px solid #cbd5e1;
        background: #ffffff;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s ease;
        flex-shrink: 0;
    }

    .delivery-zone-card.active-zone .custom-zone-radio {
        border-color: #0f172a;
        background: #0f172a;
    }

    .delivery-zone-card.active-zone .custom-zone-radio::after {
        content: '';
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background: #ffffff;
    }

    .zone-info-block {
        flex-grow: 1;
        min-width: 0;
        padding-left: 10px;
    }

    .zone-info-title {
        font-family: var(--font-heading);
        font-weight: 700;
        font-size: 0.82rem;
        color: #0f172a;
        line-height: 1.2;
        display: block;
    }

    .zone-info-sub {
        font-size: 0.68rem;
        color: #64748b;
        display: block;
        margin-top: 1px;
        line-height: 1.2;
    }

    .zone-price-pill {
        font-family: var(--font-heading);
        font-weight: 700;
        font-size: 0.78rem;
        color: #dc2626;
        background: #fef2f2;
        border: 1px solid #fee2e2;
        border-radius: 6px;
        padding: 2px 8px;
        flex-shrink: 0;
    }

    .delivery-zone-card.active-zone .zone-price-pill {
        background: #fee2e2;
        border-color: #fca5a5;
    }

    .delivery-est-banner {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        padding: 7px 10px;
        font-size: 0.72rem;
        color: #475569;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    #summarySelectedBreakdown {
        padding-bottom: 10px;
    }

    
    .summary-item-pill {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 9px;
        margin: 5px 0;
        font-size: 0.78rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
    }

    
    .grand-total-highlight-box {
        background: #f8fafc;
        border: 1.5px dashed #cbd5e1;
        border-radius: 12px;
        padding: 10px 14px;
    }

    .btn-action-buynow {
        background: #0f172a;
        color: #ffffff;
        border: none;
        border-radius: 10px;
        font-family: var(--font-heading);
        font-weight: 700;
        font-size: 0.92rem;
        padding: 11px 16px;
        box-shadow: 0 4px 14px rgba(15, 23, 42, 0.2);
        transition: all 0.2s ease;
    }

    .btn-action-buynow:hover {
        background: #000000;
        color: #ffffff;
        transform: translateY(-1px);
        box-shadow: 0 6px 18px rgba(0, 0, 0, 0.28);
    }

    .btn-action-cart {
        background: #ffffff;
        color: #0f172a;
        border: 1.5px solid #0f172a;
        border-radius: 10px;
        font-family: var(--font-heading);
        font-weight: 700;
        font-size: 0.88rem;
        padding: 9px 16px;
        transition: all 0.2s ease;
    }

    .btn-action-cart:hover {
        background: #f1f5f9;
        color: #000000;
    }

    .btn-action-whatsapp {
        background: #ecfdf5;
        color: #047857;
        border: 1.5px solid #a7f3d0;
        border-radius: 10px;
        font-family: var(--font-heading);
        font-weight: 700;
        font-size: 0.85rem;
        padding: 8px 14px;
        transition: all 0.2s ease;
    }

    .btn-action-whatsapp:hover {
        background: #d1fae5;
        color: #065f46;
        border-color: #6ee7b7;
    }

    
    .share-social-popup {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        box-shadow: 0 20px 40px -12px rgba(15, 23, 42, 0.18), 0 0 0 1px rgba(15, 23, 42, 0.05);
        padding: 16px;
        min-width: 290px;
        max-width: 320px;
        z-index: 1060;
        animation: popupFadeIn 0.2s cubic-bezier(0.16, 1, 0.3, 1);
    }

    @keyframes popupFadeIn {
        from { opacity: 0; transform: translateY(6px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .share-social-btn {
        display: flex;
        flex-direction: column;
        align-items: center;
        text-decoration: none;
        gap: 6px;
        transition: transform 0.2s ease;
    }

    .share-social-btn:hover {
        transform: translateY(-2px);
    }

    .share-social-icon {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.15rem;
        transition: all 0.2s ease;
    }

    .share-social-btn:hover .share-social-icon {
        box-shadow: 0 6px 16px rgba(0, 0, 0, 0.15);
    }

    .share-icon-fb { background: #eff6ff; color: #1877f2; }
    .share-icon-wa { background: #ecfdf5; color: #25d366; }
    .share-icon-x { background: #f1f5f9; color: #0f172a; }
    .share-icon-tg { background: #f0f9ff; color: #229ed9; }

    .share-social-label {
        font-size: 11px;
        font-weight: 600;
        color: #475569;
        font-family: var(--font-heading);
    }

    .share-copy-input-group {
        background: #f8fafc;
        border: 1.5px solid #e2e8f0;
        border-radius: 10px;
        padding: 4px 6px 4px 10px;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    
    .product-tabs-scroll-wrap {
        background: #f1f5f9;
        border-radius: 50rem;
        padding: 4px;
        display: inline-flex;
        gap: 4px;
        max-width: 100%;
        overflow-x: auto;
        scrollbar-width: none;
        -ms-overflow-style: none;
        -webkit-overflow-scrolling: touch;
        box-shadow: inset 0 1px 3px rgba(0, 0, 0, 0.04);
    }

    .product-tabs-scroll-wrap::-webkit-scrollbar {
        display: none;
    }

    .product-tab-btn {
        padding: 8px 18px;
        border-radius: 50rem;
        font-size: 13px;
        font-weight: 700;
        font-family: var(--font-heading);
        color: #64748b;
        background: transparent;
        border: none;
        transition: all 0.2s cubic-bezier(0.34, 1.56, 0.64, 1);
        white-space: nowrap;
        cursor: pointer;
    }

    .product-tab-btn:hover {
        color: #0f172a;
        background: rgba(255, 255, 255, 0.6);
    }

    .product-tab-btn.active {
        background: #ffffff !important;
        color: #0f172a !important;
        border: none !important;
        box-shadow: 0 3px 10px rgba(0, 0, 0, 0.08) !important;
    }

    
    .zk-bottom-nav {
        display: none !important;
    }

    .mobile-floating-action-sheet {
        position: fixed;
        bottom: calc(10px + env(safe-area-inset-bottom, 0px)) !important;
        left: 12px !important;
        right: 12px !important;
        width: auto !important;
        background: rgba(255, 255, 255, 0.94);
        backdrop-filter: blur(24px);
        -webkit-backdrop-filter: blur(24px);
        border: 1px solid rgba(255, 255, 255, 0.85);
        border-radius: 28px !important;
        padding: 8px 12px !important;
        z-index: 1040;
        box-shadow: 0 16px 36px -4px rgba(15, 23, 42, 0.16), 0 4px 12px rgba(0, 0, 0, 0.04);
    }

    [data-bs-theme="dark"] .mobile-floating-action-sheet {
        background: rgba(15, 23, 42, 0.92);
        border-color: rgba(255, 255, 255, 0.12);
        box-shadow: 0 16px 36px -4px rgba(0, 0, 0, 0.45);
    }

    .btn-mobile-back {
        width: 42px;
        height: 42px;
        min-width: 42px;
        border-radius: 50% !important;
        background: #f1f5f9;
        color: #0f172a;
        border: 1px solid #e2e8f0;
        font-size: 15px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.2s cubic-bezier(0.34, 1.56, 0.64, 1);
        flex-shrink: 0;
        text-decoration: none;
    }

    .btn-mobile-back:active {
        transform: scale(0.9);
        background: #e2e8f0;
    }

    [data-bs-theme="dark"] .btn-mobile-back {
        background: #1e293b;
        color: #f8fafc;
        border-color: #334155;
    }

    [data-bs-theme="dark"] .btn-mobile-back:active {
        background: #334155;
    }

    .mobile-floating-action-sheet .btn-action-cart {
        height: 44px;
        padding: 0 16px;
        font-size: 13.5px;
        border-radius: 50rem !important;
        font-weight: 700;
        white-space: nowrap;
        background: #ffffff;
        border: 1.5px solid #0f172a;
        color: #0f172a;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.04);
        transition: all 0.2s cubic-bezier(0.34, 1.56, 0.64, 1);
    }
    .mobile-floating-action-sheet .btn-action-cart:active {
        transform: scale(0.94);
    }

    .mobile-floating-action-sheet .btn-action-buynow {
        height: 44px;
        padding: 0 20px;
        font-size: 14.5px;
        border-radius: 50rem !important;
        font-weight: 700;
        white-space: nowrap;
        background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
        color: #ffffff;
        border: none;
        box-shadow: 0 6px 18px -2px rgba(15, 23, 42, 0.35);
        transition: all 0.2s cubic-bezier(0.34, 1.56, 0.64, 1);
    }
    .mobile-floating-action-sheet .btn-action-buynow:active {
        transform: scale(0.94);
        box-shadow: 0 2px 8px rgba(15, 23, 42, 0.2);
    }

    @media (max-width: 991.98px) {
        .product-page-main {
            padding-bottom: calc(90px + env(safe-area-inset-bottom, 0px)) !important;
        }
    }

    
    .custom-lightbox-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100vw;
        height: 100vh;
        background: rgba(0, 0, 0, 0.92);
        backdrop-filter: blur(16px);
        z-index: 9999;
        display: none;
        align-items: center;
        justify-content: center;
        flex-direction: column;
        padding: 20px;
        opacity: 0;
        transition: opacity 0.25s ease;
    }

    .custom-lightbox-overlay.show {
        display: flex;
        opacity: 1;
    }

    .btn-lightbox-close {
        position: absolute;
        top: 20px;
        right: 20px;
        width: 44px;
        height: 44px;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.15);
        border: 1px solid rgba(255, 255, 255, 0.3);
        color: #ffffff;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        font-size: 1.25rem;
        transition: all 0.2s ease;
    }

    .btn-lightbox-close:hover {
        background: rgba(255, 255, 255, 0.3);
        transform: scale(1.08);
    }

    .lightbox-stage {
        max-width: 90vw;
        max-height: 72vh;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .lightbox-stage img {
        max-width: 90vw;
        max-height: 72vh;
        object-fit: contain;
        border-radius: 12px;
        box-shadow: 0 20px 50px rgba(0, 0, 0, 0.8);
        cursor: zoom-in;
        transition: transform 0.2s ease;
    }

    .lightbox-thumb-strip {
        display: flex;
        gap: 10px;
        margin-top: 16px;
        overflow-x: auto;
        max-width: 90vw;
        padding: 6px;
    }

    .lightbox-thumb-strip .strip-thumb {
        width: 56px;
        height: 56px;
        border-radius: 10px;
        overflow: hidden;
        border: 2px solid transparent;
        opacity: 0.5;
        cursor: pointer;
        transition: all 0.2s ease;
        flex-shrink: 0;
    }

    .lightbox-thumb-strip .strip-thumb.active {
        opacity: 1;
        border-color: #ffffff;
        transform: scale(1.05);
    }

    .lightbox-thumb-strip .strip-thumb img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    @media (min-width: 1200px) {
        .row-cols-xl-6 > * {
            flex: 0 0 auto;
            width: 16.66666667%;
        }
    }
</style>
<style>
    /* Modern Product Breadcrumb & Top Bar */
    .product-top-bar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        min-height: 38px;
    }
    .product-breadcrumb-nav {
        flex: 1;
        min-width: 0;
        overflow: hidden;
    }
    .product-breadcrumb-list {
        display: flex;
        align-items: center;
        flex-wrap: nowrap;
        overflow-x: auto;
        white-space: nowrap;
        margin: 0;
        padding: 4px 0;
        list-style: none;
        scrollbar-width: none;
        -ms-overflow-style: none;
        -webkit-overflow-scrolling: touch;
        gap: 6px;
        font-size: 13px;
    }
    .product-breadcrumb-list::-webkit-scrollbar {
        display: none;
    }
    .product-breadcrumb-list .crumb-link {
        color: #64748b;
        text-decoration: none;
        font-weight: 500;
        transition: color 0.15s ease;
        display: inline-flex;
        align-items: center;
        gap: 4px;
        flex-shrink: 0;
    }
    .product-breadcrumb-list .crumb-link:hover {
        color: #0f172a;
    }
    .product-breadcrumb-list .crumb-sep {
        color: #cbd5e1;
        font-size: 10px;
        flex-shrink: 0;
        user-select: none;
        display: inline-flex;
        align-items: center;
    }
    .product-breadcrumb-list .crumb-current {
        color: #0f172a;
        font-weight: 600;
        max-width: 260px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        flex-shrink: 0;
    }
    @media (max-width: 576px) {
        .product-breadcrumb-list .crumb-current {
            max-width: 150px;
        }
    }
    .btn-product-share {
        width: 36px;
        height: 36px;
        min-width: 36px;
        border-radius: 50%;
        background: #ffffff;
        border: 1px solid #e2e8f0;
        color: #2563eb;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 14px;
        cursor: pointer;
        transition: all 0.2s ease;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
        flex-shrink: 0;
        padding: 0;
    }
    .btn-product-share:hover {
        background: #f8fafc;
        border-color: #cbd5e1;
        color: #1d4ed8;
        transform: translateY(-1px);
        box-shadow: 0 3px 8px rgba(0, 0, 0, 0.08);
    }
    .btn-product-share:active {
        transform: scale(0.95);
    }
    @media (min-width: 768px) {
        .btn-product-share {
            width: auto;
            border-radius: 9999px;
            padding: 0 14px;
            gap: 6px;
            font-size: 12.5px;
            font-weight: 600;
            color: #1e293b;
            height: 36px;
        }
    }

@media (max-width: 767.98px) {
    /* Main Page Container & Layout */
    .product-page-main {
        padding-top: 8px !important;
        padding-bottom: calc(88px + env(safe-area-inset-bottom, 0px)) !important;
    }
    .product-page-main > .container {
        padding-left: 10px !important;
        padding-right: 10px !important;
        max-width: 100% !important;
    }
    .product-master-box {
        padding: 12px 10px !important;
        border-radius: 16px !important;
        margin-bottom: 16px !important;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.04) !important;
        border: 1px solid #e2e8f0 !important;
    }

    /* Product Gallery */
    .p-gallery-sticky {
        position: relative !important;
        top: 0 !important;
        z-index: 5;
    }
    .p-gallery-box {
        border-radius: 14px !important;
        aspect-ratio: 1 / 1 !important;
        width: 100% !important;
        max-width: 100% !important;
        background: #f8fafc;
    }
    .productThumbSwiper {
        padding: 8px 0 4px !important;
    }
    .p-thumb-slide {
        width: 54px !important;
        height: 54px !important;
    }
    .p-thumb-slide .thumb-inner {
        border-radius: 10px !important;
        padding: 2px !important;
    }

    /* Product Titles & Badges */
    .product-title-heading {
        font-size: 1.18rem !important;
        line-height: 1.35 !important;
        margin-bottom: 8px !important;
        font-weight: 700 !important;
    }
    .product-price-box {
        padding: 10px 12px !important;
        border-radius: 12px !important;
        margin-bottom: 12px !important;
    }
    .product-current-price {
        font-size: 1.45rem !important;
        font-weight: 800 !important;
    }

    /* Variants / Bulk Selector */
    .bulk-variant-item {
        padding: 8px 10px !important;
        border-radius: 12px !important;
        margin-bottom: 8px !important;
    }
    .bulk-variant-thumb {
        width: 40px !important;
        height: 40px !important;
        border-radius: 8px !important;
        margin-right: 8px !important;
    }
    .bulk-qty-box {
        gap: 2px;
    }
    .bulk-qty-btn {
        width: 28px !important;
        height: 28px !important;
        border-radius: 6px !important;
    }
    .bulk-qty-val {
        min-width: 24px !important;
        font-size: 0.88rem !important;
    }

    /* Order Summary Sidebar on Mobile */
    .live-sidebar-summary {
        padding: 14px 12px !important;
        border-radius: 14px !important;
        background: #fafbfc !important;
        border: 1px solid #e2e8f0 !important;
        margin-top: 14px !important;
    }
    .delivery-zone-card {
        padding: 9px 11px !important;
        border-radius: 10px !important;
    }
    .zone-price-pill {
        padding: 3px 8px !important;
        font-size: 0.74rem !important;
    }
    .grand-total-highlight-box {
        padding: 10px 12px !important;
        border-radius: 10px !important;
    }

    /* Tabs on Mobile */
    .product-tabs-scroll-wrap {
        overflow-x: auto !important;
        -webkit-overflow-scrolling: touch !important;
        padding-bottom: 6px !important;
    }
    .product-tab-btn {
        padding: 7px 14px !important;
        font-size: 12px !important;
        flex-shrink: 0 !important;
    }
    .product-description-content img {
        max-width: 100% !important;
        height: auto !important;
        border-radius: 8px !important;
    }

    /* Floating Bottom Action Sheet */
    .mobile-sheet-price-col {
        min-width: 72px;
    }
    .mobile-sheet-price-col #mobileGrandTotalText {
        font-size: 1.15rem !important;
        font-weight: 800 !important;
    }
}
</style>

<div class="product-page-main py-3 py-md-4">
    <div class="container">
        
        
        <div class="product-top-bar mb-2.5">
            <nav aria-label="breadcrumb" class="product-breadcrumb-nav">
                <ol class="product-breadcrumb-list">
                    <li>
                        <a href="{{ route('home') }}" class="crumb-link">
                            <i class="fa-solid fa-house" style="font-size: 11px;"></i>
                            <span>হোম</span>
                        </a>
                    </li>
                    @if(isset($categoryBreadcrumbs) && count($categoryBreadcrumbs) > 0)
                        @foreach($categoryBreadcrumbs as $bCat)
                            <li class="crumb-sep" aria-hidden="true"><i class="fa-solid fa-chevron-right"></i></li>
                            <li>
                                <a href="{{ route('category.show', $bCat->slug) }}" class="crumb-link" title="{{ $bCat->name_bn ?? $bCat->name }}">
                                    {{ $bCat->name_bn ?? $bCat->name }}
                                </a>
                            </li>
                        @endforeach
                    @elseif(!empty($product->cat_slug))
                        <li class="crumb-sep" aria-hidden="true"><i class="fa-solid fa-chevron-right"></i></li>
                        <li>
                            <a href="{{ route('category.show', $product->cat_slug) }}" class="crumb-link" title="{{ $product->cat_name_bn ?? $product->cat_name_en }}">
                                {{ $product->cat_name_bn ?? $product->cat_name_en }}
                            </a>
                        </li>
                    @endif
                    <li class="crumb-sep" aria-hidden="true"><i class="fa-solid fa-chevron-right"></i></li>
                    <li class="crumb-current" aria-current="page" title="{{ $product->title }}">{{ $product->title }}</li>
                </ol>
            </nav>

            <div class="position-relative flex-shrink-0">
                <button type="button" 
                        class="btn-product-share" 
                        id="shareProductBtn" 
                        onclick="toggleShareDropdown(event)"
                        title="শেয়ার করুন"
                        aria-label="শেয়ার করুন">
                    <i class="fa-solid fa-share-nodes text-primary"></i>
                    <span class="d-none d-md-inline">শেয়ার</span>
                </button>

                
                <div class="share-social-popup position-absolute end-0 top-100 mt-2" id="shareDropdownMenu" style="display: none;">
                    <div class="d-flex align-items-center justify-content-between pb-2.5 mb-3 border-bottom">
                        <div class="d-flex align-items-center gap-1.5">
                            <i class="fa-solid fa-share-nodes text-primary" style="font-size: 12px;"></i>
                            <span class="fw-bold text-dark font-heading" style="font-size: 13px;">সোশ্যাল মিডিয়ায় শেয়ার করুন</span>
                        </div>
                        <button type="button" class="btn-close" style="font-size: 10px;" onclick="toggleShareDropdown(event)" aria-label="Close"></button>
                    </div>

                    
                    <div class="row g-2 text-center mb-3">
                        <div class="col-3">
                            <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(url()->current()) }}" target="_blank" class="share-social-btn" rel="noopener">
                                <div class="share-social-icon share-icon-fb">
                                    <i class="fa-brands fa-facebook-f"></i>
                                </div>
                                <span class="share-social-label">ফেসবুক</span>
                            </a>
                        </div>
                        <div class="col-3">
                            <a href="https://api.whatsapp.com/send?text={{ urlencode($product->title . ' ' . url()->current()) }}" target="_blank" class="share-social-btn" rel="noopener">
                                <div class="share-social-icon share-icon-wa">
                                    <i class="fa-brands fa-whatsapp"></i>
                                </div>
                                <span class="share-social-label">হোয়াটসঅ্যাপ</span>
                            </a>
                        </div>
                        <div class="col-3">
                            <a href="https://twitter.com/intent/tweet?text={{ urlencode($product->title) }}&url={{ urlencode(url()->current()) }}" target="_blank" class="share-social-btn" rel="noopener">
                                <div class="share-social-icon share-icon-x">
                                    <i class="fa-brands fa-x-twitter"></i>
                                </div>
                                <span class="share-social-label">টুইটার / X</span>
                            </a>
                        </div>
                        <div class="col-3">
                            <a href="https://t.me/share/url?url={{ urlencode(url()->current()) }}&text={{ urlencode($product->title) }}" target="_blank" class="share-social-btn" rel="noopener">
                                <div class="share-social-icon share-icon-tg">
                                    <i class="fa-brands fa-telegram"></i>
                                </div>
                                <span class="share-social-label">টেলিগ্রাম</span>
                            </a>
                        </div>
                    </div>

                    
                    <div class="pt-2 border-top">
                        <div class="share-copy-input-group">
                            <i class="fa-solid fa-link text-muted" style="font-size: 11px;"></i>
                            <input type="text" class="form-control form-control-sm border-0 bg-transparent p-0 text-muted font-mono" value="{{ url()->current() }}" readonly style="font-size: 11px;" id="productShareUrlInput">
                            <button type="button" class="btn btn-dark btn-sm rounded-2 px-2.5 py-1 font-heading fw-bold d-flex align-items-center gap-1 flex-shrink-0" onclick="copyProductLink(event)" style="font-size: 11.5px;">
                                <i class="fa-regular fa-copy"></i>
                                <span id="copyLinkBtnText">কপি</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        
        <div class="product-master-box p-2.5 p-sm-3 p-md-4 p-xl-4 mb-4">
            <div class="row g-3 g-md-4 items-start">
                
                
                <div class="col-12 col-md-5 col-lg-4 col-xl-4">
                    <div class="p-gallery-sticky">
                        <div class="p-gallery-box mb-2.5" id="zoomContainer">
                            @if($discountPercent > 0)
                                <span class="position-absolute top-0 start-0 m-3 badge bg-danger text-white px-3 py-1.5 rounded-pill fw-bold shadow-sm font-heading" style="z-index: 15; pointer-events: none; font-size: 0.8rem;">
                                    -{{ $discountPercent }}% ছাড়
                                </span>
                            @elseif($product->tag)
                                <span class="position-absolute top-0 start-0 m-3 badge bg-primary text-white px-3 py-1.5 rounded-pill fw-bold shadow-sm font-heading" style="z-index: 15; pointer-events: none; font-size: 0.75rem;">
                                    {{ $product->tag }}
                                </span>
                            @endif

                            <button type="button" class="btn-gallery-action position-absolute top-0 end-0 m-3" onclick="openFullscreenLightbox()" aria-label="Fullscreen Zoom" title="বড় করে জুম দেখুন">
                                <i class="fa-solid fa-expand"></i>
                            </button>

                            <div class="position-absolute bottom-0 start-0 m-3 badge bg-dark bg-opacity-75 text-white px-2.5 py-1 rounded-pill small d-flex align-items-center gap-1 font-heading" style="z-index: 15; pointer-events: none; font-size: 0.7rem;">
                                <i class="fa-solid fa-magnifying-glass-plus"></i> <span class="d-none d-md-inline">জুম করতে হোভার করুন</span><span class="d-md-none">টাচ করে জুম করুন</span>
                            </div>

                            <div class="position-absolute bottom-0 end-0 m-3 badge bg-dark bg-opacity-75 text-white px-2.5 py-1 rounded-pill small d-flex align-items-center gap-1 font-heading" style="z-index: 15; pointer-events: none; font-size: 0.75rem;">
                                <i class="fa-solid fa-circle-check text-success"></i> {{ $product->stock_qty > 0 ? 'ইন স্টক' : 'স্টক আউট' }}
                            </div>

                            @if(!empty($product->video_url) && youtube_embed_url($product->video_url))
                                <button type="button" 
                                        class="btn btn-light btn-sm rounded-pill position-absolute px-3 py-1 shadow d-flex align-items-center gap-1.5 font-heading text-dark border" 
                                        onclick="switchProductDetailTab('tab-video', document.getElementById('tab-video-btn')); document.getElementById('productDetailTabs').scrollIntoView({behavior: 'smooth', block: 'start'});" 
                                        style="bottom: 44px; left: 50%; transform: translateX(-50%); z-index: 15; font-size: 0.75rem; font-weight: 700;">
                                    <i class="fa-brands fa-youtube text-danger fs-6"></i>
                                    <span>ভিডিও রিভিউ দেখুন</span>
                                </button>
                            @endif

                            
                            <div class="swiper productMainSwiper">
                                <div class="swiper-wrapper">
                                    @foreach($galleryList as $gIdx => $gImg)
                                        <div class="swiper-slide">
                                            <div class="zoom-slide-container"
                                                 onmousemove="handleDesktopZoomMove(event, this)"
                                                 onmouseenter="handleDesktopZoomEnter(this)"
                                                 onmouseleave="handleDesktopZoomLeave(this)"
                                                 ontouchstart="handleTouchStart(event)"
                                                 ontouchmove="handleTouchMove(event)"
                                                 ontouchend="handleTouchEnd(event)">
                                                <img src="{{ $gImg ?: $brandFallbackSvg }}" 
                                                     alt="{{ $product->title }} - Zippy BD {{ $gIdx + 1 }}" 
                                                     class="main-slider-img"
                                                     onerror="this.onerror=null;this.src='{{ $brandFallbackSvg }}';">
                                            </div>
                                        </div>
                                    @endforeach
                                </div>

                                @if(count($galleryList) > 1)
                                    <div class="swiper-button-prev main-swiper-arrow" aria-label="Previous image">
                                        <i class="fa-solid fa-chevron-left"></i>
                                    </div>
                                    <div class="swiper-button-next main-swiper-arrow" aria-label="Next image">
                                        <i class="fa-solid fa-chevron-right"></i>
                                    </div>
                                @endif
                            </div>
                        </div>

                        
                        @if(count($galleryList) > 1 || (!empty($product->video_url) && youtube_embed_url($product->video_url)))
                            <div class="swiper productThumbSwiper">
                                <div class="swiper-wrapper">
                                    @foreach($galleryList as $idx => $img)
                                        <div class="swiper-slide p-thumb-slide">
                                            <div class="thumb-inner">
                                                <img src="{{ $img ?: $brandFallbackSvg }}" alt="{{ $product->title }} - Zippy BD থাম্বনেইল {{ $idx + 1 }}" onerror="this.onerror=null;this.src='{{ $brandFallbackSvg }}';">
                                            </div>
                                        </div>
                                    @endforeach
                                    @if(!empty($product->video_url) && youtube_embed_url($product->video_url))
                                        @php $ytThumbId = youtube_video_id($product->video_url); @endphp
                                        <div class="swiper-slide p-thumb-slide" onclick="switchProductDetailTab('tab-video', document.getElementById('tab-video-btn')); document.getElementById('productDetailTabs').scrollIntoView({behavior: 'smooth', block: 'start'});" title="ভিডিও রিভিউ দেখুন">
                                            <div class="thumb-inner position-relative d-flex align-items-center justify-content-center bg-dark">
                                                <img src="{{ $ytThumbId ? 'https://img.youtube.com/vi/' . $ytThumbId . '/mqdefault.jpg' : $brandFallbackSvg }}" alt="{{ $product->title }} - Zippy BD ভিডিও রিভিউ" onerror="this.onerror=null;this.src='{{ $brandFallbackSvg }}';">
                                                <div class="position-absolute d-flex align-items-center justify-content-center text-white bg-danger rounded-circle shadow" style="width: 24px; height: 24px; font-size: 0.65rem;">
                                                    <i class="fa-solid fa-play"></i>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                
                <div class="col-12 col-md-7 col-lg-5 col-xl-5">
                    <div class="d-flex flex-column h-100">
                        <div class="d-flex align-items-center gap-2 mb-2 flex-wrap">
                            <a href="{{ route('category.show', $product->cat_slug) }}" class="badge bg-light text-dark border px-2.5 py-1 rounded-pill fw-bold text-decoration-none font-heading" style="font-size: 0.74rem;">
                                {{ $product->cat_name_bn ?? $product->cat_name_en }}
                            </a>
                            <span class="text-muted small ms-auto font-mono">SKU: <strong class="text-dark">{{ $product->sku ?: 'ZB-'.$product->id }}</strong></span>
                        </div>

                        <h1 class="font-heading fw-bold fs-4 text-dark mb-2 lh-sm product-title-heading">{{ $product->title }}</h1>

                        <div class="d-flex align-items-center gap-2 mb-3 flex-wrap">
                            @if($product->reviews_count > 0 && $product->rating > 0)
                                <div class="text-warning small d-flex align-items-center gap-0.5">
                                    @php $rStars = (int) round($product->rating); @endphp
                                    @for($i = 1; $i <= 5; $i++)
                                        <i class="fa-solid fa-star {{ $i <= $rStars ? 'text-warning' : 'text-muted text-opacity-25' }}"></i>
                                    @endfor
                                </div>
                                <span class="fw-bold text-dark font-heading">{{ number_format($product->rating, 1) }}</span>
                                <span class="text-muted small">({{ $product->reviews_count }} ভেরিফাইড রিভিউ)</span>
                                <span class="text-muted">•</span>
                            @else
                                <span class="text-muted small d-inline-flex align-items-center gap-1">
                                    <i class="fa-regular fa-star text-warning"></i> এখনো কোনো রিভিউ নেই
                                </span>
                                <span class="text-muted">•</span>
                            @endif
                            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-0.5" style="font-size: 0.72rem; font-weight: 600;">
                                <i class="fa-solid fa-bolt me-1"></i> {{ $totalSoldUnits > 0 ? number_format($totalSoldUnits) . '+ সফল অর্ডার' : '১০০% জেনুইন প্রোডাক্ট' }}
                            </span>
                            @if($product->stock_qty > 0)
                                @if($product->stock_qty <= 5)
                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-2.5 py-0.5 fw-semibold font-mono" style="font-size: 0.72rem;">
                                        <i class="fa-solid fa-fire me-1"></i> স্টক: {{ $product->stock_qty }}টি অবশিষ্ট
                                    </span>
                                @else
                                    <span class="badge bg-info-subtle text-primary border border-info-subtle rounded-pill px-2.5 py-0.5 fw-semibold font-mono" style="font-size: 0.72rem;">
                                        <i class="fa-solid fa-boxes-stacked me-1"></i> ইন স্টক: {{ $product->stock_qty }}টি
                                    </span>
                                @endif
                            @else
                                <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill px-2.5 py-0.5 fw-semibold" style="font-size: 0.72rem;">
                                    আউট অফ স্টক
                                </span>
                            @endif
                            <x-product-live-viewers />
                        </div>

                        <div class="product-price-box p-3 rounded-3 bg-light border mb-2 d-flex align-items-center justify-content-between flex-wrap gap-2">
                            <div>
                                <span class="text-muted small d-block" style="font-size: 0.75rem;">প্রতি পিসের মূল্য:</span>
                                <div class="d-flex align-items-baseline gap-2">
                                    <span class="fs-4 fw-bold text-danger font-heading product-current-price">৳ {{ number_format($product->price, 0) }}</span>
                                    @if($product->old_price && $product->old_price > $product->price)
                                        <span class="text-muted text-decoration-line-through small font-mono">৳ {{ number_format($product->old_price, 0) }}</span>
                                    @endif
                                </div>
                            </div>
                            @if($discountPercent > 0)
                                <span class="badge bg-danger text-white rounded-pill px-2.5 py-1 fw-bold font-heading" style="font-size: 0.75rem;">
                                    -{{ $discountPercent }}% ছাড় (৳ {{ number_format($product->old_price - $product->price, 0) }} সাশ্রয়)
                                </span>
                            @endif
                        </div>

                        <x-product-scarcity-bar :stock="$product->stock_qty" />
                        <x-dispatch-cutoff-timer />

                        <div class="mb-3.5 pb-3 border-bottom">
                            <ul class="list-unstyled d-flex flex-column gap-1.5 m-0">
                                @foreach($shortDescLines as $line)
                                    <li class="d-flex align-items-start gap-2 text-secondary small">
                                        <i class="fa-solid fa-circle-check text-success mt-1" style="font-size: 0.8rem;"></i>
                                        <span>{{ $line }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>

                        @if(count($variantList) > 0)
                            <div class="mb-3 pb-3 pt-1 border-bottom" style="padding-top: 5px !important;">
                                <label class="small fw-bold text-dark font-heading mb-2 d-flex align-items-center justify-content-between">
                                    <span><i class="fa-solid fa-layer-group text-primary me-1"></i> কালার / ভ্যারিয়েন্ট সিলেক্ট করুন:</span>
                                    <span class="badge bg-light text-secondary border" style="font-size: 0.68rem;">একসাথে একাধিক সিলেক্ট সম্ভব</span>
                                </label>
                                <div class="d-flex flex-column gap-2" id="bulkVariantsWrap">
                                    @foreach($variantList as $vIdx => $vItem)
                                        @php
                                            $vName = is_array($vItem) ? ($vItem['name'] ?? '') : (is_object($vItem) ? ($vItem->name ?? '') : $vItem);
                                            $vPrice = is_array($vItem) ? ($vItem['price'] ?? $product->price) : (is_object($vItem) ? ($vItem->price ?? $product->price) : $product->price);
                                            $vImg = is_array($vItem) ? ($vItem['image'] ?? ($galleryList[0] ?? $product->main_image)) : ($galleryList[0] ?? $product->main_image);
                                            $vStock = is_array($vItem) ? ($vItem['stock'] ?? $product->stock_qty) : $product->stock_qty;
                                            $rowKey = md5($vName);
                                            $initQty = ($vIdx === 0 && $vStock > 0) ? 1 : 0;
                                        @endphp
                                        <div class="bulk-variant-item d-flex align-items-center justify-content-between gap-2 {{ $initQty > 0 ? 'item-active' : '' }}" 
                                             id="bulkRow_{{ $rowKey }}"
                                             data-variant-name="{{ $vName }}"
                                             data-variant-price="{{ $vPrice }}"
                                             data-variant-stock="{{ $vStock }}"
                                             data-variant-img="{{ $vImg }}">
                                             <div class="d-flex align-items-center gap-2.5">
                                                 <div class="bulk-variant-thumb" onclick="switchMainDetailImage('{{ $vImg }}', null)">
                                                      <img src="{{ $vImg ?: $brandFallbackSvg }}" alt="{{ $product->title }} - {{ $vName }}" onerror="this.onerror=null;this.src='{{ $brandFallbackSvg }}';">
                                                 </div>
                                                 <div>
                                                     <span class="fw-bold text-dark small d-block font-heading">{{ $vName }}</span>
                                                     <span class="text-danger fw-bold font-mono" style="font-size: 0.82rem;">৳ {{ number_format($vPrice, 0) }}</span>
                                                 </div>
                                             </div>
                                            <div class="d-flex align-items-center">
                                                <div class="bulk-qty-box">
                                                    <button type="button" class="bulk-qty-btn" onclick="adjustBulkVariantQty('{{ addslashes($vName) }}', -1)" aria-label="Decrease">
                                                        <i class="fa-solid fa-minus" style="font-size: 0.7rem;"></i>
                                                    </button>
                                                    <span class="bulk-qty-val" id="bulkQtyVal_{{ $rowKey }}" data-max="{{ $vStock }}">{{ $initQty }}</span>
                                                    <button type="button" class="bulk-qty-btn" onclick="adjustBulkVariantQty('{{ addslashes($vName) }}', 1)" aria-label="Increase">
                                                        <i class="fa-solid fa-plus" style="font-size: 0.7rem;"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @else
                            <div class="p-3 border rounded-3 bg-white mb-3 d-flex align-items-center justify-content-between">
                                <div>
                                    <span class="fw-bold text-dark small font-heading d-block">পণ্যের পরিমাণ:</span>
                                    <small class="text-muted" style="font-size: 0.7rem;">সর্বোচ্চ স্টক: {{ $product->stock_qty }}টি</small>
                                </div>
                                <div class="bulk-qty-box">
                                    <button type="button" class="bulk-qty-btn" onclick="adjustSingleQty(-1)" aria-label="Decrease">
                                        <i class="fa-solid fa-minus" style="font-size: 0.7rem;"></i>
                                    </button>
                                    <span class="bulk-qty-val" id="singleQtyDisplay" data-max="{{ $product->stock_qty }}">1</span>
                                    <button type="button" class="bulk-qty-btn" onclick="adjustSingleQty(1)" aria-label="Increase">
                                        <i class="fa-solid fa-plus" style="font-size: 0.7rem;"></i>
                                    </button>
                                </div>
                            </div>
                        @endif

                        
                        <div class="d-flex flex-wrap gap-2 mt-auto pt-2">
                            <div class="badge bg-light text-dark border px-2.5 py-1.5 rounded-2 small font-heading">
                                <i class="fa-solid fa-shield-check text-success me-1"></i> ১০০% অথেনটিক
                            </div>
                            <div class="badge bg-light text-dark border px-2.5 py-1.5 rounded-2 small font-heading">
                                <i class="fa-solid fa-hand-holding-dollar text-warning me-1"></i> ক্যাশ অন ডেলিভারি
                            </div>
                            <div class="badge bg-light text-dark border px-2.5 py-1.5 rounded-2 small font-heading">
                                <i class="fa-solid fa-rotate-left text-info me-1"></i> ৭ দিনের রিপ্লেসমেন্ট
                            </div>
                        </div>
                    </div>
                </div>

                
                <div class="col-12 col-md-12 col-lg-3 col-xl-3">
                    <div class="live-sidebar-summary">
                        
                        <div class="d-flex align-items-center justify-content-between pb-2 mb-2.5 border-bottom">
                            <div class="d-flex align-items-center gap-1.5">
                                <i class="fa-solid fa-receipt text-primary" style="font-size: 0.9rem;"></i>
                                <span class="summary-header-title">অর্ডার সারাংশ</span>
                            </div>
                            <span class="summary-header-badge" id="summaryTotalItemsBadge">১ টি আইটেম</span>
                        </div>

                        
                        <div class="mb-3">
                            <div class="d-flex align-items-center gap-1.5 text-secondary mb-2" style="font-size: 0.75rem; font-weight: 700; padding: 10px 0;">
                                <i class="fa-solid fa-truck-fast text-primary"></i>
                                <span>ডেলিভারি এরিয়া সিলেক্ট করুন:</span>
                            </div>
                            <div class="d-flex flex-column gap-2">
                                <div class="delivery-zone-card active-zone" id="zoneCard_dhaka" onclick="changeShippingZone({{ $shippingInside }}, 'dhaka')">
                                    <div class="d-flex align-items-center gap-2.5 min-w-0">
                                        <div class="custom-zone-radio"></div>
                                        <div class="zone-info-block">
                                            <span class="zone-info-title">ঢাকার ভেতরে</span>
                                            <span class="zone-info-sub">২৪ - ৪৮ ঘণ্টার মধ্যে</span>
                                        </div>
                                    </div>
                                    <span class="zone-price-pill {{ $shippingInside == 0 ? 'text-success bg-success-subtle border-success-subtle' : '' }}">{{ $shippingInside == 0 ? 'ফ্রি' : '৳ ' . number_format($shippingInside, 0) }}</span>
                                </div>

                                <div class="delivery-zone-card" id="zoneCard_outside" onclick="changeShippingZone({{ $shippingOutside }}, 'outside')">
                                    <div class="d-flex align-items-center gap-2.5 min-w-0">
                                        <div class="custom-zone-radio"></div>
                                        <div class="zone-info-block">
                                            <span class="zone-info-title">ঢাকার বাইরে</span>
                                            <span class="zone-info-sub">২ - ৩ কার্যদিবস</span>
                                        </div>
                                    </div>
                                    <span class="zone-price-pill {{ $shippingOutside == 0 ? 'text-success bg-success-subtle border-success-subtle' : '' }}">{{ $shippingOutside == 0 ? 'ফ্রি' : '৳ ' . number_format($shippingOutside, 0) }}</span>
                                </div>
                            </div>

                            <div class="delivery-est-banner mt-2" id="summaryDeliveryDate">
                                <i class="fa-regular fa-clock text-primary"></i>
                                <span>সম্ভাব্য ডেলিভারি: <strong class="text-dark">{{ now()->addDays(1)->translatedFormat('l, d F') }}</strong></span>
                            </div>
                        </div>

                        
                        <div class="mb-2.5">
                            <div class="text-muted mb-1.5" style="font-size: 0.72rem; font-weight: 600;">
                                সিলেক্টেড আইটেম তালিকা:
                            </div>
                            <div class="d-flex flex-column gap-1.5" id="summarySelectedBreakdown">
                            </div>
                        </div>

                        
                        <div class="pt-2 border-top mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-1.5 small text-secondary">
                                <span style="font-size: 0.78rem;">পণ্যের মোট মূল্য:</span>
                                <strong class="text-dark font-mono" id="summarySubtotalText" style="font-size: 0.84rem;">৳ {{ number_format($product->price, 0) }}</strong>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mb-1.5 small text-secondary">
                                <span style="font-size: 0.78rem;">ডেলিভারি চার্জ:</span>
                                <strong class="text-dark font-mono" id="summaryShippingText" style="font-size: 0.84rem;">{{ $shippingInside == 0 ? 'ফ্রি' : '৳ ' . number_format($shippingInside, 0) }}</strong>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mb-2 small text-secondary">
                                <span style="font-size: 0.78rem;">ক্যাশ অন ডেলিভারি:</span>
                                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill font-heading" style="font-size: 0.65rem;">১০০% ফ্রি সুবিধা</span>
                            </div>

                            
                            <div class="grand-total-highlight-box d-flex justify-content-between align-items-center mt-2">
                                <div>
                                    <span class="fw-bold text-dark font-heading d-block" style="font-size: 0.84rem; line-height: 1.1;">সর্বমোট পরিশোধযোগ্য:</span>
                                    <small class="text-muted" style="font-size: 0.66rem;">পণ্য হাতে পেয়ে মূল্য পরিশোধ</small>
                                </div>
                                <strong class="text-danger fs-5 font-heading" id="summaryGrandTotalText">৳ {{ number_format($product->price + $shippingInside, 0) }}</strong>
                            </div>
                        </div>

                        
                        <div class="d-none d-lg-flex flex-column gap-2">
                            <button type="button" class="btn btn-action-buynow d-flex align-items-center justify-content-center gap-2" onclick="executeBulkBuyNow({{ $product->id }})">
                                <i class="fa-solid fa-bolt"></i> <span>Buy Now (সরাসরি অর্ডার)</span>
                            </button>
                            <button type="button" class="btn btn-action-cart btn-cart-animated d-flex align-items-center justify-content-center gap-2" onclick="executeBulkAddToCart({{ $product->id }}, this)">
                                <span class="btn-cart-content d-flex align-items-center justify-content-center gap-2">
                                    <span class="cart-anim-bag-wrap"><i class="fa-duotone fa-solid fa-bag-shopping fa-jello cart-anim-bag"></i></span>
                                    <span class="cart-anim-text">কার্টে যোগ করুন</span>
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
                                        <svg class="cart-success-check" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M3.5 8.5L6.5 11.5L12.5 4.5" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                        <span class="cart-success-text">কার্টে যুক্ত হয়েছে</span>
                                    </span>
                                </span>
                            </button>
                            <a href="#" id="desktopWhatsAppLink" onclick="executeWhatsAppOrder(event)" target="_blank" class="btn btn-action-whatsapp d-flex align-items-center justify-content-center gap-2 text-decoration-none">
                                <i class="fa-brands fa-whatsapp fs-5"></i> <span>WhatsApp এ দ্রুত অর্ডার</span>
                            </a>
                        </div>

                        
                        <div class="d-flex align-items-center justify-content-center gap-1.5 text-muted mt-2.5 pt-2 border-top small" style="font-size: 0.7rem;">
                            <i class="fa-solid fa-shield-halved text-success"></i>
                            <span>১০০% নিরাপদ ও সুরক্ষিত চেকআউট গ্যারান্টি</span>
                        </div>
                    </div>
                </div>
            </div>


            <div class="mt-4 pt-3 border-top">
                <div class="product-tabs-scroll-wrap pb-2 mb-4">
                    <div class="d-flex align-items-center gap-2" id="productDetailTabs">
                        <button class="product-tab-btn active" id="tab-overview-btn" onclick="switchProductDetailTab('tab-overview', this)" type="button">
                            <i class="fa-solid fa-circle-info me-1"></i> বিবরণ ও বৈশিষ্ট্য
                        </button>
                        <button class="product-tab-btn" id="tab-specs-btn" onclick="switchProductDetailTab('tab-specs', this)" type="button">
                            <i class="fa-solid fa-list-check me-1"></i> স্পেসিফিকেশন
                        </button>
                        @if(!empty($product->video_url) && youtube_embed_url($product->video_url))
                            <button class="product-tab-btn" id="tab-video-btn" onclick="switchProductDetailTab('tab-video', this)" type="button">
                                <i class="fa-brands fa-youtube text-danger me-1"></i> ভিডিও রিভিউ
                            </button>
                        @endif
                        <button class="product-tab-btn" id="tab-policy-btn" onclick="switchProductDetailTab('tab-policy', this)" type="button">
                            <i class="fa-solid fa-truck-ramp-box me-1"></i> ডেলিভারি ও রিটার্ন
                        </button>
                        <button class="product-tab-btn" id="tab-reviews-btn" onclick="switchProductDetailTab('tab-reviews', this)" type="button">
                            <i class="fa-solid fa-star me-1"></i> কাস্টমার রিভিউ ({{ $product->reviews_count }})
                        </button>
                    </div>
                </div>

                <div class="tab-content" id="productDetailTabsContent">
                    
                    <div class="tab-pane fade show active" id="tab-overview" role="tabpanel">
                        <div class="text-secondary lh-lg fs-6">
                            <h5 class="fw-bold text-dark font-heading mb-3">পণ্যের বিস্তারিত বিবরণ:</h5>
                            <div class="product-description-content mb-4">{!! $product->description !!}</div>
                            
                            <h6 class="fw-bold text-dark font-heading mt-4 mb-2">কেন {{ $siteName }} থেকে কিনবেন?</h6>
                            <ul class="list-unstyled d-flex flex-column gap-2 small">
                                <li><i class="fa-solid fa-check text-success me-2"></i> প্রতিটি প্রোডাক্ট ইন-হাউস কঠোর কোয়ালিটি চেকিং সম্পন্ন।</li>
                                <li><i class="fa-solid fa-check text-success me-2"></i> সারা দেশে দ্রুততম হোম ডেলিভারি ও সুরক্ষিত প্যাকিং।</li>
                                <li><i class="fa-solid fa-check text-success me-2"></i> যেকোনো ত্রুটিতে ৭ দিনের ঝামেলাহীন রিপ্লেসমেন্ট সুবিধা।</li>
                            </ul>
                        </div>
                    </div>

                    
                    <div class="tab-pane fade d-none" id="tab-specs" role="tabpanel">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped align-middle mb-0">
                                <tbody>
                                    <tr>
                                        <th class="bg-light text-dark fw-bold" style="width: 30%;">প্রোডাক্ট নেম</th>
                                        <td>{{ $product->title }}</td>
                                    </tr>
                                    <tr>
                                        <th class="bg-light text-dark fw-bold">ক্যাটাগরি</th>
                                        <td>{{ $product->cat_name_bn ?? $product->cat_name_en }}</td>
                                    </tr>
                                    <tr>
                                        <th class="bg-light text-dark fw-bold">SKU কোড</th>
                                        <td>{{ $product->sku ?: 'ZB-'.$product->id }}</td>
                                    </tr>
                                    @if(count($specList) > 0)
                                        @foreach($specList as $key => $val)
                                            <tr>
                                                <th class="bg-light text-dark fw-bold">{{ $key }}</th>
                                                <td>{{ $val }}</td>
                                            </tr>
                                        @endforeach
                                    @else
                                        <tr>
                                            <th class="bg-light text-dark fw-bold">স্টক স্ট্যাটাস</th>
                                            <td>{{ $product->stock_qty > 0 ? 'ইন স্টক (' . $product->stock_qty . ' টি এভেইলেবল)' : 'স্টক আউট' }}</td>
                                        </tr>
                                        <tr>
                                            <th class="bg-light text-dark fw-bold">কোয়ালিটি গ্রেড</th>
                                            <td>১০০% অরিজিনাল ও ভেরিফাইড প্রিমিয়াম কোয়ালিটি</td>
                                        </tr>
                                        <tr>
                                            <th class="bg-light text-dark fw-bold">ওয়ারেন্টি</th>
                                            <td>৭ দিনের রিপ্লেসমেন্ট ও অফিসিয়াল সার্ভিস সাপোর্ট</td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>

                    @if(!empty($product->video_url) && youtube_embed_url($product->video_url))
                        <div class="tab-pane fade d-none" id="tab-video" role="tabpanel">
                            <div class="row g-4 items-start">
                                <div class="col-12 col-lg-8">
                                    <div class="border rounded-4 overflow-hidden shadow-sm bg-black position-relative" style="aspect-ratio: 16 / 9;">
                                        <iframe src="{{ youtube_embed_url($product->video_url) }}" 
                                                title="{{ $product->title }} Video Review" 
                                                class="w-100 h-100 border-0" 
                                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" 
                                                allowfullscreen>
                                        </iframe>
                                    </div>
                                </div>
                                <div class="col-12 col-lg-4">
                                    <div class="p-4 border rounded-4 bg-light h-100 d-flex flex-column justify-content-between">
                                        <div>
                                            <div class="d-flex align-items-center gap-2 mb-3">
                                                <span class="badge bg-danger text-white rounded-pill px-3 py-1 font-heading d-inline-flex align-items-center gap-1">
                                                    <i class="fa-brands fa-youtube"></i> ভিডিও রিভিউ
                                                </span>
                                                <span class="text-muted small">HD Player</span>
                                            </div>
                                            <h5 class="fw-bold text-dark font-heading mb-2">{{ $product->title }}</h5>
                                            <p class="text-secondary small mb-3">
                                                প্রোডাক্টটি অর্ডার করার আগে রিয়েল আনবক্সিং, কোয়ালিটি এবং রিভিউ সরাসরি ভিডিওতে দেখে নিন।
                                            </p>
                                        </div>
                                        <div class="pt-3 border-top mt-auto">
                                            <a href="{{ $product->video_url }}" target="_blank" rel="noopener noreferrer" class="btn btn-danger w-100 rounded-pill py-2.5 fw-bold font-heading d-flex align-items-center justify-content-center gap-2 shadow-sm">
                                                <i class="fa-brands fa-youtube fs-5"></i> <span>YouTube-এ দেখুন ও সাবস্ক্রাইব করুন</span>
                                            </a>
                                            <small class="text-muted text-center d-block mt-2" style="font-size: 0.72rem;">সরাসরি YouTube অ্যাপে ওপেন হবে</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    
                    <div class="tab-pane fade d-none" id="tab-policy" role="tabpanel">
                        <div class="row g-3 g-md-4">
                            <div class="col-md-6">
                                <div class="border rounded-3 bg-light h-100" style="padding: 20px !important;">
                                    <h6 class="fw-bold text-dark font-heading"><i class="fa-solid fa-truck text-primary me-2"></i>ডেলিভারি পলিসি</h6>
                                    <ul class="small text-secondary mt-2.5 d-flex flex-column gap-2 mb-0">
                                        <li><strong>ঢাকা সিটি:</strong> ডেলিভারি চার্জ ৳{{ number_format($shippingInside, 0) }} টাকা (২৪ থেকে ৪৮ ঘণ্টার মধ্যে ডেলিভারি)।</li>
                                        <li><strong>ঢাকার বাইরে:</strong> ডেলিভারি চার্জ ৳{{ number_format($shippingOutside, 0) }} টাকা (২ থেকে ৩ কার্যদিবস)।</li>
                                        <li>ক্যাশ অন ডেলিভারি সুবিধা রয়েছে। পণ্য দেখে রিসিভ করতে পারবেন।</li>
                                        <li>জরুরি প্রয়োজনে যোগাযোগ করুন: <strong>{{ $supportPhone }}</strong></li>
                                    </ul>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="border rounded-3 bg-light h-100" style="padding: 20px !important;">
                                    <h6 class="fw-bold text-dark font-heading"><i class="fa-solid fa-rotate-left text-success me-2"></i>রিটার্ন ও রিপ্লেসমেন্ট পলিসি</h6>
                                    <ul class="small text-secondary mt-2.5 d-flex flex-column gap-2 mb-0">
                                        <li>পণ্য হাতে পাওয়ার পর কোনো সমস্যা থাকলে ৭ দিনের মধ্যে আমাদের সাথে যোগাযোগ করুন।</li>
                                        <li>রিপ্লেসমেন্টের ক্ষেত্রে বক্স ও অ্যাক্সেসরিজ অক্ষত থাকতে হবে।</li>
                                        <li>আমাদের হেল্পলাইন ({{ $supportPhone }}) বা WhatsApp ({{ $whatsappNumber }})-এ মেসেজ দিয়ে সহজেই রিটার্ন প্রসেস সম্পন্ন করা যায়।</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    
                    <div class="tab-pane fade d-none" id="tab-reviews" role="tabpanel">
                        <div class="row g-4 items-start">
                            <div class="col-12 col-md-4 text-center p-4 border rounded-3 bg-light">
                                @if($product->reviews_count > 0 && $product->rating > 0)
                                    <h2 class="fw-bold text-dark display-4 font-heading mb-0">{{ number_format($product->rating, 1) }}</h2>
                                    <div class="text-warning my-2 fs-5">
                                        @php $ratingStars = (int) round($product->rating); @endphp
                                        @for($s = 1; $s <= 5; $s++)
                                            <i class="fa-solid fa-star {{ $s <= $ratingStars ? 'text-warning' : 'text-muted text-opacity-25' }}"></i>
                                        @endfor
                                    </div>
                                    <div class="text-muted small">মোট {{ $product->reviews_count }} জন ক্রেতার রেটিং ও রিভিউ</div>

                                    @if(isset($ratingBreakdown) && is_array($ratingBreakdown))
                                        <div class="mt-3 text-start pt-3 border-top">
                                            @for($star = 5; $star >= 1; $star--)
                                                @php
                                                    $cnt = $ratingBreakdown[$star] ?? 0;
                                                    $pct = $product->reviews_count > 0 ? round(($cnt / $product->reviews_count) * 100) : 0;
                                                @endphp
                                                <div class="d-flex align-items-center gap-2 mb-1.5" style="font-size: 0.78rem;">
                                                    <span class="font-mono text-muted" style="width: 20px;">{{ $star }}★</span>
                                                    <div class="progress flex-grow-1" style="height: 6px; background-color: #e2e8f0;">
                                                        <div class="progress-bar bg-warning" role="progressbar" style="width: {{ $pct }}%" aria-valuenow="{{ $pct }}" aria-valuemin="0" aria-valuemax="100"></div>
                                                    </div>
                                                    <span class="text-muted font-mono" style="width: 28px; text-align: right;">{{ $cnt }}</span>
                                                </div>
                                            @endfor
                                        </div>
                                    @endif
                                @else
                                    <h2 class="fw-bold text-muted display-4 font-heading mb-0">-.-</h2>
                                    <div class="text-muted text-opacity-50 my-2 fs-5">
                                        @for($s = 1; $s <= 5; $s++)
                                            <i class="fa-regular fa-star"></i>
                                        @endfor
                                    </div>
                                    <div class="text-muted small">এখনো কোনো রিভিউ দেওয়া হয়নি</div>
                                @endif
                                <button type="button" class="btn btn-primary btn-sm rounded-pill px-3 py-2 fw-bold font-heading mt-3 w-100 shadow-sm" data-bs-toggle="modal" data-bs-target="#writeReviewModal">
                                    <i class="fa-solid fa-pen-to-square me-1"></i> আপনার রিভিউ লিখুন
                                </button>
                            </div>
                            <div class="col-12 col-md-8" id="productReviewsContainer">
                                @if(isset($productReviews) && count($productReviews) > 0)
                                    <div class="d-flex flex-column gap-3">
                                        @foreach($productReviews as $rev)
                                            <div class="border rounded-3 p-3 bg-white shadow-2xs">
                                                <div class="d-flex justify-content-between align-items-center mb-1">
                                                    <span class="fw-bold text-dark"><i class="fa-solid fa-circle-user text-primary me-1"></i> {{ $rev->customer_name }}</span>
                                                    <span class="text-muted small">{{ \Carbon\Carbon::parse($rev->created_at)->diffForHumans() }}</span>
                                                </div>
                                                <div class="text-warning small mb-2">
                                                    @for($st = 1; $st <= 5; $st++)
                                                        <i class="fa-solid fa-star {{ $st <= $rev->rating ? 'text-warning' : 'text-muted text-opacity-25' }}"></i>
                                                    @endfor
                                                    <span class="badge bg-success-subtle text-success ms-2 small">ভেরিফাইড রিভিউ</span>
                                                </div>
                                                <p class="text-secondary small mb-2">{{ $rev->comment }}</p>
                                                @php
                                                    $revImages = [];
                                                    if (!empty($rev->images)) {
                                                        $decoded = is_string($rev->images) ? json_decode($rev->images, true) : $rev->images;
                                                        if (is_array($decoded)) {
                                                            $revImages = $decoded;
                                                        }
                                                    }
                                                    if (empty($revImages) && !empty($rev->photo)) {
                                                        $revImages = [$rev->photo];
                                                    }
                                                @endphp
                                                @if(!empty($revImages))
                                                    <div class="d-flex flex-wrap gap-2 mt-2">
                                                        @foreach($revImages as $imgSrc)
                                                            <a href="javascript:void(0)" onclick="openReviewPhotoModal('{{ asset($imgSrc) }}')">
                                                                <img src="{{ asset($imgSrc) }}" class="rounded-2 border shadow-2xs" style="width: 72px; height: 72px; object-fit: cover;" alt="{{ $product->title }} রিভিউ ছবি">
                                                            </a>
                                                        @endforeach
                                                    </div>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="border rounded-3 p-4 bg-white text-center">
                                        <i class="fa-regular fa-comment-dots text-muted fs-1 mb-2"></i>
                                        <h6 class="fw-bold text-dark font-heading">এখনো কোনো রিভিউ দেওয়া হয়নি</h6>
                                        <p class="text-muted small mb-0">{{ $siteName }}-এ এই প্রোডাক্টটি অর্ডার করে প্রথম রিভিউটি লিখুন।</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        
        @if(count($relatedList) > 0)
            <section class="mt-5 pt-3">
                <div class="d-flex align-items-center justify-content-between mb-4 pb-2 border-bottom">
                    <div class="d-flex align-items-center gap-2">
                        <div class="bg-danger rounded-pill" style="width: 4px; height: 22px;"></div>
                        <h3 class="font-heading fs-4 fw-bold text-dark mb-0">সম্পর্কিত অন্যান্য {{ $product->cat_name_bn ?: 'পণ্যসমূহ' }}</h3>
                    </div>
                    @if(!empty($product->cat_slug))
                        <a href="{{ route('category.show', $product->cat_slug) }}" class="btn btn-outline-dark btn-sm rounded-pill px-3 fw-bold font-heading" style="font-size: 13px;">
                            সকল দেখুন <i class="fa-solid fa-arrow-right ms-1"></i>
                        </a>
                    @endif
                </div>
                <div class="row zb-products-row g-2 g-md-3">
                    @foreach($relatedList as $relProduct)
                        <div class="col">
                            @include('frontend.inc.product-card', ['product' => $relProduct])
                        </div>
                    @endforeach
                </div>
            </section>
        @endif
    </div>
</div>


<div class="mobile-floating-action-sheet d-lg-none">
    <div class="d-flex align-items-center justify-content-between gap-2">
        <button type="button" class="btn-mobile-back" onclick="if(window.history.length > 1) { window.history.back(); } else { window.location.href='{{ route('home') }}'; }" aria-label="পিছনে যান" title="Back">
            <i class="fa-solid fa-arrow-left"></i>
        </button>
        <div class="mobile-sheet-price-col pe-1">
            <span class="text-muted d-block" style="font-size: 0.68rem; line-height: 1;">সর্বমোট:</span>
            <span class="fw-bold text-danger font-heading fs-5 text-nowrap" id="mobileGrandTotalText">৳ {{ number_format($product->price + $shippingInside, 0) }}</span>
        </div>
        <div class="d-flex gap-2 flex-grow-1 justify-content-end align-items-center">
            <button type="button" class="btn btn-action-cart btn-cart-animated btn-sm rounded-pill px-3 py-2 fw-bold d-flex align-items-center gap-1.5" onclick="executeBulkAddToCart({{ $product->id }}, this)">
                <span class="btn-cart-content d-flex align-items-center justify-content-center gap-1.5">
                    <span class="cart-anim-bag-wrap"><i class="fa-duotone fa-solid fa-bag-shopping fa-jello cart-anim-bag"></i></span>
                    <span class="cart-anim-text">কার্ট</span>
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
                        <svg class="cart-success-check" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M3.5 8.5L6.5 11.5L12.5 4.5" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        <span class="cart-success-text">কার্টে যুক্ত হয়েছে</span>
                    </span>
                </span>
            </button>
            <button type="button" class="btn btn-action-buynow btn-sm rounded-pill px-3.5 py-2 fw-bold d-flex align-items-center gap-1.5" onclick="executeBulkBuyNow({{ $product->id }})">
                <i class="fa-solid fa-bolt"></i> <span>Buy Now</span>
            </button>
        </div>
    </div>
</div>

<div class="modal fade" id="writeReviewModal" tabindex="-1" aria-labelledby="writeReviewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-bottom pb-3">
                <h5 class="modal-title fw-bold font-heading" id="writeReviewModalLabel">
                    <i class="fa-solid fa-star text-warning me-1"></i> আপনার রিভিউ লিখুন
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="reviewSubmitForm" enctype="multipart/form-data" onsubmit="handleReviewSubmit(event)">
                <div class="modal-body p-4">
                    <div class="mb-3 text-center">
                        <label class="form-label small fw-bold text-dark d-block">রেটিং নির্বাচন করুন:</label>
                        <div class="star-rating-selector d-inline-flex gap-2 fs-3 text-warning" style="cursor: pointer;" id="reviewStarSelector">
                            <i class="fa-solid fa-star star-opt" data-rating="1" onclick="setReviewRating(1)"></i>
                            <i class="fa-solid fa-star star-opt" data-rating="2" onclick="setReviewRating(2)"></i>
                            <i class="fa-solid fa-star star-opt" data-rating="3" onclick="setReviewRating(3)"></i>
                            <i class="fa-solid fa-star star-opt" data-rating="4" onclick="setReviewRating(4)"></i>
                            <i class="fa-solid fa-star star-opt" data-rating="5" onclick="setReviewRating(5)"></i>
                        </div>
                        <input type="hidden" name="rating" id="reviewRatingInput" value="5">
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-dark">আপনার নাম <span class="text-danger">*</span></label>
                        <input type="text" name="customer_name" class="form-control rounded-3" placeholder="যেমন: তানভীর আহমেদ" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-dark">মোবাইল নাম্বার (ঐচ্ছিক)</label>
                        <input type="text" name="customer_phone" class="form-control rounded-3" placeholder="017XXXXXXXX">
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-dark">আপনার মতামত ও অভিজ্ঞতা <span class="text-danger">*</span></label>
                        <textarea name="comment" class="form-control rounded-3" rows="3" placeholder="প্রোডাক্টের মান, ডেলিভারি এবং আপনার অনুভূতি..." required></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-dark">প্রোডাক্টের ছবি যুক্ত করুন (ঐচ্ছিক, সর্বোচ্চ ৩টি)</label>
                        <input type="file" name="photos[]" id="reviewPhotosInput" class="form-control rounded-3" accept="image/jpeg,image/png,image/webp,image/jpg" multiple onchange="handleReviewPhotoSelect(this)">
                        <small class="text-muted d-block mt-1" style="font-size: 0.72rem;">JPG, PNG, WEBP (সর্বোচ্চ ৩টি ছবি, প্রতিটি সর্বোচ্চ ৫ মেগাবাইট)</small>
                        <div id="reviewPhotoThumbnails" class="d-flex flex-wrap gap-2 mt-2"></div>
                    </div>

                    <div id="reviewAlertBox" class="alert d-none small mb-0 py-2"></div>
                </div>
                <div class="modal-footer border-top pt-2">
                    <button type="button" class="btn btn-light rounded-pill px-3" data-bs-dismiss="modal">বাতিল</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold font-heading" id="reviewSubmitBtn">
                        <span id="reviewSubmitBtnText">রিভিউ জমা দিন</span>
                        <span id="reviewSubmitSpinner" class="spinner-border spinner-border-sm d-none"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="reviewPhotoModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content rounded-4 border-0 shadow bg-dark text-center position-relative p-2">
            <button type="button" class="btn-close btn-close-white position-absolute top-0 end-0 m-3 z-3 shadow" data-bs-dismiss="modal" aria-label="Close"></button>
            <img id="reviewPhotoModalImg" src="" class="img-fluid rounded-3 mx-auto" style="max-height: 80vh; width: auto; object-fit: contain;" alt="রিভিউ ছবি">
        </div>
    </div>
</div>

<div id="imageLightboxModal" class="custom-lightbox-overlay" role="dialog" aria-modal="true">
    <button type="button" class="btn-lightbox-close" onclick="closeFullscreenLightbox()" aria-label="Close">
        <i class="fa-solid fa-xmark"></i>
    </button>

    <div class="lightbox-stage">
        <img src="{{ ($galleryList[0] ?? $product->main_image) ?: $brandFallbackSvg }}" 
             id="lightboxMainImg" 
             alt="{{ $product->title }} - Zippy BD" 
             onclick="toggleLightboxZoom(this)">
    </div>

    @if(count($galleryList) > 1)
        <div class="lightbox-thumb-strip" id="lightboxThumbs">
            @foreach($galleryList as $lIdx => $lImg)
                <div class="strip-thumb {{ $lIdx === 0 ? 'active' : '' }}" 
                     onclick="switchLightboxImage('{{ $lImg }}', this)">
                    <img src="{{ $lImg ?: $brandFallbackSvg }}" alt="{{ $product->title }} - Zippy BD থাম্বনেইল {{ $lIdx + 1 }}" onerror="this.onerror=null;this.src='{{ $brandFallbackSvg }}';">
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
(function() {
    const baseProductPrice = {{ (float) $product->price }};
    const productTitle = "{{ addslashes($product->title) }}";
    const productUrl = "{{ route('product.show', $product->slug) }}";

    window.singleProductQty = 1;
    window.currentShippingFee = {{ $shippingInside }};
    window.bulkState = {};
    window.mainSwiper = null;
    window.thumbSwiper = null;
    let touchZoomActive = false;
    let lastTapTime = 0;
    let desktopZoomRaf = null;
    let touchZoomRaf = null;

    window.handleDesktopZoomEnter = function(container) {
        const img = container ? container.querySelector('img') : null;
        if (img) img.style.transform = 'scale(2.2)';
    };

    window.handleDesktopZoomMove = function(e, container) {
        if (!container) return;
        const clientX = e.clientX;
        const clientY = e.clientY;
        if (desktopZoomRaf) cancelAnimationFrame(desktopZoomRaf);
        desktopZoomRaf = requestAnimationFrame(() => {
            const img = container.querySelector('img');
            if (!img) return;
            const rect = container.getBoundingClientRect();
            const x = clientX - rect.left;
            const y = clientY - rect.top;
            const xPercent = Math.max(0, Math.min(100, (x / rect.width) * 100));
            const yPercent = Math.max(0, Math.min(100, (y / rect.height) * 100));
            img.style.transformOrigin = `${xPercent}% ${yPercent}%`;
        });
    };

    window.handleDesktopZoomLeave = function(container) {
        if (desktopZoomRaf) {
            cancelAnimationFrame(desktopZoomRaf);
            desktopZoomRaf = null;
        }
        const img = container ? container.querySelector('img') : null;
        if (img) {
            img.style.transform = 'scale(1)';
            img.style.transformOrigin = 'center center';
        }
    };

    window.handleTouchStart = function(e) {
        const currentTime = new Date().getTime();
        const tapLength = currentTime - lastTapTime;

        if (tapLength < 300 && tapLength > 0) {
            e.preventDefault();
            const container = e.currentTarget;
            const img = container ? container.querySelector('img') : null;
            if (img) {
                if (touchZoomActive) {
                    img.style.transform = 'scale(1)';
                    touchZoomActive = false;
                } else {
                    const touch = e.touches[0];
                    const rect = container.getBoundingClientRect();
                    const xPercent = Math.max(0, Math.min(100, ((touch.clientX - rect.left) / rect.width) * 100));
                    const yPercent = Math.max(0, Math.min(100, ((touch.clientY - rect.top) / rect.height) * 100));
                    img.style.transformOrigin = `${xPercent}% ${yPercent}%`;
                    img.style.transform = 'scale(2.4)';
                    touchZoomActive = true;
                }
            }
        }
        lastTapTime = currentTime;
    };

    window.handleTouchMove = function(e) {
        if (!touchZoomActive || !e.touches || e.touches.length === 0) return;
        const container = e.currentTarget;
        const clientX = e.touches[0].clientX;
        const clientY = e.touches[0].clientY;
        if (touchZoomRaf) cancelAnimationFrame(touchZoomRaf);
        touchZoomRaf = requestAnimationFrame(() => {
            if (!container) return;
            const img = container.querySelector('img');
            if (!img) return;
            const rect = container.getBoundingClientRect();
            const x = clientX - rect.left;
            const y = clientY - rect.top;
            const xPercent = Math.max(0, Math.min(100, (x / rect.width) * 100));
            const yPercent = Math.max(0, Math.min(100, (y / rect.height) * 100));
            img.style.transformOrigin = `${xPercent}% ${yPercent}%`;
        });
    };

    window.handleTouchEnd = function() {
        if (touchZoomRaf) {
            cancelAnimationFrame(touchZoomRaf);
            touchZoomRaf = null;
        }
    };

    window.openFullscreenLightbox = function() {
        let currentImgSrc = '';
        if (window.mainSwiper && window.mainSwiper.slides && window.mainSwiper.slides[window.mainSwiper.activeIndex]) {
            const activeSlideImg = window.mainSwiper.slides[window.mainSwiper.activeIndex].querySelector('img');
            if (activeSlideImg) currentImgSrc = activeSlideImg.src;
        }
        if (!currentImgSrc) {
            const firstImg = document.querySelector('.main-slider-img');
            if (firstImg) currentImgSrc = firstImg.src;
        }

        const lightboxImg = document.getElementById('lightboxMainImg');
        if (lightboxImg && currentImgSrc) {
            lightboxImg.src = currentImgSrc;
            lightboxImg.style.transform = 'scale(1)';
            lightboxImg.style.cursor = 'zoom-in';

            document.querySelectorAll('#lightboxThumbs .strip-thumb').forEach(thumb => {
                const tImg = thumb.querySelector('img');
                if (tImg && (tImg.src === currentImgSrc || tImg.getAttribute('src') === currentImgSrc)) {
                    thumb.classList.add('active');
                } else {
                    thumb.classList.remove('active');
                }
            });
        }
        const modalEl = document.getElementById('imageLightboxModal');
        if (modalEl) {
            modalEl.classList.add('show');
            document.body.style.overflow = 'hidden';
        }
    };

    window.closeFullscreenLightbox = function() {
        const modalEl = document.getElementById('imageLightboxModal');
        if (modalEl) {
            modalEl.classList.remove('show');
            document.body.style.overflow = '';
        }
    };

    window.toggleLightboxZoom = function(imgEl) {
        if (imgEl.style.transform === 'scale(2)') {
            imgEl.style.transform = 'scale(1)';
            imgEl.style.cursor = 'zoom-in';
        } else {
            imgEl.style.transform = 'scale(2)';
            imgEl.style.cursor = 'zoom-out';
        }
    };

    window.switchProductDetailTab = function(tabId, btn) {
        document.querySelectorAll('#productDetailTabs button').forEach(b => {
            b.classList.remove('active');
        });
        if (btn) btn.classList.add('active');

        document.querySelectorAll('#productDetailTabsContent .tab-pane').forEach(p => {
            p.classList.remove('show', 'active');
            p.classList.add('d-none');
        });

        const targetPane = document.getElementById(tabId);
        if (targetPane) {
            targetPane.classList.remove('d-none');
            targetPane.classList.add('show', 'active');
        }
    };

    window.switchLightboxImage = function(imgUrl, el) {
        const lightboxImg = document.getElementById('lightboxMainImg');
        if (lightboxImg) {
            lightboxImg.src = imgUrl;
            lightboxImg.style.transform = 'scale(1)';
            lightboxImg.style.cursor = 'zoom-in';
        }
        document.querySelectorAll('#lightboxThumbs .strip-thumb').forEach(b => b.classList.remove('active'));
        if (el) el.classList.add('active');
    };

    window.switchMainDetailImage = function(imgUrl, el) {
        if (window.mainSwiper && window.mainSwiper.slides) {
            let foundIdx = -1;
            window.mainSwiper.slides.forEach((slide, idx) => {
                const img = slide.querySelector('img');
                if (img && (img.src === imgUrl || img.getAttribute('src') === imgUrl)) {
                    foundIdx = idx;
                }
            });
            if (foundIdx !== -1) {
                window.mainSwiper.slideTo(foundIdx);
            }
        }

        const lightboxImg = document.getElementById('lightboxMainImg');
        if (lightboxImg && imgUrl) lightboxImg.src = imgUrl;

        document.querySelectorAll('#lightboxThumbs .strip-thumb').forEach(b => {
            const tImg = b.querySelector('img');
            if (tImg && (tImg.src === imgUrl || tImg.getAttribute('src') === imgUrl)) {
                b.classList.add('active');
            } else {
                b.classList.remove('active');
            }
        });
    };

    window.adjustBulkVariantQty = function(vName, delta) {
        if (!window.bulkState[vName]) {
            window.bulkState[vName] = { qty: 0, price: baseProductPrice };
        }

        const row = document.querySelector(`.bulk-variant-item[data-variant-name="${CSS.escape(vName)}"]`);
        const maxStock = row ? parseInt(row.getAttribute('data-variant-stock') || '999') : 999;

        if (delta > 0 && window.bulkState[vName].qty >= maxStock) {
            if (typeof showToast === 'function') {
                showToast(`দুঃখিত, এই ভ্যারিয়েন্টের সর্বোচ্চ স্টক সীমায় পৌঁছেছেন (${maxStock} টি)!`, 'warning');
            }
            return;
        }

        window.bulkState[vName].qty = Math.min(maxStock, Math.max(0, window.bulkState[vName].qty + delta));

        if (row) {
            const valSpan = row.querySelector('.bulk-qty-val');
            if (valSpan) valSpan.textContent = window.bulkState[vName].qty;

            if (window.bulkState[vName].qty > 0) {
                row.classList.add('item-active');
            } else {
                row.classList.remove('item-active');
            }

            if (delta > 0 && window.bulkState[vName].img) {
                window.switchMainDetailImage(window.bulkState[vName].img, null);
            }
        }

        window.calculateLiveSummary();
    };

    window.adjustSingleQty = function(delta) {
        const maxStock = {{ max(1, (int) $product->stock_qty) }};
        if (delta > 0 && window.singleProductQty >= maxStock) {
            if (typeof showToast === 'function') {
                showToast(`দুঃখিত, সর্বোচ্চ স্টক সীমায় পৌঁছেছেন (${maxStock} টি)!`, 'warning');
            }
            return;
        }
        window.singleProductQty = Math.min(maxStock, Math.max(1, window.singleProductQty + delta));
        const qtyEl = document.getElementById('singleQtyDisplay');
        if (qtyEl) qtyEl.textContent = window.singleProductQty;
        window.calculateLiveSummary();
    };

    window.changeShippingZone = function(fee, zone) {
        window.currentShippingFee = fee;

        document.querySelectorAll('.delivery-zone-card').forEach(card => {
            card.classList.remove('active-zone');
        });
        const activeCard = document.getElementById('zoneCard_' + zone);
        if (activeCard) {
            activeCard.classList.add('active-zone');
            const radio = activeCard.querySelector('input[type="radio"]');
            if (radio) radio.checked = true;
        }

        window.calculateLiveSummary();

        const estText = document.getElementById('summaryDeliveryDate');
        if (!estText) return;
        if (zone === 'dhaka') {
            estText.innerHTML = '<i class="fa-regular fa-clock text-primary me-1"></i> <span>সম্ভাব্য ডেলিভারি: <strong class="text-dark">{{ now()->addDays(1)->translatedFormat("l, d F") }}</strong> (২৪-৪৮ ঘণ্টা)</span>';
        } else {
            estText.innerHTML = '<i class="fa-regular fa-clock text-primary me-1"></i> <span>সম্ভাব্য ডেলিভারি: <strong class="text-dark">{{ now()->addDays(3)->translatedFormat("l, d F") }}</strong> (২-৩ কার্যদিবস)</span>';
        }
    };

    window.calculateLiveSummary = function() {
        let totalQty = 0;
        let subtotal = 0;
        let breakdownHtml = '';

        const keys = Object.keys(window.bulkState);
        if (keys.length > 0) {
            keys.forEach(name => {
                const item = window.bulkState[name];
                if (item.qty > 0) {
                    const lineTotal = item.price * item.qty;
                    totalQty += item.qty;
                    subtotal += lineTotal;
                    breakdownHtml += `
                        <div class="d-flex justify-content-between align-items-center summary-item-pill">
                            <span class="d-flex align-items-center text-dark fw-medium text-truncate" style="max-width: 150px;">
                                <i class="fa-solid fa-circle-check text-success" style="font-size: 11px;"></i>
                                <span class="text-truncate" style="margin-left: 5px;">${name}</span>
                                <span class="badge bg-light text-secondary border font-mono" style="margin-left: 5px;">×${item.qty}</span>
                            </span>
                            <span class="font-mono fw-bold text-dark">৳ ${lineTotal.toLocaleString()}</span>
                        </div>
                    `;
                }
            });

            if (totalQty === 0) {
                totalQty = 1;
                const defName = keys[0];
                subtotal = window.bulkState[defName]?.price || baseProductPrice;
                breakdownHtml += `
                    <div class="d-flex justify-content-between align-items-center summary-item-pill">
                        <span class="d-flex align-items-center text-dark fw-medium text-truncate" style="max-width: 150px;">
                            <i class="fa-solid fa-circle-check text-success" style="font-size: 11px;"></i>
                            <span class="text-truncate" style="margin-left: 5px;">${defName}</span>
                            <span class="badge bg-light text-secondary border font-mono" style="margin-left: 5px;">×1</span>
                        </span>
                        <span class="font-mono fw-bold text-dark">৳ ${subtotal.toLocaleString()}</span>
                    </div>
                `;
            }
        } else {
            totalQty = window.singleProductQty;
            subtotal = baseProductPrice * window.singleProductQty;
            breakdownHtml += `
                <div class="d-flex justify-content-between align-items-center summary-item-pill">
                    <span class="d-flex align-items-center text-dark fw-medium text-truncate" style="max-width: 150px;">
                        <i class="fa-solid fa-circle-check text-success" style="font-size: 11px;"></i>
                        <span class="text-truncate" style="margin-left: 5px;">${productTitle.length > 18 ? productTitle.substring(0, 18) + '...' : productTitle}</span>
                        <span class="badge bg-light text-secondary border font-mono" style="margin-left: 5px;">×${window.singleProductQty}</span>
                    </span>
                    <span class="font-mono fw-bold text-dark">৳ ${subtotal.toLocaleString()}</span>
                </div>
            `;
        }

        const grandTotal = subtotal + window.currentShippingFee;

        const breakdownContainer = document.getElementById('summarySelectedBreakdown');
        const itemsBadge = document.getElementById('summaryTotalItemsBadge');
        const subtotalText = document.getElementById('summarySubtotalText');
        const shippingText = document.getElementById('summaryShippingText');
        const grandTotalText = document.getElementById('summaryGrandTotalText');
        const mobileGrandTotalText = document.getElementById('mobileGrandTotalText');

        if (breakdownContainer) breakdownContainer.innerHTML = breakdownHtml;
        if (itemsBadge) itemsBadge.textContent = `${totalQty} টি আইটেম`;
        if (subtotalText) subtotalText.textContent = `৳ ${subtotal.toLocaleString()}`;
        if (shippingText) shippingText.textContent = window.currentShippingFee === 0 ? 'ফ্রি' : `৳ ${window.currentShippingFee.toLocaleString()}`;
        if (grandTotalText) grandTotalText.textContent = `৳ ${grandTotal.toLocaleString()}`;
        if (mobileGrandTotalText) mobileGrandTotalText.textContent = `৳ ${grandTotal.toLocaleString()}`;
    };

    window.getSelectedBulkItems = function(productId) {
        const items = [];
        const keys = Object.keys(window.bulkState);

        if (keys.length > 0) {
            keys.forEach(name => {
                const it = window.bulkState[name];
                if (it.qty > 0) {
                    items.push({
                        product_id: productId,
                        variant: name,
                        quantity: it.qty,
                        price: it.price,
                        image: it.img || null
                    });
                }
            });

            if (items.length === 0) {
                const defName = keys[0];
                items.push({
                    product_id: productId,
                    variant: defName,
                    quantity: 1,
                    price: window.bulkState[defName]?.price || baseProductPrice,
                    image: window.bulkState[defName]?.img || null
                });
            }
        } else {
            items.push({
                product_id: productId,
                variant: null,
                quantity: window.singleProductQty,
                price: baseProductPrice
            });
        }
        return items;
    };

    window.executeBulkAddToCart = function(productId, btn = null) {
        const items = window.getSelectedBulkItems(productId);
        if (typeof addBatchToCart === 'function') {
            addBatchToCart(items, null, btn);
        }
    };

    window.executeBulkBuyNow = function(productId) {
        const items = window.getSelectedBulkItems(productId);
        if (typeof addBatchToCart === 'function') {
            addBatchToCart(items, () => {
                if (typeof Turbo !== 'undefined') {
                    Turbo.visit("{{ route('checkout') }}");
                } else {
                    window.location.href = "{{ route('checkout') }}";
                }
            });
        }
    };

    window.executeWhatsAppOrder = function(e) {
        e.preventDefault();
        const items = window.getSelectedBulkItems({{ $product->id }});
        let total = 0;
        const lines = [];

        items.forEach(it => {
            const lineTotal = it.price * it.quantity;
            total += lineTotal;
            if (it.variant) {
                lines.push(`- ${it.quantity}x ${it.variant} (৳${lineTotal.toLocaleString()})`);
            } else {
                lines.push(`- পরিমাণ: ${it.quantity} টি (৳${lineTotal.toLocaleString()})`);
            }
        });

        const grandTotal = total + window.currentShippingFee;
        const msg = `আসসালামু আলাইকুম, আমি এই প্রোডাক্টটি অর্ডার করতে চাই:\n\nপণ্য: ${productTitle}\n\nঅর্ডার আইটেম:\n${lines.join('\n')}\n\nডেলিভারি চার্জ: ৳ ${window.currentShippingFee}\nসর্বমোট মূল্য: ৳ ${grandTotal.toLocaleString()}\nলিংক: ${productUrl}`;
        const waUrl = `https://wa.me/{{ preg_replace('/[^0-9]/', '', $whatsappNumber) }}?text=${encodeURIComponent(msg)}`;
        window.open(waUrl, '_blank');
    };

    window.toggleShareDropdown = function(e) {
        e.stopPropagation();
        const menu = document.getElementById('shareDropdownMenu');
        if (menu) {
            menu.style.display = menu.style.display === 'block' ? 'none' : 'block';
        }
    };

    window.copyProductLink = function(e) {
        e.stopPropagation();
        const url = window.location.href;
        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(url).then(() => {
                showCopySuccess();
            }).catch(() => {
                fallbackCopy(url);
            });
        } else {
            fallbackCopy(url);
        }
    };

    function fallbackCopy(text) {
        const temp = document.createElement('input');
        temp.value = text;
        document.body.appendChild(temp);
        temp.select();
        document.execCommand('copy');
        document.body.removeChild(temp);
        showCopySuccess();
    }

    function showCopySuccess() {
        const btnText = document.getElementById('copyLinkBtnText');
        if (btnText) btnText.innerHTML = '<i class="fa-solid fa-check text-success me-1"></i> কপি হয়েছে!';
        if (typeof showToast === 'function') {
            showToast('প্রোডাক্ট লিংক সফলভাবে কপি করা হয়েছে!', 'success');
        }
        setTimeout(() => {
            if (btnText) btnText.textContent = 'লিংক কপি করুন';
            const menu = document.getElementById('shareDropdownMenu');
            if (menu) menu.style.display = 'none';
        }, 1800);
    }

    function initSingleProductPage() {
        const productMainContainer = document.querySelector('.product-master-box, .productMainSwiper');
        if (!productMainContainer) return;

        if (typeof Swiper === 'undefined') {
            setTimeout(initSingleProductPage, 50);
            return;
        }

        window.singleProductQty = 1;
        window.currentShippingFee = {{ $shippingInside }};
        window.bulkState = {};

        if (window.mainSwiper && typeof window.mainSwiper.destroy === 'function') {
            try { window.mainSwiper.destroy(true, true); } catch(err) {}
            window.mainSwiper = null;
        }
        if (window.thumbSwiper && typeof window.thumbSwiper.destroy === 'function') {
            try { window.thumbSwiper.destroy(true, true); } catch(err) {}
            window.thumbSwiper = null;
        }

        if (document.querySelector('.productThumbSwiper') && typeof Swiper !== 'undefined') {
            window.thumbSwiper = new Swiper('.productThumbSwiper', {
                spaceBetween: 10,
                slidesPerView: 'auto',
                freeMode: true,
                watchSlidesProgress: true,
            });
        }

        if (document.querySelector('.productMainSwiper') && typeof Swiper !== 'undefined') {
            const hasMultipleImages = {{ count($galleryList) > 1 ? 'true' : 'false' }};
            window.mainSwiper = new Swiper('.productMainSwiper', {
                spaceBetween: 0,
                speed: 600,
                grabCursor: true,
                rewind: true,
                autoplay: hasMultipleImages ? {
                    delay: 3500,
                    disableOnInteraction: false,
                    pauseOnMouseEnter: true,
                } : false,
                navigation: {
                    nextEl: '.swiper-button-next',
                    prevEl: '.swiper-button-prev',
                },
                thumbs: window.thumbSwiper ? { swiper: window.thumbSwiper } : undefined,
            });
        }

        document.querySelectorAll('.bulk-variant-item').forEach((row, idx) => {
            const vName = row.getAttribute('data-variant-name');
            const vPrice = parseFloat(row.getAttribute('data-variant-price')) || baseProductPrice;
            const vImg = row.getAttribute('data-variant-img');
            const rowId = row.id;
            const initialQty = (idx === 0) ? 1 : 0;

            window.bulkState[vName] = {
                qty: initialQty,
                price: vPrice,
                img: vImg,
                rowId: rowId
            };
        });

        window.calculateLiveSummary();

        if (window.ZippyTracker && typeof window.ZippyTracker.trackViewItem === 'function') {
            window.ZippyTracker.trackViewItem({
                id: '{{ (string)$product->id }}',
                name: {!! json_encode($product->title) !!},
                price: {{ $productPrice }},
                value: {{ $productPrice }},
                content_ids: ['{{ (string)$product->id }}'],
                items: [
                    {
                        item_id: '{{ (string)$product->id }}',
                        item_name: {!! json_encode($product->title) !!},
                        item_brand: {!! json_encode($product->brand_name ?? $siteName) !!},
                        item_category: {!! json_encode($product->cat_name_bn ?? ($product->cat_name_en ?? 'Gadget')) !!},
                        price: {{ $productPrice }},
                        quantity: 1
                    }
                ]
            });
        }
    }

    window.setReviewRating = function(rating) {
        document.getElementById('reviewRatingInput').value = rating;
        const stars = document.querySelectorAll('#reviewStarSelector .star-opt');
        stars.forEach(star => {
            const r = parseInt(star.getAttribute('data-rating'));
            if (r <= rating) {
                star.classList.remove('text-muted', 'text-opacity-25');
                star.classList.add('text-warning');
            } else {
                star.classList.remove('text-warning');
                star.classList.add('text-muted', 'text-opacity-25');
            }
        });
    };

    window.handleReviewSubmit = function(e) {
        e.preventDefault();
        const form = document.getElementById('reviewSubmitForm');
        const btn = document.getElementById('reviewSubmitBtn');
        const btnText = document.getElementById('reviewSubmitBtnText');
        const spinner = document.getElementById('reviewSubmitSpinner');
        const alertBox = document.getElementById('reviewAlertBox');

        btn.disabled = true;
        btnText.classList.add('d-none');
        spinner.classList.remove('d-none');
        alertBox.className = 'alert d-none small mb-0 py-2';

        const formData = new FormData(form);

        axios.post("{{ route('product.review.submit', $product->id) }}", formData, {
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            }
        }).then(res => {
            btn.disabled = false;
            btnText.classList.remove('d-none');
            spinner.classList.add('d-none');

            if (res.data && res.data.success) {
                alertBox.className = 'alert alert-success small mb-0 py-2';
                alertBox.textContent = res.data.message;
                alertBox.classList.remove('d-none');
                setTimeout(() => {
                    location.reload();
                }, 1200);
            } else {
                alertBox.className = 'alert alert-danger small mb-0 py-2';
                alertBox.textContent = res.data.message || 'Something went wrong.';
                alertBox.classList.remove('d-none');
            }
        }).catch(err => {
            btn.disabled = false;
            btnText.classList.remove('d-none');
            spinner.classList.add('d-none');

            let msg = 'Failed to submit review. Please try again.';
            if (err.response && err.response.data && err.response.data.message) {
                msg = err.response.data.message;
            }
            alertBox.className = 'alert alert-danger small mb-0 py-2';
            alertBox.textContent = msg;
            alertBox.classList.remove('d-none');
        });
    };

    window.openReviewPhotoModal = function(src) {
        const modalImg = document.getElementById('reviewPhotoModalImg');
        const modalEl = document.getElementById('reviewPhotoModal');
        if (modalImg && modalEl) {
            modalImg.src = src;
            if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                bootstrap.Modal.getOrCreateInstance(modalEl).show();
            }
        }
    };

    window.handleReviewPhotoSelect = function(input) {
        const previewContainer = document.getElementById('reviewPhotoThumbnails');
        if (!previewContainer) return;
        previewContainer.innerHTML = '';
        const files = Array.from(input.files || []).slice(0, 3);
        files.forEach(file => {
            const reader = new FileReader();
            reader.onload = function(e) {
                const img = document.createElement('img');
                img.src = e.target.result;
                img.className = 'rounded-2 border shadow-2xs';
                img.style.width = '60px';
                img.style.height = '60px';
                img.style.objectFit = 'cover';
                previewContainer.appendChild(img);
            };
            reader.readAsDataURL(file);
        });
    };

    function cleanupSingleProductPage() {
        if (window.mainSwiper && typeof window.mainSwiper.destroy === 'function') {
            try { window.mainSwiper.destroy(true, true); } catch(err) {}
            window.mainSwiper = null;
        }
        if (window.thumbSwiper && typeof window.thumbSwiper.destroy === 'function') {
            try { window.thumbSwiper.destroy(true, true); } catch(err) {}
            window.thumbSwiper = null;
        }
        if (window.__liveViewersInterval) {
            clearInterval(window.__liveViewersInterval);
            window.__liveViewersInterval = null;
        }
    }

    window.initSingleProductPage = initSingleProductPage;
    window.cleanupSingleProductPage = cleanupSingleProductPage;

    if (!window.__productPageTurboBound) {
        window.__productPageTurboBound = true;
        document.addEventListener('turbo:load', function() {
            if (typeof window.initSingleProductPage === 'function') {
                window.initSingleProductPage();
            }
        });
        document.addEventListener('turbo:before-cache', function() {
            if (typeof window.cleanupSingleProductPage === 'function') {
                window.cleanupSingleProductPage();
            }
        });
        document.addEventListener('turbo:before-render', function() {
            if (typeof window.cleanupSingleProductPage === 'function') {
                window.cleanupSingleProductPage();
            }
        });
    }

    initSingleProductPage();
})();
</script>
@endpush
