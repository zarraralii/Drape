<?php

namespace App\Support;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Counts *failed* sign-ins (the per-minute route throttle counts every
 * attempt) and locks things down when they pile up:
 *
 *  - one account: N wrong passwords → that account can't sign in for a
 *    while, from anywhere (stops slow, spread-out password guessing);
 *  - one IP: M wrong passwords across any accounts → the IP is banned
 *    from the whole site through the Firewall.
 *
 * The lock is checked *before* the password hash is verified, so a
 * locked-out script can't burn CPU on bcrypt either.
 */
class LoginThrottle
{
    public function __construct(private Firewall $firewall)
    {
    }

    /**
     * @throws ValidationException when the account is currently locked
     */
    public function ensureNotLocked(Request $request, string $guard, string $identity, string $field): void
    {
        $key = $this->accountKey($guard, $identity);
        $config = config('security.login');

        if ($this->firewall->limiter()->tooManyAttempts($key, $config['account_max_failures'])) {
            $minutes = (int) ceil($this->firewall->limiter()->availableIn($key) / 60);

            throw ValidationException::withMessages([
                $field => "Too many failed sign-in attempts for this account. Please try again in $minutes " . Str::plural('minute', $minutes) . '.',
            ]);
        }
    }

    public function failed(Request $request, string $guard, string $identity): void
    {
        $config = config('security.login');
        $limiter = $this->firewall->limiter();
        $ip = (string) $request->ip();

        $accountFailures = $limiter->hit($this->accountKey($guard, $identity), $config['account_lock_minutes'] * 60);

        if ($accountFailures === $config['account_max_failures']) {
            Log::warning('Login: account locked after repeated failures', ['guard' => $guard, 'identity' => $identity, 'ip' => $ip]);
        }

        $ipFailures = $limiter->hit("login-fail:ip:$ip", $config['ip_window_minutes'] * 60);

        if ($ipFailures >= $config['ip_max_failures']) {
            $this->firewall->ban($ip, $config['ip_ban_minutes'], "$ipFailures failed $guard sign-ins in {$config['ip_window_minutes']} min");
        }
    }

    public function succeeded(string $guard, string $identity): void
    {
        $this->firewall->limiter()->clear($this->accountKey($guard, $identity));
    }

    private function accountKey(string $guard, string $identity): string
    {
        return 'login-fail:account:' . $guard . ':' . sha1(Str::lower(trim($identity)));
    }
}
