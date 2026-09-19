<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            'site_name' => 'Zippy',
            'site_tagline' => 'শপিং মানেই Zippy',
            'site_title' => 'Zippy | প্রিমিয়াম গ্যাজেট ও লাইফস্টাইল স্টোর বাংলাদেশ',
            'meta_description' => 'Zippy - বাংলাদেশের সেরা প্রিমিয়াম গ্যাজেট, মেকানিক্যাল কিবোর্ড, ডেস্ক সেটআপ এক্সেসরিজ ও অডিও ইকুইপমেন্টের অনলাইন শপ। ১০০% জেনুইন প্রোডাক্ট ও ক্যাশ অন ডেলিভারি।',
            'whatsapp_number' => '8801700000000',
            'phone' => '01700-000000',
            'email' => 'support@Zippy.com',
            'currency' => '৳',
            'shipping_inside_dhaka' => '60',
            'shipping_outside_dhaka' => '120',
            'gtm_enabled' => '1',
            'gtm_container_id' => 'GTM-ZIPPY01',
        ];

        foreach ($settings as $key => $val) {
            DB::table('settings')->updateOrInsert(
                ['key' => $key],
                ['value' => $val, 'created_at' => now(), 'updated_at' => now()]
            );
        }
    }
}
