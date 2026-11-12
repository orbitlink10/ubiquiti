<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\Vendor;
use App\Support\ProductContent;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class VendorController extends Controller
{
    private function uniqueSlug(string $table, string $text): string
    {
        $base = Str::slug($text);
        if ($base === '') {
            $base = Str::lower(Str::random(8));
        }

        $slug = $base;
        $counter = 1;
        while (DB::table($table)->where('slug', $slug)->exists()) {
            $slug = $base . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    private function currentVendor(Request $request): ?Vendor
    {
        return $request->user()->vendor;
    }

    public function applyForm(Request $request): View
    {
        return view('vendor.apply', [
            'vendor' => $this->currentVendor($request),
        ]);
    }

    public function apply(Request $request): RedirectResponse
    {
        if ($request->user()->vendor) {
            return back()->with('error', 'You already have a vendor profile.');
        }

        $data = $request->validate([
            'shop_name' => ['required', 'string', 'min:2', 'max:150'],
            'phone' => ['required', 'string', 'max:40'],
            'address' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
        ]);

        Vendor::create([
            'user_id' => $request->user()->id,
            'shop_name' => $data['shop_name'],
            'slug' => $this->uniqueSlug('vendors', $data['shop_name']),
            'description' => $data['description'] ?? null,
            'phone' => $data['phone'],
            'address' => $data['address'],
            'is_approved' => false,
        ]);

        $request->user()->update(['role' => 'vendor']);

        return redirect()->route('vendor.dashboard')->with('success', 'Vendor application submitted. Approval is pending.');
    }

    public function dashboard(Request $request): View|RedirectResponse
    {
        $vendor = $this->currentVendor($request);
        if (!$vendor) {
            return redirect()->route('vendor.apply.form')->with('error', 'Create your vendor profile first.');
        }

        $vendor->load([
            'products.category',
            'vendorOrders.order' => fn ($query) => $query->latest(),
        ]);

        return view('vendor.dashboard', [
            'vendor' => $vendor,
            'products' => $vendor->products()->latest()->get(),
            'orders' => $vendor->vendorOrders()->with('order')->latest()->limit(50)->get(),
        ]);
    }

    public function createProductForm(Request $request): View|RedirectResponse
    {
        $vendor = $this->currentVendor($request);
        if (!$vendor) {
            return redirect()->route('vendor.apply.form')->with('error', 'Create your vendor profile first.');
        }

        return view('vendor.product_create', [
            'vendor' => $vendor,
            'categories' => Category::query()->orderBy('name')->get(),
        ]);
    }

    public function storeProduct(Request $request): RedirectResponse
    {
        $vendor = $this->currentVendor($request);
        if (!$vendor) {
            return redirect()->route('vendor.apply.form')->with('error', 'Create your vendor profile first.');
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'min:2', 'max:180'],
            'category_id' => ['required', 'exists:categories,id'],
            'description' => ['nullable', 'string', 'max:5000'],
            'meta_description' => ['nullable', 'string', 'max:255'],
            'price' => ['nullable', 'numeric', 'min:0.01'],
            'stock' => ['required', 'integer', 'min:0'],
            'image_url' => ['nullable', 'url', 'max:255'],
        ]);

        $sku = strtoupper('SKU-' . Str::upper(Str::random(8)));
        while (Product::where('sku', $sku)->exists()) {
            $sku = strtoupper('SKU-' . Str::upper(Str::random(8)));
        }

        $product = Product::create([
            'vendor_id' => $vendor->id,
            'category_id' => $data['category_id'],
            'name' => $data['name'],
            'slug' => $this->uniqueSlug('products', $data['name']),
            'description' => ProductContent::sanitizeRichText($data['description'] ?? null),
            'meta_description' => ProductContent::sanitizeMetaDescription($data['meta_description'] ?? null),
            'price' => $data['price'] ?? null,
            'stock' => $data['stock'],
            'sku' => $sku,
            'status' => $vendor->is_approved ? 'active' : 'draft',
        ]);

        if (!empty($data['image_url'])) {
            ProductImage::create([
                'product_id' => $product->id,
                'image_url' => $data['image_url'],
                'is_primary' => true,
                'sort_order' => 0,
            ]);
        }

        return redirect()->route('vendor.dashboard')->with('success', 'Product added successfully.');
    }
}
