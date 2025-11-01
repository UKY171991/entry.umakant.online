<?php

namespace App\Providers;

use App\Contracts\EmailFetcherInterface;
use App\Contracts\EmailParserInterface;
use App\Contracts\TransactionProcessorInterface;
use App\Services\EmailFetcherService;
use App\Services\EmailParserService;
use App\Services\TransactionProcessorService;
use App\Services\EmailTransactionLoggerService;
use App\Services\EmailTransactionNotificationService;
use Illuminate\Support\ServiceProvider;

class EmailServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Bind interfaces to implementations
        $this->app->bind(EmailFetcherInterface::class, EmailFetcherService::class);
        $this->app->bind(EmailParserInterface::class, EmailParserService::class);
        $this->app->bind(TransactionProcessorInterface::class, TransactionProcessorService::class);
        
        // Register singleton services
        $this->app->singleton(EmailTransactionLoggerService::class);
        $this->app->singleton(EmailTransactionNotificationService::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}