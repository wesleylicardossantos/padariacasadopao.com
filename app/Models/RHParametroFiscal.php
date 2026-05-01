<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RHParametroFiscal extends Model
{
    protected $table = 'rh_parametros_fiscais';

    protected $fillable = [
        'competencia',
        'inss_faixas_json',
        'inss_teto',
        'irrf_faixas_json',
        'irrf_dependente',
        'irrf_desconto_simplificado',
        'fgts_percentual',
        'fgts_multa_percentual',
        'ativo',
    ];

    protected $casts = [
        'inss_faixas_json' => 'array',
        'irrf_faixas_json' => 'array',
        'ativo' => 'boolean',
        'inss_teto' => 'float',
        'irrf_dependente' => 'float',
        'irrf_desconto_simplificado' => 'float',
        'fgts_percentual' => 'float',
        'fgts_multa_percentual' => 'float',
    ];
}
