<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('representants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('representant_id')->constrained('personne_physiques')->onDelete('cascade');
            $table->foreignId('personne_morale_id')->constrained('personne_morales')->onDelete('cascade');
            $table->boolean('est_principal')->default(false);
            $table->string('fonction')->nullable();
             $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('representants');
    }
};
