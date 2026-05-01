<?php

namespace App\Modules\SaaS\Models;

use Illuminate\Database\Eloquent\Model;

class SaasTenantSetting extends Model
{
    protected $table = 'saas_tenant_settings';

    protected $fillable = [
        'empresa_id',
        'setting_key',
        'setting_value',
    ];

    protected $casts = [
        'setting_value' => 'array',
    ];
}
