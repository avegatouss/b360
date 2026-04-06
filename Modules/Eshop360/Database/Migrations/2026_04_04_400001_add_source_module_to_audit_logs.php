<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Add source_module to Core audit_logs table so Eshop360 can write there
 * instead of maintaining a separate eshop_audit_logs table.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('audit_logs') && !Schema::hasColumn('audit_logs', 'source_module')) {
            Schema::table('audit_logs', function (Blueprint $table) {
                $table->string('source_module', 50)->nullable()->after('instance_id');
                $table->index('source_module');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('audit_logs') && Schema::hasColumn('audit_logs', 'source_module')) {
            Schema::table('audit_logs', function (Blueprint $table) {
                $table->dropIndex(['source_module']);
                $table->dropColumn('source_module');
            });
        }
    }
};
