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
    |--------------------------------------------------------------------------
    | Morph short-keys article (ref_article_links.linkable_type) — Lot 2
    |--------------------------------------------------------------------------
    | Univers articles disjoints : aucune dédup cross-module (matching
    | lien-only). 1 row source = 1 golden record article.
    */
    'article_link_types' => [
        'mnu.catalog_item',
        'mnu.matiere',
        'eshop.product',
    ],

    /*
    | Pays par défaut (ISO-2) lorsque la valeur source est absente / non mappable.
    */
    'default_country' => 'CI',
];
