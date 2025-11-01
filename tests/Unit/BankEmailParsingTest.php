<?php

namespace Tests\Unit;

use App\Services\EmailParserService;
use App\Services\EmailTransactionLoggerService;
use Tests\TestCase;
use Tests\Helpers\MockEmailDataHelper;
use Mockery;

class BankEmailParsingTest extends TestCase
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
    public function it_can_parse_sbi_debit_transactions()
    {
        $this->mockLogger->shouldReceive('logEmailParsing')->once();

        $emailData = MockEmailDataHelper::generateSBIDebitEmail(2500.00, 'AMAZON');
        $result = $this->emailParser->parseTransaction($emailData);

        $this->assertNotNull($result);
        $this->assertEquals(2500.00, $result->amount);
        $this->assertEquals('expense', $result->transactionType);
        $this->assertNotNull($result->transactionDate);
        $this->assertStringContainsString('AMAZON', $result->description);
    }

    /** @test */
    public function it_can_parse_sbi_credit_transactions()
    {
        $this->mockLogger->shouldReceive('logEmailParsing')->once();

        $emailData = MockEmailDataHelper::generateSBICreditEmail(50000.00, 'Salary Credit');
        $result = $this->emailParser->parseTransaction($emailData);

        $this->assertNotNull($result);
        $this->assertEquals(50000.00, $result->amount);
        $this->assertEquals('income', $result->transactionType);
        $this->assertNotNull($result->transactionDate);
        $this->assertStringContainsString('Salary', $result->description);
    }

    /** @test */
    public function it_can_parse_hdfc_debit_transactions()
    {
        $this->mockLogger->shouldReceive('logEmailParsing')->once();

        $emailData = MockEmailDataHelper::generateHDFCDebitEmail(1500.00, 'SWIGGY');
        $result = $this->emailParser->parseTransaction($emailData);

        $this->assertNotNull($result);
        $this->assertEquals(1500.00, $result->amount);
        $this->assertEquals('expense', $result->transactionType);
        $this->assertNotNull($result->transactionDate);
    }

    /** @test */
    public function it_can_parse_hdfc_credit_transactions()
    {
        $this->mockLogger->shouldReceive('logEmailParsing')->once();

        $emailData = MockEmailDataHelper::generateHDFCCreditEmail(25000.00, 'Salary Credit');
        $result = $this->emailParser->parseTransaction($emailData);

        $this->assertNotNull($result);
        $this->assertEquals(25000.00, $result->amount);
        $this->assertEquals('income', $result->transactionType);
        $this->assertNotNull($result->transactionDate);
    }

    /** @test */
    public function it_can_parse_icici_debit_transactions()
    {
        $this->mockLogger->shouldReceive('logEmailParsing')->once();

        $emailData = MockEmailDataHelper::generateICICIDebitEmail(800.00, 'UBER');
        $result = $this->emailParser->parseTransaction($emailData);

        $this->assertNotNull($result);
        $this->assertEquals(800.00, $result->amount);
        $this->assertEquals('expense', $result->transactionType);
        $this->assertNotNull($result->transactionDate);
    }

    /** @test */
    public function it_can_parse_icici_credit_transactions()
    {
        $this->mockLogger->shouldReceive('logEmailParsing')->once();

        $emailData = MockEmailDataHelper::generateICICICreditEmail(15000.00, 'Interest Credit');
        $result = $this->emailParser->parseTransaction($emailData);

        $this->assertNotNull($result);
        $this->assertEquals(15000.00, $result->amount);
        $this->assertEquals('income', $result->transactionType);
        $this->assertNotNull($result->transactionDate);
    }

    /** @test */
    public function it_can_parse_axis_bank_transactions()
    {
        $this->mockLogger->shouldReceive('logEmailParsing')->twice();

        // Test debit transaction
        $debitEmailData = MockEmailDataHelper::generateAxisDebitEmail(3200.00, 'FLIPKART');
        $debitResult = $this->emailParser->parseTransaction($debitEmailData);

        $this->assertNotNull($debitResult);
        $this->assertEquals(3200.00, $debitResult->amount);
        $this->assertEquals('expense', $debitResult->transactionType);

        // Test credit transaction
        $creditEmailData = MockEmailDataHelper::generateAxisCreditEmail(35000.00, 'Salary Credit');
        $creditResult = $this->emailParser->parseTransaction($creditEmailData);

        $this->assertNotNull($creditResult);
        $this->assertEquals(35000.00, $creditResult->amount);
        $this->assertEquals('income', $creditResult->transactionType);
    }

    /** @test */
    public function it_can_parse_atm_withdrawal_transactions()
    {
        $this->mockLogger->shouldReceive('logEmailParsing')->once();

        $emailData = MockEmailDataHelper::generateATMWithdrawalEmail(5000.00, 'SBI ATM CONNAUGHT PLACE');
        $result = $this->emailParser->parseTransaction($emailData);

        $this->assertNotNull($result);
        $this->assertEquals(5000.00, $result->amount);
        $this->assertEquals('expense', $result->transactionType);
        $this->assertNotNull($result->transactionDate);
    }

    /** @test */
    public function it_can_parse_bill_payment_transactions()
    {
        $this->mockLogger->shouldReceive('logEmailParsing')->once();

        $emailData = MockEmailDataHelper::generateBillPaymentEmail(1200.00, 'ELECTRICITY BOARD');
        $result = $this->emailParser->parseTransaction($emailData);

        $this->assertNotNull($result);
        $this->assertEquals(1200.00, $result->amount);
        $this->assertEquals('expense', $result->transactionType);
        $this->assertNotNull($result->transactionDate);
    }

    /** @test */
    public function it_returns_null_for_non_bank_emails()
    {
        $this->mockLogger->shouldReceive('logEmailParsing')->once();

        $emailData = MockEmailDataHelper::generateNonBankEmail();
        $result = $this->emailParser->parseTransaction($emailData);

        $this->assertNull($result);
    }

    /** @test */
    public function it_handles_malformed_bank_emails()
    {
        $this->mockLogger->shouldReceive('logEmailParsing')->once();
        $this->mockLogger->shouldReceive('logCriticalError')->once();

        $emailData = MockEmailDataHelper::generateMalformedBankEmail();
        $result = $this->emailParser->parseTransaction($emailData);

        $this->assertNull($result);
    }

    /** @test */
    public function it_can_parse_all_bank_formats_in_batch()
    {
        $this->mockLogger->shouldReceive('logEmailParsing')->times(8);

        $bankEmails = MockEmailDataHelper::generateBankEmails();
        $results = [];

        foreach ($bankEmails as $bankType => $emailData) {
            $result = $this->emailParser->parseTransaction($emailData);
            $results[$bankType] = $result;
        }

        // Verify all bank emails were parsed successfully
        foreach ($results as $bankType => $result) {
            $this->assertNotNull($result, "Failed to parse {$bankType} email");
            $this->assertGreaterThan(0, $result->amount, "Invalid amount for {$bankType}");
            $this->assertContains($result->transactionType, ['income', 'expense'], "Invalid type for {$bankType}");
        }
    }

    /** @test */
    public function it_extracts_correct_amounts_from_different_formats()
    {
        $this->mockLogger->shouldReceive('logEmailParsing')->times(6);

        $testCases = [
            ['email' => MockEmailDataHelper::generateSBIDebitEmail(1234.56), 'expected' => 1234.56],
            ['email' => MockEmailDataHelper::generateHDFCCreditEmail(50000.00), 'expected' => 50000.00],
            ['email' => MockEmailDataHelper::generateICICIDebitEmail(999.99), 'expected' => 999.99],
            ['email' => MockEmailDataHelper::generateAxisCreditEmail(75000.25), 'expected' => 75000.25],
            ['email' => MockEmailDataHelper::generateATMWithdrawalEmail(2000.00), 'expected' => 2000.00],
            ['email' => MockEmailDataHelper::generateBillPaymentEmail(1500.75), 'expected' => 1500.75],
        ];

        foreach ($testCases as $testCase) {
            $result = $this->emailParser->parseTransaction($testCase['email']);
            $this->assertNotNull($result);
            $this->assertEquals($testCase['expected'], $result->amount, 
                "Amount mismatch for email: " . $testCase['email']['subject']);
        }
    }

    /** @test */
    public function it_correctly_identifies_transaction_types()
    {
        $this->mockLogger->shouldReceive('logEmailParsing')->times(8);

        $bankEmails = MockEmailDataHelper::generateBankEmails();
        
        $expectedTypes = [
            'sbi_debit' => 'expense',
            'sbi_credit' => 'income',
            'hdfc_debit' => 'expense',
            'hdfc_credit' => 'income',
            'icici_debit' => 'expense',
            'icici_credit' => 'income',
            'axis_debit' => 'expense',
            'axis_credit' => 'income',
        ];

        foreach ($bankEmails as $bankType => $emailData) {
            $result = $this->emailParser->parseTransaction($emailData);
            $this->assertNotNull($result);
            $this->assertEquals($expectedTypes[$bankType], $result->transactionType, 
                "Transaction type mismatch for {$bankType}");
        }
    }

    /** @test */
    public function it_handles_various_currency_formats()
    {
        $this->mockLogger->shouldReceive('logEmailParsing')->times(5);

        $currencyTestCases = [
            'Rs. 1,500.00' => 1500.00,
            'INR 2,500.50' => 2500.50,
            'Rs 3,000' => 3000.00,
            '₹ 4,250.75' => 4250.75,
            'USD 100.25' => 100.25,
        ];

        foreach ($currencyTestCases as $currencyText => $expectedAmount) {
            $emailData = [
                'message_id' => 'currency_test_' . uniqid(),
                'subject' => 'Transaction Alert',
                'sender' => 'alerts@bank.com',
                'raw_content' => "Dear Customer, {$currencyText} has been debited from your account.",
                'received_date' => now()->toDateTimeString()
            ];

            $result = $this->emailParser->parseTransaction($emailData);
            $this->assertNotNull($result);
            $this->assertEquals($expectedAmount, $result->amount, 
                "Failed to parse currency format: {$currencyText}");
        }
    }

    /** @test */
    public function it_extracts_merchant_information_when_available()
    {
        $this->mockLogger->shouldReceive('logEmailParsing')->times(4);

        $merchantTestCases = [
            ['merchant' => 'AMAZON', 'email' => MockEmailDataHelper::generateSBIDebitEmail(1000, 'AMAZON')],
            ['merchant' => 'SWIGGY', 'email' => MockEmailDataHelper::generateHDFCDebitEmail(500, 'SWIGGY')],
            ['merchant' => 'UBER', 'email' => MockEmailDataHelper::generateICICIDebitEmail(300, 'UBER')],
            ['merchant' => 'FLIPKART', 'email' => MockEmailDataHelper::generateAxisDebitEmail(2000, 'FLIPKART')],
        ];

        foreach ($merchantTestCases as $testCase) {
            $result = $this->emailParser->parseTransaction($testCase['email']);
            $this->assertNotNull($result);
            
            // Check if merchant name is extracted (either in merchantName or description)
            $merchantFound = str_contains(strtoupper($result->description ?? ''), $testCase['merchant']) ||
                           str_contains(strtoupper($result->merchantName ?? ''), $testCase['merchant']);
            
            $this->assertTrue($merchantFound, 
                "Merchant {$testCase['merchant']} not found in parsed result");
        }
    }
}