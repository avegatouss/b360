<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Add exchange_rate column to eshop_payments.
 *
 * The Currency module's Phase 2 migration (2026_04_04_300002_multi_currency_phase2.php)
 * added `currency_code` and `amount_in_base_currency` to eshop_payments but not
 * `exchange_rate` (asymmetry vs eshop_orders/eshop_invoices). This migration fills
 * the gap so payment-time rate auditing is possible.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('eshop_payments') && !Schema::hasColumn('eshop_payments', 'exchange_rate')) {
            Schema::table('eshop_payments', function (Blueprint $table) {
                $table->decimal('exchange_rate', 20, 10)->nullable()->after('currency_code');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('eshop_payments') && Schema::hasColumn('eshop_payments', 'exchange_rate')) {
            Schema::table('eshop_payments', function (Blueprint $table) {
                $table->dropColumn('exchange_rate');
            });
        }
    }
};
