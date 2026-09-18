@extends('backend.layouts.app')

@section('title', 'Custom CMS Pages')

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.snow.css" rel="stylesheet">
<style>
    #pageModal .modal-dialog {
        max-width: 1400px;
        width: 95vw;
        height: 92vh;
        margin: 1.5rem auto;
    }
    #pageModal .modal-content {
        height: 100%;
        max-height: 92vh;
        display: flex;
        flex-direction: column;
        border-radius: 18px;
        overflow: hidden;
    }
    #pageModal form#pageForm {
        height: 100%;
        display: flex;
        flex-direction: column;
        overflow: hidden;
    }
    #pageModal .modal-body {
        flex: 1 1 auto;
        overflow-y: auto !important;
        -webkit-overflow-scrolling: touch;
        padding: 1.5rem 1.75rem;
    }
    #pageModal .modal-footer {
        flex-shrink: 0;
        background: var(--surface-2);
        border-top: 1px solid var(--border-color);
        padding: 0.85rem 1.75rem;
    }
    .ql-toolbar.ql-snow {
        border-top-left-radius: 12px;
        border-top-right-radius: 12px;
        border-color: var(--border-color) !important;
        background: var(--surface-2);
    }
    .ql-container.ql-snow {
        border-bottom-left-radius: 12px;
        border-bottom-right-radius: 12px;
        border-color: var(--border-color) !important;
        background: var(--bs-body-bg);
        color: var(--text-main);
        font-family: inherit;
        font-size: 0.95rem;
        min-height: 380px;
    }
    .ql-editor {
        min-height: 380px;
        font-size: 0.95rem;
        line-height: 1.6;
    }
    [data-bs-theme="dark"] .ql-snow .ql-stroke {
        stroke: #94a3b8;
    }
    [data-bs-theme="dark"] .ql-snow .ql-fill {
        fill: #94a3b8;
    }
    [data-bs-theme="dark"] .ql-snow .ql-picker {
        color: #94a3b8;
    }
    [data-bs-theme="dark"] .ql-snow .ql-picker-options {
        background-color: var(--surface-2);
        border-color: var(--border-color);
    }
    @media (max-width: 768px) {
        #pageModal .modal-dialog {
            width: 100vw;
            height: 100vh;
            max-height: 100vh;
            margin: 0;
        }
        #pageModal .modal-content {
            border-radius: 0;
            max-height: 100vh;
            height: 100%;
        }
    }
</style>
@endpush

@section('content')
<div class="card p-3 p-md-4 mb-4" data-turbo="false">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <h3 class="fw-bold mb-1">Custom Pages (CMS)</h3>
            <p class="text-muted small mb-0">Create and update static information pages such as About, Privacy Policy, Terms, and Delivery.</p>
        </div>
        <button type="button" class="btn btn-primary" onclick="openPageModal()">
            <i class="fa-solid fa-plus me-1"></i> Create New Page
        </button>
    </div>
</div>

<div class="card p-3 p-md-4 table-card mb-4">
    <div class="table-responsive">
        <table class="table table-striped table-hover table-bordered align-middle mb-0">
            <thead>
                <tr>
                    <th style="width: 35%;">Page Title</th>
                    <th style="width: 25%;">URL Slug</th>
                    <th style="width: 15%; text-align: center;">Status</th>
                    <th style="width: 15%;">Last Updated</th>
                    <th style="width: 10%; text-align: center;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($pages as $page)
                @php
                    $updatedDate = !empty($page->updated_at) ? date('d M Y, h:i A', strtotime($page->updated_at)) : 'N/A';
                @endphp
                <tr>
                    <td>
                        <div class="fw-bold text-body">{{ $page->title }}</div>
                    </td>
                    <td><code class="small text-primary font-monospace">/page/{{ $page->slug }}</code></td>
                    <td class="text-center">
                        @if($page->is_published)
                            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-1">Published</span>
                        @else
                            <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill px-2.5 py-1">Draft</span>
                        @endif
                    </td>
                    <td><span class="small text-muted">{{ $updatedDate }}</span></td>
                    <td class="text-center">
                        <div class="d-inline-flex gap-1">
                            <button type="button" class="btn-action text-primary" onclick="editPage({{ json_encode($page) }})" title="Edit Page">
                                <i class="fa-solid fa-pen-to-square"></i>
                            </button>
                            <form action="{{ route('admin.pages.delete', $page->id) }}" method="POST" onsubmit="return confirm('Delete this page permanently?')" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-action text-danger" title="Delete Page">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center py-4 text-muted">No custom pages created yet. Click "Create New Page" to add one.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- ========================================================================= -->
<!-- FULL-WIDTH BIG MODAL FOR CMS PAGE WITH RICH TEXT EDITOR -->
<!-- ========================================================================= -->
<div class="modal fade" id="pageModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl modal-fullscreen-lg-down">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header border-0 pb-3">
                <div>
                    <h5 class="fw-bold mb-0" id="pageModalTitle">
                        <i class="fa-solid fa-file-lines text-primary me-2"></i> Create Page
                    </h5>
                    <small class="text-muted">Compose rich-text page content, slug, and publishing status</small>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="pageForm" action="{{ route('admin.pages.store') }}" method="POST" onsubmit="syncEditorContent()">
                @csrf
                <div class="modal-body d-flex flex-column gap-3">
                    <div class="row g-3">
                        <div class="col-12 col-md-7">
                            <label class="form-label small fw-bold">Page Title <span class="text-danger">*</span></label>
                            <input type="text" name="title" id="pageTitle" class="form-control" required placeholder="e.g. Terms &amp; Conditions, Privacy Policy">
                        </div>
                        <div class="col-12 col-md-5">
                            <label class="form-label small fw-bold">Custom Slug (optional)</label>
                            <div class="input-group">
                                <span class="input-group-text bg-body-secondary text-muted font-monospace small">/page/</span>
                                <input type="text" name="slug" id="pageSlug" class="form-control font-monospace" placeholder="terms-and-conditions">
                            </div>
                        </div>
                    </div>

                    <div class="d-flex flex-column flex-grow-1">
                        <label class="form-label small fw-bold mb-2">Page Body / Rich Text Content</label>
                        <input type="hidden" name="content" id="hiddenContent">
                        <div id="quillEditor"></div>
                    </div>

                    <div class="card p-3 bg-body-tertiary border">
                        <div class="form-check form-switch mb-0">
                            <input class="form-check-input" type="checkbox" name="is_published" id="pagePublished" value="1" checked>
                            <label class="form-check-label fw-bold" for="pagePublished">Publish Immediately (Visible to customers)</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary px-4 fw-bold">
                        <i class="fa-solid fa-check me-1"></i> Save Page
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.js"></script>
<script>
(() => {
    let quill = null;

    function getPageModal() {
        const modalEl = document.getElementById('pageModal');
        return modalEl ? bootstrap.Modal.getOrCreateInstance(modalEl) : null;
    }

    function initCmsPage() {
        const editorEl = document.getElementById('quillEditor');
        if (!editorEl) return;

        if (editorEl.querySelector('.ql-editor')) return;

        quill = new Quill('#quillEditor', {
            theme: 'snow',
            placeholder: 'Write your page content, policies, formatting, headings, links, and tables here...',
            modules: {
                toolbar: [
                    [{ 'header': [1, 2, 3, 4, 5, false] }],
                    ['bold', 'italic', 'underline', 'strike'],
                    [{ 'color': [] }, { 'background': [] }],
                    [{ 'list': 'ordered' }, { 'list': 'bullet' }, { 'align': [] }],
                    ['blockquote', 'code-block'],
                    ['link', 'clean']
                ]
            }
        });
    }

    document.addEventListener('turbo:load', initCmsPage);
    document.addEventListener('DOMContentLoaded', initCmsPage);

    function openPageModal() {
        const form = document.getElementById('pageForm');
        if (form) form.action = "{{ route('admin.pages.store') }}";
        const titleEl = document.getElementById('pageModalTitle');
        if (titleEl) titleEl.innerHTML = '<i class="fa-solid fa-file-lines text-primary me-2"></i> Create Page';
        const pageTitle = document.getElementById('pageTitle');
        if (pageTitle) pageTitle.value = '';
        const pageSlug = document.getElementById('pageSlug');
        if (pageSlug) pageSlug.value = '';
        const pagePublished = document.getElementById('pagePublished');
        if (pagePublished) pagePublished.checked = true;
        if (quill) {
            quill.root.innerHTML = '';
        }
        const modal = getPageModal();
        if (modal) {
            modal.show();
        }
    }

    function editPage(page) {
        const form = document.getElementById('pageForm');
        if (form) form.action = "/admin/pages/" + page.id + "/update";
        const titleEl = document.getElementById('pageModalTitle');
        if (titleEl) titleEl.innerHTML = '<i class="fa-solid fa-pen-to-square text-primary me-2"></i> Edit Page: ' + (page.title || '');
        const pageTitle = document.getElementById('pageTitle');
        if (pageTitle) pageTitle.value = page.title || '';
        const pageSlug = document.getElementById('pageSlug');
        if (pageSlug) pageSlug.value = page.slug || '';
        const pagePublished = document.getElementById('pagePublished');
        if (pagePublished) pagePublished.checked = !!page.is_published;
        if (quill) {
            quill.root.innerHTML = page.content || '';
        }
        const modal = getPageModal();
        if (modal) {
            modal.show();
        }
    }

    function syncEditorContent() {
        if (quill) {
            const html = quill.root.innerHTML;
            const hiddenEl = document.getElementById('hiddenContent');
            if (hiddenEl) hiddenEl.value = html === '<p><br></p>' ? '' : html;
        }
    }

    window.openPageModal = openPageModal;
    window.editPage = editPage;
    window.syncEditorContent = syncEditorContent;
})();
</script>
@endpush

