<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::connection('system')->create('settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('instance_id')->default(0);
            $table->string('group', 100)->default('general');
            $table->string('key', 255);
            $table->text('value')->nullable();
            $table->string('type', 20)->default('string');
            $table->timestamps();

            $table->unique(['instance_id', 'group', 'key']);
            $table->index(['group', 'key']);
        });
    }

    public function down(): void
    {
        Schema::connection('system')->dropIfExists('settings');
    }
};
