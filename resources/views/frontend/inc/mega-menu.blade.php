<div class="mega-menu-dropdown position-absolute top-100 start-0 bg-white rounded-4 shadow-lg border p-0" id="megaMenuDropdown" style="width: min(1080px, calc(100vw - 32px)); max-width: calc(100vw - 32px); z-index: 1060; overflow: hidden; box-shadow: 0 25px 60px -15px rgba(0, 0, 0, 0.25) !important;">
    <div class="p-3 d-flex gap-3 align-items-stretch">
        <div class="bg-light rounded-4 border flex-shrink-0 d-flex flex-column overflow-hidden" style="width: 260px; height: 420px;">
            <div class="d-flex align-items-center justify-content-between px-3 py-3 bg-light border-bottom flex-shrink-0">
                <div class="d-flex align-items-center gap-2">
                    <i class="fa-solid fa-layer-group text-primary fs-6"></i>
                    <span class="fw-bold text-dark" style="font-size: 13.5px;">ক্যাটাগরি ব্রাউজ</span>
                </div>
                <span class="badge bg-white text-dark border rounded-pill px-2.5 py-1 fw-bold" style="font-size: 11px;">{{ is_countable($navCategories ?? null) ? count($navCategories) : 0 }}</span>
            </div>
            
            <ul class="list-unstyled p-2 m-0 d-flex flex-column gap-1.5 flex-grow-1 overflow-auto custom-mega-scrollbar pe-1" id="megaCatList">
                @forelse($navCategories ?? [] as $index => $cat)
                    @php
                        $cSlug = $cat->slug ?? '';
                        $cName = $cat->name_bn ?? ($cat->name ?? 'ক্যাটাগরি');
                        $cIcon = $cat->icon ?? 'fa-layer-group';
                        if (!str_starts_with($cIcon, 'fa-') && !str_starts_with($cIcon, 'fa ')) {
                            $cIcon = 'fa-solid fa-' . $cIcon;
                        } elseif (!str_starts_with($cIcon, 'fa-solid') && !str_starts_with($cIcon, 'fa-brands') && !str_starts_with($cIcon, 'fa-regular')) {
                            $cIcon = 'fa-solid ' . $cIcon;
                        }
                    @endphp
                    <li class="mega-cat-item {{ $index === 0 ? 'active-cat' : '' }} rounded-3" 
                        data-cat-slug="{{ $cSlug }}" 
                        onmouseenter="activateMegaTab('{{ $cSlug }}')">
                        <a href="{{ route('category.show', $cSlug) }}" class="d-flex align-items-center justify-content-between px-3 py-2 text-decoration-none w-100">
                            <span class="d-flex align-items-center gap-2.5 text-truncate pe-1">
                                <i class="{{ $cIcon }} cat-icon flex-shrink-0" style="width: 16px; font-size: 13px;"></i>
                                <span class="cat-label text-truncate" style="font-size: 12.5px;">{{ $cName }}</span>
                            </span>
                            <i class="fa-solid fa-chevron-right cat-chevron flex-shrink-0" style="font-size: 9.5px;"></i>
                        </a>
                    </li>
                @empty
                    <li class="p-3 text-secondary small text-center">কোনো ক্যাটাগরি নেই</li>
                @endforelse
            </ul>
        </div>

        <div class="flex-grow-1 min-w-0 p-2 d-flex flex-column rounded-4 bg-white border" style="height: 420px; overflow-y: auto;">
            @forelse($navCategories ?? [] as $index => $cat)
                @php
                    $cSlug = $cat->slug ?? '';
                    $cName = $cat->name_bn ?? ($cat->name ?? 'ক্যাটাগরি');
                    $cProds = $megaMenuProducts[$cSlug] ?? [];
                    $totalCount = (int)($cat->products_count ?? count($cProds));
                @endphp
                <div class="mega-tab-panel flex-column custom-mega-scrollbar" id="megaPanel-{{ $cSlug }}" style="display: {{ $index === 0 ? 'flex' : 'none' }};">
                    <div class="d-flex align-items-center justify-content-between px-2 pt-1 pb-3 border-bottom mb-3 flex-shrink-0">
                        <div class="d-flex align-items-center gap-2">
                            <h6 class="fw-bold text-dark mb-0 font-heading" style="font-size: 14.5px;">{{ $cName }} - ফিচারড প্রোডাক্টস</h6>
                            <span class="badge bg-dark rounded-pill" style="font-size: 10px; padding: 3px 9px;">{{ $totalCount }} টি পণ্য</span>
                        </div>
                        <a href="{{ $cSlug ? route('category.show', $cSlug) : '#' }}" class="small fw-bold text-dark text-decoration-none d-flex align-items-center gap-1.5">
                            <span>সকল পণ্য দেখুন</span>
                            <i class="fa-solid fa-arrow-right" style="font-size: 10px;"></i>
                        </a>
                    </div>

                    @if(!empty($cat->subs) && count($cat->subs) > 0)
                        <div class="px-2 mb-3 flex-shrink-0">
                            <div class="d-flex flex-wrap gap-1.5 align-items-center">
                                @foreach($cat->subs as $sCat)
                                    <a href="{{ route('category.show', $sCat->slug) }}" class="badge bg-light text-dark border rounded-pill px-2.5 py-1 text-decoration-none" style="font-size: 11px; font-weight: 500;">
                                        <i class="fa-solid fa-angle-right text-primary me-1" style="font-size: 9px;"></i>{{ $sCat->name_bn ?? $sCat->name }}
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <div class="mega-products-grid px-1 pb-2">
                        @forelse(collect($cProds)->take(4) as $prod)
                            @php
                                $pPrice = (float)($prod->price ?? 0);
                                $pOldPrice = (float)($prod->old_price ?? 0);
                                $discountPercent = ($pOldPrice > $pPrice) ? round((($pOldPrice - $pPrice) / $pOldPrice) * 100) : 0;
                                $mImg = ($prod->main_image && !str_contains($prod->main_image, 'example.com')) ? $prod->main_image : asset('images/product-placeholder.svg');
                                if (str_contains($mImg, 'images.unsplash.com')) {
                                    $mImg = preg_replace('/([?&]w=)\d+/', '${1}360', $mImg);
                                    $mImg = preg_replace('/([?&]q=)\d+/', '${1}70', $mImg);
                                    if (!str_contains($mImg, 'w=')) {
                                        $mImg .= (str_contains($mImg, '?') ? '&' : '?') . 'w=360&q=70&fm=webp';
                                    } elseif (!str_contains($mImg, 'fm=webp')) {
                                        $mImg .= '&fm=webp';
                                    }
                                }
                            @endphp
                            <a href="{{ !empty($prod->slug) ? route('product.show', $prod->slug) : '#' }}" class="mega-prod-card rounded-3 bg-white border text-decoration-none shadow-2xs d-flex flex-column">
                                <div class="position-relative overflow-hidden rounded-2 bg-light mb-2 w-100 flex-shrink-0" style="padding-top: 100%;">
                                    <img src="{{ $mImg }}" alt="{{ $prod->title }}" class="position-absolute top-0 start-0 w-100 h-100 object-fit-cover" width="160" height="160" decoding="async" loading="lazy">
                                    @if($discountPercent > 0)
                                        <span class="position-absolute top-0 end-0 m-1.5 badge bg-danger text-white fw-bold shadow-xs" style="font-size: 9px; padding: 2px 5px; border-radius: 4px; z-index: 2;">
                                            -{{ $discountPercent }}%
                                        </span>
                                    @endif
                                </div>
                                <div class="fw-semibold text-dark mb-1 px-1" style="font-size: 11.5px; line-height: 1.35; height: 31px; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;" title="{{ $prod->title }}">
                                    {{ $prod->title }}
                                </div>
                                <div class="d-flex align-items-baseline gap-1 mt-auto px-1">
                                    <span class="fw-bold text-dark font-heading" style="font-size: 12px;">৳ {{ number_format($pPrice, 0) }}</span>
                                    @if($pOldPrice > $pPrice)
                                        <span class="text-secondary text-decoration-line-through small" style="font-size: 10px;">৳ {{ number_format($pOldPrice, 0) }}</span>
                                    @endif
                                </div>
                            </a>
                        @empty
                            <div class="py-5 text-center text-secondary w-100">
                                <i class="fa-solid fa-box-open fs-3 mb-2"></i>
                                <div class="small">শীঘ্রই নতুন পণ্য যুক্ত হচ্ছে</div>
                            </div>
                        @endforelse
                    </div>
                </div>
            @empty
            @endforelse
        </div>
    </div>

    <div class="border-top bg-light py-3 px-4 d-flex justify-content-around align-items-center flex-wrap small text-muted" style="font-size: 11.5px;">
        <div class="d-flex align-items-center gap-2 py-1">
            <i class="fa-solid fa-bolt text-warning fs-6"></i>
            <span class="text-secondary fw-semibold">সুপারফাস্ট ডেলিভারি</span>
        </div>
        <div class="d-flex align-items-center gap-2 py-1">
            <i class="fa-solid fa-rotate-left text-info fs-6"></i>
            <span class="text-secondary fw-semibold">৭ দিনের ইজি রিটার্ন</span>
        </div>
        <div class="d-flex align-items-center gap-2 py-1">
            <i class="fa-solid fa-money-bill-wave text-success fs-6"></i>
            <span class="text-secondary fw-semibold">ক্যাশ অন ডেলিভারি</span>
        </div>
        <div class="d-flex align-items-center gap-2 py-1">
            <i class="fa-solid fa-headset text-primary fs-6"></i>
            <span class="text-secondary fw-semibold">২৪/৭ ডেডিকেটেড সাপোর্ট</span>
        </div>
    </div>
</div>

<style>
.header-mega-wrapper::after {
    display: none !important;
}
.header-mega-wrapper:not(.is-open):not(.open) .mega-menu-dropdown:not(.show) {
    display: none !important;
}
@media (min-width: 992px) and (max-width: 1399.98px) {
    .header-mega-wrapper {
        position: static !important;
    }
    .header-mega-wrapper .mega-menu-dropdown {
        left: 16px !important;
        right: 16px !important;
        width: calc(100% - 32px) !important;
        max-width: 1080px !important;
        margin: 0 auto !important;
    }
}
@media (min-width: 1400px) {
    .header-mega-wrapper {
        position: relative !important;
    }
    .header-mega-wrapper .mega-menu-dropdown {
        left: 0 !important;
        width: 1080px !important;
        max-width: calc(100vw - 32px) !important;
    }
}
.custom-mega-scrollbar::-webkit-scrollbar {
    width: 4px;
}
.custom-mega-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}
.custom-mega-scrollbar::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 4px;
}
.custom-mega-scrollbar::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
}
.mega-products-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 12px;
    width: 100%;
}
.mega-cat-item {
    transition: all 0.15s ease;
    cursor: pointer;
}
.mega-cat-item a {
    color: #475569;
}
.mega-cat-item .cat-icon {
    color: #64748b;
}
.mega-cat-item .cat-chevron {
    color: #94a3b8;
}
.mega-cat-item.active-cat {
    background-color: #0f172a !important;
    box-shadow: 0 4px 8px -1px rgba(0, 0, 0, 0.15);
}
.mega-cat-item.active-cat a {
    color: #ffffff !important;
    font-weight: 600;
}
.mega-cat-item.active-cat .cat-icon,
.mega-cat-item.active-cat .cat-chevron {
    color: #ffffff !important;
}
.mega-cat-item:not(.active-cat):hover {
    background-color: #ffffff;
}
.mega-prod-card {
    padding: 10px;
    border-color: #e2e8f0 !important;
    transition: transform 0.18s ease, box-shadow 0.18s ease, border-color 0.18s ease;
}
.mega-prod-card:hover {
    border-color: #0f172a !important;
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08) !important;
    transform: translateY(-2px);
}
</style>
