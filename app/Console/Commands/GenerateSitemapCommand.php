<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class GenerateSitemapCommand extends Command
{
    protected $signature = 'sitemap:generate';

    protected $description = 'Generate a dynamic XML sitemap including products, categories, pages and images';

    public function handle(): int
    {
        $products = DB::table('products')
            ->where('is_active', 1)
            ->select('id', 'title', 'slug', 'main_image', 'updated_at')
            ->orderBy('id', 'desc')
            ->get();

        $categories = DB::table('categories')
            ->where('is_active', 1)
            ->select('id', 'name', 'slug', 'updated_at')
            ->orderBy('id', 'desc')
            ->get();

        $pages = DB::table('pages')
            ->where('is_published', 1)
            ->select('id', 'title', 'slug', 'updated_at')
            ->orderBy('id', 'desc')
            ->get();

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL;
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"' . PHP_EOL;
        $xml .= '        xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">' . PHP_EOL;

        $xml .= '    <url>' . PHP_EOL;
        $xml .= '        <loc>' . htmlspecialchars(url('/'), ENT_XML1, 'UTF-8') . '</loc>' . PHP_EOL;
        $xml .= '        <lastmod>' . now()->toAtomString() . '</lastmod>' . PHP_EOL;
        $xml .= '        <changefreq>daily</changefreq>' . PHP_EOL;
        $xml .= '        <priority>1.0</priority>' . PHP_EOL;
        $xml .= '    </url>' . PHP_EOL;

        $xml .= '    <url>' . PHP_EOL;
        $xml .= '        <loc>' . htmlspecialchars(route('product.index'), ENT_XML1, 'UTF-8') . '</loc>' . PHP_EOL;
        $xml .= '        <lastmod>' . now()->toAtomString() . '</lastmod>' . PHP_EOL;
        $xml .= '        <changefreq>daily</changefreq>' . PHP_EOL;
        $xml .= '        <priority>0.9</priority>' . PHP_EOL;
        $xml .= '    </url>' . PHP_EOL;

        $xml .= '    <url>' . PHP_EOL;
        $xml .= '        <loc>' . htmlspecialchars(route('product.flash_deals'), ENT_XML1, 'UTF-8') . '</loc>' . PHP_EOL;
        $xml .= '        <lastmod>' . now()->toAtomString() . '</lastmod>' . PHP_EOL;
        $xml .= '        <changefreq>daily</changefreq>' . PHP_EOL;
        $xml .= '        <priority>0.8</priority>' . PHP_EOL;
        $xml .= '    </url>' . PHP_EOL;

        $xml .= '    <url>' . PHP_EOL;
        $xml .= '        <loc>' . htmlspecialchars(route('product.new_collection'), ENT_XML1, 'UTF-8') . '</loc>' . PHP_EOL;
        $xml .= '        <lastmod>' . now()->toAtomString() . '</lastmod>' . PHP_EOL;
        $xml .= '        <changefreq>daily</changefreq>' . PHP_EOL;
        $xml .= '        <priority>0.8</priority>' . PHP_EOL;
        $xml .= '    </url>' . PHP_EOL;

        $xml .= '    <url>' . PHP_EOL;
        $xml .= '        <loc>' . htmlspecialchars(route('product.best_sale'), ENT_XML1, 'UTF-8') . '</loc>' . PHP_EOL;
        $xml .= '        <lastmod>' . now()->toAtomString() . '</lastmod>' . PHP_EOL;
        $xml .= '        <changefreq>daily</changefreq>' . PHP_EOL;
        $xml .= '        <priority>0.8</priority>' . PHP_EOL;
        $xml .= '    </url>' . PHP_EOL;

        foreach ($categories as $cat) {
            $catLastMod = !empty($cat->updated_at) ? date('c', strtotime($cat->updated_at)) : now()->toAtomString();
            $xml .= '    <url>' . PHP_EOL;
            $xml .= '        <loc>' . htmlspecialchars(route('category.show', $cat->slug), ENT_XML1, 'UTF-8') . '</loc>' . PHP_EOL;
            $xml .= '        <lastmod>' . $catLastMod . '</lastmod>' . PHP_EOL;
            $xml .= '        <changefreq>weekly</changefreq>' . PHP_EOL;
            $xml .= '        <priority>0.8</priority>' . PHP_EOL;
            $xml .= '    </url>' . PHP_EOL;
        }

        foreach ($products as $prod) {
            $prodLastMod = !empty($prod->updated_at) ? date('c', strtotime($prod->updated_at)) : now()->toAtomString();
            $xml .= '    <url>' . PHP_EOL;
            $xml .= '        <loc>' . htmlspecialchars(route('product.show', $prod->slug), ENT_XML1, 'UTF-8') . '</loc>' . PHP_EOL;
            $xml .= '        <lastmod>' . $prodLastMod . '</lastmod>' . PHP_EOL;
            $xml .= '        <changefreq>daily</changefreq>' . PHP_EOL;
            $xml .= '        <priority>0.9</priority>' . PHP_EOL;

            if (!empty($prod->main_image)) {
                $imgUrl = filter_var($prod->main_image, FILTER_VALIDATE_URL) ? $prod->main_image : asset($prod->main_image);
                $xml .= '        <image:image>' . PHP_EOL;
                $xml .= '            <image:loc>' . htmlspecialchars($imgUrl, ENT_XML1, 'UTF-8') . '</image:loc>' . PHP_EOL;
                $xml .= '            <image:title>' . htmlspecialchars($prod->title, ENT_XML1, 'UTF-8') . '</image:title>' . PHP_EOL;
                $xml .= '        </image:image>' . PHP_EOL;
            }

            $xml .= '    </url>' . PHP_EOL;
        }

        foreach ($pages as $page) {
            $pageLastMod = !empty($page->updated_at) ? date('c', strtotime($page->updated_at)) : now()->toAtomString();
            $xml .= '    <url>' . PHP_EOL;
            $xml .= '        <loc>' . htmlspecialchars(route('page.show', $page->slug), ENT_XML1, 'UTF-8') . '</loc>' . PHP_EOL;
            $xml .= '        <lastmod>' . $pageLastMod . '</lastmod>' . PHP_EOL;
            $xml .= '        <changefreq>monthly</changefreq>' . PHP_EOL;
            $xml .= '        <priority>0.5</priority>' . PHP_EOL;
            $xml .= '    </url>' . PHP_EOL;
        }

        $xml .= '</urlset>' . PHP_EOL;

        File::put(public_path('sitemap.xml'), $xml);
        $this->info('Sitemap successfully generated at ' . public_path('sitemap.xml'));

        return Command::SUCCESS;
    }
}
