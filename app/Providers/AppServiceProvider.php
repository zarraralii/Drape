<?php

namespace App\Providers;

use App\Models\Category;
use App\Support\Cart;
use App\Support\Store;
use App\Support\Wishlist;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Middleware\TrustProxies;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(\App\Support\Firewall::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Everything the storefront chrome (header, menus, footer, cart
        // drawer) needs. Page views read store settings via App\Support\Store.
        View::composer('layouts.shop', function ($view) {
            $hasSession = request()->hasSession();

            $view->with([
                'siteInfo' => Store::info(),
                'brand' => Store::brand(),
                'navCollections' => Category::isActive()->whereNull('parent_id')->orderBy('sort_order')->orderBy('name')->get(['id', 'name', 'slug']),
                'cartCount' => $hasSession ? Cart::count() : 0,
                'wishlistCount' => $hasSession ? Wishlist::count() : 0,
            ]);
        });

        if ($proxies = config('security.trusted_proxies')) {
            TrustProxies::at($proxies === '*' ? '*' : array_map('trim', explode(',', $proxies)));
        }

        $this->configureRateLimiting();
    }

    /**
     * Per-minute limits for everything a visitor can submit. Keyed by the
     * logged-in user where there is one (so customers sharing an IP, e.g.
     * on mobile data, don't throttle each other) and by IP otherwise.
     * Login is keyed by the account being tried *and* the IP, so one
     * attacker can't lock a real customer out from another network.
     */
    protected function configureRateLimiting(): void
    {
        $byUserOrIp = fn (Request $request) => $request->user()?->id ?: $request->ip();

        RateLimiter::for('login', fn (Request $request) => [
            Limit::perMinute(5)
                ->by(Str::lower((string) $request->input('identity')) . '|' . $request->ip())
                ->response($this->throttledResponse('Too many login attempts. Please wait a minute and try again.')),
            Limit::perMinute(20)->by($request->ip())
                ->response($this->throttledResponse('Too many login attempts. Please wait a minute and try again.')),
        ]);

        // The admin login route also serves the form on GET — only count
        // actual sign-in attempts.
        RateLimiter::for('admin-login', fn (Request $request) => $request->isMethod('get')
            ? Limit::none()
            : Limit::perMinute(5)
                ->by(Str::lower((string) $request->input('email')) . '|' . $request->ip())
                ->response($this->throttledResponse('Too many login attempts. Please wait a minute and try again.')));

        RateLimiter::for('register', fn (Request $request) => Limit::perMinute(3)->by($request->ip())
            ->response($this->throttledResponse('Too many sign-up attempts. Please wait a minute and try again.')));

        RateLimiter::for('reviews', fn (Request $request) => Limit::perMinute(3)->by($byUserOrIp($request))
            ->response($this->throttledResponse('You\'re posting reviews too quickly. Please wait a minute and try again.')));

        RateLimiter::for('contact', fn (Request $request) => Limit::perMinute(3)->by($request->ip())
            ->response($this->throttledResponse('You\'ve sent several messages already. Please wait a minute before sending another.')));

        RateLimiter::for('newsletter', fn (Request $request) => Limit::perMinute(3)->by($request->ip())
            ->response($this->throttledResponse('Too many attempts. Please wait a minute and try again.')));

        RateLimiter::for('checkout', fn (Request $request) => Limit::perMinute(5)->by($byUserOrIp($request))
            ->response($this->throttledResponse('Too many order attempts. Please wait a minute and try again.')));

        // Coupon and agent-reference checks answer "is this code valid?",
        // so keep them tight enough that codes can't be guessed by brute force.
        RateLimiter::for('codes', fn (Request $request) => Limit::perMinute(10)->by($byUserOrIp($request))
            ->response($this->throttledResponse('Too many code attempts. Please wait a minute and try again.')));

        // Order IDs are guessable-ish strings — slow down enumeration.
        // Just opening the page (no order_uid yet) isn't a lookup.
        RateLimiter::for('track-order', fn (Request $request) => !$request->filled('order_uid')
            ? Limit::none()
            : Limit::perMinute(10)->by($request->ip())
                ->response($this->throttledResponse('Too many lookups. Please wait a minute and try again.')));

        RateLimiter::for('account', fn (Request $request) => Limit::perMinute(10)->by($byUserOrIp($request))
            ->response($this->throttledResponse('Too many updates. Please wait a minute and try again.')));

        // Live search fires as the visitor types.
        RateLimiter::for('search', fn (Request $request) => Limit::perMinute(60)->by($request->ip())
            ->response($this->throttledResponse('Too many searches. Please wait a moment.')));

        // Cart and wishlist clicks are normal browsing, so this one is generous.
        RateLimiter::for('cart', fn (Request $request) => Limit::perMinute(60)->by($byUserOrIp($request))
            ->response($this->throttledResponse('You\'re going a little fast. Please wait a moment and try again.')));
    }

    /**
     * AJAX callers (cart, wishlist, coupon…) get JSON their existing
     * `.fail()` handlers already toast. Normal form posts go back to the
     * page with the message: the storefront layout shows `throttled` as a
     * toast, and the admin login page shows it through its errors popup.
     */
    protected function throttledResponse(string $message): \Closure
    {
        return function (Request $request, array $headers) use ($message) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $message], 429, $headers);
            }

            if ($request->is('track-order')) {
                return redirect()->route('order.track')->with('throttled', $message);
            }

            $redirect = back()->withInput($request->except('password', 'password_confirmation'));

            return $request->is('admin/*')
                ? $redirect->withErrors(['throttle' => $message])
                : $redirect->with('throttled', $message);
        };
    }
}
