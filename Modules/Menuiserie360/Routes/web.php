<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Routes Menuiserie360 (L4)
|--------------------------------------------------------------------------
|
| Toutes les routes du module sont scopées sous `/i/{slug}/menuiserie/`
| via le groupe instance B360 + le préfixe `menuiserie/` propre au module.
|
| Stack middleware héritée (cf. spec v1.3 §5.1) :
|   - `auth`
|   - `instance` (BindInstanceFromRoute)
|   - `instance.membership` (EnsureInstanceMembershipActive)
|   - `spatie.team` (SetSpatieTeamContextFromInstance)
|   - `EnsureMenuiserieFeature::class` — feature gating Billing (à coder P0-5)
|
| P0 (squelette) : aucune route métier. Les routes apparaissent en P2..P3
| au fil des Controllers de chaque BC.
*/

Route::middleware([
    'auth',
    'instance',
    'instance.membership',
    'spatie.team',
])
    ->prefix('menuiserie')
    ->name('menuiserie.')
    ->group(function () {
        // P0 — placeholder. Les routes métier (devis, BC, chantiers, etc.)
        // sont introduites par phases au fil de P2..P3.
    });
