<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Artisan;

/**
 * One-click "after upload" step for shared hosting without SSH: runs any
 * pending migrations and clears every Laravel cache (config, routes,
 * compiled Blade views, app cache, events) plus PHP OPcache, so freshly
 * uploaded files take effect immediately.
 *
 * Compiled views matter most here: Laravel only recompiles a view when the
 * .blade.php file's mtime is newer than the compiled copy, and zip/FTP
 * uploads often keep older timestamps — so stale pages were being served.
 */
class MaintenanceController extends Controller
{
    public function clearCache()
    {
        try {
            Artisan::call('migrate', ['--force' => true]);
        } catch (\Throwable $e) {
            return back()->with('fail', 'Migration failed: ' . $e->getMessage());
        }

        Artisan::call('optimize:clear');

        // Rebuild the config cache from this server's own .env (reading .env
        // on every request isn't safe under concurrent load). Re-click this
        // button after editing .env, or the change won't take effect.
        Artisan::call('config:cache');

        if (function_exists('opcache_reset')) {
            @opcache_reset();
        }

        return back()->with('success', 'Cache cleared and database updated. Hard-refresh the site (Ctrl+F5) to see the changes.');
    }
}
