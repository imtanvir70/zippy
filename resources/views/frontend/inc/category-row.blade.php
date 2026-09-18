@php
    $catName = is_object($category) ? ($category->name_bn ?? ($category->name ?? '')) : (is_array($category) ? ($category['name_bn'] ?? ($category['name'] ?? '')) : '');
    $catSlug = is_object($category) ? ($category->slug ?? '') : (is_array($category) ? ($category['slug'] ?? '') : '');
@endphp

<section class="container py-2 py-md-3">
    <div class="d-flex justify-content-between align-items-center mb-2 mb-md-3">
        <h5 class="fw-bold mb-0 text-dark font-heading fs-6 fs-md-5">{{ $catName }}</h5>
        <a href="{{ route('category.show', $catSlug) }}" class="btn btn-outline-dark btn-sm rounded-pill px-3 py-1 fw-semibold text-decoration-none small">
            <span>সব দেখুন</span>
            <i class="fa-solid fa-chevron-right ms-1 small"></i>
        </a>
    </div>

    <div class="row zb-products-row g-2 g-md-3">
        @foreach($products as $product)
            <div class="col">
                @include('frontend.inc.product-card', ['product' => $product])
            </div>
        @endforeach
    </div>
</section>
