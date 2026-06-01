<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'system';

    public function up(): void
    {
        Schema::connection($this->connection)->table('instances', function (Blueprint $table) {
            $table->boolean('is_maintenance')->default(false)->after('is_active');
            $table->text('maintenance_message')->nullable()->after('is_maintenance');
            $table->json('maintenance_allowed_ips')->nullable()->after('maintenance_message');
        });
    }

    public function down(): void
    {
        Schema::connection($this->connection)->table('instances', function (Blueprint $table) {
            $table->dropColumn(['is_maintenance', 'maintenance_message', 'maintenance_allowed_ips']);
        });
    }
};
