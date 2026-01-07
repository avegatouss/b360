<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::connection('system')->create('instance_user', function (Blueprint $table) {
            $table->unsignedBigInteger('instance_id');
            $table->unsignedBigInteger('user_id');
            $table->string('status', 20)->default('active'); // active|invited|disabled
            $table->timestamps();

            $table->primary(['instance_id', 'user_id']);
            $table->index(['user_id']);
            $table->index(['instance_id', 'status']);

            // If you already enforce FK on system DB:
            // $table->foreign('instance_id')->references('id')->on('instances')->cascadeOnDelete();
            // $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::connection('system')->dropIfExists('instance_user');
    }
};
