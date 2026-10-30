<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Facades\File;

/**
 * One-click sitemap rebuild: writes public/sitemap.xml with every public
 * page plus each active collection and product, and points robots.txt at it. Re-click
 * after adding, removing or deactivating products.
 *
 * URLs are built from the current request's host, so running it on the
 * live site gives live-domain links regardless of APP_URL.
 */
class SitemapController extends Controller
{
    public function generate()
    {
        $now = now()->toAtomString();

        $urls = [
            ['loc' => route('home'), 'lastmod' => $now, 'changefreq' => 'daily', 'priority' => '1.0'],
            ['loc' => route('products.index'), 'lastmod' => $now, 'changefreq' => 'daily', 'priority' => '0.9'],
            ['loc' => route('collections.index'), 'lastmod' => $now, 'changefreq' => 'weekly', 'priority' => '0.7'],
            ['loc' => route('story'), 'changefreq' => 'monthly', 'priority' => '0.6'],
            ['loc' => route('contact'), 'changefreq' => 'monthly', 'priority' => '0.5'],
            ['loc' => route('faq'), 'changefreq' => 'monthly', 'priority' => '0.5'],
            ['loc' => route('privacy'), 'changefreq' => 'yearly', 'priority' => '0.3'],
            ['loc' => route('terms'), 'changefreq' => 'yearly', 'priority' => '0.3'],
            ['loc' => route('returns'), 'changefreq' => 'yearly', 'priority' => '0.3'],
            ['loc' => route('shipping'), 'changefreq' => 'yearly', 'priority' => '0.3'],
        ];

        Category::isActive()
            ->whereNotNull('slug')
            ->orderBy('sort_order')
            ->get(['id', 'slug', 'updated_at'])
            ->each(function (Category $category) use (&$urls) {
                $urls[] = [
                    'loc' => route('collections.show', $category->slug),
                    'lastmod' => $category->updated_at?->toAtomString(),
                    'changefreq' => 'weekly',
                    'priority' => '0.8',
                ];
            });

        Product::isActive()
            ->whereNotNull('slug')
            ->orderBy('id')
            ->get(['id', 'slug', 'updated_at'])
            ->each(function (Product $product) use (&$urls) {
                $urls[] = [
                    'loc' => route('products.show', $product->slug),
                    'lastmod' => $product->updated_at?->toAtomString(),
                    'changefreq' => 'weekly',
                    'priority' => '0.8',
                ];
            });

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n"
            . '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

        foreach ($urls as $url) {
            $xml .= "  <url>\n";
            $xml .= '    <loc>' . htmlspecialchars($url['loc'], ENT_XML1) . "</loc>\n";
            if (!empty($url['lastmod'])) {
                $xml .= "    <lastmod>{$url['lastmod']}</lastmod>\n";
            }
            $xml .= "    <changefreq>{$url['changefreq']}</changefreq>\n";
            $xml .= "    <priority>{$url['priority']}</priority>\n";
            $xml .= "  </url>\n";
        }

        $xml .= "</urlset>\n";

        try {
            File::put(public_path('sitemap.xml'), $xml);
            $this->pointRobotsAtSitemap(url('sitemap.xml'));
        } catch (\Throwable $e) {
            return back()->with('fail', 'Could not write sitemap.xml: ' . $e->getMessage());
        }

        return back()->with('success', 'Sitemap generated with ' . count($urls) . ' URLs: ' . url('sitemap.xml'));
    }

    /**
     * Replace (or add) the "Sitemap:" line in robots.txt so crawlers find it.
     */
    private function pointRobotsAtSitemap(string $sitemapUrl): void
    {
        $path = public_path('robots.txt');
        $robots = File::exists($path) ? File::get($path) : "User-agent: *\nDisallow:\n";

        $robots = preg_replace('/^Sitemap:.*$\R?/mi', '', $robots);
        $robots = rtrim($robots) . "\n\nSitemap: {$sitemapUrl}\n";

        File::put($path, $robots);
    }
}
