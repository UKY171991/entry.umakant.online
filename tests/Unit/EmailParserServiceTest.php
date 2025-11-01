<?php

namespace Tests\Unit;

use App\Services\EmailParserService;
use App\Services\EmailTransactionLoggerService;
use App\DTOs\TransactionData;
use Tests\TestCase;
use Mockery;

class EmailParserServiceTest extends TestCase
{
    private EmailParserService $emailParser;
    private $mockLogger;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->mockLogger = Mockery::mock(EmailTransactionLoggerService::class);
        $this->emailParser = new EmailParserService($this->mockLogger);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /** @test */
    public function it_can_extract_amount_from_various_formats()
    {
        // Test different amount formats
        $testCases = [
            'Amount: Rs. 1,500.00' => 1500.00,
            'Debited with Rs 2500' => 2500.00,
            'Credited ₹ 3,250.50' => 3250.50,
            'Transaction of USD 100.75' => 100.75,
            'Payment of $50.25' => 50.25,
            'Balance: 10,000' => 10000.00,
            'Rs.15000 has been debited' => 15000.00,
            '₹ 999.99 credited to your account' => 999.99,
        ];

        foreach ($testCases as $content => $expectedAmount) {
            $extractedAmount = $this->emailParser->extractAmount($content);
            $this->assertEquals($expectedAmount, $extractedAmount, "Failed to extract amount from: {$content}");
        }
    }

    /** @test */
    public function it_returns_null_for_invalid_amounts()
    {
        $invalidCases = [
            'No amount mentioned here',
            'Amount: invalid',
            'Rs. abc',
            'Just some text without numbers',
            '', // Empty string
        ];

        foreach ($invalidCases as $content) {
            $extractedAmount = $this->emailParser->extractAmount($content);
            $this->assertNull($extractedAmount, "Should return null for: {$content}");
        }
    }

    /** @test */
    public function it_can_determine_transaction_type_for_income()
    {
        $incomeKeywords = [
            'Amount credited to your account',
            'Salary deposit received',
            'Refund processed successfully',
            'Cashback credited',
            'Interest payment received',
            'Dividend credited to account',
            'Money received from transfer',
            'Credit transaction completed',
        ];

        foreach ($incomeKeywords as $content) {
            $transactionType = $this->emailParser->determineTransactionType($content);
            $this->assertEquals('income', $transactionType, "Should detect income for: {$content}");
        }
    }

    /** @test */
    public function it_can_determine_transaction_type_for_expense()
    {
        $expenseKeywords = [
            'Amount debited from your account',
            'Payment made successfully',
            'ATM withdrawal completed',
            'Online purchase transaction',
            'Bill payment processed',
            'EMI deducted from account',
            'POS transaction at merchant',
            'Transfer sent successfully',
        ];

        foreach ($expenseKeywords as $content) {
            $transactionType = $this->emailParser->determineTransactionType($content);
            $this->assertEquals('expense', $transactionType, "Should detect expense for: {$content}");
        }
    }

    /** @test */
    public function it_defaults_to_expense_for_unclear_content()
    {
        $unclearContent = 'Some unclear transaction message without clear indicators';
        $transactionType = $this->emailParser->determineTransactionType($unclearContent);
        $this->assertEquals('expense', $transactionType);
    }

    /** @test */
    public function it_can_parse_complete_transaction_from_sbi_email()
    {
        $this->mockLogger->shouldReceive('logEmailParsing')->once();

        $emailData = [
            'message_id' => 'test-message-123',
            'subject' => 'SBI Account Transaction Alert',
            'sender' => 'sbi.alerts@sbi.co.in',
            'raw_content' => 'Dear Customer, Rs. 2,500.00 has been debited from your account XXXXXX1234 on 15-Jan-2024 for ATM withdrawal at SBI ATM. Available balance: Rs. 45,000.00. Ref: TXN123456789',
            'received_date' => '2024-01-15 14:30:00'
        ];

        $result = $this->emailParser->parseTransaction($emailData);

        $this->assertInstanceOf(TransactionData::class, $result);
        $this->assertEquals(2500.00, $result->amount);
        $this->assertEquals('expense', $result->transactionType);
        $this->assertNotNull($result->transactionDate);
    }

    /** @test */
    public function it_can_parse_complete_transaction_from_hdfc_email()
    {
        $this->mockLogger->shouldReceive('logEmailParsing')->once();

        $emailData = [
            'message_id' => 'test-message-456',
            'subject' => 'HDFC Bank Transaction Alert',
            'sender' => 'alerts@hdfcbank.com',
            'raw_content' => 'Dear Customer, INR 5,000.00 has been credited to your HDFC Bank Account XX1234 on 15-Jan-2024. Transaction: Salary Credit. Available Balance: INR 55,000.00',
            'received_date' => '2024-01-15 09:00:00'
        ];

        $result = $this->emailParser->parseTransaction($emailData);

        $this->assertInstanceOf(TransactionData::class, $result);
        $this->assertEquals(5000.00, $result->amount);
        $this->assertEquals('income', $result->transactionType);
        $this->assertNotNull($result->transactionDate);
    }

    /** @test */
    public function it_can_parse_complete_transaction_from_icici_email()
    {
        $this->mockLogger->shouldReceive('logEmailParsing')->once();

        $emailData = [
            'message_id' => 'test-message-789',
            'subject' => 'ICICI Bank Account Alert',
            'sender' => 'no-reply@icicibank.com',
            'raw_content' => 'Dear Customer, Rs 1,250.75 debited from A/c XX9876 on 15-Jan-24 for UPI payment to AMAZON. Ref No: 123456789012. Available Bal: Rs 25,749.25',
            'received_date' => '2024-01-15 16:45:00'
        ];

        $result = $this->emailParser->parseTransaction($emailData);

        $this->assertInstanceOf(TransactionData::class, $result);
        $this->assertEquals(1250.75, $result->amount);
        $this->assertEquals('expense', $result->transactionType);
        $this->assertNotNull($result->transactionDate);
    }

    /** @test */
    public function it_returns_null_for_unparseable_email()
    {
        $this->mockLogger->shouldReceive('logEmailParsing')->once();

        $emailData = [
            'message_id' => 'test-message-invalid',
            'subject' => 'Some random email',
            'sender' => 'random@example.com',
            'raw_content' => 'This email does not contain any transaction information',
            'received_date' => '2024-01-15 12:00:00'
        ];

        $result = $this->emailParser->parseTransaction($emailData);

        $this->assertNull($result);
    }

    /** @test */
    public function it_can_extract_merchant_name()
    {
        $testCases = [
            'Payment to AMAZON INDIA' => 'AMAZON INDIA',
            'Transaction at SWIGGY BANGALORE' => 'SWIGGY BANGALORE',
            'Purchase from FLIPKART' => 'FLIPKART',
            'Merchant: ZOMATO ONLINE' => 'ZOMATO ONLINE',
        ];

        foreach ($testCases as $content => $expectedMerchant) {
            $merchantName = $this->invokePrivateMethod($this->emailParser, 'extractMerchantName', [$content]);
            $this->assertEquals($expectedMerchant, $merchantName, "Failed to extract merchant from: {$content}");
        }
    }

    /** @test */
    public function it_can_extract_account_number()
    {
        $testCases = [
            'Account No: 1234567890' => '1234567890',
            'A/c XXXX1234' => 'XXXX1234',
            'from account 9876543210' => '9876543210',
            'Account: XX**5678' => 'XX**5678',
        ];

        foreach ($testCases as $content => $expectedAccount) {
            $accountNumber = $this->invokePrivateMethod($this->emailParser, 'extractAccountNumber', [$content]);
            $this->assertEquals($expectedAccount, $accountNumber, "Failed to extract account from: {$content}");
        }
    }

    /** @test */
    public function it_can_extract_reference_number()
    {
        $testCases = [
            'Ref No: TXN123456789' => 'TXN123456789',
            'Reference: UPI123456' => 'UPI123456',
            'Transaction ID: 987654321' => '987654321',
            'UTR: HDFC0001234' => 'HDFC0001234',
        ];

        foreach ($testCases as $content => $expectedRef) {
            $referenceNumber = $this->invokePrivateMethod($this->emailParser, 'extractReferenceNumber', [$content]);
            $this->assertEquals($expectedRef, $referenceNumber, "Failed to extract reference from: {$content}");
        }
    }

    /** @test */
    public function it_handles_parsing_exceptions_gracefully()
    {
        $this->mockLogger->shouldReceive('logEmailParsing')->once();
        $this->mockLogger->shouldReceive('logCriticalError')->once();

        // Create email data that will cause an exception during parsing
        $emailData = [
            'message_id' => 'test-exception',
            'subject' => null, // This might cause issues
            'sender' => null,
            'raw_content' => null,
            'received_date' => 'invalid-date-format'
        ];

        $result = $this->emailParser->parseTransaction($emailData);

        $this->assertNull($result);
    }

    /** @test */
    public function it_can_add_and_retrieve_bank_patterns()
    {
        $bankPatterns = [
            'sender_patterns' => ['testbank', 'test bank'],
            'amount_patterns' => ['/test\s*([0-9,]+\.?[0-9]*)/i'],
            'type_indicators' => [
                'income' => ['credited'],
                'expense' => ['debited']
            ]
        ];

        $this->emailParser->addBankPattern('testbank', $bankPatterns);
        $supportedBanks = $this->emailParser->getSupportedBanks();

        $this->assertContains('testbank', $supportedBanks);
    }

    /**
     * Helper method to invoke private methods for testing
     */
    private function invokePrivateMethod($object, $methodName, array $parameters = [])
    {
        $reflection = new \ReflectionClass(get_class($object));
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invokeArgs($object, $parameters);
    }
}