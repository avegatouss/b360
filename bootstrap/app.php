<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        api: __DIR__ . '/../routes/api.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->web(append: [
            \App\Http\Middleware\EnsureInstalled::class,
            \App\Instances\Middleware\InstanceMiddleware::class,
            \Modules\Lang\Http\Middleware\SetLocale::class,
            \Modules\Core\Http\Middleware\SecurityHeaders::class,
        ]);
         $middleware->api(append: [
            \App\Http\Middleware\EnsureInstalled::class,
            \App\Instances\Middleware\InstanceMiddleware::class,
            \Modules\Core\Http\Middleware\SecurityHeaders::class,
        ]);
        //$middleware->append();
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
