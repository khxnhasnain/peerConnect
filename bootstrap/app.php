<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // CORS handled by config/cors.php
        $middleware->trimStrings(except: [
            'signal.sdp',
        ]);

        $middleware->redirectGuestsTo(function () {
            if (request()->is('meeting/*') || str_contains(request()->url(), '/meeting/')) {
                session(['intended_meeting_url' => request()->fullUrl()]);
            }
            return route('login');
        });
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })
    ->create();
