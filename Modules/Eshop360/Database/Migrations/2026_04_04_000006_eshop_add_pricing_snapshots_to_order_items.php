<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('eshop_order_items', function (Blueprint $table) {
            if (!Schema::hasColumn('eshop_order_items', 'pricing_snapshot')) {
                $table->json('pricing_snapshot')->nullable()->after('total');
            }
            if (!Schema::hasColumn('eshop_order_items', 'margin_snapshot')) {
                $table->json('margin_snapshot')->nullable()->after('pricing_snapshot');
            }
        });
    }

    public function down(): void
    {
        Schema::table('eshop_order_items', function (Blueprint $table) {
            $cols = ['pricing_snapshot', 'margin_snapshot'];
            foreach ($cols as $col) {
                if (Schema::hasColumn('eshop_order_items', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
