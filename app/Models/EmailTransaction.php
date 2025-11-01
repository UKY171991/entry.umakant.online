<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class EmailTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'email_configuration_id',
        'message_id',
        'subject',
        'sender',
        'transaction_type',
        'amount',
        'transaction_date',
        'description',
        'raw_content',
        'processing_status',
        'income_id',
        'expense_id',
        'error_message',
        'retry_count',
        'last_retry_at'
    ];

    protected $casts = [
        'transaction_date' => 'date',
        'amount' => 'decimal:2',
        'last_retry_at' => 'datetime'
    ];

    /**
     * Get the email configuration that owns this transaction
     */
    public function emailConfiguration(): BelongsTo
    {
        return $this->belongsTo(EmailConfiguration::class);
    }

    /**
     * Get the associated income record (if transaction_type is 'income')
     */
    public function income(): BelongsTo
    {
        return $this->belongsTo(Income::class);
    }

    /**
     * Get the associated expense record (if transaction_type is 'expense')
     */
    public function expense(): BelongsTo
    {
        return $this->belongsTo(Expense::class);
    }

    /**
     * Scope to filter by processing status
     */
    public function scopeByStatus($query, string $status)
    {
        return $query->where('processing_status', $status);
    }

    /**
     * Scope to filter by transaction type
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('transaction_type', $type);
    }

    /**
     * Scope to filter by date range
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('transaction_date', [$startDate, $endDate]);
    }

    /**
     * Check if this transaction has been processed successfully
     */
    public function isProcessed(): bool
    {
        return $this->processing_status === 'processed';
    }

    /**
     * Check if this transaction failed processing
     */
    public function hasFailed(): bool
    {
        return $this->processing_status === 'failed';
    }

    /**
     * Mark transaction as processed
     */
    public function markAsProcessed(): void
    {
        $this->update(['processing_status' => 'processed']);
    }

    /**
     * Mark transaction as failed with error message
     */
    public function markAsFailed(string $errorMessage): void
    {
        $this->update([
            'processing_status' => 'failed',
            'error_message' => $errorMessage
        ]);
    }
}
