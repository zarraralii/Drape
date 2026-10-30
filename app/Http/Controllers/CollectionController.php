<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariation;
use App\Support\Swatches;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

/**
 * Product listings: /collections/all and /collections/{handle}, with
 * Shopify-style filters (availability, price, size, colour…) and sorting.
 */
class CollectionController extends Controller
{
    public const SORTS = [
        'featured' => 'Featured',
        'newest' => 'Newest',
        'best_selling' => 'Best selling',
        'price_asc' => 'Price, low to high',
        'price_desc' => 'Price, high to low',
        'title' => 'Alphabetically, A–Z',
    ];

    // Sale price when a discount is set, otherwise the regular price.
    private const EFFECTIVE_PRICE = 'CASE WHEN is_discounted = 1 AND discounted_price IS NOT NULL AND discounted_price < price THEN discounted_price ELSE price END';

    public function index(): View
    {
        return view('collections.index', [
            'collections' => Category::isActive()
                ->withCount(['products' => fn ($q) => $q->isActive()])
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function all(Request $request): View
    {
        return $this->listing($request, null);
    }

    public function show(Request $request, Category $category): View
    {
        abort_unless($category->is_active, 404);

        return $this->listing($request, $category);
    }

    private function listing(Request $request, ?Category $category): View
    {
        $scope = Product::isActive()->when($category, function (Builder $q) use ($category) {
            $ids = $category->subcategories->pluck('id')->push($category->id);
            $q->where(fn ($sq) => $sq->whereIn('category_id', $ids)->orWhereIn('subcategory_id', $ids));
        });

        // Option filters (Size, Colour…) offered = what this collection actually stocks.
        $optionFilters = $this->availableOptions((clone $scope)->pluck('id'));

        $search = trim((string) $request->query('q', ''));
        $sort = array_key_exists($request->query('sort'), self::SORTS) ? $request->query('sort') : ($category ? 'featured' : 'newest');
        $selectedOptions = $this->sanitiseOptions((array) $request->query('filter', []), $optionFilters);
        $categoryIds = array_values(array_filter(array_map('intval', (array) $request->query('category', []))));
        $inStock = $request->boolean('in_stock');
        $onSale = $request->boolean('sale');
        $minPrice = is_numeric($request->query('min_price')) ? (float) $request->query('min_price') : null;
        $maxPrice = is_numeric($request->query('max_price')) ? (float) $request->query('max_price') : null;

        $query = (clone $scope)->with(['variations', 'category'])->withSum('orderItems', 'qty');

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('short_description', 'like', "%{$search}%")
                    ->orWhere('tags', 'like', "%{$search}%")
                    ->orWhereHas('category', fn ($cq) => $cq->where('name', 'like', "%{$search}%"));
            });
        }

        if ($categoryIds) {
            $query->whereIn('category_id', $categoryIds);
        }

        foreach ($selectedOptions as $name => $values) {
            $query->whereHas('variations', fn ($vq) => $vq->whereIn("attributes->$name", $values)->where('quantity', '>', 0));
        }

        if ($inStock) {
            $query->where(fn ($q) => $q->whereNull('qty')->orWhere('qty', '>', 0));
        }

        if ($onSale) {
            $query->where('is_discounted', 1)->whereColumn('discounted_price', '<', 'price');
        }

        if ($minPrice !== null) {
            $query->whereRaw(self::EFFECTIVE_PRICE . ' >= CAST(? AS DECIMAL(10,2))', [$minPrice]);
        }
        if ($maxPrice !== null) {
            $query->whereRaw(self::EFFECTIVE_PRICE . ' <= CAST(? AS DECIMAL(10,2))', [$maxPrice]);
        }

        match ($sort) {
            'featured' => $query->orderByDesc('is_featured')->latest(),
            'best_selling' => $query->orderByDesc('order_items_sum_qty')->latest(),
            'price_asc' => $query->orderByRaw(self::EFFECTIVE_PRICE . ' asc'),
            'price_desc' => $query->orderByRaw(self::EFFECTIVE_PRICE . ' desc'),
            'title' => $query->orderBy('name'),
            default => $query->latest(),
        };

        $bounds = (clone $scope)->selectRaw('MIN(' . self::EFFECTIVE_PRICE . ') as min_price, MAX(' . self::EFFECTIVE_PRICE . ') as max_price')->first();

        $activeFilterCount = $selectedOptions->flatten()->count() + count($categoryIds) + (int) $inStock + (int) $onSale
            + (int) ($minPrice !== null) + (int) ($maxPrice !== null);

        return view('collections.show', [
            'category' => $category,
            'title' => $category?->name ?? ($onSale ? 'Sale' : ($search !== '' ? 'Search results' : 'All products')),
            'products' => $query->paginate(24)->withQueryString(),
            'search' => $search,
            'sort' => $sort,
            'sorts' => self::SORTS,
            'optionFilters' => $optionFilters,
            'selectedOptions' => $selectedOptions,
            'categories' => $category ? collect() : Category::isActive()->orderBy('sort_order')->orderBy('name')->get(['id', 'name']),
            'categoryIds' => $categoryIds,
            'inStock' => $inStock,
            'onSale' => $onSale,
            'minPrice' => $minPrice,
            'maxPrice' => $maxPrice,
            'priceFloor' => (float) floor($bounds->min_price ?? 0),
            'priceCeil' => (float) ceil($bounds->max_price ?? 0),
            'activeFilterCount' => $activeFilterCount,
        ]);
    }

    /**
     * Every option name → its values in display order, across the in-stock
     * variants of the products in scope.
     *
     * @return Collection<string, array<int, string>>
     */
    private function availableOptions(Collection $productIds): Collection
    {
        $options = [];

        ProductVariation::whereIn('product_id', $productIds)
            ->where('quantity', '>', 0)
            ->get(['attributes'])
            ->each(function (ProductVariation $variant) use (&$options) {
                foreach ($variant->optionValues() as $name => $value) {
                    if (preg_match('/^[\pL\pN _-]{1,40}$/u', (string) $name) && $value !== '' && $value !== null) {
                        $options[$name][(string) $value] = true;
                    }
                }
            });

        return collect($options)
            ->map(fn ($values, $name) => collect(array_keys($values))->sortBy(fn ($v) => Swatches::sortOrder($name, $v))->values()->all())
            ->sortKeysUsing(fn ($a, $b) => (Swatches::isColorOption($b) <=> Swatches::isColorOption($a)) ?: strcmp($a, $b));
    }

    /**
     * Only option names and values that really exist in this collection
     * make it into the query — the name ends up in a JSON path.
     *
     * @return Collection<string, array<int, string>>
     */
    private function sanitiseOptions(array $requested, Collection $available): Collection
    {
        return collect($requested)
            ->filter(fn ($values, $name) => $available->has($name))
            ->map(fn ($values, $name) => array_values(array_intersect(array_map('strval', (array) $values), $available[$name])))
            ->filter();
    }
}
