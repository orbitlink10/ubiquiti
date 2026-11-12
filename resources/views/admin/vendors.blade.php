@extends('layouts.app')

@section('content')
<section class="panel">
    <div class="dashboard-head">
        <h1>Pending Vendor Approvals</h1>
        <a class="button-link" href="{{ route('admin.dashboard') }}">Back to Dashboard</a>
    </div>
    @if($vendors->isEmpty())
        <p class="empty">No pending applications.</p>
    @else
        <div class="table-wrap">
            <table>
                <thead>
                <tr>
                    <th>Shop</th>
                    <th>Owner</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Address</th>
                    <th>Action</th>
                </tr>
                </thead>
                <tbody>
                @foreach($vendors as $vendor)
                    <tr>
                        <td>{{ $vendor->shop_name }}</td>
                        <td>{{ $vendor->user->name }}</td>
                        <td>{{ $vendor->user->email }}</td>
                        <td>{{ $vendor->phone }}</td>
                        <td>{{ $vendor->address }}</td>
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
@endsection
