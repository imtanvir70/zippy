@extends('backend.layouts.app')

@section('title', 'Order Details: #' . $order->order_number)

@section('content')
<div class="card p-3 p-md-4 mb-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <a href="{{ route('admin.orders.index') }}" class="text-muted small text-decoration-none"><i class="fa-solid fa-arrow-left me-1"></i> Back to Orders</a>
            </div>
            <h3 class="fw-bold mb-0">
                Order #{{ $order->order_number }}
                <span class="badge bg-{{ $order->order_status === 'delivered' ? 'success' : ($order->order_status === 'pending' ? 'warning' : 'primary') }}-subtle text-{{ $order->order_status === 'delivered' ? 'success' : ($order->order_status === 'pending' ? 'dark' : 'primary') }} ms-2 fs-6 rounded-pill px-3 py-1">
                    {{ ucfirst($order->order_status) }}
                </span>
            </h3>
            <small class="text-muted">Placed on: {{ \Carbon\Carbon::parse($order->created_at)->format('d F Y, h:i A') }}</small>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            @canPerm('admin.logistics.dispatch')
                <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#courierDispatchModal">
                    <i class="fa-solid fa-truck-fast me-1"></i> Dispatch Courier
                </button>
            @endcanPerm
            @canPerm('admin.refunds.store')
                <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#refundModal">
                    <i class="fa-solid fa-rotate-left me-1"></i> Issue Refund
                </button>
            @endcanPerm
            @canPerm('admin.orders.packing_slip')
                <button type="button" onclick="printInvoiceDirect('{{ route('admin.orders.packing_slip', $order->id) }}')" class="btn btn-sm btn-outline-secondary">
                    <i class="fa-solid fa-clipboard-check me-1"></i> Packing Slip
                </button>
            @endcanPerm
            @canPerm('admin.orders.print')
                <button type="button" onclick="printInvoiceDirect('{{ route('admin.orders.invoice', $order->id) }}')" class="btn btn-sm btn-outline-secondary">
                    <i class="fa-solid fa-print me-1"></i> Invoice (Print)
                </button>
            @endcanPerm
        </div>
    </div>
</div>

<iframe id="printInvoiceFrame" style="position: absolute; width: 0; height: 0; border: 0; visibility: hidden;"></iframe>

@php
    $cData = $courierProfile['data'] ?? null;
    $cSummary = $cData['summary'] ?? null;
    $cReports = $courierProfile['reports'] ?? [];
    $riskColor = ($order->fraud_score ?? 0) >= 65 ? 'danger' : (($order->fraud_score ?? 0) >= 35 ? 'warning' : 'success');
@endphp
<div class="card p-3 p-md-4 mb-4 border-start border-4 border-{{ $riskColor }} shadow-sm bg-white rounded-4">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
        <div class="d-flex align-items-center gap-3">
            <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 44px; height: 44px; font-size: 1.25rem; background: rgba(var(--bs-{{ $riskColor }}-rgb), 0.15); color: var(--bs-{{ $riskColor }});">
                <i class="fa-solid fa-shield-halved"></i>
            </div>
            <div>
                <div class="d-flex align-items-center gap-2">
                    <h5 class="fw-bold mb-0">Fraud Risk: {{ $order->fraud_score ?? 0 }}/100</h5>
                    <span class="badge bg-{{ $riskColor }} rounded-pill px-2.5 py-1 text-uppercase">{{ str_replace('_', ' ', $order->fraud_status ?? 'SAFE') }}</span>
                    @if($cSummary && $cSummary['total_parcel'] > 0)
                        <span class="badge bg-dark-subtle text-dark border rounded-pill px-2.5 py-1 small">
                            <i class="fa-solid fa-truck-fast me-1"></i> BDCourier Live Accuracy: {{ $cSummary['success_ratio'] }}% Success
                        </span>
                    @endif
                </div>
                <small class="text-muted d-block mt-0.5">{{ $order->fraud_notes ?? 'No suspicious activity detected for this order.' }}</small>
            </div>
        </div>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-sm btn-outline-primary" onclick="reEvaluateCurrentOrder({{ $order->id }})">
                <i class="fa-solid fa-arrows-rotate me-1"></i> Re-scan Fraud Engine
            </button>
            <a href="{{ route('admin.fraud.index') }}" class="btn btn-sm btn-outline-secondary">Fraud Engine</a>
        </div>
    </div>

    @if($cData)
        <div class="p-3 bg-light rounded-3 border mt-2">
            <div class="d-flex align-items-center justify-content-between mb-2 pb-2 border-bottom">
                <div class="fw-bold small text-dark d-flex align-items-center gap-2">
                    <i class="fa-solid fa-chart-pie text-primary"></i> Nationwide Courier Performance (Phone: {{ $order->customer_phone }})
                </div>
                @if($cSummary)
                    <div class="small fw-semibold">
                        Total Parcels: <span class="badge bg-secondary">{{ $cSummary['total_parcel'] }}</span>
                        | Delivered: <span class="badge bg-success">{{ $cSummary['success_parcel'] }}</span>
                        | Cancelled/Returned: <span class="badge bg-danger">{{ $cSummary['cancelled_parcel'] }}</span>
                        | Success Rate: <span class="badge bg-{{ $cSummary['success_ratio'] >= 80 ? 'success' : ($cSummary['success_ratio'] >= 50 ? 'warning' : 'danger') }}">{{ $cSummary['success_ratio'] }}%</span>
                    </div>
                @endif
            </div>

            <div class="d-flex flex-wrap gap-2 pt-1">
                @foreach(['steadfast', 'pathao', 'redx', 'paperfly', 'carrybee', 'courrierfast', 'parceldex'] as $cSlug)
                    @if(isset($cData[$cSlug]) && $cData[$cSlug]['total_parcel'] > 0)
                        @php $carrier = $cData[$cSlug]; @endphp
                        <div class="border rounded-2 p-2 bg-white d-flex align-items-center gap-2 shadow-xs" style="min-width: 140px;">
                            @if(!empty($carrier['logo']))
                                <img src="{{ $carrier['logo'] }}" style="width: 22px; height: 22px; object-fit: contain;" alt="{{ $carrier['name'] }}" onerror="this.style.display='none'">
                            @endif
                            <div style="line-height: 1.1;">
                                <div class="fw-bold small" style="font-size: 0.75rem;">{{ $carrier['name'] }}</div>
                                <div class="text-muted" style="font-size: 0.7rem;">
                                    <span class="text-success fw-bold">{{ $carrier['success_parcel'] }}✓</span> / <span class="text-danger fw-bold">{{ $carrier['cancelled_parcel'] }}✕</span> ({{ $carrier['success_ratio'] }}%)
                                </div>
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>

            @if(!empty($cReports))
                <div class="mt-3 pt-2 border-top">
                    <div class="fw-bold text-danger small mb-1.5 d-flex align-items-center gap-1">
                        <i class="fa-solid fa-triangle-exclamation"></i> Verified Merchant Fraud Reports ({{ count($cReports) }}):
                    </div>
                    <div class="d-flex flex-column gap-2">
                        @foreach($cReports as $rep)
                            <div class="p-2.5 rounded-2 bg-danger-subtle border border-danger-subtle text-danger-emphasis small">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <strong><i class="fa-solid fa-user-xmark me-1"></i> Reported by: {{ $rep['name'] ?? 'Merchant' }} (via {{ $rep['courierName'] ?? 'Courier' }})</strong>
                                    <span class="text-muted text-xs" style="font-size: 0.72rem;">{{ !empty($rep['created_at']) ? \Carbon\Carbon::parse($rep['created_at'])->format('d M Y') : '' }}</span>
                                </div>
                                <div class="fst-italic">"{{ $rep['details'] ?? 'Reported as return/fraudulent customer' }}"</div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    @endif
</div>

<div class="row g-4 mb-4">
    <div class="col-lg-8">
        <div class="card p-3 p-md-4 table-card mb-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="fw-bold mb-0">
                    <i class="fa-solid fa-bag-shopping text-primary me-2"></i> Ordered Items ({{ count($items) }} items)
                </h6>
                <a href="{{ route('admin.orders.edit_items', $order->id) }}" class="btn btn-sm btn-outline-primary">
                    <i class="fa-solid fa-pen-to-square me-1"></i> Edit Items
                </a>
            </div>
            <div class="table-responsive">
                <table class="table table-striped table-hover table-bordered align-middle" style="width: 100%;">
                    <thead>
                        <tr>
                            <th>Product Details</th>
                            <th>Unit Price</th>
                            <th>Quantity</th>
                            <th style="text-align: right;">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($items as $item)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-3">
                                        <img src="{{ product_image_url($item->product_image) }}" alt="" class="rounded-2 border" style="width: 44px; height: 44px; object-fit: cover; flex-shrink: 0;" onerror="this.onerror=null;this.src='{{ asset('images/product-placeholder.svg') }}';">
                                        <div>
                                            <div class="fw-bold small">{{ $item->product_title }}</div>
                                            <small class="text-muted">Item ID: #{{ $item->product_id }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>৳ {{ number_format($item->unit_price, 0) }}</td>
                                <td><span class="badge bg-secondary">{{ $item->quantity }} Units</span></td>
                                <td style="text-align: right;"><strong class="text-dark">৳ {{ number_format($item->total_price, 0) }}</strong></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="p-3 border-top bg-light">
                <div class="row justify-content-end">
                    <div class="col-md-6">
                        <div class="d-flex justify-content-between py-1 small text-secondary">
                            <span>Subtotal:</span>
                            <strong class="text-dark">৳ {{ number_format($order->subtotal, 0) }}</strong>
                        </div>
                        <div class="d-flex justify-content-between py-1 small text-secondary">
                            <span>Shipping Fee:</span>
                            <strong class="text-dark">৳ {{ number_format($order->shipping_cost, 0) }}</strong>
                        </div>
                        @if($order->discount > 0)
                            <div class="d-flex justify-content-between py-1 small text-success">
                                <span>Coupon Discount:</span>
                                <strong>- ৳ {{ number_format($order->discount, 0) }}</strong>
                            </div>
                        @endif
                        @if($order->refunded_amount > 0)
                            <div class="d-flex justify-content-between py-1 small text-danger">
                                <span>Refunded Amount:</span>
                                <strong>- ৳ {{ number_format($order->refunded_amount, 0) }}</strong>
                            </div>
                        @endif
                        <div class="d-flex justify-content-between py-2 border-top mt-2 fs-5">
                            <span class="fw-bold">Total Payable:</span>
                            <strong class="text-dark fw-bold">৳ {{ number_format($order->total, 0) }}</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @if($order->notes)
            <div class="card p-3 mb-4">
                <h6 class="fw-bold mb-2"><i class="fa-regular fa-note-sticky me-2 text-warning"></i> Customer Notes:</h6>
                <p class="text-secondary small mb-0">{{ $order->notes }}</p>
            </div>
        @endif
    </div>

    <div class="col-lg-4">
        <div class="d-flex flex-column gap-4">
            <div class="card p-3 p-md-4">
                <h6 class="fw-bold mb-3 border-bottom pb-2">
                    <i class="fa-solid fa-truck-ramp-box text-primary me-2"></i> Courier Logistics
                </h6>
                <div class="d-flex flex-column gap-2 small">
                    <div>
                        <span class="text-muted d-block" style="font-size: 0.75rem;">Assigned Courier:</span>
                        <strong class="text-uppercase">{{ $order->courier_provider ?? 'Not yet dispatched' }}</strong>
                    </div>
                    @if($order->courier_tracking_code)
                        <div>
                            <span class="text-muted d-block" style="font-size: 0.75rem;">Tracking Code:</span>
                            <span class="fw-bold font-monospace text-primary fs-6">{{ $order->courier_tracking_code }}</span>
                        </div>
                        <div>
                            <span class="text-muted d-block" style="font-size: 0.75rem;">Courier Status:</span>
                            <span class="badge bg-info-subtle text-info rounded-pill px-2.5 py-1">{{ ucfirst($order->courier_status ?? 'Pending') }}</span>
                        </div>
                    @endif
                </div>
            </div>

            <div class="card p-3 p-md-4">
                <h6 class="fw-bold mb-3 border-bottom pb-2">
                    <i class="fa-solid fa-sliders text-primary me-2"></i> Update Order Status
                </h6>
                <form action="{{ route('admin.orders.status', $order->id) }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Fulfillment Status:</label>
                        <select name="order_status" class="form-select">
                            <option value="pending" {{ $order->order_status === 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="processing" {{ $order->order_status === 'processing' ? 'selected' : '' }}>Processing</option>
                            <option value="shipped" {{ $order->order_status === 'shipped' ? 'selected' : '' }}>Shipped (In Courier)</option>
                            <option value="delivered" {{ $order->order_status === 'delivered' ? 'selected' : '' }}>Delivered (Completed)</option>
                            <option value="cancelled" {{ $order->order_status === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Payment Status:</label>
                        <select name="payment_status" class="form-select">
                            <option value="unpaid" {{ $order->payment_status === 'unpaid' ? 'selected' : '' }}>Unpaid / Cash on Delivery</option>
                            <option value="paid" {{ $order->payment_status === 'paid' ? 'selected' : '' }}>Paid</option>
                            <option value="refunded" {{ $order->payment_status === 'refunded' ? 'selected' : '' }}>Refunded</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Internal Remarks:</label>
                        <textarea name="notes" class="form-control" rows="2" placeholder="Internal remarks...">{{ $order->notes }}</textarea>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 py-2.5 fw-bold">
                        <i class="fa-solid fa-check me-1"></i> Save Changes
                    </button>
                </form>
            </div>

            <div class="card p-3 p-md-4">
                <h6 class="fw-bold mb-3 border-bottom pb-2">
                    <i class="fa-solid fa-circle-user text-primary me-2"></i> Customer Details
                </h6>
                <div class="d-flex flex-column gap-2 small">
                    <div>
                        <span class="text-muted d-block" style="font-size: 0.75rem;">Full Name:</span>
                        <strong class="fs-6">{{ $order->customer_name }}</strong>
                    </div>
                    <div>
                        <span class="text-muted d-block" style="font-size: 0.75rem;">Contact Number:</span>
                        <a href="tel:{{ $order->customer_phone }}" class="text-primary fw-semibold fs-6">
                            <i class="fa-solid fa-phone me-1"></i>{{ $order->customer_phone }}
                        </a>
                    </div>
                    <div>
                        <span class="text-muted d-block" style="font-size: 0.75rem;">District:</span>
                        <span class="badge bg-secondary">{{ $order->district }}</span>
                    </div>
                    <div>
                        <span class="text-muted d-block" style="font-size: 0.75rem;">Delivery Address:</span>
                        <div class="p-2 border rounded-2 bg-light mt-1">
                            {{ $order->customer_address }}
                        </div>
                    </div>
                    <div>
                        <span class="text-muted d-block" style="font-size: 0.75rem;">Payment Method:</span>
                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill">
                            {{ $order->payment_method === 'cod' ? 'Cash on Delivery (COD)' : strtoupper($order->payment_method) }}
                        </span>
                    </div>

                    <div class="border-top pt-2 mt-2">
                        <span class="text-muted d-block" style="font-size: 0.75rem;">Account Type:</span>
                        @if(!empty($order->user_id) && $customerUser)
                            <span class="badge bg-primary text-white rounded-pill px-2.5 py-1 mt-1">
                                <i class="fa-solid fa-user-check me-1"></i> Registered: {{ $customerUser->email }}
                            </span>
                        @else
                            <span class="badge bg-secondary text-white rounded-pill px-2.5 py-1 mt-1">
                                <i class="fa-solid fa-laptop me-1"></i> Guest Device Order
                            </span>
                        @endif
                    </div>

                    @if(!empty($order->device_token))
                        <div class="p-2.5 rounded-3 bg-light border mt-1">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="text-muted small fw-bold">Device Token:</span>
                                <span class="badge bg-dark rounded-pill" style="font-size: 0.65rem;">{{ $deviceOrdersCount }} Total Orders</span>
                            </div>
                            <code class="d-block text-truncate small font-monospace text-dark" title="{{ $order->device_token }}">{{ $order->device_token }}</code>

                            @if($guestDevice && $guestDevice->unlinked_at)
                                <span class="badge bg-danger-subtle text-danger rounded-pill px-2 py-0.5 text-xs mt-1">
                                    <i class="fa-solid fa-link-slash me-1"></i> Session Unlinked
                                </span>
                            @else
                                <form action="{{ route('admin.orders.unlink_device', $order->device_token) }}" method="POST" class="mt-2" onsubmit="return confirm('Unlink this guest device session?');">
                                    @csrf
                                    <button type="submit" class="btn btn-outline-danger btn-sm py-1 px-2 rounded-pill">
                                        <i class="fa-solid fa-link-slash me-1"></i> Unlink Device
                                    </button>
                                </form>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="courierDispatchModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0">
            <div class="modal-header border-0">
                <h5 class="fw-bold mb-0"><i class="fa-solid fa-truck-fast text-primary me-2"></i> Book Courier Consignment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.logistics.dispatch', $order->id) }}" method="POST">
                @csrf
                <div class="modal-body p-4 d-flex flex-column gap-3">
                    <p class="small text-muted m-0">Select courier provider to automatically push parcel consignment and generate live tracking code.</p>
                    <div>
                        <label class="form-label">Courier Provider</label>
                        <select name="provider" class="form-select" required>
                            <option value="steadfast">Steadfast Courier</option>
                            <option value="pathao">Pathao Courier</option>
                            <option value="redx">RedX Logistics</option>
                        </select>
                    </div>
                    <div class="p-3 rounded-3 border bg-light">
                        <div class="small text-muted">Collect COD Amount:</div>
                        <strong class="fs-5 text-danger">৳ {{ number_format($order->payment_method === 'cod' ? $order->total : 0, 0) }}</strong>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary px-4 fw-bold">Push to Courier</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="refundModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0">
            <div class="modal-header border-0">
                <h5 class="fw-bold mb-0"><i class="fa-solid fa-rotate-left text-danger me-2"></i> Issue RMA Refund</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.refunds.store', $order->id) }}" method="POST">
                @csrf
                <div class="modal-body p-4 d-flex flex-column gap-3">
                    <div>
                        <label class="form-label">Refund Type</label>
                        <select name="refund_type" class="form-select">
                            <option value="full">Full Refund (৳ {{ number_format($order->total, 0) }})</option>
                            <option value="partial">Partial Refund</option>
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Refund Amount (BDT) <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" name="amount" class="form-control" value="{{ $order->total }}" max="{{ $order->total }}" required>
                    </div>
                    <div>
                        <label class="form-label">Reason for Refund <span class="text-danger">*</span></label>
                        <input type="text" name="reason" class="form-control" placeholder="e.g. Defective item, customer return" required>
                    </div>
                    <div class="form-check form-switch mt-2">
                        <input class="form-check-input" type="checkbox" name="restock_inventory" id="restockInv" checked>
                        <label class="form-check-label fw-bold small" for="restockInv">Automatically restore product inventory stock</label>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger px-4 fw-bold">Confirm & Issue Refund</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(() => {
    function reEvaluateCurrentOrder(orderId) {
        Swal.fire({
            title: 'Scanning Fraud Risk & BDCourier...',
            didOpen: () => {
                Swal.showLoading();
            }
        });

        axios.post(`/admin/fraud-checker/evaluate/${orderId}`, null, {
            headers: { 'Accept': 'application/json' }
        })
        .then(res => {
            if (res.data.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Risk Evaluated!',
                    text: `Updated Score: ${res.data.evaluation.fraud_score}/100`,
                    timer: 1500,
                    showConfirmButton: false
                }).then(() => {
                    if (window.Turbo) {
                        window.Turbo.visit(window.location.href, { action: 'replace' });
                    } else {
                        window.location.reload();
                    }
                });
            } else {
                Swal.fire('Error', res.data.message || 'Evaluation failed', 'error');
            }
        })
        .catch(err => {
            Swal.fire('Error', 'Unable to reach Fraud Risk API', 'error');
        });
    }

    function printInvoiceDirect(url) {
        let frame = document.getElementById('printInvoiceFrame');
        if (!frame) {
            frame = document.createElement('iframe');
            frame.id = 'printInvoiceFrame';
            frame.style.display = 'none';
            document.body.appendChild(frame);
        }

        Swal.fire({
            toast: true,
            position: 'top-end',
            icon: 'info',
            title: 'Preparing invoice print...',
            showConfirmButton: false,
            timer: 1200
        });

        frame.src = url;
        frame.onload = function() {
            try {
                frame.contentWindow.focus();
                frame.contentWindow.print();
            } catch (e) {
                window.open(url, '_blank');
            }
        };
    }

    window.reEvaluateCurrentOrder = reEvaluateCurrentOrder;
    window.printInvoiceDirect = printInvoiceDirect;
})();
</script>
@endpush