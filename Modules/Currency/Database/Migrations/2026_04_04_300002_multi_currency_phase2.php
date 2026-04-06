<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Multi-currency Phase 2 — User preferences, order snapshots, Eshop360 extensions.
 */
return new class extends Migration
{
    public function up(): void
    {
        // 1. User currency preferences (persistent choice per user per instance)
        if (!Schema::hasTable('user_currency_preferences')) {
            Schema::create('user_currency_preferences', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id');
                $table->unsignedBigInteger('instance_id');
                $table->string('preferred_currency', 10);
                $table->timestamps();
                $table->unique(['user_id', 'instance_id']);
            });
        }

        // 2. Polymorphic currency snapshots (immutable — never update after creation)
        if (!Schema::hasTable('order_currency_snapshots')) {
            Schema::create('order_currency_snapshots', function (Blueprint $table) {
                $table->id();
                $table->morphs('snapshotable'); // snapshotable_type + snapshotable_id
                $table->string('display_currency', 10);
                $table->string('base_currency', 10);
                $table->decimal('exchange_rate', 20, 10);
                $table->json('amounts'); // { total_display, total_base, subtotal_display, ... }
                $table->timestamp('snapshotted_at');
            });
        }

        // 3. Extend eshop_orders with currency columns
        if (Schema::hasTable('eshop_orders') && !Schema::hasColumn('eshop_orders', 'currency_code')) {
            Schema::table('eshop_orders', function (Blueprint $table) {
                $table->string('currency_code', 10)->nullable()->after('total');
                $table->decimal('exchange_rate', 20, 10)->nullable()->after('currency_code');
            });
        }

        // 4. Extend eshop_invoices with currency columns
        if (Schema::hasTable('eshop_invoices') && !Schema::hasColumn('eshop_invoices', 'currency_code')) {
            Schema::table('eshop_invoices', function (Blueprint $table) {
                $table->string('currency_code', 10)->nullable()->after('total');
                $table->decimal('exchange_rate', 20, 10)->nullable()->after('currency_code');
            });
        }

        // 5. Extend eshop_payments with currency columns
        if (Schema::hasTable('eshop_payments') && !Schema::hasColumn('eshop_payments', 'currency_code')) {
            Schema::table('eshop_payments', function (Blueprint $table) {
                $table->string('currency_code', 10)->nullable()->after('amount');
                $table->decimal('amount_in_base_currency', 15, 4)->nullable()->after('currency_code');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('eshop_payments') && Schema::hasColumn('eshop_payments', 'currency_code')) {
            Schema::table('eshop_payments', function (Blueprint $table) {
                $table->dropColumn(['currency_code', 'amount_in_base_currency']);
            });
        }

        if (Schema::hasTable('eshop_invoices') && Schema::hasColumn('eshop_invoices', 'currency_code')) {
            Schema::table('eshop_invoices', function (Blueprint $table) {
                $table->dropColumn(['currency_code', 'exchange_rate']);
            });
        }

        if (Schema::hasTable('eshop_orders') && Schema::hasColumn('eshop_orders', 'currency_code')) {
            Schema::table('eshop_orders', function (Blueprint $table) {
                $table->dropColumn(['currency_code', 'exchange_rate']);
            });
        }

        Schema::dropIfExists('order_currency_snapshots');
        Schema::dropIfExists('user_currency_preferences');
    }
};
