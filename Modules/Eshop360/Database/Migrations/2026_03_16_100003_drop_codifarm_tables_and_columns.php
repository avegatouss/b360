<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('eshop_orders', function (Blueprint $table) {
            $table->dropColumn('is_codifarm');
        });

        Schema::table('eshop_products', function (Blueprint $table) {
            $table->dropColumn('sale_price_codifarm');
        });

        Schema::dropIfExists('eshop_codifarm_margin_logs');
        Schema::dropIfExists('eshop_codifarm_margin_config');
    }

    public function down(): void
    {
        // Recreate codifarm_margin_config
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

        // Recreate codifarm_margin_logs
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

        // Re-add is_codifarm to orders
        Schema::table('eshop_orders', function (Blueprint $table) {
            $table->boolean('is_codifarm')->default(false)->after('holding_id');
        });

        // Re-add sale_price_codifarm to products
        Schema::table('eshop_products', function (Blueprint $table) {
            $table->decimal('sale_price_codifarm', 15, 4)->nullable()->after('cost_price_real');
        });
    }
};
