<?php

namespace Tests\Feature;

use App\Models\EmailConfiguration;
use App\Models\EmailTransaction;
use App\Services\EmailTransactionLoggerService;
use App\Contracts\EmailFetcherInterface;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;

class EmailConfigurationControllerTest extends TestCase
{
    use RefreshDatabase;

    private $mockEmailFetcher;
    private $mockLogger;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->mockEmailFetcher = Mockery::mock(EmailFetcherInterface::class);
        $this->mockLogger = Mockery::mock(EmailTransactionLoggerService::class);
        
        $this->app->instance(EmailFetcherInterface::class, $this->mockEmailFetcher);
        $this->app->instance(EmailTransactionLoggerService::class, $this->mockLogger);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /** @test */
    public function it_can_display_email_configurations_index()
    {
        // Create some test configurations
        $config1 = EmailConfiguration::factory()->create(['name' => 'Test Config 1']);
        $config2 = EmailConfiguration::factory()->create(['name' => 'Test Config 2']);

        // Create some email transactions for the configurations
        EmailTransaction::factory()->count(3)->create(['email_configuration_id' => $config1->id]);
        EmailTransaction::factory()->count(2)->create(['email_configuration_id' => $config2->id]);

        $response = $this->get(route('email-configurations.index'));

        $response->assertStatus(200);
        $response->assertViewIs('email-configurations.index');
        $response->assertViewHas('configurations');
        $response->assertSee('Test Config 1');
        $response->assertSee('Test Config 2');
    }

    /** @test */
    public function it_can_display_create_form()
    {
        $response = $this->get(route('email-configurations.create'));

        $response->assertStatus(200);
        $response->assertViewIs('email-configurations.create');
    }

    /** @test */
    public function it_can_store_new_email_configuration()
    {
        $this->mockLogger->shouldReceive('logAuditTrail')->once();

        $configData = [
            'name' => 'Test Bank Configuration',
            'email' => 'test@example.com',
            'password' => 'test-password',
            'imap_host' => 'imap.example.com',
            'imap_port' => 993,
            'imap_encryption' => 'ssl',
            'is_active' => true,
            'bank_patterns' => ['testbank', 'test alerts']
        ];

        $response = $this->post(route('email-configurations.store'), $configData);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('email_configurations', [
            'name' => 'Test Bank Configuration',
            'email' => 'test@example.com',
            'imap_host' => 'imap.example.com',
            'is_active' => true
        ]);
    }

    /** @test */
    public function it_validates_required_fields_when_storing()
    {
        $response = $this->post(route('email-configurations.store'), []);

        $response->assertSessionHasErrors(['name', 'email', 'password', 'imap_host', 'imap_port']);
    }

    /** @test */
    public function it_validates_email_format()
    {
        $configData = [
            'name' => 'Test Config',
            'email' => 'invalid-email-format',
            'password' => 'password',
            'imap_host' => 'imap.example.com',
            'imap_port' => 993,
        ];

        $response = $this->post(route('email-configurations.store'), $configData);

        $response->assertSessionHasErrors(['email']);
    }

    /** @test */
    public function it_validates_port_number_range()
    {
        $configData = [
            'name' => 'Test Config',
            'email' => 'test@example.com',
            'password' => 'password',
            'imap_host' => 'imap.example.com',
            'imap_port' => 99999, // Invalid port
        ];

        $response = $this->post(route('email-configurations.store'), $configData);

        $response->assertSessionHasErrors(['imap_port']);
    }

    /** @test */
    public function it_can_display_email_configuration_details()
    {
        $config = EmailConfiguration::factory()->create();
        
        // Create some email transactions for statistics
        EmailTransaction::factory()->count(5)->create([
            'email_configuration_id' => $config->id,
            'processing_status' => 'processed'
        ]);
        EmailTransaction::factory()->count(2)->create([
            'email_configuration_id' => $config->id,
            'processing_status' => 'failed'
        ]);

        $response = $this->get(route('email-configurations.show', $config));

        $response->assertStatus(200);
        $response->assertViewIs('email-configurations.show');
        $response->assertViewHas(['emailConfiguration', 'stats']);
        $response->assertSee($config->name);
    }

    /** @test */
    public function it_can_display_edit_form()
    {
        $config = EmailConfiguration::factory()->create();

        $response = $this->get(route('email-configurations.edit', $config));

        $response->assertStatus(200);
        $response->assertViewIs('email-configurations.edit');
        $response->assertViewHas('emailConfiguration');
        $response->assertSee($config->name);
    }

    /** @test */
    public function it_can_update_email_configuration()
    {
        $this->mockLogger->shouldReceive('logAuditTrail')->once();

        $config = EmailConfiguration::factory()->create([
            'name' => 'Original Name',
            'email' => 'original@example.com'
        ]);

        $updateData = [
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
            'imap_host' => $config->imap_host,
            'imap_port' => $config->imap_port,
            'imap_encryption' => $config->imap_encryption,
            'is_active' => true
        ];

        $response = $this->put(route('email-configurations.update', $config), $updateData);

        $response->assertRedirect(route('email-configurations.show', $config));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('email_configurations', [
            'id' => $config->id,
            'name' => 'Updated Name',
            'email' => 'updated@example.com'
        ]);
    }

    /** @test */
    public function it_does_not_update_password_when_empty()
    {
        $this->mockLogger->shouldReceive('logAuditTrail')->once();

        $config = EmailConfiguration::factory()->create([
            'password' => 'original-password'
        ]);

        $updateData = [
            'name' => $config->name,
            'email' => $config->email,
            'password' => '', // Empty password
            'imap_host' => $config->imap_host,
            'imap_port' => $config->imap_port,
            'imap_encryption' => $config->imap_encryption,
            'is_active' => true
        ];

        $response = $this->put(route('email-configurations.update', $config), $updateData);

        $response->assertRedirect();
        
        // Password should remain unchanged
        $config->refresh();
        $this->assertEquals('original-password', $config->password);
    }

    /** @test */
    public function it_can_delete_email_configuration()
    {
        $this->mockLogger->shouldReceive('logAuditTrail')->once();

        $config = EmailConfiguration::factory()->create(['name' => 'To Be Deleted']);

        $response = $this->delete(route('email-configurations.destroy', $config));

        $response->assertRedirect(route('email-configurations.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('email_configurations', [
            'id' => $config->id
        ]);
    }

    /** @test */
    public function it_can_test_email_connection_successfully()
    {
        $this->mockEmailFetcher->shouldReceive('testConnection')
            ->once()
            ->andReturn(true);

        $config = EmailConfiguration::factory()->create();

        $response = $this->post(route('email-configurations.test-connection', $config));

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'Connection successful! Email configuration is working properly.'
        ]);
    }

    /** @test */
    public function it_handles_failed_email_connection_test()
    {
        $this->mockEmailFetcher->shouldReceive('testConnection')
            ->once()
            ->andReturn(false);

        $config = EmailConfiguration::factory()->create();

        $response = $this->post(route('email-configurations.test-connection', $config));

        $response->assertStatus(400);
        $response->assertJson([
            'success' => false
        ]);
    }

    /** @test */
    public function it_can_toggle_configuration_status()
    {
        $this->mockLogger->shouldReceive('logAuditTrail')->once();

        $config = EmailConfiguration::factory()->create(['is_active' => true]);

        $response = $this->post(route('email-configurations.toggle-status', $config));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $config->refresh();
        $this->assertFalse($config->is_active);
    }

    /** @test */
    public function it_can_manually_sync_emails()
    {
        $this->mockLogger->shouldReceive('logAuditTrail')->once();
        
        $this->mockEmailFetcher->shouldReceive('fetchNewEmails')
            ->once()
            ->andReturn(collect([
                ['message_id' => 'msg1', 'subject' => 'Test 1'],
                ['message_id' => 'msg2', 'subject' => 'Test 2']
            ]));

        $config = EmailConfiguration::factory()->create();

        $response = $this->post(route('email-configurations.manual-sync', $config));

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'count' => 2
        ]);
    }

    /** @test */
    public function it_handles_manual_sync_errors()
    {
        $this->mockEmailFetcher->shouldReceive('fetchNewEmails')
            ->once()
            ->andThrow(new \Exception('Connection failed'));

        $config = EmailConfiguration::factory()->create();

        $response = $this->post(route('email-configurations.manual-sync', $config));

        $response->assertStatus(500);
        $response->assertJson([
            'success' => false
        ]);
    }

    /** @test */
    public function it_requires_authentication_for_all_routes()
    {
        $config = EmailConfiguration::factory()->create();

        // Test all routes require authentication
        $routes = [
            ['GET', route('email-configurations.index')],
            ['GET', route('email-configurations.create')],
            ['POST', route('email-configurations.store')],
            ['GET', route('email-configurations.show', $config)],
            ['GET', route('email-configurations.edit', $config)],
            ['PUT', route('email-configurations.update', $config)],
            ['DELETE', route('email-configurations.destroy', $config)],
            ['POST', route('email-configurations.test-connection', $config)],
            ['POST', route('email-configurations.toggle-status', $config)],
            ['POST', route('email-configurations.manual-sync', $config)],
        ];

        foreach ($routes as [$method, $url]) {
            $response = $this->call($method, $url);
            
            // Should redirect to login or return 401/403
            $this->assertTrue(
                in_array($response->getStatusCode(), [302, 401, 403]),
                "Route {$method} {$url} should require authentication"
            );
        }
    }

    /** @test */
    public function it_shows_configuration_statistics_correctly()
    {
        $config = EmailConfiguration::factory()->create();
        
        // Create various types of email transactions
        EmailTransaction::factory()->count(10)->create([
            'email_configuration_id' => $config->id,
            'processing_status' => 'processed'
        ]);
        EmailTransaction::factory()->count(3)->create([
            'email_configuration_id' => $config->id,
            'processing_status' => 'failed'
        ]);
        EmailTransaction::factory()->count(2)->create([
            'email_configuration_id' => $config->id,
            'processing_status' => 'pending'
        ]);

        $response = $this->get(route('email-configurations.show', $config));

        $response->assertStatus(200);
        
        // Check that statistics are passed to the view
        $stats = $response->viewData('stats');
        $this->assertEquals(15, $stats['total_transactions']); // 10 + 3 + 2
        $this->assertEquals(10, $stats['processed_transactions']);
        $this->assertEquals(3, $stats['failed_transactions']);
    }

    /** @test */
    public function it_encrypts_password_when_storing()
    {
        $this->mockLogger->shouldReceive('logAuditTrail')->once();

        $configData = [
            'name' => 'Encryption Test',
            'email' => 'test@example.com',
            'password' => 'plain-text-password',
            'imap_host' => 'imap.example.com',
            'imap_port' => 993,
            'imap_encryption' => 'ssl',
            'is_active' => true
        ];

        $response = $this->post(route('email-configurations.store'), $configData);

        $response->assertRedirect();

        // Verify password is encrypted (not stored as plain text)
        $config = EmailConfiguration::where('email', 'test@example.com')->first();
        $this->assertNotEquals('plain-text-password', $config->getAttributes()['password']);
        
        // But should decrypt correctly when accessed
        $this->assertEquals('plain-text-password', $config->password);
    }
}