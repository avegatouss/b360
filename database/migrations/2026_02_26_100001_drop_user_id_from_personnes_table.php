<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Migrer les associations existantes vers users.personne_id
        // avant de supprimer la colonne redondante
        if (Schema::hasColumn('personnes', 'user_id') && Schema::hasColumn('users', 'personne_id')) {
            if (DB::getDriverName() === 'sqlite') {
                DB::statement('
                    UPDATE users
                    SET personne_id = (SELECT p.id FROM personnes p WHERE p.user_id = users.id)
                    WHERE personne_id IS NULL
                      AND EXISTS (SELECT 1 FROM personnes p WHERE p.user_id = users.id)
                ');
            } else {
                DB::statement('
                    UPDATE users u
                    INNER JOIN personnes p ON p.user_id = u.id
                    SET u.personne_id = p.id
                    WHERE u.personne_id IS NULL
                ');
            }
        }

        Schema::table('personnes', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropIndex(['user_id']);
            $table->dropColumn('user_id');
        });
    }

    public function down(): void
    {
        Schema::table('personnes', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('origine_donnees')
                ->constrained()->nullOnDelete();
            $table->index('user_id');
        });
    }
};
