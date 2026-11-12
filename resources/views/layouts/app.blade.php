<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    @php
        $homepageBrandContent = \App\Models\HomepageContent::current();
        $siteLogoUrl = $homepageBrandContent->siteLogoUrl();
        $pageTitle = trim($__env->yieldContent('title')) ?: config('app.name', 'Ubiquiti UniFi Kenya');
        $pageDescription = trim($__env->yieldContent('meta_description')) ?: 'Browse Ubiquiti products, UniFi networking equipment and current prices in Kenya.';
        $marketCssVersion = @filemtime(public_path('assets/market.css')) ?: time();
        $canonicalUrl = trim($__env->yieldContent('canonical_url'));
        $robotsContent = trim($__env->yieldContent('robots'));
        $openGraphTitle = trim($__env->yieldContent('og_title')) ?: $pageTitle;
        $openGraphDescription = trim($__env->yieldContent('og_description')) ?: $pageDescription;
        $openGraphImage = trim($__env->yieldContent('og_image')) ?: $siteLogoUrl;
        $openGraphType = trim($__env->yieldContent('og_type')) ?: 'website';
        $organizationSchema = \App\Support\StructuredData::organization($homepageBrandContent);
        $headerPhone = $homepageBrandContent->contactPhone();
        $headerPhoneHref = $headerPhone ? 'tel:'.preg_replace('/[^\d+]+/', '', $headerPhone) : null;
        $websiteSchema = \App\Support\StructuredData::website();
        $primaryNavItems = $homepageBrandContent->navMenuItems();
        $menuCategories = \Illuminate\Support\Facades\Schema::hasTable('categories')
            ? \App\Models\Category::query()->whereNull('parent_id')->with('children')->orderBy('name')->get()
            : collect();
    @endphp
    <title>{!! $pageTitle !!}</title>
    <meta name="description" content="{!! $pageDescription !!}">
    <link rel="canonical" href="{!! $canonicalUrl !== '' ? $canonicalUrl : \App\Support\CanonicalUrl::current() !!}">
    @if($robotsContent !== '')
        <meta name="robots" content="{!! $robotsContent !!}">
    @endif
    <meta property="og:type" content="{!! $openGraphType !!}">
    <meta property="og:site_name" content="{{ config('app.name', 'Ubiquiti UniFi Kenya') }}">
    <meta property="og:title" content="{!! $openGraphTitle !!}">
    <meta property="og:description" content="{!! $openGraphDescription !!}">
    <meta property="og:url" content="{!! $canonicalUrl !== '' ? $canonicalUrl : \App\Support\CanonicalUrl::current() !!}">
    @if($openGraphImage)
        <meta property="og:image" content="{{ \App\Support\CanonicalUrl::absoluteAsset($openGraphImage) }}">
    @endif
    <meta name="twitter:card" content="{{ $openGraphImage ? 'summary_large_image' : 'summary' }}">
    <meta name="twitter:title" content="{!! $openGraphTitle !!}">
    <meta name="twitter:description" content="{!! $openGraphDescription !!}">
    @if($openGraphImage)
        <meta name="twitter:image" content="{{ \App\Support\CanonicalUrl::absoluteAsset($openGraphImage) }}">
    @endif
    <script type="application/ld+json">@json($organizationSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)</script>
    <script type="application/ld+json">@json($websiteSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)</script>
    <link rel="stylesheet" href="{{ asset('assets/market.css') }}?v={{ $marketCssVersion }}">
    @stack('head')
</head>
<body>
<a class="skip-link" href="#main-content">Skip to content</a>
<header class="top-header">
    <div class="nav-wrap">
        <a href="{{ route('home') }}" class="logo" aria-label="Go to homepage">
            @if($siteLogoUrl)
                <img class="logo-image" src="{{ $siteLogoUrl }}" alt="{{ config('app.name', 'Ubiquiti UniFi Kenya') }}">
            @else
                <span class="logo-main logo-main--single">{{ config('app.name', 'Ubiquiti UniFi Kenya') }}</span>
            @endif
        </a>

        <form class="search-form" method="get" action="{{ route('home') }}" role="search">
            <input type="search" name="search" value="{{ request('search') }}" placeholder="Search Ubiquiti access points, switches and gateways" aria-label="Search products" autocomplete="off" required>
            <button type="submit">Search</button>
        </form>

        <div class="header-actions">
            <nav class="top-links top-contact-links" aria-label="Header actions">
                @if($headerPhone && $headerPhoneHref)
                    <a class="contact-link contact-link--phone" href="{{ $headerPhoneHref }}">Phone {{ $headerPhone }}</a>
                @endif
                <a class="account-link header-login-link" href="{{ route('login') }}">Login</a>
            </nav>

            <nav class="top-account-links top-account-links--menu-only" aria-label="Account">
                <button type="button" class="menu-toggle" aria-expanded="false" aria-controls="mobile-menu" aria-label="Open navigation menu">
                    <span class="menu-toggle-icon" aria-hidden="true"></span>
                </button>
            </nav>
        </div>
    </div>

    @if($primaryNavItems !== [])
        <nav class="primary-nav" aria-label="Main navigation">
            <div class="primary-nav-inner">
                <a class="primary-nav-link" href="{{ route('home') }}">Home</a>
                @foreach($primaryNavItems as $navItem)
                    <a class="primary-nav-link" href="{{ $navItem['url'] }}">{{ $navItem['label'] }}</a>
                @endforeach
            </div>
        </nav>
    @endif
</header>

<div class="mobile-menu-backdrop" data-menu-backdrop hidden></div>
<nav id="mobile-menu" class="mobile-menu" aria-label="Main navigation" data-mobile-menu hidden>
    <div class="mobile-menu-head">
        <span class="mobile-menu-title">Menu</span>
        <button type="button" class="mobile-menu-close" data-menu-close aria-label="Close navigation menu">&times;</button>
    </div>
    <ul class="mobile-menu-list">
        <li><a class="mobile-menu-link" href="{{ route('home') }}">Home</a></li>
        @foreach($primaryNavItems as $navItem)
            <li><a class="mobile-menu-link" href="{{ $navItem['url'] }}">{{ $navItem['label'] }}</a></li>
        @endforeach
        @foreach($menuCategories as $menuCategory)
            @if($menuCategory->children->isNotEmpty())
                <li class="mobile-menu-accordion">
                    <button type="button" class="mobile-menu-link mobile-menu-accordion-toggle" aria-expanded="false" aria-controls="mobile-submenu-{{ $menuCategory->id }}">
                        <span>{{ \App\Support\UbiquitiSeoCatalog::navLabel($menuCategory) }}</span>
                        <span class="mobile-menu-chevron" aria-hidden="true"></span>
                    </button>
                    <ul id="mobile-submenu-{{ $menuCategory->id }}" class="mobile-menu-submenu" hidden>
                        <li><a class="mobile-menu-sublink" href="{{ route('category.show', $menuCategory) }}">All {{ \App\Support\UbiquitiSeoCatalog::navLabel($menuCategory) }}</a></li>
                        @foreach($menuCategory->children as $menuChildCategory)
                            <li><a class="mobile-menu-sublink" href="{{ route('category.show', $menuChildCategory) }}">{{ \App\Support\UbiquitiSeoCatalog::navLabel($menuChildCategory) }}</a></li>
                        @endforeach
                    </ul>
                </li>
            @else
                <li><a class="mobile-menu-link" href="{{ route('category.show', $menuCategory) }}">{{ \App\Support\UbiquitiSeoCatalog::navLabel($menuCategory) }}</a></li>
            @endif
        @endforeach
        <li><a class="mobile-menu-link" href="{{ route('pages.show', ['page' => 'contact-us']) }}">Contact Us</a></li>
        <li><a class="mobile-menu-link" href="{{ route('login') }}">Login</a></li>
    </ul>
</nav>

<main class="container" id="main-content">
    @if(session('success'))
        <div class="alert success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert error">{{ session('error') }}</div>
    @endif
    @if($errors->any())
        <div class="alert error">
            {{ $errors->first() }}
        </div>
    @endif
    @yield('content')
</main>

<footer class="footer">
    <nav class="footer-links" aria-label="Footer">
        <a href="{{ route('pages.show', ['page' => 'about-us']) }}">About Us</a>
        <a href="{{ route('pages.show', ['page' => 'contact-us']) }}">Contact Us</a>
        <a href="{{ route('pages.show', ['page' => 'delivery-policy']) }}">Delivery Policy</a>
        <a href="{{ route('pages.show', ['page' => 'returns-policy']) }}">Returns Policy</a>
        <a href="{{ route('pages.show', ['page' => 'warranty-policy']) }}">Warranty Policy</a>
        <a href="{{ route('pages.show', ['page' => 'privacy-policy']) }}">Privacy Policy</a>
        <a href="{{ route('pages.show', ['page' => 'terms-and-conditions']) }}">Terms and Conditions</a>
    </nav>
    <p>&copy; {{ date('Y') }} {{ config('business.name', config('app.name', 'Ubiquiti UniFi Kenya')) }}</p>
</footer>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var searchForm = document.querySelector('.search-form');
    var searchInput = searchForm ? searchForm.querySelector('input[name="search"]') : null;

    if (searchForm && searchInput) {
        searchForm.addEventListener('submit', function (event) {
            if (searchInput.value.trim() === '') {
                event.preventDefault();
                searchInput.setCustomValidity('Please enter a product name to search.');
                searchInput.reportValidity();
                searchInput.setCustomValidity('');
                searchInput.focus();
            }
        });
    }

    var toggle = document.querySelector('.menu-toggle');
    var menu = document.querySelector('[data-mobile-menu]');
    var backdrop = document.querySelector('[data-menu-backdrop]');
    var closeButton = document.querySelector('[data-menu-close]');

    if (!toggle || !menu) {
        return;
    }

    var setMenuState = function (open) {
        menu.hidden = !open;
        if (backdrop) {
            backdrop.hidden = !open;
        }
        toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
        toggle.setAttribute('aria-label', open ? 'Close navigation menu' : 'Open navigation menu');
        document.documentElement.classList.toggle('menu-is-open', open);
        if (open && closeButton) {
            closeButton.focus();
        }
    };

    toggle.addEventListener('click', function () {
        setMenuState(menu.hidden);
    });

    if (closeButton) {
        closeButton.addEventListener('click', function () {
            setMenuState(false);
        });
    }

    if (backdrop) {
        backdrop.addEventListener('click', function () {
            setMenuState(false);
        });
    }

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape' && !menu.hidden) {
            setMenuState(false);
            toggle.focus();
        }
    });

    menu.querySelectorAll('.mobile-menu-accordion-toggle').forEach(function (accordionToggle) {
        accordionToggle.addEventListener('click', function () {
            var submenu = document.getElementById(accordionToggle.getAttribute('aria-controls') || '');
            if (!submenu) {
                return;
            }

            var isOpen = !submenu.hidden;
            submenu.hidden = isOpen;
            accordionToggle.setAttribute('aria-expanded', isOpen ? 'false' : 'true');
        });
    });
});
</script>
</body>
</html>
