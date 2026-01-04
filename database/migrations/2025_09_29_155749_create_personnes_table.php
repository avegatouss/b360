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
        Schema::create('personnes', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['physique', 'moral']);
            $table->string('identifiant')->nullable()->unique(); // identifiant interne
            $table->string('email')->nullable()->unique();
            $table->string('telephone')->nullable()->unique();
            $table->string('nationalite')->nullable();
            $table->string('pays')->nullable();
            $table->boolean('actif')->default(true);
            $table->string('origine_donnees')->nullable(); // manuel, import, api...
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->index('user_id');
            // Ajouter des index pour améliorer les performances
            $table->index('type');
            $table->index('actif');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('personnes');
    }
};
