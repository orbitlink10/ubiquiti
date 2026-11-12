<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Support\CanonicalUrl;
use App\Support\MerchantCatalog;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;

class MerchantFeedController extends Controller
{
    public function __invoke(): Response
    {
        $products = $this->eligibleProducts();

        $items = $products->map(fn (Product $product): array => $this->feedItem($product))->values();

        $xml = view('merchant.google-feed', [
            'items' => $items,
            'title' => config('app.name', 'Ubiquiti UniFi Kenya').' — Google Merchant Feed',
            'homeUrl' => CanonicalUrl::route('home'),
        ])->render();

        return response($xml, 200, ['Content-Type' => 'application/xml; charset=UTF-8']);
    }

    /**
     * @return \Illuminate\Support\Collection<int, Product>
     */
    private function eligibleProducts()
    {
        return Cache::remember('google-merchant-feed:products', 300, function () {
            return Product::query()
                ->with(['vendor', 'category', 'images'])
                ->active()
                ->get()
                ->filter(fn (Product $product): bool => MerchantCatalog::isFeedEligible($product))
                ->sortBy(fn (Product $product): string => (string) $product->sku)
                ->values();
        });
    }

    /**
     * @return array<string, mixed>
     */
    private function feedItem(Product $product): array
    {
        return [
            'id' => $this->feedId($product),
            'title' => MerchantCatalog::merchantTitle($product),
            'description' => MerchantCatalog::merchantDescription($product),
            'link' => CanonicalUrl::route('product.show', $product),
            'image_link' => MerchantCatalog::primaryImage($product),
            'additional_image_link' => MerchantCatalog::additionalImages($product),
            'availability' => MerchantCatalog::availability($product),
            'price' => $this->feedPrice($product),
            'condition' => MerchantCatalog::condition($product),
            'brand' => 'Ubiquiti',
            'mpn' => MerchantCatalog::mpn($product),
            'gtin' => MerchantCatalog::gtin($product),
            'google_product_category' => MerchantCatalog::googleCategoryFor($product),
            'product_type' => MerchantCatalog::productTypeFor($product),
            'identifier_exists' => $this->identifierExists($product),
        ];
    }

    private function feedId(Product $product): string
    {
        $sku = trim((string) $product->sku);

        return $sku !== '' ? $sku : 'product-'.$product->id;
    }

    private function feedPrice(Product $product): string
    {
        return number_format((float) $product->price, 2, '.', '').' '.MerchantCatalog::CURRENCY;
    }

    private function identifierExists(Product $product): ?string
    {
        if (MerchantCatalog::gtin($product) || MerchantCatalog::mpn($product)) {
            return null;
        }

        return 'no';
    }
}
