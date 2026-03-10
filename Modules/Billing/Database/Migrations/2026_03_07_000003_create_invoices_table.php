<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'system';

    public function up(): void
    {
        Schema::connection($this->connection)->create('invoices', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('instance_id');
            $table->unsignedBigInteger('subscription_id')->nullable();
            $table->string('number', 50)->unique();
            $table->decimal('amount', 10, 2);
            $table->decimal('tax', 10, 2)->default(0);
            $table->decimal('total', 10, 2);
            $table->string('currency', 3)->default('EUR');
            $table->string('status', 20)->default('draft'); // draft, pending, paid, failed, refunded, cancelled
            $table->date('due_date');
            $table->dateTime('paid_at')->nullable();
            $table->string('billing_name')->nullable();
            $table->text('billing_address')->nullable();
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index('instance_id');
            $table->index(['status', 'due_date']);
        });
    }

    public function down(): void
    {
        Schema::connection($this->connection)->dropIfExists('invoices');
    }
};
