<?php

return [
    // Cache
    'cache' => [
        'enabled_modules_ttl_seconds' => 60,
    ],

    // Membership enforcement (post-install)
    // If true: a user must be ACTIVE member of current instance to access protected routes
    'enforce_membership' => true,

    // Safe-by-default when no instance resolved
    // 'block' => abort (503) unless route is explicitly allowed
    // 'root'  => fallback to root instance (NOT recommended for strict environments)
    'no_instance_strategy' => 'block', // 'block' | 'root'
];
