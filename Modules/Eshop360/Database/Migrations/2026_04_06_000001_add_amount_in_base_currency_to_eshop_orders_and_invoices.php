<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Symmetrise multi-currency reporting columns.
 *
 * Phase 2 of the Currency module (2026_04_04_300002_multi_currency_phase2.php)
 * already added `currency_code` + `exchange_rate` on eshop_orders, eshop_invoices
 * and eshop_payments, plus `amount_in_base_currency` on eshop_payments only.
 *
 * To enable consolidated reporting in the tenant base currency across orders
 * and invoices (not just payments), we add `amount_in_base_currency` here.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('eshop_orders') && !Schema::hasColumn('eshop_orders', 'amount_in_base_currency')) {
            Schema::table('eshop_orders', function (Blueprint $table) {
                $table->decimal('amount_in_base_currency', 15, 4)->nullable()->after('exchange_rate');
            });
        }

        if (Schema::hasTable('eshop_invoices') && !Schema::hasColumn('eshop_invoices', 'amount_in_base_currency')) {
            Schema::table('eshop_invoices', function (Blueprint $table) {
                $table->decimal('amount_in_base_currency', 15, 4)->nullable()->after('exchange_rate');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('eshop_orders') && Schema::hasColumn('eshop_orders', 'amount_in_base_currency')) {
            Schema::table('eshop_orders', function (Blueprint $table) {
                $table->dropColumn('amount_in_base_currency');
            });
        }

        if (Schema::hasTable('eshop_invoices') && Schema::hasColumn('eshop_invoices', 'amount_in_base_currency')) {
            Schema::table('eshop_invoices', function (Blueprint $table) {
                $table->dropColumn('amount_in_base_currency');
            });
        }
    }
};
