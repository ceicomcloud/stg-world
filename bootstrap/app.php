<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->appendToGroup('web', \App\Http\Middleware\SchedulerTrigger::class);
        $middleware->appendToGroup('web', \App\Http\Middleware\UpdateLastActivity::class);
        $middleware->alias([
            'guest' => \App\Http\Middleware\GuestMiddleware::class,
            'game' => \App\Http\Middleware\GameMiddleware::class,
            'admin' => \App\Http\Middleware\AdminMiddleware::class,
            'game.vacation' => \App\Http\Middleware\GameVacationMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
