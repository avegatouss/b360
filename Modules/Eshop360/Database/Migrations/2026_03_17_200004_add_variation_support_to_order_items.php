<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('eshop_order_items', function (Blueprint $table) {
            $table->unsignedBigInteger('variation_id')->nullable()->after('product_id');
            $table->string('variation_name')->nullable()->after('product_name');
        });

        Schema::table('eshop_product_variations', function (Blueprint $table) {
            $table->boolean('is_active')->default(true)->after('values');
            $table->string('barcode')->nullable()->after('sku');
            $table->string('image')->nullable()->after('barcode');
        });
    }

    public function down(): void
    {
        Schema::table('eshop_order_items', function (Blueprint $table) {
            $table->dropColumn(['variation_id', 'variation_name']);
        });

        Schema::table('eshop_product_variations', function (Blueprint $table) {
            $table->dropColumn(['is_active', 'barcode', 'image']);
        });
    }
};
