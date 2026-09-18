<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Alter orders table to add enterprise columns
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'courier_provider')) {
                $table->string('courier_provider', 50)->nullable()->after('payment_status');
            }
            if (!Schema::hasColumn('orders', 'courier_tracking_code')) {
                $table->string('courier_tracking_code', 100)->nullable()->after('courier_provider');
            }
            if (!Schema::hasColumn('orders', 'courier_consignment_id')) {
                $table->string('courier_consignment_id', 100)->nullable()->after('courier_tracking_code');
            }
            if (!Schema::hasColumn('orders', 'courier_status')) {
                $table->string('courier_status', 100)->nullable()->after('courier_consignment_id');
            }
            if (!Schema::hasColumn('orders', 'fraud_score')) {
                $table->integer('fraud_score')->default(0)->after('courier_status');
            }
            if (!Schema::hasColumn('orders', 'fraud_status')) {
                $table->string('fraud_status', 50)->default('safe')->after('fraud_score');
            }
            if (!Schema::hasColumn('orders', 'fraud_notes')) {
                $table->text('fraud_notes')->nullable()->after('fraud_status');
            }
            if (!Schema::hasColumn('orders', 'refunded_amount')) {
                $table->decimal('refunded_amount', 10, 2)->default(0.00)->after('discount');
            }
            if (!Schema::hasColumn('orders', 'is_refunded')) {
                $table->tinyInteger('is_refunded')->default(0)->after('refunded_amount');
            }
            if (!Schema::hasColumn('orders', 'packing_slip_printed_at')) {
                $table->timestamp('packing_slip_printed_at')->nullable()->after('updated_at');
            }
            if (!Schema::hasColumn('orders', 'ip_address')) {
                $table->string('ip_address', 45)->nullable()->after('notes');
            }
            if (!Schema::hasColumn('orders', 'user_agent')) {
                $table->text('user_agent')->nullable()->after('ip_address');
            }
        });

        // 2. Courier Consignments
        if (!Schema::hasTable('courier_consignments')) {
            Schema::create('courier_consignments', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('order_id')->index();
                $table->string('provider', 50)->index(); // steadfast, pathao, redx, ecourier, paperfly
                $table->string('consignment_id', 100)->nullable()->index();
                $table->string('tracking_code', 100)->nullable()->index();
                $table->string('status', 100)->default('pending');
                $table->decimal('cod_amount', 10, 2)->default(0.00);
                $table->json('request_payload')->nullable();
                $table->json('response_payload')->nullable();
                $table->timestamps();

                $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
            });
        }

        // 3. Courier Webhooks
        if (!Schema::hasTable('courier_webhooks')) {
            Schema::create('courier_webhooks', function (Blueprint $table) {
                $table->id();
                $table->string('provider', 50)->index();
                $table->string('event_type', 100)->nullable();
                $table->string('tracking_code', 100)->nullable()->index();
                $table->json('payload')->nullable();
                $table->boolean('processed')->default(false);
                $table->timestamps();
            });
        }

        // 4. Refunds & RMA
        if (!Schema::hasTable('refunds')) {
            Schema::create('refunds', function (Blueprint $table) {
                $table->id();
                $table->string('refund_number', 50)->unique();
                $table->unsignedBigInteger('order_id')->index();
                $table->string('refund_type', 20)->default('full'); // full, partial
                $table->decimal('amount', 10, 2);
                $table->string('reason', 255);
                $table->boolean('restock_inventory')->default(true);
                $table->string('status', 30)->default('approved'); // pending, approved, rejected, completed
                $table->string('processed_by', 100)->nullable();
                $table->text('admin_notes')->nullable();
                $table->timestamps();

                $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
            });
        }

        if (!Schema::hasTable('refund_items')) {
            Schema::create('refund_items', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('refund_id')->index();
                $table->unsignedBigInteger('order_item_id')->nullable()->index();
                $table->unsignedBigInteger('product_id')->nullable()->index();
                $table->string('product_title', 255);
                $table->integer('quantity')->default(1);
                $table->decimal('refund_price', 10, 2);
                $table->timestamps();

                $table->foreign('refund_id')->references('id')->on('refunds')->onDelete('cascade');
            });
        }

        // 5. System Audit Logs
        if (!Schema::hasTable('audit_logs')) {
            Schema::create('audit_logs', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id')->nullable()->index();
                $table->string('user_name', 100)->nullable();
                $table->string('action', 50)->index(); // create, update, delete, status_change, courier_push, refund, login
                $table->string('module', 50)->index(); // orders, products, categories, coupons, settings, courier, refunds
                $table->string('target_id', 50)->nullable();
                $table->text('description')->nullable();
                $table->json('old_values')->nullable();
                $table->json('new_values')->nullable();
                $table->string('ip_address', 45)->nullable();
                $table->text('user_agent')->nullable();
                $table->timestamp('created_at')->useCurrent()->index();
            });
        }

        // 6. Coupons & Usages
        if (!Schema::hasTable('coupons')) {
            Schema::create('coupons', function (Blueprint $table) {
                $table->id();
                $table->string('code', 50)->unique();
                $table->string('type', 20)->default('fixed'); // fixed, percent
                $table->decimal('value', 10, 2);
                $table->decimal('min_order_amount', 10, 2)->default(0.00);
                $table->decimal('max_discount_amount', 10, 2)->nullable();
                $table->integer('usage_limit')->nullable();
                $table->integer('used_count')->default(0);
                $table->dateTime('start_date')->nullable();
                $table->dateTime('end_date')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('coupon_usages')) {
            Schema::create('coupon_usages', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('coupon_id')->index();
                $table->unsignedBigInteger('order_id')->index();
                $table->string('customer_phone', 50)->index();
                $table->decimal('discount_amount', 10, 2);
                $table->timestamp('created_at')->useCurrent();

                $table->foreign('coupon_id')->references('id')->on('coupons')->onDelete('cascade');
                $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
            });
        }

        // 7. Abandoned Carts
        if (!Schema::hasTable('abandoned_carts')) {
            Schema::create('abandoned_carts', function (Blueprint $table) {
                $table->id();
                $table->string('session_id', 100)->index();
                $table->string('customer_name', 100)->nullable();
                $table->string('customer_phone', 50)->nullable()->index();
                $table->json('cart_data');
                $table->decimal('total_amount', 10, 2)->default(0.00);
                $table->boolean('is_recovered')->default(false);
                $table->timestamp('last_activity_at')->useCurrent();
                $table->timestamps();
            });
        }

        // 8. Customer Wallets & Reward Points
        if (!Schema::hasTable('customer_wallets')) {
            Schema::create('customer_wallets', function (Blueprint $table) {
                $table->id();
                $table->string('customer_phone', 50)->unique();
                $table->string('customer_name', 100)->nullable();
                $table->decimal('balance', 10, 2)->default(0.00);
                $table->integer('reward_points')->default(0);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('wallet_transactions')) {
            Schema::create('wallet_transactions', function (Blueprint $table) {
                $table->id();
                $table->string('customer_phone', 50)->index();
                $table->string('type', 20); // credit, debit
                $table->decimal('amount', 10, 2)->default(0.00);
                $table->integer('points')->default(0);
                $table->string('reference_type', 50)->nullable();
                $table->string('reference_id', 50)->nullable();
                $table->string('description', 255)->nullable();
                $table->timestamp('created_at')->useCurrent();
            });
        }

        // 9. Customer Support Desk / Inquiries
        if (!Schema::hasTable('support_tickets')) {
            Schema::create('support_tickets', function (Blueprint $table) {
                $table->id();
                $table->string('ticket_number', 50)->unique();
                $table->string('customer_name', 100);
                $table->string('customer_phone', 50)->index();
                $table->string('customer_email', 100)->nullable();
                $table->string('subject', 255);
                $table->text('message');
                $table->string('priority', 20)->default('medium'); // low, medium, high, urgent
                $table->string('status', 30)->default('open'); // open, in_progress, resolved, closed
                $table->text('admin_reply')->nullable();
                $table->timestamps();
            });
        }

        // 10. RBAC Roles & Permissions
        if (!Schema::hasTable('roles')) {
            Schema::create('roles', function (Blueprint $table) {
                $table->id();
                $table->string('name', 50)->unique();
                $table->string('display_name', 100);
                $table->string('description', 255)->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('permissions')) {
            Schema::create('permissions', function (Blueprint $table) {
                $table->id();
                $table->string('name', 100)->unique();
                $table->string('group_name', 50)->index();
                $table->string('display_name', 150);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('role_permissions')) {
            Schema::create('role_permissions', function (Blueprint $table) {
                $table->unsignedBigInteger('role_id');
                $table->unsignedBigInteger('permission_id');
                $table->primary(['role_id', 'permission_id']);
            });
        }

        if (!Schema::hasTable('user_roles')) {
            Schema::create('user_roles', function (Blueprint $table) {
                $table->unsignedBigInteger('user_id');
                $table->unsignedBigInteger('role_id');
                $table->primary(['user_id', 'role_id']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_roles');
        Schema::dropIfExists('role_permissions');
        Schema::dropIfExists('permissions');
        Schema::dropIfExists('roles');
        Schema::dropIfExists('support_tickets');
        Schema::dropIfExists('wallet_transactions');
        Schema::dropIfExists('customer_wallets');
        Schema::dropIfExists('abandoned_carts');
        Schema::dropIfExists('coupon_usages');
        Schema::dropIfExists('coupons');
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('refund_items');
        Schema::dropIfExists('refunds');
        Schema::dropIfExists('courier_webhooks');
        Schema::dropIfExists('courier_consignments');
    }
};