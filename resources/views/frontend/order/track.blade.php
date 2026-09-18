@extends('frontend.layouts.app')

@php
    $settings = \App\Services\Frontend\FrontendCacheService::settings();
    $whatsappPhone = $settings['whatsapp_number'] ?? '8801700000000';
    $storeName = $settings['store_name'] ?? 'ZippyBD';
@endphp

@section('title', 'লাইভ পার্সেল ও অর্ডার ট্র্যাকিং | ' . $storeName)
@section('meta_description', $storeName . '-এর লাইভ অর্ডার ট্র্যাকিং পেজে আপনার অর্ডার নম্বর বা ফোন নম্বর দিয়ে পার্সেলের রিয়েল-টাইম লাইভ টাইমলাইন ও ডেলিভারি অগ্রগতি ট্র্যাক করুন।')

@section('content')
<div class="tracking-page-wrapper py-4 py-md-5">
    <div class="container" style="max-width: 1040px;">

        <div class="tracking-hero-card p-4 p-md-5 rounded-4 mb-4 text-white position-relative overflow-hidden shadow-sm">
            <div class="row align-items-center position-relative z-2 g-3 g-md-4">
                <div class="col-12 col-lg-5 text-center text-lg-start">
                    <span class="badge bg-white bg-opacity-10 text-white border border-white border-opacity-15 rounded-pill px-3 py-1.5 font-heading small mb-2 d-inline-flex align-items-center">
                        <span class="live-pulse-dot me-2"></span> রিয়েল-টাইম লাইভ ট্র্যাকিং
                    </span>
                    <h1 class="fw-bold mb-2 font-heading text-white fs-4 fs-md-3">আপনার অর্ডার ট্র্যাক করুন</h1>
                    <p class="text-white-50 small mb-0 fs-13">অর্ডার আইডি (যেমন: ZB-A5W6B8) অথবা আপনার মোবাইল নম্বর লিখুন</p>
                </div>

                <div class="col-12 col-lg-7">
                    <form action="{{ route('order.track') }}" method="GET" class="m-0">
                        <div class="tracking-search-box d-flex align-items-center bg-white rounded-pill p-1.5 shadow-sm">
                            <i class="fa-solid fa-magnifying-glass text-secondary ms-3 me-2 flex-shrink-0 fs-15"></i>
                            <input type="text" 
                                   name="track_id" 
                                   value="{{ $query ?? request('track_id', request('query', request('tracking_input', ''))) }}" 
                                   class="form-control border-0 shadow-none px-2 font-monospace fw-bold text-dark bg-transparent fs-14" 
                                   placeholder="যেমন: ZB-A5W6B8 বা মোবাইল নম্বর..." 
                                   required 
                                   autocomplete="off">
                            @if(!empty($query))
                                <a href="javascript:void(0)" onclick="quickTrackOrder('')" class="text-secondary text-decoration-none me-2 p-1.5" title="মুছে ফেলুন">
                                    <i class="fa-solid fa-circle-xmark fs-15"></i>
                                </a>
                            @endif
                            <button type="submit" class="btn btn-dark rounded-pill px-3 px-md-4 py-2 fw-bold font-heading d-flex align-items-center gap-2 flex-shrink-0 text-white shadow-none fs-13">
                                <span class="d-none d-sm-inline">ট্র্যাক করুন</span>
                                <span class="d-inline d-sm-none">ট্র্যাক</span>
                                <i class="fa-solid fa-arrow-right fs-12"></i>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            <div class="hero-decorative-circle"></div>
        </div>

        <div id="trackingDynamicContainer">
            @if(!isset($order) && empty($query))
                <div class="row g-3 g-md-4 justify-content-center">
                    <div class="col-12 col-md-4">
                        <div class="tracking-guide-card bg-white p-4 rounded-4 shadow-xs h-100 border text-center">
                            <div class="guide-icon-box rounded-3 mx-auto mb-3 d-flex align-items-center justify-content-center">
                                <i class="fa-solid fa-barcode fs-5"></i>
                            </div>
                            <h6 class="fw-bold text-dark font-heading mb-2 fs-14">অর্ডার আইডি ট্র্যাকিং</h6>
                            <p class="text-secondary small mb-0 fs-12">অর্ডারের সময় পাওয়া <span class="text-dark font-monospace fw-semibold">ZB-A5W6B8</span> কোড দিয়ে সহজে ট্র্যাক করুন।</p>
                        </div>
                    </div>

                    <div class="col-12 col-md-4">
                        <div class="tracking-guide-card bg-white p-4 rounded-4 shadow-xs h-100 border text-center">
                            <div class="guide-icon-box rounded-3 mx-auto mb-3 d-flex align-items-center justify-content-center">
                                <i class="fa-solid fa-mobile-screen-button fs-5"></i>
                            </div>
                            <h6 class="fw-bold text-dark font-heading mb-2 fs-14">ফোন নম্বর দিয়ে ট্র্যাকিং</h6>
                            <p class="text-secondary small mb-0 fs-12">যে মোবাইল নম্বর দিয়ে অর্ডার করেছেন, সেটি লিখলেই অর্ডারের লাইভ অগ্রগতি দেখা যাবে।</p>
                        </div>
                    </div>

                    <div class="col-12 col-md-4">
                        <div class="tracking-guide-card bg-white p-4 rounded-4 shadow-xs h-100 border text-center">
                            <div class="guide-icon-box rounded-3 mx-auto mb-3 d-flex align-items-center justify-content-center">
                                <i class="fa-solid fa-truck-fast fs-5"></i>
                            </div>
                            <h6 class="fw-bold text-dark font-heading mb-2 fs-14">রিয়েল-টাইম কুরিয়ার সিঙ্ক</h6>
                            <p class="text-secondary small mb-0 fs-12">Steadfast, Pathao ও RedX হাবের সাথে সরাসরি সিঙ্ক হওয়া লাইভ ডেলিভারি স্ট্যাটাস।</p>
                        </div>
                    </div>
                </div>

                @if(isset($deviceOrders) && count($deviceOrders) > 0)
                    <div class="bg-white p-3 p-md-4 rounded-4 shadow-xs mt-4 border">
                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3 pb-3 border-bottom">
                            <div class="d-flex align-items-center gap-2">
                                <div class="rounded-circle bg-light border d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                                    <i class="fa-solid fa-clock-rotate-left text-dark fs-13"></i>
                                </div>
                                <h6 class="fw-bold text-dark font-heading mb-0 fs-14">এই ডিভাইসের সাম্প্রতিক অর্ডারসমূহ</h6>
                            </div>
                            <span class="badge bg-light text-secondary border rounded-pill px-2.5 py-1 fs-11">অটো-ডিটেক্টেড</span>
                        </div>

                        <div class="row g-2 g-md-3">
                            @foreach($deviceOrders as $devOrder)
                                <div class="col-12 col-md-6 col-lg-4">
                                    <a href="javascript:void(0)" onclick="quickTrackOrder('{{ $devOrder->order_number }}')" class="d-flex justify-content-between align-items-center p-3 rounded-3 bg-light text-decoration-none text-dark border recent-order-item transition">
                                        <div>
                                            <span class="fw-bold font-monospace text-dark d-block fs-13">{{ $devOrder->order_number }}</span>
                                            <span class="text-secondary fs-11">{{ \Carbon\Carbon::parse($devOrder->created_at)->format('d M Y, h:i A') }}</span>
                                        </div>
                                        <div class="text-end">
                                            <span class="badge bg-dark rounded-pill px-2 py-1 small text-uppercase mb-1 d-inline-block fs-10">{{ $devOrder->order_status ?? ($devOrder->status ?? 'Pending') }}</span>
                                            <span class="fw-bold text-dark d-block font-heading fs-12">৳ {{ number_format($devOrder->total ?? 0, 0) }}</span>
                                        </div>
                                    </a>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            @endif

            @if(isset($order) && $order)
                @php
                    $delStatus = strtolower($order->delivery_status ?? '');
                    $ordStatus = strtolower($order->order_status ?? ($order->status ?? 'pending'));
                    $courierRaw = strtolower($order->courier_status ?? '');

                    $step = 1;
                    $statusLabel = 'অর্ডার প্লেসড';
                    $badgeClass = 'status-badge-placed';

                    if (in_array($delStatus, ['cancelled']) || in_array($ordStatus, ['cancelled', 'canceled', 'rejected'])) {
                        $step = -1;
                        $statusLabel = 'অর্ডার বাতিল';
                        $badgeClass = 'status-badge-cancelled';
                    } elseif (in_array($delStatus, ['returned']) || in_array($courierRaw, ['return', 'returned', 'failed'])) {
                        $step = -2;
                        $statusLabel = 'পার্সেল রিটার্নড';
                        $badgeClass = 'status-badge-returned';
                    } elseif ($delStatus === 'delivered' || $ordStatus === 'delivered' || in_array($courierRaw, ['delivered', 'completed', 'successful'])) {
                        $step = 5;
                        $statusLabel = 'ডেলিভার্ড';
                        $badgeClass = 'status-badge-delivered';
                    } elseif ($delStatus === 'out_for_delivery' || in_array($courierRaw, ['out_for_delivery', 'rider_assigned', 'assigned_for_delivery'])) {
                        $step = 4;
                        $statusLabel = 'আউট ফর ডেলিভারি';
                        $badgeClass = 'status-badge-transit';
                    } elseif ($delStatus === 'in_transit' || $ordStatus === 'shipped' || in_array($courierRaw, ['in_transit', 'picked_up', 'dispatched', 'sorting_hub', 'hold'])) {
                        $step = 3;
                        $statusLabel = 'কুরিয়ারে হস্তান্তর';
                        $badgeClass = 'status-badge-shipped';
                    } elseif ($delStatus === 'processing' || in_array($ordStatus, ['processing', 'confirmed', 'packed', 'warehouse']) || in_array($courierRaw, ['in_review', 'pickup_pending', 'ready_for_pickup'])) {
                        $step = 2;
                        $statusLabel = 'প্রসেসিং হচ্ছে';
                        $badgeClass = 'status-badge-processing';
                    } else {
                        $step = 1;
                        $statusLabel = 'অর্ডার প্লেসড';
                        $badgeClass = 'status-badge-placed';
                    }

                    $courierName = strtolower(trim($order->courier_name ?? ($order->courier_provider ?? '')));
                    $trackingCode = $order->tracking_code ?? ($order->courier_tracking_code ?? '');
                    $consignmentId = $order->consignment_id ?? ($order->courier_consignment_id ?? '');

                    $externalTrackingUrl = null;
                    if ($courierName === 'steadfast' && $trackingCode) {
                        $externalTrackingUrl = "https://steadfast.com.bd/t/" . urlencode($trackingCode);
                    } elseif ($courierName === 'pathao') {
                        $pId = $consignmentId ?: $trackingCode;
                        if ($pId) {
                            $externalTrackingUrl = "https://merchant.pathao.com/tracking?consignment_id=" . urlencode($pId);
                        }
                    } elseif ($courierName === 'redx') {
                        $rId = $trackingCode ?: $consignmentId;
                        if ($rId) {
                            $externalTrackingUrl = "https://redx.com.bd/track-order?trackingId=" . urlencode($rId);
                        }
                    }

                    $historyEvents = [];
                    if (!empty($order->courier_history)) {
                        $decodedHistory = json_decode($order->courier_history, true);
                        if (is_array($decodedHistory)) {
                            $historyEvents = $decodedHistory;
                        }
                    }

                    if (empty($historyEvents)) {
                        $createdAt = $order->created_at ? \Carbon\Carbon::parse($order->created_at) : now();
                        $historyEvents[] = [
                            'status' => 'pending',
                            'title' => 'অর্ডার প্লেসড',
                            'message' => 'গ্রাহক দ্বারা অর্ডার সফলভাবে সম্পন্ন হয়েছে।',
                            'location' => 'অনলাইন স্টোর',
                            'timestamp' => $createdAt->format('Y-m-d H:i:s')
                        ];

                        if ($step >= 2) {
                            $historyEvents[] = [
                                'status' => 'processing',
                                'title' => 'ওয়্যারহাউস প্রসেসিং',
                                'message' => 'অর্ডারটি কিউসি ভেরিফিকেশন ও প্যাকিং শেষ হয়েছে।',
                                'location' => 'সেন্ট্রাল ওয়্যারহাউস',
                                'timestamp' => $createdAt->copy()->addHours(2)->format('Y-m-d H:i:s')
                            ];
                        }

                        if ($step >= 3) {
                            $historyEvents[] = [
                                'status' => 'in_transit',
                                'title' => 'কুরিয়ারে হস্তান্তর (' . ($courierName ? ucfirst($courierName) : 'এক্সপ্রেস') . ')',
                                'message' => 'পার্সেলটি কুরিয়ার সার্ভিস সেন্টারে ট্রানজিটের জন্য প্রেরণ করা হয়েছে।',
                                'location' => 'ডিস্ট্রিবিউশন হাব',
                                'timestamp' => $createdAt->copy()->addHours(5)->format('Y-m-d H:i:s')
                            ];
                        }

                        if ($step >= 4) {
                            $historyEvents[] = [
                                'status' => 'out_for_delivery',
                                'title' => 'আউট ফর ডেলিভারি',
                                'message' => 'ডেলিভারি রাইডার আপনার গন্তব্যের উদ্দেশ্যে রওয়ানা হয়েছেন।',
                                'location' => $order->district ?? 'লোকাল ডেলিভারি হাব',
                                'timestamp' => $createdAt->copy()->addHours(8)->format('Y-m-d H:i:s')
                            ];
                        }

                        if ($step >= 5) {
                            $historyEvents[] = [
                                'status' => 'delivered',
                                'title' => 'পার্সেল ডেলিভার্ড',
                                'message' => 'গ্রাহকের নিকট সফলভাবে পার্সেল বুঝিয়ে দেওয়া হয়েছে।',
                                'location' => 'ডেলিভারি ঠিকানা',
                                'timestamp' => $order->updated_at ? \Carbon\Carbon::parse($order->updated_at)->format('Y-m-d H:i:s') : $createdAt->copy()->addHours(12)->format('Y-m-d H:i:s')
                            ];
                        }

                        if ($step === -1) {
                            $historyEvents[] = [
                                'status' => 'cancelled',
                                'title' => 'অর্ডার বাতিল',
                                'message' => 'এই অর্ডারটি বাতিল করা হয়েছে।',
                                'location' => 'সিস্টেম',
                                'timestamp' => $order->updated_at ? \Carbon\Carbon::parse($order->updated_at)->format('Y-m-d H:i:s') : now()->format('Y-m-d H:i:s')
                            ];
                        } elseif ($step === -2) {
                            $historyEvents[] = [
                                'status' => 'returned',
                                'title' => 'পার্সেল রিটার্নড',
                                'message' => 'পার্সেল ডেলিভারি সম্ভব না হওয়ায় ওয়্যারহাউসে রিটার্ন পাঠানো হয়েছে।',
                                'location' => 'রিটার্ন হাব',
                                'timestamp' => $order->updated_at ? \Carbon\Carbon::parse($order->updated_at)->format('Y-m-d H:i:s') : now()->format('Y-m-d H:i:s')
                            ];
                        }
                    }

                    $reversedHistory = array_reverse($historyEvents);

                    $orderTotal = (float)($order->total ?? 0);
                    $shippingFee = (float)($order->shipping_cost ?? 0);
                    $subtotal = (float)($order->subtotal ?? ($orderTotal - $shippingFee));
                    $itemCount = $items->sum('quantity') ?: 1;
                @endphp

                <div class="bg-white p-3 p-md-4 rounded-4 shadow-xs mb-4 border">
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 pb-3 mb-3 border-bottom">
                        <div>
                            <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
                                <span class="fs-12 text-secondary">অর্ডার নম্বর:</span>
                                <span class="fw-bold font-monospace text-dark fs-15">{{ $order->order_number }}</span>
                                <button type="button" class="btn btn-sm btn-light border py-0.5 px-2 text-muted rounded-pill fs-11" onclick="navigator.clipboard.writeText('{{ $order->order_number }}'); this.innerHTML='<i class=\'fa-solid fa-check text-success\'></i>'; setTimeout(() => this.innerHTML='<i class=\'fa-regular fa-copy\'></i>', 2000);" title="কপি করুন">
                                    <i class="fa-regular fa-copy"></i>
                                </button>

                                @if($courierName === 'steadfast')
                                    <span class="badge rounded-pill px-2.5 py-1 text-white border fs-11" style="background-color: #059669; border-color: #047857 !important;">
                                        <i class="fa-solid fa-truck-fast me-1"></i> Steadfast Courier
                                    </span>
                                @elseif($courierName === 'pathao')
                                    <span class="badge rounded-pill px-2.5 py-1 text-white border fs-11" style="background-color: #e11d48; border-color: #be123c !important;">
                                        <i class="fa-solid fa-motorcycle me-1"></i> Pathao Logistics
                                    </span>
                                @elseif($courierName === 'redx')
                                    <span class="badge rounded-pill px-2.5 py-1 text-white border fs-11" style="background-color: #dc2626; border-color: #b91c1c !important;">
                                        <i class="fa-solid fa-box-open me-1"></i> RedX Delivery
                                    </span>
                                @else
                                    <span class="badge rounded-pill px-2.5 py-1 text-dark border bg-light fs-11">
                                        <i class="fa-solid fa-truck-ramp-box me-1 text-secondary"></i> Zippy Express
                                    </span>
                                @endif
                            </div>
                            <div class="fs-12 text-secondary">
                                অর্ডার তারিখ: <span class="font-monospace text-dark fw-medium">{{ \Carbon\Carbon::parse($order->created_at)->format('d M Y, h:i A') }}</span>
                            </div>
                        </div>

                        <div class="d-flex align-items-center gap-2">
                            <span class="badge rounded-pill px-3 py-2 fw-bold font-heading fs-12 {{ $badgeClass }}">
                                <i class="fa-solid fa-circle-dot me-1"></i> {{ $statusLabel }}
                            </span>
                        </div>
                    </div>

                    @if(!empty($trackingCode))
                        <div class="tracking-code-banner p-3 rounded-3 mb-4 d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2">
                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                <span class="fs-12 text-secondary fw-semibold">কুরিয়ার ট্র্যাকিং কোড:</span>
                                <span class="font-monospace fw-bold text-dark px-2.5 py-1 rounded bg-white border fs-13">{{ $trackingCode }}</span>
                                <button type="button" class="btn btn-sm btn-white border py-0.5 px-2 text-dark rounded-pill fs-11" onclick="navigator.clipboard.writeText('{{ $trackingCode }}'); this.innerHTML='<i class=\'fa-solid fa-check text-success me-1\'></i>কপি হয়েছে'; setTimeout(() => this.innerHTML='<i class=\'fa-regular fa-copy me-1\'></i>কপি', 2000);">
                                    <i class="fa-regular fa-copy me-1"></i>কপি
                                </button>
                            </div>

                            @if($externalTrackingUrl)
                                <a href="{{ $externalTrackingUrl }}" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-dark rounded-pill px-3 py-1.5 fw-bold font-heading d-flex align-items-center gap-1.5 text-white fs-11">
                                    <span>কুরিয়ার পোর্টালে লাইভ দেখুন</span>
                                    <i class="fa-solid fa-arrow-up-right-from-square fs-10"></i>
                                </a>
                            @endif
                        </div>
                    @endif

                    @if($step >= 1)
                        <div class="stepper-section pt-2 pb-3">
                            <div class="modern-stepper-container">
                                <div class="modern-stepper-track d-flex justify-content-between position-relative">
                                    <div class="step-node-wrap text-center {{ $step >= 1 ? 'is-completed' : '' }} {{ $step == 1 ? 'is-active' : '' }}">
                                        <div class="step-node-icon">
                                            <i class="fa-solid fa-receipt"></i>
                                        </div>
                                        <span class="step-node-title mt-2">অর্ডার প্লেসড</span>
                                    </div>

                                    <div class="step-node-wrap text-center {{ $step >= 2 ? 'is-completed' : '' }} {{ $step == 2 ? 'is-active' : '' }}">
                                        <div class="step-node-icon">
                                            <i class="fa-solid fa-boxes-packing"></i>
                                        </div>
                                        <span class="step-node-title mt-2">প্রসেসিং</span>
                                    </div>

                                    <div class="step-node-wrap text-center {{ $step >= 3 ? 'is-completed' : '' }} {{ $step == 3 ? 'is-active' : '' }}">
                                        <div class="step-node-icon">
                                            <i class="fa-solid fa-truck-fast"></i>
                                        </div>
                                        <span class="step-node-title mt-2">কুরিয়ারে হস্তান্তর</span>
                                    </div>

                                    <div class="step-node-wrap text-center {{ $step >= 4 ? 'is-completed' : '' }} {{ $step == 4 ? 'is-active' : '' }}">
                                        <div class="step-node-icon">
                                            <i class="fa-solid fa-person-biking"></i>
                                        </div>
                                        <span class="step-node-title mt-2">আউট ফর ডেলিভারি</span>
                                    </div>

                                    <div class="step-node-wrap text-center {{ $step >= 5 ? 'is-completed' : '' }} {{ $step == 5 ? 'is-active' : '' }}">
                                        <div class="step-node-icon">
                                            <i class="fa-solid fa-circle-check"></i>
                                        </div>
                                        <span class="step-node-title mt-2">ডেলিভার্ড</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @elseif($step === -1)
                        <div class="alert alert-danger rounded-3 p-3 my-2 fs-13 d-flex align-items-center gap-2.5">
                            <i class="fa-solid fa-circle-xmark fs-5 flex-shrink-0 text-danger"></i>
                            <div>এই অর্ডারটি বাতিল করা হয়েছে। কোনো জিজ্ঞাসা থাকলে আমাদের কাস্টমার সার্ভিসের সাথে যোগাযোগ করুন।</div>
                        </div>
                    @elseif($step === -2)
                        <div class="alert alert-warning rounded-3 p-3 my-2 fs-13 d-flex align-items-center gap-2.5">
                            <i class="fa-solid fa-rotate-left fs-5 flex-shrink-0 text-warning"></i>
                            <div>পার্সেলটি ওয়্যারহাউসে রিটার্ন এসেছে। বিস্তারিত তথ্যের জন্য আমাদের সাপোর্ট টিমে যোগাযোগ করুন।</div>
                        </div>
                    @endif
                </div>

                <div class="row g-4">
                    <div class="col-12 col-lg-7">
                        <div class="bg-white p-3 p-md-4 rounded-4 shadow-xs mb-4 border">
                            <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
                                <h6 class="fw-bold text-dark font-heading mb-0 fs-14">
                                    <i class="fa-solid fa-bag-shopping me-1 text-secondary"></i> অর্ডারের পণ্যসমূহ
                                </h6>
                                <span class="badge bg-light text-secondary border rounded-pill font-monospace fs-11">{{ $itemCount }} টি পণ্য</span>
                            </div>

                            <div class="d-flex flex-column gap-2.5">
                                @forelse($items as $item)
                                    @php
                                        $itemImg = product_image_url($item->product_image ?? null);
                                        $rawTitle = $item->product_title ?? ($item->product_name ?? 'প্রোডাক্ট');
                                        $itemVariant = !empty($item->variant) ? $item->variant : null;
                                        $itemTitle = $rawTitle;

                                        if (preg_match('/^(.*?)\s*-\s*([^-]+)$/u', $rawTitle, $vMatch)) {
                                            $possibleVar = trim($vMatch[2]);
                                            if (empty($itemVariant) && strlen($possibleVar) <= 30) {
                                                $itemVariant = $possibleVar;
                                            }
                                            if (!empty($itemVariant) && strcasecmp(trim($possibleVar), trim($itemVariant)) === 0) {
                                                $itemTitle = trim($vMatch[1]);
                                            }
                                        }
                                        $itemQty = (int)($item->quantity ?? 1);
                                        $itemUnit = (float)($item->unit_price ?? ($item->price ?? 0));
                                    @endphp
                                    <div class="d-flex align-items-center gap-3 p-3 px-sm-3.5 rounded-3 bg-light border border-slate-200">
                                        <div class="flex-shrink-0" style="width: 56px; height: 56px; border-radius: 12px; overflow: hidden; background: #fff; border: 1px solid #e2e8f0; padding: 2px;">
                                            <img src="{{ $itemImg }}" alt="{{ $itemTitle }}" class="w-100 h-100 object-fit-cover rounded-2" onerror="this.onerror=null; this.src='{{ asset('images/product-placeholder.svg') }}';">
                                        </div>
                                        <div class="flex-grow-1 min-w-0 pe-2">
                                            <h6 class="fw-bold text-dark mb-1 text-truncate font-heading fs-13" title="{{ $rawTitle }}">{{ $itemTitle }}</h6>
                                            @if(!empty($itemVariant))
                                                <div class="mb-1">
                                                    <span class="badge bg-white text-dark border px-2 py-0.5 rounded-pill font-mono fs-11 d-inline-flex align-items-center gap-1 shadow-2xs">
                                                        <i class="fa-solid fa-tag text-primary" style="font-size: 9px;"></i> ভ্যারিয়েন্ট: <strong>{{ $itemVariant }}</strong>
                                                    </span>
                                                </div>
                                            @endif
                                            <div class="text-secondary fs-11 d-flex align-items-center gap-2 flex-wrap">
                                                <span>পরিমাণ: <strong class="text-dark font-monospace">{{ $itemQty }} টি</strong></span>
                                                <span class="text-muted">•</span>
                                                <span>একক মূল্য: <strong class="text-dark font-monospace">৳ {{ number_format($itemUnit, 0) }}</strong></span>
                                            </div>
                                        </div>
                                        <div class="text-end flex-shrink-0 font-monospace fw-bold text-dark fs-14 pe-1 ps-2">
                                            ৳ {{ number_format($itemUnit * $itemQty, 0) }}
                                        </div>
                                    </div>
                                @empty
                                    <div class="text-center text-muted py-3 fs-12">কোনো পণ্যের তথ্য পাওয়া যায়নি।</div>
                                @endforelse
                            </div>

                            <div class="mt-3 pt-3 border-top">
                                <div class="d-flex flex-column gap-2 fs-12 text-secondary">
                                    <div class="d-flex justify-content-between">
                                        <span>সাবটোটাল:</span>
                                        <span class="text-dark font-monospace fw-semibold">৳ {{ number_format($subtotal, 0) }}</span>
                                    </div>
                                    <div class="d-flex justify-content-between">
                                        <span>ডেলিভারি চার্জ:</span>
                                        <span class="text-dark font-monospace fw-semibold">{{ $shippingFee == 0 ? 'ফ্রি / ৳ ০' : '৳ ' . number_format($shippingFee, 0) }}</span>
                                    </div>
                                    @if(!empty($order->discount) && (float)$order->discount > 0)
                                        <div class="d-flex justify-content-between text-danger">
                                            <span>ডিসকাউন্ট:</span>
                                            <span class="font-monospace fw-semibold">- ৳ {{ number_format($order->discount, 0) }}</span>
                                        </div>
                                    @endif
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span>পেমেন্ট মেথড:</span>
                                        <span class="badge bg-light text-dark border font-monospace py-1 fs-10">{{ strtoupper($order->payment_method ?? 'COD') }}</span>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center pt-2 mt-1 border-top">
                                        <span class="fw-bold text-dark font-heading fs-14">সর্বমোট মূল্য:</span>
                                        <span class="fw-bold text-dark font-heading fs-5">৳ {{ number_format($orderTotal, 0) }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white p-3 p-md-4 rounded-4 shadow-xs border">
                            <h6 class="fw-bold text-dark font-heading mb-3 pb-2 border-bottom fs-14">
                                <i class="fa-solid fa-location-dot me-1 text-secondary"></i> ডেলিভারি ঠিকানা
                            </h6>
                            @php
                                $rawPhone = preg_replace('/[^0-9]/', '', (string)($order->customer_phone ?? ''));
                                if (strlen($rawPhone) >= 10) {
                                    $displayPhone = substr($rawPhone, 0, 3) . '****' . substr($rawPhone, -4);
                                } else {
                                    $displayPhone = '01*********';
                                }

                                $nameParts = array_filter(explode(' ', trim((string)($order->customer_name ?? ''))));
                                if (!empty($nameParts)) {
                                    $displayParts = array_map(function($part) {
                                        return mb_substr($part, 0, 1) . str_repeat('*', max(2, min(5, mb_strlen($part) - 1)));
                                    }, $nameParts);
                                    $displayName = implode(' ', $displayParts);
                                } else {
                                    $displayName = 'গ্রাহক';
                                }

                                $rawAddr = (string)($order->customer_address ?? '');
                                $addrTokens = array_filter(array_map('trim', explode(',', $rawAddr)));
                                $safeArea = '';
                                if (count($addrTokens) >= 2) {
                                    $safeArea = implode(', ', array_slice($addrTokens, -2));
                                } elseif (!empty($order->district)) {
                                    $safeArea = $order->district;
                                }
                                $displayAddress = !empty($safeArea) ? '***, ' . $safeArea : '*** গোপন রাখা হয়েছে ***';
                            @endphp
                            <div class="d-flex flex-column gap-2 fs-12">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="text-secondary" style="width: 75px;">গ্রাহকের নাম:</span>
                                    <span class="fw-bold text-dark">{{ $displayName }}</span>
                                </div>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="text-secondary" style="width: 75px;">ফোন নম্বর:</span>
                                    <span class="font-monospace text-dark fw-bold">{{ $displayPhone }}</span>
                                </div>
                                <div class="d-flex align-items-start gap-2">
                                    <span class="text-secondary" style="width: 75px;">ঠিকানা:</span>
                                    <span class="text-dark">{{ $displayAddress }}</span>
                                </div>
                            </div>
                            <div class="mt-2.5 pt-2 border-top text-muted d-flex align-items-center gap-1.5 fs-11">
                                <i class="fa-solid fa-shield-halved text-success"></i>
                                <span>গ্রাহকের তথ্যের সুরক্ষা ও নিরাপত্তার স্বার্থে বিস্তারিত ব্যক্তিগত তথ্য গোপন রাখা হয়েছে।</span>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-lg-5">
                        <div class="bg-white p-3 p-md-4 rounded-4 shadow-xs mb-4 border">
                            <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
                                <h6 class="fw-bold text-dark font-heading mb-0 fs-14">
                                    <i class="fa-solid fa-timeline me-1 text-secondary"></i> ট্র্যাকিং টাইমলাইন
                                </h6>
                                <span class="badge bg-light text-secondary border rounded-pill fs-10">{{ count($reversedHistory) }} টি আপডেট</span>
                            </div>

                            <div class="luxury-status-timeline ps-1 mt-2">
                                @forelse($reversedHistory as $idx => $event)
                                    @php
                                        $isLatest = ($idx === 0);
                                        $eventStatus = strtolower($event['status'] ?? ($event['delivery_status'] ?? ''));
                                        $eventTitle = $event['title'] ?? ucfirst(str_replace('_', ' ', $eventStatus));
                                        $eventMsg = $event['message'] ?? '';
                                        $eventLoc = $event['location'] ?? null;
                                        $eventTime = !empty($event['timestamp']) ? \Carbon\Carbon::parse($event['timestamp'])->format('d M Y, h:i A') : '';
                                    @endphp
                                    <div class="tl-item {{ $isLatest ? 'is-latest' : 'is-done' }}">
                                        <div class="tl-node">
                                            @if($isLatest)
                                                <span class="tl-pulse-ring"></span>
                                                <i class="fa-solid fa-circle-dot fs-10 text-white"></i>
                                            @else
                                                <i class="fa-solid fa-check fs-10 text-secondary"></i>
                                            @endif
                                        </div>
                                        <div class="tl-content">
                                            <div class="d-flex flex-wrap justify-content-between align-items-center gap-1 mb-1">
                                                <h6 class="tl-title mb-0 {{ $isLatest ? 'text-dark fw-bold' : 'text-secondary fw-semibold' }} fs-13">{{ $eventTitle }}</h6>
                                                @if($eventLoc)
                                                    <span class="badge bg-light text-secondary border rounded-pill fs-10">{{ $eventLoc }}</span>
                                                @endif
                                            </div>
                                            @if($eventTime)
                                                <span class="tl-time text-muted d-block mb-1 font-monospace fs-11">{{ $eventTime }}</span>
                                            @endif
                                            @if($eventMsg)
                                                <p class="tl-desc text-secondary mb-0 fs-12">{{ $eventMsg }}</p>
                                            @endif
                                        </div>
                                    </div>
                                @empty
                                    <div class="text-center text-muted py-3 fs-12">কোনো টাইমলাইন তথ্য পাওয়া যায়নি।</div>
                                @endforelse
                            </div>
                        </div>

                        <div class="d-flex flex-column gap-2 mb-2">
                            <a href="{{ route('home') }}" class="btn btn-outline-dark rounded-pill py-2.5 fw-bold w-100 fs-13">
                                <i class="fa-solid fa-store me-1.5"></i> আরো কেনাকাটা করুন
                            </a>
                            <a href="https://wa.me/{{ $whatsappPhone }}?text={{ urlencode('Hello ' . $storeName . ', I need support for order: ' . ($order->order_number ?? '')) }}" target="_blank" class="btn btn-dark rounded-pill py-2.5 fw-bold d-flex align-items-center justify-content-center gap-2 text-white w-100 fs-13" style="background-color: #0f172a;">
                                <i class="fa-brands fa-whatsapp fs-5 text-success"></i>
                                <span>কাস্টমার সাপোর্ট (WhatsApp)</span>
                            </a>
                        </div>
                    </div>
                </div>

                @if(isset($relatedOrders) && count($relatedOrders) > 0)
                    <div class="bg-white p-3 p-md-4 rounded-4 shadow-xs mt-4 border">
                        <h6 class="fw-bold text-dark font-heading mb-3 pb-2 border-bottom fs-14">
                            <i class="fa-solid fa-layer-group me-1.5 text-secondary"></i> এই নম্বরের অন্যান্য অর্ডারসমূহ
                        </h6>
                        <div class="row g-2 g-md-3">
                            @foreach($relatedOrders as $relOrder)
                                <div class="col-12 col-md-6 col-lg-4">
                                    <a href="javascript:void(0)" onclick="quickTrackOrder('{{ $relOrder->order_number }}')" class="d-flex justify-content-between align-items-center p-3 rounded-3 bg-light text-decoration-none text-dark border recent-order-item transition">
                                        <div>
                                            <span class="fw-bold font-monospace text-dark d-block fs-13">{{ $relOrder->order_number }}</span>
                                            <span class="text-secondary fs-11">{{ \Carbon\Carbon::parse($relOrder->created_at)->format('d M Y, h:i A') }}</span>
                                        </div>
                                        <div class="text-end">
                                            <span class="badge bg-dark rounded-pill px-2 py-1 small text-uppercase mb-1 d-inline-block fs-10">{{ $relOrder->order_status ?? ($relOrder->status ?? 'Pending') }}</span>
                                            <span class="fw-bold text-dark d-block font-heading fs-12">৳ {{ number_format($relOrder->total ?? 0, 0) }}</span>
                                        </div>
                                    </a>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

            @elseif(!empty($query))
                <div class="bg-white p-4 p-md-5 rounded-4 shadow-xs text-center border mx-auto my-3" style="max-width: 480px;">
                    <div class="rounded-circle bg-light border text-muted d-flex align-items-center justify-content-center mx-auto mb-3" style="width: 64px; height: 64px;">
                        <i class="fa-solid fa-magnifying-glass fs-3"></i>
                    </div>
                    <h6 class="fw-bold text-dark font-heading mb-2 fs-15">কোনো অর্ডার খুঁজে পাওয়া যায়নি</h6>
                    <p class="text-muted small mb-3 fs-12" style="line-height: 1.6;">"<strong>{{ $query }}</strong>" দিয়ে কোনো অর্ডার বা কুরিয়ার ট্র্যাকিং রেকর্ড পাওয়া যায়নি। অনুগ্রহ করে সঠিক অর্ডার নম্বর বা মোবাইল নম্বর দিন।</p>
                    <a href="javascript:void(0)" onclick="quickTrackOrder('')" class="btn btn-dark rounded-pill px-4 py-2 fw-bold fs-12">পুনরায় অনুসন্ধান করুন</a>
                </div>
            @endif
        </div>

    </div>
</div>

<script>
window.quickTrackOrder = function(trackId) {
    const form = document.querySelector('form[action="{{ route('order.track') }}"]');
    if (!form) {
        if (typeof Turbo !== 'undefined') {
            Turbo.visit("{{ route('order.track') }}?track_id=" + encodeURIComponent(trackId));
        } else {
            window.location.href = "{{ route('order.track') }}?track_id=" + encodeURIComponent(trackId);
        }
        return;
    }
    const input = form.querySelector('input[name="track_id"]');
    if (input) {
        input.value = trackId;
        if (typeof window.executeLiveTrack === 'function') {
            window.executeLiveTrack(trackId);
        } else {
            form.submit();
        }
    }
};

function initOrderTrackingModule() {
    const form = document.querySelector('form[action="{{ route('order.track') }}"]');
    if (!form) return;

    const input = form.querySelector('input[name="track_id"]');
    const submitBtn = form.querySelector('button[type="submit"]');
    const container = document.getElementById('trackingDynamicContainer');

    window.executeLiveTrack = function(query) {
        if (!query) {
            if (typeof Turbo !== 'undefined') {
                Turbo.visit("{{ route('order.track') }}");
            } else {
                window.location.href = "{{ route('order.track') }}";
            }
            return;
        }

        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>';
        }

        if (container) {
            container.style.opacity = '0.5';
            container.style.transition = 'opacity 0.2s ease';
        }

        const trackUrl = "{{ route('order.track') }}?track_id=" + encodeURIComponent(query);

        axios.get(trackUrl, {
            responseType: 'text',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-Tracking-HTML': 'true',
                'Accept': 'text/html'
            }
        })
        .then(response => {
            const parser = new DOMParser();
            const doc = parser.parseFromString(response.data, 'text/html');
            const newContent = doc.getElementById('trackingDynamicContainer');

            if (newContent && container) {
                container.innerHTML = newContent.innerHTML;
            }

            window.history.pushState({ track_id: query }, '', trackUrl);
        })
        .catch(err => {
            console.error('Tracking Error:', err);
        })
        .finally(() => {
            if (container) container.style.opacity = '1';
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<span class="d-none d-sm-inline">ট্র্যাক করুন</span><span class="d-inline d-sm-none">ট্র্যাক</span><i class="fa-solid fa-arrow-right fs-12 ms-1"></i>';
            }
        });
    };

    form.onsubmit = function(e) {
        e.preventDefault();
        const val = input ? input.value.trim() : '';
        if (val) {
            window.executeLiveTrack(val);
        }
    };
}

if (!window.__orderTrackTurboBound) {
    window.__orderTrackTurboBound = true;
    document.addEventListener('turbo:load', initOrderTrackingModule);
}
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initOrderTrackingModule, { once: true });
} else {
    initOrderTrackingModule();
}
</script>

<style>
.tracking-page-wrapper {
    background-color: #f8fafc;
    min-height: 80vh;
}

.tracking-hero-card {
    background-color: #0f172a;
    border: 1px solid rgba(255, 255, 255, 0.08);
}

.hero-decorative-circle {
    position: absolute;
    width: 260px;
    height: 260px;
    background: radial-gradient(circle, rgba(255, 255, 255, 0.04) 0%, transparent 70%);
    top: -60px;
    right: -40px;
    pointer-events: none;
}

.live-pulse-dot {
    width: 7px;
    height: 7px;
    background-color: #10b981;
    border-radius: 50%;
    box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7);
    animation: livePulseGlow 2s infinite;
}

@keyframes livePulseGlow {
    0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7); }
    70% { transform: scale(1); box-shadow: 0 0 0 6px rgba(16, 185, 129, 0); }
    100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
}

.tracking-search-box {
    border: 1px solid rgba(255, 255, 255, 0.2);
    transition: box-shadow 0.2s ease, border-color 0.2s ease;
}

.tracking-search-box:focus-within {
    box-shadow: 0 0 0 3px rgba(255, 255, 255, 0.2) !important;
    border-color: rgba(255, 255, 255, 0.4);
}

.tracking-guide-card {
    border-color: #e2e8f0 !important;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.tracking-guide-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px -4px rgba(15, 23, 42, 0.06) !important;
}

.guide-icon-box {
    width: 48px;
    height: 48px;
    background-color: #f8fafc;
    border: 1px solid #e2e8f0;
    color: #0f172a;
}

.tracking-code-banner {
    background-color: #f8fafc;
    border: 1px solid #e2e8f0;
}

.recent-order-item:hover {
    background-color: #ffffff !important;
    border-color: #cbd5e1 !important;
    box-shadow: 0 4px 12px rgba(15, 23, 42, 0.05);
}

.status-badge-placed {
    background-color: #f1f5f9;
    color: #334155;
    border: 1px solid #cbd5e1;
}

.status-badge-processing {
    background-color: #f5f3ff;
    color: #6b21a8;
    border: 1px solid #ddd6fe;
}

.status-badge-shipped {
    background-color: #f0fdf4;
    color: #166534;
    border: 1px solid #bbf7d0;
}

.status-badge-transit {
    background-color: #f8fafc;
    color: #0f172a;
    border: 1px solid #94a3b8;
}

.status-badge-delivered {
    background-color: #ecfdf5;
    color: #047857;
    border: 1px solid #a7f3d0;
}

.status-badge-cancelled {
    background-color: #fef2f2;
    color: #b91c1c;
    border: 1px solid #fecaca;
}

.status-badge-returned {
    background-color: #fffbeb;
    color: #b45309;
    border: 1px solid #fde68a;
}

.modern-stepper-container {
    width: 100%;
    padding: 8px 0;
}

.modern-stepper-track::before {
    content: '';
    position: absolute;
    top: 19px;
    left: 8%;
    right: 8%;
    height: 2px;
    background-color: #e2e8f0;
    z-index: 1;
}

.step-node-wrap {
    position: relative;
    z-index: 2;
    flex: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
}

.step-node-icon {
    width: 38px;
    height: 38px;
    border-radius: 50%;
    background-color: #ffffff;
    border: 2px solid #cbd5e1;
    color: #94a3b8;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 13px;
    transition: all 0.25s ease;
}

.step-node-wrap.is-completed .step-node-icon {
    background-color: #0f172a;
    border-color: #0f172a;
    color: #ffffff;
}

.step-node-wrap.is-active .step-node-icon {
    background-color: #0f172a;
    border-color: #0f172a;
    color: #ffffff;
    box-shadow: 0 0 0 5px rgba(15, 23, 42, 0.15);
}

.step-node-title {
    font-size: 11px;
    font-weight: 600;
    color: #64748b;
    line-height: 1.3;
}

.step-node-wrap.is-completed .step-node-title {
    color: #0f172a;
    font-weight: 700;
}

.step-node-wrap.is-active .step-node-title {
    color: #0f172a;
    font-weight: 800;
}

@media (max-width: 576px) {
    .modern-stepper-track::before {
        top: 15px;
    }
    .step-node-icon {
        width: 30px;
        height: 30px;
        font-size: 10.5px;
    }
    .step-node-title {
        font-size: 9.5px;
    }
}

.luxury-status-timeline {
    position: relative;
}

.tl-item {
    position: relative;
    display: flex;
    align-items: flex-start;
    gap: 14px;
    padding-bottom: 22px;
}

.tl-item:last-child {
    padding-bottom: 0;
}

.tl-item::before {
    content: '';
    position: absolute;
    top: 24px;
    left: 11px;
    width: 2px;
    height: calc(100% - 20px);
    background-color: #e2e8f0;
    z-index: 1;
}

.tl-item.is-done::before, .tl-item.is-latest::before {
    background-color: #0f172a;
}

.tl-node {
    position: relative;
    z-index: 2;
    width: 24px;
    height: 24px;
    border-radius: 50%;
    background-color: #ffffff;
    border: 2px solid #cbd5e1;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.tl-item.is-done .tl-node {
    background-color: #f1f5f9;
    border-color: #0f172a;
}

.tl-item.is-latest .tl-node {
    background-color: #0f172a;
    border-color: #0f172a;
}

.tl-pulse-ring {
    position: absolute;
    width: 100%;
    height: 100%;
    border-radius: 50%;
    animation: timelinePulse 1.8s infinite;
    pointer-events: none;
}

@keyframes timelinePulse {
    0% { box-shadow: 0 0 0 0 rgba(15, 23, 42, 0.4); }
    70% { box-shadow: 0 0 0 7px rgba(15, 23, 42, 0); }
    100% { box-shadow: 0 0 0 0 rgba(15, 23, 42, 0); }
}

.shadow-xs {
    box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.04), 0 1px 2px 0 rgba(0, 0, 0, 0.02) !important;
}

.fs-10 { font-size: 10px !important; }
.fs-11 { font-size: 11px !important; }
.fs-12 { font-size: 12px !important; }
.fs-13 { font-size: 13px !important; }
.fs-14 { font-size: 14px !important; }
.fs-15 { font-size: 15px !important; }
</style>
@endsection