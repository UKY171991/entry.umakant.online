<?php

namespace App\Contracts;

use App\DTOs\TransactionData;
use App\Models\EmailTransaction;

interface TransactionProcessorInterface
{
    /**
     * Process a transaction and create corresponding income/expense record
     *
     * @param TransactionData $data
     * @param mixed $email
     * @param EmailTransaction|null $emailTransaction
     * @return bool
     */
    public function processTransaction(TransactionData $data, $email = null, EmailTransaction $emailTransaction = null): bool;

    /**
     * Check if a transaction is a duplicate
     *
     * @param TransactionData $data
     * @return bool
     */
    public function isDuplicate(TransactionData $data): bool;

    /**
     * Retry failed transactions
     *
     * @return int Number of transactions retried
     */
    public function retryFailedTransactions(): int;

    /**
     * Create an EmailTransaction record
     *
     * @param TransactionData $data
     * @param array $emailData
     * @return EmailTransaction
     */
    public function createEmailTransaction(TransactionData $data, array $emailData): EmailTransaction;

    /**
     * Create an Income record from transaction data
     *
     * @param TransactionData $data
     * @param EmailTransaction $emailTransaction
     * @return \App\Models\Income|null
     */
    public function createIncomeRecord(TransactionData $data, EmailTransaction $emailTransaction): ?\App\Models\Income;

    /**
     * Create an Expense record from transaction data
     *
     * @param TransactionData $data
     * @param EmailTransaction $emailTransaction
     * @return \App\Models\Expense|null
     */
    public function createExpenseRecord(TransactionData $data, EmailTransaction $emailTransaction): ?\App\Models\Expense;
}