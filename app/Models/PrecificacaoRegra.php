<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PrecificacaoRegra extends Model
{
    protected $table = 'precificacao_regras';

    const CREATED_AT = 'created_at';
    const UPDATED_AT = null;

    protected $fillable = [
        'precificacao_id', 'tipo', 'valor', 'prioridade', 'ativo'
    ];
}
