<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'system';

    public function up(): void
    {
        Schema::connection($this->connection)->create('billing_webhook_logs', function (Blueprint $table) {
            $table->id();
            $table->string('gateway_slug', 50)->index();
            $table->unsignedBigInteger('instance_id')->nullable()->index();
            $table->string('event_type', 100)->nullable();
            $table->json('payload');
            $table->json('result')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::connection($this->connection)->dropIfExists('billing_webhook_logs');
    }
};
