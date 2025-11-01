<?php

namespace App\Services;

use App\Models\EmailConfiguration;
use App\Models\EmailTransaction;
use Illuminate\Support\Facades\Log;
use Exception;

class EmailTransactionLoggerService
{
    private const LOG_CHANNEL = 'email_transactions';
    
    /**
     * Log email fetch operation
     */
    public function logEmailFetch(EmailConfiguration $config, int $emailCount, ?Exception $exception = null): void
    {
        $context = [
            'email_config_id' => $config->id,
            'email_account' => $config->email,
            'emails_fetched' => $emailCount,
            'operation' => 'email_fetch'
        ];

        if ($exception) {
            Log::channel(self::LOG_CHANNEL)->error('Email fetch failed', array_merge($context, [
                'error' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString()
            ]));
        } else {
            Log::channel(self::LOG_CHANNEL)->info('Email fetch completed', $context);
        }
    }

    /**
     * Log email parsing operation
     */
    public function logEmailParsing(string $messageId, ?array $parsedData = null, ?Exception $exception = null): void
    {
        $context = [
            'message_id' => $messageId,
            'operation' => 'email_parsing'
        ];

        if ($exception) {
            Log::channel(self::LOG_CHANNEL)->error('Email parsing failed', array_merge($context, [
                'error' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString()
            ]));
        } elseif ($parsedData) {
            Log::channel(self::LOG_CHANNEL)->info('Email parsing successful', array_merge($context, [
                'parsed_amount' => $parsedData['amount'] ?? null,
                'transaction_type' => $parsedData['type'] ?? null,
                'transaction_date' => $parsedData['date'] ?? null
            ]));
        } else {
            Log::channel(self::LOG_CHANNEL)->warning('Email parsing returned no data', $context);
        }
    }

    /**
     * Log transaction processing operation
     */
    public function logTransactionProcessing(EmailTransaction $emailTransaction, ?int $recordId = null, ?Exception $exception = null): void
    {
        $context = [
            'email_transaction_id' => $emailTransaction->id,
            'message_id' => $emailTransaction->message_id,
            'transaction_type' => $emailTransaction->transaction_type,
            'amount' => $emailTransaction->amount,
            'operation' => 'transaction_processing'
        ];

        if ($exception) {
            Log::channel(self::LOG_CHANNEL)->error('Transaction processing failed', array_merge($context, [
                'error' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString()
            ]));
        } elseif ($recordId) {
            Log::channel(self::LOG_CHANNEL)->info('Transaction processing successful', array_merge($context, [
                'created_record_id' => $recordId,
                'record_type' => $emailTransaction->transaction_type === 'income' ? 'Income' : 'Expense'
            ]));
        }
    }

    /**
     * Log duplicate detection
     */
    public function logDuplicateDetection(array $transactionData, bool $isDuplicate): void
    {
        $context = [
            'amount' => $transactionData['amount'] ?? null,
            'transaction_type' => $transactionData['type'] ?? null,
            'transaction_date' => $transactionData['date'] ?? null,
            'operation' => 'duplicate_detection',
            'is_duplicate' => $isDuplicate
        ];

        if ($isDuplicate) {
            Log::channel(self::LOG_CHANNEL)->warning('Duplicate transaction detected', $context);
        } else {
            Log::channel(self::LOG_CHANNEL)->debug('Transaction uniqueness verified', $context);
        }
    }

    /**
     * Log connection test results
     */
    public function logConnectionTest(EmailConfiguration $config, bool $success, ?Exception $exception = null): void
    {
        $context = [
            'email_config_id' => $config->id,
            'email_account' => $config->email,
            'imap_host' => $config->imap_host,
            'imap_port' => $config->imap_port,
            'operation' => 'connection_test',
            'success' => $success
        ];

        if ($exception) {
            Log::channel(self::LOG_CHANNEL)->error('Email connection test failed', array_merge($context, [
                'error' => $exception->getMessage()
            ]));
        } elseif ($success) {
            Log::channel(self::LOG_CHANNEL)->info('Email connection test successful', $context);
        } else {
            Log::channel(self::LOG_CHANNEL)->warning('Email connection test failed', $context);
        }
    }

    /**
     * Log retry operations
     */
    public function logRetryOperation(int $failedCount, int $successCount): void
    {
        Log::channel(self::LOG_CHANNEL)->info('Retry operation completed', [
            'failed_transactions_found' => $failedCount,
            'successful_retries' => $successCount,
            'operation' => 'retry_failed_transactions'
        ]);
    }

    /**
     * Log system status check
     */
    public function logSystemStatus(array $statusData): void
    {
        Log::channel(self::LOG_CHANNEL)->info('System status check', array_merge($statusData, [
            'operation' => 'system_status'
        ]));
    }

    /**
     * Log audit trail for transaction modifications
     */
    public function logAuditTrail(string $action, string $entityType, int $entityId, array $changes = [], ?int $userId = null): void
    {
        $context = [
            'action' => $action, // 'created', 'updated', 'deleted'
            'entity_type' => $entityType, // 'EmailTransaction', 'Income', 'Expense'
            'entity_id' => $entityId,
            'user_id' => $userId,
            'changes' => $changes,
            'operation' => 'audit_trail',
            'timestamp' => now()->toISOString()
        ];

        Log::channel(self::LOG_CHANNEL)->info('Audit trail entry', $context);
    }

    /**
     * Log critical system errors that require immediate attention
     */
    public function logCriticalError(string $component, Exception $exception, array $context = []): void
    {
        Log::channel(self::LOG_CHANNEL)->critical('Critical system error', array_merge($context, [
            'component' => $component,
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
            'operation' => 'critical_error'
        ]));
    }

    /**
     * Log performance metrics
     */
    public function logPerformanceMetrics(string $operation, float $executionTime, array $metrics = []): void
    {
        Log::channel(self::LOG_CHANNEL)->info('Performance metrics', array_merge($metrics, [
            'operation' => $operation,
            'execution_time_seconds' => $executionTime,
            'timestamp' => now()->toISOString()
        ]));
    }
}