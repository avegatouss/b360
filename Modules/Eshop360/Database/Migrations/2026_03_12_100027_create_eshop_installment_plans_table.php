<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('eshop_installment_plans', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('instance_id')->index();
            $table->foreignId('order_id')->constrained('eshop_orders');
            $table->decimal('total', 15, 2);
            $table->integer('installments_count');
            $table->enum('frequency', ['weekly', 'biweekly', 'monthly']);
            $table->enum('status', ['active', 'completed', 'defaulted'])->default('active');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('eshop_installment_plans');
    }
};
