<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\HomepageContent;
use App\Models\Page;
use App\Models\Product;
use App\Models\Testimonial;
use App\Support\CanonicalUrl;
use App\Support\ProductContent;
use App\Support\ProductSeo;
use App\Support\SeoMetadata;
use App\Support\UbiquitiSeoCatalog;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\View\View;

class StorefrontController extends Controller
{
    private const ROUTER_PRICES_CATEGORY_NAME = 'Ubiquiti Routers';

    private const ROUTER_PRICES_CATEGORY_SLUG = UbiquitiSeoCatalog::ROUTER_AUTHORITY_SLUG;

    private const ROUTER_PRODUCTS_LIMIT = 6;

    public function index(Request $request): View|RedirectResponse
    {
        $category = $request->filled('category')
            ? Category::query()->find($request->integer('category'))
            : null;

        return $this->renderCatalog($request, $category);
    }

    public function showCategory(Request $request, string $category): View|RedirectResponse
    {
        $resolvedCategory = $this->resolveCategory($category);

        if ($resolvedCategory instanceof RedirectResponse) {
            return $resolvedCategory;
        }

        return $this->renderCatalog($request, $resolvedCategory);
    }

    public function show(string $product): View|RedirectResponse
    {
        $product = $this->resolveProduct($product);

        if ($product instanceof RedirectResponse) {
            return $product;
        }

        $product->load(['vendor', 'category', 'images' => fn ($query) => $query->orderByDesc('is_primary')->orderBy('sort_order')]);

        if ($product->status !== 'active' || ! $product->vendor?->is_approved) {
            abort(404);
        }

        $relatedProducts = Product::query()
            ->with(['vendor', 'category', 'images' => fn ($query) => $query->orderByDesc('is_primary')->orderBy('sort_order')])
            ->active()
            ->whereKeyNot($product->id)
            ->when($product->category_id, fn (Builder $query) => $query->where('category_id', $product->category_id))
            ->latest()
            ->limit(4)
            ->get();

        return view('product.show', [
            'product' => $product,
            'relatedProducts' => $relatedProducts,
            'relatedCategories' => $this->relatedCategories($product->category),
            'comparisonLinks' => ProductSeo::comparisonLinks($product),
        ]);
    }

    public function showPage(string $page): View|RedirectResponse
    {
        $requestedSlug = Str::slug($page);
        $page = $this->publicPageQuery()
            ->whereRaw('LOWER(slug) = ?', [Str::lower($requestedSlug)])
            ->first();

        if (! $page) {
            return $this->showTrustPage($requestedSlug);
        }

        if ($page->slug !== $requestedSlug) {
            return redirect()->route('pages.show', ['page' => $page->slug], 301);
        }

        return view('page.show', [
            'page' => $page,
            'pageBody' => ProductContent::sanitizeRichText($page->body) ?: '<p>No content available.</p>',
            'pageMetaDescription' => SeoMetadata::pageDescription($page, ProductContent::excerpt($page->body, 160)),
        ]);
    }

    public function blogIndex(): View
    {
        $posts = $this->publicPageQuery()
            ->where('type', 'post')
            ->latest()
            ->paginate(12);

        return view('blog.index', [
            'posts' => $posts,
            'blogCategories' => $this->blogCategories(),
            'canonicalUrl' => CanonicalUrl::route('blog.index'),
        ]);
    }

    public function showBlogPost(string $page): View|RedirectResponse
    {
        $requestedSlug = Str::slug($page);
        $page = $this->publicPageQuery()
            ->where('type', 'post')
            ->whereRaw('LOWER(slug) = ?', [Str::lower($requestedSlug)])
            ->first();

        abort_unless($page, 404);

        if ($page->slug !== $requestedSlug) {
            return redirect()->route('blog.show', ['page' => $page->slug], 301);
        }

        return view('page.show', [
            'page' => $page,
            'pageBody' => ProductContent::sanitizeRichText($page->body) ?: '<p>No content available.</p>',
            'pageMetaDescription' => SeoMetadata::pageDescription($page, ProductContent::excerpt($page->body, 160)),
            'canonicalRoute' => 'blog.show',
        ]);
    }

    public function redirectLegacyPage(string $page): RedirectResponse
    {
        return redirect()->route('pages.show', ['page' => Str::slug($page)], 301);
    }

    public function redirectLegacyProduct(string $product): RedirectResponse
    {
        $product = $this->findProductBySlug($product);

        abort_unless($product, 404);

        return redirect()->route('product.show', $product, 301);
    }

    public function redirectLegacyCategory(string $category): RedirectResponse
    {
        $resolvedCategory = $this->resolveCategory($category, true);

        if ($resolvedCategory instanceof RedirectResponse) {
            return $resolvedCategory;
        }

        return redirect()->route('category.show', $resolvedCategory, 301);
    }

    public function redirectTopLevelCategory(string $categorySlug): RedirectResponse
    {
        $targetSlug = UbiquitiSeoCatalog::targetSlugForTopLevel($categorySlug);

        abort_unless($targetSlug, 404);

        $targetCategory = Category::query()->where('slug', $targetSlug)->first();

        if ($targetCategory) {
            return redirect()->route('category.show', $targetCategory, 301);
        }

        return redirect()->route('home', [], 301);
    }

    private function renderCatalog(Request $request, ?Category $currentCategory = null): View|RedirectResponse
    {
        $categories = Category::query()
            ->whereNull('parent_id')
            ->orderBy('name')
            ->get();

        $search = trim((string) $request->query('search', ''));
        $searchSlug = Str::slug($search);
        $normalizedSearch = Str::lower($search);
        $catalogFilters = $this->catalogFilters($request);
        $hasCatalogFilters = $this->hasCatalogFilters($catalogFilters);
        $productsQuery = Product::query()
            ->with(['vendor', 'category', 'images' => fn ($query) => $query->orderByDesc('is_primary')->orderBy('sort_order')])
            ->active();

        $selectedCategory = null;
        if ($currentCategory) {
            $currentCategory->loadMissing('children', 'parent');
            $selectedCategory = $currentCategory->parent_id ?: $currentCategory->id;
            $this->constrainProductsToCategory($productsQuery, $currentCategory);
        }

        if ($search !== '' && ! $currentCategory) {
            $exactProduct = Product::query()
                ->active()
                ->where(function ($query) use ($normalizedSearch, $searchSlug): void {
                    $query->whereRaw('LOWER(name) = ?', [$normalizedSearch])
                        ->orWhereRaw('LOWER(sku) = ?', [$normalizedSearch]);

                    if (SeoMetadata::columnReady('products', 'model_number')) {
                        $query->orWhereRaw('LOWER(model_number) = ?', [$normalizedSearch]);
                    }

                    if ($searchSlug !== '') {
                        $query->orWhere('slug', $searchSlug);
                    }
                })
                ->first();

            if ($exactProduct) {
                return redirect()->route('product.show', $exactProduct);
            }
        }

        if ($search !== '') {
            $productsQuery->where(function ($query) use ($search, $searchSlug): void {
                    $query->where('name', 'like', '%'.$search.'%')
                        ->orWhere('sku', 'like', '%'.$search.'%')
                        ->orWhere('description', 'like', '%'.$search.'%')
                        ->orWhere('meta_description', 'like', '%'.$search.'%')
                        ->orWhereHas('category', fn ($categoryQuery) => $categoryQuery->where('name', 'like', '%'.$search.'%'))
                        ->orWhereHas('vendor', fn ($vendorQuery) => $vendorQuery->where('shop_name', 'like', '%'.$search.'%'));

                    foreach (['model_number', 'brand', 'key_specifications', 'technical_specifications'] as $column) {
                        if (SeoMetadata::columnReady('products', $column)) {
                            $query->orWhere($column, 'like', '%'.$search.'%');
                        }
                    }

                    if ($searchSlug !== '') {
                        $query->orWhere('slug', 'like', '%'.$searchSlug.'%');
                    }
                });
        }

        $this->applyCatalogFilters($productsQuery, $catalogFilters);

        $homepageProductCategory = $search === '' && ! $currentCategory
            ? $this->routerPricesCategory()
            : null;
        $featuredProductIds = $search === '' && ! $currentCategory
            ? HomepageContent::current()->featuredProductIds()
            : [];

        if ($featuredProductIds !== []) {
            $homepageFeaturedProducts = Product::query()
                ->with(['vendor', 'category', 'images' => fn ($query) => $query->orderByDesc('is_primary')->orderBy('sort_order')])
                ->active()
                ->whereIn('id', $featuredProductIds)
                ->get()
                ->sortBy(function (Product $product) use ($featuredProductIds): int {
                    $position = array_search($product->id, $featuredProductIds, true);

                    return $position === false ? PHP_INT_MAX : $position;
                })
                ->take(self::ROUTER_PRODUCTS_LIMIT)
                ->values();
        } elseif ($homepageProductCategory) {
            $homepageFeaturedProducts = Product::query()
                ->with(['vendor', 'category', 'images' => fn ($query) => $query->orderByDesc('is_primary')->orderBy('sort_order')])
                ->active()
                ->where(function (Builder $query) use ($homepageProductCategory): void {
                    $this->constrainProductsToCategory($query, $homepageProductCategory);
                })
                ->latest()
                ->limit(self::ROUTER_PRODUCTS_LIMIT)
                ->get();
        } else {
            $homepageFeaturedProducts = collect();
        }

        if ($homepageFeaturedProducts->isNotEmpty()) {
            $productsQuery->whereNotIn('id', $homepageFeaturedProducts->modelKeys());
        }

        $products = $this->applyCatalogSort($productsQuery, $catalogFilters['sort'])
            ->paginate(24)
            ->withQueryString();
        $usedCategoryFallback = false;

        if ($products->total() === 0 && $currentCategory && UbiquitiSeoCatalog::isBroadUbiquitiCategory($currentCategory)) {
            $products = UbiquitiSeoCatalog::ubiquitiProductsQuery()
                ->latest()
                ->paginate(24)
                ->withQueryString();
            $usedCategoryFallback = $products->total() > 0;
        }

        $routerPriceTableProducts = collect();
        $isRouterAuthorityPage = $currentCategory && UbiquitiSeoCatalog::isRouterAuthorityCategory($currentCategory);

        if ($isRouterAuthorityPage) {
            $routerPriceTableProducts = Product::query()
                ->with(['vendor', 'category', 'images' => fn ($query) => $query->orderByDesc('is_primary')->orderBy('sort_order')])
                ->active()
                ->where(function (Builder $query) use ($currentCategory): void {
                    $this->constrainProductsToCategory($query, $currentCategory);
                })
                ->orderBy('name')
                ->limit(48)
                ->get();

            if ($routerPriceTableProducts->isEmpty()) {
                $routerPriceTableProducts = UbiquitiSeoCatalog::ubiquitiProductsQuery()
                    ->where(function (Builder $query): void {
                        $query->where('name', 'like', '%router%')
                            ->orWhere('name', 'like', '%gateway%')
                            ->orWhere('name', 'like', '%dream machine%')
                            ->orWhere('name', 'like', '%edgerouter%')
                            ->orWhere('slug', 'like', '%router%');
                    })
                    ->orderBy('name')
                    ->limit(48)
                    ->get();
            }
        }

        return view('home', [
            'categories' => $categories,
            'featuredCategories' => $this->featuredCategories(),
            'homepageContent' => HomepageContent::current(),
            'homepageProductCategory' => $homepageProductCategory,
            'homepageFeaturedProducts' => $homepageFeaturedProducts,
            'products' => $products,
            'articleResults' => $this->articleSearchResults($search),
            'search' => $search,
            'catalogFilters' => $catalogFilters,
            'hasCatalogFilters' => $hasCatalogFilters,
            'filterOptions' => $this->filterOptions(),
            'selectedCategory' => $selectedCategory,
            'testimonials' => Testimonial::homepageItems(),
            'currentCategory' => $currentCategory,
            'usedCategoryFallback' => $usedCategoryFallback,
            'isRouterAuthorityPage' => $isRouterAuthorityPage,
            'routerPriceTableProducts' => $routerPriceTableProducts,
            'routerFaqItems' => UbiquitiSeoCatalog::routerFaqItems(),
            'relatedCategories' => $this->relatedCategories($currentCategory),
            'homepageComparisonLinks' => $search === '' && ! $currentCategory
                ? $this->homepageComparisonLinks()
                : [],
        ]);
    }

    private function publicPageQuery(): Builder
    {
        return Page::query()
            ->when(Page::publicationFieldsReady(), fn (Builder $query) => $query->where('status', 'published'));
    }

    /**
     * @return array<int, string>
     */
    private function blogCategories(): array
    {
        if (! Page::publicationFieldsReady()) {
            return [];
        }

        return Page::query()
            ->where('type', 'post')
            ->where('status', 'published')
            ->whereNotNull('blog_category')
            ->distinct()
            ->orderBy('blog_category')
            ->pluck('blog_category')
            ->filter()
            ->values()
            ->all();
    }

    private function articleSearchResults(string $search)
    {
        if ($search === '' || ! Page::storageReady()) {
            return collect();
        }

        return $this->publicPageQuery()
            ->where('type', 'post')
            ->where(function (Builder $query) use ($search): void {
                $query->where('title', 'like', '%'.$search.'%')
                    ->orWhere('slug', 'like', '%'.Str::slug($search).'%')
                    ->orWhere('meta_title', 'like', '%'.$search.'%')
                    ->orWhere('meta_description', 'like', '%'.$search.'%');

                if (SeoMetadata::columnReady('pages', 'blog_category')) {
                    $query->orWhere('blog_category', 'like', '%'.$search.'%');
                }
            })
            ->latest()
            ->limit(8)
            ->get();
    }

    /**
     * @return array{availability: ?string, brand: ?string, price_min: ?float, price_max: ?float, sort: string}
     */
    private function catalogFilters(Request $request): array
    {
        $availability = $request->query('availability');
        $availability = in_array($availability, ['in_stock', 'out_of_stock'], true) ? $availability : null;

        $sort = $request->query('sort');
        $sort = in_array($sort, ['latest', 'name_asc', 'price_asc', 'price_desc'], true) ? $sort : 'latest';

        return [
            'availability' => $availability,
            'brand' => $this->cleanFilterText($request->query('brand')),
            'price_min' => $this->cleanFilterNumber($request->query('price_min')),
            'price_max' => $this->cleanFilterNumber($request->query('price_max')),
            'sort' => $sort,
        ];
    }

    /**
     * @param  array{availability: ?string, brand: ?string, price_min: ?float, price_max: ?float, sort: string}  $filters
     */
    private function hasCatalogFilters(array $filters): bool
    {
        return $filters['availability'] !== null
            || $filters['brand'] !== null
            || $filters['price_min'] !== null
            || $filters['price_max'] !== null
            || $filters['sort'] !== 'latest';
    }

    /**
     * @param  array{availability: ?string, brand: ?string, price_min: ?float, price_max: ?float, sort: string}  $filters
     */
    private function applyCatalogFilters(Builder $query, array $filters): void
    {
        if ($filters['availability'] === 'in_stock') {
            $query->where('stock', '>', 0);
        } elseif ($filters['availability'] === 'out_of_stock') {
            $query->where('stock', '<=', 0);
        }

        if ($filters['brand'] !== null && SeoMetadata::columnReady('products', 'brand')) {
            $query->where('brand', $filters['brand']);
        }

        if ($filters['price_min'] !== null) {
            $query->where('price', '>=', $filters['price_min']);
        }

        if ($filters['price_max'] !== null) {
            $query->where('price', '<=', $filters['price_max']);
        }
    }

    private function applyCatalogSort(Builder $query, string $sort): Builder
    {
        return match ($sort) {
            'name_asc' => $query->orderBy('name'),
            'price_asc' => $query->orderByRaw('price IS NULL')->orderBy('price'),
            'price_desc' => $query->orderByDesc('price'),
            default => $query->latest(),
        };
    }

    /**
     * @return array{brands: array<int, string>}
     */
    private function filterOptions(): array
    {
        $brands = SeoMetadata::columnReady('products', 'brand')
            ? Product::query()
                ->active()
                ->whereNotNull('brand')
                ->distinct()
                ->orderBy('brand')
                ->pluck('brand')
                ->filter()
                ->values()
                ->all()
            : [];

        return [
            'brands' => $brands,
        ];
    }

    private function constrainProductsToCategory(Builder $query, Category $category): void
    {
        $categoryIds = $this->catalogCategoryIds($category);

        $query->where(function (Builder $categoryQuery) use ($categoryIds): void {
            $categoryQuery->whereIn('category_id', $categoryIds);

            if (Product::categoryAssignmentsReady()) {
                $categoryQuery->orWhereHas('categories', fn (Builder $assignedCategoryQuery) => $assignedCategoryQuery->whereIn('categories.id', $categoryIds));
            }
        });
    }

    private function cleanFilterText(mixed $value): ?string
    {
        $text = trim(preg_replace('/\s+/u', ' ', strip_tags((string) $value)) ?? '');

        return $text !== '' ? Str::limit($text, 80, '') : null;
    }

    private function cleanFilterNumber(mixed $value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        $number = filter_var($value, FILTER_VALIDATE_FLOAT);

        return $number !== false && $number >= 0 ? (float) $number : null;
    }

    /**
     * @return array<int, array{url: string, label: string}>
     */
    private function homepageComparisonLinks(): array
    {
        $labels = UbiquitiSeoCatalog::comparisonPages();
        $links = [];

        foreach (UbiquitiSeoCatalog::resolvableComparisonSlugs() as $slug) {
            $links[] = [
                'url' => route('comparison.show', $slug),
                'label' => $labels[$slug] ?? $slug,
            ];
        }

        return $links;
    }

    private function routerPricesCategory(): ?Category
    {
        $routerSlugs = $this->routerAuthoritySlugs();

        $candidates = Category::query()
            ->where(function (Builder $query) use ($routerSlugs): void {
                $query->whereRaw('LOWER(name) = ?', [Str::lower(self::ROUTER_PRICES_CATEGORY_NAME)])
                    ->orWhereIn('slug', $routerSlugs);
            })
            ->with('children')
            ->get()
            ->sortBy(function (Category $category) use ($routerSlugs): int {
                if ($category->slug === self::ROUTER_PRICES_CATEGORY_SLUG) {
                    return 0;
                }

                $position = array_search($category->slug, $routerSlugs, true);

                return $position === false ? 999 : $position + 1;
            })
            ->values();

        if ($candidates->isEmpty()) {
            return null;
        }

        return $candidates->first(function (Category $category): bool {
            return Product::query()
                ->active()
                ->where(function (Builder $query) use ($category): void {
                    $this->constrainProductsToCategory($query, $category);
                })
                ->exists();
        }) ?: $candidates->first();
    }

    /**
     * @return array<int, string>
     */
    private function routerAuthoritySlugs(): array
    {
        return array_values(array_unique(array_merge(
            [self::ROUTER_PRICES_CATEGORY_SLUG, UbiquitiSeoCatalog::ROUTER_AUTHORITY_SLUG],
            array_keys(array_filter(
                UbiquitiSeoCatalog::legacyCategoryRedirects(),
                fn (string $target): bool => $target === UbiquitiSeoCatalog::ROUTER_AUTHORITY_SLUG
            ))
        )));
    }

    /**
     * @return array<int>
     */
    private function catalogCategoryIds(Category $category): array
    {
        if ($category->parent_id) {
            return [$category->id];
        }

        $category->loadMissing('children');

        return array_merge([$category->id], $category->children->pluck('id')->all());
    }

    private function resolveCategory(string $slug, bool $forceRedirect = false): Category|RedirectResponse
    {
        $requestedSlug = Str::slug($slug);
        $legacyTarget = UbiquitiSeoCatalog::targetSlugForLegacy($requestedSlug);

        if ($legacyTarget && ($forceRedirect || $legacyTarget !== $requestedSlug)) {
            $targetCategory = Category::query()->where('slug', $legacyTarget)->first();

            if ($targetCategory) {
                return redirect()->route('category.show', $targetCategory, 301);
            }
        }

        $category = Category::query()->whereRaw('LOWER(slug) = ?', [Str::lower($requestedSlug)])->first();

        if (! $category && $requestedSlug === UbiquitiSeoCatalog::ROUTER_AUTHORITY_SLUG) {
            $category = $this->routerPricesCategory();
        }

        abort_unless($category, 404);

        if ($category->slug !== $requestedSlug) {
            if ($requestedSlug === UbiquitiSeoCatalog::ROUTER_AUTHORITY_SLUG && UbiquitiSeoCatalog::isRouterAuthorityCategory($category)) {
                return $category;
            }

            return redirect()->route('category.show', $category, 301);
        }

        return $category;
    }

    private function resolveProduct(string $slug): Product|RedirectResponse
    {
        $requestedSlug = Str::slug($slug);
        $product = $this->findProductBySlug($requestedSlug);

        abort_unless($product, 404);

        if ($product->slug !== $requestedSlug) {
            return redirect()->route('product.show', $product, 301);
        }

        return $product;
    }

    private function findProductBySlug(string $slug): ?Product
    {
        return Product::query()->whereRaw('LOWER(slug) = ?', [Str::lower(Str::slug($slug))])->first();
    }

    private function showTrustPage(string $slug): View
    {
        $trustPages = [
            'about-us' => [
                'title' => 'About Us',
                'heading' => 'About Ubiquiti UniFi Kenya',
                'summary' => 'Ubiquiti UniFi Kenya is a networking equipment catalogue for buyers in Kenya. Compare access points, switches, gateways and outdoor wireless equipment by price, specifications and stock availability. Contact us for product selection, compatibility questions and quotations.',
            ],
            'contact-us' => [
                'title' => 'Contact Us',
                'heading' => 'Contact Ubiquiti UniFi Kenya',
                'summary' => 'Use the available contact details below to enquire about products, quotations, delivery and support.',
            ],
            'delivery-policy' => [
                'title' => 'Delivery Policy',
                'heading' => 'Delivery Policy',
                'summary' => 'Delivery availability, cost and timelines are confirmed before dispatch based on order size, stock location and destination.',
            ],
            'returns-policy' => [
                'title' => 'Returns Policy',
                'heading' => 'Returns Policy',
                'summary' => 'Our return window is '.config('business.return_window_days', 7).' days. Contact us by phone or email to discuss a return and confirm the return process.',
            ],
            'warranty-policy' => [
                'title' => 'Warranty Policy',
                'heading' => 'Warranty Policy',
                'summary' => 'Warranty coverage depends on the product, seller and supplier terms. Confirm warranty status before purchase or quotation approval.',
            ],
            'privacy-policy' => [
                'title' => 'Privacy Policy',
                'heading' => 'Privacy Policy',
                'summary' => 'Customer information is used to process accounts, orders, delivery communication and support requests.',
            ],
            'terms-and-conditions' => [
                'title' => 'Terms and Conditions',
                'heading' => 'Terms and Conditions',
                'summary' => 'Orders, quotations, payments, delivery and support are handled under the seller terms shown during purchase or direct enquiry.',
            ],
        ];

        abort_unless(isset($trustPages[$slug]), 404);

        return view('page.trust', [
            'slug' => $slug,
            'trustPage' => $trustPages[$slug],
            'canonicalUrl' => CanonicalUrl::route('pages.show', ['page' => $slug]),
        ]);
    }

    private function featuredCategories()
    {
        $primarySlugs = array_keys(UbiquitiSeoCatalog::primaryCategories());
        $featuredSlugs = array_values(array_unique(array_merge($primarySlugs, $this->routerAuthoritySlugs())));

        return Category::query()
            ->whereIn('slug', $featuredSlugs)
            ->get()
            ->sortBy(function (Category $category) use ($primarySlugs): int {
                $targetSlug = UbiquitiSeoCatalog::targetSlugForLegacy($category->slug) ?: $category->slug;
                $position = array_search($targetSlug, $primarySlugs, true);

                return $position === false ? 999 : $position;
            })
            ->unique(fn (Category $category): string => UbiquitiSeoCatalog::targetSlugForLegacy($category->slug) ?: $category->slug)
            ->values();
    }

    private function relatedCategories(?Category $currentCategory)
    {
        $primarySlugs = array_keys(UbiquitiSeoCatalog::primaryCategories());

        return Category::query()
            ->whereIn('slug', $primarySlugs)
            ->when($currentCategory, fn ($query) => $query->whereKeyNot($currentCategory->id))
            ->orderBy('name')
            ->limit(8)
            ->get();
    }
}
