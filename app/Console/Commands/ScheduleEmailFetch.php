<?php

namespace App\Console\Commands;

use App\Jobs\FetchEmailsJob;
use App\Models\EmailConfiguration;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ScheduleEmailFetch extends Command
{
    protected $signature = 'email:schedule-fetch 
                            {--config-id= : Specific email configuration ID to process}
                            {--force : Force fetch even if recently synced}';

    protected $description = 'Schedule email fetching jobs for all active email configurations';

    public function handle(): int
    {
        try {
            $this->info('Starting scheduled email fetch...');

            $query = EmailConfiguration::where('is_active', true);

            // Filter by specific configuration if provided
            if ($configId = $this->option('config-id')) {
                $query->where('id', $configId);
            }

            // Skip recently synced configurations unless forced
            if (!$this->option('force')) {
                $query->where(function ($q) {
                    $q->whereNull('last_sync_at')
                      ->orWhere('last_sync_at', '<', now()->subMinutes(15));
                });
            }

            $configurations = $query->get();

            if ($configurations->isEmpty()) {
                $this->info('No email configurations found for processing.');
                return self::SUCCESS;
            }

            $this->info("Found {$configurations->count()} email configuration(s) to process.");

            $jobsDispatched = 0;

            foreach ($configurations as $configuration) {
                try {
                    $this->line("Dispatching job for: {$configuration->email}");
                    
                    FetchEmailsJob::dispatch($configuration);
                    $jobsDispatched++;

                    Log::info('Dispatched email fetch job', [
                        'email_configuration_id' => $configuration->id,
                        'email' => $configuration->email
                    ]);

                } catch (\Exception $e) {
                    $this->error("Failed to dispatch job for {$configuration->email}: {$e->getMessage()}");
                    
                    Log::error('Failed to dispatch email fetch job', [
                        'email_configuration_id' => $configuration->id,
                        'email' => $configuration->email,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            $this->info("Successfully dispatched {$jobsDispatched} email fetch job(s).");

            Log::info('Scheduled email fetch completed', [
                'configurations_processed' => $configurations->count(),
                'jobs_dispatched' => $jobsDispatched
            ]);

            return self::SUCCESS;

        } catch (\Exception $e) {
            $this->error("Scheduled email fetch failed: {$e->getMessage()}");
            
            Log::error('Scheduled email fetch command failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return self::FAILURE;
        }
    }
}