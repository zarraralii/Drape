@extends('layouts.shop')

@section('seo_title', 'Account settings')
@section('seo_noindex', '1')

@section('content')

@include('account.partials.header', ['active' => 'settings'])

<section class="section section--flush-top">
    <div class="container account account--narrow">
        <form method="POST" action="{{ route('account.settings.update') }}" enctype="multipart/form-data" class="card stack">
            @csrf

            @if ($errors->any())
                <div class="alert alert--error"><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
            @endif

            <h2 class="card__title">Profile</h2>
            <div class="form-grid">
                <div class="field">
                    <label for="name">Full name</label>
                    <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}" required autocomplete="name">
                </div>
                <div class="field">
                    <label for="phone">Mobile number</label>
                    <input type="tel" id="phone" name="phone" value="{{ old('phone', $user->phone) }}" required autocomplete="tel">
                </div>
            </div>
            <div class="field">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}" autocomplete="email">
            </div>
            <div class="field">
                <label for="profile_image">Profile photo <span class="muted">(optional)</span></label>
                <input type="file" id="profile_image" name="profile_image" accept="image/jpeg,image/png,image/webp">
            </div>

            <h2 class="card__title">{{ $user->password ? 'Change password' : 'Set a password' }}</h2>
            @if (!$user->password)
                <p class="muted small">You signed up with Google. Set a password to also log in with your email or phone.</p>
            @endif
            @if ($user->password)
                <div class="field">
                    <label for="current_password">Current password</label>
                    <input type="password" id="current_password" name="current_password" autocomplete="current-password">
                </div>
            @endif
            <div class="form-grid">
                <div class="field">
                    <label for="password">New password</label>
                    <input type="password" id="password" name="password" autocomplete="new-password" minlength="8">
                </div>
                <div class="field">
                    <label for="password_confirmation">Confirm new password</label>
                    <input type="password" id="password_confirmation" name="password_confirmation" autocomplete="new-password">
                </div>
            </div>

            <button type="submit" class="btn btn--primary">Save changes</button>
        </form>
    </div>
</section>

@endsection
