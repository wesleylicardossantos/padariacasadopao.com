<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RHFerias extends Model
{
    protected $table = 'rh_ferias';

    protected $fillable = [
        'empresa_id',
        'funcionario_id',
        'periodo_aquisitivo_inicio',
        'periodo_aquisitivo_fim',
        'data_inicio',
        'data_fim',
        'dias',
        'status',
        'observacao',
        'usuario_id',
    ];

    public function funcionario()
    {
        return $this->belongsTo(Funcionario::class, 'funcionario_id');
    }
}
