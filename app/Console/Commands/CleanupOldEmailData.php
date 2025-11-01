<?php

namespace App\Console\Commands;

use App\Models\EmailTransaction;
use App\Services\EmailTransactionLoggerService;
use Illuminate\Console\Command;
use Exception;

class CleanupOldEmailData extends Command
{
    protected $signature = 'email-transactions:cleanup-old-data {--dry-run : Show what would be deleted without actually deleting}';
    protected $description = 'Clean up old email transaction data based on retention policies';

    private EmailTransactionLoggerService $logger;

    public function __construct(EmailTransactionLoggerService $logger)
    {
        parent::__construct();
        $this->logger = $logger;
    }

    public function handle(): int
    {
        $isDryRun = $this->option('dry-run');
        
        try {
            $this->info('Starting email transaction data cleanup...');
            
            if ($isDryRun) {
                $this->warn('DRY RUN MODE - No data will actually be deleted');
            }

            $cleanupResults = $this->performCleanup($isDryRun);
            $this->displayCleanupResults($cleanupResults);
            
            if (!$isDryRun) {
                $this->logger->logAuditTrail('cleanup', 'EmailTransaction', 0, $cleanupResults);
            }

            $this->info('Email transaction data cleanup completed.');
            return 0;
            
        } catch (Exception $e) {
            $this->error('Cleanup failed: ' . $e->getMessage());
            $this->logger->logCriticalError('CleanupOldEmailData', $e);
            return 1;
        }
    }

    private function performCleanup(bool $isDryRun): array
    {
        $results = [
            'failed_transactions_deleted' => 0,
            'old_processed_transactions_deleted' => 0,
            'total_deleted' => 0
        ];

        // Get retention periods from config
        $failedRetentionDays = config('email_transactions.data_retention.failed_transactions_days', 30);
        $processedRetentionDays = config('email_transactions.data_retention.processed_transactions_days', 365);

        // Clean up old failed transactions
        $failedCutoffDate = now()->subDays($failedRetentionDays);
        $failedQuery = EmailTransaction::where('processing_status', 'failed')
                                     ->where('created_at', '<', $failedCutoffDate);

        $failedCount = $failedQuery->count();
        
        if ($failedCount > 0) {
            $this->line("Found {$failedCount} failed transactions older than {$failedRetentionDays} days");
            
            if (!$isDryRun) {
                $deleted = $failedQuery->delete();
                $results['failed_transactions_deleted'] = $deleted;
                $this->info("Deleted {$deleted} failed transactions");
            } else {
                $this->info("Would delete {$failedCount} failed transactions");
                $results['failed_transactions_deleted'] = $failedCount;
            }
        }

        // Clean up very old processed transactions (keep recent ones for reporting)
        $processedCutoffDate = now()->subDays($processedRetentionDays);
        $processedQuery = EmailTransaction::where('processing_status', 'processed')
                                        ->where('created_at', '<', $processedCutoffDate);

        $processedCount = $processedQuery->count();
        
        if ($processedCount > 0) {
            $this->line("Found {$processedCount} processed transactions older than {$processedRetentionDays} days");
            
            if (!$isDryRun) {
                $deleted = $processedQuery->delete();
                $results['old_processed_transactions_deleted'] = $deleted;
                $this->info("Deleted {$deleted} old processed transactions");
            } else {
                $this->info("Would delete {$processedCount} old processed transactions");
                $results['old_processed_transactions_deleted'] = $processedCount;
            }
        }

        $results['total_deleted'] = $results['failed_transactions_deleted'] + $results['old_processed_transactions_deleted'];

        return $results;
    }

    private function displayCleanupResults(array $results): void
    {
        $this->line('');
        $this->line('=== Cleanup Results ===');
        $this->line("Failed transactions cleaned: {$results['failed_transactions_deleted']}");
        $this->line("Old processed transactions cleaned: {$results['old_processed_transactions_deleted']}");
        $this->line("Total records cleaned: {$results['total_deleted']}");
        
        if ($results['total_deleted'] === 0) {
            $this->info('No old data found to clean up.');
        }
    }
}