@extends('layouts.shop')

@section('seo_title', 'Create account')
@section('seo_noindex', '1')

@section('content')

<section class="auth">
    <div class="auth__card">
        <h1 class="display">Create account</h1>
        <p class="muted">Save your details, see your order history and leave reviews.</p>

        <form method="POST" action="{{ route('register.store') }}" class="stack">
            @csrf
            @if ($errors->any())
                <div class="alert alert--error"><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
            @endif
            <div class="field">
                <label for="name">Full name</label>
                <input type="text" id="name" name="name" value="{{ old('name') }}" required autocomplete="name">
            </div>
            <div class="field">
                <label for="phone">Mobile number</label>
                <input type="tel" id="phone" name="phone" value="{{ old('phone') }}" required autocomplete="tel" placeholder="03XX XXXXXXX">
            </div>
            <div class="field">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" required autocomplete="email">
            </div>
            <div class="form-grid">
                <div class="field">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required minlength="8" autocomplete="new-password">
                </div>
                <div class="field">
                    <label for="password_confirmation">Confirm password</label>
                    <input type="password" id="password_confirmation" name="password_confirmation" required autocomplete="new-password">
                </div>
            </div>
            <button type="submit" class="btn btn--primary btn--block">Create account</button>
        </form>

        @include('auth.partials.google-button')

        <p class="auth__switch">Already have an account? <a href="{{ route('login') }}">Log in</a></p>
    </div>
</section>

@endsection
