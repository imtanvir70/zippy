<?php

namespace App\Services\Marketing;

use Illuminate\Support\Facades\DB;

class CouponService
{
    /**
     * Validate and calculate discount for a coupon code against cart subtotal.
     */
    public function validateAndApply(string $code, float $cartSubtotal, ?string $phone = null): array
    {
        $code = strtoupper(trim($code));
        if (empty($code)) {
            return ['success' => false, 'message' => 'Please enter a coupon code.'];
        }

        $coupon = DB::table('coupons')
            ->where('code', $code)
            ->where('is_active', 1)
            ->first();

        if (!$coupon) {
            return ['success' => false, 'message' => 'Invalid or expired coupon code.'];
        }

        $now = now();
        if ($coupon->start_date && $now->lt($coupon->start_date)) {
            return ['success' => false, 'message' => 'This coupon has not started yet.'];
        }

        if ($coupon->end_date && $now->gt($coupon->end_date)) {
            return ['success' => false, 'message' => 'This coupon has expired.'];
        }

        if ($cartSubtotal < (float) $coupon->min_order_amount) {
            return [
                'success' => false,
                'message' => "Minimum order amount of ৳{$coupon->min_order_amount} required to use this coupon."
            ];
        }

        if ($coupon->usage_limit && $coupon->used_count >= $coupon->usage_limit) {
            return ['success' => false, 'message' => 'Coupon usage limit has been reached.'];
        }

        // Check customer specific usage if phone provided
        if (!empty($phone)) {
            $userUsage = DB::table('coupon_usages')
                ->where('coupon_id', $coupon->id)
                ->where('customer_phone', $phone)
                ->count();

            if ($userUsage >= 3) {
                return ['success' => false, 'message' => 'You have already reached the maximum usage limit for this coupon.'];
            }
        }

        // Calculate discount
        $discount = 0.00;
        if ($coupon->type === 'percent') {
            $discount = ($cartSubtotal * (float) $coupon->value) / 100;
            if ($coupon->max_discount_amount && $discount > (float) $coupon->max_discount_amount) {
                $discount = (float) $coupon->max_discount_amount;
            }
        } else {
            $discount = (float) $coupon->value;
        }

        // Discount cannot exceed cart subtotal
        $discount = min($discount, $cartSubtotal);

        return [
            'success' => true,
            'coupon_id' => $coupon->id,
            'code' => $coupon->code,
            'type' => $coupon->type,
            'discount' => round($discount, 2),
            'message' => "Coupon '{$coupon->code}' applied successfully! You saved ৳" . number_format($discount, 0)
        ];
    }

    /**
     * Record coupon usage after order is successfully placed.
     */
    public function recordUsage(int $couponId, int $orderId, string $phone, float $discountAmount): void
    {
        DB::transaction(function () use ($couponId, $orderId, $phone, $discountAmount) {
            DB::table('coupon_usages')->insert([
                'coupon_id' => $couponId,
                'order_id' => $orderId,
                'customer_phone' => $phone,
                'discount_amount' => $discountAmount,
                'created_at' => now(),
            ]);

            DB::table('coupons')->where('id', $couponId)->increment('used_count');
        });
    }
}