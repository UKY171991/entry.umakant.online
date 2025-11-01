<?php

namespace App\Http\Middleware;

use App\Exceptions\EmailTransactionException;
use App\Services\EmailTransactionLoggerService;
use App\Services\EmailTransactionNotificationService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Exception;

class EmailTransactionErrorHandler
{
    private EmailTransactionLoggerService $logger;
    private EmailTransactionNotificationService $notificationService;

    public function __construct(
        EmailTransactionLoggerService $logger,
        EmailTransactionNotificationService $notificationService
    ) {
        $this->logger = $logger;
        $this->notificationService = $notificationService;
    }

    public function handle(Request $request, Closure $next): Response
    {
        try {
            return $next($request);
        } catch (EmailTransactionException $e) {
            return $this->handleEmailTransactionException($request, $e);
        } catch (Exception $e) {
            return $this->handleGenericException($request, $e);
        }
    }

    private function handleEmailTransactionException(Request $request, EmailTransactionException $e): Response
    {
        // Log the error
        $this->logger->logCriticalError($e->getComponent(), $e, array_merge([
            'request_url' => $request->url(),
            'request_method' => $request->method(),
            'user_id' => auth()->id(),
        ], $e->getContext()));

        // Send notification if critical
        if ($e->shouldNotify()) {
            $this->notificationService->notifyCriticalError($e->getComponent(), $e, [
                'request_url' => $request->url(),
                'user_id' => auth()->id(),
            ]);
        }

        // Return appropriate response
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
    }

    private function handleGenericException(Request $request, Exception $e): Response
    {
        // Check if this is related to email transactions based on the route
        $isEmailTransactionRoute = str_contains($request->route()?->getName() ?? '', 'email') ||
                                  str_contains($request->path(), 'email') ||
                                  str_contains($request->path(), 'inbox');

        if ($isEmailTransactionRoute) {
            // Log as email transaction related error
            $this->logger->logCriticalError('Unknown', $e, [
                'request_url' => $request->url(),
                'request_method' => $request->method(),
                'user_id' => auth()->id(),
            ]);

            // Send notification for critical errors
            if ($e->getCode() >= 500 || str_contains(strtolower($e->getMessage()), 'database')) {
                $this->notificationService->notifyCriticalError('Unknown', $e, [
                    'request_url' => $request->url(),
                    'user_id' => auth()->id(),
                ]);
            }

            $userMessage = 'An error occurred while processing your request. Please try again or contact support if the problem persists.';

            if ($request->expectsJson()) {
                return response()->json([
                    'error' => true,
                    'message' => $userMessage,
                    'details' => config('app.debug') ? $e->getMessage() : null
                ], 500);
            }

            return back()->withErrors([
                'system_error' => $userMessage
            ])->withInput();
        }

        // Re-throw if not email transaction related
        throw $e;
    }
}