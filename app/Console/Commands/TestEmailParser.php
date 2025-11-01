<?php

namespace App\Console\Commands;

use App\Contracts\EmailParserInterface;
use Illuminate\Console\Command;

class TestEmailParser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'email:test-parser';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test email parsing functionality with sample data';

    /**
     * Execute the console command.
     */
    public function handle(EmailParserInterface $parser): int
    {
        $this->info('Testing Email Parser Service');
        $this->line('');

        // Sample bank transaction emails
        $sampleEmails = [
            [
                'message_id' => 'test_001',
                'subject' => 'Account Debited - Rs. 1,250.00',
                'sender' => 'alerts@sbi.co.in',
                'raw_content' => 'Dear Customer, Your account XXXXXX1234 has been debited with Rs. 1,250.00 on 01/11/2025 at AMAZON INDIA. Available balance: Rs. 15,750.00. Ref: TXN123456789',
                'received_date' => '2025-11-01 10:30:00'
            ],
            [
                'message_id' => 'test_002', 
                'subject' => 'Salary Credited',
                'sender' => 'noreply@hdfc.com',
                'raw_content' => 'Your salary of INR 50,000.00 has been credited to your account on 01-Nov-2025. Transaction ID: SAL202511010001',
                'received_date' => '2025-11-01 09:00:00'
            ],
            [
                'message_id' => 'test_003',
                'subject' => 'UPI Payment',
                'sender' => 'alerts@icici.com', 
                'raw_content' => 'UPI payment of Rs 750 debited from account ending 5678 to merchant SWIGGY on 01/11/2025. UPI Ref: 123456789012',
                'received_date' => '2025-11-01 14:15:00'
            ]
        ];

        foreach ($sampleEmails as $index => $emailData) {
            $this->info("Testing Email " . ($index + 1) . ":");
            $this->line("Subject: {$emailData['subject']}");
            $this->line("Sender: {$emailData['sender']}");
            
            $transactionData = $parser->parseTransaction($emailData);
            
            if ($transactionData) {
                $this->info("✓ Parsing successful!");
                $this->line("  Amount: {$transactionData->amount}");
                $this->line("  Type: {$transactionData->transactionType}");
                $this->line("  Date: {$transactionData->transactionDate->format('Y-m-d')}");
                $this->line("  Description: {$transactionData->description}");
                if ($transactionData->merchantName) {
                    $this->line("  Merchant: {$transactionData->merchantName}");
                }
                if ($transactionData->referenceNumber) {
                    $this->line("  Reference: {$transactionData->referenceNumber}");
                }
            } else {
                $this->error("✗ Parsing failed!");
            }
            
            $this->line('');
        }

        // Test individual parsing methods
        $this->info('Testing individual parsing methods:');
        
        $testContent = 'Your account has been debited with Rs. 2,500.50 on 01/11/2025';
        $amount = $parser->extractAmount($testContent);
        $this->line("Amount extraction: " . ($amount ? "Rs. $amount" : "Failed"));
        
        $type = $parser->determineTransactionType($testContent);
        $this->line("Type detection: " . ($type ?: "Failed"));

        return 0;
    }
}