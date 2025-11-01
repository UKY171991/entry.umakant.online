<?php

namespace Tests\Unit;

use App\Services\TransactionProcessorService;
use App\Services\EmailTransactionLoggerService;
use App\Services\EmailTransactionNotificationService;
use App\DTOs\TransactionData;
use App\Models\EmailTransaction;
use App\Models\Income;
use App\Models\Expense;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;
use Mockery;

class TransactionProcessorServiceTest extends TestCase
{
    use RefreshDatabase;

    private TransactionProcessorService $processor;
    private $mockLogger;
    private $mockNotificationService;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->mockLogger = Mockery::mock(EmailTransactionLoggerService::class);
        $this->mockNotificationService = Mockery::mock(EmailTransactionNotificationService::class);
        
        $this->processor = new TransactionProcessorService(
            $this->mockLogger,
            $this->mockNotificationService
        );
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /** @test */
    public function it_can_detect_duplicate_transactions_in_email_transactions()
    {
        // Create an existing processed email transaction
        EmailTransaction::factory()->create([
            'processing_status' => 'processed',
            'transaction_type' => 'expense',
            'amount' => 1000.00,
            'transaction_date' => now(),
        ]);

        // Create transaction data that should be detected as duplicate
        $transactionData = new TransactionData(
            amount: 1000.00,
            transactionType: 'expense',
            transactionDate: now(),
            description: 'Test transaction'
        );

        $isDuplicate = $this->processor->isDuplicate($transactionData);

        $this->assertTrue($isDuplicate);
    }

    /** @test */
    public function it_can_detect_duplicate_transactions_in_income_table()
    {
        // Create an existing manual income record
        Income::factory()->create([
            'source' => 'manual',
            'received_amount' => 2000.00,
            'date' => now()->toDateString(),
        ]);

        // Create transaction data that should be detected as duplicate
        $transactionData = new TransactionData(
            amount: 2000.00,
            transactionType: 'income',
            transactionDate: now(),
            description: 'Test income'
        );

        $isDuplicate = $this->processor->isDuplicate($transactionData);

        $this->assertTrue($isDuplicate);
    }

    /** @test */
    public function it_can_detect_duplicate_transactions_in_expense_table()
    {
        // Create an existing manual expense record
        Expense::factory()->create([
            'source' => 'manual',
            'amount' => 500.00,
            'date' => now()->toDateString(),
        ]);

        // Create transaction data that should be detected as duplicate
        $transactionData = new TransactionData(
            amount: 500.00,
            transactionType: 'expense',
            transactionDate: now(),
            description: 'Test expense'
        );

        $isDuplicate = $this->processor->isDuplicate($transactionData);

        $this->assertTrue($isDuplicate);
    }

    /** @test */
    public function it_does_not_detect_duplicate_for_unique_transactions()
    {
        // Create transaction data that should not be a duplicate
        $transactionData = new TransactionData(
            amount: 1500.00,
            transactionType: 'expense',
            transactionDate: now(),
            description: 'Unique transaction'
        );

        $isDuplicate = $this->processor->isDuplicate($transactionData);

        $this->assertFalse($isDuplicate);
    }

    /** @test */
    public function it_considers_time_window_for_duplicate_detection()
    {
        // Create an existing transaction from 2 days ago
        EmailTransaction::factory()->create([
            'processing_status' => 'processed',
            'transaction_type' => 'expense',
            'amount' => 1000.00,
            'transaction_date' => now()->subDays(2),
        ]);

        // Create transaction data with same amount but within time window (24 hours default)
        $transactionData = new TransactionData(
            amount: 1000.00,
            transactionType: 'expense',
            transactionDate: now(),
            description: 'Test transaction'
        );

        $isDuplicate = $this->processor->isDuplicate($transactionData);

        // Should not be duplicate because it's outside the 24-hour window
        $this->assertFalse($isDuplicate);
    }

    /** @test */
    public function it_considers_amount_threshold_for_duplicate_detection()
    {
        // Create an existing transaction
        EmailTransaction::factory()->create([
            'processing_status' => 'processed',
            'transaction_type' => 'expense',
            'amount' => 1000.00,
            'transaction_date' => now(),
        ]);

        // Create transaction data with slightly different amount (outside threshold)
        $transactionData = new TransactionData(
            amount: 1000.50, // 0.50 difference, should be outside default 0.01 threshold
            transactionType: 'expense',
            transactionDate: now(),
            description: 'Test transaction'
        );

        $isDuplicate = $this->processor->isDuplicate($transactionData);

        // Should not be duplicate because amount difference exceeds threshold
        $this->assertFalse($isDuplicate);
    }

    /** @test */
    public function it_can_create_income_record_from_transaction_data()
    {
        $emailTransaction = EmailTransaction::factory()->create();
        
        $transactionData = new TransactionData(
            amount: 3000.00,
            transactionType: 'income',
            transactionDate: Carbon::parse('2024-01-15'),
            description: 'Salary payment'
        );

        $incomeRecord = $this->processor->createIncomeRecord($transactionData, $emailTransaction);

        $this->assertInstanceOf(Income::class, $incomeRecord);
        $this->assertEquals(3000.00, $incomeRecord->total_amount);
        $this->assertEquals(3000.00, $incomeRecord->received_amount);
        $this->assertEquals('2024-01-15', $incomeRecord->date);
        $this->assertEquals('email', $incomeRecord->source);
        $this->assertEquals($emailTransaction->id, $incomeRecord->email_transaction_id);
    }

    /** @test */
    public function it_can_create_expense_record_from_transaction_data()
    {
        $emailTransaction = EmailTransaction::factory()->create();
        
        $transactionData = new TransactionData(
            amount: 1500.00,
            transactionType: 'expense',
            transactionDate: Carbon::parse('2024-01-15'),
            description: 'Online shopping',
            merchantName: 'Amazon'
        );

        $expenseRecord = $this->processor->createExpenseRecord($transactionData, $emailTransaction);

        $this->assertInstanceOf(Expense::class, $expenseRecord);
        $this->assertEquals(1500.00, $expenseRecord->amount);
        $this->assertEquals('2024-01-15', $expenseRecord->date);
        $this->assertEquals('email', $expenseRecord->source);
        $this->assertEquals('paid', $expenseRecord->status);
        $this->assertEquals($emailTransaction->id, $expenseRecord->email_transaction_id);
        $this->assertStringContainsString('Amazon', $expenseRecord->expense_name);
    }

    /** @test */
    public function it_can_determine_expense_category_from_description()
    {
        $testCases = [
            'Payment to SWIGGY' => 'Food & Dining',
            'Transaction at AMAZON' => 'Shopping',
            'UBER ride payment' => 'Transportation',
            'Electricity bill payment' => 'Utilities',
            'Payment to APOLLO HOSPITAL' => 'Healthcare',
            'NETFLIX subscription' => 'Entertainment',
            'ATM withdrawal' => 'ATM',
            'UPI transfer to friend' => 'Transfer',
            'Unknown merchant payment' => 'Others',
        ];

        foreach ($testCases as $description => $expectedCategory) {
            $transactionData = new TransactionData(
                amount: 100.00,
                transactionType: 'expense',
                transactionDate: now(),
                description: $description
            );

            $emailTransaction = EmailTransaction::factory()->create();
            $expenseRecord = $this->processor->createExpenseRecord($transactionData, $emailTransaction);

            $this->assertEquals($expectedCategory, $expenseRecord->category, 
                "Failed to categorize: {$description}");
        }
    }

    /** @test */
    public function it_processes_income_transaction_successfully()
    {
        $this->mockLogger->shouldReceive('logDuplicateDetection')->once();
        $this->mockLogger->shouldReceive('logAuditTrail')->twice(); // Once for Income, once for EmailTransaction
        $this->mockLogger->shouldReceive('logTransactionProcessing')->once();
        $this->mockLogger->shouldReceive('logPerformanceMetrics')->once();

        $emailTransaction = EmailTransaction::factory()->create([
            'processing_status' => 'pending'
        ]);

        $transactionData = new TransactionData(
            amount: 5000.00,
            transactionType: 'income',
            transactionDate: now(),
            description: 'Salary credit'
        );

        $result = $this->processor->processTransaction($transactionData, null, $emailTransaction);

        $this->assertTrue($result);
        
        // Verify EmailTransaction was updated
        $emailTransaction->refresh();
        $this->assertEquals('processed', $emailTransaction->processing_status);
        $this->assertNotNull($emailTransaction->income_id);
        
        // Verify Income record was created
        $this->assertDatabaseHas('incomes', [
            'total_amount' => 5000.00,
            'source' => 'email',
            'email_transaction_id' => $emailTransaction->id
        ]);
    }

    /** @test */
    public function it_processes_expense_transaction_successfully()
    {
        $this->mockLogger->shouldReceive('logDuplicateDetection')->once();
        $this->mockLogger->shouldReceive('logAuditTrail')->twice(); // Once for Expense, once for EmailTransaction
        $this->mockLogger->shouldReceive('logTransactionProcessing')->once();
        $this->mockLogger->shouldReceive('logPerformanceMetrics')->once();

        $emailTransaction = EmailTransaction::factory()->create([
            'processing_status' => 'pending'
        ]);

        $transactionData = new TransactionData(
            amount: 2500.00,
            transactionType: 'expense',
            transactionDate: now(),
            description: 'Shopping payment'
        );

        $result = $this->processor->processTransaction($transactionData, null, $emailTransaction);

        $this->assertTrue($result);
        
        // Verify EmailTransaction was updated
        $emailTransaction->refresh();
        $this->assertEquals('processed', $emailTransaction->processing_status);
        $this->assertNotNull($emailTransaction->expense_id);
        
        // Verify Expense record was created
        $this->assertDatabaseHas('expenses', [
            'amount' => 2500.00,
            'source' => 'email',
            'email_transaction_id' => $emailTransaction->id
        ]);
    }

    /** @test */
    public function it_rejects_duplicate_transactions()
    {
        $this->mockLogger->shouldReceive('logDuplicateDetection')->twice(); // Once true, once false
        $this->mockLogger->shouldReceive('logAuditTrail')->once(); // Only for failed status

        // Create existing transaction
        EmailTransaction::factory()->create([
            'processing_status' => 'processed',
            'transaction_type' => 'expense',
            'amount' => 1000.00,
            'transaction_date' => now(),
        ]);

        $emailTransaction = EmailTransaction::factory()->create([
            'processing_status' => 'pending'
        ]);

        $transactionData = new TransactionData(
            amount: 1000.00,
            transactionType: 'expense',
            transactionDate: now(),
            description: 'Duplicate transaction'
        );

        $result = $this->processor->processTransaction($transactionData, null, $emailTransaction);

        $this->assertFalse($result);
        
        // Verify EmailTransaction was marked as failed
        $emailTransaction->refresh();
        $this->assertEquals('failed', $emailTransaction->processing_status);
        $this->assertStringContainsString('Duplicate', $emailTransaction->error_message);
    }

    /** @test */
    public function it_can_create_email_transaction_record()
    {
        $transactionData = new TransactionData(
            amount: 1500.00,
            transactionType: 'expense',
            transactionDate: Carbon::parse('2024-01-15'),
            description: 'Test transaction'
        );

        $emailData = [
            'email_configuration_id' => 1,
            'message_id' => 'test-message-123',
            'subject' => 'Transaction Alert',
            'sender' => 'bank@example.com',
            'raw_content' => 'Transaction details...'
        ];

        $emailTransaction = $this->processor->createEmailTransaction($transactionData, $emailData);

        $this->assertInstanceOf(EmailTransaction::class, $emailTransaction);
        $this->assertEquals(1500.00, $emailTransaction->amount);
        $this->assertEquals('expense', $emailTransaction->transaction_type);
        $this->assertEquals('pending', $emailTransaction->processing_status);
        $this->assertEquals('test-message-123', $emailTransaction->message_id);
    }

    /** @test */
    public function it_handles_database_errors_gracefully()
    {
        $this->mockLogger->shouldReceive('logDuplicateDetection')->once();
        $this->mockLogger->shouldReceive('logTransactionProcessing')->once();
        $this->mockLogger->shouldReceive('logAuditTrail')->once();
        $this->mockLogger->shouldReceive('logCriticalError')->once();

        // Create an EmailTransaction with invalid data that will cause database error
        $emailTransaction = new EmailTransaction();
        $emailTransaction->id = 999; // Non-existent ID

        $transactionData = new TransactionData(
            amount: 1000.00,
            transactionType: 'income',
            transactionDate: now(),
            description: 'Test transaction'
        );

        $result = $this->processor->processTransaction($transactionData, null, $emailTransaction);

        $this->assertFalse($result);
    }
}