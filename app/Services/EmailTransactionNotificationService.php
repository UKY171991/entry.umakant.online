<?php

namespace App\Services;

use App\Models\EmailConfiguration;
use App\Models\EmailTransaction;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Exception;

class EmailTransactionNotificationService
{
    private const NOTIFICATION_CACHE_PREFIX = 'email_notification_';
    private const NOTIFICATION_COOLDOWN = 3600; // 1 hour cooldown for similar notifications

    /**
     * Notify administrators of critical email system errors
     */
    public function notifyCriticalError(string $component, Exception $exception, array $context = []): void
    {
        $cacheKey = self::NOTIFICATION_CACHE_PREFIX . 'critical_' . md5($component . $exception->getMessage());
        
        // Check if we've already sent this notification recently
        if (Cache::has($cacheKey)) {
            return;
        }

        try {
            $adminEmails = $this->getAdminEmails();
            
            if (empty($adminEmails)) {
                Log::warning('No admin emails configured for critical error notifications');
                return;
            }

            $errorData = [
                'component' => $component,
                'error_message' => $exception->getMessage(),
                'error_file' => $exception->getFile(),
                'error_line' => $exception->getLine(),
                'context' => $context,
                'timestamp' => now()->toDateTimeString(),
                'server' => request()->getHost() ?? 'Unknown'
            ];

            foreach ($adminEmails as $email) {
                Mail::send('emails.critical-error-notification', $errorData, function ($message) use ($email, $component) {
                    $message->to($email)
                           ->subject("Critical Error in Email Transaction System - {$component}");
                });
            }

            // Cache to prevent spam
            Cache::put($cacheKey, true, self::NOTIFICATION_COOLDOWN);
            
            Log::info('Critical error notification sent to administrators', [
                'component' => $component,
                'admin_count' => count($adminEmails)
            ]);

        } catch (Exception $e) {
            Log::error('Failed to send critical error notification', [
                'original_error' => $exception->getMessage(),
                'notification_error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Notify administrators of high parsing failure rates
     */
    public function notifyHighParsingFailureRate(float $failureRate, int $totalEmails, int $failedEmails): void
    {
        $cacheKey = self::NOTIFICATION_CACHE_PREFIX . 'parsing_failure_' . date('Y-m-d-H');
        
        if (Cache::has($cacheKey) || $failureRate < 0.3) { // Only notify if failure rate > 30%
            return;
        }

        try {
            $adminEmails = $this->getAdminEmails();
            
            if (empty($adminEmails)) {
                return;
            }

            $data = [
                'failure_rate' => round($failureRate * 100, 2),
                'total_emails' => $totalEmails,
                'failed_emails' => $failedEmails,
                'timestamp' => now()->toDateTimeString(),
                'recommendation' => $this->getParsingFailureRecommendation($failureRate)
            ];

            foreach ($adminEmails as $email) {
                Mail::send('emails.parsing-failure-notification', $data, function ($message) use ($email, $failureRate) {
                    $message->to($email)
                           ->subject('High Email Parsing Failure Rate Alert - ' . round($failureRate * 100, 2) . '%');
                });
            }

            Cache::put($cacheKey, true, self::NOTIFICATION_COOLDOWN);
            
        } catch (Exception $e) {
            Log::error('Failed to send parsing failure notification', [
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Notify administrators of email connection failures
     */
    public function notifyConnectionFailure(EmailConfiguration $config, Exception $exception): void
    {
        $cacheKey = self::NOTIFICATION_CACHE_PREFIX . 'connection_failure_' . $config->id . '_' . date('Y-m-d-H');
        
        if (Cache::has($cacheKey)) {
            return;
        }

        try {
            $adminEmails = $this->getAdminEmails();
            
            if (empty($adminEmails)) {
                return;
            }

            $data = [
                'config_name' => $config->name,
                'email_account' => $config->email,
                'imap_host' => $config->imap_host,
                'error_message' => $exception->getMessage(),
                'timestamp' => now()->toDateTimeString(),
                'troubleshooting_steps' => $this->getConnectionTroubleshootingSteps()
            ];

            foreach ($adminEmails as $email) {
                Mail::send('emails.connection-failure-notification', $data, function ($message) use ($email, $config) {
                    $message->to($email)
                           ->subject("Email Connection Failure - {$config->name}");
                });
            }

            Cache::put($cacheKey, true, self::NOTIFICATION_COOLDOWN);
            
        } catch (Exception $e) {
            Log::error('Failed to send connection failure notification', [
                'config_id' => $config->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Notify administrators of unusual transaction patterns
     */
    public function notifyUnusualTransactionPattern(array $patternData): void
    {
        $cacheKey = self::NOTIFICATION_CACHE_PREFIX . 'unusual_pattern_' . date('Y-m-d');
        
        if (Cache::has($cacheKey)) {
            return;
        }

        try {
            $adminEmails = $this->getAdminEmails();
            
            if (empty($adminEmails)) {
                return;
            }

            $data = array_merge($patternData, [
                'timestamp' => now()->toDateTimeString(),
                'recommendation' => 'Please review the transactions for potential fraud or system errors.'
            ]);

            foreach ($adminEmails as $email) {
                Mail::send('emails.unusual-pattern-notification', $data, function ($message) use ($email) {
                    $message->to($email)
                           ->subject('Unusual Transaction Pattern Detected');
                });
            }

            Cache::put($cacheKey, true, self::NOTIFICATION_COOLDOWN);
            
        } catch (Exception $e) {
            Log::error('Failed to send unusual pattern notification', [
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Send daily summary report to administrators
     */
    public function sendDailySummary(): void
    {
        try {
            $adminEmails = $this->getAdminEmails();
            
            if (empty($adminEmails)) {
                return;
            }

            $summaryData = $this->generateDailySummaryData();

            foreach ($adminEmails as $email) {
                Mail::send('emails.daily-summary', $summaryData, function ($message) use ($email) {
                    $message->to($email)
                           ->subject('Email Transaction System - Daily Summary ' . now()->toDateString());
                });
            }
            
            Log::info('Daily summary sent to administrators', [
                'admin_count' => count($adminEmails),
                'summary_data' => $summaryData
            ]);

        } catch (Exception $e) {
            Log::error('Failed to send daily summary', [
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Get administrator email addresses from configuration
     */
    private function getAdminEmails(): array
    {
        // Get from config or environment variable
        $adminEmails = config('email_transactions.admin_emails', []);
        
        if (empty($adminEmails)) {
            $adminEmails = explode(',', env('EMAIL_TRANSACTION_ADMIN_EMAILS', ''));
            $adminEmails = array_filter(array_map('trim', $adminEmails));
        }

        return $adminEmails;
    }

    /**
     * Generate daily summary data
     */
    private function generateDailySummaryData(): array
    {
        $today = now()->toDateString();
        
        $totalProcessed = EmailTransaction::whereDate('created_at', $today)->count();
        $successfullyProcessed = EmailTransaction::whereDate('created_at', $today)
                                                ->where('processing_status', 'processed')
                                                ->count();
        $failed = EmailTransaction::whereDate('created_at', $today)
                                 ->where('processing_status', 'failed')
                                 ->count();
        $pending = EmailTransaction::whereDate('created_at', $today)
                                  ->where('processing_status', 'pending')
                                  ->count();

        $totalAmount = EmailTransaction::whereDate('created_at', $today)
                                      ->where('processing_status', 'processed')
                                      ->sum('amount');

        $incomeCount = EmailTransaction::whereDate('created_at', $today)
                                      ->where('processing_status', 'processed')
                                      ->where('transaction_type', 'income')
                                      ->count();

        $expenseCount = EmailTransaction::whereDate('created_at', $today)
                                       ->where('processing_status', 'processed')
                                       ->where('transaction_type', 'expense')
                                       ->count();

        return [
            'date' => $today,
            'total_processed' => $totalProcessed,
            'successful' => $successfullyProcessed,
            'failed' => $failed,
            'pending' => $pending,
            'success_rate' => $totalProcessed > 0 ? round(($successfullyProcessed / $totalProcessed) * 100, 2) : 0,
            'total_amount' => $totalAmount,
            'income_transactions' => $incomeCount,
            'expense_transactions' => $expenseCount,
            'active_configurations' => EmailConfiguration::where('is_active', true)->count()
        ];
    }

    /**
     * Get parsing failure recommendations
     */
    private function getParsingFailureRecommendation(float $failureRate): string
    {
        if ($failureRate > 0.7) {
            return 'Critical: Review and update email parsing patterns immediately. Consider adding new bank-specific patterns.';
        } elseif ($failureRate > 0.5) {
            return 'High: Check recent failed emails and update parsing patterns as needed.';
        } else {
            return 'Moderate: Monitor parsing patterns and consider minor adjustments.';
        }
    }

    /**
     * Get connection troubleshooting steps
     */
    private function getConnectionTroubleshootingSteps(): array
    {
        return [
            'Verify email account credentials are correct',
            'Check if IMAP is enabled for the email account',
            'Verify IMAP server settings (host, port, encryption)',
            'Check if the email provider has changed their IMAP settings',
            'Ensure the server can reach the IMAP host (firewall/network issues)',
            'Check if the email account is locked or requires additional authentication'
        ];
    }
}