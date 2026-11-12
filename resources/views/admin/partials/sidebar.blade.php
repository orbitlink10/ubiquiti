@php
    $showSidebarBrand = $showSidebarBrand ?? true;
    $activeAdminNav = $activeAdminNav ?? 'dashboard';
    $homepageBrandContent = \App\Models\HomepageContent::current();
    $siteLogoUrl = $homepageBrandContent->siteLogoUrl();
    $adminNavItems = [
        ['id' => 'dashboard', 'label' => 'Dashboard', 'badge' => 'DB', 'href' => route('admin.dashboard')],
        ['id' => 'pages-content', 'label' => 'Homepage Content', 'badge' => 'HC', 'href' => route('admin.pages-content.edit')],
        ['id' => 'testimonials', 'label' => 'Testimonials', 'badge' => 'TS', 'href' => route('admin.testimonials.index')],
        ['id' => 'categories', 'label' => 'Categories', 'badge' => 'CT', 'href' => route('admin.categories.index')],
        ['id' => 'subcategories', 'label' => 'Sub Categories', 'badge' => 'SC', 'href' => route('admin.subcategories.index')],
        ['id' => 'products', 'label' => 'Products', 'badge' => 'PR', 'href' => route('admin.products.index')],
        ['id' => 'merchant', 'label' => 'Merchant Feed', 'badge' => 'MC', 'href' => route('admin.merchant.index')],
        ['id' => 'pages', 'label' => 'Pages', 'badge' => 'PG', 'href' => route('admin.pages.index')],
        ['id' => 'orders', 'label' => 'Orders', 'badge' => 'OR', 'href' => route('admin.orders.index')],
        ['id' => 'invoices', 'label' => 'Invoices', 'badge' => 'IV', 'href' => route('admin.invoices.index')],
    ];
    $adminNavIcons = [
        'dashboard' => <<<'SVG'
<svg viewBox="0 0 24 24" aria-hidden="true">
    <path d="M4 13a8 8 0 1 1 16 0"></path>
    <path d="M6 13v3"></path>
    <path d="M18 13v3"></path>
    <path d="M12 13l3-4"></path>
    <path d="M8 17h8"></path>
</svg>
SVG,
        'pages-content' => <<<'SVG'
<svg viewBox="0 0 24 24" aria-hidden="true">
    <rect x="4" y="5" width="16" height="14" rx="2"></rect>
    <path d="M8 9h8"></path>
    <path d="M8 13h8"></path>
    <path d="M8 17h5"></path>
</svg>
SVG,
        'testimonials' => <<<'SVG'
<svg viewBox="0 0 24 24" aria-hidden="true">
    <path d="M9 11H5a1 1 0 0 1-1-1V6a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v3a4 4 0 0 1-4 4"></path>
    <path d="M19 11h-4a1 1 0 0 1-1-1V6a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v3a4 4 0 0 1-4 4"></path>
</svg>
SVG,
        'categories' => <<<'SVG'
<svg viewBox="0 0 24 24" aria-hidden="true">
    <rect x="4" y="5" width="16" height="14" rx="2"></rect>
    <path d="M9 9h7"></path>
    <path d="M9 12h7"></path>
    <path d="M9 15h7"></path>
    <path d="M7 9h.01"></path>
    <path d="M7 12h.01"></path>
    <path d="M7 15h.01"></path>
</svg>
SVG,
        'subcategories' => <<<'SVG'
<svg viewBox="0 0 24 24" aria-hidden="true">
    <path d="M10 5H6a2 2 0 0 0-2 2v4l8 8 8-8-6-6h-4Z"></path>
    <path d="M8 9h.01"></path>
</svg>
SVG,
        'products' => <<<'SVG'
<svg viewBox="0 0 24 24" aria-hidden="true">
    <path d="M4 9h16v9a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V9Z"></path>
    <path d="M9 9V5h6v4"></path>
    <path d="M12 9v3"></path>
</svg>
SVG,
        'pages' => <<<'SVG'
<svg viewBox="0 0 24 24" aria-hidden="true">
    <path d="M7 4h7l5 5v11a1 1 0 0 1-1 1H7a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2Z"></path>
    <path d="M14 4v5h5"></path>
    <path d="M9 13h6"></path>
    <path d="M9 17h6"></path>
</svg>
SVG,
        'merchant' => <<<'SVG'
<svg viewBox="0 0 24 24" aria-hidden="true">
    <path d="M4 4h16v16H4z"></path>
    <path d="M8 8h8v2H8z"></path>
    <path d="M8 12h5"></path>
    <path d="M8 15h3"></path>
</svg>
SVG,
        'orders' => <<<'SVG'
<svg viewBox="0 0 24 24" aria-hidden="true">
    <path d="M4 5h2l2 10h9l2-7H7"></path>
    <path d="M10 19a1 1 0 1 1 0 .01"></path>
    <path d="M17 19a1 1 0 1 1 0 .01"></path>
</svg>
SVG,
        'invoices' => <<<'SVG'
<svg viewBox="0 0 24 24" aria-hidden="true">
    <path d="M7 4h10v16l-2-1-2 1-2-1-2 1-2-1V6a2 2 0 0 1 2-2Z"></path>
    <path d="M9 9h6"></path>
    <path d="M9 13h6"></path>
    <path d="M9 17h4"></path>
</svg>
SVG,
    ];

    $heroItem = $adminNavItems[0];
    $menuItems = array_slice($adminNavItems, 1);
@endphp

<aside class="admin-sidebar">
    @if($showSidebarBrand)
        <a class="admin-brand-card admin-brand-logo" href="{{ route('home') }}" aria-label="Go to {{ config('app.name', 'Ubiquiti UniFi Kenya') }} homepage">
            <span class="admin-brand-logo-mark" aria-hidden="true">
                @if($siteLogoUrl)
                    <img src="{{ $siteLogoUrl }}" alt="">
                @else
                    <svg viewBox="0 0 48 48" focusable="false">
                        <path class="admin-brand-logo-ring" d="M24 4 41.3 14v20L24 44 6.7 34V14L24 4Z"></path>
                        <path d="M15 28.5c2.25-2.1 5.3-3.38 9-3.38s6.75 1.28 9 3.38"></path>
                        <path d="M18.8 22.8a8.1 8.1 0 0 1 10.4 0"></path>
                        <path d="M22.2 17.4a3.6 3.6 0 0 1 3.6 0"></path>
                        <circle cx="24" cy="33" r="2.3"></circle>
                    </svg>
                @endif
            </span>
            <span class="admin-brand-logo-text">
                <span class="admin-brand-logo-name">{{ config('app.name', 'Ubiquiti UniFi Kenya') }}</span>
                <span class="admin-brand-logo-kicker">Admin Panel</span>
            </span>
        </a>
    @endif

    <div class="admin-sidebar-panel">
        <nav class="admin-sidebar-nav">
            <a class="admin-nav-link @if($activeAdminNav === $heroItem['id']) is-active @endif" href="{{ $heroItem['href'] }}">
                <span class="admin-nav-badge">{!! $adminNavIcons[$heroItem['id']] ?? e($heroItem['badge']) !!}</span>
                <span>{{ $heroItem['label'] }}</span>
            </a>
        </nav>

        <div class="admin-sidebar-menu">
            <p class="admin-sidebar-label">Content Management</p>
            <nav class="admin-sidebar-nav admin-sidebar-nav--menu">
                @foreach($menuItems as $item)
                    <a class="admin-nav-link @if($activeAdminNav === $item['id']) is-active @endif" href="{{ $item['href'] }}">
                        <span class="admin-nav-badge">{!! $adminNavIcons[$item['id']] ?? e($item['badge']) !!}</span>
                        <span>{{ $item['label'] }}</span>
                    </a>
                @endforeach
            </nav>
        </div>
    </div>
</aside>
