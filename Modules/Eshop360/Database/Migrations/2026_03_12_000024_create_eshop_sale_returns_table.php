<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('eshop_sale_returns', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('instance_id')->index();
            $table->foreignId('order_id')->nullable()->constrained('eshop_orders')->nullOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained('eshop_customers')->nullOnDelete();
            $table->foreignId('product_id')->constrained('eshop_products')->cascadeOnDelete();
            $table->date('date');
            $table->enum('status', ['pending', 'received', 'cancelled'])->default('pending');
            $table->decimal('total', 12, 2)->default(0);
            $table->decimal('paid_amount', 12, 2)->default(0);
            $table->decimal('due_amount', 12, 2)->default(0);
            $table->enum('payment_status', ['unpaid', 'paid', 'overdue'])->default('unpaid');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('eshop_sale_returns');
    }
};
