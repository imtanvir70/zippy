@if(!empty($activePopup))
@php
    $popupImage = null;
    if (!empty($activePopup->image)) {
        $cleanPopupImg = ltrim($activePopup->image, '/');
        $popupImage = Str::startsWith($cleanPopupImg, ['http://', 'https://'])
            ? $activePopup->image
            : (Str::startsWith($cleanPopupImg, 'storage/') ? asset($cleanPopupImg) : asset('storage/' . $cleanPopupImg));
    }
@endphp
<style>
#promoCampaignModal.modal-static .modal-dialog {
    transform: scale(1.035) translateY(0) !important;
    transition: transform 120ms cubic-bezier(0.16, 1, 0.3, 1) !important;
}
</style>
<div class="modal fade" id="promoCampaignModal" tabindex="-1" aria-labelledby="promoCampaignModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered @if($activePopup->type === 'image_only') modal-lg @else modal-dialog-scrollable @endif">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden position-relative" style="background: #ffffff;">
            <button type="button" class="btn-close position-absolute top-0 end-0 m-3 z-3 bg-white p-2 rounded-circle shadow-sm" data-bs-dismiss="modal" aria-label="Close" style="opacity: 0.9;"></button>
            
            @if($activePopup->type === 'image_only')
                <div class="p-0 text-center position-relative">
                    @if(!empty($activePopup->btn_link))
                        <a href="{{ $activePopup->btn_link }}" class="d-block text-decoration-none">
                    @endif
                        <img src="{{ $popupImage }}" alt="{{ $activePopup->title }}" class="img-fluid w-100 h-auto" style="max-height: 80vh; object-fit: contain;">
                    @if(!empty($activePopup->btn_link))
                        </a>
                    @endif

                    @if(!empty($activePopup->btn_text) && !empty($activePopup->btn_link))
                        <div class="p-3 bg-white border-top text-center">
                            <a href="{{ $activePopup->btn_link }}" class="btn btn-primary px-4 py-2 rounded-pill fw-bold shadow-sm">
                                {{ $activePopup->btn_text }} <i class="fa-solid fa-arrow-right ms-1"></i>
                            </a>
                        </div>
                    @endif
                </div>
            @elseif($activePopup->type === 'text_only')
                <div class="modal-body p-4 p-md-5 text-center">
                    <div class="d-inline-flex align-items-center justify-content-center bg-primary bg-opacity-10 text-primary rounded-circle mb-3" style="width: 68px; height: 68px; font-size: 28px;">
                        <i class="fa-solid fa-bullhorn"></i>
                    </div>
                    @if(!empty($activePopup->heading))
                        <h4 class="fw-bold text-dark mb-2" id="promoCampaignModalLabel">{{ $activePopup->heading }}</h4>
                    @endif
                    @if(!empty($activePopup->content))
                        <div class="text-secondary fs-6 mb-4 lh-base" style="white-space: pre-line;">{!! nl2br(e($activePopup->content)) !!}</div>
                    @endif
                    @if(!empty($activePopup->btn_text) && !empty($activePopup->btn_link))
                        <a href="{{ $activePopup->btn_link }}" class="btn btn-primary btn-lg px-4 py-2 rounded-pill fw-bold shadow-sm">
                            {{ $activePopup->btn_text }} <i class="fa-solid fa-arrow-right ms-1"></i>
                        </a>
                    @endif
                </div>
            @else
                <div class="p-0">
                    @if(!empty($popupImage))
                        <div class="position-relative text-center p-3 pt-4 pb-1 bg-white">
                            <img src="{{ $popupImage }}" alt="{{ $activePopup->title }}" class="img-fluid mx-auto d-block" style="max-height: 280px; max-width: 100%; width: auto; height: auto; object-fit: contain;">
                        </div>
                    @endif
                    <div class="modal-body p-4 pt-2 text-center">
                        @if(!empty($activePopup->heading))
                            <h4 class="fw-bold text-dark mb-2" id="promoCampaignModalLabel">{{ $activePopup->heading }}</h4>
                        @endif
                        @if(!empty($activePopup->content))
                            <div class="text-secondary small mb-4 lh-base" style="white-space: pre-line;">{!! nl2br(e($activePopup->content)) !!}</div>
                        @endif
                        @if(!empty($activePopup->btn_text) && !empty($activePopup->btn_link))
                            <div class="d-grid gap-2 col-10 col-md-8 mx-auto">
                                <a href="{{ $activePopup->btn_link }}" class="btn btn-primary py-2 rounded-pill fw-bold shadow-sm">
                                    {{ $activePopup->btn_text }} <i class="fa-solid fa-arrow-right ms-1"></i>
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

<script>
(function() {
    function runPromoPopup() {
        if (window.__promoPopupTimer) {
            clearTimeout(window.__promoPopupTimer);
            window.__promoPopupTimer = null;
        }

        const modalEl = document.getElementById('promoCampaignModal');
        if (!modalEl || typeof bootstrap === 'undefined') {
            return;
        }

        const popupId = {{ $activePopup->id }};
        const storageKey = 'zippy_popup_seen_' + popupId;
        const expiryTime = localStorage.getItem(storageKey);
        const now = Date.now();

        if (expiryTime && now < parseInt(expiryTime, 10)) {
            return;
        }

        window.__promoPopupTimer = setTimeout(function() {
            const currentModal = document.getElementById('promoCampaignModal');
            if (!currentModal || typeof bootstrap === 'undefined') {
                return;
            }

            const modalInstance = bootstrap.Modal.getOrCreateInstance(currentModal, {
                backdrop: 'static',
                keyboard: false
            });
            modalInstance.show();

            const tenMinutes = 10 * 60 * 1000;
            localStorage.setItem(storageKey, (Date.now() + tenMinutes).toString());

            if (window.axios) {
                window.axios.post('/popup-impression/' + popupId, {}, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                }).catch(function() {});
            }
        }, 1200);
    }

    function cleanupPromoPopup() {
        if (window.__promoPopupTimer) {
            clearTimeout(window.__promoPopupTimer);
            window.__promoPopupTimer = null;
        }
        const modalEl = document.getElementById('promoCampaignModal');
        if (modalEl && typeof bootstrap !== 'undefined') {
            const inst = bootstrap.Modal.getInstance(modalEl);
            if (inst) {
                try { inst.hide(); } catch(e) {}
            }
            modalEl.classList.remove('show');
            modalEl.setAttribute('aria-hidden', 'true');
        }
    }

    if (!window.__promoPopupBound) {
        window.__promoPopupBound = true;
        document.addEventListener('turbo:before-cache', cleanupPromoPopup);
        document.addEventListener('turbo:before-render', cleanupPromoPopup);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', runPromoPopup, { once: true });
    } else {
        runPromoPopup();
    }
})();
</script>
@endif

