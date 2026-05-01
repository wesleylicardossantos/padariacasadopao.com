<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RHCompetencia extends Model
{
    protected $table = 'rh_competencias';

    protected $fillable = [
        'empresa_id', 'mes', 'ano', 'status', 'processado_em', 'fechado_em', 'usuario_id', 'observacao'
    ];

    public function itens()
    {
        return $this->hasMany(RHFolhaItem::class, 'competencia_id');
    }
}
