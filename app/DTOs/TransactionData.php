<?php

namespace App\DTOs;

use Carbon\Carbon;

class TransactionData
{
    public function __construct(
        public readonly float $amount,
        public readonly string $transactionType, // 'income' or 'expense'
        public readonly Carbon $transactionDate,
        public readonly ?string $description = null,
        public readonly ?string $merchantName = null,
        public readonly ?string $accountNumber = null,
        public readonly ?string $referenceNumber = null,
        public readonly ?string $currency = 'USD'
    ) {}

    /**
     * Create from array data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            amount: $data['amount'],
            transactionType: $data['transaction_type'],
            transactionDate: Carbon::parse($data['transaction_date']),
            description: $data['description'] ?? null,
            merchantName: $data['merchant_name'] ?? null,
            accountNumber: $data['account_number'] ?? null,
            referenceNumber: $data['reference_number'] ?? null,
            currency: $data['currency'] ?? 'USD'
        );
    }

    /**
     * Convert to array
     */
    public function toArray(): array
    {
        return [
            'amount' => $this->amount,
            'transaction_type' => $this->transactionType,
            'transaction_date' => $this->transactionDate->toDateString(),
            'description' => $this->description,
            'merchant_name' => $this->merchantName,
            'account_number' => $this->accountNumber,
            'reference_number' => $this->referenceNumber,
            'currency' => $this->currency
        ];
    }

    /**
     * Check if this is an income transaction
     */
    public function isIncome(): bool
    {
        return $this->transactionType === 'income';
    }

    /**
     * Check if this is an expense transaction
     */
    public function isExpense(): bool
    {
        return $this->transactionType === 'expense';
    }
}