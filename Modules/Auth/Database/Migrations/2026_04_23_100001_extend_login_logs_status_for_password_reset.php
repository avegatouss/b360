<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * R-301 — Étend la colonne `login_logs.status` pour accepter
 * la valeur `password_reset`.
 *
 * L'enum historique (`success`, `failed`, `locked`) bloquait l'insertion
 * d'un log de réinitialisation de mot de passe (event orphelin R-301).
 * Cette migration convertit la colonne en VARCHAR(30) ce qui autorise
 * `password_reset` et facilite l'ajout futur de nouveaux statuts
 * (logout, session_expired, etc.) sans migration enum.
 *
 * Stratégie cross-driver :
 *   - SQLite : recréation de la table (seul moyen de modifier un
 *     CHECK constraint généré par ENUM).
 *   - MySQL / PostgreSQL : ALTER TABLE MODIFY COLUMN / TYPE VARCHAR(30).
 */
return new class extends Migration
{
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'sqlite') {
            // SQLite : rebuild table (seul moyen de modifier un CHECK).
            $this->sqliteRebuild();

            return;
        }

        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE login_logs MODIFY COLUMN status VARCHAR(30) NOT NULL');

            return;
        }

        if ($driver === 'pgsql') {
            DB::statement('ALTER TABLE login_logs ALTER COLUMN status TYPE VARCHAR(30)');

            return;
        }
    }

    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'sqlite') {
            // Restore the original enum CHECK via rebuild.
            $this->sqliteRestore();

            return;
        }

        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE login_logs MODIFY COLUMN status ENUM('success', 'failed', 'locked') NOT NULL");

            return;
        }

        if ($driver === 'pgsql') {
            DB::statement('ALTER TABLE login_logs ALTER COLUMN status TYPE VARCHAR(20)');

            return;
        }
    }

    private function sqliteRebuild(): void
    {
        // Create the new table with VARCHAR(30) instead of ENUM.
        Schema::create('login_logs_new', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('instance_id')->nullable();
            $table->string('ip_address', 45);
            $table->string('user_agent', 500)->nullable();
            $table->string('status', 30);
            $table->timestamp('created_at')->nullable();

            $table->index(['user_id', 'created_at']);
            $table->index('instance_id');

            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('instance_id')->references('id')->on('instances')->nullOnDelete();
        });

        DB::statement('INSERT INTO login_logs_new (id, user_id, instance_id, ip_address, user_agent, status, created_at) SELECT id, user_id, instance_id, ip_address, user_agent, status, created_at FROM login_logs');

        Schema::drop('login_logs');
        Schema::rename('login_logs_new', 'login_logs');
    }

    private function sqliteRestore(): void
    {
        // Rebuild with original ENUM-like CHECK (rejet de password_reset).
        Schema::create('login_logs_old', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('instance_id')->nullable();
            $table->string('ip_address', 45);
            $table->string('user_agent', 500)->nullable();
            $table->enum('status', ['success', 'failed', 'locked']);
            $table->timestamp('created_at')->nullable();

            $table->index(['user_id', 'created_at']);
            $table->index('instance_id');

            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('instance_id')->references('id')->on('instances')->nullOnDelete();
        });

        DB::statement("INSERT INTO login_logs_old (id, user_id, instance_id, ip_address, user_agent, status, created_at) SELECT id, user_id, instance_id, ip_address, user_agent, status, created_at FROM login_logs WHERE status IN ('success', 'failed', 'locked')");

        Schema::drop('login_logs');
        Schema::rename('login_logs_old', 'login_logs');
    }
};
