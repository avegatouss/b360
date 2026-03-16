<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('eshop_distribution_channels', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('instance_id')->index();
            $table->string('name');              // e.g. "CODIFARM", "Wholesale B", etc.
            $table->string('slug')->index();     // e.g. "codifarm", "wholesale-b"
            $table->string('code')->nullable();  // short code for references
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);

            // Pricing: markup applied on PGHT to get channel sale price
            $table->decimal('margin_rate', 5, 4)->default(0.13);    // PGHT = provisional × (1 + margin_rate)
            $table->decimal('buy_rate', 5, 4)->default(0.20);       // channel_price = PGHT × (1 + buy_rate)

            // Tripartite margin distribution shares (must sum to 1)
            $table->decimal('debt_share', 5, 4)->default(0.3333);
            $table->decimal('channel_share', 5, 4)->default(0.3333); // part going to the channel
            $table->decimal('owner_share', 5, 4)->default(0.3334);   // part going to the parent company

            $table->json('settings')->nullable(); // extra config per channel
            $table->timestamps();

            $table->unique(['instance_id', 'slug']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('eshop_distribution_channels');
    }
};
