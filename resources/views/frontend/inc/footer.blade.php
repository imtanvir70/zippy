<footer id="footer">
    @php
        $footerBrand = $settings['store_name'] ?? 'ZippyBD';
        $footerInitial = strtoupper(substr($footerBrand, 0, 1));
        $footerTagline = $settings['store_tagline'] ?? 'বাংলাদেশের বিশ্বস্ত অনলাইন শপ। মেকানিক্যাল কিবোর্ড, ডেস্ক সেটআপ এক্সেসরিজ, অডিও ডিভাইস ও প্রিমিয়াম গ্যাজেট কিনুন সবচেয়ে সাশ্রয়ী মূল্যে।';
        $footerPhone = $settings['store_phone'] ?? '01700-000000';
        $footerEmail = $settings['store_email'] ?? 'support@zippybd.com';
        $footerWa = preg_replace('/[^0-9]/', '', $settings['store_whatsapp'] ?? '01700000000');
        if (strlen($footerWa) === 11 && str_starts_with($footerWa, '01')) {
            $footerWa = '88' . $footerWa;
        }
    @endphp
    
    <!-- ==========================================
         1. DESKTOP PREMIUM FOOTER (d-none d-lg-block)
    ========================================== -->
    <div class="footer-premium d-none d-lg-block">
        <div class="container pt-5 pb-4">
            <div class="row g-4 mb-5 align-items-start">
                
                <!-- Col 1: Brand -->
                <div class="col-lg-4 text-start">
                    <div class="d-flex align-items-center gap-3 footer-header-box">
                        <div class="brand-logo-box">{{ $footerInitial }}</div>
                        <h4 class="fw-bold text-white m-0 font-heading fs-4">{{ $footerBrand }}</h4>
                    </div>
                    <p class="text-slate-400 mb-4 pe-lg-4" style="font-size: 14.5px; line-height: 1.8;">
                        {{ $footerTagline }}
                    </p>
                    <div class="d-flex align-items-center gap-3">
                        <a href="https://facebook.com" target="_blank" rel="noopener noreferrer" class="social-btn" aria-label="Facebook"><i class="fa-brands fa-facebook-f"></i></a>
                        <a href="https://instagram.com" target="_blank" rel="noopener noreferrer" class="social-btn" aria-label="Instagram"><i class="fa-brands fa-instagram"></i></a>
                        <a href="https://youtube.com" target="_blank" rel="noopener noreferrer" class="social-btn" aria-label="YouTube"><i class="fa-brands fa-youtube"></i></a>
                        <a href="https://wa.me/{{ $footerWa }}" target="_blank" rel="noopener noreferrer" class="social-btn whatsapp" aria-label="WhatsApp"><i class="fa-brands fa-whatsapp"></i></a>
                    </div>
                </div>

                <!-- Col 2: Quick Links -->
                <div class="col-lg-2 text-start">
                    <div class="footer-header-box">
                        <h6 class="footer-title m-0">কুইক লিংকস</h6>
                    </div>
                    <ul class="footer-links">
                        <li><a href="{{ route('home') }}">হোম</a></li>
                        <li><a href="{{ route('product.index') }}">প্রোডাক্টস</a></li>
                        <li><a href="{{ route('product.best_sale') }}">বেস্ট সেল</a></li>
                        <li><a href="{{ route('product.new_collection') }}">নতুন কালেকশন</a></li>
                        <li><a href="{{ route('product.flash_deals') }}">ফ্ল্যাশ ডিলস</a></li>
                    </ul>
                </div>

                <!-- Col 3: Customer Service -->
                <div class="col-lg-3 text-start">
                    <div class="footer-header-box">
                        <h6 class="footer-title m-0">কাস্টমার সার্ভিস</h6>
                    </div>
                    <ul class="footer-links">
                        <li><a href="{{ route('order.track') }}">অর্ডার ট্র্যাকিং</a></li>
                        <li><a href="{{ route('order.history') }}">অর্ডার হিস্ট্রি</a></li>
                        <li><a href="{{ route('customer.account') }}">আমার একাউন্ট</a></li>
                        <li><a href="{{ url('/page/return-refund') }}">রিটার্ন ও রিফান্ড পলিসি</a></li>
                        <li><a href="{{ url('/page/privacy') }}">প্রাইভেসি পলিসি</a></li>
                    </ul>
                </div>

                <!-- Col 4: Contact & Support -->
                <div class="col-lg-3 text-start">
                    <div class="footer-header-box">
                        <h6 class="footer-title m-0">যোগাযোগ ও সাপোর্ট</h6>
                    </div>
                    <div class="d-flex flex-column gap-3">
                        <a href="tel:{{ $footerPhone }}" class="support-card">
                            <div class="support-icon bg-warning-subtle text-warning">
                                <i class="fa-solid fa-phone"></i>
                            </div>
                            <div class="support-text">
                                <span class="label">হটলাইন</span>
                                <span class="value">{{ $footerPhone }}</span>
                            </div>
                        </a>
                        <a href="mailto:{{ $footerEmail }}" class="support-card">
                            <div class="support-icon bg-info-subtle text-info">
                                <i class="fa-solid fa-envelope"></i>
                            </div>
                            <div class="support-text">
                                <span class="label">ইমেইল সাপোর্ট</span>
                                <span class="value">{{ $footerEmail }}</span>
                            </div>
                        </a>
                    </div>
                </div>
                
            </div>

            <div class="footer-bottom">
                <div class="row align-items-center">
                    <div class="col-md-6 text-start">
                        <p class="m-0 text-slate-400" style="font-size: 13.5px;">&copy; {{ date('Y') }} {{ $footerBrand }}. সর্বস্বত্ব সংরক্ষিত।</p>
                    </div>
                    <div class="col-md-6 d-flex align-items-center justify-content-end gap-3">
                        <div class="trust-badge">
                            <i class="fa-solid fa-shield-check text-success"></i>
                            <span>সিকিউর পেমেন্ট</span>
                        </div>
                        <div class="trust-badge">
                            <i class="fa-solid fa-hand-holding-dollar text-warning"></i>
                            <span>ক্যাশ অন ডেলিভারি</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ==========================================
         2. MOBILE NATIVE APP FOOTER (d-lg-none)
    ========================================== -->
    <div class="footer-app-native d-lg-none pb-mobile-safe pt-4 pb-4 px-3 {{ request()->routeIs('login', 'register') ? 'd-none' : '' }}">
        
        <h6 class="px-2 mb-2 font-heading fw-bold" style="font-size: 13.5px; color: #64748b;">হেল্প ও সাপোর্ট</h6>
        
        <!-- App Style List Group -->
        <div class="app-list-group bg-white rounded-4 shadow-sm border mb-4">
            
            <a href="tel:{{ $footerPhone }}" class="app-list-item d-flex align-items-center justify-content-between p-3 border-bottom text-decoration-none">
                <div class="d-flex align-items-center gap-3">
                    <div class="app-list-icon bg-warning-subtle text-warning"><i class="fa-solid fa-phone"></i></div>
                    <div class="d-flex flex-column">
                        <span class="fw-bold text-dark" style="font-size: 14.5px;">হটলাইন সাপোর্ট</span>
                        <span class="text-secondary" style="font-size: 12px;">{{ $footerPhone }}</span>
                    </div>
                </div>
                <i class="fa-solid fa-chevron-right text-secondary small"></i>
            </a>

            <a href="mailto:{{ $footerEmail }}" class="app-list-item d-flex align-items-center justify-content-between p-3 border-bottom text-decoration-none">
                <div class="d-flex align-items-center gap-3">
                    <div class="app-list-icon bg-info-subtle text-info"><i class="fa-solid fa-envelope"></i></div>
                    <div class="d-flex flex-column">
                        <span class="fw-bold text-dark" style="font-size: 14.5px;">ইমেইল সাপোর্ট</span>
                        <span class="text-secondary" style="font-size: 12px;">{{ $footerEmail }}</span>
                    </div>
                </div>
                <i class="fa-solid fa-chevron-right text-secondary small"></i>
            </a>

            <a href="{{ route('order.track') }}" class="app-list-item d-flex align-items-center justify-content-between p-3 border-bottom text-decoration-none">
                <div class="d-flex align-items-center gap-3">
                    <div class="app-list-icon bg-light text-dark"><i class="fa-solid fa-truck-fast"></i></div>
                    <span class="fw-bold text-dark" style="font-size: 14.5px;">অর্ডার ট্র্যাকিং</span>
                </div>
                <i class="fa-solid fa-chevron-right text-secondary small"></i>
            </a>

            <a href="{{ url('/page/privacy') }}" class="app-list-item d-flex align-items-center justify-content-between p-3 text-decoration-none">
                <div class="d-flex align-items-center gap-3">
                    <div class="app-list-icon bg-light text-dark"><i class="fa-solid fa-shield-halved"></i></div>
                    <span class="fw-bold text-dark" style="font-size: 14.5px;">পলিসি ও শর্তাবলী</span>
                </div>
                <i class="fa-solid fa-chevron-right text-secondary small"></i>
            </a>

        </div>

        <!-- App Style Socials -->
        <div class="d-flex align-items-center justify-content-center gap-3 mb-4">
            <a href="https://facebook.com" class="app-social-btn text-dark bg-white shadow-sm border" aria-label="Facebook" rel="noopener noreferrer"><i class="fa-brands fa-facebook-f"></i></a>
            <a href="https://instagram.com" class="app-social-btn text-dark bg-white shadow-sm border" aria-label="Instagram" rel="noopener noreferrer"><i class="fa-brands fa-instagram"></i></a>
            <a href="https://youtube.com" class="app-social-btn text-dark bg-white shadow-sm border" aria-label="YouTube" rel="noopener noreferrer"><i class="fa-brands fa-youtube"></i></a>
            <a href="https://wa.me/{{ $footerWa }}" class="app-social-btn text-success bg-white shadow-sm border" aria-label="WhatsApp" rel="noopener noreferrer"><i class="fa-brands fa-whatsapp"></i></a>
        </div>

        <!-- App Version / Watermark -->
        <div class="text-center pb-2">
            <div class="brand-logo-box mx-auto mb-2 shadow-sm" style="width: 32px; height: 32px; font-size: 16px;">{{ $footerInitial }}</div>
            <span class="d-block fw-bold text-dark font-heading" style="font-size: 15px;">{{ $footerBrand }}</span>
            <span class="d-block text-secondary mt-1" style="font-size: 11.5px;">App Version 1.0.0 &bull; Secure Checkout</span>
            <span class="d-block text-secondary mt-1" style="font-size: 10px;">&copy; {{ date('Y') }} All Rights Reserved</span>
        </div>

    </div>
</footer>

<style>
/* ================= DESKTOP PREMIUM CSS ================= */
.footer-premium {
    background-color: #090d16;
    color: #f8fafc;
    border-top: 1px solid rgba(255, 255, 255, 0.05);
    overflow-x: clip;
}

.text-slate-400 { color: #94a3b8 !important; }

/* Desktop header height fixed to perfectly align all columns horizontally */
.footer-header-box {
    height: 42px;
    display: flex;
    align-items: center;
    margin-bottom: 24px;
}

.brand-logo-box {
    background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
    color: #ffffff;
    width: 42px;
    height: 42px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 800;
    font-size: 20px;
    box-shadow: 0 4px 15px rgba(37, 99, 235, 0.35);
}

.social-btn {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background-color: rgba(255, 255, 255, 0.04);
    color: #cbd5e1;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
    text-decoration: none;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    border: 1px solid rgba(255, 255, 255, 0.06);
}

.social-btn:hover {
    background-color: #ffffff;
    color: #090d16;
    transform: translateY(-4px);
    box-shadow: 0 8px 20px rgba(255, 255, 255, 0.15);
}

.social-btn.whatsapp:hover {
    background-color: #22c55e;
    color: #ffffff;
    border-color: #22c55e;
    box-shadow: 0 8px 20px rgba(34, 197, 94, 0.25);
}

.footer-title {
    font-size: 16px;
    font-weight: 700;
    color: #ffffff;
}

.footer-links {
    list-style: none;
    padding: 0;
    margin: 0;
    display: flex;
    gap: 14px;
    flex-direction: column;
}

.footer-links a {
    color: #94a3b8;
    text-decoration: none;
    font-size: 14.5px;
    display: inline-flex;
    align-items: center;
    transition: all 0.25s ease;
}

.footer-links a::before {
    content: '\f105';
    font-family: 'Font Awesome 6 Free';
    font-weight: 900;
    font-size: 11px;
    margin-right: 8px;
    color: #3b82f6;
    opacity: 0;
    transform: translateX(-8px);
    transition: all 0.25s ease;
}

.footer-links a:hover {
    color: #ffffff;
    transform: translateX(6px);
}

.footer-links a:hover::before {
    opacity: 1;
    transform: translateX(0);
}

.support-card {
    display: flex;
    align-items: center;
    gap: 16px;
    background: rgba(255, 255, 255, 0.03);
    border: 1px solid rgba(255, 255, 255, 0.06);
    padding: 14px 18px;
    border-radius: 14px;
    text-decoration: none;
    transition: all 0.3s ease;
}

.support-card:hover {
    background: rgba(255, 255, 255, 0.06);
    border-color: rgba(255, 255, 255, 0.12);
    transform: translateY(-2px);
}

.support-icon {
    width: 38px;
    height: 38px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
}

.support-text {
    display: flex;
    flex-direction: column;
}

.support-text .label {
    font-size: 12px;
    color: #94a3b8;
    margin-bottom: 2px;
}

.support-text .value {
    font-size: 15px;
    font-weight: 700;
    color: #ffffff;
}

.footer-bottom {
    padding-top: 24px;
    border-top: 1px solid rgba(255, 255, 255, 0.06);
}

.trust-badge {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: rgba(255, 255, 255, 0.04);
    padding: 8px 16px;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 600;
    color: #e2e8f0;
    border: 1px solid rgba(255, 255, 255, 0.05);
}

/* ================= MOBILE NATIVE APP CSS ================= */
@media (max-width: 991.98px) {
    .footer-app-native {
        background-color: #f1f5f9; /* Same as app background */
    }

    .app-list-group {
        overflow: hidden;
    }

    .app-list-item {
        background-color: #ffffff;
        transition: background-color 0.2s;
    }

    .app-list-item:active {
        background-color: #f8fafc;
    }

    .app-list-icon {
        width: 36px;
        height: 36px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 15px;
    }

    .app-social-btn {
        width: 44px;
        height: 44px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
        text-decoration: none;
        transition: transform 0.2s;
    }
    
    .app-social-btn:active {
        transform: scale(0.92);
    }
}

/* iOS Safe Area Padding */
@supports (padding-bottom: env(safe-area-inset-bottom)) {
    .pb-mobile-safe {
        padding-bottom: calc(110px + env(safe-area-inset-bottom)) !important;
    }
}
</style>