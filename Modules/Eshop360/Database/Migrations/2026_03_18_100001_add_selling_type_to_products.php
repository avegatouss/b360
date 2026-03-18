<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('eshop_products', function (Blueprint $table) {
            $table->string('selling_type', 20)->default('both')->after('is_active');
            // Values: 'pos', 'online', 'both'
        });
    }

    public function down(): void
    {
        Schema::table('eshop_products', function (Blueprint $table) {
            $table->dropColumn('selling_type');
        });
    }
};
