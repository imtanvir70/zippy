@php
    $rawCat           = request('category', []);
    $activeCategories = is_array($rawCat) ? $rawCat : (is_string($rawCat) ? array_filter(array_map('trim', explode(',', $rawCat))) : []);
    $activePriceRange = request('price_range', '');
    $activeMinPrice   = request('min_price', '');
    $activeMaxPrice   = request('max_price', '');
    $activeRating     = request('rating', '');
    $activeInStock    = request('in_stock', '');
    $activeDiscount   = request('has_discount', '');
    
    $hasActiveFilters = !empty($activeCategories) || !empty($activePriceRange) || !empty($activeMinPrice) || !empty($activeMaxPrice) || !empty($activeRating) || !empty($activeInStock) || !empty($activeDiscount);
@endphp

<!-- ==========================================
     1. DESKTOP STICKY SIDEBAR (>= 992px)
========================================== -->
<div class="bg-white rounded-4 border p-3.5 p-xl-4 shadow-sm position-sticky d-none d-lg-block catalog-sidebar-card" style="top: 80px; z-index: 10;">
    <!-- Sidebar Header -->
    <div class="d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom">
        <div class="d-flex align-items-center gap-2.5">
            <div class="d-flex align-items-center justify-content-center bg-dark text-white rounded-3 shadow-xs" style="width: 32px; height: 32px;">
                <i class="fa-solid fa-sliders" style="font-size: 13px;"></i>
            </div>
            <div>
                <h6 class="fw-bold text-dark font-heading mb-0" style="font-size: 15.5px; margin-left: 10px;">ফিল্টার ও বাছাই</h6>
            </div>
        </div>
        <button type="button" class="btn btn-sm text-danger fw-bold rounded-pill px-2.5 py-1 catalog-reset-filter-btn {{ $hasActiveFilters ? '' : 'd-none' }}" style="font-size: 12px; background-color: #fee2e2; border: 1px solid #fecaca;">
            <i class="fa-solid fa-rotate-left me-1"></i>রিসেট
        </button>
    </div>

    <form action="{{ url()->current() }}" method="GET" id="catalogFilterForm">
        @if(request('sort'))
            <input type="hidden" name="sort" value="{{ request('sort') }}">
        @endif
        @if(request('q'))
            <input type="hidden" name="q" value="{{ request('q') }}">
        @endif

        <!-- 1. Category Section -->
        @if(isset($filterCategories) && count($filterCategories) > 0)
            <div class="mb-4 pb-3 border-bottom">
                <div class="d-flex align-items-center justify-content-between mb-2 px-1">
                    <label class="form-label fw-bold text-dark font-heading mb-0" style="font-size: 14px;">ক্যাটাগরি</label>
                    <span class="badge bg-light text-secondary border rounded-pill px-2 py-0.5 fw-semibold" style="font-size: 11px;">{{ count($filterCategories) }}</span>
                </div>
                <div class="d-flex flex-column gap-1">
                    @foreach($filterCategories as $fCat)
                        @php
                            $isChecked = in_array($fCat->slug, $activeCategories) || (isset($category) && $category->slug === $fCat->slug);
                        @endphp
                        <label class="d-flex align-items-center justify-content-between px-2.5 py-1.5 rounded-2 cursor-pointer filter-option-item transition-all {{ $isChecked ? 'active-filter-item' : '' }}" style="cursor: pointer;">
                            <div class="d-flex align-items-center text-truncate me-2">
                                <input type="checkbox" 
                                       name="category[]" 
                                       value="{{ $fCat->slug }}" 
                                       class="custom-filter-check filter-field" 
                                       {{ $isChecked ? 'checked' : '' }}>
                                <span class="filter-item-name text-truncate" style="font-size: 13.5px; line-height: 1.4;">{{ $fCat->name_bn ?? ($fCat->name ?? '') }}</span>
                            </div>
                            @if(isset($fCat->products_count) && $fCat->products_count > 0)
                                <span class="filter-count-badge rounded-pill px-2 py-0.5 ms-auto flex-shrink-0">{{ $fCat->products_count }}</span>
                            @endif
                        </label>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- 2. Price Range Section -->
        <div class="mb-4 pb-3 border-bottom">
            <label class="form-label fw-bold text-dark font-heading mb-2.5 px-1" style="font-size: 14px;">মূল্য পরিসীমা (৳)</label>
            
            <!-- Quick Price Chips (2x2 Grid) -->
            <div class="row row-cols-2 g-2 mb-3">
                @php
                    $priceRanges = [
                        '0-1000'    => '৳০ - ৳১,০০০',
                        '1000-2500' => '৳১,০০০ - ৳২,৫০০',
                        '2500-5000' => '৳২,৫০০ - ৳৫,০০০',
                        '5000+'     => '৳৫,০০০+',
                    ];
                @endphp
                @foreach($priceRanges as $rKey => $rLabel)
                    <div class="col">
                        <label class="d-flex align-items-center justify-content-center p-2.5 rounded-2 border text-center cursor-pointer h-100 price-chip-item transition-all {{ $activePriceRange === $rKey ? 'active-price-chip' : '' }}" style="cursor: pointer;">
                            <input type="radio" 
                                   name="price_range" 
                                   value="{{ $rKey }}" 
                                   class="d-none filter-field"
                                   {{ $activePriceRange === $rKey ? 'checked' : '' }}>
                            <span class="fw-semibold" style="font-size: 12.5px;">{{ $rLabel }}</span>
                        </label>
                    </div>
                @endforeach
            </div>

            <!-- Custom Price Range Inputs -->
            <div class="d-flex align-items-center gap-2 px-0.5">
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0 text-secondary fw-bold px-2.5" style="font-size: 13px;">৳</span>
                    <input type="number" 
                           name="min_price" 
                           value="{{ $activeMinPrice }}" 
                           class="form-control rounded-end-2 shadow-none text-center py-2 fw-semibold hide-arrows" 
                           placeholder="সর্বনিম্ন" 
                           style="font-size: 13px;">
                </div>
                <span class="text-secondary fw-bold opacity-40">—</span>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0 text-secondary fw-bold px-2.5" style="font-size: 13px;">৳</span>
                    <input type="number" 
                           name="max_price" 
                           value="{{ $activeMaxPrice }}" 
                           class="form-control rounded-end-2 shadow-none text-center py-2 fw-semibold hide-arrows" 
                           placeholder="সর্বোচ্চ" 
                           style="font-size: 13px;">
                </div>
            </div>
        </div>

        <!-- 3. Rating Section -->
        <div class="mb-4 pb-3 border-bottom">
            <label class="form-label fw-bold text-dark font-heading mb-2.5 px-1" style="font-size: 14px;">কাস্টমার রেটিং</label>
            <div class="d-flex flex-column gap-2">
                @foreach(['4.5' => '৪.৫+ স্টার', '4.0' => '৪.০+ স্টার', '3.5' => '৩.৫+ স্টার'] as $rVal => $rText)
                    <label class="d-flex align-items-center justify-content-between px-3 py-2.5 rounded-3 border cursor-pointer rating-chip-item transition-all {{ (string)$activeRating === (string)$rVal ? 'active-rating-chip' : '' }}" style="cursor: pointer;">
                        <div class="d-flex align-items-center">
                            <input type="radio" 
                                   name="rating" 
                                   value="{{ $rVal }}" 
                                   class="custom-filter-radio filter-field"
                                   {{ (string)$activeRating === (string)$rVal ? 'checked' : '' }}>
                            <span class="d-inline-flex align-items-center gap-1.5 fw-semibold text-dark" style="font-size: 13.5px;">
                                <i class="fa-solid fa-star text-warning" style="font-size: 13px;"></i>
                                <span>{{ $rText }}</span>
                            </span>
                        </div>
                        <div class="d-flex align-items-center gap-0.5 text-warning" style="font-size: 11px;">
                            @for($i = 0; $i < (int)floor((float)$rVal); $i++)
                                <i class="fa-solid fa-star"></i>
                            @endfor
                            @if((float)$rVal - floor((float)$rVal) >= 0.5)
                                <i class="fa-solid fa-star-half-stroke"></i>
                            @endif
                        </div>
                    </label>
                @endforeach
            </div>
        </div>

        <!-- 4. Special Deals & In-Stock -->
        <div class="pb-3">
            <label class="form-label fw-bold text-dark font-heading mb-2.5 px-1" style="font-size: 14px;">বিশেষ সুবিধা ও প্রাপ্যতা</label>
            <div class="d-flex flex-column gap-2">
                <label class="d-flex align-items-center justify-content-between px-3 py-2.5 rounded-3 border cursor-pointer special-deal-card transition-all {{ $activeInStock == '1' ? 'active-deal-card' : '' }}" style="cursor: pointer;">
                    <div class="d-flex align-items-center gap-2.5">
                        <div class="d-flex align-items-center justify-content-center rounded-circle flex-shrink-0" style="width: 30px; height: 30px; background-color: #ecfdf5; color: #10b981;">
                            <i class="fa-solid fa-circle-check" style="font-size: 15px;"></i>
                        </div>
                        <span class="fw-semibold text-dark" style="font-size: 13.5px;">শুধুমাত্র ইন-স্টক পণ্য</span>
                    </div>
                    <input type="checkbox" 
                           name="in_stock" 
                           value="1" 
                           class="custom-filter-check filter-field m-0" 
                           {{ $activeInStock == '1' ? 'checked' : '' }}>
                </label>

                <label class="d-flex align-items-center justify-content-between px-3 py-2.5 rounded-3 border cursor-pointer special-deal-card transition-all {{ $activeDiscount == '1' ? 'active-deal-card' : '' }}" style="cursor: pointer;">
                    <div class="d-flex align-items-center gap-2.5">
                        <div class="d-flex align-items-center justify-content-center rounded-circle flex-shrink-0" style="width: 30px; height: 30px; background-color: #fffbeb; color: #f59e0b;">
                            <i class="fa-solid fa-bolt" style="font-size: 14px;"></i>
                        </div>
                        <span class="fw-semibold text-dark" style="font-size: 13.5px;">ডিসকাউন্ট ও অফার</span>
                    </div>
                    <input type="checkbox" 
                           name="has_discount" 
                           value="1" 
                           class="custom-filter-check filter-field m-0" 
                           {{ $activeDiscount == '1' ? 'checked' : '' }}>
                </label>
            </div>
        </div>
    </form>
</div>

<!-- ==========================================
     2. MOBILE FLOATING ACTION PILL (< 992px)
========================================== -->
<div class="position-fixed start-50 translate-middle-x d-lg-none mobile-floating-filter-wrap" style="bottom: 74px; z-index: 1040;">
    <div class="snake-border-wrapper">
        <span class="snake-border-glow"></span>
        <button type="button" 
                class="btn rounded-pill px-4 py-2.5 d-flex align-items-center gap-2 font-heading text-white mobile-floating-filter-btn"
                data-bs-toggle="offcanvas" 
                data-bs-target="#mobileFilterOffcanvas" 
                aria-controls="mobileFilterOffcanvas">
            <i class="fa-solid fa-sliders text-warning"></i>
            <span class="fw-bold">ফিল্টার</span>
            <span class="badge bg-danger rounded-pill px-2 py-0.5 catalog-active-filter-badge {{ $hasActiveFilters ? '' : 'd-none' }}" style="font-size: 10px;">সক্রিয়</span>
        </button>
    </div>
</div>

<!-- ==========================================
     3. MOBILE FILTER BOTTOM SHEET (Native Zippy App Style)
========================================== -->
<div class="offcanvas offcanvas-bottom native-filter-bottom-sheet bg-white p-0 d-lg-none" tabindex="-1" id="mobileFilterOffcanvas" aria-labelledby="mobileFilterOffcanvasLabel" data-bs-scroll="false" data-bs-backdrop="true" style="height: 88vh; border-top-left-radius: 20px !important; border-top-right-radius: 20px !important; border: none !important; box-shadow: 0 -10px 40px rgba(0, 0, 0, 0.15) !important;">
    <!-- iOS / Android Style Pull Indicator -->
    <div class="sheet-drag-handle mt-2.5 mb-1 mx-auto rounded-pill" style="width: 40px; height: 4px; background-color: #cbd5e1;"></div>
    
    <!-- Sheet Header -->
    <div class="offcanvas-header pt-2 pb-2.5 px-4 border-bottom d-flex align-items-center justify-content-between flex-shrink-0">
        <div class="d-flex align-items-center gap-2">
            <i class="fa-solid fa-sliders text-dark fs-6"></i>
            <h5 class="offcanvas-title fw-bold text-dark font-heading mb-0" id="mobileFilterOffcanvasLabel" style="font-size: 16px; margin-left: 10px;">ফিল্টার ও বাছাই</h5>
            <span class="badge bg-danger rounded-pill px-2 py-0.5 catalog-active-filter-badge ms-2 {{ $hasActiveFilters ? '' : 'd-none' }}" style="font-size: 10px;">সক্রিয়</span>
        </div>
        <div class="d-flex align-items-center gap-2">
            <button type="button" class="btn btn-sm btn-light text-danger border-0 py-1 px-2.5 rounded-pill fw-bold text-decoration-none catalog-reset-filter-btn {{ $hasActiveFilters ? '' : 'd-none' }}" style="font-size: 12px; background-color: #fee2e2;">
                <i class="fa-solid fa-rotate-left me-1"></i>রিসেট
            </button>
            <button type="button" class="btn-close shadow-none p-2" data-bs-dismiss="offcanvas" aria-label="Close" style="font-size: 12px;"></button>
        </div>
    </div>

    <!-- Sheet Body -->
    <div class="offcanvas-body p-4 overflow-y-auto bg-white" style="scrollbar-width: thin; height: calc(88vh - 130px);">
        <form action="{{ url()->current() }}" method="GET" id="mobileCatalogFilterForm">
            @if(request('sort'))
                <input type="hidden" name="sort" value="{{ request('sort') }}">
            @endif
            @if(request('q'))
                <input type="hidden" name="q" value="{{ request('q') }}">
            @endif

            <!-- 1. Category Section -->
            @if(isset($filterCategories) && count($filterCategories) > 0)
                <div class="mb-4 pb-3 border-bottom">
                    <div class="d-flex align-items-center justify-content-between mb-2 px-1">
                        <label class="form-label fw-bold text-dark font-heading mb-0" style="font-size: 14.5px;">ক্যাটাগরি</label>
                        <span class="badge bg-light text-secondary border rounded-pill px-2 py-0.5" style="font-size: 11px;">{{ count($filterCategories) }}</span>
                    </div>
                    <div class="d-flex flex-column gap-1">
                        @foreach($filterCategories as $fCat)
                            @php
                                $isChecked = in_array($fCat->slug, $activeCategories) || (isset($category) && $category->slug === $fCat->slug);
                            @endphp
                            <label class="d-flex align-items-center justify-content-between px-2.5 py-1.5 rounded-2 cursor-pointer filter-option-item transition-all {{ $isChecked ? 'active-filter-item' : '' }}" style="cursor: pointer;">
                                <div class="d-flex align-items-center text-truncate me-2">
                                    <input type="checkbox" 
                                           name="category[]" 
                                           value="{{ $fCat->slug }}" 
                                           class="custom-filter-check filter-field" 
                                           {{ $isChecked ? 'checked' : '' }}>
                                    <span class="filter-item-name text-truncate" style="font-size: 14px; line-height: 1.4;">{{ $fCat->name_bn ?? ($fCat->name ?? '') }}</span>
                                </div>
                                @if(isset($fCat->products_count) && $fCat->products_count > 0)
                                    <span class="filter-count-badge rounded-pill px-2.5 py-0.5 ms-auto flex-shrink-0">{{ $fCat->products_count }}</span>
                                @endif
                            </label>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- 2. Price Range Section -->
            <div class="mb-4 pb-3 border-bottom">
                <label class="form-label fw-bold text-dark font-heading mb-2.5 px-1" style="font-size: 14.5px;">মূল্য পরিসীমা (৳)</label>
                
                <!-- Quick Price Pills (2x2 Grid) -->
                <div class="row row-cols-2 g-2 mb-3">
                    @foreach($priceRanges as $rKey => $rLabel)
                        <div class="col">
                            <label class="d-flex align-items-center justify-content-center p-2.5 rounded-2 border text-center cursor-pointer h-100 price-chip-item transition-all {{ $activePriceRange === $rKey ? 'active-price-chip' : '' }}" style="cursor: pointer;">
                                <input type="radio" 
                                       name="price_range" 
                                       value="{{ $rKey }}" 
                                       class="d-none filter-field"
                                       {{ $activePriceRange === $rKey ? 'checked' : '' }}>
                                <span class="fw-semibold" style="font-size: 13px;">{{ $rLabel }}</span>
                            </label>
                        </div>
                    @endforeach
                </div>

                <!-- Custom Range Inputs -->
                <div class="d-flex align-items-center gap-2">
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0 text-secondary fw-bold px-2.5" style="font-size: 13px;">৳</span>
                        <input type="number" 
                               name="min_price" 
                               value="{{ $activeMinPrice }}" 
                               class="form-control rounded-end-2 shadow-none text-center py-2 fw-semibold hide-arrows" 
                               placeholder="সর্বনিম্ন" 
                               style="font-size: 13.5px;">
                    </div>
                    <span class="text-secondary fw-bold opacity-40">—</span>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0 text-secondary fw-bold px-2.5" style="font-size: 13px;">৳</span>
                        <input type="number" 
                               name="max_price" 
                               value="{{ $activeMaxPrice }}" 
                               class="form-control rounded-end-2 shadow-none text-center py-2 fw-semibold hide-arrows" 
                               placeholder="সর্বোচ্চ" 
                               style="font-size: 13.5px;">
                    </div>
                </div>
            </div>

            <!-- 3. Rating Section -->
            <div class="mb-4 pb-3 border-bottom">
                <label class="form-label fw-bold text-dark font-heading mb-2.5 px-1" style="font-size: 14.5px;">কাস্টমার রেটিং</label>
                <div class="d-flex flex-column gap-2">
                    @foreach(['4.5' => '৪.৫+ স্টার', '4.0' => '৪.০+ স্টার', '3.5' => '৩.৫+ স্টার'] as $rVal => $rText)
                        <label class="d-flex align-items-center justify-content-between px-3 py-2.5 rounded-3 border cursor-pointer rating-chip-item transition-all {{ (string)$activeRating === (string)$rVal ? 'active-rating-chip' : '' }}" style="cursor: pointer;">
                            <div class="d-flex align-items-center">
                                <input type="radio" 
                                       name="rating" 
                                       value="{{ $rVal }}" 
                                       class="custom-filter-radio filter-field"
                                       {{ (string)$activeRating === (string)$rVal ? 'checked' : '' }}>
                                <span class="d-inline-flex align-items-center gap-1.5 fw-semibold text-dark" style="font-size: 14px;">
                                    <i class="fa-solid fa-star text-warning" style="font-size: 13px;"></i>
                                    <span>{{ $rText }}</span>
                                </span>
                            </div>
                            <div class="d-flex align-items-center gap-0.5 text-warning" style="font-size: 11px;">
                                @for($i = 0; $i < (int)floor((float)$rVal); $i++)
                                    <i class="fa-solid fa-star"></i>
                                @endfor
                                @if((float)$rVal - floor((float)$rVal) >= 0.5)
                                    <i class="fa-solid fa-star-half-stroke"></i>
                                @endif
                            </div>
                        </label>
                    @endforeach
                </div>
            </div>

            <!-- 4. Special Deals & In-Stock -->
            <div class="mb-2">
                <label class="form-label fw-bold text-dark font-heading mb-2.5 px-1" style="font-size: 14.5px;">বিশেষ সুবিধা ও প্রাপ্যতা</label>
                <div class="d-flex flex-column gap-2">
                    <label class="d-flex align-items-center justify-content-between px-3 py-2.5 rounded-3 border cursor-pointer special-deal-card transition-all {{ $activeInStock == '1' ? 'active-deal-card' : '' }}" style="cursor: pointer;">
                        <div class="d-flex align-items-center gap-2.5">
                            <div class="d-flex align-items-center justify-content-center rounded-circle flex-shrink-0" style="width: 30px; height: 30px; background-color: #ecfdf5; color: #10b981;">
                                <i class="fa-solid fa-circle-check" style="font-size: 15px;"></i>
                            </div>
                            <span class="fw-semibold text-dark" style="font-size: 14px;">শুধুমাত্র ইন-স্টক পণ্য</span>
                        </div>
                        <input type="checkbox" 
                               name="in_stock" 
                               value="1" 
                               class="custom-filter-check filter-field m-0" 
                               {{ $activeInStock == '1' ? 'checked' : '' }}>
                    </label>

                    <label class="d-flex align-items-center justify-content-between px-3 py-2.5 rounded-3 border cursor-pointer special-deal-card transition-all {{ $activeDiscount == '1' ? 'active-deal-card' : '' }}" style="cursor: pointer;">
                        <div class="d-flex align-items-center gap-2.5">
                            <div class="d-flex align-items-center justify-content-center rounded-circle flex-shrink-0" style="width: 30px; height: 30px; background-color: #fffbeb; color: #f59e0b;">
                                <i class="fa-solid fa-bolt" style="font-size: 14px;"></i>
                            </div>
                            <span class="fw-semibold text-dark" style="font-size: 14px;">ডিসকাউন্ট ও অফার</span>
                        </div>
                        <input type="checkbox" 
                               name="has_discount" 
                               value="1" 
                               class="custom-filter-check filter-field m-0" 
                               {{ $activeDiscount == '1' ? 'checked' : '' }}>
                    </label>
                </div>
            </div>
        </form>
    </div>

    <!-- Sheet Footer -->
    <div class="offcanvas-footer border-top p-3 px-4 bg-white mt-auto d-flex align-items-center gap-2 flex-shrink-0">
        <button type="button" class="btn btn-outline-secondary btn-sm py-2.5 px-3 rounded-3 fw-bold catalog-reset-filter-btn {{ $hasActiveFilters ? '' : 'd-none' }}" style="font-size: 13.5px; min-width: 80px;">
            রিসেট
        </button>
        <button type="button" class="btn flex-grow-1 py-2.5 fw-bold rounded-3 d-flex align-items-center justify-content-center gap-2 shadow-sm font-heading" style="background-color: #0f172a; color: #fff; font-size: 14.5px;" data-bs-dismiss="offcanvas">
            <span>ফিল্টার প্রয়োগ করুন</span>
            <i class="fa-solid fa-check" style="font-size: 13px;"></i>
        </button>
    </div>
</div>

<style>
/* ===================================================
   FUTURE-PROOF BESPOKE FILTER COMPONENT STYLES
=================================================== */

/* Card Styling */
.catalog-sidebar-card {
    border-color: #e2e8f0 !important;
    border-radius: 20px !important;
    box-shadow: 0 10px 30px -5px rgba(0, 0, 0, 0.05) !important;
    scrollbar-gutter: stable;
}

/* 1. Custom Checkbox Design (Uniform Across All Devices) */
.custom-filter-check {
    appearance: none;
    -webkit-appearance: none;
    width: 19px !important;
    height: 19px !important;
    border: 1.5px solid #cbd5e1;
    border-radius: 6px;
    background-color: #ffffff;
    display: inline-grid;
    place-content: center;
    margin: 0 12px 0 0 !important;
    cursor: pointer;
    flex-shrink: 0 !important;
    position: relative;
    transition: all 0.18s cubic-bezier(0.16, 1, 0.3, 1);
}
.custom-filter-check:hover {
    border-color: #94a3b8;
    background-color: #f8fafc;
}
.custom-filter-check:checked {
    background-color: #0f172a !important;
    border-color: #0f172a !important;
    box-shadow: 0 2px 6px rgba(15, 23, 42, 0.2);
}
.custom-filter-check:checked::before {
    content: "";
    width: 9px;
    height: 5px;
    border-left: 2px solid #ffffff;
    border-bottom: 2px solid #ffffff;
    transform: rotate(-45deg) translate(1px, -1px);
    box-sizing: border-box;
}

/* 2. Custom Radio Design (Uniform Across All Devices) */
.custom-filter-radio {
    appearance: none;
    -webkit-appearance: none;
    width: 18px !important;
    height: 18px !important;
    border: 1.5px solid #cbd5e1;
    border-radius: 50%;
    background-color: #ffffff;
    display: inline-grid;
    place-content: center;
    margin: 0 12px 0 0 !important;
    cursor: pointer;
    flex-shrink: 0 !important;
    position: relative;
    transition: all 0.18s cubic-bezier(0.16, 1, 0.3, 1);
}
.custom-filter-radio:hover {
    border-color: #94a3b8;
}
.custom-filter-radio:checked {
    border-color: #0f172a !important;
    background-color: #ffffff !important;
    box-shadow: 0 0 0 1px #0f172a;
}
.custom-filter-radio:checked::before {
    content: "";
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background-color: #0f172a;
}

/* 3. Filter Option Item (Category) */
.filter-option-item {
    border: 1px solid transparent;
    background-color: transparent;
    padding: 6px 10px !important;
    margin: 1px 0 !important;
    border-radius: 8px !important;
}
.filter-option-item:hover {
    background-color: #f8fafc;
}
.filter-option-item:has(input:checked),
.filter-option-item.active-filter-item {
    background-color: #f8fafc;
    border-color: #e2e8f0;
}
.filter-option-item .filter-item-name {
    color: #334155;
    font-weight: 500;
}
.filter-option-item:has(input:checked) .filter-item-name,
.filter-option-item.active-filter-item .filter-item-name {
    color: #0f172a;
    font-weight: 700;
}
.filter-count-badge {
    background-color: #f1f5f9;
    color: #64748b;
    border: 1px solid #e2e8f0;
    font-size: 11px;
    font-weight: 600;
    padding: 2px 8px !important;
}
.filter-option-item:has(input:checked) .filter-count-badge,
.filter-option-item.active-filter-item .filter-count-badge {
    background-color: #0f172a;
    color: #ffffff;
    border-color: #0f172a;
}

/* 4. Price Chips */
.price-chip-item {
    background-color: #f8fafc;
    border-color: #e2e8f0 !important;
    color: #475569;
    padding: 10px !important;
}
.price-chip-item:hover {
    background-color: #f1f5f9;
    color: #0f172a;
    border-color: #cbd5e1 !important;
}
.price-chip-item:has(input:checked),
.price-chip-item.active-price-chip {
    background-color: #0f172a !important;
    color: #ffffff !important;
    border-color: #0f172a !important;
    box-shadow: 0 4px 12px rgba(15, 23, 42, 0.15);
}

/* 5. Rating Chips */
.rating-chip-item {
    background-color: #ffffff;
    border-color: #e2e8f0 !important;
    color: #334155;
    padding: 10px 14px !important;
}
.rating-chip-item:hover {
    background-color: #f8fafc;
}
.rating-chip-item:has(input:checked),
.rating-chip-item.active-rating-chip {
    background-color: #f8fafc;
    border-color: #0f172a !important;
    box-shadow: 0 0 0 1px #0f172a;
}

/* 6. Special Deal Cards */
.special-deal-card {
    background-color: #ffffff;
    border-color: #e2e8f0 !important;
}
.special-deal-card:hover {
    background-color: #f8fafc;
}
.special-deal-card:has(input:checked),
.special-deal-card.active-deal-card {
    background-color: #f8fafc;
    border-color: #0f172a !important;
}

/* Custom Scrollbar */
.custom-scrollbar {
    scrollbar-width: thin;
    scrollbar-color: #cbd5e1 transparent;
    scroll-behavior: smooth;
}
.custom-scrollbar::-webkit-scrollbar {
    width: 5px;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background-color: #cbd5e1;
    border-radius: 6px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background-color: #94a3b8;
}

/* Hide number input spinners */
.hide-arrows::-webkit-inner-spin-button, 
.hide-arrows::-webkit-outer-spin-button { 
    -webkit-appearance: none; 
    margin: 0; 
}
.hide-arrows {
    -moz-appearance: textfield;
}

/* Mobile Floating Filter Pill */
.mobile-floating-filter-wrap {
    animation: floatIn 0.35s cubic-bezier(0.16, 1, 0.3, 1);
}
@keyframes floatIn {
    from {
        opacity: 0;
        transform: translate(-50%, 20px);
    }
    to {
        opacity: 1;
        transform: translate(-50%, 0);
    }
}
.mobile-floating-filter-wrap button {
    transition: transform 0.2s cubic-bezier(0.34, 1.56, 0.64, 1), box-shadow 0.2s ease;
}
.mobile-floating-filter-wrap button:active {
    transform: scale(0.94);
}

/* ===================================================
   PERFECT OUTER SNAKE BORDER ANIMATION & BUTTON GLASS
=================================================== */
.snake-border-wrapper {
    position: relative;
    padding: 2px;
    border-radius: 9999px;
    overflow: hidden;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}

.snake-border-wrapper .snake-border-glow {
    position: absolute;
    top: -50%;
    left: -50%;
    width: 200%;
    height: 200%;
    background: conic-gradient(from 0deg, transparent 65%, rgba(56, 189, 248, 0.2) 75%, #38bdf8 88%, #818cf8 94%, #c084fc 100%);
    animation: snakeBorderSpin 2.8s linear infinite;
    pointer-events: none;
    z-index: 0;
}

@keyframes snakeBorderSpin {
    0% {
        transform: rotate(0deg);
    }
    100% {
        transform: rotate(360deg);
    }
}

.mobile-floating-filter-btn {
    position: relative;
    background: #0f172a82 !important;
    box-shadow: none !important;
    font-size: 14px !important;
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
    border: none !important;
    border-radius: 9999px !important;
    z-index: 1;
    cursor: pointer;
}
body.offcanvas-open-locked,
body.scroll-locked {
    overflow: hidden !important;
    touch-action: none !important;
}
</style>

