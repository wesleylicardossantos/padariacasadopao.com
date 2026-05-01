<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RHDocumento extends Model
{
    protected $table = 'rh_documentos';

    protected $fillable = [
        'empresa_id',
        'funcionario_id',
        'tipo',
        'nome',
        'arquivo',
        'validade',
        'observacao',
        'categoria',
        'origem',
        'metadata_json',
        'usuario_id',
    ];

    protected $casts = [
        'validade' => 'date',
        'metadata_json' => 'array',
    ];

    public function funcionario()
    {
        return $this->belongsTo(Funcionario::class, 'funcionario_id');
    }
}
