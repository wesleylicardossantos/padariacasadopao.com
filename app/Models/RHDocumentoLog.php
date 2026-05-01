<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RHDocumentoLog extends Model
{
    protected $table = 'rh_documento_logs';

    protected $fillable = [
        'empresa_id',
        'documento_id',
        'funcionario_id',
        'acao',
        'usuario_id',
        'detalhes',
        'payload_resumo',
    ];

    protected $casts = [
        'payload_resumo' => 'array',
    ];
}
