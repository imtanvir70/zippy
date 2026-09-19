<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Jobs\SendOrderEmailJob;
use App\Jobs\SendOrderSmsJob;
use App\Jobs\SendMetaCapiEventJob;
use App\Services\Ai\AiService;
use App\Services\Order\OrderNumberService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Exception;
use Throwable;

class ChatbotController extends Controller
{
    protected AiService $aiService;

    public function __construct(AiService $aiService)
    {
        $this->aiService = $aiService;
    }

    protected function determineTimelineStep(string $status): int
    {
        $status = strtolower(trim($status));
        if (in_array($status, ['delivered', 'completed'])) {
            return 4;
        }
        if (in_array($status, ['shipped', 'dispatched', 'in_transit', 'in transit', 'out_for_delivery'])) {
            return 3;
        }
        if (in_array($status, ['processing', 'confirmed', 'packed', 'packing'])) {
            return 2;
        }
        return 1;
    }

    protected function buildCourierTrackingUrl(?string $provider, ?string $trackingCode, string $orderNumber): ?string
    {
        if (empty($trackingCode) || $trackingCode === 'Pending' || $trackingCode === 'Processing') {
            return url('/order/track?order_number=' . urlencode($orderNumber));
        }

        $providerLower = strtolower($provider ?? '');
        if (str_contains($providerLower, 'steadfast')) {
            return 'https://steadfast.com.bd/t/' . urlencode($trackingCode);
        }
        if (str_contains($providerLower, 'pathao')) {
            return 'https://pathao.com/courier-tracking/?consignment_id=' . urlencode($trackingCode);
        }
        if (str_contains($providerLower, 'redx')) {
            return 'https://redx.com.bd/track-order?trackingId=' . urlencode($trackingCode);
        }

        return url('/order/track?order_number=' . urlencode($orderNumber));
    }

    protected function parseUserActivity(Request $request): array
    {
        $activity = $request->input('activity', []);
        if (!is_array($activity)) {
            $activity = [];
        }

        $pageType = trim((string) ($activity['page_type'] ?? 'general'));
        $currentUrl = trim((string) ($activity['current_url'] ?? ''));
        $pageTitle = trim((string) ($activity['page_title'] ?? ''));
        $cartCount = max(0, (int) ($activity['cart_count'] ?? 0));
        $recentlyViewed = is_array($activity['recently_viewed'] ?? null) ? $activity['recently_viewed'] : [];
        $currentProduct = is_array($activity['current_product'] ?? null) ? $activity['current_product'] : null;

        if (empty($currentProduct) && !empty($activity['product_slug'])) {
            $slug = trim((string) $activity['product_slug']);
            $dbProd = DB::table('products')->where('slug', $slug)->where('is_active', 1)->first();
            if ($dbProd) {
                $currentProduct = [
                    'id' => $dbProd->id,
                    'title' => $dbProd->title,
                    'slug' => $dbProd->slug,
                    'price' => (float) $dbProd->price,
                    'stock_qty' => (int) $dbProd->stock_qty,
                    'short_desc' => $dbProd->short_desc,
                    'specifications' => $dbProd->specifications,
                ];
            }
        } elseif (!empty($currentProduct) && !empty($currentProduct['slug']) && empty($currentProduct['id'])) {
            $dbProd = DB::table('products')->where('slug', $currentProduct['slug'])->where('is_active', 1)->first();
            if ($dbProd) {
                $currentProduct['id'] = $dbProd->id;
                $currentProduct['stock_qty'] = (int) $dbProd->stock_qty;
                $currentProduct['short_desc'] = $dbProd->short_desc;
                $currentProduct['specifications'] = $dbProd->specifications;
            }
        }

        return [
            'page_type' => $pageType,
            'current_url' => $currentUrl,
            'page_title' => $pageTitle,
            'cart_count' => $cartCount,
            'recently_viewed' => $recentlyViewed,
            'current_product' => $currentProduct,
        ];
    }

    protected function getActiveStockSummary(string $lang = 'bn'): array
    {
        $products = DB::table('products')
            ->where('is_active', 1)
            ->where('stock_qty', '>', 0)
            ->orderByDesc('is_featured')
            ->orderByDesc('id')
            ->get(['id', 'title', 'slug', 'price', 'category_id', 'stock_qty', 'tag', 'short_desc']);

        $categoryIds = $products->pluck('category_id')->filter()->unique()->toArray();
        $categories = empty($categoryIds) ? collect() : DB::table('categories')
            ->whereIn('id', $categoryIds)
            ->where('is_active', 1)
            ->get()
            ->keyBy('id');

        $detectedTypes = [];

        foreach ($products as $p) {
            $text = mb_strtolower($p->title . ' ' . $p->tag . ' ' . $p->short_desc);
            $cat = $categories->get($p->category_id);
            if ($cat) {
                $text .= ' ' . mb_strtolower($cat->name . ' ' . $cat->slug);
            }

            if (preg_match('/(watch|smartwatch|wearable|ঘড়ি|ঘড়ি|স্মার্টওয়াচ)/iu', $text)) {
                $detectedTypes['watch'] = [
                    'icon' => '⌚',
                    'label_bn' => 'স্মার্টওয়াচ',
                    'label_en' => 'Smartwatches',
                    'prompt_bn' => 'স্মার্টওয়াচ কালেকশন দেখান',
                    'prompt_en' => 'Show me smartwatches',
                ];
            } elseif (preg_match('/(earphone|headphone|earbud|tws|audio|sound|হেডফোন|ইয়ারফোন|ইয়ারবাড)/iu', $text)) {
                $detectedTypes['audio'] = [
                    'icon' => '🎧',
                    'label_bn' => 'ইয়ারফোন ও অডিও',
                    'label_en' => 'Earbuds & Audio',
                    'prompt_bn' => 'ইয়ারফোন ও অডিও গ্যাজেট দেখান',
                    'prompt_en' => 'Show me earbuds and audio gear',
                ];
            } elseif (preg_match('/(keyboard|mechanical|কীবোর্ড|কিবোর্ড)/iu', $text)) {
                $detectedTypes['keyboard'] = [
                    'icon' => '⌨️',
                    'label_bn' => 'মেকানিক্যাল কিবোর্ড',
                    'label_en' => 'Keyboards',
                    'prompt_bn' => 'মেকানিক্যাল কিবোর্ড দেখান',
                    'prompt_en' => 'Show me mechanical keyboards',
                ];
            } elseif (preg_match('/(mouse|gaming\s*mouse|মাউস)/iu', $text)) {
                $detectedTypes['mouse'] = [
                    'icon' => '🖱️',
                    'label_bn' => 'গেমিং মাউস',
                    'label_en' => 'Gaming Mice',
                    'prompt_bn' => 'গেমিং মাউস দেখান',
                    'prompt_en' => 'Show me gaming mice',
                ];
            } elseif (preg_match('/(phone|smartphone|mobile|ফোন|মোবাইল)/iu', $text)) {
                $detectedTypes['phone'] = [
                    'icon' => '📱',
                    'label_bn' => 'স্মার্টফোন',
                    'label_en' => 'Smartphones',
                    'prompt_bn' => 'স্মার্টফোন দেখান',
                    'prompt_en' => 'Show me smartphones',
                ];
            } elseif (preg_match('/(charger|power|cable|ক্যাবল|চার্জার)/iu', $text)) {
                $detectedTypes['charger'] = [
                    'icon' => '🔋',
                    'label_bn' => 'চার্জার ও পাওয়ার',
                    'label_en' => 'Power & Chargers',
                    'prompt_bn' => 'চার্জার ও পাওয়ার এক্সেসরিজ দেখান',
                    'prompt_en' => 'Show me chargers and power accessories',
                ];
            } else {
                $catName = $cat ? $cat->name : 'গ্যাজেট';
                $key = 'cat_' . ($cat ? $cat->id : 'gen');
                if (!isset($detectedTypes[$key])) {
                    $detectedTypes[$key] = [
                        'icon' => '✨',
                        'label_bn' => $catName,
                        'label_en' => $catName,
                        'prompt_bn' => "{$catName} কালেকশন দেখান",
                        'prompt_en' => "Show {$catName}",
                    ];
                }
            }
        }

        $typeChips = [];
        $typeNamesBn = [];
        $typeNamesEn = [];

        foreach ($detectedTypes as $t) {
            $typeChips[] = [
                'label' => $lang === 'en' ? "{$t['icon']} {$t['label_en']}" : "{$t['icon']} {$t['label_bn']}",
                'prompt' => $lang === 'en' ? $t['prompt_en'] : $t['prompt_bn'],
            ];
            $typeNamesBn[] = $t['label_bn'];
            $typeNamesEn[] = $t['label_en'];
        }

        $summaryBn = !empty($typeNamesBn) ? implode(', ', $typeNamesBn) : 'স্মার্ট গ্যাজেট';
        $summaryEn = !empty($typeNamesEn) ? implode(', ', $typeNamesEn) : 'smart gadgets';

        return [
            'products' => $products,
            'categories' => $categories,
            'chips' => $typeChips,
            'summary_bn' => $summaryBn,
            'summary_en' => $summaryEn,
        ];
    }

    protected function generateDynamicGreeting(array $activity, string $storeName, string $lang = 'bn'): array
    {
        $pageType = $activity['page_type'] ?? 'general';
        $currentProduct = $activity['current_product'] ?? null;
        $customerName = null;

        if (auth()->check()) {
            $customerName = explode(' ', trim(auth()->user()->name ?? ''))[0];
        }

        $lastOrder = null;
        if (auth()->check()) {
            $lastOrder = DB::table('orders')->where('user_id', auth()->id())->orderByDesc('id')->first();
        }

        $greeting = "";
        $chips = [];

        if ($lang === 'en') {
            if ($pageType === 'product' && !empty($currentProduct)) {
                $pTitle = $currentProduct['title'] ?? 'this item';
                $pPrice = !empty($currentProduct['price']) ? ' (৳' . number_format((float)$currentProduct['price'], 0) . ')' : '';
                $nameGreet = $customerName ? "Welcome {$customerName}! " : "Hello! 👋 ";
                $greeting = "{$nameGreet}I see you're looking at **{$pTitle}**{$pPrice}. Have any questions about its features, specifications, stock, or cash on delivery?";
                $chips = [
                    ['label' => '🛒 Order Directly', 'prompt' => "I want to order {$pTitle}"],
                    ['label' => '🔍 Specs & Features', 'prompt' => "Tell me the specifications of {$pTitle}"],
                    ['label' => '🚚 Delivery Fee & Time', 'prompt' => "What is the delivery fee and estimated time?"],
                    ['label' => '💵 Cash on Delivery', 'prompt' => 'Is Cash on Delivery available?'],
                ];
            } elseif ($pageType === 'cart' || $pageType === 'checkout') {
                $nameGreet = $customerName ? "Hello {$customerName}! 👋 " : "Hello! 👋 ";
                $greeting = "{$nameGreet}Need help with your cart or checkout? We deliver nationwide via Cash on Delivery!";
                $chips = [
                    ['label' => '🚚 Delivery Fee & Time', 'prompt' => 'What is the delivery charge and timeline?'],
                    ['label' => '💵 Cash on Delivery Info', 'prompt' => 'Do I need to pay any advance for COD?'],
                    ['label' => '🛡️ 7-Day Warranty', 'prompt' => 'What is the replacement warranty policy?'],
                    ['label' => '⚡ 1-Click Order Help', 'prompt' => 'Help me complete my order'],
                ];
            } elseif ($pageType === 'tracking') {
                $greeting = "Hello! 👋 Enter your order number (e.g. ZB-260305-1025) or mobile number to track your package live!";
                $chips = [
                    ['label' => '📦 Track My Order', 'prompt' => 'I want to track my order'],
                    ['label' => '🚚 Delivery Status', 'prompt' => 'Give me courier status update'],
                    ['label' => '💬 Customer Support', 'prompt' => 'I want to speak with support'],
                ];
            } elseif ($lastOrder) {
                $stockSummary = $this->getActiveStockSummary($lang);
                $catChip = !empty($stockSummary['chips']) ? $stockSummary['chips'][0] : null;
                $nameGreet = $customerName ? "Welcome {$customerName}! 👋 " : "Welcome! 👋 ";
                $greeting = "{$nameGreet}Would you like to track your recent order **#{$lastOrder->order_number}**, or are you exploring new gadgets today?";
                $chips = [
                    ['label' => "📦 Track Order #{$lastOrder->order_number}", 'prompt' => "track order {$lastOrder->order_number}"],
                    $catChip ?? ['label' => '✨ New Arrivals', 'prompt' => 'Show me new arrivals'],
                    ['label' => '🚚 Delivery Policy', 'prompt' => 'What are the delivery charges?'],
                ];
            } else {
                $stockSummary = $this->getActiveStockSummary($lang);
                $nameGreet = $customerName ? "Welcome {$customerName}! 👋 " : "Welcome to {$storeName}! 👋 ";
                $greeting = "{$nameGreet}I'm your personal shopping assistant. Looking for {$stockSummary['summary_en']}, or something within your budget? Let me know!";
                $chips = array_merge(
                    [
                        ['label' => '🚚 Delivery Charge', 'prompt' => 'What is the delivery fee?'],
                        ['label' => '💵 Cash on Delivery', 'prompt' => 'Is cash on delivery available?'],
                    ],
                    $stockSummary['chips'],
                    [
                        ['label' => '📦 Track Order', 'prompt' => 'I want to track my order'],
                    ]
                );
            }
        } else {
            if ($pageType === 'product' && !empty($currentProduct)) {
                $pTitle = $currentProduct['title'] ?? 'এই প্রোডাক্টটি';
                $pPrice = !empty($currentProduct['price']) ? ' (৳' . number_format((float)$currentProduct['price'], 0) . ')' : '';
                $nameGreet = $customerName ? "স্বাগতম {$customerName} ভাই! " : "হ্যালো ভাইয়া! 👋 ";
                $greeting = "{$nameGreet}দেখতে পাচ্ছি আপনি **{$pTitle}**{$pPrice} দেখছেন। এর ফিচার, স্পেসিফিকেশন, স্টক বা ক্যাশ অন ডেলিভারিতে অর্ডার করার বিষয়ে কোনো প্রশ্ন থাকলে আমাকে নির্দ্বিধায় বলতে পারেন!";
                $chips = [
                    ['label' => '🛒 সরাসরি অর্ডার করতে চাই', 'prompt' => "আমি {$pTitle} অর্ডার করতে চাই"],
                    ['label' => '🔍 ফিচার ও স্পেক্স কি?', 'prompt' => "{$pTitle} এর স্পেসিফিকেশন ও ফিচারগুলো বলুন"],
                    ['label' => '🚚 ডেলিভারি চার্জ ও সময়', 'prompt' => "এই প্রোডাক্টের ডেলিভারি চার্জ কত এবং কবে পাব?"],
                    ['label' => '💵 ক্যাশ অন ডেলিভারি আছে?', 'prompt' => 'ক্যাশ অন ডেলিভারিতে চেক করে নেয়া যাবে?'],
                ];
            } elseif ($pageType === 'cart' || $pageType === 'checkout') {
                $nameGreet = $customerName ? "হ্যালো {$customerName} ভাই! 👋 " : "হ্যালো ভাইয়া! 👋 ";
                $greeting = "{$nameGreet}আপনার কার্ট বা চেকআউট নিয়ে কি কোনো জিজ্ঞাসা আছে? সারা বাংলাদেশে ক্যাশ অন ডেলিভারিতে দ্রুত ডেলিভারি নিশ্চিত করতে আমি সাহায্য করতে প্রস্তুত!";
                $chips = [
                    ['label' => '🚚 ডেলিভারি চার্জ ও সময় কত?', 'prompt' => 'ডেলিভারি চার্জ কত এবং কত দিনে পাব?'],
                    ['label' => '💵 ক্যাশ অন ডেলিভারির নিয়ম', 'prompt' => 'ক্যাশ অন ডেলিভারিতে কি অগ্রিম টাকা দিতে হবে?'],
                    ['label' => '🛡️ ৭ দিনের রিপ্লেসমেন্ট ওয়ারেন্টি', 'prompt' => 'প্রোডাক্টে সমস্যা হলে ওয়ারেন্টি কি?'],
                    ['label' => '⚡ ১-ক্লিক অর্ডার হেল্প', 'prompt' => 'আমাকে অর্ডার সম্পন্ন করতে সাহায্য করুন'],
                ];
            } elseif ($pageType === 'tracking') {
                $greeting = "হ্যালো ভাইয়া! 👋 আপনার অর্ডারের বর্তমান অবস্থা ও লাইভ কুরিয়ার স্ট্যাটাস দেখতে আপনার অর্ডার কোড (যেমন: ZB-260305-1025) অথবা ফোন নম্বরটি এখানে দিন!";
                $chips = [
                    ['label' => '📦 অর্ডার ট্র্যাক করতে চাই', 'prompt' => 'আমার অর্ডার ট্র্যাক করতে চাই'],
                    ['label' => '🚚 কুরিয়ার ডেলিভারি আপডেট', 'prompt' => 'কুরিয়ার ডেলিভারি আপডেট দিন'],
                    ['label' => '💬 কাস্টমার সাপোর্ট হেল্প', 'prompt' => 'কাস্টমার কেয়ার প্রতিনিধির সাথে কথা বলতে চাই'],
                ];
            } elseif ($lastOrder) {
                $stockSummary = $this->getActiveStockSummary($lang);
                $catChip = !empty($stockSummary['chips']) ? $stockSummary['chips'][0] : null;
                $nameGreet = $customerName ? "স্বাগতম {$customerName} ভাই! 👋 " : "স্বাগতম ভাইয়া! 👋 ";
                $greeting = "{$nameGreet}আপনার সাম্প্রতিক অর্ডার **#{$lastOrder->order_number}** এর স্ট্যাটাস দেখতে চান, নাকি নতুন কোনো গ্যাজেট খুঁজছেন?";
                $chips = [
                    ['label' => "📦 অর্ডার #{$lastOrder->order_number} ট্র্যাক", 'prompt' => "track order {$lastOrder->order_number}"],
                    $catChip ?? ['label' => '✨ নতুন কালেকশন', 'prompt' => 'নতুন কালেকশন দেখাও'],
                    ['label' => '🚚 ডেলিভারি সংক্রান্ত তথ্য', 'prompt' => 'ডেলিভারি চার্জ কত?'],
                ];
            } else {
                $stockSummary = $this->getActiveStockSummary($lang);
                $nameGreet = $customerName ? "স্বাগতম {$customerName} ভাই! 👋 " : "স্বাগতম {$storeName}-তে! 👋 ";
                $greeting = "{$nameGreet}আমি আপনার পার্সোনাল গ্যাজেট অ্যাসিস্ট্যান্ট। আমাদের স্টকে বর্তমানে {$stockSummary['summary_bn']}-সহ আকর্ষণীয় সব গ্যাজেট রয়েছে। আপনার পছন্দের বাজেটের যে কোনো প্রোডাক্ট খুঁজতে আমাকে জানান!";
                $chips = array_merge(
                    [
                        ['label' => '🚚 ডেলিভারি চার্জ কত?', 'prompt' => 'ডেলিভারি চার্জ কত?'],
                        ['label' => '💵 ক্যাশ অন ডেলিভারি আছে?', 'prompt' => 'ক্যাশ অন ডেলিভারি আছে কি?'],
                    ],
                    $stockSummary['chips'],
                    [
                        ['label' => '📦 অর্ডার ট্র্যাক', 'prompt' => 'আমার অর্ডার ট্র্যাক করতে চাই'],
                    ]
                );
            }
        }

        return [
            'greeting' => $greeting,
            'chips' => $chips,
        ];
    }

    protected function generateDynamicFollowUpChips(
        string $intent,
        array $activity,
        ?array $matchedProducts,
        string $reply,
        ?array $createdOrderData = null,
        ?array $orderingState = null,
        string $lang = 'bn'
    ): array {
        $chips = [];
        $currentProduct = $activity['current_product'] ?? null;

        if (!empty($createdOrderData)) {
            $stockSummary = $this->getActiveStockSummary($lang);
            $catChip = !empty($stockSummary['chips']) ? $stockSummary['chips'][0] : null;
            return $lang === 'en' ? [
                ['label' => '📦 Track Order', 'prompt' => 'Track my order'],
                ['label' => '🚚 Delivery Timeline', 'prompt' => 'When will my order arrive?'],
                $catChip ?? ['label' => '✨ View More Gadgets', 'prompt' => 'Show popular gadgets'],
            ] : [
                ['label' => '📦 অর্ডার ট্র্যাক করুন', 'prompt' => 'আমার অর্ডার ট্র্যাক করতে চাই'],
                ['label' => '🚚 ডেলিভারি আপডেট', 'prompt' => 'কুরিয়ার ডেলিভারি আপডেট দিন'],
                $catChip ?? ['label' => '✨ আরও প্রোডাক্ট দেখুন', 'prompt' => 'জনপ্রিয় অন্যান্য গ্যাজেট দেখান'],
            ];
        }

        if ($intent === 'ABUSIVE_OR_PROFANITY') {
            $stockSummary = $this->getActiveStockSummary($lang);
            $catChip = !empty($stockSummary['chips']) ? $stockSummary['chips'][0] : null;
            return $lang === 'en' ? [
                ['label' => '💬 Customer Support', 'prompt' => 'I need help with my order'],
                ['label' => '📦 Track Order', 'prompt' => 'Track my order'],
                $catChip ?? ['label' => '✨ Browse Store', 'prompt' => 'Show available stock'],
            ] : [
                ['label' => '💬 কাস্টমার সাপোর্ট', 'prompt' => 'আমার অর্ডারে সমস্যা হয়েছে'],
                ['label' => '📦 অর্ডার ট্র্যাক', 'prompt' => 'আমার অর্ডার ট্র্যাক করতে চাই'],
                $catChip ?? ['label' => '✨ স্টকের প্রোডাক্ট', 'prompt' => 'আপনাদের স্টকে কি কি প্রোডাক্ট আছে?'],
            ];
        }

        if ($intent === 'CHIT_CHAT') {
            $stockSummary = $this->getActiveStockSummary($lang);
            $chips = !empty($stockSummary['chips']) ? array_slice($stockSummary['chips'], 0, 2) : [];
            if ($lang === 'en') {
                $chips[] = ['label' => '🚚 Delivery & COD', 'prompt' => 'Tell me delivery charges and COD rules'];
                $chips[] = ['label' => '📦 Track Order', 'prompt' => 'Track my order'];
            } else {
                $chips[] = ['label' => '🚚 ডেলিভারি নিয়ম', 'prompt' => 'ডেলিভারি চার্জ এবং নিয়ম বলুন'];
                $chips[] = ['label' => '📦 অর্ডার ট্র্যাক', 'prompt' => 'আমার অর্ডার ট্র্যাক করতে চাই'];
            }
            return $chips;
        }

        if ($intent === 'POLICY_DELIVERY') {
            $stockSummary = $this->getActiveStockSummary($lang);
            $catChip = !empty($stockSummary['chips']) ? $stockSummary['chips'][0] : null;
            $chips = $lang === 'en' ? [
                ['label' => '💵 COD Advance Rules', 'prompt' => 'Do I need to pay advance for COD?'],
                $catChip ?? ['label' => '✨ Available Stock', 'prompt' => 'Show available stock'],
                ['label' => '📦 Track Order', 'prompt' => 'I want to track my order'],
            ] : [
                ['label' => '💵 ক্যাশ অন ডেলিভারি নিয়ম', 'prompt' => 'ক্যাশ অন ডেলিভারিতে অগ্রিম দিতে হবে কি?'],
                $catChip ?? ['label' => '✨ স্টকের গ্যাজেট কালেকশন', 'prompt' => 'স্টকে কি কি প্রোডাক্ট আছে?'],
                ['label' => '📦 অর্ডার ট্র্যাক করতে চাই', 'prompt' => 'আমার অর্ডার ট্র্যাক করতে চাই'],
            ];
        } elseif ($intent === 'POLICY_PAYMENT') {
            $stockSummary = $this->getActiveStockSummary($lang);
            $catChip = !empty($stockSummary['chips']) ? $stockSummary['chips'][0] : null;
            $chips = $lang === 'en' ? [
                ['label' => '🚚 Delivery Time', 'prompt' => 'What is the delivery fee and time?'],
                ['label' => '🛡️ Warranty Policy', 'prompt' => 'Is there a replacement warranty?'],
                $catChip ?? ['label' => '🔥 Featured Gadgets', 'prompt' => 'Show me best selling gadgets'],
            ] : [
                ['label' => '🚚 ডেলিভারি কত দিনে পাব?', 'prompt' => 'ডেলিভারি চার্জ কত এবং কত দিনে পাব?'],
                ['label' => '🛡️ ওয়ারেন্টি পলিসি কি?', 'prompt' => 'প্রোডাক্টের রিপ্লেসমেন্ট ওয়ারেন্টি আছে?'],
                $catChip ?? ['label' => '🔥 সেরা গ্যাজেট কালেকশন', 'prompt' => 'আপনাদের সেরা কিছু প্রোডাক্ট দেখাও'],
            ];
        } elseif ($intent === 'POLICY_WARRANTY') {
            $chips = $lang === 'en' ? [
                ['label' => '💵 Order via COD', 'prompt' => 'I want to order via Cash on Delivery'],
                ['label' => '🚚 Delivery Charge', 'prompt' => 'How much is the delivery charge?'],
                ['label' => '🔍 Browse Catalog', 'prompt' => 'Show available stock'],
            ] : [
                ['label' => '💵 ক্যাশ অন ডেলিভারিতে অর্ডার', 'prompt' => 'ক্যাশ অন ডেলিভারিতে কিনতে চাই'],
                ['label' => '🚚 ডেলিভারি চার্জ কত?', 'prompt' => 'ডেলিভারি চার্জ কত?'],
                ['label' => '🔍 প্রোডাক্ট কালেকশন', 'prompt' => 'আপনাদের স্টকে কি কি প্রোডাক্ট আছে?'],
            ];
        } elseif (!empty($matchedProducts)) {
            $firstP = $matchedProducts[0];
            $title = is_array($firstP) ? ($firstP['title'] ?? '') : ($firstP->title ?? '');
            $shortTitle = Str::limit($title, 18);
            $chips = $lang === 'en' ? [
                ['label' => '🛒 Order ' . $shortTitle, 'prompt' => "I want to order {$title}"],
                ['label' => '🎨 Color Options?', 'prompt' => "What variants are available for {$title}?"],
                ['label' => '🚚 Delivery Timeline', 'prompt' => "How long does delivery take for this item?"],
            ] : [
                ['label' => '🛒 ' . $shortTitle . ' অর্ডার', 'prompt' => "আমি {$title} অর্ডার করতে চাই"],
                ['label' => '🎨 কালার বা ভ্যারিয়েন্ট?', 'prompt' => "{$title} এর আর কি কি কালার বা ভ্যারিয়েন্ট আছে?"],
                ['label' => '🚚 এই প্রোডাক্টের ডেলিভারি সময়', 'prompt' => "এই প্রোডাক্ট ডেলিভারি পেতে কত দিন লাগবে?"],
            ];
        } elseif ($intent === 'IN_CHAT_ORDER') {
            if (($orderingState['step'] ?? '') === 'confirming') {
                $chips = $lang === 'en' ? [
                    ['label' => '✅ Confirm Order', 'prompt' => 'Confirm Order'],
                    ['label' => '❌ Cancel Order', 'prompt' => 'Cancel Order'],
                ] : [
                    ['label' => '✅ হ্যাঁ, কনফার্ম করুন', 'prompt' => 'কনফার্ম'],
                    ['label' => '❌ অর্ডার বাতিল করুন', 'prompt' => 'বাতিল'],
                ];
            } else {
                $chips = $lang === 'en' ? [
                    ['label' => '❌ Cancel Order', 'prompt' => 'Cancel Order'],
                ] : [
                    ['label' => '❌ অর্ডার বাতিল করুন', 'prompt' => 'বাতিল'],
                ];
            }
        } elseif ($intent === 'ORDER_TRACKING') {
            $stockSummary = $this->getActiveStockSummary($lang);
            $catChip = !empty($stockSummary['chips']) ? $stockSummary['chips'][0] : null;
            $chips = $lang === 'en' ? [
                ['label' => '🚚 Courier Update', 'prompt' => 'Give me courier tracking update'],
                $catChip ?? ['label' => '✨ View Gadgets', 'prompt' => 'Show me new gadgets'],
                ['label' => '💬 Support Agent', 'prompt' => 'I want to speak with support'],
            ] : [
                ['label' => '🚚 কুরিয়ার ট্র্যাকিং হেল্প', 'prompt' => 'কুরিয়ার আপডেট বিস্তারিত বলুন'],
                $catChip ?? ['label' => '✨ নতুন গ্যাজেট দেখুন', 'prompt' => 'নতুন কিছু গ্যাজেট দেখাও'],
                ['label' => '💬 সাপোর্ট হেল্প', 'prompt' => 'কাস্টমার কেয়ার সাপোর্ট হেল্প'],
            ];
        } else {
            if (!empty($currentProduct)) {
                $pTitle = $currentProduct['title'] ?? 'এই প্রোডাক্ট';
                $shortPTitle = Str::limit($pTitle, 20);
                $chips = $lang === 'en' ? [
                    ['label' => '🛒 Order ' . $shortPTitle, 'prompt' => "I want to order {$pTitle}"],
                    ['label' => '🚚 Delivery Charge', 'prompt' => 'How much is the delivery charge?'],
                    ['label' => '🔍 Looking for Other Items', 'prompt' => 'Show me other gadgets'],
                ] : [
                    ['label' => '🛒 ' . $shortPTitle . ' অর্ডার', 'prompt' => "আমি {$pTitle} অর্ডার করতে চাই"],
                    ['label' => '🚚 ডেলিভারি চার্জ কত?', 'prompt' => 'ডেলিভারি চার্জ কত?'],
                    ['label' => '🔍 অন্য কোনো আইটেম খুঁজছি', 'prompt' => 'অন্যান্য প্রোডাক্ট দেখাও'],
                ];
            } else {
                $stockSummary = $this->getActiveStockSummary($lang);
                if (!empty($stockSummary['chips'])) {
                    $chips = $stockSummary['chips'];
                    $chips[] = $lang === 'en'
                        ? ['label' => '🚚 Delivery & Payment', 'prompt' => 'Tell me delivery charges and COD rules']
                        : ['label' => '🚚 ডেলিভারি ও পেমেন্ট নিয়ম', 'prompt' => 'ডেলিভারি ও ক্যাশ অন ডেলিভারি নিয়ম বলুন'];
                } else {
                    $chips = $lang === 'en' ? [
                        ['label' => '✨ Available Gadgets', 'prompt' => 'Show me available gadgets'],
                        ['label' => '🚚 Delivery & Payment', 'prompt' => 'Tell me delivery charges and COD rules'],
                        ['label' => '📦 Track Order', 'prompt' => 'I want to track my order'],
                    ] : [
                        ['label' => '✨ স্টকে কি কি আছে?', 'prompt' => 'আপনাদের স্টকে কি কি প্রোডাক্ট আছে?'],
                        ['label' => '🚚 ডেলিভারি ও পেমেন্ট নিয়ম', 'prompt' => 'ডেলিভারি ও ক্যাশ অন ডেলিভারি নিয়ম বলুন'],
                        ['label' => '📦 অর্ডার ট্র্যাক', 'prompt' => 'আমার অর্ডার ট্র্যাক করতে চাই'],
                    ];
                }
            }
        }

        return $chips;
    }

    protected function classifyCustomerIntent(
        string $message,
        ?string $orderCode,
        ?string $cleanPhone,
        ?array $orderingState
    ): string {
        $lowerMsg = mb_strtolower(trim($message));

        $isCancel = (bool) preg_match('/(cancel|বাতিল|ক্যান্সেল|রদ|stop|don\'t want|চাই না)/iu', $lowerMsg);
        if ($isCancel && !empty($orderingState)) {
            return 'IN_CHAT_ORDER';
        }

        $isAbusiveOrProfanity = (bool) preg_match('/(?:^|[[:space:]]|[[:punct:]])(fuck|fck|f\*ck|fuk|shit|bitch|bastard|asshole|cunt|dick|pussy|idiot|stupid|shut\s*up|bullshit|wtf|stfu|chod|choda|chuda|bokachoda|madarchod|khankir|magi|bal|baal|bals|gandu|harami|kutta|suor|shala|saala|sala|chutiya|dhon|pod|batpar|butpar|fraud|scam|scammer|chor)(?:[[:space:]]|[[:punct:]]|$)/iu', $lowerMsg);
        if ($isAbusiveOrProfanity) {
            return 'ABUSIVE_OR_PROFANITY';
        }

        $isChitChat = (bool) preg_match('/(?:^|[[:space:]]|[[:punct:]])(thank\s*you|thanks|thx|tnx|dhonnobad|ধন্যবাদ|shukriya|অনেক\s*ধন্যবাদ|great|awesome|good\s*job|khub\s*bhalo|darun|সেরা|দারুণ|অনেক\s*ভালো|tumi\s*ke|who\s*are\s*you|apni\s*ke|তুমি\s*কে|আপনি\s*কে|real\s*human|ai\s*or\s*human|manush\s*na\s*bot|biye\s*korba|gf\s*ache|girlfriend|love\s*you|ভালোবাসি|haha|hehe|lol|rofl|kire|ki\s*obostha|ki\s*khobor|kisu\s*na|kichu\s*na|thak|lagbe\s*na|pore\s*bolbo|nothing|কিছু\s*না|থাক|লাগবে\s*না|bye|good\s*night|allah\s*hafez|tata|বিদায়|বিদায়|শুভ\s*রাত্রি|টাটা|ok|accha|acha|hmm|hm|achha|thik\s*ache|okay|fine|আচ্ছা|ঠিক\s*আছে|হুম|ওকে|ji\s*bhai|ji\s*vai|জি\s*ভাই|জি\s*ভাইয়া|ek\s*kotha|bar\s*bar|বার\s*বার|এক\s*কথা|kmn\s*kotha|kemon\s*kotha|bot\s*er\s*moto|robot|ai\s*bot)(?:[[:space:]]|[[:punct:]]|$)/iu', $lowerMsg);
        if ($isChitChat && mb_strlen($lowerMsg) <= 120) {
            return 'CHIT_CHAT';
        }

        $isExplicitBuy = (bool) preg_match('/(অর্ডার করতে চাই|কিনতে চাই|অর্ডার করব|অর্ডার দিন|বুকিং|buy\s+now|order\s+this|want\s+to\s+buy|place\s+order|purchase|i\s+want\s+to\s+order|buy\s+it|order\s+korte\s+chai|eta\s+nibo|eta\s+order\s+korbo|nibo|order\s+korbo)/iu', $lowerMsg);
        if ($isExplicitBuy || !empty($orderingState)) {
            return 'IN_CHAT_ORDER';
        }

        if (!empty($orderCode)) {
            return 'ORDER_TRACKING';
        }

        $isSpecificTrackingPhrase = (bool) preg_match('/(track\s*(?:my\s*)?order|order\s*track|amar\s*order|order\s*kothay|parcel\s*kothay|parcel\s*track|অর্ডার\s*ট্র্যাক|ট্র্যাক\s*করতে\s*চাই|আমার\s*অর্ডার\s*কোথায়|পার্সেল\s*কোথায়|ট্র্যাকিং\s*কোড|order\s*status|স্ট্যাটাস)/iu', $lowerMsg);
        if ($isSpecificTrackingPhrase) {
            return 'ORDER_TRACKING';
        }

        // When a customer enters a phone number and is not in an active checkout/ordering flow, it's always order tracking
        if (!empty($cleanPhone) && empty($orderingState)) {
            return 'ORDER_TRACKING';
        }

        if (!empty($cleanPhone) && preg_match('/(track|status|কোথায়|ট্র্যাক)/iu', $lowerMsg)) {
            return 'ORDER_TRACKING';
        }

        $isDeliveryPolicy = (bool) preg_match('/(delivery\s*(?:charge|fee|cost|koto|taka|time|kobe)|ডেলিভারি\s*(?:চার্জ|খরচ|ফি|কত|টাকা|সময়|কবে)|কুরিয়ার\s*চার্জ|courier\s*(?:charge|fee|partner)|shipping\s*(?:cost|fee)|ঢাকার\s*বাইরে|dhakar\s*baire|outside\s*dhaka|inside\s*dhaka|kobe\s*pabo|koto\s*din\s*lage|কত\s*দিনে\s*পাব|পৌঁছাবে|কোন\s*কুরিয়ার)/iu', $lowerMsg);
        if ($isDeliveryPolicy) {
            return 'POLICY_DELIVERY';
        }

        $isPaymentPolicy = (bool) preg_match('/(cash\s*on\s*delivery|cod|advance|bokeya|payment|bkash|nagad|টাকা\s*আগে|অগ্রিম|পেমেন্ট|ক্যাশ\s*অন\s*ডেলিভারি|বিকাশ|অগ্রিম\s*টাকা)/iu', $lowerMsg);
        if ($isPaymentPolicy) {
            return 'POLICY_PAYMENT';
        }

        $isWarrantyPolicy = (bool) preg_match('/(warranty|waranty|guarantee|gurantee|replacement|damaged|defective|nosto|khrap|problem|ওয়ারেন্টি|গ্যারান্টি|রিপ্লেসমেন্ট|নষ্ট\s*হলে|সমস্যা\s*হলে|চেক\s*করে\s*নেব)/iu', $lowerMsg);
        if ($isWarrantyPolicy) {
            return 'POLICY_WARRANTY';
        }

        $isStorePolicy = (bool) preg_match('/(showroom|physical\s*shop|dokan|office|location|authentic|original|copy|fake|কোথায়|শোরুম|দোকান|অরিজিনাল|নকল|আসল)/iu', $lowerMsg);
        if ($isStorePolicy) {
            return 'POLICY_STORE';
        }

        $isGreeting = (bool) preg_match('/^(hi|hello|hey|salam|assalamu\s*alaikum|slaam|kemon\s*achen|hlw|হাই|হ্যালো|সালাম|আসসালামু\s*আলাইকুম|কেমন\s*আছেন|ভাই|bro|vai)\b/iu', $lowerMsg);
        if ($isGreeting && mb_strlen($lowerMsg) <= 35) {
            return 'GREETING';
        }

        return 'PRODUCT_QUERY';
    }

    protected function extractProductSearchIntent(string $message): array
    {
        $clean = preg_replace('/[^\p{L}\p{M}\p{N}\s]/u', ' ', mb_strtolower($message));
        $clean = preg_replace('/\s+/', ' ', trim($clean));

        $maxBudget = null;
        if (preg_match('/(?:under|below|budget|within|কম|মধ্যে)\s*[:=]?\s*(\d+)/iu', $message, $bm)) {
            $maxBudget = (float) $bm[1];
        } elseif (preg_match('/(\d+)\s*(?:tk|taka|টাকা)?\s*(?:er|takar)?\s*(?:moddhe|er\s*moddhe|budget|under)/iu', $message, $bm)) {
            $maxBudget = (float) $bm[1];
        } elseif (preg_match('/\b(\d{1,2})k\b/i', $message, $bm)) {
            $maxBudget = (float) $bm[1] * 1000;
        }

        $detectedCategory = null;
        if (preg_match('/(keyboard|mechanical|কীবোর্ড|কিবোর্ড|মেকানিক্যাল)/iu', $message)) {
            $detectedCategory = 'keyboard';
        } elseif (preg_match('/(switch|switches|gateron|outemu|cherry|linear|tactile|সুইচ)/iu', $message)) {
            $detectedCategory = 'switch';
        } elseif (preg_match('/(mouse|gaming\s*mouse|mice|মাউস)/iu', $message)) {
            $detectedCategory = 'mouse';
        } elseif (preg_match('/(headphone|headset|earbuds|earphone|tws|soundbar|speaker|audio|হেডফোন|ইয়ারবাড|স্পিকার|ইয়ারফোন)/iu', $message)) {
            $detectedCategory = 'audio';
        } elseif (preg_match('/(smart\s*watch|watch|স্মার্টওয়াচ|ঘড়ি|ঘড়ি)/iu', $message)) {
            $detectedCategory = 'watch';
        } elseif (preg_match('/(iphone|samsung|galaxy|redmagic|pixel|oneplus|realme|xiaomi|redmi|oppo|vivo|rog\s*phone|smart\s*phone|gaming\s*phone|phone|phones|mobile|স্মার্টফোন|মোবাইল|ফোন)/iu', $message) && !preg_match('/(headphone|earphone|microphone|হেডফোন|ইয়ারফোন|মাইক্রোফোন)/iu', $message)) {
            $detectedCategory = 'phone';
        } elseif (preg_match('/(laptop|notebook|desktop|pc|ল্যাপটপ|কম্পিউটার)/iu', $message)) {
            $detectedCategory = 'laptop';
        } elseif (preg_match('/(tablet|ipad|tab|ট্যাব|ট্যাবলেট)/iu', $message)) {
            $detectedCategory = 'tablet';
        } elseif (preg_match('/(gamepad|controller|joystick|গেমপ্যাড|কন্ট্রোলার)/iu', $message)) {
            $detectedCategory = 'controller';
        } elseif (preg_match('/(deskmat|mousepad|desk\s*pad|মাউসপ্যাড|ম্যাট)/iu', $message)) {
            $detectedCategory = 'deskmat';
        } elseif (preg_match('/(keycap|keycaps|pbt|কীক্যাপ)/iu', $message)) {
            $detectedCategory = 'keycap';
        } elseif (preg_match('/(charger|charging|adapter|gan|চার্জার)/iu', $message)) {
            $detectedCategory = 'charger';
        } elseif (preg_match('/(cable|ক্যাবল|coiled)/iu', $message)) {
            $detectedCategory = 'cable';
        } elseif (preg_match('/(gimbal|গিম্বল|stabilizer)/iu', $message)) {
            $detectedCategory = 'gimbal';
        } elseif (preg_match('/(mic|microphone|মাইক|মাইক্রোফোন)/iu', $message)) {
            $detectedCategory = 'mic';
        }

        $stopwords = [
            'apnader',
            'kache',
            'ki',
            'ache',
            'valo',
            'bhalo',
            'kon',
            'kono',
            'dekhan',
            'dekhao',
            'kinte',
            'chai',
            'suggest',
            'korun',
            'koren',
            'need',
            'want',
            'looking',
            'for',
            'best',
            'cheap',
            'sosta',
            'price',
            'cost',
            'dam',
            'koto',
            'taka',
            'কম',
            'দামে',
            'ভালো',
            'কোনটা',
            'আছে',
            'কিনা',
            'বলেন',
            'ভাই',
            'ভাইয়া',
            'bro',
            'vai',
            'please',
            'plz',
            'show',
            'me',
            'what',
            'is',
            'the',
            'of',
            'do',
            'you',
            'have',
            'i',
            'a',
            'an',
            'and',
            'or',
            'to',
            'product',
            'item',
            'store',
            'any',
            'available',
            'stock',
            'tell',
            'details',
            'khujchi',
            'কালেকশন',
            'দেখান',
            'দেখা',
            'দেখাও',
            'গ্যাজেট',
            'ও',
            'kom',
            'komano',
            'koman',
            'koma',
            'komay',
            'kombe',
            'possible',
            'posible',
            'kora',
            'jay',
            'jabe',
            'hobe',
            'hobena',
            'na',
            'fixed',
            'discount',
            'aro',
            'kisu',
            'kichu',
            'kichuta',
            'কমান',
            'কমানো',
            'কমবে',
            'ডিসকাউন্ট',
            'ফিক্সড',
            'সম্ভব',
            'হবে',
            'না'
        ];

        $tokens = array_diff(explode(' ', $clean), $stopwords);
        $tokens = array_values(array_filter($tokens, fn($w) => mb_strlen(trim($w)) >= 2));

        $isGeneralBrowse = (bool) preg_match('/(?:browse|popular|all|products|items|gadgets|stock|collection|কালেকশন|সব|প্রোডাক্ট|গ্যাজেট|স্টকে|স্টক|কি কি|apnader\s*kache\s*ki|dekhan|dekhao|suggest)/iu', $message);

        return [
            'category' => $detectedCategory,
            'max_budget' => $maxBudget,
            'tokens' => $tokens,
            'clean_query' => $clean,
            'is_general_browse' => $isGeneralBrowse,
        ];
    }

    protected function findRelevantProducts(array $searchIntent): array
    {
        $tokens = $searchIntent['tokens'] ?? [];
        $category = $searchIntent['category'] ?? null;
        $maxBudget = $searchIntent['max_budget'] ?? null;
        $cleanQuery = $searchIntent['clean_query'] ?? '';

        $query = DB::table('products')->where('is_active', 1);

        if (!empty($maxBudget) && $maxBudget > 0) {
            $query->where('price', '<=', $maxBudget);
        }

        $hasFilters = false;

        if (!empty($category)) {
            $categoryTerms = match ($category) {
                'keyboard' => ['keyboard', 'mechanical', 'কীবোর্ড', 'কিবোর্ড'],
                'switch' => ['switch', 'switches', 'সুইচ'],
                'mouse' => ['mouse', 'gaming mouse', 'মাউস'],
                'audio' => ['headphone', 'headset', 'earbuds', 'earphone', 'tws', 'speaker', 'soundbar', 'audio', 'হেডফোন', 'ইয়ারবাড', 'ইয়ারবাড', 'স্পিকার', 'ইয়ারফোন', 'ইয়ারফোন', 'অডিও'],
                'watch' => ['watch', 'smartwatch', 'wearable', 'ঘড়ি', 'ঘড়ি', 'স্মার্টওয়াচ'],
                'phone' => ['smartphone', 'gaming phone', 'mobile phone', 'স্মার্টফোন', 'মোবাইল ফোন'],
                'laptop' => ['laptop', 'notebook', 'desktop', 'ল্যাপটপ'],
                'tablet' => ['tablet', 'ipad', 'ট্যাব'],
                'controller' => ['gamepad', 'controller', 'joystick', 'গেমপ্যাড'],
                'deskmat' => ['deskmat', 'mousepad', 'মাউসপ্যাড', 'ম্যাট'],
                'keycap' => ['keycap', 'keycaps', 'কীক্যাপ'],
                'charger' => ['charger', 'charging', 'gan', 'চার্জার'],
                'cable' => ['cable', 'ক্যাবল', 'coiled'],
                'gimbal' => ['gimbal', 'গিম্বল'],
                'mic' => ['mic', 'microphone', 'মাইক', 'মাইক্রোফোন'],
                default => [$category],
            };

            $matchedCategoryIds = DB::table('categories')
                ->where(function ($cq) use ($categoryTerms) {
                    foreach ($categoryTerms as $ct) {
                        $cq->orWhere('name', 'LIKE', "%{$ct}%")
                            ->orWhere('slug', 'LIKE', "%{$ct}%");
                    }
                })
                ->pluck('id')
                ->toArray();

            $query->where(function ($q) use ($categoryTerms, $matchedCategoryIds) {
                if (!empty($matchedCategoryIds)) {
                    $q->whereIn('category_id', $matchedCategoryIds);
                }
                foreach ($categoryTerms as $ct) {
                    $q->orWhere('title', 'LIKE', "%{$ct}%")
                        ->orWhere('slug', 'LIKE', "%{$ct}%")
                        ->orWhere('tag', 'LIKE', "%{$ct}%")
                        ->orWhere('short_desc', 'LIKE', "%{$ct}%");
                }
            });
            $hasFilters = true;

            $catTermsLower = array_map('mb_strtolower', $categoryTerms);
            $tokens = array_values(array_filter($tokens, fn($t) => !in_array(mb_strtolower($t), $catTermsLower)));
        }

        if (!empty($tokens)) {
            $query->where(function ($q) use ($tokens) {
                foreach ($tokens as $t) {
                    if (in_array($t, ['phone', 'phones'])) {
                        $q->orWhereRaw("title REGEXP '(^|[[:space:]]|[[:punct:]])phone[s]?([[:space:]]|[[:punct:]]|$)'")
                            ->orWhereRaw("short_desc REGEXP '(^|[[:space:]]|[[:punct:]])phone[s]?([[:space:]]|[[:punct:]]|$)'");
                    } else {
                        $q->orWhere('title', 'LIKE', "%{$t}%")
                            ->orWhere('slug', 'LIKE', "%{$t}%")
                            ->orWhere('tag', 'LIKE', "%{$t}%")
                            ->orWhere('short_desc', 'LIKE', "%{$t}%")
                            ->orWhere('specifications', 'LIKE', "%{$t}%");
                    }
                }
            });
            $hasFilters = true;
        }

        if (!$hasFilters && !empty($cleanQuery) && mb_strlen($cleanQuery) >= 3) {
            $query->where(function ($q) use ($cleanQuery) {
                $q->orWhere('title', 'LIKE', "%{$cleanQuery}%")
                    ->orWhere('slug', 'LIKE', "%{$cleanQuery}%")
                    ->orWhere('tag', 'LIKE', "%{$cleanQuery}%")
                    ->orWhere('short_desc', 'LIKE', "%{$cleanQuery}%");
            });
            $hasFilters = true;
        }

        if (!$hasFilters) {
            if ($searchIntent['is_general_browse'] ?? false) {
                return $query->where('stock_qty', '>', 0)
                    ->select([
                        'id',
                        'title',
                        'slug',
                        'price',
                        'old_price',
                        'stock_qty',
                        'main_image',
                        'short_desc',
                        'specifications',
                        'variants',
                        'tag',
                    ])
                    ->orderByDesc('is_featured')
                    ->orderByDesc('id')
                    ->take(4)
                    ->get()
                    ->all();
            }
            return [];
        }

        return $query->select([
            'id',
            'title',
            'slug',
            'price',
            'old_price',
            'stock_qty',
            'main_image',
            'short_desc',
            'specifications',
            'variants',
            'tag',
        ])
            ->orderByDesc('stock_qty')
            ->orderByDesc('id')
            ->take(4)
            ->get()
            ->all();
    }

    public function sendMessage(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:1500',
            'order_code' => 'nullable|string|max:50',
            'phone_number' => 'nullable|string|max:30',
            'activity' => 'nullable|array',
        ]);

        $userMessage = trim(strip_tags($request->input('message')));
        if (empty($userMessage)) {
            return response()->json(['success' => false, 'message' => 'Message cannot be empty.'], 422);
        }

        $sessionHistory = session()->get('chatbot_history', []);
        if (!is_array($sessionHistory)) {
            $sessionHistory = [];
        }

        $activity = $this->parseUserActivity($request);

        $explicitOrderCode = trim((string) $request->input('order_code'));
        $explicitPhoneNumber = trim((string) $request->input('phone_number'));

        $bengaliDigits = ['০' => '0', '১' => '1', '২' => '2', '৩' => '3', '৪' => '4', '৫' => '5', '৬' => '6', '৭' => '7', '৮' => '8', '৯' => '9'];
        $normalizedMsg = strtr($userMessage, $bengaliDigits);

        $detectedOrderCode = null;
        if (preg_match('/\b(ZB-\d{6}-\d{4}|ZB-[A-Za-z0-9\-]+)\b/i', $normalizedMsg, $matches)) {
            $detectedOrderCode = strtoupper($matches[1]);
        } elseif (preg_match('/#?ZB[-_\s]*(\d{6}[-_\s]*\d{4})/i', $normalizedMsg, $matches)) {
            $detectedOrderCode = 'ZB-' . preg_replace('/[^0-9\-]/', '', $matches[1]);
        } elseif (preg_match('/\b(\d{6}-\d{4})\b/', $normalizedMsg, $matches)) {
            $detectedOrderCode = 'ZB-' . $matches[1];
        } elseif (preg_match('/(?:order|ord|#|অর্ডার|ট্র্যাক)\s*[:=]?\s*([A-Za-z0-9\-]{4,25})/iu', $normalizedMsg, $matches)) {
            if (!preg_match('/^01[3-9]\d{8}$/', $matches[1])) {
                $detectedOrderCode = strtoupper(trim($matches[1]));
            }
        }

        $detectedPhoneNumber = null;
        if (preg_match('/(?:\+?88)?(01[3-9]\d{8})/', $normalizedMsg, $matches)) {
            $detectedPhoneNumber = $matches[1];
        }

        $orderCode = !empty($detectedOrderCode) ? $detectedOrderCode : (!empty($explicitOrderCode) ? $explicitOrderCode : null);
        $phoneNumber = !empty($detectedPhoneNumber) ? $detectedPhoneNumber : (!empty($explicitPhoneNumber) ? $explicitPhoneNumber : null);

        $cleanPhone = null;
        if (!empty($phoneNumber)) {
            $digits = preg_replace('/[^0-9]/', '', $phoneNumber);
            if (str_starts_with($digits, '8801')) {
                $digits = substr($digits, 2);
            }
            if (strlen($digits) === 11 && str_starts_with($digits, '01')) {
                $cleanPhone = $digits;
            }
        }

        $userLang = trim((string) $request->input('language', 'bn'));
        if (!in_array($userLang, ['bn', 'en'])) {
            $userLang = 'bn';
        }

        $isBengaliScript = (bool) preg_match('/[\x{0980}-\x{09FF}]/u', $userMessage);
        $isBanglish = (bool) preg_match('/\b(koto|dam|daam|kobe|pabo|nibo|nebo|chai|hobe|kom|bhai|vai|bro|apnader|amr|amar|apnar|ache|achhe|kichu|kichuta|dekhaw|dekhao|kemon|valo|bhalo|thik|ekta|dokan|shop|ashbe|pathan|korbo|kore|den|hobe na|na|ar|aro|eta|aita|oita|kena|kinbo|pochondo|hoise|lagbe|details|specs)\b/i', $userMessage);

        $enforceBangla = true;
        if ($userLang === 'en' && !$isBengaliScript && !$isBanglish) {
            $enforceBangla = false;
        }

        $orderingState = session()->get('chatbot_ordering');

        $intent = $this->classifyCustomerIntent($userMessage, $orderCode, $cleanPhone, $orderingState);

        $settings = DB::table('settings')->pluck('value', 'key')->toArray();
        $insideDhakaFee = isset($settings['shipping_inside_dhaka']) && is_numeric($settings['shipping_inside_dhaka'])
            ? (float) $settings['shipping_inside_dhaka']
            : 60.00;
        $outsideDhakaFee = isset($settings['shipping_outside_dhaka']) && is_numeric($settings['shipping_outside_dhaka'])
            ? (float) $settings['shipping_outside_dhaka']
            : 120.00;
        $storeName = $settings['site_name'] ?? 'Zippy';

        $orderStatusData = null;
        $ordersListData = null;
        $orderContext = '';

        $createdOrderData = null;
        $orderingContext = '';

        $suggestedProducts = [];
        $productContext = '';
        $policyContext = '';

        if ($intent === 'ORDER_TRACKING') {
            if (!empty($orderCode) && !empty($cleanPhone)) {
                $order = DB::table('orders')
                    ->where(function ($q) use ($orderCode) {
                        $q->where('order_number', $orderCode)
                            ->orWhere('order_number', 'LIKE', "%{$orderCode}%");
                    })
                    ->where('customer_phone', 'LIKE', "%{$cleanPhone}%")
                    ->first();

                if ($order) {
                    session(['chatbot_last_order_number' => $order->order_number]);

                    $items = DB::table('order_items')
                        ->where('order_id', $order->id)
                        ->select(['product_title', 'product_image', 'quantity', 'unit_price', 'total_price'])
                        ->get();

                    $formattedItems = [];
                    foreach ($items as $item) {
                        $formattedItems[] = [
                            'title' => $item->product_title,
                            'image' => $item->product_image ?: '/favicon.ico',
                            'qty' => (int) $item->quantity,
                            'price' => (float) $item->unit_price,
                            'total' => (float) $item->total_price,
                        ];
                    }

                    $step = $this->determineTimelineStep($order->order_status);
                    $trackingUrl = $this->buildCourierTrackingUrl($order->courier_provider, $order->courier_tracking_code, $order->order_number);

                    $orderStatusData = [
                        'order_number' => $order->order_number,
                        'order_status' => strtolower($order->order_status),
                        'order_status_label' => ucfirst($order->order_status),
                        'timeline_step' => $step,
                        'payment_status' => ucfirst($order->payment_status),
                        'payment_method' => strtoupper($order->payment_method ?: 'COD'),
                        'courier_provider' => $order->courier_provider ?: 'Standard Express',
                        'courier_tracking_code' => $order->courier_tracking_code ?: 'Processing',
                        'courier_tracking_url' => $trackingUrl,
                        'courier_status' => $order->courier_status ?: 'In Transit',
                        'order_date' => date('d M, Y', strtotime($order->created_at)),
                        'total' => (float) $order->total,
                        'subtotal' => (float) ($order->subtotal ?? $order->total),
                        'shipping_cost' => (float) ($order->shipping_cost ?? $insideDhakaFee),
                        'items' => $formattedItems,
                    ];

                    $orderContext = "LIVE ORDER TRACKING DATA (ORDER #{$order->order_number}):\n"
                        . "- Status: " . ucfirst($order->order_status) . "\n"
                        . "- Progress Timeline Step: {$step} of 4\n"
                        . "- Total: ৳" . number_format($order->total, 2) . "\n"
                        . "CRITICAL DIRECTIVE: An interactive visual Order Card with timeline and courier badge is already rendered in the chat UI. DO NOT duplicate the items list or courier table. Give a brief, courteous 1-2 sentence response acknowledging their order status.";
                } else {
                    $orderContext = "ORDER LOOKUP RESULT: No order matched Order Number '{$orderCode}' with Phone '{$cleanPhone}'. Politely inform the customer to verify their order number or phone number.";
                }
            } elseif (empty($orderCode) && !empty($cleanPhone)) {
                $last9 = substr($cleanPhone, -9);
                $orders = DB::table('orders')
                    ->where(function ($q) use ($cleanPhone, $last9) {
                        $q->where('customer_phone', 'LIKE', "%{$cleanPhone}%")
                            ->orWhere('customer_phone', 'LIKE', "%{$last9}%");
                    })
                    ->orderByDesc('id')
                    ->take(3)
                    ->get();

                if ($orders->isNotEmpty()) {
                    session(['chatbot_last_order_number' => $orders->first()->order_number]);

                    $ordersListData = [];
                    $summaryLines = [];

                    foreach ($orders as $ord) {
                        $items = DB::table('order_items')
                            ->where('order_id', $ord->id)
                            ->select(['product_title', 'product_image', 'quantity', 'unit_price', 'total_price'])
                            ->get();

                        $formattedItems = [];
                        foreach ($items as $item) {
                            $formattedItems[] = [
                                'title' => $item->product_title,
                                'image' => $item->product_image ?: '/favicon.ico',
                                'qty' => (int) $item->quantity,
                                'price' => (float) $item->unit_price,
                                'total' => (float) $item->total_price,
                            ];
                        }

                        $step = $this->determineTimelineStep($ord->order_status);
                        $trackingUrl = $this->buildCourierTrackingUrl($ord->courier_provider, $ord->courier_tracking_code, $ord->order_number);

                        $rawPhone = $ord->customer_phone ?: '';
                        $maskedPhone = (strlen($rawPhone) >= 7)
                            ? substr($rawPhone, 0, 4) . '****' . substr($rawPhone, -3)
                            : '0179****671';

                        $ordersListData[] = [
                            'order_number' => $ord->order_number,
                            'customer_phone_masked' => $maskedPhone,
                            'order_status' => strtolower($ord->order_status),
                            'order_status_label' => ucfirst($ord->order_status),
                            'timeline_step' => $step,
                            'payment_status' => ucfirst($ord->payment_status),
                            'payment_method' => strtoupper($ord->payment_method ?: 'COD'),
                            'courier_provider' => $ord->courier_provider ?: 'Standard Express',
                            'courier_tracking_code' => $ord->courier_tracking_code ?: 'Processing',
                            'courier_tracking_url' => $trackingUrl,
                            'courier_status' => $ord->courier_status ?: 'In Transit',
                            'order_date' => date('d M, Y', strtotime($ord->created_at)),
                            'total' => (float) $ord->total,
                            'subtotal' => (float) ($ord->subtotal ?? $ord->total),
                            'shipping_cost' => (float) ($ord->shipping_cost ?? $insideDhakaFee),
                            'items' => $formattedItems,
                        ];

                        $summaryLines[] = "Order #{$ord->order_number} (Status: {$ord->order_status}, Total: ৳" . number_format($ord->total, 2) . ")";
                    }

                    if ($orders->count() === 1) {
                        $orderStatusData = $ordersListData[0];
                        $ordersListData = null;
                    }

                    $orderContext = "LIVE ORDERS FOUND IN DATABASE FOR PHONE {$cleanPhone}:\n"
                        . implode("\n", $summaryLines) . "\n"
                        . "CRITICAL INSTRUCTION: The interactive visual Order Tracking Card is already displayed in the chat UI right below this message. In Bengali, write a short, polite 1-2 sentence response confirming that their order has been found and is shown below (e.g. 'আপনার {$cleanPhone} নম্বর অনুযায়ী অর্ডারটি পাওয়া গেছে। নিচে বিস্তারিত স্ট্যাটাস দেওয়া হলো:'). NEVER say that the order was not found!";
                } else {
                    $orderContext = "ORDER LOOKUP RESULT: No orders found matching phone number {$cleanPhone} in our system. In Bengali, politely inform the customer that no order was found with {$cleanPhone}, and advise them to double check their phone number or provide their Order Number (e.g. ZB-260917-0001).";
                }
            } elseif (!empty($orderCode) && empty($cleanPhone)) {
                $order = DB::table('orders')
                    ->where(function ($q) use ($orderCode) {
                        $q->where('order_number', $orderCode)
                            ->orWhere('order_number', 'LIKE', "%{$orderCode}%");
                    })
                    ->first();

                if ($order) {
                    session(['chatbot_last_order_number' => $order->order_number]);

                    $rawPhone = $order->customer_phone ?: '';
                    $maskedPhone = (strlen($rawPhone) >= 7)
                        ? substr($rawPhone, 0, 4) . '****' . substr($rawPhone, -3)
                        : '0179****671';

                    $items = DB::table('order_items')
                        ->where('order_id', $order->id)
                        ->select(['product_title', 'product_image', 'quantity', 'unit_price', 'total_price'])
                        ->get();

                    $formattedItems = [];
                    foreach ($items as $item) {
                        $formattedItems[] = [
                            'title' => $item->product_title,
                            'image' => $item->product_image ?: '/favicon.ico',
                            'qty' => (int) $item->quantity,
                            'price' => (float) $item->unit_price,
                            'total' => (float) $item->total_price,
                        ];
                    }

                    $step = $this->determineTimelineStep($order->order_status);
                    $trackingUrl = $this->buildCourierTrackingUrl($order->courier_provider, $order->courier_tracking_code, $order->order_number);

                    $orderStatusData = [
                        'order_number' => $order->order_number,
                        'customer_phone_masked' => $maskedPhone,
                        'order_status' => strtolower($order->order_status),
                        'order_status_label' => ucfirst($order->order_status),
                        'timeline_step' => $step,
                        'payment_status' => ucfirst($order->payment_status),
                        'payment_method' => strtoupper($order->payment_method ?: 'COD'),
                        'courier_provider' => $order->courier_provider ?: 'Standard Express',
                        'courier_tracking_code' => $order->courier_tracking_code ?: 'Processing',
                        'courier_tracking_url' => $trackingUrl,
                        'courier_status' => $order->courier_status ?: 'In Transit',
                        'order_date' => date('d M, Y', strtotime($order->created_at)),
                        'total' => (float) $order->total,
                        'items' => $formattedItems,
                    ];

                    $orderContext = "LIVE ORDER TRACKING DATA (ORDER #{$order->order_number}):\n"
                        . "- Status: " . ucfirst($order->order_status) . "\n"
                        . "- Customer Phone Masked: {$maskedPhone}\n"
                        . "- Progress Step: {$step} of 4\n"
                        . "CRITICAL DIRECTIVE: An interactive visual Order Card is displayed in the UI. Keep text reply brief (1-2 sentences) acknowledging their order status.";
                } else {
                    $orderContext = "ORDER LOOKUP RESULT: Order #{$orderCode} was not found in our system. Advise the customer to double-check their order number.";
                }
            } else {
                $orderContext = "ORDER TRACKING PROMPT: The customer wants to track their order. Politely ask them to provide their Order Number (e.g. ZB-260305-1025) or their 11-digit mobile phone number.";
            }
        } elseif ($intent === 'IN_CHAT_ORDER') {
            $isCancelIntent = (bool) preg_match('/(cancel|বাতিল|ক্যান্সেল|রদ|stop|don\'t want|চাই না)/iu', $userMessage);

            if ($isCancelIntent && !empty($orderingState)) {
                session()->forget('chatbot_ordering');
                $orderingState = null;
                $orderingContext = "IN-CHAT ORDERING NOTICE: The user explicitly cancelled their ongoing ordering session. Acknowledge the cancellation courteously and offer general assistance.";
            } else {
                if (empty($orderingState)) {
                    $orderingState = [
                        'step' => 'gathering',
                        'product_id' => null,
                        'product_title' => null,
                        'product_price' => null,
                        'product_image' => null,
                        'variant' => null,
                        'quantity' => 1,
                        'customer_name' => null,
                        'customer_phone' => null,
                        'delivery_address' => null,
                        'district' => 'ঢাকা',
                    ];

                    if (!empty($activity['current_product']) && !empty($activity['current_product']['id'])) {
                        $orderingState['product_id'] = $activity['current_product']['id'];
                        $orderingState['product_title'] = $activity['current_product']['title'] ?? null;
                        $orderingState['product_price'] = (float) ($activity['current_product']['price'] ?? 0);
                        $orderingState['product_image'] = $activity['current_product']['main_image'] ?? null;
                    }

                    if (empty($orderingState['product_id'])) {
                        $cleanSearch = preg_replace('/[^\p{L}\p{N}\s]/u', ' ', mb_strtolower($userMessage));
                        $stopwords = ['order', 'korte', 'chai', 'buy', 'now', 'this', 'want', 'to', 'place', 'purchase', 'i', 'a', 'the', 'অর্ডার', 'করতে', 'চাই', 'কিনতে', 'করব', 'দিন', 'বুকিং', 'একটি', 'টা', 'প্লিজ', 'eta', 'nibo'];
                        $terms = array_diff(explode(' ', $cleanSearch), $stopwords);
                        $terms = array_values(array_filter($terms, fn($w) => mb_strlen(trim($w)) >= 2));

                        if (!empty($terms)) {
                            $pQuery = DB::table('products')->where('is_active', 1);
                            $pQuery->where(function ($q) use ($terms) {
                                foreach ($terms as $t) {
                                    $q->orWhere('title', 'LIKE', "%{$t}%")
                                        ->orWhere('slug', 'LIKE', "%{$t}%")
                                        ->orWhere('tag', 'LIKE', "%{$t}%")
                                        ->orWhere('short_desc', 'LIKE', "%{$t}%");
                                }
                            });
                            $matchedProduct = $pQuery->first();
                            if ($matchedProduct) {
                                $orderingState['product_id'] = $matchedProduct->id;
                                $orderingState['product_title'] = $matchedProduct->title;
                                $orderingState['product_price'] = (float) $matchedProduct->price;
                                $orderingState['product_image'] = $matchedProduct->main_image;
                            }
                        }
                    }

                    if (empty($orderingState['product_id'])) {
                        $lastPId = session()->get('chatbot_last_product_id');
                        if (!empty($lastPId)) {
                            $pCandidate = DB::table('products')->where('id', $lastPId)->where('is_active', 1)->first();
                            if ($pCandidate) {
                                $orderingState['product_id'] = $pCandidate->id;
                                $orderingState['product_title'] = $pCandidate->title;
                                $orderingState['product_price'] = (float) $pCandidate->price;
                                $orderingState['product_image'] = $pCandidate->main_image;
                            }
                        }
                    }
                }

                if (!empty($cleanPhone) && empty($orderingState['customer_phone'])) {
                    $orderingState['customer_phone'] = $cleanPhone;
                }

                if (preg_match('/(?:quantity|qty|পরিমাণ|পিস|টি)\s*[:=]?\s*(\d+)/iu', $userMessage, $qm) || preg_match('/\b(\d+)\s*(?:piece|pcs|টি|পিস)\b/iu', $userMessage, $qm)) {
                    $orderingState['quantity'] = max(1, (int) $qm[1]);
                }

                if (preg_match('/(ঢাকার বাইরে|outside dhaka|chittagong|ctg|sylhet|rajshahi|khulna|barishal|rangpur|mymensingh|comilla|gazipur|narayanganj|savar)/iu', $userMessage)) {
                    $orderingState['district'] = 'ঢাকার বাইরে';
                } elseif (preg_match('/(ঢাকা|inside dhaka|dhaka city|mirpur|dhanmondi|uttara|gulshan|banani|mohammadpur|badda|motijheel|farmgate|khilgaon|jatrabari|rampura)/iu', $userMessage)) {
                    $orderingState['district'] = 'ঢাকা';
                }

                if (preg_match('/(?:নাম|name)\s*[:=]\s*([^\n,]+)/iu', $userMessage, $nm)) {
                    $orderingState['customer_name'] = trim($nm[1]);
                }

                if (preg_match('/(?:ঠিকানা|address)\s*[:=]\s*([^\n]+)/iu', $userMessage, $am)) {
                    $candidateAddr = trim($am[1]);
                    $cleanDigits = preg_replace('/[^\d]/', '', $candidateAddr);
                    $isPhoneLike = (bool) preg_match('/^(?:88)?01[3-9]\d{8}$/', $cleanDigits) || (bool) preg_match('/^[0-9\s\-\+\(\)]+$/', $candidateAddr);
                    if (!$isPhoneLike && mb_strlen($candidateAddr) >= 3) {
                        $orderingState['delivery_address'] = $candidateAddr;
                    }
                }

                if (empty($orderingState['delivery_address'])) {
                    if (preg_match('/(?:বাসা|রোড|বাড়ি|সেক্টর|মিরপুর|ধানমন্ডি|উত্তরা|গুলশান|মোহাম্মদপুর|বনানী|বাড্ডা|বসুন্ধরা|road|house|sector|block|mirpur|dhanmondi|uttara|gulshan|mohammadpur|banani)[^\n]*/iu', $userMessage, $addMatch)) {
                        $candidateAddr = trim($addMatch[0]);
                        $cleanDigits = preg_replace('/[^\d]/', '', $candidateAddr);
                        $isPhoneLike = (bool) preg_match('/^(?:88)?01[3-9]\d{8}$/', $cleanDigits) || (bool) preg_match('/^[0-9\s\-\+\(\)]+$/', $candidateAddr);
                        if (!$isPhoneLike && mb_strlen($candidateAddr) >= 4) {
                            $orderingState['delivery_address'] = $candidateAddr;
                        }
                    }
                }

                if (empty($orderingState['customer_name'])) {
                    if (preg_match('/(?:আমার নাম|i am|this is)\s+([A-Za-z\p{Bengali}\s]{2,30})/iu', $userMessage, $nameMatch)) {
                        $orderingState['customer_name'] = trim($nameMatch[1]);
                    }
                }

                if (empty($orderingState['customer_name']) || empty($orderingState['delivery_address'])) {
                    $parts = preg_split('/[,|\n]/', $userMessage);
                    if (count($parts) >= 2) {
                        foreach ($parts as $p) {
                            $trimmedPart = trim($p);
                            if (empty($trimmedPart)) continue;

                            $digitsOnly = preg_replace('/[^\d]/', '', $trimmedPart);
                            $isPhone = (bool) preg_match('/^(?:88)?01[3-9]\d{8}$/', $digitsOnly) || (bool) preg_match('/^[0-9\s\-\+\(\)]{9,16}$/', $trimmedPart);

                            if ($isPhone) {
                                if (empty($orderingState['customer_phone']) && strlen($digitsOnly) >= 11) {
                                    $pNum = str_starts_with($digitsOnly, '8801') ? substr($digitsOnly, 2) : $digitsOnly;
                                    if (strlen($pNum) === 11) {
                                        $orderingState['customer_phone'] = $pNum;
                                    }
                                }
                                continue;
                            }

                            if (empty($orderingState['customer_name']) && preg_match('/^[\p{L}\s\.\']{2,35}$/u', $trimmedPart) && !preg_match('/(?:ঢাকা|dhaka|mirpur|uttara|chittagong|sylhet|road|house|sector|block|বাসা|রোড|বাড়ি|সেক্টর|মিরপুর|ধানমন্ডি|উত্তরা|গুলশান|থানা|উপজেলা)/iu', $trimmedPart)) {
                                $orderingState['customer_name'] = $trimmedPart;
                            } elseif (empty($orderingState['delivery_address'])) {
                                $hasAddrKeyword = (bool) preg_match('/(?:বাসা|রোড|বাড়ি|সেক্টর|মিরপুর|ধানমন্ডি|উত্তরা|গুলশান|মোহাম্মদপুর|বনানী|বাড্ডা|বসুন্ধরা|road|house|sector|block|mirpur|dhanmondi|uttara|gulshan|mohammadpur|banani|thana|upazila|zila|জেলা|থানা|গ্রাম|ডাকঘর)/iu', $trimmedPart);
                                $hasLetters = (bool) preg_match('/[\p{L}]/u', $trimmedPart);
                                if (($hasAddrKeyword || $hasLetters) && mb_strlen($trimmedPart) >= 5) {
                                    $orderingState['delivery_address'] = $trimmedPart;
                                }
                            }
                        }
                    }
                }

                // Strict sanity check: Delivery address MUST NOT be numeric or phone number
                if (!empty($orderingState['delivery_address'])) {
                    $addrDigits = preg_replace('/[^\d]/', '', $orderingState['delivery_address']);
                    if (preg_match('/^[0-9\s\-\+\(\)]+$/', $orderingState['delivery_address']) || preg_match('/^(?:88)?01[3-9]\d{8}$/', $addrDigits)) {
                        if (empty($orderingState['customer_phone']) && preg_match('/^(?:88)?01[3-9]\d{8}$/', $addrDigits)) {
                            $orderingState['customer_phone'] = substr($addrDigits, -11);
                        }
                        $orderingState['delivery_address'] = null;
                    }
                }

                $isConfirmCommand = (bool) preg_match('/^(?:ok|okay|okey|ha|haa|thik ache|accha|acha|done|yes|yep|confirm|confirm order|place order|হাঁ|হ্যাঁ|কনফার্ম|অর্ডার কনফার্ম|ঠিক আছে|আচ্ছা|হুম|হবে)\b|(?:\b(?:confirm|কনফার্ম|অর্ডার কনফার্ম|place order)\b)/iu', trim($userMessage));
                $isCancelCommand = (bool) preg_match('/^(?:cancel|no|na|nah|stop|বাতিল|ক্যান্সেল|লাগবে না|না)\b/iu', trim($userMessage));

                if (($orderingState['step'] ?? '') === 'confirming' && $isCancelCommand) {
                    session()->forget('chatbot_ordering');
                    $orderingState = null;
                    $orderingContext = $enforceBangla
                        ? "IN-CHAT ORDERING CANCELLED: The customer cancelled their pending order. Politely acknowledge that the order was not placed, and ask how else we can assist them."
                        : "IN-CHAT ORDERING CANCELLED: The customer cancelled their pending order. Politely acknowledge that the order was not placed, and ask how else we can assist them.";
                } elseif (($orderingState['step'] ?? '') === 'confirming' && $isConfirmCommand) {
                    $prod = DB::table('products')->where('id', $orderingState['product_id'])->where('is_active', 1)->first();

                    if (!$prod || $prod->stock_qty < $orderingState['quantity']) {
                        $orderingContext = "IN-CHAT ORDERING ERROR: Product is out of stock or does not have enough inventory for {$orderingState['quantity']} units. Apologize to customer and inform them.";
                        session()->forget('chatbot_ordering');
                    } else {
                        $todayPrefix = 'ZB-' . date('ymd') . '-';
                        $subtotal = $orderingState['product_price'] * $orderingState['quantity'];
                        $shippingCost = ($orderingState['district'] === 'ঢাকার বাইরে') ? $outsideDhakaFee : $insideDhakaFee;
                        $grandTotal = $subtotal + $shippingCost;
                        $orderNumber = '';

                        $orderId = DB::transaction(function () use (
                            $todayPrefix,
                            $orderingState,
                            $prod,
                            $subtotal,
                            $shippingCost,
                            $grandTotal,
                            $request,
                            &$orderNumber
                        ) {
                            $orderNumber = OrderNumberService::generate();

                            $ip = $request->ip() ?: '127.0.0.1';
                            $uId = auth()->id();

                            $newId = DB::table('orders')->insertGetId([
                                'order_number' => $orderNumber,
                                'user_id' => $uId,
                                'device_token' => $request->input('device_token') ?: Str::random(32),
                                'is_guest' => $uId ? 0 : 1,
                                'customer_name' => $orderingState['customer_name'] ?: 'Valued Customer',
                                'customer_phone' => $orderingState['customer_phone'],
                                'customer_address' => $orderingState['delivery_address'],
                                'district' => $orderingState['district'] ?: 'ঢাকা',
                                'subtotal' => $subtotal,
                                'shipping_cost' => $shippingCost,
                                'discount' => 0.00,
                                'total' => $grandTotal,
                                'payment_method' => 'cod',
                                'payment_status' => 'pending',
                                'order_status' => 'pending',
                                'ip_address' => $ip,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ]);

                            $productTitle = $orderingState['variant']
                                ? $prod->title . ' - ' . $orderingState['variant']
                                : $prod->title;

                            DB::table('order_items')->insert([
                                'order_id' => $newId,
                                'product_id' => $prod->id,
                                'product_title' => $productTitle,
                                'product_image' => $prod->main_image,
                                'unit_price' => $orderingState['product_price'],
                                'quantity' => $orderingState['quantity'],
                                'total_price' => $subtotal,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ]);

                            DB::table('products')->where('id', $prod->id)->decrement('stock_qty', $orderingState['quantity']);

                            return $newId;
                        });

                        $createdOrderData = [
                            'order_id' => $orderId,
                            'order_number' => $orderNumber,
                            'product_title' => $orderingState['product_title'],
                            'quantity' => $orderingState['quantity'],
                            'total' => $grandTotal,
                            'delivery_address' => $orderingState['delivery_address'],
                            'customer_phone' => $orderingState['customer_phone'],
                            'payment_method' => 'Cash on Delivery (COD)',
                            'estimated_delivery' => ($orderingState['district'] === 'ঢাকার বাইরে') ? '2-4 Days' : '24-48 Hours',
                        ];

                        $orderingContext = "IN-CHAT ORDER CREATION SUCCESSFUL IN DATABASE:\n"
                            . "- Order Number: {$orderNumber}\n"
                            . "- Product: {$orderingState['product_title']} (Qty: {$orderingState['quantity']})\n"
                            . "- Total: ৳" . number_format($grandTotal, 2) . " (Cash on Delivery)\n"
                            . "INSTRUCTION: A celebratory Order Placed Card is displayed in the chat UI. Congratulate the customer warmly in 1-2 sentences with their Order Number ({$orderNumber}). Mention payment is Cash on Delivery.";

                        try {
                            SendMetaCapiEventJob::dispatch('Purchase', [
                                'order_number' => $orderNumber,
                                'event_id' => 'order_' . $orderNumber,
                                'email' => null,
                                'phone' => $orderingState['customer_phone'],
                                'name' => $orderingState['customer_name'],
                                'city' => $orderingState['district'] ?: 'ঢাকা',
                                'value' => (float) $grandTotal,
                                'currency' => 'BDT',
                                'client_ip_address' => $ip,
                                'client_user_agent' => $request->header('User-Agent') ?: 'Browser',
                                'fbp' => $request->cookie('_fbp'),
                                'fbc' => $request->cookie('_fbc') ?: ($request->input('fbclid') ? 'fb.1.' . time() . '.' . $request->input('fbclid') : null),
                                'event_source_url' => $request->fullUrl(),
                                'contents' => [
                                    [
                                        'id' => (string) $prod->id,
                                        'quantity' => (int) $orderingState['quantity'],
                                        'item_price' => (float) $unitPrice,
                                    ]
                                ],
                            ]);
                        } catch (Throwable $e) {
                        }

                        session()->forget('chatbot_ordering');
                        $orderingState = null;
                    }
                } else {
                    $missing = [];
                    if (empty($orderingState['product_id'])) {
                        $missing[] = 'Product name';
                    }
                    if (empty($orderingState['customer_name'])) {
                        $missing[] = 'Customer full name';
                    }
                    if (empty($orderingState['customer_phone'])) {
                        $missing[] = 'Phone number';
                    }
                    if (empty($orderingState['delivery_address']) || preg_match('/^[0-9\s\-\+\(\)]+$/', $orderingState['delivery_address'])) {
                        $missing[] = 'Full street delivery address';
                    }

                    if (empty($missing)) {
                        $orderingState['step'] = 'confirming';
                        session(['chatbot_ordering' => $orderingState]);

                        $subtotal = $orderingState['product_price'] * $orderingState['quantity'];
                        $shippingCost = ($orderingState['district'] === 'ঢাকার বাইরে') ? $outsideDhakaFee : $insideDhakaFee;
                        $total = $subtotal + $shippingCost;

                        $orderingContext = "IN-CHAT ORDER READY FOR CONFIRMATION (ALL DETAILS COLLECTED):\n"
                            . "- Product: {$orderingState['product_title']} (Quantity: {$orderingState['quantity']})\n"
                            . "- Name: {$orderingState['customer_name']}\n"
                            . "- Phone: {$orderingState['customer_phone']}\n"
                            . "- Address: {$orderingState['delivery_address']} ({$orderingState['district']})\n"
                            . "- Subtotal: ৳" . number_format($subtotal, 2) . "\n"
                            . "- Delivery Fee: ৳" . number_format($shippingCost, 2) . "\n"
                            . "- Total Payable: ৳" . number_format($total, 2) . " (Cash on Delivery)\n"
                            . "CRITICAL INSTRUCTION: The order is NOT yet placed or confirmed in the database! DO NOT say 'অর্ডার কনফার্ম করে দিয়েছি' or 'Order confirmed'! Present this clean Order Summary to the customer in clear Bengali script (বাংলা লিপি). Then ask them to confirm: 'ভাইয়া, আপনার অর্ডারের সকল তথ্য ঠিক থাকলে নিচে 'অর্ডার কনফার্ম করুন' বাটনে চাপুন অথবা 'হ্যাঁ / কনফার্ম' লিখে রিপ্লাই দিন—আমরা সাথে সাথে আপনার অর্ডারটি কনফার্ম করে নেব।'";
                    } else {
                        $orderingState['step'] = 'gathering';
                        session(['chatbot_ordering' => $orderingState]);

                        $orderingContext = "IN-CHAT ORDERING IN PROGRESS:\n"
                            . "- Selected Product: " . ($orderingState['product_title'] ?: 'Not selected yet') . "\n"
                            . "- Name: " . ($orderingState['customer_name'] ?: 'Pending') . "\n"
                            . "- Phone: " . ($orderingState['customer_phone'] ?: 'Pending') . "\n"
                            . "- Address: " . ($orderingState['delivery_address'] ?: 'Pending') . "\n"
                            . "- Missing Fields: " . implode(', ', $missing) . "\n"
                            . "INSTRUCTION: Politely guide the customer to provide the missing fields (" . implode(', ', $missing) . ") so we can process their order.";
                    }
                }
            }
        } elseif ($intent === 'POLICY_DELIVERY') {
            $policyContext = "STORE POLICY - DELIVERY DETAILS:\n"
                . "- Inside Dhaka: ৳" . number_format($insideDhakaFee, 0) . " delivery charge. Estimated delivery time: 24 to 48 hours.\n"
                . "- Outside Dhaka: ৳" . number_format($outsideDhakaFee, 0) . " delivery charge. Estimated delivery time: 2 to 4 days across all 64 districts in Bangladesh.\n"
                . "- Courier Partners: Steadfast, Pathao, RedX Express.\n"
                . "- Cash on Delivery: Available across all districts. Customers pay after receiving the package.\n"
                . "DIRECTIVE: Immediately and directly state the exact delivery fees: Inside Dhaka ৳" . number_format($insideDhakaFee, 0) . " (24-48 hours) and Outside Dhaka ৳" . number_format($outsideDhakaFee, 0) . " (2-4 days). Mention nationwide Cash on Delivery.";
        } elseif ($intent === 'POLICY_PAYMENT') {
            $policyContext = "STORE POLICY - PAYMENT & COD:\n"
                . "- Cash on Delivery (COD): 100% available all over Bangladesh across all 64 districts.\n"
                . "- Advance Payment: No advance payment required for regular orders. Customers pay the delivery agent in cash upon receiving the parcel.\n"
                . "- Digital Payment Option: bKash, Nagad, and Rocket payments are accepted if preferred.\n"
                . "DIRECTIVE: Assure the customer that Cash on Delivery is completely supported without any advance hassle.";
        } elseif ($intent === 'POLICY_WARRANTY') {
            $policyContext = "STORE POLICY - WARRANTY & GUARANTEE:\n"
                . "- Official Replacement Warranty: 7 days full replacement warranty for any manufacturing defect or damage in transit.\n"
                . "- Check on Delivery: Customers can inspect the package in front of the courier delivery rider.\n"
                . "- Authentic Products: All items sold on {$storeName} are 100% genuine, brand new, and sealed.\n"
                . "DIRECTIVE: Explain the 7-day replacement warranty and return assurance reassuringly.";
        } elseif ($intent === 'POLICY_STORE') {
            $stockSummary = $this->getActiveStockSummary($enforceBangla ? 'bn' : 'en');
            $summaryText = $enforceBangla ? $stockSummary['summary_bn'] : $stockSummary['summary_en'];
            $policyContext = "STORE PROFILE - {$storeName}:\n"
                . "- {$storeName} is a trusted Bangladeshi online gadget store specializing in 100% genuine products, currently featuring {$summaryText}.\n"
                . "- Authenticity: 100% genuine and original brand new products.\n"
                . "- Orders & Delivery: Orders are placed online via our website or right here in this chat assistant, with nationwide fast delivery.\n"
                . "DIRECTIVE: Answer courteously about {$storeName}'s authenticity, available stock ({$summaryText}), and ordering process.";
        } elseif ($intent === 'GREETING') {
            $policyContext = "CUSTOMER GREETING CONTEXT:\n"
                . "The customer sent a friendly greeting. Reply warmly and courteously in the customer's exact language (Bengali / Banglish / English). Welcome them to {$storeName} and offer assistance with products, specs, delivery charges, or order tracking.";
        } elseif ($intent === 'ABUSIVE_OR_PROFANITY') {
            $stockSummary = $this->getActiveStockSummary($enforceBangla ? 'bn' : 'en');
            $summaryText = $enforceBangla ? $stockSummary['summary_bn'] : $stockSummary['summary_en'];
            $policyContext = "CUSTOMER SENT HARSH, PROFANE, OR SLANG LANGUAGE:\n"
                . "The customer said: '{$userMessage}'.\n"
                . "HUMAN SUPPORT SPECIALIST DIRECTIVE:\n"
                . "- DO NOT treat this as a product search or item name under any circumstances! DO NOT say 'এই নামের কোনো প্রোডাক্ট নেই' or talk about procurement teams!\n"
                . "- Respond with extreme poise, warmth, patience, and professional empathy, like a mature customer support manager handling an agitated or playful user.\n"
                . "- De-escalate with kindness and dignity in natural Bengali script: 'ভাইয়া, শান্ত হোন প্লিজ! আমরা সবসময় আপনাদের সেরা সেবা দিতে চাই। আমাদের কোনো সার্ভিস বা অর্ডার সংক্রান্ত কোনো সমস্যা হয়ে থাকলে দয়া করে বলুন, আমি আন্তরিকভাবে সমাধান করে দিচ্ছি।' or if it's casual slang, playfully and politely guide them back: 'ভাইয়া, আসুন আমরা হাসিমুখে কথা বলি! আপনি কি নির্দিষ্ট কোনো গ্যাজেট বা ডেলিভারি তথ্য জানতে চাচ্ছেন?'\n"
                . "- NEVER repeat or validate the offensive words.";
        } elseif ($intent === 'CHIT_CHAT') {
            $stockSummary = $this->getActiveStockSummary($enforceBangla ? 'bn' : 'en');
            $summaryText = $enforceBangla ? $stockSummary['summary_bn'] : $stockSummary['summary_en'];
            $policyContext = "CASUAL CONVERSATION & CHIT-CHAT:\n"
                . "The customer said: '{$userMessage}'.\n"
                . "HUMAN AGENT DIRECTIVE:\n"
                . "- DO NOT treat this as a product search! DO NOT say 'এই নামের কোনো প্রোডাক্ট নেই'!\n"
                . "- Respond warmly, naturally, and playfully like a real friendly person (not a robot).\n"
                . "- If they commented or complained about repeating 'জি ভাইয়া' or talking like a bot (e.g. 'জি ভাইয়া, জি ভাইয়া, sob sms a just জি ভাইয়া'): Laugh warmly and apologize like a genuine person: 'হা হা, একদম ঠিক বলেছেন! সরি, অভ্যাস হয়ে গিয়েছিল। এবার থেকে একদম সোজাসুজি স্বাভাবিক কথা বলছি। বলুন, কোন গ্যাজেট নিয়ে জানতে চাচ্ছিলেন?' and NEVER begin the reply with 'জি ভাইয়া'!\n"
                . "- If they expressed gratitude ('thank you', 'ধন্যবাদ'), say 'মোস্ট ওয়েলকাম ভাইয়া! আপনার পাশে থাকতে পেরে ভালো লাগছে।'\n"
                . "- If they asked who you are, say you are Zippy's friendly digital gadget assistant here to help them find genuine gadgets and track orders.\n"
                . "- If they made a joke or small talk, respond with a smile and warmly mention what gadgets we currently have in stock ({$summaryText}) if they want to take a look.\n"
                . "- If they said goodbye ('bye', 'allah hafez'), wish them a wonderful time ahead.";
        } else {
            $searchIntent = $this->extractProductSearchIntent($userMessage);
            $detectedCategory = $searchIntent['category'] ?? null;
            $isExploreNewProducts = (bool) preg_match('/(?:show\s*(?:other|all|more)|recommend|browse|other\s*products|another|নতুন|অন্যান্য|অন্য|আর\s*কি\s*আছে|arek\s*ta|onno\s*kisu|bhalo\s*arekta|notun\s*item|dekhao|suggest)/iu', $userMessage);

            $activeProductId = $activity['current_product']['id'] ?? session()->get('chatbot_last_product_id');
            $candidateProduct = null;
            if (!empty($activeProductId)) {
                $candidateProduct = DB::table('products')->where('id', $activeProductId)->where('is_active', 1)->first();
            }

            if (!$candidateProduct && !empty($sessionHistory)) {
                $allActiveProducts = DB::table('products')->where('is_active', 1)->get();
                foreach (array_reverse($sessionHistory) as $h) {
                    $content = $h['content'] ?? '';
                    foreach ($allActiveProducts as $p) {
                        if (stripos($content, $p->title) !== false || (!empty($p->slug) && stripos($content, $p->slug) !== false)) {
                            $candidateProduct = $p;
                            session(['chatbot_last_product_id' => $p->id]);
                            break 2;
                        }
                    }
                }
            }

            if (!$candidateProduct && !empty($activity['recently_viewed']) && is_array($activity['recently_viewed'])) {
                foreach ($activity['recently_viewed'] as $rSlug) {
                    $p = DB::table('products')->where('slug', $rSlug)->where('is_active', 1)->first();
                    if ($p) {
                        $candidateProduct = $p;
                        session(['chatbot_last_product_id' => $p->id]);
                        break;
                    }
                }
            }

            $isSwitchingProduct = false;
            if ($isExploreNewProducts) {
                $isSwitchingProduct = true;
            } elseif (!empty($detectedCategory) && $candidateProduct) {
                $candText = mb_strtolower($candidateProduct->title . ' ' . $candidateProduct->tag . ' ' . $candidateProduct->short_desc);
                $catMatchesCurrent = match ($detectedCategory) {
                    'watch' => (bool) preg_match('/(watch|smartwatch|wearable|ঘড়ি|ঘড়ি|স্মার্টওয়াচ)/iu', $candText),
                    'audio' => (bool) preg_match('/(earphone|headphone|earbud|tws|audio|sound|হেডফোন|ইয়ারফোন)/iu', $candText),
                    'keyboard' => (bool) preg_match('/(keyboard|mechanical|কীবোর্ড|কিবোর্ড)/iu', $candText),
                    'mouse' => (bool) preg_match('/(mouse|মাউস)/iu', $candText),
                    default => false,
                };
                if (!$catMatchesCurrent) {
                    $isSwitchingProduct = true;
                }
            }

            $isBargaining = (bool) preg_match('/(?:kom|komano|koman|koma|komay|kombe|discount|kicuto\s*kom|aro\s*kom|dam\s*kom|price\s*kom|fixed|rate\s*kom|bargain|possible|posible|কম|কমান|কমানো|কমবে|ডিসকাউন্ট|ফিক্সড|কিছুটা\s*কম|দাম\s*কম)/iu', $userMessage);

            if ($candidateProduct && !$isSwitchingProduct) {
                session(['chatbot_last_product_id' => $candidateProduct->id]);
                $specsExcerpt = '';
                if (!empty($candidateProduct->specifications)) {
                    $decodedSpecs = json_decode($candidateProduct->specifications, true);
                    if (is_array($decodedSpecs)) {
                        $specsExcerpt = ' | Specs: ' . json_encode($decodedSpecs, JSON_UNESCAPED_UNICODE);
                    }
                }

                if ($isBargaining) {
                    $productContext = "ACTIVE PRODUCT IN CONTEXT (CUSTOMER BARGAINING / ASKING FOR DISCOUNT ON THIS ITEM):\n"
                        . "- Title: {$candidateProduct->title}\n"
                        . "- Price: ৳" . number_format($candidateProduct->price, 2) . " (Fixed Price)\n"
                        . "- Stock: {$candidateProduct->stock_qty} available\n"
                        . "- Short Desc: {$candidateProduct->short_desc}{$specsExcerpt}\n"
                        . "DIRECTIVE FOR BARGAINING:\n"
                        . "- The customer is asking if the price can be reduced or if there is a discount ('{$userMessage}') for {$candidateProduct->title}.\n"
                        . "- Respond with human charm, warmth, and persuasive salesmanship: Politely explain in sweet Bengali script that our price of ৳" . number_format($candidateProduct->price, 0) . " is rock-bottom and 100% fixed, because we strictly sell 100% original, brand new, sealed-box items.\n"
                        . "- Highlight our unmatched safety: 100% Cash on Delivery across all 64 districts in Bangladesh (the customer can inspect the product in front of the courier rider before paying!) + official 7-day replacement warranty.\n"
                        . "- Warmly ask if they would like to place their order for {$candidateProduct->title}.";
                } else {
                    $productContext = "ACTIVE PRODUCT IN CONTEXT (CUSTOMER INQUIRY ABOUT THIS ITEM):\n"
                        . "- Title: {$candidateProduct->title}\n"
                        . "- Price: ৳" . number_format($candidateProduct->price, 2) . "\n"
                        . "- Stock: {$candidateProduct->stock_qty} available\n"
                        . "- Short Desc: {$candidateProduct->short_desc}{$specsExcerpt}\n"
                        . "DIRECTIVE: The customer is asking a question or follow-up ('{$userMessage}') about this specific item. Answer accurately, concisely, and conversationally from the details above without forcing them to re-state the product name.";
                }
                $suggestedProducts = [];
            } elseif (!$candidateProduct && $isBargaining) {
                $allAvailable = DB::table('products')->where('is_active', 1)->get(['id', 'title', 'price']);
                $availList = [];
                foreach ($allAvailable as $ap) {
                    $availList[] = "{$ap->title} (৳" . number_format($ap->price, 0) . ")";
                }
                $availListStr = implode(', ', $availList);
                $stockSummary = $this->getActiveStockSummary($enforceBangla ? 'bn' : 'en');
                $summaryText = $enforceBangla ? $stockSummary['summary_bn'] : $stockSummary['summary_en'];

                $productContext = "CUSTOMER ASKING ABOUT DISCOUNT / BARGAINING WITHOUT SPECIFYING A PRODUCT:\n"
                    . "The customer said: '{$userMessage}'.\n"
                    . "CRITICAL MANDATORY DIRECTIVE:\n"
                    . "- Under NO circumstances treat this as an out-of-stock product search! '{$userMessage}' is a price bargaining inquiry, NOT a product name!\n"
                    . "- Politely and warmly ask the customer in natural Bengali script which product's price they are inquiring about.\n"
                    . "- Mention that our currently available products in stock are: {$availListStr}.\n"
                    . "- Explain that at {$storeName}, all products are 100% genuine and sealed-pack with rock-bottom fixed pricing, nationwide Cash on Delivery with parcel inspection before payment, and 7-day replacement warranty.\n"
                    . "- Ask which one they would like to explore or order.";
                $suggestedProducts = [];
            } else {
                $foundProducts = $this->findRelevantProducts($searchIntent);

                if (!empty($foundProducts)) {
                    session(['chatbot_last_product_id' => $foundProducts[0]->id]);

                    $productLines = [];
                    foreach ($foundProducts as $prod) {
                        $stockText = $prod->stock_qty > 0 ? "In Stock ({$prod->stock_qty} available)" : "Out of Stock";
                        $url = url('/product/' . $prod->slug);
                        $specsExcerpt = '';
                        if (!empty($prod->specifications)) {
                            $decodedSpecs = json_decode($prod->specifications, true);
                            if (is_array($decodedSpecs)) {
                                $specsExcerpt = ' | Specs: ' . json_encode($decodedSpecs, JSON_UNESCAPED_UNICODE);
                            }
                        }
                        $productLines[] = "- Title: {$prod->title} | Price: ৳" . number_format($prod->price, 2) . " | Stock: {$stockText} | Short Desc: {$prod->short_desc}{$specsExcerpt} | URL: {$url}";

                        $variantsList = [];
                        if (!empty($prod->variants)) {
                            $decodedVars = is_string($prod->variants) ? json_decode($prod->variants, true) : $prod->variants;
                            if (is_array($decodedVars)) {
                                foreach ($decodedVars as $v) {
                                    if (is_array($v)) {
                                        $vName = $v['name'] ?? ($v['color_name'] ?? ($v['title'] ?? ''));
                                        $vPrice = isset($v['price']) ? (float) $v['price'] : (float) $prod->price;
                                        if (!empty($vName)) {
                                            $variantsList[] = [
                                                'name' => (string) $vName,
                                                'price' => $vPrice,
                                            ];
                                        }
                                    } elseif (is_string($v) && trim($v) !== '') {
                                        $variantsList[] = [
                                            'name' => trim($v),
                                            'price' => (float) $prod->price,
                                        ];
                                    }
                                }
                            }
                        }

                        $suggestedProducts[] = [
                            'id' => $prod->id,
                            'title' => $prod->title,
                            'slug' => $prod->slug,
                            'price' => (float) $prod->price,
                            'old_price' => $prod->old_price ? (float) $prod->old_price : null,
                            'in_stock' => $prod->stock_qty > 0,
                            'stock_qty' => (int) $prod->stock_qty,
                            'main_image' => $prod->main_image ?: null,
                            'url' => $url,
                            'variants' => $variantsList,
                        ];
                    }
                    $productContext = "RELEVANT STORE PRODUCTS FOUND IN CATALOG:\n" . implode("\n", $productLines) . "\nRecommend only these products when answering.";
                } else {
                    $detectedCat = $searchIntent['category'] ?? null;
                    $cleanTerm = trim($searchIntent['clean_query'] ?? $userMessage);
                    if (mb_strlen($cleanTerm) >= 3 && !preg_match('/^(hi|hello|hey|ok|hmm|bye)$/i', $cleanTerm)) {
                        $this->recordUnmetDemand($userMessage, $detectedCat, $request);
                    }

                    $stockSummary = $this->getActiveStockSummary($enforceBangla ? 'bn' : 'en');
                    $summaryText = $enforceBangla ? $stockSummary['summary_bn'] : $stockSummary['summary_en'];
                    $productContext = "CATALOG LOOKUP: No products currently matching '{$userMessage}' in stock.\n"
                        . "HUMAN SALES ADVISOR DIRECTIVE:\n"
                        . "- Answer like a helpful shop manager: Politely apologize that this specific item is currently not available in our stock.\n"
                        . "- DO NOT use robotic bureaucratic phrases like 'আমাদের প্রকিউরমেন্ট টিম এই ডিমান্ডটি নোট করে রেখেছে এবং ভবিষ্যতে যোগ করার বিষয়টি বিবেচনা করবে'. Speak like a real human shopkeeper!\n"
                        . "- Mention that {$storeName}-এ বর্তমানে ১০০% অথেনটিক {$summaryText} রেডি স্টকে আছে এবং ক্যাশ অন ডেলিভারিতে পাওয়া যাচ্ছে।\n"
                        . "- Warmly ask if they would like to explore these or if they have a specific model or budget in mind.";
                }
            }
        }

        $userActivityContext = "LIVE CUSTOMER BROWSING ACTIVITY:\n"
            . "- Current Page: " . ($activity['page_title'] ?: ($activity['current_url'] ?: 'Browsing Zippy')) . "\n"
            . "- Page Type: " . ($activity['page_type'] ?? 'general') . "\n";

        if (!empty($activity['current_product'])) {
            $userActivityContext .= "- Active Product on Screen: " . ($activity['current_product']['title'] ?? '') . " (৳" . ($activity['current_product']['price'] ?? '') . ")\n";
        }

        if (!empty($activity['cart_count'])) {
            $userActivityContext .= "- Cart Count: {$activity['cart_count']} item(s)\n";
        }

        if (auth()->check()) {
            $userActivityContext .= "- Authenticated Customer: " . auth()->user()->name . "\n";
        }

        $languageDirective = "";
        if ($enforceBangla) {
            $languageDirective = "MANDATORY LANGUAGE RULE: BENGALI SCRIPT (বাংলা লিপি) ONLY\n"
                . "- You MUST respond strictly in natural, polite Bengali written in BENGALI SCRIPT (বাংলা লিপি).\n"
                . "- NEVER use Romanized Banglish under any circumstances (NEVER write broken phrases like 'price kom hote parbe na, bhai', 'Ami 100% original product diye thaki', 'Apnar order ta confirm kore dilam', etc.).\n"
                . "- Even if the customer writes in Banglish (e.g. 'price ki kichuta kom a hobe?', 'ok amr pochondo hoise nibo aita', 'watch?', 'kobe pabo', 'bhai dam koto'), YOUR REPLY MUST BE IN CLEAR, SWEET BENGALI SCRIPT (বাংলা লিপি).\n"
                . "- Technical brand and model names may remain in English (e.g. 'Haylou Watch Faro', 'Cash on Delivery').\n\n";
        } else {
            $languageDirective = "LANGUAGE PREFERENCE: ENGLISH\n"
                . "- The customer has selected English or communicates in fluent English. Respond in polite, natural English.\n\n";
        }

        $stockSummary = $this->getActiveStockSummary($enforceBangla ? 'bn' : 'en');
        $summaryText = $enforceBangla ? $stockSummary['summary_bn'] : $stockSummary['summary_en'];
        $systemPrompt = "You are the warm, highly intelligent, attentive, and empathetic sales & support advisor for '{$storeName}', Bangladesh's trusted store for 100% authentic gadgets and accessories (currently featuring: {$summaryText}).\n\n"
            . "{$userActivityContext}\n"
            . "{$languageDirective}"
            . "HUMAN-LIKE SALES ADVISOR PERSONALITY & BEHAVIOR:\n"
            . "- You talk like a real caring human sales manager chatting on WhatsApp or in a boutique tech shop—NOT like an emotionless robotic script.\n"
            . "- NATURAL HUMAN TONE (ZERO REPETITIVE ROBOTIC PREFIXES):\n"
            . "  * ABSOLUTELY DO NOT begin every single message with 'জি ভাইয়া' or 'জি ভাইয়া'! Repeating 'জি ভাইয়া' in every reply sounds irritating, robotic, and unnatural.\n"
            . "  * Vary your sentence openings naturally like a real human shopkeeper:\n"
            . "    - Start straight with the answer: 'HAYLOU Watch Faro-র বর্তমান অফিশিয়াল প্রাইজ ৩,২৯৯ টাকা...'\n"
            . "    - Use varied conversational openers: 'আসলে...', 'দেখুন...', 'হাঁ অবশ্যই,', 'নিশ্চিন্ত থাকুন,', 'অবশ্যই!', 'ধন্যবাদ!', etc.\n"
            . "    - Use 'ভাইয়া' or their name naturally inside the sentence (e.g. 'চিন্তা করবেন না ভাইয়া...'), NEVER as an automatic reflex at the beginning of every single reply.\n"
            . "  * If the customer complains about repeating 'জি ভাইয়া' (e.g. 'জি ভাইয়া, জি ভাইয়া, sob sms a just জি ভাইয়া'): Laugh warmly and apologize like a real human ('হা হা, একদম ঠিক বলেছেন! সরি, একটা বাজে অভ্যাস হয়ে গিয়েছিল। এবার থেকে একদম সোজাসুজি ও স্বাভাবিকভাবে কথা বলছি।'), and address what they want without saying 'জি ভাইয়া'!\n"
            . "- Natural human conversational intelligence:\n"
            . "  * Understand slang, swear words, anger, humor, jokes, and emotional expressions naturally like an experienced human shopkeeper.\n"
            . "  * NEVER treat swear words, profanities, or casual expressions as product names! NEVER say things like \"'Fuck' নামে কোনো প্রোডাক্ট নেই\" or mention procurement teams for such words.\n"
            . "  * If a customer uses foul language, curses, or insults: stay completely calm, polite, respectful, and dignified. Never get offended, never repeat the vulgar words. De-escalate with kindness: ask with genuine concern if they experienced an issue with an order or service so you can help them right away, or politely invite them to keep the conversation respectful.\n"
            . "  * When a customer makes small talk ('kemon acho', 'tumi ke', 'dhonnobad', 'kisu na'): respond playfully, warmly, and naturally, like a friendly human companion.\n"
            . "  * Only when a customer explicitly asks for an unavailable gadget or model by name (e.g. 'iPhone 16', 'Sony WH-1000XM5'): explain politely like a real human shopkeeper that we don't have that model right now, and suggest our in-stock {$summaryText}. NEVER use this out-of-stock phrasing for bargaining, discounts, price questions, or greetings!\n"
            . "- You have active situational awareness: you know what page and product the customer is looking at right now. If they say 'eta koto?', 'bhalo hobe?', 'delivery kobe pabo?', they mean the active item on their screen.\n"
            . "- When customers bargain, ask for a discount, or ask if it is original ('price ki kichuta kom a hobe?', 'dam komano jay?', 'original to?'):\n"
            . "  * NEVER give blunt, cold, or robotic rejections (NEVER say 'Hm, price ta aro kom hote parbe na').\n"
            . "  * Respond with human charm, warmth, and persuasive value: Explain politely that all our products are 100% original and brand new in sealed box, with rock-bottom fixed pricing.\n"
            . "  * Emphasize the unique trust factor: We offer 100% Cash on Delivery across all 64 districts in Bangladesh—the customer can open and check the product in front of the courier rider before paying a single taka! Plus, an official 7-day replacement warranty for complete peace of mind.\n"
            . "- When customers express buying intent ('ok amr pochondo hoise nibo aita', 'order korte chai'):\n"
            . "  * Compliment their choice enthusiastically and guide them to provide Name, Phone, and Delivery Address.\n"
            . "- Match the customer's conversation pace and length: do not dump giant unrequested bullet lists for simple questions. Keep replies concise, engaging, and conversational.\n\n"
            . "STORE POLICIES:\n"
            . "- Delivery: Inside Dhaka ৳" . number_format($insideDhakaFee, 0) . " (24-48 hours), Outside Dhaka ৳" . number_format($outsideDhakaFee, 0) . " (2-4 days across all 64 districts).\n"
            . "- Payment: 100% Cash on Delivery (COD) across all 64 districts. No advance payment required for regular orders.\n"
            . "- Warranty: 7-day official replacement warranty for manufacturing defects.";

        if (!empty($policyContext)) {
            $systemPrompt .= "\n\n" . $policyContext;
        }

        if (!empty($orderContext)) {
            $systemPrompt .= "\n\n" . $orderContext;
        }

        if (!empty($orderingContext)) {
            $systemPrompt .= "\n\n" . $orderingContext;
        }

        if (!empty($productContext)) {
            $systemPrompt .= "\n\n" . $productContext;
        }

        $recentHistory = array_slice($sessionHistory, -6);
        $messagesForApi = [];
        foreach ($recentHistory as $item) {
            if (isset($item['role']) && isset($item['content'])) {
                $messagesForApi[] = [
                    'role' => $item['role'],
                    'content' => $item['content'],
                ];
            }
        }
        $messagesForApi[] = [
            'role' => 'user',
            'content' => $userMessage,
        ];

        try {
            $reply = $this->aiService->chatWithFailover(
                $messagesForApi,
                $systemPrompt,
                'customer_qa',
                800,
                0.4
            );
        } catch (\Throwable $e) {
            Log::warning('Chatbot AI Gateway Throttled: ' . $e->getMessage());
            if (!empty($suggestedProducts)) {
                $reply = $enforceBangla
                    ? "আমাদের AI অ্যাসিস্ট্যান্ট সাময়িকভাবে ব্যস্ত, তবে আপনার পছন্দের ভিত্তিতে নিচের সেরা প্রোডাক্টগুলো খুঁজে পেয়েছি:"
                    : "Our assistant is currently busy, but here are top recommended products matching your interest:";
            } else {
                $reply = $enforceBangla
                    ? "জি ভাইয়া, আমি আপনাকে সাহায্য করতে প্রস্তুত! আপনি কি নির্দিষ্ট কোনো গ্যাজেট খুঁজছেন? আমাদের হটলাইনেও যোগাযোগ করতে পারেন: " . ($settings['phone'] ?? '01700-000000')
                    : "Yes! I am here to help you. Are you looking for any specific gadget? You can also reach our hotline: " . ($settings['phone'] ?? '01700-000000');
            }
        }

        $dynamicChips = $this->generateDynamicFollowUpChips(
            $intent,
            $activity,
            $suggestedProducts,
            $reply,
            $createdOrderData,
            $orderingState,
            $enforceBangla ? 'bn' : 'en'
        );

        if ($intent === 'PRODUCT_QUERY' && !$isBargaining && preg_match('/(ক্যাটালগে\s*(?:পাওয়া|নেই|পাচ্ছেন)|স্টকে\s*নেই|not\s*(?:in|available\s*in)\s*our\s*catalog|currently\s*unavailable\s*in\s*our\s*catalog)/iu', $reply)) {
            $this->recordUnmetDemand($userMessage, $detectedCat ?? null, $request);
        }

        $timeNow = date('h:i A');
        $sessionHistory[] = ['role' => 'user', 'content' => $userMessage, 'time' => $timeNow];
        $sessionHistory[] = ['role' => 'assistant', 'content' => $reply, 'time' => $timeNow];
        session(['chatbot_history' => array_slice($sessionHistory, -10)]);

        return response()->json([
            'success' => true,
            'reply' => $reply,
            'chips' => $dynamicChips,
            'products' => $suggestedProducts,
            'order_card' => $orderStatusData,
            'order_status' => $orderStatusData,
            'orders_list' => $ordersListData,
            'created_order' => $createdOrderData,
            'ordering_state' => session()->get('chatbot_ordering'),
        ]);
    }

    public function quickOrder(Request $request)
    {
        $request->validate([
            'product_id' => 'required|integer',
            'customer_name' => 'required|string|max:191',
            'customer_phone' => 'required|string|max:20',
            'customer_address' => 'required|string|max:500',
            'district' => 'required|string|in:ঢাকা,ঢাকার বাইরে',
            'quantity' => 'nullable|integer|min:1|max:50',
            'variant' => 'nullable|string|max:100',
        ]);

        $rawPhone = trim((string) $request->input('customer_phone'));
        $digits = preg_replace('/[^0-9]/', '', $rawPhone);
        if (str_starts_with($digits, '8801')) {
            $digits = substr($digits, 2);
        }
        if (!preg_match('/^01[3-9]\d{8}$/', $digits)) {
            return response()->json([
                'success' => false,
                'message' => 'Please enter a valid 11-digit Bangladeshi mobile number (e.g. 017XXXXXXXX).',
            ], 422);
        }

        $productId = (int) $request->input('product_id');
        $qty = max(1, (int) $request->input('quantity', 1));
        $selectedVariant = trim((string) $request->input('variant', ''));
        $district = $request->input('district') === 'ঢাকার বাইরে' ? 'ঢাকার বাইরে' : 'ঢাকা';

        $settings = DB::table('settings')->pluck('value', 'key')->toArray();
        $shippingInside = isset($settings['shipping_inside_dhaka']) && is_numeric($settings['shipping_inside_dhaka'])
            ? (float) $settings['shipping_inside_dhaka']
            : 60.00;
        $shippingOutside = isset($settings['shipping_outside_dhaka']) && is_numeric($settings['shipping_outside_dhaka'])
            ? (float) $settings['shipping_outside_dhaka']
            : 120.00;
        $shippingCost = ($district === 'ঢাকার বাইরে') ? $shippingOutside : $shippingInside;

        $todayPrefix = 'ZB-' . date('ymd') . '-';
        $orderNumber = '';
        $orderId = 0;
        $productTitle = '';
        $unitPrice = 0.00;
        $subtotal = 0.00;
        $grandTotal = 0.00;
        $prodImage = null;

        try {
            DB::transaction(function () use (
                $productId,
                $qty,
                $selectedVariant,
                $district,
                $shippingCost,
                $todayPrefix,
                $digits,
                $request,
                &$orderNumber,
                &$orderId,
                &$productTitle,
                &$unitPrice,
                &$subtotal,
                &$grandTotal,
                &$prodImage
            ) {
                $prod = DB::table('products')->where('id', $productId)->where('is_active', 1)->lockForUpdate()->first();
                if (!$prod) {
                    throw new Exception('Product not found or currently unavailable.');
                }

                if ($prod->stock_qty < $qty) {
                    throw new Exception("Insufficient stock. Only {$prod->stock_qty} unit(s) available.");
                }

                $unitPrice = (float) $prod->price;
                if (!empty($selectedVariant) && !empty($prod->variants)) {
                    $decodedVars = is_string($prod->variants) ? json_decode($prod->variants, true) : $prod->variants;
                    if (is_array($decodedVars)) {
                        foreach ($decodedVars as $v) {
                            if (is_array($v)) {
                                $vName = $v['name'] ?? ($v['color_name'] ?? ($v['title'] ?? ''));
                                if ($vName === $selectedVariant && isset($v['price']) && is_numeric($v['price'])) {
                                    $unitPrice = (float) $v['price'];
                                    break;
                                }
                            }
                        }
                    }
                }

                $subtotal = $unitPrice * $qty;
                $grandTotal = $subtotal + $shippingCost;

                $orderNumber = OrderNumberService::generate();

                $userId = auth()->id();
                $ip = $request->ip() ?: '127.0.0.1';
                $userAgent = $request->header('User-Agent') ?: 'Browser';
                $deviceToken = $request->input('device_token') ?: Str::random(32);

                $orderId = DB::table('orders')->insertGetId([
                    'order_number' => $orderNumber,
                    'user_id' => $userId,
                    'device_token' => $deviceToken,
                    'is_guest' => $userId ? 0 : 1,
                    'customer_name' => trim($request->input('customer_name')),
                    'customer_phone' => $digits,
                    'customer_address' => trim($request->input('customer_address')),
                    'district' => $district,
                    'subtotal' => $subtotal,
                    'shipping_cost' => $shippingCost,
                    'discount' => 0.00,
                    'total' => $grandTotal,
                    'payment_method' => 'cod',
                    'payment_status' => 'pending',
                    'order_status' => 'pending',
                    'courier_provider' => 'Standard Express',
                    'courier_tracking_code' => 'Processing',
                    'courier_status' => 'Order Placed',
                    'ip_address' => $ip,
                    'user_agent' => $userAgent,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $productTitle = !empty($selectedVariant) ? ($prod->title . ' - ' . $selectedVariant) : $prod->title;
                $prodImage = $prod->main_image ?: '/favicon.ico';

                DB::table('order_items')->insert([
                    'order_id' => $orderId,
                    'product_id' => $prod->id,
                    'product_title' => $productTitle,
                    'product_image' => $prodImage,
                    'unit_price' => $unitPrice,
                    'quantity' => $qty,
                    'total_price' => $subtotal,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                DB::table('products')->where('id', $prod->id)->decrement('stock_qty', $qty);
            });
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }

        try {
            SendMetaCapiEventJob::dispatch('Purchase', [
                'order_number' => $orderNumber,
                'event_id' => 'order_' . $orderNumber,
                'email' => null,
                'phone' => $digits,
                'name' => trim($request->input('customer_name')),
                'city' => $district,
                'value' => (float) $grandTotal,
                'currency' => 'BDT',
                'client_ip_address' => $ip,
                'client_user_agent' => $userAgent,
                'fbp' => $request->cookie('_fbp'),
                'fbc' => $request->cookie('_fbc') ?: ($request->input('fbclid') ? 'fb.1.' . time() . '.' . $request->input('fbclid') : null),
                'event_source_url' => $request->fullUrl(),
                'contents' => [
                    [
                        'id' => (string) $prod->id,
                        'quantity' => (int) $qty,
                        'item_price' => (float) $unitPrice,
                    ]
                ],
            ]);
        } catch (Throwable $e) {
        }

        $estimatedDelivery = ($district === 'ঢাকার বাইরে') ? '2-4 Days' : '24-48 Hours';
        $trackingUrl = $this->buildCourierTrackingUrl('Standard Express', 'Processing', $orderNumber);

        $createdOrder = [
            'order_id' => $orderId,
            'order_number' => $orderNumber,
            'product_title' => $productTitle,
            'quantity' => $qty,
            'total' => $grandTotal,
            'delivery_address' => trim($request->input('customer_address')),
            'customer_phone' => $digits,
            'payment_method' => 'Cash on Delivery (COD)',
            'estimated_delivery' => $estimatedDelivery,
        ];

        $orderCard = [
            'order_number' => $orderNumber,
            'customer_name' => trim($request->input('customer_name')),
            'customer_phone_masked' => substr($digits, 0, 4) . '****' . substr($digits, -3),
            'order_status' => 'pending',
            'order_status_label' => 'Order Placed',
            'timeline_step' => 1,
            'payment_status' => 'Pending',
            'payment_method' => 'Cash on Delivery (COD)',
            'courier_provider' => 'Standard Express',
            'courier_tracking_code' => 'Processing',
            'courier_tracking_url' => $trackingUrl,
            'courier_status' => 'Order Placed',
            'order_date' => date('d M, Y'),
            'estimated_delivery' => $estimatedDelivery,
            'total' => (float) $grandTotal,
            'subtotal' => (float) $subtotal,
            'shipping_cost' => (float) $shippingCost,
            'items' => [
                [
                    'title' => $productTitle,
                    'image' => $prodImage,
                    'qty' => $qty,
                    'price' => $unitPrice,
                    'total' => $subtotal,
                ],
            ],
        ];

        session(['chatbot_last_order_number' => $orderNumber]);
        session(['last_order_code' => $orderNumber]);
        session(['chatbot_last_product_id' => $productId]);
        session()->forget('chatbot_ordering');

        try {
            SendOrderSmsJob::dispatch((int) $orderId, 'pending');
            SendOrderEmailJob::dispatch((int) $orderId);
        } catch (\Throwable $e) {
        }

        $timeNow = date('h:i A');
        $reply = "🎉 Congratulations " . trim($request->input('customer_name')) . "! Your order **#{$orderNumber}** for **{$productTitle}** (x{$qty}) has been confirmed via Cash on Delivery.\n\nTotal Payable: **৳" . number_format($grandTotal, 2) . "**.\nEstimated Delivery: **{$estimatedDelivery}**.\n\nThank you for choosing {$storeName}! Our dispatch team will contact you shortly before shipping.";

        $sessionHistory = session()->get('chatbot_history', []);
        if (!is_array($sessionHistory)) {
            $sessionHistory = [];
        }
        $sessionHistory[] = ['role' => 'assistant', 'content' => $reply, 'time' => $timeNow];
        session(['chatbot_history' => array_slice($sessionHistory, -10)]);

        $dynamicChips = [
            ['label' => "📦 অর্ডার #{$orderNumber} ট্র্যাক করুন", 'prompt' => "track order {$orderNumber}"],
            ['label' => '🚚 ডেলিভারি সংক্রান্ত নিয়ম', 'prompt' => 'ডেলিভারি চার্জ এবং সময় কত?'],
            ['label' => '💬 সাপোর্ট প্রতিনিধির সাথে যোগাযোগ', 'prompt' => 'কাস্টমার কেয়ার প্রতিনিধির সাথে কথা বলতে চাই'],
        ];

        return response()->json([
            'success' => true,
            'message' => 'Order placed successfully!',
            'reply' => $reply,
            'chips' => $dynamicChips,
            'order_number' => $orderNumber,
            'created_order' => $createdOrder,
            'order_card' => $orderCard,
            'order_status' => $orderCard,
            'estimated_delivery' => $estimatedDelivery,
        ]);
    }

    public function getHistory(Request $request)
    {
        $history = session()->get('chatbot_history', []);
        if (!is_array($history)) {
            $history = [];
        }

        $userLang = trim((string) $request->input('language', 'bn'));
        if (!in_array($userLang, ['bn', 'en'])) {
            $userLang = 'bn';
        }

        $activity = $this->parseUserActivity($request);
        $settings = DB::table('settings')->pluck('value', 'key')->toArray();
        $storeName = $settings['site_name'] ?? 'Zippy';

        $dynamicData = $this->generateDynamicGreeting($activity, $storeName, $userLang);

        return response()->json([
            'success' => true,
            'history' => $history,
            'ordering_state' => session()->get('chatbot_ordering'),
            'greeting' => $dynamicData['greeting'],
            'chips' => $dynamicData['chips'],
            'activity_context' => [
                'page_type' => $activity['page_type'],
                'product_title' => $activity['current_product']['title'] ?? null,
            ],
        ]);
    }

    public function clearHistory(Request $request)
    {
        session()->forget('chatbot_history');
        session()->forget('chatbot_ordering');
        session()->forget('chatbot_last_product_id');
        session()->forget('chatbot_last_order_number');

        return response()->json([
            'success' => true,
            'message' => 'Chat history and ordering state cleared successfully.',
        ]);
    }

    protected function recordUnmetDemand(string $query, ?string $detectedCategory, Request $request): void
    {
        $rawQuery = trim($query);
        if (mb_strlen($rawQuery) < 3) {
            return;
        }

        $isProfane = (bool) preg_match('/(?:^|[[:space:]]|[[:punct:]])(fuck|fck|f\*ck|fuk|shit|bitch|bastard|asshole|cunt|dick|pussy|idiot|stupid|shut\s*up|bullshit|wtf|stfu|chod|choda|chuda|bokachoda|madarchod|khankir|magi|bal|baal|bals|gandu|harami|kutta|suor|shala|saala|sala|chutiya|dhon|pod|batpar|butpar|fraud|scam|scammer|chor)(?:[[:space:]]|[[:punct:]]|$)/iu', $rawQuery);
        if ($isProfane) {
            return;
        }

        $isChitChat = (bool) preg_match('/(?:^|[[:space:]]|[[:punct:]])(thank|thanks|dhonnobad|ধন্যবাদ|good|great|hello|hi|hey|bye|tata|kemon|tumi|apni|tui)(?:[[:space:]]|[[:punct:]]|$)/iu', $rawQuery);
        if ($isChitChat) {
            return;
        }

        $isBargaining = (bool) preg_match('/(?:kom|komano|koman|koma|komay|kombe|discount|kicuto\s*kom|aro\s*kom|dam\s*kom|price\s*kom|fixed|rate\s*kom|bargain|possible|posible|কম|কমান|কমানো|কমবে|ডিসকাউন্ট|ফিক্সড|কিছুটা\s*কম|দাম\s*কম)/iu', $rawQuery);
        if ($isBargaining) {
            return;
        }

        $isGeneralQuery = (bool) preg_match('/^(?:ki|ki\s*ki|koto|kobe|kothay|delivery|charge|track|order|status|number|phone)\b/iu', $rawQuery);
        if ($isGeneralQuery && mb_strlen($rawQuery) <= 30) {
            return;
        }

        $digitsOnly = preg_replace('/[^\d]/', '', $rawQuery);
        if (strlen($digitsOnly) >= 7 && strlen($digitsOnly) === strlen(str_replace([' ', '-', '+', '(', ')'], '', $rawQuery))) {
            return;
        }

        $stopWords = ['the', 'and', 'for', 'with', 'from', 'ache', 'koto', 'dam', 'bhalo', 'eta', 'nibo', 'chai', 'khujchi', 'dekhaw', 'dekhao', 'ki', 'আছে', 'কত', 'দাম', 'ভালো', 'চাই', 'খুঁজছি', 'দেখান', 'দেখা'];
        $clean = preg_replace('/[^\p{L}\p{N}\s]/u', ' ', mb_strtolower($rawQuery));
        $terms = array_diff(explode(' ', $clean), $stopWords);
        $normalized = trim(implode(' ', array_filter($terms, fn($w) => mb_strlen(trim($w)) >= 2)));

        if (mb_strlen($normalized) < 2) {
            $normalized = mb_substr($clean, 0, 250);
        }

        $customerName = null;
        $customerPhone = null;
        $userId = auth()->id();

        if (auth()->check()) {
            $customerName = auth()->user()->name ?? null;
            $customerPhone = auth()->user()->phone ?? null;
        }

        $orderingState = session()->get('chatbot_ordering');
        if (is_array($orderingState)) {
            if (!empty($orderingState['customer_name'])) {
                $customerName = $orderingState['customer_name'];
            }
            if (!empty($orderingState['customer_phone'])) {
                $customerPhone = $orderingState['customer_phone'];
            }
        }

        $ip = $request->ip() ?: '127.0.0.1';

        $existing = DB::table('product_demands')
            ->where(function ($q) use ($normalized, $rawQuery) {
                $q->where('normalized_query', $normalized)
                    ->orWhere('query', $rawQuery);
            })
            ->first();

        if ($existing) {
            DB::table('product_demands')
                ->where('id', $existing->id)
                ->update([
                    'hits_count' => $existing->hits_count + 1,
                    'customer_name' => $customerName ?: $existing->customer_name,
                    'customer_phone' => $customerPhone ?: $existing->customer_phone,
                    'category_hint' => $detectedCategory ?: $existing->category_hint,
                    'last_requested_at' => now(),
                    'updated_at' => now(),
                ]);
        } else {
            DB::table('product_demands')->insert([
                'query' => mb_substr($rawQuery, 0, 255),
                'normalized_query' => mb_substr($normalized, 0, 255),
                'category_hint' => $detectedCategory ? mb_substr($detectedCategory, 0, 100) : null,
                'customer_name' => $customerName ? mb_substr($customerName, 0, 150) : null,
                'customer_phone' => $customerPhone ? mb_substr($customerPhone, 0, 30) : null,
                'user_id' => $userId,
                'ip_address' => $ip,
                'hits_count' => 1,
                'status' => 'pending',
                'admin_notes' => null,
                'last_requested_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
