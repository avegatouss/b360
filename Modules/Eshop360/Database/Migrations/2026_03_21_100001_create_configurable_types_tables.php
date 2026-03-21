<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // Import cost types (replaces enum)
        Schema::create('eshop_import_cost_types', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('instance_id')->index();
            $table->string('code', 50)->index();
            $table->string('label');
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            $table->unique(['instance_id', 'code']);
        });

        // Charge categories (replaces enum)
        Schema::create('eshop_charge_categories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('instance_id')->index();
            $table->string('code', 50)->index();
            $table->string('label');
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            $table->unique(['instance_id', 'code']);
        });

        // Convert import_costs.type from enum to string
        DB::statement("ALTER TABLE eshop_import_costs MODIFY COLUMN type VARCHAR(50) NOT NULL");

        // Convert company_charges.category from enum to string
        DB::statement("ALTER TABLE eshop_company_charges MODIFY COLUMN category VARCHAR(50) NOT NULL");
    }

    public function down(): void
    {
        Schema::dropIfExists('eshop_charge_categories');
        Schema::dropIfExists('eshop_import_cost_types');
    }
};
