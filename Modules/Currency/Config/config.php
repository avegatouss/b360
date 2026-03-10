<?php

return [
    'name' => 'Currency',

    // Supported currencies
    'supported' => [
        'EUR' => ['name' => 'Euro', 'symbol' => "\u{20AC}", 'decimals' => 2],
        'USD' => ['name' => 'Dollar US', 'symbol' => '$', 'decimals' => 2],
        'GBP' => ['name' => 'Livre Sterling', 'symbol' => "\u{00A3}", 'decimals' => 2],
        'XOF' => ['name' => 'Franc CFA (BCEAO)', 'symbol' => 'CFA', 'decimals' => 0],
        'XAF' => ['name' => 'Franc CFA (BEAC)', 'symbol' => 'FCFA', 'decimals' => 0],
        'MAD' => ['name' => 'Dirham marocain', 'symbol' => 'MAD', 'decimals' => 2],
        'TND' => ['name' => 'Dinar tunisien', 'symbol' => 'TND', 'decimals' => 3],
        'CAD' => ['name' => 'Dollar canadien', 'symbol' => 'CA$', 'decimals' => 2],
        'CHF' => ['name' => 'Franc suisse', 'symbol' => 'CHF', 'decimals' => 2],
    ],

    // Default exchange rates (base: EUR)
    // These are fallback rates; actual rates can be stored in settings
    'rates' => [
        'EUR' => 1.0,
        'USD' => 1.08,
        'GBP' => 0.86,
        'XOF' => 655.957,
        'XAF' => 655.957,
        'MAD' => 10.85,
        'TND' => 3.38,
        'CAD' => 1.47,
        'CHF' => 0.96,
    ],
];
