<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PendingTask extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'image_path',
        'task_name',
        'client_name',
        'description',
        'whatsapp_message',
        'due_date',
        'status',
        'payment',
        'payment_status',
    ];

    protected $casts = [
        'due_date' => 'date',
        'payment' => 'decimal:2',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }
}
