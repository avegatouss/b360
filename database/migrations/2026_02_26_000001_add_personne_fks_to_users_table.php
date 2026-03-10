<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('personne_id')->nullable()->after('settings')
                ->constrained('personnes')->nullOnDelete();

            $table->foreignId('personne_physique_id')->nullable()->after('personne_id')
                ->constrained('personne_physiques')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('personne_physique_id');
            $table->dropConstrainedForeignId('personne_id');
        });
    }
};
