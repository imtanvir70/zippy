<div class="offcanvas offcanvas-bottom native-bottom-sheet bg-white p-0" tabindex="-1" id="categoryBottomSheet" aria-labelledby="categoryBottomSheetLabel" data-bs-scroll="false" data-bs-backdrop="true" style="height: 88vh; border-top-left-radius: 20px !important; border-top-right-radius: 20px !important; border: none !important; box-shadow: 0 -10px 40px rgba(0, 0, 0, 0.15) !important;z-index: 10585; overflow-x: hidden !important; max-width: 100vw !important; box-sizing: border-box !important;">
    <div class="sheet-drag-handle mt-2 mb-1 mx-auto rounded-pill" style="width: 40px; height: 4px; background-color: #cbd5e1;"></div>
    
    <div class="offcanvas-header pt-2 pb-2 px-3 border-bottom d-flex align-items-center justify-content-between flex-shrink-0" style="border-color: #f1f5f9 !important;">
        <div class="d-flex align-items-center gap-2">
            <h5 class="offcanvas-title fw-bold text-dark mb-0" id="categoryBottomSheetLabel" style="font-size: 15px;">সকল ক্যাটাগরি</h5>
        </div>
        <button type="button" class="btn-close shadow-none p-2" data-bs-dismiss="offcanvas" aria-label="Close" onclick="closeCategorySheet()" style="font-size: 12px;"></button>
    </div>

    <div class="offcanvas-body p-0 d-flex overflow-hidden" style="height: calc(88vh - 65px);">
        <div class="sheet-sidebar d-flex flex-column overflow-y-auto border-end" style="width: 76px; flex-shrink: 0; background-color: #f8fafc; border-color: #f1f5f9 !important; scrollbar-width: none;">
            <ul class="list-unstyled p-0 m-0 d-flex flex-column">
                @forelse($navCategories ?? [] as $index => $cat)
                    @php
                        $cSlug = $cat->slug ?? '';
                        $cName = $cat->name_bn ?? ($cat->name ?? 'ক্যাটাগরি');
                        $cImg  = $cat->image ?: asset('images/product-placeholder.svg');
                        $cUrl  = $cSlug ? route('category.show', $cSlug) : '#';
                    @endphp
                    <li>
                        <button type="button" 
                                class="sheet-cat-btn w-100 d-flex flex-column align-items-center pt-2.5 pb-2 px-1 border-0 text-center {{ $index === 0 ? 'sheet-cat-btn--active' : '' }}" 
                                data-cat-url="{{ $cUrl }}"
                                onclick="switchSheetCat('{{ $cSlug }}', this)">
                            <img src="{{ $cImg }}" alt="{{ $cName }}" class="object-fit-cover shadow-sm mb-1" width="34" height="34" style="width: 34px; height: 34px; border-radius: 8px;" decoding="async" loading="lazy" onerror="this.onerror=null; this.src='{{ asset('images/product-placeholder.svg') }}';">
                            <span class="sheet-cat-name w-100" style="font-size: 10px; font-weight: 500; line-height: 1.25; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; color: #64748b;">{{ $cName }}</span>
                        </button>
                    </li>
                @empty
                    <li class="p-3 text-center text-muted small">নেই</li>
                @endforelse
            </ul>
        </div>

        <div class="sheet-main-wrapper flex-grow-1 d-flex flex-column overflow-hidden bg-white">
            <div class="sheet-content-area flex-grow-1 p-2.5" style="scrollbar-width: thin; padding-bottom: 20px !important; overflow-y: auto; overflow-x: hidden !important; scrollbar-color: #cbd5e1 transparent;padding: 10px;">
                @forelse($navCategories ?? [] as $index => $cat)
                    @php
                        $cSlug = $cat->slug ?? '';
                        $cName = $cat->name_bn ?? ($cat->name ?? 'ক্যাটাগরি');
                        $cProds = $megaMenuProducts[$cSlug] ?? collect([]);
                        $top10Prods = collect($cProds)->take(10);
                    @endphp
                    <div class="sheet-cat-panel" id="sheetPanel-{{ $cSlug }}" style="display: {{ $index === 0 ? 'block' : 'none' }};">
                        <div class="pb-1 mb-2">
                            <h6 class="fw-bold text-dark mb-0.5" style="font-size: 14px;">{{ $cName }}</h6>
                            <span class="text-muted" style="font-size: 10.5px;">১০টি সেরা পণ্য</span>
                        </div>

                        <div class="row row-cols-2 g-2 mb-2">
                            @forelse($top10Prods as $prod)
                                @php
                                    $pId = is_object($prod) ? ($prod->id ?? 0) : ($prod['id'] ?? 0);
                                    $pTitle = is_object($prod) ? ($prod->title ?? '') : ($prod['title'] ?? '');
                                    if (empty($pTitle)) $pTitle = 'Product';
                                    $pSlug  = is_object($prod) ? ($prod->slug ?? '') : ($prod['slug'] ?? '');
                                    $pPrice = (float)(is_object($prod) ? ($prod->price ?? 0) : ($prod['price'] ?? 0));
                                    $pOldPrice = (float)(is_object($prod) ? ($prod->old_price ?? 0) : ($prod['old_price'] ?? 0));
                                    $pImg = is_object($prod) ? ($prod->main_image ?? '') : ($prod['main_image'] ?? '');
                                    $discountPercent = ($pOldPrice > $pPrice) ? round((($pOldPrice - $pPrice) / $pOldPrice) * 100) : 0;
                                    $fallback = asset('images/product-placeholder.svg');
                                    if (!$pImg || trim($pImg) === '' || str_contains($pImg, 'example.com')) {
                                        $pImg = $fallback;
                                    } elseif (str_contains($pImg, 'images.unsplash.com')) {
                                        $pImg = 'https://images.weserv.nl/?url=' . urlencode($pImg) . '&w=200&q=75&output=webp';
                                    }
                                @endphp
                                <div class="col">
                                    <a href="{{ $pSlug ? route('product.show', $pSlug) : '#' }}" class="sheet-prod-card text-decoration-none text-dark d-flex flex-column h-100" onclick="closeCategorySheet()">
                                        <div class="sheet-prod-img-box position-relative w-100">
                                            <img src="{{ $pImg }}" alt="{{ $pTitle }}" class="w-100 h-100 object-fit-cover" width="150" height="150" decoding="async" loading="lazy" onerror="this.onerror=null; this.src='{{ asset('images/product-placeholder.svg') }}';">
                                            @if($discountPercent > 0)
                                                <span class="sheet-prod-badge">-{{ $discountPercent }}%</span>
                                            @endif
                                        </div>
                                        <div class="sheet-prod-info d-flex flex-column flex-grow-1">
                                            <div class="sheet-prod-name mb-1" title="{{ $pTitle }}">{{ $pTitle }}</div>
                                            <div class="mt-auto d-flex align-items-baseline gap-1 pt-0.5">
                                                <span class="sheet-prod-price">৳{{ number_format($pPrice, 0) }}</span>
                                                @if($pOldPrice > $pPrice)
                                                    <span class="sheet-prod-oldprice">৳{{ number_format($pOldPrice, 0) }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            @empty
                                <div class="col-12 py-5 text-center text-muted small">
                                    <i class="fa-solid fa-box-open fs-3 mb-2 text-light"></i>
                                    <div>শীঘ্রই পণ্য আসছে</div>
                                </div>
                            @endforelse
                        </div>
                    </div>
                @empty
                    <div class="py-5 text-center text-muted">
                        <i class="fa-solid fa-box-open fs-2 mb-2"></i>
                        <div>কোনো ক্যাটাগরি পাওয়া যায়নি</div>
                    </div>
                @endforelse
            </div>

            @php
                $firstCat = collect($navCategories ?? [])->first();
                $firstSlug = $firstCat->slug ?? '';
                $firstUrl = $firstSlug ? route('category.show', $firstSlug) : '#';
            @endphp
            <div class="sheet-fixed-footer p-2.5 border-top bg-white flex-shrink-0" style="border-color: #f1f5f9 !important; box-shadow: 0 -4px 16px rgba(0, 0, 0, 0.05); z-index: 10;margin-bottom: 5px !important;">
                <a href="{{ $firstUrl }}" id="sheetFixedViewAllBtn" class="btn w-100 py-2 fw-bold d-flex align-items-center justify-content-center gap-2 shadow-sm" style="background-color: #0f172a; color: #fff; border-radius: 10px; font-size: 13px;width: 98% !important;margin: 0 auto !important;display: block !important;" onclick="closeCategorySheet()">
                    <span>সকল পণ্য দেখুন</span>
                    <i class="fa-solid fa-arrow-right" style="font-size: 11px;"></i>
                </a>
            </div>
        </div>
    </div>
</div>

<style>
.sheet-cat-btn {
    background-color: transparent;
    border-left: 3px solid transparent !important;
    transition: background-color 0.15s ease;
}
.sheet-cat-btn:hover {
    background-color: #f1f5f9;
}
.sheet-cat-btn--active {
    background-color: #ffffff !important;
    border-left: 3px solid #0f172a !important;
    margin-right: -1px;
    position: relative;
    z-index: 2;
}
.sheet-cat-btn--active .sheet-cat-name {
    color: #0f172a !important;
    font-weight: 700 !important;
}
.sheet-prod-card {
    background: #ffffff;
    border: 1px solid #eef2f6;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
    transition: transform 0.15s ease, box-shadow 0.15s ease;
}
.sheet-prod-card:active {
    transform: scale(0.98);
}
.sheet-prod-img-box {
    aspect-ratio: 1 / 1;
    width: 100%;
    background: #f8fafc;
    overflow: hidden;
    position: relative;
}
.sheet-prod-badge {
    position: absolute;
    top: 5px;
    left: 5px;
    background: #ef4444;
    color: #ffffff;
    font-size: 8.5px;
    font-weight: 700;
    padding: 2px 6px;
    border-radius: 4px;
    letter-spacing: 0.2px;
    z-index: 2;
}
.sheet-prod-info {
    padding: 7px 8px 8px;
}
.sheet-prod-name {
    font-size: 11px;
    font-weight: 500;
    line-height: 1.35;
    color: #1e293b;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    min-height: 29px;
}
.sheet-prod-price {
    font-size: 13px;
    font-weight: 700;
    color: #0f172a;
    letter-spacing: -0.2px;
}
.sheet-prod-oldprice {
    font-size: 9.5px;
    color: #94a3b8;
    text-decoration: line-through;
}
.sheet-cat-panel {
    animation: sheetPanelFadeIn 0.2s ease-out;
}
@keyframes sheetPanelFadeIn {
    from { opacity: 0; transform: translateX(5px); }
    to { opacity: 1; transform: translateX(0); }
}
</style>

<script data-turbo-eval="false">
function switchSheetCat(slug, btn) {
    document.querySelectorAll('.sheet-cat-btn').forEach(function(b) {
        b.classList.remove('sheet-cat-btn--active');
    });
    document.querySelectorAll('.sheet-cat-panel').forEach(function(p) {
        p.style.display = 'none';
    });
    btn.classList.add('sheet-cat-btn--active');
    var target = document.getElementById('sheetPanel-' + slug);
    if (target) {
        target.style.display = 'block';
        var contentArea = document.querySelector('.sheet-content-area');
        if (contentArea) {
            contentArea.scrollTop = 0;
        }
    }
    var fixedBtn = document.getElementById('sheetFixedViewAllBtn');
    if (fixedBtn && btn) {
        var catUrl = btn.getAttribute('data-cat-url');
        if (catUrl) {
            fixedBtn.setAttribute('href', catUrl);
        }
    }
}
</script>
