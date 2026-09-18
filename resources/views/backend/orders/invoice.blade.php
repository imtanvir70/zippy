@php
    $storeName = $settings['store_name'] ?? config('app.name', 'Zippy');
    $storeAddress = $settings['store_address'] ?? config('app.address', 'Dhaka, Bangladesh');
    $storePhone = $settings['store_phone'] ?? ($settings['whatsapp_number'] ?? config('app.phone', ''));
    $storeEmail = $settings['store_email'] ?? config('mail.from.address', '');
    $rawUrl = config('app.url') ? rtrim(config('app.url'), '/') : url('/');
    $displayUrl = preg_replace('#^https?://#', '', $rawUrl);
    $currency = $settings['currency_symbol'] ?? '৳';
    $orderNumber = $order->order_number ?: ('ORD-' . $order->id);
    $customerName = $order->customer_name ?: 'Valued Customer';
    $customerPhone = $order->customer_phone ?: 'N/A';
    
    $customerEmail = $order->guest_email;
    if (empty($customerEmail) && !empty($order->user_id)) {
        $customerEmail = DB::table('users')->where('id', $order->user_id)->value('email');
    }
    
    $customerAddress = $order->customer_address ?: ($order->district ? 'District: ' . $order->district : 'Address not specified');
    $district = $order->district ?: 'Dhaka';
    $isCod = ($order->payment_method === 'cod' || strtolower($order->payment_status ?? '') !== 'paid');
    $paymentMethod = $isCod ? 'Cash on Delivery (COD)' : strtoupper($order->payment_method ?? 'Online Payment');
    $courier = $order->courier_provider ?? null;
    $trackingCode = $order->courier_tracking_code ?? null;
    $orderDate = $order->created_at ? \Carbon\Carbon::parse($order->created_at)->format('d M Y, h:i A') : now()->format('d M Y, h:i A');
    $customerTrackingUrl = url('/track-order?order=' . $orderNumber);
    $qrData = urlencode($customerTrackingUrl);
@endphp
<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice #{{ $orderNumber }} - {{ $storeName }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Hind+Siliguri:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        *, *::before, *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        body {
            font-family: 'Plus Jakarta Sans', 'Hind Siliguri', -apple-system, BlinkMacSystemFont, sans-serif;
            background-color: #f8fafc;
            color: #09090b;
            padding: 20px 0;
            line-height: 1.4;
            -webkit-font-smoothing: antialiased;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
        .invoice-container {
            max-width: 800px;
            margin: 0 auto;
        }
        .invoice-sheet {
            background: #ffffff;
            border: 1.5px solid #09090b;
            border-radius: 4px;
            box-shadow: 0 4px 20px -2px rgba(0, 0, 0, 0.05);
            padding: 28px 30px;
            min-height: 1040px;
            display: flex;
            flex-direction: column;
            justify-content: flex-start;
        }
        .invoice-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 16px;
        }
        .brand-section {
            display: flex;
            flex-direction: column;
            gap: 2px;
        }
        .brand-name {
            font-size: 1.75rem;
            font-weight: 800;
            letter-spacing: -0.04em;
            color: #09090b;
            line-height: 1.1;
            margin-bottom: 4px;
        }
        .brand-contact {
            font-size: 0.72rem;
            color: #52525b;
            line-height: 1.45;
        }
        .invoice-meta {
            text-align: right;
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            gap: 6px;
        }
        .qr-code-box {
            width: 58px;
            height: 58px;
            background: #ffffff;
            border: 1px solid #09090b;
            border-radius: 4px;
            padding: 2px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .qr-code-img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }
        .invoice-meta-details {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
        }
        .method-pill {
            display: inline-flex;
            padding: 2px 8px;
            background: #09090b;
            color: #ffffff;
            font-size: 0.62rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            border-radius: 4px;
            margin-bottom: 2px;
        }
        .invoice-number {
            font-size: 1.15rem;
            font-weight: 800;
            color: #09090b;
            letter-spacing: -0.02em;
            line-height: 1.2;
        }
        .invoice-date {
            font-size: 0.72rem;
            color: #71717a;
            font-weight: 500;
        }
        .divider {
            height: 1px;
            background: #e4e4e7;
            width: 100%;
            margin: 0 0 16px 0;
        }
        .info-grid {
            display: grid;
            grid-template-columns: 1.2fr 0.8fr;
            gap: 24px;
            margin-bottom: 16px;
            align-items: start;
        }
        .info-block {
            display: flex;
            flex-direction: column;
            gap: 2px;
        }
        .info-block-right {
            align-items: flex-end;
            text-align: right;
        }
        .info-label {
            font-size: 0.65rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: #09090b;
            margin-bottom: 4px;
        }
        .customer-name {
            font-size: 0.88rem;
            font-weight: 700;
            color: #09090b;
        }
        .customer-phone {
            font-size: 0.82rem;
            font-weight: 700;
            color: #09090b;
            font-family: ui-monospace, SFMono-Regular, Menlo, monospace;
        }
        .customer-address {
            font-size: 0.75rem;
            color: #52525b;
            line-height: 1.35;
            margin-top: 1px;
        }
        .district-badge {
            display: inline-flex;
            margin-top: 4px;
            padding: 2px 7px;
            font-size: 0.68rem;
            font-weight: 600;
            background: #f4f4f5;
            color: #09090b;
            border-radius: 4px;
            border: 1px solid #d4d4d8;
        }
        .meta-list {
            display: flex;
            flex-direction: column;
            gap: 4px;
            width: 100%;
        }
        .meta-item {
            display: flex;
            justify-content: flex-end;
            align-items: center;
            gap: 12px;
            font-size: 0.74rem;
        }
        .meta-item-label {
            color: #71717a;
            font-weight: 500;
        }
        .meta-item-value {
            color: #09090b;
            font-weight: 600;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 16px;
        }
        .items-table th {
            background: #fafafa;
            color: #09090b;
            font-size: 0.65rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            padding: 7px 10px;
            border-top: 1px solid #09090b;
            border-bottom: 1px solid #09090b;
        }
        .items-table td {
            padding: 5px 10px;
            border-bottom: 1px solid #f4f4f5;
            vertical-align: middle;
            color: #09090b;
        }
        .items-table tr:last-child td {
            border-bottom: 1px solid #09090b;
        }
        .product-cell {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .product-img {
            width: 32px;
            height: 32px;
            object-fit: cover;
            border-radius: 4px;
            border: 1px solid #d4d4d8;
            flex-shrink: 0;
            background: #fafafa;
        }
        .product-details {
            display: flex;
            flex-direction: column;
            gap: 1px;
        }
        .product-name {
            font-size: 0.75rem;
            font-weight: 700;
            color: #09090b;
            line-height: 1.2;
        }
        .product-sku {
            font-size: 0.65rem;
            color: #71717a;
            font-family: ui-monospace, SFMono-Regular, Menlo, monospace;
        }
        .product-variant {
            font-size: 0.65rem;
            color: #3f3f46;
            font-weight: 500;
        }
        .qty-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 22px;
            height: 22px;
            background: #ffffff;
            border: 1px solid #d4d4d8;
            color: #09090b;
            border-radius: 4px;
            font-size: 0.72rem;
            font-weight: 700;
        }
        .money-cell {
            font-size: 0.75rem;
            font-weight: 600;
            font-variant-numeric: tabular-nums;
        }
        .money-cell-total {
            font-size: 0.8rem;
            font-weight: 800;
            color: #09090b;
        }
        .summary-section {
            display: grid;
            grid-template-columns: 1fr 270px;
            gap: 24px;
            align-items: start;
        }
        .notes-block {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        .notes-box {
            background: #f4f4f5;
            border: 1px solid #d4d4d8;
            border-radius: 4px;
            padding: 8px 12px;
        }
        .notes-title {
            font-size: 0.64rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: #09090b;
            margin-bottom: 2px;
        }
        .notes-content {
            font-size: 0.72rem;
            color: #3f3f46;
            line-height: 1.35;
        }
        .policy-text {
            font-size: 0.68rem;
            color: #71717a;
            line-height: 1.35;
        }
        .calc-block {
            display: flex;
            flex-direction: column;
        }
        .calc-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 3px 0;
            color: #52525b;
            font-size: 0.74rem;
        }
        .calc-row-label {
            font-weight: 500;
        }
        .calc-row-value {
            font-weight: 600;
            color: #09090b;
            font-variant-numeric: tabular-nums;
        }
        .calc-total {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 6px 0 0 0;
            margin-top: 4px;
            border-top: 1px solid #e4e4e7;
        }
        .calc-total-label {
            font-size: 0.8rem;
            font-weight: 700;
            color: #71717a;
        }
        .calc-total-value {
            font-size: 0.85rem;
            font-weight: 700;
            color: #09090b;
            font-variant-numeric: tabular-nums;
        }
        .payable-banner {
            margin-top: 8px;
            padding: 9px 12px;
            border-radius: 4px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .payable-cod {
            background: #09090b;
            color: #ffffff;
        }
        .payable-paid {
            background: #ffffff;
            color: #09090b;
            border: 1.5px solid #09090b;
        }
        .payable-title {
            font-size: 0.72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        .payable-amount {
            font-size: 1.05rem;
            font-weight: 800;
            font-family: ui-monospace, SFMono-Regular, Menlo, monospace;
        }
        .footer {
            margin-top: auto;
            padding-top: 14px;
            border-top: 1px solid #e4e4e7;
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
        }
        .system-generated-text {
            font-size: 0.68rem;
            font-weight: 500;
            color: #a1a1aa;
        }
        .thank-you {
            text-align: right;
        }
        .thank-you-title {
            font-size: 0.78rem;
            font-weight: 700;
            color: #09090b;
            margin-bottom: 1px;
        }
        .thank-you-sub {
            font-size: 0.65rem;
            color: #a1a1aa;
        }
        @media print {
            html, body {
                height: 100% !important;
                background: #ffffff !important;
                padding: 0 !important;
                margin: 0 !important;
                overflow: hidden !important;
            }
            .invoice-container {
                max-width: 100% !important;
                width: 100% !important;
                height: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
            }
            .invoice-sheet {
                border: 1.5px solid #09090b !important;
                border-radius: 0 !important;
                box-shadow: none !important;
                padding: 24px 26px !important;
                height: 279mm !important;
                min-height: 279mm !important;
                max-height: 279mm !important;
                display: flex !important;
                flex-direction: column !important;
                box-sizing: border-box !important;
            }
            .footer {
                margin-top: auto !important;
            }
            .no-print {
                display: none !important;
            }
            @page {
                size: A4 portrait;
                margin: 8mm 10mm;
            }
            tr, .summary-section, .footer {
                page-break-inside: avoid !important;
            }
        }
    </style>
</head>
<body>

<div class="invoice-container">
    <div class="d-flex justify-content-between align-items-center mb-3 px-1 no-print">
        <a href="{{ route('admin.orders.show', $orderNumber) }}" class="btn btn-sm btn-outline-dark rounded-pill px-3 fw-semibold">
            <i class="fa-solid fa-arrow-left me-1"></i> Return to Order
        </a>
        <button onclick="window.print()" class="btn btn-sm btn-dark rounded-pill px-4 fw-bold d-flex align-items-center gap-2 shadow-sm">
            <i class="fa-solid fa-print"></i>
            <span>Print Invoice</span>
        </button>
    </div>

    <div class="invoice-sheet">
        <div class="invoice-header">
            <div class="brand-section">
                <div class="brand-name">{{ $storeName }}</div>
                <div class="brand-contact">
                    <div>{{ $storeAddress }}</div>
                    @if($storePhone)
                        <div>Helpline: {{ $storePhone }}</div>
                    @endif
                    @if($storeEmail)
                        <div>Email: {{ $storeEmail }}</div>
                    @endif
                    <div>{{ $displayUrl }}</div>
                </div>
            </div>

            <div class="invoice-meta">
                <div class="qr-code-box">
                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=90x90&data={{ $qrData }}&margin=0" alt="Track Order QR" class="qr-code-img">
                </div>
                <div class="invoice-meta-details">
                    <span class="method-pill">{{ $isCod ? 'COD' : 'PREPAID' }}</span>
                    <div class="invoice-number">#{{ $orderNumber }}</div>
                    <div class="invoice-date">Date: {{ $orderDate }}</div>
                </div>
            </div>
        </div>

        <div class="divider"></div>

        <div class="info-grid">
            <div class="info-block">
                <div class="info-label">Customer & Delivery Info</div>
                <div class="customer-name">{{ $customerName }}</div>
                <div class="customer-phone">{{ $customerPhone }}</div>
                @if(!empty($customerEmail))
                    <div class="customer-email" style="font-size: 0.75rem; color: #52525b; margin-top: 1px;">{{ $customerEmail }}</div>
                @endif
                <div class="customer-address">
                    {!! nl2br(e($customerAddress)) !!}
                </div>
                <div>
                    <span class="district-badge">District: {{ $district }}</span>
                </div>
            </div>

            <div class="info-block info-block-right">
                <div class="info-label">Billing & Logistics</div>
                <div class="meta-list">
                    <div class="meta-item">
                        <span class="meta-item-label">Payment Method:</span>
                        <span class="meta-item-value">{{ $paymentMethod }}</span>
                    </div>
                    @if($courier)
                        <div class="meta-item">
                            <span class="meta-item-label">Courier Partner:</span>
                            <span class="meta-item-value">{{ $courier }}</span>
                        </div>
                    @endif
                    @if($trackingCode)
                        <div class="meta-item">
                            <span class="meta-item-label">Tracking Code:</span>
                            <span class="meta-item-value" style="font-family: ui-monospace, monospace; font-weight: 700;">{{ $trackingCode }}</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <table class="items-table">
            <thead>
                <tr>
                    <th style="width: 55%; text-align: left;">Product Details</th>
                    <th style="width: 15%; text-align: right;">Unit Price</th>
                    <th style="width: 10%; text-align: center;">Qty</th>
                    <th style="width: 20%; text-align: right;">Line Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($items as $item)
                    <tr>
                        <td>
                            <div class="product-cell">
                                <img src="{{ $item->product_image ?: asset('images/product-placeholder.svg') }}" alt="{{ $item->product_title }}" class="product-img">
                                <div class="product-details">
                                    <div class="product-name">{{ $item->product_title }}</div>
                                    <div class="product-sku">SKU: #{{ $item->sku ?? $item->product_id }}</div>
                                    @if(!empty($item->variant_info))
                                        <div class="product-variant">{{ $item->variant_info }}</div>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td style="text-align: right;" class="money-cell">
                            {{ $currency }} {{ number_format($item->unit_price, 0) }}
                        </td>
                        <td style="text-align: center;">
                            <span class="qty-badge">{{ $item->quantity }}</span>
                        </td>
                        <td style="text-align: right;" class="money-cell money-cell-total">
                            {{ $currency }} {{ number_format($item->total_price, 0) }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="summary-section">
            <div class="notes-block">
                @if($order->notes)
                    <div class="notes-box">
                        <div class="notes-title">Delivery Instructions</div>
                        <div class="notes-content">
                            {!! nl2br(e($order->notes)) !!}
                        </div>
                    </div>
                @endif
                <div class="policy-text">
                    <strong>Return Policy:</strong> Exchange or return accepted within 7 days with intact packaging.<br>
                    Please preserve this invoice copy for any warranty claims.
                </div>
            </div>

            <div class="calc-block">
                <div class="calc-row">
                    <span class="calc-row-label">Subtotal</span>
                    <span class="calc-row-value">{{ $currency }} {{ number_format($order->subtotal, 0) }}</span>
                </div>
                <div class="calc-row">
                    <span class="calc-row-label">Shipping Fee</span>
                    <span class="calc-row-value">{{ $currency }} {{ number_format($order->shipping_cost, 0) }}</span>
                </div>
                @if($order->discount > 0)
                    <div class="calc-row">
                        <span class="calc-row-label">Discount</span>
                        <span class="calc-row-value">- {{ $currency }} {{ number_format($order->discount, 0) }}</span>
                    </div>
                @endif
                @if($order->refunded_amount > 0)
                    <div class="calc-row">
                        <span class="calc-row-label">Refund Adjustment</span>
                        <span class="calc-row-value">- {{ $currency }} {{ number_format($order->refunded_amount, 0) }}</span>
                    </div>
                @endif
                
                <div class="calc-total">
                    <span class="calc-total-label">Total Amount</span>
                    <span class="calc-total-value">{{ $currency }} {{ number_format($order->total, 0) }}</span>
                </div>

                @if($isCod)
                    <div class="payable-banner payable-cod">
                        <span class="payable-title">Payable (COD):</span>
                        <span class="payable-amount">{{ $currency }} {{ number_format($order->total, 0) }}</span>
                    </div>
                @else
                    <div class="payable-banner payable-paid">
                        <span class="payable-title">Amount Paid:</span>
                        <span class="payable-amount">{{ $currency }} {{ number_format($order->total, 0) }}</span>
                    </div>
                @endif
            </div>
        </div>

        <div class="footer">
            <div class="system-generated-text">
                This is a computer-generated invoice and requires no physical signature.
            </div>
            <div class="thank-you">
                <div class="thank-you-title">Thank you for shopping with us!</div>
                <div class="thank-you-sub">Authentic electronic receipt by {{ $storeName }}</div>
            </div>
        </div>
    </div>
</div>

</body>
</html>