<?php

namespace App\Jobs;

use App\Models\EmailConfiguration;
use App\Services\EmailFetcherService;
use App\Services\EmailParserService;
use App\Services\TransactionProcessorService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Exception;

class FetchEmailsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $backoff = [60, 300, 900]; // 1 min, 5 min, 15 min

    protected EmailConfiguration $emailConfiguration;

    public function __construct(EmailConfiguration $emailConfiguration)
    {
        $this->emailConfiguration = $emailConfiguration;
    }

    public function handle(
        EmailFetcherService $emailFetcher,
        EmailParserService $emailParser,
        TransactionProcessorService $transactionProcessor
    ): void {
        try {
            Log::info('Starting email fetch job', [
                'email_configuration_id' => $this->emailConfiguration->id,
                'email' => $this->emailConfiguration->email
            ]);

            // Fetch new emails
            $emails = $emailFetcher->fetchNewEmails($this->emailConfiguration);
            
            Log::info('Fetched emails', [
                'count' => $emails->count(),
                'email_configuration_id' => $this->emailConfiguration->id
            ]);

            // Process each email
            foreach ($emails as $email) {
                try {
                    // Dispatch individual email processing job
                    ProcessEmailTransactionJob::dispatch($email, $this->emailConfiguration);
                } catch (Exception $e) {
                    Log::error('Failed to dispatch email processing job', [
                        'email_configuration_id' => $this->emailConfiguration->id,
                        'message_id' => $email->getMessageId(),
                        'error' => $e->getMessage()
                    ]);
                }
            }

            // Update last sync time
            $this->emailConfiguration->update([
                'last_sync_at' => now()
            ]);

            Log::info('Email fetch job completed successfully', [
                'email_configuration_id' => $this->emailConfiguration->id,
                'emails_processed' => $emails->count()
            ]);

        } catch (Exception $e) {
            Log::error('Email fetch job failed', [
                'email_configuration_id' => $this->emailConfiguration->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            throw $e;
        }
    }

    public function failed(Exception $exception): void
    {
        Log::error('Email fetch job failed permanently', [
            'email_configuration_id' => $this->emailConfiguration->id,
            'error' => $exception->getMessage(),
            'attempts' => $this->attempts()
        ]);

        // Could send notification to administrators here
    }
}