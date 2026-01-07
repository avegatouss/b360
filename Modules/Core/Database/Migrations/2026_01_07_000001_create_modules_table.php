<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::connection('system')->create('modules', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->unique(); // ex: 'POS'
            $table->boolean('is_enabled')->default(false);
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index(['is_enabled', 'name']);
        });
    }

    public function down(): void
    {
        Schema::connection('system')->dropIfExists('modules');
    }
};
