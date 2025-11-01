<?php

namespace App\Services;

use App\Contracts\TransactionProcessorInterface;
use App\DTOs\TransactionData;
use App\Models\EmailTransaction;
use App\Models\Income;
use App\Models\Expense;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class TransactionProcessorService implements TransactionProcessorInterface
{
    private float $duplicateThreshold;
    private int $duplicateTimeWindow;
    private EmailTransactionLoggerService $logger;
    private EmailTransactionNotificationService $notificationService;

    public function __construct(
        EmailTransactionLoggerService $logger,
        EmailTransactionNotificationService $notificationService
    ) {
        $this->duplicateThreshold = config('email_transactions.error_handling.duplicate_threshold', 0.01);
        $this->duplicateTimeWindow = config('email_transactions.error_handling.duplicate_time_window', 24);
        $this->logger = $logger;
        $this->notificationService = $notificationService;
    }

    /**
     * Process a transaction and create corresponding income/expense record
     */
    public function processTransaction(TransactionData $data, $email = null, EmailTransaction $emailTransaction = null): bool
    {
        $startTime = microtime(true);
        
        try {
            // Check for duplicates first
            if ($this->isDuplicate($data)) {
                $this->logger->logDuplicateDetection([
                    'amount' => $data->amount,
                    'date' => $data->date->toDateString(),
                    'type' => $data->type
                ], true);
                
                if ($emailTransaction) {
                    $emailTransaction->update([
                        'processing_status' => 'failed',
                        'error_message' => 'Duplicate transaction detected'
                    ]);
                    
                    $this->logger->logAuditTrail('failed', 'EmailTransaction', $emailTransaction->id, [
                        'reason' => 'duplicate_detected'
                    ]);
                }
                return false;
            }

            $this->logger->logDuplicateDetection([
                'amount' => $data->amount,
                'date' => $data->date->toDateString(),
                'type' => $data->type
            ], false);

            return DB::transaction(function () use ($data, $email, $emailTransaction, $startTime) {
                // Create corresponding Income or Expense record
                $record = null;
                if ($data->type === 'income') {
                    $record = $this->createIncomeRecord($data, $emailTransaction);
                    if ($record && $emailTransaction) {
                        $emailTransaction->update(['income_id' => $record->id]);
                        $this->logger->logAuditTrail('created', 'Income', $record->id, [
                            'amount' => $data->amount,
                            'source' => 'email_transaction',
                            'email_transaction_id' => $emailTransaction->id
                        ]);
                    }
                } else {
                    $record = $this->createExpenseRecord($data, $emailTransaction);
                    if ($record && $emailTransaction) {
                        $emailTransaction->update(['expense_id' => $record->id]);
                        $this->logger->logAuditTrail('created', 'Expense', $record->id, [
                            'amount' => $data->amount,
                            'source' => 'email_transaction',
                            'email_transaction_id' => $emailTransaction->id
                        ]);
                    }
                }

                if ($record) {
                    if ($emailTransaction) {
                        $emailTransaction->update(['processing_status' => 'processed']);
                        $this->logger->logAuditTrail('updated', 'EmailTransaction', $emailTransaction->id, [
                            'processing_status' => 'processed',
                            'linked_record_id' => $record->id,
                            'linked_record_type' => $data->type
                        ]);
                    }
                    
                    $this->logger->logTransactionProcessing($emailTransaction, $record->id);
                    
                    // Log performance metrics
                    $executionTime = microtime(true) - $startTime;
                    $this->logger->logPerformanceMetrics('transaction_processing', $executionTime, [
                        'email_transaction_id' => $emailTransaction?->id,
                        'record_type' => $data->type,
                        'amount' => $data->amount
                    ]);
                    
                    return true;
                } else {
                    if ($emailTransaction) {
                        $emailTransaction->update([
                            'processing_status' => 'failed',
                            'error_message' => 'Failed to create income/expense record'
                        ]);
                        
                        $this->logger->logAuditTrail('failed', 'EmailTransaction', $emailTransaction->id, [
                            'reason' => 'record_creation_failed'
                        ]);
                    }
                    return false;
                }
            });

        } catch (Exception $e) {
            $this->logger->logTransactionProcessing($emailTransaction, null, $e);
            
            if ($emailTransaction) {
                $emailTransaction->update([
                    'processing_status' => 'failed',
                    'error_message' => $e->getMessage()
                ]);
                
                $this->logger->logAuditTrail('failed', 'EmailTransaction', $emailTransaction->id, [
                    'error' => $e->getMessage(),
                    'reason' => 'processing_exception'
                ]);
            }
            
            // Log critical error if this is a system-level failure
            if (str_contains($e->getMessage(), 'database') || str_contains($e->getMessage(), 'connection')) {
                $this->logger->logCriticalError('TransactionProcessorService', $e, [
                    'email_transaction_id' => $emailTransaction?->id,
                    'transaction_data' => $data->toArray()
                ]);
            }
            
            return false;
        }
    }

    /**
     * Check if a transaction is a duplicate
     */
    public function isDuplicate(TransactionData $data): bool
    {
        try {
            $startDate = $data->date->copy()->subHours($this->duplicateTimeWindow);
            $endDate = $data->date->copy()->addHours($this->duplicateTimeWindow);

            // Check in EmailTransactions first
            $existingEmailTransaction = EmailTransaction::where('processing_status', 'processed')
                ->where('transaction_type', $data->type)
                ->whereBetween('transaction_date', [$startDate, $endDate])
                ->where(function ($query) use ($data) {
                    $query->whereBetween('amount', [
                        $data->amount - $this->duplicateThreshold,
                        $data->amount + $this->duplicateThreshold
                    ]);
                })
                ->exists();

            if ($existingEmailTransaction) {
                return true;
            }

            // Check in Income/Expense tables for manual entries
            if ($data->type === 'income') {
                return Income::where('source', 'manual')
                    ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
                    ->where(function ($query) use ($data) {
                        $query->whereBetween('received_amount', [
                            $data->amount - $this->duplicateThreshold,
                            $data->amount + $this->duplicateThreshold
                        ]);
                    })
                    ->exists();
            } else {
                return Expense::where('source', 'manual')
                    ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
                    ->where(function ($query) use ($data) {
                        $query->whereBetween('amount', [
                            $data->amount - $this->duplicateThreshold,
                            $data->amount + $this->duplicateThreshold
                        ]);
                    })
                    ->exists();
            }
        } catch (Exception $e) {
            $this->logger->logCriticalError('TransactionProcessorService', $e, [
                'context' => 'duplicate_detection_failed',
                'transaction_data' => $data->toArray()
            ]);
            
            // Return false to allow processing if duplicate detection fails
            return false;
        }
    }

    /**
     * Retry failed transactions
     */
    public function retryFailedTransactions(): int
    {
        $failedTransactions = EmailTransaction::where('processing_status', 'failed')
            ->whereDate('created_at', '>=', now()->subDays(7)) // Only retry recent failures
            ->get();

        $retryCount = 0;

        foreach ($failedTransactions as $emailTransaction) {
            try {
                // Recreate TransactionData from stored data
                $transactionData = new TransactionData(
                    type: $emailTransaction->transaction_type,
                    amount: $emailTransaction->amount,
                    date: $emailTransaction->transaction_date,
                    description: $emailTransaction->description
                );

                // Prepare email data
                $emailData = [
                    'email_configuration_id' => $emailTransaction->email_configuration_id,
                    'message_id' => $emailTransaction->message_id,
                    'subject' => $emailTransaction->subject,
                    'sender' => $emailTransaction->sender,
                    'raw_content' => $emailTransaction->raw_content
                ];

                // Reset status to pending before retry
                $emailTransaction->update(['processing_status' => 'pending', 'error_message' => null]);

                if ($this->processTransaction($transactionData, $emailData)) {
                    $retryCount++;
                }

            } catch (Exception $e) {
                Log::error("Retry failed for email transaction {$emailTransaction->id}: " . $e->getMessage());
                $emailTransaction->markAsFailed('Retry failed: ' . $e->getMessage());
            }
        }

        Log::info("Retried {$retryCount} failed transactions");
        return $retryCount;
    }  
  /**
     * Create an EmailTransaction record
     */
    public function createEmailTransaction(TransactionData $data, array $emailData): EmailTransaction
    {
        return EmailTransaction::create([
            'email_configuration_id' => $emailData['email_configuration_id'],
            'message_id' => $emailData['message_id'],
            'subject' => $emailData['subject'],
            'sender' => $emailData['sender'],
            'transaction_type' => $data->transactionType,
            'amount' => $data->amount,
            'transaction_date' => $data->transactionDate,
            'description' => $data->description,
            'raw_content' => $emailData['raw_content'],
            'processing_status' => 'pending'
        ]);
    }

    /**
     * Create an Income record from transaction data
     */
    public function createIncomeRecord(TransactionData $data, EmailTransaction $emailTransaction): ?Income
    {
        try {
            return Income::create([
                'client_id' => null, // Email transactions don't have associated clients
                'total_amount' => $data->amount,
                'pending_amount' => 0.00,
                'received_amount' => $data->amount,
                'date' => $data->date,
                'source' => 'email',
                'description' => $data->description ?: $emailTransaction->subject,
                'email_transaction_id' => $emailTransaction->id
            ]);
        } catch (Exception $e) {
            Log::error("Failed to create income record", [
                'error' => $e->getMessage(),
                'email_transaction_id' => $emailTransaction->id,
                'data' => $data->toArray()
            ]);
            return null;
        }
    }

    /**
     * Create an Expense record from transaction data
     */
    public function createExpenseRecord(TransactionData $data, EmailTransaction $emailTransaction): ?Expense
    {
        try {
            return Expense::create([
                'expense_name' => $this->generateExpenseName($data),
                'amount' => $data->amount,
                'category' => $this->determineExpenseCategory($data),
                'status' => 'paid', // Email transactions are already completed
                'date' => $data->date,
                'notes' => $data->description,
                'source' => 'email',
                'email_transaction_id' => $emailTransaction->id
            ]);
        } catch (Exception $e) {
            Log::error("Failed to create expense record", [
                'error' => $e->getMessage(),
                'email_transaction_id' => $emailTransaction->id,
                'data' => $data->toArray()
            ]);
            return null;
        }
    }

    /**
     * Generate expense name from transaction data
     */
    private function generateExpenseName(TransactionData $data): string
    {
        if ($data->merchantName) {
            return "Payment to " . $data->merchantName;
        }

        if ($data->description) {
            return substr($data->description, 0, 50);
        }

        return "Email Transaction - " . $data->date->format('Y-m-d');
    }

    /**
     * Determine expense category based on transaction data
     */
    private function determineExpenseCategory(TransactionData $data): string
    {
        $description = strtolower($data->description ?: '');
        $content = $description;

        // Category mapping based on keywords
        $categories = [
            'Food & Dining' => ['restaurant', 'food', 'swiggy', 'zomato', 'cafe', 'pizza', 'burger'],
            'Shopping' => ['amazon', 'flipkart', 'mall', 'store', 'shopping', 'purchase'],
            'Transportation' => ['uber', 'ola', 'taxi', 'fuel', 'petrol', 'diesel', 'metro'],
            'Utilities' => ['electricity', 'water', 'gas', 'internet', 'mobile', 'phone'],
            'Healthcare' => ['hospital', 'pharmacy', 'medical', 'doctor', 'clinic'],
            'Entertainment' => ['movie', 'cinema', 'netflix', 'spotify', 'game'],
            'ATM' => ['atm', 'cash withdrawal'],
            'Transfer' => ['transfer', 'upi', 'neft', 'rtgs']
        ];

        foreach ($categories as $category => $keywords) {
            foreach ($keywords as $keyword) {
                if (str_contains($content, $keyword)) {
                    return $category;
                }
            }
        }

        return 'Others'; // Default category
    }
}