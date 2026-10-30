<?php

namespace App\Http\Controllers;

use App\Traits\FileManagerTrait;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AccountController extends Controller
{
    use FileManagerTrait;

    public function orders(): View
    {
        $orders = Auth::user()
            ->orders()
            ->with(['items.item', 'delivery', 'trails' => fn ($q) => $q->oldest()])
            ->latest()
            ->get();

        return view('account.orders', [
            'user' => Auth::user(),
            'orders' => $orders,
        ]);
    }

    public function settings(): View
    {
        return view('account.settings', [
            'user' => Auth::user(),
        ]);
    }

    public function updateSettings(Request $request): RedirectResponse
    {
        $user = Auth::user();

        $data = $request->validate([
            'name' => 'required|string|max:150',
            'email' => ['nullable', 'email', 'max:150', Rule::unique('users', 'email')->ignore($user->id)],
            'phone' => ['required', 'string', 'max:30', 'regex:/^[0-9+\-\s()]{7,30}$/', Rule::unique('users', 'phone')->ignore($user->id)],
            'profile_image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
            'current_password' => 'nullable|string',
            'password' => 'nullable|string|min:8|confirmed',
        ], [
            'email.unique' => 'Another account already uses this email.',
            'phone.unique' => 'Another account already uses this phone number.',
        ]);

        if ($request->filled('password')) {
            // Accounts created via Google have no password yet — let them
            // set one without a "current" password to check against.
            if ($user->password && (!$request->filled('current_password') || !Hash::check($request->current_password, $user->password))) {
                return back()->withInput()->with('fail', 'Your current password is incorrect.');
            }

            $user->password = $data['password'];
        }

        $user->name = $data['name'];
        $user->email = $data['email'] ?? null;
        $user->phone = $data['phone'];

        if ($request->hasFile('profile_image')) {
            $before = basename((string) $user->getRawOriginal('profile_image'));
            $user->profile_image = $this->upload('upload/user_profiles/', $request->file('profile_image'), $before);
        }

        $user->save();

        return back()->with('success', 'Your settings have been updated.');
    }
}
