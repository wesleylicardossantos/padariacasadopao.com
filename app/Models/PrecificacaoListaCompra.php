<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PrecificacaoListaCompra extends Model
{
    use HasFactory;

    protected $table = 'precificacao_lista_compras';

    protected $guarded = [];
}
