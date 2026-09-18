@extends('frontend.layouts.app')

@section('title', 'সকল প্রোডাক্টস - সেরা গ্যাজেট ও লাইফস্টাইল কালেকশন | ' . ($settings['store_name'] ?? 'ZippyBD'))
@section('meta_description', ($settings['store_name'] ?? 'ZippyBD') . '-এর সকল প্রিমিয়াম গ্যাজেট, মেকানিক্যাল কিবোর্ড, ডেস্ক সেটআপ অ্যাক্সেসরিজ ও অরিজিনাল টেক প্রোডাক্ট ব্রাউজ করুন। সেরা দাম ও দ্রুত হোম ডেলিভারি।')

@section('content')
<div class="container-fluid px-3 px-md-4 px-xl-5 py-4">
    <nav class="small text-secondary mb-3" aria-label="breadcrumb">
        <a href="{{ route('home') }}" class="text-secondary text-decoration-none">হোম</a>
        <span class="mx-1">/</span>
        <span class="text-dark fw-bold">সকল প্রোডাক্টস</span>
    </nav>

    <div class="bg-white rounded-4 border p-3 p-md-4 shadow-sm mb-4">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
            <div>
                <div class="d-flex align-items-center gap-2 mb-2">
                    <span class="badge bg-dark rounded-pill px-3 py-1">
                        <i class="fa-solid fa-boxes-stacked me-1"></i> অল প্রোডাক্ট ক্যাটালগ
                    </span>
                    <span class="badge bg-light text-dark border rounded-pill px-3 py-1 catalog-total-count">
                        মোট {{ $products->total() }} টি পণ্য
                    </span>
                </div>
                <h1 class="h3 fw-bold text-dark font-heading mb-1">সকল প্রোডাক্টস</h1>
                <p class="text-secondary small mb-0">আমাদের স্টোরের সমস্ত প্রিমিয়াম ও ১০০% জেনুইন পণ্য কালেকশন</p>
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

    <div class="row g-3 g-xl-4 items-start">
        <div class="col-12 col-lg-3 col-xl-3">
            @include('frontend.inc.product-filter-sidebar')
        </div>
        <div class="col-12 col-lg-9 col-xl-9" id="productGridWrapper">
            @include('frontend.product.partials.product-grid-container')
        </div>
    </div>
</div>
@endsection
