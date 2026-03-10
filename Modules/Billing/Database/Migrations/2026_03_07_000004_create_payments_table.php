<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'system';

    public function up(): void
    {
        Schema::connection($this->connection)->create('payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('invoice_id');
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3)->default('EUR');
            $table->string('method', 50); // manual, bank_transfer, card, stripe, paypal
            $table->string('status', 20)->default('pending'); // pending, completed, failed, refunded
            $table->string('reference')->nullable();
            $table->string('gateway_id')->nullable();
            $table->dateTime('paid_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index('invoice_id');
        });
    }

    public function down(): void
    {
        Schema::connection($this->connection)->dropIfExists('payments');
    }
};
