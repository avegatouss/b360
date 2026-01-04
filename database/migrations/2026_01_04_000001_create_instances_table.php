<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('instances', function (Blueprint $table) {
            $table->id();

            $table->string('name');
            $table->string('slug')->unique();

            // v1: résolution par domain
            $table->string('domain')->nullable()->unique();
            $table->string('subdomain')->nullable()->unique(); // ex: "v1.acme.com"
            $table->string('path')->nullable()->unique();    // ex: "acme.com/acme"

            // database-per-instance
            $table->string('database')->nullable();
            $table->string('db_driver')->nullable(); // mysql/pgsql/sqlsrv

            $table->boolean('is_active')->default(true)->index();
            $table->timestamp('installed_at')->nullable();

            $table->json('meta')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('instances');
    }
};
