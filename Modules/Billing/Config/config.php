<?php

return [
    'name' => 'Billing',

    'currency' => 'XOF',

    'invoice_prefix' => 'B360-INV',

    'default_trial_days' => 14,

    'auto_expire' => true,

    /*
    |--------------------------------------------------------------------------
    | Payment Gateways
    |--------------------------------------------------------------------------
    | Default configuration for payment gateways.
    | Instance-specific credentials are stored in the Settings table.
    */
    'gateways' => [
        'webhook_log_retention_days' => 90,
    ],
];
