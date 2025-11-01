<?php

namespace App\Contracts;

use App\Models\EmailConfiguration;
use Illuminate\Support\Collection;

interface EmailFetcherInterface
{
    /**
     * Fetch new emails from the configured email account
     *
     * @param EmailConfiguration $config
     * @return Collection
     */
    public function fetchNewEmails(EmailConfiguration $config): Collection;

    /**
     * Mark an email as processed to prevent duplicate processing
     *
     * @param string $messageId
     * @return void
     */
    public function markAsProcessed(string $messageId): void;

    /**
     * Test connection to email account
     *
     * @param EmailConfiguration $config
     * @return bool
     */
    public function testConnection(EmailConfiguration $config): bool;

    /**
     * Get the list of processed message IDs
     *
     * @return array
     */
    public function getProcessedMessageIds(): array;
}