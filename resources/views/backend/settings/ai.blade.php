@extends('backend.layouts.app')

@section('title', 'AI Integrations & API Hub')

@section('content')
<div class="card p-3 p-md-4 mb-4 border-0 shadow-sm">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
        <div class="d-flex align-items-center gap-3">
            <div class="stat-icon d-flex align-items-center justify-content-center text-white" style="width: 52px; height: 52px; border-radius: 14px; background: linear-gradient(135deg, #6366f1 0%, #a855f7 100%); font-size: 22px;">
                <i class="fa-solid fa-brain"></i>
            </div>
            <div>
                <div class="d-flex align-items-center gap-2">
                    <h3 class="fw-bold mb-0">AI Integrations & API Hub</h3>
                    <span class="badge {{ $aiSettings->is_active ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-secondary' }} rounded-pill px-2.5 py-1 small fw-semibold">
                        <i class="fa-solid fa-circle-dot me-1" style="font-size: 8px;"></i>
                        {{ $aiSettings->is_active ? 'Engine Layer Active' : 'Engine Layer Disabled' }}
                    </span>
                </div>
                <small class="text-muted">Enterprise multi-model intelligence routing with zero-downtime hot failover and cached configuration.</small>
            </div>
        </div>
        <div class="d-flex align-items-center gap-2">
            <button type="submit" form="aiSettingsForm" class="btn btn-primary px-4 py-2 fw-semibold rounded-3 shadow-sm d-flex align-items-center gap-2" id="btnSaveTop">
                <i class="fa-solid fa-floppy-disk"></i>
                <span>Save All Settings</span>
            </button>
        </div>
    </div>
</div>

<form id="aiSettingsForm" action="{{ route('admin.settings.ai.update') }}" method="POST">
    @csrf

    <div class="card p-3 p-md-4 mb-4 border-0 shadow-sm">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-3 pb-2 border-bottom">
            <div>
                <h6 class="fw-bold mb-1 text-primary d-flex align-items-center gap-2">
                    <i class="fa-solid fa-network-wired"></i>
                    <span>Dynamic Role Assignment & Failover Matrix</span>
                </h6>
                <small class="text-muted">Direct high-volume tasks to specialized models. If a primary provider encounters rate limits or errors, requests fail over instantly.</small>
            </div>
            <div class="form-check form-switch fs-6 mb-0">
                <input class="form-check-input" type="checkbox" name="is_active" id="isActiveSwitch" value="1" {{ $aiSettings->is_active ? 'checked' : '' }}>
                <label class="form-check-label fw-bold small text-body" for="isActiveSwitch">Enable AI Ecosystem</label>
            </div>
        </div>

        <div class="row g-3">
            <div class="col-lg-4">
                <div class="p-3 rounded-3 border h-100 bg-body-tertiary">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="badge bg-primary-subtle text-primary fw-bold px-2.5 py-1 rounded-pill small">
                            <i class="fa-solid fa-wand-magic-sparkles me-1"></i> Product Generation
                        </span>
                        <small class="text-muted fw-semibold">Primary Role</small>
                    </div>
                    <label class="form-label fw-bold small mb-1">Product Generator Engine</label>
                    <select name="product_generator_provider" id="productGeneratorSelect" class="form-select mb-2 fw-semibold">
                        @foreach($providers as $key => $name)
                            <option value="{{ $key }}" {{ $aiSettings->product_generator_provider === $key ? 'selected' : '' }}>{{ $name }}</option>
                        @endforeach
                    </select>
                    <small class="text-muted d-block" style="font-size: 11px; line-height: 1.4;">
                        Powers automatic SKU cataloging, structured SEO titles, specifications, and Bengali/English copy generation.
                    </small>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="p-3 rounded-3 border h-100 bg-body-tertiary">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="badge bg-success-subtle text-success fw-bold px-2.5 py-1 rounded-pill small">
                            <i class="fa-solid fa-comments me-1"></i> Customer Support QA
                        </span>
                        <small class="text-muted fw-semibold">Primary Role</small>
                    </div>
                    <label class="form-label fw-bold small mb-1">Frontend Customer QA Engine</label>
                    <select name="customer_qa_provider" id="customerQaSelect" class="form-select mb-2 fw-semibold">
                        @foreach($providers as $key => $name)
                            <option value="{{ $key }}" {{ $aiSettings->customer_qa_provider === $key ? 'selected' : '' }}>{{ $name }}</option>
                        @endforeach
                    </select>
                    <small class="text-muted d-block" style="font-size: 11px; line-height: 1.4;">
                        Powers real-time conversational chat, delivery time inquiries, cash-on-delivery questions, and customer FAQ.
                    </small>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="p-3 rounded-3 border h-100 bg-body-tertiary border-danger-subtle">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="badge bg-danger-subtle text-danger fw-bold px-2.5 py-1 rounded-pill small">
                            <i class="fa-solid fa-shield-heart me-1"></i> Hot Backup / Failover
                        </span>
                        <small class="text-danger fw-semibold">Automatic</small>
                    </div>
                    <label class="form-label fw-bold small mb-1">Failover / Hot Backup Engine</label>
                    <select name="failover_provider" id="failoverSelect" class="form-select mb-2 fw-semibold">
                        @foreach($providers as $key => $name)
                            <option value="{{ $key }}" {{ $aiSettings->failover_provider === $key ? 'selected' : '' }}>{{ $name }}</option>
                        @endforeach
                    </select>
                    <small class="text-muted d-block" style="font-size: 11px; line-height: 1.4;">
                        Instantly handles execution when the primary engine encounters HTTP 429 rate limits, quotas, or network timeouts.
                    </small>
                </div>
            </div>
        </div>
    </div>

    <div class="alert alert-info border-0 shadow-sm d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 p-3 rounded-3 mb-4" style="background: rgba(59, 130, 246, 0.08);">
        <div class="d-flex align-items-center gap-3">
            <div class="text-primary fs-4">
                <i class="fa-solid fa-wand-magic-sparkles"></i>
            </div>
            <div>
                <strong class="d-block text-body">AI Product Generation is in Catalog &amp; Inventory</strong>
                <small class="text-muted">Generate full listings, SEO copy, and specifications directly under <strong>Catalog &amp; Inventory &rarr; AI Product Generator</strong>.</small>
            </div>
        </div>
        <a href="{{ route('admin.products.ai') }}" class="btn btn-primary btn-sm px-3 py-2 fw-semibold rounded-3 text-nowrap">
            <span>Open AI Product Studio</span>
            <i class="fa-solid fa-arrow-right ms-1"></i>
        </a>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-lg-4">
            <div class="card p-3 p-md-4 h-100 border-0 shadow-sm">
                <div class="d-flex align-items-center justify-content-between pb-3 mb-3 border-bottom">
                    <div class="d-flex align-items-center gap-2.5">
                        <div class="d-flex align-items-center justify-content-center rounded-3 bg-primary-subtle text-primary" style="width: 38px; height: 38px;">
                            <i class="fa-brands fa-google fs-5"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold mb-0">Google Gemini</h6>
                            <small class="text-muted">Multimodal & Vision</small>
                        </div>
                    </div>
                    <span id="badgeGeminiKey" class="badge {{ !empty($aiSettings->gemini_api_key) ? 'bg-success-subtle text-success' : 'bg-warning-subtle text-warning' }} rounded-pill px-2.5 py-1 small">
                        {{ !empty($aiSettings->gemini_api_key) ? 'Configured' : 'No Key' }}
                    </span>
                </div>

                <div class="d-flex flex-column gap-3">
                    <div>
                        <div class="d-flex align-items-center justify-content-between mb-1">
                            <label class="form-label small fw-bold mb-0">Gemini API Key</label>
                            @if(!empty($aiSettings->gemini_api_key))
                                <button type="button" class="btn btn-link p-0 text-decoration-none small text-danger fw-semibold" onclick="clearProviderKey('gemini')">
                                    <i class="fa-solid fa-trash me-1"></i>Remove
                                </button>
                            @endif
                        </div>
                        <input type="hidden" name="remove_gemini_key" id="removeGeminiKey" value="0">
                        <div class="input-group">
                            <input type="password" name="gemini_api_key" id="geminiApiKey" autocomplete="new-password" class="form-control font-monospace" placeholder="AIzaSy..." value="{{ $aiSettings->gemini_api_key ?? '' }}" oninput="cancelKeyRemoval('gemini')">
                            <button type="button" class="btn btn-outline-secondary" onclick="togglePasswordVisibility('geminiApiKey', this)">
                                <i class="fa-solid fa-eye"></i>
                            </button>
                        </div>
                        <small class="text-muted d-block mt-1">Obtained from Google AI Studio console.</small>
                    </div>

                    <div>
                        <div class="d-flex align-items-center justify-content-between mb-1">
                            <label class="form-label small fw-bold mb-0">Model Selection</label>
                            <button type="button" class="btn btn-link p-0 text-decoration-none small text-primary fw-semibold" onclick="syncLiveModels('gemini', this)">
                                <i class="fa-solid fa-arrows-rotate me-1"></i>Auto-Detect
                            </button>
                        </div>
                        <input type="text" name="gemini_model" id="geminiModelInput" list="geminiModelList" class="form-control font-monospace" value="{{ $aiSettings->gemini_model ?? 'auto' }}" placeholder="auto">
                        <datalist id="geminiModelList">
                            <option value="auto">Auto (Auto-Detect Latest Active Model)</option>
                            @foreach($geminiModels as $mKey => $mDesc)
                                <option value="{{ $mKey }}">{{ $mDesc }}</option>
                            @endforeach
                        </datalist>
                        <small class="text-muted d-block mt-1">Set to <code>auto</code> for dynamic detection, or pick from active models.</small>
                    </div>

                    <div class="pt-2 border-top d-flex align-items-center justify-content-between">
                        <button type="button" class="btn btn-sm btn-outline-primary fw-semibold px-3" onclick="testProviderConnection('gemini')">
                            <i class="fa-solid fa-plug-circle-bolt me-1"></i> Test Gemini
                        </button>
                        <span id="geminiTestStatus" class="small text-muted fw-semibold"></span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card p-3 p-md-4 h-100 border-0 shadow-sm">
                <div class="d-flex align-items-center justify-content-between pb-3 mb-3 border-bottom">
                    <div class="d-flex align-items-center gap-2.5">
                        <div class="d-flex align-items-center justify-content-center rounded-3 bg-warning-subtle text-warning" style="width: 38px; height: 38px;">
                            <i class="fa-solid fa-bolt fs-5"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold mb-0">Groq Cloud</h6>
                            <small class="text-muted">Ultra-Low TTFT Engine</small>
                        </div>
                    </div>
                    <span id="badgeGroqKey" class="badge {{ !empty($aiSettings->groq_api_key) ? 'bg-success-subtle text-success' : 'bg-warning-subtle text-warning' }} rounded-pill px-2.5 py-1 small">
                        {{ !empty($aiSettings->groq_api_key) ? 'Configured' : 'No Key' }}
                    </span>
                </div>

                <div class="d-flex flex-column gap-3">
                    <div>
                        <div class="d-flex align-items-center justify-content-between mb-1">
                            <label class="form-label small fw-bold mb-0">Groq API Key</label>
                            @if(!empty($aiSettings->groq_api_key))
                                <button type="button" class="btn btn-link p-0 text-decoration-none small text-danger fw-semibold" onclick="clearProviderKey('groq')">
                                    <i class="fa-solid fa-trash me-1"></i>Remove
                                </button>
                            @endif
                        </div>
                        <input type="hidden" name="remove_groq_key" id="removeGroqKey" value="0">
                        <div class="input-group">
                            <input type="password" name="groq_api_key" id="groqApiKey" autocomplete="new-password" class="form-control font-monospace" placeholder="gsk_..." value="{{ $aiSettings->groq_api_key ?? '' }}" oninput="cancelKeyRemoval('groq')">
                            <button type="button" class="btn btn-outline-secondary" onclick="togglePasswordVisibility('groqApiKey', this)">
                                <i class="fa-solid fa-eye"></i>
                            </button>
                        </div>
                        <small class="text-muted d-block mt-1">Obtained from Groq Cloud Console.</small>
                    </div>

                    <div>
                        <div class="d-flex align-items-center justify-content-between mb-1">
                            <label class="form-label small fw-bold mb-0">Model Selection</label>
                            <button type="button" class="btn btn-link p-0 text-decoration-none small text-warning fw-semibold" onclick="syncLiveModels('groq', this)">
                                <i class="fa-solid fa-arrows-rotate me-1"></i>Auto-Detect
                            </button>
                        </div>
                        <input type="text" name="groq_model" id="groqModelInput" list="groqModelList" class="form-control font-monospace" value="{{ $aiSettings->groq_model ?? 'auto' }}" placeholder="auto">
                        <datalist id="groqModelList">
                            <option value="auto">Auto (Auto-Detect Latest Active Model)</option>
                            @foreach($groqModels as $mKey => $mDesc)
                                <option value="{{ $mKey }}">{{ $mDesc }}</option>
                            @endforeach
                        </datalist>
                        <small class="text-muted d-block mt-1">Set to <code>auto</code> for dynamic detection, or pick from active models.</small>
                    </div>

                    <div class="pt-2 border-top d-flex align-items-center justify-content-between">
                        <button type="button" class="btn btn-sm btn-outline-warning fw-semibold px-3" onclick="testProviderConnection('groq')">
                            <i class="fa-solid fa-plug-circle-bolt me-1"></i> Test Groq
                        </button>
                        <span id="groqTestStatus" class="small text-muted fw-semibold"></span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card p-3 p-md-4 h-100 border-0 shadow-sm">
                <div class="d-flex align-items-center justify-content-between pb-3 mb-3 border-bottom">
                    <div class="d-flex align-items-center gap-2.5">
                        <div class="d-flex align-items-center justify-content-center rounded-3 bg-info-subtle text-info" style="width: 38px; height: 38px;">
                            <i class="fa-solid fa-diagram-project fs-5"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold mb-0">OpenRouter</h6>
                            <small class="text-muted">Universal AI Gateway</small>
                        </div>
                    </div>
                    <span id="badgeOpenRouterKey" class="badge {{ !empty($aiSettings->openrouter_api_key) ? 'bg-success-subtle text-success' : 'bg-warning-subtle text-warning' }} rounded-pill px-2.5 py-1 small">
                        {{ !empty($aiSettings->openrouter_api_key) ? 'Configured' : 'No Key' }}
                    </span>
                </div>

                <div class="d-flex flex-column gap-3">
                    <div>
                        <div class="d-flex align-items-center justify-content-between mb-1">
                            <label class="form-label small fw-bold mb-0">OpenRouter API Key</label>
                            @if(!empty($aiSettings->openrouter_api_key))
                                <button type="button" class="btn btn-link p-0 text-decoration-none small text-danger fw-semibold" onclick="clearProviderKey('openrouter')">
                                    <i class="fa-solid fa-trash me-1"></i>Remove
                                </button>
                            @endif
                        </div>
                        <input type="hidden" name="remove_openrouter_key" id="removeOpenRouterKey" value="0">
                        <div class="input-group">
                            <input type="password" name="openrouter_api_key" id="openrouterApiKey" autocomplete="new-password" class="form-control font-monospace" placeholder="sk-or-v1-..." value="{{ $aiSettings->openrouter_api_key ?? '' }}" oninput="cancelKeyRemoval('openrouter')">
                            <button type="button" class="btn btn-outline-secondary" onclick="togglePasswordVisibility('openrouterApiKey', this)">
                                <i class="fa-solid fa-eye"></i>
                            </button>
                        </div>
                        <small class="text-muted d-block mt-1">Obtained from OpenRouter.ai API keys dashboard.</small>
                    </div>

                    <div>
                        <div class="d-flex align-items-center justify-content-between mb-1">
                            <label class="form-label small fw-bold mb-0">Model Selection</label>
                            <button type="button" class="btn btn-link p-0 text-decoration-none small text-info fw-semibold" onclick="syncLiveModels('openrouter', this)">
                                <i class="fa-solid fa-arrows-rotate me-1"></i>Auto-Detect
                            </button>
                        </div>
                        <input type="text" name="openrouter_model" id="openrouterModelInput" list="openrouterModelList" class="form-control font-monospace" value="{{ $aiSettings->openrouter_model ?? 'auto' }}" placeholder="auto">
                        <datalist id="openrouterModelList">
                            <option value="auto">Auto (Auto-Detect Latest Active Model)</option>
                            @foreach($openRouterModels as $mKey => $mDesc)
                                <option value="{{ $mKey }}">{{ $mDesc }}</option>
                            @endforeach
                        </datalist>
                        <small class="text-muted d-block mt-1">Set to <code>auto</code> for dynamic detection, or pick from active models.</small>
                    </div>

                    <div class="pt-2 border-top d-flex align-items-center justify-content-between">
                        <button type="button" class="btn btn-sm btn-outline-info fw-semibold px-3" onclick="testProviderConnection('openrouter')">
                            <i class="fa-solid fa-plug-circle-bolt me-1"></i> Test OpenRouter
                        </button>
                        <span id="openrouterTestStatus" class="small text-muted fw-semibold"></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card p-3 p-md-4 mb-4 border-0 shadow-sm">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
            <div class="d-flex align-items-center gap-2">
                <i class="fa-solid fa-bolt-lightning text-primary fs-5"></i>
                <div>
                    <span class="fw-bold d-block">Zero Query Architecture</span>
                    <small class="text-muted">Settings are memoized with <code>Cache::rememberForever</code> and instantly updated across all worker processes upon saving.</small>
                </div>
            </div>
            <button type="submit" class="btn btn-primary px-4 py-2.5 fw-bold rounded-3 shadow-sm d-flex align-items-center gap-2" id="btnSaveBottom">
                <i class="fa-solid fa-floppy-disk"></i>
                <span>Save All Settings</span>
            </button>
        </div>
    </div>
</form>

<div class="card p-3 p-md-4 border-0 shadow-sm">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-3 pb-2 border-bottom">
        <div>
            <h6 class="fw-bold mb-1 text-primary d-flex align-items-center gap-2">
                <i class="fa-solid fa-headset"></i>
                <span>Customer Support QA Engine Diagnostic</span>
            </h6>
            <small class="text-muted">Test the primary Customer QA provider response in real-time. For product generation, visit <a href="{{ route('admin.products.ai') }}" class="fw-semibold text-decoration-underline">Catalog &amp; Inventory &rarr; AI Product Generator</a>.</small>
        </div>
        <span id="chatPlayStatus" class="badge bg-secondary-subtle text-secondary rounded-pill px-2.5 py-1 small">Awaiting query</span>
    </div>

    <div class="row g-3">
        <div class="col-lg-5">
            <div class="d-flex flex-column gap-2.5">
                <div>
                    <label class="form-label small fw-bold mb-1">Customer Question (Bangla or English)</label>
                    <textarea id="playChatMessage" class="form-control" rows="4" placeholder="Type a typical customer query...">Do you provide cash on delivery in Chittagong, and how many days does standard delivery take?</textarea>
                </div>
                <button type="button" class="btn btn-success fw-semibold d-flex align-items-center justify-content-center gap-2 text-white" id="btnPlayChat" onclick="runChatPlayground()">
                    <i class="fa-solid fa-paper-plane"></i>
                    <span>Send Query to Support QA</span>
                </button>
            </div>
        </div>

        <div class="col-lg-7">
            <div class="p-3 rounded-3 border bg-body-tertiary h-100 d-flex flex-column">
                <span class="fw-bold small text-muted mb-2 pb-2 border-bottom">Model Response</span>
                <div class="flex-grow-1 overflow-auto p-2" id="chatPlayResult" style="max-height: 240px; font-size: 13px; line-height: 1.6;">
                    <span class="text-muted">Click send to simulate live customer support response.</span>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
(() => {
    function togglePasswordVisibility(inputId, buttonEl) {
        const input = document.getElementById(inputId);
        if (!input) return;
        const icon = buttonEl.querySelector('i');
        if (input.type === 'password') {
            input.type = 'text';
            if (icon) {
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            }
        } else {
            input.type = 'password';
            if (icon) {
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }
    }

    function clearProviderKey(provider) {
        if (provider === 'gemini') {
            document.getElementById('removeGeminiKey').value = '1';
            document.getElementById('geminiApiKey').value = '';
            const b = document.getElementById('badgeGeminiKey');
            if (b) {
                b.className = 'badge bg-danger-subtle text-danger rounded-pill px-2.5 py-1 small';
                b.textContent = 'Removing (Save to apply)';
            }
        } else if (provider === 'groq') {
            document.getElementById('removeGroqKey').value = '1';
            document.getElementById('groqApiKey').value = '';
            const b = document.getElementById('badgeGroqKey');
            if (b) {
                b.className = 'badge bg-danger-subtle text-danger rounded-pill px-2.5 py-1 small';
                b.textContent = 'Removing (Save to apply)';
            }
        } else if (provider === 'openrouter') {
            document.getElementById('removeOpenRouterKey').value = '1';
            document.getElementById('openrouterApiKey').value = '';
            const b = document.getElementById('badgeOpenRouterKey');
            if (b) {
                b.className = 'badge bg-danger-subtle text-danger rounded-pill px-2.5 py-1 small';
                b.textContent = 'Removing (Save to apply)';
            }
        }
    }

    function cancelKeyRemoval(provider) {
        if (provider === 'gemini') {
            document.getElementById('removeGeminiKey').value = '0';
        } else if (provider === 'groq') {
            document.getElementById('removeGroqKey').value = '0';
        } else if (provider === 'openrouter') {
            document.getElementById('removeOpenRouterKey').value = '0';
        }
    }

    function updateKeyBadges() {
        const geminiKeyEl = document.getElementById('geminiApiKey');
        if (!geminiKeyEl) return;
        const geminiKey = geminiKeyEl.value.trim();
        const groqKey = document.getElementById('groqApiKey') ? document.getElementById('groqApiKey').value.trim() : '';
        const openRouterKey = document.getElementById('openrouterApiKey') ? document.getElementById('openrouterApiKey').value.trim() : '';

        const badgeGemini = document.getElementById('badgeGeminiKey');
        if (badgeGemini) {
            badgeGemini.className = 'badge ' + (geminiKey ? 'bg-success-subtle text-success' : 'bg-warning-subtle text-warning') + ' rounded-pill px-2.5 py-1 small';
            badgeGemini.textContent = geminiKey ? 'Configured' : 'No Key';
        }

        const badgeGroq = document.getElementById('badgeGroqKey');
        if (badgeGroq) {
            badgeGroq.className = 'badge ' + (groqKey ? 'bg-success-subtle text-success' : 'bg-warning-subtle text-warning') + ' rounded-pill px-2.5 py-1 small';
            badgeGroq.textContent = groqKey ? 'Configured' : 'No Key';
        }

        const badgeOR = document.getElementById('badgeOpenRouterKey');
        if (badgeOR) {
            badgeOR.className = 'badge ' + (openRouterKey ? 'bg-success-subtle text-success' : 'bg-warning-subtle text-warning') + ' rounded-pill px-2.5 py-1 small';
            badgeOR.textContent = openRouterKey ? 'Configured' : 'No Key';
        }
    }

    document.addEventListener('turbo:load', updateKeyBadges);

    document.addEventListener('submit', function (e) {
        if (!e.target || e.target.id !== 'aiSettingsForm') return;
        e.preventDefault();
        const form = e.target;

        const btnTop = document.getElementById('btnSaveTop');
        const btnBottom = document.getElementById('btnSaveBottom');
        const originalTopHtml = btnTop ? btnTop.innerHTML : '';
        const originalBottomHtml = btnBottom ? btnBottom.innerHTML : '';

        if (btnTop) {
            btnTop.disabled = true;
            btnTop.innerHTML = '<span class="spinner-border spinner-border-sm me-1.5" role="status"></span> Saving...';
        }
        if (btnBottom) {
            btnBottom.disabled = true;
            btnBottom.innerHTML = '<span class="spinner-border spinner-border-sm me-1.5" role="status"></span> Saving...';
        }

        const formData = new FormData(form);

        axios.post(form.action, formData)
            .then(function (response) {
                if (window.showToast) {
                    window.showToast(response.data.message || 'Settings saved successfully.', 'success');
                }
                updateKeyBadges();
            })
            .catch(function (error) {
                const msg = (error.response && error.response.data && error.response.data.message) ? error.response.data.message : 'Error saving settings.';
                if (window.showToast) {
                    window.showToast(msg, 'error');
                }
            })
            .finally(function () {
                if (btnTop) {
                    btnTop.disabled = false;
                    btnTop.innerHTML = originalTopHtml;
                }
                if (btnBottom) {
                    btnBottom.disabled = false;
                    btnBottom.innerHTML = originalBottomHtml;
                }
            });
    });

    function syncLiveModels(provider, btnEl) {
        let apiKey = '';
        let inputEl = null;
        let listEl = null;

        if (provider === 'gemini') {
            apiKey = document.getElementById('geminiApiKey').value.trim();
            inputEl = document.getElementById('geminiModelInput');
            listEl = document.getElementById('geminiModelList');
        } else if (provider === 'groq') {
            apiKey = document.getElementById('groqApiKey').value.trim();
            inputEl = document.getElementById('groqModelInput');
            listEl = document.getElementById('groqModelList');
        } else if (provider === 'openrouter') {
            apiKey = document.getElementById('openrouterApiKey').value.trim();
            inputEl = document.getElementById('openrouterModelInput');
            listEl = document.getElementById('openrouterModelList');
        }

        const originalHtml = btnEl ? btnEl.innerHTML : '';
        if (btnEl) {
            btnEl.disabled = true;
            btnEl.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status"></span>Syncing...';
        }

        axios.post('{{ route("admin.settings.ai.sync_models") }}', {
            provider: provider,
            api_key: apiKey
        })
        .then(function (response) {
            const data = response.data;
            if (data.success && data.models) {
                if (listEl) {
                    let html = '<option value="auto">Auto (Auto-Detect Latest Active Model)</option>';
                    for (const [mId, mLabel] of Object.entries(data.models)) {
                        html += '<option value="' + mId + '">' + mLabel + '</option>';
                    }
                    listEl.innerHTML = html;
                }
                if (inputEl && (!inputEl.value || inputEl.value === 'auto')) {
                    if (data.recommended) {
                        inputEl.value = 'auto';
                    }
                }
                if (window.showToast) {
                    window.showToast(data.message || 'Models synced successfully!', 'success');
                }
            } else {
                if (window.showToast) {
                    window.showToast(data.message || 'Could not fetch live models.', 'warning');
                }
            }
        })
        .catch(function (error) {
            const msg = (error.response && error.response.data && error.response.data.message) ? error.response.data.message : 'Sync failed';
            if (window.showToast) {
                window.showToast('Failed to sync live models: ' + msg, 'error');
            }
        })
        .finally(function () {
            if (btnEl) {
                btnEl.disabled = false;
                btnEl.innerHTML = originalHtml;
            }
        });
    }

    function testProviderConnection(provider) {
        let apiKey = '';
        let model = '';
        let statusEl = null;

        if (provider === 'gemini') {
            apiKey = document.getElementById('geminiApiKey').value.trim();
            model = document.getElementById('geminiModelInput').value.trim();
            statusEl = document.getElementById('geminiTestStatus');
        } else if (provider === 'groq') {
            apiKey = document.getElementById('groqApiKey').value.trim();
            model = document.getElementById('groqModelInput').value.trim();
            statusEl = document.getElementById('groqTestStatus');
        } else if (provider === 'openrouter') {
            apiKey = document.getElementById('openrouterApiKey').value.trim();
            model = document.getElementById('openrouterModelInput').value.trim();
            statusEl = document.getElementById('openrouterTestStatus');
        }

        if (statusEl) {
            statusEl.innerHTML = '<span class="spinner-border spinner-border-sm text-primary me-1" role="status"></span> Testing...';
        }

        axios.post('{{ route("admin.settings.ai.test") }}', {
            provider: provider,
            api_key: apiKey,
            model: model
        })
        .then(function (response) {
            const data = response.data;
            if (data.success) {
                if (statusEl) {
                    statusEl.innerHTML = '<span class="text-success"><i class="fa-solid fa-circle-check me-1"></i> ' + data.latency_ms + 'ms (OK)</span>';
                }
                if (window.showToast) {
                    window.showToast('[' + provider.toUpperCase() + '] Connected successfully in ' + data.latency_ms + 'ms', 'success');
                }
            } else {
                if (statusEl) {
                    statusEl.innerHTML = '<span class="text-danger" title="' + data.message + '"><i class="fa-solid fa-circle-xmark me-1"></i> Failed</span>';
                }
                if (window.showToast) {
                    window.showToast('[' + provider.toUpperCase() + '] Error: ' + data.message, 'error');
                }
            }
        })
        .catch(function (error) {
            const msg = (error.response && error.response.data && error.response.data.message) ? error.response.data.message : 'Request error';
            if (statusEl) {
                statusEl.innerHTML = '<span class="text-danger"><i class="fa-solid fa-circle-xmark me-1"></i> Error</span>';
            }
            if (window.showToast) {
                window.showToast('Test failed: ' + msg, 'error');
            }
        });
    }

    function runChatPlayground() {
        const message = document.getElementById('playChatMessage').value.trim();
        const btn = document.getElementById('btnPlayChat');
        const statusEl = document.getElementById('chatPlayStatus');
        const resultEl = document.getElementById('chatPlayResult');

        if (!message) {
            if (window.showToast) window.showToast('Please enter a customer message.', 'warning');
            return;
        }

        const originalBtnHtml = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1.5" role="status"></span> Thinking...';

        if (statusEl) {
            statusEl.className = 'badge bg-primary-subtle text-primary rounded-pill px-2 py-0.5 small';
            statusEl.textContent = 'Querying model...';
        }

        axios.post('{{ route("admin.settings.ai.playground.chat") }}', {
            message: message
        })
        .then(function (response) {
            const res = response.data;
            if (res.success) {
                const failoverText = res.was_failover ? ' (Failover to ' + res.provider_used + ')' : ' (Via ' + res.provider_used + ')';
                if (statusEl) {
                    statusEl.className = 'badge ' + (res.was_failover ? 'bg-warning-subtle text-warning' : 'bg-success-subtle text-success') + ' rounded-pill px-2 py-0.5 small';
                    statusEl.textContent = 'Answered' + failoverText;
                }
                if (resultEl) {
                    resultEl.innerHTML = '<div class="p-3 rounded-3 bg-body border"><strong class="d-block mb-1 text-success"><i class="fa-solid fa-robot me-1"></i> Zippy AI:</strong>' + (res.data || '').replace(/\n/g, '<br>') + '</div>';
                }
                if (window.showToast) {
                    window.showToast('Response received!', 'success');
                }
            } else {
                if (statusEl) {
                    statusEl.className = 'badge bg-danger-subtle text-danger rounded-pill px-2 py-0.5 small';
                    statusEl.textContent = 'Execution failed';
                }
                if (resultEl) {
                    resultEl.innerHTML = '<div class="p-3 rounded-3 bg-danger-subtle text-danger border">' + (res.error || 'An error occurred.') + '</div>';
                }
                if (window.showToast) {
                    window.showToast(res.error || 'Failed to get answer.', 'error');
                }
            }
        })
        .catch(function (error) {
            const msg = (error.response && error.response.data && error.response.data.message) ? error.response.data.message : 'Chat query failed.';
            if (statusEl) {
                statusEl.className = 'badge bg-danger-subtle text-danger rounded-pill px-2 py-0.5 small';
                statusEl.textContent = 'Error';
            }
            if (resultEl) {
                resultEl.innerHTML = '<div class="p-3 rounded-3 bg-danger-subtle text-danger border">' + msg + '</div>';
            }
            if (window.showToast) {
                window.showToast(msg, 'error');
            }
        })
        .finally(function () {
            btn.disabled = false;
            btn.innerHTML = originalBtnHtml;
        });
    }

    window.togglePasswordVisibility = togglePasswordVisibility;
    window.clearProviderKey = clearProviderKey;
    window.cancelKeyRemoval = cancelKeyRemoval;
    window.updateKeyBadges = updateKeyBadges;
    window.syncLiveModels = syncLiveModels;
    window.testProviderConnection = testProviderConnection;
    window.runChatPlayground = runChatPlayground;
})();
</script>
@endsection
