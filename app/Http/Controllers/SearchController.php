<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SearchController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('q', ''));

        $products = $search === ''
            ? Product::isActive()->with('variations')->latest()->limit(8)->get()
            : $this->matching($search)->with('variations')->orderByDesc('is_featured')->latest()->paginate(24)->withQueryString();

        return view('search.index', [
            'search' => $search,
            'products' => $products,
        ]);
    }

    /** Predictive search for the header search panel. */
    public function suggest(Request $request): JsonResponse
    {
        $search = trim((string) $request->query('q', ''));

        if (mb_strlen($search) < 2) {
            return response()->json(['products' => []]);
        }

        $products = $this->matching($search)->with('variations')->orderByDesc('is_featured')->latest()->limit(6)->get();

        return response()->json([
            'products' => $products->map(fn (Product $product) => [
                'name' => $product->name,
                'url' => route('products.show', $product->slug),
                'image' => $product->cover_image_thumbnail ?: $product->cover_image,
                'price' => money($product->priceRange()[0]),
            ]),
            'all_url' => route('search', ['q' => $search]),
        ]);
    }

    private function matching(string $search): Builder
    {
        return Product::isActive()->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
                ->orWhere('short_description', 'like', "%{$search}%")
                ->orWhere('tags', 'like', "%{$search}%")
                ->orWhereHas('category', fn ($cq) => $cq->where('name', 'like', "%{$search}%"));
        });
    }
}
