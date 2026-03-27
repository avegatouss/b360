<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'system';

    public function up(): void
    {
        Schema::connection($this->connection)->create('payment_gateways', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 50)->unique();
            $table->string('name', 100);
            $table->boolean('is_active')->default(false);
            $table->json('supported_currencies')->nullable();
            $table->json('metadata')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::connection($this->connection)->dropIfExists('payment_gateways');
    }
};
