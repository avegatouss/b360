<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ip_rules', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('instance_id');
            $table->string('ip_address', 45);
            $table->enum('type', ['allow', 'deny']);
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('note', 255)->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->index(['instance_id', 'type']);
            $table->index('ip_address');

            $table->foreign('instance_id')->references('id')->on('instances')->cascadeOnDelete();
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ip_rules');
    }
};
