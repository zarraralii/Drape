<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AbuseProtectionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // In-memory counters per test, and small limits so tests stay fast.
        config([
            'security.cache_store' => 'array',
            'cache.limiter' => 'array',
            'security.allowlist' => [],
            'security.firewall.requests_per_minute' => 10,
            'security.firewall.writes_per_minute' => 30,
            'security.firewall.strikes_before_ban' => 3,
        ]);
    }

    public function test_looping_page_requests_are_refused_then_banned(): void
    {
        $ip = ['REMOTE_ADDR' => '203.0.113.5'];

        for ($i = 1; $i <= 10; $i++) {
            $this->withServerVariables($ip)->get('/pages/faq')->assertOk();
        }

        // 11th in the same minute: refused, with a Retry-After.
        $this->withServerVariables($ip)->get('/pages/faq')
            ->assertStatus(429)
            ->assertHeader('Retry-After');

        // Another visitor is unaffected.
        $this->withServerVariables(['REMOTE_ADDR' => '203.0.113.99'])->get('/pages/faq')->assertOk();

        // Keep looping across windows → strikes → site-wide ban.
        $this->travel(61)->seconds();
        for ($i = 0; $i < 11; $i++) {
            $this->withServerVariables($ip)->get('/pages/faq');
        }
        $this->travel(61)->seconds();
        for ($i = 0; $i < 11; $i++) {
            $this->withServerVariables($ip)->get('/pages/faq');
        }

        // A fresh minute would normally reset the counter — but it's banned now.
        $this->travel(61)->seconds();
        $this->withServerVariables($ip)->get('/pages/faq')->assertStatus(429);
        $this->withServerVariables($ip)->getJson('/faq')->assertStatus(429)->assertJson(['success' => false]);
    }

    public function test_account_locks_after_failed_logins_even_from_different_ips(): void
    {
        config(['security.firewall.requests_per_minute' => 1000]);
        User::forceCreate(['name' => 'Ayesha', 'email' => 'ayesha@example.test', 'phone' => '03001234567', 'password' => 'correct-password'] /* User hashes it itself */);

        // Distributed guessing: each attempt from a new IP, so the per-IP
        // route throttle never fires — only the per-account lock can stop it.
        for ($i = 1; $i <= 5; $i++) {
            $this->withServerVariables(['REMOTE_ADDR' => "198.51.100.$i"])
                ->post(route('login.store'), ['identity' => 'ayesha@example.test', 'password' => 'guess-' . $i])
                ->assertSessionHas('fail');
        }

        // Now locked: even the right password is refused, from a new IP.
        $this->withServerVariables(['REMOTE_ADDR' => '198.51.100.50'])
            ->post(route('login.store'), ['identity' => 'ayesha@example.test', 'password' => 'correct-password'])
            ->assertSessionHasErrors('identity');
        $this->assertGuest();

        // Lock expires.
        $this->travel(16)->minutes();
        $this->withServerVariables(['REMOTE_ADDR' => '198.51.100.51'])
            ->post(route('login.store'), ['identity' => 'ayesha@example.test', 'password' => 'correct-password']);
        $this->assertAuthenticated();
    }

    public function test_ip_trying_many_accounts_gets_banned_site_wide(): void
    {
        config(['security.firewall.requests_per_minute' => 1000, 'security.login.ip_max_failures' => 6]);
        $ip = ['REMOTE_ADDR' => '192.0.2.77'];

        for ($i = 1; $i <= 6; $i++) {
            $this->withServerVariables($ip)
                ->post(route('login.store'), ['identity' => "victim$i@example.test", 'password' => 'x']);
        }

        $this->withServerVariables($ip)->get('/')->assertStatus(429);
        $this->withServerVariables(['REMOTE_ADDR' => '192.0.2.78'])->get('/pages/faq')->assertOk();
    }

    public function test_admin_login_locks_after_failures(): void
    {
        config(['security.firewall.requests_per_minute' => 1000]);
        Admin::forceCreate(['name' => 'Admin', 'email' => 'admin@example.test', 'password' => Hash::make('admin-pass')]);

        for ($i = 1; $i <= 5; $i++) {
            $this->withServerVariables(['REMOTE_ADDR' => "198.51.100.$i"])
                ->post(route('auth.login'), ['email' => 'admin@example.test', 'password' => 'wrong'])
                ->assertSessionHasErrors(['email' => 'Wrong Credentials']);
        }

        $this->withServerVariables(['REMOTE_ADDR' => '198.51.100.60'])
            ->post(route('auth.login'), ['email' => 'admin@example.test', 'password' => 'admin-pass'])
            ->assertSessionHasErrors('email');
        $this->assertGuest('admin');
    }

    public function test_allowlisted_ip_is_never_limited(): void
    {
        config(['security.allowlist' => ['203.0.113.10']]);

        for ($i = 0; $i < 15; $i++) {
            $this->withServerVariables(['REMOTE_ADDR' => '203.0.113.10'])->get('/pages/faq')->assertOk();
        }
    }
}
