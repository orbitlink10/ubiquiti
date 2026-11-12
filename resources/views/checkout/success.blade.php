@extends('layouts.app')

@section('content')
<section class="panel">
    <h1>Order Confirmed</h1>
    <p>Your order number is <strong>{{ $order->order_number }}</strong>.</p>
    <p>Status: {{ ucfirst($order->status) }}</p>
    <a class="button-link" href="{{ route('home') }}">Continue Shopping</a>
</section>
@endsection
