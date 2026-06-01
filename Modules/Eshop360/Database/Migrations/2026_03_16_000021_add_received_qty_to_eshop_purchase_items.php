<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasColumn('eshop_purchase_items', 'received_qty')) {
            Schema::table('eshop_purchase_items', function (Blueprint $table) {
                $table->integer('received_qty')->default(0)->after('quantity');
            });
        }
    }

    public function down(): void
    {
        Schema::table('eshop_purchase_items', function (Blueprint $table) {
            if (Schema::hasColumn('eshop_purchase_items', 'received_qty')) {
                $table->dropColumn('received_qty');
            }
        });
    }
};
