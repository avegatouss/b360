<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('eshop_company_charges', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('instance_id')->index();
            $table->enum('category', ['rent', 'electricity', 'salary', 'transport', 'maintenance', 'insurance', 'other']);
            $table->string('name');
            $table->decimal('amount_monthly', 15, 2);
            $table->boolean('is_active')->default(true);
            $table->date('start_date')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('eshop_company_charges');
    }
};
