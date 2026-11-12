@extends('layouts.app')

@section('content')
<section class="panel">
    <h1>Become a Vendor</h1>
    @if($vendor)
        <div class="status-card">
            <p><strong>Shop:</strong> {{ $vendor->shop_name }}</p>
            <p><strong>Status:</strong> {{ $vendor->is_approved ? 'Approved' : 'Pending approval' }}</p>
            <p><strong>Phone:</strong> {{ $vendor->phone }}</p>
            <p><strong>Address:</strong> {{ $vendor->address }}</p>
            <p><a href="{{ route('vendor.dashboard') }}">Open Vendor Dashboard</a></p>
        </div>
    @else
        <form class="form-grid" method="post" action="{{ route('vendor.apply.submit') }}">
            @csrf
            <label>
                Shop Name
                <input type="text" name="shop_name" value="{{ old('shop_name') }}" required>
            </label>
            <label>
                Phone
                <input type="text" name="phone" value="{{ old('phone') }}" required>
            </label>
            <label>
                Address
                <input type="text" name="address" value="{{ old('address') }}" required>
            </label>
            <label>
                Description
                <textarea name="description">{{ old('description') }}</textarea>
            </label>
            <button type="submit">Submit Application</button>
        </form>
    @endif
</section>
@endsection
