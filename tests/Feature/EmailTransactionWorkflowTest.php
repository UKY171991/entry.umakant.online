<?php

namespace Tests\Feature;

use App\Models\EmailConfiguration;
use App\Models\EmailTransaction;
use App\Models\Income;
use App\Models\Expense;
use App\Services\EmailFetcherService;
use App\Services\EmailParserService;
use App\Services\TransactionProcessorService;
use App\Services\EmailTransactionLoggerService;
use App\Services\EmailTransactionNotificationService;
use App\DTOs\TransactionData;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;
use Mockery;

class EmailTransactionWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private $mockLogger;
    private $mockNotificationService;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->mockLogger = Mockery::mock(EmailTransactionLoggerService::class);
        $this->mockNotificationService = Mockery::mock(EmailTransactionNotificationService::class);
        
        $this->app->instance(EmailTransactionLoggerService::class, $this->mockLogger);
        $this->app->instance(EmailTransactionNotificationService::class, $this->mockNotificationService);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /** @test */
    public function it_can_process_complete_income_transaction_workflow()
    {
        // Setup mocks
        $this->mockLogger->shouldReceive('logEmailParsing')->once();
        $this->mockLogger->shouldReceive('logDuplicateDetection')->once();
        $this->mockLogger->shouldReceive('logAuditTrail')->twice();
        $this->mockLogger->shouldReceive('logTransactionProcessing')->once();
        $this->mockLogger->shouldReceive('logPerformanceMetrics')->once();

        // Create email configuration
        $config = EmailConfiguration::factory()->configured()->create();

        // Create email transaction record
        $emailTransaction = EmailTransaction::factory()->create([
            'email_configuration_id' => $config->id,
            'processing_status' => 'pending',
            'transaction_type' => null,
            'amount' => null
        ]);

        // Simulate email data
        $emailData = [
            'email_configuration_id' => $config->id,
            'message_id' => $emailTransaction->message_id,
            'subject' => 'Salary Credited - Rs. 50,000.00',
            'sender' => 'alerts@sbi.co.in',
            'raw_content' => 'Dear Customer, Your account XXXXXX1234 has been credited with Rs. 50,000.00 on 15-Jan-2024. Transaction: Salary Credit. Available Balance: Rs. 75,000.00. Ref: SAL123456789',
            'received_date' => '2024-01-15 09:00:00'
        ];

        // Parse the email
        $emailParser = new EmailParserService($this->mockLogger);
        $transactionData = $emailParser->parseTransaction($emailData);

        $this->assertNotNull($transactionData);
        $this->assertEquals(50000.00, $transactionData->amount);
        $this->assertEquals('income', $transactionData->transactionType);

        // Process the transaction
        $processor = new TransactionProcessorService($this->mockLogger, $this->mockNotificationService);
        $result = $processor->processTransaction($transactionData, $emailData, $emailTransaction);

        $this->assertTrue($result);

        // Verify email transaction was updated
        $emailTransaction->refresh();
        $this->assertEquals('processed', $emailTransaction->processing_status);
        $this->assertEquals('income', $emailTransaction->transaction_type);
        $this->assertEquals(50000.00, $emailTransaction->amount);
        $this->assertNotNull($emailTransaction->income_id);

        // Verify income record was created
        $income = Income::find($emailTransaction->income_id);
        $this->assertNotNull($income);
        $this->assertEquals(50000.00, $income->total_amount);
        $this->assertEquals(50000.00, $income->received_amount);
        $this->assertEquals('email', $income->source);
        $this->assertEquals($emailTransaction->id, $income->email_transaction_id);
    }

    /** @test */
    public function it_can_process_complete_expense_transaction_workflow()
    {
        // Setup mocks
        $this->mockLogger->shouldReceive('logEmailParsing')->once();
        $this->mockLogger->shouldReceive('logDuplicateDetection')->once();
        $this->mockLogger->shouldReceive('logAuditTrail')->twice();
        $this->mockLogger->shouldReceive('logTransactionProcessing')->once();
        $this->mockLogger->shouldReceive('logPerformanceMetrics')->once();

        // Create email configuration
        $config = EmailConfiguration::factory()->configured()->create();

        // Create email transaction record
        $emailTransaction = EmailTransaction::factory()->create([
            'email_configuration_id' => $config->id,
            'processing_status' => 'pending',
            'transaction_type' => null,
            'amount' => null
        ]);

        // Simulate email data
        $emailData = [
            'email_configuration_id' => $config->id,
            'message_id' => $emailTransaction->message_id,
            'subject' => 'Account Debited - Rs. 2,500.00',
            'sender' => 'alerts@hdfc.com',
            'raw_content' => 'Dear Customer, Rs. 2,500.00 debited from A/c XX1234 on 15-Jan-24 for UPI payment to AMAZON. Available Bal: Rs. 25,000.00. Ref No: UPI123456789',
            'received_date' => '2024-01-15 16:45:00'
        ];

        // Parse the email
        $emailParser = new EmailParserService($this->mockLogger);
        $transactionData = $emailParser->parseTransaction($emailData);

        $this->assertNotNull($transactionData);
        $this->assertEquals(2500.00, $transactionData->amount);
        $this->assertEquals('expense', $transactionData->transactionType);

        // Process the transaction
        $processor = new TransactionProcessorService($this->mockLogger, $this->mockNotificationService);
        $result = $processor->processTransaction($transactionData, $emailData, $emailTransaction);

        $this->assertTrue($result);

        // Verify email transaction was updated
        $emailTransaction->refresh();
        $this->assertEquals('processed', $emailTransaction->processing_status);
        $this->assertEquals('expense', $emailTransaction->transaction_type);
        $this->assertEquals(2500.00, $emailTransaction->amount);
        $this->assertNotNull($emailTransaction->expense_id);

        // Verify expense record was created
        $expense = Expense::find($emailTransaction->expense_id);
        $this->assertNotNull($expense);
        $this->assertEquals(2500.00, $expense->amount);
        $this->assertEquals('paid', $expense->status);
        $this->assertEquals('email', $expense->source);
        $this->assertEquals($emailTransaction->id, $expense->email_transaction_id);
        $this->assertEquals('Shopping', $expense->category); // Should categorize AMAZON as Shopping
    }

    /** @test */
    public function it_handles_duplicate_transaction_detection()
    {
        // Setup mocks
        $this->mockLogger->shouldReceive('logEmailParsing')->once();
        $this->mockLogger->shouldReceive('logDuplicateDetection')->twice();
        $this->mockLogger->shouldReceive('logAuditTrail')->once();

        // Create existing processed transaction
        $existingTransaction = EmailTransaction::factory()->create([
            'processing_status' => 'processed',
            'transaction_type' => 'expense',
            'amount' => 1000.00,
            'transaction_date' => now(),
        ]);

        // Create new email transaction with same details
        $config = EmailConfiguration::factory()->configured()->create();
        $emailTransaction = EmailTransaction::factory()->create([
            'email_configuration_id' => $config->id,
            'processing_status' => 'pending'
        ]);

        // Simulate duplicate email data
        $emailData = [
            'email_configuration_id' => $config->id,
            'message_id' => $emailTransaction->message_id,
            'subject' => 'Account Debited - Rs. 1,000.00',
            'sender' => 'alerts@bank.com',
            'raw_content' => 'Dear Customer, Rs. 1,000.00 debited from your account on ' . now()->format('d-M-Y'),
            'received_date' => now()->toDateTimeString()
        ];

        // Parse the email
        $emailParser = new EmailParserService($this->mockLogger);
        $transactionData = $emailParser->parseTransaction($emailData);

        // Process the transaction (should detect duplicate)
        $processor = new TransactionProcessorService($this->mockLogger, $this->mockNotificationService);
        $result = $processor->processTransaction($transactionData, $emailData, $emailTransaction);

        $this->assertFalse($result);

        // Verify email transaction was marked as failed
        $emailTransaction->refresh();
        $this->assertEquals('failed', $emailTransaction->processing_status);
        $this->assertStringContainsString('Duplicate', $emailTransaction->error_message);
    }

    /** @test */
    public function it_handles_parsing_failures_gracefully()
    {
        // Setup mocks
        $this->mockLogger->shouldReceive('logEmailParsing')->once();
        $this->mockLogger->shouldReceive('logCriticalError')->once();

        // Create email configuration
        $config = EmailConfiguration::factory()->configured()->create();

        // Simulate unparseable email data
        $emailData = [
            'email_configuration_id' => $config->id,
            'message_id' => 'unparseable-email-123',
            'subject' => 'Random Newsletter',
            'sender' => 'newsletter@example.com',
            'raw_content' => 'This is just a regular newsletter with no transaction information whatsoever.',
            'received_date' => '2024-01-15 12:00:00'
        ];

        // Parse the email (should fail)
        $emailParser = new EmailParserService($this->mockLogger);
        $transactionData = $emailParser->parseTransaction($emailData);

        $this->assertNull($transactionData);

        // Verify failed email transaction was created for manual review
        $this->assertDatabaseHas('email_transactions', [
            'message_id' => 'unparseable-email-123',
            'processing_status' => 'failed',
        ]);
    }

    /** @test */
    public function it_processes_multiple_bank_formats_correctly()
    {
        $this->mockLogger->shouldReceive('logEmailParsing')->times(3);

        $emailParser = new EmailParserService($this->mockLogger);

        // Test SBI format
        $sbiEmailData = [
            'message_id' => 'sbi-test',
            'subject' => 'SBI Account Transaction Alert',
            'sender' => 'sbi.alerts@sbi.co.in',
            'raw_content' => 'Dear Customer, Rs. 5,000.00 has been credited to your account XXXXXX1234 on 15-Jan-2024. Available balance: Rs. 45,000.00.',
            'received_date' => '2024-01-15 14:30:00'
        ];

        $sbiResult = $emailParser->parseTransaction($sbiEmailData);
        $this->assertNotNull($sbiResult);
        $this->assertEquals(5000.00, $sbiResult->amount);
        $this->assertEquals('income', $sbiResult->transactionType);

        // Test HDFC format
        $hdfcEmailData = [
            'message_id' => 'hdfc-test',
            'subject' => 'HDFC Bank Transaction Alert',
            'sender' => 'alerts@hdfcbank.com',
            'raw_content' => 'Dear Customer, INR 3,250.50 has been debited from your HDFC Bank Account XX1234 on 15-Jan-2024.',
            'received_date' => '2024-01-15 09:00:00'
        ];

        $hdfcResult = $emailParser->parseTransaction($hdfcEmailData);
        $this->assertNotNull($hdfcResult);
        $this->assertEquals(3250.50, $hdfcResult->amount);
        $this->assertEquals('expense', $hdfcResult->transactionType);

        // Test ICICI format
        $iciciEmailData = [
            'message_id' => 'icici-test',
            'subject' => 'ICICI Bank Account Alert',
            'sender' => 'no-reply@icicibank.com',
            'raw_content' => 'Dear Customer, Rs 1,750.25 credited to A/c XX9876 on 15-Jan-24. Available Bal: Rs 35,749.25',
            'received_date' => '2024-01-15 16:45:00'
        ];

        $iciciResult = $emailParser->parseTransaction($iciciEmailData);
        $this->assertNotNull($iciciResult);
        $this->assertEquals(1750.25, $iciciResult->amount);
        $this->assertEquals('income', $iciciResult->transactionType);
    }

    /** @test */
    public function it_categorizes_expenses_correctly_based_on_merchant()
    {
        $this->mockLogger->shouldReceive('logEmailParsing')->times(4);
        $this->mockLogger->shouldReceive('logDuplicateDetection')->times(4);
        $this->mockLogger->shouldReceive('logAuditTrail')->times(8);
        $this->mockLogger->shouldReceive('logTransactionProcessing')->times(4);
        $this->mockLogger->shouldReceive('logPerformanceMetrics')->times(4);

        $config = EmailConfiguration::factory()->configured()->create();
        $processor = new TransactionProcessorService($this->mockLogger, $this->mockNotificationService);
        $emailParser = new EmailParserService($this->mockLogger);

        $testCases = [
            ['merchant' => 'SWIGGY', 'expected_category' => 'Food & Dining'],
            ['merchant' => 'AMAZON', 'expected_category' => 'Shopping'],
            ['merchant' => 'UBER', 'expected_category' => 'Transportation'],
            ['merchant' => 'NETFLIX', 'expected_category' => 'Entertainment'],
        ];

        foreach ($testCases as $index => $testCase) {
            $emailTransaction = EmailTransaction::factory()->create([
                'email_configuration_id' => $config->id,
                'processing_status' => 'pending'
            ]);

            $emailData = [
                'email_configuration_id' => $config->id,
                'message_id' => "test-merchant-{$index}",
                'subject' => "Payment to {$testCase['merchant']}",
                'sender' => 'alerts@bank.com',
                'raw_content' => "Rs. 500.00 debited for payment to {$testCase['merchant']}",
                'received_date' => now()->toDateTimeString()
            ];

            $transactionData = $emailParser->parseTransaction($emailData);
            $transactionData->merchantName = $testCase['merchant'];
            $transactionData->description = "Payment to {$testCase['merchant']}";

            $result = $processor->processTransaction($transactionData, $emailData, $emailTransaction);

            $this->assertTrue($result);

            $emailTransaction->refresh();
            $expense = Expense::find($emailTransaction->expense_id);
            
            $this->assertEquals($testCase['expected_category'], $expense->category, 
                "Failed to categorize {$testCase['merchant']} correctly");
        }
    }

    /** @test */
    public function it_maintains_audit_trail_throughout_workflow()
    {
        // Setup comprehensive audit logging expectations
        $this->mockLogger->shouldReceive('logEmailParsing')->once();
        $this->mockLogger->shouldReceive('logDuplicateDetection')->once();
        $this->mockLogger->shouldReceive('logAuditTrail')->times(2); // Income creation + EmailTransaction update
        $this->mockLogger->shouldReceive('logTransactionProcessing')->once();
        $this->mockLogger->shouldReceive('logPerformanceMetrics')->once();

        $config = EmailConfiguration::factory()->configured()->create();
        $emailTransaction = EmailTransaction::factory()->create([
            'email_configuration_id' => $config->id,
            'processing_status' => 'pending'
        ]);

        $emailData = [
            'email_configuration_id' => $config->id,
            'message_id' => $emailTransaction->message_id,
            'subject' => 'Test Transaction',
            'sender' => 'test@bank.com',
            'raw_content' => 'Rs. 1,000.00 credited to your account',
            'received_date' => now()->toDateTimeString()
        ];

        // Process complete workflow
        $emailParser = new EmailParserService($this->mockLogger);
        $transactionData = $emailParser->parseTransaction($emailData);

        $processor = new TransactionProcessorService($this->mockLogger, $this->mockNotificationService);
        $result = $processor->processTransaction($transactionData, $emailData, $emailTransaction);

        $this->assertTrue($result);

        // Verify all audit expectations were met (handled by Mockery)
    }
}