<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RHFolhaFechamento extends Model
{
    protected $table = 'rh_folha_fechamentos';

    protected $fillable = [
        'empresa_id',
        'mes',
        'ano',
        'salario_base_total',
        'eventos_total',
        'descontos_total',
        'liquido_total',
        'conta_pagar_id',
        'status',
        'observacao',
        'usuario_id',
        'fechado_por',
        'reaberto_por',
    ];
}
