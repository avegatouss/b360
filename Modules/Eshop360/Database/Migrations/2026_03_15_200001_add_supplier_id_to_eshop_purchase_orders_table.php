<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasColumn('eshop_purchase_orders', 'supplier_id')) {
            Schema::table('eshop_purchase_orders', function (Blueprint $table) {
                $table->foreignId('supplier_id')
                    ->nullable()
                    ->after('instance_id')
                    ->constrained('eshop_suppliers')
                    ->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        Schema::table('eshop_purchase_orders', function (Blueprint $table) {
            if (Schema::hasColumn('eshop_purchase_orders', 'supplier_id')) {
                $table->dropForeign(['supplier_id']);
                $table->dropColumn('supplier_id');
            }
        });
    }
};
