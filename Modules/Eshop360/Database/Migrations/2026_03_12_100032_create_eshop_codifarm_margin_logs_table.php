<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('eshop_codifarm_margin_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('instance_id')->index();
            $table->foreignId('order_id')->nullable()->constrained('eshop_orders')->nullOnDelete();
            $table->decimal('total_margin', 15, 2);
            $table->decimal('debt_part', 15, 2);
            $table->decimal('codifarm_part', 15, 2);
            $table->decimal('saphir_part', 15, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('eshop_codifarm_margin_logs');
    }
};
