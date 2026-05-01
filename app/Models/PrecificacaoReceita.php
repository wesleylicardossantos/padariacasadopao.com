<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PrecificacaoReceita extends Model
{
    protected $table = 'precificacao_receitas';

    protected $fillable = [
        'empresa_id', 'nome', 'rendimento', 'unidade_rendimento', 'custo_mao_obra',
        'custo_indireto', 'custo_embalagem', 'perda', 'status'
    ];

    public function itens()
    {
        return $this->hasMany(PrecificacaoReceitaItem::class, 'receita_id');
    }
}
