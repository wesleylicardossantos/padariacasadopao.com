<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PrecificacaoListaCompraItem extends Model
{
    use HasFactory;

    protected $table = 'precificacao_lista_compras_itens';

    protected $guarded = [];
}
