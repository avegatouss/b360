<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'system';

    public function up(): void
    {
        Schema::connection('system')->table('plans', function (Blueprint $table) {
            $table->string('visibility', 20)->default('all')->after('is_active'); // all, specific
        });

        Schema::connection('system')->create('plan_instance', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plan_id')->constrained('plans')->cascadeOnDelete();
            $table->foreignId('instance_id')->constrained('instances')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['plan_id', 'instance_id']);
        });
    }

    public function down(): void
    {
        Schema::connection('system')->dropIfExists('plan_instance');

        Schema::connection('system')->table('plans', function (Blueprint $table) {
            $table->dropColumn('visibility');
        });
    }
};
