<?php

namespace App\Modules\RH\Repositories;

use App\Models\RHMovimentacao;
use Illuminate\Support\Facades\Schema;

class MovimentacaoRepository
{
    public function recentes(int $empresaId)
    {
        if (! Schema::hasTable('rh_movimentacoes')) {
            return collect();
        }

        return RHMovimentacao::with('funcionario')
            ->where('empresa_id', $empresaId)
            ->orderBy('data_movimentacao', 'desc')
            ->limit(10)
            ->get();
    }
}
