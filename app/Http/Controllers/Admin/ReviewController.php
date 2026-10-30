<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Review;
use App\Traits\FileManagerTrait;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    use FileManagerTrait;

    public function reviews(Request $request)
    {
        $reviews = Review::with(['product:id,name,slug', 'order:id,uid'])
            ->when($request->product_id, fn ($q, $id) => $q->where('product_id', $id))
            ->when($request->status === 'hidden', fn ($q) => $q->where('is_hidden', true))
            ->when($request->status === 'visible', fn ($q) => $q->where('is_hidden', false))
            ->when($request->status === 'unreplied', fn ($q) => $q->whereNull('admin_reply'))
            ->when($request->search, function ($q, $search) {
                $q->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('review', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $products = Product::whereHas('reviews')->orderBy('name')->get(['id', 'name']);

        return view('admin.pages.reviews', compact('reviews', 'products'));
    }

    public function updateVisibility(Request $request)
    {
        $request->validate(['id' => 'required|exists:reviews,id']);

        $review = Review::findOrFail($request->id);
        $review->is_hidden = $request->boolean('is_hidden');
        $review->save();

        return response()->json([
            'success' => true,
            'message' => $review->is_hidden ? 'Review hidden from the storefront' : 'Review is visible again',
        ]);
    }

    /**
     * Saving an empty reply removes it, so the same form both edits and
     * clears the store's public response.
     */
    public function reply(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:reviews,id',
            'admin_reply' => 'nullable|string|max:2000',
        ]);

        $reply = trim((string) $request->admin_reply);

        $review = Review::findOrFail($request->id);
        $review->admin_reply = $reply !== '' ? $reply : null;
        $review->replied_at = $reply !== '' ? now() : null;
        $review->save();

        return response()->json([
            'success' => true,
            'message' => $review->admin_reply ? 'Reply saved' : 'Reply removed',
            'reply' => $review->admin_reply,
            'replied_at' => $review->replied_at?->format('M d, Y'),
        ]);
    }

    /**
     * Removing the row (rather than hiding it) is what lets the customer
     * review this product for that order again — both the product page and
     * the order card only offer the form when no review row exists.
     */
    public function deleteReview(Request $request)
    {
        $review = Review::findOrFail($request->id);

        foreach ($review->mediaFilenames() as $filename) {
            $this->delete('upload/reviews/', $filename);
        }

        $review->delete();

        return response()->json(['success' => true, 'message' => 'Review deleted — the customer can now review again']);
    }
}
