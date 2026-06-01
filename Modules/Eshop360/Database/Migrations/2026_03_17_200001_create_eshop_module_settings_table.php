<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('eshop_module_settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('instance_id')->default(0);
            $table->string('group', 50)->index();
            $table->json('data');
            $table->timestamps();

            $table->unique(['instance_id', 'group']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('eshop_module_settings');
    }
};
