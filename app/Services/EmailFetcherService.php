<?php

namespace App\Services;

use App\Contracts\EmailFetcherInterface;
use App\Models\EmailConfiguration;
use App\Models\EmailTransaction;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use PhpImap\Mailbox;
use PhpImap\IncomingMail;
use Exception;

class EmailFetcherService implements EmailFetcherInterface
{
    private array $processedMessageIds = [];
    private int $maxRetries;
    private int $retryDelay;
    private EmailTransactionLoggerService $logger;
    private EmailTransactionNotificationService $notificationService;

    public function __construct(
        EmailTransactionLoggerService $logger,
        EmailTransactionNotificationService $notificationService
    ) {
        $this->maxRetries = config('email_transactions.error_handling.max_retries', 3);
        $this->retryDelay = config('email_transactions.error_handling.retry_delay', 5);
        $this->logger = $logger;
        $this->notificationService = $notificationService;
        $this->loadProcessedMessageIds();
    }

    /**
     * Fetch new emails from the configured email account
     */
    public function fetchNewEmails(EmailConfiguration $config): Collection
    {
        $startTime = microtime(true);
        $emails = collect();
        
        if (!$config->isConfigured() || !$config->is_active) {
            $this->logger->logEmailFetch($config, 0, new Exception('Configuration not properly set up or inactive'));
            return $emails;
        }

        $retryCount = 0;
        $lastException = null;
        
        while ($retryCount < $this->maxRetries) {
            try {
                $mailbox = $this->createMailboxConnection($config);
                $mailIds = $mailbox->searchMailbox('SINCE "' . $this->getLastSyncDate($config) . '"');
                
                foreach ($mailIds as $mailId) {
                    $mail = $mailbox->getMail($mailId);
                    
                    if ($this->shouldProcessEmail($mail, $config)) {
                        $emails->push($this->formatEmailData($mail, $config));
                    }
                }
                
                $mailbox->disconnect();
                $config->updateLastSync();
                
                // Log successful fetch
                $this->logger->logEmailFetch($config, $emails->count());
                
                // Log performance metrics
                $executionTime = microtime(true) - $startTime;
                $this->logger->logPerformanceMetrics('email_fetch', $executionTime, [
                    'email_config_id' => $config->id,
                    'emails_fetched' => $emails->count(),
                    'retry_count' => $retryCount
                ]);
                
                break;
                
            } catch (Exception $e) {
                $lastException = $e;
                $retryCount++;
                
                $this->logger->logEmailFetch($config, 0, $e);
                
                if ($retryCount >= $this->maxRetries) {
                    // Send notification for connection failure
                    $this->notificationService->notifyConnectionFailure($config, $e);
                    
                    // Log critical error
                    $this->logger->logCriticalError('EmailFetcherService', $e, [
                        'email_config_id' => $config->id,
                        'retry_count' => $retryCount
                    ]);
                    
                    throw $e;
                }
                
                // Exponential backoff
                $delay = config('email_transactions.error_handling.exponential_backoff', true) 
                    ? $this->retryDelay * $retryCount 
                    : $this->retryDelay;
                    
                sleep($delay);
            }
        }

        return $emails;
    }   
 /**
     * Mark an email as processed to prevent duplicate processing
     */
    public function markAsProcessed(string $messageId): void
    {
        if (!in_array($messageId, $this->processedMessageIds)) {
            $this->processedMessageIds[] = $messageId;
            $this->saveProcessedMessageIds();
        }
    }

    /**
     * Test connection to email account
     */
    public function testConnection(EmailConfiguration $config): bool
    {
        try {
            $mailbox = $this->createMailboxConnection($config);
            $mailbox->getMailboxInfo();
            $mailbox->disconnect();
            
            $this->logger->logConnectionTest($config, true);
            return true;
            
        } catch (Exception $e) {
            $this->logger->logConnectionTest($config, false, $e);
            return false;
        }
    }

    /**
     * Get the list of processed message IDs
     */
    public function getProcessedMessageIds(): array
    {
        return $this->processedMessageIds;
    }

    /**
     * Create IMAP mailbox connection
     */
    private function createMailboxConnection(EmailConfiguration $config): Mailbox
    {
        $connectionString = sprintf(
            '{%s:%d/imap/%s}INBOX',
            $config->imap_host,
            $config->imap_port,
            $config->imap_encryption
        );

        return new Mailbox(
            $connectionString,
            $config->email,
            $config->password,
            storage_path('app/email_attachments')
        );
    }

    /**
     * Check if email should be processed based on sender patterns
     */
    private function shouldProcessEmail(IncomingMail $mail, EmailConfiguration $config = null): bool
    {
        // Skip if already processed
        if (in_array($mail->messageId, $this->processedMessageIds)) {
            return false;
        }

        // Check if sender matches bank patterns
        $sender = strtolower($mail->fromAddress);
        $subject = strtolower($mail->subject);
        
        // Use configuration-specific patterns if available
        $bankPatterns = [];
        if ($config && $config->bank_patterns) {
            $bankPatterns = array_merge($bankPatterns, $config->bank_patterns);
        }
        
        // Default bank email patterns
        $defaultPatterns = [
            'bank', 'credit', 'debit', 'transaction', 'payment', 'transfer',
            'account', 'statement', 'balance', 'deposit', 'withdrawal',
            'noreply@', 'alerts@', 'notifications@'
        ];
        
        $bankPatterns = array_merge($bankPatterns, $defaultPatterns);

        foreach ($bankPatterns as $pattern) {
            if (str_contains($sender, strtolower($pattern)) || str_contains($subject, strtolower($pattern))) {
                return true;
            }
        }

        return false;
    }    /**

     * Format email data for processing
     */
    private function formatEmailData(IncomingMail $mail, EmailConfiguration $config): array
    {
        return [
            'email_configuration_id' => $config->id,
            'message_id' => $mail->messageId,
            'subject' => $mail->subject,
            'sender' => $mail->fromAddress,
            'raw_content' => $mail->textPlain ?: $mail->textHtml,
            'received_date' => $mail->date,
            'processing_status' => 'pending'
        ];
    }

    /**
     * Get last sync date for email filtering
     */
    private function getLastSyncDate(EmailConfiguration $config): string
    {
        if ($config->last_sync_at) {
            return $config->last_sync_at->format('d-M-Y');
        }
        
        // Default to 30 days ago for first sync
        return now()->subDays(30)->format('d-M-Y');
    }

    /**
     * Load processed message IDs from storage
     */
    private function loadProcessedMessageIds(): void
    {
        $processedIds = EmailTransaction::pluck('message_id')->toArray();
        $this->processedMessageIds = $processedIds;
    }

    /**
     * Save processed message IDs (not needed as we store in database)
     */
    private function saveProcessedMessageIds(): void
    {
        // Message IDs are stored in the email_transactions table
        // This method is kept for interface compatibility
    }
}