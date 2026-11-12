@extends('admin.layout')

@section('content')
<div class="admin-shell">
    @include('admin.partials.sidebar', ['activeAdminNav' => 'merchant'])

    <div class="admin-main admin-management-main admin-products-main">
        <section class="admin-page-head">
            <div>
                <h1 class="admin-page-title">Google Merchant</h1>
                <p class="admin-page-copy">Review product readiness for the Google Merchant Center feed.</p>
            </div>
            @if($merchantFieldsReady)
                <a class="admin-primary-pill" href="{{ $feedUrl }}" target="_blank" rel="noopener noreferrer">View Feed</a>
            @endif
        </section>

        <section class="panel admin-list-panel">
            <div class="admin-list-panel-head">
                <h2>Feed Summary</h2>
            </div>
            <p>
                <strong>{{ $eligible }}</strong> of <strong>{{ $total }}</strong> products are eligible for the Merchant feed
                ({{ $excluded }} excluded). Feed URL: <code>{{ $feedUrl }}</code>
            </p>
            <p class="admin-product-optional-copy">
                Products are excluded when they are not active, are missing a price or image, are marked "No" for
                include-in-feed, or are missing a title. Out-of-stock products may still appear in the feed.
            </p>
        </section>

        <section class="panel admin-list-panel">
            <div class="admin-list-panel-head">
                <h2>Products with Merchant issues</h2>
            </div>

            <div class="table-wrap">
                <table class="admin-data-table">
                    <thead>
                    <tr>
                        <th>Product</th>
                        <th>Price (KES)</th>
                        <th>Issues</th>
                        <th>Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($issues as $row)
                        <tr>
                            <td>{{ $row['product']->name }}</td>
                            <td>{{ $row['product']->price !== null ? number_format((float) $row['product']->price, 2) : '—' }}</td>
                            <td>{{ implode(', ', $row['issues']) }}</td>
                            <td>
                                <div class="admin-action-stack">
                                    <a class="admin-outline-action tone-primary" href="{{ route('admin.products.edit', $row['product']) }}">Fix</a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="admin-empty-cell">No products have Merchant issues.</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</div>
@endsection
