<?php

namespace App\Console\Commands;

use App\Models\EmailConfiguration;
use App\Models\EmailTransaction;
use App\Services\EmailTransactionLoggerService;
use App\Services\EmailTransactionNotificationService;
use Illuminate\Console\Command;
use Exception;

class EmailTransactionHealthCheck extends Command
{
    protected $signature = 'email-transactions:health-check';
    protected $description = 'Perform health checks on the email transaction system';

    private EmailTransactionLoggerService $logger;
    private EmailTransactionNotificationService $notificationService;

    public function __construct(
        EmailTransactionLoggerService $logger,
        EmailTransactionNotificationService $notificationService
    ) {
        parent::__construct();
        $this->logger = $logger;
        $this->notificationService = $notificationService;
    }

    public function handle(): int
    {
        $this->info('Starting email transaction system health check...');

        try {
            $healthData = $this->performHealthChecks();
            $this->displayHealthStatus($healthData);
            $this->logger->logSystemStatus($healthData);

            // Check for issues that require notifications
            $this->checkForAlerts($healthData);

            $this->info('Health check completed successfully.');
            return 0;

        } catch (Exception $e) {
            $this->error('Health check failed: ' . $e->getMessage());
            $this->logger->logCriticalError('EmailTransactionHealthCheck', $e);
            return 1;
        }
    }

    private function performHealthChecks(): array
    {
        $healthData = [
            'timestamp' => now()->toISOString(),
            'overall_status' => 'healthy',
            'checks' => []
        ];

        // Check email configurations
        $healthData['checks']['email_configurations'] = $this->checkEmailConfigurations();
        
        // Check recent processing performance
        $healthData['checks']['processing_performance'] = $this->checkProcessingPerformance();
        
        // Check for failed transactions
        $healthData['checks']['failed_transactions'] = $this->checkFailedTransactions();
        
        // Check parsing success rate
        $healthData['checks']['parsing_success_rate'] = $this->checkParsingSuccessRate();
        
        // Check system resources
        $healthData['checks']['system_resources'] = $this->checkSystemResources();
        
        // Check database connectivity
        $healthData['checks']['database_connectivity'] = $this->checkDatabaseConnectivity();

        // Determine overall status
        $healthData['overall_status'] = $this->determineOverallStatus($healthData['checks']);

        return $healthData;
    }

    private function checkEmailConfigurations(): array
    {
        $configs = EmailConfiguration::where('is_active', true)->get();
        $totalConfigs = $configs->count();
        $workingConfigs = 0;
        $failedConfigs = [];

        foreach ($configs as $config) {
            try {
                // Simple connection test
                $connectionString = sprintf(
                    '{%s:%d/imap/%s}INBOX',
                    $config->imap_host,
                    $config->imap_port,
                    $config->imap_encryption
                );

                $context = stream_context_create([
                    'ssl' => [
                        'verify_peer' => false,
                        'verify_peer_name' => false,
                    ]
                ]);

                $connection = @imap_open($connectionString, $config->email, $config->password, OP_HALFOPEN, 1, [
                    'DISABLE_AUTHENTICATOR' => 'GSSAPI'
                ]);

                if ($connection) {
                    imap_close($connection);
                    $workingConfigs++;
                } else {
                    $failedConfigs[] = [
                        'id' => $config->id,
                        'name' => $config->name,
                        'error' => imap_last_error() ?: 'Unknown connection error'
                    ];
                }
            } catch (Exception $e) {
                $failedConfigs[] = [
                    'id' => $config->id,
                    'name' => $config->name,
                    'error' => $e->getMessage()
                ];
            }
        }

        return [
            'status' => count($failedConfigs) === 0 ? 'healthy' : (count($failedConfigs) === $totalConfigs ? 'critical' : 'warning'),
            'total_configurations' => $totalConfigs,
            'working_configurations' => $workingConfigs,
            'failed_configurations' => count($failedConfigs),
            'failed_details' => $failedConfigs
        ];
    }

    private function checkProcessingPerformance(): array
    {
        $last24Hours = now()->subHours(24);
        
        $totalProcessed = EmailTransaction::where('created_at', '>=', $last24Hours)->count();
        $successful = EmailTransaction::where('created_at', '>=', $last24Hours)
                                    ->where('processing_status', 'processed')
                                    ->count();
        $failed = EmailTransaction::where('created_at', '>=', $last24Hours)
                                ->where('processing_status', 'failed')
                                ->count();
        $pending = EmailTransaction::where('created_at', '>=', $last24Hours)
                                 ->where('processing_status', 'pending')
                                 ->count();

        $successRate = $totalProcessed > 0 ? ($successful / $totalProcessed) * 100 : 100;

        $status = 'healthy';
        if ($successRate < 70) {
            $status = 'critical';
        } elseif ($successRate < 90) {
            $status = 'warning';
        }

        return [
            'status' => $status,
            'total_processed_24h' => $totalProcessed,
            'successful_24h' => $successful,
            'failed_24h' => $failed,
            'pending_24h' => $pending,
            'success_rate' => round($successRate, 2)
        ];
    }

    private function checkFailedTransactions(): array
    {
        $recentFailed = EmailTransaction::where('processing_status', 'failed')
                                      ->where('created_at', '>=', now()->subHours(24))
                                      ->count();

        $oldFailed = EmailTransaction::where('processing_status', 'failed')
                                   ->where('created_at', '<', now()->subDays(7))
                                   ->count();

        $status = 'healthy';
        if ($recentFailed > 50) {
            $status = 'critical';
        } elseif ($recentFailed > 20) {
            $status = 'warning';
        }

        return [
            'status' => $status,
            'recent_failed_24h' => $recentFailed,
            'old_failed_7d_plus' => $oldFailed,
            'needs_cleanup' => $oldFailed > 100
        ];
    }

    private function checkParsingSuccessRate(): array
    {
        $last24Hours = now()->subHours(24);
        
        $totalEmails = EmailTransaction::where('created_at', '>=', $last24Hours)->count();
        $parsingFailed = EmailTransaction::where('created_at', '>=', $last24Hours)
                                        ->where('processing_status', 'failed')
                                        ->where('error_message', 'like', '%parsing%')
                                        ->count();

        $parsingSuccessRate = $totalEmails > 0 ? (($totalEmails - $parsingFailed) / $totalEmails) * 100 : 100;

        $status = 'healthy';
        if ($parsingSuccessRate < 70) {
            $status = 'critical';
        } elseif ($parsingSuccessRate < 90) {
            $status = 'warning';
        }

        return [
            'status' => $status,
            'total_emails_24h' => $totalEmails,
            'parsing_failed_24h' => $parsingFailed,
            'parsing_success_rate' => round($parsingSuccessRate, 2)
        ];
    }

    private function checkSystemResources(): array
    {
        $memoryUsage = memory_get_usage(true) / 1024 / 1024; // MB
        $memoryLimit = ini_get('memory_limit');
        $memoryLimitMB = $this->parseMemoryLimit($memoryLimit);

        $diskUsage = disk_free_space(storage_path()) / 1024 / 1024 / 1024; // GB

        $status = 'healthy';
        if ($memoryUsage > ($memoryLimitMB * 0.9)) {
            $status = 'critical';
        } elseif ($memoryUsage > ($memoryLimitMB * 0.7)) {
            $status = 'warning';
        }

        if ($diskUsage < 1) { // Less than 1GB free
            $status = 'critical';
        }

        return [
            'status' => $status,
            'memory_usage_mb' => round($memoryUsage, 2),
            'memory_limit_mb' => $memoryLimitMB,
            'memory_usage_percent' => round(($memoryUsage / $memoryLimitMB) * 100, 2),
            'disk_free_gb' => round($diskUsage, 2)
        ];
    }

    private function checkDatabaseConnectivity(): array
    {
        try {
            $startTime = microtime(true);
            EmailConfiguration::count();
            $queryTime = (microtime(true) - $startTime) * 1000; // ms

            $status = 'healthy';
            if ($queryTime > 1000) { // > 1 second
                $status = 'warning';
            }

            return [
                'status' => $status,
                'connection' => 'successful',
                'query_time_ms' => round($queryTime, 2)
            ];
        } catch (Exception $e) {
            return [
                'status' => 'critical',
                'connection' => 'failed',
                'error' => $e->getMessage()
            ];
        }
    }

    private function determineOverallStatus(array $checks): string
    {
        $criticalCount = 0;
        $warningCount = 0;

        foreach ($checks as $check) {
            if ($check['status'] === 'critical') {
                $criticalCount++;
            } elseif ($check['status'] === 'warning') {
                $warningCount++;
            }
        }

        if ($criticalCount > 0) {
            return 'critical';
        } elseif ($warningCount > 0) {
            return 'warning';
        }

        return 'healthy';
    }

    private function displayHealthStatus(array $healthData): void
    {
        $this->line('');
        $this->line('=== Email Transaction System Health Status ===');
        $this->line('Overall Status: ' . strtoupper($healthData['overall_status']));
        $this->line('Timestamp: ' . $healthData['timestamp']);
        $this->line('');

        foreach ($healthData['checks'] as $checkName => $checkData) {
            $statusColor = match($checkData['status']) {
                'healthy' => 'green',
                'warning' => 'yellow',
                'critical' => 'red',
                default => 'white'
            };

            $this->line("<fg={$statusColor}>" . ucwords(str_replace('_', ' ', $checkName)) . ": " . strtoupper($checkData['status']) . "</>");
            
            // Display key metrics for each check
            foreach ($checkData as $key => $value) {
                if ($key !== 'status' && !is_array($value)) {
                    $this->line("  {$key}: {$value}");
                }
            }
            $this->line('');
        }
    }

    private function checkForAlerts(array $healthData): void
    {
        // Check for critical email configuration failures
        if ($healthData['checks']['email_configurations']['status'] === 'critical') {
            foreach ($healthData['checks']['email_configurations']['failed_details'] as $failedConfig) {
                $config = EmailConfiguration::find($failedConfig['id']);
                if ($config) {
                    $this->notificationService->notifyConnectionFailure(
                        $config, 
                        new Exception($failedConfig['error'])
                    );
                }
            }
        }

        // Check for high parsing failure rate
        $parsingData = $healthData['checks']['parsing_success_rate'];
        if ($parsingData['status'] !== 'healthy') {
            $failureRate = (100 - $parsingData['parsing_success_rate']) / 100;
            $this->notificationService->notifyHighParsingFailureRate(
                $failureRate,
                $parsingData['total_emails_24h'],
                $parsingData['parsing_failed_24h']
            );
        }
    }

    private function parseMemoryLimit(string $memoryLimit): float
    {
        $memoryLimit = trim($memoryLimit);
        $last = strtolower($memoryLimit[strlen($memoryLimit) - 1]);
        $value = (float) $memoryLimit;

        switch ($last) {
            case 'g':
                $value *= 1024;
            case 'm':
                $value *= 1024;
            case 'k':
                $value *= 1024;
        }

        return $value / 1024 / 1024; // Convert to MB
    }
}