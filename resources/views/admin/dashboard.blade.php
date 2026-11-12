@extends('admin.layout')

@section('content')
@php
    $contentNavigation = [
        ['label' => 'Categories', 'href' => route('admin.categories.index'), 'badge' => 'CT'],
        ['label' => 'Sub Categories', 'href' => route('admin.subcategories.index'), 'badge' => 'SC'],
        ['label' => 'Products', 'href' => route('admin.products.index'), 'badge' => 'PR'],
        ['label' => 'Pages', 'href' => route('admin.pages.index'), 'badge' => 'PG'],
        ['label' => 'Orders', 'href' => route('admin.orders.index'), 'badge' => 'OR'],
        ['label' => 'Invoices', 'href' => route('admin.invoices.index'), 'badge' => 'IV'],
    ];

    $overviewCards = [
        [
            'tone' => 'orange',
            'badge' => 'OR',
            'title' => 'Orders',
            'value' => number_format($stats['total_orders']),
            'meta' => number_format($stats['pending_orders']) . ' pending review',
            'href' => route('admin.orders.index'),
            'link' => 'View orders',
        ],
        [
            'tone' => 'warm',
            'badge' => 'PR',
            'title' => 'Products',
            'value' => number_format($stats['total_products']),
            'meta' => number_format($stats['active_products']) . ' active listings',
            'href' => route('admin.products.index'),
            'link' => 'View products',
        ],
        [
            'tone' => 'dark',
            'badge' => 'US',
            'title' => 'Users',
            'value' => number_format($stats['total_users']),
            'meta' => 'Registered accounts',
            'href' => '#users-insights',
            'link' => 'View users',
        ],
        [
            'tone' => 'red',
            'badge' => 'AP',
            'title' => 'Approvals',
            'value' => number_format($stats['pending_vendors']),
            'meta' => 'Pending vendor approvals',
            'href' => route('admin.vendors.pending'),
            'link' => 'Review approvals',
        ],
    ];

    $summaryCards = [
        [
            'tone' => 'dark',
            'title' => 'Total Revenue',
            'value' => 'KSh ' . number_format($stats['gross_revenue'], 2),
            'note' => 'Paid orders',
        ],
        [
            'tone' => 'orange',
            'title' => 'Recent Orders',
            'value' => number_format($stats['recent_orders_7_days']),
            'note' => 'Last 7 days',
        ],
        [
            'tone' => 'red',
            'title' => 'New Users',
            'value' => number_format($stats['new_users_30_days']),
            'note' => 'Last 30 days',
        ],
        [
            'tone' => 'warm',
            'title' => 'Active Users',
            'value' => number_format($stats['active_users_24_hours']),
            'note' => 'Last 24 hours',
        ],
    ];
@endphp

<div class="admin-shell">
    @include('admin.partials.sidebar', ['activeAdminNav' => 'dashboard'])

    <div class="admin-main admin-management-main">
        <section class="admin-hero" id="admin-overview">
            <div>
                <span class="admin-pill">Admin Overview</span>
                <h1>Dashboard</h1>
                <p class="admin-hero-copy muted">View and manage all customer orders, users, products, and vendor approvals.</p>
            </div>
            <div class="dashboard-actions">
                <a class="button-link" href="{{ route('admin.products.create') }}">+ New Product</a>
                <a class="button-link secondary" href="#users-insights">Manage Users</a>
                <a class="button-link secondary" href="{{ route('admin.products.index') }}">Manage Products</a>
                <a class="button-link secondary" href="{{ route('admin.testimonials.index') }}">Manage Testimonials</a>
            </div>
        </section>

        <div class="admin-breadcrumb">
            <a href="{{ route('home') }}">Home</a>
            <span>/</span>
            <span>Dashboard</span>
        </div>

        <section class="admin-card-grid admin-card-grid--four">
            @foreach($overviewCards as $card)
                <article class="admin-overview-card tone-{{ $card['tone'] }}">
                    <span class="admin-overview-icon">{{ $card['badge'] }}</span>
                    <p class="admin-overview-title">{{ $card['title'] }}</p>
                    <h3>{{ $card['value'] }}</h3>
                    <small>{{ $card['meta'] }}</small>
                    <a class="admin-overview-link" href="{{ $card['href'] }}">{{ $card['link'] }}</a>
                </article>
            @endforeach
        </section>

        <section class="admin-summary-strip admin-summary-strip--four" id="users-insights">
            @foreach($summaryCards as $card)
                <article class="admin-summary-card tone-{{ $card['tone'] }}">
                    <p class="stat-label">{{ $card['title'] }}</p>
                    <h3>{{ $card['value'] }}</h3>
                    <small>{{ $card['note'] }}</small>
                </article>
            @endforeach
        </section>

        <section class="admin-ops-grid">
            <div class="admin-stack">
                <section class="panel admin-section-card" id="recent-orders">
                    <div class="admin-section-head">
                        <div>
                            <p class="admin-section-kicker">Orders Desk</p>
                            <h2>Recent Orders</h2>
                        </div>
                        <span class="admin-section-meta">Latest updates</span>
                    </div>
                    @if($recentOrders->isEmpty())
                        <p class="empty">No orders yet.</p>
                    @else
                        <div class="table-wrap">
                            <table>
                                <thead>
                                <tr>
                                    <th>Order No</th>
                                    <th>Customer</th>
                                    <th>Status</th>
                                    <th>Total</th>
                                    <th>Date</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($recentOrders as $order)
                                    <tr>
                                        <td>{{ $order->order_number }}</td>
                                        <td>{{ $order->user->name ?? 'Unknown' }}</td>
                                        <td>{{ ucfirst($order->status) }}</td>
                                        <td>KSh {{ number_format((float) $order->total_amount, 2) }}</td>
                                        <td>{{ $order->created_at }}</td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </section>

                <section class="panel admin-section-card" id="pending-vendors">
                    <div class="admin-section-head">
                        <div>
                            <p class="admin-section-kicker">Approval Queue</p>
                            <h2>Pending Vendors</h2>
                        </div>
                        <span class="admin-section-meta">Requires review</span>
                    </div>
                    @if($pendingVendors->isEmpty())
                        <p class="empty">No pending vendors.</p>
                    @else
                        <div class="table-wrap">
                            <table>
                                <thead>
                                <tr>
                                    <th>Shop</th>
                                    <th>Owner</th>
                                    <th>Email</th>
                                    <th></th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($pendingVendors as $vendor)
                                    <tr>
                                        <td>{{ $vendor->shop_name }}</td>
                                        <td>{{ $vendor->user->name }}</td>
                                        <td>{{ $vendor->user->email }}</td>
                                        <td>
                                            <form method="post" action="{{ route('admin.vendors.approve', $vendor) }}">
                                                @csrf
                                                <button type="submit">Approve</button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </section>
            </div>

            <div class="admin-stack">
                <section class="panel admin-section-card" id="admin-catalog">
                    <div class="admin-section-head">
                        <div>
                            <p class="admin-section-kicker">Catalog</p>
                            <h2>Admin Products</h2>
                        </div>
                        <span class="admin-section-meta">Latest listings</span>
                    </div>
                    @if($adminProducts->isEmpty())
                        <p class="empty">No admin-posted products yet.</p>
                    @else
                        <div class="table-wrap">
                            <table>
                                <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Category</th>
                                    <th>Price</th>
                                    <th>Stock</th>
                                    <th>Status</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($adminProducts as $product)
                                    <tr>
                                        <td>{{ $product->name }}</td>
                                        <td>{{ $product->category->name ?? 'General' }}</td>
                                        <td>KSh {{ number_format((float) $product->price, 2) }}</td>
                                        <td>{{ $product->stock }}</td>
                                        <td>{{ ucfirst($product->status) }}</td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </section>
            </div>
        </section>
    </div>
</div>
@endsection
