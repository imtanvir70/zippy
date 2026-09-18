<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Recovery | {{ $storeName ?? 'Zippy' }} Security Desk</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('backend/lib/all.min.css') }}">
    <link rel="stylesheet" href="{{ asset('backend/lib/bootstrap.min.css') }}">
    <script src="{{ asset('lib/axios.min.js') }}"></script>

    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 1rem;
            background-color: #eaf1fb;
            background-image: 
                radial-gradient(at 10% 15%, rgba(37, 99, 235, 0.18) 0px, transparent 40%),
                radial-gradient(at 90% 10%, rgba(14, 165, 233, 0.2) 0px, transparent 45%),
                radial-gradient(at 80% 85%, rgba(99, 102, 241, 0.18) 0px, transparent 45%),
                radial-gradient(at 15% 90%, rgba(56, 189, 248, 0.22) 0px, transparent 40%);
            background-attachment: fixed;
            color: #1e293b;
        }

        .auth-container {
            width: 100%;
            max-width: 960px;
            background: rgba(255, 255, 255, 0.72);
            backdrop-filter: blur(24px) saturate(190%);
            -webkit-backdrop-filter: blur(24px) saturate(190%);
            border: 1px solid rgba(255, 255, 255, 0.85);
            border-radius: 20px;
            box-shadow: 
                0 25px 50px -12px rgba(37, 99, 235, 0.16),
                0 0 0 1px rgba(255, 255, 255, 0.8) inset;
            overflow: hidden;
            display: flex;
        }

        .auth-showcase {
            flex: 1;
            padding: 2.5rem;
            background: linear-gradient(150deg, rgba(240, 246, 255, 0.65) 0%, rgba(255, 255, 255, 0.3) 100%);
            border-right: 1px solid rgba(226, 232, 240, 0.8);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .auth-form-panel {
            flex: 1;
            padding: 2.5rem;
            background: rgba(255, 255, 255, 0.55);
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        @media (max-width: 860px) {
            .auth-container {
                max-width: 440px;
                flex-direction: column;
            }
            .auth-showcase {
                display: none;
            }
            .auth-form-panel {
                padding: 2rem 1.5rem;
            }
        }

        .brand-header-row {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 2rem;
        }

        .brand-badge {
            width: 44px;
            height: 44px;
            min-width: 44px;
            border-radius: 12px;
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #ffffff;
            font-size: 1.3rem;
            font-weight: 800;
            box-shadow: 0 8px 16px -2px rgba(37, 99, 235, 0.35);
        }

        .brand-info h4 {
            font-size: 1.2rem;
            font-weight: 800;
            color: #0f172a;
            line-height: 1.2;
        }

        .brand-info span {
            font-size: 0.75rem;
            color: #64748b;
        }

        .showcase-heading {
            font-size: 1.35rem;
            font-weight: 800;
            color: #0f172a;
            margin-bottom: 0.5rem;
            letter-spacing: -0.02em;
        }

        .showcase-subtext {
            font-size: 0.8125rem;
            color: #64748b;
            line-height: 1.55;
            margin-bottom: 1.75rem;
        }

        .feature-list {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }

        .feature-card {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 0.85rem 1rem;
            background: rgba(255, 255, 255, 0.6);
            border: 1px solid rgba(255, 255, 255, 0.8);
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.03);
        }

        .feature-icon-box {
            width: 36px;
            height: 36px;
            min-width: 36px;
            border-radius: 10px;
            background: #eff6ff;
            color: #2563eb;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.9rem;
        }

        .feature-title {
            font-size: 0.8125rem;
            font-weight: 700;
            color: #0f172a;
            line-height: 1.2;
        }

        .feature-desc {
            font-size: 0.72rem;
            color: #64748b;
            margin-top: 2px;
        }

        .live-status-pill {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 6px 14px;
            background: rgba(236, 253, 245, 0.85);
            border: 1px solid #a7f3d0;
            border-radius: 9999px;
            font-size: 0.72rem;
            font-weight: 600;
            color: #059669;
            margin-top: 2rem;
        }

        .pulse-dot {
            width: 7px;
            height: 7px;
            background-color: #059669;
            border-radius: 50%;
            box-shadow: 0 0 0 0 rgba(5, 150, 105, 0.6);
            animation: pulseDot 1.8s infinite;
        }

        @keyframes pulseDot {
            0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(5, 150, 105, 0.6); }
            70% { transform: scale(1); box-shadow: 0 0 0 6px rgba(5, 150, 105, 0); }
            100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(5, 150, 105, 0); }
        }

        .step-progress-tracker {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 1.5rem;
        }

        .step-pill {
            flex: 1;
            height: 4px;
            border-radius: 999px;
            background: #cbd5e1;
            transition: all 0.3s ease;
        }

        .step-pill.active {
            background: linear-gradient(135deg, #2563eb 0%, #0ea5e9 100%);
        }

        .form-header {
            margin-bottom: 1.25rem;
        }

        .form-header h3 {
            font-size: 1.35rem;
            font-weight: 800;
            color: #0f172a;
            letter-spacing: -0.02em;
            margin-bottom: 0.25rem;
        }

        .form-header p {
            font-size: 0.8125rem;
            color: #64748b;
        }

        .info-pill-box {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0.65rem 1rem;
            background: rgba(239, 246, 255, 0.8);
            border: 1px solid #bfdbfe;
            border-radius: 10px;
            margin-bottom: 1.25rem;
            font-size: 0.8rem;
            color: #334155;
        }

        .input-block {
            margin-bottom: 1.15rem;
        }

        .input-label-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 8px;
        }

        .field-label {
            font-size: 0.7rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #64748b;
            display: block;
        }

        .otp-inputs-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
            margin-top: 4px;
        }

        .otp-box-cell {
            flex: 1;
            height: 52px;
            max-width: 54px;
            border-radius: 12px;
            border: 1.5px solid #cbd5e1;
            background: rgba(255, 255, 255, 0.85);
            font-size: 1.35rem;
            font-weight: 800;
            text-align: center;
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
            color: #0f172a;
            outline: none;
            box-shadow: 0 2px 6px rgba(15, 23, 42, 0.03);
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .otp-box-cell:focus {
            border-color: #2563eb;
            background: #ffffff;
            transform: translateY(-2px) scale(1.03);
            box-shadow: 0 8px 18px -2px rgba(37, 99, 235, 0.22), 0 0 0 3px rgba(37, 99, 235, 0.15);
        }

        .otp-box-cell.filled {
            border-color: #93c5fd;
            background: #f8faff;
        }

        .otp-box-cell.is-valid {
            border-color: #10b981 !important;
            background: #f0fdf4 !important;
            box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.2) !important;
        }

        .otp-box-cell.is-invalid {
            border-color: #ef4444 !important;
            background: #fef2f2 !important;
            box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.2) !important;
        }

        .otp-inputs-row.shake {
            animation: otpShake 0.3s cubic-bezier(0.36, 0.07, 0.19, 0.97) both;
        }

        @keyframes otpShake {
            0%, 100% { transform: translateX(0); }
            20%, 60% { transform: translateX(-6px); }
            40%, 80% { transform: translateX(6px); }
        }

        .input-field-wrap {
            display: flex;
            align-items: center;
            height: 44px;
            background: rgba(255, 255, 255, 0.85);
            border: 1px solid #cbd5e1;
            border-radius: 10px;
            overflow: hidden;
            transition: border-color 0.15s, box-shadow 0.15s;
        }

        .input-field-wrap:focus-within {
            border-color: #2563eb;
            background: #ffffff;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15);
        }

        .input-prefix-icon {
            width: 42px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #94a3b8;
            font-size: 0.9rem;
            flex-shrink: 0;
        }

        .input-element {
            flex: 1;
            height: 100%;
            border: none;
            background: transparent;
            outline: none;
            font-size: 0.875rem;
            color: #0f172a;
            font-weight: 500;
            padding-right: 12px;
        }

        .input-element::placeholder {
            color: #94a3b8;
        }

        .btn-eye-toggle {
            background: transparent;
            border: none;
            color: #94a3b8;
            padding: 0 12px;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
        }

        .btn-eye-toggle:hover {
            color: #0f172a;
        }

        .btn-primary-submit {
            width: 100%;
            height: 44px;
            border-radius: 10px;
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            border: none;
            color: #ffffff;
            font-size: 0.875rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            cursor: pointer;
            box-shadow: 0 8px 18px -3px rgba(37, 99, 235, 0.35);
            transition: transform 0.15s ease, box-shadow 0.15s ease;
        }

        .btn-primary-submit:hover {
            transform: translateY(-1px);
            box-shadow: 0 10px 22px -3px rgba(37, 99, 235, 0.45);
        }

        .bottom-actions-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-top: 1.25rem;
            padding-top: 1rem;
            border-top: 1px solid #e2e8f0;
        }

        .action-link {
            text-decoration: none;
            color: #64748b;
            font-size: 0.8125rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: none;
            border: none;
            padding: 0;
            cursor: pointer;
        }

        .action-link:hover {
            color: #2563eb;
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <div class="auth-showcase">
            <div>
                <div class="brand-header-row">
                    <div class="brand-badge">
                        <span>{{ strtoupper(substr($storeName ?? 'Zippy', 0, 1)) }}</span>
                    </div>
                    <div class="brand-info">
                        <h4>{{ $storeName ?? 'Zippy' }}</h4>
                        <span>Operations & Security Desk</span>
                    </div>
                </div>

                <h3 class="showcase-heading">Cryptographic Account Recovery</h3>
                <p class="showcase-subtext">Secure verification codes dispatched through authenticated enterprise SMTP with strict token expiration and automated audit tracing.</p>

                <div class="feature-list">
                    <div class="feature-card">
                        <div class="feature-icon-box">
                            <i class="fa-solid fa-envelope-circle-check"></i>
                        </div>
                        <div>
                            <div class="feature-title">Direct SMTP Delivery</div>
                            <div class="feature-desc">Zero-delay OTP dispatch via dedicated mail server</div>
                        </div>
                    </div>

                    <div class="feature-card">
                        <div class="feature-icon-box">
                            <i class="fa-solid fa-stopwatch"></i>
                        </div>
                        <div>
                            <div class="feature-title">15-Minute Expiry Window</div>
                            <div class="feature-desc">Single-use cryptographic verification protection</div>
                        </div>
                    </div>

                    <div class="feature-card">
                        <div class="feature-icon-box">
                            <i class="fa-solid fa-shield-halved"></i>
                        </div>
                        <div>
                            <div class="feature-title">Argon2 / Bcrypt Encrypted</div>
                            <div class="feature-desc">Military-grade password protection protocols</div>
                        </div>
                    </div>
                </div>
            </div>

            <div>
                <div class="live-status-pill">
                    <span class="pulse-dot"></span>
                    <span>Security Desk Active &bull; SSL/TLS Encrypted</span>
                </div>
            </div>
        </div>

        <div class="auth-form-panel">
            <div class="step-progress-tracker">
                <div class="step-pill active" title="Step 1: Request Code"></div>
                <div class="step-pill {{ ($step ?? 1) == 2 ? 'active' : '' }}" title="Step 2: Verify & Reset"></div>
            </div>

            @if(session('error'))
                <div class="alert alert-danger py-2 px-3 small rounded-3 mb-3 border-0 bg-danger-subtle text-danger d-flex align-items-center gap-2" style="font-size: 0.78rem;">
                    <i class="fa-solid fa-circle-exclamation flex-shrink-0"></i>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            @if(session('success'))
                <div class="alert alert-success py-2 px-3 small rounded-3 mb-3 border-0 bg-success-subtle text-success d-flex align-items-center gap-2" style="font-size: 0.78rem;">
                    <i class="fa-solid fa-circle-check flex-shrink-0"></i>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger py-2 px-3 small rounded-3 mb-3 border-0 bg-danger-subtle text-danger d-flex align-items-center gap-2" style="font-size: 0.78rem;">
                    <i class="fa-solid fa-circle-exclamation flex-shrink-0"></i>
                    <span>{{ $errors->first() }}</span>
                </div>
            @endif

            @if(($step ?? 1) == 2 && !empty($email))
                <div class="form-header">
                    <h3>Verify & Reset</h3>
                    <p>Enter the 6-digit verification code and set your new password.</p>
                </div>

                <div class="info-pill-box">
                    <div class="d-flex align-items-center gap-1.5">
                        <i class="fa-solid fa-envelope-circle-check text-primary"></i>
                        <span>Code sent to <strong class="text-dark">{{ $email }}</strong></span>
                    </div>
                    <a href="{{ route('admin.forgot_password', ['step' => 1, 'email' => $email]) }}" class="action-link" style="color: #2563eb;">Change</a>
                </div>

                <form method="POST" action="{{ route('admin.forgot_password.reset') }}" id="resetPasswordForm" novalidate>
                    @csrf
                    <input type="hidden" name="email" value="{{ old('email', $email) }}">
                    <input type="hidden" name="otp" id="realOtpHiddenInput" value="" required>

                    <div class="input-block">
                        <div class="input-label-row">
                            <label class="field-label">6-Digit Security Code</label>
                            <span class="text-muted small" style="font-size: 0.7rem;">Paste supported</span>
                        </div>
                        <div class="otp-inputs-row" id="otpBoxContainer">
                            <input type="text" class="otp-box-cell" maxlength="1" inputmode="numeric" pattern="[0-9]" autocomplete="one-time-code" data-index="0" autofocus>
                            <input type="text" class="otp-box-cell" maxlength="1" inputmode="numeric" pattern="[0-9]" data-index="1">
                            <input type="text" class="otp-box-cell" maxlength="1" inputmode="numeric" pattern="[0-9]" data-index="2">
                            <input type="text" class="otp-box-cell" maxlength="1" inputmode="numeric" pattern="[0-9]" data-index="3">
                            <input type="text" class="otp-box-cell" maxlength="1" inputmode="numeric" pattern="[0-9]" data-index="4">
                            <input type="text" class="otp-box-cell" maxlength="1" inputmode="numeric" pattern="[0-9]" data-index="5">
                        </div>
                        <div id="otpFeedbackMsg" style="min-height: 22px; margin-top: 6px; font-size: 0.75rem; font-weight: 600; display: flex; align-items: center; gap: 6px;"></div>
                    </div>

                    <div class="input-block">
                        <div class="input-label-row">
                            <label class="field-label" for="newPasswordInput">New Password</label>
                        </div>
                        <div class="input-field-wrap">
                            <span class="input-prefix-icon"><i class="fa-solid fa-lock"></i></span>
                            <input type="password" name="password" id="newPasswordInput" class="input-element" placeholder="Min. 6 characters" required autocomplete="new-password">
                            <button type="button" class="btn-eye-toggle" onclick="toggleFieldVisibility('newPasswordInput', 'newEyeIcon')" aria-label="Toggle password visibility">
                                <i class="fa-regular fa-eye" id="newEyeIcon"></i>
                            </button>
                        </div>
                    </div>

                    <div class="input-block">
                        <div class="input-label-row">
                            <label class="field-label" for="confirmPasswordInput">Confirm New Password</label>
                        </div>
                        <div class="input-field-wrap">
                            <span class="input-prefix-icon"><i class="fa-solid fa-lock"></i></span>
                            <input type="password" name="password_confirmation" id="confirmPasswordInput" class="input-element" placeholder="Repeat new password" required autocomplete="new-password">
                            <button type="button" class="btn-eye-toggle" onclick="toggleFieldVisibility('confirmPasswordInput', 'confirmEyeIcon')" aria-label="Toggle confirm password visibility">
                                <i class="fa-regular fa-eye" id="confirmEyeIcon"></i>
                            </button>
                        </div>
                    </div>

                    <button type="submit" class="btn-primary-submit" id="submitResetBtn">
                        <span>Save Password & Sign In</span>
                        <i class="fa-solid fa-arrow-right fa-xs"></i>
                    </button>
                </form>

                <div class="bottom-actions-row">
                    <form method="POST" action="{{ route('admin.forgot_password.send_otp') }}" class="d-inline">
                        @csrf
                        <input type="hidden" name="email" value="{{ old('email', $email) }}">
                        <button type="submit" class="action-link">
                            <i class="fa-solid fa-rotate-right fa-xs"></i>
                            <span>Resend Code</span>
                        </button>
                    </form>
                    <a href="{{ route('admin.login') }}" class="action-link">
                        <i class="fa-solid fa-arrow-left fa-xs"></i>
                        <span>Back to Sign In</span>
                    </a>
                </div>
            @else
                <div class="form-header">
                    <h3>Reset Password</h3>
                    <p>Enter your registered administrator email to receive a 6-digit OTP.</p>
                </div>

                <form method="POST" action="{{ route('admin.forgot_password.send_otp') }}" id="sendOtpForm" novalidate>
                    @csrf
                    <div class="input-block">
                        <div class="input-label-row">
                            <label class="field-label" for="forgotEmailInput">Administrator Email Address</label>
                        </div>
                        <div class="input-field-wrap">
                            <span class="input-prefix-icon"><i class="fa-regular fa-envelope"></i></span>
                            <input type="email" name="email" id="forgotEmailInput" class="input-element" placeholder="admin@zippybd.com" value="{{ old('email', $email ?? '') }}" required autocomplete="email" autofocus>
                        </div>
                    </div>

                    <button type="submit" class="btn-primary-submit" id="submitSendOtpBtn">
                        <span>Dispatch Security Code</span>
                        <i class="fa-solid fa-arrow-right fa-xs"></i>
                    </button>
                </form>

                <div class="bottom-actions-row justify-content-center">
                    <a href="{{ route('admin.login') }}" class="action-link">
                        <i class="fa-solid fa-arrow-left fa-xs"></i>
                        <span>Back to Sign In</span>
                    </a>
                </div>
            @endif
        </div>
    </div>

    <script>
        function toggleFieldVisibility(inputId, iconId) {
            const input = document.getElementById(inputId);
            const icon = document.getElementById(iconId);
            if (!input || !icon) return;

            if (input.type === 'password') {
                input.type = 'text';
                icon.className = 'fa-regular fa-eye-slash';
            } else {
                input.type = 'password';
                icon.className = 'fa-regular fa-eye';
            }
        }

        document.addEventListener('DOMContentLoaded', function () {
            const otpCells = document.querySelectorAll('.otp-box-cell');
            const hiddenOtp = document.getElementById('realOtpHiddenInput');
            const otpBoxContainer = document.getElementById('otpBoxContainer');
            const feedbackMsg = document.getElementById('otpFeedbackMsg');
            let activeAjaxController = null;

            function clearOtpFeedback() {
                otpCells.forEach(cell => {
                    cell.classList.remove('is-valid', 'is-invalid');
                });
                if (otpBoxContainer) {
                    otpBoxContainer.classList.remove('shake');
                }
                if (feedbackMsg) {
                    feedbackMsg.innerHTML = '';
                }
                if (activeAjaxController) {
                    activeAjaxController.abort();
                    activeAjaxController = null;
                }
            }

            function triggerOtpValidation(code) {
                if (activeAjaxController) {
                    activeAjaxController.abort();
                }
                activeAjaxController = new AbortController();

                const emailInput = document.querySelector('input[name="email"]');
                const emailVal = emailInput ? emailInput.value : "{{ old('email', $email ?? '') }}";
                const ajaxUrl = "{{ route('admin.forgot_password.verify_otp_ajax') }}";
                const token = "{{ csrf_token() }}";

                if (feedbackMsg) {
                    feedbackMsg.innerHTML = '<span class="text-primary d-inline-flex align-items-center gap-1"><span class="spinner-border spinner-border-sm" style="width: 12px; height: 12px; border-width: 2px;" role="status"></span><span>Validating code...</span></span>';
                }

                axios.post(ajaxUrl, {
                    email: emailVal,
                    otp: code
                }, {
                    headers: {
                        'X-CSRF-TOKEN': token
                    },
                    signal: activeAjaxController.signal
                })
                .then(response => {
                    const data = response.data;
                    if (data.valid) {
                        otpCells.forEach(c => {
                            c.classList.remove('is-invalid');
                            c.classList.add('is-valid');
                        });
                        if (otpBoxContainer) {
                            otpBoxContainer.classList.remove('shake');
                        }
                        if (feedbackMsg) {
                            feedbackMsg.innerHTML = '<span class="text-success d-inline-flex align-items-center gap-1"><i class="fa-solid fa-circle-check"></i> <span>' + (data.message || 'Code verified successfully!') + '</span></span>';
                        }
                    } else {
                        otpCells.forEach(c => {
                            c.classList.remove('is-valid');
                            c.classList.add('is-invalid');
                        });
                        if (otpBoxContainer) {
                            otpBoxContainer.classList.remove('shake');
                            void otpBoxContainer.offsetWidth;
                            otpBoxContainer.classList.add('shake');
                            setTimeout(() => {
                                otpBoxContainer.classList.remove('shake');
                            }, 300);
                        }
                        if (feedbackMsg) {
                            feedbackMsg.innerHTML = '<span class="text-danger d-inline-flex align-items-center gap-1"><i class="fa-solid fa-circle-xmark"></i> <span>' + (data.message || 'The verification code entered is invalid.') + '</span></span>';
                        }
                    }
                })
                .catch(err => {
                    if (err.name === 'AbortError') return;
                    otpCells.forEach(c => {
                        c.classList.remove('is-valid');
                        c.classList.add('is-invalid');
                    });
                    if (feedbackMsg) {
                        feedbackMsg.innerHTML = '<span class="text-danger d-inline-flex align-items-center gap-1"><i class="fa-solid fa-circle-xmark"></i> <span>Verification error. Please try again.</span></span>';
                    }
                });
            }

            function syncHiddenOtp() {
                let code = '';
                otpCells.forEach(cell => {
                    code += cell.value.trim();
                    if (cell.value.trim() !== '') {
                        cell.classList.add('filled');
                    } else {
                        cell.classList.remove('filled');
                    }
                });
                if (hiddenOtp) {
                    hiddenOtp.value = code;
                }
                if (code.length === 6) {
                    triggerOtpValidation(code);
                } else {
                    clearOtpFeedback();
                }
            }

            const urlPrefilledOtp = "{{ $otp ?? '' }}";
            if (urlPrefilledOtp && otpCells.length) {
                urlPrefilledOtp.replace(/[^0-9]/g, '').slice(0, 6).split('').forEach((d, i) => {
                    if (otpCells[i]) otpCells[i].value = d;
                });
                syncHiddenOtp();
            }

            otpCells.forEach((cell, idx) => {
                cell.addEventListener('input', function (e) {
                    clearOtpFeedback();
                    const val = this.value.replace(/[^0-9]/g, '');
                    this.value = val ? val[val.length - 1] : '';
                    syncHiddenOtp();

                    if (this.value && idx < otpCells.length - 1) {
                        otpCells[idx + 1].focus();
                        otpCells[idx + 1].select();
                    }
                });

                cell.addEventListener('keydown', function (e) {
                    if (e.key === 'Backspace') {
                        clearOtpFeedback();
                        if (!this.value && idx > 0) {
                            otpCells[idx - 1].focus();
                            otpCells[idx - 1].value = '';
                            syncHiddenOtp();
                        }
                    } else if (e.key === 'ArrowLeft' && idx > 0) {
                        otpCells[idx - 1].focus();
                    } else if (e.key === 'ArrowRight' && idx < otpCells.length - 1) {
                        otpCells[idx + 1].focus();
                    }
                });

                cell.addEventListener('paste', function (e) {
                    e.preventDefault();
                    clearOtpFeedback();
                    const clipData = (e.clipboardData || window.clipboardData).getData('text').trim();
                    const digits = clipData.replace(/[^0-9]/g, '').slice(0, 6);
                    if (!digits) return;

                    digits.split('').forEach((d, i) => {
                        if (otpCells[i]) {
                            otpCells[i].value = d;
                        }
                    });

                    syncHiddenOtp();

                    const nextTarget = Math.min(digits.length, otpCells.length - 1);
                    otpCells[nextTarget].focus();
                });

                cell.addEventListener('focus', function () {
                    this.select();
                });
            });

            const sendOtpForm = document.getElementById('sendOtpForm');
            if (sendOtpForm) {
                sendOtpForm.addEventListener('submit', function () {
                    const btn = document.getElementById('submitSendOtpBtn');
                    if (btn) {
                        btn.disabled = true;
                        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status"></span><span>Dispatching Code...</span>';
                    }
                });
            }

            const resetPasswordForm = document.getElementById('resetPasswordForm');
            if (resetPasswordForm) {
                resetPasswordForm.addEventListener('submit', function (e) {
                    syncHiddenOtp();
                    const btn = document.getElementById('submitResetBtn');
                    if (btn) {
                        btn.disabled = true;
                        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status"></span><span>Saving New Password...</span>';
                    }
                });
            }
        });
    </script>
</body>
</html>