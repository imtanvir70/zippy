<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class FacebookFeedController extends Controller
{
    public function feed(): Response
    {
        $settings = DB::table('settings')
            ->whereIn('key', [
                'store_name',
                'store_tagline',
                'shipping_inside_dhaka',
                'return_policy_days',
            ])
            ->pluck('value', 'key');

        $storeName = $settings['store_name'] ?? 'Zippy BD';
        $storeDescription = $settings['store_tagline'] ?? 'Premium & Trendy Lifestyle Tech Store in Bangladesh';
        $storeUrl = url('/');
        $defaultShippingCost = isset($settings['shipping_inside_dhaka']) && $settings['shipping_inside_dhaka'] !== ''
            ? (float) $settings['shipping_inside_dhaka']
            : 60.00;
        $returnPolicyDays = isset($settings['return_policy_days']) && $settings['return_policy_days'] !== ''
            ? (int) $settings['return_policy_days']
            : 7;

        $categories = DB::table('categories')
            ->select('id', 'name', 'parent_id')
            ->get()
            ->keyBy('id');

        $buildCategoryPath = function ($catId) use (&$buildCategoryPath, $categories) {
            if (!$catId || !isset($categories[$catId])) {
                return null;
            }
            $cat = $categories[$catId];
            if ($cat->parent_id && isset($categories[$cat->parent_id])) {
                $parentPath = $buildCategoryPath($cat->parent_id);
                return $parentPath ? ($parentPath . ' > ' . $cat->name) : $cat->name;
            }
            return $cat->name;
        };

        $formatImageUrl = function ($imagePath) {
            if (empty($imagePath)) {
                return null;
            }
            if (str_starts_with($imagePath, 'http://') || str_starts_with($imagePath, 'https://')) {
                return $imagePath;
            }
            return asset(ltrim($imagePath, '/'));
        };

        $rawProducts = DB::table('products')
            ->where('is_active', 1)
            ->orderBy('id', 'desc')
            ->get();

        $products = [];

        foreach ($rawProducts as $p) {
            $rawDesc = !empty($p->short_desc) ? $p->short_desc : $p->description;
            $cleanDesc = trim(strip_tags($rawDesc ?: $p->title));
            if (mb_strlen($cleanDesc) > 5000) {
                $cleanDesc = mb_substr($cleanDesc, 0, 4997) . '...';
            }

            $imageLink = $formatImageUrl($p->main_image);
            $gallery = is_array($p->gallery_images) ? $p->gallery_images : (json_decode($p->gallery_images ?? '', true) ?: []);
            $additionalImages = [];
            if (is_array($gallery)) {
                foreach ($gallery as $img) {
                    $url = $formatImageUrl($img);
                    if ($url && $url !== $imageLink && !in_array($url, $additionalImages, true)) {
                        $additionalImages[] = $url;
                    }
                }
            }

            $currentPrice = (float) $p->price;
            $oldPrice = !empty($p->old_price) ? (float) $p->old_price : null;
            if ($oldPrice !== null && $oldPrice > $currentPrice) {
                $catalogPrice = $oldPrice;
                $salePrice = $currentPrice;
            } else {
                $catalogPrice = $currentPrice;
                $salePrice = null;
            }

            $salePriceEffectiveDate = null;
            if ($p->is_flash_deal && $salePrice !== null) {
                $salePriceEffectiveDate = now()->startOfDay()->toIso8601String() . '/' . now()->addDays(7)->endOfDay()->toIso8601String();
            }

            $specs = is_array($p->specifications) ? $p->specifications : (json_decode($p->specifications ?? '', true) ?: []);
            if (!is_array($specs)) {
                $specs = [];
            }

            $getSpec = function (array $keys) use ($specs) {
                foreach ($specs as $k => $v) {
                    if (!empty($v) && is_string($v) && in_array(strtolower(trim($k)), array_map('strtolower', $keys), true)) {
                        return trim($v);
                    }
                }
                return null;
            };

            $brand = $getSpec(['brand', 'manufacturer', 'make']) ?: $storeName;
            $color = $getSpec(['color', 'colour']);
            $size = $getSpec(['size', 'dimension', 'dimensions']);
            $gender = $getSpec(['gender']);
            $ageGroup = $getSpec(['age group', 'age_group', 'age']);
            $material = $getSpec(['material']);
            $pattern = $getSpec(['pattern']);
            $gtin = $getSpec(['gtin', 'ean', 'upc', 'barcode']);
            $mpn = $getSpec(['mpn', 'model', 'model no', 'model number']) ?: $p->sku;

            $variants = is_array($p->variants) ? $p->variants : (json_decode($p->variants ?? '', true) ?: []);
            $itemGroupId = (!empty($variants) && is_array($variants) && count($variants) > 1) ? (string) $p->id : null;
            if (empty($color) && !empty($variants[0]['name']) && is_string($variants[0]['name'])) {
                $color = $variants[0]['name'];
            }

            $catPath = !empty($p->category_id) ? $buildCategoryPath($p->category_id) : null;
            $productType = $catPath;
            $googleProductCategory = $catPath;

            if ($currentPrice < 1000) {
                $customLabel0 = 'Under 1000 BDT';
            } elseif ($currentPrice <= 3000) {
                $customLabel0 = '1000-3000 BDT';
            } elseif ($currentPrice <= 5000) {
                $customLabel0 = '3000-5000 BDT';
            } else {
                $customLabel0 = 'Over 5000 BDT';
            }

            if ($p->is_flash_deal) {
                $customLabel1 = 'Flash Deal';
            } elseif ($p->is_featured) {
                $customLabel1 = 'Featured';
            } elseif (!empty($p->badge_type)) {
                $customLabel1 = ucwords(str_replace('_', ' ', $p->badge_type));
            } else {
                $customLabel1 = 'Regular';
            }

            if ((int) $p->stock_qty <= 0) {
                $customLabel2 = 'Out of Stock';
            } elseif ((int) $p->stock_qty <= 5) {
                $customLabel2 = 'Low Stock';
            } else {
                $customLabel2 = 'In Stock';
            }

            $customLabel3 = $p->is_free_shipping ? 'Free Shipping' : 'Standard Delivery';
            $customLabel4 = !empty($p->tag) ? $p->tag : ($catPath ? explode(' > ', $catPath)[0] : null);

            $shippingPrice = $p->is_free_shipping ? 0.00 : $defaultShippingCost;

            $products[] = (object) [
                'id' => (string) $p->id,
                'title' => (string) $p->title,
                'description' => $cleanDesc,
                'link' => route('product.show', $p->slug),
                'image_link' => $imageLink,
                'additional_image_links' => $additionalImages,
                'availability' => ((int) $p->stock_qty > 0) ? 'in stock' : 'out of stock',
                'condition' => 'new',
                'price' => $catalogPrice,
                'sale_price' => $salePrice,
                'sale_price_effective_date' => $salePriceEffectiveDate,
                'brand' => $brand,
                'google_product_category' => $googleProductCategory,
                'product_type' => $productType,
                'item_group_id' => $itemGroupId,
                'color' => $color,
                'size' => $size,
                'gender' => $gender,
                'age_group' => $ageGroup,
                'material' => $material,
                'pattern' => $pattern,
                'gtin' => $gtin,
                'mpn' => $mpn,
                'custom_label_0' => $customLabel0,
                'custom_label_1' => $customLabel1,
                'custom_label_2' => $customLabel2,
                'custom_label_3' => $customLabel3,
                'custom_label_4' => $customLabel4,
                'shipping_country' => 'BD',
                'shipping_price' => $shippingPrice,
                'return_policy_days' => $returnPolicyDays,
            ];
        }

        return response()->view('frontend.feeds.facebook', [
            'storeName' => $storeName,
            'storeDescription' => $storeDescription,
            'storeUrl' => $storeUrl,
            'products' => $products,
        ], 200, [
            'Content-Type' => 'application/xml; charset=UTF-8',
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }
}
