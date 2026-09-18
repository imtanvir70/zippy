<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds via Query Builder.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Desk Setup',
                'name_bn' => 'ডেস্ক সেটআপ কালেকশন',
                'slug' => 'desk',
                'icon' => 'fa-laptop-code',
                'badge_text' => '১৮+ আইটেম',
                'badge_color' => '#000000',
                'image' => 'https://images.unsplash.com/photo-1587829741301-dc798b83add3?auto=format&fit=crop&w=500&q=80',
                'description' => 'কাস্টম মেকানিক্যাল কিবোর্ড, অ্যালুমিনিয়াম ল্যাপটপ স্ট্যান্ড ও অর্গানাইজার সমন্বিত আধুনিক ডেস্ক সেটআপ।',
                'sort_order' => 1,
                'is_active' => 1,
            ],
            [
                'name' => 'Audio Devices',
                'name_bn' => 'প্রিমিয়াম অডিও গিয়ার',
                'slug' => 'audio',
                'icon' => 'fa-headphones',
                'badge_text' => '১৫+ আইটেম',
                'badge_color' => '#2563eb',
                'image' => 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?auto=format&fit=crop&w=500&q=80',
                'description' => 'হাইড্রোফোবনিক স্টুডিও সাউন্ড, ANC হেডফোন, গেমিং বাডস ও হাই-ফাই স্পিকার।',
                'sort_order' => 2,
                'is_active' => 1,
            ],
            [
                'name' => 'Smart Gadgets',
                'name_bn' => 'স্মার্ট গ্যাজেটস',
                'slug' => 'gadget',
                'icon' => 'fa-microchip',
                'badge_text' => '২৪+ আইটেম',
                'badge_color' => '#10b981',
                'image' => 'https://images.unsplash.com/photo-1550009158-9ebf69173e03?auto=format&fit=crop&w=500&q=80',
                'description' => 'মাল্টিপোর্ট ডক, GaN ফাস্ট চার্জার ও AI ট্র্যাকিং ট্রাইপড।',
                'sort_order' => 3,
                'is_active' => 1,
            ],
            [
                'name' => 'Ambient Lighting',
                'name_bn' => 'অ্যাম্বিয়েন্ট লাইটিং',
                'slug' => 'home',
                'icon' => 'fa-lightbulb',
                'badge_text' => '১২+ আইটেম',
                'badge_color' => '#f59e0b',
                'image' => 'https://images.unsplash.com/photo-1513506003901-1e6a229e2d15?auto=format&fit=crop&w=500&q=80',
                'description' => 'ম্যাগনেটিক ফ্লোটিং মুন ল্যাম্প, স্ক্রিনবার ও আরজিবিআইসি কর্নার লাইট।',
                'sort_order' => 4,
                'is_active' => 1,
            ],
            [
                'name' => 'Minimal EDC Gear',
                'name_bn' => 'মিনিমাল EDC গিয়ার',
                'slug' => 'edc',
                'icon' => 'fa-toolbox',
                'badge_text' => '২০+ আইটেম',
                'badge_color' => '#6366f1',
                'image' => 'https://images.unsplash.com/photo-1627123424574-724758594e93?auto=format&fit=crop&w=500&q=80',
                'description' => 'কার্বন ফাইবার ম্যাগসেফ ওয়ালেট, টাইটানিয়াম মাল্টিটুল ও ট্যাকটিকাল ফ্ল্যাশলাইট।',
                'sort_order' => 5,
                'is_active' => 1,
            ],
        ];

        foreach ($categories as $cat) {
            $cat['created_at'] = now();
            $cat['updated_at'] = now();
            DB::table('categories')->updateOrInsert(['slug' => $cat['slug']], $cat);
        }
    }
}
