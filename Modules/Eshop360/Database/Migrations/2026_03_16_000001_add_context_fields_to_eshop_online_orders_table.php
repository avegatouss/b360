<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('eshop_online_orders', function (Blueprint $table) {
            if (! Schema::hasColumn('eshop_online_orders', 'channel_id')) {
                $table->foreignId('channel_id')
                    ->nullable()
                    ->after('customer_id')
                    ->constrained('eshop_distribution_channels')
                    ->nullOnDelete();
            }

            if (! Schema::hasColumn('eshop_online_orders', 'is_codifarm')) {
                $table->boolean('is_codifarm')->default(false)->after('channel_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('eshop_online_orders', function (Blueprint $table) {
            if (Schema::hasColumn('eshop_online_orders', 'channel_id')) {
                $table->dropConstrainedForeignId('channel_id');
            }

            if (Schema::hasColumn('eshop_online_orders', 'is_codifarm')) {
                $table->dropColumn('is_codifarm');
            }
        });
    }
};
