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
        'email_template',
        'project_name',
        'estimated_cost',
        'timeframe',
        'notes',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }
}
