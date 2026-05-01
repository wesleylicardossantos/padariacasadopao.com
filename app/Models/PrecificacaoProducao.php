<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PrecificacaoProducao extends Model
{
    use HasFactory;

    protected $table = 'precificacao_producao';

    protected $guarded = [];
}
