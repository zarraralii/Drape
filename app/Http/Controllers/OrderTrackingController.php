<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderTrackingController extends Controller
{
    /**
     * Lookup needs both the order number and the phone number used at
     * checkout — the order page shows the delivery address, so the order
     * number alone (which appears on parcels and in messages) isn't enough.
     */
    public function index(Request $request): View
    {
        $uid = strtoupper(trim((string) $request->query('order_uid', '')));
        $phone = (string) $request->query('phone', '');
        $searched = $request->filled('order_uid');
        $order = null;

        if ($searched && $phone !== '') {
            $candidate = Order::with(['items.item', 'delivery', 'trails' => fn ($q) => $q->oldest(), 'user', 'tracking'])
                ->where('uid', $uid)
                ->first();

            if ($candidate && self::samePhone($candidate->contact_phone, $phone)) {
                $order = $candidate;
            }
        }

        return view('order.track', [
            'order' => $order,
            'searched' => $searched,
            'orderUid' => $uid,
            'phone' => $phone,
        ]);
    }

    /** Compares the last 10 digits, so "0300 1234567" matches "+92 300 1234567". */
    public static function samePhone(?string $a, ?string $b): bool
    {
        $digits = fn (?string $value) => substr(preg_replace('/\D+/', '', (string) $value), -10);

        return $digits($a) !== '' && strlen($digits($a)) >= 7 && hash_equals($digits($a), $digits($b));
    }
}
