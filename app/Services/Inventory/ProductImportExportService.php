<?php

namespace App\Services\Inventory;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProductImportExportService
{
    /**
     * Export all products to CSV format string.
     */
    public function exportProductsCsv(): string
    {
        $products = DB::table('products')
            ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
            ->select('products.*', 'categories.name as category_name')
            ->orderBy('products.id')
            ->get();

        $handle = fopen('php://temp', 'r+');
        fputcsv($handle, [
            'ID', 'SKU', 'Title', 'Category', 'Price', 'Old Price',
            'Stock Quantity', 'Rating', 'Reviews Count', 'Tag', 'Badge Type',
            'Is Flash Deal', 'Is Featured', 'Is Active', 'Main Image', 'Short Description'
        ]);

        foreach ($products as $p) {
            fputcsv($handle, [
                $p->id,
                $p->sku,
                $p->title,
                $p->category_name ?? 'Uncategorized',
                $p->price,
                $p->old_price ?? '',
                $p->stock_qty,
                $p->rating,
                $p->reviews_count,
                $p->tag ?? '',
                $p->badge_type ?? '',
                $p->is_flash_deal,
                $p->is_featured,
                $p->is_active,
                $p->main_image,
                $p->short_desc ?? ''
            ]);
        }

        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        return $csv;
    }

    /**
     * Import products from CSV file in a safe database transaction.
     */
    public function importProductsCsv(string $filePath): array
    {
        if (!file_exists($filePath) || !is_readable($filePath)) {
            return ['success' => false, 'message' => 'CSV file could not be read.'];
        }

        $handle = fopen($filePath, 'r');
        $header = fgetcsv($handle);
        if (!$header) {
            fclose($handle);
            return ['success' => false, 'message' => 'Invalid or empty CSV file.'];
        }

        $imported = 0;
        $updated = 0;
        $errors = [];

        DB::beginTransaction();
        try {
            $categories = DB::table('categories')->pluck('id', 'name')->toArray();
            $defaultCatId = DB::table('categories')->value('id') ?? 1;

            $rowNumber = 1;
            while (($row = fgetcsv($handle)) !== false) {
                $rowNumber++;
                if (empty($row[1]) && empty($row[2])) continue; // Skip empty rows

                $sku = trim($row[1] ?? '');
                $title = trim($row[2] ?? 'Product ' . $rowNumber);
                $categoryName = trim($row[3] ?? '');
                $price = (float) ($row[4] ?? 0);
                $oldPrice = !empty($row[5]) ? (float) $row[5] : null;
                $stock = (int) ($row[6] ?? 10);
                $tag = $row[9] ?? null;
                $mainImage = !empty($row[14]) ? trim($row[14]) : 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e';

                $catId = $categories[$categoryName] ?? $defaultCatId;

                if (empty($sku)) {
                    $sku = 'ZB-' . strtoupper(Str::random(6));
                }

                $existing = DB::table('products')->where('sku', $sku)->first();

                if ($existing) {
                    DB::table('products')->where('id', $existing->id)->update([
                        'title' => $title,
                        'category_id' => $catId,
                        'price' => $price,
                        'old_price' => $oldPrice,
                        'stock_qty' => $stock,
                        'tag' => $tag,
                        'main_image' => $mainImage,
                        'updated_at' => now(),
                    ]);
                    $updated++;
                } else {
                    $slug = Str::slug($title);
                    $origSlug = $slug;
                    $c = 1;
                    while (DB::table('products')->where('slug', $slug)->exists()) {
                        $slug = "{$origSlug}-" . $c++;
                    }

                    DB::table('products')->insert([
                        'category_id' => $catId,
                        'title' => $title,
                        'slug' => $slug,
                        'sku' => $sku,
                        'price' => $price,
                        'old_price' => $oldPrice,
                        'stock_qty' => $stock,
                        'rating' => 5.0,
                        'reviews_count' => 10,
                        'tag' => $tag,
                        'badge_type' => 'new',
                        'is_flash_deal' => 0,
                        'is_featured' => 0,
                        'is_active' => 1,
                        'main_image' => $mainImage,
                        'gallery_images' => json_encode([$mainImage]),
                        'variants' => json_encode([]),
                        'specifications' => json_encode([]),
                        'short_desc' => 'High quality premium product.',
                        'description' => 'Detailed product specifications and premium description.',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    $imported++;
                }
            }

            fclose($handle);
            DB::commit();

            return [
                'success' => true,
                'message' => "CSV Processed successfully! {$imported} new products imported, {$updated} products updated.",
                'imported_count' => $imported,
                'updated_count' => $updated
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            if (is_resource($handle)) fclose($handle);
            return [
                'success' => false,
                'message' => 'Error during CSV import: ' . $e->getMessage()
            ];
        }
    }
}