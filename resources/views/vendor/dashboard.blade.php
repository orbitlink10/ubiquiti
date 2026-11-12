@extends('layouts.app')

@section('content')
<section class="panel">
    <div class="dashboard-head">
        <h1>Vendor Dashboard</h1>
        <a class="button-link" href="{{ route('vendor.products.create') }}">Add Product</a>
    </div>

    <div class="status-card">
        <p><strong>Shop:</strong> {{ $vendor->shop_name }}</p>
        <p><strong>Status:</strong> {{ $vendor->is_approved ? 'Approved' : 'Pending approval' }}</p>
        @if(!$vendor->is_approved)
            <p class="muted">Products stay in draft until admin approval.</p>
        @endif
    </div>

    <h2>Your Products</h2>
    @if($products->isEmpty())
        <p class="empty">No products yet.</p>
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
                @foreach($products as $product)
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

    <h2>Recent Orders</h2>
    @if($orders->isEmpty())
        <p class="empty">No orders yet.</p>
    @else
        <div class="table-wrap">
            <table>
                <thead>
                <tr>
                    <th>Order No</th>
                    <th>Status</th>
                    <th>Subtotal</th>
                    <th>Date</th>
                </tr>
                </thead>
                <tbody>
                @foreach($orders as $order)
                    <tr>
                        <td>{{ $order->order->order_number }}</td>
                        <td>{{ ucfirst($order->status) }}</td>
                        <td>KSh {{ number_format((float) $order->subtotal, 2) }}</td>
                        <td>{{ $order->created_at }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @endif
</section>
@endsection
