<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('eshop_quotations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('instance_id')->index();
            $table->foreignId('customer_id')->nullable()->constrained('eshop_customers')->nullOnDelete();
            $table->string('quotation_number');
            $table->enum('status', ['draft', 'sent', 'pending', 'ordered', 'cancelled'])->default('draft');
            $table->decimal('total', 12, 2)->default(0);
            $table->text('notes')->nullable();
            $table->date('valid_until')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
            $table->unique(['instance_id', 'quotation_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('eshop_quotations');
    }
};
