<?php

namespace App\Modules\RH\Repositories;

use App\Models\ApuracaoMensal;
use Illuminate\Support\Facades\Schema;

class ApuracaoMensalRepository
{
    public function sumPagamentosMes(int $empresaId): float
    {
        if (! Schema::hasTable('apuracao_mensals')) {
            return 0;
        }

        return (float) ApuracaoMensal::whereMonth('created_at', date('m'))
            ->whereYear('created_at', date('Y'))
            ->whereHas('funcionario', function ($query) use ($empresaId) {
                $query->where('empresa_id', $empresaId);
            })
            ->sum('valor_final');
    }
}
