<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BannerSeeder extends Seeder
{
    public function run(): void
    {
        $banners = [
            [
                'type' => 'hero_slide',
                'badge_text' => 'লিমিটেড স্টক',
                'title' => 'মিনিমাল ডেস্ক আর্কিটেক্ট',
                'subtitle' => 'আপনার ওয়ার্কস্পেসকে দিন নতুন রূপ ও হাই-প্রোডাক্টিভিটি ভাইব',
                'btn_text' => 'কালেকশন দেখুন',
                'btn_link' => '#sec-desk',
                'image_url' => '/images/banners/hero-1.jpg',
                'gradient' => 'linear-gradient(90deg, rgba(0,0,0,0.85) 0%, rgba(0,0,0,0.4) 60%, transparent 100%)',
                'sort_order' => 1,
                'is_active' => true,
            ],
            [
                'type' => 'hero_slide',
                'badge_text' => 'নতুন কালেকশন',
                'title' => 'আল্ট্রা-ক্লিয়ার স্টুডিও সাউন্ড',
                'subtitle' => 'অ্যাক্টিভ নয়েজ ক্যান্সেলেশন হেডফোন ও প্রিমিয়াম গেমিং অডিও',
                'btn_text' => 'অর্ডার করুন',
                'btn_link' => '#sec-audio',
                'image_url' => '/images/banners/hero-2.jpg',
                'gradient' => 'linear-gradient(90deg, rgba(15,23,42,0.88) 0%, rgba(15,23,42,0.4) 60%, transparent 100%)',
                'sort_order' => 2,
                'is_active' => true,
            ],
            [
                'type' => 'hero_slide',
                'badge_text' => 'হট ডিল',
                'title' => 'ভবিষ্যতের স্মার্ট গ্যাজেট',
                'subtitle' => 'হাই-স্পিড GaN ফাস্ট চার্জিং ও মাল্টিপোর্ট কানেক্টিভিটি সমাধান',
                'btn_text' => 'এখনই কিনুন',
                'btn_link' => '#sec-gadget',
                'image_url' => '/images/banners/hero-3.jpg',
                'gradient' => 'linear-gradient(90deg, rgba(24,24,27,0.88) 0%, rgba(24,24,27,0.4) 60%, transparent 100%)',
                'sort_order' => 3,
                'is_active' => true,
            ],
            [
                'type' => 'promo_card',
                'badge_text' => '🔥 স্পেশাল ডিল',
                'title' => 'কাস্টম মেকানিক্যাল কিবোর্ড',
                'subtitle' => 'হট-সোয়াপ্যাবল ও সাউন্ড ড্যাম্পেন্ড অ্যাকোস্টিক',
                'btn_text' => 'কালেকশন দেখুন <i class="fa-solid fa-arrow-right ms-1"></i>',
                'btn_link' => '#sec-desk',
                'image_url' => '/images/banners/promo-1.jpg',
                'gradient' => 'linear-gradient(135deg, #090d16 0%, #111827 50%, #312e81 100%)',
                'sort_order' => 1,
                'is_active' => true,
            ],
            [
                'type' => 'promo_card',
                'badge_text' => '⚡ ট্রেন্ডিং গ্যাজেট',
                'title' => 'ওয়্যারলেস নয়েজ ক্যানসেলিং বাডস',
                'subtitle' => 'আল্ট্রা-লো লেটেন্সি গেমিং ও হাই-রেস অডিও',
                'btn_text' => 'এখনই কিনুন <i class="fa-solid fa-arrow-right ms-1"></i>',
                'btn_link' => '#sec-audio',
                'image_url' => '/images/banners/promo-2.jpg',
                'gradient' => 'linear-gradient(135deg, #052e16 0%, #064e3b 45%, #0f172a 100%)',
                'sort_order' => 2,
                'is_active' => true,
            ],
        ];

        foreach ($banners as $b) {
            $b['created_at'] = now();
            $b['updated_at'] = now();
            DB::table('banners')->updateOrInsert([
                'type' => $b['type'],
                'sort_order' => $b['sort_order'],
            ], $b);
        }
    }
}
