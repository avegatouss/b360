<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('eshop_import_orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('instance_id')->index();
            $table->foreignId('supplier_id')->constrained('eshop_suppliers');
            $table->string('reference');
            $table->string('container_no')->nullable();
            $table->enum('shipping_type', ['sea', 'air', 'land']);
            $table->date('ship_date')->nullable();
            $table->date('eta')->nullable();
            $table->enum('status', ['draft', 'confirmed', 'shipped', 'customs', 'received', 'cancelled'])->default('draft');
            $table->foreignId('warehouse_id')->constrained('eshop_warehouses');
            $table->enum('cost_allocation_method', ['value', 'quantity'])->default('value');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['instance_id', 'reference']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('eshop_import_orders');
    }
};
