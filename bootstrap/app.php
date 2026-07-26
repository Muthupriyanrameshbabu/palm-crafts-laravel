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
        // Razorpay's servers POST here directly and cannot supply a Laravel CSRF
        // token. Safety is instead enforced by verifying the X-Razorpay-Signature
        // header inside RazorpayWebhookController — see that class for details.
        $middleware->validateCsrfTokens(except: [
            'webhooks/razorpay',
        ]);

        // OWASP: basic security headers on every response.
        $middleware->append(\App\Http\Middleware\SecurityHeaders::class);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
