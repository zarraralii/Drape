<?php

namespace App\Http\Controllers;

use App\Models\Faq;
use App\Models\StorySection;
use App\Support\Store;
use Illuminate\View\View;

class PageController extends Controller
{
    public function story(): View
    {
        $siteInfo = Store::info();

        return view('pages.story', [
            'about' => $siteInfo?->about,
            'sections' => StorySection::isActive()->orderBy('sort_order')->orderBy('id')->get(),
            'banner' => $siteInfo?->about_banner,
            'story' => [
                'eyebrow' => $siteInfo?->story_eyebrow ?: 'Our Story',
                'headline' => $siteInfo?->story_headline ?: 'Clothes that hang right and last',
                'description' => $siteInfo?->story_description ?: 'Drape started with a simple idea: everyday clothing should feel as good on the tenth wear as the first. We choose breathable natural fabrics, cut them generously so they fall well on real bodies, and make them in small runs.',
                'ctaLabel' => $siteInfo?->story_cta_label ?: 'Shop the Collection',
                'ctaLink' => $siteInfo?->story_cta_link ?: route('products.index'),
            ],
        ]);
    }

    public function faq(): View
    {
        return view('pages.faq', [
            'faqs' => Faq::orderBy('sort')->get(),
            'banner' => Store::info()?->faq_banner,
        ]);
    }

    public function sizeGuide(): View
    {
        return view('pages.size-guide', ['content' => Store::info()?->size_guide]);
    }

    public function privacy(): View
    {
        return $this->policy('Privacy Policy', Store::info()?->privacy_policy, Store::info()?->privacy_banner);
    }

    public function terms(): View
    {
        return $this->policy('Terms of Service', Store::info()?->tnc, Store::info()?->tnc_banner);
    }

    public function returns(): View
    {
        return $this->policy('Refund & Exchange Policy', Store::info()?->return_and_exchange, Store::info()?->return_banner);
    }

    public function shipping(): View
    {
        return $this->policy('Shipping Policy', Store::info()?->shipping_policy, null);
    }

    private function policy(string $title, ?string $content, ?string $banner): View
    {
        return view('pages.policy', compact('title', 'content', 'banner'));
    }
}
