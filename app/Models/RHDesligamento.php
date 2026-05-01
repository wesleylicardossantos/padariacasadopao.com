<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RHDesligamento extends Model
{
    protected $table = 'rh_desligamentos';

    protected $casts = [
        'data_desligamento' => 'date',
    ];

    protected $fillable = [
        'empresa_id',
        'funcionario_id',
        'data_desligamento',
        'motivo',
        'tipo',
        'observacao',
        'usuario_id',
    ];

    public function funcionario()
    {
        return $this->belongsTo(Funcionario::class, 'funcionario_id');
    }
}
