<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Webhook deduplication key
        if (Schema::hasTable('eshop_webhook_logs') && !Schema::hasColumn('eshop_webhook_logs', 'deduplication_key')) {
            Schema::table('eshop_webhook_logs', function (Blueprint $table) {
                $table->string('deduplication_key', 64)->nullable()->after('success');
                $table->index('deduplication_key');
            });
        }

        // Commission idempotence: unique constraint on (order_id, employee_id)
        if (Schema::hasTable('eshop_employee_commissions')) {
            Schema::table('eshop_employee_commissions', function (Blueprint $table) {
                $table->unique(['order_id', 'employee_id'], 'uq_commission_order_employee');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('eshop_webhook_logs') && Schema::hasColumn('eshop_webhook_logs', 'deduplication_key')) {
            Schema::table('eshop_webhook_logs', function (Blueprint $table) {
                $table->dropIndex(['deduplication_key']);
                $table->dropColumn('deduplication_key');
            });
        }

        if (Schema::hasTable('eshop_employee_commissions')) {
            Schema::table('eshop_employee_commissions', function (Blueprint $table) {
                $table->dropUnique('uq_commission_order_employee');
            });
        }
    }
};
