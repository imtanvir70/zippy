@extends('backend.layouts.app')

@section('title', 'WhatsApp & SMS Notifications')

@section('content')
<div class="card p-3 p-md-4 mb-4">
    <h3 class="fw-bold mb-1">WhatsApp &amp; SMS Commerce Settings</h3>
    <p class="text-muted small mb-0">Manage WhatsApp and SMS API keys for automated order notifications and cart recovery.</p>
</div>

<form action="{{ route('admin.notification_settings.update') }}" method="POST">
    @csrf
    <div class="row g-4 mb-4">
        <div class="col-lg-6">
            <div class="card p-3 p-md-4 h-100">
                <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                    <h5 class="fw-bold mb-0 text-success">
                        <i class="fa-brands fa-whatsapp me-2"></i> WhatsApp API
                    </h5>
                    <div class="form-check form-switch mb-0">
                        <input class="form-check-input" type="checkbox" name="whatsapp_enabled" id="waEnabled" value="1" {{ $setting->whatsapp_enabled ? 'checked' : '' }}>
                        <label class="form-check-label fw-bold" for="waEnabled">Enable</label>
                    </div>
                </div>
                <div class="d-flex flex-column gap-3">
                    <div>
                        <label class="form-label small fw-bold">WhatsApp API Endpoint URL</label>
                        <input type="text" name="whatsapp_api_url" class="form-control" value="{{ $setting->whatsapp_api_url }}" placeholder="https://api.whatsapp.com/v1/messages">
                    </div>
                    <div>
                        <label class="form-label small fw-bold">WhatsApp API Access Token</label>
                        <div class="input-group">
                            <input type="password" name="whatsapp_api_token" id="waToken" class="form-control font-monospace" value="{{ $setting->whatsapp_api_token }}" placeholder="Bearer access token">
                            <button type="button" class="btn btn-outline-secondary" onclick="toggleVisibility('waToken', 'waIcon')">
                                <i class="fa-solid fa-eye" id="waIcon"></i>
                            </button>
                        </div>
                    </div>
                    <div>
                        <label class="form-label small fw-bold">WhatsApp Sender / Phone Number ID</label>
                        <input type="text" name="whatsapp_from_phone" class="form-control" value="{{ $setting->whatsapp_from_phone }}" placeholder="e.g. 8801700000000">
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card p-3 p-md-4 h-100">
                <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                    <h5 class="fw-bold mb-0 text-primary">
                        <i class="fa-solid fa-comment-sms me-2"></i> SMS Gateway
                    </h5>
                    <div class="form-check form-switch mb-0">
                        <input class="form-check-input" type="checkbox" name="sms_enabled" id="smsEnabled" value="1" {{ $setting->sms_enabled ? 'checked' : '' }}>
                        <label class="form-check-label fw-bold" for="smsEnabled">Enable</label>
                    </div>
                </div>
                <div class="d-flex flex-column gap-3">
                    <div>
                        <label class="form-label small fw-bold">SMS Provider</label>
                        <select name="sms_provider" class="form-select">
                            <option value="greenweb" {{ $setting->sms_provider === 'greenweb' ? 'selected' : '' }}>GreenWeb BD</option>
                            <option value="mimsms" {{ $setting->sms_provider === 'mimsms' ? 'selected' : '' }}>MIM SMS</option>
                            <option value="adn" {{ $setting->sms_provider === 'adn' ? 'selected' : '' }}>ADN Telecom</option>
                            <option value="custom" {{ $setting->sms_provider === 'custom' ? 'selected' : '' }}>Custom Gateway</option>
                        </select>
                    </div>
                    <div>
                        <label class="form-label small fw-bold">SMS API Endpoint URL</label>
                        <input type="text" name="sms_api_url" class="form-control" value="{{ $setting->sms_api_url }}" placeholder="http://api.greenweb.com.bd/api.php">
                    </div>
                    <div>
                        <label class="form-label small fw-bold">SMS API Key / Token</label>
                        <div class="input-group">
                            <input type="password" name="sms_api_key" id="smsKey" class="form-control font-monospace" value="{{ $setting->sms_api_key }}" placeholder="Enter API Key">
                            <button type="button" class="btn btn-outline-secondary" onclick="toggleVisibility('smsKey', 'smsIcon')">
                                <i class="fa-solid fa-eye" id="smsIcon"></i>
                            </button>
                        </div>
                    </div>
                    <div>
                        <label class="form-label small fw-bold">Sender ID / Masking</label>
                        <input type="text" name="sms_sender_id" class="form-control" value="{{ $setting->sms_sender_id }}" placeholder="e.g. ZippyBD">
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="card p-3 p-md-4">
                <h5 class="fw-bold mb-3 border-bottom pb-2">Automation Triggers &amp; Message Templates</h5>
                <div class="row g-4">
                    <div class="col-md-6">
                        <div class="form-check form-switch mb-2">
                            <input class="form-check-input" type="checkbox" name="notify_on_order_status" id="nOrder" value="1" {{ $setting->notify_on_order_status ? 'checked' : '' }}>
                            <label class="form-check-label fw-bold" for="nOrder">Send Notification on Order Status Changes</label>
                        </div>
                        <label class="form-label small fw-semibold">Template:</label>
                        <textarea name="order_status_template" class="form-control" rows="3">{{ $setting->order_status_template }}</textarea>
                        <small class="text-muted">Variables: <code>{name}</code>, <code>{order_number}</code>, <code>{status}</code>, <code>{total}</code></small>
                    </div>
                    <div class="col-md-6">
                        <div class="form-check form-switch mb-2">
                            <input class="form-check-input" type="checkbox" name="notify_on_abandoned_cart" id="nCart" value="1" {{ $setting->notify_on_abandoned_cart ? 'checked' : '' }}>
                            <label class="form-check-label fw-bold" for="nCart">Send Reminder on Abandoned Carts</label>
                        </div>
                        <label class="form-label small fw-semibold">Template:</label>
                        <textarea name="abandoned_cart_template" class="form-control" rows="3">{{ $setting->abandoned_cart_template }}</textarea>
                        <small class="text-muted">Variables: <code>{name}</code>, <code>{checkout_url}</code></small>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12">
            <button type="submit" class="btn btn-primary px-4 py-2 fw-bold">
                <i class="fa-solid fa-floppy-disk me-1"></i> Save Notification Settings
            </button>
        </div>
    </div>
</form>

<script>
(() => {
    function toggleVisibility(inputId, iconId) {
        const input = document.getElementById(inputId);
        const icon = document.getElementById(iconId);
        if (!input || !icon) return;
        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    }

    window.toggleVisibility = toggleVisibility;
})();
</script>
@endsection

