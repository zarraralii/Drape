<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Support\Cart;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Every action works as a plain form post (cart page) and as AJAX (the
 * slide-out cart drawer), which gets the re-rendered drawer back.
 */
class CartController extends Controller
{
    public function index(): View
    {
        return view('cart.index', Cart::summary());
    }

    public function drawer(): JsonResponse
    {
        return $this->json(true, '');
    }

    public function add(Request $request): JsonResponse|RedirectResponse
    {
        $data = $request->validate([
            'product_id' => 'required|integer',
            'variation_id' => 'nullable|integer',
            'qty' => 'nullable|integer|min:1|max:99',
        ]);

        $product = Product::isActive()->with('variations')->find($data['product_id']);
        if (!$product) {
            return $this->respond($request, false, 'That product is no longer available.');
        }

        $variant = null;
        if ($product->isVariable()) {
            $variant = $product->variations->firstWhere('id', (int) ($data['variation_id'] ?? 0));
            if (!$variant) {
                return $this->respond($request, false, 'Please choose a ' . strtolower($product->options()->pluck('name')->implode(' and ')) . '.');
            }
        }

        $stock = $variant ? (int) $variant->quantity : $product->qty;
        $name = $product->name . ($variant ? ' — ' . $variant->label : '');

        if ($stock !== null && $stock <= 0) {
            return $this->respond($request, false, "$name is sold out.");
        }

        $key = Cart::key($product->id, $variant?->id);
        $before = Cart::quantityOf($key);
        $after = Cart::add($key, (int) ($data['qty'] ?? 1), $stock);

        if ($after === $before) {
            return $this->respond($request, false, "You already have all $stock of $name in your bag.");
        }

        return $this->respond($request, true, "$name added to your bag.");
    }

    public function update(Request $request): JsonResponse|RedirectResponse
    {
        $data = $request->validate([
            'key' => 'required|string|max:40',
            'qty' => 'required|integer|min:0|max:99',
        ]);

        $line = Cart::items()->firstWhere('key', $data['key']);
        if (!$line) {
            Cart::remove($data['key']);

            return $this->respond($request, false, 'That item is no longer in your bag.');
        }

        Cart::set($line['key'], (int) $data['qty'], $line['max']);

        $capped = $line['max'] !== null && $data['qty'] > $line['max'];

        return $this->respond($request, !$capped, $capped ? "Only {$line['max']} available." : 'Bag updated.');
    }

    public function remove(Request $request): JsonResponse|RedirectResponse
    {
        $data = $request->validate(['key' => 'required|string|max:40']);

        Cart::remove($data['key']);

        return $this->respond($request, true, 'Removed from your bag.');
    }

    public function applyCoupon(Request $request): JsonResponse|RedirectResponse
    {
        $request->validate(['code' => 'required|string|max:50']);

        $result = Cart::applyCoupon($request->code);

        return $this->respond($request, $result['success'], $result['message']);
    }

    public function removeCoupon(Request $request): JsonResponse|RedirectResponse
    {
        Cart::removeCoupon();

        return $this->respond($request, true, 'Discount removed.');
    }

    private function respond(Request $request, bool $success, string $message): JsonResponse|RedirectResponse
    {
        if ($request->wantsJson()) {
            return $this->json($success, $message, $success ? 200 : 422);
        }

        return back()->with($success ? 'success' : 'fail', $message);
    }

    private function json(bool $success, string $message, int $status = 200): JsonResponse
    {
        $summary = Cart::summary();

        return response()->json([
            'success' => $success,
            'message' => $message,
            'count' => $summary['count'],
            'subtotal' => money($summary['subtotal']),
            'html' => view('cart.partials.drawer', $summary)->render(),
        ], $status);
    }
}
