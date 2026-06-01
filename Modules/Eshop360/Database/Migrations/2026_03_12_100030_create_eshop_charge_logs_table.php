<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('eshop_charge_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('charge_id')->constrained('eshop_company_charges')->cascadeOnDelete();
            $table->decimal('amount_per_second', 20, 10);
            $table->datetime('period_start');
            $table->datetime('period_end');
            $table->decimal('total_accumulated', 15, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('eshop_charge_logs');
    }
};
