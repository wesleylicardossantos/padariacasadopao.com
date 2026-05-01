<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PdvOfflineSync extends Model
{
    protected $table = 'pdv_offline_syncs';

    protected $fillable = [
        'empresa_id',
        'usuario_id',
        'uuid_local',
        'payload_hash',
        'status',
        'venda_caixa_id',
        'request_payload',
        'response_payload',
        'erro',
        'erro_tipo',
        'mensagem_amigavel',
        'servidor_entidade_tipo',
        'servidor_entidade_id',
        'sincronizado_em',
        'tentativas',
        'ultima_tentativa_em',
    ];

    protected $casts = [
        'request_payload' => 'array',
        'response_payload' => 'array',
        'sincronizado_em' => 'datetime',
        'ultima_tentativa_em' => 'datetime',
        'tentativas' => 'integer',
        'servidor_entidade_id' => 'integer',
    ];
}
