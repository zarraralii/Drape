<?php

namespace App\Http\Controllers;

use Dotenv\Dotenv;
use Illuminate\Support\Facades\Artisan;
use Symfony\Component\HttpFoundation\Response;

/**
 * Emergency cache tools for hosting without SSH, reachable at
 *
 *   /system/{MAINTENANCE_TOKEN}/clear   remove cached config/routes/views
 *   /system/{MAINTENANCE_TOKEN}/cache   the above, then rebuild the config
 *                                       cache from THIS server's .env
 *
 * Built to work when the site itself is broken (e.g. a cached config with
 * the wrong database login): the route has no session/web middleware, never
 * touches the database, renders no views, and reads the token straight
 * from the .env file rather than from (possibly stale) cached config.
 * Wrong or missing token → plain 404, so the URL reveals nothing.
 */
class SystemMaintenanceController extends Controller
{
    public function run(string $token, string $action): Response
    {
        $expected = $this->tokenFromEnvFile();

        if (strlen($expected) < 32 || !hash_equals($expected, $token)) {
            abort(404);
        }

        if ($action === 'ip') {
            return $this->ipReport();
        }

        $log = [];

        // Not optimize:clear — its cache:clear step uses the default cache
        // store (database here), which fails while the DB login is wrong.
        foreach (['config:clear', 'route:clear', 'view:clear', 'event:clear', 'clear-compiled'] as $command) {
            $log[] = $this->artisan($command);
        }

        if ($action === 'cache') {
            $log[] = $this->artisan('config:cache');
        }

        // Cached config is a PHP file — without this OPcache can keep
        // serving the old one.
        if (function_exists('opcache_reset')) {
            @opcache_reset();
            $log[] = 'opcache reset';
        }

        return response(implode("\n", $log) . "\n\nDone. Open the site to check it.\n", 200, [
            'Content-Type' => 'text/plain; charset=UTF-8',
            'Cache-Control' => 'no-store',
            'X-Robots-Tag' => 'noindex',
        ]);
    }

    /**
     * What IP the firewall sees for you vs. what the proxy headers say.
     * If "Laravel sees" isn't your real IP, the host puts a proxy in front
     * and TRUSTED_PROXIES must be set — otherwise every visitor shares one
     * IP and the firewall limits/bans them all together.
     */
    private function ipReport(): Response
    {
        $request = request();
        $lines = [
            'Laravel sees (firewall uses): ' . $request->ip(),
            'REMOTE_ADDR:                  ' . $request->server('REMOTE_ADDR'),
            'X-Forwarded-For:              ' . ($request->header('X-Forwarded-For') ?? '-'),
            'X-Real-IP:                    ' . ($request->header('X-Real-IP') ?? '-'),
            '',
            $request->ip() === $request->header('X-Real-IP', $request->ip())
                ? 'OK: Laravel sees your real IP. Leave TRUSTED_PROXIES empty.'
                : 'ACTION NEEDED: Laravel sees the proxy, not you. Set TRUSTED_PROXIES=' . $request->server('REMOTE_ADDR') . ' in .env, then open /system/{token}/cache.',
        ];

        return response(implode("\n", $lines) . "\n", 200, ['Content-Type' => 'text/plain; charset=UTF-8', 'Cache-Control' => 'no-store']);
    }

    private function artisan(string $command): string
    {
        try {
            Artisan::call($command);

            return "OK    $command";
        } catch (\Throwable $e) {
            return "FAIL  $command: " . $e->getMessage();
        }
    }

    private function tokenFromEnvFile(): string
    {
        $path = base_path('.env');

        if (!is_readable($path)) {
            return '';
        }

        try {
            return (string) (Dotenv::parse((string) file_get_contents($path))['MAINTENANCE_TOKEN'] ?? '');
        } catch (\Throwable) {
            return '';
        }
    }
}
