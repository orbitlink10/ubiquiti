@extends('layouts.app')

@php
    $articleTitle = \App\Support\SeoMetadata::pageTitle($page);
    $heroSummary = trim((string) $pageMetaDescription);
    $backUrl = url()->previous() !== request()->fullUrl()
        ? url()->previous()
        : route('home');
    $pageCanonicalUrl = \App\Support\SeoMetadata::canonicalOverride($page)
        ?: \App\Support\CanonicalUrl::route('pages.show', $page);
    $fullPageTitle = \Illuminate\Support\Str::contains($articleTitle, config('app.name', 'Ubiquiti UniFi Kenya'))
        ? $articleTitle
        : $articleTitle . ' | ' . config('app.name', 'Ubiquiti UniFi Kenya');
    $breadcrumbSchema = \App\Support\StructuredData::breadcrumbs([
        ['name' => 'Home', 'url' => \App\Support\CanonicalUrl::route('home')],
        ['name' => $page->title, 'url' => $pageCanonicalUrl],
    ]);
    $pageFaqItems = is_array($page->faq_items) ? $page->faq_items : [];
    $faqSchema = \App\Support\StructuredData::faq($pageFaqItems);
@endphp

@section('title', $fullPageTitle)
@section('meta_description', $pageMetaDescription)
@section('canonical_url', $pageCanonicalUrl)
@section('og_type', $page->type === 'post' ? 'article' : 'website')
@section('og_title', \App\Support\SeoMetadata::openGraphTitle($page, $articleTitle))
@section('og_description', \App\Support\SeoMetadata::openGraphDescription($page, $pageMetaDescription))
@if(\App\Support\SeoMetadata::openGraphImage($page, $page->image_url))
    @section('og_image', \App\Support\SeoMetadata::openGraphImage($page, $page->image_url))
@endif
@if(\App\Support\SeoMetadata::robots($page))
    @section('robots', \App\Support\SeoMetadata::robots($page))
@endif

@push('head')
    <script type="application/ld+json">@json($breadcrumbSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)</script>
    @if($faqSchema)
        <script type="application/ld+json">@json($faqSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)</script>
    @endif
@endpush

@section('content')
<article class="page-story">
    <section class="page-story-hero">
        <div class="page-story-hero-copy">
            <h1 class="page-story-title">{{ $page->title }}</h1>

            @if($heroSummary !== '')
                <p class="page-story-summary">{{ $heroSummary }}</p>
            @endif

            <div class="page-story-actions">
                <a class="page-story-primary" href="{{ route('home') }}">Shop Products</a>
                <a class="page-story-secondary" href="#page-article">Read Article</a>
            </div>
        </div>

        <div class="page-story-hero-media{{ $page->image_url ? '' : ' is-empty' }}">
            @if($page->image_url)
                <img src="{{ $page->image_url }}" alt="{{ $page->alt_text ?: $page->title }}" width="900" height="620" loading="eager" decoding="async">
            @else
                <div class="page-story-hero-placeholder" aria-hidden="true">
                    <span>{{ $page->title }}</span>
                </div>
            @endif
        </div>
    </section>

    <section class="page-story-article-shell" id="page-article">
        <div class="page-story-article-head">
            <div class="page-story-article-labels">
                <p class="page-story-article-kicker">{{ $page->title }}</p>
                @if($page->heading_two)
                    <p class="page-story-article-subtitle">{{ $page->heading_two }}</p>
                @endif
            </div>

            <a class="page-story-back" href="{{ $backUrl }}">Back</a>
        </div>

        <h2 class="page-story-article-title">{{ $articleTitle }}</h2>

        @if($page->image_url)
            <figure class="page-story-feature-image">
                <img src="{{ $page->image_url }}" alt="{{ $page->alt_text ?: $page->title }}" width="900" height="620" loading="lazy" decoding="async">
            </figure>
        @endif

        <div class="page-story-article-copy rich-content">{!! $pageBody !!}</div>

        @if($pageFaqItems !== [])
            <section class="page-faq-section">
                <h2>{{ $page->title }} FAQs</h2>
                <div class="faq-list">
                    @foreach($pageFaqItems as $item)
                        @if(!empty($item['question']) && !empty($item['answer']))
                            <details class="faq-item" @if($loop->first) open @endif>
                                <summary>{{ $item['question'] }}</summary>
                                <p>{{ $item['answer'] }}</p>
                            </details>
                        @endif
                    @endforeach
                </div>
            </section>
        @endif
    </section>
</article>
@endsection
