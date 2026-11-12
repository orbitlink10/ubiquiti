<?php

namespace App\Support;

use App\Models\Product;

class ProductPricing
{
    public static function hasPublishedPrice(Product $product): bool
    {
        return $product->price !== null;
    }

    public static function priceLabel(Product $product, string $currency = 'KSh'): string
    {
        if (! self::hasPublishedPrice($product)) {
            return 'Contact for price';
        }

        return $currency.' '.number_format((float) $product->price, 2);
    }

    public static function availabilityLabel(Product $product, bool $uppercase = false): string
    {
        $label = $product->stock > 0 ? 'In stock' : 'Contact for availability';

        return $uppercase ? strtoupper($label) : $label;
    }

    public static function canPurchase(Product $product): bool
    {
        return self::hasPublishedPrice($product) && $product->stock > 0;
    }
}
