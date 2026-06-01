<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('eshop_channel_credits', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('instance_id');
            $table->foreignId('channel_id')
                ->constrained('eshop_distribution_channels')
                ->cascadeOnDelete();
            $table->decimal('amount', 15, 2);
            $table->decimal('used_amount', 15, 2)->default(0);
            $table->enum('type', ['purchase', 'debt_coverage', 'gift', 'adjustment']);
            $table->enum('status', ['active', 'exhausted', 'cancelled'])->default('active');
            $table->text('notes')->nullable();
            $table->date('expires_at')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->index(['instance_id', 'channel_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('eshop_channel_credits');
    }
};
