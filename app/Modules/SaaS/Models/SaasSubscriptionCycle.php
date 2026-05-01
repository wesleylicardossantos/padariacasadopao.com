<?php

namespace App\Modules\SaaS\Models;

use Illuminate\Database\Eloquent\Model;

class SaasSubscriptionCycle extends Model
{
    protected $table = 'saas_subscription_cycles';

    protected $fillable = [
        'empresa_id',
        'plano_empresa_id',
        'period_start',
        'period_end',
        'status',
        'trial_ends_at',
        'grace_ends_at',
        'meta',
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'trial_ends_at' => 'datetime',
        'grace_ends_at' => 'datetime',
        'meta' => 'array',
    ];
}
