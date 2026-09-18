@extends('frontend.layouts.app')

@php
    $successStoreName = $settings['store_name'] ?? 'ZippyBD';
@endphp

@section('title', 'অর্ডার সফলভাবে সম্পন্ন হয়েছে | ' . $successStoreName)

@section('content')
<div class="container py-4 py-md-5">
    <div class="row justify-content-center">
        <div class="col-12 col-md-8 col-lg-6 mx-auto" style="max-width: 600px;">
            <div class="card border-0 shadow-sm rounded-4 p-4 p-sm-5 text-center bg-white">
                <div class="d-inline-flex align-items-center justify-content-center bg-success bg-opacity-10 text-success rounded-circle p-3 mx-auto mb-3" style="width: 64px; height: 64px;">
                    <i class="fa-solid fa-check fs-2"></i>
                </div>
                
                <h1 class="h4 fw-bold text-dark mb-2">অর্ডার সফলভাবে সম্পন্ন হয়েছে!</h1>
                <p class="text-secondary small mb-3">ধন্যবাদ! আমাদের কাস্টমার সার্ভিস প্রতিনিধি শীঘ্রই কল করে আপনার অর্ডারটি নিশ্চিত করবেন।</p>

                <div class="d-inline-flex align-items-center justify-content-center gap-2 px-3 py-1.5 rounded-pill bg-light border mx-auto mb-4 small">
                    <span class="text-secondary">অর্ডার নম্বর:</span>
                    <strong class="text-dark font-monospace">{{ $order->order_number ?? '' }}</strong>
                    <button type="button" class="btn btn-sm btn-link text-secondary p-0 ms-1" onclick="navigator.clipboard.writeText('{{ $order->order_number ?? '' }}'); if(typeof showToast==='function') showToast('অর্ডার নম্বর কপি হয়েছে!');" title="কপি করুন">
                        <i class="fa-regular fa-copy"></i>
                    </button>
                </div>

                @if(isset($items) && count($items) > 0)
                    <div class="card border text-start mb-4 overflow-hidden rounded-3">
                        <div class="card-header bg-light py-2.5 px-3 d-flex align-items-center justify-content-between border-bottom">
                            <span class="fw-bold text-dark small"><i class="fa-solid fa-bag-shopping me-2"></i>অর্ডারের আইটেমসমূহ</span>
                            <span class="badge bg-white text-dark border font-monospace">{{ count($items) }} টি পণ্য</span>
                        </div>
                        <div class="list-group list-group-flush">
                            @foreach($items as $item)
                                @php
                                    $rawTitle = $item->product_title ?? ($item->product_name ?? 'প্রোডাক্ট');
                                    $itemVariant = !empty($item->variant) ? $item->variant : null;
                                    $itemCleanTitle = $rawTitle;

                                    if (preg_match('/^(.*?)\s*-\s*([^-]+)$/u', $rawTitle, $vMatch)) {
                                        $possibleVar = trim($vMatch[2]);
                                        if (empty($itemVariant) && mb_strlen($possibleVar) <= 30) {
                                            $itemVariant = $possibleVar;
                                        }
                                        if (!empty($itemVariant) && mb_strtolower(trim($possibleVar)) === mb_strtolower(trim($itemVariant))) {
                                            $itemCleanTitle = trim($vMatch[1]);
                                        }
                                    }
                                    $itemQty = (int)($item->quantity ?? 1);
                                    $itemUnit = (float)($item->unit_price ?? ($item->price ?? 0));
                                @endphp
                                <div class="list-group-item p-3">
                                    <div class="d-flex align-items-center justify-content-between gap-3">
                                        <div class="d-flex align-items-center gap-3 min-w-0 flex-grow-1">
                                            <div class="flex-shrink-0 rounded border p-1 bg-white" style="width: 56px; height: 56px;">
                                                <img src="{{ product_image_url($item->product_image ?? null) }}" alt="{{ $itemCleanTitle }}" class="w-100 h-100 object-fit-cover rounded" onerror="this.onerror=null; this.src='{{ asset('images/product-placeholder.svg') }}';">
                                            </div>
                                            <div class="min-w-0 flex-grow-1">
                                                <div class="fw-bold text-dark text-truncate small mb-1" title="{{ $rawTitle }}">{{ $itemCleanTitle }}</div>
                                                @if(!empty($itemVariant))
                                                    <div class="mb-1">
                                                        <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 rounded-pill font-monospace" style="font-size: 0.75rem;">
                                                            <i class="fa-solid fa-tag me-1"></i>ভ্যারিয়েন্ট: {{ $itemVariant }}
                                                        </span>
                                                    </div>
                                                @endif
                                                <div class="text-secondary small d-flex align-items-center gap-2">
                                                    <span>পরিমাণ: <strong class="text-dark font-monospace">{{ $itemQty }} টি</strong></span>
                                                    @if($itemUnit > 0)
                                                        <span>•</span>
                                                        <span>একক: <strong class="text-dark font-monospace">৳ {{ number_format($itemUnit, 0) }}</strong></span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                        <div class="text-end flex-shrink-0 font-monospace fw-bold text-dark fs-6">
                                            ৳ {{ number_format($item->total_price, 0) }}
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <div class="card-footer bg-light p-3 border-top">
                            @php
                                $subTotalVal = (float)($order->subtotal ?? 0);
                                $shippingVal = (float)($order->shipping_cost ?? 0);
                                $discountVal = (float)($order->discount ?? 0);
                                $grandTotalVal = (float)($order->total ?? ($order->grand_total ?? 0));
                            @endphp
                            <div class="d-flex flex-column gap-1.5 small text-secondary">
                                @if($subTotalVal > 0)
                                    <div class="d-flex justify-content-between">
                                        <span>সাবটোটাল:</span>
                                        <span class="text-dark font-monospace fw-semibold">৳ {{ number_format($subTotalVal, 0) }}</span>
                                    </div>
                                @endif
                                <div class="d-flex justify-content-between">
                                    <span>ডেলিভারি চার্জ:</span>
                                    <span class="text-dark font-monospace fw-semibold">{{ $shippingVal == 0 ? 'ফ্রি / ৳ ০' : '৳ ' . number_format($shippingVal, 0) }}</span>
                                </div>
                                @if($discountVal > 0)
                                    <div class="d-flex justify-content-between text-danger">
                                        <span>ডিসকাউন্ট:</span>
                                        <span class="font-monospace fw-semibold">- ৳ {{ number_format($discountVal, 0) }}</span>
                                    </div>
                                @endif
                                <div class="d-flex justify-content-between border-top pt-2 mt-1 text-dark">
                                    <span class="fw-bold">সর্বমোট প্রদেয়:</span>
                                    <span class="fw-bold fs-6 font-monospace">৳ {{ number_format($grandTotalVal, 0) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                <div class="card border text-start mb-4 rounded-3 bg-light bg-opacity-50">
                    <div class="card-header bg-light py-2 px-3 border-bottom">
                        <span class="fw-bold text-dark small"><i class="fa-solid fa-location-dot text-danger me-2"></i>ডেলিভারি ও গ্রাহক তথ্য</span>
                    </div>
                    <div class="card-body p-3">
                        <ul class="list-group list-group-flush bg-transparent small">
                            <li class="list-group-item bg-transparent px-0 py-2 d-flex justify-content-between align-items-center">
                                <span class="text-secondary"><i class="fa-solid fa-user me-2 text-muted"></i>গ্রাহকের নাম:</span>
                                <span class="fw-bold text-dark">{{ $order->customer_name ?? '' }}</span>
                            </li>
                            <li class="list-group-item bg-transparent px-0 py-2 d-flex justify-content-between align-items-center">
                                <span class="text-secondary"><i class="fa-solid fa-phone me-2 text-muted"></i>মোবাইল নম্বর:</span>
                                <span class="fw-bold font-monospace text-dark">{{ $order->customer_phone ?? '' }}</span>
                            </li>
                            <li class="list-group-item bg-transparent px-0 py-2 d-flex justify-content-between align-items-start">
                                <span class="text-secondary flex-shrink-0 me-3"><i class="fa-solid fa-house me-2 text-muted"></i>ঠিকানা:</span>
                                <span class="fw-medium text-dark text-end text-break">{{ $order->customer_address ?? '' }}</span>
                            </li>
                            <li class="list-group-item bg-transparent px-0 py-2 d-flex justify-content-between align-items-center">
                                <span class="text-secondary"><i class="fa-solid fa-wallet me-2 text-muted"></i>পেমেন্ট মেথড:</span>
                                <span class="badge bg-light text-dark border font-monospace">{{ strtoupper($order->payment_method ?? 'COD') }}</span>
                            </li>
                        </ul>
                    </div>
                </div>

                <div class="d-flex flex-column flex-sm-row gap-2 justify-content-center mb-3">
                    <a href="{{ route('order.track', ['track_id' => $order->order_number ?? '']) }}" class="btn btn-primary rounded-pill py-2.5 px-4 fw-semibold d-flex align-items-center justify-content-center gap-2 flex-grow-1 order-1 order-sm-2">
                        <i class="fa-solid fa-location-dot"></i>
                        <span>অর্ডার ট্র্যাক করুন</span>
                    </a>
                    <a href="{{ route('home') }}" class="btn btn-outline-secondary rounded-pill py-2.5 px-4 fw-semibold d-flex align-items-center justify-content-center gap-2 flex-grow-1 order-2 order-sm-1">
                        <i class="fa-solid fa-house"></i>
                        <span>হোমপেজে ফিরে যান</span>
                    </a>
                </div>

                @if(!empty($whatsappUrl))
                    <div class="mt-2">
                        <a href="{{ $whatsappUrl }}" target="_blank" class="text-decoration-none text-muted small d-inline-flex align-items-center gap-2">
                            <i class="fa-brands fa-whatsapp text-success fs-5"></i>
                            <span>কোনো তথ্যের প্রয়োজন হলে হোয়াটসঅ্যাপে জানান</span>
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

@php
    $rawPhone = preg_replace('/[^0-9]/', '', (string)($order->customer_phone ?? ''));
    if (strlen($rawPhone) === 11 && str_starts_with($rawPhone, '01')) {
        $rawPhone = '88' . $rawPhone;
    }
    $hashedPhone = !empty($rawPhone) ? hash('sha256', $rawPhone) : '';
    $orderVal = (float)($order->total ?? ($order->grand_total ?? 0));
    $orderShipping = (float)($order->shipping_cost ?? 0);

    $ecommerceItems = [];
    $contentIds = [];
    if (isset($items) && count($items) > 0) {
        foreach ($items as $it) {
            $pId = (string)($it->product_id ?? ($it->id ?? ''));
            $contentIds[] = $pId;
            $ecommerceItems[] = [
                'item_id' => $pId,
                'item_name' => $it->product_title ?? 'Product',
                'price' => (float)($it->unit_price ?? ($it->price ?? 0)),
                'quantity' => (int)($it->quantity ?? 1)
            ];
        }
    }
@endphp

@push('scripts')
<script>
    (function () {
        const orderNumber = '{{ $order->order_number ?? '' }}';
        if (!orderNumber) return;

        if (window.ZippyTracker && typeof window.ZippyTracker.trackPurchase === 'function') {
            window.ZippyTracker.trackPurchase({
                order_id: orderNumber,
                event_id: 'order_' + orderNumber,
                value: {{ $orderVal }},
                tax: 0,
                shipping: {{ $orderShipping }},
                coupon: '{{ $order->coupon_code ?? '' }}',
                content_ids: {!! json_encode($contentIds) !!},
                items: {!! json_encode($ecommerceItems, JSON_UNESCAPED_UNICODE) !!},
                user_data: {
                    phone_number: '{{ $hashedPhone }}',
                    address: {
                        city: {!! json_encode($order->district ?? 'Dhaka') !!},
                        country: 'BD'
                    }
                }
            });
        }
    })();
</script>
@endpush
@endsection

