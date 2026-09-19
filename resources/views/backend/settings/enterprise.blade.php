@php
    $tabTitles = [
        'couriers' => ['title' => 'BD Courier Integrations API', 'sub' => 'Configure Steadfast, Pathao, and RedX logistics credentials', 'icon' => 'fa-truck-fast'],
        'payments' => ['title' => 'Payment Gateway Credentials', 'sub' => 'Configure bKash Merchant and SSLCommerz API keys', 'icon' => 'fa-credit-card'],
        'fraud' => ['title' => 'Fraud Detection Engine API', 'sub' => 'Live courier fraud score checking and automated risk rules', 'icon' => 'fa-shield-halved'],
        'gtm' => ['title' => 'Google Tag Manager & Analytics', 'sub' => 'Manage GTM container ID and eCommerce tracking snippet', 'icon' => 'fa-brands fa-google'],
        'smtp' => ['title' => 'SMTP Mail Configuration', 'sub' => 'Configure outgoing email server, credentials, and live delivery testing', 'icon' => 'fa-envelope'],
        'backup' => ['title' => 'System Database Backup', 'sub' => 'Instant manual database snapshot and audit records', 'icon' => 'fa-database'],
    ];
    $curr = $tabTitles[$activeTab ?? 'couriers'] ?? $tabTitles['couriers'];
@endphp

@extends('backend.layouts.app')

@section('title', $curr['title'] ?? 'Settings')

@section('content')
<form id="enterpriseSettingsForm" data-autosave="true" action="{{ route('admin.settings.enterprise.update') }}" method="POST">
    @csrf
    <input type="hidden" name="_active_tab" value="{{ $activeTab ?? 'couriers' }}">

    <div class="card p-3 p-md-4 mb-4">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div class="d-flex align-items-center gap-3">
                <div class="stat-icon" style="width: 48px; height: 48px; border-radius: 14px;">
                    <i class="fa-solid {{ $curr['icon'] }}"></i>
                </div>
                <div>
                    <h3 class="fw-bold mb-1">{{ $curr['title'] }}</h3>
                    <small class="text-muted">{{ $curr['sub'] }}</small>
                </div>
            </div>
            <div class="d-flex align-items-center gap-2">
                @if(($activeTab ?? '') === 'backup')
                    @canPerm('admin.settings.backup')
                        <button type="button" class="btn btn-outline-danger btn-sm px-3 py-2 fw-semibold rounded-3" onclick="triggerSnapshotBackup()">
                            <i class="fa-solid fa-database me-1.5"></i> Trigger DB Snapshot
                        </button>
                    @endcanPerm
                @else
                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-1.5 small fw-semibold">
                        <i class="fa-solid fa-bolt me-1 text-success"></i> Auto-Save Active
                    </span>
                    @canPerm('admin.settings.enterprise.update')
                        <button type="submit" class="btn btn-primary btn-sm px-3.5 py-2 fw-semibold rounded-3">
                            <i class="fa-solid fa-floppy-disk me-1.5"></i> Save Configurations
                        </button>
                    @endcanPerm
                @endif
            </div>
        </div>
    </div>

    <div class="row g-4">
        @if(($activeTab ?? 'couriers') === 'couriers')
        <div class="col-lg-7">
            <div class="card p-3 p-md-4 h-100">
                <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                    <h6 class="fw-bold mb-0 text-primary">
                        <i class="fa-solid fa-truck-fast me-2"></i> Courier Providers Integration
                    </h6>
                    <span class="badge bg-primary-subtle text-primary rounded-pill px-2.5 py-1 small">Steadfast • Pathao • RedX</span>
                </div>
                <div class="d-flex flex-column gap-3">
                    <div>
                        <label class="form-label small fw-bold">Default Courier Provider</label>
                        <select name="courier_default" class="form-select">
                            <option value="steadfast" {{ ($settings['courier_default'] ?? '') === 'steadfast' ? 'selected' : '' }}>Steadfast Courier</option>
                            <option value="pathao" {{ ($settings['courier_default'] ?? '') === 'pathao' ? 'selected' : '' }}>Pathao Courier</option>
                            <option value="redx" {{ ($settings['courier_default'] ?? '') === 'redx' ? 'selected' : '' }}>RedX Logistics</option>
                        </select>
                    </div>
                    <div>
                        <label class="form-label small fw-bold">Steadfast API Key</label>
                        <div class="input-group">
                            <input type="password" name="steadfast_api_key" id="steadfast_api_key" class="form-control font-monospace" value="{{ $settings['steadfast_api_key'] ?? '' }}" placeholder="Enter Steadfast API Key">
                            <button type="button" class="btn btn-outline-secondary toggle-password-btn" data-target="steadfast_api_key" title="Toggle visibility">
                                <i class="fa-solid fa-eye"></i>
                            </button>
                        </div>
                    </div>
                    <div>
                        <label class="form-label small fw-bold">Steadfast Secret Key</label>
                        <div class="input-group">
                            <input type="password" name="steadfast_secret_key" id="steadfast_secret_key" class="form-control font-monospace" value="{{ $settings['steadfast_secret_key'] ?? '' }}" placeholder="Enter Steadfast Secret Key">
                            <button type="button" class="btn btn-outline-secondary toggle-password-btn" data-target="steadfast_secret_key" title="Toggle visibility">
                                <i class="fa-solid fa-eye"></i>
                            </button>
                        </div>
                    </div>
                    <div>
                        <label class="form-label small fw-bold">Pathao Client ID</label>
                        <input type="text" name="pathao_client_id" class="form-control" value="{{ $settings['pathao_client_id'] ?? '' }}" placeholder="Pathao Client ID">
                    </div>
                    <div>
                        <label class="form-label small fw-bold">RedX API Token</label>
                        <div class="input-group">
                            <input type="password" name="redx_api_token" id="redx_api_token" class="form-control font-monospace" value="{{ $settings['redx_api_token'] ?? '' }}" placeholder="Enter RedX Token">
                            <button type="button" class="btn btn-outline-secondary toggle-password-btn" data-target="redx_api_token" title="Toggle visibility">
                                <i class="fa-solid fa-eye"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="card p-3 p-md-4 h-100">
                <h5 class="fw-bold mb-3 d-flex align-items-center gap-2">
                    <i class="fa-solid fa-circle-question" style="color: var(--accent);"></i>
                    <span>কুরিয়ার সেটআপ নির্দেশনা</span>
                </h5>
                <p class="text-muted small mb-3">
                    অর্ডার এক ক্লিকে সরাসরি কুরিয়ারে বুকিং এবং লাইভ ট্র্যাকিং চালু করতে নিচের নিয়মগুলো মেনে ক্রেডেনশিয়াল বসান:
                </p>
                <div class="d-flex flex-column gap-3">
                    <div class="p-3 rounded-3" style="background: var(--surface); border: 1px solid var(--border-color);">
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <span class="badge bg-primary rounded-circle p-1 d-inline-flex align-items-center justify-content-center" style="width: 22px; height: 22px; font-size: 11px;">১</span>
                            <h6 class="fw-bold mb-0 small">Steadfast কুরিয়ার সেটআপ</h6>
                        </div>
                        <small class="text-muted d-block">
                            Steadfast মার্চেন্ট প্যানেল থেকে <strong>API Settings</strong> অপশনে গিয়ে আপনার <code>API Key</code> ও <code>Secret Key</code> কপি করে এখানে পেস্ট করুন।
                        </small>
                    </div>
                    <div class="p-3 rounded-3" style="background: var(--surface); border: 1px solid var(--border-color);">
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <span class="badge bg-primary rounded-circle p-1 d-inline-flex align-items-center justify-content-center" style="width: 22px; height: 22px; font-size: 11px;">২</span>
                            <h6 class="fw-bold mb-0 small">Pathao Logistics সংযোগ</h6>
                        </div>
                        <small class="text-muted d-block">
                            Pathao Merchant Developer পোর্টাল থেকে প্রাপ্ত <code>Client ID</code> বসিয়ে সরাসরি অর্ডার পুশ সুবিধা সক্রিয় করুন।
                        </small>
                    </div>
                    <div class="p-3 rounded-3" style="background: var(--surface); border: 1px solid var(--border-color);">
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <span class="badge bg-primary rounded-circle p-1 d-inline-flex align-items-center justify-content-center" style="width: 22px; height: 22px; font-size: 11px;">৩</span>
                            <h6 class="fw-bold mb-0 small">RedX API Token</h6>
                        </div>
                        <small class="text-muted d-block">
                            RedX মার্চেন্ট অ্যাকাউন্ট থেকে জেনারেট করা Bearer <code>API Token</code> টি সাবমিট করুন।
                        </small>
                    </div>
                    <div class="alert alert-info small m-0 p-2.5">
                        <i class="fa-solid fa-circle-info me-1"></i> অর্ডার পেজ থেকে "Send to Courier" চাপলে নির্বাচিত ডিফল্ট কুরিয়ারের মাধ্যমে বুকিং হবে।
                    </div>
                </div>
            </div>
        </div>
        @endif

        @if(($activeTab ?? '') === 'payments')
        <div class="col-lg-7">
            <div class="card p-3 p-md-4 h-100">
                <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                    <h6 class="fw-bold mb-0 text-warning">
                        <i class="fa-solid fa-credit-card me-2"></i> MFS & Card Gateways
                    </h6>
                    <span class="badge bg-warning-subtle text-warning rounded-pill px-2.5 py-1 small">bKash & SSLCommerz</span>
                </div>
                <div class="d-flex flex-column gap-3">
                    <div>
                        <label class="form-label small fw-bold">bKash Merchant App Key</label>
                        <div class="input-group">
                            <input type="password" name="bkash_app_key" id="bkash_app_key" class="form-control font-monospace" value="{{ $settings['bkash_app_key'] ?? '' }}" placeholder="bKash App Key">
                            <button type="button" class="btn btn-outline-secondary toggle-password-btn" data-target="bkash_app_key" title="Toggle visibility">
                                <i class="fa-solid fa-eye"></i>
                            </button>
                        </div>
                    </div>
                    <div>
                        <label class="form-label small fw-bold">bKash App Secret</label>
                        <div class="input-group">
                            <input type="password" name="bkash_app_secret" id="bkash_app_secret" class="form-control font-monospace" value="{{ $settings['bkash_app_secret'] ?? '' }}" placeholder="bKash App Secret">
                            <button type="button" class="btn btn-outline-secondary toggle-password-btn" data-target="bkash_app_secret" title="Toggle visibility">
                                <i class="fa-solid fa-eye"></i>
                            </button>
                        </div>
                    </div>
                    <div>
                        <label class="form-label small fw-bold">SSLCommerz Store ID</label>
                        <input type="text" name="ssl_store_id" class="form-control" value="{{ $settings['ssl_store_id'] ?? '' }}" placeholder="SSLCommerz Store ID">
                    </div>
                    <div>
                        <label class="form-label small fw-bold">SSLCommerz Store Password</label>
                        <div class="input-group">
                            <input type="password" name="ssl_store_password" id="ssl_store_password" class="form-control font-monospace" value="{{ $settings['ssl_store_password'] ?? '' }}" placeholder="SSLCommerz Store Password">
                            <button type="button" class="btn btn-outline-secondary toggle-password-btn" data-target="ssl_store_password" title="Toggle visibility">
                                <i class="fa-solid fa-eye"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="card p-3 p-md-4 h-100">
                <h5 class="fw-bold mb-3 d-flex align-items-center gap-2">
                    <i class="fa-solid fa-circle-question" style="color: var(--accent);"></i>
                    <span>পেমেন্ট গেটওয়ে নির্দেশিকা</span>
                </h5>
                <p class="text-muted small mb-3">
                    অনলাইন পেমেন্ট গেটওয়েগুলো সুরক্ষিতভাবে কনফিগার করতে নিচের তথ্যগুলো সংগ্রহ করুন:
                </p>
                <div class="d-flex flex-column gap-3">
                    <div class="p-3 rounded-3" style="background: var(--surface); border: 1px solid var(--border-color);">
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <span class="badge bg-warning text-dark rounded-circle p-1 d-inline-flex align-items-center justify-content-center" style="width: 22px; height: 22px; font-size: 11px;">১</span>
                            <h6 class="fw-bold mb-0 small">bKash PGW Integration</h6>
                        </div>
                        <small class="text-muted d-block">
                            bKash Merchant Portal হতে প্রাপ্ত <code>App Key</code> এবং <code>App Secret</code> প্রবেশ করান। ক্রেতারা চেকআউটে বিকাশ পেমেন্ট করতে পারবেন।
                        </small>
                    </div>
                    <div class="p-3 rounded-3" style="background: var(--surface); border: 1px solid var(--border-color);">
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <span class="badge bg-warning text-dark rounded-circle p-1 d-inline-flex align-items-center justify-content-center" style="width: 22px; height: 22px; font-size: 11px;">২</span>
                            <h6 class="fw-bold mb-0 small">SSLCommerz গেটওয়ে</h6>
                        </div>
                        <small class="text-muted d-block">
                            ভিসা, মাস্টারকার্ড ও অন্যান্য ব্যাংকিং চ্যানেলের জন্য SSLCommerz-এর <code>Store ID</code> এবং <code>Store Password</code> প্রদান করুন।
                        </small>
                    </div>
                    <div class="alert alert-warning small m-0 p-2.5">
                        <i class="fa-solid fa-shield-check me-1"></i> পাসওয়ার্ড ও সিক্রেট কিগুলো এনক্রিপ্ট করে সুরক্ষিতভাবে ডাটাবেজে সংরক্ষণ করা হয়।
                    </div>
                </div>
            </div>
        </div>
        @endif

        @if(($activeTab ?? '') === 'fraud')
        <div class="col-lg-7">
            <div class="card p-3 p-md-4 h-100 border-start border-4 border-primary">
                <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                    <h6 class="fw-bold mb-0 text-primary">
                        <i class="fa-solid fa-shield-halved me-2"></i> Real-Time Fraud Detection Engine
                    </h6>
                    <span class="badge bg-primary-subtle text-primary rounded-pill px-2.5 py-1 small">BDCourier API</span>
                </div>
                <div class="d-flex flex-column gap-3">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="bdcourier_is_enabled" value="1" id="bdcourierEnabled" {{ ($settings['bdcourier_is_enabled'] ?? '1') === '1' ? 'checked' : '' }}>
                        <label class="form-check-label fw-bold" for="bdcourierEnabled">Enable Live Courier Fraud Checking</label>
                    </div>
                    <div>
                        <label class="form-label small fw-bold">Fraud API Base URL</label>
                        <input type="text" name="bdcourier_api_url" class="form-control" value="{{ $settings['bdcourier_api_url'] ?? 'https://api.bdcourier.com' }}" placeholder="https://api.bdcourier.com">
                        <small class="text-muted">Calls <code>/courier-check</code> endpoint with customer mobile.</small>
                    </div>
                    <div>
                        <label class="form-label small fw-bold">Fraud API Bearer Token / Key</label>
                        <div class="input-group">
                            <input type="password" name="bdcourier_api_key" id="bdcourier_api_key" class="form-control font-monospace" value="{{ $settings['bdcourier_api_key'] ?? 'jHxRx7kq1EpPt10ZHxOXyr0dltuhs6djfLDelYnhUBvN3CJCZwbybIj8fHeb' }}" placeholder="Enter API Key">
                            <button type="button" class="btn btn-outline-secondary toggle-password-btn" data-target="bdcourier_api_key" title="Toggle visibility">
                                <i class="fa-solid fa-eye"></i>
                            </button>
                        </div>
                    </div>
                    <div class="row g-2">
                        <div class="col-6">
                            <label class="form-label small fw-bold">Auto-Flag Threshold</label>
                            <input type="number" min="1" max="100" name="fraud_auto_flag_threshold" class="form-control" value="{{ $settings['fraud_auto_flag_threshold'] ?? 65 }}">
                            <small class="text-muted">Scores &ge; this are Flagged Fraud</small>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-bold">API Cache Lifetime (Sec)</label>
                            <input type="number" name="fraud_cache_seconds" class="form-control" value="{{ $settings['fraud_cache_seconds'] ?? 7200 }}">
                            <small class="text-muted">7200s = 2 hours cache</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="card p-3 p-md-4 h-100">
                <h5 class="fw-bold mb-3 d-flex align-items-center gap-2">
                    <i class="fa-solid fa-circle-question" style="color: var(--accent);"></i>
                    <span>ফ্রড ইঞ্জিন ব্যবহারের নিয়ম</span>
                </h5>
                <p class="text-muted small mb-3">
                    পার্সেল রিটার্ন ও ভুয়া অর্ডার প্রতিরোধে BDCourier ফ্রড ইঞ্জিনের কার্যপদ্ধতি:
                </p>
                <div class="d-flex flex-column gap-3">
                    <div class="p-3 rounded-3" style="background: var(--surface); border: 1px solid var(--border-color);">
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <span class="badge bg-primary rounded-circle p-1 d-inline-flex align-items-center justify-content-center" style="width: 22px; height: 22px; font-size: 11px;">১</span>
                            <h6 class="fw-bold mb-0 small">লাইভ কুরিয়ার হিস্ট্রি চেক</h6>
                        </div>
                        <small class="text-muted d-block">
                            অর্ডার প্লেস হলে স্বয়ংক্রিয়ভাবে গ্রাহকের মোবাইল নাম্বারের অতীত ডেলিভারি সাকসেস রেট এবং কুরিয়ার রিপোর্ট যাচাই করা হয়।
                        </small>
                    </div>
                    <div class="p-3 rounded-3" style="background: var(--surface); border: 1px solid var(--border-color);">
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <span class="badge bg-primary rounded-circle p-1 d-inline-flex align-items-center justify-content-center" style="width: 22px; height: 22px; font-size: 11px;">২</span>
                            <h6 class="fw-bold mb-0 small">অটোমেটেড রিস্ক স্কোরিং</h6>
                        </div>
                        <small class="text-muted d-block">
                            রিটার্ন অনুপাত এবং মার্চেন্ট অভিযোগ অনুযায়ী ০ থেকে ১০০ স্কোর নির্ধারিত হয়। থ্রেশহোল্ড (যেমন: ৬৫) পার হলে অর্ডার লাল মার্ক হয়ে যাবে।
                        </small>
                    </div>
                    <div class="p-3 rounded-3" style="background: var(--surface); border: 1px solid var(--border-color);">
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <span class="badge bg-primary rounded-circle p-1 d-inline-flex align-items-center justify-content-center" style="width: 22px; height: 22px; font-size: 11px;">৩</span>
                            <h6 class="fw-bold mb-0 small">ক্যাশিং ও রেট লিমিট সেফটি</h6>
                        </div>
                        <small class="text-muted d-block">
                            একই নাম্বারের ঘনঘন অনুরোধ ক্যাশ করে রাখা হয় যেন API কোটা সাশ্রয় হয় এবং গতি দ্রুত থাকে।
                        </small>
                    </div>
                </div>
            </div>
        </div>
        @endif

        @if(($activeTab ?? '') === 'gtm')
        <div class="col-lg-7">
            <div class="card p-3 p-md-4 h-100">
                <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                    <h6 class="fw-bold mb-0 text-primary">
                        <i class="fa-brands fa-google me-2"></i> Google Tag Manager & Tracking Engine
                    </h6>
                    <span class="badge {{ ($settings['gtm_enabled'] ?? '') === '1' ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-secondary' }} rounded-pill px-2.5 py-1 small">
                        {{ ($settings['gtm_enabled'] ?? '') === '1' ? 'সক্রিয় (Active)' : 'নিষ্ক্রিয় (Disabled)' }}
                    </span>
                </div>
                <div class="d-flex flex-column gap-3">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="gtm_enabled" value="1" id="gtmEnabled" {{ ($settings['gtm_enabled'] ?? '') === '1' ? 'checked' : '' }}>
                        <label class="form-check-label fw-bold" for="gtmEnabled">Enable Google Tag Manager</label>
                    </div>

                    <div>
                        <label class="form-label small fw-bold">GTM Container ID</label>
                        <div class="input-group">
                            <span class="input-group-text font-monospace small" style="background: var(--input-group-bg); border-color: var(--border-color); color: var(--text-main);"><i class="fa-solid fa-tag"></i></span>
                            <input type="text" name="gtm_container_id" class="form-control font-monospace" placeholder="GTM-XXXXXXX" value="{{ $settings['gtm_container_id'] ?? '' }}">
                        </div>
                        <small class="text-muted mt-1 d-block" style="font-size: 11.5px;">
                            আপনার Google Tag Manager কনটেইনার আইডি দিন (যেমন: <code>GTM-ABC1234</code>)।
                        </small>
                    </div>

                    <div class="row g-2.5">
                        <div class="col-md-4">
                            <div class="p-3 rounded-3 h-100" style="background: var(--surface); border: 1px solid var(--border-color);">
                                <div class="d-flex align-items-center gap-2 mb-1.5 text-warning">
                                    <i class="fa-solid fa-bolt"></i>
                                    <h6 class="fw-bold mb-0 small">Turbo SPA Ready</h6>
                                </div>
                                <small class="text-muted d-block" style="font-size: 11.5px; line-height: 1.4;">
                                    প্রতিটি পেজ ট্রানজিশনে স্বয়ংক্রিয় <code>turbo_page_view</code> ফায়ার হয়।
                                </small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3 rounded-3 h-100" style="background: var(--surface); border: 1px solid var(--border-color);">
                                <div class="d-flex align-items-center gap-2 mb-1.5 text-success">
                                    <i class="fa-solid fa-cart-shopping"></i>
                                    <h6 class="fw-bold mb-0 small">E-Commerce Events</h6>
                                </div>
                                <small class="text-muted d-block" style="font-size: 11.5px; line-height: 1.4;">
                                    ViewItem, AddToCart, BeginCheckout, ও Purchase ডাটালেয়ার সক্রিয়।
                                </small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3 rounded-3 h-100" style="background: var(--surface); border: 1px solid var(--border-color);">
                                <div class="d-flex align-items-center gap-2 mb-1.5 text-info">
                                    <i class="fa-solid fa-shield-check"></i>
                                    <h6 class="fw-bold mb-0 small">EMQ & Deduplication</h6>
                                </div>
                                <small class="text-muted d-block" style="font-size: 11.5px; line-height: 1.4;">
                                    SHA-256 ফোন হ্যাশিং ও SessionStorage ডুপ্লিকেট প্রতিরোধ কার্যকর।
                                </small>
                            </div>
                        </div>
                    </div>

                    <div class="p-3 rounded-3" style="background: var(--surface); border: 1px solid var(--border-color);">
                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
                            <span class="small fw-bold text-muted text-uppercase tracking-wider" style="font-size: 11px;">প্ল্যাটফর্ম কনসোল লিঙ্কসমূহ</span>
                            <span class="badge bg-primary-subtle text-primary rounded-pill px-2 py-0.5" style="font-size: 10px;">Direct Portals</span>
                        </div>
                        <div class="d-flex flex-wrap gap-2">
                            <a href="https://tagmanager.google.com" target="_blank" class="btn btn-sm btn-outline-secondary rounded-2 px-2.5 py-1 text-decoration-none">
                                <i class="fa-brands fa-google me-1 text-danger"></i> GTM Console
                            </a>
                            <a href="https://business.facebook.com/events_manager2" target="_blank" class="btn btn-sm btn-outline-secondary rounded-2 px-2.5 py-1 text-decoration-none">
                                <i class="fa-brands fa-facebook me-1 text-primary"></i> Meta Events Manager
                            </a>
                            <a href="https://ads.tiktok.com" target="_blank" class="btn btn-sm btn-outline-secondary rounded-2 px-2.5 py-1 text-decoration-none">
                                <i class="fa-brands fa-tiktok me-1"></i> TikTok Ads
                            </a>
                            <a href="https://analytics.google.com" target="_blank" class="btn btn-sm btn-outline-secondary rounded-2 px-2.5 py-1 text-decoration-none">
                                <i class="fa-solid fa-chart-line me-1 text-warning"></i> GA4 Analytics
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="card p-3 p-md-4 h-100">
                <h5 class="fw-bold mb-2 d-flex align-items-center gap-2">
                    <i class="fa-solid fa-circle-question" style="color: var(--accent);"></i>
                    <span>পিক্সেল ও GTM সহজ সেটআপ</span>
                </h5>
                <p class="text-muted small mb-3">
                    কোনো কোডিং ছাড়া মাত্র ২ মিনিটে ফেসবুক পিক্সেল ও অ্যানালিটিক্স চালু করার ৩টি সহজ ধাপ:
                </p>
                <div class="d-flex flex-column gap-3">
                    <div class="p-3 rounded-3" style="background: var(--surface); border: 1px solid var(--border-color);">
                        <div class="d-flex align-items-center justify-content-between mb-1.5">
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge bg-primary rounded-circle p-1 d-inline-flex align-items-center justify-content-center" style="width: 22px; height: 22px; font-size: 11px;">১</span>
                                <h6 class="fw-bold mb-0 small">রেডিমেড কনটেইনার ফাইল ডাউনলোড</h6>
                            </div>
                            <span class="badge bg-success-subtle text-success" style="font-size: 10px;">১-ক্লিক সমাধান</span>
                        </div>
                        <small class="text-muted d-block mb-2.5">
                            আমাদের প্রি-কনফিগার করা ফাইলটিতে ফেসবুক পিক্সেল, GA4 এবং সমস্ত ইভেন্ট আগে থেকেই রেডি করা আছে।
                        </small>
                        <a href="{{ asset('downloads/zippy-gtm-container.json') }}" download="zippy-gtm-container.json" class="btn btn-primary btn-sm w-100 fw-bold py-2 rounded-2">
                            <i class="fa-solid fa-download me-1.5"></i> Download GTM Container (.json)
                        </a>
                    </div>

                    <div class="p-3 rounded-3" style="background: var(--surface); border: 1px solid var(--border-color);">
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <span class="badge bg-primary rounded-circle p-1 d-inline-flex align-items-center justify-content-center" style="width: 22px; height: 22px; font-size: 11px;">২</span>
                            <h6 class="fw-bold mb-0 small">GTM-এ আপলোড ও Pixel ID বসান</h6>
                        </div>
                        <small class="text-muted d-block">
                            GTM-এর <strong>Admin &gt; Import Container</strong> এ গিয়ে ডাউনলোড করা ফাইলটি আপলোড করে <strong>Merge</strong> দিন। এরপর <strong>Variables</strong> থেকে <code>Constant - Facebook Pixel ID</code>-তে আপনার আসল পিক্সেল আইডি বসিয়ে <strong>Submit</strong> চাপুন।
                        </small>
                    </div>

                    <div class="p-3 rounded-3" style="background: var(--surface); border: 1px solid var(--border-color);">
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <span class="badge bg-primary rounded-circle p-1 d-inline-flex align-items-center justify-content-center" style="width: 22px; height: 22px; font-size: 11px;">৩</span>
                            <h6 class="fw-bold mb-0 small">GTM আইডি বসিয়ে সেভ করুন</h6>
                        </div>
                        <small class="text-muted d-block">
                            বামে আপনার <strong>GTM Container ID</strong> (যেমন: <code>GTM-ABC1234</code>) পেস্ট করে <strong>Enable Google Tag Manager</strong> অন করে উপরে <strong>Save Configurations</strong> চাপুন। ব্যস, পিক্সেল লাইভ!
                        </small>
                    </div>

                    <div class="p-3 rounded-3 d-flex align-items-center justify-content-between flex-wrap gap-2" style="background: rgba(99, 102, 241, 0.08); border: 1px dashed var(--accent);">
                        <div>
                            <div class="fw-bold small text-primary"><i class="fa-solid fa-book-open me-1"></i> সচিত্র বিস্তারিত গাইডলাইন</div>
                            <small class="text-muted" style="font-size: 11.5px;">পিক্সেল টেস্ট করার উপায় ও বিস্তারিত দেখতে ক্লিক করুন</small>
                        </div>
                        <button type="button" class="btn btn-outline-primary btn-sm px-3 py-1.5 rounded-2 fw-semibold" data-bs-toggle="modal" data-bs-target="#gtmSnippetModal">
                            <i class="fa-solid fa-eye me-1"></i> সম্পূর্ণ গাইড দেখুন
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="gtmSnippetModal" tabindex="-1" aria-labelledby="gtmSnippetModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content" style="background: var(--surface-2, #0f172a); border: 1px solid var(--border-color); color: var(--text-main, #f8fafc);">
                    <div class="modal-header border-bottom" style="border-color: var(--border-color) !important;">
                        <div class="d-flex align-items-center gap-2">
                            <div class="stat-icon" style="width: 38px; height: 38px; border-radius: 10px; display: inline-flex; align-items: center; justify-content: center; background: rgba(99, 102, 241, 0.15); color: var(--accent);">
                                <i class="fa-solid fa-book-open"></i>
                            </div>
                            <div>
                                <h6 class="modal-title fw-bold mb-0" id="gtmSnippetModalLabel">নন-টেক সহজ পিক্সেল ও GTM সেটআপ গাইড</h6>
                                <small class="text-muted" style="font-size: 11.5px;">কোনো কোডিং ছাড়া মাত্র ৩ ক্লিকে পিক্সেল সেটআপ এবং টেস্ট করার নির্দেশিকা</small>
                            </div>
                        </div>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-3 p-md-4">
                        <ul class="nav nav-pills mb-3 gap-2" id="modalSnippetTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active btn-sm rounded-3 py-1.5 px-3 fw-bold" id="m-tab-auto-btn" data-bs-toggle="pill" data-bs-target="#m-tab-auto" type="button" role="tab">
                                    <i class="fa-solid fa-bolt me-1.5 text-warning"></i> ১-ক্লিক অটো সেটআপ (রেকমেন্ডেড)
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link btn-sm rounded-3 py-1.5 px-3 fw-bold" id="m-tab-verify-btn" data-bs-toggle="pill" data-bs-target="#m-tab-verify" type="button" role="tab">
                                    <i class="fa-solid fa-circle-check me-1.5 text-success"></i> পিক্সেল টেস্ট করার উপায়
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link btn-sm rounded-3 py-1.5 px-3 fw-bold" id="m-tab-faq-btn" data-bs-toggle="pill" data-bs-target="#m-tab-faq" type="button" role="tab">
                                    <i class="fa-solid fa-circle-question me-1.5 text-info"></i> সাধারণ প্রশ্নোত্তর (FAQ)
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link btn-sm rounded-3 py-1.5 px-3 fw-bold" id="m-tab-dev-btn" data-bs-toggle="pill" data-bs-target="#m-tab-dev" type="button" role="tab">
                                    <i class="fa-solid fa-code me-1.5 text-danger"></i> ডেভেলপার রেফারেন্স (ঐচ্ছিক)
                                </button>
                            </li>
                        </ul>

                        <div class="tab-content" id="modalSnippetTabsContent">
                            <div class="tab-pane fade show active" id="m-tab-auto" role="tabpanel">
                                <div class="d-flex flex-column gap-3">
                                    <div class="alert alert-primary d-flex align-items-center gap-2 m-0 p-3 rounded-3" style="background: rgba(99, 102, 241, 0.12); border: 1px solid var(--accent);">
                                        <i class="fa-solid fa-lightbulb text-primary fs-5"></i>
                                        <div class="small">
                                            <strong>কেন এটি সবচেয়ে সহজ?</strong> আপনাকে নিজে নিজে কোনো ট্যাগ, ট্রিগার বা কোড লিখতে হবে না। আমাদের প্রি-বিল্ট ফাইলটি GTM-এ আপলোড করলেই ফেসবুক পিক্সেল, GA4, পেজভিউ, কার্ট এবং পারচেজ ট্র্যাকিং অটোমেটিক রেডি হয়ে যাবে।
                                        </div>
                                    </div>

                                    <div class="p-3 rounded-3" style="background: var(--surface); border: 1px solid var(--border-color);">
                                        <div class="d-flex align-items-center gap-2 mb-2">
                                            <span class="badge bg-primary rounded-circle p-1 d-inline-flex align-items-center justify-content-center" style="width: 22px; height: 22px; font-size: 11px;">১</span>
                                            <h6 class="fw-bold mb-0 text-white small">ধাপ ১: কনটেইনার ফাইলটি ডাউনলোড করুন</h6>
                                        </div>
                                        <p class="text-muted small mb-2">নিচের বাটনে ক্লিক করে ফাইলটি আপনার কম্পিউটারে সেভ করুন:</p>
                                        <a href="{{ asset('downloads/zippy-gtm-container.json') }}" download="zippy-gtm-container.json" class="btn btn-primary btn-sm px-3 py-1.5 fw-bold rounded-2">
                                            <i class="fa-solid fa-download me-1"></i> zippy-gtm-container.json ডাউনলোড করুন
                                        </a>
                                    </div>

                                    <div class="p-3 rounded-3" style="background: var(--surface); border: 1px solid var(--border-color);">
                                        <div class="d-flex align-items-center gap-2 mb-2">
                                            <span class="badge bg-primary rounded-circle p-1 d-inline-flex align-items-center justify-content-center" style="width: 22px; height: 22px; font-size: 11px;">২</span>
                                            <h6 class="fw-bold mb-0 text-white small">ধাপ ২: GTM-এ ইমপোর্ট করুন</h6>
                                        </div>
                                        <ol class="small text-muted mb-0 ps-3 d-flex flex-column gap-1.5">
                                            <li><a href="https://tagmanager.google.com" target="_blank" class="text-primary text-decoration-none fw-semibold">Google Tag Manager</a>-এ লগইন করে আপনার অ্যাকাউন্টে ঢুকুন।</li>
                                            <li>উপরের মেনু থেকে <strong>Admin</strong> (অ্যাডমিন) এ ক্লিক করুন।</li>
                                            <li>ডানপাশের কলাম থেকে <strong>Import Container</strong> এ ক্লিক করুন।</li>
                                            <li><strong>Choose container file</strong> বাটনে ক্লিক করে ডাউনলোড করা <code>zippy-gtm-container.json</code> ফাইলটি সিলেক্ট করুন।</li>
                                            <li><strong>Choose workspace</strong> এ <strong>Default Workspace</strong> বা <strong>Existing</strong> সিলেক্ট করুন।</li>
                                            <li>অপশন হিসেবে <strong>Merge</strong> সিলেক্ট করে <strong>Overwrite conflicting tags, triggers, and variables</strong> এ টিক দিন।</li>
                                            <li>নিচে নীল রঙের <strong>Confirm</strong> বাটনে চাপুন।</li>
                                        </ol>
                                    </div>

                                    <div class="p-3 rounded-3" style="background: var(--surface); border: 1px solid var(--border-color);">
                                        <div class="d-flex align-items-center gap-2 mb-2">
                                            <span class="badge bg-primary rounded-circle p-1 d-inline-flex align-items-center justify-content-center" style="width: 22px; height: 22px; font-size: 11px;">৩</span>
                                            <h6 class="fw-bold mb-0 text-white small">ধাপ ৩: শুধু আপনার Facebook Pixel ID বসিয়ে Publish করুন</h6>
                                        </div>
                                        <ol class="small text-muted mb-0 ps-3 d-flex flex-column gap-1.5">
                                            <li>GTM-এর বামপাশের মেনু থেকে <strong>Variables</strong> এ ক্লিক করুন।</li>
                                            <li><strong>User-Defined Variables</strong> তালিকায় <code>Constant - Facebook Pixel ID</code> এ ক্লিক করুন।</li>
                                            <li>সেখানে <code>YOUR_PIXEL_ID_HERE</code> মুছে আপনার ফেসবুক পিক্সেল আইডি (যেমন: <code>987654321012345</code>) বসিয়ে <strong>Save</strong> করুন।</li>
                                            <li>(যদি GA4 চান) একইভাবে <code>Constant - GA4 Measurement ID</code>-তে আপনার GA4 আইডি (যেমন: <code>G-XXXXXXXXXX</code>) বসিয়ে সেভ করুন।</li>
                                            <li>এবার GTM-এর উপরে ডান কোনায় নীল <strong>Submit</strong> বাটনে চাপুন, তারপর <strong>Publish</strong> চাপুন!</li>
                                        </ol>
                                    </div>

                                    <div class="p-3 rounded-3" style="background: var(--surface); border: 1px solid var(--border-color);">
                                        <div class="d-flex align-items-center gap-2 mb-2">
                                            <span class="badge bg-primary rounded-circle p-1 d-inline-flex align-items-center justify-content-center" style="width: 22px; height: 22px; font-size: 11px;">৪</span>
                                            <h6 class="fw-bold mb-0 text-white small">ধাপ ৪: Zippy এডমিনে GTM চালু করুন</h6>
                                        </div>
                                        <small class="text-muted d-block">
                                            এই পেজের বাম পাশের বক্সে আপনার <strong>GTM Container ID</strong> (যেমন: <code>GTM-ABC1234</code>) লিখে <strong>Enable Google Tag Manager</strong> সুইচ অন করে উপরে <strong>Save Configurations</strong> চাপুন। আপনার কাজ শেষ!
                                        </small>
                                    </div>
                                </div>
                            </div>

                            <div class="tab-pane fade" id="m-tab-verify" role="tabpanel">
                                <div class="d-flex flex-column gap-3">
                                    <div class="p-3 rounded-3" style="background: var(--surface); border: 1px solid var(--border-color);">
                                        <h6 class="fw-bold text-white small mb-2">
                                            <i class="fa-brands fa-chrome me-1 text-warning"></i> Chrome Extension দিয়ে টেস্ট করার সহজ নিয়ম:
                                        </h6>
                                        <ol class="small text-muted mb-2 ps-3 d-flex flex-column gap-1.5">
                                            <li>গুগল ক্রোম ব্রাউজারে <a href="https://chromewebstore.google.com/detail/meta-pixel-helper/fdgfkebogiimcoedlicjlajpkdmockpc" target="_blank" class="text-primary fw-semibold">Meta Pixel Helper</a> এক্সটেনশনটি ইনস্টল করে নিন।</li>
                                            <li>আপনার ওয়েবসাইটের যেকোনো পেজে যান।</li>
                                            <li>ব্রাউজারের ডানপাশের পিক্সেল হেল্পার আইকনে ক্লিক করে নিচের ইভেন্টগুলো চেক করুন:</li>
                                        </ol>
                                    </div>

                                    <div class="row g-2">
                                        <div class="col-md-6">
                                            <div class="p-2.5 rounded-3 h-100" style="background: var(--surface); border: 1px solid var(--border-color);">
                                                <span class="badge bg-success-subtle text-success mb-1">হোম বা ক্যাটাগরি পেজে</span>
                                                <h6 class="fw-bold small text-white mb-1">PageView</h6>
                                                <small class="text-muted d-block">পেজ ওপেন করলেই এটি সবুজ টিক দেখাবে।</small>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="p-2.5 rounded-3 h-100" style="background: var(--surface); border: 1px solid var(--border-color);">
                                                <span class="badge bg-primary-subtle text-primary mb-1">প্রোডাক্ট বিস্তারিত পেজে</span>
                                                <h6 class="fw-bold small text-white mb-1">ViewContent</h6>
                                                <small class="text-muted d-block">প্রোডাক্টের নাম, ক্যাটাগরি ও দাম পিক্সেলে যাবে।</small>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="p-2.5 rounded-3 h-100" style="background: var(--surface); border: 1px solid var(--border-color);">
                                                <span class="badge bg-info-subtle text-info mb-1">Add to Cart চাপলে</span>
                                                <h6 class="fw-bold small text-white mb-1">AddToCart</h6>
                                                <small class="text-muted d-block">প্রোডাক্ট কার্টে যোগ করার সাথে সাথে সবুজ টিক দেখাবে।</small>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="p-2.5 rounded-3 h-100" style="background: var(--surface); border: 1px solid var(--border-color);">
                                                <span class="badge bg-danger-subtle text-danger mb-1">অর্ডার সম্পন্ন হলে</span>
                                                <h6 class="fw-bold small text-white mb-1">Purchase</h6>
                                                <small class="text-muted d-block">অর্ডার নাম্বার, মোট টাকার পরিমাণ এবং ফোন নাম্বার যাবে।</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="tab-pane fade" id="m-tab-faq" role="tabpanel">
                                <div class="d-flex flex-column gap-3">
                                    <div class="p-3 rounded-3" style="background: var(--surface); border: 1px solid var(--border-color);">
                                        <h6 class="fw-bold text-white small mb-1">১. কাস্টমার পেজ রিলোড দিলে কি বারবার Purchase ফায়ার হবে?</h6>
                                        <small class="text-muted d-block">
                                            না! Zippy BD-তে বিল্ট-ইন <strong>Anti-Duplication</strong> ব্যবস্থা আছে। একই অর্ডারের ক্ষেত্রে ব্রাউজার ১০ বার রিলোড দিলেও মেটা পিক্সেলে একবারের বেশি পারচেজ কাউন্ট হবে না।
                                        </small>
                                    </div>
                                    <div class="p-3 rounded-3" style="background: var(--surface); border: 1px solid var(--border-color);">
                                        <h6 class="fw-bold text-white small mb-1">২. ফেসবুক অ্যাড ম্যানেজারে ইভেন্ট ম্যাচ কোয়ালিটি (EMQ) কেমন থাকবে?</h6>
                                        <small class="text-muted d-block">
                                            Zippy BD গ্রাহকের ফোন নাম্বার সরাসরি পাঠায় না, বরং আন্তর্জাতিক নিরাপত্তা নিয়ম মেনে SHA-256 এনক্রিপ্ট করে পাঠায়। এর ফলে মেটা অ্যাড ম্যানেজারে Event Match Quality (EMQ) স্কোর সবসময় হাই থাকে।
                                        </small>
                                    </div>
                                    <div class="p-3 rounded-3" style="background: var(--surface); border: 1px solid var(--border-color);">
                                        <h6 class="fw-bold text-white small mb-1">৩. কোনো কোডিং ছাড়াই কি টিকটক পিক্সেলও যুক্ত করা যাবে?</h6>
                                        <small class="text-muted d-block">
                                            হ্যাঁ, GTM-এ গিয়ে একইভাবে TikTok Pixel ট্যাগ তৈরি করে ট্রিগার হিসেবে <code>turbo_page_view</code> এবং অন্যান্য ইভেন্টগুলো সিলেক্ট করে দিলেই কাজ করবে।
                                        </small>
                                    </div>
                                </div>
                            </div>

                            <div class="tab-pane fade" id="m-tab-dev" role="tabpanel">
                                <div class="d-flex flex-column gap-3">
                                    <p class="text-muted small mb-0">
                                        এই সেকশনটি শুধুমাত্র এজেন্সী বা ডেভেলপারদের জন্য যারা নিজে কাস্টম কোড দিয়ে ট্যাগ কনফিগার করতে চান:
                                    </p>
                                    <div class="p-3 rounded-3" style="background: var(--surface); border: 1px solid var(--border-color);">
                                        <div class="d-flex justify-content-between align-items-center mb-1.5">
                                            <span class="fw-bold small text-primary">Meta Base Pixel (Custom HTML)</span>
                                            <span class="badge bg-danger-subtle text-danger">ট্রিগার: turbo_page_view</span>
                                        </div>
                                        <pre class="p-2.5 rounded-2 font-monospace m-0" style="background: #000; border: 1px solid var(--border-color); color: #38bdf8; font-size: 11px; overflow-x: auto;"><code>&lt;script&gt;
!function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window, document,'script','https://connect.facebook.net/en_US/fbevents.js');
fbq('init', 'YOUR_PIXEL_ID');
fbq('track', 'PageView');
&lt;/script&gt;</code></pre>
                                    </div>
                                    <div class="p-3 rounded-3" style="background: var(--surface); border: 1px solid var(--border-color);">
                                        <div class="d-flex justify-content-between align-items-center mb-1.5">
                                            <span class="fw-bold small text-success">Meta Purchase Event</span>
                                            <span class="badge bg-success-subtle text-success">ট্রিগার: purchase</span>
                                        </div>
                                        <pre class="p-2.5 rounded-2 font-monospace m-0" style="background: #000; border: 1px solid var(--border-color); color: #86efac; font-size: 11px; overflow-x: auto;"><code>&lt;script&gt;
fbq('track', 'Purchase', { content_type: 'product', content_ids: @{{content_ids}}, value: @{{ecommerce.value}}, currency: 'BDT' }, { eventID: '@{{ecommerce.transaction_id}}' });
&lt;/script&gt;</code></pre>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-top d-flex justify-content-between align-items-center" style="border-color: var(--border-color) !important;">
                        <small class="text-muted" style="font-size: 11.5px;">
                            <i class="fa-solid fa-file-lines me-1 text-primary"></i> টেক্সট গাইডলাইন: <code>docs/GTM_TRACKING_GUIDE.md</code>
                        </small>
                        <button type="button" class="btn btn-secondary btn-sm px-3 rounded-2" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
        @endif

        @if(($activeTab ?? '') === 'smtp')
        <div class="col-lg-7">
            <div class="card p-3 p-md-4 h-100">
                <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                    <h6 class="fw-bold mb-0 text-primary">
                        <i class="fa-solid fa-envelope me-2"></i> Outgoing Mail Server (SMTP)
                    </h6>
                    <span class="badge bg-primary-subtle text-primary rounded-pill px-2.5 py-1 small">Email Services</span>
                </div>
                <div class="d-flex flex-column gap-3">
                    <div class="row g-2">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Mail Driver</label>
                            <select name="mail_mailer" class="form-select">
                                <option value="smtp" {{ ($settings['mail_mailer'] ?? 'smtp') === 'smtp' ? 'selected' : '' }}>SMTP</option>
                                <option value="sendmail" {{ ($settings['mail_mailer'] ?? '') === 'sendmail' ? 'selected' : '' }}>Sendmail</option>
                                <option value="log" {{ ($settings['mail_mailer'] ?? '') === 'log' ? 'selected' : '' }}>Log (Dev Testing)</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Encryption Protocol</label>
                            <select name="mail_encryption" class="form-select">
                                <option value="ssl" {{ ($settings['mail_encryption'] ?? 'ssl') === 'ssl' ? 'selected' : '' }}>SSL (Port 465)</option>
                                <option value="tls" {{ ($settings['mail_encryption'] ?? '') === 'tls' ? 'selected' : '' }}>TLS / STARTTLS (Port 587)</option>
                                <option value="none" {{ ($settings['mail_encryption'] ?? '') === 'none' ? 'selected' : '' }}>None</option>
                            </select>
                        </div>
                    </div>
                    <div class="row g-2">
                        <div class="col-md-8">
                            <label class="form-label small fw-bold">SMTP Host</label>
                            <input type="text" name="mail_host" class="form-control font-monospace" placeholder="e.g. s11642.sgp1.stableserver.net or mail.domain.com" value="{{ $settings['mail_host'] ?? 's11642.sgp1.stableserver.net' }}" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">SMTP Port</label>
                            <input type="number" name="mail_port" class="form-control font-monospace" placeholder="465" value="{{ $settings['mail_port'] ?? '465' }}" required>
                        </div>
                    </div>
                    <div class="row g-2">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">SMTP Username / Email</label>
                            <input type="text" name="mail_username" class="form-control" placeholder="security@Zippy.com" value="{{ $settings['mail_username'] ?? 'security@Zippy.com' }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">SMTP Password</label>
                            <div class="input-group">
                                <input type="password" name="mail_password" id="mail_password" class="form-control font-monospace" placeholder="••••••••" value="{{ $settings['mail_password'] ?? '' }}">
                                <button type="button" class="btn btn-outline-secondary toggle-password-btn" data-target="mail_password" title="Toggle visibility">
                                    <i class="fa-solid fa-eye"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="row g-2">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Sender Name (From Name)</label>
                            <input type="text" name="mail_from_name" class="form-control" placeholder="Zippy Security" value="{{ $settings['mail_from_name'] ?? 'Zippy Security' }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Sender Address (From Email)</label>
                            <input type="email" name="mail_from_address" class="form-control" placeholder="security@Zippy.com" value="{{ $settings['mail_from_address'] ?? 'security@Zippy.com' }}">
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="card p-3 p-md-4 h-100">
                <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                    <h6 class="fw-bold mb-0 text-success">
                        <i class="fa-solid fa-paper-plane me-2"></i> Live SMTP Test
                    </h6>
                    <span class="badge bg-success-subtle text-success rounded-pill px-2.5 py-1 small">Instant Test</span>
                </div>
                <p class="text-muted small mb-3">
                    Verify that your configured SMTP credentials and outgoing mail server connect properly by sending a test email to your inbox.
                </p>
                <div class="mb-3">
                    <label class="form-label small fw-bold">Recipient Email Address</label>
                    <input type="email" id="testEmailRecipient" class="form-control" placeholder="admin@example.com" value="{{ $settings['mail_from_address'] ?? 'security@Zippy.com' }}">
                </div>
                <button type="button" class="btn btn-outline-success w-100 py-2 fw-semibold" id="sendTestEmailBtn" onclick="runLiveSmtpTest()">
                    <i class="fa-solid fa-paper-plane me-1.5"></i> Send Test Verification Email
                </button>
                <div id="testEmailStatus" class="mt-3" style="display: none;"></div>

                <div class="mt-4 pt-3 border-top">
                    <h6 class="fw-bold small text-body mb-2"><i class="fa-solid fa-shield-halved me-1 text-primary"></i> Direct Origin Guidance</h6>
                    <small class="text-muted" style="line-height: 1.6;">
                        If your domain is behind Cloudflare, standard mail proxy blocks ports 465 and 587. Always use the server's direct hostname (e.g. <code>s11642.sgp1.stableserver.net</code>) or origin server IP for zero-timeout delivery.
                    </small>
                </div>
            </div>
        </div>
        @endif

        @if(($activeTab ?? '') === 'backup')
        <div class="col-lg-7">
            <div class="card p-3 p-md-4 h-100">
                <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                    <h6 class="fw-bold mb-0 text-info">
                        <i class="fa-solid fa-database me-2"></i> Database Snapshot & Backup
                    </h6>
                    <span class="badge bg-info-subtle text-info rounded-pill px-2.5 py-1 small">Enterprise Safety</span>
                </div>
                <div class="d-flex flex-column gap-3">
                    <p class="text-muted small mb-0">
                        Generate an instant snapshot backup of your entire application database.
                    </p>
                    <div class="p-3 rounded-3" style="background: var(--surface); border: 1px solid var(--border-color);">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <span class="fw-bold d-block">Manual Database Snapshot</span>
                                <small class="text-muted">Creates encrypted SQL dump file in storage</small>
                            </div>
                            @canPerm('admin.settings.backup')
                                <button type="button" class="btn btn-outline-danger btn-sm" onclick="triggerSnapshotBackup()">
                                    <i class="fa-solid fa-database me-1"></i> Backup Now
                                </button>
                            @endcanPerm
                        </div>
                    </div>
                    <div class="alert alert-success small m-0 p-2.5">
                        <i class="fa-solid fa-circle-check me-1"></i> Automated backups are logged into the System Audit Trail.
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="card p-3 p-md-4 h-100">
                <h5 class="fw-bold mb-3 d-flex align-items-center gap-2">
                    <i class="fa-solid fa-circle-question" style="color: var(--accent);"></i>
                    <span>ব্যাকআপ নির্দেশিকা ও নিরাপত্তা</span>
                </h5>
                <p class="text-muted small mb-3">
                    ডাটাবেজ সুরক্ষা ও পুনরুদ্ধার প্রক্রিয়ার গুরুত্বপূর্ণ তথ্য:
                </p>
                <div class="d-flex flex-column gap-3">
                    <div class="p-3 rounded-3" style="background: var(--surface); border: 1px solid var(--border-color);">
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <span class="badge bg-info text-dark rounded-circle p-1 d-inline-flex align-items-center justify-content-center" style="width: 22px; height: 22px; font-size: 11px;">১</span>
                            <h6 class="fw-bold mb-0 small">ইনস্ট্যান্ট স্ন্যাপশট</h6>
                        </div>
                        <small class="text-muted d-block">
                            "Backup Now" বোতাম চাপলে বর্তমান সম্পূর্ণ ডাটাবেজের একটি টাইমস্ট্যাম্পযুক্ত এসকিউএল স্ন্যাপশট তৈরি হয়।
                        </small>
                    </div>
                    <div class="p-3 rounded-3" style="background: var(--surface); border: 1px solid var(--border-color);">
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <span class="badge bg-info text-dark rounded-circle p-1 d-inline-flex align-items-center justify-content-center" style="width: 22px; height: 22px; font-size: 11px;">২</span>
                            <h6 class="fw-bold mb-0 small">অডিট লগ ট্র্যাকিং</h6>
                        </div>
                        <small class="text-muted d-block">
                            কোন এডমিন কখন ব্যাকআপ জেনারেট করেছেন তা বিস্তারিতভাবে সিস্টেম অডিট ট্রেইলে সংরক্ষিত থাকে।
                        </small>
                    </div>
                    <div class="alert alert-info small m-0 p-2.5">
                        <i class="fa-solid fa-lock me-1"></i> সিস্টেম আপডেট বা ডাটা মাইগ্রেশনের পূর্বে একটি স্ন্যাপশট ব্যাকআপ নেওয়া উত্তম চর্চা।
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>
</form>
@endsection

@push('scripts')
<script>
(() => {
    document.addEventListener('click', function(e) {
        const btn = e.target.closest('.toggle-password-btn');
        if (!btn) return;
        const targetId = btn.getAttribute('data-target');
        const input = document.getElementById(targetId);
        const icon = btn.querySelector('i');

        if (input) {
            if (input.type === 'password') {
                input.type = 'text';
                if (icon) {
                    icon.classList.remove('fa-eye');
                    icon.classList.add('fa-eye-slash');
                }
            } else {
                input.type = 'password';
                if (icon) {
                    icon.classList.remove('fa-eye-slash');
                    icon.classList.add('fa-eye');
                }
            }
        }
    });

    function triggerSnapshotBackup() {
        Swal.fire({
            title: 'Trigger DB Snapshot Backup?',
            text: 'This generates an encrypted database snapshot file.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, Backup Now'
        }).then(res => {
            if (res.isConfirmed) {
                axios.post('{{ route('admin.settings.backup') }}')
                .then(res => {
                    const d = res.data;
                    Swal.fire('Completed!', d.message, 'success');
                });
            }
        });
    }

    function runLiveSmtpTest() {
        const email = document.getElementById('testEmailRecipient').value;
        const btn = document.getElementById('sendTestEmailBtn');
        const statusDiv = document.getElementById('testEmailStatus');

        if (!email) {
            if (window.showToast) window.showToast('Please enter a recipient email address.', 'warning');
            return;
        }

        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1.5" role="status"></span> Sending Test Email...';
        statusDiv.style.display = 'block';
        statusDiv.className = 'alert alert-info small py-2 px-3';
        statusDiv.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-1"></i> Testing connection to SMTP server...';

        axios.post('{{ route("admin.settings.smtp.test") }}', {
            test_email: email
        }).then(res => {
            btn.disabled = false;
            btn.innerHTML = '<i class="fa-solid fa-paper-plane me-1.5"></i> Send Test Verification Email';
            statusDiv.className = 'alert alert-success small py-2 px-3';
            statusDiv.innerHTML = '<i class="fa-solid fa-circle-check me-1"></i> ' + res.data.message;
        }).catch(err => {
            btn.disabled = false;
            btn.innerHTML = '<i class="fa-solid fa-paper-plane me-1.5"></i> Send Test Verification Email';
            const msg = err.response && err.response.data && err.response.data.message ? err.response.data.message : 'SMTP Connection Failed.';
            statusDiv.className = 'alert alert-danger small py-2 px-3';
            statusDiv.innerHTML = '<i class="fa-solid fa-circle-exclamation me-1"></i> ' + msg;
        });
    }

    window.triggerSnapshotBackup = triggerSnapshotBackup;
    window.runLiveSmtpTest = runLiveSmtpTest;
})();
</script>
@endpush