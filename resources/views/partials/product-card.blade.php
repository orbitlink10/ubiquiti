@php
    $productDisplayName = \App\Support\ProductSeo::displayName($product);
    $productImage = $product->images->firstWhere('is_primary', true) ?? $product->images->first();
    $uploadedProductImage = \App\Support\ProductImageCatalog::uploadedUrlFor($product->name, $product->slug);
    $officialProductImages = \App\Support\ProductImageCatalog::officialUrls($product);
    $officialProductImage = $officialProductImages[0] ?? null;
    $image = $productImage?->publicUrl()
        ?: $uploadedProductImage
        ?: $officialProductImage
        ?: $productImageFallback;
    $imageErrorFallback = $image !== $uploadedProductImage && $uploadedProductImage
        ? $uploadedProductImage
        : ($image !== $officialProductImage && $officialProductImage ? $officialProductImage : $productImageFallback);
    $productDescription = \App\Support\ProductContent::excerpt($product->meta_description ?: $product->description, 132);
    $productDescription = $productDescription !== ''
        ? $productDescription
        : $productDisplayName . ' is available in Kenya.';
@endphp

<article class="product-card">
    <a class="product-media-link" href="{{ route('product.show', $product) }}" aria-label="View {{ $productDisplayName }}">
        <img
            class="product-image"
            src="{{ $image }}"
            alt="{{ $productDisplayName }}"
            width="480"
            height="360"
            loading="lazy"
            decoding="async"
            onerror="this.onerror=null;this.src='{{ $imageErrorFallback }}';"
        >
    </a>
    <div class="product-body">
        <h3 class="product-name">
            <a href="{{ route('product.show', $product) }}">{{ $productDisplayName }}</a>
        </h3>
        <p class="product-desc">{{ $productDescription }}</p>
        <div class="product-bottom">
            <span class="price">{{ \App\Support\ProductPricing::priceLabel($product, 'KES') }}</span>
            <a class="view-btn" href="{{ route('product.show', $product) }}">View</a>
        </div>
    </div>
</article>
