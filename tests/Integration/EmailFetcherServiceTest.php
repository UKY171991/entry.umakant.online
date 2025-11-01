<?php

namespace Tests\Integration;

use App\Services\EmailFetcherService;
use App\Services\EmailTransactionLoggerService;
use App\Services\EmailTransactionNotificationService;
use App\Models\EmailConfiguration;
use App\Models\EmailTransaction;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;

class EmailFetcherServiceTest extends TestCase
{
    use RefreshDatabase;

    private EmailFetcherService $emailFetcher;
    private $mockLogger;
    private $mockNotificationService;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->mockLogger = Mockery::mock(EmailTransactionLoggerService::class);
        $this->mockNotificationService = Mockery::mock(EmailTransactionNotificationService::class);
        
        $this->emailFetcher = new EmailFetcherService(
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
    public function it_returns_empty_collection_for_inactive_configuration()
    {
        $this->mockLogger->shouldReceive('logEmailFetch')->once();

        $config = EmailConfiguration::factory()->create([
            'is_active' => false
        ]);

        $emails = $this->emailFetcher->fetchNewEmails($config);

        $this->assertTrue($emails->isEmpty());
    }

    /** @test */
    public function it_returns_empty_collection_for_unconfigured_email()
    {
        $this->mockLogger->shouldReceive('logEmailFetch')->once();

        $config = EmailConfiguration::factory()->create([
            'email' => null,
            'password' => null
        ]);

        $emails = $this->emailFetcher->fetchNewEmails($config);

        $this->assertTrue($emails->isEmpty());
    }

    /** @test */
    public function it_can_identify_processed_message_ids()
    {
        // Create some existing email transactions
        EmailTransaction::factory()->create(['message_id' => 'msg-001']);
        EmailTransaction::factory()->create(['message_id' => 'msg-002']);
        EmailTransaction::factory()->create(['message_id' => 'msg-003']);

        // Create a fresh service instance to load processed IDs
        $emailFetcher = new EmailFetcherService(
            $this->mockLogger,
            $this->mockNotificationService
        );

        $processedIds = $emailFetcher->getProcessedMessageIds();

        $this->assertContains('msg-001', $processedIds);
        $this->assertContains('msg-002', $processedIds);
        $this->assertContains('msg-003', $processedIds);
    }

    /** @test */
    public function it_can_mark_emails_as_processed()
    {
        $messageId = 'new-message-123';
        
        // Initially should not be in processed list
        $this->assertNotContains($messageId, $this->emailFetcher->getProcessedMessageIds());
        
        // Mark as processed
        $this->emailFetcher->markAsProcessed($messageId);
        
        // Should now be in processed list
        $this->assertContains($messageId, $this->emailFetcher->getProcessedMessageIds());
    }

    /** @test */
    public function it_does_not_duplicate_processed_message_ids()
    {
        $messageId = 'duplicate-test-123';
        
        // Mark as processed twice
        $this->emailFetcher->markAsProcessed($messageId);
        $this->emailFetcher->markAsProcessed($messageId);
        
        $processedIds = $this->emailFetcher->getProcessedMessageIds();
        $occurrences = array_count_values($processedIds)[$messageId] ?? 0;
        
        $this->assertEquals(1, $occurrences);
    }

    /** @test */
    public function it_identifies_bank_emails_by_sender_patterns()
    {
        // Create mock IncomingMail object
        $mockMail = Mockery::mock('PhpImap\IncomingMail');
        $mockMail->messageId = 'test-bank-email';
        $mockMail->fromAddress = 'alerts@sbi.co.in';
        $mockMail->subject = 'Account Transaction Alert';

        $config = EmailConfiguration::factory()->create();

        // Use reflection to test private method
        $reflection = new \ReflectionClass($this->emailFetcher);
        $method = $reflection->getMethod('shouldProcessEmail');
        $method->setAccessible(true);

        $shouldProcess = $method->invokeArgs($this->emailFetcher, [$mockMail, $config]);

        $this->assertTrue($shouldProcess);
    }

    /** @test */
    public function it_identifies_bank_emails_by_subject_patterns()
    {
        // Create mock IncomingMail object
        $mockMail = Mockery::mock('PhpImap\IncomingMail');
        $mockMail->messageId = 'test-bank-email-2';
        $mockMail->fromAddress = 'random@example.com';
        $mockMail->subject = 'Bank Transaction Notification';

        $config = EmailConfiguration::factory()->create();

        // Use reflection to test private method
        $reflection = new \ReflectionClass($this->emailFetcher);
        $method = $reflection->getMethod('shouldProcessEmail');
        $method->setAccessible(true);

        $shouldProcess = $method->invokeArgs($this->emailFetcher, [$mockMail, $config]);

        $this->assertTrue($shouldProcess);
    }

    /** @test */
    public function it_skips_non_bank_emails()
    {
        // Create mock IncomingMail object
        $mockMail = Mockery::mock('PhpImap\IncomingMail');
        $mockMail->messageId = 'test-non-bank-email';
        $mockMail->fromAddress = 'newsletter@example.com';
        $mockMail->subject = 'Weekly Newsletter';

        $config = EmailConfiguration::factory()->create();

        // Use reflection to test private method
        $reflection = new \ReflectionClass($this->emailFetcher);
        $method = $reflection->getMethod('shouldProcessEmail');
        $method->setAccessible(true);

        $shouldProcess = $method->invokeArgs($this->emailFetcher, [$mockMail, $config]);

        $this->assertFalse($shouldProcess);
    }

    /** @test */
    public function it_skips_already_processed_emails()
    {
        // Create existing email transaction
        EmailTransaction::factory()->create(['message_id' => 'already-processed']);

        // Create fresh service instance to load processed IDs
        $emailFetcher = new EmailFetcherService(
            $this->mockLogger,
            $this->mockNotificationService
        );

        // Create mock IncomingMail object with processed message ID
        $mockMail = Mockery::mock('PhpImap\IncomingMail');
        $mockMail->messageId = 'already-processed';
        $mockMail->fromAddress = 'alerts@bank.com';
        $mockMail->subject = 'Transaction Alert';

        $config = EmailConfiguration::factory()->create();

        // Use reflection to test private method
        $reflection = new \ReflectionClass($emailFetcher);
        $method = $reflection->getMethod('shouldProcessEmail');
        $method->setAccessible(true);

        $shouldProcess = $method->invokeArgs($emailFetcher, [$mockMail, $config]);

        $this->assertFalse($shouldProcess);
    }

    /** @test */
    public function it_uses_configuration_specific_bank_patterns()
    {
        $config = EmailConfiguration::factory()->create([
            'bank_patterns' => ['custombank', 'special-alerts']
        ]);

        // Create mock IncomingMail object
        $mockMail = Mockery::mock('PhpImap\IncomingMail');
        $mockMail->messageId = 'custom-bank-email';
        $mockMail->fromAddress = 'alerts@custombank.com';
        $mockMail->subject = 'Account Alert';

        // Use reflection to test private method
        $reflection = new \ReflectionClass($this->emailFetcher);
        $method = $reflection->getMethod('shouldProcessEmail');
        $method->setAccessible(true);

        $shouldProcess = $method->invokeArgs($this->emailFetcher, [$mockMail, $config]);

        $this->assertTrue($shouldProcess);
    }

    /** @test */
    public function it_formats_email_data_correctly()
    {
        $config = EmailConfiguration::factory()->create();

        // Create mock IncomingMail object
        $mockMail = Mockery::mock('PhpImap\IncomingMail');
        $mockMail->messageId = 'format-test-123';
        $mockMail->subject = 'Test Transaction Alert';
        $mockMail->fromAddress = 'test@bank.com';
        $mockMail->textPlain = 'Plain text content';
        $mockMail->textHtml = '<html>HTML content</html>';
        $mockMail->date = '2024-01-15 10:30:00';

        // Use reflection to test private method
        $reflection = new \ReflectionClass($this->emailFetcher);
        $method = $reflection->getMethod('formatEmailData');
        $method->setAccessible(true);

        $formattedData = $method->invokeArgs($this->emailFetcher, [$mockMail, $config]);

        $this->assertEquals($config->id, $formattedData['email_configuration_id']);
        $this->assertEquals('format-test-123', $formattedData['message_id']);
        $this->assertEquals('Test Transaction Alert', $formattedData['subject']);
        $this->assertEquals('test@bank.com', $formattedData['sender']);
        $this->assertEquals('Plain text content', $formattedData['raw_content']);
        $this->assertEquals('2024-01-15 10:30:00', $formattedData['received_date']);
        $this->assertEquals('pending', $formattedData['processing_status']);
    }

    /** @test */
    public function it_prefers_plain_text_over_html_content()
    {
        $config = EmailConfiguration::factory()->create();

        // Create mock IncomingMail object with both plain and HTML content
        $mockMail = Mockery::mock('PhpImap\IncomingMail');
        $mockMail->messageId = 'content-test-123';
        $mockMail->subject = 'Test Email';
        $mockMail->fromAddress = 'test@example.com';
        $mockMail->textPlain = 'Plain text content';
        $mockMail->textHtml = '<html>HTML content</html>';
        $mockMail->date = '2024-01-15 10:30:00';

        // Use reflection to test private method
        $reflection = new \ReflectionClass($this->emailFetcher);
        $method = $reflection->getMethod('formatEmailData');
        $method->setAccessible(true);

        $formattedData = $method->invokeArgs($this->emailFetcher, [$mockMail, $config]);

        $this->assertEquals('Plain text content', $formattedData['raw_content']);
    }

    /** @test */
    public function it_falls_back_to_html_when_no_plain_text()
    {
        $config = EmailConfiguration::factory()->create();

        // Create mock IncomingMail object with only HTML content
        $mockMail = Mockery::mock('PhpImap\IncomingMail');
        $mockMail->messageId = 'html-only-test';
        $mockMail->subject = 'HTML Only Email';
        $mockMail->fromAddress = 'test@example.com';
        $mockMail->textPlain = null;
        $mockMail->textHtml = '<html>HTML content</html>';
        $mockMail->date = '2024-01-15 10:30:00';

        // Use reflection to test private method
        $reflection = new \ReflectionClass($this->emailFetcher);
        $method = $reflection->getMethod('formatEmailData');
        $method->setAccessible(true);

        $formattedData = $method->invokeArgs($this->emailFetcher, [$mockMail, $config]);

        $this->assertEquals('<html>HTML content</html>', $formattedData['raw_content']);
    }

    /** @test */
    public function it_generates_correct_last_sync_date_for_new_configuration()
    {
        $config = EmailConfiguration::factory()->create([
            'last_sync_at' => null
        ]);

        // Use reflection to test private method
        $reflection = new \ReflectionClass($this->emailFetcher);
        $method = $reflection->getMethod('getLastSyncDate');
        $method->setAccessible(true);

        $lastSyncDate = $method->invokeArgs($this->emailFetcher, [$config]);

        // Should return date 30 days ago in d-M-Y format
        $expectedDate = now()->subDays(30)->format('d-M-Y');
        $this->assertEquals($expectedDate, $lastSyncDate);
    }

    /** @test */
    public function it_uses_existing_last_sync_date()
    {
        $lastSync = now()->subDays(5);
        $config = EmailConfiguration::factory()->create([
            'last_sync_at' => $lastSync
        ]);

        // Use reflection to test private method
        $reflection = new \ReflectionClass($this->emailFetcher);
        $method = $reflection->getMethod('getLastSyncDate');
        $method->setAccessible(true);

        $lastSyncDate = $method->invokeArgs($this->emailFetcher, [$config]);

        $expectedDate = $lastSync->format('d-M-Y');
        $this->assertEquals($expectedDate, $lastSyncDate);
    }

    /** @test */
    public function it_handles_connection_failures_with_retry_and_notification()
    {
        $this->mockLogger->shouldReceive('logEmailFetch')->times(4); // 3 retries + 1 final
        $this->mockNotificationService->shouldReceive('notifyConnectionFailure')->once();
        $this->mockLogger->shouldReceive('logCriticalError')->once();

        // Create a configuration that will fail connection (invalid host)
        $config = EmailConfiguration::factory()->create([
            'imap_host' => 'invalid-host.example.com',
            'imap_port' => 993,
            'imap_encryption' => 'ssl',
            'email' => 'test@example.com',
            'password' => 'invalid-password'
        ]);

        $this->expectException(\Exception::class);
        
        $this->emailFetcher->fetchNewEmails($config);
    }
}