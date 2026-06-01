<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('eshop_tasks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('instance_id')->index();
            $table->unsignedBigInteger('project_id')->index();
            $table->unsignedBigInteger('parent_task_id')->nullable();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('status')->default('todo'); // todo, in_progress, review, done, cancelled
            $table->string('priority')->default('medium'); // low, medium, high, urgent
            $table->unsignedBigInteger('assigned_to')->nullable(); // user_id
            $table->unsignedBigInteger('created_by')->nullable();
            $table->datetime('start_date')->nullable();
            $table->datetime('due_date')->nullable();
            $table->datetime('completed_at')->nullable();
            $table->unsignedInteger('estimated_hours')->nullable();
            $table->unsignedInteger('actual_hours')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->json('labels')->nullable();
            $table->timestamps();

            $table->foreign('project_id')->references('id')->on('eshop_projects')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('eshop_tasks');
    }
};
