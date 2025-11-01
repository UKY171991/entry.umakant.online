<?php

namespace App\Console\Commands;

use App\Models\EmailConfiguration;
use App\Models\EmailTransaction;
use App\Services\EmailFetcherService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class EmailHealthCheck extends Command
{
    protected $signature = 'email:health-check 
                            {--alert-threshold=10 : Failure rate threshold for alerts (percentage)}
                            {--time-window=24 : Time window in hours for health check}';

    protected $description = 'Perform health check on email transaction system';

    public function handle(EmailFetcherService $emailFetcher): int
    {
        $this->info('Performing Email Transaction System Health Check...');
        
        $timeWindow = (int) $this->option('time-window');
        $alertThreshold = (int) $this->option('alert-threshold');
        $issues = [];

        // Check email configurations
        $configIssues = $this->checkEmailConfigurations($emailFetcher);
        $issues = array_merge($issues, $configIssues);

        // Check transaction processing health
        $transactionIssues = $this->checkTransactionHealth($timeWindow, $alertThreshold);
        $issues = array_merge($issues, $transactionIssues);

        // Check for stuck transactions
        $stuckIssues = $this->checkStuckTransactions();
        $issues = array_merge($issues, $stuckIssues);

        // Check queue health
        $queueIssues = $this->checkQueueHealth();
        $issues = array_merge($issues, $queueIssues);

        // Report results
        if (empty($issues)) {
            $this->info('✅ All health checks passed!');
            Log::info('Email system health check passed');
            return self::SUCCESS;
        } else {
            $this->error('❌ Health check found issues:');
            foreach ($issues as $issue) {
                $this->error("   • {$issue}");
            }
            
            Log::warning('Email system health check failed', ['issues' => $issues]);
            return self::FAILURE;
        }
    }

    private function checkEmailConfigurations(EmailFetcherService $emailFetcher): array
    {
        $issues = [];
        
        $activeConfigurations = EmailConfiguration::where('is_active', true)->get();
        
        if ($activeConfigurations->isEmpty()) {
            $issues[] = 'No active email configurations found';
            return $issues;
        }

        foreach ($activeConfigurations as $config) {
            try {
                $isConnected = $emailFetcher->testConnection($config);
                
                if (!$isConnected) {
                    $issues[] = "Cannot connect to email account: {$config->email}";
                }
                
                // Check if configuration hasn't synced recently
                if ($config->last_sync_at && $config->last_sync_at->diffInHours(now()) > 2) {
                    $issues[] = "Email account {$config->email} hasn't synced in over 2 hours";
                }
                
            } catch (\Exception $e) {
                $issues[] = "Error testing connection for {$config->email}: {$e->getMessage()}";
            }
        }

        return $issues;
    }

    private function checkTransactionHealth(int $timeWindow, int $alertThreshold): array
    {
        $issues = [];
        
        $since = now()->subHours($timeWindow);
        
        $totalTransactions = EmailTransaction::where('created_at', '>=', $since)->count();
        $failedTransactions = EmailTransaction::where('created_at', '>=', $since)
            ->where('processing_status', 'failed')
            ->count();

        if ($totalTransactions > 0) {
            $failureRate = ($failedTransactions / $totalTransactions) * 100;
            
            if ($failureRate > $alertThreshold) {
                $issues[] = "High failure rate: {$failureRate}% of transactions failed in the last {$timeWindow} hours";
            }
        }

        // Check for transactions stuck in pending status
        $oldPendingCount = EmailTransaction::where('processing_status', 'pending')
            ->where('created_at', '<', now()->subHour())
            ->count();

        if ($oldPendingCount > 0) {
            $issues[] = "{$oldPendingCount} transactions stuck in pending status for over 1 hour";
        }

        return $issues;
    }

    private function checkStuckTransactions(): array
    {
        $issues = [];
        
        // Check for transactions that have been retried too many times
        $maxRetryCount = EmailTransaction::where('retry_count', '>', 5)->count();
        
        if ($maxRetryCount > 0) {
            $issues[] = "{$maxRetryCount} transactions have exceeded maximum retry attempts";
        }

        // Check for very old failed transactions
        $oldFailedCount = EmailTransaction::where('processing_status', 'failed')
            ->where('created_at', '<', now()->subDays(7))
            ->whereNull('last_retry_at')
            ->count();

        if ($oldFailedCount > 0) {
            $issues[] = "{$oldFailedCount} failed transactions older than 7 days haven't been retried";
        }

        return $issues;
    }

    private function checkQueueHealth(): array
    {
        $issues = [];
        
        try {
            // This is a basic check - in production you might want more sophisticated queue monitoring
            $queueSize = \Illuminate\Support\Facades\Queue::size();
            
            if ($queueSize > 1000) {
                $issues[] = "Queue is backed up with {$queueSize} pending jobs";
            }
        } catch (\Exception $e) {
            $issues[] = "Unable to check queue status: {$e->getMessage()}";
        }

        return $issues;
    }
}