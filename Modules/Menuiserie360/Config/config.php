<?php

declare(strict_types=1);

return [
    'name' => 'Menuiserie360',

    /*
    |--------------------------------------------------------------------------
    | TVA par défaut (Côte d'Ivoire)
    |--------------------------------------------------------------------------
    | Configurable par instance via `mnu_settings`. Valeur par défaut = 18%
    | (taux normal CI). L'override par instance se fait par P1 via la table
    | `mnu_settings`.
    */
    'default_tax_rate' => 0.18,

    /*
    |--------------------------------------------------------------------------
    | Préfixes de numérotation
    |--------------------------------------------------------------------------
    | Surchargeables par instance via `mnu_settings`.
    */
    'numbering' => [
        'devis' => 'DEV-{YYYY}-{NNNN}',
        'bc' => 'BC-{YYYY}-{NNNN}',
        'invoice' => 'MNU-FAC-{YYYY}-{NNNN}',
        'of' => 'OF-{YYYY}-{NNNN}',
    ],
];
