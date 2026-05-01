<?php

namespace App\Modules\RH\Application\Queries;

use App\Models\Funcionario;
use App\Models\HistoricoFuncionario;
use App\Models\RHDocumento;
use App\Models\RHDossieEvento;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

final class EmployeeDossierTimelineQuery
{
    public function execute(Funcionario $funcionario): Collection
    {
        $timeline = collect();

        if (Schema::hasTable('historico_funcionarios')) {
            HistoricoFuncionario::query()
                ->where('funcionario_id', $funcionario->id)
                ->get()
                ->each(function ($row) use ($timeline) {
                    $timeline->push([
                        'data' => $row->created_at,
                        'tipo' => 'historico',
                        'titulo' => ucfirst((string) ($row->tipo ?? 'Histórico')),
                        'descricao' => $row->descricao,
                    ]);
                });
        }

        if (Schema::hasTable('rh_dossie_eventos')) {
            RHDossieEvento::query()
                ->where('funcionario_id', $funcionario->id)
                ->get()
                ->each(function ($row) use ($timeline) {
                    $timeline->push([
                        'data' => $row->data_evento,
                        'tipo' => 'dossie_evento',
                        'titulo' => $row->titulo,
                        'descricao' => $row->descricao,
                    ]);
                });
        }

        if (Schema::hasTable('rh_documentos')) {
            RHDocumento::query()
                ->where('funcionario_id', $funcionario->id)
                ->get()
                ->each(function ($row) use ($timeline) {
                    $timeline->push([
                        'data' => $row->created_at,
                        'tipo' => 'documento',
                        'titulo' => $row->nome ?: 'Documento RH',
                        'descricao' => $row->observacao ?: ('Documento do tipo ' . ($row->tipo ?: 'geral')),
                    ]);
                });
        }

        return $timeline->sortByDesc(fn (array $item) => $item['data'] ? strtotime((string) $item['data']) : 0)->values();
    }
}
