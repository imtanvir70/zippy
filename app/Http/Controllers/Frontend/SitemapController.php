<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;

class SitemapController extends Controller
{
    public function sitemap()
    {
        $xml = Cache::remember('fc.sitemap.xml', 1800, function () {
            $products = DB::table('products')->where('is_active', 1)->select('slug', 'updated_at')->get();
            $categories = DB::table('categories')->where('is_active', 1)->select('slug', 'updated_at')->get();
            $pages = DB::table('pages')->select('slug', 'updated_at')->get();

            return $this->buildSitemapXml($products, $categories, $pages);
        });

        return Response::make($xml, 200, ['Content-Type' => 'application/xml']);
    }

    protected function buildSitemapXml($products, $categories, $pages): string
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>';
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

        $xml .= '<url>';
        $xml .= '<loc>' . url('/') . '</loc>';
        $xml .= '<changefreq>daily</changefreq>';
        $xml .= '<priority>1.0</priority>';
        $xml .= '</url>';

        $curatedCollections = [
            ['loc' => route('product.new_collection'), 'freq' => 'daily', 'priority' => '0.8'],
            ['loc' => route('product.best_sale'), 'freq' => 'daily', 'priority' => '0.8'],
            ['loc' => route('product.flash_deals'), 'freq' => 'daily', 'priority' => '0.8'],
        ];

        foreach ($curatedCollections as $item) {
            $xml .= '<url>';
            $xml .= '<loc>' . $item['loc'] . '</loc>';
            $xml .= '<changefreq>' . $item['freq'] . '</changefreq>';
            $xml .= '<priority>' . $item['priority'] . '</priority>';
            $xml .= '</url>';
        }

        foreach ($categories as $cat) {
            $xml .= '<url>';
            $xml .= '<loc>' . route('category.show', $cat->slug) . '</loc>';
            $xml .= '<lastmod>' . date('Y-m-d', strtotime($cat->updated_at)) . '</lastmod>';
            $xml .= '<changefreq>weekly</changefreq>';
            $xml .= '<priority>0.8</priority>';
            $xml .= '</url>';
        }

        foreach ($products as $prod) {
            $xml .= '<url>';
            $xml .= '<loc>' . route('product.show', $prod->slug) . '</loc>';
            $xml .= '<lastmod>' . date('Y-m-d', strtotime($prod->updated_at)) . '</lastmod>';
            $xml .= '<changefreq>weekly</changefreq>';
            $xml .= '<priority>0.9</priority>';
            $xml .= '</url>';
        }

        $recordedPageSlugs = [];
        foreach ($pages as $p) {
            if (!empty($p->slug)) {
                $recordedPageSlugs[] = $p->slug;
                $xml .= '<url>';
                $xml .= '<loc>' . route('page.show', $p->slug) . '</loc>';
                $xml .= '<lastmod>' . date('Y-m-d', strtotime($p->updated_at ?? now())) . '</lastmod>';
                $xml .= '<changefreq>monthly</changefreq>';
                $xml .= '<priority>0.6</priority>';
                $xml .= '</url>';
            }
        }

        $standardPolicies = ['about-us', 'privacy-policy', 'terms-and-conditions', 'return-refund-policy'];
        foreach ($standardPolicies as $policySlug) {
            if (!in_array($policySlug, $recordedPageSlugs)) {
                $xml .= '<url>';
                $xml .= '<loc>' . route('page.show', $policySlug) . '</loc>';
                $xml .= '<changefreq>monthly</changefreq>';
                $xml .= '<priority>0.5</priority>';
                $xml .= '</url>';
            }
        }

        $xml .= '</urlset>';

        return $xml;
    }

    public function robots()
    {
        $robots = "User-agent: *\n"
            . "Allow: /\n"
            . "Disallow: /admin\n"
            . "Disallow: /admin/\n"
            . "Disallow: /checkout\n"
            . "Disallow: /checkout/\n"
            . "Disallow: /cart\n"
            . "Disallow: /order/track\n"
            . "Disallow: /api/\n"
            . "\n"
            . "User-agent: Googlebot\n"
            . "Allow: /\n"
            . "\n"
            . "User-agent: Bingbot\n"
            . "Allow: /\n"
            . "\n"
            . "User-agent: GPTBot\n"
            . "Allow: /\n"
            . "\n"
            . "User-agent: ClaudeBot\n"
            . "Allow: /\n"
            . "\n"
            . "User-agent: PerplexityBot\n"
            . "Allow: /\n"
            . "\n"
            . "User-agent: CCBot\n"
            . "Allow: /\n"
            . "\n"
            . "Sitemap: " . url('/sitemap.xml') . "\n"
            . "LLMs-Txt: " . url('/llms.txt') . "\n";

        return Response::make($robots, 200, ['Content-Type' => 'text/plain']);
    }
}
