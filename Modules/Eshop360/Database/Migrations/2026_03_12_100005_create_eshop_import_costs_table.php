<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('eshop_import_costs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('import_order_id')->constrained('eshop_import_orders')->cascadeOnDelete();
            $table->enum('type', ['freight', 'customs', 'tax', 'admin', 'local_transport', 'handling', 'storage', 'other']);
            $table->string('description')->nullable();
            $table->decimal('amount', 15, 2);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('eshop_import_costs');
    }
};
