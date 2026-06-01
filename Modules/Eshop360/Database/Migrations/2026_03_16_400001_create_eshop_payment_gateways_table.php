<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('eshop_payment_gateways', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('instance_id')->index();
            $table->string('driver', 50);
            $table->string('display_name', 100);
            $table->text('config')->nullable()->comment('Encrypted JSON');
            $table->boolean('is_active')->default(false);
            $table->boolean('is_test_mode')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['instance_id', 'driver']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('eshop_payment_gateways');
    }
};
