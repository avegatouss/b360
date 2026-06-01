<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('eshop_customer_dues', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('eshop_customers');
            $table->foreignId('order_id')->nullable()->constrained('eshop_orders')->nullOnDelete();
            $table->foreignId('invoice_id')->nullable()->constrained('eshop_invoices')->nullOnDelete();
            $table->decimal('amount_due', 15, 2);
            $table->date('due_date')->nullable();
            $table->enum('status', ['pending', 'partial', 'paid', 'overdue'])->default('pending');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('eshop_customer_dues');
    }
};
