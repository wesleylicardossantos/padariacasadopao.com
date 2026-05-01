<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RHOfficialNatureActivity extends Model
{
    protected $table = 'rh_official_nature_activities';

    protected $fillable = [
        'codigo',
        'descricao',
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
