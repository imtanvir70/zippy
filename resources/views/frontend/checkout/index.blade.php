@extends('frontend.layouts.app')

@php
    $checkoutStoreName = $settings['store_name'] ?? 'ZippyBD';
    $calculatedTotal = $grandTotal ?? $total ?? max(0, ($subtotal ?? 0) + ($shippingCost ?? 60) - ($discount ?? 0));
    $fallbackProdImg = asset('images/product-placeholder.svg');

    $dlCheckoutItems = [];
    $dlContentIds = [];
    if (is_array($cart) && count($cart) > 0) {
        foreach ($cart as $cIt) {
            $pId = (string) (is_array($cIt) ? ($cIt['id'] ?? '') : ($cIt->id ?? ''));
            $pTitle = is_array($cIt) ? ($cIt['title'] ?? ($cIt['name'] ?? 'Product')) : ($cIt->title ?? ($cIt->name ?? 'Product'));
            $pPrice = is_array($cIt) ? (float) ($cIt['price'] ?? 0) : (float) ($cIt->price ?? 0);
            $pQty = is_array($cIt) ? (int) ($cIt['quantity'] ?? ($cIt['qty'] ?? 1)) : (int) ($cIt->quantity ?? ($cIt->qty ?? 1));
            $dlContentIds[] = $pId;
            $dlCheckoutItems[] = [
                'item_id' => $pId,
                'item_name' => $pTitle,
                'price' => $pPrice,
                'quantity' => $pQty
            ];
        }
    }
@endphp

@section('title', 'নিরাপদ চেকআউট ও দ্রুত অর্ডার কনফার্মেশন | ' . $checkoutStoreName)
@section('meta_description', $checkoutStoreName . ' এ ক্যাশ অন ডেলিভারিতে দ্রুত ও সুরক্ষিত চেকআউট করুন। সারাদেশে দ্রুততম ডেলিভারি।')

@section('content')
<div class="zk-checkout-wrapper pb-5 pb-lg-5">
    <div class="container max-w-6xl mx-auto px-3 px-md-4 mt-4">
        <form id="checkoutForm" onsubmit="submitOrder(event)">
            @csrf
            <h1 class="visually-hidden">নিরাপদ চেকআউট - {{ $checkoutStoreName }}</h1>
            <input type="hidden" name="device_token" id="checkoutDeviceToken" value="{{ $deviceToken ?? '' }}">
            <input type="hidden" name="district" id="hiddenDistrictType" value="{{ (($customerDefault->district ?? 'ঢাকা') === 'ঢাকার বাইরে') ? 'ঢাকার বাইরে' : 'ঢাকা' }}">

            <div class="zk-mobile-cart-toggle d-block d-lg-none mb-4">
                <div class="zk-mobile-bar p-3 bg-white rounded-4 border shadow-xs d-flex align-items-center justify-content-between cursor-pointer" onclick="toggleMobileSummary()">
                    <div class="d-flex align-items-center gap-3">
                        <div class="zk-panel-icon bg-black text-white rounded-circle shadow-sm">
                            <i class="fa-solid fa-bag-shopping fs-12"></i>
                        </div>
                        <div>
                            <div class="d-flex align-items-center gap-2">
                                <span class="fs-14 fw-bold text-dark">অর্ডার সারাংশ</span>
                                <span class="badge rounded-pill bg-light text-dark border fs-10 px-2 font-mono checkout-badge-count">{{ is_array($cart) ? count($cart) : 0 }} টি</span>
                            </div>
                            <span class="fs-12 text-muted mt-0.5 d-block">আইটেম ও মূল্য বিস্তারিত দেখুন</span>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <span class="fs-16 fw-extrabold text-black font-mono checkout-total-val">৳ {{ number_format($calculatedTotal, 0) }}</span>
                        <i class="fa-solid fa-chevron-down fs-12 text-muted transition-transform bg-light rounded-circle p-2" id="mobileSummaryChevron"></i>
                    </div>
                </div>
                
                <div class="zk-mobile-summary-body p-3 mt-3 bg-white rounded-4 border shadow-sm d-none" id="mobileSummaryBody">
                    <div class="mb-3 zk-cart-scroll" id="mobileCartItemList">
                        @if(is_array($cart) && count($cart) > 0)
                            @foreach($cart as $mKey => $mItem)
                                @php
                                    $mTitle = is_array($mItem) ? ($mItem['title'] ?? ($mItem['name'] ?? 'প্রোডাক্ট')) : ($mItem->title ?? ($mItem->name ?? 'প্রোডাক্ট'));
                                    $mPrice = is_array($mItem) ? (float)($mItem['price'] ?? 0) : (float)($mItem->price ?? 0);
                                    $mQty = is_array($mItem) ? (int)($mItem['quantity'] ?? ($mItem['qty'] ?? 1)) : (int)($mItem->quantity ?? ($mItem->qty ?? 1));
                                    $mImg = is_array($mItem) ? ($mItem['image'] ?? ($mItem['attributes']['image'] ?? '')) : ($mItem->image ?? '');
                                    $rawMImg = is_array($mItem) ? ($mItem['image'] ?? ($mItem['attributes']['image'] ?? '')) : ($mItem->image ?? '');
                                    $mImg = (!empty($rawMImg) && !str_contains($rawMImg, 'example.com')) ? $rawMImg : $fallbackProdImg;
                                    $mKeyVal = is_array($mItem) ? ($mItem['cart_key'] ?? $mKey) : $mKey;
                                    $mVariant = is_array($mItem) ? ($mItem['variant'] ?? '') : ($mItem->variant ?? '');
                                @endphp
                                <div class="zk-cart-item py-3 border-bottom d-flex align-items-center gap-3" id="mrow-{{ $mKeyVal }}">
                                    <div class="zk-img-box flex-shrink-0" style="width: 56px; height: 56px; border-radius: 10px; overflow: hidden; border: 1px solid #f1f5f9;">
                                        @if(!empty($mImg))
                                            <img src="{{ $mImg }}" alt="{{ $mTitle }}" class="zk-prod-thumb w-100 h-100 object-fit-cover" onerror="this.onerror=null;this.src='{{ $fallbackProdImg }}';">
                                        @else
                                            <div class="zk-prod-thumb d-flex align-items-center justify-content-center text-muted bg-light w-100 h-100">
                                                <i class="fa-solid fa-image fs-15"></i>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="overflow-hidden min-w-0 flex-grow-1">
                                        <div class="d-flex align-items-center justify-content-between gap-2 mb-2 min-w-0">
                                            <h4 class="fs-13 fw-semibold text-dark text-truncate mb-0" title="{{ $mTitle }}">{{ $mTitle }}</h4>
                                            @if(!empty($mVariant))
                                                <span class="badge bg-light text-secondary border fw-medium flex-shrink-0 d-inline-flex align-items-center" style="font-size: 11px; padding: 2px 7px; border-radius: 12px;">
                                                    <i class="fa-solid fa-tag me-1 text-muted" style="font-size: 8px;"></i>{{ $mVariant }}
                                                </span>
                                            @endif
                                        </div>
                                        <div class="d-flex align-items-center justify-content-between gap-2 pt-0.5">
                                            <div class="d-flex align-items-center gap-2 min-w-0">
                                                <span class="fs-14 fw-bold text-dark font-mono">৳ {{ number_format($mPrice, 0) }}</span>
                                                <span class="fs-11 text-muted font-mono bg-light px-2 py-0.5 rounded-pill">× <span id="munit-qty-{{ $mKeyVal }}">{{ $mQty }}</span></span>
                                            </div>
                                            <div class="d-flex align-items-center gap-2 flex-shrink-0">
                                                <div class="zk-counter d-flex align-items-center" style="padding: 2px;">
                                                    <button type="button" class="zk-counter-btn" style="width: 26px; height: 26px;" onclick="alterItemQty('{{ $mKeyVal }}', -1)">
                                                        <i class="fa-solid fa-minus" style="font-size: 8px;"></i>
                                                    </button>
                                                    <span class="zk-counter-num font-mono" style="width: 26px; font-size: 13px;" id="mqty-{{ $mKeyVal }}">{{ $mQty }}</span>
                                                    <button type="button" class="zk-counter-btn" style="width: 26px; height: 26px;" onclick="alterItemQty('{{ $mKeyVal }}', 1)">
                                                        <i class="fa-solid fa-plus" style="font-size: 8px;"></i>
                                                    </button>
                                                </div>
                                                <button type="button" class="zk-trash-btn" style="width: 28px; height: 28px;" onclick="deleteItem('{{ $mKeyVal }}')">
                                                    <i class="fa-regular fa-trash-can" style="font-size: 11px;"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        @endif
                    </div>
                    <div class="zk-calc-box p-3 rounded-4 bg-light border">
                        <div class="d-flex justify-content-between align-items-center fs-13 text-muted mb-2">
                            <span>সাবটোটাল</span>
                            <span class="text-dark fw-bold font-mono checkout-subtotal-val">৳ {{ number_format($subtotal ?? 0, 0) }}</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center fs-13 text-muted mb-2">
                            <span>ডেলিভারি চার্জ</span>
                            <span class="text-dark fw-bold font-mono checkout-shipping-val">{{ ($shippingCost ?? 60) == 0 ? 'ফ্রি' : '৳ ' . number_format($shippingCost ?? 60, 0) }}</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center fs-13 text-dark fw-bold mb-2 {{ empty($discount) ? 'd-none' : '' }} checkout-discount-row">
                            <span>কুপন ছাড়</span>
                            <span class="fw-bold font-mono checkout-discount-val">- ৳ {{ number_format($discount ?? 0, 0) }}</span>
                        </div>
                        <div class="zk-calc-divider my-3"></div>
                        <div class="d-flex justify-content-between align-items-center pt-1">
                            <div>
                                <span class="fs-14 fw-bold text-dark d-block mb-0.5">সর্বমোট প্রদেয়</span>
                                <span class="fs-11 text-muted">ভ্যাট সহ মোট মূল্য</span>
                            </div>
                            <span class="fs-18 fw-extrabold text-black font-mono checkout-total-val">৳ {{ number_format($calculatedTotal, 0) }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-4 pb-5 pb-lg-0 mb-5 mb-lg-0">
                <div class="col-12 col-lg-7">
                    <div class="zk-card p-4 p-md-5 mb-4 rounded-4 shadow-custom border">
                        <div class="zk-stepper-wrap mb-4 pb-4 border-bottom">
                            <div class="zk-stepper d-flex align-items-center justify-content-between px-2">
                                <div class="zk-step-item zk-step-active d-flex flex-column flex-sm-row align-items-center gap-2.5 cursor-pointer" id="stepIndicator1" onclick="switchStep(1)">
                                    <span class="zk-step-num">১</span>
                                    <div class="zk-step-text text-center text-sm-start">
                                        <span class="zk-step-title">ঠিকানা</span>
                                        <span class="zk-step-sub d-none d-sm-block">ডেলিভারি এরিয়া</span>
                                    </div>
                                </div>
                                <div class="zk-step-divider flex-grow-1 mx-2 mx-sm-3" id="stepDivider1"></div>
                                <div class="zk-step-item d-flex flex-column flex-sm-row align-items-center gap-2.5 cursor-pointer" id="stepIndicator2" onclick="switchStep(2)">
                                    <span class="zk-step-num">২</span>
                                    <div class="zk-step-text text-center text-sm-start">
                                        <span class="zk-step-title">পেমেন্ট</span>
                                        <span class="zk-step-sub d-none d-sm-block">মেথড বাছুন</span>
                                    </div>
                                </div>
                                <div class="zk-step-divider flex-grow-1 mx-2 mx-sm-3" id="stepDivider2"></div>
                                <div class="zk-step-item d-flex flex-column flex-sm-row align-items-center gap-2.5 cursor-pointer" id="stepIndicator3" onclick="switchStep(3)">
                                    <span class="zk-step-num">৩</span>
                                    <div class="zk-step-text text-center text-sm-start">
                                        <span class="zk-step-title">রিভিউ</span>
                                        <span class="zk-step-sub d-none d-sm-block">অর্ডার নিশ্চিত</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div id="stepPanel1" class="zk-step-panel">
                            <div class="d-flex align-items-center justify-content-between mb-4 pb-2">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="zk-panel-icon bg-black text-white rounded-circle shadow-sm">
                                        <i class="fa-solid fa-location-dot fs-14"></i>
                                    </div>
                                    <div>
                                        <h2 class="fs-16 fw-bold text-dark mb-0.5">গ্রাহক ও ডেলিভারি ঠিকানা</h2>
                                        <span class="fs-12 text-muted">সঠিক তথ্য দিয়ে দ্রুত ডেলিভারি নিশ্চিত করুন</span>
                                    </div>
                                </div>
                            </div>

                            <div class="row g-3.5 mb-4">
                                <div class="col-12 col-md-6">
                                    <label class="form-label fs-13 fw-semibold text-dark mb-2">আপনার পুরো নাম <span class="text-black fw-bold">*</span></label>
                                    <div class="input-group zk-input-group rounded-3">
                                        <span class="input-group-text bg-white border-end-0 text-muted ps-3.5 pe-2">
                                            <i class="fa-solid fa-user fs-13"></i>
                                        </span>
                                        <input type="text" name="customer_name" id="customer_name" class="form-control zk-input border-start-0 ps-1" placeholder="যেমন: তানভীর আহমেদ" value="{{ old('customer_name', $customerDefault->name ?? '') }}" required oninput="handleCustomerInfoChange()">
                                    </div>
                                </div>
                                <div class="col-12 col-md-6">
                                    <label class="form-label fs-13 fw-semibold text-dark mb-2">মোবাইল নম্বর <span class="text-black fw-bold">*</span></label>
                                    <div class="input-group zk-input-group rounded-3">
                                        <span class="input-group-text bg-white border-end-0 text-muted ps-3.5 pe-2">
                                            <i class="fa-solid fa-phone fs-13"></i>
                                        </span>
                                        <input type="tel" name="customer_phone" id="customer_phone" class="form-control zk-input font-mono border-start-0 ps-1" placeholder="017XXXXXXXX" value="{{ old('customer_phone', $customerDefault->phone ?? '') }}" required oninput="handleCustomerInfoChange()">
                                    </div>
                                </div>
                            </div>

                            <div class="row g-3.5 mb-4">
                                <div class="col-12 col-md-4">
                                    <label class="form-label fs-13 fw-semibold text-dark mb-2">বিভাগ <span class="text-black fw-bold">*</span></label>
                                    <div class="rounded-3">
                                        <select class="form-select zk-select" id="geoDivisionSelect" name="division_name" required onchange="if(window.onDivisionChange) window.onDivisionChange(this.value, false)">
                                            <option value="">বিভাগ বাছুন</option>
                                            @if(!empty($divisions))
                                                @foreach($divisions as $div)
                                                    <option value="{{ data_get($div, 'id') }}" data-bn="{{ data_get($div, 'bn_name') }}" data-en="{{ data_get($div, 'name') }}">{{ data_get($div, 'bn_name') }} ({{ data_get($div, 'name') }})</option>
                                                @endforeach
                                            @endif
                                        </select>
                                    </div>
                                </div>
                                <div class="col-12 col-md-4">
                                    <label class="form-label fs-13 fw-semibold text-dark mb-2">জেলা <span class="text-black fw-bold">*</span></label>
                                    <div class="rounded-3">
                                        <select class="form-select zk-select" id="geoDistrictSelect" name="district_name" required disabled onchange="if(window.onDistrictChange) window.onDistrictChange(this.value, false)">
                                            <option value="">আগে বিভাগ বাছুন</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-12 col-md-4">
                                    <label class="form-label fs-13 fw-semibold text-dark mb-2">থানা / উপজেলা <span class="text-black fw-bold">*</span></label>
                                    <div class="rounded-3">
                                        <select class="form-select zk-select" id="geoUpazilaSelect" name="upazila_name" required disabled onchange="if(window.onUpazilaChange) window.onUpazilaChange(this.value)">
                                            <option value="">আগে জেলা বাছুন</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label class="form-label fs-13 fw-semibold text-dark mb-2 d-flex align-items-center gap-2">
                                    <i class="fa-solid fa-house-chimney text-muted fs-12"></i>
                                    <span>সঠিক ও বিস্তারিত ঠিকানা (রোড, বাড়ি, ফ্ল্যাট)</span>
                                    <span class="text-black fw-bold">*</span>
                                </label>
                                @php
                                    $initCleanAddr = $customerDefault->address ?? '';
                                    if (!empty($initCleanAddr)) {
                                        if (preg_match('/ঠিকানা:\s*(.*)/u', $initCleanAddr, $mMatches)) {
                                            $initCleanAddr = $mMatches[1];
                                        }
                                        $initCleanAddr = preg_replace('/(বিভাগ|জেলা|থানা|উপজেলা)\s*:\s*[^,]+,?/u', '', $initCleanAddr);
                                        $addrParts = array_filter(array_map('trim', explode(',', $initCleanAddr)));
                                        $dedupedAddrParts = [];
                                        foreach ($addrParts as $ap) {
                                            if (!in_array(mb_strtolower($ap), array_map('mb_strtolower', $dedupedAddrParts), true)) {
                                                $dedupedAddrParts[] = $ap;
                                            }
                                        }
                                        $initCleanAddr = implode(', ', $dedupedAddrParts);
                                    }
                                @endphp
                                <textarea name="customer_address" id="customer_address" rows="3" maxlength="400" class="form-control zk-textarea shadow-none" placeholder="যেমন: বাড়ি # ১২, রোড # ৪, ব্লক # বি, সেক্টর # ৭, মিরপুর..." required oninput="handleCustomerInfoChange()">{{ old('customer_address', $initCleanAddr) }}</textarea>
                                <div class="fs-12 text-muted d-flex align-items-center gap-2 mt-2">
                                    <i class="fa-solid fa-circle-info text-dark fs-12 flex-shrink-0"></i>
                                    <span>ডেলিভারি রাইডারের সুবিধার্থে নির্ভুল রোড নম্বর ও বাড়ি নম্বর উল্লেখ করুন।</span>
                                </div>
                            </div>

                            <div class="d-none d-lg-flex justify-content-end mt-4 pt-2">
                                <button type="button" class="btn zk-btn-primary px-5 py-3.5 d-inline-flex align-items-center justify-content-center gap-2.5 fw-semibold rounded-pill" onclick="goToStep(2)">
                                    <span>পরবর্তী ধাপ (পেমেন্ট)</span>
                                    <i class="fa-solid fa-arrow-right fs-12"></i>
                                </button>
                            </div>
                        </div>

                        <div id="stepPanel2" class="zk-step-panel d-none">
                            <div class="d-flex align-items-center justify-content-between mb-4 pb-2">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="zk-panel-icon bg-black text-white rounded-circle shadow-sm">
                                        <i class="fa-solid fa-credit-card fs-14"></i>
                                    </div>
                                    <div>
                                        <h2 class="fs-16 fw-bold text-dark mb-0.5">পেমেন্ট মেথড নির্বাচন</h2>
                                        <span class="fs-12 text-muted">আপনার সুবিধাজনক পেমেন্ট পদ্ধতি বেছে নিন</span>
                                    </div>
                                </div>
                            </div>

                            <div class="row g-3.5 mb-4">
                                <div class="col-12 col-md-6">
                                    <label class="zk-radio-card zk-radio-card-active d-block cursor-pointer p-4 rounded-4 h-100 border shadow-xs" for="payMethodCod">
                                        <div class="d-flex align-items-start gap-3">
                                            <input class="form-check-input mt-1 zk-check-input" type="radio" name="payment_method" id="payMethodCod" value="cod" checked onchange="handleCustomerInfoChange()">
                                            <div class="flex-grow-1">
                                                <div class="d-flex align-items-center justify-content-between mb-2">
                                                    <span class="fw-bold text-dark fs-15">ক্যাশ অন ডেলিভারি</span>
                                                    <span class="badge bg-black text-white fs-10 px-2.5 py-1 rounded-pill fw-semibold font-mono">সক্রিয়</span>
                                                </div>
                                                <p class="fs-13 text-muted mb-0 lh-base">পণ্য হাতে পেয়ে দেখে মূল্য পরিশোধ করুন</p>
                                            </div>
                                        </div>
                                    </label>
                                </div>
                                <div class="col-12 col-md-6">
                                    <label class="zk-radio-card zk-radio-disabled d-block p-4 border rounded-4 h-100 shadow-xs" for="payMethodBkash" onclick="onBkashClick(event)">
                                        <div class="d-flex align-items-start gap-3">
                                            <input class="form-check-input mt-1 zk-check-input" type="radio" name="payment_method" id="payMethodBkash" value="bkash" disabled>
                                            <div class="flex-grow-1">
                                                <div class="d-flex align-items-center justify-content-between mb-2">
                                                    <span class="fw-bold text-dark fs-15">বিকাশ পেমেন্ট</span>
                                                    <span class="badge bg-light text-muted border fs-10 px-2.5 py-1 rounded-pill fw-semibold">শীঘ্রই আসছে</span>
                                                </div>
                                                <p class="fs-13 text-muted mb-0 lh-base">বিকাশ গেটওয়ে সিস্টেম আপডেট চলছে</p>
                                            </div>
                                        </div>
                                    </label>
                                </div>
                            </div>

                            <div class="p-4 rounded-4 zk-zone-notice mb-4 border">
                                <div class="d-flex align-items-center gap-2.5 mb-2">
                                    <i class="fa-solid fa-truck-fast text-black fs-15" style="margin-right: 0.5rem;"></i>
                                    <span class="fw-bold text-dark fs-14">ডেলিভারি চার্জ সংক্রান্ত তথ্য</span>
                                </div>
                                <p class="fs-13 text-muted mb-0 lh-base">
                                    আপনার নির্বাচিত জেলা অনুযায়ী ঢাকা সিটিতে ৳ {{ number_format($shippingInside ?? 60, 0) }} এবং ঢাকার বাইরে ৳ {{ number_format($shippingOutside ?? 120, 0) }} ডেলিভারি চার্জ প্রযোজ্য।
                                </p>
                            </div>

                            @if(!empty($orderBump))
                                <div class="zk-bump-card p-3 mb-4 rounded-4 border shadow-xs d-block d-lg-none" style="border: 2px dashed #000 !important; background: #fffcf5 !important;">
                                    <div class="d-flex align-items-start gap-3">
                                        <div class="form-check mt-1">
                                            <input class="form-check-input zk-bump-checkbox" type="checkbox" id="include_bump_mobile" value="1" onchange="syncBumpCheckboxes(this)" style="cursor: pointer; width: 1.35em; height: 1.35em;">
                                        </div>
                                        <div class="flex-shrink-0">
                                            <img src="{{ !empty($orderBump->main_image) ? $orderBump->main_image : $fallbackProdImg }}" alt="{{ $orderBump->bump_product_title }}" class="rounded-3 border" style="width: 52px; height: 52px; object-fit: cover;" onerror="this.onerror=null;this.src='{{ $fallbackProdImg }}';">
                                        </div>
                                        <div class="flex-grow-1 min-w-0">
                                            <div class="d-flex align-items-center gap-2 mb-1 flex-wrap">
                                                <span class="badge bg-danger text-white fs-10 px-2 py-0.5 rounded-pill fw-bold">স্পেশাল অফার</span>
                                                <span class="fs-13 fw-bold text-dark text-truncate">{{ $orderBump->title }}</span>
                                            </div>
                                            <div class="d-flex align-items-center gap-2">
                                                <span class="fs-14 fw-bold text-dark font-mono">মাত্র ৳ {{ number_format($orderBump->price, 0) }}</span>
                                                @if($orderBump->original_price > $orderBump->price)
                                                    <del class="fs-12 text-muted font-mono">৳ {{ number_format($orderBump->original_price, 0) }}</del>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <div class="d-none d-lg-flex justify-content-between align-items-center gap-3">
                                <button type="button" class="btn zk-btn-outline px-4.5 py-3 rounded-pill d-inline-flex align-items-center gap-2 fs-14 fw-semibold" onclick="goToStep(1)">
                                    <i class="fa-solid fa-arrow-left fs-12"></i>
                                    <span>আগের ধাপ</span>
                                </button>
                                <button type="button" class="btn zk-btn-primary px-5 py-3.5 rounded-pill flex-fill d-inline-flex align-items-center justify-content-center gap-2.5 fw-semibold" onclick="goToStep(3)">
                                    <span>পরবর্তী ধাপ (রিভিউ)</span>
                                    <i class="fa-solid fa-arrow-right fs-12"></i>
                                </button>
                            </div>
                        </div>

                        <div id="stepPanel3" class="zk-step-panel d-none">
                            <div class="d-flex align-items-center justify-content-between mb-4 pb-2">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="zk-panel-icon bg-black text-white rounded-circle shadow-sm">
                                        <i class="fa-solid fa-clipboard-check fs-14"></i>
                                    </div>
                                    <div>
                                        <h2 class="fs-16 fw-bold text-dark mb-0.5">অর্ডার ও ডেলিভারি তথ্য যাচাই</h2>
                                        <span class="fs-12 text-muted">তথ্যগুলো ঠিক আছে কিনা একনজরে মিলিয়ে নিন</span>
                                    </div>
                                </div>
                            </div>

                            <div class="zk-review-card bg-white rounded-4 border p-4 mb-4 shadow-xs">
                                <div class="d-flex align-items-center justify-content-between pb-3 mb-3 border-bottom">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="zk-panel-icon bg-light text-dark rounded-circle border">
                                            <i class="fa-solid fa-location-dot fs-13"></i>
                                        </div>
                                        <div>
                                            <h3 class="fs-15 fw-bold text-dark mb-0">ডেলিভারি ও প্রাপকের বিবরণ</h3>
                                        </div>
                                    </div>
                                    <button type="button" class="btn btn-sm zk-btn-edit py-1.5 px-3.5 d-inline-flex align-items-center gap-1.5 rounded-pill shadow-xs" onclick="goToStep(1)">
                                        <i class="fa-solid fa-pen-to-square fs-11"></i>
                                        <span>পরিবর্তন</span>
                                    </button>
                                </div>
                                <div class="row g-3">
                                    <div class="col-12 col-md-6">
                                        <div class="p-3 rounded-3 bg-light border h-100">
                                            <div class="fs-11 text-muted fw-bold text-uppercase mb-2 d-flex align-items-center gap-1.5">
                                                <i class="fa-regular fa-user text-dark fs-11"></i>
                                                <span>প্রাপকের তথ্য</span>
                                            </div>
                                            <div class="fs-14 fw-bold text-dark mb-1" id="revName">-</div>
                                            <div class="fs-13 text-muted font-mono d-flex align-items-center gap-1.5">
                                                <i class="fa-solid fa-phone text-muted fs-11"></i>
                                                <span id="revPhone">-</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <div class="p-3 rounded-3 bg-light border h-100">
                                            <div class="fs-11 text-muted fw-bold text-uppercase mb-2 d-flex align-items-center gap-1.5">
                                                <i class="fa-solid fa-map-location-dot text-dark fs-11"></i>
                                                <span>ডেলিভারি ঠিকানা</span>
                                            </div>
                                            <div class="fs-13 fw-semibold text-dark mb-2 lh-base" id="revStreetDetail">-</div>
                                            <div class="d-flex flex-wrap align-items-center gap-1">
                                                <span class="badge bg-white text-dark border px-2 py-0.5 fs-11" id="revUpazilaBadge">-</span>
                                                <span class="badge bg-white text-dark border px-2 py-0.5 fs-11" id="revDistBadge">-</span>
                                                <span class="badge bg-white text-muted border px-2 py-0.5 fs-11" id="revDivBadge">-</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row g-3.5 mb-4">
                                <div class="col-12 col-md-6">
                                    <div class="p-4 rounded-4 bg-light border h-100 shadow-xs">
                                        <div class="d-flex align-items-center justify-content-between mb-3">
                                            <span class="fs-11 text-muted fw-bold text-uppercase d-flex align-items-center gap-2">
                                                <i class="fa-solid fa-truck-fast text-dark fs-13"></i>
                                                <span>ডেলিভারি এরিয়া</span>
                                            </span>
                                            <span class="badge bg-black text-white font-mono fs-12 px-3 py-1.5 rounded-pill" id="revZoneFeeBadge">৳ ৬০</span>
                                        </div>
                                        <div class="fs-15 fw-bold text-dark" id="revZoneText">ঢাকা সিটির ভেতরে</div>
                                    </div>
                                </div>
                                <div class="col-12 col-md-6">
                                    <div class="p-4 rounded-4 bg-light border h-100 shadow-xs">
                                        <div class="d-flex align-items-center justify-content-between mb-3">
                                            <span class="fs-11 text-muted fw-bold text-uppercase d-flex align-items-center gap-2">
                                                <i class="fa-solid fa-wallet text-dark fs-13"></i>
                                                <span>পেমেন্ট মেথড</span>
                                            </span>
                                            <span class="badge bg-black text-white fs-10 px-2.5 py-1 rounded-pill fw-semibold font-mono">সক্রিয়</span>
                                        </div>
                                        <div class="fs-15 fw-bold text-dark" id="revPaymentTitle">ক্যাশ অন ডেলিভারি</div>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label class="form-label fs-13 fw-semibold text-dark mb-2 d-flex align-items-center gap-2">
                                    <i class="fa-regular fa-comment-dots text-muted fs-13"></i>
                                    <span>অর্ডার সংক্রান্ত বিশেষ নির্দেশনা (ঐচ্ছিক)</span>
                                </label>
                                <textarea name="notes" id="order_notes" rows="2" maxlength="500" class="form-control zk-textarea zk-textarea-compact p-3.5 shadow-none border" placeholder="ডেলিভারির জন্য কোনো বিশেষ নির্দেশনা থাকলে লিখুন..."></textarea>
                            </div>

                            <div class="d-flex align-items-center justify-content-between gap-3 pt-3 border-top">
                                <button type="button" class="btn zk-btn-outline px-4.5 py-3 rounded-pill d-inline-flex align-items-center gap-2 fs-14 fw-semibold" onclick="goToStep(2)">
                                    <i class="fa-solid fa-arrow-left fs-12"></i>
                                    <span>আগের ধাপ</span>
                                </button>
                                <div class="text-muted fs-12 fw-medium d-none d-lg-block">
                                    <i class="fa-solid fa-circle-info text-dark me-1"></i> ডানপাশের প্যানেল থেকে অর্ডার কনফার্ম করুন
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-lg-5" id="checkoutSidebarCol">
                    <div class="zk-card p-4 p-md-5 rounded-4 shadow-custom border zk-sticky-sidebar d-none d-lg-block">
                        <div class="d-flex align-items-center justify-content-between mb-4 pb-3 border-bottom">
                            <div class="d-flex align-items-center gap-3">
                                <div class="zk-panel-icon bg-black text-white rounded-circle shadow-sm">
                                    <i class="fa-solid fa-bag-shopping fs-14"></i>
                                </div>
                                <h3 class="fs-16 fw-bold text-dark mb-0">অর্ডারকৃত পণ্য</h3>
                            </div>
                            <span class="badge rounded-pill bg-light text-dark border fs-12 px-3 py-1.5 font-mono checkout-badge-count" id="badgeCartCount">{{ is_array($cart) ? count($cart) : 0 }} টি</span>
                        </div>

                        <div class="zk-cart-scroll mb-4 pe-2" id="checkoutCartList">
                            @if(is_array($cart) && count($cart) > 0)
                                @foreach($cart as $itemKey => $item)
                                    @php
                                        $pTitle = is_array($item) ? ($item['title'] ?? ($item['name'] ?? 'প্রোডাক্ট')) : ($item->title ?? ($item->name ?? 'প্রোডাক্ট'));
                                        $pPrice = is_array($item) ? (float)($item['price'] ?? 0) : (float)($item->price ?? 0);
                                        $pQty = is_array($item) ? (int)($item['quantity'] ?? ($item['qty'] ?? 1)) : (int)($item->quantity ?? ($item->qty ?? 1));
                                        $pImg = is_array($item) ? ($item['image'] ?? ($item['attributes']['image'] ?? '')) : ($item->image ?? '');
                                        $rawPImg = is_array($item) ? ($item['image'] ?? ($item['attributes']['image'] ?? '')) : ($item->image ?? '');
                                        $pImg = (!empty($rawPImg) && !str_contains($rawPImg, 'example.com')) ? $rawPImg : $fallbackProdImg;
                                        $itemKeyVal = is_array($item) ? ($item['cart_key'] ?? $itemKey) : $itemKey;
                                        $pVariant = is_array($item) ? ($item['variant'] ?? '') : ($item->variant ?? '');
                                    @endphp
                                    <div class="zk-cart-item py-3 border-bottom d-flex align-items-center gap-3" id="row-{{ $itemKeyVal }}">
                                        <div class="zk-img-box flex-shrink-0" style="width: 58px; height: 58px; border-radius: 10px; overflow: hidden; border: 1px solid #f1f5f9;">
                                            @if(!empty($pImg))
                                                <img src="{{ $pImg }}" alt="{{ $pTitle }}" class="zk-prod-thumb w-100 h-100 object-fit-cover" onerror="this.onerror=null;this.src='{{ $fallbackProdImg }}';">
                                            @else
                                                <div class="zk-prod-thumb d-flex align-items-center justify-content-center text-muted bg-light w-100 h-100">
                                                    <i class="fa-solid fa-image fs-16"></i>
                                                </div>
                                            @endif
                                        </div>
                                        <div class="overflow-hidden min-w-0 flex-grow-1">
                                            <div class="d-flex align-items-center justify-content-between gap-2 mb-2 min-w-0">
                                                <h4 class="fs-13 fw-semibold text-dark text-truncate mb-0" title="{{ $pTitle }}">{{ $pTitle }}</h4>
                                                @if(!empty($pVariant))
                                                    <span class="badge bg-light text-secondary border fw-medium flex-shrink-0 d-inline-flex align-items-center" style="font-size: 11px; padding: 2px 8px; border-radius: 14px;">
                                                        <i class="fa-solid fa-tag me-1 text-muted" style="font-size: 8px;"></i>{{ $pVariant }}
                                                    </span>
                                                @endif
                                            </div>
                                            <div class="d-flex align-items-center justify-content-between gap-2 pt-0.5">
                                                <div class="d-flex align-items-center gap-2 min-w-0">
                                                    <span class="fs-14 fw-bold text-dark font-mono">৳ {{ number_format($pPrice, 0) }}</span>
                                                    <span class="fs-11 text-muted font-mono bg-light px-2 py-0.5 rounded-pill">× <span id="unit-qty-{{ $itemKeyVal }}">{{ $pQty }}</span></span>
                                                </div>
                                                <div class="d-flex align-items-center gap-2 flex-shrink-0">
                                                    <div class="zk-counter d-flex align-items-center">
                                                        <button type="button" class="zk-counter-btn" onclick="alterItemQty('{{ $itemKeyVal }}', -1)">
                                                            <i class="fa-solid fa-minus fs-10"></i>
                                                        </button>
                                                        <span class="zk-counter-num font-mono fs-13" id="qty-{{ $itemKeyVal }}">{{ $pQty }}</span>
                                                        <button type="button" class="zk-counter-btn" onclick="alterItemQty('{{ $itemKeyVal }}', 1)">
                                                            <i class="fa-solid fa-plus fs-10"></i>
                                                        </button>
                                                    </div>
                                                    <button type="button" class="zk-trash-btn" onclick="deleteItem('{{ $itemKeyVal }}')" title="মুছুন">
                                                        <i class="fa-regular fa-trash-can fs-13"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            @else
                                <div class="text-center py-5 text-muted fs-14">কার্টে কোনো পণ্য নেই</div>
                            @endif
                        </div>

                        <div class="mb-4">
                            <div class="input-group zk-input-group shadow-xs rounded-4 border {{ !empty($coupon) ? 'd-none' : '' }}" id="couponInputGroup">
                                <span class="input-group-text bg-white border-end-0 text-muted ps-3.5 pe-2">
                                    <i class="fa-solid fa-ticket fs-14"></i>
                                </span>
                                <input type="text" id="checkoutCouponInput" class="form-control zk-input border-start-0 ps-1 uppercase" placeholder="কুপন কোড লিখুন">
                                <button type="button" class="btn zk-btn-primary px-4 fw-semibold" id="applyCouponBtn" onclick="applyCheckoutCoupon()">প্রয়োগ</button>
                            </div>
                            <div id="couponFeedback" class="d-none"></div>

                            @if(!empty($availableCoupons) && count($availableCoupons) > 0)
                                <div class="mt-3 {{ !empty($coupon) ? 'd-none' : '' }}" id="suggestedCouponsWrap">
                                    <div class="fs-12 fw-semibold text-muted mb-2 d-flex align-items-center gap-2">
                                        <i class="fa-solid fa-tags fs-12"></i>
                                        <span>উপলব্ধ কুপন কোড:</span>
                                    </div>
                                    <div class="d-flex flex-wrap gap-2">
                                        @foreach($availableCoupons as $ac)
                                            @php
                                                $isPercent = $ac->type === 'percent';
                                                $benefit = $isPercent ? ((float)$ac->value . '% ছাড়') : ('৳ ' . number_format((float)$ac->value, 0) . ' ছাড়');
                                                $minAmt = (float)$ac->min_order_amount;
                                            @endphp
                                            <button type="button" class="btn btn-sm zk-coupon-pill text-start shadow-xs" onclick="applyCheckoutCoupon('{{ $ac->code }}')">
                                                <span class="badge bg-black text-white font-mono uppercase px-2 py-1 fs-11">{{ $ac->code }}</span>
                                                <span class="fw-bold text-dark fs-12 ms-2">{{ $benefit }}</span>
                                            </button>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            <div class="zk-coupon-badge p-3 rounded-4 align-items-center justify-content-between shadow-xs border {{ empty($coupon) ? 'd-none' : '' }}" id="couponAppliedBadge" style="display: {{ !empty($coupon) ? 'flex' : 'none' }};">
                                <div class="d-flex align-items-center gap-3 overflow-hidden">
                                    <div class="zk-panel-icon bg-white text-dark rounded-circle border flex-shrink-0">
                                        <i class="fa-solid fa-tag fs-13"></i>
                                    </div>
                                    <div class="overflow-hidden">
                                        <span class="fw-bold text-dark fs-13 uppercase font-mono text-truncate d-block mb-0.5" id="appliedCouponCodeText">{{ $coupon['code'] ?? '' }}</span>
                                        <span class="badge bg-black text-white fs-11 font-mono" id="appliedCouponDiscountText">সাশ্রয়: ৳ {{ number_format($discount ?? 0, 0) }}</span>
                                    </div>
                                </div>
                                <button type="button" class="btn btn-sm bg-white text-dark shadow-xs rounded-circle p-0 d-flex align-items-center justify-content-center border flex-shrink-0" onclick="removeCheckoutCoupon()" style="width: 32px; height: 32px; min-width: 32px;">
                                    <i class="fa-solid fa-xmark fs-13"></i>
                                </button>
                            </div>
                        </div>

                        @if(!empty($orderBump))
                            <div class="zk-bump-card p-3 mb-4 rounded-4 border shadow-xs position-relative" style="border: 2px dashed #000 !important; background: #fffcf5 !important;">
                                <div class="d-flex align-items-start gap-3">
                                    <div class="form-check mt-1">
                                        <input class="form-check-input zk-bump-checkbox" type="checkbox" name="include_bump" id="include_bump" value="1" onchange="toggleOrderBump(this)" style="cursor: pointer; width: 1.35em; height: 1.35em;">
                                        <input type="hidden" name="bump_id" value="{{ $orderBump->id }}">
                                    </div>
                                    <div class="flex-shrink-0">
                                        <img src="{{ !empty($orderBump->main_image) ? $orderBump->main_image : $fallbackProdImg }}" alt="{{ $orderBump->bump_product_title }}" class="rounded-3 border" style="width: 56px; height: 56px; object-fit: cover;" onerror="this.onerror=null;this.src='{{ $fallbackProdImg }}';">
                                    </div>
                                    <div class="flex-grow-1 min-w-0">
                                        <div class="d-flex align-items-center gap-2 mb-1 flex-wrap">
                                            <span class="badge bg-danger text-white fs-10 px-2 py-0.5 rounded-pill fw-bold">এককালীন স্পেশাল অফার</span>
                                            <span class="fs-13 fw-bold text-dark text-truncate">{{ $orderBump->title }}</span>
                                        </div>
                                        @if(!empty($orderBump->description))
                                            <p class="fs-12 text-muted mb-1.5 lh-sm">{{ $orderBump->description }}</p>
                                        @endif
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="fs-14 fw-bold text-dark font-mono">মাত্র ৳ {{ number_format($orderBump->price, 0) }}</span>
                                            @if($orderBump->original_price > $orderBump->price)
                                                <del class="fs-12 text-muted font-mono">৳ {{ number_format($orderBump->original_price, 0) }}</del>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <div class="zk-calc-box p-4 rounded-4 bg-light border mb-4">
                            <div class="d-flex justify-content-between align-items-center fs-14 text-muted mb-2.5">
                                <span>সাবটোটাল</span>
                                <span class="text-dark fw-bold font-mono checkout-subtotal-val">৳ {{ number_format($subtotal ?? 0, 0) }}</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center fs-14 text-dark fw-bold mb-2.5 d-none checkout-bump-row">
                                <span>অর্ডার বাম্প অফার</span>
                                <span class="fw-bold font-mono checkout-bump-val">+ ৳ {{ !empty($orderBump) ? number_format($orderBump->price, 0) : 0 }}</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center fs-14 text-muted mb-2.5">
                                <span>ডেলিভারি চার্জ</span>
                                <span class="text-dark fw-bold font-mono checkout-shipping-val">{{ ($shippingCost ?? 60) == 0 ? 'ফ্রি' : '৳ ' . number_format($shippingCost ?? 60, 0) }}</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center fs-14 text-dark fw-bold mb-2.5 {{ empty($discount) ? 'd-none' : '' }} checkout-discount-row">
                                <span>কুপন ছাড়</span>
                                <span class="fw-bold font-mono checkout-discount-val">- ৳ {{ number_format($discount ?? 0, 0) }}</span>
                            </div>
                            <div class="zk-calc-divider my-3"></div>
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="fs-16 fw-bold text-dark mb-0.5">সর্বমোট প্রদেয়</div>
                                    <div class="fs-11 text-muted">ভ্যাট সহ মোট প্রদেয়</div>
                                </div>
                                <div class="fs-22 fw-extrabold text-black font-mono checkout-total-val">৳ {{ number_format($calculatedTotal, 0) }}</div>
                            </div>
                        </div>

                        <button type="submit" id="btnConfirmOrder" class="btn zk-btn-primary w-100 py-4 fw-bold fs-16 d-flex align-items-center justify-content-center gap-2.5 shadow-sm rounded-pill">
                            <span>অর্ডার নিশ্চিত করুন</span>
                            <i class="fa-solid fa-circle-check fs-14"></i>
                        </button>

                        <div class="text-center mt-3">
                            <span class="fs-12 text-muted d-inline-flex align-items-center gap-2 bg-light px-3 py-1.5 rounded-pill border">
                                <i class="fa-solid fa-lock text-dark fs-12"></i>
                                <span>নিরাপদ ও এনক্রিপ্টেড চেকআউট</span>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="zk-mobile-bottom-bar d-lg-none shadow-lg">
                <div class="d-flex align-items-center justify-content-between gap-2 w-100">
                    <button type="button" class="btn-mobile-back" onclick="handleMobileBack()" aria-label="পিছনে যান" title="Back">
                        <i class="fa-solid fa-arrow-left"></i>
                    </button>
                    <div class="d-flex flex-column pe-1 flex-shrink-0">
                        <span class="text-muted fw-semibold" style="font-size: 0.68rem; line-height: 1;">সর্বমোট:</span>
                        <span class="fs-16 fw-extrabold text-black font-mono checkout-total-val text-nowrap">৳ {{ number_format($calculatedTotal, 0) }}</span>
                    </div>
                    <button type="button" id="mobileStickyFooterBtn" class="btn zk-btn-primary rounded-pill px-3 py-2.5 fw-bold fs-14 flex-grow-1 d-flex align-items-center justify-content-center gap-2 shadow-sm" onclick="handleMobileFooterClick(event)">
                        <span>পরবর্তী ধাপ</span>
                        <i class="fa-solid fa-arrow-right fs-13"></i>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<style>
.zk-checkout-wrapper, .zk-checkout-wrapper *, .zk-checkout-wrapper *::before, .zk-checkout-wrapper *::after { box-sizing: border-box; }
.zk-checkout-wrapper { background-color: #fafafa; min-height: 100vh; font-family: 'Inter', 'Hind Siliguri', sans-serif; }
.zk-card { background: #ffffff; border-color: #eaeaea !important; }
.shadow-custom { box-shadow: 0 10px 40px -10px rgba(0,0,0,0.06), 0 0 1px 1px rgba(0,0,0,0.02); }

.fs-10 { font-size: 10px !important; }
.fs-11 { font-size: 11px !important; }
.fs-12 { font-size: 12px !important; }
.fs-13 { font-size: 13px !important; }
.fs-14 { font-size: 14px !important; }
.fs-15 { font-size: 15px !important; }
.fs-16 { font-size: 16px !important; }
.fs-18 { font-size: 18px !important; }
.fs-20 { font-size: 20px !important; }
.fs-22 { font-size: 22px !important; }
.cursor-pointer { cursor: pointer; }
.transition-transform { transition: transform 0.2s ease; }
.zk-panel-icon { width: 38px; height: 38px; min-width: 38px; display: flex; align-items: center; justify-content: center; }

.zk-stepper-wrap { width: 100%; }
.zk-stepper { display: flex; align-items: center; justify-content: space-between; }
.zk-step-item { display: flex; align-items: center; gap: 10px; cursor: pointer; user-select: none; transition: all 0.2s ease; }
.zk-step-num { width: 34px; height: 34px; border-radius: 50%; background: #f1f5f9; color: #64748b; font-size: 13px; font-weight: 700; display: flex; align-items: center; justify-content: center; flex-shrink: 0; transition: all 0.3s ease; }
.zk-step-text { display: flex; flex-direction: column; line-height: 1.3; }
.zk-step-title { font-size: 13.5px; font-weight: 600; color: #64748b; transition: color 0.3s ease; }
.zk-step-sub { font-size: 11px; color: #94a3b8; }
.zk-step-divider { height: 2px; background: #e2e8f0; margin: 0 16px; border-radius: 4px; transition: background 0.3s ease; }

.zk-step-item.zk-step-active .zk-step-num { background: #000000; color: #fff; box-shadow: 0 4px 12px rgba(0,0,0,0.2); }
.zk-step-item.zk-step-active .zk-step-title { color: #000000; font-weight: 700; }
.zk-step-item.zk-step-active .zk-step-sub { color: #64748b; }
.zk-step-item.zk-step-complete .zk-step-num { background: #000000; color: #fff; }
.zk-step-item.zk-step-complete .zk-step-title { color: #000000; font-weight: 700; }

.zk-input, .zk-select { background-color: #fff; border: 1.5px solid #e2e8f0; color: #000; font-size: 14px; padding: 12px 16px; height: 50px; transition: all 0.2s ease; width: 100%; box-sizing: border-box; border-radius: 12px; }
.zk-input:focus, .zk-select:focus { border-color: #000000; box-shadow: 0 0 0 3px rgba(0,0,0,0.06); outline: none; }
.zk-input-group { background: #fff; border: 1.5px solid #e2e8f0; border-radius: 12px; transition: all 0.2s ease; }
.zk-input-group .input-group-text { background-color: transparent; border: none; height: 48px; }
.zk-input-group .zk-input { border: none !important; height: 48px; font-size: 14px; box-shadow: none !important; }
.zk-input-group:focus-within { border-color: #000000; box-shadow: 0 0 0 3px rgba(0,0,0,0.06); }
.zk-input-group:has(.btn) .zk-input { border-radius: 0; }
.zk-input-group .btn { border-radius: 0 10px 10px 0; height: 48px; }
.zk-textarea { border: 1.5px solid #e2e8f0; padding: 14px 16px !important; font-size: 14px; min-height: 95px; width: 100%; transition: all 0.2s ease; border-radius: 12px; }
.zk-textarea:focus { border-color: #000000; box-shadow: 0 0 0 3px rgba(0,0,0,0.06); outline: none; }
.zk-textarea-compact { min-height: 70px !important; }

.select2-container { width: 100% !important; }
.select2-container--default .select2-selection--single { background: #fff; border: 1.5px solid #e2e8f0; border-radius: 12px; height: 50px; display: flex; align-items: center; transition: all 0.2s ease; box-shadow: 0 1px 2px rgba(0,0,0,0.02); }
.select2-container--default.select2-container--focus .select2-selection--single,
.select2-container--default.select2-container--open .select2-selection--single { border-color: #000000; box-shadow: 0 0 0 3px rgba(0,0,0,0.06); }
.select2-container--default .select2-selection--single .select2-selection__rendered { color: #000 !important; font-size: 14px; font-weight: 500; padding-left: 16px; padding-right: 36px; line-height: 48px; }
.select2-container--default .select2-selection--single .select2-selection__arrow { height: 48px; right: 12px; }
.select2-dropdown { border: 1.5px solid #000 !important; border-radius: 12px !important; box-shadow: 0 15px 35px rgba(0,0,0,0.1) !important; padding-top: 6px; }
.select2-search--dropdown .select2-search__field { border: 1.5px solid #e2e8f0 !important; border-radius: 8px !important; padding: 10px 14px !important; font-size: 14px !important; outline: none; }
.select2-search--dropdown .select2-search__field:focus { border-color: #000 !important; box-shadow: 0 0 0 2px rgba(0,0,0,0.08) !important; }
.select2-results__option { padding: 10px 14px; font-size: 13.5px; }
.select2-container--default .select2-results__option--highlighted.select2-results__option--selectable { background-color: #000000; color: #fff; border-radius: 6px; margin: 0 6px; }

.zk-btn-primary { background: #000000; color: #fff; border: none; font-weight: 600; transition: all 0.2s ease; }
.zk-btn-primary:hover { background: #1a1a1a; transform: translateY(-1px); box-shadow: 0 6px 20px rgba(0,0,0,0.15); color: #fff; }
.zk-btn-outline { background: #fff; color: #000000; border: 1.5px solid #cbd5e1; font-weight: 600; transition: all 0.2s ease; border-radius: 12px; }
.zk-btn-outline:hover { background: #f8fafc; border-color: #000; }
.zk-btn-edit { background: #fff; border: 1px solid #e2e8f0; color: #000000; font-size: 11.5px; font-weight: 600; }
.zk-btn-edit:hover { background: #000; color: #fff; border-color: #000; }

.zk-radio-card { border: 1.5px solid #e2e8f0 !important; transition: all 0.2s ease; background: #fff; }
.zk-radio-card:hover { border-color: #cbd5e1 !important; background: #fbfbfb; }
.zk-radio-card:has(input:checked) { border-color: #000000 !important; background: #fff; box-shadow: 0 4px 15px rgba(0,0,0,0.04); }
.zk-radio-disabled { opacity: 0.55; cursor: not-allowed !important; background: #f8fafc !important; }
.zk-check-input { width: 18px; height: 18px; border: 2px solid #cbd5e1; }
.zk-check-input:checked { background-color: #000000; border-color: #000000; }

.zk-zone-notice { background: #f8fafc; border-color: #eaeaea !important; border-radius: 16px; }
.zk-sticky-sidebar { position: sticky; top: 100px; }
.zk-cart-scroll { max-height: 280px; overflow-y: auto; padding-right: 6px; }
.zk-cart-scroll::-webkit-scrollbar { width: 4px; }
.zk-cart-scroll::-webkit-scrollbar-thumb { background-color: #cbd5e1; border-radius: 10px; }
.zk-img-box { width: 58px; height: 58px; border-radius: 12px; overflow: hidden; background: #f1f5f9; border: 1px solid #eaeaea; }
.zk-prod-thumb { width: 100%; height: 100%; object-fit: cover; }

.zk-counter { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 30px; padding: 3px; }
.zk-counter-btn { width: 28px; height: 28px; background: #fff; border: 1px solid #e2e8f0; border-radius: 50%; color: #000; display: flex; align-items: center; justify-content: center; transition: all 0.2s ease; }
.zk-counter-btn:hover { background: #000000; color: #fff; border-color: #000; }
.zk-counter-num { width: 30px; text-align: center; font-weight: 700; color: #000; font-size: 13px; }
.zk-trash-btn { width: 34px; height: 34px; border-radius: 10px; background: #fef2f2; border: 1px solid #fee2e2; color: #ef4444; display: flex; align-items: center; justify-content: center; transition: all 0.2s ease; }
.zk-trash-btn:hover { background: #ef4444; color: #fff; }

.zk-coupon-badge { background: #f8fafc; border-color: #e2e8f0; display: flex; align-items: center; width: 100%; box-sizing: border-box; }
.zk-coupon-pill { background: #fff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 6px 12px; transition: all 0.2s ease; }
.zk-coupon-pill:hover { border-color: #000; transform: translateY(-1px); }
.zk-calc-box { background-color: #f8fafc; border-color: #eaeaea !important; border-radius: 16px; }
.zk-calc-divider { height: 1px; background: #e2e8f0; }
.border-end-md { border-right: 1px solid #eaeaea; }

.zk-bottom-nav {
    display: none !important;
}

.zk-mobile-bottom-bar {
    position: fixed;
    bottom: 0 !important;
    left: 0;
    right: 0;
    width: 100%;
    background: rgba(255, 255, 255, 0.96);
    backdrop-filter: blur(20px);
    -webkit-backdrop-filter: blur(20px);
    border-top: 1px solid rgba(226, 232, 240, 0.85);
    padding: 9px 12px calc(9px + env(safe-area-inset-bottom, 0px));
    z-index: 1050;
    box-shadow: 0 -6px 24px -4px rgba(15, 23, 42, 0.12);
}

.btn-mobile-back {
    width: 40px;
    height: 40px;
    min-width: 40px;
    border-radius: 12px;
    background: #f1f5f9;
    color: #0f172a;
    border: 1px solid #e2e8f0;
    font-size: 15px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    flex-shrink: 0;
    text-decoration: none;
}

.btn-mobile-back:active {
    transform: scale(0.92);
    background: #e2e8f0;
}

[data-bs-theme="dark"] .zk-mobile-bottom-bar {
    background: rgba(15, 23, 42, 0.95);
    border-top-color: rgba(51, 65, 85, 0.8);
    box-shadow: 0 -6px 24px -4px rgba(0, 0, 0, 0.35);
}

[data-bs-theme="dark"] .btn-mobile-back {
    background: #1e293b;
    color: #f8fafc;
    border-color: #334155;
}

[data-bs-theme="dark"] .btn-mobile-back:active {
    background: #334155;
}

@media (max-width: 991px) {
    .zk-checkout-wrapper {
        background: #fafafa;
        padding-top: 12px;
        padding-bottom: calc(85px + env(safe-area-inset-bottom, 0px)) !important;
    }
    .border-end-md { border-right: none !important; border-bottom: 1px solid #eaeaea; padding-bottom: 20px !important; margin-bottom: 12px !important; }
}
@media (max-width: 576px) {
    .zk-step-item { flex-direction: column; gap: 4px; }
    .zk-step-text { text-align: center; }
    .zk-step-num { width: 30px; height: 30px; font-size: 12px; }
    .zk-step-title { font-size: 11.5px; }
    .zk-img-box { width: 50px; height: 50px; }
}
</style>
@endsection

@push('scripts')
<script>
(() => {
    let rawGeo = @json($divisions ?? []);
    const geoData = Array.isArray(rawGeo) ? rawGeo : [];
    const allGeoNames = new Set();
    try {
        geoData.forEach(div => {
            if (div.bn_name) allGeoNames.add(div.bn_name.trim().toLowerCase());
            if (div.name) allGeoNames.add(div.name.trim().toLowerCase());
            (div.districts || []).forEach(dist => {
                if (dist.bn_name) allGeoNames.add(dist.bn_name.trim().toLowerCase());
                if (dist.name) allGeoNames.add(dist.name.trim().toLowerCase());
                (dist.upazilas || []).forEach(up => {
                    if (up.bn_name) allGeoNames.add(up.bn_name.trim().toLowerCase());
                    if (up.name) allGeoNames.add(up.name.trim().toLowerCase());
                });
            });
        });
    } catch (e) {}
    let currentStep = 1;
    let currentSubtotal = {{ (float)($subtotal ?? 0) }};
    let currentDiscount = {{ (float)($discount ?? 0) }};
    let shippingInside = {{ (float)($shippingInside ?? 60) }};
    let shippingOutside = {{ (float)($shippingOutside ?? 120) }};
    let qualifiesFree = {{ !empty($qualifiesForFreeShipping) ? 'true' : 'false' }};
    let currentShipping = {{ (float)($shippingCost ?? 60) }};
    let bumpPrice = {{ !empty($orderBump) ? (float)$orderBump->price : 0 }};
    let isBumpIncluded = false;
    let isSubmitting = false;
    let updatingKeys = {};
    let pendingOpenDistrict = false;
    let pendingOpenUpazila = false;
    let pendingFocusAddress = false;

    let autoOpenTimer = null;
    let shouldAutoOpenDistrict = false;
    let shouldAutoOpenUpazila = false;
    let shouldFocusAddress = false;

    let currentDivisionId = '';
    let currentDistrictId = '';
    let currentUpazilaId = '';

    function whenSelect2Ready(callback, maxAttempts = 60) {
        let attempts = 0;
        function check() {
            if (typeof window.jQuery !== 'undefined' && window.jQuery.fn && typeof window.jQuery.fn.select2 === 'function') {
                callback(window.jQuery);
            } else if (attempts < maxAttempts) {
                attempts++;
                setTimeout(check, 30);
            } else {
                callback(null);
            }
        }
        check();
    }

    function triggerAutoOpen(selector) {
        if (autoOpenTimer) {
            clearTimeout(autoOpenTimer);
        }
        autoOpenTimer = setTimeout(() => {
            try {
                const el = document.querySelector(selector);
                if (!el) return;
                const $el = $(el);
                $el.prop('disabled', false);

                if (!$el.data('select2')) {
                    if (selector === '#geoDivisionSelect') initDivisionSelect();
                    else if (selector === '#geoDistrictSelect') initDistrictSelect();
                    else if (selector === '#geoUpazilaSelect') initUpazilaSelect();
                }

                if ($el.data('select2')) {
                    if (!$el.data('select2').isOpen()) {
                        $el.select2('open');
                    }
                } else {
                    el.focus();
                }

                const focusSearchField = () => {
                    const searchField = document.querySelector('.select2-container--open .select2-search__field')
                        || document.querySelector('.select2-search--dropdown .select2-search__field');
                    if (searchField) {
                        searchField.focus();
                        const len = searchField.value ? searchField.value.length : 0;
                        if (typeof searchField.setSelectionRange === 'function') {
                            searchField.setSelectionRange(len, len);
                        }
                        return true;
                    }
                    return false;
                };

                if (!focusSearchField()) {
                    setTimeout(focusSearchField, 40);
                    setTimeout(focusSearchField, 90);
                    setTimeout(focusSearchField, 160);
                }

                const container = el.nextElementSibling && el.nextElementSibling.classList.contains('select2-container')
                    ? el.nextElementSibling
                    : el;
                container.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            } catch (e) {}
        }, 60);
    }

    function focusCustomerAddress() {
        const addr = document.getElementById('customer_address');
        if (addr) {
            addr.focus();
            addr.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    }

    function customGeoMatcher(params, data) {
        if (!params.term || jQuery.trim(params.term) === '') return data;
        if (typeof data.text === 'undefined') return null;
        const term = params.term.toLowerCase().trim();
        const text = (data.text || '').toLowerCase();
        const el = data.element;
        const en = el ? ((el.getAttribute('data-en') || el.dataset?.en || '')).toLowerCase() : '';
        const bn = el ? ((el.getAttribute('data-bn') || el.dataset?.bn || '')).toLowerCase() : '';
        if (text.indexOf(term) > -1 || en.indexOf(term) > -1 || bn.indexOf(term) > -1) {
            return data;
        }
        return null;
    }

    function initDivisionSelect() {
        if (typeof window.jQuery === 'undefined' || !window.jQuery.fn || typeof window.jQuery.fn.select2 === 'undefined') return;
        const $ = window.jQuery;
        const $div = $('#geoDivisionSelect');
        if (!$div.length) return;
        if ($div.data('select2')) {
            try { $div.select2('destroy'); } catch(e) {}
        }
        $div.removeClass('select2-hidden-accessible').removeAttr('data-select2-id').removeAttr('aria-hidden').removeAttr('tabindex');
        $div.parent().find('.select2-container').remove();
        $div.select2({
            placeholder: 'বিভাগ নির্বাচন করুন',
            width: '100%',
            minimumResultsForSearch: 0,
            matcher: customGeoMatcher,
            language: {
                noResults: () => 'কোনো বিভাগ পাওয়া যায়নি',
                searching: () => 'অনুসন্ধান করা হচ্ছে...'
            }
        });

        $div.off('.geoDiv');
        $div.on('select2:select.geoDiv', function(e) {
            const val = (e.params && e.params.data) ? e.params.data.id : $(this).val();
            if (val) {
                window.onDivisionChange(val, false);
                setTimeout(() => {
                    triggerAutoOpen('#geoDistrictSelect');
                }, 160);
            }
        });
        $div.on('change.geoDiv', function() {
            const val = $(this).val();
            if (val && String(currentDivisionId) !== String(val)) {
                window.onDivisionChange(val, false);
            }
        });
    }

    function initDistrictSelect() {
        if (typeof window.jQuery === 'undefined' || !window.jQuery.fn || typeof window.jQuery.fn.select2 === 'undefined') return;
        const $ = window.jQuery;
        const $dist = $('#geoDistrictSelect');
        if (!$dist.length) return;
        if ($dist.data('select2')) {
            try { $dist.select2('destroy'); } catch(e) {}
        }
        $dist.removeClass('select2-hidden-accessible').removeAttr('data-select2-id').removeAttr('aria-hidden').removeAttr('tabindex');
        $dist.parent().find('.select2-container').remove();
        $dist.select2({
            placeholder: $dist.prop('disabled') ? 'আগে বিভাগ বাছুন' : 'জেলা নির্বাচন করুন',
            width: '100%',
            minimumResultsForSearch: 0,
            matcher: customGeoMatcher,
            language: {
                noResults: () => 'কোনো জেলা পাওয়া যায়নি',
                searching: () => 'অনুসন্ধান করা হচ্ছে...'
            }
        });

        $dist.off('.geoDist');
        $dist.on('select2:select.geoDist', function(e) {
            const val = (e.params && e.params.data) ? e.params.data.id : $(this).val();
            if (val) {
                window.onDistrictChange(val, false);
                setTimeout(() => {
                    triggerAutoOpen('#geoUpazilaSelect');
                }, 160);
            }
        });
        $dist.on('change.geoDist', function() {
            const val = $(this).val();
            if (val && String(currentDistrictId) !== String(val)) {
                window.onDistrictChange(val, false);
            }
        });
    }

    function initUpazilaSelect() {
        if (typeof window.jQuery === 'undefined' || !window.jQuery.fn || typeof window.jQuery.fn.select2 === 'undefined') return;
        const $ = window.jQuery;
        const $up = $('#geoUpazilaSelect');
        if (!$up.length) return;
        if ($up.data('select2')) {
            try { $up.select2('destroy'); } catch(e) {}
        }
        $up.removeClass('select2-hidden-accessible').removeAttr('data-select2-id').removeAttr('aria-hidden').removeAttr('tabindex');
        $up.parent().find('.select2-container').remove();
        $up.select2({
            placeholder: $up.prop('disabled') ? 'আগে জেলা বাছুন' : 'থানা / উপজেলা নির্বাচন করুন',
            width: '100%',
            minimumResultsForSearch: 0,
            matcher: customGeoMatcher,
            language: {
                noResults: () => 'কোনো থানা পাওয়া যায়নি',
                searching: () => 'অনুসন্ধান করা হচ্ছে...'
            }
        });

        $up.off('.geoUp');
        $up.on('select2:select.geoUp', function(e) {
            const val = (e.params && e.params.data) ? e.params.data.id : $(this).val();
            if (val) {
                currentUpazilaId = String(val);
                window.onUpazilaChange(val);
                setTimeout(() => {
                    focusCustomerAddress();
                }, 160);
            }
        });
        $up.on('change.geoUp', function() {
            const val = $(this).val();
            if (val && String(currentUpazilaId) !== String(val)) {
                currentUpazilaId = String(val);
                window.onUpazilaChange(val);
            }
        });
    }

    function initSelect2Boxes() {
        whenSelect2Ready(($) => {
            if (!$) return;
            initDivisionSelect();
            initDistrictSelect();
            initUpazilaSelect();
            $(document).off('select2:open.geoFocus').on('select2:open.geoFocus', () => {
                const focusInput = () => {
                    const field = document.querySelector('.select2-container--open .select2-search__field')
                        || document.querySelector('.select2-search--dropdown .select2-search__field');
                    if (field) {
                        field.focus();
                        return true;
                    }
                    return false;
                };
                if (!focusInput()) {
                    setTimeout(focusInput, 30);
                    setTimeout(focusInput, 80);
                    setTimeout(focusInput, 150);
                }
            });
        });
    }

    function safeSelect2Open(selector) {
        try {
            if (typeof jQuery === 'undefined') {
                const el = document.querySelector(selector);
                if (el) el.focus();
                return;
            }
            const $el = $(selector);
            if (!$el.length) return;
            if (!$el.data('select2')) {
                if (selector === '#geoDivisionSelect') initDivisionSelect();
                else if (selector === '#geoDistrictSelect') initDistrictSelect();
                else if (selector === '#geoUpazilaSelect') initUpazilaSelect();
                else initSelect2Boxes();
            }
            if ($el.data('select2') && $el.hasClass('select2-hidden-accessible')) {
                $el.select2('open');
            } else {
                $el.trigger('focus');
            }
        } catch (e) {
            try {
                const el = document.querySelector(selector);
                if (el) el.focus();
            } catch (err) {}
        }
    }

    window.onBkashClick = function(e) {
        if (e && typeof e.preventDefault === 'function') e.preventDefault();
        displayToast('বিকাশ সিস্টেম শীঘ্রই চালু হচ্ছে। ক্যাশ অন ডেলিভারি সিলেক্ট করুন।');
    };

    window.onDivisionChange = function(divisionId, autoOpenNext = false) {
        const distEl = document.getElementById('geoDistrictSelect');
        const upEl = document.getElementById('geoUpazilaSelect');
        if (!distEl || !upEl) return;

        if (!divisionId) {
            currentDivisionId = '';
            currentDistrictId = '';
            currentUpazilaId = '';
            distEl.innerHTML = '<option value="">আগে বিভাগ বাছুন</option>';
            distEl.disabled = true;
            upEl.innerHTML = '<option value="">আগে জেলা বাছুন</option>';
            upEl.disabled = true;
            initDistrictSelect();
            initUpazilaSelect();
            return;
        }

        if (currentDivisionId === String(divisionId) && distEl.options.length > 1) {
            if (autoOpenNext) {
                triggerAutoOpen('#geoDistrictSelect');
            }
            return;
        }
        currentDivisionId = String(divisionId);
        currentDistrictId = '';
        currentUpazilaId = '';

        upEl.innerHTML = '<option value="">আগে জেলা বাছুন</option>';
        upEl.disabled = true;
        initUpazilaSelect();

        const divObj = geoData.find(d => String(d.id || '') === String(divisionId));
        if (divObj && divObj.districts && Array.isArray(divObj.districts)) {
            populateDistricts(divObj.districts, autoOpenNext);
        } else {
            axios.get('/geo/districts/' + divisionId)
                .then(r => { if (Array.isArray(r.data)) populateDistricts(r.data, autoOpenNext); })
                .catch(() => {});
        }
    };

    function populateDistricts(districts, autoOpenNext = false) {
        const distEl = document.getElementById('geoDistrictSelect');
        if (!distEl) return;
        distEl.innerHTML = '<option value="">জেলা নির্বাচন করুন</option>';
        districts.forEach(d => {
            const opt = document.createElement('option');
            opt.value = d.id;
            opt.textContent = (d.bn_name || d.name) + (d.name ? ` (${d.name})` : '');
            opt.dataset.bn = d.bn_name || d.name;
            opt.dataset.en = d.name || '';
            distEl.appendChild(opt);
        });

        distEl.disabled = false;
        initDistrictSelect();
        if (autoOpenNext) {
            triggerAutoOpen('#geoDistrictSelect');
        }
    }

    window.onDistrictChange = function(districtId, autoOpenNext = false) {
        const upEl = document.getElementById('geoUpazilaSelect');
        if (!upEl) return;
        if (!districtId) {
            currentDistrictId = '';
            currentUpazilaId = '';
            upEl.innerHTML = '<option value="">আগে জেলা বাছুন</option>';
            upEl.disabled = true;
            initUpazilaSelect();
            return;
        }

        const distSelect = document.getElementById('geoDistrictSelect');
        const selectedOpt = distSelect ? (distSelect.querySelector('option[value="' + districtId + '"]') || distSelect.options[distSelect.selectedIndex]) : null;
        const distBn = selectedOpt ? (selectedOpt.getAttribute('data-bn') || selectedOpt.dataset?.bn || selectedOpt.textContent || '') : '';

        const isDhaka = distBn.includes('ঢাকা') || distBn.toLowerCase().includes('dhaka');
        const hiddenDist = document.getElementById('hiddenDistrictType');
        if (hiddenDist) hiddenDist.value = isDhaka ? 'ঢাকা' : 'ঢাকার বাইরে';

        const newShipCost = qualifiesFree ? 0 : (isDhaka ? shippingInside : shippingOutside);
        updateShippingCost(newShipCost);

        if (currentDistrictId === String(districtId) && upEl.options.length > 1) {
            if (autoOpenNext) {
                triggerAutoOpen('#geoUpazilaSelect');
            }
            handleCustomerInfoChange();
            return;
        }
        currentDistrictId = String(districtId);
        currentUpazilaId = '';

        let foundUpazilas = null;
        for (let d of geoData) {
            if (d.districts && Array.isArray(d.districts)) {
                let distObj = d.districts.find(dst => String(dst.id || '') === String(districtId));
                if (distObj && distObj.upazilas && Array.isArray(distObj.upazilas)) {
                    foundUpazilas = distObj.upazilas;
                    break;
                }
            }
        }

        if (foundUpazilas) {
            populateUpazilas(foundUpazilas, autoOpenNext);
        } else {
            axios.get('/geo/upazilas/' + districtId)
                .then(r => { if (Array.isArray(r.data)) populateUpazilas(r.data, autoOpenNext); })
                .catch(() => {});
        }

        handleCustomerInfoChange();
    };

    function populateUpazilas(upazilas, autoOpenNext = false) {
        const upEl = document.getElementById('geoUpazilaSelect');
        if (!upEl) return;
        upEl.innerHTML = '<option value="">থানা / উপজেলা নির্বাচন করুন</option>';
        upazilas.forEach(u => {
            const opt = document.createElement('option');
            opt.value = u.id;
            opt.textContent = (u.bn_name || u.name) + (u.name ? ` (${u.name})` : '');
            opt.dataset.bn = u.bn_name || u.name;
            opt.dataset.en = u.name || '';
            upEl.appendChild(opt);
        });

        upEl.disabled = false;
        initUpazilaSelect();
        if (autoOpenNext) {
            triggerAutoOpen('#geoUpazilaSelect');
        }
    }

    window.onUpazilaChange = function() {
        handleCustomerInfoChange();
    };

    window.toggleOrderBump = function(checkbox) {
        isBumpIncluded = !!checkbox.checked;
        const mobileCb = document.getElementById('include_bump_mobile');
        if (mobileCb && mobileCb !== checkbox) mobileCb.checked = isBumpIncluded;
        const mainCb = document.getElementById('include_bump');
        if (mainCb && mainCb !== checkbox) mainCb.checked = isBumpIncluded;

        document.querySelectorAll('.checkout-bump-row').forEach(el => {
            el.classList.toggle('d-none', !isBumpIncluded);
        });
        updateShippingCost(currentShipping);
    };

    window.syncBumpCheckboxes = function(checkbox) {
        window.toggleOrderBump(checkbox);
    };

    function updateShippingCost(cost) {
        currentShipping = cost;
        const formatted = cost === 0 ? 'ফ্রি' : ('৳ ' + cost.toLocaleString('en-US'));
        document.querySelectorAll('.checkout-shipping-val').forEach(el => el.innerText = formatted);
        const bumpAdd = isBumpIncluded ? bumpPrice : 0;
        const grandTotal = Math.max(0, currentSubtotal + currentShipping + bumpAdd - currentDiscount);
        document.querySelectorAll('.checkout-total-val').forEach(el => el.innerText = '৳ ' + grandTotal.toLocaleString('en-US'));
    }

    window.switchStep = function(step) {
        if (step === 2 && !validateStep1()) return;
        if (step === 3 && !validateStep2()) return;
        goToStep(step);
    };

    window.goToStep = function(step) {
        if (step === 2 && !validateStep1()) return;
        if (step === 3 && !validateStep2()) return;

        currentStep = step;
        for (let i = 1; i <= 3; i++) {
            const panel = document.getElementById('stepPanel' + i);
            const ind = document.getElementById('stepIndicator' + i);
            const div = document.getElementById('stepDivider' + (i - 1));

            if (panel) panel.classList.toggle('d-none', i !== step);
            if (ind) {
                ind.classList.toggle('zk-step-active', i === step);
                ind.classList.toggle('zk-step-complete', i < step);
            }
            if (div) div.style.background = (i <= step) ? '#000000' : '#e2e8f0';
        }

        if (step === 3) updateReviewInfo();
        updateMobileFooterUI();
        window.scrollTo({ top: 0, behavior: 'smooth' });
    };

    function updateMobileFooterUI() {
        const btn = document.getElementById('mobileStickyFooterBtn');
        if (!btn) return;
        
        if (currentStep === 1) {
            btn.innerHTML = '<span>পরবর্তী ধাপ</span><i class="fa-solid fa-arrow-right fs-13"></i>';
            btn.onclick = (e) => { e.preventDefault(); goToStep(2); };
        } else if (currentStep === 2) {
            btn.innerHTML = '<span>রিভিউ করুন</span><i class="fa-solid fa-arrow-right fs-13"></i>';
            btn.onclick = (e) => { e.preventDefault(); goToStep(3); };
        } else {
            btn.innerHTML = '<span>অর্ডার নিশ্চিত করুন</span><i class="fa-solid fa-circle-check fs-14"></i>';
            btn.onclick = (e) => { submitOrder(e); };
        }
    }

    window.handleMobileFooterClick = function(e) {
        e.preventDefault();
        if (currentStep === 1) goToStep(2);
        else if (currentStep === 2) goToStep(3);
        else submitOrder(e);
    };

    window.handleMobileBack = function() {
        if (currentStep > 1) {
            goToStep(currentStep - 1);
        } else {
            if (window.history.length > 1) {
                window.history.back();
            } else {
                window.location.href = '{{ route('home') }}';
            }
        }
    };

    function validateStep1() {
        const name = document.getElementById('customer_name')?.value || '';
        const phone = document.getElementById('customer_phone')?.value || '';
        const div = document.getElementById('geoDivisionSelect')?.value || '';
        const dist = document.getElementById('geoDistrictSelect')?.value || '';
        const up = document.getElementById('geoUpazilaSelect')?.value || '';
        const addr = document.getElementById('customer_address')?.value || '';

        if (!name.trim()) {
            displayToast('আপনার পুরো নাম লিখুন');
            document.getElementById('customer_name')?.focus();
            return false;
        }

        const cleanPhone = phone.replace(/[\s\-\(\)]/g, '');
        const bdPhoneRegex = /^(?:\+?88)?01[3-9]\d{8}$/;
        if (!cleanPhone || !bdPhoneRegex.test(cleanPhone)) {
            displayToast('সঠিক মোবাইল নম্বর লিখুন (যেমন: 017XXXXXXXX)');
            document.getElementById('customer_phone')?.focus();
            return false;
        }

        if (!div) {
            displayToast('বিভাগ নির্বাচন করুন');
            safeSelect2Open('#geoDivisionSelect');
            return false;
        }
        if (!dist) {
            displayToast('জেলা নির্বাচন করুন');
            safeSelect2Open('#geoDistrictSelect');
            return false;
        }
        if (!up) {
            displayToast('থানা বা উপজেলা নির্বাচন করুন');
            safeSelect2Open('#geoUpazilaSelect');
            return false;
        }
        if (!addr.trim()) {
            displayToast('সম্পূর্ণ বিস্তারিত ঠিকানা লিখুন');
            document.getElementById('customer_address')?.focus();
            return false;
        }
        return true;
    }

    function validateStep2() {
        if (!validateStep1()) {
            goToStep(1);
            return false;
        }
        const pay = document.querySelector('input[name="payment_method"]:checked');
        if (!pay) {
            displayToast('পেমেন্ট পদ্ধতি নির্বাচন করুন');
            return false;
        }
        return true;
    }

    function extractOnlyStreetAddress(raw) {
        if (!raw) return '';
        let str = String(raw).trim();
        if (str.includes('ঠিকানা:')) {
            let parts = str.split('ঠিকানা:');
            str = parts[parts.length - 1].trim();
        }
        str = str.replace(/বিভাগ\s*:\s*[^,]+,?/gi, '')
                 .replace(/জেলা\s*:\s*[^,]+,?/gi, '')
                 .replace(/থানা(?:\/উপজেলা)?\s*:\s*[^,]+,?/gi, '')
                 .replace(/উপজেলা\s*:\s*[^,]+,?/gi, '')
                 .trim();

        const tokens = str.split(',').map(t => t.trim()).filter(Boolean);
        const nonGeoTokens = [];
        const curUp = (getGeoSelectedName(document.getElementById('geoUpazilaSelect')) || '').toLowerCase();
        const curDist = (getGeoSelectedName(document.getElementById('geoDistrictSelect')) || '').toLowerCase();
        const curDiv = (getGeoSelectedName(document.getElementById('geoDivisionSelect')) || '').toLowerCase();

        for (const t of tokens) {
            const lower = t.toLowerCase();
            if (typeof allGeoNames !== 'undefined' && allGeoNames.has(lower)) {
                continue;
            }
            if ((curUp && lower === curUp) || (curDist && lower === curDist) || (curDiv && lower === curDiv)) {
                continue;
            }
            if (!nonGeoTokens.some(existing => existing.toLowerCase() === lower)) {
                nonGeoTokens.push(t);
            }
        }

        return nonGeoTokens.length > 0 ? nonGeoTokens.join(', ') : (tokens.length > 0 ? tokens[0] : str);
    }

    function getGeoSelectedName(el) {
        if (!el || !el.value) return '';
        const opt = el.querySelector('option[value="' + el.value + '"]') || (el.selectedIndex >= 0 ? el.options[el.selectedIndex] : null);
        if (!opt) return '';
        const bn = opt.getAttribute('data-bn') || opt.dataset?.bn;
        if (bn) return bn;
        return (opt.textContent || '').replace(/\s*\([^)]*\)/, '').trim();
    }

    function updateReviewInfo() {
        const name = document.getElementById('customer_name')?.value || '';
        const phone = document.getElementById('customer_phone')?.value || '';
        const divEl = document.getElementById('geoDivisionSelect');
        const distEl = document.getElementById('geoDistrictSelect');
        const upEl = document.getElementById('geoUpazilaSelect');
        const rawStreet = document.getElementById('customer_address')?.value || '';
        const street = extractOnlyStreetAddress(rawStreet);

        const divText = getGeoSelectedName(divEl);
        const distText = getGeoSelectedName(distEl);
        const upText = getGeoSelectedName(upEl);
        const isDhaka = distText.includes('ঢাকা') || distText.toLowerCase().includes('dhaka');

        const revNameEl = document.getElementById('revName');
        if (revNameEl) revNameEl.innerText = name || '-';
        const revPhoneEl = document.getElementById('revPhone');
        if (revPhoneEl) revPhoneEl.innerText = phone || '-';
        const revDivEl = document.getElementById('revDivBadge');
        if (revDivEl) revDivEl.innerText = divText || '-';
        const revDistEl = document.getElementById('revDistBadge');
        if (revDistEl) revDistEl.innerText = distText || '-';
        const revUpEl = document.getElementById('revUpazilaBadge');
        if (revUpEl) revUpEl.innerText = upText || '-';
        const revStreetEl = document.getElementById('revStreetDetail');
        if (revStreetEl) revStreetEl.innerText = street || '-';

        const shipFeeText = qualifiesFree ? 'ফ্রি' : ('৳ ' + currentShipping.toLocaleString('en-US'));
        const feeBadge = document.getElementById('revZoneFeeBadge');
        if (feeBadge) feeBadge.innerText = shipFeeText;

        const zoneText = document.getElementById('revZoneText');
        if (zoneText) zoneText.innerText = isDhaka ? 'ঢাকা সিটির ভেতরে' : (distText ? `ঢাকার বাইরে (${distText})` : 'ঢাকার বাইরে');
    }

    function syncCartUI(data) {
        currentSubtotal = parseFloat(data.subtotal || 0);
        currentDiscount = parseFloat(data.discount || 0);
        if (typeof data.is_free_shipping !== 'undefined') {
            qualifiesFree = !!data.is_free_shipping;
        }
        const count = data.count || (Array.isArray(data.cart) ? data.cart.length : 0);

        document.querySelectorAll('.checkout-subtotal-val').forEach(el => el.innerText = '৳ ' + currentSubtotal.toLocaleString('en-US'));
        document.querySelectorAll('.checkout-badge-count').forEach(el => el.innerText = count + ' টি');

        document.querySelectorAll('.checkout-discount-row').forEach(el => {
            if (currentDiscount > 0) {
                el.classList.remove('d-none');
                const valEl = el.querySelector('.checkout-discount-val');
                if (valEl) valEl.innerText = `- ৳ ${currentDiscount.toLocaleString('en-US')}`;
            } else {
                el.classList.add('d-none');
            }
        });

        const appliedDiscountText = document.getElementById('appliedCouponDiscountText');
        if (appliedDiscountText && currentDiscount > 0) {
            appliedDiscountText.innerText = `সাশ্রয়: ৳ ${currentDiscount.toLocaleString('en-US')}`;
        } else if (appliedDiscountText && currentDiscount === 0) {
            document.getElementById('couponAppliedBadge')?.classList.add('d-none');
            document.getElementById('couponInputGroup')?.classList.remove('d-none');
            document.getElementById('suggestedCouponsWrap')?.classList.remove('d-none');
        }

        const hiddenDist = document.getElementById('hiddenDistrictType')?.value || 'ঢাকা';
        const isDhaka = hiddenDist === 'ঢাকা';
        const newShipCost = qualifiesFree ? 0 : (isDhaka ? shippingInside : shippingOutside);
        updateShippingCost(newShipCost);
    }

    window.alterItemQty = function(cartKey, delta) {
        if (updatingKeys[cartKey]) return;
        updatingKeys[cartKey] = true;

        axios.post('{{ route('cart.update') }}', { cart_key: cartKey, delta: delta, _token: '{{ csrf_token() }}' })
            .then(res => {
                delete updatingKeys[cartKey];
                if (res.data && res.data.success) {
                    const item = (res.data.cart || []).find(i => String(i.cart_key || i.id) === String(cartKey) || String(i.id) === String(cartKey));
                    if (item && item.qty > 0) {
                        ['qty-', 'mqty-', 'unit-qty-', 'munit-qty-'].forEach(p => {
                            const el = document.getElementById(p + cartKey);
                            if (el) el.innerText = item.qty;
                        });
                    } else {
                        document.getElementById('row-' + cartKey)?.remove();
                        document.getElementById('mrow-' + cartKey)?.remove();
                    }
                    if (!res.data.cart || res.data.cart.length === 0) {
                        window.location.reload();
                        return;
                    }
                    syncCartUI(res.data);
                }
            })
            .catch(() => {
                delete updatingKeys[cartKey];
            });
    };

    window.deleteItem = function(cartKey) {
        axios.post('{{ route('cart.remove') }}', { cart_key: cartKey, _token: '{{ csrf_token() }}' })
            .then(res => {
                if (res.data && res.data.success) {
                    document.getElementById('row-' + cartKey)?.remove();
                    document.getElementById('mrow-' + cartKey)?.remove();
                    if (!res.data.cart || res.data.cart.length === 0) {
                        window.location.reload();
                        return;
                    }
                    syncCartUI(res.data);
                }
            })
            .catch(() => {});
    };

    window.toggleMobileSummary = function() {
        const body = document.getElementById('mobileSummaryBody');
        const chev = document.getElementById('mobileSummaryChevron');
        if (!body) return;
        const isHidden = body.classList.contains('d-none');
        body.classList.toggle('d-none', !isHidden);
        if (chev) chev.style.transform = isHidden ? 'rotate(180deg)' : 'rotate(0deg)';
    };

    window.applyCheckoutCoupon = function(customCode) {
        const input = document.getElementById('checkoutCouponInput');
        const feedback = document.getElementById('couponFeedback');
        const btn = document.getElementById('applyCouponBtn');
        const code = (typeof customCode === 'string' && customCode.trim()) ? customCode.trim() : (input ? input.value.trim() : '');
        if (!code) return;

        if (input) input.value = code;
        if (btn) btn.disabled = true;
        axios.post('{{ route('cart.apply_coupon') }}', { code: code, _token: '{{ csrf_token() }}' })
            .then(res => {
                if (btn) btn.disabled = false;
                if (res.data && res.data.success) {
                    currentDiscount = parseFloat(res.data.discount || 0);
                    document.getElementById('couponInputGroup')?.classList.add('d-none');
                    document.getElementById('suggestedCouponsWrap')?.classList.add('d-none');
                    const badge = document.getElementById('couponAppliedBadge');
                    if (badge) {
                        badge.style.display = 'flex';
                        badge.classList.remove('d-none');
                        document.getElementById('appliedCouponCodeText').innerText = res.data.code || code.toUpperCase();
                        document.getElementById('appliedCouponDiscountText').innerText = `সাশ্রয়: ৳ ${currentDiscount.toLocaleString('en-US')}`;
                    }
                    document.querySelectorAll('.checkout-discount-row').forEach(el => {
                        el.classList.remove('d-none');
                        const valEl = el.querySelector('.checkout-discount-val');
                        if (valEl) valEl.innerText = `- ৳ ${currentDiscount.toLocaleString('en-US')}`;
                    });
                    if (feedback) feedback.classList.add('d-none');
                    updateShippingCost(currentShipping);
                } else {
                    if (feedback) {
                        feedback.className = 'fs-13 mt-2 text-danger fw-semibold px-2';
                        feedback.innerHTML = res.data?.message || 'অবৈধ কুপন কোড';
                        feedback.classList.remove('d-none');
                    }
                }
            })
            .catch(err => {
                if (btn) btn.disabled = false;
                if (feedback) {
                    feedback.className = 'fs-13 mt-2 text-danger fw-semibold px-2';
                    feedback.innerHTML = err.response?.data?.message || 'অবৈধ বা অকার্যকর কুপন কোড';
                    feedback.classList.remove('d-none');
                }
            });
    };

    window.removeCheckoutCoupon = function() {
        axios.post('{{ route('cart.remove_coupon') }}', { _token: '{{ csrf_token() }}' })
            .then(() => {
                currentDiscount = 0;
                const badge = document.getElementById('couponAppliedBadge');
                if (badge) {
                    badge.style.display = 'none';
                    badge.classList.add('d-none');
                }
                const input = document.getElementById('checkoutCouponInput');
                if (input) input.value = '';
                document.getElementById('couponInputGroup')?.classList.remove('d-none');
                document.getElementById('suggestedCouponsWrap')?.classList.remove('d-none');
                document.querySelectorAll('.checkout-discount-row').forEach(el => el.classList.add('d-none'));
                updateShippingCost(currentShipping);
            });
    };

    window.submitOrder = function(e) {
        if (e && typeof e.preventDefault === 'function') e.preventDefault();
        if (isSubmitting) return false;
        if (!validateStep1() || !validateStep2()) return false;

        isSubmitting = true;
        const submitBtns = [document.getElementById('btnConfirmOrder'), document.getElementById('mobileStickyFooterBtn')].filter(Boolean);
        submitBtns.forEach(b => {
            b.disabled = true;
            b.innerHTML = `<span class="spinner-border spinner-border-sm me-2"></span><span>অর্ডার হচ্ছে...</span>`;
        });

        const phoneInput = document.getElementById('customer_phone');
        let rawPhone = phoneInput ? phoneInput.value.trim() : '';
        let cleanPhone = rawPhone.replace(/[\s\-\(\)]/g, '');
        if (cleanPhone.startsWith('+88')) cleanPhone = cleanPhone.substring(3);
        else if (cleanPhone.startsWith('88')) cleanPhone = cleanPhone.substring(2);
        if (phoneInput) phoneInput.value = cleanPhone;

        const divEl = document.getElementById('geoDivisionSelect');
        const distEl = document.getElementById('geoDistrictSelect');
        const upEl = document.getElementById('geoUpazilaSelect');
        const rawStreet = document.getElementById('customer_address')?.value || '';
        const street = extractOnlyStreetAddress(rawStreet);

        const distText = getGeoSelectedName(distEl);
        const upText = getGeoSelectedName(upEl);

        const addressParts = [];
        if (street) addressParts.push(street);
        if (upText && !street.toLowerCase().includes(upText.toLowerCase())) {
            addressParts.push(upText);
        }
        if (distText && !street.toLowerCase().includes(distText.toLowerCase()) && distText.toLowerCase() !== (upText || '').toLowerCase()) {
            addressParts.push(distText);
        }
        const compiledAddress = addressParts.join(', ');

        const form = document.getElementById('checkoutForm');
        const formData = new FormData(form);
        formData.set('customer_name', (document.getElementById('customer_name')?.value || '').trim());
        formData.set('customer_phone', cleanPhone);
        formData.set('customer_address', compiledAddress);

        axios.post('{{ route('checkout.process') }}', formData)
            .then(res => {
                if (res.data && res.data.success && res.data.redirect_url) {
                    window.location.replace(res.data.redirect_url);
                } else {
                    isSubmitting = false;
                    displayToast((res.data && res.data.message) ? res.data.message : 'অর্ডার সম্পন্ন করতে সমস্যা হয়েছে');
                    resetSubmitBtns(submitBtns);
                }
            })
            .catch(err => {
                isSubmitting = false;
                let msg = err.response?.data?.message || 'অনুগ্রহ করে সব তথ্য সঠিকভাবে পূরণ করুন';
                if (err.response?.data?.errors) {
                    const firstErr = Object.values(err.response.data.errors)[0];
                    msg = (Array.isArray(firstErr) ? firstErr[0] : firstErr) || msg;
                }
                displayToast(msg);
                resetSubmitBtns(submitBtns);
            });
    };

    function resetSubmitBtns(btns) {
        btns.forEach(b => {
            b.disabled = false;
            b.innerHTML = `<span>অর্ডার নিশ্চিত করুন</span> <i class="fa-solid fa-circle-check fs-14"></i>`;
        });
    }

    function displayToast(msg) {
        if (typeof showToast === 'function') {
            showToast(msg, 'error');
        } else if (typeof window.showToast === 'function') {
            window.showToast(msg, 'error');
        } else {
            alert(msg);
        }
    }

    let leadTimeout = null;
    function handleCustomerInfoChange() {
        const name = document.getElementById('customer_name')?.value || '';
        const phone = document.getElementById('customer_phone')?.value || '';
        const rawStreet = document.getElementById('customer_address')?.value || '';
        const street = extractOnlyStreetAddress(rawStreet);
        const districtType = document.getElementById('hiddenDistrictType')?.value || 'ঢাকা';

        clearTimeout(leadTimeout);
        leadTimeout = setTimeout(() => {
            if (phone.length >= 6 || name.length >= 3) {
                const token = document.getElementById('checkoutDeviceToken')?.value || '';
                axios.post('{{ route('checkout.abandoned_lead') }}', {
                    customer_name: name,
                    customer_phone: phone,
                    customer_address: street,
                    district: districtType,
                    device_token: token,
                    _token: '{{ csrf_token() }}'
                }).catch(() => {});
            }
        }, 1200);
    }
    window.handleCustomerInfoChange = handleCustomerInfoChange;

    function initAll() {
        const addrEl = document.getElementById('customer_address');
        const rawAddr = addrEl ? addrEl.value : '';
        if (addrEl && rawAddr) {
            addrEl.value = extractOnlyStreetAddress(rawAddr);
        }
        initSelect2Boxes();
        updateMobileFooterUI();

        if (rawAddr && rawAddr.includes('বিভাগ:')) {
            const divMatch = rawAddr.match(/বিভাগ\s*:\s*([^,]+)/);
            const distMatch = rawAddr.match(/জেলা\s*:\s*([^,]+)/);
            const upMatch = rawAddr.match(/থানা(?:\/উপজেলা)?\s*:\s*([^,]+)/);
            if (divMatch && divMatch[1]) {
                const targetDiv = divMatch[1].trim();
                const divEl = document.getElementById('geoDivisionSelect');
                if (divEl && !divEl.value) {
                    for (let opt of divEl.options) {
                        const optBn = opt.getAttribute('data-bn') || opt.dataset?.bn || opt.textContent;
                        if (optBn && (optBn.includes(targetDiv) || targetDiv.includes(optBn))) {
                            divEl.value = opt.value;
                            $(divEl).trigger('change');
                            window.onDivisionChange(opt.value);
                            break;
                        }
                    }
                    if (distMatch && distMatch[1]) {
                        const targetDist = distMatch[1].trim();
                        setTimeout(() => {
                            const distEl = document.getElementById('geoDistrictSelect');
                            if (distEl) {
                                for (let opt of distEl.options) {
                                    const optBn = opt.getAttribute('data-bn') || opt.dataset?.bn || opt.textContent;
                                    if (optBn && (optBn.includes(targetDist) || targetDist.includes(optBn))) {
                                        distEl.value = opt.value;
                                        $(distEl).trigger('change');
                                        window.onDistrictChange(opt.value);
                                        break;
                                    }
                                }
                                if (upMatch && upMatch[1]) {
                                    const targetUp = upMatch[1].trim();
                                    setTimeout(() => {
                                        const upEl = document.getElementById('geoUpazilaSelect');
                                        if (upEl) {
                                            for (let opt of upEl.options) {
                                                const optBn = opt.getAttribute('data-bn') || opt.dataset?.bn || opt.textContent;
                                                if (optBn && (optBn.includes(targetUp) || targetUp.includes(optBn))) {
                                                    upEl.value = opt.value;
                                                    $(upEl).trigger('change');
                                                    window.onUpazilaChange(opt.value);
                                                    break;
                                                }
                                            }
                                        }
                                    }, 200);
                                }
                            }
                        }, 200);
                    }
                }
            }
        }
    }

    function safeInit() {
        initAll();

        if (window.ZippyTracker && typeof window.ZippyTracker.trackBeginCheckout === 'function') {
            window.ZippyTracker.trackBeginCheckout({
                value: {{ (float)($subtotal ?? 0) }},
                content_ids: {!! json_encode($dlContentIds) !!},
                items: {!! json_encode($dlCheckoutItems, JSON_UNESCAPED_UNICODE) !!}
            });
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', safeInit);
    } else {
        safeInit();
    }

    if (!window._zkCheckoutTurboBound) {
        window._zkCheckoutTurboBound = true;
        document.addEventListener('turbo:load', () => {
            if (document.getElementById('checkoutForm') && typeof window.reinitCheckoutGeo === 'function') {
                window.reinitCheckoutGeo();
            }
        });
        document.addEventListener('turbo:render', () => {
            if (document.getElementById('checkoutForm') && typeof window.reinitCheckoutGeo === 'function') {
                window.reinitCheckoutGeo();
            }
        });
        document.addEventListener('turbo:before-cache', () => {
            if (typeof window.jQuery !== 'undefined' && window.jQuery.fn && window.jQuery.fn.select2) {
                try { window.jQuery('#geoDivisionSelect').select2('destroy'); } catch(e) {}
                try { window.jQuery('#geoDistrictSelect').select2('destroy'); } catch(e) {}
                try { window.jQuery('#geoUpazilaSelect').select2('destroy'); } catch(e) {}
                window.jQuery('.select2-container').remove();
            }
        });
    }
    window.reinitCheckoutGeo = safeInit;
})();
</script>
@endpush