<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('eshop_recurring_invoices', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('instance_id')->index();
            $table->foreignId('customer_id')->constrained('eshop_customers')->cascadeOnDelete();
            $table->foreignId('template_invoice_id')->constrained('eshop_invoices')->cascadeOnDelete();
            $table->enum('frequency', ['weekly', 'biweekly', 'monthly', 'quarterly', 'yearly'])->default('monthly');
            $table->date('next_due_date');
            $table->datetime('last_generated_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('total_generated')->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('eshop_recurring_invoices');
    }
};
