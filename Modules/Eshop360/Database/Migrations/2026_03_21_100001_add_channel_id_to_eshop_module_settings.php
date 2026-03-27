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

            // Replace (instance_id, group) unique with (instance_id, group, channel_id)
            // to allow per-channel settings for the same group
            $table->dropUnique('eshop_module_settings_instance_id_group_unique');
            $table->unique(['instance_id', 'group', 'channel_id'], 'eshop_module_settings_inst_group_channel_unique');
        });
    }

    public function down(): void
    {
        Schema::table('eshop_module_settings', function (Blueprint $table) {
            $table->dropUnique('eshop_module_settings_inst_group_channel_unique');
            $table->unique(['instance_id', 'group']);
            $table->dropForeign(['channel_id']);
            $table->dropColumn('channel_id');
        });
    }
};
