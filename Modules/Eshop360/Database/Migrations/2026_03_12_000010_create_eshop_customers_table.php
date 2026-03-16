<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('eshop_customers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('instance_id')->index();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('code');
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->text('address')->nullable();
            $table->string('city')->nullable();
            $table->string('country')->nullable();
            $table->integer('loyalty_points')->default(0);
            $table->integer('bonus_points')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique(['instance_id', 'code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('eshop_customers');
    }
};
