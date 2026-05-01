<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PrecificacaoProduto extends Model
{
    protected $table = 'precificacao_produtos';

    protected $fillable = [
        'empresa_id', 'receita_id', 'nome', 'custo_total', 'preco_sugerido',
        'lucro_bruto', 'cmv', 'produto_legado_id'
    ];

    public function receita()
    {
        return $this->belongsTo(PrecificacaoReceita::class, 'receita_id');
    }

    public function precos()
    {
        return $this->hasMany(PrecificacaoProdutoPreco::class, 'produto_id');
    }

    public function regras()
    {
        return $this->hasMany(PrecificacaoRegra::class, 'precificacao_id');
    }
}
