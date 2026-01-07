<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void
    {
        Schema::create('instances', function (Blueprint $table) {
            $table->id();

            /*
            |--------------------------------------------------------------------------
            | Identity
            |--------------------------------------------------------------------------
            */
            $table->string('name');
            $table->string('slug')->unique();


            /*
            |--------------------------------------------------------------------------
            | Resolution (domain, subdomain, path)
            |--------------------------------------------------------------------------
            */
            $table->string('domain')->nullable()->index();
            $table->string('subdomain')->nullable()->index(); // ex: "v1.acme.com"
            $table->string('path')->nullable()->index();    // ex: "acme.com/acme"

            /*
            |--------------------------------------------------------------------------
            | Database (Multi DB uniquement)  database-per-instance
            |--------------------------------------------------------------------------
            */
            $table->string('database')->nullable();
            $table->string('db_driver')->nullable(); // mysql/pgsql/sqlsrv

            /*
            |--------------------------------------------------------------------------
            | Status
            |--------------------------------------------------------------------------
            */
            $table->boolean('is_active')->default(true)->index();
            $table->timestamp('installed_at')->nullable();

            /*
            |--------------------------------------------------------------------------
            | Meta (config libre par instance)
            |--------------------------------------------------------------------------
            */
            $table->json('meta')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('instances');
    }
};
