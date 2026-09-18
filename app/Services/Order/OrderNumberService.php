<?php

namespace App\Services\Order;

use Illuminate\Support\Facades\DB;

class OrderNumberService
{
    /**
     * Generate an easy, highly readable, and unique order number.
     * Format: ZB-A5W6B8 (ZB- followed by 6 alphanumeric characters with a guaranteed mix of letters and digits).
     * Excludes easily confused characters (0, O, 1, I, L) for supreme readability over phone, SMS, and invoices.
     */
    public static function generate(): string
    {
        // Letters without ambiguous I, O, L
        $letters = 'ABCDEFGHJKMNPQRSTUVWXYZ';
        // Digits without ambiguous 0, 1
        $digits = '23456789';

        $maxAttempts = 50;
        $attempt = 0;

        do {
            $attempt++;

            // Guarantee a strong, balanced mix (at least 2 letters and 2 digits)
            $chars = [];
            $chars[] = $letters[random_int(0, strlen($letters) - 1)];
            $chars[] = $letters[random_int(0, strlen($letters) - 1)];
            $chars[] = $digits[random_int(0, strlen($digits) - 1)];
            $chars[] = $digits[random_int(0, strlen($digits) - 1)];

            $all = $letters . $digits;
            $chars[] = $all[random_int(0, strlen($all) - 1)];
            $chars[] = $all[random_int(0, strlen($all) - 1)];

            // Shuffle so letters and numbers are interleaved naturally
            shuffle($chars);
            $code = implode('', $chars);

            $orderNumber = 'ZB-' . $code;

            $exists = DB::table('orders')->where('order_number', $orderNumber)->exists();
        } while ($exists && $attempt < $maxAttempts);

        // Fallback in case of collision
        if ($exists) {
            $orderNumber = 'ZB-' . strtoupper(substr(uniqid(), -6));
        }

        return $orderNumber;
    }
}
