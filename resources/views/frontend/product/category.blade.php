@extends('frontend.layouts.app')

@section('title', ($category->name_bn ?? ($category->name ?? 'ক্যাটাগরি')) . ' কিনুন সাশ্রয়ী মূল্যে | ' . ($settings['store_name'] ?? 'ZippyBD'))
@section('meta_description', ($category->name_bn ?? ($category->name ?? 'ক্যাটাগরি')) . ' ক্যাটাগরির সেরা পণ্যসমূহ সেরা মূল্যে কিনুন ' . ($settings['store_name'] ?? 'ZippyBD') . ' থেকে। ১০০% অরিজিনাল ও দ্রুততম ডেলিভারি।')
@section('canonical', route('category.show', $category->slug))
@section('og_type', 'website')

@push('schema')
<script type="application/ld+json">
{!! json_encode([
    '@'.'context' => 'https://schema.org',
    '@type' => 'CollectionPage',
    'name' => $category->name_bn ?? ($category->name ?? 'ক্যাটাগরি'),
    'url' => route('category.show', $category->slug),
    'description' => ($category->name_bn ?? ($category->name ?? 'ক্যাটাগরি')) . ' ক্যাটাগরির সেরা পণ্যসমূহ সেরা মূল্যে কিনুন ZippyBD থেকে।'
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
</script>
<script type="application/ld+json">
@php
    $catBreadcrumbsSchema = [
        [
            '@type' => 'ListItem',
            'position' => 1,
            'name' => 'হোম',
            'item' => url('/'),
        ]
    ];
    $cPos = 2;
    if (isset($categoryBreadcrumbs) && count($categoryBreadcrumbs) > 0) {
        foreach ($categoryBreadcrumbs as $bCat) {
            $catBreadcrumbsSchema[] = [
                '@type' => 'ListItem',
                'position' => $cPos++,
                'name' => $bCat->name_bn ?? ($bCat->name ?? ''),
                'item' => route('category.show', $bCat->slug),
            ];
        }
    } else {
        $catBreadcrumbsSchema[] = [
            '@type' => 'ListItem',
            'position' => $cPos++,
            'name' => $category->name_bn ?? ($category->name ?? 'ক্যাটাগরি'),
            'item' => route('category.show', $category->slug),
        ];
    }
@endphp
{!! json_encode([
    '@'.'context' => 'https://schema.org',
    '@type' => 'BreadcrumbList',
    'itemListElement' => $catBreadcrumbsSchema
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
</script>
@endpush

@section('content')
<div class="container-fluid px-3 px-md-4 px-xl-5 py-4">
    <nav class="small text-secondary mb-3" aria-label="breadcrumb">
        <a href="{{ route('home') }}" class="text-secondary text-decoration-none">হোম</a>
        @if(isset($categoryBreadcrumbs) && count($categoryBreadcrumbs) > 0)
            @foreach($categoryBreadcrumbs as $index => $bCat)
                <span class="mx-1">/</span>
                @if($index === count($categoryBreadcrumbs) - 1)
                    <span class="text-dark fw-bold">{{ $bCat->name_bn ?? ($bCat->name ?? '') }}</span>
                @else
                    <a href="{{ route('category.show', $bCat->slug) }}" class="text-secondary text-decoration-none">{{ $bCat->name_bn ?? ($bCat->name ?? '') }}</a>
                @endif
            @endforeach
        @else
            <span class="mx-1">/</span>
            <span class="text-dark fw-bold">{{ $category->name_bn ?? ($category->name ?? '') }}</span>
        @endif
    </nav>

    <div class="bg-white rounded-4 border p-3 p-md-4 shadow-sm mb-4">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
            <div>
                <div class="d-flex align-items-center gap-2 mb-2">
                    <span class="badge bg-dark rounded-pill px-3 py-1">
                        <i class="fa-solid fa-layer-group me-1"></i> ক্যাটাগরি কালেকশন
                    </span>
                    <span class="badge bg-light text-dark border rounded-pill px-3 py-1 catalog-total-count">
                        মোট {{ $products->total() }} টি পণ্য
                    </span>
                </div>
                <h1 class="h3 fw-bold text-dark font-heading mb-1">{{ $category->name_bn ?? ($category->name ?? '') }}</h1>
                <p class="text-secondary small mb-0">{{ $category->name_bn ?? ($category->name ?? '') }} ক্যাটাগরির সমস্ত প্রিমিয়াম ও অরিজিনাল প্রোডাক্ট</p>
            </div>

            <div class="d-flex flex-wrap align-items-center gap-2">
                <div class="d-flex align-items-center gap-1.5">
                    <span class="small fw-bold text-secondary text-nowrap" style="font-size: 12px;">প্রতি পেজে:</span>
                    <select name="per_page" class="form-select form-select-sm rounded-pill px-2.5 py-1.5 catalog-per-page-select shadow-none" style="width: auto; font-size: 12.5px; cursor: pointer;">
                        @foreach([15, 20, 30, 50] as $pp)
                            <option value="{{ $pp }}" {{ $products->perPage() == $pp ? 'selected' : '' }}>{{ $pp }} টি</option>
                        @endforeach
                    </select>
                </div>
                <div class="d-flex align-items-center gap-1.5 border-start ps-2">
                    <span class="small fw-bold text-secondary text-nowrap" style="font-size: 12px;">সর্ট:</span>
                    <select name="sort" class="form-select form-select-sm rounded-pill px-3 py-1.5 catalog-sort-select shadow-none" style="width: auto; font-size: 12.5px; cursor: pointer;">
                        <option value="latest" {{ ($sort ?? 'latest') === 'latest' ? 'selected' : '' }}>নতুন সংযোজন</option>
                        <option value="price_asc" {{ ($sort ?? '') === 'price_asc' ? 'selected' : '' }}>দাম: কম থেকে বেশি</option>
                        <option value="price_desc" {{ ($sort ?? '') === 'price_desc' ? 'selected' : '' }}>দাম: বেশি থেকে কম</option>
                        <option value="rating" {{ ($sort ?? '') === 'rating' ? 'selected' : '' }}>রেটিং ও জনপ্রিয়তা</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    @if(isset($childCategories) && count($childCategories) > 0)
        <div class="d-flex flex-wrap gap-2 mb-4">
            @foreach($childCategories as $cCat)
                <a href="{{ route('category.show', $cCat->slug) }}" class="btn btn-sm btn-light border rounded-pill px-3 py-1.5 font-heading text-dark fw-semibold shadow-2xs">
                    <i class="fa-solid fa-layer-group text-primary me-1" style="font-size: 11px;"></i>{{ $cCat->name_bn ?? $cCat->name }}
                </a>
            @endforeach
        </div>
    @endif

    <div class="row g-3 g-xl-4 items-start">
        <div class="col-12 col-lg-3 col-xl-3">
            @include('frontend.inc.product-filter-sidebar')
        </div>
        <div class="col-12 col-lg-9 col-xl-9" id="productGridWrapper">
            @include('frontend.product.partials.product-grid-container')
        </div>
    </div>

    <div class="mt-5 bg-white border rounded-4 p-3 p-md-4 shadow-2xs">
        <h2 class="h5 fw-bold text-dark font-heading mb-2">
            {{ $category->name_bn ?? ($category->name ?? 'ক্যাটাগরি') }} কালেকশন ও অনলাইন শপিং - Zippy BD
        </h2>
        <div class="text-secondary small line-height-base">
            <p class="mb-2">
                {{ $category->name_bn ?? ($category->name ?? 'ক্যাটাগরি') }} এর সেরা ও লেটেস্ট পণ্যগুলো কিনুন নির্ভরযোগ্য অনলাইন শপ Zippy BD থেকে। আমরা আপনাকে দিচ্ছি ১০০% অরিজিনাল ব্র্যান্ডের গুণগত মানসম্পন্ন প্রোডাক্ট, সাশ্রয়ী দাম এবং সারা বাংলাদেশে দ্রুততম হোম ডেলিভারি সুবিধা।
            </p>
            <div class="collapse" id="categorySeoBlock">
                <p class="mb-2">
                    কেন Zippy BD থেকে {{ $category->name_bn ?? ($category->name ?? 'ক্যাটাগরি') }} কিনবেন? আমাদের কালেকশনে রয়েছে সর্বোচ্চ ভ্যারাইটি, জেনুইন অফিশিয়াল ওয়ারেন্টি গ্যারান্টি, ক্যাশ অন ডেলিভারি (COD) এবং ৭ দিনের সহজ রিটার্ন ও রিপ্লেসমেন্ট পলিসি। ঢাকা সিটিতে ২৪ থেকে ৪৮ ঘণ্টা এবং ঢাকার বাইরে মাত্র ২ থেকে ৩ দিনের মধ্যে আপনার কাঙ্ক্ষিত পণ্য পৌঁছে দেওয়া হয়।
                </p>
                <p class="mb-0">
                    আমাদের বিশেষ অফার, ডিসকাউন্ট ভাউচার এবং ক্যাশব্যাক সুবিধার মাধ্যমে সেরা কেনাকাটার অভিজ্ঞতা উপভোগ করুন। যে কোনো প্রয়োজনে আমাদের সার্বক্ষণিক কাস্টমার সাপোর্ট টিম আপনার সেবায় নিয়োজিত।
                </p>
            </div>
            <button class="btn btn-link btn-sm p-0 mt-2 text-decoration-none fw-semibold d-inline-flex align-items-center gap-1" 
                    type="button" 
                    data-bs-toggle="collapse" 
                    data-bs-target="#categorySeoBlock" 
                    aria-expanded="false" 
                    aria-controls="categorySeoBlock"
                    onclick="this.querySelector('.btn-toggle-text').innerText = this.getAttribute('aria-expanded') === 'true' ? 'কম দেখুন' : 'আরো পড়ুন'; this.querySelector('i').classList.toggle('fa-chevron-up'); this.querySelector('i').classList.toggle('fa-chevron-down');">
                <span class="btn-toggle-text">আরো পড়ুন</span>
                <i class="fa-solid fa-chevron-down small"></i>
            </button>
        </div>
    </div>
</div>
@endsection