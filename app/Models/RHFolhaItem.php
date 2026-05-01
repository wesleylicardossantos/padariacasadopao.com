<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RHFolhaItem extends Model
{
    protected $table = 'rh_folha_itens';

    protected $fillable = [
        'empresa_id', 'competencia_id', 'apuracao_id', 'funcionario_id', 'evento_id', 'codigo',
        'nome', 'tipo', 'condicao', 'referencia', 'valor', 'origem'
    ];

    public function funcionario()
    {
        return $this->belongsTo(Funcionario::class, 'funcionario_id');
    }

    public function apuracao()
    {
        return $this->belongsTo(ApuracaoMensal::class, 'apuracao_id');
    }
}
