<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('eshop_pricing_rules', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 100)->unique();
            $table->string('name', 255);
            $table->string('class_name', 255);
            $table->unsignedTinyInteger('priority')->default(50);
            $table->enum('pipeline', ['retail', 'channel', 'wholesale', 'all'])->default('all');
            $table->boolean('is_active')->default(true);
            $table->boolean('is_core')->default(false);
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('eshop_pricing_rule_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rule_id')
                ->constrained('eshop_pricing_rules')
                ->cascadeOnDelete();
            $table->unsignedSmallInteger('version')->default(1);
            $table->json('configuration')->nullable();
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamp('created_at')->nullable();

            $table->unique(['rule_id', 'version']);
        });

        Schema::create('eshop_pricing_rule_configs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rule_id')
                ->constrained('eshop_pricing_rules')
                ->cascadeOnDelete();
            $table->unsignedBigInteger('instance_id');
            $table->json('config')->nullable();
            $table->boolean('is_active')->default(true);

            $table->unique(['rule_id', 'instance_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('eshop_pricing_rule_configs');
        Schema::dropIfExists('eshop_pricing_rule_versions');
        Schema::dropIfExists('eshop_pricing_rules');
    }
};
