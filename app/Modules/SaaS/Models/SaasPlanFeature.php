<?php

namespace App\Modules\SaaS\Models;

use Illuminate\Database\Eloquent\Model;

class SaasPlanFeature extends Model
{
    protected $table = 'saas_plan_features';

    protected $fillable = [
        'plano_id',
        'feature_key',
        'feature_label',
        'limit_value',
        'is_enabled',
        'meta',
    ];

    protected $casts = [
        'limit_value' => 'integer',
        'is_enabled' => 'boolean',
        'meta' => 'array',
    ];
}
