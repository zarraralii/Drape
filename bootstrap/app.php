<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

require_once __DIR__.'/../app/helpers.php';

$app = Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            // Emergency cache routes — deliberately outside the "web" group.
            \Illuminate\Support\Facades\Route::group([], base_path('routes/maintenance.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'admin.auth' => \App\Http\Middleware\EnsureAdminIsAuthenticated::class,
        ]);

        // Trusted proxies (TRUSTED_PROXIES) are applied in AppServiceProvider
        // from config/security.php — config isn't loaded yet at this point.

        // Global, ahead of the web group: refuses floods before a session
        // row or any other database work is created for them.
        $middleware->append(\App\Http\Middleware\Firewall::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();

// On the live host, the web-served folder is a sibling called "public_html"
// instead of Laravel's default "public" — point public_path() at whichever
// one actually exists so upload()/asset() write to and read from the real
// document root instead of a same-named-but-wrong "public" folder.
if (is_dir(dirname(__DIR__).'/public_html')) {
    $app->usePublicPath(dirname(__DIR__).'/public_html');
}

return $app;
