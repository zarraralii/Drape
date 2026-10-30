@extends('layouts.shop')

@section('seo_title', 'Contact us')

@section('content')

<x-page-header title="Contact us" :crumbs="['Contact']" :image="$siteInfo?->contact_banner">
    Questions about sizing, an order or an exchange? We usually reply within a day.
</x-page-header>

<section class="section section--flush-top">
    <div class="container contact">
        <div class="contact__info">
            @if ($siteInfo?->phone)
                <div class="contact__item">
                    <x-icon name="phone" />
                    <div><h3>Call or WhatsApp</h3><a href="tel:{{ preg_replace('/[^0-9+]/', '', $siteInfo->phone) }}">{{ $siteInfo->phone }}</a></div>
                </div>
            @endif
            @if ($siteInfo?->email)
                <div class="contact__item">
                    <x-icon name="mail" />
                    <div><h3>Email</h3><a href="mailto:{{ $siteInfo->email }}">{{ $siteInfo->email }}</a></div>
                </div>
            @endif
            @if ($siteInfo?->location)
                <div class="contact__item">
                    <x-icon name="pin" />
                    <div><h3>Studio</h3><p>{{ $siteInfo->location }}</p></div>
                </div>
            @endif
            @if ($siteInfo?->service_timing)
                <div class="contact__item">
                    <x-icon name="clock" />
                    <div><h3>Hours</h3><p>{!! nl2br(e($siteInfo->service_timing)) !!}</p></div>
                </div>
            @endif
            <div class="contact__item">
                <x-icon name="truck" />
                <div><h3>Order status</h3><a href="{{ route('order.track') }}">Track your order</a></div>
            </div>
        </div>

        <form method="POST" action="{{ route('contact.store') }}" class="card stack">
            @csrf
            @if ($errors->any())
                <div class="alert alert--error"><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
            @endif
            <div class="form-grid">
                <div class="field"><label for="c-name">Name</label><input type="text" id="c-name" name="name" value="{{ old('name', auth()->user()->name ?? '') }}" required autocomplete="name"></div>
                <div class="field"><label for="c-email">Email</label><input type="email" id="c-email" name="email" value="{{ old('email', auth()->user()->email ?? '') }}" required autocomplete="email"></div>
            </div>
            <div class="form-grid">
                <div class="field"><label for="c-phone">Phone <span class="muted">(optional)</span></label><input type="tel" id="c-phone" name="phone" value="{{ old('phone', auth()->user()->phone ?? '') }}" autocomplete="tel"></div>
                <div class="field"><label for="c-subject">Subject</label><input type="text" id="c-subject" name="subject" value="{{ old('subject') }}" required></div>
            </div>
            <div class="field"><label for="c-message">Message</label><textarea id="c-message" name="message" rows="5" required>{{ old('message') }}</textarea></div>
            <button type="submit" class="btn btn--primary">Send message</button>
        </form>
    </div>
</section>

@endsection
