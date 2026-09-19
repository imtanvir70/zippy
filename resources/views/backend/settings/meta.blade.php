@extends('backend.layouts.app')

@section('title', 'Meta Pixel & Conversions API (CAPI) Settings')

@section('content')
<div class="card p-3 p-md-4 mb-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
        <div class="d-flex align-items-center gap-3">
            <div class="stat-icon" style="width: 48px; height: 48px; border-radius: 14px; background: rgba(24, 119, 242, 0.1); color: #1877f2; display: flex; align-items: center; justify-content: center; font-size: 24px;">
                <i class="fa-brands fa-facebook"></i>
            </div>
            <div>
                <h3 class="fw-bold mb-1">Meta Pixel & Conversions API (CAPI)</h3>
                <small class="text-muted">সার্ভার-সাইড ট্র্যাকিং এবং মেটা কনভার্সন এপিআই ক্রেডেনশিয়াল ডেটাবেজ থেকে নিয়ন্ত্রণ করুন।</small>
            </div>
        </div>
        <div>
            @if(!empty($settings['meta_capi_status']) && !empty($settings['meta_pixel_id']) && !empty($settings['meta_capi_access_token']))
                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-2 fw-semibold d-inline-flex align-items-center gap-2">
                    <i class="fa-solid fa-circle" style="font-size: 8px;"></i> CAPI Active & Connected
                </span>
            @elseif(!empty($settings['meta_pixel_id']))
                <span class="badge bg-warning-subtle text-warning border border-warning-subtle rounded-pill px-3 py-2 fw-semibold d-inline-flex align-items-center gap-2">
                    <i class="fa-solid fa-circle" style="font-size: 8px;"></i> Pixel Set (Token Required)
                </span>
            @else
                <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill px-3 py-2 fw-semibold d-inline-flex align-items-center gap-2">
                    <i class="fa-solid fa-circle" style="font-size: 8px;"></i> CAPI Inactive
                </span>
            @endif
        </div>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show d-flex align-items-center gap-2 rounded-3 mb-4" role="alert">
        <i class="fa-solid fa-circle-check fs-5"></i>
        <div>{{ session('success') }}</div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show rounded-3 mb-4" role="alert">
        <div class="fw-bold mb-1"><i class="fa-solid fa-circle-exclamation me-1"></i> অনুগ্রহ করে নিচের ত্রুটিগুলো সংশোধন করুন:</div>
        <ul class="mb-0 ps-3">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<div class="row g-4">
    <div class="col-lg-7">
        <div class="card p-3 p-md-4 h-100">
            <form action="{{ route('admin.settings.meta.update') }}" method="POST">
                @csrf

                <div class="d-flex justify-content-between align-items-center p-3 rounded-3 mb-4" style="background: var(--surface); border: 1px solid var(--border-color);">
                    <div class="d-flex align-items-center gap-3">
                        <div class="p-2 rounded-3 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px; background: rgba(24, 119, 242, 0.1); color: #1877f2; border: 1px solid rgba(24, 119, 242, 0.2);">
                            <i class="fa-solid fa-server fs-5"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold mb-0">Server-Side Conversions API (CAPI)</h6>
                            <small class="text-muted">সার্ভার থেকে সরাসরি Meta Graph API-তে ইভেন্ট পাঠানো সক্রিয়/নিষ্ক্রিয় করুন</small>
                        </div>
                    </div>
                    <div class="form-check form-switch mb-0">
                        <input class="form-check-input" type="checkbox" name="meta_capi_status" id="metaCapiStatus" value="1" {{ !empty($settings['meta_capi_status']) ? 'checked' : '' }} style="cursor: pointer; width: 2.75em; height: 1.4em;">
                    </div>
                </div>

                <div class="mb-3">
                    <label for="metaPixelId" class="form-label fw-bold">Meta Pixel ID <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text bg-light text-muted"><i class="fa-solid fa-bullseye"></i></span>
                        <input type="text" class="form-control font-monospace" name="meta_pixel_id" id="metaPixelId" value="{{ old('meta_pixel_id', $settings['meta_pixel_id'] ?? '') }}" placeholder="e.g. 123456789012345" required>
                    </div>
                    <div class="form-text small text-muted">আপনার ফেসবুক ইভেন্টস ম্যানেজার থেকে প্রাপ্ত ১৫-১৬ সংখ্যার মেটা ডেটাসেট বা পিক্সেল আইডি দিন।</div>
                </div>

                <div class="mb-3">
                    <label for="fbAppId" class="form-label fw-bold">Facebook App ID (fb:app_id) <span class="text-muted fw-normal">(ঐচ্ছিক / ডিবাগার ওয়ার্নিং দূর করার জন্য)</span></label>
                    <div class="input-group">
                        <span class="input-group-text bg-light text-muted"><i class="fa-brands fa-facebook-f"></i></span>
                        <input type="text" class="form-control font-monospace" name="fb_app_id" id="fbAppId" value="{{ old('fb_app_id', $settings['fb_app_id'] ?? '') }}" placeholder="e.g. 123456789012345">
                    </div>
                    <div class="form-text small text-muted">developers.facebook.com থেকে আপনার মেটা অ্যাপ আইডি দিন। এটি বসালে ফেসবুক ডিবাগারের "missing fb:app_id" ওয়ার্নিং দূর হয়ে যাবে।</div>
                </div>

                <div class="mb-3">
                    <label for="metaCapiAccessToken" class="form-label fw-bold">Meta CAPI System User Access Token <span class="text-danger">*</span></label>
                    <div class="position-relative">
                        <textarea class="form-control font-monospace" name="meta_capi_access_token" id="metaCapiAccessToken" rows="4" placeholder="EAAB... (Paste your Meta Graph API System User Token here)" style="font-size: 13px;">{{ old('meta_capi_access_token', $settings['meta_capi_access_token'] ?? '') }}</textarea>
                    </div>
                    <div class="form-text small text-muted">Meta Business Settings > System Users থেকে জেনারেট করা কনভার্সন এপিআই অ্যাক্সেস টোকেন (কখনো মেয়াদোত্তীর্ণ হয় না এমন টোকেন সুপারিশ করা হয়)।</div>
                </div>

                <div class="mb-4">
                    <label for="metaCapiTestEventCode" class="form-label fw-bold">Meta CAPI Test Event Code <span class="text-muted fw-normal">(ঐচ্ছিক / টেস্টিংয়ের জন্য)</span></label>
                    <div class="input-group">
                        <span class="input-group-text bg-light text-muted"><i class="fa-solid fa-flask"></i></span>
                        <input type="text" class="form-control font-monospace" name="meta_capi_test_event_code" id="metaCapiTestEventCode" value="{{ old('meta_capi_test_event_code', $settings['meta_capi_test_event_code'] ?? '') }}" placeholder="e.g. TEST12345">
                    </div>
                    <div class="form-text small text-muted">ইভেন্টস ম্যানেজারের Test Events ট্যাব থেকে প্রাপ্ত কোড দিন। লাইভ অর্ডারের সময় এটি ফাঁকা রাখুন।</div>
                </div>

                <div class="d-flex justify-content-end gap-2 border-top pt-3">
                    <button type="submit" class="btn btn-primary px-4 py-2 fw-semibold d-inline-flex align-items-center gap-2">
                        <i class="fa-solid fa-floppy-disk"></i>
                        <span>সেটিংস সংরক্ষণ করুন</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="card p-3 p-md-4 mb-4">
            <h5 class="fw-bold mb-3 d-flex align-items-center gap-2">
                <i class="fa-solid fa-circle-info text-primary"></i>
                <span>CAPI সেটআপ নির্দেশনা</span>
            </h5>
            <ol class="small text-muted ps-3 mb-0 d-flex flex-column gap-2">
                <li><a href="https://business.facebook.com/events_manager2" target="_blank" class="fw-bold text-decoration-none">Meta Events Manager</a>-এ লগইন করে আপনার পিক্সেল সিলেক্ট করুন।</li>
                <li><strong>Settings</strong> ট্যাবে গিয়ে নিচে স্ক্রোল করে <strong>Conversions API</strong> সেকশনে যান।</li>
                <li><strong>Generate access token</strong> লিঙ্কে ক্লিক করে স্থায়ী অ্যাক্সেস টোকেন তৈরি করুন ও কপি করে এখানে পেস্ট করুন।</li>
                <li>ব্রাউজার এবং সার্ভার উভয় ইভেন্টেই স্বয়ংক্রিয়ভাবে একই <code>event_id</code> (যেমন: <code>order_202609170001</code>) পাঠানো হয়, যাতে মেটাতে কোনো ডুপ্লিকেট কাউন্ট না হয়।</li>
            </ol>
        </div>

        <div class="card p-3 p-md-4">
            <h5 class="fw-bold mb-3 d-flex align-items-center gap-2">
                <i class="fa-solid fa-rss text-warning"></i>
                <span>Facebook Catalog Product Feed</span>
            </h5>
            <p class="small text-muted mb-3">মেটা কমার্স ম্যানেজার ক্যাটালগের জন্য ডাইনামিক XML প্রোডাক্ট ফিড লিঙ্ক:</p>
            <div class="input-group mb-2">
                <input type="text" class="form-control font-monospace small" id="catalogFeedUrl" value="{{ url('/facebook-product-feed.xml') }}" readonly>
                <button class="btn btn-outline-secondary" type="button" onclick="navigator.clipboard.writeText(document.getElementById('catalogFeedUrl').value); this.innerText='Copied!'; setTimeout(() => this.innerText='Copy', 2000);">Copy</button>
            </div>
            <div class="small text-muted">Commerce Manager-এ গিয়ে Data Sources > Data Feed হিসেবে এই URL টি দিয়ে শিডিউল করুন।</div>
        </div>
    </div>
</div>
@endsection

