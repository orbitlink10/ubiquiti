@extends('layouts.app')

@section('title', 'Ubiquiti UniFi Kenya')

@section('content')
<section class="panel auth-card">
    <h1>{{ $pageTitle ?? 'Create Account' }}</h1>
    <form class="form-grid" action="{{ $formAction ?? route('register.submit') }}" method="post">
        @csrf
        <label>
            Full Name
            <input type="text" name="name" value="{{ old('name') }}" required>
        </label>
        <label>
            Email
            <input type="email" name="email" value="{{ old('email') }}" required>
        </label>
        <label>
            Phone
            <input type="text" name="phone" value="{{ old('phone') }}">
        </label>
        <label>
            Password
            <input type="password" name="password" minlength="6" required>
        </label>
        <label>
            Confirm Password
            <input type="password" name="password_confirmation" minlength="6" required>
        </label>
        <button type="submit">{{ $submitLabel ?? 'Register' }}</button>
    </form>
    <p class="muted">{{ $loginPrompt ?? 'Already registered?' }} <a href="{{ route('login') }}">{{ $loginLinkLabel ?? 'Login' }}</a>.</p>
    @if(!empty($secondaryLinkUrl))
        <p class="muted">{{ $secondaryPrompt ?? '' }} <a href="{{ $secondaryLinkUrl }}">{{ $secondaryLinkLabel ?? 'Open' }}</a>.</p>
    @endif
</section>
@endsection
