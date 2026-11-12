<?php

namespace App\Support;

use App\Models\HomepageContent;
use App\Models\Page;
use App\Models\Product;

class StructuredData
{
    /**
     * @param  array<int, string>  $images
     */
    public static function product(Product $product, array $images, string $description, string $canonicalUrl): array
    {
        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'Product',
            'name' => ProductSeo::displayName($product),
            'image' => array_values(array_filter($images)),
            'description' => $description,
            'brand' => [
                '@type' => 'Brand',
                'name' => ProductSeo::brand($product),
            ],
            'offers' => [
                '@type' => 'Offer',
                'url' => $canonicalUrl,
                'priceCurrency' => 'KES',
                'availability' => MerchantCatalog::schemaAvailability($product),
                'itemCondition' => MerchantCatalog::conditionSchema($product),
                'seller' => [
                    '@type' => 'Organization',
                    'name' => config('business.name', config('app.name', 'Ubiquiti UniFi Kenya')),
                ],
            ],
        ];

        if (trim((string) $product->sku) !== '') {
            $schema['sku'] = $product->sku;
        }

        if ($mpn = MerchantCatalog::mpn($product)) {
            $schema['mpn'] = $mpn;
        }

        if ($gtin = MerchantCatalog::gtin($product)) {
            $schema['gtin'] = $gtin;
        }

        if ($product->price !== null) {
            $schema['offers']['price'] = number_format((float) $product->price, 2, '.', '');
        }

        return $schema;
    }

    /**
     * @param  array<int, Product>  $products
     */
    public static function collectionPage(string $name, string $description, string $url, iterable $products): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'CollectionPage',
            'name' => $name,
            'description' => $description,
            'url' => $url,
            'mainEntity' => self::itemList($products, $url),
        ];
    }

    /**
     * @param  iterable<int, Product>  $products
     */
    public static function itemList(iterable $products, string $url): array
    {
        $items = [];
        $position = 1;

        foreach ($products as $product) {
            $items[] = [
                '@type' => 'ListItem',
                'position' => $position++,
                'url' => CanonicalUrl::route('product.show', $product),
                'name' => ProductSeo::displayName($product),
            ];
        }

        return [
            '@type' => 'ItemList',
            'url' => $url,
            'itemListElement' => $items,
        ];
    }

    public static function article(Page $page, string $description, string $canonicalUrl): array
    {
        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'BlogPosting',
            'headline' => SeoMetadata::heading($page, $page->title),
            'description' => $description,
            'url' => $canonicalUrl,
            'mainEntityOfPage' => $canonicalUrl,
        ];

        if ($page->image_url) {
            $schema['image'] = CanonicalUrl::absoluteAsset($page->image_url);
        }

        if ($page->created_at) {
            $schema['datePublished'] = $page->created_at->toAtomString();
        }

        if ($page->updated_at) {
            $schema['dateModified'] = $page->updated_at->toAtomString();
        }

        return $schema;
    }

    /**
     * @param  array<int, array{name: string, url: string}>  $items
     */
    public static function breadcrumbs(array $items): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => array_map(
                fn (array $item, int $index): array => [
                    '@type' => 'ListItem',
                    'position' => $index + 1,
                    'name' => $item['name'],
                    'item' => $item['url'],
                ],
                array_values($items),
                array_keys(array_values($items))
            ),
        ];
    }

    /**
     * @param  array<int, array{question: string, answer: string}>  $items
     */
    public static function faq(array $items): ?array
    {
        $items = array_values(array_filter($items, fn (array $item): bool => $item['question'] !== '' && $item['answer'] !== ''));
        if ($items === []) {
            return null;
        }

        return [
            '@context' => 'https://schema.org',
            '@type' => 'FAQPage',
            'mainEntity' => array_map(fn (array $item): array => [
                '@type' => 'Question',
                'name' => $item['question'],
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text' => $item['answer'],
                ],
            ], $items),
        ];
    }

    public static function organization(?HomepageContent $homepageContent = null): array
    {
        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'Organization',
            'name' => config('business.name', config('app.name', 'Ubiquiti UniFi Kenya')),
            'url' => CanonicalUrl::normalize('/'),
        ];
        if (config('business.legal_name')) {
            $schema['legalName'] = config('business.legal_name');
        }

        if ($homepageContent?->siteLogoUrl()) {
            $schema['logo'] = CanonicalUrl::absoluteAsset($homepageContent->siteLogoUrl());
        }

        $phone = $homepageContent?->contactPhone() ?: config('business.phone');

        if ($phone) {
            $schema['telephone'] = $phone;
        }

        if ($email = $homepageContent?->contactEmail() ?: config('business.email')) {
            $schema['email'] = $email;
        }

        if (config('business.address')) {
            $schema['address'] = [
                '@type' => 'PostalAddress',
                'streetAddress' => config('business.address'),
            ];
        }

        if (config('business.social_profiles')) {
            $schema['sameAs'] = config('business.social_profiles');
        }

        return $schema;
    }

    public static function website(): array
    {
        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'WebSite',
            'name' => config('app.name', 'Ubiquiti UniFi Kenya'),
            'url' => CanonicalUrl::normalize('/'),
        ];

        if (CanonicalUrl::normalize('/') !== '') {
            $schema['potentialAction'] = [
                '@type' => 'SearchAction',
                'target' => [
                    '@type' => 'EntryPoint',
                    'urlTemplate' => CanonicalUrl::normalize('/').'?search={search_term_string}',
                ],
                'query-input' => 'required name=search_term_string',
            ];
        }

        return $schema;
    }
}
