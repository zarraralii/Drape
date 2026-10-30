<?php

namespace App\Http\Controllers;

use App\Models\Banner;
use App\Models\Category;
use App\Models\GalleryImage;
use App\Models\Product;
use App\Support\Store;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        $siteInfo = Store::info();

        $hero = [
            'eyebrow' => $siteInfo?->hero_eyebrow ?: 'New Season',
            'headline' => $siteInfo?->hero_subheadline ?: 'Made to move with you',
            'description' => $siteInfo?->hero_description ?: 'Easy, considered pieces in natural fabrics — cut to drape well and wear every day.',
            'ctaLabel' => $siteInfo?->hero_cta_label ?: 'Shop New Arrivals',
        ];

        $newArrivals = Product::isActive()->with('variations')->latest()->limit(8)->get();

        $featured = Product::isActive()
            ->where('is_featured', true)
            ->with('variations')
            ->latest()
            ->limit(8)
            ->get();

        $onSale = Product::isActive()
            ->with('variations')
            ->where(fn ($q) => $q->where('is_deal', true)->orWhere(fn ($sq) => $sq->where('is_discounted', 1)->whereColumn('discounted_price', '<', 'price')))
            ->latest()
            ->limit(4)
            ->get()
            ->filter(fn (Product $product) => $product->is_deal || $product->is_on_sale)
            ->values();

        return view('home.index', [
            'hero' => $hero,
            'banners' => Banner::isActive()->orderBy('sort_order')->orderBy('id')->get(),
            'collections' => Category::isActive()
                ->whereNull('parent_id')
                ->withCount(['products' => fn ($q) => $q->isActive()])
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get(),
            'newArrivals' => $newArrivals,
            'featured' => $featured->isNotEmpty() ? $featured : $newArrivals,
            'onSale' => $onSale,
            'gallery' => GalleryImage::latest()->limit(8)->get(),
        ]);
    }
}
