<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Creates (or resets the password of) an admin login. Replaces the old
// hard-coded default admin that shipped inside a migration.
Artisan::command('store:create-admin {email} {--name=Admin}', function (string $email) {
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $this->error('Please give a valid email address.');

        return 1;
    }

    $password = $this->secret('Password (min 10 characters)');
    if (strlen((string) $password) < 10 || $password !== $this->secret('Confirm password')) {
        $this->error('Passwords must match and be at least 10 characters.');

        return 1;
    }

    $admin = \App\Models\Admin::withTrashed()->firstOrNew(['email' => $email]);
    $admin->name = $admin->name ?: $this->option('name');
    $admin->password = $password;
    $admin->is_active = 1;
    $admin->deleted_at = null;
    $admin->save();

    $this->info(($admin->wasRecentlyCreated ? 'Created' : 'Updated') . " admin $email — log in at " . route('auth.login'));

    return 0;
})->purpose('Create an admin account, or reset an existing admin\'s password');
