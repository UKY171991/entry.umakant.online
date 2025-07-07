<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Email extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'client_name',
        'email',
        'phone',
        'email_template',
        'project_name',
        'estimated_cost',
        'timeframe',
        'notes',
        'last_email_sent_at',
        'last_whatsapp_sent_at',
    ];

    protected $casts = [
        'last_email_sent_at' => 'datetime',
        'last_whatsapp_sent_at' => 'datetime',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }
}
