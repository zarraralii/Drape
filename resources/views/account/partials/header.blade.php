<section class="page-header page-header--account">
    <div class="container account-head">
        <span class="avatar avatar--lg">
            @if ($user->profile_image)
                <img src="{{ $user->profile_image }}" alt="">
            @else
                {{ strtoupper(mb_substr($user->name ?? 'U', 0, 1)) }}
            @endif
        </span>
        <div>
            <p class="eyebrow">My account</p>
            <h1 class="page-header__title">Hi, {{ explode(' ', trim($user->name ?? 'there'))[0] }}</h1>
            <p class="muted">{{ collect([$user->phone, $user->email])->filter()->implode(' · ') }}</p>
        </div>
        <form method="POST" action="{{ route('logout') }}" class="account-head__logout">
            @csrf
            <button type="submit" class="link-btn"><x-icon name="logout" /> Log out</button>
        </form>
    </div>
    <div class="container">
        <nav class="tabs" aria-label="Account">
            <a href="{{ route('account.orders') }}" @class(['is-active' => $active === 'orders'])>Orders</a>
            <a href="{{ route('account.settings') }}" @class(['is-active' => $active === 'settings'])>Profile &amp; password</a>
            <a href="{{ route('wishlist.index') }}">Wishlist</a>
        </nav>
    </div>
</section>
