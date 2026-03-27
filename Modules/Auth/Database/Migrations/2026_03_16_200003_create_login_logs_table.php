<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('login_logs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('instance_id')->nullable();
            $table->string('ip_address', 45);
            $table->string('user_agent', 500)->nullable();
            $table->enum('status', ['success', 'failed', 'locked']);
            $table->timestamp('created_at')->nullable();

            $table->index(['user_id', 'created_at']);
            $table->index('instance_id');

            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('instance_id')->references('id')->on('instances')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('login_logs');
    }
};
