<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('eshop_gift_card_topups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('gift_card_id')->constrained('eshop_gift_cards')->cascadeOnDelete();
            $table->decimal('amount', 15, 2);
            $table->string('notes')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('eshop_gift_card_topups');
    }
};
