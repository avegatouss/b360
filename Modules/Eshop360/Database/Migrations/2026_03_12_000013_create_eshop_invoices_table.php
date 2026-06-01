<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('eshop_invoices', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('instance_id')->index();
            $table->foreignId('order_id')->nullable()->constrained('eshop_orders')->nullOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained('eshop_customers')->nullOnDelete();
            $table->string('invoice_number');
            $table->enum('status', ['draft', 'sent', 'paid', 'unpaid', 'overdue', 'cancelled'])->default('draft');
            $table->date('due_date')->nullable();
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('tax_amount', 12, 2)->default(0);
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);
            $table->decimal('paid_amount', 12, 2)->default(0);
            $table->decimal('due_amount', 12, 2)->default(0);
            $table->text('notes')->nullable();
            $table->text('terms')->nullable();
            $table->text('footer_text')->nullable();
            $table->string('template')->default('default');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['instance_id', 'invoice_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('eshop_invoices');
    }
};
