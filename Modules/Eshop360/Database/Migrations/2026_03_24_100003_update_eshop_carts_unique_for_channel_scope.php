<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('eshop_carts', function (Blueprint $table) {
            $table->dropUnique('eshop_carts_instance_id_user_id_unique');
            $table->unique(['instance_id', 'user_id', 'channel_id'], 'eshop_carts_instance_user_channel_unique');
        });
    }

    public function down(): void
    {
        Schema::table('eshop_carts', function (Blueprint $table) {
            $table->dropUnique('eshop_carts_instance_user_channel_unique');
            $table->unique(['instance_id', 'user_id'], 'eshop_carts_instance_id_user_id_unique');
        });
    }
};
