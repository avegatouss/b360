<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('eshop_products', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('instance_id')->index();
            $table->foreignId('category_id')->nullable()->constrained('eshop_categories')->nullOnDelete();
            $table->foreignId('brand_id')->nullable()->constrained('eshop_brands')->nullOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->string('sku')->nullable();
            $table->text('description')->nullable();
            $table->decimal('price', 12, 2)->default(0);
            $table->decimal('cost_price', 12, 2)->default(0);
            $table->decimal('tax_rate', 5, 2)->default(0);
            $table->enum('discount_type', ['none', 'percentage', 'fixed'])->default('none');
            $table->decimal('discount_value', 12, 2)->default(0);
            $table->string('unit', 20)->default('pc');
            $table->integer('min_quantity')->default(0);
            $table->integer('alert_quantity')->default(10);
            $table->string('barcode')->nullable();
            $table->string('qrcode')->nullable();
            $table->string('image')->nullable();
            $table->json('images')->nullable();
            $table->date('expiry_date')->nullable();
            $table->date('manufactured_date')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['instance_id', 'sku']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('eshop_products');
    }
};
