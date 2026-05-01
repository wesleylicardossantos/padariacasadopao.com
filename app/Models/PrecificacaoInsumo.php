<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PrecificacaoInsumo extends Model
{
    protected $table = 'precificacao_insumos';

    protected $fillable = [
        'empresa_id', 'nome', 'categoria', 'unidade_compra', 'unidade_uso', 'fator_conversao',
        'fator_perda', 'quantidade_embalagem', 'custo_embalagem', 'custo_unitario',
        'custo_unitario_base', 'fornecedor', 'ativo', 'produto_legado_id'
    ];
}
