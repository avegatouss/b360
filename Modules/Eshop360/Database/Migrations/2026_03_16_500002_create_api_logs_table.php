<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('eshop_api_logs', function (Blueprint $table) {
            $table->id();
            $table->string('method', 10);
            $table->string('endpoint', 500);
            $table->unsignedSmallInteger('response_code');
            $table->string('ip', 45)->nullable();
            $table->unsignedInteger('duration_ms')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('instance_id')->nullable();
            $table->timestamp('requested_at');
            $table->timestamps();

            $table->index(['endpoint', 'requested_at']);
            $table->index('user_id');
            $table->index('instance_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('eshop_api_logs');
    }
};
