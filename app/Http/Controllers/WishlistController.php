<?php

namespace App\Http\Controllers;

use App\Support\Wishlist;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WishlistController extends Controller
{
    public function index(): View
    {
        return view('wishlist.index', [
            'products' => Wishlist::items(),
        ]);
    }

    public function toggle(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
        ]);

        $added = Wishlist::toggle((int) $request->product_id);

        $message = $added ? 'Added to your wishlist.' : 'Removed from your wishlist.';

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'added' => $added, 'message' => $message, 'count' => Wishlist::count()]);
        }

        return back()->with('success', $message);
    }

    public function remove(Request $request)
    {
        $request->validate(['product_id' => 'required|exists:products,id']);

        Wishlist::remove((int) $request->product_id);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'count' => Wishlist::count()]);
        }

        return back()->with('success', 'Removed from your wishlist.');
    }
}
