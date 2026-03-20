<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Per-channel pricing for products (replaces per-channel sale_price column)
        Schema::create('eshop_channel_product_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('channel_id')->constrained('eshop_distribution_channels')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('eshop_products')->cascadeOnDelete();
            $table->decimal('sale_price', 15, 4)->nullable(); // auto-calculated or manual override
            $table->boolean('is_manual_override')->default(false);
            $table->timestamps();

            $table->unique(['channel_id', 'product_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('eshop_channel_product_prices');
    }
};
