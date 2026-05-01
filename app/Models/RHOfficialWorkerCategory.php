<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RHOfficialWorkerCategory extends Model
{
    protected $table = 'rh_official_worker_categories';

    protected $fillable = [
        'codigo',
        'descricao',
        'grupo',
        'inicio_vigencia',
        'fim_vigencia',
        'ativo',
        'fonte',
        'fonte_url',
        'fonte_atualizada_em',
    ];

    protected $casts = [
        'inicio_vigencia' => 'date',
        'fim_vigencia' => 'date',
        'ativo' => 'boolean',
        'fonte_atualizada_em' => 'datetime',
    ];
}
