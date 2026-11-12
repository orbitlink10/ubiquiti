<?xml version="1.0" encoding="UTF-8"?>
<rss xmlns:g="http://base.google.com/ns/1.0" version="2.0">
<channel>
<title>{{ $title }}</title>
<link>{{ $homeUrl }}</link>
<description>Ubiquiti networking products available in Kenya.</description>
@foreach($items as $item)
<item>
<g:id>{{ $item['id'] }}</g:id>
<g:title>{{ $item['title'] }}</g:title>
<g:description>{{ $item['description'] }}</g:description>
<g:link>{{ $item['link'] }}</g:link>
<g:image_link>{{ $item['image_link'] }}</g:image_link>
@foreach($item['additional_image_link'] as $additionalImage)
<g:additional_image_link>{{ $additionalImage }}</g:additional_image_link>
@endforeach
<g:availability>{{ $item['availability'] }}</g:availability>
<g:price>{{ $item['price'] }}</g:price>
<g:condition>{{ $item['condition'] }}</g:condition>
<g:brand>{{ $item['brand'] }}</g:brand>
@if($item['mpn'])
<g:mpn>{{ $item['mpn'] }}</g:mpn>
@endif
@if($item['gtin'])
<g:gtin>{{ $item['gtin'] }}</g:gtin>
@endif
@if($item['identifier_exists'])
<g:identifier_exists>{{ $item['identifier_exists'] }}</g:identifier_exists>
@endif
@if($item['google_product_category'])
<g:google_product_category>{{ $item['google_product_category'] }}</g:google_product_category>
@endif
@if($item['product_type'])
<g:product_type>{{ $item['product_type'] }}</g:product_type>
@endif
</item>
@endforeach
</channel>
</rss>
