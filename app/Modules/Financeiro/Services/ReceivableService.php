<?php

namespace App\Modules\Financeiro\Services;

use App\Models\ContaReceber;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class ReceivableService
{
    public function summary(int $empresaId, $filialId = 'todos'): array
    {
        $hoje = now()->toDateString();
        $ate7 = Carbon::today()->addDays(7)->toDateString();

        $row = $this->baseQuery($empresaId, $filialId)
            ->selectRaw('COUNT(*) as total_qtd')
            ->selectRaw('SUM(CASE WHEN status = 0 THEN valor_integral ELSE 0 END) as pendente_valor')
            ->selectRaw('SUM(CASE WHEN status = 1 THEN COALESCE(NULLIF(valor_recebido, 0), valor_integral) ELSE 0 END) as recebido_valor')
            ->selectRaw('SUM(CASE WHEN status = 0 THEN 1 ELSE 0 END) as qtd_pendente')
            ->selectRaw('SUM(CASE WHEN status = 1 THEN 1 ELSE 0 END) as qtd_recebido')
            ->selectRaw("SUM(CASE WHEN status = 0 AND data_vencimento < ? THEN valor_integral ELSE 0 END) as vencido_valor", [$hoje])
            ->selectRaw("SUM(CASE WHEN status = 0 AND data_vencimento BETWEEN ? AND ? THEN valor_integral ELSE 0 END) as a_vencer_7_dias", [$hoje, $ate7])
            ->first();

        $pendenteValor = (float) ($row->pendente_valor ?? 0);
        $recebidoValor = (float) ($row->recebido_valor ?? 0);

        return [
            'pendente_valor' => $pendenteValor,
            'recebido_valor' => $recebidoValor,
            'total_valor' => $pendenteValor + $recebidoValor,
            'qtd_pendente' => (int) ($row->qtd_pendente ?? 0),
            'qtd_recebido' => (int) ($row->qtd_recebido ?? 0),
            'vencido_valor' => (float) ($row->vencido_valor ?? 0),
            'a_vencer_7_dias' => (float) ($row->a_vencer_7_dias ?? 0),
        ];
    }

    public function monthlyForecast(int $empresaId, $filialId = 'todos', int $months = 6): array
    {
        $months = max(1, min($months, 12));
        $start = now()->copy()->startOfMonth();
        $end = $start->copy()->addMonths($months - 1)->endOfMonth();

        $rows = $this->baseQuery($empresaId, $filialId)
            ->where('status', false)
            ->whereBetween('data_vencimento', [$start->toDateString(), $end->toDateString()])
            ->selectRaw("DATE_FORMAT(data_vencimento, '%Y-%m') as periodo_ref")
            ->selectRaw('SUM(valor_integral) as valor')
            ->selectRaw('COUNT(*) as quantidade')
            ->groupBy('periodo_ref')
            ->pluck('valor', 'periodo_ref');

        $quantidades = $this->baseQuery($empresaId, $filialId)
            ->where('status', false)
            ->whereBetween('data_vencimento', [$start->toDateString(), $end->toDateString()])
            ->selectRaw("DATE_FORMAT(data_vencimento, '%Y-%m') as periodo_ref")
            ->selectRaw('COUNT(*) as quantidade')
            ->groupBy('periodo_ref')
            ->pluck('quantidade', 'periodo_ref');

        $series = [];
        for ($offset = 0; $offset < $months; $offset++) {
            $date = $start->copy()->addMonths($offset);
            $ref = $date->format('Y-m');
            $series[] = [
                'periodo' => $date->format('m/Y'),
                'label' => $date->translatedFormat('M/Y'),
                'valor' => (float) ($rows[$ref] ?? 0),
                'quantidade' => (int) ($quantidades[$ref] ?? 0),
            ];
        }

        return $series;
    }

    public function topDebtors(int $empresaId, $filialId = 'todos', int $limit = 10): array
    {
        $limit = max(1, min($limit, 30));

        return $this->baseQuery($empresaId, $filialId)
            ->where('status', false)
            ->selectRaw("COALESCE(cliente_id, 0) as cliente_ref, COALESCE(MAX(referencia), 'Sem referência') as nome, SUM(valor_integral) as valor, COUNT(*) as quantidade")
            ->groupBy('cliente_ref')
            ->orderByDesc('valor')
            ->limit($limit)
            ->get()
            ->map(fn ($item) => [
                'cliente_id' => (int) $item->cliente_ref,
                'nome' => (string) $item->nome,
                'valor' => (float) $item->valor,
                'quantidade' => (int) $item->quantidade,
            ])
            ->values()
            ->all();
    }

    public function baseQuery(int $empresaId, $filialId = 'todos'): Builder
    {
        $query = ContaReceber::query()->where('empresa_id', $empresaId);

        if ($filialId !== 'todos' && $filialId !== null && $filialId !== '') {
            $query->where('filial_id', $filialId === -1 ? null : $filialId);
        }

        return $query;
    }
}
