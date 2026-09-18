<div class="row row-cols-2 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 row-cols-xl-5 g-2 g-md-2.5 g-xl-3 mb-4" id="productsGrid">
    @forelse($products as $product)
        <div class="col">
            @include('frontend.inc.product-card', ['product' => $product])
        </div>
    @empty
        <div class="col-12 w-100">
            <div class="d-flex flex-column align-items-center justify-content-center text-center bg-white rounded-4 border shadow-xs py-5 px-3 w-100 my-2" style="min-height: 380px;">
                <div class="rounded-circle d-flex align-items-center justify-content-center mb-3 shadow-xs" style="width: 80px; height: 80px; background-color: #f1f5f9;">
                    <i class="fa-solid fa-box-open fs-2" style="color: #64748b;"></i>
                </div>
                <h5 class="fw-bold text-dark font-heading mb-1.5" style="font-size: 1.2rem;">কোনো পণ্য পাওয়া যায়নি</h5>
                <p class="text-secondary small mb-3.5 mx-auto" style="max-width: 340px; line-height: 1.6;">অন্য কোনো ফিল্টার বা ক্যাটাগরি ট্রাই করুন অথবা ফিল্টার রিসেট করুন।</p>
                <div class="d-flex flex-wrap justify-content-center gap-2">
                    <button type="button" class="btn btn-outline-dark btn-sm rounded-pill px-3.5 py-2 fw-semibold catalog-reset-filter-btn shadow-none">
                        <i class="fa-solid fa-rotate-left me-1.5"></i> ফিল্টার রিসেট করুন
                    </button>
                    <a href="{{ route('product.index') }}" class="btn btn-dark btn-sm rounded-pill px-3.5 py-2 fw-semibold shadow-none" style="background-color: #0f172a;">
                        <i class="fa-solid fa-boxes-stacked me-1.5"></i> সকল পণ্য দেখুন
                    </a>
                </div>
            </div>
        </div>
    @endforelse
</div>

@if($products->total() > 0)
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-3 pt-3 mt-4 pb-2 bg-white rounded-4 px-3 py-3 border shadow-xs">
        <!-- Left Side: Showing X to Y of Z results & Per-Page Selector -->
        <div class="d-flex flex-wrap align-items-center gap-3 text-secondary small">
            <span class="text-nowrap">
                Showing <strong class="text-dark">{{ $products->firstItem() ?? 0 }}</strong> to <strong class="text-dark">{{ $products->lastItem() ?? 0 }}</strong> of <strong class="text-dark">{{ $products->total() }}</strong> results
            </span>
            <div class="d-flex align-items-center gap-2 border-start ps-3 ms-md-1">
                <span class="text-secondary text-nowrap" style="font-size: 12px;">প্রতি পেজে:</span>
                <select name="per_page" class="form-select form-select-sm rounded-pill px-2.5 py-1 catalog-per-page-select shadow-none" style="width: auto; font-size: 12px; cursor: pointer;">
                    @foreach([15, 20, 30, 50] as $pp)
                        <option value="{{ $pp }}" {{ $products->perPage() == $pp ? 'selected' : '' }}>{{ $pp }} টি</option>
                    @endforeach
                </select>
            </div>
        </div>

        <!-- Right Side: Pagination Buttons -->
        <div class="catalog-pagination-wrap d-flex justify-content-center justify-content-md-end">
            @if($products->hasPages())
                {{ $products->appends(request()->query())->links('pagination::bootstrap-5') }}
            @endif
        </div>
    </div>
@endif

<style>
.catalog-pagination-wrap .pagination {
    margin-bottom: 0;
    gap: 4px;
    flex-wrap: wrap;
}
.catalog-pagination-wrap .page-item .page-link {
    border-radius: 8px !important;
    border: 1px solid #e2e8f0;
    color: #0f172a;
    font-weight: 600;
    font-size: 13px;
    padding: 6px 12px;
    transition: all 0.2s ease;
    box-shadow: none;
}
.catalog-pagination-wrap .page-item.active .page-link {
    background-color: #0f172a !important;
    border-color: #0f172a !important;
    color: #ffffff !important;
}
.catalog-pagination-wrap .page-item .page-link:hover {
    background-color: #f1f5f9;
    color: #0f172a;
}
.catalog-pagination-wrap .page-item.disabled .page-link {
    background-color: #f8fafc;
    color: #94a3b8;
    border-color: #f1f5f9;
}
</style>
