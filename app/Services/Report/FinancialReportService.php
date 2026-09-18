<?php

namespace App\Services\Report;

use Illuminate\Support\Facades\DB;

class FinancialReportService
{
    /**
     * Generate financial summary analytics for a date range.
     */
    public function generateFinancialSummary(string $startDate, string $endDate): array
    {
        $ordersQuery = DB::table('orders')
            ->whereDate('created_at', '>=', $startDate)
            ->whereDate('created_at', '<=', $endDate);

        $totalOrders = (clone $ordersQuery)->count();
        $successfulOrders = (clone $ordersQuery)->whereIn('order_status', ['delivered', 'processing', 'shipped'])->count();
        $cancelledOrders = (clone $ordersQuery)->where('order_status', 'cancelled')->count();

        $grossSales = (float) (clone $ordersQuery)->where('order_status', '!=', 'cancelled')->sum('total');
        $subtotal = (float) (clone $ordersQuery)->where('order_status', '!=', 'cancelled')->sum('subtotal');
        $shippingCollected = (float) (clone $ordersQuery)->where('order_status', '!=', 'cancelled')->sum('shipping_cost');
        $totalDiscount = (float) (clone $ordersQuery)->where('order_status', '!=', 'cancelled')->sum('discount');
        $refundedAmount = (float) (clone $ordersQuery)->sum('refunded_amount');

        // Estimate Cost of Goods (COGS) at ~65% of subtotal
        $estimatedCogs = $subtotal * 0.65;
        // Estimated gateway fees (1.5% on non-COD payments)
        $digitalPaymentsTotal = (float) (clone $ordersQuery)->where('order_status', '!=', 'cancelled')->where('payment_method', '!=', 'cod')->sum('total');
        $gatewayFees = $digitalPaymentsTotal * 0.015;

        $netProfit = $grossSales - $estimatedCogs - $refundedAmount - $gatewayFees;
        $aov = $successfulOrders > 0 ? $grossSales / $successfulOrders : 0;

        // Payment Method breakdown
        $paymentMethods = (clone $ordersQuery)
            ->where('order_status', '!=', 'cancelled')
            ->select('payment_method', DB::raw('count(*) as count'), DB::raw('sum(total) as total_amount'))
            ->groupBy('payment_method')
            ->get();

        // District Breakdown (Dhaka vs Outside)
        $dhakaStats = (clone $ordersQuery)
            ->where('order_status', '!=', 'cancelled')
            ->where('district', 'Dhaka')
            ->select(DB::raw('count(*) as count'), DB::raw('sum(total) as total_amount'))
            ->first();

        $outsideDhakaStats = (clone $ordersQuery)
            ->where('order_status', '!=', 'cancelled')
            ->where('district', '!=', 'Dhaka')
            ->select(DB::raw('count(*) as count'), DB::raw('sum(total) as total_amount'))
            ->first();

        return [
            'start_date' => $startDate,
            'end_date' => $endDate,
            'total_orders' => $totalOrders,
            'successful_orders' => $successfulOrders,
            'cancelled_orders' => $cancelledOrders,
            'gross_sales' => $grossSales,
            'subtotal' => $subtotal,
            'shipping_collected' => $shippingCollected,
            'total_discount' => $totalDiscount,
            'refunded_amount' => $refundedAmount,
            'estimated_cogs' => $estimatedCogs,
            'gateway_fees' => $gatewayFees,
            'net_profit' => $netProfit,
            'aov' => $aov,
            'payment_methods' => $paymentMethods,
            'dhaka_orders' => $dhakaStats->count ?? 0,
            'dhaka_sales' => (float) ($dhakaStats->total_amount ?? 0),
            'outside_dhaka_orders' => $outsideDhakaStats->count ?? 0,
            'outside_dhaka_sales' => (float) ($outsideDhakaStats->total_amount ?? 0),
        ];
    }

    /**
     * Generate standard CSV spreadsheet export of orders and revenue.
     */
    public function generateOrdersCsv(string $startDate, string $endDate): string
    {
        $orders = DB::table('orders')
            ->whereDate('created_at', '>=', $startDate)
            ->whereDate('created_at', '<=', $endDate)
            ->orderByDesc('id')
            ->get();

        $handle = fopen('php://temp', 'r+');
        fputcsv($handle, [
            'Order #', 'Date', 'Customer Name', 'Phone', 'District', 'Address',
            'Subtotal (BDT)', 'Shipping (BDT)', 'Discount (BDT)', 'Total (BDT)',
            'Refunded (BDT)', 'Payment Method', 'Payment Status', 'Order Status',
            'Courier Provider', 'Tracking Code'
        ]);

        foreach ($orders as $ord) {
            fputcsv($handle, [
                $ord->order_number,
                $ord->created_at,
                $ord->customer_name,
                $ord->customer_phone,
                $ord->district,
                $ord->customer_address,
                $ord->subtotal,
                $ord->shipping_cost,
                $ord->discount,
                $ord->total,
                $ord->refunded_amount ?? 0,
                $ord->payment_method,
                $ord->payment_status,
                $ord->order_status,
                $ord->courier_provider ?? 'N/A',
                $ord->courier_tracking_code ?? 'N/A'
            ]);
        }

        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        return $csv;
    }
}