@extends('layouts.app')

@section('content')
<section class="panel">
    <h1>Checkout</h1>
    <p class="muted">Order total: <strong>KSh {{ number_format($subtotal, 2) }}</strong></p>

    <form class="form-grid" method="post" action="{{ route('checkout.submit') }}">
        @csrf
        <label>
            Full Name
            <input type="text" name="shipping_name" value="{{ old('shipping_name', $user->name) }}" required>
        </label>
        <label>
            Email
            <input type="email" name="shipping_email" value="{{ old('shipping_email', $user->email) }}" required>
        </label>
        <label>
            Phone
            <input type="text" name="shipping_phone" value="{{ old('shipping_phone', $user->phone) }}" required>
        </label>
        <label>
            Address
            <textarea name="shipping_address" required>{{ old('shipping_address') }}</textarea>
        </label>
        <button type="submit" class="checkout-submit-button">Place Order</button>
    </form>
</section>
@endsection
