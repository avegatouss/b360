<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('eshop_user_assignments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->enum('resource_type', ['store', 'warehouse', 'customer']);
            $table->unsignedBigInteger('resource_id');
            $table->unsignedBigInteger('instance_id');
            $table->timestamps();

            $table->unique(['user_id', 'resource_type', 'resource_id']);
            $table->index(['user_id', 'resource_type']);
            $table->index('instance_id');

            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('eshop_user_assignments');
    }
};
