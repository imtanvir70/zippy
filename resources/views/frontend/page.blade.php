@extends('frontend.layouts.app')

@section('title', ($page->title ?? 'Page') . ' - ' . ($settings['store_name'] ?? 'ZippyBD'))

@section('content')
<div class="custom-page-wrapper py-4 py-md-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12 col-xl-9 col-lg-10">

                <!-- Breadcrumb Navigation -->
                <nav aria-label="breadcrumb" class="mb-3 mb-md-4">
                    <ol class="breadcrumb bg-transparent p-0 mb-0 align-items-center gap-1" style="font-size: 13.5px;">
                        <li class="breadcrumb-item">
                            <a href="{{ route('home') }}" class="text-decoration-none text-muted d-inline-flex align-items-center gap-1 hover-primary">
                                <i class="fa-solid fa-house-chimney" style="font-size: 12px;"></i> হোম
                            </a>
                        </li>
                        <li class="breadcrumb-item active text-dark fw-medium" aria-current="page">{{ $page->title }}</li>
                    </ol>
                </nav>

                <!-- Page Header Hero Card -->
                <div class="page-hero-card rounded-4 p-4 p-md-5 mb-4 position-relative overflow-hidden shadow-sm border border-slate-100">
                    <div class="hero-backdrop-glow"></div>
                    <div class="position-relative z-1">
                        <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
                            <span class="badge rounded-pill bg-primary-subtle text-primary fw-semibold px-3 py-2" style="font-size: 12px;">
                                <i class="fa-solid fa-file-shield me-1"></i> অফিশিয়াল পলিসি
                            </span>
                            @if(!empty($page->updated_at))
                                <span class="badge rounded-pill bg-light text-secondary border fw-normal px-3 py-2" style="font-size: 12px;">
                                    <i class="fa-regular fa-clock me-1"></i> আপডেট: {{ \Carbon\Carbon::parse($page->updated_at)->format('d M, Y') }}
                                </span>
                            @endif
                        </div>
                        <h1 class="page-main-title font-heading fw-bold text-dark m-0 mb-2">{{ $page->title }}</h1>
                        <p class="text-muted mb-0" style="font-size: 14.5px;">
                            {{ $settings['store_name'] ?? 'ZippyBD' }} এর অফিশিয়াল নির্দেশিকা ও তথ্যাবলী নিচে বিস্তারিত তুলে ধরা হলো।
                        </p>
                    </div>
                </div>

                <!-- Main Content Article Container -->
                <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 bg-white mb-4">
                    <article class="page-body-content">
                        {!! $page->content !!}
                    </article>

                    <!-- Quick Support Bar at end of content -->
                    <hr class="my-4 my-md-5 text-muted opacity-25">
                    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 p-3 p-md-4 rounded-3 bg-light border border-slate-100">
                        <div class="d-flex align-items-center gap-3">
                            <div class="support-avatar-icon bg-primary text-white rounded-circle d-flex align-items-center justify-content-center shadow-sm">
                                <i class="fa-solid fa-headset"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold text-dark mb-0 font-heading" style="font-size: 14.5px;">কোনো প্রশ্ন বা জিজ্ঞাসা আছে?</h6>
                                <span class="text-muted" style="font-size: 13px;">আমাদের সাপোর্ট টিম সবসময় আপনাকে সহায়তা করতে প্রস্তুত</span>
                            </div>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            @if(!empty($settings['store_whatsapp']))
                                @php
                                    $pageWa = preg_replace('/[^0-9]/', '', $settings['store_whatsapp']);
                                    if (strlen($pageWa) === 11 && str_starts_with($pageWa, '01')) {
                                        $pageWa = '88' . $pageWa;
                                    }
                                @endphp
                                <a href="https://wa.me/{{ $pageWa }}" target="_blank" class="btn btn-sm btn-success rounded-pill px-3 py-2 fw-semibold d-inline-flex align-items-center gap-2">
                                    <i class="fa-brands fa-whatsapp"></i> হোয়াটসঅ্যাপ
                                </a>
                            @endif
                            @if(!empty($settings['store_phone']))
                                <a href="tel:{{ $settings['store_phone'] }}" class="btn btn-sm btn-outline-dark rounded-pill px-3 py-2 fw-semibold d-inline-flex align-items-center gap-2">
                                    <i class="fa-solid fa-phone"></i> কল করুন
                                </a>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Back to Shopping Button -->
                <div class="text-center">
                    <a href="{{ route('home') }}" class="btn btn-light border rounded-pill px-4 py-2 text-secondary fw-semibold d-inline-flex align-items-center gap-2 hover-shadow">
                        <i class="fa-solid fa-arrow-left"></i> হোমপেজে ফিরে যান
                    </a>
                </div>

            </div>
        </div>
    </div>
</div>

<style>
/* Page Layout Styling */
.custom-page-wrapper {
    background-color: #f8fafc;
    min-height: 70vh;
}

.page-hero-card {
    background: linear-gradient(135deg, #ffffff 0%, #f1f5f9 100%);
    border: 1px solid #e2e8f0;
}

.hero-backdrop-glow {
    position: absolute;
    top: -50px;
    right: -50px;
    width: 220px;
    height: 220px;
    background: radial-gradient(circle, rgba(99, 102, 241, 0.12) 0%, rgba(255, 255, 255, 0) 70%);
    border-radius: 50%;
    pointer-events: none;
}

.page-main-title {
    font-size: 1.85rem;
    letter-spacing: -0.02em;
    line-height: 1.3;
}
@media (min-width: 768px) {
    .page-main-title {
        font-size: 2.25rem;
    }
}

.support-avatar-icon {
    width: 44px;
    height: 44px;
    font-size: 18px;
    flex-shrink: 0;
}

.hover-primary:hover {
    color: #2563eb !important;
}

.hover-shadow:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.06);
    background-color: #ffffff !important;
}

/* Rich Prose Typography for Quill Content */
.page-body-content {
    color: #334155;
    font-size: 1.05rem;
    line-height: 1.9;
    word-break: break-word;
}

.page-body-content p {
    margin-bottom: 1.35rem;
    color: #334155;
}

.page-body-content h1,
.page-body-content h2,
.page-body-content h3,
.page-body-content h4,
.page-body-content h5,
.page-body-content h6 {
    color: #0f172a;
    font-family: inherit;
    font-weight: 700;
    margin-top: 2rem;
    margin-bottom: 1rem;
    line-height: 1.4;
}

.page-body-content h1 { font-size: 1.75rem; border-bottom: 1px solid #e2e8f0; padding-bottom: 0.5rem; }
.page-body-content h2 { font-size: 1.5rem; border-bottom: 1px solid #e2e8f0; padding-bottom: 0.5rem; }
.page-body-content h3 { font-size: 1.25rem; }
.page-body-content h4 { font-size: 1.125rem; }

.page-body-content ul,
.page-body-content ol {
    margin-bottom: 1.5rem;
    padding-left: 1.5rem;
}

.page-body-content li {
    margin-bottom: 0.5rem;
    line-height: 1.8;
}

.page-body-content blockquote {
    margin: 1.5rem 0;
    padding: 1rem 1.25rem;
    background-color: #f8fafc;
    border-left: 4px solid #2563eb;
    border-radius: 0 0.5rem 0.5rem 0;
    font-style: italic;
    color: #475569;
}

.page-body-content blockquote p:last-child {
    margin-bottom: 0;
}

.page-body-content pre {
    background-color: #0f172a;
    color: #e2e8f0;
    padding: 1.25rem;
    border-radius: 0.75rem;
    font-size: 0.95rem;
    line-height: 1.6;
    overflow-x: auto;
    margin: 1.5rem 0;
    font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
}

.page-body-content img {
    max-width: 100%;
    height: auto;
    border-radius: 0.75rem;
    margin: 1.5rem 0;
    box-shadow: 0 4px 14px rgba(0,0,0,0.06);
}

.page-body-content a {
    color: #2563eb;
    text-decoration: underline;
    text-underline-offset: 3px;
    font-weight: 500;
}
.page-body-content a:hover {
    color: #1d4ed8;
}

.page-body-content table {
    width: 100%;
    margin: 1.5rem 0;
    border-collapse: collapse;
    font-size: 0.95rem;
}

.page-body-content table th,
.page-body-content table td {
    padding: 0.75rem 1rem;
    border: 1px solid #e2e8f0;
}

.page-body-content table th {
    background-color: #f1f5f9;
    font-weight: 600;
    color: #1e293b;
}
</style>
@endsection

