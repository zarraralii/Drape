<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

/**
 * "Continue with Google" for customers. Matches an existing account by
 * Google ID first, then by (Google-verified) email — so someone who
 * registered or checked out with the same email lands in their own
 * account and order history — otherwise creates a new, password-less one.
 */
class GoogleAuthController extends Controller
{
    public function redirect(): RedirectResponse
    {
        if (!$this->configured()) {
            return redirect()->route('login')->with('fail', 'Google sign-in isn\'t available right now.');
        }

        return Socialite::driver('google')->redirect();
    }

    public function callback(Request $request): RedirectResponse
    {
        if (!$this->configured()) {
            return redirect()->route('login')->with('fail', 'Google sign-in isn\'t available right now.');
        }

        try {
            $google = Socialite::driver('google')->user();
        } catch (\Throwable $e) {
            report($e);

            return redirect()->route('login')->with('fail', 'Google sign-in was cancelled or failed. Please try again.');
        }

        $email = $google->getEmail();
        $emailVerified = (bool) ($google->user['email_verified'] ?? false);

        $user = User::where('google_id', $google->getId())->first();

        // Only link by email when Google vouches for it — otherwise anyone
        // could claim an account by putting its address on a Google profile.
        if (!$user && $email && $emailVerified) {
            $user = User::where('email', $email)->first();

            // Sign-up doesn't verify email, so whoever set this account's
            // password may not own the address — Google just proved who
            // does. Drop that password and any "remember me" logins so a
            // pre-registered lookalike account can't be used to watch the
            // real owner's orders. They can set a new password in Settings.
            if ($user && !$user->google_id && $user->password) {
                $user->password = null;
                $user->setRememberToken(Str::random(60));
            }
        }

        if (!$user) {
            if (!$email || !$emailVerified) {
                return redirect()->route('login')->with('fail', 'Your Google account has no verified email address.');
            }

            $user = new User([
                'name' => $google->getName() ?: strstr($email, '@', true),
                'email' => $email,
            ]);
        }

        if ($user->is_active === false) {
            return redirect()->route('login')->with('fail', 'This account has been deactivated. Please contact us.');
        }

        $user->google_id = $google->getId();
        $user->save();

        Auth::login($user, true);
        $request->session()->regenerate();

        return redirect()->intended(route('account.orders'))->with('success', 'Welcome, ' . explode(' ', $user->name ?? 'there')[0] . '!');
    }

    private function configured(): bool
    {
        return filled(config('services.google.client_id')) && filled(config('services.google.client_secret'));
    }
}
