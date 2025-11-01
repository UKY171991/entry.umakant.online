<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Expense extends Model
{
    use HasFactory;
    protected $fillable = [
        'expense_name',
        'amount',
        'category',
        'status',
        'date',
        'notes',
        'source',
        'email_transaction_id'
    ];

    protected $casts = [
        'date' => 'date',
        'amount' => 'decimal:2'
    ];

    /**
     * Get the email transaction that created this expense (if any)
     */
    public function emailTransaction()
    {
        return $this->belongsTo(EmailTransaction::class);
    }
}
