<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('eshop_channel_product_prices', function (Blueprint $table) {
            if (!Schema::hasColumn('eshop_channel_product_prices', 'purchase_price')) {
                $table->decimal('purchase_price', 15, 4)->nullable()->after('sale_price');
            }
            if (!Schema::hasColumn('eshop_channel_product_prices', 'margin_owner_pct')) {
                $table->decimal('margin_owner_pct', 5, 2)->default(0)->after('purchase_price');
            }
            if (!Schema::hasColumn('eshop_channel_product_prices', 'margin_channel_pct')) {
                $table->decimal('margin_channel_pct', 5, 2)->default(0)->after('margin_owner_pct');
            }
            if (!Schema::hasColumn('eshop_channel_product_prices', 'debt_enabled')) {
                $table->boolean('debt_enabled')->default(false)->after('margin_channel_pct');
            }
            if (!Schema::hasColumn('eshop_channel_product_prices', 'min_order_quantity')) {
                $table->unsignedInteger('min_order_quantity')->nullable()->after('debt_enabled');
            }
            if (!Schema::hasColumn('eshop_channel_product_prices', 'max_order_quantity')) {
                $table->unsignedInteger('max_order_quantity')->nullable()->after('min_order_quantity');
            }
        });
    }

    public function down(): void
    {
        Schema::table('eshop_channel_product_prices', function (Blueprint $table) {
            $cols = [
                'purchase_price',
                'margin_owner_pct',
                'margin_channel_pct',
                'debt_enabled',
                'min_order_quantity',
                'max_order_quantity',
            ];
            foreach ($cols as $col) {
                if (Schema::hasColumn('eshop_channel_product_prices', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
