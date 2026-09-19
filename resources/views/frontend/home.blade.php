@extends('frontend.layouts.app')

@php
    $homeStoreName = $settings['store_name'] ?? 'Zippy';
    $homeStoreTagline = $settings['store_tagline'] ?? 'প্রিমিয়াম গ্যাজেট, মেকানিক্যাল কিবোর্ড ও লাইফস্টাইল স্টোর বাংলাদেশ';
@endphp

@section('title', 'Zippy  | Authentic Gadgets and Online Shopping Bangladesh')
@section('meta_description', $homeStoreName . ' - বাংলাদেশের বিশ্বস্ত অনলাইন শপ। মেকানিক্যাল কিবোর্ড, ডেস্ক সেটআপ এক্সেসরিজ, অডিও ডিভাইস ও প্রিমিয়াম গ্যাজেট কিনুন সবচেয়ে সাশ্রয়ী মূল্যে। ১০০% ক্যাশ অন ডেলিভারি।')
@section('meta_keywords', 'gadget bd, mechanical keyboard bangladesh, desk setup bd, audio gadget, online shopping bangladesh')

@push('styles')
<style>
body { background: #f4f5f7; }

.zb-section-hd {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 1rem;
}
.zb-title {
    font-size: 1.15rem;
    font-weight: 700;
    color: #0f172a;
    letter-spacing: -0.02em;
    margin: 0;
}
@media (min-width: 992px) { .zb-title { font-size: 1.4rem; } }

.zb-see-all-link {
    font-size: 12.5px;
    font-weight: 600;
    color: #6b7280;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 4px;
    white-space: nowrap;
    transition: color .15s;
}
.zb-see-all-link:hover { color: #0f172a; }

.flash-capsule {
    background: #0f172a;
    border-radius: 16px;
    padding: 12px 20px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    box-shadow: 0 4px 20px rgba(15,23,42,.15);
    gap: 10px;
}

.flash-left {
    display: flex;
    align-items: center;
    gap: 12px;
}

.zb-flame {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 34px; height: 34px;
    border-radius: 10px;
    background: #ff4444;
    color: #fff;
    font-size: 15px;
    flex-shrink: 0;
}

.flash-see-all-btn {
    background: rgba(255, 255, 255, 0.12);
    color: #f8fafc;
    font-size: 13px;
    padding: 6px 14px;
    border-radius: 50px;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-weight: 600;
    white-space: nowrap;
    transition: all 0.2s ease;
}

.flash-see-all-btn:hover {
    background: rgba(255, 255, 255, 0.2);
    color: #ffffff;
}

.zb-countdown {
    display: flex;
    align-items: center;
    gap: 6px;
    font-variant-numeric: tabular-nums;
    flex-shrink: 0;
}
.zb-cd-block {
    background: rgba(255, 255, 255, 0.1);
    backdrop-filter: blur(8px);
    -webkit-backdrop-filter: blur(8px);
    border: 1px solid rgba(255, 255, 255, 0.08);
    border-radius: 10px;
    min-width: 46px;
    padding: 6px 0;
    text-align: center;
    line-height: 1.2;
}
.zb-cd-num   { display: block; font-size: 1.05rem; font-weight: 800; color: #fff; letter-spacing: .02em; }
.zb-cd-label { display: block; font-size: 9px; font-weight: 600; color: rgba(255,255,255,.7); margin-top: 2px; }
.zb-cd-sep   { font-size: 1.1rem; font-weight: 800; color: rgba(255,255,255,.4); transform: translateY(-4px); }

.zb-flash-body {
    background: #fff;
    padding: 1.5rem 0 2rem;
}

.zb-home-section-card {
    background: #ffffff;
    padding: 16px 16px 20px;
    border-radius: 18px;
    border: 1px solid rgba(15, 23, 42, 0.06);
    box-shadow: 0 4px 20px -4px rgba(15, 23, 42, 0.04);
}
@media (min-width: 992px) {
    .zb-home-section-card {
        padding: 20px 22px 24px;
    }
}

.g-mobile-tight {
    --bs-gutter-x: 10px;
    --bs-gutter-y: 12px;
}
@media (min-width: 992px) {
    .g-mobile-tight {
        --bs-gutter-x: 16px;
        --bs-gutter-y: 16px;
    }
}

.zb-trust-bar {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    background: #ffffff;
    border: 1px solid rgba(15, 23, 42, 0.08);
    border-radius: 20px;
    box-shadow: 0 4px 20px -4px rgba(15, 23, 42, 0.05), 0 1px 3px rgba(15, 23, 42, 0.02);
    padding: 10px;
    gap: 8px;
    position: relative;
    overflow: hidden;
}
.zb-trust-item {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 16px 18px;
    border-radius: 14px;
    text-decoration: none;
    background: transparent;
    transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
    position: relative;
}
.zb-trust-item:hover {
    background: #f8fafc;
    transform: translateY(-2px);
}
.zb-trust-icon-wrap {
    flex-shrink: 0;
}
.zb-trust-icon {
    width: 44px;
    height: 44px;
    border-radius: 12px;
    background: #0f172a;
    color: #ffffff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 17px;
    box-shadow: 0 4px 12px rgba(15, 23, 42, 0.12);
    transition: all 0.28s cubic-bezier(0.16, 1, 0.3, 1);
}
.zb-trust-item:hover .zb-trust-icon {
    background: #1e293b;
    transform: scale(1.08) rotate(-3deg);
    box-shadow: 0 6px 16px rgba(15, 23, 42, 0.22);
}
.zb-trust-info {
    display: flex;
    flex-direction: column;
    gap: 3px;
    min-width: 0;
}
.zb-trust-title {
    font-size: 13.5px;
    font-weight: 700;
    color: #0f172a;
    letter-spacing: -0.01em;
    line-height: 1.25;
    transition: color 0.15s ease;
}
.zb-trust-item:hover .zb-trust-title {
    color: #000000;
}
.zb-trust-sub {
    font-size: 11.5px;
    color: #64748b;
    line-height: 1.35;
}
@media (min-width: 992px) {
    .zb-trust-item:not(:last-child)::after {
        content: '';
        position: absolute;
        right: -5px;
        top: 22%;
        height: 56%;
        width: 1px;
        background: rgba(15, 23, 42, 0.07);
        pointer-events: none;
    }
}
@media (max-width: 991.98px) and (min-width: 576px) {
    .zb-trust-bar {
        grid-template-columns: repeat(2, 1fr);
        gap: 10px;
        padding: 12px;
    }
}

@media (max-width: 767.98px) {
    body { background: #f2f2f7; padding-bottom: 72px; }
    
    .container.px-mobile-tight,
    .px-mobile-tight {
        padding-left: 6px !important;
        padding-right: 6px !important;
    }
    .zb-home-section-card {
        padding: 10px 8px 12px;
        border-radius: 14px;
    }
    .g-mobile-tight {
        --bs-gutter-x: 6px;
        --bs-gutter-y: 8px;
    }
    .zb-flash-body {
        padding: 10px 0 14px;
    }

    .flash-capsule {
        border-radius: 10px;
        padding: 6px 8px 6px 8px;
        overflow-x: auto;
        scrollbar-width: none;
    }
    .flash-capsule::-webkit-scrollbar { display: none; }
    .flash-left { gap: 6px; }
    .zb-flame { width: 30px; height: 30px; border-radius: 10px; font-size: 14px; }
    .zb-title { font-size: 14.5px !important; }
    .flash-see-all-btn { font-size: 11px; padding: 4px 10px; gap: 4px; margin-left: 2px;}

    .zb-countdown { gap: 4px; }
    .zb-cd-block {
        min-width: 38px;
        padding: 4px 0;
        border-radius: 8px;
    }
    .zb-cd-num { font-size: 12.5px; }
    .zb-cd-label { font-size: 8px; margin-top: 1px; }
    .zb-cd-sep { font-size: 13px; transform: translateY(-4px); margin: 0 -1px;}

    .zb-see-all-link { background: rgba(0,0,0,.06); padding: 4px 10px; border-radius: 20px; font-size: 11px; color: #333; }
    .zb-trust-bar {
        grid-template-columns: repeat(2, 1fr);
        gap: 8px;
        padding: 8px;
        border-radius: 16px;
    }
    .zb-trust-item {
        padding: 10px 8px;
        gap: 9px;
    }
    .zb-trust-icon {
        width: 36px;
        height: 36px;
        min-width: 36px;
        font-size: 14px;
        border-radius: 10px;
    }
    .zb-trust-title {
        font-size: 12px;
        font-weight: 700;
        line-height: 1.25;
    }
    .zb-trust-sub {
        font-size: 10px;
        line-height: 1.25;
        color: #64748b;
        display: -webkit-box;
        -webkit-line-clamp: 1;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    .zb-product-card:hover { transform: none; }
}
</style>
@endpush

@section('content')

@include('frontend.inc.hero')

@if(isset($flashProducts) && count($flashProducts) > 0)
<div class="container mt-3 mt-md-4 px-mobile-tight px-sm-2 px-md-3">
    <div class="flash-capsule">
        <div class="flash-left">
            <div class="zb-flame"><i class="fa-solid fa-bolt"></i></div>
            <h2 class="zb-title text-white mb-0 font-heading text-nowrap">ফ্ল্যাশ সেল</h2>
            <a href="{{ route('product.flash_deals') }}" class="flash-see-all-btn">
                সব দেখুন <i class="fa-solid fa-arrow-right"></i>
            </a>
        </div>

        <div class="zb-countdown ms-auto">
            <div class="zb-cd-block">
                <span id="hours" class="zb-cd-num">08</span>
                <span class="zb-cd-label">ঘণ্টা</span>
            </div>
            <span class="zb-cd-sep">:</span>
            <div class="zb-cd-block">
                <span id="mins" class="zb-cd-num">40</span>
                <span class="zb-cd-label">মিনিট</span>
            </div>
            <span class="zb-cd-sep">:</span>
            <div class="zb-cd-block">
                <span id="secs" class="zb-cd-num">24</span>
                <span class="zb-cd-label">সেকেন্ড</span>
            </div>
        </div>
    </div>
</div>

<div class="zb-flash-body">
    <div class="container px-mobile-tight px-sm-2 px-md-3">
        <div class="row zb-products-row g-mobile-tight">
            @foreach(collect($flashProducts)->take(6) as $product)
                <div class="col">
                    @include('frontend.inc.product-card', ['product' => $product])
                </div>
            @endforeach
        </div>
    </div>
</div>
@endif

@if(isset($featuredProducts) && count($featuredProducts) > 0)
<div class="container mt-3 mt-md-4 px-mobile-tight px-sm-2 px-md-3">
    <div class="zb-home-section-card">
        <div class="d-flex align-items-center justify-content-between mb-2 mb-md-3 border-bottom pb-2">
            <h2 class="zb-title text-dark mb-0 d-flex align-items-center gap-2">
                <span style="color: #f59e0b; font-size: 18px;"><i class="fa-solid fa-star"></i></span>
                Featured Products
            </h2>
            <a href="{{ route('product.index') }}" class="zb-see-all-link">
                সব দেখুন <i class="fa-solid fa-arrow-right"></i>
            </a>
        </div>
        <div class="row zb-products-row g-mobile-tight">
            @foreach(collect($featuredProducts)->take(12) as $product)
                <div class="col">
                    @include('frontend.inc.product-card', ['product' => $product])
                </div>
            @endforeach
        </div>
    </div>
</div>
@endif

@php
    $anyCategoryHasProducts = false;
    foreach($categories as $cat) {
        $slug = is_object($cat) ? ($cat->slug ?? '') : ($cat['slug'] ?? '');
        if (!empty($slug) && isset($categoryProducts[$slug]) && count($categoryProducts[$slug]) > 0) {
            $anyCategoryHasProducts = true;
            break;
        }
    }
@endphp

@if($anyCategoryHasProducts)
    @foreach($categories as $i => $category)
        @php
            $catSlug = is_object($category) ? ($category->slug ?? '') : ($category['slug'] ?? '');
            $catName = is_object($category) ? ($category->name_bn ?: $category->name) : ($category['name_bn'] ?: $category['name']);
            $hasProducts = !empty($catSlug) && isset($categoryProducts[$catSlug]) && count($categoryProducts[$catSlug]) > 0;
        @endphp
        @if($hasProducts)
            <div class="container mt-3 mt-md-4 px-mobile-tight px-sm-2 px-md-3">
                <div class="zb-home-section-card">
                    <div class="d-flex align-items-center justify-content-between mb-2 mb-md-3 border-bottom pb-2">
                        <h2 class="zb-title text-dark mb-0 d-flex align-items-center gap-2">
                            <span style="color: #2563eb; font-size: 16px;"><i class="fa-solid fa-layer-group"></i></span>
                            {{ $catName }}
                        </h2>
                        <a href="{{ $catSlug ? route('category.show', $catSlug) : '#' }}" class="zb-see-all-link">
                            সব দেখুন <i class="fa-solid fa-arrow-right"></i>
                        </a>
                    </div>

                    <div class="row zb-products-row g-mobile-tight">
                        @foreach(collect($categoryProducts[$catSlug] ?? [])->take(6) as $product)
                            <div class="col">
                                @include('frontend.inc.product-card', ['product' => $product])
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif
    @endforeach
@endif

<div class="container my-3 my-md-4 px-mobile-tight px-sm-2 px-md-3">
    <div class="zb-trust-bar">
        <a href="{{ route('product.index') }}" class="zb-trust-item">
            <div class="zb-trust-icon-wrap">
                <div class="zb-trust-icon">
                    <i class="fa-solid fa-truck-fast"></i>
                </div>
            </div>
            <div class="zb-trust-info">
                <div class="zb-trust-title">দ্রুত এক্সপ্রেস ডেলিভারি</div>
                <div class="zb-trust-sub">সারা দেশে ২৪-৭২ ঘণ্টায় ক্যাশ অন ডেলিভারি</div>
            </div>
        </a>
        <a href="{{ route('product.index') }}" class="zb-trust-item">
            <div class="zb-trust-icon-wrap">
                <div class="zb-trust-icon">
                    <i class="fa-solid fa-shield-check"></i>
                </div>
            </div>
            <div class="zb-trust-info">
                <div class="zb-trust-title">১০০% অথেনটিক গ্যাজেট</div>
                <div class="zb-trust-sub">অফিসিয়াল ব্র্যান্ড ওয়ারেন্টি ও রিপ্লেসমেন্ট</div>
            </div>
        </a>
        <a href="{{ route('product.index') }}" class="zb-trust-item">
            <div class="zb-trust-icon-wrap">
                <div class="zb-trust-icon">
                    <i class="fa-solid fa-arrow-rotate-left"></i>
                </div>
            </div>
            <div class="zb-trust-info">
                <div class="zb-trust-title">৭ দিনের সহজ রিটার্ন</div>
                <div class="zb-trust-sub">ত্রুটিযুক্ত পণ্যে সহজ ও দ্রুত এক্সচেঞ্জ</div>
            </div>
        </a>
        <a href="{{ route('order.track') }}" class="zb-trust-item">
            <div class="zb-trust-icon-wrap">
                <div class="zb-trust-icon">
                    <i class="fa-solid fa-headset"></i>
                </div>
            </div>
            <div class="zb-trust-info">
                <div class="zb-trust-title">২৪/৭ কাস্টমার সাপোর্ট</div>
                <div class="zb-trust-sub">লাইভ ট্র্যাকিং ও সার্বক্ষণিক সহায়তা</div>
            </div>
        </a>
    </div>
</div>

@include('frontend.inc.promo-popup')

@endsection