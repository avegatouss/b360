<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('translations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('instance_id')->default(0)->index();
            $table->string('locale', 10)->index();
            $table->string('group', 100)->index(); // e.g. 'eshop360::eshop', 'lang::common'
            $table->string('key', 255);
            $table->text('value')->nullable();
            $table->timestamps();
            $table->unique(['instance_id', 'locale', 'group', 'key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('translations');
    }
};
