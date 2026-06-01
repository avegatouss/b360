<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * This table lives on the system (central) database.
     */
    public function getConnection(): ?string
    {
        return config('database.default');
    }

    public function up(): void
    {
        Schema::create('tenant_feature_overrides', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('instance_id');
            $table->string('feature', 150);
            $table->boolean('enabled')->default(true);
            $table->json('value')->nullable();
            $table->unsignedBigInteger('activated_by')->nullable();
            $table->timestamp('activated_at')->nullable();
            $table->timestamps();

            $table->unique(['instance_id', 'feature']);
            $table->index('instance_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenant_feature_overrides');
    }
};
