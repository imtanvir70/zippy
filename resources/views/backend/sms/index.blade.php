@extends('backend.layouts.app')

@section('title', 'SMS Gateway Settings')

@section('content')
<div class="card p-3 p-md-4 mb-4">
    <h3 class="fw-bold mb-1">SMS &amp; Notifications Setup</h3>
    <p class="text-muted small mb-0">Configure your SMS API credentials and customer notification triggers.</p>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card p-3 p-md-4">
            <h5 class="fw-bold mb-3">Gateway Credentials</h5>
            <form action="{{ route('admin.sms.update') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="is_active" id="isActive" value="1" {{ $setting->is_active ? 'checked' : '' }}>
                        <label class="form-check-label fw-bold" for="isActive">Enable Automated SMS Dispatch</label>
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">SMS Provider</label>
                        <select name="provider" class="form-select">
                            <option value="greenweb" {{ $setting->provider === 'greenweb' ? 'selected' : '' }}>Greenweb BD</option>
                            <option value="mimsms" {{ $setting->provider === 'mimsms' ? 'selected' : '' }}>MIM SMS</option>
                            <option value="adn" {{ $setting->provider === 'adn' ? 'selected' : '' }}>ADN Telecom</option>
                            <option value="custom" {{ $setting->provider === 'custom' ? 'selected' : '' }}>Custom Gateway API</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Sender ID / Masking</label>
                        <input type="text" name="sender_id" class="form-control" value="{{ $setting->sender_id }}" placeholder="e.g. Zippy">
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">API Endpoint URL</label>
                        <input type="text" name="api_url" class="form-control" value="{{ $setting->api_url }}" placeholder="http://api.greenweb.com.bd/api.php">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">API Key / Token</label>
                        <input type="password" name="api_key" class="form-control font-monospace" value="{{ $setting->api_key }}" placeholder="Enter provider API Token">
                    </div>
                </div>

                <hr class="my-4">
                <h5 class="fw-bold mb-3">Automated Order Triggers &amp; Templates</h5>

                <div class="mb-3">
                    <div class="form-check form-switch mb-1">
                        <input class="form-check-input" type="checkbox" name="notify_on_order_placed" id="nPlaced" value="1" {{ $setting->notify_on_order_placed ? 'checked' : '' }}>
                        <label class="form-check-label fw-semibold" for="nPlaced">Send SMS when Order is Placed (Pending)</label>
                    </div>
                    <textarea name="order_placed_template" class="form-control" rows="2">{{ $setting->order_placed_template }}</textarea>
                    <small class="text-muted">Tags: <code>{name}</code>, <code>{order_number}</code>, <code>{total}</code></small>
                </div>

                <div class="mb-3">
                    <div class="form-check form-switch mb-1">
                        <input class="form-check-input" type="checkbox" name="notify_on_order_shipped" id="nShipped" value="1" {{ $setting->notify_on_order_shipped ? 'checked' : '' }}>
                        <label class="form-check-label fw-semibold" for="nShipped">Send SMS when Order is Shipped</label>
                    </div>
                    <textarea name="order_shipped_template" class="form-control" rows="2">{{ $setting->order_shipped_template }}</textarea>
                    <small class="text-muted">Tags: <code>{name}</code>, <code>{order_number}</code>, <code>{courier}</code></small>
                </div>

                <div class="mb-4">
                    <div class="form-check form-switch mb-1">
                        <input class="form-check-input" type="checkbox" name="notify_on_order_delivered" id="nDelivered" value="1" {{ $setting->notify_on_order_delivered ? 'checked' : '' }}>
                        <label class="form-check-label fw-semibold" for="nDelivered">Send SMS when Order is Delivered</label>
                    </div>
                    <textarea name="order_delivered_template" class="form-control" rows="2">{{ $setting->order_delivered_template }}</textarea>
                    <small class="text-muted">Tags: <code>{name}</code>, <code>{order_number}</code></small>
                </div>

                <button type="submit" class="btn btn-primary px-4">Save SMS Settings</button>
            </form>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card p-3 p-md-4">
            <h5 class="fw-bold mb-3">Send Test SMS</h5>
            <p class="text-muted small">Verify your API connection and message delivery directly to your phone.</p>
            <div class="mb-3">
                <label class="form-label small fw-bold">Recipient Mobile</label>
                <input type="text" id="testPhone" class="form-control" placeholder="017xxxxxxxx">
            </div>
            <div class="mb-3">
                <label class="form-label small fw-bold">Test Message</label>
                <textarea id="testMessage" class="form-control" rows="3">This is a test notification from Zippy Store.</textarea>
            </div>
            <button type="button" class="btn btn-outline-primary w-100" onclick="sendTestSms()">
                <i class="fa-solid fa-paper-plane me-1"></i> Send Test Message
            </button>
            <div id="testResult" class="mt-3 small" style="display:none;"></div>
        </div>
    </div>
</div>

<script>
(() => {
    function sendTestSms() {
        const phone = document.getElementById('testPhone').value;
        const message = document.getElementById('testMessage').value;
        const resultBox = document.getElementById('testResult');

        if (!phone) {
            if (window.showToast) window.showToast('Please enter a recipient mobile number.', 'warning');
            return;
        }

        resultBox.style.display = 'block';
        resultBox.className = 'mt-3 small text-muted';
        resultBox.innerText = 'Sending test message...';

        axios.post('{{ route('admin.sms.test') }}', {
            test_phone: phone,
            test_message: message
        })
        .then(res => {
            const data = res.data;
            if (data.success) {
                resultBox.className = 'mt-3 small text-success fw-bold';
                resultBox.innerText = 'SMS Sent successfully!';
            } else {
                resultBox.className = 'mt-3 small text-danger';
                resultBox.innerText = 'Failed: ' + (data.message || data.response || 'Gateway error');
            }
        })
        .catch(err => {
            resultBox.className = 'mt-3 small text-danger';
            resultBox.innerText = 'Connection error.';
        });
    }

    window.sendTestSms = sendTestSms;
})();
</script>
@endsection

