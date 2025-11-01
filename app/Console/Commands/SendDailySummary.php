<?php

namespace App\Console\Commands;

use App\Services\EmailTransactionNotificationService;
use App\Services\EmailTransactionLoggerService;
use Illuminate\Console\Command;
use Exception;

class SendDailySummary extends Command
{
    protected $signature = 'email-transactions:send-daily-summary';
    protected $description = 'Send daily summary report to administrators';

    private EmailTransactionNotificationService $notificationService;
    private EmailTransactionLoggerService $logger;

    public function __construct(
        EmailTransactionNotificationService $notificationService,
        EmailTransactionLoggerService $logger
    ) {
        parent::__construct();
        $this->notificationService = $notificationService;
        $this->logger = $logger;
    }

    public function handle(): int
    {
        try {
            $this->info('Sending daily summary report...');
            
            $this->notificationService->sendDailySummary();
            
            $this->info('Daily summary report sent successfully.');
            return 0;
            
        } catch (Exception $e) {
            $this->error('Failed to send daily summary: ' . $e->getMessage());
            $this->logger->logCriticalError('SendDailySummary', $e);
            return 1;
        }
    }
}