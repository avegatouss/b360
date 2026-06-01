<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('eshop_cash_registers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('instance_id')->index();
            $table->foreignId('store_id')->nullable()->constrained('eshop_stores')->nullOnDelete();
            $table->foreignId('user_id')->constrained('users');
            $table->decimal('opening_amount', 15, 2);
            $table->decimal('closing_amount', 15, 2)->nullable();
            $table->decimal('expected_amount', 15, 2)->nullable();
            $table->decimal('difference', 15, 2)->nullable();
            $table->enum('status', ['open', 'closed'])->default('open');
            $table->datetime('opened_at');
            $table->datetime('closed_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('eshop_cash_registers');
    }
};
