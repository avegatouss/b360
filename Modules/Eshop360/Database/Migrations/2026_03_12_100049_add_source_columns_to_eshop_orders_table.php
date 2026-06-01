<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('eshop_orders', function (Blueprint $table) {
            $table->foreignId('store_id')->nullable()->constrained('eshop_stores')->nullOnDelete();
            $table->foreignId('warehouse_id')->nullable()->constrained('eshop_warehouses')->nullOnDelete();
            $table->foreignId('cash_register_id')->nullable()->constrained('eshop_cash_registers')->nullOnDelete();
            $table->foreignId('holding_id')->nullable()->constrained('eshop_holdings')->nullOnDelete();
            $table->boolean('is_codifarm')->default(false);
            $table->string('payment_terms')->nullable();
            $table->date('delivery_date')->nullable();
            $table->datetime('delivered_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('eshop_orders', function (Blueprint $table) {
            $table->dropForeign(['store_id']);
            $table->dropForeign(['warehouse_id']);
            $table->dropForeign(['cash_register_id']);
            $table->dropForeign(['holding_id']);
            $table->dropColumn([
                'store_id',
                'warehouse_id',
                'cash_register_id',
                'holding_id',
                'is_codifarm',
                'payment_terms',
                'delivery_date',
                'delivered_at',
            ]);
        });
    }
};
