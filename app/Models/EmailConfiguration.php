<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class EmailConfiguration extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'password',
        'imap_host',
        'imap_port',
        'imap_encryption',
        'is_active',
        'last_sync_at',
        'bank_patterns'
    ];

    protected $casts = [
        'password' => 'encrypted',
        'bank_patterns' => 'array',
        'last_sync_at' => 'datetime',
        'is_active' => 'boolean',
        'imap_port' => 'integer'
    ];

    protected $hidden = [
        'password'
    ];

    /**
     * Get all email transactions for this configuration
     */
    public function emailTransactions(): HasMany
    {
        return $this->hasMany(EmailTransaction::class);
    }

    /**
     * Get only processed email transactions
     */
    public function processedTransactions(): HasMany
    {
        return $this->hasMany(EmailTransaction::class)->where('processing_status', 'processed');
    }

    /**
     * Get only failed email transactions
     */
    public function failedTransactions(): HasMany
    {
        return $this->hasMany(EmailTransaction::class)->where('processing_status', 'failed');
    }

    /**
     * Check if this configuration is ready for use
     */
    public function isConfigured(): bool
    {
        return !empty($this->email) && 
               !empty($this->password) && 
               !empty($this->imap_host) && 
               !empty($this->imap_port);
    }

    /**
     * Update last sync timestamp
     */
    public function updateLastSync(): void
    {
        $this->update(['last_sync_at' => now()]);
    }
}
