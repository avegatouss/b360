<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Php82\Rector\Class_\ReadOnlyClassRector;

return RectorConfig::configure()
    ->withPaths([
        __DIR__.'/Modules',
        __DIR__.'/app',
    ])
    ->withSkip([
        __DIR__.'/vendor',
        __DIR__.'/storage',
        __DIR__.'/bootstrap/cache',
        __DIR__.'/Modules/*/Database/Migrations',
        __DIR__.'/Modules/*/Database/Seeders',
        __DIR__.'/Modules/*/Database/Factories',

        // Skip rules trop agressives sur le legacy
        ReadOnlyClassRector::class,
    ])
    ->withPhpSets(php82: true)
    ->withPreparedSets(
        deadCode: true,
        codeQuality: true,
        typeDeclarations: true,
        privatization: false,    // trop intrusif sur Eloquent
        naming: false,           // peut casser les conventions Laravel
        instanceOf: true,
        earlyReturn: true,
        strictBooleans: false,
    )
    ->withImportNames(
        importShortClasses: false,
        removeUnusedImports: true,
    )
    ->withParallel(
        timeoutSeconds: 120,
        maxNumberOfProcess: 4,
    );
