<?php

return [
    'enabled' => env('UI_SELECT2_ENABLED', true),

    'selectors' => [
        'include' => [
            'select',
        ],
        'exclude' => [
            'select.select2-hidden-accessible',
            'select[data-no-select2]',
            'select.no-select2',
            '.select2-container select',
            'template select',
        ],
    ],

    'default' => [
        'width' => '100%',
        'dropdownAutoWidth' => true,
        'closeOnSelect' => true,
    ],

    'search' => [
        'minimum_options_for_search' => 8,
    ],

    'placeholder' => [
        'prefer_blank_option' => true,
        'allow_clear_when_placeholder' => true,
    ],

    'inventory' => [
        'json_path' => 'docs/ui/selects-inventory.json',
        'markdown_path' => 'docs/ui/selects-inventory.md',
        'stub_path' => 'docs/ui/selects-overrides.stub.php',
    ],

    'overrides' => [
        'keys' => [
            'name.memberships.status' => [
                'minimumResultsForSearch' => -1,
            ],
            'name.memberships.role' => [
                'minimumResultsForSearch' => -1,
            ],
        ],
        'names' => [
            'memberships.status' => [
                'minimumResultsForSearch' => -1,
            ],
            'memberships.role' => [
                'minimumResultsForSearch' => -1,
            ],
        ],
        'ids' => [
        ],
    ],
];
