<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('eshop_products', function (Blueprint $table) {
            if (!Schema::hasColumn('eshop_products', 'wholesale_price_mode')) {
                $table->enum('wholesale_price_mode', ['percentage', 'fixed', 'manual'])
                    ->default('manual')
                    ->after('wholesale_price');
            }
            if (!Schema::hasColumn('eshop_products', 'wholesale_price_rate')) {
                $table->decimal('wholesale_price_rate', 10, 4)
                    ->nullable()
                    ->after('wholesale_price_mode');
            }
            if (!Schema::hasColumn('eshop_products', 'pharmacy_price_mode')) {
                $table->enum('pharmacy_price_mode', ['percentage', 'fixed', 'manual'])
                    ->default('manual')
                    ->after('pharmacy_price');
            }
            if (!Schema::hasColumn('eshop_products', 'pharmacy_price_rate')) {
                $table->decimal('pharmacy_price_rate', 10, 4)
                    ->nullable()
                    ->after('pharmacy_price_mode');
            }
            if (!Schema::hasColumn('eshop_products', 'price_mode')) {
                $table->enum('price_mode', ['manual', 'wholesale_based'])
                    ->default('manual')
                    ->after('price');
            }
            if (!Schema::hasColumn('eshop_products', 'price_rate')) {
                $table->decimal('price_rate', 10, 4)
                    ->nullable()
                    ->after('price_mode');
            }
            if (!Schema::hasColumn('eshop_products', 'min_order_quantity')) {
                $table->unsignedInteger('min_order_quantity')
                    ->default(1);
            }
            if (!Schema::hasColumn('eshop_products', 'max_order_quantity')) {
                $table->unsignedInteger('max_order_quantity')
                    ->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('eshop_products', function (Blueprint $table) {
            $cols = [
                'wholesale_price_mode',
                'wholesale_price_rate',
                'pharmacy_price_mode',
                'pharmacy_price_rate',
                'price_mode',
                'price_rate',
                'min_order_quantity',
                'max_order_quantity',
            ];
            foreach ($cols as $col) {
                if (Schema::hasColumn('eshop_products', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
