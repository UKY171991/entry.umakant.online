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
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'email.transaction.error' => \App\Http\Middleware\EmailTransactionErrorHandler::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (\App\Exceptions\EmailTransactionException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'error' => true,
                    'message' => $e->getUserMessage(),
                    'details' => config('app.debug') ? $e->getMessage() : null
                ], $e->getCode() ?: 500);
            }

            return back()->withErrors([
                'email_transaction_error' => $e->getUserMessage()
            ])->withInput();
        });
    })->create();
