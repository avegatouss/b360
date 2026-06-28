<?php

return [
    'name' => 'Referentiel360',

    /*
    |--------------------------------------------------------------------------
    | Morph short-keys (ref_party_links.linkable_type)
    |--------------------------------------------------------------------------
    | Clés courtes stables et cohérentes avec l'existant (`mnu.invoice`).
    | ref_party_links stocke une string libre (pas un morphTo Eloquent
    | classique) ; ces clés ne sont là que pour la lisibilité / cohérence.
    */
    'link_types' => [
        'mnu.client',
        'mnu.supplier',
        'eshop.customer',
        'eshop.supplier',
    ],

    /*
    | Pays par défaut (ISO-2) lorsque la valeur source est absente / non mappable.
    */
    'default_country' => 'CI',
];
