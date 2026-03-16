<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('eshop_product_taxes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('eshop_products')->cascadeOnDelete();
            $table->foreignId('tax_id')->constrained('eshop_taxes');
            $table->enum('type', ['inclusive', 'exclusive'])->default('exclusive');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('eshop_product_taxes');
    }
};
