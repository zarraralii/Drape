<?php

use App\Http\Controllers\SystemMaintenanceController;
use Illuminate\Support\Facades\Route;

/*
| Loaded from bootstrap/app.php WITHOUT the "web" middleware group — no
| session, cookies or CSRF — so these keep working while the database
| connection is broken. Protected by MAINTENANCE_TOKEN in .env (see
| SystemMaintenanceController). The global Firewall still rate-limits it.
*/

Route::get('/system/{token}/{action}', [SystemMaintenanceController::class, 'run'])
    ->where('action', 'clear|cache|ip')
    ->name('system.maintenance');
