<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class EventoSalario extends Model
{
    use HasFactory;

    public const ATIVO_VALUES = [1, '1', 'S', 's', 'SIM', 'sim', 'A', 'a'];
    protected $fillable = [
        'nome', 'codigo', 'tipo', 'metodo', 'condicao', 'ativo', 'empresa_id', 'tipo_valor',
        'ordem_calculo', 'formula', 'sistema_padrao', 'padrao_sistema', 'incide_inss', 'incide_fgts', 'incide_irrf', 'incidencia_inss', 'incidencia_fgts', 'incidencia_irrf'
    ];

    protected $casts = [
        'ativo' => 'boolean',
        'incide_inss' => 'boolean',
        'incide_fgts' => 'boolean',
        'incide_irrf' => 'boolean',
        'sistema_padrao' => 'boolean',
    ];

    public function scopeAtivos(Builder $query): Builder
    {
        return $query->where(function (Builder $builder) {
            $builder->whereNull('ativo')->orWhereIn('ativo', self::ATIVO_VALUES);
        });
    }
}
