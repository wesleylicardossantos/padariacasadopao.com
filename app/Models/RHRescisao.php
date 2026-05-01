<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RHRescisao extends Model
{
    protected $table = 'rh_rescisoes';

    protected $fillable = [
        'empresa_id',
        'funcionario_id',
        'desligamento_id',
        'data_admissao',
        'data_rescisao',
        'motivo',
        'tipo_aviso',
        'dependentes_irrf',
        'descontos_extras',
        'saldo_salario',
        'ferias_vencidas',
        'ferias_proporcionais',
        'terco_ferias',
        'decimo_terceiro',
        'aviso_previo',
        'fgts_base',
        'fgts_deposito',
        'inss',
        'irrf',
        'fgts_multa',
        'total_bruto',
        'total_descontos',
        'total_liquido',
        'observacoes',
        'observacao',
        'status',
        'documentos_json',
        'competencia',
        'processado_em',
        'usuario_id',
    ];

    protected $casts = [
        'data_admissao' => 'date',
        'data_rescisao' => 'date',
        'processado_em' => 'datetime',
        'documentos_json' => 'array',
        'dependentes_irrf' => 'integer',
        'descontos_extras' => 'float',
        'saldo_salario' => 'float',
        'ferias_vencidas' => 'float',
        'ferias_proporcionais' => 'float',
        'terco_ferias' => 'float',
        'decimo_terceiro' => 'float',
        'aviso_previo' => 'float',
        'fgts_base' => 'float',
        'fgts_deposito' => 'float',
        'inss' => 'float',
        'irrf' => 'float',
        'fgts_multa' => 'float',
        'total_bruto' => 'float',
        'total_descontos' => 'float',
        'total_liquido' => 'float',
    ];

    public function getObservacoesAttribute(): ?string
    {
        return $this->attributes['observacoes'] ?? $this->attributes['observacao'] ?? null;
    }

    public function getObservacaoAttribute(): ?string
    {
        return $this->attributes['observacao'] ?? $this->attributes['observacoes'] ?? null;
    }

    public function funcionario()
    {
        return $this->belongsTo(Funcionario::class, 'funcionario_id');
    }

    public function desligamento()
    {
        return $this->belongsTo(RHDesligamento::class, 'desligamento_id');
    }

    public function itens()
    {
        return $this->hasMany(RHRescisaoItem::class, 'rescisao_id')->orderBy('id');
    }
}
