<?php

namespace App\Modules\Fiscal\Models;

use Illuminate\Database\Eloquent\Model;

class FiscalDocument extends Model
{
    protected $table = 'fiscal_documents';

    protected $fillable = [
        'empresa_id',
        'venda_id',
        'tipo_documento',
        'numero_referencia',
        'status',
        'payload_preparado',
        'retorno_integracao',
        'chave_acesso',
        'motivo',
        'prepared_at',
        'transmitted_at',
        'cancelled_at',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'payload_preparado' => 'array',
        'retorno_integracao' => 'array',
        'prepared_at' => 'datetime',
        'transmitted_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];
}
