<?php

namespace App\Services;

use App\Contracts\EmailParserInterface;
use App\DTOs\TransactionData;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Exception;

class EmailParserService implements EmailParserInterface
{
    private array $bankPatterns = [];
    private array $amountPatterns = [];
    private array $datePatterns = [];
    private array $incomeKeywords = [];
    private array $expenseKeywords = [];
    private EmailTransactionLoggerService $logger;

    public function __construct(EmailTransactionLoggerService $logger)
    {
        $this->logger = $logger;
        $this->initializePatterns();
    }

    /**
     * Parse transaction data from email content
     */
    public function parseTransaction(array $emailData): ?TransactionData
    {
        $messageId = $emailData['message_id'] ?? 'unknown';
        
        try {
            $content = $emailData['raw_content'] ?? '';
            $subject = $emailData['subject'] ?? '';
            $sender = $emailData['sender'] ?? '';
            $fullContent = $content . ' ' . $subject;
            
            // Extract amount
            $amount = $this->extractAmount($fullContent);
            if (!$amount) {
                $this->logger->logEmailParsing($messageId, null, new Exception('Could not extract amount from email'));
                return null;
            }

            // Determine transaction type
            $transactionType = $this->determineTransactionType($fullContent);
            if (!$transactionType) {
                $this->logger->logEmailParsing($messageId, null, new Exception('Could not determine transaction type from email'));
                return null;
            }

            // Extract transaction date
            $transactionDate = $this->extractTransactionDate($content, $emailData['received_date'] ?? null);

            // Extract description and merchant info
            $description = $this->extractDescription($content, $subject);
            $merchantName = $this->extractMerchantName($content);
            $accountNumber = $this->extractAccountNumber($content);
            $referenceNumber = $this->extractReferenceNumber($content);

            $transactionData = new TransactionData(
                amount: $amount,
                transactionType: $transactionType,
                transactionDate: $transactionDate,
                description: $description,
                merchantName: $merchantName,
                accountNumber: $accountNumber,
                referenceNumber: $referenceNumber
            );

            // Log successful parsing
            $this->logger->logEmailParsing($messageId, [
                'amount' => $amount,
                'type' => $transactionType,
                'date' => $transactionDate->toDateString(),
                'description' => $description
            ]);

            return $transactionData;

        } catch (Exception $e) {
            $this->logger->logEmailParsing($messageId, null, $e);
            
            // Store unparseable email for manual review
            $this->storeUnparseableEmail($emailData, $e);
            
            return null;
        }
    }  
  /**
     * Extract amount from text content
     */
    public function extractAmount(string $content): ?float
    {
        $content = strtolower($content);
        
        // Common amount patterns with various currency symbols and formats
        $patterns = [
            '/(?:amount|sum|total|balance|rs\.?|₹|usd|\$|€|£)\s*:?\s*([0-9,]+\.?[0-9]*)/i',
            '/([0-9,]+\.?[0-9]*)\s*(?:rs\.?|₹|usd|\$|€|£)/i',
            '/(?:debited|credited|paid|received|transferred)\s*(?:with|for|of)?\s*(?:rs\.?|₹|usd|\$|€|£)?\s*([0-9,]+\.?[0-9]*)/i',
            '/(?:rs\.?|₹|usd|\$|€|£)\s*([0-9,]+\.?[0-9]*)/i',
            '/\b([0-9,]+\.[0-9]{2})\b/i', // Decimal amounts
            '/\b([0-9,]{4,})\b/i' // Large numbers (likely amounts)
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $content, $matches)) {
                $amountStr = str_replace(',', '', $matches[1]);
                $amount = floatval($amountStr);
                
                // Validate amount (should be reasonable)
                if ($amount > 0 && $amount < 10000000) { // Max 10 million
                    return $amount;
                }
            }
        }

        return null;
    }

    /**
     * Determine transaction type (income/expense)
     */
    public function determineTransactionType(string $content): ?string
    {
        $content = strtolower($content);

        // Income indicators
        $incomeKeywords = [
            'credited', 'deposit', 'received', 'salary', 'payment received',
            'refund', 'cashback', 'interest', 'dividend', 'bonus',
            'transfer received', 'money received', 'credit'
        ];

        // Expense indicators  
        $expenseKeywords = [
            'debited', 'debit', 'withdrawal', 'payment', 'purchase',
            'transaction', 'spent', 'paid', 'transfer', 'atm',
            'pos', 'online payment', 'bill payment', 'emi'
        ];

        // Check for income keywords
        foreach ($incomeKeywords as $keyword) {
            if (str_contains($content, $keyword)) {
                return 'income';
            }
        }

        // Check for expense keywords
        foreach ($expenseKeywords as $keyword) {
            if (str_contains($content, $keyword)) {
                return 'expense';
            }
        }

        // Default to expense if unclear
        return 'expense';
    }

    /**
     * Add bank-specific parsing patterns
     */
    public function addBankPattern(string $bank, array $patterns): void
    {
        $this->bankPatterns[$bank] = $patterns;
    }

    /**
     * Get list of supported banks
     */
    public function getSupportedBanks(): array
    {
        return array_keys($this->bankPatterns);
    }    /**
 
    * Extract transaction date from content
     */
    private function extractTransactionDate(string $content, ?string $receivedDate = null): Carbon
    {
        // Try to find date patterns in content
        $datePatterns = [
            '/(?:on|date|transaction date|dated)\s*:?\s*([0-9]{1,2}[-\/][0-9]{1,2}[-\/][0-9]{2,4})/i',
            '/([0-9]{1,2}[-\/][0-9]{1,2}[-\/][0-9]{2,4})/i',
            '/([0-9]{2,4}[-\/][0-9]{1,2}[-\/][0-9]{1,2})/i'
        ];

        foreach ($datePatterns as $pattern) {
            if (preg_match($pattern, $content, $matches)) {
                try {
                    return Carbon::parse($matches[1]);
                } catch (Exception $e) {
                    continue;
                }
            }
        }

        // Fallback to received date or current date
        if ($receivedDate) {
            try {
                return Carbon::parse($receivedDate);
            } catch (Exception $e) {
                // Continue to default
            }
        }

        return Carbon::now();
    }

    /**
     * Extract description from content
     */
    private function extractDescription(string $content, string $subject): string
    {
        // Use subject as primary description
        $description = trim($subject);
        
        // Try to extract more detailed description from content
        $descPatterns = [
            '/(?:description|details|particulars)\s*:?\s*([^\n\r]{10,100})/i',
            '/(?:at|to|from)\s+([A-Za-z0-9\s]{5,50})/i'
        ];

        foreach ($descPatterns as $pattern) {
            if (preg_match($pattern, $content, $matches)) {
                $extracted = trim($matches[1]);
                if (strlen($extracted) > strlen($description)) {
                    $description = $extracted;
                }
            }
        }

        return substr($description, 0, 255); // Limit length
    }

    /**
     * Extract merchant name from content
     */
    private function extractMerchantName(string $content): ?string
    {
        $patterns = [
            '/(?:merchant|vendor|store|shop)\s*:?\s*([A-Za-z0-9\s]{3,50})/i',
            '/(?:at|to)\s+([A-Z][A-Za-z0-9\s]{2,30})/i'
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $content, $matches)) {
                return trim($matches[1]);
            }
        }

        return null;
    }

    /**
     * Extract account number from content
     */
    private function extractAccountNumber(string $content): ?string
    {
        $patterns = [
            '/(?:account|a\/c|acc)\s*(?:no|number)?\s*:?\s*([0-9X*]{4,20})/i',
            '/(?:from|to)\s+(?:account|a\/c)\s*:?\s*([0-9X*]{4,20})/i'
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $content, $matches)) {
                return trim($matches[1]);
            }
        }

        return null;
    }

    /**
     * Extract reference number from content
     */
    private function extractReferenceNumber(string $content): ?string
    {
        $patterns = [
            '/(?:ref|reference|txn|transaction)\s*(?:no|number|id)?\s*:?\s*([A-Za-z0-9]{6,30})/i',
            '/(?:utr|rrn|stan)\s*:?\s*([A-Za-z0-9]{6,30})/i'
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $content, $matches)) {
                return trim($matches[1]);
            }
        }

        return null;
    }

    /**
     * Store unparseable email for manual review
     */
    private function storeUnparseableEmail(array $emailData, Exception $exception): void
    {
        try {
            // Create a failed email transaction record for manual review
            EmailTransaction::create([
                'email_configuration_id' => $emailData['email_configuration_id'] ?? null,
                'message_id' => $emailData['message_id'] ?? 'unknown',
                'subject' => $emailData['subject'] ?? '',
                'sender' => $emailData['sender'] ?? '',
                'raw_content' => $emailData['raw_content'] ?? '',
                'processing_status' => 'failed',
                'error_message' => 'Parsing failed: ' . $exception->getMessage(),
                'transaction_type' => null,
                'amount' => null,
                'transaction_date' => now(),
                'description' => 'Failed to parse - requires manual review'
            ]);
        } catch (Exception $e) {
            $this->logger->logCriticalError('EmailParserService', $e, [
                'context' => 'Failed to store unparseable email',
                'original_message_id' => $emailData['message_id'] ?? 'unknown'
            ]);
        }
    }

    /**
     * Initialize default parsing patterns
     */
    private function initializePatterns(): void
    {
        // Initialize with common bank patterns
        $this->bankPatterns = [
            'sbi' => [
                'sender_patterns' => ['sbi', 'state bank'],
                'amount_patterns' => ['/rs\.?\s*([0-9,]+\.?[0-9]*)/i'],
                'type_indicators' => [
                    'income' => ['credited', 'deposit'],
                    'expense' => ['debited', 'withdrawal']
                ]
            ],
            'hdfc' => [
                'sender_patterns' => ['hdfc'],
                'amount_patterns' => ['/inr\s*([0-9,]+\.?[0-9]*)/i'],
                'type_indicators' => [
                    'income' => ['credited'],
                    'expense' => ['debited']
                ]
            ],
            'icici' => [
                'sender_patterns' => ['icici'],
                'amount_patterns' => ['/rs\s*([0-9,]+\.?[0-9]*)/i'],
                'type_indicators' => [
                    'income' => ['credit'],
                    'expense' => ['debit']
                ]
            ]
        ];
    }
}