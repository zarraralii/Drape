<?php

namespace App\Support;

use Illuminate\Cache\RateLimiter;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Per-IP request counting and site-wide bans. All state is kept in the
 * cache store from config/security.php (file by default), never MySQL.
 */
class Firewall
{
    private Repository $store;

    private RateLimiter $limiter;

    public function __construct()
    {
        $this->store = Cache::store(config('security.cache_store'));
        $this->limiter = new RateLimiter($this->store);
    }

    public function limiter(): RateLimiter
    {
        return $this->limiter;
    }

    public function isAllowlisted(string $ip): bool
    {
        return in_array($ip, config('security.allowlist', []), true);
    }

    /** Seconds left on the IP's ban, or 0 if it isn't banned. */
    public function banRemaining(string $ip): int
    {
        $until = (int) $this->store->get($this->banKey($ip), 0);

        return max(0, $until - time());
    }

    public function ban(string $ip, int $minutes, string $reason): void
    {
        if ($this->isAllowlisted($ip)) {
            return;
        }

        $until = time() + $minutes * 60;
        $this->store->put($this->banKey($ip), $until, $minutes * 60);

        Log::warning('Firewall: IP banned', ['ip' => $ip, 'minutes' => $minutes, 'reason' => $reason]);
    }

    public function unban(string $ip): void
    {
        $this->store->forget($this->banKey($ip));
        $this->limiter->clear("fw:strikes:$ip");
        $this->limiter->clear("fw:req:$ip");
        $this->limiter->clear("fw:write:$ip");
    }

    /**
     * Count one over-the-limit episode. Only the first refused request of
     * each window counts, so a single burst is one strike, not hundreds.
     * Returns true when this strike got the IP banned.
     */
    public function strike(string $ip, string $bucket, int $windowSeconds): bool
    {
        if (!$this->store->add("fw:struck:$bucket:$ip", 1, max(1, $windowSeconds))) {
            return false;
        }

        $config = config('security.firewall');
        $strikes = $this->limiter->hit("fw:strikes:$ip", $config['strike_window_minutes'] * 60);

        if ($strikes >= $config['strikes_before_ban']) {
            $this->ban($ip, $config['ban_minutes'], "hit the $bucket limit $strikes times in {$config['strike_window_minutes']} min");

            return true;
        }

        return false;
    }

    private function banKey(string $ip): string
    {
        return "fw:ban:$ip";
    }
}
