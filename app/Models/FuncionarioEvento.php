<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class FuncionarioEvento extends Model
{
    use HasFactory;

    public const ATIVO_VALUES = [1, '1', 'S', 's', 'SIM', 'sim', 'A', 'a'];

    protected $fillable = [
        'empresa_id', 'evento_id', 'funcionario_id', 'condicao', 'metodo', 'valor', 'ativo', 'referencia', 'tipo_calculo'
    ];

    public function scopeAtivos(Builder $query): Builder
    {
        return $query->where(function (Builder $builder) {
            $builder->whereNull('ativo')->orWhereIn('ativo', self::ATIVO_VALUES);
        });
    }

    public function evento()
    {
        return $this->belongsTo(EventoSalario::class, 'evento_id');
    }

    public function funcionario()
    {
        return $this->belongsTo(Funcionario::class, 'funcionario_id');
    }
}

