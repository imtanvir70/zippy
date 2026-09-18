@php
    $siteName = $siteName ?? 'ZippyBD';
    $metaTitle = trim($title ?? ($siteName . ' - Online Shopping'));
    $rawDesc = $description ?? ($siteName . ' - Premium Gadgets & Accessories in Bangladesh');
    $metaDescription = Str::limit(trim(preg_replace('/\s+/', ' ', strip_tags($rawDesc))), 160, '');
    $metaUrl = $url ?? url()->current();
    $metaType = $type ?? 'website';
    
    $fallbackImage = 'https://images.unsplash.com/photo-1518455027359-f3f8164ba6bd?auto=format&fit=crop&w=1200&h=630&q=85';
    $rawImage = $image ?? null;
    if (!empty($rawImage)) {
        $metaImage = filter_var($rawImage, FILTER_VALIDATE_URL) ? $rawImage : asset($rawImage);
    } else {
        $metaImage = $fallbackImage;
    }

    $priceAmount = isset($price) ? (float) str_replace(',', '', (string)$price) : null;
    $priceCurrency = $currency ?? 'BDT';
    $availabilityState = isset($inStock) ? ($inStock ? 'in stock' : 'out of stock') : null;
@endphp

<title>{{ $metaTitle }}</title>
<meta name="description" content="{{ $metaDescription }}">
@if(!empty($keywords))
    <meta name="keywords" content="{{ $keywords }}">
@endif
<link rel="canonical" href="{{ $metaUrl }}">

<meta property="og:title" content="{{ $metaTitle }}">
<meta property="og:description" content="{{ $metaDescription }}">
<meta property="og:url" content="{{ $metaUrl }}">
<meta property="og:type" content="{{ $metaType }}">
<meta property="og:site_name" content="{{ $siteName }}">
<meta property="og:image" content="{{ $metaImage }}">
<meta property="og:image:secure_url" content="{{ $metaImage }}">
<meta property="og:image:width" content="1200">
<meta property="og:image:height" content="630">

@if($metaType === 'product' && !is_null($priceAmount))
    <meta property="product:price:amount" content="{{ $priceAmount }}">
    <meta property="product:price:currency" content="{{ $priceCurrency }}">
    <meta property="og:price:amount" content="{{ $priceAmount }}">
    <meta property="og:price:currency" content="{{ $priceCurrency }}">
    @if(!is_null($availabilityState))
        <meta property="product:availability" content="{{ $availabilityState }}">
    @endif
@endif

<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{ $metaTitle }}">
<meta name="twitter:description" content="{{ $metaDescription }}">
<meta name="twitter:image" content="{{ $metaImage }}">

