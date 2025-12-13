<?php

use Nwidart\Modules\Activators\FileActivator;
use Nwidart\Modules\Providers\ConsoleServiceProvider;

return [

    /*
    |--------------------------------------------------------------------------
    | Module Namespace
    |--------------------------------------------------------------------------
    */
    'namespace' => 'Modules',

    /*
    |--------------------------------------------------------------------------
    | Module Stubs
    |--------------------------------------------------------------------------
    | Activés pour garantir une structure homogène des modules B360
    */
    'stubs' => [
        'enabled' => true,
        'path' => base_path('vendor/nwidart/laravel-modules/src/Commands/stubs'),
        'files' => [
            'routes/web'      => 'routes/web.php',
            'routes/api'      => 'routes/api.php',
            'views/index'     => 'resources/views/index.blade.php',
            'views/master'    => 'resources/views/components/layouts/master.blade.php',
            'scaffold/config' => 'config/config.php',
            'composer'        => 'composer.json',
            'assets/js/app'   => 'resources/assets/js/app.js',
            'assets/sass/app' => 'resources/assets/sass/app.scss',
            'vite'            => 'vite.config.js',
            'package'         => 'package.json',
        ],
        'replacements' => [
            'routes/web'  => ['LOWER_NAME', 'STUDLY_NAME', 'KEBAB_NAME', 'MODULE_NAMESPACE', 'CONTROLLER_NAMESPACE'],
            'routes/api'  => ['LOWER_NAME', 'STUDLY_NAME', 'KEBAB_NAME', 'MODULE_NAMESPACE', 'CONTROLLER_NAMESPACE'],
            'vite'        => ['LOWER_NAME', 'STUDLY_NAME', 'KEBAB_NAME'],
            'json'        => ['LOWER_NAME', 'STUDLY_NAME', 'KEBAB_NAME', 'MODULE_NAMESPACE', 'PROVIDER_NAMESPACE'],
            'views/index' => ['LOWER_NAME'],
            'views/master'=> ['LOWER_NAME', 'STUDLY_NAME', 'KEBAB_NAME'],
            'scaffold/config' => ['STUDLY_NAME'],
            'composer' => [
                'LOWER_NAME',
                'STUDLY_NAME',
                'MODULE_NAMESPACE',
                'PROVIDER_NAMESPACE',
                'APP_FOLDER_NAME',
            ],
        ],
        'gitkeep' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Paths
    |--------------------------------------------------------------------------
    */
    'paths' => [
        'modules'   => base_path('Modules'),
        'assets'    => public_path('modules'),
        'migration' => base_path('database/migrations'),
        'app_folder'=> 'app/',

        'generator' => [

            // Core app
            'provider'       => ['path' => 'app/Providers', 'generate' => true],
            'route-provider' => ['path' => 'app/Providers', 'generate' => true],
            'controller'     => ['path' => 'app/Http/Controllers', 'generate' => true],

            // HTTP
            'filter'  => ['path' => 'app/Http/Middleware', 'generate' => false],
            'request' => ['path' => 'app/Http/Requests', 'generate' => false],

            // Database
            'migration' => ['path' => 'database/migrations', 'generate' => true],
            'seeder'    => ['path' => 'database/seeders', 'generate' => true],
            'factory'   => ['path' => 'database/factories', 'generate' => true],

            // Resources
            'views' => ['path' => 'resources/views', 'generate' => true],
            'lang'  => ['path' => 'lang', 'generate' => true],
            'assets'=> ['path' => 'resources/assets', 'generate' => true],

            // Routes
            'routes' => ['path' => 'routes', 'generate' => true],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Auto Discover
    |--------------------------------------------------------------------------
    */
    'auto-discover' => [
        'migrations'   => true,
        'translations' => false,
    ],

    /*
    |--------------------------------------------------------------------------
    | Commands
    |--------------------------------------------------------------------------
    */
    'commands' => ConsoleServiceProvider::defaultCommands()->toArray(),

    /*
    |--------------------------------------------------------------------------
    | Scan
    |--------------------------------------------------------------------------
    */
    'scan' => [
        'enabled' => false,
        'paths' => [],
    ],

    /*
    |--------------------------------------------------------------------------
    | Composer
    |--------------------------------------------------------------------------
    */
    'composer' => [
        'vendor' => 'b360',
        'author' => [
            'name'  => 'B360 Core',
            'email' => 'dev@b360.app',
        ],
        'composer-output' => false,
    ],

    /*
    |--------------------------------------------------------------------------
    | Register
    |--------------------------------------------------------------------------
    */
    'register' => [
        'translations' => true,
        'files' => 'register',
    ],

    /*
    |--------------------------------------------------------------------------
    | Activators
    |--------------------------------------------------------------------------
    */
    'activators' => [
        'file' => [
            'class' => FileActivator::class,
            'statuses-file' => base_path('modules_statuses.json'),
        ],
    ],

    'activator' => 'file',
];
