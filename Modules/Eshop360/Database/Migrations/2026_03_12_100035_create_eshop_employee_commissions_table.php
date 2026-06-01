<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('eshop_employee_commissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('eshop_employees')->cascadeOnDelete();
            $table->foreignId('order_id')->constrained('eshop_orders');
            $table->decimal('rate', 5, 2);
            $table->decimal('amount', 15, 2);
            $table->date('paid_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('eshop_employee_commissions');
    }
};
