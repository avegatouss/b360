<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('eshop_import_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('import_order_id')->constrained('eshop_import_orders')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('eshop_products');
            $table->integer('quantity');
            $table->decimal('unit_price_factory', 15, 4);
            $table->decimal('total_factory', 15, 2);
            $table->decimal('allocated_cost', 15, 2)->default(0);
            $table->decimal('cost_price_real', 15, 4)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('eshop_import_order_items');
    }
};
