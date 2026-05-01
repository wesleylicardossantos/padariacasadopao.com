<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RHOfficialCboOccupation extends Model
{
    protected $table = 'rh_official_cbo_occupations';

    protected $fillable = [
        'codigo',
        'titulo',
        'titulo_normalizado',
        'fonte',
        'fonte_url',
        'fonte_atualizada_em',
    ];

    protected $casts = [
        'fonte_atualizada_em' => 'datetime',
    ];
}
