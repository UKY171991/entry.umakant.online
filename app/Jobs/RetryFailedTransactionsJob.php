<?php

namespace App\Jobs;

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

class RetryFailedTransactionsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 1; // Only try once for retry job
    public $timeout = 300; // 5 minutes

    public function handle(
        EmailParserService $emailParser,
        TransactionProcessorService $transactionProcessor
    ): void {
        try {
            Log::info('Starting retry of failed transactions');

            // Get failed transactions from the last 24 hours
            $failedTransactions = EmailTransaction::where('processing_status', 'failed')
                ->where('created_at', '>=', now()->subDay())
                ->whereNull('income_id')
                ->whereNull('expense_id')
                ->limit(50) // Process in batches
                ->get();

            Log::info('Found failed transactions to retry', [
                'count' => $failedTransactions->count()
            ]);

            $retryCount = 0;
            $successCount = 0;

            foreach ($failedTransactions as $emailTransaction) {
                try {
                    $retryCount++;
                    
                    Log::info('Retrying failed transaction', [
                        'email_transaction_id' => $emailTransaction->id,
                        'message_id' => $emailTransaction->message_id
                    ]);

                    // Skip if we don't have the necessary data
                    if (!$emailTransaction->amount || !$emailTransaction->transaction_type) {
                        Log::warning('Skipping transaction retry - missing required data', [
                            'email_transaction_id' => $emailTransaction->id
                        ]);
                        continue;
                    }

                    // Create transaction data from stored email transaction
                    $transactionData = new \App\DTOs\TransactionData(
                        type: $emailTransaction->transaction_type,
                        amount: $emailTransaction->amount,
                        date: $emailTransaction->transaction_date,
                        description: $emailTransaction->description ?? 'Email transaction'
                    );

                    // Create a mock email object for the processor
                    $mockEmail = new class($emailTransaction) {
                        private $emailTransaction;
                        
                        public function __construct($emailTransaction) {
                            $this->emailTransaction = $emailTransaction;
                        }
                        
                        public function getMessageId() {
                            return $this->emailTransaction->message_id;
                        }
                        
                        public function getSubject() {
                            return $this->emailTransaction->subject;
                        }
                        
                        public function getSender() {
                            return $this->emailTransaction->sender;
                        }
                        
                        public function getBody() {
                            return $this->emailTransaction->raw_content;
                        }
                    };

                    // Attempt to process the transaction
                    $success = $transactionProcessor->processTransaction(
                        $transactionData, 
                        $mockEmail, 
                        $emailTransaction
                    );

                    if ($success) {
                        $emailTransaction->update([
                            'processing_status' => 'processed',
                            'error_message' => null
                        ]);
                        
                        $successCount++;
                        
                        Log::info('Successfully retried failed transaction', [
                            'email_transaction_id' => $emailTransaction->id,
                            'transaction_type' => $transactionData->type,
                            'amount' => $transactionData->amount
                        ]);
                    } else {
                        Log::warning('Retry failed for transaction', [
                            'email_transaction_id' => $emailTransaction->id
                        ]);
                    }

                } catch (Exception $e) {
                    Log::error('Error during transaction retry', [
                        'email_transaction_id' => $emailTransaction->id,
                        'error' => $e->getMessage()
                    ]);
                    
                    // Update error message
                    $emailTransaction->update([
                        'error_message' => 'Retry failed: ' . $e->getMessage()
                    ]);
                }
            }

            Log::info('Completed retry of failed transactions', [
                'total_attempted' => $retryCount,
                'successful_retries' => $successCount,
                'failed_retries' => $retryCount - $successCount
            ]);

        } catch (Exception $e) {
            Log::error('Failed transactions retry job failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            throw $e;
        }
    }

    public function failed(Exception $exception): void
    {
        Log::error('Retry failed transactions job failed permanently', [
            'error' => $exception->getMessage()
        ]);
    }
}