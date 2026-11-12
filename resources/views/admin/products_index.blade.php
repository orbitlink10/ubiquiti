@extends('admin.layout')

@section('content')
<div class="admin-shell">
    @include('admin.partials.sidebar', ['activeAdminNav' => 'products'])

    <div class="admin-main admin-management-main admin-products-main">
        <section class="admin-page-head">
            <div>
                <h1 class="admin-page-title">Products</h1>
                <p class="admin-page-copy">Manage and view all products available in the system</p>
            </div>
            <a class="admin-primary-pill" href="{{ route('admin.products.create') }}">+ Add Product</a>
        </section>

        <section class="panel admin-list-panel admin-products-panel">
            <div class="admin-list-panel-head">
                <h2>Product List</h2>
                <form class="admin-search-form" method="get" action="{{ route('admin.products.index') }}">
                    <input type="text" name="search" value="{{ $search }}" placeholder="Search by product name...">
                    <button type="submit" class="admin-search-button">Search</button>
                </form>
            </div>

            <div class="table-wrap">
                <table class="admin-data-table">
                    <thead>
                    <tr>
                        <th>#</th>
                        <th>Image</th>
                        <th>Name</th>
                        <th>Price (KES)</th>
                        <th>Google Merchant</th>
                        <th>Category</th>
                        <th>Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($products as $product)
                        @php($primaryImage = $product->images->first())
                        <tr>
                            <td>{{ $products->firstItem() + $loop->index }}</td>
                            <td>
                                @if($primaryImage?->image_url)
                                    <img class="admin-thumb admin-thumb--product" src="{{ $primaryImage->publicUrl() }}" alt="{{ $product->name }}">
                                @else
                                    <div class="admin-thumb admin-thumb--placeholder admin-thumb--product">No Image</div>
                                @endif
                            </td>
                            <td>{{ $product->name }}</td>
                            <td>{{ $product->price !== null ? number_format((float) $product->price, 2) : '—' }}</td>
                            <td>{{ ($product->include_in_merchant_feed ?? true) ? 'Yes' : 'No' }}</td>
                            <td>{{ $product->category?->name ?? 'General' }}</td>
                            <td>
                                <div class="admin-action-stack">
                                    <a
                                        class="admin-outline-action tone-info"
                                        href="{{ route('product.show', $product) }}"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                    >Preview</a>
                                    <a class="admin-outline-action tone-primary" href="{{ route('admin.products.edit', $product) }}">Update</a>
                                    <form method="post" action="{{ route('admin.products.destroy', $product) }}" onsubmit="return confirm('Delete this product?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="admin-outline-action tone-danger">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="admin-empty-cell">No products found.</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
            @include('admin.partials.pagination', ['paginator' => $products, 'label' => 'Products'])
        </section>
    </div>
</div>
@endsection
