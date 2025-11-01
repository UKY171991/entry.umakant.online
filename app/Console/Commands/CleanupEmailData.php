<?php

namespace App\Console\Commands;

use App\Models\EmailTransaction;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CleanupEmailData extends Command
{
    protected $signature = 'email:cleanup 
                            {--days=30 : Number of days to keep processed transactions}
                            {--keep-failed=90 : Number of days to keep failed transactions}
                            {--dry-run : Show what would be deleted without actually deleting}
                            {--force : Skip confirmation prompt}';

    protected $description = 'Clean up old email transaction data to free up storage space';

    public function handle(): int
    {
        try {
            $days = (int) $this->option('days');
            $keepFailedDays = (int) $this->option('keep-failed');
            $dryRun = $this->option('dry-run');
            $force = $this->option('force');

            $this->info('Email Data Cleanup');
            $this->line('==================');

            // Calculate cutoff dates
            $processedCutoff = now()->subDays($days);
            $failedCutoff = now()->subDays($keepFailedDays);

            $this->line("Processed transactions older than: {$processedCutoff->format('Y-m-d H:i:s')}");
            $this->line("Failed transactions older than: {$failedCutoff->format('Y-m-d H:i:s')}");
            $this->line('');

            // Count records to be deleted
            $processedCount = EmailTransaction::where('processing_status', 'processed')
                ->where('created_at', '<', $processedCutoff)
                ->count();

            $failedCount = EmailTransaction::where('processing_status', 'failed')
                ->where('created_at', '<', $failedCutoff)
                ->count();

            $totalCount = $processedCount + $failedCount;

            $this->info("Records to be deleted:");
            $this->line("  Processed transactions: {$processedCount}");
            $this->line("  Failed transactions: {$failedCount}");
            $this->line("  Total: {$totalCount}");

            if ($totalCount === 0) {
                $this->info('No records to delete.');
                return self::SUCCESS;
            }

            if ($dryRun) {
                $this->info('');
                $this->warn('DRY RUN - No records will actually be deleted.');
                return self::SUCCESS;
            }

            // Confirmation prompt
            if (!$force) {
                $this->line('');
                if (!$this->confirm("Are you sure you want to delete {$totalCount} email transaction records?")) {
                    $this->info('Cleanup cancelled.');
                    return self::SUCCESS;
                }
            }

            $this->line('');
            $this->info('Starting cleanup...');

            $deletedCount = 0;

            // Delete processed transactions
            if ($processedCount > 0) {
                $this->line('Deleting processed transactions...');
                
                $deleted = EmailTransaction::where('processing_status', 'processed')
                    ->where('created_at', '<', $processedCutoff)
                    ->delete();
                    
                $deletedCount += $deleted;
                $this->info("  Deleted {$deleted} processed transactions");
            }

            // Delete failed transactions
            if ($failedCount > 0) {
                $this->line('Deleting failed transactions...');
                
                $deleted = EmailTransaction::where('processing_status', 'failed')
                    ->where('created_at', '<', $failedCutoff)
                    ->delete();
                    
                $deletedCount += $deleted;
                $this->info("  Deleted {$deleted} failed transactions");
            }

            $this->line('');
            $this->info("Cleanup completed successfully!");
            $this->info("Total records deleted: {$deletedCount}");

            // Log the cleanup operation
            Log::info('Email data cleanup completed', [
                'processed_cutoff' => $processedCutoff,
                'failed_cutoff' => $failedCutoff,
                'records_deleted' => $deletedCount,
                'processed_deleted' => $processedCount,
                'failed_deleted' => $failedCount
            ]);

            // Show remaining records
            $remaining = EmailTransaction::count();
            $this->line("Remaining email transactions: {$remaining}");

            return self::SUCCESS;

        } catch (\Exception $e) {
            $this->error("Cleanup failed: {$e->getMessage()}");
            
            Log::error('Email data cleanup failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return self::FAILURE;
        }
    }
}