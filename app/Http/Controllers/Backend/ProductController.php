<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Services\Media\ImageOptimizerService;
use App\Services\Inventory\ProductImportExportService;
use App\Services\Rbac\PermissionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;
use App\Services\Ai\AiService;
use App\Services\Frontend\FrontendCacheService;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax() && !$request->has('_no_dt')) {
            $query = DB::table('products')
                ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
                ->select('products.*', 'categories.name as category_name', 'categories.name_bn as category_name_bn');

            if ($request->filled('category_id') && $request->category_id !== 'all') {
                $filterCatId = (int) $request->category_id;
                $childCatIds = DB::table('categories')->where('parent_id', $filterCatId)->pluck('id')->toArray();
                $grandChildCatIds = [];
                if (!empty($childCatIds)) {
                    $grandChildCatIds = DB::table('categories')->whereIn('parent_id', $childCatIds)->pluck('id')->toArray();
                }
                $allFilterCatIds = array_unique(array_merge([$filterCatId], $childCatIds, $grandChildCatIds));
                $query->whereIn('products.category_id', $allFilterCatIds);
            }
            if ($request->filled('stock_status')) {
                if ($request->stock_status === 'out_of_stock') {
                    $query->where('products.stock_qty', '<=', 0);
                } elseif ($request->stock_status === 'low_stock') {
                    $query->whereBetween('products.stock_qty', [1, 9]);
                } elseif ($request->stock_status === 'in_stock') {
                    $query->where('products.stock_qty', '>=', 10);
                }
            }
            if ($request->filled('status') && $request->status !== 'all') {
                $query->where('products.is_active', $request->status === 'active' ? 1 : 0);
            }

            $adminId = (int) session('admin_id', 0);
            $rbac = app(PermissionService::class);
            $isSuperAdmin = $rbac->isSuperAdmin($adminId);
            $canToggle = $isSuperAdmin || $rbac->userCan($adminId, 'admin.products.toggle');
            $canEdit = $isSuperAdmin || $rbac->userCan($adminId, 'admin.products.update');
            $canDelete = $isSuperAdmin || $rbac->userCan($adminId, 'admin.products.delete');

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('product_details', function ($prod) {
                    $img = product_image_url($prod->main_image);
                    $title = e($prod->title);
                    $sku = e($prod->sku);
                    $tagHtml = !empty($prod->tag) ? '<span class="badge bg-danger text-white ms-1" style="font-size: 0.65rem;">' . e($prod->tag) . '</span>' : '';
                    $flashHtml = $prod->is_flash_deal ? '<span class="badge bg-warning text-dark ms-1" style="font-size: 0.65rem;"><i class="fa-solid fa-bolt"></i> Flash</span>' : '';
                    return '<div class="d-flex align-items-center gap-3">
                        <img src="' . $img . '" class="rounded-2 border" style="width: 44px; height: 44px; object-fit: cover; flex-shrink: 0;" onerror="this.onerror=null;this.src=\'' . asset('images/product-placeholder.svg') . '\';">
                        <div class="overflow-hidden">
                            <div class="fw-semibold small d-block text-truncate" title="' . $title . '" style="max-width: 260px;">' . $title . '</div>
                            <div class="d-flex align-items-center gap-1 mt-1">
                                <span class="text-muted" style="font-size: 0.72rem;">SKU: ' . $sku . '</span>
                                ' . $tagHtml . '
                                ' . $flashHtml . '
                            </div>
                        </div>
                    </div>';
                })
                ->addColumn('category', function ($prod) {
                    $catName = e($prod->category_name ?? 'Uncategorized');
                    return '<span class="badge bg-body-secondary text-body border">' . $catName . '</span>';
                })
                ->addColumn('price_formatted', function ($prod) {
                    $oldPriceHtml = ($prod->old_price && $prod->old_price > $prod->price) ? '<span class="text-muted text-decoration-line-through small d-block">৳ ' . number_format($prod->old_price, 0) . '</span>' : '';
                    return '<div class="fw-bold text-body fs-6">৳ ' . number_format($prod->price, 0) . '</div>' . $oldPriceHtml;
                })
                ->addColumn('inventory_badge', function ($prod) {
                    if ($prod->stock_qty <= 0) {
                        return '<span class="badge bg-danger text-white rounded-pill"><i class="fa-solid fa-circle-xmark me-1"></i>0 (Out of Stock)</span>';
                    } elseif ($prod->stock_qty < 10) {
                        return '<span class="badge bg-warning text-dark border border-warning rounded-pill"><i class="fa-solid fa-triangle-exclamation me-1"></i>' . $prod->stock_qty . ' Units (Low Stock)</span>';
                    }
                    return '<span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill"><i class="fa-solid fa-check me-1"></i>' . $prod->stock_qty . ' Units</span>';
                })
                ->addColumn('status_toggle', function ($prod) use ($canToggle) {
                    $checked = $prod->is_active ? 'checked' : '';
                    $disabled = $canToggle ? '' : 'disabled';
                    return '<div class="form-check form-switch m-0">
                        <input class="form-check-input" type="checkbox" role="switch" ' . $checked . ' ' . $disabled . ' ' . ($canToggle ? 'onchange="toggleProductActive(' . $prod->id . ')"' : '') . '>
                    </div>';
                })
                ->addColumn('actions', function ($prod) use ($canEdit, $canDelete) {
                    $previewUrl = route('product.show', $prod->slug);
                    $html = '<div class="d-inline-flex align-items-center gap-1">';
                    $html .= '<a href="' . $previewUrl . '" target="_blank" class="btn-action text-secondary" title="Preview on Storefront"><i class="fa-solid fa-arrow-up-right-from-square"></i></a>';
                    if ($canEdit) {
                        $html .= '<button type="button" class="btn-action text-info" onclick="duplicateProductAjax(' . $prod->id . ')" title="Duplicate / Clone Product"><i class="fa-solid fa-copy"></i></button>';
                        $html .= '<button type="button" class="btn-action text-primary" onclick="openEditProductModal(' . $prod->id . ')" title="Edit Product"><i class="fa-solid fa-pen-to-square"></i></button>';
                    }
                    if ($canDelete) {
                        $html .= '<button type="button" class="btn-action text-danger" onclick="deleteProductAjax(' . $prod->id . ')" title="Delete Product"><i class="fa-solid fa-trash"></i></button>';
                    }
                    $html .= '</div>';
                    return $html;
                })
                ->rawColumns(['product_details', 'category', 'price_formatted', 'inventory_badge', 'status_toggle', 'actions'])
                ->make(true);
        }

        $categories = DB::table('categories')->where('is_active', 1)->orderBy('sort_order')->get();

        return view('backend.products.index', compact('categories'));
    }

    public function json($id)
    {
        $product = DB::table('products')->where('id', $id)->first();
        if (!$product) {
            return response()->json(['success' => false, 'message' => 'Product not found.'], 404);
        }

        $variants = is_array($product->variants) ? $product->variants : json_decode($product->variants ?? '[]', true);
        if (is_string($variants)) {
            $variants = json_decode($variants, true) ?: [];
        }

        $specs = is_array($product->specifications) ? $product->specifications : json_decode($product->specifications ?? '[]', true);
        if (is_string($specs)) {
            $specs = json_decode($specs, true) ?: [];
        }

        $gallery = is_array($product->gallery_images) ? $product->gallery_images : json_decode($product->gallery_images ?? '[]', true);
        if (is_string($gallery)) {
            $gallery = json_decode($gallery, true) ?: [];
        }

        $product->main_image_url = product_image_url($product->main_image);

        return response()->json([
            'success' => true,
            'product' => $product,
            'variants' => $variants ?: [],
            'specifications' => $specs ?: [],
            'gallery_images' => $gallery ?: [],
            'gallery_text' => is_array($gallery) ? implode("\n", $gallery) : '',
        ]);
    }

    public function ajaxSave(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'price' => 'required|numeric|min:0',
            'cost_price' => 'nullable|numeric|min:0',
            'stock_qty' => 'required|integer|min:0',
            'main_image' => 'nullable|string|max:1000',
            'video_url' => 'nullable|string|max:1000',
            'main_image_file' => 'nullable|image|max:10240',
            'gallery_files.*' => 'nullable|image|max:10240',
            'var_image_file.*' => 'nullable|image|max:10240',
        ]);

        $id = $request->input('id');
        $isEdit = !empty($id);
        $existing = null;
        if (!$isEdit) {
            $slug = Str::slug($request->title);
            $originalSlug = $slug;
            $count = 1;
            while (DB::table('products')->where('slug', $slug)->exists()) {
                $slug = "{$originalSlug}-" . $count++;
            }
        } else {
            $existing = DB::table('products')->where('id', $id)->first();
            $slug = $existing ? $existing->slug : Str::slug($request->title);
        }

        $optimizer = app(ImageOptimizerService::class);

        $mainImage = $request->input('main_image') ?: 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e';
        if ($request->hasFile('main_image_file')) {
            $mainImage = $optimizer->convertProductImageToWebp($request->file('main_image_file'), 'products', 800, 85);
            if ($isEdit && $existing && !empty($existing->main_image) && $existing->main_image !== $mainImage) {
                if (!$this->isMediaInUse($existing->main_image, $id)) {
                    $optimizer->deleteMedia($existing->main_image);
                }
            }
        }

        $variants = $this->parseVariantsInput($request);

        $existingGallery = $request->input('existing_gallery', []);
        $galleryImages = is_array($existingGallery) ? array_values(array_filter($existingGallery)) : [];

        if ($request->hasFile('gallery_files')) {
            foreach ($request->file('gallery_files') as $gFile) {
                if ($gFile) {
                    $galleryImages[] = $optimizer->convertProductImageToWebp($gFile, 'products', 800, 85);
                }
            }
        }
        if (empty($galleryImages)) {
            $galleryImages = [$mainImage];
        }

        if ($isEdit && $existing && !empty($existing->gallery_images)) {
            $oldGalleryArr = is_string($existing->gallery_images)
                ? json_decode($existing->gallery_images, true)
                : (is_array($existing->gallery_images) ? $existing->gallery_images : []);
            if (is_array($oldGalleryArr)) {
                foreach ($oldGalleryArr as $oldImg) {
                    $oldUrl = is_string($oldImg) ? $oldImg : ($oldImg['image'] ?? null);
                    if (!empty($oldUrl) && !in_array($oldUrl, $galleryImages) && $oldUrl !== $mainImage) {
                        if (!$this->isMediaInUse($oldUrl, $id)) {
                            $optimizer->deleteMedia($oldUrl);
                        }
                    }
                }
            }
        }

        $specs = $this->parseSpecsInput($request);

        $data = [
            'category_id' => $request->category_id,
            'title' => $request->title,
            'sku' => $request->sku ?: 'ZB-' . strtoupper(Str::random(6)),
            'price' => (float) $request->price,
            'cost_price' => $request->filled('cost_price') ? (float) $request->cost_price : null,
            'old_price' => $request->old_price ? (float) $request->old_price : null,
            'stock_qty' => (int) $request->stock_qty,
            'tag' => $request->tag,
            'badge_type' => $request->badge_type ?: 'new',
            'is_flash_deal' => $request->has('is_flash_deal') && ($request->is_flash_deal == '1' || $request->is_flash_deal == 'on') ? 1 : 0,
            'is_featured' => $request->has('is_featured') && ($request->is_featured == '1' || $request->is_featured == 'on') ? 1 : 0,
            'is_free_shipping' => $request->has('is_free_shipping') && ($request->is_free_shipping == '1' || $request->is_free_shipping == 'on') ? 1 : 0,
            'is_active' => $request->has('is_active') && ($request->is_active == '1' || $request->is_active == 'on') ? 1 : 0,
            'main_image' => $mainImage,
            'video_url' => $request->filled('video_url') ? trim($request->video_url) : null,
            'gallery_images' => json_encode(array_values($galleryImages), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
            'variants' => json_encode($variants, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
            'specifications' => json_encode($specs, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
            'short_desc' => $request->short_desc,
            'description' => $request->description,
            'meta_title' => $request->meta_title ?: $request->title,
            'meta_description' => $request->meta_description ?: ($request->short_desc ?: $request->title),
            'meta_keywords' => $request->meta_keywords,
            'updated_at' => now(),
        ];

        if (!$isEdit) {
            $data['slug'] = $slug;
            $data['rating'] = $request->filled('rating') ? (float) $request->rating : 0.0;
            $data['reviews_count'] = $request->filled('reviews_count') ? (int) $request->reviews_count : 0;
            $data['created_at'] = now();
            $id = DB::table('products')->insertGetId($data);
        } else {
            if ($request->filled('rating')) {
                $data['rating'] = (float) $request->rating;
            }
            if ($request->filled('reviews_count')) {
                $data['reviews_count'] = (int) $request->reviews_count;
            }
            DB::table('products')->where('id', $id)->update($data);
        }

        FrontendCacheService::flushProducts((int) $id);

        $savedProduct = DB::table('products')
            ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
            ->select('products.*', 'categories.name as category_name')
            ->where('products.id', $id)
            ->first();

        return response()->json([
            'success' => true,
            'message' => $isEdit ? 'Product updated successfully.' : 'Product created successfully.',
            'product' => $savedProduct,
        ]);
    }

    public function duplicate($id)
    {
        $original = DB::table('products')->where('id', $id)->first();
        if (!$original) {
            return response()->json(['success' => false, 'message' => 'Product not found.'], 404);
        }

        $title = $original->title . ' (Copy)';
        $baseSlug = Str::slug($title);
        $slug = $baseSlug;
        $counter = 1;
        while (DB::table('products')->where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . $counter++;
        }

        $sku = 'ZB-' . strtoupper(Str::random(6));
        while (DB::table('products')->where('sku', $sku)->exists()) {
            $sku = 'ZB-' . strtoupper(Str::random(6));
        }

        $optimizer = app(ImageOptimizerService::class);
        $clonedMap = [];
        $cloneHelper = function (?string $url, string $folder = 'products') use (&$clonedMap, $optimizer) {
            if (empty($url) || !is_string($url)) {
                return $url;
            }
            if (isset($clonedMap[$url])) {
                return $clonedMap[$url];
            }
            $cloned = $optimizer->cloneMedia($url, $folder);
            $clonedMap[$url] = $cloned;
            return $cloned;
        };

        $clonedMainImage = $cloneHelper($original->main_image, 'products');

        $clonedGalleryJson = null;
        if (!empty($original->gallery_images)) {
            $galleryArr = is_string($original->gallery_images)
                ? json_decode($original->gallery_images, true)
                : (is_array($original->gallery_images) ? $original->gallery_images : []);
            if (is_array($galleryArr)) {
                $newGallery = [];
                foreach ($galleryArr as $gItem) {
                    if (is_string($gItem) && !empty(trim($gItem))) {
                        $newGallery[] = $cloneHelper(trim($gItem), 'products');
                    } elseif (is_array($gItem) && isset($gItem['image']) && is_string($gItem['image'])) {
                        $gItem['image'] = $cloneHelper($gItem['image'], 'products');
                        $newGallery[] = $gItem;
                    }
                }
                $clonedGalleryJson = json_encode(array_values($newGallery), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            }
        }

        $clonedVariantsJson = null;
        if (!empty($original->variants)) {
            $variantsArr = is_string($original->variants)
                ? json_decode($original->variants, true)
                : (is_array($original->variants) ? $original->variants : []);
            if (is_array($variantsArr)) {
                $newVariants = [];
                foreach ($variantsArr as $vItem) {
                    if (is_array($vItem)) {
                        if (!empty($vItem['image']) && is_string($vItem['image'])) {
                            $vItem['image'] = $cloneHelper($vItem['image'], 'products/variants');
                        }
                        $newVariants[] = $vItem;
                    } else {
                        $newVariants[] = $vItem;
                    }
                }
                $clonedVariantsJson = json_encode($newVariants, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            }
        }

        $data = (array) $original;
        unset($data['id']);
        $data['title'] = $title;
        $data['slug'] = $slug;
        $data['sku'] = $sku;
        $data['main_image'] = $clonedMainImage;
        $data['gallery_images'] = $clonedGalleryJson ?? $original->gallery_images;
        $data['variants'] = $clonedVariantsJson ?? $original->variants;
        $data['is_active'] = 1;
        $data['created_at'] = now();
        $data['updated_at'] = now();

        $newId = DB::table('products')->insertGetId($data);

        FrontendCacheService::flushProducts((int) $newId);

        return response()->json([
            'success' => true,
            'message' => 'Product duplicated successfully!',
            'id' => $newId,
        ]);
    }

    public function create()
    {
        $categories = DB::table('categories')->where('is_active', 1)->orderBy('sort_order')->get();
        return view('backend.products.form', compact('categories'));
    }

    public function store(Request $request)
    {
        $response = $this->ajaxSave($request);
        if ($request->ajax() || $request->wantsJson()) {
            return $response;
        }
        return redirect()->route('admin.products.index')->with('success', 'Product created successfully.');
    }

    public function edit($id)
    {
        $product = DB::table('products')->where('id', $id)->first();
        if (!$product) {
            return redirect()->route('admin.products.index')->with('error', 'Product not found.');
        }

        $categories = DB::table('categories')->where('is_active', 1)->orderBy('sort_order')->get();
        return view('backend.products.form', compact('product', 'categories'));
    }

    public function update(Request $request, $id)
    {
        $request->merge(['id' => $id]);
        $response = $this->ajaxSave($request);
        if ($request->ajax() || $request->wantsJson()) {
            return $response;
        }
        return redirect()->route('admin.products.index')->with('success', 'Product updated successfully.');
    }

    public function toggle($id)
    {
        $product = DB::table('products')->where('id', $id)->first();
        if (!$product) {
            return response()->json(['success' => false, 'message' => 'Product not found.'], 404);
        }

        $newStatus = $product->is_active ? 0 : 1;
        DB::table('products')->where('id', $id)->update([
            'is_active' => $newStatus,
            'updated_at' => now(),
        ]);
        FrontendCacheService::flushProducts((int) $id);

        return response()->json([
            'success' => true,
            'is_active' => $newStatus,
            'message' => $newStatus ? 'Product is now active.' : 'Product is now inactive.'
        ]);
    }

    public function delete($id)
    {
        $product = DB::table('products')->where('id', $id)->first();
        if ($product) {
            $optimizer = app(ImageOptimizerService::class);
            if (!empty($product->main_image) && !$this->isMediaInUse($product->main_image, $id)) {
                $optimizer->deleteMedia($product->main_image);
            }
            if (!empty($product->gallery_images)) {
                $gallery = is_string($product->gallery_images) ? json_decode($product->gallery_images, true) : (is_array($product->gallery_images) ? $product->gallery_images : []);
                if (is_array($gallery)) {
                    foreach ($gallery as $gImg) {
                        $gUrl = is_string($gImg) ? $gImg : ($gImg['image'] ?? null);
                        if (!empty($gUrl) && !$this->isMediaInUse($gUrl, $id)) {
                            $optimizer->deleteMedia($gUrl);
                        }
                    }
                }
            }
            if (!empty($product->variants)) {
                $variants = is_string($product->variants) ? json_decode($product->variants, true) : (is_array($product->variants) ? $product->variants : []);
                if (is_array($variants)) {
                    foreach ($variants as $v) {
                        if (!empty($v['image']) && is_string($v['image']) && !$this->isMediaInUse($v['image'], $id)) {
                            $optimizer->deleteMedia($v['image']);
                        }
                    }
                }
            }
            DB::table('products')->where('id', $id)->delete();
            FrontendCacheService::flushProducts((int) $id);
        }

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Product removed successfully.',
            ]);
        }

        return redirect()->route('admin.products.index')->with('success', 'Product removed successfully.');
    }

    private function isMediaInUse(?string $url, int|string|null $excludeProductId = null): bool
    {
        if (empty($url) || !is_string($url) || !str_starts_with($url, '/storage/')) {
            return false;
        }

        $query = DB::table('products');
        if (!empty($excludeProductId)) {
            $query->where('id', '!=', $excludeProductId);
        }

        return $query->where(function ($q) use ($url) {
            $q->where('main_image', $url)
                ->orWhere('gallery_images', 'like', '%' . $url . '%')
                ->orWhere('variants', 'like', '%' . $url . '%');
        })->exists();
    }

    public function ajaxDelete($id)
    {
        return $this->delete($id);
    }

    public function exportCsv(ProductImportExportService $importExportService)
    {
        $csv = $importExportService->exportProductsCsv();
        $filename = 'Products_Catalog_' . date('Y-m-d_H-i') . '.csv';

        return response($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    public function importCsv(Request $request, ProductImportExportService $importExportService)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:5120'
        ]);

        $file = $request->file('csv_file');
        $result = $importExportService->importProductsCsv($file->getRealPath());
        if (!empty($result['success'])) {
            FrontendCacheService::flushProducts();
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json($result);
        }

        return back()->with($result['success'] ? 'success' : 'error', $result['message']);
    }

    private function parseVariantsInput(Request $request)
    {
        $variantNames = $request->input('var_name', []);
        $variantPrices = $request->input('var_price', []);
        $variantImages = $request->input('var_image', []);
        $variantStocks = $request->input('var_stock', []);
        $variantFiles = $request->file('var_image_file', []);

        $optimizer = app(ImageOptimizerService::class);

        $variants = [];
        if (is_array($variantNames)) {
            foreach ($variantNames as $idx => $name) {
                $name = trim($name);
                if (!empty($name)) {
                    $price = isset($variantPrices[$idx]) && is_numeric($variantPrices[$idx]) ? (float) $variantPrices[$idx] : (float) $request->price;

                    $image = isset($variantImages[$idx]) && !empty(trim($variantImages[$idx])) ? trim($variantImages[$idx]) : $request->main_image;
                    if (isset($variantFiles[$idx]) && $variantFiles[$idx]) {
                        $image = $optimizer->convertProductImageToWebp($variantFiles[$idx], 'products/variants', 800, 85);
                    }

                    $stock = isset($variantStocks[$idx]) && is_numeric($variantStocks[$idx]) ? (int) $variantStocks[$idx] : (int) $request->stock_qty;

                    $variants[] = [
                        'name' => $name,
                        'price' => $price,
                        'image' => $image,
                        'stock' => $stock,
                    ];
                }
            }
        }
        return $variants;
    }

    private function parseSpecsInput(Request $request)
    {
        $specKeys = $request->input('spec_key', []);
        $specVals = $request->input('spec_val', []);

        $specs = [];
        if (is_array($specKeys) && is_array($specVals)) {
            foreach ($specKeys as $idx => $k) {
                $k = trim($k);
                $v = isset($specVals[$idx]) ? trim($specVals[$idx]) : '';
                if (!empty($k) && !empty($v)) {
                    $specs[$k] = $v;
                }
            }
        }
        return $specs;
    }

    public function aiGenerator(Request $request)
    {
        $rawCategories = DB::table('categories')->where('is_active', 1)->orderBy('sort_order')->orderBy('name')->get();
        $categories = $rawCategories->map(function ($cat) use ($rawCategories) {
            $names = [$cat->name];
            $parentId = $cat->parent_id;
            $guard = 0;
            while ($parentId && $guard < 5) {
                $guard++;
                $parent = $rawCategories->firstWhere('id', $parentId);
                if ($parent) {
                    array_unshift($names, $parent->name);
                    $parentId = $parent->parent_id;
                } else {
                    break;
                }
            }
            $cat->hierarchy_name = implode(' > ', $names);
            return $cat;
        })->sortBy('hierarchy_name')->values();

        return view('backend.products.ai_generator', compact('categories'));
    }

    public function findOrCreateCategoryHierarchy(string $parentName, string $subName, string $childName): array
    {
        $parentName = trim($parentName) !== '' ? trim($parentName) : 'General';
        $subName = trim($subName) !== '' ? trim($subName) : $parentName;
        $childName = trim($childName) !== '' ? trim($childName) : $subName;

        if ($parentName === $subName && $subName === $childName) {
            $existing = $this->findSimilarCategory($childName);
            if ($existing) {
                $child = $existing;
                $sub = $child->parent_id ? (DB::table('categories')->where('id', $child->parent_id)->first() ?: $child) : $child;
                $parent = ($sub && $sub->parent_id) ? (DB::table('categories')->where('id', $sub->parent_id)->first() ?: $sub) : $sub;

                return [
                    'parent' => $parent,
                    'sub' => $sub,
                    'child' => $child,
                    'child_category_id' => $child->id,
                    'already_existed' => true,
                ];
            }
        }

        $existingChild = $this->findSimilarCategory($childName);
        if ($existingChild && $existingChild->parent_id) {
            $sub = DB::table('categories')->where('id', $existingChild->parent_id)->first();
            if ($sub) {
                $parent = $sub->parent_id ? (DB::table('categories')->where('id', $sub->parent_id)->first() ?: $sub) : $sub;
                return [
                    'parent' => $parent,
                    'sub' => $sub,
                    'child' => $existingChild,
                    'child_category_id' => $existingChild->id,
                    'already_existed' => true,
                ];
            }
        }

        $parentCreated = false;
        $subCreated = false;
        $childCreated = false;

        $parent = $this->findOrCreateCategoryTier($parentName, null, 'fa-layer-group', $parentCreated);
        $sub = $this->findOrCreateCategoryTier($subName, $parent->id, 'fa-folder', $subCreated);
        $child = $this->findOrCreateCategoryTier($childName, $sub->id, 'fa-tag', $childCreated);

        Cache::forget('home_payload_v1');
        Cache::forget('site_global_navigation_data');

        return [
            'parent' => $parent,
            'sub' => $sub,
            'child' => $child,
            'child_category_id' => $child->id,
            'already_existed' => (!$parentCreated && !$subCreated && !$childCreated),
        ];
    }

    private function findOrCreateCategoryTier(string $name, ?int $parentId = null, string $defaultIcon = 'fa-layer-group', bool &$wasCreated = false): object
    {
        $name = trim($name);
        if ($name === '') {
            $name = 'General';
        }

        $query = DB::table('categories');
        if ($parentId === null) {
            $query->whereNull('parent_id');
        } else {
            $query->where('parent_id', $parentId);
        }

        $exact = $query->whereRaw('LOWER(name) = ?', [strtolower($name)])->first();
        if ($exact) {
            $wasCreated = false;
            return $exact;
        }

        $candidatesQuery = DB::table('categories');
        if ($parentId === null) {
            $candidatesQuery->whereNull('parent_id');
        } else {
            $candidatesQuery->where('parent_id', $parentId);
        }
        $candidates = $candidatesQuery->get();

        $cleanTarget = preg_replace('/[^a-z0-9]/', '', strtolower($name));
        if ($cleanTarget !== '') {
            foreach ($candidates as $cand) {
                $cleanCand = preg_replace('/[^a-z0-9]/', '', strtolower($cand->name));
                if ($cleanCand === $cleanTarget) {
                    $wasCreated = false;
                    return $cand;
                }
            }
        }

        foreach ($candidates as $cand) {
            $lowerCand = strtolower($cand->name);
            $lowerTarget = strtolower($name);
            if (strlen($lowerCand) >= 4 && strlen($lowerTarget) >= 4) {
                if ($lowerCand === $lowerTarget || str_contains($lowerTarget, $lowerCand) || str_contains($lowerCand, $lowerTarget)) {
                    $wasCreated = false;
                    return $cand;
                }
            }
        }

        $bestMatch = null;
        $highestPercent = 0;
        foreach ($candidates as $cand) {
            similar_text(strtolower($cand->name), strtolower($name), $percent);
            if ($percent >= 75 && $percent > $highestPercent) {
                $highestPercent = $percent;
                $bestMatch = $cand;
            }
        }
        if ($bestMatch) {
            $wasCreated = false;
            return $bestMatch;
        }

        $baseSlug = Str::slug($name);
        if (empty($baseSlug)) {
            $baseSlug = 'cat-' . Str::lower(Str::random(6));
        }

        $slug = $baseSlug;
        $count = 1;
        while (DB::table('categories')->where('slug', $slug)->exists()) {
            $slug = "{$baseSlug}-{$count}";
            $count++;
        }

        $sortQuery = DB::table('categories');
        if ($parentId === null) {
            $sortQuery->whereNull('parent_id');
        } else {
            $sortQuery->where('parent_id', $parentId);
        }
        $maxSort = $sortQuery->max('sort_order') ?? 0;

        $newId = DB::table('categories')->insertGetId([
            'parent_id' => $parentId,
            'name' => $name,
            'name_bn' => $name,
            'slug' => $slug,
            'icon' => $defaultIcon,
            'sort_order' => ((int) $maxSort) + 1,
            'is_active' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $wasCreated = true;
        return DB::table('categories')->where('id', $newId)->first();
    }

    public function aiGenerateDetails(Request $request, AiService $aiService)
    {
        $validated = validator($request->all(), [
            'name' => 'nullable|string|max:250',
            'hint' => 'nullable|string|max:500',
            'category' => 'nullable|string|max:150',
            'specs' => 'nullable|string',
            'extra' => 'nullable|string',
            'language' => 'nullable|string|max:50',
            'image_url' => 'nullable|string',
            'image_base64' => 'nullable|string',
            'image_mime' => 'nullable|string',
            'featured_image' => 'nullable|image|max:10240',
            'gallery_images' => 'nullable|array|max:10',
            'gallery_images.*' => 'nullable|image|max:10240',
            'gallery_base64' => 'nullable|array|max:10',
            'gallery_urls' => 'nullable|array|max:10',
        ])->validate();

        $optimizer = app(ImageOptimizerService::class);
        $imagesForAi = [];
        $savedMainImage = null;
        $savedGalleryImages = [];

        if ($request->hasFile('featured_image')) {
            $fFile = $request->file('featured_image');
            if ($fFile && $fFile->isValid()) {
                $savedMainImage = $optimizer->convertProductImageToWebp($fFile, 'products', 800, 85);
                $diskPath = public_path(ltrim($savedMainImage, '/'));
                if (file_exists($diskPath)) {
                    $imagesForAi[] = [
                        'mime_type' => 'image/webp',
                        'data' => base64_encode(file_get_contents($diskPath)),
                        'file_path' => $diskPath,
                    ];
                } else {
                    $imagesForAi[] = [
                        'mime_type' => $fFile->getMimeType() ?: 'image/jpeg',
                        'data' => base64_encode(file_get_contents($fFile->getRealPath())),
                        'file_path' => $fFile->getRealPath(),
                    ];
                }

                if (empty($validated['hint']) && empty($validated['name'])) {
                    $filename = pathinfo($fFile->getClientOriginalName(), PATHINFO_FILENAME);
                    if ($filename && !in_array(strtolower($filename), ['image', 'photo', 'product', 'upload', 'screenshot', 'file'])) {
                        $validated['hint'] = trim(str_replace(['-', '_'], ' ', $filename));
                    }
                }
            }
        }

        if ($request->hasFile('gallery_images')) {
            foreach ($request->file('gallery_images') as $gFile) {
                if ($gFile && $gFile->isValid()) {
                    $savedGallery = $optimizer->convertProductImageToWebp($gFile, 'products', 800, 85);
                    $savedGalleryImages[] = $savedGallery;
                    $gDiskPath = public_path(ltrim($savedGallery, '/'));
                    if (file_exists($gDiskPath)) {
                        $imagesForAi[] = [
                            'mime_type' => 'image/webp',
                            'data' => base64_encode(file_get_contents($gDiskPath)),
                            'file_path' => $gDiskPath,
                        ];
                    } else {
                        $imagesForAi[] = [
                            'mime_type' => $gFile->getMimeType() ?: 'image/jpeg',
                            'data' => base64_encode(file_get_contents($gFile->getRealPath())),
                            'file_path' => $gFile->getRealPath(),
                        ];
                    }
                }
            }
        }

        if (!empty($validated['image_base64']) && !empty($validated['image_mime'])) {
            $imagesForAi[] = [
                'mime_type' => $validated['image_mime'],
                'data' => $validated['image_base64'],
            ];
        }

        $aiPayload = $validated;
        if (!empty($imagesForAi)) {
            $aiPayload['images'] = $imagesForAi;
        }

        if (empty($aiPayload['name']) && !empty($aiPayload['hint'])) {
            $aiPayload['name'] = $aiPayload['hint'];
        }

        $result = $aiService->generateProductDetails($aiPayload);

        if (!empty($result['success']) && !empty($result['data'])) {
            if ($savedMainImage) {
                $result['data']['main_image'] = $savedMainImage;
            }
            if (!empty($savedGalleryImages)) {
                $result['data']['gallery_images'] = $savedGalleryImages;
            }

            $parentCat = $result['data']['parent_category'] ?? null;
            $subCat = $result['data']['sub_category'] ?? null;
            $childCat = $result['data']['child_category'] ?? ($result['data']['suggested_category'] ?? ($result['data']['category_suggestion'] ?? null));
            $prodTitle = $result['data']['title'] ?? ($aiPayload['name'] ?? null);

            $matchedExisting = $this->findMostRelatedExistingCategory((string) $childCat, (string) $parentCat, (string) $subCat, (string) $prodTitle);

            if ($matchedExisting) {
                $hierarchyName = $this->getCategoryHierarchyName((int) $matchedExisting->id);
                $result['data']['child_category_id'] = $matchedExisting->id;
                $result['data']['category_id'] = $matchedExisting->id;
                $result['data']['matched_category'] = [
                    'id' => $matchedExisting->id,
                    'name' => $matchedExisting->name,
                    'hierarchy_name' => $hierarchyName ?: $matchedExisting->name,
                    'slug' => $matchedExisting->slug,
                ];
            } elseif (!empty($childCat)) {
                $hierarchy = $this->findOrCreateCategoryHierarchy(
                    $parentCat ?: 'General',
                    $subCat ?: ($parentCat ?: 'General'),
                    $childCat
                );

                $result['data']['hierarchy'] = [
                    'parent' => [
                        'id' => $hierarchy['parent']->id,
                        'name' => $hierarchy['parent']->name,
                        'slug' => $hierarchy['parent']->slug,
                    ],
                    'sub' => [
                        'id' => $hierarchy['sub']->id,
                        'name' => $hierarchy['sub']->name,
                        'slug' => $hierarchy['sub']->slug,
                    ],
                    'child' => [
                        'id' => $hierarchy['child']->id,
                        'name' => $hierarchy['child']->name,
                        'slug' => $hierarchy['child']->slug,
                    ],
                ];
                $result['data']['child_category_id'] = $hierarchy['child_category_id'];
                $result['data']['category_id'] = $hierarchy['child_category_id'];
                $result['data']['matched_category'] = [
                    'id' => $hierarchy['child']->id,
                    'name' => $hierarchy['child']->name,
                    'hierarchy_name' => $hierarchy['parent']->name . ' > ' . $hierarchy['sub']->name . ' > ' . $hierarchy['child']->name,
                    'slug' => $hierarchy['child']->slug,
                ];
            }
        }

        return response()->json($result);
    }

    public function aiGenerateSeoDescription(Request $request, AiService $aiService)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:250',
            'category_id' => 'nullable',
            'category' => 'nullable|string|max:150',
            'price' => 'nullable|numeric',
            'old_price' => 'nullable|numeric',
            'tag' => 'nullable|string|max:100',
            'specs' => 'nullable',
            'language' => 'nullable|string|max:50',
        ]);

        $categoryName = $validated['category'] ?? '';
        if (empty($categoryName) && !empty($validated['category_id'])) {
            $cat = DB::table('categories')->where('id', $validated['category_id'])->first();
            if ($cat) {
                $categoryName = $cat->name;
            }
        }

        $specsInput = $request->input('specs');
        $specsArray = [];
        if (is_array($specsInput)) {
            $specsArray = $specsInput;
        } elseif (is_string($specsInput) && !empty(trim($specsInput))) {
            $decodedSpecs = json_decode($specsInput, true);
            if (is_array($decodedSpecs)) {
                $specsArray = $decodedSpecs;
            } else {
                $specsArray = $specsInput;
            }
        }

        $payload = [
            'title' => $validated['title'],
            'category' => $categoryName,
            'price' => $validated['price'] ?? null,
            'old_price' => $validated['old_price'] ?? null,
            'tag' => $validated['tag'] ?? null,
            'specs' => $specsArray,
            'language' => $validated['language'] ?? 'mixed',
        ];

        $result = $aiService->generateSeoAndDescription($payload);

        return response()->json($result);
    }

    public function aiCreateCategory(Request $request)
    {
        $validated = $request->validate([
            'parent_category' => 'nullable|string|max:150',
            'sub_category' => 'nullable|string|max:150',
            'child_category' => 'nullable|string|max:150',
            'name' => 'nullable|string|max:255',
        ]);

        $parentName = trim($validated['parent_category'] ?? '');
        $subName = trim($validated['sub_category'] ?? '');
        $childName = trim($validated['child_category'] ?? '');

        if ($parentName === '' && $subName === '' && $childName === '') {
            $raw = trim($validated['name'] ?? '');
            if ($raw === '') {
                return response()->json([
                    'success' => false,
                    'message' => 'Category name cannot be empty.',
                ], 422);
            }
            if (str_contains($raw, '>')) {
                $parts = array_map('trim', explode('>', $raw));
                $parentName = $parts[0] ?? 'General';
                $subName = $parts[1] ?? ($parts[0] ?? 'General');
                $childName = $parts[2] ?? ($parts[1] ?? $parts[0]);
            } else {
                $parentName = $raw;
                $subName = $raw;
                $childName = $raw;
            }
        } else {
            if ($parentName === '') $parentName = 'General';
            if ($subName === '') $subName = $parentName;
            if ($childName === '') $childName = $subName;
        }

        $hierarchy = $this->findOrCreateCategoryHierarchy($parentName, $subName, $childName);

        return response()->json([
            'success' => true,
            'already_existed' => $hierarchy['already_existed'],
            'parent' => $hierarchy['parent'],
            'sub' => $hierarchy['sub'],
            'child' => $hierarchy['child'],
            'child_category_id' => $hierarchy['child_category_id'],
            'category' => [
                'id' => $hierarchy['child']->id,
                'name' => $hierarchy['child']->name,
                'hierarchy_name' => $hierarchy['parent']->name . ' > ' . $hierarchy['sub']->name . ' > ' . $hierarchy['child']->name,
                'slug' => $hierarchy['child']->slug,
            ],
            'message' => $hierarchy['already_existed'] ? "Category '{$hierarchy['child']->name}' already exists and was selected." : "Category '{$hierarchy['child']->name}' created and selected successfully!",
        ]);
    }

    public function getCategoryHierarchyName(int $categoryId): string
    {
        $names = [];
        $currentId = $categoryId;
        $visited = [];
        while ($currentId && !in_array($currentId, $visited)) {
            $visited[] = $currentId;
            $cat = DB::table('categories')->where('id', $currentId)->first();
            if (!$cat) {
                break;
            }
            array_unshift($names, $cat->name);
            $currentId = $cat->parent_id ? (int) $cat->parent_id : null;
        }
        return !empty($names) ? implode(' > ', $names) : '';
    }

    public function findMostRelatedExistingCategory(string $targetCategory, ?string $parentHint = null, ?string $subHint = null, ?string $titleHint = null): ?object
    {
        $categories = DB::table('categories')->where('is_active', 1)->select('id', 'name', 'name_bn', 'slug', 'parent_id')->get();
        if ($categories->isEmpty()) {
            return null;
        }

        $cleanTarget = strtolower(trim($targetCategory));
        $cleanParent = strtolower(trim((string) $parentHint));
        $cleanSub = strtolower(trim((string) $subHint));
        $cleanTitle = strtolower(trim((string) $titleHint));

        if ($cleanTarget !== '') {
            $exact = $categories->first(function ($c) use ($cleanTarget) {
                return strtolower($c->name) === $cleanTarget || $c->slug === Str::slug($cleanTarget);
            });
            if ($exact) {
                return $exact;
            }
        }

        $extractWords = function (string $text) {
            $words = preg_split('/[^a-z0-9]+/i', strtolower($text), -1, PREG_SPLIT_NO_EMPTY);
            $stopWords = ['the', 'and', 'for', 'with', 'in', 'of', 'a', 'an', 'to', 'by', 'pro', 'max', 'plus', 'ultra', 'new', 'series'];
            return array_values(array_filter($words, function ($w) use ($stopWords) {
                return strlen($w) >= 3 && !in_array($w, $stopWords);
            }));
        };

        $targetWords = $extractWords($cleanTarget);
        $hintWords = array_unique(array_merge(
            $targetWords,
            $extractWords($cleanSub),
            $extractWords($cleanParent),
            $extractWords($cleanTitle)
        ));

        $bestMatch = null;
        $highestScore = 0;

        foreach ($categories as $cat) {
            $catNameLower = strtolower($cat->name);
            $catWords = $extractWords($catNameLower);
            $catSlugWords = $extractWords(str_replace('-', ' ', $cat->slug));
            $allCatWords = array_unique(array_merge($catWords, $catSlugWords));

            $score = 0;

            if ($cleanTarget !== '') {
                if ($catNameLower === $cleanTarget) {
                    $score += 100;
                } elseif (str_contains($cleanTarget, $catNameLower) || str_contains($catNameLower, $cleanTarget)) {
                    $score += 65;
                }
                similar_text($catNameLower, $cleanTarget, $simPercent);
                $score += ($simPercent * 0.4);
            }

            foreach ($allCatWords as $cw) {
                $cwSingular = rtrim($cw, 's');
                foreach ($targetWords as $tw) {
                    $twSingular = rtrim($tw, 's');
                    if ($cw === $tw || $cwSingular === $twSingular) {
                        $score += 45;
                    } elseif (str_contains($tw, $cw) || str_contains($cw, $tw)) {
                        $score += 25;
                    }
                }
                foreach ($hintWords as $hw) {
                    $hwSingular = rtrim($hw, 's');
                    if ($cw === $hw || $cwSingular === $hwSingular) {
                        $score += 25;
                    } elseif (str_contains($hw, $cw) || str_contains($cw, $hw)) {
                        $score += 15;
                    }
                }
            }

            if ($cat->parent_id !== null) {
                $score += 10;
            }

            if ($score > $highestScore) {
                $highestScore = $score;
                $bestMatch = $cat;
            }
        }

        if ($bestMatch && $highestScore >= 20) {
            return $bestMatch;
        }

        return $categories->first();
    }

    public function findSimilarCategory(string $name): ?object
    {
        return $this->findMostRelatedExistingCategory($name);
    }

    public function aiSaveProduct(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'category_id' => 'nullable|integer',
            'child_category_id' => 'nullable|integer',
            'parent_category' => 'nullable|string|max:150',
            'sub_category' => 'nullable|string|max:150',
            'child_category' => 'nullable|string|max:150',
            'price' => 'required|numeric|min:0',
            'old_price' => 'nullable|numeric|min:0',
            'cost_price' => 'nullable|numeric|min:0',
            'stock_qty' => 'required|integer|min:0',
            'short_desc' => 'nullable|string',
            'description' => 'nullable|string',
            'main_image' => 'nullable|string',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
            'meta_keywords' => 'nullable|string',
            'tag' => 'nullable|string|max:50',
            'specifications' => 'nullable',
            'sku' => 'nullable|string|max:100',
            'slug' => 'nullable|string|max:255',
            'variants' => 'nullable',
            'gallery_images' => 'nullable',
            'is_featured' => 'nullable',
            'is_active' => 'nullable',
            'is_flash_deal' => 'nullable',
            'is_free_shipping' => 'nullable',
        ]);

        $childCategoryId = $validated['child_category_id'] ?? ($validated['category_id'] ?? null);

        if (!$childCategoryId && (!empty($validated['parent_category']) || !empty($validated['child_category']))) {
            $hierarchy = $this->findOrCreateCategoryHierarchy(
                $validated['parent_category'] ?? 'General',
                $validated['sub_category'] ?? ($validated['parent_category'] ?? 'General'),
                $validated['child_category'] ?? ($validated['sub_category'] ?? 'General')
            );
            $childCategoryId = $hierarchy['child_category_id'];
        }

        if (!$childCategoryId) {
            $defaultCat = DB::table('categories')->whereNotNull('parent_id')->first() ?: DB::table('categories')->first();
            $childCategoryId = $defaultCat ? $defaultCat->id : 1;
        }

        $slug = !empty($validated['slug']) ? Str::slug($validated['slug']) : Str::slug($validated['title']);
        $originalSlug = $slug;
        $count = 1;
        while (DB::table('products')->where('slug', $slug)->exists()) {
            $slug = "{$originalSlug}-{$count}";
            $count++;
        }

        $sku = !empty($validated['sku']) ? trim($validated['sku']) : ('SKU-' . strtoupper(Str::random(8)));
        $originalSku = $sku;
        $count = 1;
        while (DB::table('products')->where('sku', $sku)->exists()) {
            $sku = "{$originalSku}-{$count}";
            $count++;
        }

        $specsJson = null;
        if (!empty($validated['specifications'])) {
            if (is_array($validated['specifications'])) {
                $specsJson = json_encode($validated['specifications'], JSON_UNESCAPED_UNICODE);
            } else {
                $specsJson = $validated['specifications'];
            }
        }

        $variantsJson = null;
        if (!empty($validated['variants'])) {
            if (is_array($validated['variants'])) {
                $variantsJson = json_encode($validated['variants'], JSON_UNESCAPED_UNICODE);
            } else {
                $variantsJson = $validated['variants'];
            }
        }

        $mainImage = !empty($validated['main_image']) ? trim($validated['main_image']) : asset('images/product-placeholder.svg');

        $galleryJson = null;
        if (!empty($validated['gallery_images'])) {
            if (is_array($validated['gallery_images'])) {
                $galleryJson = json_encode(array_values(array_filter($validated['gallery_images'])), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            } else {
                $galleryJson = $validated['gallery_images'];
            }
        }
        if (!$galleryJson && $mainImage) {
            $galleryJson = json_encode([$mainImage], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        }

        $isFeatured = $request->has('is_featured') ? (bool) $request->input('is_featured') : true;
        $isActive = $request->has('is_active') ? (bool) $request->input('is_active') : true;
        $isFlashDeal = (bool) $request->input('is_flash_deal', false);
        $isFreeShipping = (bool) $request->input('is_free_shipping', false);

        $productId = DB::table('products')->insertGetId([
            'category_id' => $childCategoryId,
            'title' => $validated['title'],
            'slug' => $slug,
            'sku' => $sku,
            'price' => $validated['price'],
            'old_price' => $validated['old_price'] ?? null,
            'cost_price' => $validated['cost_price'] ?? null,
            'stock_qty' => $validated['stock_qty'],
            'short_desc' => $validated['short_desc'] ?? null,
            'description' => $validated['description'] ?? null,
            'main_image' => $mainImage,
            'gallery_images' => $galleryJson,
            'meta_title' => $validated['meta_title'] ?? $validated['title'],
            'meta_description' => $validated['meta_description'] ?? ($validated['short_desc'] ?? null),
            'meta_keywords' => $validated['meta_keywords'] ?? null,
            'tag' => $validated['tag'] ?? null,
            'specifications' => $specsJson,
            'variants' => $variantsJson,
            'is_active' => $isActive ? 1 : 0,
            'is_featured' => $isFeatured ? 1 : 0,
            'is_flash_deal' => $isFlashDeal ? 1 : 0,
            'is_free_shipping' => $isFreeShipping ? 1 : 0,
            'rating' => 0.0,
            'reviews_count' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        FrontendCacheService::flushProducts((int) $productId);

        return response()->json([
            'success' => true,
            'message' => 'Product successfully created and published to Catalog!',
            'product_id' => $productId,
            'child_category_id' => $childCategoryId,
            'redirect_url' => route('admin.products.index'),
        ]);
    }
}
