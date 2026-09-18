@extends('backend.layouts.app')

@section('title', 'Social Login Settings')

@section('content')
<!-- Top Header Card -->
<div class="card p-3 p-md-4 mb-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
        <div class="d-flex align-items-center gap-3">
            <div class="stat-icon" style="width: 48px; height: 48px; border-radius: 14px;">
                <i class="fa-solid fa-users-gear"></i>
            </div>
            <div>
                <h3 class="fw-bold mb-1">Social Login Configurations</h3>
                <small class="text-muted">Google OAuth ক্রেডেনশিয়াল ও সিঙ্গেল সাইন-অন (SSO) সেটিংস ডায়নামিকভাবে নিয়ন্ত্রণ করুন।</small>
            </div>
        </div>
        <div>
            @if(!empty($setting->is_google_active) && !empty($setting->google_client_id))
                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-2 fw-semibold d-inline-flex align-items-center gap-2">
                    <i class="fa-solid fa-circle" style="font-size: 8px;"></i> Google Login Active
                </span>
            @else
                <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill px-3 py-2 fw-semibold d-inline-flex align-items-center gap-2">
                    <i class="fa-solid fa-circle" style="font-size: 8px;"></i> Google Login Disabled
                </span>
            @endif
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Main Form Configuration Card -->
    <div class="col-lg-7">
        <div class="card p-3 p-md-4 h-100">
            <form action="{{ route('admin.social_settings.update') }}" method="POST">
                @csrf
                
                <!-- Google Card Top Switch Header -->
                <div class="d-flex justify-content-between align-items-center p-3 rounded-3 mb-4" style="background: var(--surface); border: 1px solid var(--border-color);">
                    <div class="d-flex align-items-center gap-3">
                        <div class="p-2 rounded-3 d-flex align-items-center justify-content-center" style="width: 42px; height: 42px; background: var(--surface-2); border: 1px solid var(--border-color);">
                            <svg width="22" height="22" viewBox="0 0 48 48">
                                <path fill="#EA4335" d="M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 13.72 17.74 9.5 24 9.5z"/>
                                <path fill="#4285F4" d="M46.98 24.55c0-1.57-.15-3.09-.38-4.55H24v9.02h12.94c-.58 2.96-2.26 5.48-4.78 7.18l7.73 6c4.51-4.18 7.09-10.36 7.09-17.65z"/>
                                <path fill="#FBBC05" d="M10.53 28.59c-.48-1.45-.76-2.99-.76-4.59s.27-3.14.76-4.59l-7.98-6.19C.92 16.46 0 20.12 0 24c0 3.88.92 7.54 2.56 10.78l7.97-6.19z"/>
                                <path fill="#34A853" d="M24 48c6.48 0 11.93-2.13 15.89-5.81l-7.73-6c-2.15 1.45-4.92 2.3-8.16 2.3-6.26 0-11.57-4.22-13.47-9.91l-7.98 6.19C6.51 42.62 14.62 48 24 48z"/>
                            </svg>
                        </div>
                        <div>
                            <h6 class="fw-bold mb-0">Google Social Login</h6>
                            <small class="text-muted">লগইন পেজে গুগল বোতাম সক্রিয়/নিষ্ক্রিয় করুন</small>
                        </div>
                    </div>
                    <div class="form-check form-switch mb-0">
                        <input class="form-check-input" type="checkbox" name="is_google_active" id="isGoogleActive" value="1" {{ $setting->is_google_active ? 'checked' : '' }} style="cursor: pointer; width: 2.5em; height: 1.3em;">
                    </div>
                </div>

                <!-- Google Client ID -->
                <div class="mb-3">
                    <label class="form-label small fw-bold mb-1">
                        Google Client ID <span class="text-danger">*</span>
                    </label>
                    <input type="text" name="google_client_id" class="form-control font-monospace" value="{{ $setting->google_client_id }}" placeholder="xxxxxxxxxxxx-xxxxxxxxxxxxxxxx.apps.googleusercontent.com" autocomplete="off">
                    <small class="text-muted d-block mt-1">Google Cloud Console থেকে প্রাপ্ত ওয়েব ক্লায়েন্ট আইডি এখানে লিখুন।</small>
                </div>

                <!-- Google Client Secret -->
                <div class="mb-3">
                    <label class="form-label small fw-bold mb-1">
                        Google Client Secret <span class="text-danger">*</span>
                    </label>
                    <div class="input-group">
                        <input type="password" name="google_client_secret" id="google_client_secret" class="form-control font-monospace" value="{{ $setting->google_client_secret }}" placeholder="GOCSPX-xxxxxxxxxxxxxxxxxxxxxx" autocomplete="off">
                        <button type="button" class="btn btn-outline-secondary" onclick="toggleSecret()" title="পাসওয়ার্ড দেখুন/লুকান">
                            <i class="fa-solid fa-eye" id="secretIcon"></i>
                        </button>
                    </div>
                </div>

                <!-- Authorized Redirect URI -->
                <div class="mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <label class="form-label small fw-bold m-0">Authorized Redirect URI (Callback URL)</label>
                        <button type="button" class="btn btn-link p-0 text-decoration-none small fw-semibold" onclick="copyCallbackUrl()" style="color: var(--accent);">
                            <i class="fa-regular fa-copy me-1"></i> কপি করুন
                        </button>
                    </div>
                    @php
                        $callbackUrl = $setting->google_redirect_url ?: url('/auth/google/callback');
                    @endphp
                    <div class="input-group">
                        <input type="text" id="googleCallbackUrlInput" name="google_redirect_url" class="form-control font-monospace" value="{{ $callbackUrl }}" readonly>
                        <button type="button" class="btn btn-outline-secondary" onclick="copyCallbackUrl()">
                            <i class="fa-solid fa-copy"></i>
                        </button>
                    </div>
                    <small class="text-muted d-block mt-1">
                        গুগল ক্লাউড কনসোলের <strong>"Authorized redirect URIs"</strong> ফিল্ডে এই লিংকটি পেস্ট করতে হবে।
                    </small>
                </div>

                <!-- Save Action Button -->
                <div class="d-flex align-items-center gap-2 pt-3 border-top" style="border-color: var(--border-color) !important;">
                    <button type="submit" class="btn btn-primary px-4 fw-bold">
                        <i class="fa-solid fa-floppy-disk me-1"></i> Save Configurations
                    </button>
                    <a href="{{ route('login') }}" target="_blank" class="btn btn-action w-auto">
                        <i class="fa-solid fa-arrow-up-right-from-square"></i>
                        <span>View Login Page</span>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Quick Guide & Help Card -->
    <div class="col-lg-5">
        <div class="card p-3 p-md-4 h-100">
            <h5 class="fw-bold mb-3 d-flex align-items-center gap-2">
                <i class="fa-solid fa-circle-question" style="color: var(--accent);"></i>
                <span>সেটআপ গাইডলাইন</span>
            </h5>
            
            <p class="text-muted small mb-3">
                Google Social Login সক্রিয় করার জন্য নিচের সহজ ধাপগুলো অনুসরণ করে আপনার ক্লাউড ক্রেডেনশিয়াল সংগ্রহ করুন:
            </p>

            <div class="d-flex flex-column gap-3">
                <div class="d-flex align-items-start gap-3 p-3 rounded-3" style="background: var(--surface); border: 1px solid var(--border-color);">
                    <span class="badge bg-primary rounded-circle p-2 d-inline-flex align-items-center justify-content-center" style="width: 26px; height: 26px; font-size: 12px;">১</span>
                    <div>
                        <h6 class="fw-bold mb-1 small">গুগল ক্লাউড কনসোলে যান</h6>
                        <small class="text-muted">
                            <a href="https://console.cloud.google.com/apis/credentials" target="_blank" class="text-decoration-none fw-semibold" style="color: var(--accent);">Google Cloud Console</a>-এ গিয়ে প্রজেক্ট তৈরি বা সিলেক্ট করুন।
                        </small>
                    </div>
                </div>

                <div class="d-flex align-items-start gap-3 p-3 rounded-3" style="background: var(--surface); border: 1px solid var(--border-color);">
                    <span class="badge bg-primary rounded-circle p-2 d-inline-flex align-items-center justify-content-center" style="width: 26px; height: 26px; font-size: 12px;">২</span>
                    <div>
                        <h6 class="fw-bold mb-1 small">OAuth Consent Screen কনফিগার</h6>
                        <small class="text-muted">
                            User Type "External" দিন এবং অ্যাপ নাম হিসেবে <strong>{{ $settings['store_name'] ?? 'ZippyBD' }}</strong> দিন।
                        </small>
                    </div>
                </div>

                <div class="d-flex align-items-start gap-3 p-3 rounded-3" style="background: var(--surface); border: 1px solid var(--border-color);">
                    <span class="badge bg-primary rounded-circle p-2 d-inline-flex align-items-center justify-content-center" style="width: 26px; height: 26px; font-size: 12px;">৩</span>
                    <div>
                        <h6 class="fw-bold mb-1 small">OAuth 2.0 Client ID তৈরি করুন</h6>
                        <small class="text-muted">
                            Application type "Web application" দিন এবং পাশের Callback URL-টি Authorized redirect URI-তে বসান।
                        </small>
                    </div>
                </div>

                <div class="d-flex align-items-start gap-3 p-3 rounded-3" style="background: var(--surface); border: 1px solid var(--border-color);">
                    <span class="badge bg-primary rounded-circle p-2 d-inline-flex align-items-center justify-content-center" style="width: 26px; height: 26px; font-size: 12px;">৪</span>
                    <div>
                        <h6 class="fw-bold mb-1 small">সক্রিয় বা নিষ্ক্রিয় সুবিধা</h6>
                        <small class="text-muted">
                            সুইচ অফ করে সেভ করলে ফ্রন্টএন্ড <strong>/login</strong> পেজে স্বয়ংক্রিয়ভাবে Google সাইন-ইন বাটন সম্পূর্ণ লুকিয়ে যাবে।
                        </small>
                    </div>
                </div>
            </div>

            <!-- Notice Alert -->
            <div class="mt-3 p-3 rounded-3 bg-warning-subtle text-warning-emphasis border border-warning-subtle d-flex align-items-start gap-2 small">
                <i class="fa-solid fa-triangle-exclamation mt-0.5 flex-shrink-0 text-warning"></i>
                <div>
                    <strong>টিপস:</strong> Client ID বা Secret ফাঁকা থাকলে অথবা সুইচ অফ থাকলে ফ্রন্টএন্ডে গুগল লগইন ডিসপ্লে হবে না।
                </div>
            </div>
        </div>
    </div>
</div>

<script>
(() => {
    function toggleSecret() {
        const input = document.getElementById('google_client_secret');
        const icon = document.getElementById('secretIcon');
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

    function copyCallbackUrl() {
        const copyText = document.getElementById("googleCallbackUrlInput");
        if (!copyText) return;
        copyText.select();
        copyText.setSelectionRange(0, 99999);
        navigator.clipboard.writeText(copyText.value).then(() => {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'success',
                    title: 'Callback URL কপি হয়েছে!',
                    showConfirmButton: false,
                    timer: 2000
                });
            } else if (window.showToast) {
                window.showToast('Callback URL কপি হয়েছে!', 'success');
            }
        });
    }

    window.toggleSecret = toggleSecret;
    window.copyCallbackUrl = copyCallbackUrl;
})();
</script>
@endsection


