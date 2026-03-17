<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('eshop_sms_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('instance_id')->index();
            $table->foreignId('gateway_id')->nullable()->constrained('eshop_sms_gateways')->nullOnDelete();
            $table->string('to');
            $table->text('message');
            $table->enum('status', ['pending', 'sent', 'failed'])->default('pending');
            $table->text('error')->nullable();
            $table->datetime('sent_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('eshop_sms_logs');
    }
};
