<?php

namespace App\Jobs;

use App\Models\EmailConfiguration;
use App\Models\EmailTransaction;
use App\Services\EmailParserService;
use App\Services\TransactionProcessorService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Exception;

class ProcessEmailTransactionJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $backoff = [30, 120, 600]; // 30 sec, 2 min, 10 min

    protected $email;
    protected EmailConfiguration $emailConfiguration;

    public function __construct($email, EmailConfiguration $emailConfiguration)
    {
        $this->email = $email;
        $this->emailConfiguration = $emailConfiguration;
    }

    public function handle(
        EmailParserService $emailParser,
        TransactionProcessorService $transactionProcessor
    ): void {
        try {
            $messageId = $this->email->getMessageId();
            
            Log::info('Processing email transaction', [
                'message_id' => $messageId,
                'email_configuration_id' => $this->emailConfiguration->id
            ]);

            // Check if already processed
            $existingTransaction = EmailTransaction::where('message_id', $messageId)->first();
            if ($existingTransaction) {
                Log::info('Email already processed, skipping', [
                    'message_id' => $messageId,
                    'existing_transaction_id' => $existingTransaction->id
                ]);
                return;
            }

            // Create email transaction record
            $emailTransaction = EmailTransaction::create([
                'email_configuration_id' => $this->emailConfiguration->id,
                'message_id' => $messageId,
                'subject' => $this->email->getSubject(),
                'sender' => $this->email->getSender(),
                'raw_content' => $this->email->getBody(),
                'processing_status' => 'pending'
            ]);

            // Parse transaction data
            $transactionData = $emailParser->parseTransaction($this->email);
            
            if (!$transactionData) {
                $emailTransaction->update([
                    'processing_status' => 'failed',
                    'error_message' => 'Failed to parse transaction data from email'
                ]);
                
                Log::warning('Failed to parse email transaction', [
                    'message_id' => $messageId,
                    'subject' => $this->email->getSubject()
                ]);
                return;
            }

            // Update email transaction with parsed data
            $emailTransaction->update([
                'transaction_type' => $transactionData->type,
                'amount' => $transactionData->amount,
                'transaction_date' => $transactionData->date,
                'description' => $transactionData->description
            ]);

            // Process the transaction (create income/expense record)
            $success = $transactionProcessor->processTransaction($transactionData, $this->email, $emailTransaction);

            if ($success) {
                $emailTransaction->update(['processing_status' => 'processed']);
                
                Log::info('Email transaction processed successfully', [
                    'message_id' => $messageId,
                    'transaction_type' => $transactionData->type,
                    'amount' => $transactionData->amount
                ]);
            } else {
                $emailTransaction->update([
                    'processing_status' => 'failed',
                    'error_message' => 'Failed to create income/expense record'
                ]);
                
                Log::error('Failed to process email transaction', [
                    'message_id' => $messageId,
                    'transaction_data' => $transactionData->toArray()
                ]);
                
                throw new Exception('Failed to process transaction');
            }

        } catch (Exception $e) {
            Log::error('Email transaction processing job failed', [
                'message_id' => $this->email->getMessageId() ?? 'unknown',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // Update email transaction status if it exists
            if (isset($emailTransaction)) {
                $emailTransaction->update([
                    'processing_status' => 'failed',
                    'error_message' => $e->getMessage()
                ]);
            }

            throw $e;
        }
    }

    public function failed(Exception $exception): void
    {
        Log::error('Email transaction processing job failed permanently', [
            'message_id' => $this->email->getMessageId() ?? 'unknown',
            'email_configuration_id' => $this->emailConfiguration->id,
            'error' => $exception->getMessage(),
            'attempts' => $this->attempts()
        ]);

        // Update email transaction status if possible
        try {
            $messageId = $this->email->getMessageId();
            if ($messageId) {
                EmailTransaction::where('message_id', $messageId)
                    ->update([
                        'processing_status' => 'failed',
                        'error_message' => 'Job failed permanently: ' . $exception->getMessage()
                    ]);
            }
        } catch (Exception $e) {
            Log::error('Failed to update email transaction status after job failure', [
                'error' => $e->getMessage()
            ]);
        }
    }
}