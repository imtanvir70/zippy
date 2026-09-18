@extends('frontend.layouts.app')

@php
    $authBrand = $settings['store_name'] ?? 'ZippyBD';
    $authInitial = strtoupper(substr($authBrand, 0, 1));
@endphp

@section('title', 'কাস্টমার লগইন | ' . $authBrand)

@section('content')
<div class="auth-mobile-app-wrapper">

    <!-- Mobile Native App Top Bar (Back Button + Title + Home Button) -->
    <div class="mobile-nav-bar d-md-none">
        <a href="javascript:history.back()" class="mobile-nav-action-btn" aria-label="ফিরে যান">
            <i class="fa-solid fa-chevron-left"></i>
        </a>
        <div class="mobile-nav-title font-heading">
            <span>লগইন</span>
        </div>
        <a href="{{ route('home') }}" class="mobile-nav-action-btn" aria-label="হোম">
            <i class="fa-solid fa-house-chimney" style="font-size: 15px;"></i>
        </a>
    </div>

    <div class="container auth-content-container d-flex align-items-center justify-content-center">
        <div class="auth-app-card w-100">
            
            <!-- Mobile App Brand Hero Banner -->
            <div class="mobile-app-hero d-md-none text-center mb-4">
                <div class="app-avatar-box mx-auto mb-3 shadow-sm">
                    <span class="avatar-letter">{{ $authInitial }}</span>
                </div>
                <h2 class="app-hero-title font-heading mb-1">স্বাগতম! 👋</h2>
                <p class="app-hero-subtitle text-secondary mb-0">আপনার একাউন্টে প্রবেশ করে কেনাকাটা চালিয়ে যান</p>
            </div>

            <!-- Desktop View Header -->
            <div class="text-center mb-4 pb-1 d-none d-md-block">
                <div class="brand-logo-icon mx-auto mb-3 shadow-sm">{{ $authInitial }}</div>
                <h3 class="fw-bold text-dark font-heading mb-1 fs-4">স্বাগতম ফিরে এসেছেন!</h3>
                <p class="text-secondary small m-0">{{ $authBrand }}-এ আপনার একাউন্টে লগইন করে কেনাকাটা শুরু করুন</p>
            </div>

            <!-- Alert Notification -->
            @if(session('error'))
                <div class="custom-alert-danger mb-3">
                    <i class="fa-solid fa-circle-exclamation flex-shrink-0"></i>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            @if($errors->any())
                <div class="custom-alert-danger mb-3">
                    <i class="fa-solid fa-circle-exclamation flex-shrink-0"></i>
                    <span>{{ $errors->first() }}</span>
                </div>
            @endif

            <!-- Login Form -->
            <form action="{{ route('login.post') }}" method="POST" id="nativeLoginForm">
                @csrf
                
                <div class="app-field-group mb-3">
                    <label class="app-input-label">ইমেইল অথবা মোবাইল নম্বর</label>
                    <div class="app-input-box">
                        <div class="input-icon text-muted">
                            <i class="fa-regular fa-user"></i>
                        </div>
                        <input type="text" name="login" class="form-control app-input" placeholder="017XXXXXXXX বা user@mail.com" value="{{ old('login') }}" required autocomplete="username" inputmode="email">
                    </div>
                </div>

                <div class="app-field-group mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <label class="app-input-label m-0">পাসওয়ার্ড</label>
                        <a href="javascript:void(0)" class="forgot-link text-decoration-none">ভুলে গেছেন?</a>
                    </div>
                    <div class="app-input-box position-relative">
                        <div class="input-icon text-muted">
                            <i class="fa-solid fa-lock"></i>
                        </div>
                        <input type="password" name="password" id="loginPasswordField" class="form-control app-input pe-5" placeholder="••••••••" required autocomplete="current-password">
                        <button type="button" class="btn border-0 text-secondary position-absolute end-0 top-50 translate-middle-y px-3 shadow-none btn-toggle-pw" onclick="toggleLoginPassword()" aria-label="Show password">
                            <i class="fa-regular fa-eye" id="togglePasswordIcon"></i>
                        </button>
                    </div>
                </div>

                <!-- Primary Submit Action -->
                <button type="submit" class="btn btn-dark w-100 app-login-submit-btn mb-3">
                    <span>লগইন করুন</span>
                    <i class="fa-solid fa-arrow-right"></i>
                </button>
            </form>

            @if(!empty($socialLoginSetting) && $socialLoginSetting->is_google_active && !empty($socialLoginSetting->google_client_id))
                <!-- Social Divider -->
                <div class="app-divider my-3">
                    <span>অথবা</span>
                </div>

                <!-- Google OAuth One-Tap Style Button -->
                <a href="{{ route('google.redirect') }}" class="btn-google-native mb-4">
                    <svg width="20" height="20" viewBox="0 0 48 48">
                        <path fill="#EA4335" d="M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 13.72 17.74 9.5 24 9.5z"/>
                        <path fill="#4285F4" d="M46.98 24.55c0-1.57-.15-3.09-.38-4.55H24v9.02h12.94c-.58 2.96-2.26 5.48-4.78 7.18l7.73 6c4.51-4.18 7.09-10.36 7.09-17.65z"/>
                        <path fill="#FBBC05" d="M10.53 28.59c-.48-1.45-.76-2.99-.76-4.59s.27-3.14.76-4.59l-7.98-6.19C.92 16.46 0 20.12 0 24c0 3.88.92 7.54 2.56 10.78l7.97-6.19z"/>
                        <path fill="#34A853" d="M24 48c6.48 0 11.93-2.13 15.89-5.81l-7.73-6c-2.15 1.45-4.92 2.3-8.16 2.3-6.26 0-11.57-4.22-13.47-9.91l-7.98 6.19C6.51 42.62 14.62 48 24 48z"/>
                    </svg>
                    <span>Google দিয়ে সাইন ইন</span>
                </a>
            @endif

            <!-- Registration Footer Prompt -->
            <div class="text-center auth-footer-box">
                <span class="text-secondary">নতুন কাস্টমার?</span>
                <a href="{{ route('register') }}" class="register-app-link ms-1">নতুন একাউন্ট খুলুন</a>
            </div>

            <!-- Trust Badge for Mobile -->
            <div class="d-flex align-items-center justify-content-center gap-2 mt-4 pt-2 text-muted" style="font-size: 11.5px;">
                <i class="fa-solid fa-shield-check text-success"></i>
                <span>১০০% সুরক্ষিত ও নিরাপদ লগইন সিস্টেম</span>
            </div>
            
        </div>
    </div>
</div>

<script data-turbo-eval="false">
function toggleLoginPassword() {
    const input = document.getElementById('loginPasswordField');
    const icon = document.getElementById('togglePasswordIcon');
    if (!input || !icon) return;
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}
</script>

<style>
/* Base Wrapper */
.auth-mobile-app-wrapper {
    background-color: #f8fafc;
    min-height: 80vh;
    display: flex;
    flex-direction: column;
}

/* Native Mobile Top Bar */
.mobile-nav-bar {
    position: sticky;
    top: 0;
    left: 0;
    right: 0;
    height: 56px;
    background: rgba(255, 255, 255, 0.96);
    backdrop-filter: blur(16px);
    -webkit-backdrop-filter: blur(16px);
    border-bottom: 1px solid #e2e8f0;
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0 16px;
    z-index: 1040;
}

.mobile-nav-action-btn {
    width: 38px;
    height: 38px;
    border-radius: 50%;
    background-color: #f1f5f9;
    color: #0f172a;
    display: flex;
    align-items: center;
    justify-content: center;
    text-decoration: none;
    font-size: 15px;
    transition: background-color 0.2s;
}
.mobile-nav-action-btn:active {
    background-color: #e2e8f0;
}

.mobile-nav-title {
    font-weight: 700;
    font-size: 16.5px;
    color: #0f172a;
}

/* Container & Card */
.auth-content-container {
    flex-grow: 1;
    padding-top: 36px;
    padding-bottom: 50px;
}

.auth-app-card {
    max-width: 440px;
    background-color: #ffffff;
    border-radius: 24px;
    padding: 38px 34px;
    box-shadow: 0 12px 36px rgba(15, 23, 42, 0.05);
    border: 1px solid rgba(226, 232, 240, 0.9);
}

/* Mobile Hero Avatar */
.app-avatar-box {
    width: 60px;
    height: 60px;
    border-radius: 18px;
    background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
    color: #ffffff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 26px;
    font-weight: 800;
    box-shadow: 0 8px 20px rgba(15, 23, 42, 0.15);
}

.brand-logo-icon {
    width: 54px;
    height: 54px;
    background: #0f172a;
    color: #ffffff;
    border-radius: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    font-weight: 800;
}

.app-hero-title {
    font-size: 22px;
    font-weight: 800;
    color: #0f172a;
}

.app-hero-subtitle {
    font-size: 13.5px;
}

/* Form Elements */
.app-input-label {
    display: block;
    font-size: 13px;
    font-weight: 600;
    color: #334155;
    margin-bottom: 6px;
}

.app-input-box {
    position: relative;
    display: flex;
    align-items: center;
}

.app-input-box .input-icon {
    position: absolute;
    left: 16px;
    font-size: 15px;
    pointer-events: none;
    z-index: 3;
}

.app-input {
    height: 50px;
    padding-left: 44px;
    padding-right: 16px;
    border-radius: 14px;
    border: 1.5px solid #e2e8f0;
    background-color: #f8fafc;
    font-size: 14.5px;
    color: #0f172a;
    font-weight: 500;
    transition: all 0.2s ease;
}

.app-input::placeholder {
    color: #94a3b8;
    font-size: 13.5px;
}

.app-input:focus {
    background-color: #ffffff;
    border-color: #0f172a;
    box-shadow: 0 0 0 3.5px rgba(15, 23, 42, 0.08);
}

.forgot-link {
    font-size: 12.5px;
    font-weight: 600;
    color: #475569;
}
.forgot-link:hover {
    color: #0f172a;
}

.btn-toggle-pw {
    z-index: 4;
}
.btn-toggle-pw:hover {
    color: #0f172a;
}

/* Login Submit Button */
.app-login-submit-btn {
    height: 50px;
    border-radius: 14px;
    background: #0f172a;
    border: none;
    font-weight: 700;
    font-size: 15px;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    transition: all 0.2s ease;
}

.app-login-submit-btn:hover {
    background: #1e293b;
    box-shadow: 0 6px 18px rgba(15, 23, 42, 0.18);
}
.app-login-submit-btn:active {
    transform: scale(0.985);
}

/* Divider */
.app-divider {
    display: flex;
    align-items: center;
    text-align: center;
    color: #94a3b8;
    font-size: 12px;
}
.app-divider::before,
.app-divider::after {
    content: '';
    flex: 1;
    border-bottom: 1px solid #e2e8f0;
}
.app-divider span {
    padding: 0 12px;
    font-weight: 500;
}

/* Google Native Button */
.btn-google-native {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    width: 100%;
    height: 50px;
    background-color: #ffffff;
    color: #1e293b;
    font-size: 14.5px;
    font-weight: 600;
    text-decoration: none;
    border: 1.5px solid #e2e8f0;
    border-radius: 14px;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.02);
    transition: all 0.2s ease;
}
.btn-google-native:hover {
    background-color: #f8fafc;
    border-color: #cbd5e1;
    color: #0f172a;
}
.btn-google-native:active {
    transform: scale(0.985);
}

.auth-footer-box {
    font-size: 13.5px;
}
.register-app-link {
    color: #0f172a;
    font-weight: 700;
    text-decoration: none;
}
.register-app-link:hover {
    text-decoration: underline;
}

/* Custom Alert */
.custom-alert-danger {
    background-color: #fef2f2;
    border: 1px solid #fee2e2;
    border-radius: 12px;
    padding: 12px 14px;
    display: flex;
    align-items: center;
    gap: 10px;
    color: #ef4444;
    font-size: 13px;
    font-weight: 500;
}

/* MOBILE APP RESPONSIVE OPTIMIZATIONS */
@media (max-width: 767.98px) {
    .auth-mobile-app-wrapper {
        background-color: #ffffff;
        min-height: 100dvh;
    }

    .auth-content-container {
        padding-top: 18px;
        padding-bottom: 28px;
        padding-left: 20px;
        padding-right: 20px;
        align-items: flex-start !important;
    }

    .auth-app-card {
        border: none;
        box-shadow: none;
        border-radius: 0;
        padding: 0;
        max-width: 100%;
    }

    .app-input {
        background-color: #f8fafc;
        border: 1.5px solid #e2e8f0;
        font-size: 15px;
    }

    .app-login-submit-btn,
    .btn-google-native {
        height: 52px;
        font-size: 15.5px;
    }
}
</style>
@endsection