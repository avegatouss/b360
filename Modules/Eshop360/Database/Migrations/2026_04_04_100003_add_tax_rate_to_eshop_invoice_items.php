<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('eshop_invoice_items', function (Blueprint $table) {
            $table->decimal('tax_rate', 8, 4)->nullable()->after('tax');
        });
    }

    public function down(): void
    {
        Schema::table('eshop_invoice_items', function (Blueprint $table) {
            $table->dropColumn('tax_rate');
        });
    }
};
