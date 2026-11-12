<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Support\CanonicalUrl;
use App\Support\UbiquitiSeoCatalog;
use Illuminate\View\View;

class ComparisonController extends Controller
{
    /**
     * @var array<string, array{0: string, 1: string, 2: string}>
     */
    public function show(string $comparison): View
    {
        $comparisonProducts = UbiquitiSeoCatalog::comparisonProducts();
        $comparisonTitles = UbiquitiSeoCatalog::comparisonPages();

        abort_unless(isset($comparisonProducts[$comparison]), 404);

        [$left, $right] = $comparisonProducts[$comparison];
        $title = $comparisonTitles[$comparison] ?? $comparison;
        $products = collect([$left, $right])
            ->map(fn (string $needle): ?Product => $this->findProduct($needle))
            ->filter()
            ->values();

        abort_if($products->count() < 2, 404);

        return view('comparison.show', [
            'comparison' => $comparison,
            'title' => $title,
            'products' => $products,
            'canonicalUrl' => CanonicalUrl::route('comparison.show', $comparison),
        ]);
    }

    private function findProduct(string $needle): ?Product
    {
        $needleSlug = str($needle)->lower()->replace('+', '-')->replace('_', '-')->slug()->toString();

        return Product::query()
            ->with(['vendor', 'category', 'images' => fn ($query) => $query->orderByDesc('is_primary')->orderBy('sort_order')])
            ->active()
            ->where(function ($query) use ($needle, $needleSlug): void {
                $query->where('name', 'like', '%'.$needle.'%')
                    ->orWhere('sku', 'like', '%'.$needle.'%')
                    ->orWhere('slug', 'like', '%'.$needleSlug.'%');
            })
            ->first();
    }
}
