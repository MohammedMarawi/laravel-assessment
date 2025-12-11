<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Exempt API routes from CSRF verification
        // This is required for Stripe webhooks to work properly
        $middleware->validateCsrfTokens(except: [
            'api/*', // All API routes are exempt (webhooks, etc.)
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
