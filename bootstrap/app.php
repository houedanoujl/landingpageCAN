<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Middleware global de sécurité
        $middleware->append(\App\Http\Middleware\SecurityHeadersMiddleware::class);

        // Middleware pour rafraîchir les points de l'utilisateur
        $middleware->append(\App\Http\Middleware\RefreshUserPoints::class);

        // Middleware pour attribuer les points quotidiens (fonctionne pour tous les utilisateurs connectés)
        $middleware->append(\App\Http\Middleware\DailyRewardMiddleware::class);

        $middleware->alias([
            'check.admin' => \App\Http\Middleware\CheckAdmin::class,
            'daily.reward' => \App\Http\Middleware\DailyRewardMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
