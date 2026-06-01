<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cron_logs', function (Blueprint $table) {
            $table->id();
            $table->string('command');
            $table->enum('status', ['success', 'failed']);
            $table->text('output')->nullable();
            $table->unsignedInteger('duration_ms')->nullable();
            $table->timestamp('executed_at');
            $table->timestamps();

            $table->index(['command', 'status']);
            $table->index('executed_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cron_logs');
    }
};
