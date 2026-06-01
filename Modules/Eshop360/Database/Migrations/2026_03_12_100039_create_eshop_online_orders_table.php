<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('eshop_online_orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('instance_id')->index();
            $table->foreignId('customer_id')->constrained('eshop_customers');
            $table->string('reference');
            $table->enum('status', [
                'pending_validation', 'validated', 'preparing', 'prepared',
                'shipping', 'delivered', 'received', 'invoiced', 'cancelled',
            ])->default('pending_validation');
            $table->decimal('subtotal', 15, 2);
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('total', 15, 2);
            $table->text('delivery_address')->nullable();
            $table->text('delivery_notes')->nullable();
            $table->datetime('confirmed_at')->nullable();
            $table->datetime('delivered_at')->nullable();
            $table->datetime('received_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('eshop_online_orders');
    }
};
