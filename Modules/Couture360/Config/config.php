<?php

return [
    'name' => 'Couture360',
    'table_prefix' => 'cout_',
    'api' => [
        'prefix' => 'api/couture',
        'throttle' => '120,1',
        'token_ttl_days' => 90,
    ],
];
