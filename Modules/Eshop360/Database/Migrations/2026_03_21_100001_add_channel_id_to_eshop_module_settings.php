<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('eshop_module_settings', function (Blueprint $table) {
            $table->unsignedBigInteger('channel_id')->nullable()->after('instance_id');
            $table->foreign('channel_id')->references('id')->on('eshop_distribution_channels')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('eshop_module_settings', function (Blueprint $table) {
            $table->dropForeign(['channel_id']);
            $table->dropColumn('channel_id');
        });
    }
};
