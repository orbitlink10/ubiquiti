@extends('layouts.app')

@section('content')
<section class="panel">
    <h1>Your Cart</h1>

    @if($cart->items->isEmpty())
        <p class="empty">Your cart is empty. <a href="{{ route('home') }}">Continue shopping</a>.</p>
    @else
        <div class="table-wrap">
            <table>
                <thead>
                <tr>
                    <th>Product</th>
                    <th>Vendor</th>
                    <th>Price</th>
                    <th>Qty</th>
                    <th>Total</th>
                    <th></th>
                </tr>
                </thead>
                <tbody>
                @foreach($cart->items as $item)
                    @php
                        $lineTotal = (float) $item->unit_price * $item->quantity;
                    @endphp
                    <tr>
                        <td>{{ $item->product->name }}</td>
                        <td>{{ $item->product->vendor->shop_name }}</td>
                        <td>KSh {{ number_format((float) $item->unit_price, 2) }}</td>
                        <td>
                            <form class="inline-form" method="post" action="{{ route('cart.update', $item) }}">
                                @csrf
                                <input type="number" name="quantity" min="0" max="{{ $item->product->stock }}" value="{{ $item->quantity }}">
                                <button type="submit">Update</button>
                            </form>
                        </td>
                        <td>KSh {{ number_format($lineTotal, 2) }}</td>
                        <td>
                            <form method="post" action="{{ route('cart.remove', $item) }}">
                                @csrf
                                <button class="danger" type="submit">Remove</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        <div class="cart-summary">
            <p><strong>Subtotal:</strong> KSh {{ number_format($subtotal, 2) }}</p>
            <a class="button-link" href="{{ route('checkout.form') }}">Proceed to Checkout</a>
        </div>
    @endif
</section>
@endsection
