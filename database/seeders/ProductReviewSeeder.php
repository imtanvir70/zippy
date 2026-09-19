<?php

namespace Database\Seeders;

use App\Services\Review\ReviewService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductReviewSeeder extends Seeder
{
    public function run(): void
    {
        $products = DB::table('products')->get();

        if ($products->isEmpty()) {
            return;
        }

        $sampleReviews = [
            [
                'customer_name' => 'তানভীর আহমেদ',
                'customer_phone' => '01711***452',
                'rating' => 5,
                'comment' => 'অসাধারণ প্রোডাক্ট! ডেলিভারি অনেক দ্রুত পেয়েছি। প্যাকেজিং খুব প্রিমিয়াম ছিল। ঘড়ির ডিসপ্লে কোয়ালিটি এবং ব্যাটারি ব্যাকআপ দারুণ। ধন্যবাদ Zippy কে!',
                'status' => 'approved',
                'created_at' => now()->subDays(12),
                'updated_at' => now()->subDays(12),
            ],
            [
                'customer_name' => 'মেহেদী হাসান',
                'customer_phone' => '01822***891',
                'rating' => 5,
                'comment' => 'যেমনটি ছবিতে দেখেছি ঠিক তেমনই পেয়েছি। সিলিকন স্ট্র্যাপের কোয়ালিটি খুবই আরামদায়ক। টাচ রেসপন্স বেশ ভালো।',
                'status' => 'approved',
                'created_at' => now()->subDays(9),
                'updated_at' => now()->subDays(9),
            ],
            [
                'customer_name' => 'ফারহানা ইসলাম',
                'customer_phone' => '01933***114',
                'rating' => 4,
                'comment' => 'প্রোডাক্ট অনেক ভালো তবে ডেলিভারি পেতে ৩ দিন সময় লেগেছে। সামগ্রিকভাবে এই প্রাইস রেঞ্জে খুবই ভালো একটি চয়েজ।',
                'status' => 'approved',
                'created_at' => now()->subDays(6),
                'updated_at' => now()->subDays(6),
            ],
            [
                'customer_name' => 'সাজিদ চৌধুরী',
                'customer_phone' => '01644***732',
                'rating' => 5,
                'comment' => '১০০% অরিজিনাল গ্যাজেট। কলিং ফিচার এবং সাউন্ড কোয়ালিটি চমৎকার স্পষ্ট। সবাইকে নেওয়ার সুপারিশ করব।',
                'status' => 'approved',
                'created_at' => now()->subDays(3),
                'updated_at' => now()->subDays(3),
            ],
            [
                'customer_name' => 'রাকিবুল হাসান',
                'customer_phone' => '01555***980',
                'rating' => 5,
                'comment' => 'অরেঞ্জ স্ট্র্যাপের কালারটা দারুণ ফুটে উঠেছে। ওয়াচ ফেসগুলো অনেক সুন্দর। লুক একদম আল্ট্রা প্রিমিয়াম।',
                'status' => 'approved',
                'created_at' => now()->subDay(),
                'updated_at' => now()->subDay(),
            ],
        ];

        foreach ($products as $p) {
            // Only seed if product has no reviews yet
            $existingCount = DB::table('product_reviews')->where('product_id', $p->id)->count();
            if ($existingCount === 0) {
                foreach ($sampleReviews as $rev) {
                    DB::table('product_reviews')->insert([
                        'product_id' => $p->id,
                        'customer_name' => $rev['customer_name'],
                        'customer_phone' => $rev['customer_phone'],
                        'rating' => $rev['rating'],
                        'comment' => $rev['comment'],
                        'status' => $rev['status'],
                        'created_at' => $rev['created_at'],
                        'updated_at' => $rev['updated_at'],
                    ]);
                }
            }

            // Recalculate and synchronize product rating and count dynamically
            ReviewService::syncProductReviewStats($p->id);
        }
    }
}
