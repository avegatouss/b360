<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('eshop_support_teams', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('instance_id')->index();
            $table->string('name');
            $table->string('description')->nullable();
            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('eshop_support_team_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained('eshop_support_teams')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['team_id', 'user_id']);
        });

        // Add support_team_id to customers
        Schema::table('eshop_customers', function (Blueprint $table) {
            $table->foreignId('support_team_id')->nullable()->after('store_id')->constrained('eshop_support_teams')->nullOnDelete();
        });

        // Add support_team_id to messages for team routing
        Schema::table('eshop_messages', function (Blueprint $table) {
            $table->foreignId('support_team_id')->nullable()->after('to_user_id')->constrained('eshop_support_teams')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('eshop_messages', function (Blueprint $table) {
            $table->dropConstrainedForeignId('support_team_id');
        });
        Schema::table('eshop_customers', function (Blueprint $table) {
            $table->dropConstrainedForeignId('support_team_id');
        });
        Schema::dropIfExists('eshop_support_team_members');
        Schema::dropIfExists('eshop_support_teams');
    }
};
