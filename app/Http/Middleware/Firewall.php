<?php

namespace App\Http\Middleware;

use App\Support\Firewall as FirewallService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * First line of defence against scripts looping requests. Runs globally,
 * *before* sessions, routing or any database work, so a refused request
 * costs almost nothing:
 *
 *  - banned IP              → 429 straight away
 *  - too many requests/min  → 429 + a strike
 *  - too many form/AJAX posts/min → 429 + a strike
 *  - repeated strikes       → IP banned from the whole site for a while
 */
class Firewall
{
    public function __construct(private FirewallService $firewall)
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $ip = (string) $request->ip();

        if ($ip === '' || $this->firewall->isAllowlisted($ip) || $request->is('up')) {
            return $next($request);
        }

        if ($remaining = $this->firewall->banRemaining($ip)) {
            return $this->refuse($request, $remaining, 'Your access has been temporarily blocked because of unusual activity.');
        }

        $config = config('security.firewall');
        $limiter = $this->firewall->limiter();

        $buckets = ['req' => $config['requests_per_minute']];
        if (!$request->isMethodSafe()) {
            $buckets['write'] = $config['writes_per_minute'];
        }

        foreach ($buckets as $bucket => $max) {
            $key = "fw:$bucket:$ip";

            if ($limiter->tooManyAttempts($key, $max)) {
                $retryAfter = $limiter->availableIn($key);

                if ($this->firewall->strike($ip, $bucket, $retryAfter)) {
                    $retryAfter = $this->firewall->banRemaining($ip);
                }

                return $this->refuse($request, $retryAfter, 'Too many requests. Please slow down and try again shortly.');
            }

            $limiter->hit($key, 60);
        }

        return $next($request);
    }

    /**
     * Deliberately bare: no view, session, or DB — rendering the normal
     * error page for every refused bot request would defeat the point.
     */
    private function refuse(Request $request, int $retryAfter, string $message): Response
    {
        $retryAfter = max(1, $retryAfter);
        $headers = ['Retry-After' => $retryAfter, 'Cache-Control' => 'no-store'];

        if ($request->expectsJson()) {
            return response()->json(['success' => false, 'message' => $message], 429, $headers);
        }

        $minutes = (int) ceil($retryAfter / 60);
        $wait = $retryAfter < 90 ? "$retryAfter seconds" : "$minutes minutes";
        $html = '<!doctype html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">'
            . '<title>Too many requests</title></head>'
            . '<body style="font-family:system-ui,sans-serif;max-width:480px;margin:15vh auto;padding:0 16px;text-align:center;color:#333">'
            . '<h1 style="font-size:22px">Please slow down</h1>'
            . '<p>' . e($message) . '</p><p style="color:#888">Try again in ' . $wait . '.</p></body></html>';

        return response($html, 429, $headers + ['Content-Type' => 'text/html; charset=UTF-8']);
    }
}
