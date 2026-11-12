@extends('layouts.app')

@php
    $homepageBrandContent = \App\Models\HomepageContent::current();
    $title = $trustPage['title'] . ' | ' . config('app.name', 'Ubiquiti UniFi Kenya');
    $description = \Illuminate\Support\Str::limit($trustPage['summary'], 155, '');
    $businessFields = array_filter([
        'Business name' => config('business.name'),
        'Legal name' => config('business.legal_name'),
        'Phone' => $homepageBrandContent->contactPhone(),
        'WhatsApp' => $homepageBrandContent->contactWhatsApp(),
        'Email' => $homepageBrandContent->contactEmail(),
        'Address' => config('business.address'),
        'Business hours' => config('business.hours'),
        'Delivery coverage' => config('business.delivery_coverage'),
        'Payment options' => implode(', ', config('business.payment_options', [])),
    ]);
    $breadcrumbSchema = \App\Support\StructuredData::breadcrumbs([
        ['name' => 'Home', 'url' => \App\Support\CanonicalUrl::route('home')],
        ['name' => $trustPage['title'], 'url' => $canonicalUrl],
    ]);
@endphp

@section('title', $title)
@section('meta_description', $description)
@section('canonical_url', $canonicalUrl)

@push('head')
    <script type="application/ld+json">@json($breadcrumbSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)</script>
@endpush

@section('content')
<article class="page-story trust-page">
    <section class="page-story-article-shell">
        <div class="page-story-article-head">
            <div class="page-story-article-labels">
                <p class="page-story-article-kicker">{{ config('app.name', 'Ubiquiti UniFi Kenya') }}</p>
                <p class="page-story-article-subtitle">{{ $trustPage['title'] }}</p>
            </div>
            <a class="page-story-back" href="{{ route('home') }}">Shop Ubiquiti UniFi Products</a>
        </div>

        <h1 class="page-story-title">{{ $trustPage['heading'] }}</h1>
        <p class="page-story-summary">{{ $trustPage['summary'] }}</p>

        @if($businessFields !== [])
            <dl class="trust-info-grid">
                @foreach($businessFields as $label => $value)
                    @if($value !== '')
                        <div>
                            <dt>{{ $label }}</dt>
                            <dd>
                                @if($label === 'Phone')
                                    <a href="tel:{{ preg_replace('/[^\d+]/', '', $value) }}">{{ $value }}</a>
                                @elseif($label === 'Email')
                                    <a href="mailto:{{ $value }}">{{ $value }}</a>
                                @else
                                    {{ $value }}
                                @endif
                            </dd>
                        </div>
                    @endif
                @endforeach
            </dl>
        @endif
    </section>
</article>
@endsection
