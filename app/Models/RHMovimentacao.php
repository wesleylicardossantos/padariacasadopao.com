<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RHMovimentacao extends Model
{
    protected $table = 'rh_movimentacoes';

    protected $fillable = [
        'empresa_id',
        'funcionario_id',
        'tipo',
        'descricao',
        'cargo_anterior',
        'cargo_novo',
        'valor_anterior',
        'valor_novo',
        'data_movimentacao',
        'status_gerado',
        'usuario_id'
    ];

    public function funcionario()
    {
        return $this->belongsTo(Funcionario::class, 'funcionario_id');
    }

    public static function tipos()
    {
        return [
            'salario' => 'Reajuste Salarial',
            'promocao' => 'Promoção',
            'cargo' => 'Mudança de Cargo',
            'demissao' => 'Demissão / Inativação',
            'advertencia' => 'Advertência',
            'elogio' => 'Elogio',
            'outro' => 'Outro'
        ];
    }
}
