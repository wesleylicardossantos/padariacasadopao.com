<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RHFalta extends Model
{
    protected $table = 'rh_faltas';

    protected $fillable = [
        'empresa_id',
        'funcionario_id',
        'tipo',
        'data_referencia',
        'quantidade_horas',
        'descricao',
        'usuario_id',
    ];

    public function funcionario()
    {
        return $this->belongsTo(Funcionario::class, 'funcionario_id');
    }

    public static function tipos()
    {
        return [
            'falta' => 'Falta',
            'atraso' => 'Atraso',
            'atestado' => 'Atestado',
            'saida_antecipada' => 'Saída antecipada',
        ];
    }
}
