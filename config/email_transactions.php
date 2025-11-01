<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Email Transaction System Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains configuration options for the email transaction
    | automation system including error handling, logging, and notifications.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Administrator Email Addresses
    |--------------------------------------------------------------------------
    |
    | Email addresses that should receive critical error notifications and
    | daily summary reports. Can also be set via EMAIL_TRANSACTION_ADMIN_EMAILS
    | environment variable as a comma-separated list.
    |
    */
    'admin_emails' => [
        // Add administrator email addresses here
        // 'admin@example.com',
        // 'tech@example.com',
    ],

    /*
    |--------------------------------------------------------------------------
    | Error Handling Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for error handling, retry logic, and failure thresholds.
    |
    */
    'error_handling' => [
        'max_retries' => env('EMAIL_TRANSACTION_MAX_RETRIES', 3),
        'retry_delay' => env('EMAIL_TRANSACTION_RETRY_DELAY', 5), // seconds
        'exponential_backoff' => env('EMAIL_TRANSACTION_EXPONENTIAL_BACKOFF', true),
        'duplicate_threshold' => env('EMAIL_TRANSACTION_DUPLICATE_THRESHOLD', 0.01), // amount difference
        'duplicate_time_window' => env('EMAIL_TRANSACTION_DUPLICATE_TIME_WINDOW', 24), // hours
    ],

    /*
    |--------------------------------------------------------------------------
    | Notification Thresholds
    |--------------------------------------------------------------------------
    |
    | Thresholds for triggering various types of notifications to administrators.
    |
    */
    'notification_thresholds' => [
        'parsing_failure_rate' => env('EMAIL_TRANSACTION_PARSING_FAILURE_THRESHOLD', 0.3), // 30%
        'connection_failure_cooldown' => env('EMAIL_TRANSACTION_CONNECTION_FAILURE_COOLDOWN', 3600), // 1 hour
        'critical_error_cooldown' => env('EMAIL_TRANSACTION_CRITICAL_ERROR_COOLDOWN', 3600), // 1 hour
    ],

    /*
    |--------------------------------------------------------------------------
    | Logging Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for logging email transaction operations.
    |
    */
    'logging' => [
        'channel' => env('EMAIL_TRANSACTION_LOG_CHANNEL', 'email_transactions'),
        'level' => env('EMAIL_TRANSACTION_LOG_LEVEL', 'info'),
        'audit_trail' => env('EMAIL_TRANSACTION_AUDIT_TRAIL', true),
        'performance_metrics' => env('EMAIL_TRANSACTION_PERFORMANCE_METRICS', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Performance Monitoring
    |--------------------------------------------------------------------------
    |
    | Configuration for performance monitoring and metrics collection.
    |
    */
    'performance' => [
        'slow_operation_threshold' => env('EMAIL_TRANSACTION_SLOW_OPERATION_THRESHOLD', 30), // seconds
        'memory_usage_threshold' => env('EMAIL_TRANSACTION_MEMORY_THRESHOLD', 128), // MB
        'enable_metrics_collection' => env('EMAIL_TRANSACTION_ENABLE_METRICS', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Data Retention
    |--------------------------------------------------------------------------
    |
    | Configuration for data retention policies.
    |
    */
    'data_retention' => [
        'failed_transactions_days' => env('EMAIL_TRANSACTION_FAILED_RETENTION_DAYS', 30),
        'processed_transactions_days' => env('EMAIL_TRANSACTION_PROCESSED_RETENTION_DAYS', 365),
        'log_retention_days' => env('EMAIL_TRANSACTION_LOG_RETENTION_DAYS', 90),
    ],

    /*
    |--------------------------------------------------------------------------
    | Security Configuration
    |--------------------------------------------------------------------------
    |
    | Security-related configuration options.
    |
    */
    'security' => [
        'encrypt_email_content' => env('EMAIL_TRANSACTION_ENCRYPT_CONTENT', false),
        'mask_sensitive_data_in_logs' => env('EMAIL_TRANSACTION_MASK_SENSITIVE_DATA', true),
        'require_admin_approval_for_retries' => env('EMAIL_TRANSACTION_REQUIRE_ADMIN_APPROVAL', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Email Processing Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for email processing behavior.
    |
    */
    'processing' => [
        'batch_size' => env('EMAIL_TRANSACTION_BATCH_SIZE', 50),
        'processing_timeout' => env('EMAIL_TRANSACTION_PROCESSING_TIMEOUT', 300), // 5 minutes
        'parallel_processing' => env('EMAIL_TRANSACTION_PARALLEL_PROCESSING', false),
        'queue_connection' => env('EMAIL_TRANSACTION_QUEUE_CONNECTION', 'default'),
        'queue_name' => env('EMAIL_TRANSACTION_QUEUE_NAME', 'email-transactions'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Monitoring and Health Checks
    |--------------------------------------------------------------------------
    |
    | Configuration for system health monitoring.
    |
    */
    'health_checks' => [
        'enable_health_checks' => env('EMAIL_TRANSACTION_ENABLE_HEALTH_CHECKS', true),
        'health_check_interval' => env('EMAIL_TRANSACTION_HEALTH_CHECK_INTERVAL', 300), // 5 minutes
        'connection_timeout' => env('EMAIL_TRANSACTION_CONNECTION_TIMEOUT', 30), // seconds
        'max_consecutive_failures' => env('EMAIL_TRANSACTION_MAX_CONSECUTIVE_FAILURES', 5),
    ],
];