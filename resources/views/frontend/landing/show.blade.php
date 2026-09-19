<!DOCTYPE html>
<html lang="bn" data-bs-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $product->title }} | স্পেশাল অফার - {{ $settings['store_name'] ?? 'Zippy' }}</title>
    <meta name="description" content="{{ $product->short_desc ?: $product->title }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;500;600;700&family=Outfit:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css">

    <style>
        :root {
            --primary: #0f172a;
            --accent: #ff385c;
            --font-main: 'Hind Siliguri', sans-serif;
            --font-heading: 'Outfit', 'Hind Siliguri', sans-serif;
        }
        body {
            font-family: var(--font-main);
            background-color: #f8fafc;
            color: #1e293b;
            line-height: 1.6;
        }
        .font-heading { font-family: var(--font-heading); }
        .lp-header {
            background: #ffffff;
            border-bottom: 1px solid #e2e8f0;
            padding: 14px 0;
        }
        .lp-topbar {
            background: #000000;
            color: #ffffff;
            font-size: 0.82rem;
            padding: 8px 0;
            text-align: center;
            font-weight: 600;
        }
        .lp-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.04);
        }
        .lp-badge-urgent {
            background: #fee2e2;
            color: #dc2626;
            border: 1px solid #fecaca;
            border-radius: 9999px;
            font-size: 0.76rem;
            font-weight: 700;
            padding: 4px 12px;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .lp-btn-cta {
            background: #000000;
            color: #ffffff;
            border: none;
            border-radius: 9999px;
            padding: 16px 28px;
            font-size: 1.1rem;
            font-weight: 700;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.25);
            transition: all 0.2s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }
        .lp-btn-cta:hover {
            background: #1e293b;
            color: #ffffff;
            transform: translateY(-2px);
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.35);
        }
        .lp-input, .lp-select {
            height: 48px;
            border-radius: 10px;
            border: 1.5px solid #cbd5e1;
            font-size: 0.95rem;
            padding: 0.5rem 1rem;
        }
        .lp-input:focus, .lp-select:focus {
            border-color: #000000;
            box-shadow: 0 0 0 3px rgba(0, 0, 0, 0.08);
            outline: none;
        }
        .lp-bump-box {
            background: #fffcf5;
            border: 2px dashed #000000;
            border-radius: 14px;
            padding: 14px 18px;
            margin-bottom: 20px;
        }
        .lp-trust-item {
            display: flex;
            align-items: center;
            gap: 10px;
            background: #f1f5f9;
            border-radius: 12px;
            padding: 12px;
            font-size: 0.85rem;
            font-weight: 600;
        }
        .main-gallery-thumb {
            width: 100%;
            height: 380px;
            object-fit: cover;
            border-radius: 14px;
        }
        @media (max-width: 768px) {
            .main-gallery-thumb {
                height: 280px;
            }
        }
    </style>
</head>
<body>
    <div class="lp-topbar">
        <span><i class="fa-solid fa-fire text-danger me-1"></i> সীমিত সময়ের ধামাকা অফার! সারাদেশে ক্যাশ অন ডেলিভারি সুবিধা</span>
    </div>

    <header class="lp-header">
        <div class="container d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center gap-2">
                <span class="fs-4 fw-bold font-heading text-dark">{{ $settings['store_name'] ?? 'Zippy' }}</span>
                <span class="badge bg-black text-white rounded-pill px-2 py-0.5" style="font-size: 0.68rem;">অফিসিয়াল স্টোর</span>
            </div>
            @php $supportPhone = $settings['store_phone'] ?? ($settings['phone'] ?? '01700000000'); @endphp
            <a href="tel:{{ $supportPhone }}" class="btn btn-sm btn-outline-dark rounded-pill px-3 py-1.5 fw-semibold d-inline-flex align-items-center gap-2">
                <i class="fa-solid fa-phone-volume"></i>
                <span>{{ $supportPhone }}</span>
            </a>
        </div>
    </header>

    <main class="py-4 py-md-5">
        <div class="container max-w-5xl">
            <div class="text-center mb-4">
                <span class="lp-badge-urgent mb-2">
                    <i class="fa-solid fa-bolt"></i> স্পেশাল স্টক ক্লিয়ারেন্স ডিসকাউন্ট
                </span>
                <h1 class="font-heading fw-bold fs-2 text-dark mt-2 mb-2">{{ $product->title }}</h1>
                @if($product->reviews_count > 0 && $product->rating > 0)
                    <div class="d-flex align-items-center justify-content-center gap-2 text-warning fs-6">
                        @php $lpStars = (int) round($product->rating); @endphp
                        @for($i = 1; $i <= 5; $i++)
                            <i class="fa-solid fa-star {{ $i <= $lpStars ? 'text-warning' : 'text-muted text-opacity-25' }}"></i>
                        @endfor
                        <span class="text-dark fw-bold ms-1 font-heading">{{ number_format($product->rating, 1) }}</span>
                        <span class="text-muted small">({{ $product->reviews_count }} ভেরিফাইড রিভিউ)</span>
                    </div>
                @endif
            </div>

            <div class="row g-4 mb-5">
                <div class="col-12 col-lg-6">
                    <div class="lp-card p-3 p-md-4">
                        <div class="swiper mainLpSwiper mb-3">
                            <div class="swiper-wrapper">
                                @foreach($galleryImages as $gImg)
                                    <div class="swiper-slide">
                                        <img src="{{ $gImg }}" alt="{{ $product->title }}" class="main-gallery-thumb border">
                                    </div>
                                @endforeach
                            </div>
                            <div class="swiper-button-next text-dark"></div>
                            <div class="swiper-button-prev text-dark"></div>
                        </div>

                        <div class="p-3 bg-light rounded-3 border mb-3">
                            <div class="d-flex align-items-baseline justify-content-between flex-wrap gap-2">
                                <div>
                                    <span class="text-muted small d-block">অফার মূল্য:</span>
                                    <span class="fs-2 fw-bold text-danger font-heading">৳ {{ number_format($product->price, 0) }}</span>
                                    @if($product->old_price && $product->old_price > $product->price)
                                        <del class="text-muted fs-6 font-mono ms-2">৳ {{ number_format($product->old_price, 0) }}</del>
                                    @endif
                                </div>
                                @if($product->old_price && $product->old_price > $product->price)
                                    @php $saveAmt = $product->old_price - $product->price; @endphp
                                    <span class="badge bg-danger text-white rounded-pill px-3 py-1.5 fw-bold">৳ {{ number_format($saveAmt, 0) }} সাশ্রয়</span>
                                @endif
                            </div>
                        </div>

                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <div class="lp-trust-item">
                                    <i class="fa-solid fa-truck-fast text-primary fs-5"></i>
                                    <span>সরাসরি ক্যাশ অন ডেলিভারি</span>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="lp-trust-item">
                                    <i class="fa-solid fa-box-open text-success fs-5"></i>
                                    <span>পণ্য দেখে পেমেন্ট সুবিধা</span>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="lp-trust-item">
                                    <i class="fa-solid fa-shield-check text-warning fs-5"></i>
                                    <span>১০০% অথেনটিক কোয়ালিটি</span>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="lp-trust-item">
                                    <i class="fa-solid fa-rotate-left text-danger fs-5"></i>
                                    <span>৭ দিনের রিটার্ন সুবিধা</span>
                                </div>
                            </div>
                        </div>

                        <div class="text-center pt-2">
                            <a href="#orderSection" class="lp-btn-cta w-100">
                                <span>অর্ডার করতে নিচে ফর্মটি পূরণ করুন</span>
                                <i class="fa-solid fa-arrow-down"></i>
                            </a>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-lg-6">
                    <div class="lp-card p-4 p-md-5 h-100">
                        <h3 class="font-heading fw-bold fs-5 text-dark mb-3">
                            <i class="fa-solid fa-circle-check text-success me-2"></i>কেন এই পণ্যটি আপনার জন্য সেরা?
                        </h3>
                        @if(!empty($product->short_desc))
                            <p class="text-secondary mb-4 fs-15">{{ $product->short_desc }}</p>
                        @endif

                        @if(!empty($specifications) && count($specifications) > 0)
                            <div class="mb-4">
                                <h6 class="fw-bold text-dark font-heading mb-2">স্পেসিফিকেশন ও ফিচার:</h6>
                                <div class="table-responsive">
                                    <table class="table table-bordered table-sm small mb-0">
                                        <tbody>
                                            @foreach($specifications as $sKey => $sVal)
                                                <tr>
                                                    <th class="bg-light text-dark fw-bold w-40">{{ $sKey }}</th>
                                                    <td>{{ $sVal }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @endif

                        <div class="p-3.5 rounded-3 bg-light border">
                            <h6 class="fw-bold text-dark font-heading mb-2">ডেলিভারি সংক্রান্ত নিয়মাবলী:</h6>
                            <ul class="list-unstyled d-flex flex-column gap-1.5 small text-secondary mb-0">
                                <li><i class="fa-solid fa-circle text-primary me-2" style="font-size: 6px;"></i> ঢাকা সিটির ভেতরে হোম ডেলিভারি ২৪ থেকে ৪৮ ঘণ্টার মধ্যে।</li>
                                <li><i class="fa-solid fa-circle text-primary me-2" style="font-size: 6px;"></i> ঢাকার বাইরে দেশের যেকোনো জেলা বা থানায় ২ থেকে ৩ কার্যদিবস।</li>
                                <li><i class="fa-solid fa-circle text-primary me-2" style="font-size: 6px;"></i> ডেলিভারি ম্যানের সামনে প্রোডাক্ট চেক করে রিসিভ করতে পারবেন।</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            @if(!empty($reviews) && count($reviews) > 0)
                <div class="lp-card p-4 p-md-5 mb-5">
                    <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
                        <div>
                            <h3 class="font-heading fw-bold fs-4 text-dark mb-1">কাস্টমারদের সন্তুষ্টি ও রিভিউ</h3>
                            <span class="text-muted small">সরাসরি পণ্য ব্যবহারকারীদের নির্ভরযোগ্য মতামত</span>
                        </div>
                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-1.5 fw-bold font-mono">
                            ১০০% ভেরিফাইড ক্রেতা
                        </span>
                    </div>

                    <div class="row g-3">
                        @foreach($reviews as $r)
                            @php
                                $rImgs = !empty($r->images) ? (is_array($r->images) ? $r->images : (json_decode($r->images, true) ?: [])) : (!empty($r->photo) ? [$r->photo] : []);
                            @endphp
                            <div class="col-12 col-md-6">
                                <div class="p-3.5 rounded-3 bg-light border h-100">
                                    <div class="d-flex align-items-center justify-content-between mb-1.5">
                                        <span class="fw-bold text-dark fs-14"><i class="fa-solid fa-circle-user text-primary me-1.5"></i>{{ $r->customer_name }}</span>
                                        <div class="text-warning small">
                                            @for($s = 1; $s <= 5; $s++)
                                                <i class="fa-solid fa-star {{ $s <= $r->rating ? 'text-warning' : 'text-muted text-opacity-25' }}"></i>
                                            @endfor
                                        </div>
                                    </div>
                                    <p class="small text-secondary mb-2 lh-base">{{ $r->comment }}</p>
                                    @if(!empty($rImgs) && count($rImgs) > 0)
                                        <div class="d-flex gap-2 flex-wrap mt-2">
                                            @foreach($rImgs as $rPhoto)
                                                <a href="{{ asset($rPhoto) }}" target="_blank">
                                                    <img src="{{ asset($rPhoto) }}" alt="Customer Review" class="rounded-2 border" style="width: 54px; height: 54px; object-fit: cover;">
                                                </a>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <div id="orderSection" class="lp-card p-4 p-md-5 border-2 border-dark">
                <div class="text-center mb-4">
                    <span class="badge bg-black text-white px-3 py-1.5 rounded-pill fw-bold font-heading mb-2">সহজ ১-ক্লিক অর্ডার ফর্ম</span>
                    <h2 class="font-heading fw-bold fs-3 text-dark mb-1">অর্ডার কনফার্ম করতে আপনার সঠিক ঠিকানা লিখুন</h2>
                    <p class="text-muted small mb-0">অর্ডার প্লেস করার পর আমাদের টিম থেকে ফোন দিয়ে অর্ডারটি কনফার্ম করা হবে।</p>
                </div>

                <form id="lpCheckoutForm" onsubmit="submitLpOrder(event)">
                    @csrf
                    <input type="hidden" name="device_token" value="{{ $deviceToken ?? '' }}">
                    <input type="hidden" name="district" id="lpDistrictType" value="ঢাকা">
                    <input type="hidden" name="payment_method" value="cod">

                    <div class="row g-3 mb-4">
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold text-dark fs-14">আপনার নাম <span class="text-danger">*</span></label>
                            <input type="text" name="customer_name" id="lpCustomerName" class="form-control lp-input" placeholder="আপনার পুরো নাম লিখুন" required>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold text-dark fs-14">মোবাইল নম্বর <span class="text-danger">*</span></label>
                            <input type="tel" name="customer_phone" id="lpCustomerPhone" class="form-control lp-input font-mono" placeholder="017XXXXXXXX" required>
                        </div>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold text-dark fs-14">ডেলিভারি এরিয়া / জেলা <span class="text-danger">*</span></label>
                            <select class="form-select lp-select" id="lpDeliveryArea" onchange="handleLpAreaChange(this.value)" required>
                                <option value="ঢাকা" selected>ঢাকা সিটির ভেতরে (চার্জ ৳ {{ number_format($shippingInside, 0) }})</option>
                                <option value="ঢাকার বাইরে">ঢাকার বাইরে যেকোনো জেলা (চার্জ ৳ {{ number_format($shippingOutside, 0) }})</option>
                            </select>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold text-dark fs-14">বিস্তারিত ঠিকানা (বাসা/রোড/এলাকা) <span class="text-danger">*</span></label>
                            <input type="text" name="customer_address" id="lpCustomerAddress" class="form-control lp-input" placeholder="যেমন: রোড # ৪, বাড়ি # ১২, মিরপুর-১০, ঢাকা" required>
                        </div>
                    </div>

                    @if(!empty($orderBump))
                        <div class="lp-bump-box">
                            <div class="d-flex align-items-start gap-3">
                                <div class="form-check mt-1">
                                    <input class="form-check-input" type="checkbox" name="include_bump" id="lpIncludeBump" value="1" onchange="toggleLpBump(this)" style="cursor: pointer; width: 1.4em; height: 1.4em;">
                                    <input type="hidden" name="bump_id" value="{{ $orderBump->id }}">
                                </div>
                                <div class="flex-shrink-0">
                                    <img src="{{ !empty($orderBump->bump_image) ? $orderBump->bump_image : $product->main_image }}" alt="{{ $orderBump->bump_product_title }}" class="rounded-3 border" style="width: 60px; height: 60px; object-fit: cover;">
                                </div>
                                <div class="flex-grow-1">
                                    <div class="d-flex align-items-center gap-2 mb-1 flex-wrap">
                                        <span class="badge bg-danger text-white fs-10 px-2 py-0.5 rounded-pill fw-bold">এককালীন স্পেশাল অফার</span>
                                        <span class="fs-14 fw-bold text-dark">{{ $orderBump->title }}</span>
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

                    <div class="p-3.5 bg-light rounded-3 border mb-4">
                        <div class="d-flex justify-content-between align-items-center fs-14 text-muted mb-2">
                            <span>প্রোডাক্ট মূল্য:</span>
                            <span class="text-dark fw-bold font-mono">৳ {{ number_format($product->price, 0) }}</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center fs-14 text-dark fw-bold mb-2 d-none" id="lpBumpPriceRow">
                            <span>অর্ডার বাম্প (স্পেশাল অফার):</span>
                            <span class="fw-bold font-mono text-danger">+ ৳ {{ !empty($orderBump) ? number_format($orderBump->price, 0) : 0 }}</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center fs-14 text-muted mb-2">
                            <span>ডেলিভারি চার্জ:</span>
                            <span class="text-dark fw-bold font-mono" id="lpShippingText">৳ {{ number_format($shippingInside, 0) }}</span>
                        </div>
                        <hr class="my-2 border-secondary opacity-25">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="fs-16 fw-bold text-dark">সর্বমোট প্রদেয়:</span>
                            <span class="fs-22 fw-extrabold text-black font-mono" id="lpGrandTotalText">৳ {{ number_format($product->price + $shippingInside, 0) }}</span>
                        </div>
                    </div>

                    <div id="lpAlertError" class="alert alert-danger d-none mb-3"></div>

                    <button type="submit" id="lpSubmitBtn" class="lp-btn-cta w-100 py-3.5 fs-5">
                        <span>অর্ডার নিশ্চিত করুন (ক্যাশ অন ডেলিভারি)</span>
                        <i class="fa-solid fa-circle-check"></i>
                    </button>
                    <div class="text-center mt-3">
                        <span class="fs-12 text-muted d-inline-flex align-items-center gap-1.5">
                            <i class="fa-solid fa-lock text-dark"></i>
                            <span>আপনার তথ্য সম্পূর্ণ সুরক্ষিত ও এনক্রিপ্টেড।</span>
                        </span>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <footer class="py-4 bg-white border-top text-center text-muted small">
        <div class="container">
            <p class="mb-1 fw-semibold text-dark">{{ $settings['store_name'] ?? 'Zippy' }} - প্রিমিয়াম গ্যাজেট ও লাইফস্টাইল স্টোর বাংলাদেশ</p>
            <p class="mb-0">সহায়তার জন্য কল করুন: {{ $supportPhone }} | কপিরাইট © {{ date('Y') }} সর্বস্বত্ব সংরক্ষিত</p>
        </div>
    </footer>

    <script src="{{ asset('lib/axios.min.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
    <script>
    (() => {
        new Swiper('.mainLpSwiper', {
            loop: true,
            navigation: {
                nextEl: '.swiper-button-next',
                prevEl: '.swiper-button-prev',
            },
        });

        let baseProductPrice = {{ (float)$product->price }};
        let shippingInside = {{ (float)$shippingInside }};
        let shippingOutside = {{ (float)$shippingOutside }};
        let currentShipping = shippingInside;
        let bumpPrice = {{ !empty($orderBump) ? (float)$orderBump->price : 0 }};
        let isBumpChecked = false;
        let isSubmitting = false;

        window.handleLpAreaChange = function(val) {
            const hiddenDist = document.getElementById('lpDistrictType');
            if (hiddenDist) hiddenDist.value = val;
            currentShipping = (val === 'ঢাকা') ? shippingInside : shippingOutside;
            document.getElementById('lpShippingText').innerText = '৳ ' + currentShipping.toLocaleString('en-US');
            recalcLpTotal();
        };

        window.toggleLpBump = function(checkbox) {
            isBumpChecked = checkbox.checked;
            const bumpRow = document.getElementById('lpBumpPriceRow');
            if (bumpRow) bumpRow.classList.toggle('d-none', !isBumpChecked);
            recalcLpTotal();
        };

        function recalcLpTotal() {
            let total = baseProductPrice + currentShipping + (isBumpChecked ? bumpPrice : 0);
            document.getElementById('lpGrandTotalText').innerText = '৳ ' + total.toLocaleString('en-US');
        }

        window.submitLpOrder = function(e) {
            e.preventDefault();
            if (isSubmitting) return;

            const name = (document.getElementById('lpCustomerName')?.value || '').trim();
            const phone = (document.getElementById('lpCustomerPhone')?.value || '').trim();
            const address = (document.getElementById('lpCustomerAddress')?.value || '').trim();
            const errBox = document.getElementById('lpAlertError');

            if (!name) {
                showLpError('আপনার পুরো নাম লিখুন।');
                return;
            }
            const cleanPhone = phone.replace(/[\s\-\(\)]/g, '');
            if (!/^(?:\+?88)?01[3-9]\d{8}$/.test(cleanPhone)) {
                showLpError('সঠিক ১১ ডিজিটের মোবাইল নম্বর লিখুন (যেমন: 017XXXXXXXX)।');
                return;
            }
            if (!address) {
                showLpError('আপনার পূর্ণাঙ্গ ডেলিভারি ঠিকানা লিখুন।');
                return;
            }

            if (errBox) errBox.classList.add('d-none');
            isSubmitting = true;
            const btn = document.getElementById('lpSubmitBtn');
            if (btn) {
                btn.disabled = true;
                btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span><span>অর্ডার হচ্ছে...</span>';
            }

            const form = document.getElementById('lpCheckoutForm');
            const formData = new FormData(form);
            formData.set('customer_name', name);
            formData.set('customer_phone', cleanPhone);
            formData.set('customer_address', address);

            axios.post('{{ route('checkout.process') }}', formData)
                .then(res => {
                    if (res.data && res.data.success && res.data.redirect_url) {
                        window.location.replace(res.data.redirect_url);
                    } else {
                        isSubmitting = false;
                        showLpError((res.data && res.data.message) ? res.data.message : 'অর্ডার সম্পন্ন করতে সমস্যা হয়েছে।');
                        resetBtn();
                    }
                })
                .catch(err => {
                    isSubmitting = false;
                    let msg = err.response?.data?.message || 'অনুগ্রহ করে সকল তথ্য সঠিকভাবে পূরণ করুন।';
                    if (err.response?.data?.errors) {
                        const firstErr = Object.values(err.response.data.errors)[0];
                        msg = (Array.isArray(firstErr) ? firstErr[0] : firstErr) || msg;
                    }
                    showLpError(msg);
                    resetBtn();
                });
        };

        function showLpError(msg) {
            const errBox = document.getElementById('lpAlertError');
            if (errBox) {
                errBox.innerText = msg;
                errBox.classList.remove('d-none');
                errBox.scrollIntoView({ behavior: 'smooth', block: 'center' });
            } else {
                alert(msg);
            }
        }

        function resetBtn() {
            const btn = document.getElementById('lpSubmitBtn');
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = '<span>অর্ডার নিশ্চিত করুন (ক্যাশ অন ডেলিভারি)</span> <i class="fa-solid fa-circle-check"></i>';
            }
        }
    })();
    </script>
</body>
</html>

