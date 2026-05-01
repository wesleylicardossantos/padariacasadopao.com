<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PrecificacaoCanalVenda extends Model
{
    use HasFactory;

    protected $table = 'precificacao_canais_venda';

    protected $guarded = [];
}
