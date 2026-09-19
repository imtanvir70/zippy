@extends('frontend.layouts.app')

@section('title', 'আমার একাউন্ট ড্যাশবোর্ড | ' . ($settings['store_name'] ?? 'Zippy'))

@section('content')
<div class="container py-5" style="max-width: 600px;">
    <div class="bg-white rounded-4 border p-4 shadow-sm mb-4 text-center">
        <div class="rounded-circle bg-dark text-white d-inline-flex align-items-center justify-content-center fs-2 fw-bold mb-3" style="width: 72px; height: 72px;">
            {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}
        </div>
        <h4 class="fw-bold text-dark font-heading mb-1">{{ auth()->user()->name ?? '' }}</h4>
        <p class="text-secondary small mb-3">{{ auth()->user()->email ?? '' }}</p>
        <span class="badge bg-success rounded-pill px-3 py-1">ভেরিফাইড কাস্টমার</span>
    </div>

    <div class="bg-white rounded-4 border p-4 shadow-sm d-flex flex-column gap-2">
        <a href="{{ route('order.history') }}" class="btn btn-light d-flex align-items-center justify-content-between p-3 rounded-3 text-decoration-none text-dark fw-bold">
            <span class="d-flex align-items-center gap-2"><i class="fa-solid fa-bag-shopping text-secondary"></i> অর্ডার হিস্ট্রি</span>
            <i class="fa-solid fa-chevron-right text-secondary small"></i>
        </a>
        <a href="{{ route('order.track') }}" class="btn btn-light d-flex align-items-center justify-content-between p-3 rounded-3 text-decoration-none text-dark fw-bold">
            <span class="d-flex align-items-center gap-2"><i class="fa-solid fa-truck-fast text-secondary"></i> অর্ডার ট্র্যাকিং</span>
            <i class="fa-solid fa-chevron-right text-secondary small"></i>
        </a>
        <a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('accLogoutForm').submit();" class="btn btn-outline-danger d-flex align-items-center justify-content-between p-3 rounded-3 text-decoration-none fw-bold mt-2">
            <span class="d-flex align-items-center gap-2"><i class="fa-solid fa-right-from-bracket"></i> লগআউট</span>
            <i class="fa-solid fa-chevron-right small"></i>
        </a>
        <form id="accLogoutForm" action="{{ route('logout') }}" method="POST" class="d-none">@csrf</form>
    </div>
</div>
@endsection
