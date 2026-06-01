<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('eshop_orders', function (Blueprint $table) {
            $table->foreignId('channel_id')->nullable()->after('holding_id')
                ->constrained('eshop_distribution_channels')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('eshop_orders', function (Blueprint $table) {
            $table->dropForeign(['channel_id']);
            $table->dropColumn('channel_id');
        });
    }
};
