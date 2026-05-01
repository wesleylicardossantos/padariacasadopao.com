<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HistoricoFuncionario extends Model
{
    protected $table = 'historico_funcionarios';

    protected $fillable = [
        'funcionario_id', 'tipo', 'descricao', 'valor_anterior', 'valor_novo'
    ];

    public function funcionario()
    {
        return $this->belongsTo(Funcionario::class, 'funcionario_id');
    }
}
