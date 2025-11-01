<?php

namespace App\Exceptions;

use Exception;
use Throwable;

class EmailTransactionException extends Exception
{
    protected array $context;
    protected string $component;

    public function __construct(
        string $message = '',
        string $component = '',
        array $context = [],
        int $code = 0,
        ?Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
        $this->component = $component;
        $this->context = $context;
    }

    public function getComponent(): string
    {
        return $this->component;
    }

    public function getContext(): array
    {
        return $this->context;
    }

    public function setContext(array $context): self
    {
        $this->context = array_merge($this->context, $context);
        return $this;
    }

    public function addContext(string $key, mixed $value): self
    {
        $this->context[$key] = $value;
        return $this;
    }

    /**
     * Get user-friendly error message
     */
    public function getUserMessage(): string
    {
        return match($this->component) {
            'EmailFetcherService' => 'Unable to fetch emails from the configured account. Please check your email settings.',
            'EmailParserService' => 'Unable to parse transaction details from the email. The email format may not be supported.',
            'TransactionProcessorService' => 'Unable to process the transaction. Please try again or contact support.',
            'EmailConfigurationController' => 'There was an issue with the email configuration. Please verify your settings.',
            default => 'An error occurred while processing email transactions. Please try again.'
        };
    }

    /**
     * Check if this is a critical error that requires immediate attention
     */
    public function isCritical(): bool
    {
        return in_array($this->component, [
            'EmailFetcherService',
            'TransactionProcessorService'
        ]) || str_contains(strtolower($this->getMessage()), 'database') 
           || str_contains(strtolower($this->getMessage()), 'connection');
    }

    /**
     * Check if this error should trigger a notification
     */
    public function shouldNotify(): bool
    {
        return $this->isCritical() || $this->code >= 500;
    }

    /**
     * Get error details for logging
     */
    public function getLogDetails(): array
    {
        return [
            'component' => $this->component,
            'message' => $this->getMessage(),
            'code' => $this->getCode(),
            'file' => $this->getFile(),
            'line' => $this->getLine(),
            'context' => $this->context,
            'trace' => $this->getTraceAsString()
        ];
    }
}