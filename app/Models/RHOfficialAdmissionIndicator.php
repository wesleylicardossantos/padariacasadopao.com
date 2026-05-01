<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RHOfficialAdmissionIndicator extends Model
{
    protected $table = 'rh_official_admission_indicators';

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
