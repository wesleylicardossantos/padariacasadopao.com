<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PrecificacaoHistorico extends Model
{
    protected $table = 'precificacao_historico';
    const CREATED_AT = 'created_at';
    const UPDATED_AT = null;

    protected $fillable = [
        'precificacao_id', 'produto_id', 'preco_antigo', 'preco_novo', 'alterado_por', 'usuario_id'
    ];
}
