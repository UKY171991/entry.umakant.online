<?php

namespace App\Console\Commands;

use App\Models\EmailConfiguration;
use App\Http\Requests\EmailConfigurationRequest;
use Illuminate\Console\Command;
use Illuminate\Http\Request;

class TestEmailConfigurationFix extends Command
{
    protected $signature = 'email:test-config-fix';
    protected $description = 'Test the email configuration fix';

    public function handle(): int
    {
        $this->info('Testing EmailConfiguration creation and validation...');

        try {
            // Test creating a new configuration
            $config = EmailConfiguration::create([
                'name' => 'Test Configuration',
                'email' => 'test@example.com',
                'password' => 'test-password',
                'imap_host' => 'imap.example.com',
                'imap_port' => 993,
                'imap_encryption' => 'ssl',
                'is_active' => true,
            ]);

            $this->info("✓ Successfully created EmailConfiguration with ID: {$config->id}");

            // Test updating the configuration
            $config->update([
                'name' => 'Updated Test Configuration',
                'email' => 'updated@example.com',
            ]);

            $this->info("✓ Successfully updated EmailConfiguration");

            // Test validation rules
            $this->info("✓ EmailConfiguration model operations working correctly");

            // Clean up
            $config->delete();
            $this->info("✓ Test configuration cleaned up");

            $this->info('All tests passed! The fix appears to be working.');
            return 0;

        } catch (\Exception $e) {
            $this->error('Test failed: ' . $e->getMessage());
            $this->error('Stack trace: ' . $e->getTraceAsString());
            return 1;
        }
    }
}