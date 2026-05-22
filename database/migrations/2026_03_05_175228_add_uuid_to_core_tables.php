<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration 
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Add nullable UUID columns first to avoid constraint violations on existing records
        Schema::table('users', function (Blueprint $table) {
            $table->uuid('uuid')->nullable()->after('id');
        });

        if (Schema::connection('system')->hasTable('instances')) {
            Schema::connection('system')->table('instances', function (Blueprint $table) {
                $table->uuid('uuid')->nullable()->after('id');
            });
        }

        Schema::table('personnes', function (Blueprint $table) {
            $table->uuid('uuid')->nullable()->after('id');
        });

        // 2. Populate existing records with UUIDs
        DB::table('users')->whereNull('uuid')->orderBy('id')->each(function ($user) {
            DB::table('users')->where('id', $user->id)->update(['uuid' => Str::uuid()]);
        });
        if (Schema::connection('system')->hasTable('instances') && Schema::connection('system')->hasColumn('instances', 'uuid')) {
            DB::connection('system')->table('instances')->whereNull('uuid')->orderBy('id')->each(function ($instance) {
                DB::connection('system')->table('instances')->where('id', $instance->id)->update(['uuid' => Str::uuid()]);
            });
        }
        DB::table('personnes')->whereNull('uuid')->orderBy('id')->each(function ($personne) {
            DB::table('personnes')->where('id', $personne->id)->update(['uuid' => Str::uuid()]);
        });

        // 3. Make them required and unique
        Schema::table('users', function (Blueprint $table) {
            $table->uuid('uuid')->nullable(false)->unique()->change();
        });
        if (Schema::connection('system')->hasTable('instances') && Schema::connection('system')->hasColumn('instances', 'uuid')) {
            Schema::connection('system')->table('instances', function (Blueprint $table) {
                $table->uuid('uuid')->nullable(false)->unique()->change();
            });
        }
        Schema::table('personnes', function (Blueprint $table) {
            $table->uuid('uuid')->nullable(false)->unique()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('core_tables', function (Blueprint $table) {
        //
        });
    }
};
