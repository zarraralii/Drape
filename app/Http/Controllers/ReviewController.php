<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Models\Review;
use App\Traits\FileManagerTrait;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    use FileManagerTrait;

    /**
     * Reviews are only allowed from a delivered order that actually
     * contains this product — the `auth` middleware on this route
     * guarantees a logged-in user, and everything else (ownership,
     * delivery status, the product being in it, not already reviewed) is
     * re-checked here rather than trusted from the form, since the order
     * id travels as a plain hidden input.
     */
    public function store(Request $request, Product $product): RedirectResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email|max:150',
            'rating' => 'required|integer|min:1|max:5',
            'review' => 'required|string|max:2000',
            'order_id' => 'required|integer|exists:orders,id',
            'media' => 'nullable|array|max:5',
            'media.*' => 'file|mimes:jpg,jpeg,png,webp,mp4,mov,webm|max:20480',
        ]);

        $order = Order::with('items')
            ->where('id', $data['order_id'])
            ->where('user_id', Auth::id())
            ->where('status', 'Delivered')
            ->first();

        $boughtThisProduct = $order?->items->contains(fn ($item) => $item->item_id === $product->id);

        if (!$order || !$boughtThisProduct) {
            return back()->withInput()->with('fail', 'You can only review a product from your own delivered orders.');
        }

        $alreadyReviewed = Review::where('order_id', $order->id)->where('product_id', $product->id)->exists();

        if ($alreadyReviewed) {
            return back()->with('fail', 'You have already reviewed this product for that order.');
        }

        $media = [];

        foreach ($request->file('media', []) as $file) {
            if (!$file) {
                continue;
            }

            // Read the mime type before upload() moves the file off its
            // temp path — afterwards the UploadedFile object can no longer
            // read the (now relocated) file to guess its type.
            $type = str_starts_with($file->getMimeType(), 'video/') ? 'video' : 'image';
            $filename = $this->upload('upload/reviews/', $file);

            $media[] = ['file' => $filename, 'type' => $type];
        }

        Review::create([
            'user_id' => Auth::id(),
            'product_id' => $product->id,
            'order_id' => $order->id,
            'name' => $data['name'],
            'email' => $data['email'],
            'rating' => $data['rating'],
            'review' => $data['review'],
            'media' => $media,
        ]);

        // redirect_to comes from the form, so only same-site URLs are honoured.
        $fallback = route('products.show', $product->slug) . '#reviews';

        return redirect(safe_redirect_url($request->input('redirect_to'), $fallback))
            ->with('success', 'Thanks — your review has been posted.');
    }
}
