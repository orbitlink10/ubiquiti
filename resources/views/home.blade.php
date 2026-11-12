@extends('layouts.app')

@php
    $catalogTitle = $currentCategory
        ? \App\Support\SeoMetadata::categoryTitle($currentCategory, $products->currentPage())
        : \App\Support\SeoMetadata::homepageTitle($products->currentPage());
    $catalogMetaDescription = $currentCategory
        ? \App\Support\SeoMetadata::categoryDescription($currentCategory)
        : \App\Support\SeoMetadata::homepageDescription();
    $categoryDescriptionHtml = $currentCategory
        ? \App\Support\ProductContent::sanitizeRichText($currentCategory->seo_content ?: $currentCategory->description)
        : null;
    $categorySummary = $currentCategory
        ? ($currentCategory->intro ?: $currentCategory->meta_description ?: \App\Support\ProductContent::excerpt($currentCategory->description, 240))
        : null;
    $showHomepageSections = $search === '' && !$currentCategory && $products->currentPage() === 1;
    $showFeaturedProductRows = $search === '' && !$currentCategory && $homepageFeaturedProducts->isNotEmpty();
    $showFullWidthProductGrid = $search === '' && !$currentCategory;
    $catalogCanonicalQuery = $search === '' && $products->currentPage() > 1
        ? ['page' => $products->currentPage()]
        : [];
    $categoryFaqItems = $isRouterAuthorityPage
        ? $routerFaqItems
        : (is_array($currentCategory?->faq_items) ? $currentCategory->faq_items : []);
    $requestedCategorySlug = $currentCategory
        ? \Illuminate\Support\Str::slug((string) request()->route('category'))
        : null;
    $useRouterAuthorityCanonical = $isRouterAuthorityPage
        && ($currentCategory->slug === \App\Support\UbiquitiSeoCatalog::ROUTER_AUTHORITY_SLUG
            || $requestedCategorySlug === \App\Support\UbiquitiSeoCatalog::ROUTER_AUTHORITY_SLUG);
    $catalogCanonicalUrl = $currentCategory
        ? (\App\Support\SeoMetadata::canonicalOverride($currentCategory)
            ?: ($useRouterAuthorityCanonical
                ? \App\Support\CanonicalUrl::route('category.show', ['category' => \App\Support\UbiquitiSeoCatalog::ROUTER_AUTHORITY_SLUG], $catalogCanonicalQuery)
                : \App\Support\CanonicalUrl::route('category.show', $currentCategory, $catalogCanonicalQuery)))
        : \App\Support\CanonicalUrl::route('home', [], $catalogCanonicalQuery);
    $faqSchema = ($showHomepageSections || ($currentCategory && $categoryFaqItems !== []))
        ? \App\Support\StructuredData::faq($showHomepageSections ? $homepageContent->faqItems() : $categoryFaqItems)
        : null;
    $breadcrumbSchema = null;
    if ($currentCategory) {
        $breadcrumbItems = [
            ['name' => 'Home', 'url' => \App\Support\CanonicalUrl::route('home')],
        ];
        if ($currentCategory->parent_id && $currentCategory->parent) {
            $breadcrumbItems[] = [
                'name' => \App\Support\UbiquitiSeoCatalog::navLabel($currentCategory->parent),
                'url' => \App\Support\CanonicalUrl::route('category.show', $currentCategory->parent),
            ];
        }
        $breadcrumbItems[] = [
            'name' => \App\Support\UbiquitiSeoCatalog::navLabel($currentCategory),
            'url' => $catalogCanonicalUrl,
        ];
        $breadcrumbSchema = \App\Support\StructuredData::breadcrumbs($breadcrumbItems);
    }
    $routerPricesCategoryUrl = null;
    $routerPricesIntro = null;
    if ($showHomepageSections && $homepageProductCategory) {
        $routerPricesCategoryUrl = route('category.show', ['category' => \App\Support\UbiquitiSeoCatalog::ROUTER_AUTHORITY_SLUG]);
        $routerPricesIntro = trim((string) $homepageProductCategory->meta_description)
            ?: \App\Support\ProductContent::excerpt((string) $homepageProductCategory->description, 220);
        $routerPricesIntro = $routerPricesIntro !== ''
            ? $routerPricesIntro
            : 'Compare current Ubiquiti router prices, models and availability for homes, offices and ISP networks across Kenya.';
    }
    $productImageFallback = \App\Support\ProductImageCatalog::placeholderUrl();
    $whyChooseIcons = [
        <<<'SVG'
<svg viewBox="0 0 48 48" aria-hidden="true"><path d="M24 6l7 3 7 8-2 10-7 5-5 10-5-10-7-5-2-10 7-8 7-3zM17 37h14" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
SVG,
        <<<'SVG'
<svg viewBox="0 0 48 48" aria-hidden="true"><path d="M6 15h22v14H6zm22 4h7l5 5v5h-12zM14 34a3 3 0 106 0 3 3 0 00-6 0zm17 0a3 3 0 106 0 3 3 0 00-6 0z" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
SVG,
        <<<'SVG'
<svg viewBox="0 0 48 48" aria-hidden="true"><path d="M11 12l9 9m0 0l-4 4a4 4 0 000 6l2 2a4 4 0 006 0l4-4m-8-8l10-10m-1 1l9 9m-10 10l4 4a4 4 0 010 6l-2 2a4 4 0 01-6 0l-4-4" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
SVG,
        <<<'SVG'
<svg viewBox="0 0 48 48" aria-hidden="true"><path d="M8 16h32v20H8zm0 0l6-6h20l6 6M14 26h8" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
SVG,
        <<<'SVG'
<svg viewBox="0 0 48 48" aria-hidden="true"><path d="M24 6l12 4v10c0 8-5 14-12 18C17 34 12 28 12 20V10zm-5 14l4 4 7-8" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
SVG,
        <<<'SVG'
<svg viewBox="0 0 48 48" aria-hidden="true"><path d="M15 26v-6a9 9 0 0118 0v6m-18 0v7a3 3 0 003 3h12a3 3 0 003-3v-7m-18 0h18m-9 7v3" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
SVG,
    ];
@endphp

@section('title', $catalogTitle)
@section('meta_description', $catalogMetaDescription)
@section('canonical_url', $catalogCanonicalUrl)
@section('og_title', $catalogTitle)
@section('og_description', $catalogMetaDescription)
@if($currentCategory && $currentCategory->image_url)
    @section('og_image', $currentCategory->image_url)
@endif
@if($search !== '')
    @section('robots', 'noindex,follow')
@elseif($currentCategory && \App\Support\SeoMetadata::robots($currentCategory))
    @section('robots', \App\Support\SeoMetadata::robots($currentCategory))
@endif
@if($faqSchema)
    @push('head')
        <script type="application/ld+json">@json($faqSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)</script>
    @endpush
@endif
@if($breadcrumbSchema)
    @push('head')
        <script type="application/ld+json">@json($breadcrumbSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)</script>
    @endpush
@endif

@section('content')
<section class="home-layout">
    <aside class="category-sidebar">
        <h3>Categories</h3>
        <ul>
            <li>
                <a class="{{ !$selectedCategory ? 'active' : '' }}" href="{{ route('home') }}">All Products</a>
            </li>
            @foreach($categories as $category)
                <li>
                    <a class="{{ $selectedCategory === $category->id ? 'active' : '' }}"
                       href="{{ route('category.show', $category) }}">
                        {{ $category->name }}
                    </a>
                </li>
            @endforeach
        </ul>
    </aside>

    <div class="home-main">
        @if($search !== '')
            <section class="panel catalog-search-summary">
                <p class="catalog-search-eyebrow">Search results</p>
                <h1>Results for "{{ $search }}"</h1>
                <p>
                    {{ $products->total() }} product{{ $products->total() === 1 ? '' : 's' }} found.
                    @if($products->total() === 0)
                        Try a different product name, SKU, or category.
                    @endif
                </p>
            </section>
        @elseif($currentCategory)
            <nav class="product-breadcrumbs" aria-label="Breadcrumb">
                <a href="{{ route('home') }}">Home</a>
                @if($currentCategory->parent_id && $currentCategory->parent)
                    <span>/</span>
                    <a href="{{ route('category.show', $currentCategory->parent) }}">{{ \App\Support\UbiquitiSeoCatalog::navLabel($currentCategory->parent) }}</a>
                @endif
                <span>/</span>
                <span>{{ \App\Support\UbiquitiSeoCatalog::navLabel($currentCategory) }}</span>
            </nav>

            <section class="panel category-content-panel {{ $currentCategory->image_url ? 'category-content-panel--with-image' : '' }}">
                @if($currentCategory->image_url)
                    <div class="category-content-media">
                        <img src="{{ $currentCategory->image_url }}" alt="{{ $currentCategory->name }}" width="640" height="480" loading="lazy" decoding="async">
                    </div>
                @endif

                <div class="category-content-body">
                    <p class="catalog-search-eyebrow">{{ $currentCategory->parent?->name ?? 'Category' }}</p>
                    <h1>{{ $currentCategory->name }}</h1>

                    @if($categorySummary)
                        <p class="category-content-summary">{{ $categorySummary }}</p>
                    @endif
                </div>
            </section>
        @else
            <div
                class="hero-banner"
                @if($homepageContent->heroImageUrl())
                    style="background-image: linear-gradient(120deg, rgba(198, 31, 31, 0.82), rgba(234, 88, 12, 0.72)), url('{{ $homepageContent->heroImageUrl() }}'); background-size: cover; background-position: center;"
                @endif
            >
                <div>
                    <h1>{{ $homepageContent->hero_title }}</h1>
                    <p>{{ $homepageContent->hero_description }}</p>
                </div>
            </div>

        @endif

        @if($usedCategoryFallback)
            <section class="panel category-fallback-note">
                <p>Showing relevant Ubiquiti products from the wider catalogue while this category is being organized.</p>
            </section>
        @endif

        @if($isRouterAuthorityPage)
            <section class="panel router-price-panel">
                <div class="router-price-head">
                    <div>
                        <p class="catalog-search-eyebrow">Current catalogue pricing</p>
                        <h2>Ubiquiti router price list in Kenya</h2>
                    </div>
                    <p>Prices and availability are loaded from product records, so this table updates when the catalogue is updated.</p>
                </div>

                <div class="table-wrap router-price-table-wrap">
                    <table class="router-price-table">
                        <thead>
                        <tr>
                            <th>Ubiquiti Model</th>
                            <th>Current Price</th>
                            <th>Key Use</th>
                            <th>Availability</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($routerPriceTableProducts as $routerProduct)
                            <tr>
                                <td><a href="{{ route('product.show', $routerProduct) }}">{{ \App\Support\ProductSeo::model($routerProduct) }}</a></td>
                                <td>{{ \App\Support\ProductPricing::priceLabel($routerProduct) }}</td>
                                <td>{{ \App\Support\ProductSeo::keyUse($routerProduct) }}</td>
                                <td>{{ \App\Support\ProductPricing::availabilityLabel($routerProduct) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4">Router prices will appear here when router products are assigned to this category.</td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </section>

            <section class="panel router-guide-panel">
                <div class="router-guide-grid">
                    <div>
                        <h2>How to choose a Ubiquiti router</h2>
                        <ul>
                            <li>Choose enough Ethernet ports for your WAN, LAN and future expansion.</li>
                            <li>Check whether you need SFP or SFP+ uplinks for fibre or switch aggregation.</li>
                            <li>Match throughput needs to your firewall, VPN and gateway workload.</li>
                            <li>Use PoE-capable models when powering access points or outdoor radios from the router.</li>
                        </ul>
                    </div>
                    <div>
                        <h2>Home, office and ISP selection guide</h2>
                        <ul>
                            <li>Home and small office: compact UniFi gateways with stable Ethernet and simple management.</li>
                            <li>Growing offices: gateways with more throughput, VLAN support and reliable failover options.</li>
                            <li>ISP and enterprise: UISP or rackmount gateway models for higher throughput and uplinks.</li>
                        </ul>
                    </div>
                </div>
            </section>
        @endif

        @if($showFeaturedProductRows)
            <div class="featured-rows-head">
                <h2>{{ $homepageProductCategory ? 'Shop Ubiquiti Routers' : 'Popular Ubiquiti Products' }}</h2>
                @if($homepageProductCategory)
                    <a class="featured-rows-link" href="{{ route('category.show', ['category' => \App\Support\UbiquitiSeoCatalog::ROUTER_AUTHORITY_SLUG]) }}">View all &rarr;</a>
                @endif
            </div>
            <section class="products-grid products-grid--router-rows" aria-label="{{ $homepageProductCategory?->name ?? 'Featured products' }}">
                @foreach($homepageFeaturedProducts as $product)
                    @include('partials.product-card', ['product' => $product, 'productImageFallback' => $productImageFallback])
                @endforeach
            </section>
        @endif

        @unless($showFullWidthProductGrid)
            <section class="products-grid">
                @forelse($products as $product)
                    @include('partials.product-card', ['product' => $product, 'productImageFallback' => $productImageFallback])
                @empty
                    @unless($currentCategory && ($categoryDescriptionHtml || $isRouterAuthorityPage))
                        <p class="empty">No products found.</p>
                    @endunless
                @endforelse
            </section>

            @if($products->hasPages())
                <div class="pager">
                    @if($products->onFirstPage())
                        <span class="pager-link disabled">Previous</span>
                    @else
                        <a class="pager-link" href="{{ $products->previousPageUrl() }}">Previous</a>
                    @endif
                    <span>Page {{ $products->currentPage() }} of {{ $products->lastPage() }}</span>
                    @if($products->hasMorePages())
                        <a class="pager-link" href="{{ $products->nextPageUrl() }}">Next</a>
                    @else
                        <span class="pager-link disabled">Next</span>
                    @endif
                </div>
            @endif
        @endunless

        @if($currentCategory && $categoryDescriptionHtml)
            <section class="panel category-description-panel">
                <div class="rich-content category-description-content">{!! $categoryDescriptionHtml !!}</div>
            </section>
        @endif
    </div>

    @if($showFullWidthProductGrid)
        <section class="home-product-section home-product-section--full-width">
            <section class="products-grid products-grid--catalog" aria-label="All products">
                @forelse($products as $product)
                    @include('partials.product-card', ['product' => $product, 'productImageFallback' => $productImageFallback])
                @empty
                    <p class="empty">No products found.</p>
                @endforelse
            </section>

            @if($products->hasPages())
                <div class="pager">
                    @if($products->onFirstPage())
                        <span class="pager-link disabled">Previous</span>
                    @else
                        <a class="pager-link" href="{{ $products->previousPageUrl() }}">Previous</a>
                    @endif
                    <span>Page {{ $products->currentPage() }} of {{ $products->lastPage() }}</span>
                    @if($products->hasMorePages())
                        <a class="pager-link" href="{{ $products->nextPageUrl() }}">Next</a>
                    @else
                        <span class="pager-link disabled">Next</span>
                    @endif
                </div>
            @endif
        </section>
    @endif

    @if($showHomepageSections)
        <section class="home-extra-sections home-extra-sections--full-width">
            @if($routerPricesCategoryUrl)
                <section class="home-section home-section--router-prices">
                    <div class="home-section-head">
                        <p class="home-section-kicker">Current catalogue pricing</p>
                        <h2>Ubiquiti Router Prices in Kenya</h2>
                        <p>{{ $routerPricesIntro }}</p>
                        <a class="home-section-cta" href="{{ $routerPricesCategoryUrl }}">View current Ubiquiti router prices</a>
                    </div>
                </section>
            @endif

            @if($homepageComparisonLinks !== [])
                <section class="home-section home-section--guides">
                    <div class="home-section-head">
                        <h2>Ubiquiti Buying Guides &amp; Comparisons</h2>
                        <p>Compare popular Ubiquiti models side by side before choosing your router or switch.</p>
                    </div>
                    <nav class="category-hub-links" aria-label="Ubiquiti buying guides and comparisons">
                        @foreach($homepageComparisonLinks as $comparisonLink)
                            <a href="{{ $comparisonLink['url'] }}">{{ $comparisonLink['label'] }}</a>
                        @endforeach
                    </nav>
                </section>
            @endif

            <section class="home-section home-section--why-choose">
                <div class="home-section-head">
                    <h2>{{ $homepageContent->whyChooseTitle() }}</h2>
                    @if($homepageContent->whyChooseIntro())
                        <p>{{ $homepageContent->whyChooseIntro() }}</p>
                    @endif
                </div>

                <div class="why-choose-grid">
                    @foreach($homepageContent->whyChooseItems() as $item)
                        <article class="why-choose-card">
                            <div class="why-choose-icon">{!! $whyChooseIcons[$loop->index % count($whyChooseIcons)] !!}</div>
                            <h3>{{ $item['title'] }}</h3>
                            <p>{{ $item['description'] }}</p>
                        </article>
                    @endforeach
                </div>
            </section>

            @if($testimonials->isNotEmpty())
                <section class="home-section home-section--testimonials">
                    <div class="home-section-head">
                        @if($homepageContent->testimonialsBadge())
                            <p class="home-section-kicker">{{ $homepageContent->testimonialsBadge() }}</p>
                        @endif
                        <h2>{{ $homepageContent->testimonialsTitle() }}</h2>
                        @if($homepageContent->testimonialsIntro())
                            <p>{{ $homepageContent->testimonialsIntro() }}</p>
                        @endif
                    </div>

                    <div class="testimonial-grid">
                        @foreach($testimonials as $testimonial)
                            @php($rating = max(1, min(5, (int) $testimonial->rating)))
                            <article class="testimonial-card">
                                <span class="testimonial-quote-mark" aria-hidden="true">&ldquo;</span>
                                <div class="testimonial-stars" aria-label="{{ $rating }} out of 5 stars">{{ str_repeat('★', $rating) }}</div>
                                <p class="testimonial-quote">{{ $testimonial->quote }}</p>
                                <h3>{{ $testimonial->name }}</h3>
                                <p class="testimonial-role">{{ $testimonial->role }}</p>
                            </article>
                        @endforeach
                    </div>
                </section>
            @endif

            <section class="home-section home-section--faq">
                <div class="home-section-head">
                    @if($homepageContent->faqBadge())
                        <p class="home-section-kicker">{{ $homepageContent->faqBadge() }}</p>
                    @endif
                    <h2>{{ $homepageContent->faqTitle() }}</h2>
                    @if($homepageContent->faqIntro())
                        <p>{{ $homepageContent->faqIntro() }}</p>
                    @endif
                </div>

                <div class="faq-list">
                    @foreach($homepageContent->faqItems() as $item)
                        <details class="faq-item" @if($loop->first) open @endif>
                            <summary>{{ $item['question'] }}</summary>
                            <p>{{ $item['answer'] }}</p>
                        </details>
                    @endforeach
                </div>
            </section>

            <section class="home-section home-section--guide">
                <div class="home-guide-shell">
                    <div class="home-guide-border" aria-hidden="true"></div>
                    <div class="home-guide-card home-guide-card--body-only home-guide-card--scroll-frame">
                        <div class="home-guide-body home-guide-body--standalone home-guide-body--scrollable rich-content">
                            {!! $homepageContent->contentBody() !!}
                        </div>
                    </div>
                </div>
            </section>
        </section>
    @endif

    @if($currentCategory && $categoryFaqItems !== [])
        <section class="home-extra-sections home-extra-sections--full-width">
            <section class="home-section home-section--faq">
                <div class="home-section-head">
                    <h2>{{ $currentCategory->name }} FAQs</h2>
                </div>

                <div class="faq-list">
                    @foreach($categoryFaqItems as $item)
                        @if(!empty($item['question']) && !empty($item['answer']))
                            <details class="faq-item" @if($loop->first) open @endif>
                                <summary>{{ $item['question'] }}</summary>
                                <p>{{ $item['answer'] }}</p>
                            </details>
                        @endif
                    @endforeach
                </div>
            </section>
        </section>
    @endif

    @if($currentCategory && $relatedCategories->isNotEmpty())
        <section class="home-extra-sections home-extra-sections--full-width">
            <section class="home-section related-category-section">
                <div class="home-section-head">
                    <h2>Related Ubiquiti categories</h2>
                </div>
                <nav class="category-hub-links" aria-label="Related Ubiquiti categories">
                    @foreach($relatedCategories as $relatedCategory)
                        <a href="{{ route('category.show', $relatedCategory) }}">{{ $relatedCategory->name }}</a>
                    @endforeach
                </nav>
            </section>
        </section>
    @endif
</section>
@endsection
