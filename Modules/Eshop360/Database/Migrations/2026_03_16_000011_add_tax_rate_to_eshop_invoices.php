<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('eshop_invoices', function (Blueprint $table) {
            if (!Schema::hasColumn('eshop_invoices', 'tax_rate')) {
                $table->decimal('tax_rate', 5, 2)->default(0)->after('total');
            }
        });
    }

    public function down(): void
    {
        Schema::table('eshop_invoices', function (Blueprint $table) {
            if (Schema::hasColumn('eshop_invoices', 'tax_rate')) {
                $table->dropColumn('tax_rate');
            }
        });
    }
};
