<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EnterpriseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Roles (Only Super Admin root role by default; all other custom roles are created dynamically by Admin)
        $roles = [
            ['name' => 'super_admin', 'display_name' => 'Super Administrator', 'description' => 'Full unrestricted master access to all system modules'],
        ];

        foreach ($roles as $r) {
            DB::table('roles')->updateOrInsert(
                ['name' => $r['name']],
                ['display_name' => $r['display_name'], 'description' => $r['description'], 'created_at' => now(), 'updated_at' => now()]
            );
        }

        // Assign super_admin role to admin user
        $adminUser = DB::table('users')->where('email', 'admin@zippybd.com')->first();
        $superRole = DB::table('roles')->where('name', 'super_admin')->first();
        if ($adminUser && $superRole) {
            DB::table('user_roles')->updateOrInsert(
                ['user_id' => $adminUser->id, 'role_id' => $superRole->id]
            );
        }

        // 2. Permissions
        $permissions = [
            ['name' => 'dashboard.view', 'group_name' => 'Dashboard', 'display_name' => 'View Dashboard Analytics'],
            ['name' => 'orders.view', 'group_name' => 'Orders', 'display_name' => 'View Orders & Invoices'],
            ['name' => 'orders.edit', 'group_name' => 'Orders', 'display_name' => 'Update Status & Edit Orders'],
            ['name' => 'orders.courier', 'group_name' => 'Logistics', 'display_name' => 'Book & Track BD Couriers'],
            ['name' => 'orders.refund', 'group_name' => 'Logistics', 'display_name' => 'Process RMA Refunds'],
            ['name' => 'products.view', 'group_name' => 'Catalog', 'display_name' => 'View Products Catalog'],
            ['name' => 'products.manage', 'group_name' => 'Catalog', 'display_name' => 'Create/Edit Products & Variants'],
            ['name' => 'products.import_export', 'group_name' => 'Catalog', 'display_name' => 'CSV Bulk Import/Export'],
            ['name' => 'reports.financial', 'group_name' => 'Reports', 'display_name' => 'View Financial & Reconciliation Reports'],
            ['name' => 'reports.audit', 'group_name' => 'Reports', 'display_name' => 'View System Audit Logs'],
            ['name' => 'crm.view', 'group_name' => 'CRM', 'display_name' => 'Customer Profiles & Wallets'],
            ['name' => 'crm.fraud', 'group_name' => 'CRM', 'display_name' => 'Real-Time Fraud Checker & Manual Review'],
            ['name' => 'marketing.coupons', 'group_name' => 'Marketing', 'display_name' => 'Manage Coupons & Flash Deals'],
            ['name' => 'support.tickets', 'group_name' => 'Support', 'display_name' => 'Manage Customer Inquiries'],
            ['name' => 'settings.manage', 'group_name' => 'Settings', 'display_name' => 'Manage Gateways, SMS, SMTP, GTM, RBAC'],
        ];

        foreach ($permissions as $p) {
            DB::table('permissions')->updateOrInsert(
                ['name' => $p['name']],
                ['group_name' => $p['group_name'], 'display_name' => $p['display_name'], 'created_at' => now(), 'updated_at' => now()]
            );
        }

        // 3. Sample Coupons
        $coupons = [
            [
                'code' => 'WELCOME100',
                'type' => 'fixed',
                'value' => 100.00,
                'min_order_amount' => 1000.00,
                'max_discount_amount' => 100.00,
                'usage_limit' => 500,
                'used_count' => 12,
                'start_date' => now()->subDays(10),
                'end_date' => now()->addDays(90),
                'is_active' => 1
            ],
            [
                'code' => 'MEGA500',
                'type' => 'fixed',
                'value' => 500.00,
                'min_order_amount' => 5000.00,
                'max_discount_amount' => 500.00,
                'usage_limit' => 100,
                'used_count' => 5,
                'start_date' => now()->subDays(5),
                'end_date' => now()->addDays(30),
                'is_active' => 1
            ],
            [
                'code' => 'DISCOUNT10',
                'type' => 'percent',
                'value' => 10.00,
                'min_order_amount' => 2000.00,
                'max_discount_amount' => 300.00,
                'usage_limit' => 200,
                'used_count' => 18,
                'start_date' => now()->subDays(2),
                'end_date' => now()->addDays(45),
                'is_active' => 1
            ]
        ];

        foreach ($coupons as $c) {
            DB::table('coupons')->updateOrInsert(
                ['code' => $c['code']],
                array_merge($c, ['created_at' => now(), 'updated_at' => now()])
            );
        }

        // 4. Default Enterprise Settings
        $settings = [
            'currency_symbol' => '৳',
            'currency_code' => 'BDT',
            'gtm_container_id' => 'GTM-ZIPPY01',
            'gtm_enabled' => '1',
            'courier_default' => 'steadfast',
            'steadfast_api_key' => 'st_api_test_key_998124',
            'steadfast_secret_key' => 'st_sec_test_secret_772183',
            'pathao_client_id' => 'pathao_test_client_id',
            'pathao_client_secret' => 'pathao_test_secret',
            'pathao_username' => 'merchant@zippybd.com',
            'pathao_password' => 'merchant_pass',
            'redx_api_token' => 'redx_test_token_8819',
            'sms_provider' => 'bulksms',
            'sms_api_key' => 'sms_test_key_5512',
            'sms_sender_id' => 'ZippyBD',
            'sms_order_placed_template' => 'Dear {name}, your order #{order_number} has been received. Total: ৳{total}. Thanks for shopping with ZippyBD!',
            'sms_order_shipped_template' => 'Dear {name}, your order #{order_number} has been handed over to {courier}. Tracking code: {tracking}.',
            'low_stock_threshold' => '5',
            'fraud_auto_flag_threshold' => '65',
            'maintenance_mode' => '0',
            'auto_restock_on_cancel' => '1',
            'tax_rate_percent' => '0',
            'bkash_merchant_number' => '01700000000',
            'nagad_merchant_number' => '01700000000',
            'rocket_merchant_number' => '01700000000'
        ];

        foreach ($settings as $k => $v) {
            DB::table('settings')->updateOrInsert(
                ['key' => $k],
                ['value' => $v, 'created_at' => now(), 'updated_at' => now()]
            );
        }

        // 5. Sample Support Inquiries
        $tickets = [
            [
                'ticket_number' => 'TCK-1001',
                'customer_name' => 'Rahim Ahmed',
                'customer_phone' => '01711223344',
                'customer_email' => 'rahim@example.com',
                'subject' => 'Delivery time inquiry for Order #ZB-1002',
                'message' => 'Hello, I ordered a smart watch 2 days ago. Could you please let me know when it will be delivered in Chittagong?',
                'priority' => 'medium',
                'status' => 'open',
                'created_at' => now()->subHours(3),
                'updated_at' => now()->subHours(3)
            ],
            [
                'ticket_number' => 'TCK-1002',
                'customer_name' => 'Sadia Islam',
                'customer_phone' => '01899887766',
                'customer_email' => 'sadia@example.com',
                'subject' => 'Color variant change request',
                'message' => 'I would like to change my ordered earphone color from Black to Silver if possible before shipping.',
                'priority' => 'high',
                'status' => 'in_progress',
                'created_at' => now()->subHours(6),
                'updated_at' => now()->subHours(1)
            ]
        ];

        foreach ($tickets as $t) {
            DB::table('support_tickets')->updateOrInsert(
                ['ticket_number' => $t['ticket_number']],
                $t
            );
        }
    }
}
