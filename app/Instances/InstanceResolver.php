<?php

namespace App\Instances;

use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class InstanceResolver
{
    /**
     * Version SAFE : ne doit jamais casser l’app si DB/tables non prêtes.
     */
    public function resolveSafely(): ?Instance
    {
        // Si pas installé, aucune résolution
        if (config('app.installed', false) !== true) {
            return null;
        }

        // Test connexion DB (évite crash PDO)
        try {
            DB::connection()->getPdo();
        } catch (\Throwable) {
            return null;
        }

        // Vérifie existence table instances avant tout Eloquent
        try {
            if (!Schema::hasTable('instances')) {
                return null;
            }
        } catch (\Throwable) {
            return null;
        }

        // Requête Eloquent protégée
        try {
            return Instance::where('is_active', true)->orderBy('id')->first();
        } catch (QueryException) {
            return null;
        }
    }
}
