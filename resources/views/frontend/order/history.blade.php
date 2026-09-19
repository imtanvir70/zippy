@extends('frontend.layouts.app')

@section('title', 'আমার অর্ডার হিস্ট্রি ও বিবরণ | ' . ($settings['store_name'] ?? 'Zippy'))

@section('content')
<div class="container py-4 py-md-5" style="max-width: 800px;">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold text-dark font-heading mb-1">অর্ডার হিস্ট্রি</h2>
            <p class="text-secondary small mb-0">আপনার পূর্ববর্তী সমস্ত অর্ডারের তালিকা ও বিবরণ</p>
        </div>
        <a href="{{ route('order.track') }}" class="btn btn-outline-dark btn-sm rounded-pill px-3 fw-bold">
            <i class="fa-solid fa-truck-fast me-1"></i> অর্ডার ট্র্যাকিং
        </a>
    </div>

    @if(isset($orders) && count($orders) > 0)
        <div class="d-flex flex-column gap-3">
            @foreach($orders as $ord)
                <div class="bg-white rounded-4 border p-3 p-md-4 shadow-sm">
                    <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                        <div>
                            <span class="fw-bold font-monospace text-dark fs-6">{{ $ord->order_number }}</span>
                            <span class="text-secondary small d-block">{{ \Carbon\Carbon::parse($ord->created_at)->translatedFormat('d M, Y') }}</span>
                        </div>
                        <span class="badge bg-secondary rounded-pill px-3 py-1 text-uppercase">{{ $ord->order_status ?? ($ord->status ?? 'Pending') }}</span>
                    </div>
                    @if(isset($ord->items) && count($ord->items) > 0)
                        <div class="py-2 border-bottom mb-2">
                            @foreach($ord->items as $item)
                                <div class="d-flex justify-content-between align-items-center small text-secondary py-1">
                                    <span class="text-truncate me-2">{{ $item->product_title ?? ($item->title ?? 'Product') }} (x{{ $item->quantity }})</span>
                                    <span class="fw-semibold text-dark">৳ {{ number_format($item->total_price ?? ($item->price * $item->quantity), 0) }}</span>
                                </div>
                            @endforeach
                        </div>
                    @endif
                    <div class="d-flex justify-content-between align-items-center small">
                        <span class="text-secondary">পেমেন্ট: {{ strtoupper($ord->payment_method ?? 'COD') }}</span>
                        <span class="fw-bold text-dark font-heading fs-6">৳ {{ number_format($ord->total ?? ($ord->grand_total ?? ($ord->total_amount ?? 0)), 0) }}</span>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="bg-white rounded-4 border p-5 text-center shadow-sm">
            <i class="fa-solid fa-box-open text-secondary fs-1 mb-3"></i>
            <h5 class="fw-bold text-dark mb-1">কোনো পূর্ববর্তী অর্ডার নেই</h5>
            <p class="text-secondary small mb-4">আপনি এখনও কোনো অর্ডার করেননি। সেরা গ্যাজেট এক্সপ্লোর করতে আমাদের হোমপেজ ভিজিট করুন।</p>
            <a href="{{ route('home') }}" class="btn btn-dark rounded-pill px-4 fw-bold" style="background-color: #0f172a;">শপিং শুরু করুন</a>
        </div>
    @endif
</div>
@endsection
