<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MercadoPagoWebhookEvent extends Model
{
    protected $fillable = [
        'topic',
        'resource_id',
        'action',
        'event_hash',
        'headers',
        'payload',
        'status',
        'error_message',
        'processed_at',
    ];

    protected $casts = [
        'headers' => 'array',
        'payload' => 'array',
        'processed_at' => 'datetime',
    ];
}
