<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('eshop_orders', function (Blueprint $table) {
            $table->unique(['instance_id', 'order_number'], 'eshop_orders_instance_order_number_unique');
        });

        Schema::table('eshop_invoices', function (Blueprint $table) {
            $table->unique(['instance_id', 'invoice_number'], 'eshop_invoices_instance_invoice_number_unique');
        });
    }

    public function down(): void
    {
        Schema::table('eshop_orders', function (Blueprint $table) {
            $table->dropUnique('eshop_orders_instance_order_number_unique');
        });

        Schema::table('eshop_invoices', function (Blueprint $table) {
            $table->dropUnique('eshop_invoices_instance_invoice_number_unique');
        });
    }
};
