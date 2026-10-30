<?php

namespace App\Http\Controllers;

use App\Models\Agent;
use App\Models\Order;
use App\Support\Cart;
use App\Support\OrderException;
use App\Support\OrderPlacer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    private const PLACED_ORDERS_SESSION_KEY = 'placed_orders';

    public function index(): View|RedirectResponse
    {
        $summary = Cart::summary();

        if ($summary['items']->isEmpty()) {
            return redirect()->route('cart.index')->with('fail', 'Your bag is empty — add something first.');
        }

        return view('checkout.index', $summary + [
            'deliveryEstimate' => config('store.delivery.estimate'),
            'authUser' => Auth::user(),
        ]);
    }

    public function store(Request $request, OrderPlacer $placer): RedirectResponse
    {
        if (Cart::items()->isEmpty()) {
            return redirect()->route('cart.index')->with('fail', 'Your bag is empty — add something first.');
        }

        $data = $request->validate([
            'name' => 'required|string|max:150',
            'phone' => ['required', 'string', 'max:30', 'regex:/^[0-9+\-\s()]{7,30}$/'],
            'email' => 'nullable|email|max:150',
            'address_line_1' => 'required|string|max:255',
            'address_line_2' => 'nullable|string|max:255',
            'city' => 'required|string|max:100',
            'state' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'country' => 'required|string|max:100',
            'remarks' => 'nullable|string|max:1000',
            'referral_code' => 'nullable|string|max:50',
        ], [
            'phone.regex' => 'Enter a valid phone number.',
        ]);

        $agent = null;
        if (!empty($data['referral_code'])) {
            $agent = Agent::where('is_active', true)
                ->where('referral_code', strtoupper(trim($data['referral_code'])))
                ->first();

            if (!$agent) {
                return back()->withErrors(['referral_code' => 'Invalid referral code.'])->withInput();
            }
        }

        // A code that expired or ran out since the shopper saw their total
        // must stop the order — never quietly charge the undiscounted price.
        $hadCoupon = Cart::couponCode() !== null;
        $coupon = Cart::appliedCoupon();
        if ($hadCoupon && !$coupon) {
            return back()->withErrors(['coupon_code' => 'Your discount code is no longer valid, so it has been removed. Please review your total and place the order again.'])->withInput();
        }

        try {
            // A signed-in customer's order is saved to their account; their
            // profile itself is never changed by what's typed here.
            $order = $placer->place(
                Cart::orderLines(),
                ['name' => $data['name'], 'phone' => $data['phone'], 'email' => $data['email'] ?? Auth::user()?->email],
                $data,
                [
                    'user_id' => Auth::id(),
                    'coupon' => $coupon,
                    'agent' => $agent,
                    'remarks' => $data['remarks'] ?? null,
                ],
            );
        } catch (OrderException $e) {
            if ($e->field === 'coupon_code') {
                Cart::removeCoupon();
            }

            return back()->withErrors([$e->field === 'coupon_code' ? 'coupon_code' : 'order' => $e->getMessage()])->withInput();
        }

        Cart::clear();

        // Lets this browser (guest or not) see its own confirmation page.
        $request->session()->push(self::PLACED_ORDERS_SESSION_KEY, $order->uid);

        return redirect()->route('checkout.thank-you', $order->uid);
    }

    /**
     * Live check for the referral code field, called on blur — purely a UX
     * convenience. The authoritative check is the one in store() above.
     */
    public function validateAgentReference(Request $request)
    {
        $request->validate(['code' => 'nullable|string|max:50']);

        $code = strtoupper(trim((string) $request->code));

        if ($code === '') {
            return response()->json(['valid' => false, 'message' => 'Enter a referral code.'], 422);
        }

        $agent = Agent::where('is_active', true)->where('referral_code', $code)->first();

        if (!$agent) {
            return response()->json(['valid' => false, 'message' => 'Invalid referral code.'], 422);
        }

        return response()->json(['valid' => true, 'message' => 'Valid — referred by ' . $agent->name . '.']);
    }

    /**
     * Only the browser that placed the order, or the account it belongs
     * to, can see the confirmation — it shows the delivery address.
     */
    public function thankYou(Request $request, Order $order): View
    {
        $placedHere = in_array($order->uid, (array) $request->session()->get(self::PLACED_ORDERS_SESSION_KEY, []), true);
        $ownsIt = Auth::check() && $order->user_id === Auth::id();

        abort_unless($placedHere || $ownsIt, 404);

        return view('checkout.thank-you', [
            'order' => $order->load('items.item', 'delivery'),
        ]);
    }
}
