<?php

namespace App\Modules\SaaS\Models;

use Illuminate\Database\Eloquent\Model;

class SaasUsageSnapshot extends Model
{
    protected $table = 'saas_usage_snapshots';

    protected $fillable = [
        'empresa_id',
        'reference_date',
        'usage_payload',
    ];

    protected $casts = [
        'reference_date' => 'date',
        'usage_payload' => 'array',
    ];
}
