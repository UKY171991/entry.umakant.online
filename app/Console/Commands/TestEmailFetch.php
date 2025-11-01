<?php

namespace App\Console\Commands;

use App\Contracts\EmailFetcherInterface;
use App\Models\EmailConfiguration;
use Illuminate\Console\Command;

class TestEmailFetch extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'email:test-fetch {config_id?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test email fetching functionality';

    /**
     * Execute the console command.
     */
    public function handle(EmailFetcherInterface $emailFetcher): int
    {
        $configId = $this->argument('config_id');
        
        if ($configId) {
            $config = EmailConfiguration::find($configId);
            if (!$config) {
                $this->error("Email configuration with ID {$configId} not found.");
                return 1;
            }
            $configs = collect([$config]);
        } else {
            $configs = EmailConfiguration::where('is_active', true)->get();
        }

        if ($configs->isEmpty()) {
            $this->info('No active email configurations found.');
            return 0;
        }

        foreach ($configs as $config) {
            $this->info("Testing connection for: {$config->name} ({$config->email})");
            
            if ($emailFetcher->testConnection($config)) {
                $this->info("✓ Connection successful");
                
                $this->info("Fetching emails...");
                $emails = $emailFetcher->fetchNewEmails($config);
                $this->info("Found {$emails->count()} emails to process");
                
                foreach ($emails->take(5) as $email) {
                    $this->line("- {$email['subject']} from {$email['sender']}");
                }
                
            } else {
                $this->error("✗ Connection failed");
            }
            
            $this->line('');
        }

        return 0;
    }
}