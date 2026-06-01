<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('eshop_products', function (Blueprint $table) {
            $table->decimal('purchase_price_factory', 15, 4)->nullable()->after('cost_price');
            $table->decimal('purchase_price_provisional', 15, 4)->nullable()->after('purchase_price_factory');
            $table->decimal('pght', 15, 4)->nullable()->after('purchase_price_provisional');
            $table->decimal('cost_price_real', 15, 4)->nullable()->after('pght');
            $table->decimal('sale_price_codifarm', 15, 4)->nullable()->after('cost_price_real');
            $table->integer('stock_alert_quantity')->default(10)->after('sale_price_codifarm');
            $table->integer('expiry_alert_days')->default(30)->after('stock_alert_quantity');
            $table->string('location')->nullable()->after('expiry_alert_days');
            $table->enum('barcode_type', ['code128', 'code39', 'ean13'])->default('code128')->after('location');
            $table->foreignId('supplier_id')->nullable()->after('barcode_type')->constrained('eshop_suppliers')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('eshop_products', function (Blueprint $table) {
            $table->dropForeign(['supplier_id']);
            $table->dropColumn([
                'purchase_price_factory',
                'purchase_price_provisional',
                'pght',
                'cost_price_real',
                'sale_price_codifarm',
                'stock_alert_quantity',
                'expiry_alert_days',
                'location',
                'barcode_type',
                'supplier_id',
            ]);
        });
    }
};
