@extends('admin.layout')

@section('content')
<div class="admin-shell">
    @include('admin.partials.sidebar', ['activeAdminNav' => 'orders'])

    <div class="admin-main admin-management-main">
        <section class="admin-page-head">
            <div>
                <h1 class="admin-page-title">Orders</h1>
                <p class="admin-page-copy">View and manage all customer orders.</p>
            </div>
            <div class="admin-breadcrumb admin-breadcrumb--static">
                <a href="{{ route('home') }}">Home</a>
                <span>/</span>
                <span>Orders</span>
            </div>
        </section>

        <section class="panel admin-list-panel">
            <form class="admin-filter-row" method="get" action="{{ route('admin.orders.index') }}">
                <h2>Order List</h2>
                <div class="admin-filter-controls">
                    <input type="text" name="search" value="{{ $search }}" placeholder="Search order, customer, email">
                    <label class="admin-filter-label" for="status">Filter by Status:</label>
                    <select id="status" name="status">
                        <option value="">All Statuses</option>
                        @foreach($statuses as $statusOption)
                            <option value="{{ $statusOption }}" @selected($status === $statusOption)>{{ ucfirst($statusOption) }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="admin-filter-button">Filter</button>
                    <a class="admin-reset-button" href="{{ route('admin.orders.index') }}">Reset</a>
                </div>
            </form>
        </section>

        <section class="admin-order-list">
            @forelse($orders as $order)
                <article class="panel admin-order-card">
                    <div class="admin-order-grid">
                        <div><strong>Order ID:</strong> {{ $order->order_number }}</div>
                        <div><strong>Customer:</strong> {{ $order->user?->name ?? $order->shipping_name }}</div>
                        <div><strong>Status:</strong> <span class="admin-status-pill">{{ ucfirst($order->status) }}</span></div>
                        <div><strong>Placed At:</strong> {{ $order->created_at?->format('d/m/Y H:i:s') }}</div>
                        <div><strong>Total:</strong> {{ number_format((float) $order->total_amount, 2) }} KES</div>
                        <div class="admin-order-action">
                            <button type="button" class="admin-outline-action tone-info">View</button>
                        </div>
                    </div>
                </article>
            @empty
                <section class="panel admin-list-panel">
                    <p class="empty">No orders found.</p>
                </section>
            @endforelse
        </section>
    </div>
</div>
@endsection
