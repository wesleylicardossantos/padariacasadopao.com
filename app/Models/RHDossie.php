<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RHDossie extends Model
{
    protected $table = 'rh_dossies';

    protected $fillable = [
        'empresa_id',
        'funcionario_id',
        'status',
        'ultima_atualizacao_em',
        'observacoes_internas',
        'metadata_json',
    ];

    protected $casts = [
        'ultima_atualizacao_em' => 'datetime',
        'metadata_json' => 'array',
    ];

    public function funcionario()
    {
        return $this->belongsTo(Funcionario::class, 'funcionario_id');
    }

    public function eventos()
    {
        return $this->hasMany(RHDossieEvento::class, 'dossie_id')->orderByDesc('data_evento')->orderByDesc('id');
    }
}
