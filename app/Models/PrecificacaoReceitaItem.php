<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PrecificacaoReceitaItem extends Model
{
    protected $table = 'precificacao_receita_itens';
    public $timestamps = false;

    protected $fillable = [
        'receita_id', 'insumo_id', 'sub_receita_id', 'quantidade', 'custo_unitario', 'custo_total'
    ];

    public function insumo()
    {
        return $this->belongsTo(PrecificacaoInsumo::class, 'insumo_id');
    }

    public function subReceita()
    {
        return $this->belongsTo(PrecificacaoReceita::class, 'sub_receita_id');
    }
}

