<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('eshop_codifarm_margin_config', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('instance_id')->index();
            $table->decimal('saphir_margin_rate', 5, 4)->default(0.13);
            $table->decimal('codifarm_buy_rate', 5, 4)->default(0.20);
            $table->decimal('debt_share', 5, 4)->default(0.3333);
            $table->decimal('codifarm_share', 5, 4)->default(0.3333);
            $table->decimal('saphir_share', 5, 4)->default(0.3334);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('eshop_codifarm_margin_config');
    }
};
