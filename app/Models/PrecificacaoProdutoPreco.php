<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PrecificacaoProdutoPreco extends Model
{
    protected $table = 'precificacao_produto_precos';
    public $timestamps = false;

    protected $fillable = [
        'produto_id', 'canal_id', 'preco', 'margem', 'lucro', 'custo'
    ];
}
