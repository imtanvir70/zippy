@extends('backend.layouts.app')

@section('title', 'Promotional Popups')

@section('content')
<div class="card p-3 p-md-4 mb-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <h3 class="fw-bold mb-0">Promotional Popups</h3>
            <small class="text-muted">Manage homepage popup alerts, promotional banners, and scheduled campaigns with frequency capping</small>
        </div>
        <button type="button" class="btn btn-primary" onclick="openCreatePopupModal()">
            <i class="fa-solid fa-plus me-1"></i> New Promo Popup
        </button>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-12 col-sm-6 col-xl-4">
        <div class="card p-3 h-100 border-0 shadow-sm bg-white">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small text-uppercase fw-semibold">Total Campaigns</span>
                    <h3 class="fw-bold mb-0 mt-1">{{ count($popups) }}</h3>
                </div>
                <div class="rounded-3 bg-primary bg-opacity-10 text-primary p-3 fs-4">
                    <i class="fa-solid fa-window-restore"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-4">
        <div class="card p-3 h-100 border-0 shadow-sm bg-white">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small text-uppercase fw-semibold">Active Popups</span>
                    <h3 class="fw-bold mb-0 mt-1 text-success">{{ $popups->where('is_active', 1)->count() }}</h3>
                </div>
                <div class="rounded-3 bg-success bg-opacity-10 text-success p-3 fs-4">
                    <i class="fa-solid fa-circle-check"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-4">
        <div class="card p-3 h-100 border-0 shadow-sm bg-white">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small text-uppercase fw-semibold">Total Impressions</span>
                    <h3 class="fw-bold mb-0 mt-1 text-info">{{ number_format($popups->sum('impressions_count')) }}</h3>
                </div>
                <div class="rounded-3 bg-info bg-opacity-10 text-info p-3 fs-4">
                    <i class="fa-solid fa-eye"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th style="width: 70px;">Media</th>
                    <th>Campaign & Mode</th>
                    <th>Display Message</th>
                    <th>Schedule</th>
                    <th class="text-center">Impressions</th>
                    <th class="text-center">Status</th>
                    <th class="text-end" style="width: 110px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($popups as $popup)
                    @php
                        $imgUrl = null;
                        if (!empty($popup->image)) {
                            $cleanImgPath = ltrim($popup->image, '/');
                            $imgUrl = Str::startsWith($cleanImgPath, ['http://', 'https://'])
                                ? $popup->image
                                : (Str::startsWith($cleanImgPath, 'storage/') ? asset($cleanImgPath) : asset('storage/' . $cleanImgPath));
                        }
                    @endphp
                    <tr id="popupRow_{{ $popup->id }}">
                        <td>
                            @if(!empty($imgUrl))
                                <img src="{{ $imgUrl }}" alt="{{ $popup->title }}" class="rounded object-fit-cover border" style="width: 60px; height: 45px;" onerror="this.onerror=null;this.src='{{ asset('images/placeholder.svg') }}';">
                            @else
                                <div class="rounded bg-light border d-flex align-items-center justify-content-center text-muted" style="width: 60px; height: 45px;">
                                    <i class="fa-solid fa-font fs-6"></i>
                                </div>
                            @endif
                        </td>
                        <td>
                            <div class="fw-bold text-dark">{{ $popup->title }}</div>
                            <div class="mt-1">
                                @if($popup->type === 'image_only')
                                    <span class="badge bg-info text-dark"><i class="fa-solid fa-image me-1"></i> Photo Only</span>
                                @elseif($popup->type === 'text_only')
                                    <span class="badge bg-warning text-dark"><i class="fa-solid fa-paragraph me-1"></i> Text Only</span>
                                @else
                                    <span class="badge bg-primary"><i class="fa-solid fa-layer-group me-1"></i> Photo + Text</span>
                                @endif
                            </div>
                        </td>
                        <td>
                            @if(!empty($popup->heading))
                                <div class="fw-semibold text-truncate" style="max-width: 250px;">{{ $popup->heading }}</div>
                            @endif
                            @if(!empty($popup->content))
                                <div class="text-muted small text-truncate" style="max-width: 250px;">{{ Str::limit($popup->content, 60) }}</div>
                            @endif
                            @if(!empty($popup->btn_text))
                                <div class="mt-1">
                                    <span class="badge bg-light text-secondary border">
                                        <i class="fa-solid fa-arrow-up-right-from-square me-1"></i> {{ $popup->btn_text }}
                                    </span>
                                </div>
                            @endif
                            @if(empty($popup->heading) && empty($popup->content) && empty($popup->btn_text))
                                <span class="text-muted small fst-italic">Visual Banner Only</span>
                            @endif
                        </td>
                        <td>
                            @if($popup->start_date || $popup->end_date)
                                <div class="small text-dark">
                                    <i class="fa-regular fa-calendar me-1 text-muted"></i>
                                    {{ $popup->start_date ? \Carbon\Carbon::parse($popup->start_date)->format('M d, Y') : 'Start' }}
                                    &rarr;
                                    {{ $popup->end_date ? \Carbon\Carbon::parse($popup->end_date)->format('M d, Y') : 'Ongoing' }}
                                </div>
                            @else
                                <span class="badge bg-light text-muted border">Always Active</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <span class="badge bg-light text-dark border px-2 py-1">
                                <i class="fa-solid fa-eye me-1 text-info"></i> {{ number_format($popup->impressions_count) }}
                            </span>
                        </td>
                        <td class="text-center">
                            <div class="form-check form-switch d-inline-block m-0">
                                <input class="form-check-input" type="checkbox" role="switch" id="togglePopup_{{ $popup->id }}" {{ $popup->is_active ? 'checked' : '' }} onchange="togglePopupStatus({{ $popup->id }}, this)">
                            </div>
                        </td>
                        <td class="text-end">
                            <div class="d-inline-flex gap-1">
                                <button type="button" class="btn btn-sm btn-outline-primary" onclick="openEditPopupModal({{ $popup->id }})" title="Edit">
                                    <i class="fa-solid fa-pen"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-danger" onclick="deletePopupAjax({{ $popup->id }})" title="Delete">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="fa-solid fa-window-restore fs-1 d-block mb-3 text-secondary"></i>
                            No promotional popups created yet.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="modal fade" id="popupModal" tabindex="-1" aria-labelledby="popupModalLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow">
            <form id="popupForm" onsubmit="handlePopupFormSubmit(event)" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="popup_id" id="popup_id" value="">
                
                <div class="modal-header bg-light border-bottom">
                    <h5 class="modal-title fw-bold" id="popupModalLabel">New Promotional Popup</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-12 col-md-8">
                            <label class="form-label fw-semibold">Campaign Title <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="title" id="popup_title" placeholder="e.g., Eid Flash Mega Sale" required>
                        </div>

                        <div class="col-12 col-md-4">
                            <label class="form-label fw-semibold">Display Format <span class="text-danger">*</span></label>
                            <select class="form-select" name="type" id="popup_type" onchange="syncPopupTypeFields()">
                                <option value="both">Photo + Text Details</option>
                                <option value="image_only">Photo Only (Banner)</option>
                                <option value="text_only">Text Only (Notice/Alert)</option>
                            </select>
                        </div>

                        <div class="col-12" id="group_image">
                            <label class="form-label fw-semibold">Banner Image</label>
                            <div class="border rounded-3 p-3 text-center bg-light">
                                <div id="imagePreviewWrap" class="mb-2 d-none">
                                    <img id="imagePreview" src="" alt="Preview" class="img-fluid rounded border shadow-sm" style="max-height: 200px;">
                                </div>
                                <input type="file" class="form-control" name="image" id="popup_image" accept="image/*" onchange="previewPopupImage(this)">
                                <small class="text-muted d-block mt-1">Recommended: 800x600px or 1000x500px (GIF, JPG, PNG, WebP up to 10MB. Animated GIFs are fully supported)</small>
                            </div>
                        </div>

                        <div class="col-12" id="group_heading">
                            <label class="form-label fw-semibold">Popup Heading</label>
                            <input type="text" class="form-control" name="heading" id="popup_heading" placeholder="e.g., স্পেশাল ঈদ অফার ২৫% ডিসকাউন্ট!">
                        </div>

                        <div class="col-12" id="group_content">
                            <label class="form-label fw-semibold">Popup Message / Details</label>
                            <textarea class="form-control" name="content" id="popup_content" rows="3" placeholder="Enter campaign description, coupon promo details, or terms..."></textarea>
                        </div>

                        <div class="col-12 col-md-6" id="group_btn_text">
                            <label class="form-label fw-semibold">Action Button Text</label>
                            <input type="text" class="form-control" name="btn_text" id="popup_btn_text" placeholder="e.g., এখনই অর্ডার করুন">
                        </div>

                        <div class="col-12 col-md-6" id="group_btn_link">
                            <label class="form-label fw-semibold">Action Button Link</label>
                            <input type="text" class="form-control" name="btn_link" id="popup_btn_link" placeholder="e.g., /flash-deals or https://...">
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold">Start Date (Optional)</label>
                            <input type="date" class="form-control" name="start_date" id="popup_start_date">
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold">End Date (Optional)</label>
                            <input type="date" class="form-control" name="end_date" id="popup_end_date">
                        </div>

                        <div class="col-12">
                            <div class="form-check form-switch pt-2">
                                <input class="form-check-input" type="checkbox" role="switch" name="is_active" id="popup_is_active" value="1" checked>
                                <label class="form-check-label fw-semibold" for="popup_is_active">Enable Campaign (Display on Homepage)</label>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="modal-footer bg-light border-top">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary px-4" id="submitBtn">
                        <i class="fa-solid fa-check me-1"></i> Save Popup
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function() {
    let popupModalInstance = null;

    function getModal() {
        if (!popupModalInstance) {
            const el = document.getElementById('popupModal');
            if (el && window.bootstrap) {
                popupModalInstance = new bootstrap.Modal(el);
            }
        }
        return popupModalInstance;
    }

    function syncPopupTypeFields() {
        const type = document.getElementById('popup_type').value;
        const groupImg = document.getElementById('group_image');
        const groupHeading = document.getElementById('group_heading');
        const groupContent = document.getElementById('group_content');
        const groupBtnText = document.getElementById('group_btn_text');
        const groupBtnLink = document.getElementById('group_btn_link');

        if (type === 'image_only') {
            if (groupImg) groupImg.style.display = 'block';
            if (groupHeading) groupHeading.style.display = 'none';
            if (groupContent) groupContent.style.display = 'none';
            if (groupBtnText) groupBtnText.style.display = 'block';
            if (groupBtnLink) groupBtnLink.style.display = 'block';
        } else if (type === 'text_only') {
            if (groupImg) groupImg.style.display = 'none';
            if (groupHeading) groupHeading.style.display = 'block';
            if (groupContent) groupContent.style.display = 'block';
            if (groupBtnText) groupBtnText.style.display = 'block';
            if (groupBtnLink) groupBtnLink.style.display = 'block';
        } else {
            if (groupImg) groupImg.style.display = 'block';
            if (groupHeading) groupHeading.style.display = 'block';
            if (groupContent) groupContent.style.display = 'block';
            if (groupBtnText) groupBtnText.style.display = 'block';
            if (groupBtnLink) groupBtnLink.style.display = 'block';
        }
    }

    function previewPopupImage(input) {
        const wrap = document.getElementById('imagePreviewWrap');
        const preview = document.getElementById('imagePreview');
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.src = e.target.result;
                wrap.classList.remove('d-none');
            };
            reader.readAsDataURL(input.files[0]);
        }
    }

    function openCreatePopupModal() {
        document.getElementById('popupForm').reset();
        document.getElementById('popup_id').value = '';
        document.getElementById('popupModalLabel').textContent = 'New Promotional Popup';
        document.getElementById('popup_is_active').checked = true;
        document.getElementById('imagePreviewWrap').classList.add('d-none');
        document.getElementById('imagePreview').src = '';
        syncPopupTypeFields();
        getModal().show();
    }

    function openEditPopupModal(id) {
        axios.get(`/admin/popups/${id}/json`)
            .then(res => {
                if (res.data.success && res.data.popup) {
                    const p = res.data.popup;
                    document.getElementById('popup_id').value = p.id;
                    document.getElementById('popup_title').value = p.title || '';
                    document.getElementById('popup_type').value = p.type || 'both';
                    document.getElementById('popup_heading').value = p.heading || '';
                    document.getElementById('popup_content').value = p.content || '';
                    document.getElementById('popup_btn_text').value = p.btn_text || '';
                    document.getElementById('popup_btn_link').value = p.btn_link || '';
                    document.getElementById('popup_start_date').value = p.start_date ? p.start_date.substring(0, 10) : '';
                    document.getElementById('popup_end_date').value = p.end_date ? p.end_date.substring(0, 10) : '';
                    document.getElementById('popup_is_active').checked = Boolean(p.is_active);

                    const wrap = document.getElementById('imagePreviewWrap');
                    const preview = document.getElementById('imagePreview');
                    if (p.image) {
                        const cleanImg = p.image.startsWith('http') ? p.image : (p.image.startsWith('/') ? p.image : '/' + p.image);
                        preview.src = cleanImg;
                        wrap.classList.remove('d-none');
                    } else {
                        wrap.classList.add('d-none');
                        preview.src = '';
                    }

                    document.getElementById('popupModalLabel').textContent = 'Edit Promotional Popup';
                    syncPopupTypeFields();
                    getModal().show();
                } else {
                    Swal.fire('Error', 'Popup data could not be loaded.', 'error');
                }
            })
            .catch(() => {
                Swal.fire('Error', 'Failed to retrieve popup details.', 'error');
            });
    }

    function handlePopupFormSubmit(e) {
        e.preventDefault();
        const form = document.getElementById('popupForm');
        const submitBtn = document.getElementById('submitBtn');
        const id = document.getElementById('popup_id').value;
        const formData = new FormData(form);

        if (!form.popup_is_active.checked) {
            formData.set('is_active', '0');
        }

        const url = id ? `/admin/popups/${id}/update` : '/admin/popups/store';

        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-1"></i> Saving...';

        axios.post(url, formData, {
            headers: { 'Content-Type': 'multipart/form-data' }
        })
        .then(res => {
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fa-solid fa-check me-1"></i> Save Popup';

            if (res.data.success) {
                getModal().hide();
                Swal.fire({
                    icon: 'success',
                    title: 'Saved!',
                    text: res.data.message,
                    timer: 1500,
                    showConfirmButton: false
                }).then(() => {
                    location.reload();
                });
            } else {
                Swal.fire('Error', res.data.message || 'Validation error', 'error');
            }
        })
        .catch(err => {
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fa-solid fa-check me-1"></i> Save Popup';
            const msg = err.response?.data?.message || 'Failed to save promotional popup.';
            Swal.fire('Error', msg, 'error');
        });
    }

    function togglePopupStatus(id, checkbox) {
        const originalState = !checkbox.checked;
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        axios.post(`/admin/popups/${id}/toggle`, {}, {
            headers: csrfToken ? { 'X-CSRF-TOKEN': csrfToken } : {}
        })
            .then(res => {
                if (res.data.success) {
                    if (window.showToast) {
                        window.showToast(res.data.message, 'success');
                    } else if (window.Notyf) {
                        new Notyf().success(res.data.message);
                    } else if (window.Swal) {
                        Swal.mixin({
                            toast: true,
                            position: 'top-end',
                            showConfirmButton: false,
                            timer: 2000
                        }).fire({
                            icon: 'success',
                            title: res.data.message
                        });
                    }
                } else {
                    checkbox.checked = originalState;
                    if (window.showToast) {
                        window.showToast(res.data.message || 'Failed to toggle status.', 'error');
                    } else {
                        Swal.fire('Error', res.data.message || 'Failed to toggle status.', 'error');
                    }
                }
            })
            .catch(err => {
                checkbox.checked = originalState;
                if (err.response && (err.response.status === 401 || err.response.status === 419)) {
                    Swal.fire({
                        title: 'Session Expired',
                        text: 'Your session has expired. Please refresh the page or log in again.',
                        icon: 'warning',
                        confirmButtonText: 'Log In'
                    }).then(() => {
                        window.location.href = '/admin/login';
                    });
                    return;
                }
                const msg = err.response?.data?.message || 'Server error while toggling popup status.';
                if (window.showToast) {
                    window.showToast(msg, 'error');
                } else {
                    Swal.fire('Error', msg, 'error');
                }
            });
    }

    function deletePopupAjax(id) {
        Swal.fire({
            title: 'Delete this popup?',
            text: 'This will permanently remove the promotional campaign.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Yes, delete it!'
        }).then(result => {
            if (result.isConfirmed) {
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                axios.delete(`/admin/popups/${id}`, {
                    headers: csrfToken ? { 'X-CSRF-TOKEN': csrfToken } : {}
                })
                    .then(res => {
                        if (res.data.success) {
                            const row = document.getElementById(`popupRow_${id}`);
                            if (row) row.remove();
                            if (window.showToast) {
                                window.showToast('Popup has been removed.', 'success');
                            } else {
                                Swal.fire('Deleted!', 'Popup has been removed.', 'success');
                            }
                        } else {
                            if (window.showToast) {
                                window.showToast(res.data.message || 'Failed to delete popup.', 'error');
                            } else {
                                Swal.fire('Error', res.data.message || 'Failed to delete popup.', 'error');
                            }
                        }
                    })
                    .catch(err => {
                        if (err.response && (err.response.status === 401 || err.response.status === 419)) {
                            Swal.fire({
                                title: 'Session Expired',
                                text: 'Your session has expired. Please refresh the page or log in again.',
                                icon: 'warning',
                                confirmButtonText: 'Log In'
                            }).then(() => {
                                window.location.href = '/admin/login';
                            });
                            return;
                        }
                        const msg = err.response?.data?.message || 'Server error while deleting popup.';
                        if (window.showToast) {
                            window.showToast(msg, 'error');
                        } else {
                            Swal.fire('Error', msg, 'error');
                        }
                    });
            }
        });
    }

    window.openCreatePopupModal = openCreatePopupModal;
    window.openEditPopupModal = openEditPopupModal;
    window.handlePopupFormSubmit = handlePopupFormSubmit;
    window.togglePopupStatus = togglePopupStatus;
    window.deletePopupAjax = deletePopupAjax;
    window.syncPopupTypeFields = syncPopupTypeFields;
    window.previewPopupImage = previewPopupImage;
})();
</script>
@endpush

