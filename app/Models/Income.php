<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Income extends Model
{
    protected $fillable = [
        'client_id',
        'total_amount',
        'pending_amount',
        'received_amount',
        'date',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }
}
