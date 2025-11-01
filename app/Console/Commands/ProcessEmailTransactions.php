<?php

namespace App\Console\Commands;

use App\Jobs\FetchEmailsJob;
use App\Jobs\RetryFailedTransactionsJob;
use App\Models\EmailConfiguration;
use App\Models\EmailTransaction;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ProcessEmailTransactions extends Command
{
    protected $signature = 'email:process 
                            {--config-id= : Specific email configuration ID to process}
                            {--retry-failed : Retry failed transactions}
                            {--sync : Run synchronously instead of queuing jobs}
                            {--limit=10 : Limit number of emails to process per configuration}';

    protected $description = 'Manually process email transactions for configured accounts';

    public function handle(): int
    {
        try {
            $this->info('Starting manual email transaction processing...');

            // Handle retry failed transactions
            if ($this->option('retry-failed')) {
                return $this->retryFailedTransactions();
            }

            // Handle regular email processing
            return $this->processEmails();

        } catch (\Exception $e) {
            $this->error("Email processing failed: {$e->getMessage()}");
            
            Log::error('Manual email processing command failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return self::FAILURE;
        }
    }

    private function processEmails(): int
    {
        $query = EmailConfiguration::where('is_active', true);

        // Filter by specific configuration if provided
        if ($configId = $this->option('config-id')) {
            $query->where('id', $configId);
        }

        $configurations = $query->get();

        if ($configurations->isEmpty()) {
            $this->info('No active email configurations found.');
            return self::SUCCESS;
        }

        $this->info("Processing {$configurations->count()} email configuration(s)...");

        $totalJobsDispatched = 0;

        foreach ($configurations as $configuration) {
            $this->line("Processing emails for: {$configuration->email}");

            try {
                if ($this->option('sync')) {
                    // Process synchronously for immediate results
                    $this->processSynchronously($configuration);
                } else {
                    // Dispatch job for asynchronous processing
                    FetchEmailsJob::dispatch($configuration);
                    $totalJobsDispatched++;
                    $this->info("  → Job dispatched for {$configuration->email}");
                }

            } catch (\Exception $e) {
                $this->error("  → Failed to process {$configuration->email}: {$e->getMessage()}");
                
                Log::error('Failed to process email configuration', [
                    'email_configuration_id' => $configuration->id,
                    'email' => $configuration->email,
                    'error' => $e->getMessage()
                ]);
            }
        }

        if (!$this->option('sync')) {
            $this->info("Successfully dispatched {$totalJobsDispatched} email processing job(s).");
            $this->line('Use "php artisan queue:work" to process the jobs.');
        }

        return self::SUCCESS;
    }

    private function processSynchronously(EmailConfiguration $configuration): void
    {
        $emailFetcher = app(\App\Services\EmailFetcherService::class);
        $emailParser = app(\App\Services\EmailParserService::class);
        $transactionProcessor = app(\App\Services\TransactionProcessorService::class);

        try {
            // Test connection first
            if (!$emailFetcher->testConnection($configuration)) {
                $this->error("  → Connection test failed for {$configuration->email}");
                return;
            }

            // Fetch emails
            $emails = $emailFetcher->fetchNewEmails($configuration);
            $this->info("  → Found {$emails->count()} new emails");

            if ($emails->isEmpty()) {
                return;
            }

            // Limit emails if specified
            $limit = (int) $this->option('limit');
            if ($limit > 0) {
                $emails = $emails->take($limit);
                $this->info("  → Processing first {$emails->count()} emails (limited)");
            }

            $processed = 0;
            $failed = 0;

            foreach ($emails as $email) {
                try {
                    $messageId = $email->getMessageId();
                    
                    // Check if already processed
                    if (EmailTransaction::where('message_id', $messageId)->exists()) {
                        $this->line("  → Skipping already processed email: {$messageId}");
                        continue;
                    }

                    // Parse transaction
                    $transactionData = $emailParser->parseTransaction($email);
                    
                    if (!$transactionData) {
                        $this->warn("  → Failed to parse email: {$email->getSubject()}");
                        $failed++;
                        continue;
                    }

                    // Create email transaction record
                    $emailTransaction = EmailTransaction::create([
                        'email_configuration_id' => $configuration->id,
                        'message_id' => $messageId,
                        'subject' => $email->getSubject(),
                        'sender' => $email->getSender(),
                        'transaction_type' => $transactionData->type,
                        'amount' => $transactionData->amount,
                        'transaction_date' => $transactionData->date,
                        'description' => $transactionData->description,
                        'raw_content' => $email->getBody(),
                        'processing_status' => 'pending'
                    ]);

                    // Process transaction
                    $success = $transactionProcessor->processTransaction($transactionData, $email, $emailTransaction);
                    
                    if ($success) {
                        $emailTransaction->update(['processing_status' => 'processed']);
                        $this->info("  → Processed: {$transactionData->type} {$transactionData->amount}");
                        $processed++;
                    } else {
                        $emailTransaction->update([
                            'processing_status' => 'failed',
                            'error_message' => 'Failed to create income/expense record'
                        ]);
                        $this->error("  → Failed to process transaction");
                        $failed++;
                    }

                } catch (\Exception $e) {
                    $this->error("  → Error processing email: {$e->getMessage()}");
                    $failed++;
                }
            }

            // Update last sync time
            $configuration->update(['last_sync_at' => now()]);

            $this->info("  → Summary: {$processed} processed, {$failed} failed");

        } catch (\Exception $e) {
            $this->error("  → Processing failed: {$e->getMessage()}");
            throw $e;
        }
    }

    private function retryFailedTransactions(): int
    {
        $this->info('Retrying failed transactions...');

        try {
            RetryFailedTransactionsJob::dispatch();
            $this->info('Retry job dispatched successfully.');
            
            // Show current failed transaction count
            $failedCount = EmailTransaction::where('processing_status', 'failed')->count();
            $this->info("Current failed transactions: {$failedCount}");

            return self::SUCCESS;

        } catch (\Exception $e) {
            $this->error("Failed to dispatch retry job: {$e->getMessage()}");
            return self::FAILURE;
        }
    }
}