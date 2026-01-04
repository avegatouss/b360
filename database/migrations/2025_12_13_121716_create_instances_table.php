<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void
    {
        Schema::create('instances', function (Blueprint $table) {
            $table->id();

            /*
            |--------------------------------------------------------------------------
            | Identity
            |--------------------------------------------------------------------------
            */
            $table->string('name');
            $table->string('slug')->unique();

            /*
            |--------------------------------------------------------------------------
            | Database (Multi DB uniquement)
            |--------------------------------------------------------------------------
            */
            $table->string('database')->nullable();

            /*
            |--------------------------------------------------------------------------
            | Status
            |--------------------------------------------------------------------------
            */
            $table->boolean('is_active')->default(true)->index();

            /*
            |--------------------------------------------------------------------------
            | Meta (config libre par instance)
            |--------------------------------------------------------------------------
            */
            $table->json('meta')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('instances');
    }
};
