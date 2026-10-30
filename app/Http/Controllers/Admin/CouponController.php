<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use Illuminate\Http\Request;

class CouponController extends Controller
{
    public function coupons()
    {
        $coupons = Coupon::latest()->paginate(15);

        return view('admin.pages.coupons', compact('coupons'));
    }

    public function addCoupon(Request $request)
    {
        $data = [];

        if ($request->id) {
            $data['coupon'] = Coupon::findOrFail($request->id);
        }

        if ($request->isMethod('post')) {
            $request->validate([
                'code' => 'required|string|max:50|unique:coupons,code' . ($request->id ? ',' . $request->id : ''),
                'type' => 'required|in:percent,fixed',
                'value' => 'required|numeric|min:0',
                'min_order_amount' => 'nullable|numeric|min:0',
                'usage_limit' => 'nullable|integer|min:1',
                'expires_at' => 'nullable|date',
            ], [
                'code.unique' => 'This coupon code is already in use.',
            ]);

            if ($request->id) {
                $coupon = Coupon::findOrFail($request->id);
                $message = 'Coupon updated successfully';
            } else {
                $coupon = new Coupon();
                $message = 'Coupon created successfully';
            }

            $coupon->code = strtoupper($request->code);
            $coupon->type = $request->type;
            $coupon->value = $request->value;
            $coupon->min_order_amount = $request->min_order_amount;
            $coupon->usage_limit = $request->usage_limit;
            $coupon->expires_at = $request->expires_at;
            $coupon->is_active = $request->boolean('is_active', true);
            $coupon->save();

            return redirect()->route('admin.coupons')->with('success', $message);
        }

        return view('admin.pages.addCoupon', compact('data'));
    }

    public function updateStatus(Request $request)
    {
        $request->validate(['id' => 'required|exists:coupons,id']);

        $coupon = Coupon::findOrFail($request->id);
        $coupon->is_active = $request->boolean('is_active');
        $coupon->save();

        return response()->json(['success' => true, 'message' => 'Coupon status updated']);
    }

    public function deleteCoupon(Request $request)
    {
        Coupon::findOrFail($request->id)->delete();

        return response()->json(['success' => true, 'message' => 'Coupon deleted successfully']);
    }
}
