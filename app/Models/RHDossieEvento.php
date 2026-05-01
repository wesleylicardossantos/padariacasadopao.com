<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RHDossieEvento extends Model
{
    protected $table = 'rh_dossie_eventos';

    protected $fillable = [
        'empresa_id',
        'dossie_id',
        'funcionario_id',
        'categoria',
        'titulo',
        'descricao',
        'origem',
        'source_uid',
        'origem_id',
        'data_evento',
        'visibilidade_portal',
        'payload_json',
        'usuario_id',
    ];

    protected $casts = [
        'data_evento' => 'date',
        'visibilidade_portal' => 'boolean',
        'payload_json' => 'array',
    ];

    public function dossie()
    {
        return $this->belongsTo(RHDossie::class, 'dossie_id');
    }

    public function funcionario()
    {
        return $this->belongsTo(Funcionario::class, 'funcionario_id');
    }
}
