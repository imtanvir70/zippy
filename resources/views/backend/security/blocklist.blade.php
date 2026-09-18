@extends('backend.layouts.app')

@section('title', 'Security & Fraud Blocklist')

@section('content')
<div class="card p-3 p-md-4 mb-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <h3 class="fw-bold mb-1">Security & Fraud Blocklist</h3>
            <p class="text-muted small mb-0">Manage blocked mobile phone numbers and suspicious IP addresses to prevent fake orders and abuse.</p>
        </div>
        <button type="button" class="btn btn-danger d-inline-flex align-items-center gap-2 px-3 py-2 rounded-3 fw-semibold shadow-sm" data-bs-toggle="modal" data-bs-target="#addBlocklistModal">
            <i class="fa-solid fa-user-slash"></i>
            <span>Add to Blocklist</span>
        </button>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-3 col-6">
        <div class="card stat-card text-center p-3">
            <small class="text-muted d-block mb-1">Total Entries</small>
            <h3 class="fw-bold mb-0 text-dark">{{ number_format($stats['total']) }}</h3>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="card stat-card text-center p-3">
            <small class="text-muted d-block mb-1">Active Blocked</small>
            <h3 class="fw-bold mb-0 text-danger">{{ number_format($stats['active_blocked']) }}</h3>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="card stat-card text-center p-3">
            <small class="text-muted d-block mb-1">Blocked Phones</small>
            <h3 class="fw-bold mb-0 text-warning">{{ number_format($stats['phones']) }}</h3>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="card stat-card text-center p-3">
            <small class="text-muted d-block mb-1">Blocked IPs</small>
            <h3 class="fw-bold mb-0 text-info">{{ number_format($stats['ips']) }}</h3>
        </div>
    </div>
</div>

<div class="card p-3 p-md-4 mb-4">
    <form method="GET" action="{{ route('admin.blocklist.index') }}" class="row g-2 align-items-center">
        <div class="col-md-5">
            <div class="input-group">
                <span class="input-group-text bg-white border-end-0 text-muted"><i class="fa-solid fa-magnifying-glass"></i></span>
                <input type="text" name="q" class="form-control border-start-0" placeholder="Search by phone, IP, or reason..." value="{{ request('q') }}">
            </div>
        </div>
        <div class="col-md-3">
            <select name="type" class="form-select" onchange="this.form.submit()">
                <option value="">All Types (Phone & IP)</option>
                <option value="phone" {{ request('type') === 'phone' ? 'selected' : '' }}>Phone Numbers Only</option>
                <option value="ip" {{ request('type') === 'ip' ? 'selected' : '' }}>IP Addresses Only</option>
            </select>
        </div>
        <div class="col-md-4 d-flex gap-2">
            <button type="submit" class="btn btn-primary px-3">Filter</button>
            @if(request()->hasAny(['q', 'type']))
                <a href="{{ route('admin.blocklist.index') }}" class="btn btn-outline-secondary px-3">Reset</a>
            @endif
        </div>
    </form>
</div>

<div class="card p-3 p-md-4">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr class="small text-muted">
                    <th>Type</th>
                    <th>Value</th>
                    <th>Reason / Notes</th>
                    <th>Status</th>
                    <th>Blocked At</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($entries as $entry)
                <tr>
                    <td>
                        @if($entry->type === 'phone')
                            <span class="badge bg-warning-subtle text-dark border border-warning-subtle px-2.5 py-1 rounded-pill">
                                <i class="fa-solid fa-phone me-1 text-warning"></i> Phone
                            </span>
                        @else
                            <span class="badge bg-info-subtle text-info border border-info-subtle px-2.5 py-1 rounded-pill">
                                <i class="fa-solid fa-network-wired me-1 text-info"></i> IP Address
                            </span>
                        @endif
                    </td>
                    <td>
                        <span class="fw-bold font-monospace text-dark fs-14">{{ $entry->value }}</span>
                    </td>
                    <td>
                        <span class="small text-secondary">{{ $entry->reason ?: 'Flagged for fraudulent activity' }}</span>
                    </td>
                    <td>
                        @if($entry->is_blocked)
                            <span class="badge bg-danger text-white rounded-pill px-2.5 py-1 font-monospace">BLOCKED</span>
                        @else
                            <span class="badge bg-secondary-subtle text-secondary rounded-pill px-2.5 py-1 font-monospace">INACTIVE</span>
                        @endif
                    </td>
                    <td>
                        <small class="text-muted font-monospace">{{ date('d M Y, h:i A', strtotime($entry->created_at)) }}</small>
                    </td>
                    <td class="text-end">
                        <div class="d-inline-flex gap-2">
                            <form action="{{ route('admin.blocklist.toggle', $entry->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-sm {{ $entry->is_blocked ? 'btn-outline-success' : 'btn-outline-warning' }}" title="{{ $entry->is_blocked ? 'Unblock' : 'Block' }}">
                                    <i class="fa-solid {{ $entry->is_blocked ? 'fa-lock-open' : 'fa-ban' }}"></i>
                                </button>
                            </form>
                            <form action="{{ route('admin.blocklist.destroy', $entry->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to remove this entry from blocklist?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center py-5 text-muted">
                        <i class="fa-solid fa-shield-check fs-1 text-success mb-2 d-block"></i>
                        <span>No blocked phone numbers or IP addresses found.</span>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">
        {{ $entries->links() }}
    </div>
</div>

<div class="modal fade" id="addBlocklistModal" tabindex="-1" aria-labelledby="addBlocklistModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <form action="{{ route('admin.blocklist.store') }}" method="POST">
                @csrf
                <div class="modal-header bg-light border-bottom px-4 py-3">
                    <h5 class="modal-title fw-bold text-dark" id="addBlocklistModalLabel">
                        <i class="fa-solid fa-shield-halved text-danger me-2"></i>Add to Blocklist
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark">Blocklist Type <span class="text-danger">*</span></label>
                        <select name="type" id="blockTypeSelect" class="form-select" required onchange="handleBlockTypeChange(this.value)">
                            <option value="phone">Phone Number (মোবাইল নম্বর)</option>
                            <option value="ip">IP Address (আইপি অ্যাড্রেস)</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark" id="blockValueLabel">Phone Number <span class="text-danger">*</span></label>
                        <input type="text" name="value" id="blockValueInput" class="form-control font-monospace" placeholder="e.g. 017XXXXXXXX" required>
                        <small class="text-muted d-block mt-1" id="blockValueHint">Enter Bangladeshi mobile number (01XXXXXXXXX).</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark">Reason / Notes</label>
                        <textarea name="reason" rows="2" class="form-control" placeholder="e.g. Repeated fake COD orders, fraudulent chargebacks, etc."></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light border-top px-4 py-3">
                    <button type="button" class="btn btn-secondary px-4 rounded-pill" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger px-4 rounded-pill fw-semibold">Confirm Block</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function handleBlockTypeChange(type) {
    const label = document.getElementById('blockValueLabel');
    const input = document.getElementById('blockValueInput');
    const hint = document.getElementById('blockValueHint');
    if (type === 'ip') {
        label.innerHTML = 'IP Address <span class="text-danger">*</span>';
        input.placeholder = 'e.g. 103.145.74.22';
        hint.innerText = 'Enter IPv4 or IPv6 address to block.';
    } else {
        label.innerHTML = 'Phone Number <span class="text-danger">*</span>';
        input.placeholder = 'e.g. 017XXXXXXXX';
        hint.innerText = 'Enter Bangladeshi mobile number (01XXXXXXXXX).';
    }
}
</script>
@endsection

