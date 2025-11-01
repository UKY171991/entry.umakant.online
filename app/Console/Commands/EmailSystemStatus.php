<?php

namespace App\Console\Commands;

use App\Models\EmailConfiguration;
use App\Models\EmailTransaction;
use App\Services\EmailFetcherService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Queue;

class EmailSystemStatus extends Command
{
    protected $signature = 'email:status 
                            {--detailed : Show detailed information}
                            {--test-connections : Test all email connections}';

    protected $description = 'Display email transaction system status and statistics';

    public function handle(): int
    {
        try {
            $this->info('Email Transaction System Status');
            $this->line('=====================================');

            $this->showConfigurationStatus();
            $this->line('');
            
            $this->showTransactionStatistics();
            $this->line('');
            
            $this->showQueueStatus();
            
            if ($this->option('detailed')) {
                $this->line('');
                $this->showDetailedStatistics();
            }

            if ($this->option('test-connections')) {
                $this->line('');
                $this->testConnections();
            }

            return self::SUCCESS;

        } catch (\Exception $e) {
            $this->error("Failed to get system status: {$e->getMessage()}");
            return self::FAILURE;
        }
    }

    private function showConfigurationStatus(): void
    {
        $this->info('Email Configurations:');
        
        $configurations = EmailConfiguration::all();
        
        if ($configurations->isEmpty()) {
            $this->warn('  No email configurations found.');
            return;
        }

        $activeCount = $configurations->where('is_active', true)->count();
        $inactiveCount = $configurations->where('is_active', false)->count();

        $this->line("  Total: {$configurations->count()}");
        $this->line("  Active: {$activeCount}");
        $this->line("  Inactive: {$inactiveCount}");

        foreach ($configurations as $config) {
            $status = $config->is_active ? '✓' : '✗';
            $lastSync = $config->last_sync_at ? $config->last_sync_at->diffForHumans() : 'Never';
            
            $this->line("  {$status} {$config->email} (Last sync: {$lastSync})");
        }
    }

    private function showTransactionStatistics(): void
    {
        $this->info('Transaction Statistics:');
        
        $total = EmailTransaction::count();
        $processed = EmailTransaction::where('processing_status', 'processed')->count();
        $pending = EmailTransaction::where('processing_status', 'pending')->count();
        $failed = EmailTransaction::where('processing_status', 'failed')->count();

        $this->line("  Total transactions: {$total}");
        $this->line("  Processed: {$processed}");
        $this->line("  Pending: {$pending}");
        $this->line("  Failed: {$failed}");

        if ($total > 0) {
            $successRate = round(($processed / $total) * 100, 1);
            $this->line("  Success rate: {$successRate}%");
        }

        // Recent activity (last 24 hours)
        $recent = EmailTransaction::where('created_at', '>=', now()->subDay())->count();
        $this->line("  Last 24 hours: {$recent}");
    }

    private function showQueueStatus(): void
    {
        $this->info('Queue Status:');
        
        try {
            // This is a simplified queue status check
            // In production, you might want to use a more sophisticated queue monitoring solution
            $this->line('  Queue connection: Available');
            
            // Show pending jobs count if possible
            $pendingJobs = \Illuminate\Support\Facades\DB::table('jobs')->count();
            $this->line("  Pending jobs: {$pendingJobs}");
            
            $failedJobs = \Illuminate\Support\Facades\DB::table('failed_jobs')->count();
            $this->line("  Failed jobs: {$failedJobs}");
            
        } catch (\Exception $e) {
            $this->warn('  Queue status: Unable to determine');
        }
    }

    private function showDetailedStatistics(): void
    {
        $this->info('Detailed Statistics:');
        
        // Transactions by type
        $incomeCount = EmailTransaction::where('transaction_type', 'income')->count();
        $expenseCount = EmailTransaction::where('transaction_type', 'expense')->count();
        
        $this->line("  Income transactions: {$incomeCount}");
        $this->line("  Expense transactions: {$expenseCount}");

        // Transactions by configuration
        $this->line('');
        $this->line('  Transactions by email configuration:');
        
        $configStats = EmailTransaction::selectRaw('email_configuration_id, count(*) as count')
            ->groupBy('email_configuration_id')
            ->with('emailConfiguration')
            ->get();

        foreach ($configStats as $stat) {
            $email = $stat->emailConfiguration->email ?? 'Unknown';
            $this->line("    {$email}: {$stat->count}");
        }

        // Recent failures
        $recentFailures = EmailTransaction::where('processing_status', 'failed')
            ->where('created_at', '>=', now()->subDay())
            ->count();
            
        if ($recentFailures > 0) {
            $this->line('');
            $this->warn("  Recent failures (24h): {$recentFailures}");
            
            // Show some recent failure reasons
            $failureReasons = EmailTransaction::where('processing_status', 'failed')
                ->where('created_at', '>=', now()->subDay())
                ->whereNotNull('error_message')
                ->pluck('error_message')
                ->unique()
                ->take(3);
                
            foreach ($failureReasons as $reason) {
                $this->line("    - {$reason}");
            }
        }
    }

    private function testConnections(): void
    {
        $this->info('Testing Email Connections:');
        
        $emailFetcher = app(EmailFetcherService::class);
        $configurations = EmailConfiguration::where('is_active', true)->get();
        
        if ($configurations->isEmpty()) {
            $this->warn('  No active configurations to test.');
            return;
        }

        foreach ($configurations as $config) {
            $this->line("  Testing {$config->email}...", null, false);
            
            try {
                $success = $emailFetcher->testConnection($config);
                
                if ($success) {
                    $this->info(' ✓ Connected');
                } else {
                    $this->error(' ✗ Failed');
                }
                
            } catch (\Exception $e) {
                $this->error(" ✗ Error: {$e->getMessage()}");
            }
        }
    }
}