<?php

/*
|--------------------------------------------------------------------------
| Abuse protection
|--------------------------------------------------------------------------
|
| Used by App\Http\Middleware\Firewall (every request, per IP) and
| App\Support\LoginThrottle (failed sign-ins on the storefront + admin).
| Counters live in the `cache_store` below — deliberately NOT the database,
| so a script hammering the site can't take MySQL down just by being
| counted and refused.
|
*/

return [

    'cache_store' => env('SECURITY_CACHE_STORE', 'file'),

    // Only set TRUSTED_PROXIES when the site really sits behind a proxy or
    // CDN (e.g. Cloudflare): its IP ranges comma-separated, or "*". Then the
    // visitor's real IP is read from the forwarded header. Without it behind
    // a proxy, every visitor looks like the proxy's IP and the firewall
    // would limit/ban everyone together. Never set it when there's no proxy —
    // anyone could then fake their IP with a header.
    'trusted_proxies' => env('TRUSTED_PROXIES'),

    // IPs that are never limited or banned (comma-separated), e.g. your
    // office / home IP so you can't lock yourself out.
    'allowlist' => array_filter(array_map('trim', explode(',', (string) env('SECURITY_ALLOWLIST', '')))),

    'firewall' => [
        // Any request that reaches Laravel (pages, AJAX, form posts).
        // Images/CSS/JS are served by the web server and never count.
        'requests_per_minute' => (int) env('FIREWALL_REQUESTS_PER_MINUTE', 120),

        // POST / PUT / PATCH / DELETE — forms and AJAX actions.
        'writes_per_minute' => (int) env('FIREWALL_WRITES_PER_MINUTE', 30),

        // Going over a limit is a "strike". This many strikes inside the
        // window bans the IP from the whole site for `ban_minutes`.
        'strikes_before_ban' => (int) env('FIREWALL_STRIKES_BEFORE_BAN', 3),
        'strike_window_minutes' => 10,
        'ban_minutes' => (int) env('FIREWALL_BAN_MINUTES', 60),
    ],

    'login' => [
        // Wrong passwords for one account (from any IP) before that
        // account's sign-in is locked.
        'account_max_failures' => 5,
        'account_lock_minutes' => 15,

        // Wrong passwords from one IP (across any accounts) before that IP
        // is banned from the whole site via the firewall.
        'ip_max_failures' => 20,
        'ip_window_minutes' => 60,
        'ip_ban_minutes' => 60,
    ],
];
