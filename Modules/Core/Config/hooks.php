<?php

return [
    /**
     * Hook providers list (loaded by CoreServiceProvider).
     * Each provider must implement RegistersHooks or provide registerHooks()/moduleName() methods.
     * HookManager will auto-skip providers whose module is disabled.
     */
    'providers' => [
        \Modules\Dashboard\Providers\DashboardHooksProvider::class,
        \Modules\Users\Providers\UsersHooksProvider::class,
        \Modules\Instances\Providers\InstancesHooksProvider::class,
        \Modules\ModuleManager\Providers\ModuleManagerHooksProvider::class,
        \Modules\Settings\Providers\SettingsHooksProvider::class,
        \Modules\Billing\Providers\BillingHooksProvider::class,
        \Modules\Lang\Providers\LangHooksProvider::class,
        \Modules\Currency\Providers\CurrencyHooksProvider::class,
    ],
];
