@extends('frontend.layouts.app')

@php
    $authBrand = $settings['store_name'] ?? 'Zippy';
    $authInitial = strtoupper(substr($authBrand, 0, 1));
@endphp

@section('title', 'নতুন অ্যাকাউন্ট রেজিস্ট্রেশন | ' . $authBrand)

@section('content')
<div class="auth-premium-wrapper">

    <div class="mobile-app-header d-md-none">
        <a href="javascript:history.back()" class="back-btn">
            <i class="fa-solid fa-arrow-left"></i>
        </a>
        <h5 class="header-title font-heading m-0">নতুন একাউন্ট</h5>
    </div>

    <div class="container auth-container d-flex align-items-center justify-content-center">
        <div class="auth-premium-card w-100">
            
            <div class="text-center mb-4 pb-2 d-none d-md-block">
                <div class="brand-logo-icon mx-auto mb-3">{{ $authInitial }}</div>
                <h3 class="fw-bold text-dark font-heading mb-2 fs-4">স্বাগতম!</h3>
                <p class="text-secondary small m-0">{{ $authBrand }}-এ যুক্ত হতে নিচের তথ্যগুলো পূরণ করুন</p>
            </div>

            <div class="mb-4 pb-2 d-md-none text-center">
                <div class="brand-logo-icon mx-auto mb-3" style="width: 48px; height: 48px; font-size: 20px;">{{ $authInitial }}</div>
                <h3 class="fw-bold text-dark font-heading mb-1 fs-3">স্বাগতম! 👋</h3>
                <p class="text-secondary m-0" style="font-size: 14px;">একাউন্ট তৈরি করে কেনাকাটা শুরু করুন</p>
            </div>

            @if($errors->any())
                <div class="custom-alert-danger mb-4">
                    <i class="fa-solid fa-circle-exclamation"></i>
                    <span>{{ $errors->first() }}</span>
                </div>
            @endif

            <form action="{{ route('register.post') }}" method="POST">
                @csrf
                
                <div class="mb-3">
                    <label class="premium-label">আপনার নাম <span class="text-danger">*</span></label>
                    <div class="input-icon-wrapper">
                        <i class="fa-regular fa-user"></i>
                        <input type="text" name="name" class="form-control premium-input" placeholder="যেমন: তানভীর আহমেদ" value="{{ old('name') }}" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="premium-label">ইমেইল <span class="text-danger">*</span></label>
                    <div class="input-icon-wrapper">
                        <i class="fa-regular fa-envelope"></i>
                        <input type="email" name="email" class="form-control premium-input" placeholder="example@email.com" value="{{ old('email') }}" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="premium-label">মোবাইল নম্বর</label>
                    <div class="input-icon-wrapper">
                        <i class="fa-solid fa-mobile-screen"></i>
                        <input type="tel" name="phone" class="form-control premium-input font-monospace" placeholder="017XXXXXXXX" value="{{ old('phone') }}">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="premium-label">পাসওয়ার্ড <span class="text-danger">*</span></label>
                    <div class="input-icon-wrapper">
                        <i class="fa-solid fa-lock"></i>
                        <input type="password" name="password" class="form-control premium-input" placeholder="••••••••" required>
                    </div>
                </div>

                <div class="mb-4 pb-1">
                    <label class="premium-label">পাসওয়ার্ড নিশ্চিত করুন <span class="text-danger">*</span></label>
                    <div class="input-icon-wrapper">
                        <i class="fa-solid fa-lock"></i>
                        <input type="password" name="password_confirmation" class="form-control premium-input" placeholder="••••••••" required>
                    </div>
                </div>

                <button type="submit" class="btn btn-dark w-100 premium-auth-btn mb-4">
                    <span>একাউন্ট তৈরি করুন</span>
                    <i class="fa-solid fa-arrow-right"></i>
                </button>
            </form>

            <div class="text-center auth-footer-text">
                ইতিমধ্যে একাউন্ট আছে? <a href="{{ route('login') }}" class="auth-link">লগইন করুন</a>
            </div>
            
        </div>
    </div>
</div>

<style>
.auth-premium-wrapper {
    background-color: #f4f6f8;
    min-height: calc(100vh - 80px);
    display: flex;
    flex-direction: column;
    position: relative;
}

.auth-container {
    flex-grow: 1;
    padding-top: 40px;
    padding-bottom: 40px;
}

.auth-premium-card {
    max-width: 460px;
    background-color: #ffffff;
    border-radius: 24px;
    padding: 48px 40px;
    box-shadow: 0 12px 36px rgba(15, 23, 42, 0.04);
    border: 1px solid rgba(226, 232, 240, 0.8);
    animation: fadeSlideUp 0.4s ease-out forwards;
}

@keyframes fadeSlideUp {
    from {
        opacity: 0;
        transform: translateY(15px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.brand-logo-icon {
    width: 56px;
    height: 56px;
    background: linear-gradient(135deg, #0f172a 0%, #334155 100%);
    color: #ffffff;
    border-radius: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    font-weight: 800;
    box-shadow: 0 8px 20px rgba(15, 23, 42, 0.15);
}

.custom-alert-danger {
    background-color: #fef2f2;
    color: #ef4444;
    padding: 12px 16px;
    border-radius: 12px;
    font-size: 13.5px;
    font-weight: 500;
    display: flex;
    align-items: center;
    gap: 10px;
    border: 1px solid #fee2e2;
}

.premium-label {
    font-size: 13.5px;
    font-weight: 700;
    color: #1e293b;
    margin-bottom: 8px;
    display: block;
}

.input-icon-wrapper {
    position: relative;
}

.input-icon-wrapper i {
    position: absolute;
    left: 18px;
    top: 50%;
    transform: translateY(-50%);
    color: #94a3b8;
    font-size: 15px;
    transition: color 0.3s ease;
    pointer-events: none;
}

.input-icon-wrapper:focus-within i {
    color: #0f172a;
}

.premium-input {
    height: 52px;
    padding-left: 46px;
    padding-right: 16px;
    background-color: #f8fafc;
    border: 1.5px solid #e2e8f0;
    border-radius: 14px;
    font-size: 14.5px;
    color: #0f172a;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: none !important;
}

.premium-input::placeholder {
    color: #cbd5e1;
    font-weight: 400;
}

.premium-input:focus {
    border-color: #0f172a;
    background-color: #ffffff;
    box-shadow: 0 4px 15px rgba(15, 23, 42, 0.05) !important;
}

.premium-auth-btn {
    height: 52px;
    background-color: #0f172a !important;
    border: none;
    border-radius: 14px;
    font-size: 15.5px;
    letter-spacing: 0.3px;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    transition: all 0.25s ease;
}

.premium-auth-btn:hover {
    background-color: #1e293b !important;
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(15, 23, 42, 0.2) !important;
}

.auth-footer-text {
    font-size: 14px;
    color: #64748b;
}

.auth-link {
    color: #0f172a;
    font-weight: 700;
    text-decoration: none;
    margin-left: 4px;
    transition: color 0.2s ease;
}

.auth-link:hover {
    color: #3b82f6;
}

@media (max-width: 767.98px) {
    .auth-premium-wrapper {
        background-color: #ffffff;
        min-height: 100dvh;
        justify-content: center;
    }
    
    .mobile-app-header {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        display: flex;
        align-items: center;
        gap: 16px;
        padding: 16px 20px;
        background-color: #ffffff;
        z-index: 10;
    }
    
    .back-btn {
        color: #0f172a;
        font-size: 20px;
        text-decoration: none;
    }

    .header-title {
        font-size: 17px;
        font-weight: 700;
        color: #0f172a;
    }

    .auth-container {
        padding: 0 24px;
        margin-top: 60px;
        flex-grow: 0;
        margin-bottom: 40px;
    }

    .auth-premium-card {
        border: none;
        box-shadow: none;
        border-radius: 0;
        padding: 0;
        animation: none;
    }

    .premium-input {
        background-color: #f1f5f9;
        border-color: transparent;
        border-radius: 12px;
    }
    
    .premium-input:focus {
        border-color: #0f172a;
        background-color: #ffffff;
    }
}
</style>
@endsection