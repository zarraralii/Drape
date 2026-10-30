<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function show(Request $request, Product $product): View
    {
        abort_unless($product->is_active, 404);

        $product->load(['category', 'gallery', 'variations', 'reviews' => fn ($q) => $q->visible()->latest()->with('user:id,profile_image')]);

        // ?variant=ID preselects a size/colour, like Shopify product links.
        $selectedVariant = $product->variations->firstWhere('id', (int) $request->query('variant'))
            ?? $product->variations->first(fn ($variant) => $variant->quantity > 0)
            ?? $product->variations->first();

        $related = Product::isActive()
            ->with('variations')
            ->where('id', '!=', $product->id)
            ->when($product->category_id, fn ($q) => $q->where('category_id', $product->category_id))
            ->inRandomOrder()
            ->limit(4)
            ->get();

        // Reviews are only ever writable from a delivered order that
        // actually contains this product (see ReviewController::store) —
        // find the user's own newest such order that doesn't have a
        // review against it yet, so the page offers the form only then.
        $reviewableOrder = null;
        if (Auth::check()) {
            $reviewedOrderIds = Review::where('product_id', $product->id)->pluck('order_id');
            $reviewableOrder = Order::where('user_id', Auth::id())
                ->where('status', 'Delivered')
                ->whereHas('items', fn ($q) => $q->where('item_id', $product->id))
                ->whereNotIn('id', $reviewedOrderIds)
                ->latest()
                ->first();
        }

        $images = collect([$product->cover_image, $product->getRawOriginal('hover_image') ? $product->hover_image : null])
            ->merge($product->gallery->pluck('image'))
            ->filter()
            ->unique()
            ->values();

        return view('products.show', [
            'product' => $product,
            'images' => $images,
            'selectedVariant' => $selectedVariant,
            'related' => $related,
            'reviewableOrder' => $reviewableOrder,
            'rating' => $product->rating,
            'reviewCount' => $product->reviews->count(),
        ]);
    }
}
