<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PrecificacaoProducaoItem extends Model
{
    use HasFactory;

    protected $table = 'precificacao_producao_itens';

    protected $guarded = [];
}
