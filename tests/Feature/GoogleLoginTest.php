<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;
use Mockery;
use Tests\TestCase;

class GoogleLoginTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['services.google.client_id' => 'test-id', 'services.google.client_secret' => 'test-secret']);
    }

    private function fakeGoogleUser(string $id, ?string $email, bool $verified = true, string $name = 'Sara Khan'): void
    {
        $googleUser = (new SocialiteUser())
            ->setRaw(['email_verified' => $verified])
            ->map(['id' => $id, 'name' => $name, 'email' => $email]);

        $provider = Mockery::mock(\Laravel\Socialite\Contracts\Provider::class);
        $provider->shouldReceive('user')->andReturn($googleUser);
        Socialite::shouldReceive('driver')->with('google')->andReturn($provider);
    }

    public function test_button_shows_on_login_and_register_pages(): void
    {
        $this->get(route('login'))->assertSee('Continue with Google');
        $this->get(route('register'))->assertSee('Continue with Google');
    }

    public function test_button_without_keys_returns_to_login_with_notice(): void
    {
        config(['services.google.client_id' => null]);

        $this->get(route('login'))->assertSee('Continue with Google');
        $this->get(route('auth.google'))->assertRedirect(route('login'))->assertSessionHas('fail');
    }

    public function test_new_google_user_is_created_and_logged_in(): void
    {
        $this->fakeGoogleUser('g-123', 'sara@gmail.com');

        $this->get(route('auth.google.callback'))->assertRedirect(route('account.orders'));

        $user = User::where('google_id', 'g-123')->firstOrFail();
        $this->assertSame('sara@gmail.com', $user->email);
        $this->assertNull($user->password);
        $this->assertAuthenticatedAs($user);
    }

    public function test_existing_account_with_same_verified_email_is_linked(): void
    {
        $existing = User::forceCreate(['name' => 'Sara', 'email' => 'sara@gmail.com', 'phone' => '0300', 'password' => 'secret123']);
        $this->fakeGoogleUser('g-123', 'sara@gmail.com');

        $this->get(route('auth.google.callback'));

        $this->assertAuthenticatedAs($existing);
        $this->assertSame('g-123', $existing->fresh()->google_id);
        $this->assertSame(1, User::count());
    }

    public function test_unverified_email_does_not_take_over_existing_account(): void
    {
        User::forceCreate(['name' => 'Sara', 'email' => 'sara@gmail.com', 'phone' => '0300']);
        $this->fakeGoogleUser('g-999', 'sara@gmail.com', verified: false);

        $this->get(route('auth.google.callback'))->assertRedirect(route('login'));

        $this->assertGuest();
        $this->assertNull(User::first()->google_id);
    }

    public function test_google_only_user_can_set_password_without_current_one(): void
    {
        $user = User::forceCreate(['name' => 'Sara', 'email' => 'sara@gmail.com', 'google_id' => 'g-1']);

        $this->actingAs($user)->post(route('account.settings.update'), [
            'name' => 'Sara',
            'phone' => '0300 1234567',
            'password' => 'newpass1',
            'password_confirmation' => 'newpass1',
        ])->assertSessionHas('success');

        $this->assertTrue(\Hash::check('newpass1', $user->fresh()->password));
    }
}
