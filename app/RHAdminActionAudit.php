<?php

namespace App\Models\RH;

use Illuminate\Database\Eloquent\Model;

class RHAdminActionAudit extends Model
{
    protected $table = 'rh_admin_action_audits';

    protected $fillable = [
        'empresa_id',
        'usuario_id',
        'acao',
        'modulo',
        'referencia_tipo',
        'referencia_id',
        'alvo_tipo',
        'alvo_id',
        'payload_json',
        'ip',
        'user_agent',
    ];

    protected $casts = [
        'payload_json' => 'array',
    ];
}
