<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('eshop_carts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('instance_id');
            $table->unsignedBigInteger('user_id');
            $table->json('items')->nullable();
            $table->json('coupon')->nullable();
            $table->json('context')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->unique(['instance_id', 'user_id']);
            $table->index('expires_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('eshop_carts');
    }
};
