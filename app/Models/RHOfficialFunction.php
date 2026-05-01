<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RHOfficialFunction extends Model
{
    protected $table = 'rh_official_functions';

    protected $fillable = [
        'codigo',
        'descricao',
        'descricao_normalizada',
        'cbo_codigo',
        'ativo',
        'fonte',
        'fonte_url',
        'fonte_atualizada_em',
    ];

    protected $casts = [
        'ativo' => 'boolean',
        'fonte_atualizada_em' => 'datetime',
    ];
}
