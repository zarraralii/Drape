@extends('layouts.checkout')

@section('title', 'Checkout')

@section('content')

<div class="checkout">
    <div class="checkout__main">
        <div class="checkout__inner">
            {{-- Mobile: collapsible order summary on top, like Shopify --}}
            <details class="checkout__mobile-summary">
                <summary>
                    <span><x-icon name="bag" /> Show order summary <x-icon name="chevron-down" /></span>
                    <strong>{{ money($total) }}</strong>
                </summary>
                @include('checkout.partials.summary')
            </details>

            @if ($errors->has('order') || $errors->has('coupon_code'))
                <div class="alert alert--error" role="alert">{{ $errors->first('order') ?: $errors->first('coupon_code') }}</div>
            @endif

            <form method="POST" action="{{ route('checkout.store') }}" class="checkout-form" id="checkout-form" novalidate>
                @csrf

                <section class="checkout-section">
                    <div class="checkout-section__head">
                        <h2>Contact</h2>
                        @guest
                            <a href="{{ route('login') }}" class="small">Log in</a>
                        @endguest
                    </div>
                    @auth
                        <p class="muted small">Signed in as {{ $authUser->email ?: $authUser->phone }} — this order will be saved to your account.</p>
                    @endauth
                    <div class="field @error('phone') has-error @enderror">
                        <label for="phone">Mobile number</label>
                        <input type="tel" id="phone" name="phone" value="{{ old('phone', $authUser?->phone) }}" autocomplete="tel" required placeholder="03XX XXXXXXX">
                        @error('phone')<p class="field__error">{{ $message }}</p>@enderror
                    </div>
                    <div class="field @error('email') has-error @enderror">
                        <label for="email">Email <span class="muted">(optional, for your receipt)</span></label>
                        <input type="email" id="email" name="email" value="{{ old('email', $authUser?->email) }}" autocomplete="email">
                        @error('email')<p class="field__error">{{ $message }}</p>@enderror
                    </div>
                </section>

                <section class="checkout-section">
                    <div class="checkout-section__head"><h2>Delivery</h2></div>
                    <div class="field">
                        <label for="country">Country</label>
                        <div class="select">
                            <select id="country" name="country" autocomplete="country-name">
                                <option value="Pakistan" @selected(old('country', 'Pakistan') === 'Pakistan')>Pakistan</option>
                            </select>
                            <x-icon name="chevron-down" />
                        </div>
                    </div>
                    <div class="field @error('name') has-error @enderror">
                        <label for="name">Full name</label>
                        <input type="text" id="name" name="name" value="{{ old('name', $authUser?->name) }}" autocomplete="name" required>
                        @error('name')<p class="field__error">{{ $message }}</p>@enderror
                    </div>
                    <div class="field @error('address_line_1') has-error @enderror">
                        <label for="address_line_1">Address</label>
                        <input type="text" id="address_line_1" name="address_line_1" value="{{ old('address_line_1') }}" autocomplete="address-line1" required placeholder="House, street, area">
                        @error('address_line_1')<p class="field__error">{{ $message }}</p>@enderror
                    </div>
                    <div class="field">
                        <label for="address_line_2">Apartment, landmark <span class="muted">(optional)</span></label>
                        <input type="text" id="address_line_2" name="address_line_2" value="{{ old('address_line_2') }}" autocomplete="address-line2">
                    </div>
                    <div class="form-grid form-grid--3">
                        <div class="field @error('city') has-error @enderror">
                            <label for="city">City</label>
                            <input type="text" id="city" name="city" value="{{ old('city') }}" autocomplete="address-level2" required>
                            @error('city')<p class="field__error">{{ $message }}</p>@enderror
                        </div>
                        <div class="field">
                            <label for="state">Province</label>
                            <div class="select">
                                <select id="state" name="state" autocomplete="address-level1">
                                    @foreach (['', 'Punjab', 'Sindh', 'Khyber Pakhtunkhwa', 'Balochistan', 'Islamabad Capital Territory', 'Gilgit-Baltistan', 'Azad Kashmir'] as $province)
                                        <option value="{{ $province }}" @selected(old('state') === $province)>{{ $province ?: 'Select' }}</option>
                                    @endforeach
                                </select>
                                <x-icon name="chevron-down" />
                            </div>
                        </div>
                        <div class="field">
                            <label for="postal_code">Postal code <span class="muted">(optional)</span></label>
                            <input type="text" id="postal_code" name="postal_code" value="{{ old('postal_code') }}" autocomplete="postal-code" inputmode="numeric">
                        </div>
                    </div>
                </section>

                <section class="checkout-section">
                    <div class="checkout-section__head"><h2>Shipping method</h2></div>
                    <div class="choice-box is-selected">
                        <span class="choice-box__radio" aria-hidden="true"></span>
                        <span class="choice-box__label">Standard delivery <small class="muted">{{ $deliveryEstimate }}</small></span>
                        <strong>{{ $delivery > 0 ? money($delivery) : 'Free' }}</strong>
                    </div>
                </section>

                <section class="checkout-section">
                    <div class="checkout-section__head"><h2>Payment</h2></div>
                    <div class="choice-box is-selected">
                        <span class="choice-box__radio" aria-hidden="true"></span>
                        <span class="choice-box__label">Cash on delivery <small class="muted">Pay in cash when your order arrives.</small></span>
                        <x-icon name="cash" />
                    </div>
                </section>

                <details class="checkout-section checkout-extra" @if (old('referral_code') || old('remarks') || $errors->has('referral_code')) open @endif>
                    <summary>Add a note or referral code <x-icon name="chevron-down" /></summary>
                    <div class="field @error('referral_code') has-error @enderror">
                        <label for="referral_code">Referral code</label>
                        <input type="text" id="referral_code" name="referral_code" value="{{ old('referral_code') }}" autocomplete="off" data-agent-code>
                        <p class="field__hint" data-agent-message>@error('referral_code'){{ $message }}@enderror</p>
                    </div>
                    <div class="field">
                        <label for="remarks">Order note</label>
                        <textarea id="remarks" name="remarks" rows="3" placeholder="Delivery instructions, gift note…">{{ old('remarks') }}</textarea>
                    </div>
                </details>

                <button type="submit" class="btn btn--primary btn--block btn--lg" data-submit-once>Complete order · {{ money($total) }}</button>
                <p class="checkout-form__legal muted small">By placing your order you agree to our <a href="{{ route('terms') }}">terms</a> and <a href="{{ route('returns') }}">refund policy</a>.</p>
            </form>
        </div>
    </div>

    <aside class="checkout__aside">
        <div class="checkout__inner">
            @include('checkout.partials.summary')
        </div>
    </aside>
</div>

@endsection
