<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('eshop_stock_movements', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('instance_id')->index();
            $table->foreignId('product_id')->constrained('eshop_products')->cascadeOnDelete();
            $table->foreignId('warehouse_id')->constrained('eshop_warehouses')->cascadeOnDelete();
            $table->foreignId('store_id')->nullable()->constrained('eshop_stores')->cascadeOnDelete();
            $table->enum('type', ['in', 'out', 'adjustment', 'transfer', 'return']);
            $table->integer('quantity');
            $table->string('reference_type')->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('performed_by')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('eshop_stock_movements');
    }
};
