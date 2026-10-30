<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Support\LoginThrottle;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class AuthController extends Controller
{
    // bcrypt of a random string — only ever compared against, never matched.
    private const DUMMY_HASH = '$2y$12$/i1ff7bMbwqY/BYhHG6wveg/QbmkytHv0vXxmr9cik4m4qCNrybtS';

    public function showLogin(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('account.orders');
        }

        return view('auth.login');
    }

    public function login(Request $request, LoginThrottle $throttle): RedirectResponse
    {
        $data = $request->validate([
            'identity' => 'required|string|max:150',
            'password' => 'required|string|max:255',
        ], [
            'identity.required' => 'Enter the phone number or email you registered with.',
        ]);

        $throttle->ensureNotLocked($request, 'web', $data['identity'], 'identity');

        $user = User::where('phone', $data['identity'])
            ->orWhere('email', $data['identity'])
            ->first();

        // Always run one hash check, even for unknown accounts, so response
        // time doesn't reveal which emails/phones are registered.
        $passwordOk = Hash::check($data['password'], $user?->password ?: self::DUMMY_HASH);

        if (!$user || !$user->password || !$passwordOk) {
            $throttle->failed($request, 'web', $data['identity']);

            return back()->withInput($request->only('identity'))->with('fail', 'Those details don\'t match an account.');
        }

        $throttle->succeeded('web', $data['identity']);

        if ($user->is_active === false) {
            return back()->withInput($request->only('identity'))->with('fail', 'This account has been deactivated. Please contact us.');
        }

        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        return redirect()->intended(route('account.orders'))->with('success', 'Welcome back, ' . explode(' ', $user->name ?? 'there')[0] . '!');
    }

    public function showRegister(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('account.orders');
        }

        return view('auth.register');
    }

    public function register(Request $request): RedirectResponse
    {
        // An existing phone/email is refused, never "claimed": taking over
        // an account just by knowing its phone number would hand anyone
        // its order history and addresses.
        $data = $request->validate([
            'name' => 'required|string|max:150',
            'phone' => ['required', 'string', 'max:30', 'regex:/^[0-9+\-\s()]{7,30}$/', 'unique:users,phone'],
            'email' => 'required|email|max:150|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
        ], [
            'phone.unique' => 'An account already uses this phone number — log in instead.',
            'email.unique' => 'An account already uses this email — log in instead.',
            'phone.regex' => 'Enter a valid phone number.',
        ]);

        $user = new User([
            'name' => $data['name'],
            'phone' => $data['phone'],
            'email' => $data['email'],
        ]);
        $user->password = $data['password'];
        $user->save();

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('account.orders')->with('success', 'Account created — welcome, ' . explode(' ', $user->name)[0] . '!');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')->with('success', 'You\'ve been logged out.');
    }
}
