<?php

return [
    'enabled' => env('RECAPTCHA_ENABLED', false),
    'site_key' => env('RECAPTCHA_SITE_KEY', ''),
    'secret_key' => env('RECAPTCHA_SECRET_KEY', ''),
    'threshold' => env('RECAPTCHA_THRESHOLD', 0.5),
];
