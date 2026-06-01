<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('eshop_distribution_channels', function (Blueprint $table) {
            $table->foreignId('warehouse_id')->nullable()->after('is_active')
                ->constrained('eshop_warehouses')->nullOnDelete();
            $table->boolean('portal_enabled')->default(false)->after('warehouse_id');
            $table->json('portal_settings')->nullable()->after('portal_enabled'); // logo, colors, etc.
        });

        Schema::create('eshop_channel_users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('channel_id')->constrained('eshop_distribution_channels')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->enum('role', ['member', 'manager', 'operator', 'viewer'])->default('viewer');
            $table->timestamps();

            $table->unique(['channel_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('eshop_channel_users');

        Schema::table('eshop_distribution_channels', function (Blueprint $table) {
            $table->dropForeign(['warehouse_id']);
            $table->dropColumn(['warehouse_id', 'portal_enabled', 'portal_settings']);
        });
    }
};
