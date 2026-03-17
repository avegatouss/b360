<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('eshop_email_templates', function (Blueprint $table) {
            // Add variables JSON column after body
            $table->json('variables')->nullable()->after('body');

            // Add unique constraint on instance_id + name
            $table->unique(['instance_id', 'name'], 'eshop_email_tpl_instance_name_unique');
        });

        // Change the type column from enum of template names to enum of categories
        DB::statement("ALTER TABLE eshop_email_templates MODIFY COLUMN `type` ENUM('transactional','marketing','system') NOT NULL DEFAULT 'transactional'");
    }

    public function down(): void
    {
        Schema::table('eshop_email_templates', function (Blueprint $table) {
            $table->dropUnique('eshop_email_tpl_instance_name_unique');
            $table->dropColumn('variables');
        });

        DB::statement("ALTER TABLE eshop_email_templates MODIFY COLUMN `type` ENUM('invoice','password_reset','product_list','report','birthday','order_status','welcome') NOT NULL");
    }
};
