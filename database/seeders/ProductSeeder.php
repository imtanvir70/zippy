<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds with 50 high-quality curated products via Query Builder.
     */
    public function run(): void
    {
        $cats = DB::table('categories')->get()->keyBy('slug');

        $products = [
            // ==================== 1. DESK SETUP (10 Products) ====================
            [
                'cat' => 'desk',
                'title' => 'অ্যালুমিনিয়াম ল্যাপটপ স্ট্যান্ড প্রো',
                'slug' => 'aluminum-laptop-stand-pro',
                'price' => 2450,
                'old_price' => 3500,
                'rating' => 4.9,
                'reviews_count' => 142,
                'main_image' => 'https://images.unsplash.com/photo-1527864550417-7fd91fc51a46?auto=format&fit=crop&w=600&q=80',
                'gallery_images' => [
                    'https://images.unsplash.com/photo-1527864550417-7fd91fc51a46?auto=format&fit=crop&w=600&q=80',
                    'https://images.unsplash.com/photo-1587829741301-dc798b83add3?auto=format&fit=crop&w=600&q=80',
                    'https://images.unsplash.com/photo-1618005182384-a83a8bd57fbe?auto=format&fit=crop&w=600&q=80'
                ],
                'tag' => '-৩০%',
                'badge_type' => '',
                'is_flash_deal' => true,
                'is_featured' => true,
                'variants' => [
                    ['name' => 'স্পেস গ্রে', 'price' => 2450],
                    ['name' => 'ম্যাট ব্ল্যাক (+৳৫০)', 'price' => 2500],
                    ['name' => 'সিলভার হোয়াইট', 'price' => 2450]
                ],
                'specifications' => [
                    'ম্যাটেরিয়াল' => 'প্রিমিয়াম অ্যানোডাইজড অ্যালুমিনিয়াম অ্যালয়',
                    'কম্প্যাটিবিলিটি' => '১০ থেকে ১৭.৩ ইঞ্চি সকল ল্যাপটপ ও ম্যাকবুক',
                    'ওজন সহ্য ক্ষমতা' => 'সর্বোচ্চ ১০ কেজি',
                    'ফিচার' => '৩৬০° এয়ারফ্লো কুলিং ভেন্ট ও অ্যান্টি-স্লিপ সিলিকন প্যাড',
                    'ওয়ারেন্টি' => '৬ মাসের অফিসিয়াল রিপ্লেসমেন্ট'
                ],
                'short_desc' => 'সিএনসি অ্যালুমিনিয়াম ফিনিশ, ফুল ফোল্ডেবল ও নন-স্লিপ সিলিকন গ্রিপ। ১১-১৭ ইঞ্চি সব ল্যাপটপ সাপোর্ট করে।',
                'description' => 'প্রিমিয়াম কোয়ালিটি এরগোনমিক ল্যাপটপ স্ট্যান্ড। হাই-গ্রেড অ্যানোডাইজড অ্যালুমিনিয়াম অ্যালয় বডি যা দীর্ঘক্ষণ কাজের সময় ঘাড় এবং পিঠের ব্যথা কমায়। ৩৬০ ডিগ্রি এয়ারফ্লো ভেন্টিলেশন ডিজাইন ল্যাপটপকে দ্রুত ঠান্ডা রাখে।',
            ],
            [
                'cat' => 'desk',
                'title' => 'গ্যাস্কেট মেকানিক্যাল কিবোর্ড RGB',
                'slug' => 'gasket-mechanical-keyboard-rgb',
                'price' => 5400,
                'old_price' => 7200,
                'rating' => 4.9,
                'reviews_count' => 184,
                'main_image' => 'https://images.unsplash.com/photo-1587829741301-dc798b83add3?auto=format&fit=crop&w=600&q=80',
                'gallery_images' => [
                    'https://images.unsplash.com/photo-1587829741301-dc798b83add3?auto=format&fit=crop&w=600&q=80',
                    'https://images.unsplash.com/photo-1618005182384-a83a8bd57fbe?auto=format&fit=crop&w=600&q=80',
                    'https://images.unsplash.com/photo-1544816155-12df9643f363?auto=format&fit=crop&w=600&q=80'
                ],
                'tag' => '-২৫%',
                'badge_type' => 'hot-badge',
                'is_flash_deal' => true,
                'is_featured' => true,
                'variants' => [
                    ['name' => 'রেড লিনিয়ার সুইচ', 'price' => 5400],
                    ['name' => 'ব্রাউন ট্যাকটাইল সুইচ (+৳১০০)', 'price' => 5500],
                    ['name' => 'ইয়েলো প্রি-লুব্রিকেন্টেড সুইচ (+৳৩০০)', 'price' => 5700]
                ],

                'specifications' => [
                    'সুইচ টাইপ' => 'হট-সোয়াপ গ্যাস্কেট মাউন্টেড প্রাক-লুব্রিকেটেড সুইচ',
                    'কানেক্টিভিটি' => 'টাইপ-সি তারযুক্ত / ব্লুটুথ ৫.০ / ২.৪GHz ডঙ্গল',
                    'ব্যাকলাইট' => '১৬.৮ মিলিয়ন আরজিবি কাস্টম মোডস',
                    'ব্যাটারি' => '৪০০০ mAh রিচার্জেবল লিথিয়াম ব্যাটারি',
                    'ওয়ারেন্টি' => '১ বছর অফিসিয়াল সার্ভিস ওয়ারেন্টি'
                ],
                'short_desc' => 'হট-সোয়াপ গ্যাস্কেট মাউন্ট, পিবিটি কি-ক্যাপস ও সাউন্ড ড্যাম্পেনার পোরন ফোম। ৪০০০mAh ব্যাটারি।',
                'description' => 'আল্ট্রা-স্মুথ টাইপিং এক্সপেরিয়েন্সের জন্য কাস্টম গ্যাস্কেট মাউন্টেড মেকানিক্যাল কিবোর্ড। ট্রাই-মোড কানেক্টিভিটি (টাইপ-সি ক্যাবল, ব্লুটুথ ৫.০ এবং ২.৪ গিগাহার্টজ ওয়্যারলেস ডঙ্গল)। ১৬.৮ মিলিয়ন আরজিবি ব্যাকলাইটিং।',
            ],
            [
                'cat' => 'desk',
                'title' => 'ম্যাগনেটিক কেবল অর্গানাইজার ডক',
                'slug' => 'magnetic-cable-organizer-dock',
                'price' => 890,
                'old_price' => 1300,
                'rating' => 4.8,
                'reviews_count' => 74,
                'main_image' => 'https://images.unsplash.com/photo-1544716278-ca5e3f4abd8c?auto=format&fit=crop&w=600&q=80',
                'gallery_images' => [
                    'https://images.unsplash.com/photo-1544716278-ca5e3f4abd8c?auto=format&fit=crop&w=600&q=80'
                ],
                'tag' => '-৩১%',
                'badge_type' => '',
                'is_flash_deal' => false,
                'is_featured' => false,
                'variants' => ['ওয়ালনাট উড', 'ম্যাট ব্ল্যাক'],
                'specifications' => [
                    'ম্যাগনেট' => 'N52 গ্রেড শক্তিশালী নিওডিয়াম ম্যাগনেট',
                    'ক্যাপাসিটি' => 'একসাথে ৫টি ক্যাবল হ্যান্ডলিং',
                    'আঠালো বেস' => 'রিমুভেবল 3M নন-রেসিডুয়াল আঠা'
                ],
                'short_desc' => 'ডেস্কের তারের জট দূর করতে স্ট্রং নিওডিয়াম ম্যাগনেটিক ক্লিপস বেস। ৫টি ক্যাবল সাপোর্ট।',
                'description' => 'ডেস্কটপ অর্গানাইজেশনের জন্য নিখুঁত ম্যাগনেটিক ক্যাবল ট্র্যাকার বেস। স্ট্রং 3M আঠালো প্যাড যেকোনো টেবিল বা কাচের সাথে মজবুতভাবে লেগে থাকে।',
            ],
            [
                'cat' => 'desk',
                'title' => 'মিনিমালিস্ট লেদার ডেস্ক প্যাড XL',
                'slug' => 'minimalist-leather-desk-pad-xl',
                'price' => 1250,
                'old_price' => 1800,
                'rating' => 4.7,
                'reviews_count' => 92,
                'main_image' => 'https://images.unsplash.com/photo-1618005182384-a83a8bd57fbe?auto=format&fit=crop&w=600&q=80',
                'gallery_images' => [
                    'https://images.unsplash.com/photo-1618005182384-a83a8bd57fbe?auto=format&fit=crop&w=600&q=80'
                ],
                'tag' => '-৩০%',
                'badge_type' => '',
                'is_flash_deal' => false,
                'is_featured' => true,
                'variants' => ['মিডনাইট ব্ল্যাক', 'স্যাডল ব্রাউন', 'নেভি ব্লু'],
                'specifications' => [
                    'সাইজ' => '৯০ সেমি × ৪০ সেমি (লার্জ সাইজ)',
                    'উপাদান' => 'ওয়াটারপ্রুফ অ্যান্টি-স্ক্র্যাচ পিইউ লেদার',
                    'বেস' => 'নন-স্লিপ সুয়েড ব্যাক সাইড'
                ],
                'short_desc' => 'ওয়াটারপ্রুফ প্রিমিয়াম পিইউ লেদার। স্মুথ মাউস গ্লাইড ও ৯০x৪০ সেমি লার্জ এরিয়া।',
                'description' => 'আপনার ওয়ার্কস্পেসকে দিন এক রাজকীয় ছোঁয়া। ডাবল সাইডেড ওয়াটারপ্রুফ লেদার যা স্ক্র্যাচ প্রতিরোধী এবং সহজে পরিষ্কার করা যায়।',
            ],
            [
                'cat' => 'desk',
                'title' => 'ভার্টিক্যাল এরগোনমিক ওয়্যারলেস মাউস',
                'slug' => 'vertical-ergonomic-wireless-mouse',
                'price' => 2150,
                'old_price' => 2900,
                'rating' => 4.8,
                'reviews_count' => 57,
                'main_image' => 'https://images.unsplash.com/photo-1615663245857-ac93bb7c39e7?auto=format&fit=crop&w=600&q=80',
                'gallery_images' => [
                    'https://images.unsplash.com/photo-1615663245857-ac93bb7c39e7?auto=format&fit=crop&w=600&q=80'
                ],
                'tag' => '-২৬%',
                'badge_type' => 'stock-badge',
                'is_flash_deal' => false,
                'is_featured' => false,
                'variants' => ['ম্যাট ব্ল্যাক', 'পার্ল হোয়াইট'],
                'specifications' => [
                    'অ্যাঙ্গেল' => '৫৭ ডিগ্রি প্রাকৃতিক হ্যান্ডশেক পজিশন',
                    'সেন্সর' => '৪০০০ DPI অপটিক্যাল অ্যাডজাস্টেবল সেন্সর',
                    'কানেক্টিভিটি' => 'ব্লুটুথ ও ২.৪ গিগাহার্টজ ডুয়াল মোড',
                    'ব্যাটারি' => '৫০০ mAh রিচার্জেবল'
                ],
                'short_desc' => 'হাতের ক্লান্তি দূর করতে ৫৭° ন্যাচারাল হ্যান্ডশেক গ্রিপ ও ৪০০০ DPI হাই-প্রিসিশন সেন্সর।',
                'description' => 'কার্পাল টানেল সিন্ড্রোম এবং কবজির ব্যথা থেকে রক্ষা পেতে আন্তর্জাতিক মানসম্মত ৫৭ ডিগ্রি অ্যাঙ্গেল ডিজাইন। সাইলেন্ট ক্লিক ও দীর্ঘস্থায়ী রিচার্জেবল ব্যাটারি।',
            ],
            [
                'cat' => 'desk',
                'title' => 'অ্যালুমিনিয়াম হেডফোন স্ট্যান্ড ও চার্জার ডক',
                'slug' => 'aluminum-headphone-stand-charger-dock',
                'price' => 1850,
                'old_price' => 2600,
                'rating' => 4.8,
                'reviews_count' => 86,
                'main_image' => 'https://images.unsplash.com/photo-1544816155-12df9643f363?auto=format&fit=crop&w=600&q=80',
                'gallery_images' => [
                    'https://images.unsplash.com/photo-1544816155-12df9643f363?auto=format&fit=crop&w=600&q=80'
                ],
                'tag' => '-২৯%',
                'badge_type' => '',
                'is_flash_deal' => false,
                'is_featured' => false,
                'variants' => ['স্পেস গ্রে', 'ম্যাট ব্ল্যাক'],
                'specifications' => [
                    'ওয়্যারলেস চার্জিং' => '১৫W কিউআই ফাস্ট চার্জিং বেস',
                    'বডি' => 'সিএনসি কাটিং অ্যারোস্পেস মেটাল'
                ],
                'short_desc' => 'হেডফোন ঝুলিয়ে রাখার প্রিমিয়াম মেটাল স্ট্যান্ড ও বেসে ফাস্ট ওয়্যারলেস চার্জিং প্যাড।',
                'description' => 'মেটাল সিএনসি কাটিং ফিনিশযুক্ত হেডফোন হ্যাঙ্গার এবং ইন্টিগ্রেটেড ১৫W কিউআই ওয়্যারলেস ফাস্ট চার্জিং বেস।',
            ],
            [
                'cat' => 'desk',
                'title' => 'সলিড উডেন মনিটর রাইজার স্ট্যান্ড',
                'slug' => 'solid-wooden-monitor-riser-stand',
                'price' => 3200,
                'old_price' => 4500,
                'rating' => 4.9,
                'reviews_count' => 49,
                'main_image' => 'https://images.unsplash.com/photo-1518455027359-f3f8164ba6bd?auto=format&fit=crop&w=600&q=80',
                'gallery_images' => [
                    'https://images.unsplash.com/photo-1518455027359-f3f8164ba6bd?auto=format&fit=crop&w=600&q=80'
                ],
                'tag' => '-২৯%',
                'badge_type' => 'stock-badge',
                'is_flash_deal' => false,
                'is_featured' => false,
                'variants' => ['ন্যাচারাল ওক', 'ডার্ক ওয়ালনাট'],
                'specifications' => [
                    'কাঠ' => 'প্রিমিয়াম সলিড ন্যাচারাল ওক উড',
                    'সাইজ' => '১০০ সেমি × ২০ সেমি × ১০ সেমি',
                    'লোড ক্যাপাসিটি' => '২৫ কেজি পর্যন্ত'
                ],
                'short_desc' => 'ডেস্ক সেটআপে বাড়তি স্পেস ও চোখের লেভেলে মনিটর রাখার ন্যাচারাল উড স্ট্যান্ড।',
                'description' => 'প্রিমিয়াম ফিনিশযুক্ত ন্যাচারাল কাঠের মনিটর স্ট্যান্ড। নিচে কিবোর্ড ও নোটবুক রাখার প্রচুর জায়গা রয়েছে।',
            ],
            [
                'cat' => 'desk',
                'title' => 'কাস্টম কয়েল্ড কিবোর্ড ক্যাবল (অ্যাভিয়েটর)',
                'slug' => 'custom-coiled-keyboard-cable-aviator',
                'price' => 1450,
                'old_price' => 2100,
                'rating' => 4.8,
                'reviews_count' => 67,
                'main_image' => 'https://images.unsplash.com/photo-1587829741301-dc798b83add3?auto=format&fit=crop&w=600&q=80',
                'gallery_images' => [
                    'https://images.unsplash.com/photo-1587829741301-dc798b83add3?auto=format&fit=crop&w=600&q=80'
                ],
                'tag' => '-৩১%',
                'badge_type' => '',
                'is_flash_deal' => false,
                'is_featured' => false,
                'variants' => ['প্যাস্টেল পিঙ্ক', 'রেট্রো হোয়াইট', 'ম্যাট ব্ল্যাক'],
                'specifications' => [
                    'কানেক্টর' => 'মেটাল GX12 5-পিন অ্যাভিয়েটর',
                    'স্লিভিং' => 'ডাবল স্লিভড টেক্সফ্লেক্স ও প্যারাটর্ড',
                    'কানেকশন' => 'ইউএসবি-এ টু টাইপ-সি'
                ],
                'short_desc' => 'মেকানিক্যাল কিবোর্ডের সৌন্দর্য বাড়াতে ডাবল স্লিভড কয়েল্ড ক্যাবল ও মেটাল অ্যাভিয়েটর।',
                'description' => 'কাস্টম কিবোর্ড সেটআপের জন্য অত্যাধুনিক টেক্সফ্লেক্স স্লিভড কয়েল্ড ক্যাবল। দ্রুত ডেটা ট্রান্সফার ও পাওয়ার ডেলিভারি।',
            ],
            [
                'cat' => 'desk',
                'title' => 'স্মার্ট রোটারি ডেস্ক স্পিকার স্ট্যান্ড পেয়ার',
                'slug' => 'smart-rotary-desk-speaker-stand-pair',
                'price' => 2600,
                'old_price' => 3600,
                'rating' => 4.8,
                'reviews_count' => 41,
                'main_image' => 'https://images.unsplash.com/photo-1545454675-3531b543be5d?auto=format&fit=crop&w=600&q=80',
                'gallery_images' => [
                    'https://images.unsplash.com/photo-1545454675-3531b543be5d?auto=format&fit=crop&w=600&q=80'
                ],
                'tag' => '-২৮%',
                'badge_type' => '',
                'is_flash_deal' => false,
                'is_featured' => false,
                'variants' => ['মেটাল ব্ল্যাক'],
                'specifications' => [
                    'অ্যাঙ্গেল' => '১৬ ডিগ্রি আপওয়ার্ড টিল্ট নিখুঁত শব্দের জন্য',
                    'ম্যাটেরিয়াল' => 'সলিড অ্যালুমিনিয়াম ও অ্যান্টি-ভাইব্রেশন প্যাড'
                ],
                'short_desc' => 'ডেস্কটপ স্টুডিও মনিটর স্পিকারের জন্য অডিওফাইল গ্রেড ভাইব্রেশন ড্যাম্পিং স্ট্যান্ড।',
                'description' => '১৬ ডিগ্রি অ্যাঙ্গেলে কানের সমান্তরালে অডিও থ্রো করার জন্য নিখুঁত মেটাল স্পিকার স্ট্যান্ড।',
            ],
            [
                'cat' => 'desk',
                'title' => 'এরগোনমিক মেমরি ফোম রিস্ট রেস্ট',
                'slug' => 'ergonomic-memory-foam-wrist-rest',
                'price' => 850,
                'old_price' => 1250,
                'rating' => 4.7,
                'reviews_count' => 53,
                'main_image' => 'https://images.unsplash.com/photo-1618005182384-a83a8bd57fbe?auto=format&fit=crop&w=600&q=80',
                'gallery_images' => [
                    'https://images.unsplash.com/photo-1618005182384-a83a8bd57fbe?auto=format&fit=crop&w=600&q=80'
                ],
                'tag' => '-৩২%',
                'badge_type' => '',
                'is_flash_deal' => false,
                'is_featured' => false,
                'variants' => ['ফুল সাইজ (100%)', 'কমপ্যাক্ট (75%)'],
                'specifications' => [
                    'ফোম' => 'হাই-ডেনসিটি প্রিমিয়াম মেমরি ফোম',
                    'ফ্যাব্রিক' => 'ব্রিদেবল লাইক্রা স্মুথ সারফেস'
                ],
                'short_desc' => 'কিবোর্ড ও মাউস ব্যবহারের সময় কবজির ক্লান্তি রোধে সুপার সফট কুশন প্যাড।',
                'description' => 'দীর্ঘ সময় টাইপিং এবং কোডিংয়ের জন্য হাতের আরাম নিশ্চিত করে এই মেমরি ফোম রিস্ট প্যাড।',
            ],

            // ==================== 2. AUDIO & HEADPHONES (10 Products) ====================
            [
                'cat' => 'audio',
                'title' => 'ANC ওয়্যারলেস স্টুডিও হেডফোন',
                'slug' => 'anc-wireless-studio-headphones',
                'price' => 4650,
                'old_price' => 6500,
                'rating' => 5.0,
                'reviews_count' => 210,
                'main_image' => 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?auto=format&fit=crop&w=600&q=80',
                'gallery_images' => [
                    'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?auto=format&fit=crop&w=600&q=80',
                    'https://images.unsplash.com/photo-1546435770-a3e426bf472b?auto=format&fit=crop&w=600&q=80'
                ],
                'tag' => '-২৮%',
                'badge_type' => 'hot-badge',
                'is_flash_deal' => true,
                'is_featured' => true,
                'variants' => ['ম্যাট ব্ল্যাক', 'সিলভার গ্রে', 'রোজ গোল্ড'],
                'specifications' => [
                    'নয়েজ ক্যান্সেলেশন' => '৩৫dB হাইব্রিড অ্যাক্টিভ নয়েজ ক্যান্সেলেশন (ANC)',
                    'ড্রাইভার' => '৪০ মিমি কাস্টম সিল্ক ডায়াফ্রাম',
                    'ব্যাটারি লাইফ' => '৬০ ঘণ্টা দীর্ঘ প্লেব্যাক ব্যাকআপ',
                    'চার্জিং' => 'টাইপ-সি ফাস্ট চার্জ (১০ মিনিট চার্জে ৫ ঘণ্টা)',
                    'ওয়ারেন্টি' => '১ বছরের রিপ্লেসমেন্ট গ্যারান্টি'
                ],
                'short_desc' => '৩৫ ডেসিবেল হাইব্রিড অ্যাক্টিভ নয়েজ ক্যান্সেলেশন, ৬০ ঘণ্টা প্লেব্যাক ও ক্রিস্টাল ক্লিয়ার স্টুডিও সাউন্ড।',
                'description' => '৪০ মিমি কাস্টম সিল্ক ড্রাইভারের সাহায্যে নিখুঁত হাই-রেস অডিও। আল্ট্রা-সফট মেমোরি ফোম ইয়ারকুশন যা দীর্ঘ সময় ব্যবহারে আরামদায়ক।',
            ],
            [
                'cat' => 'audio',
                'title' => 'মেগা-বাস ওয়্যারলেস টেবিল স্পিকার',
                'slug' => 'mega-bass-wireless-table-speaker',
                'price' => 3250,
                'old_price' => 4800,
                'rating' => 4.7,
                'reviews_count' => 88,
                'main_image' => 'https://images.unsplash.com/photo-1546435770-a3e426bf472b?auto=format&fit=crop&w=600&q=80',
                'gallery_images' => [
                    'https://images.unsplash.com/photo-1546435770-a3e426bf472b?auto=format&fit=crop&w=600&q=80'
                ],
                'tag' => '-৩২%',
                'badge_type' => '',
                'is_flash_deal' => false,
                'is_featured' => false,
                'variants' => ['হেদার গ্রে', 'কোল ব্ল্যাক'],
                'specifications' => [
                    'আউটপুট' => '২০W আরএমএস ডিপ বাস স্টেরিও',
                    'কানেক্টিভিটি' => 'ব্লুটুথ ৫.৩ ও অক্স সাপোর্ট',
                    'ব্যাটারি' => '১২ ঘণ্টা একটানা গান শোনার সুবিধা'
                ],
                'short_desc' => 'মিনিমালিস্ট ফেব্রিক গ্রিল ও ৩৬০° রুম-ফিলিং ডিপ বাস স্টেরিও সাউন্ড। ২০W আউটপুট।',
                'description' => 'ডেস্ক ও লিভিং রুমের জন্য নিখুঁত অ্যাকোস্টিক ডিজাইন। ডুয়াল সাবউফার এবং ব্লুটুথ ৫.৩ স্টেবল রেঞ্জ।',
            ],
            [
                'cat' => 'audio',
                'title' => 'আল্ট্রা-লো লেটেন্সি গেমিং TWS বাডস',
                'slug' => 'ultra-low-latency-gaming-tws-buds',
                'price' => 2650,
                'old_price' => 3900,
                'rating' => 4.8,
                'reviews_count' => 134,
                'main_image' => 'https://images.unsplash.com/photo-1590658268037-6bf12165a8df?auto=format&fit=crop&w=600&q=80',
                'gallery_images' => [
                    'https://images.unsplash.com/photo-1590658268037-6bf12165a8df?auto=format&fit=crop&w=600&q=80'
                ],
                'tag' => '-৩২%',
                'badge_type' => '',
                'is_flash_deal' => false,
                'is_featured' => true,
                'variants' => ['সাইবার হোয়াইট', 'গেমিং ব্ল্যাক'],
                'specifications' => [
                    'লেটেন্সি' => '৩৮ মিলিসেকেন্ড আল্ট্রা-লো লেটেন্সি',
                    'কল নয়েজ রিডাকশন' => '৪-মাইক এনভায়রনমেন্টাল নয়েজ ক্যান্সেলেশন (ENC)',
                    'ব্যাটারি' => 'কেস সহ ৩২ ঘণ্টা প্লেব্যাক'
                ],
                'short_desc' => '৩৮ms আল্ট্রা-লো লেটেন্সি, ডুয়াল মাইক ENC নয়েজ ক্যান্সেলেশন ও ৩২ ঘণ্টা ব্যাকআপ।',
                'description' => 'কম্পিটিটিভ মোবাইল এবং পিসি গেমিংয়ের জন্য তৈরি ডেডিকেটেড গেমিং মোড বাডস। টাইপ-সি ফাস্ট চার্জিং।',
            ],
            [
                'cat' => 'audio',
                'title' => 'ইউএসবি কনডেন্সার স্টুডিও মাইক',
                'slug' => 'usb-condenser-studio-mic',
                'price' => 3750,
                'old_price' => 5100,
                'rating' => 4.9,
                'reviews_count' => 64,
                'main_image' => 'https://images.unsplash.com/photo-1598488035139-bdbb2231ce04?auto=format&fit=crop&w=600&q=80',
                'gallery_images' => [
                    'https://images.unsplash.com/photo-1598488035139-bdbb2231ce04?auto=format&fit=crop&w=600&q=80'
                ],
                'tag' => '-২৬%',
                'badge_type' => '',
                'is_flash_deal' => false,
                'is_featured' => false,
                'variants' => ['ম্যাট ব্ল্যাক'],
                'specifications' => [
                    'স্যাম্পলিং রেট' => '২৪-বিট / ১৯২ kHz ক্রিস্টাল সাউন্ড',
                    'প্যাটার্ন' => 'কার্ডিওয়েড ভয়েস আইসোলেশন',
                    'ফিচার' => 'টাচ-টু-মিউট বাটন ও লাইভ হেডফোন মনিটরিং জ্যাক'
                ],
                'short_desc' => '২৪-বিট/১৯২kHz অডিও রেকর্ডিং, টাচ-টু-মিউট বাটন ও আরজিবি অ্যাম্বিয়েন্ট লাইটিং।',
                'description' => 'পডকাস্টিং, স্ট্রিমিং ও ভয়েসওভারের জন্য কার্ডিওয়েড পিকআপ প্যাটার্ন মাইক। শক মাউন্ট ও পপ ফিল্টার ইনক্লুডেড।',
            ],
            [
                'cat' => 'audio',
                'title' => 'হাই-রেস ডেস্কটপ সাউন্ডবার স্পিকার',
                'slug' => 'hi-res-desktop-soundbar-speaker',
                'price' => 2950,
                'old_price' => 4200,
                'rating' => 4.7,
                'reviews_count' => 48,
                'main_image' => 'https://images.unsplash.com/photo-1545454675-3531b543be5d?auto=format&fit=crop&w=600&q=80',
                'gallery_images' => [
                    'https://images.unsplash.com/photo-1545454675-3531b543be5d?auto=format&fit=crop&w=600&q=80'
                ],
                'tag' => '-৩০%',
                'badge_type' => 'stock-badge',
                'is_flash_deal' => false,
                'is_featured' => false,
                'variants' => ['সিলভার গ্রিল', 'ডার্ক মেটালিক'],
                'specifications' => [
                    'আউটপুট' => '১৬W হাই-ফিডেলিটি ডুয়াল ড্রাইভার',
                    'কানেকশন' => 'ইউএসবি অডিও, অক্স ও ব্লুটুথ ৫.০'
                ],
                'short_desc' => 'মনিটরের নিচে পারফেক্ট ফিট হওয়ার স্লিম বডি এবং ডুয়াল প্যাসিভ রেডিয়েটর বাস।',
                'description' => 'স্লিম প্রোফাইল ডেস্কটপ সাউন্ডবার। ৩.৫ মিমি অক্স এবং ব্লুটুথ ডুয়াল মোড সাপোর্ট।',
            ],
            [
                'cat' => 'audio',
                'title' => 'হাই-ফাই ব্লুটুথ অডিও রিসিভার DAC',
                'slug' => 'hifi-bluetooth-audio-receiver-dac',
                'price' => 2350,
                'old_price' => 3200,
                'rating' => 4.9,
                'reviews_count' => 62,
                'main_image' => 'https://images.unsplash.com/photo-1546435770-a3e426bf472b?auto=format&fit=crop&w=600&q=80',
                'gallery_images' => [
                    'https://images.unsplash.com/photo-1546435770-a3e426bf472b?auto=format&fit=crop&w=600&q=80'
                ],
                'tag' => '-২৭%',
                'badge_type' => '',
                'is_flash_deal' => false,
                'is_featured' => false,
                'variants' => ['অ্যালুমিনিয়াম ব্ল্যাক'],
                'specifications' => [
                    'কোডেক' => 'LDAC, aptX HD, AAC, SBC হাই-রেস কোডেক',
                    'আউটপুট' => 'RCA / 3.5mm অক্স / অপটিক্যাল ডিজিটাল'
                ],
                'short_desc' => 'ল্যাপটপ ও সাউন্ড সিস্টেমের জন্য LDAC অডিও স্ট্রিমিং ও অডিওফাইল সাউন্ড কোয়ালিটি।',
                'description' => 'পুরোনো সাউন্ড সিস্টেমকে ওয়্যারলেস হাই-রেস অডিওতে রূপান্তর করার প্রিমিয়াম ড্যাক কনভার্টার।',
            ],
            [
                'cat' => 'audio',
                'title' => 'হ্যান্ডক্রাফটেড উডেন ব্লুটুথ রেডিও স্পিকার',
                'slug' => 'handcrafted-wooden-bluetooth-radio-speaker',
                'price' => 3100,
                'old_price' => 4400,
                'rating' => 4.8,
                'reviews_count' => 52,
                'main_image' => 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?auto=format&fit=crop&w=600&q=80',
                'gallery_images' => [
                    'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?auto=format&fit=crop&w=600&q=80'
                ],
                'tag' => '-৩০%',
                'badge_type' => 'stock-badge',
                'is_flash_deal' => false,
                'is_featured' => false,
                'variants' => ['ওয়ালনাট উড', 'চেরি উড'],
                'specifications' => [
                    'ম্যাটেরিয়াল' => '১০০% ন্যাচারাল উড অ্যাকোস্টিক চেম্বার',
                    'টিউনার' => 'এফএম রেডিও ও ব্লুটুথ ৫.১'
                ],
                'short_desc' => 'রেট্রো ভিন্টেজ লুক এবং ডিপ ওয়ার্ম অ্যাকোস্টিক সাউন্ড সহ হ্যান্ডক্রাফটেড উড স্পিকার।',
                'description' => 'ভিন্টেজ ক্লাসিক ডিজাইনের রেডিও এবং মডার্ন ব্লুটুথ স্পিকারের মেলবন্ধন।',
            ],
            [
                'cat' => 'audio',
                'title' => 'ডুয়াল-ড্রাইভার অডিওফাইল IEM ইয়ারফোন',
                'slug' => 'dual-driver-audiophile-iem-earphone',
                'price' => 1950,
                'old_price' => 2800,
                'rating' => 4.9,
                'reviews_count' => 97,
                'main_image' => 'https://images.unsplash.com/photo-1590658268037-6bf12165a8df?auto=format&fit=crop&w=600&q=80',
                'gallery_images' => [
                    'https://images.unsplash.com/photo-1590658268037-6bf12165a8df?auto=format&fit=crop&w=600&q=80'
                ],
                'tag' => '-৩০%',
                'badge_type' => '',
                'is_flash_deal' => false,
                'is_featured' => false,
                'variants' => ['ট্রান্সপারেন্ট ব্ল্যাক', 'ক্রিস্টাল ক্লিয়ার'],
                'specifications' => [
                    'ড্রাইভার' => '১০ মিমি ডায়নামিক + ব্যালান্সড আর্মেচার',
                    'ক্যাবল' => 'ডিটাচেবল ২-পিন সিলভার-প্লেটেড ক্যাবল'
                ],
                'short_desc' => 'স্টুডিও কোয়ালিটি সাউন্ড মনিটরিং ও ডিপ ক্রিস্প ট্রিবলের জন্য কাস্টম IEM।',
                'description' => 'মিউজিশিয়ান ও অডিও উৎসাহীদের জন্য ডিটাচেবল সিলভার প্লেটেড তার সহ ইন-ইয়ার মনিটর।',
            ],
            [
                'cat' => 'audio',
                'title' => 'নয়েজ ক্যানসেলিং ট্রাভেল এয়ারবাডস প্রো',
                'slug' => 'noise-canceling-travel-earbuds-pro',
                'price' => 3150,
                'old_price' => 4500,
                'rating' => 4.8,
                'reviews_count' => 78,
                'main_image' => 'https://images.unsplash.com/photo-1590658268037-6bf12165a8df?auto=format&fit=crop&w=600&q=80',
                'gallery_images' => [
                    'https://images.unsplash.com/photo-1590658268037-6bf12165a8df?auto=format&fit=crop&w=600&q=80'
                ],
                'tag' => '-৩০%',
                'badge_type' => 'hot-badge',
                'is_flash_deal' => false,
                'is_featured' => false,
                'variants' => ['ম্যাট ব্ল্যাক', 'আইভরি হোয়াইট'],
                'specifications' => [
                    'ফিচার' => 'ট্রান্সপারেন্সি মোড ও স্মার্ট টাচ কন্ট্রোল',
                    'প্লেটাইম' => '৪০ ঘণ্টা দীর্ঘ ব্যাটারি ব্যাকআপ'
                ],
                'short_desc' => 'অ্যাক্টিভ নয়েজ রিডাকশন ও ওয়াটার-রেজিস্ট্যান্ট ট্রাভেল অডিও সঙ্গী।',
                'description' => 'দৈনন্দিন যাতায়াত ও ওয়ার্কআউটের জন্য কমপ্যাক্ট ট্রাভেল ইয়ারবাডস।',
            ],
            [
                'cat' => 'audio',
                'title' => 'পোর্টেবল ওয়াটারপ্রুফ আউটডোর স্পিকার',
                'slug' => 'portable-waterproof-outdoor-speaker',
                'price' => 2450,
                'old_price' => 3400,
                'rating' => 4.7,
                'reviews_count' => 61,
                'main_image' => 'https://images.unsplash.com/photo-1546435770-a3e426bf472b?auto=format&fit=crop&w=600&q=80',
                'gallery_images' => [
                    'https://images.unsplash.com/photo-1546435770-a3e426bf472b?auto=format&fit=crop&w=600&q=80'
                ],
                'tag' => '-২৮%',
                'badge_type' => '',
                'is_flash_deal' => false,
                'is_featured' => false,
                'variants' => ['আর্মি গ্রিন', 'কোল ব্ল্যাক'],
                'specifications' => [
                    'ওয়াটারপ্রুফ রেটিং' => 'IPX7 সাবমার্সিবল ওয়াটারপ্রুফ',
                    'ব্যাটারি' => '১৫W পাওয়ার ও ১৮ ঘণ্টা ব্যাকআপ'
                ],
                'short_desc' => 'IPX7 সম্পূর্ণ ওয়াটারপ্রুফ বডি ও রাগেড ড্রপ-প্রুফ বিল্ড আউটডোর স্পিকার।',
                'description' => 'ক্যাম্পিং ও ভ্রমণের জন্য নিখুঁত মজবুত বিল্ড কোয়ালিটির আউটডোর ব্লুটুথ স্পিকার।',
            ],

            // ==================== 3. SMART GADGETS & POWER (10 Products) ====================
            [
                'cat' => 'gadget',
                'title' => 'টাইপ-সি ৮-ইন-১ মাল্টিপোর্ট হাব',
                'slug' => 'type-c-8-in-1-multiport-hub',
                'price' => 2150,
                'old_price' => 3200,
                'rating' => 4.9,
                'reviews_count' => 112,
                'main_image' => 'https://images.unsplash.com/photo-1550009158-9ebf69173e03?auto=format&fit=crop&w=600&q=80',
                'gallery_images' => [
                    'https://images.unsplash.com/photo-1550009158-9ebf69173e03?auto=format&fit=crop&w=600&q=80'
                ],
                'tag' => '-৩৩%',
                'badge_type' => 'stock-badge',
                'is_flash_deal' => true,
                'is_featured' => true,
                'variants' => ['স্পেস গ্রে'],
                'specifications' => [
                    'পোর্টস' => '4K HDMI, 100W PD Type-C, ৩x USB 3.0, SD/TF স্লট, RJ45 LAN',
                    'উপাদান' => 'অ্যালুমিনিয়াম অ্যালয় বডি হিট ডিসিপেশন সহ'
                ],
                'short_desc' => '4K HDMI, 100W PD ফাস্ট চার্জিং ও হাইস্পিড SD/TF কার্ড রিডার স্লট। অ্যালুমিনিয়াম বডি।',
                'description' => 'ম্যাকবুক, উইন্ডোজ ল্যাপটপ ও আইপ্যাডের জন্য আল্ট্রা-কম্প্যাক্ট অ্যালুমিনিয়াম ডকিং হাব। গিগাবিট ইথারনেট সাপোর্ট।',
            ],
            [
                'cat' => 'gadget',
                'title' => 'স্মার্ট ডিজিটাল ক্যালিপার ০.০১মিমি',
                'slug' => 'smart-digital-caliper-001mm',
                'price' => 1150,
                'old_price' => 1650,
                'rating' => 4.7,
                'reviews_count' => 39,
                'main_image' => 'https://images.unsplash.com/photo-1581092160607-ee22621dd758?auto=format&fit=crop&w=600&q=80',
                'gallery_images' => [
                    'https://images.unsplash.com/photo-1581092160607-ee22621dd758?auto=format&fit=crop&w=600&q=80'
                ],
                'tag' => '-৩০%',
                'badge_type' => '',
                'is_flash_deal' => false,
                'is_featured' => false,
                'variants' => ['স্টেইনলেস স্টিল'],
                'specifications' => [
                    'পরিমাপ পরিসীমা' => '০-১৫০ মিমি / ০-৬ ইঞ্চি',
                    'নির্ভুলতা' => '±০.০১ মিমি / ±০.০০১ ইঞ্চি'
                ],
                'short_desc' => '০.০১ মিমি প্রিসিশন পরিমাপ ও লার্জ এলসিডি ডিসপ্লে। স্টেইনলেস স্টিল ফ্রেম।',
                'description' => 'ডিআইওয়াই ক্রাফট এবং ৩ডি প্রিন্টিং প্রজেক্টের জন্য নিখুঁত পরিমাপক ডিজিটাল ভার্নিয়ার ক্যালিপার।',
            ],
            [
                'cat' => 'gadget',
                'title' => 'অটো ট্র্যাকিং ফোন গিম্বল ৩৬০°',
                'slug' => 'auto-tracking-phone-gimbal-360',
                'price' => 2450,
                'old_price' => 3600,
                'rating' => 4.8,
                'reviews_count' => 61,
                'main_image' => 'https://images.unsplash.com/photo-1526170375885-4d8ecf77b99f?auto=format&fit=crop&w=600&q=80',
                'gallery_images' => [
                    'https://images.unsplash.com/photo-1526170375885-4d8ecf77b99f?auto=format&fit=crop&w=600&q=80'
                ],
                'tag' => '-৩১%',
                'badge_type' => '',
                'is_flash_deal' => false,
                'is_featured' => true,
                'variants' => ['ম্যাট ব্ল্যাক', 'ক্লাসিক হোয়াইট'],
                'specifications' => [
                    'AI ট্র্যাকিং' => 'অ্যাপ ছাড়া অটো ফেস এবং বডি ট্র্যাকিং সেন্সর',
                    'ব্যাটারি' => '২২০০ mAh রিচার্জেবল (৮ ঘণ্টা ব্যাকআপ)'
                ],
                'short_desc' => 'কোনো অ্যাপ ছাড়াই ৩৬০° AI ফেস ট্র্যাকিং ও জেসচার কন্ট্রোল রোবটিক ট্রাইপড মাউন্ট।',
                'description' => 'লাইভ স্ট্রিমিং ও রিল ভিডিও বানানোর পারফেক্ট অটো ফেস ফলোয়ার ডিভাইস। ইনবিল্ট আল্ট্রা-ওয়াইড ক্যামেরা সেন্সর।',
            ],
            [
                'cat' => 'gadget',
                'title' => '৬৫W GaN ফাস্ট চার্জার প্রো (৩-পোর্ট)',
                'slug' => '65w-gan-fast-charger-pro',
                'price' => 1950,
                'old_price' => 2700,
                'rating' => 4.9,
                'reviews_count' => 156,
                'main_image' => 'https://images.unsplash.com/photo-1583863788434-e58a36330cf0?auto=format&fit=crop&w=600&q=80',
                'gallery_images' => [
                    'https://images.unsplash.com/photo-1583863788434-e58a36330cf0?auto=format&fit=crop&w=600&q=80'
                ],
                'tag' => '-২৮%',
                'badge_type' => 'hot-badge',
                'is_flash_deal' => false,
                'is_featured' => true,
                'variants' => ['ম্যাট ব্ল্যাক', 'মিনিমাল হোয়াইট'],
                'specifications' => [
                    'প্রযুক্তি' => 'GaN III গ্যালিয়াম নাইট্রাইড ফাস্ট চার্জ',
                    'আউটপুট' => '২x Type-C (65W Max) + 1x USB-A (30W)'
                ],
                'short_desc' => 'GaN III টেকনোলজি। ল্যাপটপ, ম্যাকবুক ও স্মার্টফোন একসাথে সুপার ফাস্ট স্পিডে চার্জ।',
                'description' => 'গ্যালিয়াম নাইট্রাইড ৩য় প্রজন্মের সুরক্ষিত চিপসেট। তাপমাত্রা নিয়ন্ত্রণ এবং ওভারভোল্টেজ সুরক্ষা ব্যবস্থা।',
            ],
            [
                'cat' => 'gadget',
                'title' => '৩-ইন-১ ম্যাগনেটিক ওয়্যারলেস ডক',
                'slug' => '3-in-1-magnetic-wireless-dock',
                'price' => 2850,
                'old_price' => 4100,
                'rating' => 4.8,
                'reviews_count' => 83,
                'main_image' => 'https://images.unsplash.com/photo-1622445262464-84b1b0e0085d?auto=format&fit=crop&w=600&q=80',
                'gallery_images' => [
                    'https://images.unsplash.com/photo-1622445262464-84b1b0e0085d?auto=format&fit=crop&w=600&q=80'
                ],
                'tag' => '-৩০%',
                'badge_type' => '',
                'is_flash_deal' => false,
                'is_featured' => false,
                'variants' => ['স্পেস ব্ল্যাক', 'সিলভার হোয়াইট'],
                'specifications' => [
                    'ম্যাগসেফ চার্জ' => '১৫W ফোন + ৫W ওয়াচ + ৫W ইয়ারবাডস',
                    'ডিজাইন' => 'ফোল্ডেবল পকেট ট্রাভেল ফ্রেন্ডলি'
                ],
                'short_desc' => 'আইফোন, অ্যাপল ওয়াচ ও এয়ারপডস একসাথে ওয়্যারলেস চার্জিং এর ট্রাভেল ফোল্ডেবল ডক।',
                'description' => 'ম্যাগসেফ কম্প্যাটিবল ফোল্ডেবল চার্জিং স্টেশন। ট্রাভেলে সহজে পকেটে বহনযোগ্য।',
            ],
            [
                'cat' => 'gadget',
                'title' => 'স্মার্ট অ্যান্টি-লস্ট ব্লুটুথ ট্র্যাকার ট্যাগ',
                'slug' => 'smart-anti-lost-bluetooth-tracker-tag',
                'price' => 850,
                'old_price' => 1250,
                'rating' => 4.7,
                'reviews_count' => 119,
                'main_image' => 'https://images.unsplash.com/photo-1592899677977-9c10ca588bbd?auto=format&fit=crop&w=600&q=80',
                'gallery_images' => [
                    'https://images.unsplash.com/photo-1592899677977-9c10ca588bbd?auto=format&fit=crop&w=600&q=80'
                ],
                'tag' => '-৩২%',
                'badge_type' => 'hot-badge',
                'is_flash_deal' => false,
                'is_featured' => false,
                'variants' => ['ম্যাট ব্ল্যাক', 'সাদা'],
                'specifications' => [
                    'নেটওয়ার্ক' => 'Apple Find My & Global Android সাপোর্ট',
                    'ব্যাটারি লাইফ' => '১ বছরের প্রতিস্থাপনযোগ্য CR2032 ব্যাটারি'
                ],
                'short_desc' => 'গ্লোবাল ফাইন্ড নেটওয়ার্ক সাপোর্ট, ওয়াটারপ্রুফ বডি ও ১ বছরের লং ব্যাটারি লাইফ।',
                'description' => 'চাবি, ওয়ালেট বা ব্যাগের রিয়েল-টাইম লোকেশন ট্র্যাকিং এর জন্য মিনি ট্যাগ।',
            ],
            [
                'cat' => 'gadget',
                'title' => '১০০W বেইডেড ফাস্ট চার্জিং ডিসপ্লে ক্যাবল',
                'slug' => '100w-braided-fast-charging-display-cable',
                'price' => 790,
                'old_price' => 1200,
                'rating' => 4.9,
                'reviews_count' => 105,
                'main_image' => 'https://images.unsplash.com/photo-1544716278-ca5e3f4abd8c?auto=format&fit=crop&w=600&q=80',
                'gallery_images' => [
                    'https://images.unsplash.com/photo-1544716278-ca5e3f4abd8c?auto=format&fit=crop&w=600&q=80'
                ],
                'tag' => '-৩৪%',
                'badge_type' => 'stock-badge',
                'is_flash_deal' => false,
                'is_featured' => false,
                'variants' => ['১.২ মিটার', '২ মিটার'],
                'specifications' => [
                    'পাওয়ার' => '১০০W 5A E-Marker স্মার্ট চিপ',
                    'ডিসপ্লে' => 'লাইভ ওয়াট ওয়াটেজ ডিজিটাল স্ক্রিন'
                ],
                'short_desc' => 'রিয়েল-টাইম চার্জিং ওয়াটেজ ডিসপ্লে স্ক্রিন ও প্রিমিয়াম জিংক অ্যালয় হেড।',
                'description' => 'ডিভাইসে কত ওয়াটে চার্জ হচ্ছে তা সরাসরি দেখার জন্য স্মার্ট এলইডি স্ক্রিন যুক্ত টাইপ-সি ক্যাবল।',
            ],
            [
                'cat' => 'gadget',
                'title' => 'স্মার্ট ইলেকট্রিক প্রিসিশন স্ক্রু-ড্রাইভার কিট',
                'slug' => 'smart-electric-precision-screwdriver-kit',
                'price' => 2250,
                'old_price' => 3100,
                'rating' => 4.8,
                'reviews_count' => 44,
                'main_image' => 'https://images.unsplash.com/photo-1581092160607-ee22621dd758?auto=format&fit=crop&w=600&q=80',
                'gallery_images' => [
                    'https://images.unsplash.com/photo-1581092160607-ee22621dd758?auto=format&fit=crop&w=600&q=80'
                ],
                'tag' => '-২৭%',
                'badge_type' => '',
                'is_flash_deal' => false,
                'is_featured' => false,
                'variants' => ['২৪-বিট কিট', '৪৮-বিট প্রো কিট'],
                'specifications' => [
                    'টর্ক' => '০.২/০.০৫ N.m ইলেকট্রিক + ৩ N.m ম্যানুয়াল',
                    'বিট উপাদান' => 'S2 হার্ডেনড অ্যালয় স্টিল বিটস'
                ],
                'short_desc' => 'স্মার্টফোন, ল্যাপটপ ও গ্যাজেট রিপেয়ারের জন্য ২৪-ইন-১ ম্যাগনেটিক ইলেকট্রিক স্ক্রু ড্রাইভার।',
                'description' => 'টাইপ-সি চার্জিং ও রিচার্জেবল মোটর সমৃদ্ধ প্রিসিশন স্ক্রু ড্রাইভার সেট।',
            ],
            [
                'cat' => 'gadget',
                'title' => 'মিনি পোর্টেবল হাই-স্পিড টার্বো ফ্যান',
                'slug' => 'mini-portable-high-speed-turbo-fan',
                'price' => 1450,
                'old_price' => 2100,
                'rating' => 4.8,
                'reviews_count' => 92,
                'main_image' => 'https://images.unsplash.com/photo-1550009158-9ebf69173e03?auto=format&fit=crop&w=600&q=80',
                'gallery_images' => [
                    'https://images.unsplash.com/photo-1550009158-9ebf69173e03?auto=format&fit=crop&w=600&q=80'
                ],
                'tag' => '-৩১%',
                'badge_type' => '',
                'is_flash_deal' => false,
                'is_featured' => false,
                'variants' => ['নেভি ব্লু', 'ক্লাউড হোয়াইট'],
                'specifications' => [
                    'মোটর' => '১০০ স্পিড স্টেপলেস টার্বো ব্লোয়ার',
                    'ব্যাটারি' => '৪০০০ mAh লং ব্যাকআপ ব্যাটারি'
                ],
                'short_desc' => '১০০ লেভেল স্টেপলেস উইন্ড স্পিড অ্যাডজাস্টমেন্ট ও ডিজিটাল ব্যাটারি ডিসপ্লে।',
                'description' => 'পকেটে বহনযোগ্য শক্তিশালী এয়ারব্লোয়ার ও কুলিং ফ্যান।',
            ],
            [
                'cat' => 'gadget',
                'title' => 'ম্যাগনেটিক কার ড্যাশবোর্ড ফোন মাউন্ট (১৫W)',
                'slug' => 'magnetic-car-dashboard-phone-mount-15w',
                'price' => 1750,
                'old_price' => 2500,
                'rating' => 4.8,
                'reviews_count' => 65,
                'main_image' => 'https://images.unsplash.com/photo-1583863788434-e58a36330cf0?auto=format&fit=crop&w=600&q=80',
                'gallery_images' => [
                    'https://images.unsplash.com/photo-1583863788434-e58a36330cf0?auto=format&fit=crop&w=600&q=80'
                ],
                'tag' => '-৩০%',
                'badge_type' => 'stock-badge',
                'is_flash_deal' => false,
                'is_featured' => false,
                'variants' => ['ম্যাট ব্ল্যাক'],
                'specifications' => [
                    'ম্যাগনেট' => '১৬x শক্তিশালী N52 ম্যাগসেফ ম্যাগনেটস',
                    'চার্জ' => '১৫W কিউআই ফাস্ট ওয়্যারলেস কার চার্জার'
                ],
                'short_desc' => 'ম্যাগসেফ সাপোর্ট, ১৫W ওয়্যারলেস ফাস্ট চার্জ ও ৩৬০° রোটেটিং বল জয়েন্ট।',
                'description' => 'গাড়ির ড্যাশবোর্ড ও এসি ভেন্টে সহজে লক করার জন্য শক্তিশালী ম্যাগনেটিক হোল্ডার।',
            ],

            // ==================== 4. AMBIENT LIGHTING (10 Products) ====================
            [
                'cat' => 'home',
                'title' => 'ম্যাগনেটিক ফ্লোটিং মুন ল্যাম্প',
                'slug' => 'magnetic-floating-moon-lamp',
                'price' => 3850,
                'old_price' => 5200,
                'rating' => 4.8,
                'reviews_count' => 64,
                'main_image' => 'https://images.unsplash.com/photo-1513506003901-1e6a229e2d15?auto=format&fit=crop&w=600&q=80',
                'gallery_images' => [
                    'https://images.unsplash.com/photo-1513506003901-1e6a229e2d15?auto=format&fit=crop&w=600&q=80',
                    'https://images.unsplash.com/photo-1507473885765-e6ed057f782c?auto=format&fit=crop&w=600&q=80'
                ],
                'tag' => '-২৬%',
                'badge_type' => 'hot-badge',
                'is_flash_deal' => true,
                'is_featured' => true,
                'variants' => ['ওয়ালনাট বেস', 'ডার্ক ওক বেস'],
                'specifications' => [
                    'প্রযুক্তি' => 'ম্যাগনেটিক লেভিটেশন স্বয়ংক্রিয় ঘূর্ণন',
                    'লাইট মোড' => 'ওয়ার্ম হোয়াইট, ন্যাচারাল ও কুল হোয়াইট ৩-টোন'
                ],
                'short_desc' => 'শূন্যে ভাসমান ৩ডি প্রিন্টেড মুন গ্লোব এবং ৩-কালার ওয়ার্ম অ্যাম্বিয়েন্ট টাচ গ্লো।',
                'description' => 'ম্যাগনেটিক লেভিটেশন টেকনোলজি দ্বারা শূন্যে ঘূর্ণায়মান বাস্তবসম্মত চাঁদের আলো। ৩টি রঙের আলো পরিবর্তন সুবিধা।',
            ],
            [
                'cat' => 'home',
                'title' => 'স্মার্ট মনিটর স্ক্রিনবার লাইট (টাচ)',
                'slug' => 'smart-monitor-screenbar-light-touch',
                'price' => 2890,
                'old_price' => 4200,
                'rating' => 4.8,
                'reviews_count' => 98,
                'main_image' => 'https://images.unsplash.com/photo-1586210579191-33b45e38fa2c?auto=format&fit=crop&w=600&q=80',
                'gallery_images' => [
                    'https://images.unsplash.com/photo-1586210579191-33b45e38fa2c?auto=format&fit=crop&w=600&q=80'
                ],
                'tag' => '-৩১%',
                'badge_type' => '',
                'is_flash_deal' => false,
                'is_featured' => true,
                'variants' => ['ম্যাট ব্ল্যাক'],
                'specifications' => [
                    'অপটিক্স' => 'অ্যাসিমেট্রিক ফরোয়ার্ড প্রজেকশন (স্ক্রিন গ্লেয়ার মুক্ত)',
                    'কালার টেম্পারেচার' => '২৯০০K - ৬৫০০K স্টেপলেস ডিমিং'
                ],
                'short_desc' => 'চোখের সুরক্ষায় অ্যাসিমেট্রিক অপটিক্যাল ডিজাইন। ৩টি কালার টেম্পারেচার ও টাচ কন্ট্রোল।',
                'description' => 'মনিটর স্ক্রিনে কোনো রিফ্লেকশন না ফেলে শুধুমাত্র ডেস্ক সারফেস আলোকিত করে চোখের ক্লান্তি দূর করে।',
            ],
            [
                'cat' => 'home',
                'title' => 'স্মার্ট RGBIC ফ্লোর কর্নার ল্যাম্প',
                'slug' => 'smart-rgbic-floor-corner-lamp',
                'price' => 3450,
                'old_price' => 4900,
                'rating' => 4.9,
                'reviews_count' => 89,
                'main_image' => 'https://images.unsplash.com/photo-1507473885765-e6ed057f782c?auto=format&fit=crop&w=600&q=80',
                'gallery_images' => [
                    'https://images.unsplash.com/photo-1507473885765-e6ed057f782c?auto=format&fit=crop&w=600&q=80'
                ],
                'tag' => '-৩০%',
                'badge_type' => '',
                'is_flash_deal' => false,
                'is_featured' => false,
                'variants' => ['কোল ব্ল্যাক', 'সিলভার'],
                'specifications' => [
                    'উচ্চতা' => '১৪০ সেমি কর্নার স্ট্যান্ডিং',
                    'কালার' => '১৬ মিলিয়ন কালার ও মিউজিক রিঅ্যাক্টিভ সেন্সর'
                ],
                'short_desc' => '১৬ মিলিয়ন কালার, মিউজিক সিঙ্ক রিঅ্যাকশন ও রিমোট/স্মার্টফোন অ্যাপ কন্ট্রোল ল্যাম্প।',
                'description' => 'রুমের কর্নারে ইনস্ট্যান্ট গেমিং ও ড্রামাটিক অ্যাম্বিয়েন্স তৈরির জন্য মিনিমালিস্ট কর্নার ল্যাম্প।',
            ],
            [
                'cat' => 'home',
                'title' => 'অ্যারোমাথেরাপি ফ্লেম ডিফিউজার ল্যাম্প',
                'slug' => 'aromatherapy-flame-diffuser-lamp',
                'price' => 1750,
                'old_price' => 2500,
                'rating' => 4.8,
                'reviews_count' => 71,
                'main_image' => 'https://images.unsplash.com/photo-1608571423902-eed4a5ad8108?auto=format&fit=crop&w=600&q=80',
                'gallery_images' => [
                    'https://images.unsplash.com/photo-1608571423902-eed4a5ad8108?auto=format&fit=crop&w=600&q=80'
                ],
                'tag' => '-৩০%',
                'badge_type' => '',
                'is_flash_deal' => false,
                'is_featured' => false,
                'variants' => ['ম্যাট ব্ল্যাক', 'ম্যাট হোয়াইট'],
                'specifications' => [
                    'ক্যাপাসিটি' => '১৮০ মিলি ওয়াটার ট্যাংক ও অটো শাট-অফ',
                    'ইফেক্ট' => 'রিয়েল ফায়ার ফ্লেম এলইডি লাইটিং'
                ],
                'short_desc' => 'রিয়েল ফায়ার ফ্লেম এফেক্ট আল্ট্রাসনিক মিস্ট হিউমিডিফায়ার ও নাইট লাইট।',
                'description' => 'এসেন্সিয়াল অয়েল সুবাস ও বাস্তবসম্মত আগুনের শিখা ইফেক্ট সমৃদ্ধ আরামদায়ক হিউমিডিফায়ার।',
            ],
            [
                'cat' => 'home',
                'title' => 'মিনিমালিস্ট LED কিউব অ্যালার্ম ক্লক',
                'slug' => 'minimalist-led-cube-alarm-clock',
                'price' => 980,
                'old_price' => 1450,
                'rating' => 4.7,
                'reviews_count' => 53,
                'main_image' => 'https://images.unsplash.com/photo-1563861826100-9cb868fdbe1c?auto=format&fit=crop&w=600&q=80',
                'gallery_images' => [
                    'https://images.unsplash.com/photo-1563861826100-9cb868fdbe1c?auto=format&fit=crop&w=600&q=80'
                ],
                'tag' => '-৩২%',
                'badge_type' => 'stock-badge',
                'is_flash_deal' => false,
                'is_featured' => false,
                'variants' => ['ডার্ক ব্রাউন উড', 'হোয়াইট মার্বেল'],
                'specifications' => [
                    'ডিসপ্লে' => 'টাইম, ডেট ও রুম টেম্পারেচার',
                    'সেন্সর' => 'অ্যাকোস্টিক ভয়েস / ক্ল্যাপ অ্যাক্টিভেশন'
                ],
                'short_desc' => 'উডেন টেক্সচার সাউন্ড অ্যাক্টিভেটেড ডিসপ্লে, অ্যালার্ম ও রুমের তাপমাত্রা সেন্সর।',
                'description' => 'হাততালি বা শব্দে অ্যাক্টিভ হওয়া কাঠের ফিনিশ ডিজিটাল ক্লক। স্মার্ট নাইট লাইট অপশন।',
            ],
            [
                'cat' => 'home',
                'title' => 'স্মার্ট হেক্সাগন ওয়াল লাইট প্যানেল',
                'slug' => 'smart-hexagon-wall-light-panel',
                'price' => 2950,
                'old_price' => 4200,
                'rating' => 4.9,
                'reviews_count' => 77,
                'main_image' => 'https://images.unsplash.com/photo-1507473885765-e6ed057f782c?auto=format&fit=crop&w=600&q=80',
                'gallery_images' => [
                    'https://images.unsplash.com/photo-1507473885765-e6ed057f782c?auto=format&fit=crop&w=600&q=80'
                ],
                'tag' => '-৩০%',
                'badge_type' => 'stock-badge',
                'is_flash_deal' => false,
                'is_featured' => false,
                'variants' => ['৬-পিস প্যাক', '১০-পিস প্যাক'],
                'specifications' => [
                    'কন্ট্রোল' => 'টাচ সেন্সিটিভ ও মোবাইল অ্যাপ',
                    'কালার' => 'আরজিবি কালার স্পেকট্রাম'
                ],
                'short_desc' => 'দেওয়ালে নিজের মতো প্যাটার্ন বানানোর মডুলার আরজিবি হেক্সাগন প্যানেল লাইট।',
                'description' => 'টাচ সেন্সিটিভ মডুলার লাইট প্যানেল যা দেয়ালকে আকর্ষণীয় আর্টওয়ার্কে পরিণত করে।',
            ],
            [
                'cat' => 'home',
                'title' => 'ম্যাগনেটিক ব্যালেন্স টেবিল ল্যাম্প (Heng Lamp)',
                'slug' => 'magnetic-balance-table-lamp-heng',
                'price' => 2450,
                'old_price' => 3500,
                'rating' => 4.8,
                'reviews_count' => 68,
                'main_image' => 'https://images.unsplash.com/photo-1513506003901-1e6a229e2d15?auto=format&fit=crop&w=600&q=80',
                'gallery_images' => [
                    'https://images.unsplash.com/photo-1513506003901-1e6a229e2d15?auto=format&fit=crop&w=600&q=80'
                ],
                'tag' => '-৩০%',
                'badge_type' => '',
                'is_flash_deal' => false,
                'is_featured' => false,
                'variants' => ['ম্যাট ব্ল্যাক', 'বিচ উড'],
                'specifications' => [
                    'সুইচ মেকানিজম' => 'দুটো ম্যাগনেটিক বলের পারস্পরিক আকর্ষণে অন/অফ',
                    'লাইট' => 'আই-কেয়ার সফট ওয়ার্ম এলইডি'
                ],
                'short_desc' => 'শূন্যে দুটি ম্যাগনেটিক বলের মিলনে জ্বলে ওঠা আধুনিক রেড ডট ডিজাইন ল্যাম্প।',
                'description' => 'অনন্য চৌম্বকীয় বল সুইচের আর্কিটেকচারাল টেবিল ল্যাম্প। মৃদু ও আরামদায়ক আলোর উৎস।',
            ],
            [
                'cat' => 'home',
                'title' => 'অ্যাস্ট্রোনট গ্যালাক্সি স্টার প্রজেক্টর',
                'slug' => 'astronaut-galaxy-star-projector',
                'price' => 2250,
                'old_price' => 3200,
                'rating' => 4.9,
                'reviews_count' => 114,
                'main_image' => 'https://images.unsplash.com/photo-1507473885765-e6ed057f782c?auto=format&fit=crop&w=600&q=80',
                'gallery_images' => [
                    'https://images.unsplash.com/photo-1507473885765-e6ed057f782c?auto=format&fit=crop&w=600&q=80'
                ],
                'tag' => '-৩০%',
                'badge_type' => 'hot-badge',
                'is_flash_deal' => false,
                'is_featured' => false,
                'variants' => ['হোয়াইট অ্যাস্ট্রোনট'],
                'specifications' => [
                    'প্রজেকশন' => '৮টি নেবুলা মোড ও গ্রিন লেজার স্টারস',
                    'হেড' => '৩৬০° রোটেটেবল ম্যাগনেটিক হেড'
                ],
                'short_desc' => 'সিলিংয়ে ছায়াপথের চমৎকার দৃশ্য তৈরি করা ৩৬০° রোটেটিং অ্যাস্ট্রোনট প্রজেক্টর।',
                'description' => 'রুমকে রাতের আকাশের মতো মোহনীয় করতে রিমোট কন্ট্রোল গ্যালাক্সি নেবুলা প্রজেক্টর।',
            ],
            [
                'cat' => 'home',
                'title' => 'স্যান্ডআর্ট ৩ডি মুভিং স্যান্ড পিকচার ল্যাম্প',
                'slug' => 'sandart-3d-moving-sand-picture-lamp',
                'price' => 2100,
                'old_price' => 2950,
                'rating' => 4.8,
                'reviews_count' => 43,
                'main_image' => 'https://images.unsplash.com/photo-1513506003901-1e6a229e2d15?auto=format&fit=crop&w=600&q=80',
                'gallery_images' => [
                    'https://images.unsplash.com/photo-1513506003901-1e6a229e2d15?auto=format&fit=crop&w=600&q=80'
                ],
                'tag' => '-২৯%',
                'badge_type' => '',
                'is_flash_deal' => false,
                'is_featured' => false,
                'variants' => ['ডিপ ওশান ব্লু', 'গোল্ডেন স্যান্ড'],
                'specifications' => [
                    'গ্লাস' => 'হাই-ট্রান্সপারেন্ট সিলিকন গ্লাস ফ্রেম',
                    'লাইট' => 'ডিমেবল ৩-কালার অ্যাম্বিয়েন্ট ব্যাকলাইট'
                ],
                'short_desc' => 'ঘোরানোর সাথে সাথে নতুন প্রাকৃতিক ল্যান্ডস্কেপ ফুটিয়ে তোলা ডাইনামিক স্যান্ড ল্যাম্প।',
                'description' => 'মাইন্ড রিল্যাক্সেশনের জন্য নিখুঁত চলমান বালুকাবেলার নান্দনিক শিল্পকর্ম।',
            ],
            [
                'cat' => 'home',
                'title' => 'স্মার্ট মোশন সেন্সর বেডরুম নাইট লাইট',
                'slug' => 'smart-motion-sensor-bedroom-night-light',
                'price' => 690,
                'old_price' => 990,
                'rating' => 4.7,
                'reviews_count' => 88,
                'main_image' => 'https://images.unsplash.com/photo-1586210579191-33b45e38fa2c?auto=format&fit=crop&w=600&q=80',
                'gallery_images' => [
                    'https://images.unsplash.com/photo-1586210579191-33b45e38fa2c?auto=format&fit=crop&w=600&q=80'
                ],
                'tag' => '-৩০%',
                'badge_type' => 'stock-badge',
                'is_flash_deal' => false,
                'is_featured' => false,
                'variants' => ['ওয়ার্ম হোয়াইট', 'কুল হোয়াইট'],
                'specifications' => [
                    'সেন্সর' => '১২০° ওয়াইড পিআইআর মোশন সেন্সর (৩-৫ মিটার)',
                    'ব্যাটারি' => 'ম্যাগনেটিক রিচার্জেবল (৯০ দিন স্ট্যান্ডবাই)'
                ],
                'short_desc' => 'ম্যাগনেটিক স্টিকি বেস, স্বয়ংক্রিয় মানব চলাচল সনাক্তকরণ ও সফট গ্লো।',
                'description' => 'আলমারি, সিঁড়ি ও বেডরুমে রাতে অটোমেটিক জ্বলে ওঠার জন্য রিচার্জেবল মোশন লাইট।',
            ],

            // ==================== 5. MINIMAL EDC GEAR (10 Products) ====================
            [
                'cat' => 'edc',
                'title' => 'কার্বন ফাইবার ম্যাগসেফ ওয়ালেট',
                'slug' => 'carbon-fiber-magsafe-wallet',
                'price' => 1350,
                'old_price' => 1950,
                'rating' => 4.7,
                'reviews_count' => 76,
                'main_image' => 'https://images.unsplash.com/photo-1627123424574-724758594e93?auto=format&fit=crop&w=600&q=80',
                'gallery_images' => [
                    'https://images.unsplash.com/photo-1627123424574-724758594e93?auto=format&fit=crop&w=600&q=80'
                ],
                'tag' => '-৩১%',
                'badge_type' => 'stock-badge',
                'is_flash_deal' => false,
                'is_featured' => true,
                'variants' => ['ম্যাট কার্বন', 'চক কার্বন'],
                'specifications' => [
                    'উপাদান' => '১০০% রিয়েল 3K কার্বন ফাইবার প্লেট',
                    'ধারণক্ষমতা' => '১-১২টি ক্রেডিট/ডেবিট কার্ড এবং ক্যাশ মানি ক্লিপ',
                    'সুরক্ষা' => 'RFID অ্যান্টি-স্কিমিং ব্লক'
                ],
                'short_desc' => 'রিয়েল 3K কার্বন ফাইবার, স্ট্রং RFID অ্যান্টি-থেফট প্রটেকশন ও ১-১২ কার্ড ধারণ।',
                'description' => 'পকেটে কোনো ভারী ভাব ছাড়া কার্ড ও ক্যাশ সুরক্ষিত রাখার জন্য আল্ট্রা-স্লিম কার্বন ফাইবার ওয়ালেট।',
            ],
            [
                'cat' => 'edc',
                'title' => 'টাইটানিয়াম কমপ্যাক্ট মাল্টিটুল (১২-ইন-১)',
                'slug' => 'titanium-compact-multitool-12-in-1',
                'price' => 1650,
                'old_price' => 2400,
                'rating' => 4.9,
                'reviews_count' => 84,
                'main_image' => 'https://images.unsplash.com/photo-1589782182703-2aaa69037b5b?auto=format&fit=crop&w=600&q=80',
                'gallery_images' => [
                    'https://images.unsplash.com/photo-1589782182703-2aaa69037b5b?auto=format&fit=crop&w=600&q=80'
                ],
                'tag' => '-৩১%',
                'badge_type' => '',
                'is_flash_deal' => false,
                'is_featured' => false,
                'variants' => ['টাইটানিয়াম গ্রে', 'স্টোনওয়াশড ব্ল্যাক'],
                'specifications' => [
                    'ম্যাটেরিয়াল' => 'TC4 এয়ারক্রাফট গ্রেড টাইটানিয়াম অ্যালয়',
                    'টুলস' => 'বটল ওপেনার, স্ক্রু ড্রাইভার, প্রি-বার, বক্স ওপেনার'
                ],
                'short_desc' => 'পকেট সাইজ গ্রেড-৫ টাইটানিয়াম। বোতল ওপেনার, স্ক্রু ড্রাইভার ও স্কেল সমন্বিত।',
                'description' => 'দৈনন্দিন ও আউটডোর ইমার্জেন্সি ফিক্সিংয়ের জন্য আল্ট্রা-লাইটওয়েট টাইটানিয়াম মাল্টিফাংশন টুল।',
            ],
            [
                'cat' => 'edc',
                'title' => 'মিনি EDC রিচার্জেবল ফ্ল্যাশলাইট ১০০০lm',
                'slug' => 'mini-edc-rechargeable-flashlight-1000lm',
                'price' => 1290,
                'old_price' => 1850,
                'rating' => 4.9,
                'reviews_count' => 68,
                'main_image' => 'https://images.unsplash.com/photo-1517055729445-fa7d27394b48?auto=format&fit=crop&w=600&q=80',
                'gallery_images' => [
                    'https://images.unsplash.com/photo-1517055729445-fa7d27394b48?auto=format&fit=crop&w=600&q=80'
                ],
                'tag' => '-৩০%',
                'badge_type' => '',
                'is_flash_deal' => false,
                'is_featured' => false,
                'variants' => ['ফ্লুরোসেন্ট ব্লু', 'স্মোক ব্ল্যাক'],
                'specifications' => [
                    'উজ্জ্বলতা' => '১০০০ লুমেন সুপার ব্রাইটনেস (১০০ মিটার থ্রো)',
                    'চার্জ' => 'টাইপ-সি ফাস্ট চার্জ ও ম্যাগনেটিক টেইলক্যাপ'
                ],
                'short_desc' => 'আঙ্গুল সাইজ সুপার ব্রাইট ১০০০ লুমেন ফ্ল্যাশলাইট ও ম্যাগনেটিক টেইল বেস।',
                'description' => 'কীচেইনে বহনযোগ্য পাওয়ারফুল ফ্ল্যাশলাইট। টাইপ-সি কুইক রিচার্জ সুবিধা এবং ৫টি লাইটিং মোড।',
            ],
            [
                'cat' => 'edc',
                'title' => 'অ্যালুমিনিয়াম স্মার্ট কি-অর্গানাইজার',
                'slug' => 'aluminum-smart-key-organizer',
                'price' => 790,
                'old_price' => 1200,
                'rating' => 4.8,
                'reviews_count' => 95,
                'main_image' => 'https://images.unsplash.com/photo-1584917865442-de89df76afd3?auto=format&fit=crop&w=600&q=80',
                'gallery_images' => [
                    'https://images.unsplash.com/photo-1584917865442-de89df76afd3?auto=format&fit=crop&w=600&q=80'
                ],
                'tag' => '-৩৪%',
                'badge_type' => '',
                'is_flash_deal' => false,
                'is_featured' => false,
                'variants' => ['মেটালিক রেড', 'ম্যাট ব্ল্যাক'],
                'specifications' => [
                    'ধারণক্ষমতা' => '২ থেকে ৮টি চাবি কম্প্যাক্ট হোল্ড',
                    'বডি' => 'অ্যানোডাইজড অ্যালুমিনিয়াম ফিনিশ'
                ],
                'short_desc' => 'চাবির শব্দ ও পকেট ড্যামেজ দূর করার সুইস-আর্মি স্টাইল লাইটওয়েট কি-হোল্ডার।',
                'description' => '২ থেকে ৮টি চাবি নীরবে কম্প্যাক্ট রাখার জন্য অ্যানোডাইজড মেটাল কি-অর্গানাইজার।',
            ],
            [
                'cat' => 'edc',
                'title' => 'কার্ড পপ-আপ স্লিম বিজনেস কেস',
                'slug' => 'card-pop-up-slim-business-case',
                'price' => 650,
                'old_price' => 950,
                'rating' => 4.6,
                'reviews_count' => 42,
                'main_image' => 'https://images.unsplash.com/photo-1563245372-f21724e3856d?auto=format&fit=crop&w=600&q=80',
                'gallery_images' => [
                    'https://images.unsplash.com/photo-1563245372-f21724e3856d?auto=format&fit=crop&w=600&q=80'
                ],
                'tag' => '-৩২%',
                'badge_type' => 'stock-badge',
                'is_flash_deal' => false,
                'is_featured' => false,
                'variants' => ['গানমেটাল গ্রে', 'ডিপ ব্লু'],
                'specifications' => [
                    'মেকানিজম' => 'ওয়ান-ক্লিক ইজেকশন বটম ট্রিগার',
                    'উপাদান' => 'লাইটওয়েট মেটাল অ্যালুমিনিয়াম'
                ],
                'short_desc' => 'ওয়ান-ক্লিক পপ-আপ কার্ড ইজেকশন মেকানিজম ও আল্ট্রা-স্লিম মেটাল কেসিং।',
                'description' => 'একটি ট্রিগার পুশেই ক্রমানুসারে কার্ড বের হওয়ার স্মার্ট মেকানিজম। আরএফআইডি ব্লকিং প্রযুক্তি।',
            ],
            [
                'cat' => 'edc',
                'title' => 'ট্যাকটিক্যাল কার্বন ফাইবার কি-ক্যারিয়ার',
                'slug' => 'tactical-carbon-fiber-key-carrier',
                'price' => 950,
                'old_price' => 1400,
                'rating' => 4.8,
                'reviews_count' => 58,
                'main_image' => 'https://images.unsplash.com/photo-1627123424574-724758594e93?auto=format&fit=crop&w=600&q=80',
                'gallery_images' => [
                    'https://images.unsplash.com/photo-1627123424574-724758594e93?auto=format&fit=crop&w=600&q=80'
                ],
                'tag' => '-৩২%',
                'badge_type' => '',
                'is_flash_deal' => false,
                'is_featured' => false,
                'variants' => ['ম্যাট কার্বন'],
                'specifications' => [
                    'ক্লিপ' => 'হেভি ডিউটি স্টেইনলেস স্টিল বেল্ট ক্লিপ'
                ],
                'short_desc' => 'রিয়েল কার্বন ফাইবার প্লেট ও অ্যান্টি-লুজেনিং ওয়াশার সমৃদ্ধ আধুনিক কি-ক্যারিয়ার।',
                'description' => 'টেকসই ও লাইটওয়েট ট্যাকটিকাল কি-ক্যারিয়ার। বেল্ট ক্লিপ এবং বটল ওপেনার সমন্বিত।',
            ],
            [
                'cat' => 'edc',
                'title' => 'ওয়াটারপ্রুফ টেক অর্গানাইজার পাউচ',
                'slug' => 'waterproof-tech-organizer-pouch',
                'price' => 1190,
                'old_price' => 1750,
                'rating' => 4.8,
                'reviews_count' => 73,
                'main_image' => 'https://images.unsplash.com/photo-1544816155-12df9643f363?auto=format&fit=crop&w=600&q=80',
                'gallery_images' => [
                    'https://images.unsplash.com/photo-1544816155-12df9643f363?auto=format&fit=crop&w=600&q=80'
                ],
                'tag' => '-৩২%',
                'badge_type' => 'stock-badge',
                'is_flash_deal' => false,
                'is_featured' => false,
                'variants' => ['চারকোল ব্ল্যাক', 'নেভি গ্রে'],
                'specifications' => [
                    'ফ্যাব্রিক' => 'কর্ডুরা ব্যালেস্টিক নাইলন ওয়াটারপ্রুফ',
                    'কম্পার্টমেন্ট' => 'চার্জার, ক্যাবল, পেনড্রাইভ ও পাওয়ারব্যাংক স্লট'
                ],
                'short_desc' => 'ক্যাবল, চার্জার ও গ্যাজেট গুছিয়ে ট্রাভেল করার জন্য ওয়াটারপ্রুফ অর্গানাইজার ব্যাগ।',
                'description' => 'মাল্টিপল ইলাস্টিক লুপ এবং জিপার পকেট সহ প্রিমিয়াম ট্রাভেল টেক পাউচ।',
            ],
            [
                'cat' => 'edc',
                'title' => 'টাইটানিয়াম ট্যাকটিক্যাল বলপয়েন্ট পেন',
                'slug' => 'titanium-tactical-ballpoint-pen',
                'price' => 1450,
                'old_price' => 2200,
                'rating' => 4.9,
                'reviews_count' => 38,
                'main_image' => 'https://images.unsplash.com/photo-1589782182703-2aaa69037b5b?auto=format&fit=crop&w=600&q=80',
                'gallery_images' => [
                    'https://images.unsplash.com/photo-1589782182703-2aaa69037b5b?auto=format&fit=crop&w=600&q=80'
                ],
                'tag' => '-৩৪%',
                'badge_type' => '',
                'is_flash_deal' => false,
                'is_featured' => false,
                'variants' => ['স্টোনওয়াশড টাইটানিয়াম'],
                'specifications' => [
                    'রিফিল' => 'স্ট্যান্ডার্ড জার্মান স্মুথ রোলার রিফিল সাপোর্ট',
                    'টিপ' => 'টাংস্টেন স্টিল গ্লাস ব্রেকার টিপ'
                ],
                'short_desc' => 'সিএনসি টাইটানিয়াম বডি, স্মুথ বোল্ট-অ্যাকশন মেকানিজম ও গ্লাস ব্রেকার টিপ।',
                'description' => 'সেলফ ডিফেন্স ও স্মুথ সিগনেচার রাইটিংয়ের জন্য বোল্ট-অ্যাকশন টাইটানিয়াম পেন।',
            ],
            [
                'cat' => 'edc',
                'title' => 'মিনি পোর্টেবল ল্যাপটপ এয়ার ডাস্টার ব্লোয়ার',
                'slug' => 'mini-portable-laptop-air-duster-blower',
                'price' => 1850,
                'old_price' => 2600,
                'rating' => 4.8,
                'reviews_count' => 64,
                'main_image' => 'https://images.unsplash.com/photo-1517055729445-fa7d27394b48?auto=format&fit=crop&w=600&q=80',
                'gallery_images' => [
                    'https://images.unsplash.com/photo-1517055729445-fa7d27394b48?auto=format&fit=crop&w=600&q=80'
                ],
                'tag' => '-২৯%',
                'badge_type' => 'stock-badge',
                'is_flash_deal' => false,
                'is_featured' => false,
                'variants' => ['ম্যাট ব্ল্যাক'],
                'specifications' => [
                    'মোটর' => '১ লক্ষ RPM হাই-স্পিড ব্রাশলেস মোটর',
                    'চার্জ' => 'টাইপ-সি রিচার্জেবল'
                ],
                'short_desc' => 'কিবোর্ড, ক্যামেরা ও ইলেকট্রনিক গ্যাজেটের ধুলাবালি দূর করার সুপার পাওয়ারফুল ডাস্টার।',
                'description' => 'ক্যানড এয়ারের পরিবেশবান্ধব বিকল্প ইলেকট্রিক হাই-প্রেশার এয়ার ডাস্টার।',
            ],
            [
                'cat' => 'edc',
                'title' => 'অ্যান্টি-থেফট স্লিভ ক্রসবাডি স্লিং ব্যাগ',
                'slug' => 'anti-theft-sleeve-crossbody-sling-bag',
                'price' => 1750,
                'old_price' => 2500,
                'rating' => 4.8,
                'reviews_count' => 82,
                'main_image' => 'https://images.unsplash.com/photo-1544816155-12df9643f363?auto=format&fit=crop&w=600&q=80',
                'gallery_images' => [
                    'https://images.unsplash.com/photo-1544816155-12df9643f363?auto=format&fit=crop&w=600&q=80'
                ],
                'tag' => '-৩০%',
                'badge_type' => 'hot-badge',
                'is_flash_deal' => false,
                'is_featured' => false,
                'variants' => ['মিডনাইট ব্ল্যাক', 'ক্যামো গ্রে'],
                'specifications' => [
                    'সাইজ' => 'আইপ্যাড ১১ ইঞ্চি পর্যন্ত ধারণক্ষমতা',
                    'ফিচার' => 'লুকানো অ্যান্টি-থেফট জিপার ও ওয়াটার-রেজিস্ট্যান্ট কোটিং'
                ],
                'short_desc' => 'আইপ্যাড, ফোন ও গ্যাজেট বহনের আল্ট্রা-স্লিম আর্গোনমিক চেস্ট স্লিং ব্যাগ।',
                'description' => 'প্রতিদিনের যাতায়াত ও ভ্রমণের জন্য স্লিম প্রোফাইল ক্রসবাডি ব্যাগ।',
            ],
        ];

        foreach ($products as $p) {
            $catSlug = $p['cat'];
            unset($p['cat']);
            $p['category_id'] = $cats[$catSlug]->id;
            $p['sku'] = 'ZB-' . strtoupper(Str::random(6));
            $p['meta_title'] = "{$p['title']} - Zippy অনলাইন শপ";
            $p['meta_description'] = $p['short_desc'];
            $p['meta_keywords'] = "{$p['title']}, {$cats[$catSlug]->name_bn}, Zippy, gadget, bangladesh";
            $gallery = $p['gallery_images'] ?? [$p['main_image']];
            if (count($gallery) <= 1) {
                $catFallback = match ($catSlug) {
                    'desk' => 'https://images.unsplash.com/photo-1527864550417-7fd91fc51a46?auto=format&fit=crop&w=600&q=80',
                    'audio' => 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?auto=format&fit=crop&w=600&q=80',
                    'gadget' => 'https://images.unsplash.com/photo-1583863788434-e58a36330cf0?auto=format&fit=crop&w=600&q=80',
                    'home' => 'https://images.unsplash.com/photo-1507473885765-e6ed057f782c?auto=format&fit=crop&w=600&q=80',
                    'edc' => 'https://images.unsplash.com/photo-1627123424574-724758594e93?auto=format&fit=crop&w=600&q=80',
                    default => 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?auto=format&fit=crop&w=600&q=80'
                };
                if ($catFallback !== $p['main_image']) {
                    $gallery[] = $catFallback;
                }
            }
            $p['gallery_images'] = json_encode(array_values(array_unique($gallery)));
            $p['variants'] = json_encode($p['variants'] ?? []);
            $p['specifications'] = json_encode($p['specifications'] ?? []);
            $p['created_at'] = now();
            $p['updated_at'] = now();

            DB::table('products')->updateOrInsert(['slug' => $p['slug']], $p);
        }
    }
}
