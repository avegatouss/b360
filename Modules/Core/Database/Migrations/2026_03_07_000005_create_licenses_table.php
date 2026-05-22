<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'system';

    public function up(): void
    {
        Schema::connection($this->connection)->create('licenses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('instance_id')->unique();
            $table->string('license_key', 64)->unique();
            $table->string('type', 50)->default('standard');
            $table->string('status', 20)->default('active'); // active, suspended, revoked, expired
            $table->dateTime('issued_at');
            $table->dateTime('expires_at')->nullable();
            $table->dateTime('last_verified_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::connection($this->connection)->dropIfExists('licenses');
    }
};
