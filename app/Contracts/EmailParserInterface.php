<?php

namespace App\Contracts;

use App\DTOs\TransactionData;

interface EmailParserInterface
{
    /**
     * Parse transaction data from email content
     *
     * @param array $emailData
     * @return TransactionData|null
     */
    public function parseTransaction(array $emailData): ?TransactionData;

    /**
     * Add bank-specific parsing patterns
     *
     * @param string $bank
     * @param array $patterns
     * @return void
     */
    public function addBankPattern(string $bank, array $patterns): void;

    /**
     * Get list of supported banks
     *
     * @return array
     */
    public function getSupportedBanks(): array;

    /**
     * Extract amount from text content
     *
     * @param string $content
     * @return float|null
     */
    public function extractAmount(string $content): ?float;

    /**
     * Determine transaction type (income/expense)
     *
     * @param string $content
     * @return string|null
     */
    public function determineTransactionType(string $content): ?string;
}