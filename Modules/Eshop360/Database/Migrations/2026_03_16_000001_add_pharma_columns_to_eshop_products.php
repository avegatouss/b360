<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('eshop_products', function (Blueprint $table) {
            if (!Schema::hasColumn('eshop_products', 'wholesale_price')) {
                $table->decimal('wholesale_price', 15, 2)->nullable()->after('sale_price_codifarm');
            }
            if (!Schema::hasColumn('eshop_products', 'pharmacy_price')) {
                $table->decimal('pharmacy_price', 15, 2)->nullable()->after('wholesale_price');
            }
            if (!Schema::hasColumn('eshop_products', 'min_qty_wholesale')) {
                $table->unsignedInteger('min_qty_wholesale')->default(1)->after('pharmacy_price');
            }
            if (!Schema::hasColumn('eshop_products', 'dci')) {
                $table->string('dci')->nullable()->after('description');
            }
            if (!Schema::hasColumn('eshop_products', 'dosage')) {
                $table->string('dosage')->nullable()->after('dci');
            }
            if (!Schema::hasColumn('eshop_products', 'form')) {
                $table->string('form')->nullable()->after('dosage');
            }
            if (!Schema::hasColumn('eshop_products', 'packaging')) {
                $table->string('packaging')->nullable()->after('form');
            }
            if (!Schema::hasColumn('eshop_products', 'batch_number')) {
                $table->string('batch_number')->nullable()->after('expiry_date');
            }
        });
    }

    public function down(): void
    {
        Schema::table('eshop_products', function (Blueprint $table) {
            $cols = ['wholesale_price', 'pharmacy_price', 'min_qty_wholesale', 'dci', 'dosage', 'form', 'packaging', 'batch_number'];
            foreach ($cols as $col) {
                if (Schema::hasColumn('eshop_products', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
