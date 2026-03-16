<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('eshop_stocks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('instance_id')->index();
            $table->foreignId('product_id')->constrained('eshop_products')->cascadeOnDelete();
            $table->foreignId('warehouse_id')->constrained('eshop_warehouses')->cascadeOnDelete();
            $table->foreignId('store_id')->nullable()->constrained('eshop_stores')->cascadeOnDelete();
            $table->integer('quantity')->default(0);
            $table->integer('reserved_quantity')->default(0);
            $table->timestamps();
            $table->unique(['instance_id', 'product_id', 'warehouse_id', 'store_id'], 'eshop_stocks_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('eshop_stocks');
    }
};
