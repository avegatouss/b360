<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('eshop_projects', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('instance_id')->index();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('status')->default('active'); // active, on_hold, completed, cancelled
            $table->string('priority')->default('medium'); // low, medium, high, urgent
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->date('actual_end_date')->nullable();
            $table->decimal('budget', 15, 2)->nullable();
            $table->decimal('spent', 15, 2)->default(0);
            $table->unsignedBigInteger('manager_id')->nullable(); // user_id
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->unsignedInteger('progress')->default(0); // 0-100
            $table->json('settings')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->foreign('customer_id')->references('id')->on('eshop_customers')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('eshop_projects');
    }
};
