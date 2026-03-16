<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('eshop_supplier_store', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_id')->constrained('eshop_suppliers')->cascadeOnDelete();
            $table->foreignId('store_id')->constrained('eshop_stores')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('eshop_supplier_store');
    }
};
