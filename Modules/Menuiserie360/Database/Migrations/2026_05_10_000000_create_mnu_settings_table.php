<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * P0-7 : table de configuration Menuiserie360 par instance.
 *
 * Stocke les surcharges instance des valeurs par défaut définies dans
 * `Modules/Menuiserie360/Config/config.php` (TVA, préfixes de numérotation,
 * etc.). Schéma key/value JSON pour rester souple — toute config future est
 * additive sans nouvelle migration.
 *
 * Préfixe `mnu_` garanti (cohérence spec §5.3 — pas de collision avec
 * `eshop_*` ou tables Core).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('mnu_settings')) {
            return;
        }

        Schema::create('mnu_settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('instance_id');
            $table->string('group', 50);
            $table->json('data');
            $table->timestamps();

            $table->unique(['instance_id', 'group'], 'mnu_settings_instance_group_unique');
            $table->index('instance_id', 'mnu_settings_instance_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mnu_settings');
    }
};
