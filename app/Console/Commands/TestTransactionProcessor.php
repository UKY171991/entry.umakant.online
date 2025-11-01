<?php

namespace App\Console\Commands;

use App\DTOs\TransactionData;
use App\Models\EmailConfiguration;
use App\Models\EmailTransaction;
use App\Services\TransactionProcessorService;
use Illuminate\Console\Command;
use Carbon\Carbon;

class TestTransactionProcessor extends Command
{
    protected $signature = 'email:test-processor 
                            {--amount=100.50 : Test transaction amount}
                            {--type=expense : Transaction type (income/expense)}
                            {--description=Test transaction : Transaction description}';

    protected $description = 'Test the transaction processor with sample data';

    public function handle(TransactionProcessorService $processor): int
    {
        try {
            $this->info('Testing Transaction Processor...');

            $amount = (float) $this->option('amount');
            $type = $this->option('type');
            $description = $this->option('description');

            // Validate type
            if (!in_array($type, ['income', 'expense'])) {
                $this->error('Type must be either "income" or "expense"');
                return self::FAILURE;
            }

            // Create test transaction data
            $transactionData = new TransactionData(
                type: $type,
                amount: $amount,
                date: Carbon::now(),
                description: $description
            );

            $this->info("Testing {$type} transaction of {$amount}");

            // Create a mock email configuration for testing
            $emailConfig = EmailConfiguration::first();
            if (!$emailConfig) {
                $this->warn('No email configuration found. Creating a test configuration...');
                $emailConfig = EmailConfiguration::create([
                    'name' => 'Test Configuration',
                    'email' => 'test@example.com',
                    'password' => 'test_password',
                    'imap_host' => 'imap.example.com',
                    'imap_port' => 993,
                    'imap_encryption' => 'ssl',
                    'is_active' => false, // Keep it inactive for testing
                    'bank_patterns' => []
                ]);
            }

            // Create a test email transaction record
            $emailTransaction = EmailTransaction::create([
                'email_configuration_id' => $emailConfig->id,
                'message_id' => 'test_' . time() . '@example.com',
                'subject' => 'Test Transaction Email',
                'sender' => 'bank@example.com',
                'transaction_type' => $type,
                'amount' => $amount,
                'transaction_date' => Carbon::now(),
                'description' => $description,
                'raw_content' => 'Test email content for transaction processing',
                'processing_status' => 'pending'
            ]);

            $this->info("Created test email transaction with ID: {$emailTransaction->id}");

            // Create mock email object
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

            // Test the processor
            $this->info('Processing transaction...');
            $success = $processor->processTransaction($transactionData, $mockEmail, $emailTransaction);

            if ($success) {
                $this->info('✓ Transaction processed successfully!');
                
                // Refresh the email transaction to get updated data
                $emailTransaction->refresh();
                
                $this->line("Status: {$emailTransaction->processing_status}");
                
                if ($type === 'income' && $emailTransaction->income_id) {
                    $this->line("Created Income record with ID: {$emailTransaction->income_id}");
                } elseif ($type === 'expense' && $emailTransaction->expense_id) {
                    $this->line("Created Expense record with ID: {$emailTransaction->expense_id}");
                }
                
            } else {
                $this->error('✗ Transaction processing failed');
                $emailTransaction->refresh();
                if ($emailTransaction->error_message) {
                    $this->error("Error: {$emailTransaction->error_message}");
                }
            }

            // Test duplicate detection
            $this->line('');
            $this->info('Testing duplicate detection...');
            $isDuplicate = $processor->isDuplicate($transactionData);
            
            if ($isDuplicate) {
                $this->info('✓ Duplicate detection working - transaction identified as duplicate');
            } else {
                $this->warn('⚠ Duplicate detection may not be working as expected');
            }

            return self::SUCCESS;

        } catch (\Exception $e) {
            $this->error("Test failed: {$e->getMessage()}");
            return self::FAILURE;
        }
    }
}