@extends('layouts.shop')

@section('seo_title', 'Log in')
@section('seo_noindex', '1')

@section('content')

<section class="auth">
    <div class="auth__card">
        <h1 class="display">Log in</h1>
        <p class="muted">Track orders, save your details and check out faster.</p>

        <form method="POST" action="{{ route('login.store') }}" class="stack">
            @csrf
            @error('identity')<div class="alert alert--error">{{ $message }}</div>@enderror
            <div class="field">
                <label for="identity">Email or mobile number</label>
                <input type="text" id="identity" name="identity" value="{{ old('identity') }}" required autofocus autocomplete="username">
            </div>
            <div class="field">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required autocomplete="current-password">
            </div>
            <label class="check"><input type="checkbox" name="remember" value="1"> <span>Keep me logged in</span></label>
            <button type="submit" class="btn btn--primary btn--block">Log in</button>
        </form>

        @include('auth.partials.google-button')

        <p class="auth__switch">New to {{ \App\Support\Store::brand()['name'] }}? <a href="{{ route('register') }}">Create an account</a></p>
        <p class="auth__switch"><a href="{{ route('order.track') }}">Track an order without logging in</a></p>
    </div>
</section>

@endsection
