@php
    $storeName = $settings['store_name'] ?? config('app.name', 'Zippy');
    $storeAddress = $settings['store_address'] ?? config('app.address', 'Dhaka, Bangladesh');
    $storePhone = $settings['store_phone'] ?? ($settings['whatsapp_number'] ?? config('app.phone', ''));
    $currency = $settings['currency_symbol'] ?? '৳';
    $orderNumber = $order->order_number ?: ('ORD-' . $order->id);
    $customerName = $order->customer_name ?: 'Valued Customer';
    $customerPhone = $order->customer_phone ?: 'N/A';
    $customerAddress = $order->customer_address ?: ($order->district ? 'District: ' . $order->district : 'Address not specified');
    $district = strtoupper($order->district ?: 'DHAKA');
    $isCod = ($order->payment_method === 'cod' || strtolower($order->payment_status ?? '') !== 'paid');
    $courier = $order->courier_provider ? strtoupper($order->courier_provider) : 'STANDARD';
    $trackingCode = $order->courier_tracking_code ?? $orderNumber;
    $orderDate = $order->created_at ? \Carbon\Carbon::parse($order->created_at)->format('d M Y') : now()->format('d M Y');
    $totalUnits = $items->sum('quantity');
    $qrData = urlencode(url('/track-order?order=' . $orderNumber));
@endphp
<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shipping Label #{{ $orderNumber }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@600;700;800;900&family=Hind+Siliguri:wght@600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        *, *::before, *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        body {
            font-family: 'Plus Jakarta Sans', 'Hind Siliguri', sans-serif;
            background-color: #f4f4f5;
            color: #000000;
            padding: 24px 0;
            -webkit-font-smoothing: antialiased;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
        .page-container {
            width: 105mm;
            margin: 0 auto;
        }
        .shipping-label {
            width: 105mm;
            background: #ffffff;
            border: 2px solid #000000;
            height: auto;
            display: block;
        }
        .grid-row {
            border-bottom: 1.5px solid #000000;
        }
        .grid-row:last-child {
            border-bottom: none;
        }
        .head-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 12px;
        }
        .brand-name {
            font-size: 1.4rem;
            font-weight: 900;
            letter-spacing: -0.04em;
            line-height: 1;
        }
        .courier-name {
            font-size: 0.72rem;
            font-weight: 800;
            background: #000000;
            color: #ffffff;
            padding: 3px 8px;
            text-transform: uppercase;
        }
        .barcode-section {
            padding: 8px 12px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 10px;
        }
        .barcode-col {
            display: flex;
            flex-direction: column;
        }
        .barcode-svg {
            width: 170px;
            height: 34px;
        }
        .barcode-txt {
            font-size: 0.72rem;
            font-family: ui-monospace, monospace;
            font-weight: 800;
            letter-spacing: 1px;
            margin-top: 1px;
        }
        .qr-box {
            width: 50px;
            height: 50px;
            border: 1px solid #000000;
            padding: 2px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        .qr-box img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }
        .recipient-section {
            padding: 10px 12px;
        }
        .recipient-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 4px;
        }
        .caption-title {
            font-size: 0.65rem;
            font-weight: 800;
            text-transform: uppercase;
            color: #52525b;
        }
        .hub-tag {
            font-size: 0.72rem;
            font-weight: 800;
            background: #000000;
            color: #ffffff;
            padding: 2px 7px;
        }
        .customer-name {
            font-size: 1.15rem;
            font-weight: 800;
            line-height: 1.2;
        }
        .customer-phone {
            font-size: 1.25rem;
            font-weight: 900;
            font-family: ui-monospace, monospace;
            margin: 2px 0 4px 0;
            letter-spacing: 0.5px;
        }
        .customer-address {
            font-size: 0.85rem;
            font-weight: 600;
            line-height: 1.35;
        }
        .data-row {
            display: grid;
            grid-template-columns: 1fr 1.2fr;
        }
        .meta-col {
            padding: 8px 12px;
            border-right: 1.5px solid #000000;
            display: flex;
            flex-direction: column;
            justify-content: center;
            gap: 2px;
            font-size: 0.72rem;
        }
        .meta-item {
            display: flex;
            justify-content: space-between;
        }
        .meta-lbl {
            color: #52525b;
            font-weight: 600;
        }
        .meta-val {
            font-weight: 800;
        }
        .cod-col {
            background: #000000;
            color: #ffffff;
            padding: 8px 12px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: flex-end;
        }
        .cod-col-paid {
            background: #ffffff;
            color: #000000;
        }
        .cod-title {
            font-size: 0.65rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        .cod-price {
            font-size: 1.35rem;
            font-weight: 900;
            font-family: ui-monospace, monospace;
            line-height: 1.1;
        }
        .note-row {
            padding: 6px 12px;
            font-size: 0.74rem;
            background: #fafafa;
        }
        .note-row strong {
            font-weight: 800;
            font-size: 0.68rem;
        }
        .footer-row {
            padding: 8px 12px;
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            font-size: 0.65rem;
            line-height: 1.3;
        }
        .return-text strong {
            text-transform: uppercase;
        }
        .cut-line {
            display: none;
            width: 105mm;
            border-top: 1.5px dashed #71717a;
            margin-top: 12px;
            padding-top: 4px;
            text-align: center;
            font-size: 0.65rem;
            font-weight: 700;
            color: #71717a;
            letter-spacing: 1px;
            text-transform: uppercase;
        }
        @media print {
            body {
                background: #ffffff !important;
                padding: 0 !important;
                margin: 0 !important;
            }
            .page-container {
                width: 105mm !important;
                margin: 0 !important;
                padding: 4mm !important;
            }
            .shipping-label {
                width: 105mm !important;
                border: 2px solid #000000 !important;
                page-break-inside: avoid !important;
            }
            .cut-line {
                display: block !important;
            }
            .no-print {
                display: none !important;
            }
            @page {
                size: A4 portrait;
                margin: 0;
            }
        }
    </style>
</head>
<body>

<div class="page-container">

    <div class="d-flex justify-content-between align-items-center mb-3 no-print">
        <a href="{{ route('admin.orders.show', $orderNumber) }}" class="btn btn-sm btn-outline-dark rounded-pill px-3 fw-bold">
            <i class="fa-solid fa-arrow-left me-1"></i> Return
        </a>
        <button onclick="window.print()" class="btn btn-sm btn-dark rounded-pill px-4 fw-bold shadow-sm">
            <i class="fa-solid fa-print me-1"></i> Print Label
        </button>
    </div>

    <div class="shipping-label">
        <div class="grid-row head-bar">
            <div class="brand-name">{{ $storeName }}</div>
            <span class="courier-name">{{ $courier }}</span>
        </div>

        <div class="grid-row barcode-section">
            <div class="barcode-col">
                <svg class="barcode-svg" viewBox="0 0 160 36" xmlns="http://www.w3.org/2000/svg">
                    <rect x="0" y="0" width="3" height="32" fill="#000000"/>
                    <rect x="5" y="0" width="1" height="32" fill="#000000"/>
                    <rect x="9" y="0" width="3" height="32" fill="#000000"/>
                    <rect x="15" y="0" width="2" height="32" fill="#000000"/>
                    <rect x="19" y="0" width="4" height="32" fill="#000000"/>
                    <rect x="26" y="0" width="1" height="32" fill="#000000"/>
                    <rect x="30" y="0" width="3" height="32" fill="#000000"/>
                    <rect x="35" y="0" width="4" height="32" fill="#000000"/>
                    <rect x="42" y="0" width="2" height="32" fill="#000000"/>
                    <rect x="46" y="0" width="1" height="32" fill="#000000"/>
                    <rect x="50" y="0" width="4" height="32" fill="#000000"/>
                    <rect x="57" y="0" width="2" height="32" fill="#000000"/>
                    <rect x="62" y="0" width="5" height="32" fill="#000000"/>
                    <rect x="70" y="0" width="2" height="32" fill="#000000"/>
                    <rect x="75" y="0" width="3" height="32" fill="#000000"/>
                    <rect x="81" y="0" width="1" height="32" fill="#000000"/>
                    <rect x="85" y="0" width="4" height="32" fill="#000000"/>
                    <rect x="92" y="0" width="2" height="32" fill="#000000"/>
                    <rect x="97" y="0" width="4" height="32" fill="#000000"/>
                    <rect x="104" y="0" width="2" height="32" fill="#000000"/>
                    <rect x="109" y="0" width="3" height="32" fill="#000000"/>
                    <rect x="115" y="0" width="1" height="32" fill="#000000"/>
                    <rect x="119" y="0" width="4" height="32" fill="#000000"/>
                    <rect x="126" y="0" width="2" height="32" fill="#000000"/>
                    <rect x="131" y="0" width="4" height="32" fill="#000000"/>
                    <rect x="138" y="0" width="1" height="32" fill="#000000"/>
                    <rect x="142" y="0" width="3" height="32" fill="#000000"/>
                    <rect x="148" y="0" width="2" height="32" fill="#000000"/>
                    <rect x="153" y="0" width="4" height="32" fill="#000000"/>
                </svg>
                <div class="barcode-txt">*{{ $trackingCode }}*</div>
            </div>
            <div class="qr-box">
                <img src="https://api.qrserver.com/v1/create-qr-code/?size=90x90&data={{ $qrData }}&margin=0" alt="QR">
            </div>
        </div>

        <div class="grid-row recipient-section">
            <div class="recipient-header">
                <span class="caption-title">DELIVER TO / প্রাপক</span>
                <span class="hub-tag">{{ $district }}</span>
            </div>
            <div class="customer-name">{{ $customerName }}</div>
            <div class="customer-phone">{{ $customerPhone }}</div>
            <div class="customer-address">{!! nl2br(e($customerAddress)) !!}</div>
        </div>

        <div class="grid-row data-row">
            <div class="meta-col">
                <div class="meta-item">
                    <span class="meta-lbl">ORDER:</span>
                    <span class="meta-val">#{{ $orderNumber }}</span>
                </div>
                <div class="meta-item">
                    <span class="meta-lbl">DATE:</span>
                    <span class="meta-val">{{ $orderDate }}</span>
                </div>
                <div class="meta-item">
                    <span class="meta-lbl">QTY:</span>
                    <span class="meta-val">{{ $totalUnits }} PCS</span>
                </div>
            </div>
            <div class="cod-col {{ $isCod ? '' : 'cod-col-paid' }}">
                <div class="cod-title">{{ $isCod ? 'CASH ON DELIVERY' : 'PREPAID ORDER' }}</div>
                <div class="cod-price">{{ $isCod ? ($currency . ' ' . number_format($order->total, 0)) : 'PAID' }}</div>
            </div>
        </div>

        @if($order->notes)
            <div class="grid-row note-row">
                <strong>NOTE:</strong> {{ $order->notes }}
            </div>
        @endif

        <div class="footer-row">
            <div class="return-text">
                <strong>Return:</strong> {{ $storeName }}, {{ $storeAddress }}<br>
                Hotline: {{ $storePhone }}
            </div>
            <div style="font-size: 0.6rem; font-weight: 800; border: 1px solid #000; padding: 2px 4px;">
                SEALED
            </div>
        </div>
    </div>

    <div class="cut-line">
        <i class="fa-solid fa-scissors me-1"></i> Cut along line to attach on parcel
    </div>

</div>

</body>
</html>