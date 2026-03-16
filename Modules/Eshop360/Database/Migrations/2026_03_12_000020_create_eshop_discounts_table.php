<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('eshop_discounts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('instance_id')->index();
            $table->string('name');
            $table->enum('type', ['percentage', 'fixed'])->default('percentage');
            $table->decimal('value', 12, 2)->default(0);
            $table->enum('plan_type', ['standard', 'membership', 'premium', 'seasonal', 'student'])->default('standard');
            $table->date('valid_from')->nullable();
            $table->date('valid_until')->nullable();
            $table->json('active_days')->nullable();
            $table->enum('applies_to', ['all', 'specific'])->default('all');
            $table->json('product_ids')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('eshop_discounts');
    }
};
