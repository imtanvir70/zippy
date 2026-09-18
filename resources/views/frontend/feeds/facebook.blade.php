{!! '<' . '?xml version="1.0" encoding="UTF-8"?' . '>' !!}
<rss xmlns:g="http://base.google.com/ns/1.0" version="2.0">
<channel>
    <title><![CDATA[{!! str_replace(']]>', ']]&gt;', $storeName) !!}]]></title>
    <link>{{ $storeUrl }}</link>
    <description><![CDATA[{!! str_replace(']]>', ']]&gt;', $storeDescription) !!}]]></description>
@foreach($products as $product)
    <item>
        <g:id>{{ $product->id }}</g:id>
        <g:title><![CDATA[{!! str_replace(']]>', ']]&gt;', $product->title) !!}]]></g:title>
        <g:description><![CDATA[{!! str_replace(']]>', ']]&gt;', $product->description) !!}]]></g:description>
        <g:link>{{ $product->link }}</g:link>
@if(!empty($product->image_link))
        <g:image_link>{{ $product->image_link }}</g:image_link>
@endif
@if(!empty($product->additional_image_links) && is_array($product->additional_image_links))
@foreach($product->additional_image_links as $additionalImage)
        <g:additional_image_link>{{ $additionalImage }}</g:additional_image_link>
@endforeach
@endif
        <g:availability>{{ $product->availability }}</g:availability>
        <g:condition>{{ $product->condition }}</g:condition>
        <g:price>{{ number_format($product->price, 2, '.', '') }} BDT</g:price>
@if(!empty($product->sale_price))
        <g:sale_price>{{ number_format($product->sale_price, 2, '.', '') }} BDT</g:sale_price>
@endif
@if(!empty($product->sale_price_effective_date))
        <g:sale_price_effective_date>{{ $product->sale_price_effective_date }}</g:sale_price_effective_date>
@endif
@if(!empty($product->brand))
        <g:brand><![CDATA[{!! str_replace(']]>', ']]&gt;', $product->brand) !!}]]></g:brand>
@endif
@if(!empty($product->google_product_category))
        <g:google_product_category><![CDATA[{!! str_replace(']]>', ']]&gt;', $product->google_product_category) !!}]]></g:google_product_category>
@endif
@if(!empty($product->product_type))
        <g:product_type><![CDATA[{!! str_replace(']]>', ']]&gt;', $product->product_type) !!}]]></g:product_type>
@endif
@if(!empty($product->item_group_id))
        <g:item_group_id>{{ $product->item_group_id }}</g:item_group_id>
@endif
@if(!empty($product->color))
        <g:color><![CDATA[{!! str_replace(']]>', ']]&gt;', $product->color) !!}]]></g:color>
@endif
@if(!empty($product->size))
        <g:size><![CDATA[{!! str_replace(']]>', ']]&gt;', $product->size) !!}]]></g:size>
@endif
@if(!empty($product->gender))
        <g:gender><![CDATA[{!! str_replace(']]>', ']]&gt;', $product->gender) !!}]]></g:gender>
@endif
@if(!empty($product->age_group))
        <g:age_group><![CDATA[{!! str_replace(']]>', ']]&gt;', $product->age_group) !!}]]></g:age_group>
@endif
@if(!empty($product->material))
        <g:material><![CDATA[{!! str_replace(']]>', ']]&gt;', $product->material) !!}]]></g:material>
@endif
@if(!empty($product->pattern))
        <g:pattern><![CDATA[{!! str_replace(']]>', ']]&gt;', $product->pattern) !!}]]></g:pattern>
@endif
@if(!empty($product->gtin))
        <g:gtin>{{ $product->gtin }}</g:gtin>
@endif
@if(!empty($product->mpn))
        <g:mpn><![CDATA[{!! str_replace(']]>', ']]&gt;', $product->mpn) !!}]]></g:mpn>
@endif
@if(!empty($product->custom_label_0))
        <g:custom_label_0><![CDATA[{!! str_replace(']]>', ']]&gt;', $product->custom_label_0) !!}]]></g:custom_label_0>
@endif
@if(!empty($product->custom_label_1))
        <g:custom_label_1><![CDATA[{!! str_replace(']]>', ']]&gt;', $product->custom_label_1) !!}]]></g:custom_label_1>
@endif
@if(!empty($product->custom_label_2))
        <g:custom_label_2><![CDATA[{!! str_replace(']]>', ']]&gt;', $product->custom_label_2) !!}]]></g:custom_label_2>
@endif
@if(!empty($product->custom_label_3))
        <g:custom_label_3><![CDATA[{!! str_replace(']]>', ']]&gt;', $product->custom_label_3) !!}]]></g:custom_label_3>
@endif
@if(!empty($product->custom_label_4))
        <g:custom_label_4><![CDATA[{!! str_replace(']]>', ']]&gt;', $product->custom_label_4) !!}]]></g:custom_label_4>
@endif
@if(isset($product->shipping_price) && $product->shipping_price !== '')
        <g:shipping>
            <g:country>{{ $product->shipping_country ?? 'BD' }}</g:country>
            <g:price>{{ number_format($product->shipping_price, 2, '.', '') }} BDT</g:price>
        </g:shipping>
@endif
@if(!empty($product->return_policy_days))
        <g:return_policy_days>{{ $product->return_policy_days }}</g:return_policy_days>
@endif
    </item>
@endforeach
</channel>
</rss>

