@extends('admin.layout')

@push('head')
    <script src="{{ asset('assets/product-editor.js') }}" defer></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const searchInput = document.querySelector('[data-featured-product-search]');
            const searchButton = document.querySelector('[data-featured-product-search-button]');
            const clearButton = document.querySelector('[data-featured-product-search-clear]');
            const picker = document.querySelector('[data-featured-product-picker]');

            if (!searchInput || !picker) {
                return;
            }

            const items = picker.querySelectorAll('.admin-product-picker-item');
            const emptyNotice = picker.querySelector('.admin-product-picker-empty');

            const applyFilter = function () {
                const query = searchInput.value.trim().toLowerCase();
                let visible = 0;

                items.forEach(function (item) {
                    const haystack = item.getAttribute('data-featured-product-name') || '';
                    const matches = query === '' || haystack.includes(query);

                    item.hidden = !matches;

                    if (matches) {
                        visible += 1;
                    }
                });

                if (emptyNotice) {
                    emptyNotice.hidden = visible !== 0 || query === '';
                }
            };

            searchInput.addEventListener('input', applyFilter);

            searchInput.addEventListener('keydown', function (event) {
                if (event.key === 'Enter') {
                    event.preventDefault();
                    applyFilter();
                }
            });

            if (searchButton) {
                searchButton.addEventListener('click', applyFilter);
            }

            if (clearButton) {
                clearButton.addEventListener('click', function () {
                    searchInput.value = '';
                    applyFilter();
                    searchInput.focus();
                });
            }
        });
    </script>
@endpush

@php
    $whyChooseItems = old('why_choose_items', $homepageContent->whyChooseItems());
    $faqItems = old('faq_items', $homepageContent->faqItems());
    $selectedFeaturedProductIds = collect(old('featured_product_ids', $homepageContent->featuredProductIds()))
        ->map(fn ($id) => (int) $id)
        ->filter(fn ($id) => $id > 0)
        ->all();
    $navMenuItems = collect(old('nav_menu_items', $homepageContent->navMenuItems()))
        ->map(fn ($item) => is_array($item) ? [
            'label' => $item['label'] ?? '',
            'url' => $item['url'] ?? '',
        ] : [
            'label' => '',
            'url' => '',
        ])
        ->take(8)
        ->values()
        ->all();

    while (count($navMenuItems) < 8) {
        $navMenuItems[] = ['label' => '', 'url' => ''];
    }
@endphp

@section('content')
<div class="admin-shell">
    @include('admin.partials.sidebar', ['activeAdminNav' => 'pages-content'])

    <div class="admin-main admin-management-main">
        <section class="admin-page-head admin-page-head--settings">
            <div>
                <h1 class="admin-page-title admin-page-title--settings">Update Homepage Content</h1>
            </div>

            <div class="admin-breadcrumb admin-breadcrumb--settings">
                <a href="{{ route('admin.dashboard') }}">Home</a>
                <span>/</span>
                <span>Update Content</span>
            </div>
        </section>

        <section class="panel admin-settings-panel">
            <div class="admin-settings-panel-bar">Homepage Content Management</div>

            @unless($homepageContentStorageReady)
                <div class="alert error">
                    Homepage content storage is not ready yet. Run <code>php artisan migrate</code> to create the <code>homepage_contents</code> table before saving changes.
                </div>
            @endunless

            <form class="admin-settings-form" method="post" action="{{ route('admin.featured-products.update') }}">
                @csrf

                <section class="admin-settings-group">
                    <div class="admin-settings-group-head">
                        <h2 class="admin-settings-group-title">Featured Homepage Products</h2>
                        <p class="admin-settings-help">Choose up to six products to feature as the first product rows on the homepage. Leave all unchecked to show the latest products automatically.</p>
                    </div>

                    <input type="hidden" name="featured_product_ids" value="">

                    <div class="admin-settings-field">
                        <span class="admin-settings-label">Products</span>
                        <div class="admin-product-search-row">
                            <input
                                class="admin-settings-input"
                                type="search"
                                placeholder="Search products by name, model or SKU..."
                                data-featured-product-search
                                @disabled(! $homepageContentStorageReady)
                            >
                            <button type="button" class="admin-outline-action" data-featured-product-search-button>Search</button>
                            <button type="button" class="admin-outline-action" data-featured-product-search-clear>Clear</button>
                        </div>
                        <div class="admin-product-picker" data-featured-product-picker>
                            @forelse($products as $product)
                                @php($productDisplayName = \App\Support\ProductSeo::displayName($product))
                                <label class="admin-product-picker-item" data-featured-product-name="{{ Str::lower($productDisplayName) }} {{ Str::lower($product->name) }} {{ Str::lower((string) $product->model_number) }} {{ Str::lower((string) $product->sku) }}">
                                    <input
                                        type="checkbox"
                                        name="featured_product_ids[]"
                                        value="{{ $product->id }}"
                                        @checked(in_array($product->id, $selectedFeaturedProductIds, true))
                                        @disabled(! $homepageContentStorageReady)
                                    >
                                    <span>{{ $productDisplayName }}</span>
                                </label>
                            @empty
                                <p class="admin-settings-help">No active products are available yet. Add products from the Products menu first.</p>
                            @endforelse
                            <p class="admin-settings-help admin-product-picker-empty" hidden>No products match your search.</p>
                        </div>
                        <p class="admin-settings-help">Selected products appear in the order they were saved; only the first six are used.</p>
                    </div>
                </section>

                <div class="admin-settings-actions">
                    <button type="submit" class="admin-primary-pill" @disabled(! $homepageContentStorageReady)>Save Featured Products</button>
                </div>
            </form>

            <form class="admin-settings-form" method="post" action="{{ route('admin.pages-content.update') }}" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="section" value="hero">

                <section class="admin-settings-group">
                    <div class="admin-settings-group-head">
                        <h2 class="admin-settings-group-title">Hero Section</h2>
                        <p class="admin-settings-help">Logo, main heading, and image shown on the homepage.</p>
                    </div>

                    <div class="admin-settings-field">
                        <label class="admin-settings-label" for="site_logo">Website Logo</label>
                        <input
                            class="admin-settings-file"
                            id="site_logo"
                            type="file"
                            name="site_logo"
                            accept=".jpg,.jpeg,.png,.webp,image/*"
                            @disabled(! $homepageContentStorageReady)
                        >

                        @if($homepageContent->siteLogoUrl())
                            <div class="admin-settings-preview admin-settings-preview--logo">
                                <p class="admin-settings-help">Current website logo</p>
                                <img src="{{ $homepageContent->siteLogoUrl() }}" alt="Current website logo">
                            </div>
                        @endif
                    </div>

                    <div class="admin-settings-field">
                        <label class="admin-settings-label" for="hero_title">Hero Header Title</label>
                        <input
                            class="admin-settings-input"
                            id="hero_title"
                            type="text"
                            name="hero_title"
                            value="{{ old('hero_title', $homepageContent->hero_title) }}"
                            @disabled(! $homepageContentStorageReady)
                            required
                        >
                    </div>

                    <div class="admin-settings-field">
                        <label class="admin-settings-label" for="hero_description">Hero Header Description</label>
                        <textarea
                            class="admin-settings-textarea"
                            id="hero_description"
                            name="hero_description"
                            rows="3"
                            @disabled(! $homepageContentStorageReady)
                            required
                        >{{ old('hero_description', $homepageContent->hero_description) }}</textarea>
                    </div>

                    <div class="admin-settings-field">
                        <label class="admin-settings-label" for="hero_image">Hero Image (1280 x 720)</label>
                        <input
                            class="admin-settings-file"
                            id="hero_image"
                            type="file"
                            name="hero_image"
                            accept=".jpg,.jpeg,.png,.webp,image/*"
                            @disabled(! $homepageContentStorageReady)
                        >

                        @if($homepageContent->heroImageUrl())
                            <div class="admin-settings-preview">
                                <p class="admin-settings-help">Current hero image</p>
                                <img src="{{ $homepageContent->heroImageUrl() }}" alt="Current homepage hero image">
                            </div>
                        @endif
                    </div>
                </section>

                <div class="admin-settings-actions admin-settings-actions--section">
                    <button type="submit" class="admin-primary-pill" @disabled(! $homepageContentStorageReady)>Save Hero Section</button>
                </div>
            </form>

            <form class="admin-settings-form" method="post" action="{{ route('admin.pages-content.update') }}">
                @csrf
                <input type="hidden" name="section" value="contact">

                <section class="admin-settings-group">
                    <div class="admin-settings-group-head">
                        <h2 class="admin-settings-group-title">Header Contact Details</h2>
                        <p class="admin-settings-help">Phone, WhatsApp and email shown in the homepage header.</p>
                    </div>

                    <div class="admin-settings-subgrid">
                        <div class="admin-settings-field">
                            <label class="admin-settings-label" for="contact_phone">Phone Number</label>
                            <input
                                class="admin-settings-input"
                                id="contact_phone"
                                type="text"
                                name="contact_phone"
                                value="{{ old('contact_phone', $homepageContent->contactPhone()) }}"
                                @disabled(! $homepageContentStorageReady)
                            >
                        </div>

                        <div class="admin-settings-field">
                            <label class="admin-settings-label" for="contact_whatsapp">WhatsApp Number</label>
                            <input
                                class="admin-settings-input"
                                id="contact_whatsapp"
                                type="text"
                                name="contact_whatsapp"
                                value="{{ old('contact_whatsapp', $homepageContent->contactWhatsApp()) }}"
                                @disabled(! $homepageContentStorageReady)
                            >
                        </div>

                        <div class="admin-settings-field">
                            <label class="admin-settings-label" for="contact_email">Email Address</label>
                            <input
                                class="admin-settings-input"
                                id="contact_email"
                                type="email"
                                name="contact_email"
                                value="{{ old('contact_email', $homepageContent->contactEmail()) }}"
                                placeholder="sales@example.co.ke"
                                @disabled(! $homepageContentStorageReady)
                            >
                        </div>
                    </div>
                </section>

                <div class="admin-settings-actions admin-settings-actions--section">
                    <button type="submit" class="admin-primary-pill" @disabled(! $homepageContentStorageReady)>Save Contact Details</button>
                </div>
            </form>

            <form class="admin-settings-form" method="post" action="{{ route('admin.pages-content.update') }}">
                @csrf
                <input type="hidden" name="section" value="navigation">

                <section class="admin-settings-group">
                    <div class="admin-settings-group-head">
                        <h2 class="admin-settings-group-title">Navigation Menu</h2>
                        <p class="admin-settings-help">Menu links shown in the website navigation bar. Leave all rows blank to keep the previous homepage header layout without a menu bar.</p>
                    </div>

                    <div class="admin-settings-card-grid admin-settings-card-grid--two">
                        @foreach($navMenuItems as $index => $item)
                            <div class="admin-settings-repeater-card">
                                <h3 class="admin-settings-repeater-title">Menu {{ $index + 1 }}</h3>

                                <div class="admin-settings-field">
                                    <label class="admin-settings-label" for="nav_menu_items_{{ $index }}_label">Menu Label</label>
                                    <input
                                        class="admin-settings-input"
                                        id="nav_menu_items_{{ $index }}_label"
                                        type="text"
                                        name="nav_menu_items[{{ $index }}][label]"
                                        value="{{ $item['label'] ?? '' }}"
                                        placeholder="Routers"
                                        @disabled(! $homepageContentStorageReady)
                                    >
                                </div>

                                <div class="admin-settings-field">
                                    <label class="admin-settings-label" for="nav_menu_items_{{ $index }}_url">Menu URL</label>
                                    <input
                                        class="admin-settings-input"
                                        id="nav_menu_items_{{ $index }}_url"
                                        type="text"
                                        name="nav_menu_items[{{ $index }}][url]"
                                        value="{{ $item['url'] ?? '' }}"
                                        placeholder="/category/ubiquiti-routers"
                                        @disabled(! $homepageContentStorageReady)
                                    >
                                </div>
                            </div>
                        @endforeach
                    </div>
                </section>

                <div class="admin-settings-actions admin-settings-actions--section">
                    <button type="submit" class="admin-primary-pill" @disabled(! $homepageContentStorageReady)>Save Navigation Menu</button>
                </div>
            </form>

            <form class="admin-settings-form" method="post" action="{{ route('admin.pages-content.update') }}">
                @csrf
                <input type="hidden" name="section" value="why_choose">

                <section class="admin-settings-group">
                    <div class="admin-settings-group-head">
                        <h2 class="admin-settings-group-title">Why Choose Section</h2>
                        <p class="admin-settings-help">Cards that appear below the products on the main homepage.</p>
                    </div>

                    <div class="admin-settings-field">
                        <label class="admin-settings-label" for="why_choose_title">Why Choose Title</label>
                        <input
                            class="admin-settings-input"
                            id="why_choose_title"
                            type="text"
                            name="why_choose_title"
                            value="{{ old('why_choose_title', $homepageContent->whyChooseTitle()) }}"
                            @disabled(! $homepageContentStorageReady)
                        >
                    </div>

                    <div class="admin-settings-field">
                        <label class="admin-settings-label" for="why_choose_intro">Why Choose Intro</label>
                        <textarea
                            class="admin-settings-textarea"
                            id="why_choose_intro"
                            name="why_choose_intro"
                            rows="3"
                            @disabled(! $homepageContentStorageReady)
                        >{{ old('why_choose_intro', $homepageContent->whyChooseIntro()) }}</textarea>
                    </div>

                    <div class="admin-settings-card-grid">
                        @foreach($whyChooseItems as $index => $item)
                            <div class="admin-settings-repeater-card">
                                <h3 class="admin-settings-repeater-title">Benefit {{ $index + 1 }}</h3>

                                <div class="admin-settings-field">
                                    <label class="admin-settings-label" for="why_choose_items_{{ $index }}_title">Card Title</label>
                                    <input
                                        class="admin-settings-input"
                                        id="why_choose_items_{{ $index }}_title"
                                        type="text"
                                        name="why_choose_items[{{ $index }}][title]"
                                        value="{{ $item['title'] ?? '' }}"
                                        @disabled(! $homepageContentStorageReady)
                                    >
                                </div>

                                <div class="admin-settings-field">
                                    <label class="admin-settings-label" for="why_choose_items_{{ $index }}_description">Card Description</label>
                                    <textarea
                                        class="admin-settings-textarea"
                                        id="why_choose_items_{{ $index }}_description"
                                        name="why_choose_items[{{ $index }}][description]"
                                        rows="3"
                                        @disabled(! $homepageContentStorageReady)
                                    >{{ $item['description'] ?? '' }}</textarea>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </section>

                <div class="admin-settings-actions admin-settings-actions--section">
                    <button type="submit" class="admin-primary-pill" @disabled(! $homepageContentStorageReady)>Save Why Choose Section</button>
                </div>
            </form>

            <form class="admin-settings-form" method="post" action="{{ route('admin.pages-content.update') }}">
                @csrf
                <input type="hidden" name="section" value="faq">

                <section class="admin-settings-group">
                    <div class="admin-settings-group-head">
                        <h2 class="admin-settings-group-title">FAQ Section</h2>
                        <p class="admin-settings-help">Frequently asked questions that appear below the testimonial section.</p>
                    </div>

                    <div class="admin-settings-subgrid">
                        <div class="admin-settings-field">
                            <label class="admin-settings-label" for="faq_badge">FAQ Badge</label>
                            <input
                                class="admin-settings-input"
                                id="faq_badge"
                                type="text"
                                name="faq_badge"
                                value="{{ old('faq_badge', $homepageContent->faqBadge()) }}"
                                @disabled(! $homepageContentStorageReady)
                            >
                        </div>

                        <div class="admin-settings-field">
                            <label class="admin-settings-label" for="faq_title">FAQ Title</label>
                            <input
                                class="admin-settings-input"
                                id="faq_title"
                                type="text"
                                name="faq_title"
                                value="{{ old('faq_title', $homepageContent->faqTitle()) }}"
                                @disabled(! $homepageContentStorageReady)
                            >
                        </div>
                    </div>

                    <div class="admin-settings-field">
                        <label class="admin-settings-label" for="faq_intro">FAQ Intro</label>
                        <textarea
                            class="admin-settings-textarea"
                            id="faq_intro"
                            name="faq_intro"
                            rows="3"
                            @disabled(! $homepageContentStorageReady)
                        >{{ old('faq_intro', $homepageContent->faqIntro()) }}</textarea>
                    </div>

                    <div class="admin-settings-card-grid admin-settings-card-grid--two">
                        @foreach($faqItems as $index => $item)
                            <div class="admin-settings-repeater-card">
                                <h3 class="admin-settings-repeater-title">FAQ {{ $index + 1 }}</h3>

                                <div class="admin-settings-field">
                                    <label class="admin-settings-label" for="faq_items_{{ $index }}_question">Question</label>
                                    <input
                                        class="admin-settings-input"
                                        id="faq_items_{{ $index }}_question"
                                        type="text"
                                        name="faq_items[{{ $index }}][question]"
                                        value="{{ $item['question'] ?? '' }}"
                                        @disabled(! $homepageContentStorageReady)
                                    >
                                </div>

                                <div class="admin-settings-field">
                                    <label class="admin-settings-label" for="faq_items_{{ $index }}_answer">Answer</label>
                                    <textarea
                                        class="admin-settings-textarea"
                                        id="faq_items_{{ $index }}_answer"
                                        name="faq_items[{{ $index }}][answer]"
                                        rows="4"
                                        @disabled(! $homepageContentStorageReady)
                                    >{{ $item['answer'] ?? '' }}</textarea>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </section>

                <div class="admin-settings-actions admin-settings-actions--section">
                    <button type="submit" class="admin-primary-pill" @disabled(! $homepageContentStorageReady)>Save FAQ Section</button>
                </div>
            </form>

            <form class="admin-settings-form" method="post" action="{{ route('admin.pages-content.update') }}">
                @csrf
                <input type="hidden" name="section" value="guide">

                <section class="admin-settings-group">
                    <div class="admin-settings-group-head">
                        <h2 class="admin-settings-group-title">Homepage Guide Content</h2>
                        <p class="admin-settings-help">Only the content written in this editor is shown on the homepage guide section.</p>
                    </div>

                    <div class="admin-settings-field">
                        <span class="admin-settings-label">Home Page Content</span>
                        @include('admin.partials.rich_editor', [
                            'name' => 'content_body',
                            'value' => old('content_body', $homepageContent->contentBody()),
                            'placeholder' => 'Write the homepage content here...',
                            'disabled' => ! $homepageContentStorageReady,
                        ])
                        <p class="admin-settings-help">Use headings, paragraphs, lists, links, images, and formatting tools directly in the editor.</p>
                    </div>
                </section>

                <div class="admin-settings-actions admin-settings-actions--section">
                    <button type="submit" class="admin-primary-pill" @disabled(! $homepageContentStorageReady)>Save Homepage Guide</button>
                </div>
            </form>
        </section>
    </div>
</div>
@endsection
