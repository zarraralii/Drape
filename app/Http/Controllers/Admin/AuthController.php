<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Agent;
use App\Models\AgentSale;
use App\Models\Coupon;
use App\Models\Product;
use App\Support\LoginThrottle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;

class AuthController extends Controller
{
    public function login(Request $request, LoginThrottle $throttle)
    {
        if ($request->isMethod('post')) {
            $request->validate([
                'email' => 'required|email|max:150',
                'password' => 'required|max:255',
            ], [
                'email.required' => 'The Email is required.',
                'email.email' => 'Please Enter a Valid Email Address.',
                'password.required' => 'Password is required.',
            ]);

            $throttle->ensureNotLocked($request, 'admin', $request->email, 'email');

            if (Auth::guard('admin')->attempt([
                'email' => $request->email,
                'password' => $request->password,
                'is_active' => 1,
            ], $request->boolean('remember'))) {
                $throttle->succeeded('admin', $request->email);
                $request->session()->regenerate();
                return redirect()->route('admin.dashboard')->with('success', 'Login Successfully');
            }

            $throttle->failed($request, 'admin', $request->email);

            // The login page shows validation errors (its `fail` flash
            // popup is commented out), so report it the same way.
            return redirect()->back()->withInput($request->only('email'))->withErrors(['email' => 'Wrong Credentials']);
        }

        if (Auth::guard('admin')->check()) {
            return redirect()->route('admin.dashboard');
        }

        return view('admin.auth.login');
    }

    public function logout(Request $request)
    {
        Auth::guard('admin')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::route('auth.login')->with('success', 'Logout Successfully!');
    }

    public function dashboard()
    {
        $data['total_products'] = Product::count();
        $data['active_products'] = Product::isActive()->count();
        $data['out_of_stock_products'] = Product::where('qty', '<=', 0)->count();
        $data['total_coupons'] = Coupon::count();
        $data['active_coupons'] = Coupon::where('is_active', true)->count();
        $data['total_agents'] = Agent::count();
        $data['total_commission_owed'] = (float) AgentSale::sum('commission_amount');
        $data['recent_products'] = Product::latest()->limit(5)->get();
        $data['recent_sales'] = AgentSale::with('agent')->latest('sale_date')->limit(5)->get();

        return view('admin.pages.dashboard')->with(['data' => $data]);
    }
}
