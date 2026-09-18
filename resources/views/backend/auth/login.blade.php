<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Sign In | {{ $storeName ?? 'Zippy' }} Control Suite</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('backend/lib/all.min.css') }}">
    <link rel="stylesheet" href="{{ asset('backend/lib/bootstrap.min.css') }}">

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

        .form-header {
            margin-bottom: 1.5rem;
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

        .demo-account-box {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0.65rem 1rem;
            background: rgba(239, 246, 255, 0.8);
            border: 1px solid #bfdbfe;
            border-radius: 10px;
            margin-bottom: 1.5rem;
            cursor: pointer;
            transition: background 0.15s ease;
        }

        .demo-account-box:hover {
            background: #e0edff;
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

        .auth-divider {
            display: flex;
            align-items: center;
            text-align: center;
            margin: 1.25rem 0;
            color: #94a3b8;
            font-size: 0.7rem;
            font-weight: 700;
            letter-spacing: 0.05em;
        }

        .auth-divider::before,
        .auth-divider::after {
            content: '';
            flex: 1;
            border-bottom: 1px solid #e2e8f0;
        }

        .auth-divider span {
            padding: 0 10px;
        }

        .btn-google-action {
            width: 100%;
            height: 44px;
            border-radius: 10px;
            background: rgba(255, 255, 255, 0.85);
            border: 1px solid #cbd5e1;
            color: #0f172a;
            font-size: 0.84rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            text-decoration: none;
            transition: background 0.15s, border-color 0.15s;
        }

        .btn-google-action:hover {
            background: #ffffff;
            border-color: #2563eb;
            color: #0f172a;
        }

        .bottom-return-link {
            text-align: center;
            margin-top: 1.25rem;
        }

        .bottom-return-link a {
            text-decoration: none;
            color: #64748b;
            font-size: 0.8125rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .bottom-return-link a:hover {
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
                        <span>Solve IT &bull; Operations & Fulfillment Suite</span>
                    </div>
                </div>

                <h3 class="showcase-heading">Enterprise Control Hub</h3>
                <p class="showcase-subtext">Centralized multi-channel order tracking, courier fulfillment integration, and real-time fraud assessment.</p>

                <div class="feature-list">
                    <div class="feature-card">
                        <div class="feature-icon-box">
                            <i class="fa-solid fa-truck-fast"></i>
                        </div>
                        <div>
                            <div class="feature-title">Logistics & Courier Sync</div>
                            <div class="feature-desc">Automated consignment dispatch and parcel verification</div>
                        </div>
                    </div>

                    <div class="feature-card">
                        <div class="feature-icon-box">
                            <i class="fa-solid fa-shield-halved"></i>
                        </div>
                        <div>
                            <div class="feature-title">Risk & Fraud Engine</div>
                            <div class="feature-desc">Courier return ratio and delivery success scoring</div>
                        </div>
                    </div>

                    <div class="feature-card">
                        <div class="feature-icon-box">
                            <i class="fa-solid fa-headset"></i>
                        </div>
                        <div>
                            <div class="feature-title">Tele-Caller Queue</div>
                            <div class="feature-desc">Instant customer call confirmation workflow</div>
                        </div>
                    </div>
                </div>
            </div>

            <div>
                <div class="live-status-pill">
                    <span class="pulse-dot"></span>
                    <span>All Systems Operational &bull; 99.98% Uptime</span>
                </div>
            </div>
        </div>

        <div class="auth-form-panel">
            <div class="form-header">
                <h3>Welcome Back</h3>
                <p>Enter your credentials to access the operations desk.</p>
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

            <form method="POST" action="{{ route('admin.login.post') }}" id="adminLoginForm" novalidate>
                @csrf

                <div class="input-block">
                    <div class="input-label-row">
                        <label class="field-label" for="adminEmailInput">Email / Username</label>
                    </div>
                    <div class="input-field-wrap">
                        <span class="input-prefix-icon"><i class="fa-regular fa-envelope"></i></span>
                        <input type="text" name="email" id="adminEmailInput" class="input-element" placeholder="admin@example.com" value="{{ old('email') }}" required autocomplete="username" autofocus>
                    </div>
                </div>

                <div class="input-block">
                    <div class="input-label-row">
                        <label class="field-label" for="adminPasswordInput">Password</label>
                        <a href="{{ route('admin.forgot_password') }}" class="text-decoration-none text-muted" style="font-size: 0.74rem; font-weight: 600;">Forgot?</a>
                    </div>
                    <div class="input-field-wrap">
                        <span class="input-prefix-icon"><i class="fa-solid fa-lock"></i></span>
                        <input type="password" name="password" id="adminPasswordInput" class="input-element" placeholder="••••••••" value="" required autocomplete="current-password">
                        <button type="button" class="btn-eye-toggle" onclick="togglePasswordVisibility()" aria-label="Toggle password visibility">
                            <i class="fa-regular fa-eye" id="passwordEyeIcon"></i>
                        </button>
                    </div>
                </div>

                <div class="d-flex align-items-center justify-content-between mb-3 mt-1">
                    <div class="form-check mb-0">
                        <input class="form-check-input" type="checkbox" name="remember" id="rememberSession" checked style="cursor: pointer;">
                        <label class="form-check-label text-muted user-select-none" for="rememberSession" style="cursor: pointer; font-size: 0.8rem;">
                            Keep me signed in
                        </label>
                    </div>
                </div>

                <button type="submit" class="btn-primary-submit" id="submitLoginBtn">
                    <span>Sign In to Desk</span>
                    <i class="fa-solid fa-arrow-right fa-xs"></i>
                </button>
            </form>

            @if(!empty($socialLoginSetting) && ($socialLoginSetting->is_google_active ?? 1))
                <div class="auth-divider">
                    <span>OR CONTINUE WITH</span>
                </div>

                <a href="{{ route('google.redirect', ['intent' => 'admin']) }}" class="btn-google-action">
                    <svg width="17" height="17" viewBox="0 0 48 48">
                        <path fill="#EA4335" d="M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 13.72 17.74 9.5 24 9.5z"/>
                        <path fill="#4285F4" d="M46.98 24.55c0-1.57-.15-3.09-.38-4.55H24v9.02h12.94c-.58 2.96-2.26 5.48-4.78 7.18l7.73 6c4.51-4.18 7.09-10.36 7.09-17.65z"/>
                        <path fill="#FBBC05" d="M10.53 28.59c-.48-1.45-.76-2.99-.76-4.59s.27-3.14.76-4.59l-7.98-6.19C.92 16.46 0 20.12 0 24c0 3.88.92 7.54 2.56 10.78l7.97-6.19z"/>
                        <path fill="#34A853" d="M24 48c6.48 0 11.93-2.13 15.89-5.81l-7.73-6c-2.15 1.45-4.92 2.3-8.16 2.3-6.26 0-11.57-4.22-13.47-9.91l-7.98 6.19C6.51 42.62 14.62 48 24 48z"/>
                    </svg>
                    <span>Google Workspace Sign In</span>
                </a>
            @endif

            <div class="bottom-return-link">
                <a href="{{ route('home') }}">
                    <i class="fa-solid fa-arrow-left fa-xs"></i>
                    <span>Back to Storefront</span>
                </a>
            </div>
        </div>
    </div>

    <script>
        function togglePasswordVisibility() {
            const passInput = document.getElementById('adminPasswordInput');
            const eyeIcon = document.getElementById('passwordEyeIcon');
            if (!passInput || !eyeIcon) return;

            if (passInput.type === 'password') {
                passInput.type = 'text';
                eyeIcon.className = 'fa-regular fa-eye-slash';
            } else {
                passInput.type = 'password';
                eyeIcon.className = 'fa-regular fa-eye';
            }
        }

        document.addEventListener('DOMContentLoaded', function () {
            const form = document.getElementById('adminLoginForm');
            if (form) {
                form.addEventListener('submit', function () {
                    const btn = document.getElementById('submitLoginBtn');
                    if (btn) {
                        btn.disabled = true;
                        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status"></span><span>Authenticating...</span>';
                    }
                });
            }
        });
    </script>
</body>
</html>