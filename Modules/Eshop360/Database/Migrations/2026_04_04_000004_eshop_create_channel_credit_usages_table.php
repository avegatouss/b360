<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('eshop_channel_credit_usages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('credit_id')
                ->constrained('eshop_channel_credits')
                ->cascadeOnDelete();
            $table->unsignedBigInteger('channel_id');
            $table->decimal('amount_used', 15, 2);
            $table->enum('purpose', ['purchase', 'debt_payment', 'other']);
            $table->string('reference', 100)->nullable();
            $table->timestamp('created_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('eshop_channel_credit_usages');
    }
};
