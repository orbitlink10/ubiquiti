@extends('layouts.app')

@section('title', 'Ubiquiti UniFi Kenya')

@section('content')
<section class="panel auth-card">
    <h1>Login</h1>
    <form class="form-grid" action="{{ route('login.submit') }}" method="post">
        @csrf
        <label>
            Email
            <input type="email" name="email" value="{{ old('email') }}" required>
        </label>
        <label>
            Password
            <input type="password" name="password" required>
        </label>
        <button type="submit">Login</button>
    </form>
    <p class="muted">No account? <a href="{{ route('register') }}">Create one</a>.</p>
</section>
@endsection
