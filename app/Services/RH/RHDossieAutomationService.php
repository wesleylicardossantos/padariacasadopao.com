<?php

namespace App\Services\RH;

use App\Models\Funcionario;
use App\Models\FuncionarioFichaAdmissao;
use App\Models\RHDocumento;
use App\Models\RHDossie;
use App\Models\RHDossieEvento;
use App\Models\RHFerias;
use App\Models\RHMovimentacao;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class RHDossieAutomationService
{
    public function syncFuncionario(Funcionario $funcionario, ?int $empresaId = null): array
    {
        if (!$this->isReady()) {
            return ['created' => 0, 'updated' => 0, 'skipped' => 0];
        }

        $empresaId = $empresaId ?: (int) ($funcionario->empresa_id ?? 0);
        $dossie = RHDossie::firstOrCreate(
            ['empresa_id' => $empresaId, 'funcionario_id' => $funcionario->id],
            ['status' => $this->resolveStatus($funcionario), 'ultima_atualizacao_em' => now()]
        );

        $created = 0;
        $updated = 0;
        $skipped = 0;

        foreach ($this->collectAutomaticEvents($funcionario, $empresaId) as $item) {
            $attrs = [
                'empresa_id' => $empresaId,
                'funcionario_id' => $funcionario->id,
                'source_uid' => $item['source_uid'],
            ];

            $values = [
                'dossie_id' => $dossie->id,
                'categoria' => $item['categoria'],
                'titulo' => $item['titulo'],
                'descricao' => $item['descricao'],
                'origem' => 'automacao',
                'data_evento' => $item['data_evento'],
                'visibilidade_portal' => $item['visibilidade_portal'] ?? false,
                'payload_json' => $item['payload_json'] ?? null,
                'usuario_id' => null,
                'updated_at' => now(),
            ];

            $existing = RHDossieEvento::query()->where($attrs)->first();
            if ($existing) {
                $dirty = false;
                foreach ($values as $key => $value) {
                    if ($existing->{$key} != $value) {
                        $existing->{$key} = $value;
                        $dirty = true;
                    }
                }
                if ($dirty) {
                    $existing->save();
                    $updated++;
                } else {
                    $skipped++;
                }
                continue;
            }

            RHDossieEvento::create($attrs + $values + ['created_at' => now()]);
            $created++;
        }

        $dossie->forceFill([
            'status' => $this->resolveStatus($funcionario),
            'ultima_atualizacao_em' => now(),
        ])->save();

        return compact('created', 'updated', 'skipped');
    }

    public function syncEmpresa(?int $empresaId = null): array
    {
        if (!Schema::hasTable('funcionarios')) {
            return ['funcionarios' => 0, 'created' => 0, 'updated' => 0, 'skipped' => 0];
        }

        $funcionarios = Funcionario::query()->comInativos()
            ->when(($empresaId ?? 0) > 0, fn ($q) => $q->where('empresa_id', $empresaId))
            ->get();

        $stats = ['funcionarios' => $funcionarios->count(), 'created' => 0, 'updated' => 0, 'skipped' => 0];
        foreach ($funcionarios as $funcionario) {
            $result = $this->syncFuncionario($funcionario, (int) ($empresaId ?: $funcionario->empresa_id));
            $stats['created'] += $result['created'];
            $stats['updated'] += $result['updated'];
            $stats['skipped'] += $result['skipped'];
        }

        return $stats;
    }

    public function dashboardMetrics(int $empresaId, int $mes, int $ano): array
    {
        if (!$this->isReady()) {
            return [
                'total_dossies' => 0,
                'dossies_arquivados' => 0,
                'documentos_vencidos' => 0,
                'documentos_mes' => 0,
                'eventos_automaticos_mes' => 0,
                'funcionarios_pendencia_documental' => 0,
                'categorias_documento' => [],
                'timeline_automacao' => [],
                'alertas' => [],
            ];
        }

        $inicio = sprintf('%04d-%02d-01', $ano, $mes);
        $fim = date('Y-m-t', strtotime($inicio));

        $documentos = RHDocumento::query()->where('empresa_id', $empresaId);
        $eventos = RHDossieEvento::query()->where('empresa_id', $empresaId);

        $documentosVencidos = (clone $documentos)->whereNotNull('validade')->whereDate('validade', '<', now()->toDateString())->count();
        $documentosMes = (clone $documentos)->whereBetween('created_at', [$inicio . ' 00:00:00', $fim . ' 23:59:59'])->count();
        $eventosAutomaticosMes = (clone $eventos)->where('origem', 'automacao')->whereBetween('data_evento', [$inicio, $fim])->count();
        $funcionariosPendencia = (clone $documentos)
            ->select('funcionario_id')
            ->where(function ($q) {
                $q->whereNull('arquivo')
                    ->orWhere('arquivo', '')
                    ->orWhere(function ($w) {
                        $w->whereNotNull('validade')->whereDate('validade', '<', now()->toDateString());
                    });
            })
            ->distinct()
            ->count('funcionario_id');

        $categorias = (clone $documentos)
            ->selectRaw("COALESCE(categoria, 'outro') as categoria, COUNT(*) as total")
            ->groupBy('categoria')
            ->orderByDesc('total')
            ->get()
            ->map(fn ($row) => ['categoria' => $row->categoria ?: 'outro', 'total' => (int) $row->total])
            ->values()
            ->all();

        $timelineAutomacao = [];
        for ($i = 5; $i >= 0; $i--) {
            $ref = strtotime($inicio . " -{$i} month");
            $mesRef = (int) date('m', $ref);
            $anoRef = (int) date('Y', $ref);
            $inicioRef = sprintf('%04d-%02d-01', $anoRef, $mesRef);
            $fimRef = date('Y-m-t', strtotime($inicioRef));
            $timelineAutomacao[] = [
                'competencia' => sprintf('%02d/%04d', $mesRef, $anoRef),
                'documentos' => (clone $documentos)->whereBetween('created_at', [$inicioRef . ' 00:00:00', $fimRef . ' 23:59:59'])->count(),
                'eventos' => (clone $eventos)->whereBetween('data_evento', [$inicioRef, $fimRef])->count(),
                'automaticos' => (clone $eventos)->where('origem', 'automacao')->whereBetween('data_evento', [$inicioRef, $fimRef])->count(),
            ];
        }

        $alertas = [];
        if ($documentosVencidos > 0) {
            $alertas[] = sprintf('%d documento(s) do dossiê estão vencidos.', $documentosVencidos);
        }
        if ($funcionariosPendencia > 0) {
            $alertas[] = sprintf('%d funcionário(s) possuem pendência documental ou arquivo vencido.', $funcionariosPendencia);
        }
        if ($eventosAutomaticosMes === 0) {
            $alertas[] = 'Nenhum evento automático foi sincronizado na competência atual.';
        }

        return [
            'total_dossies' => RHDossie::query()->where('empresa_id', $empresaId)->count(),
            'dossies_arquivados' => RHDossie::query()->where('empresa_id', $empresaId)->where('status', 'arquivado')->count(),
            'documentos_vencidos' => $documentosVencidos,
            'documentos_mes' => $documentosMes,
            'eventos_automaticos_mes' => $eventosAutomaticosMes,
            'funcionarios_pendencia_documental' => $funcionariosPendencia,
            'categorias_documento' => $categorias,
            'timeline_automacao' => $timelineAutomacao,
            'alertas' => $alertas,
        ];
    }

    protected function collectAutomaticEvents(Funcionario $funcionario, int $empresaId): Collection
    {
        $items = collect();

        if (Schema::hasTable('funcionarios_ficha_admissao')) {
            $ficha = FuncionarioFichaAdmissao::query()->where('funcionario_id', $funcionario->id)->first();
            if ($ficha && !empty($ficha->data_admissao)) {
                $items->push([
                    'source_uid' => 'admissao:' . $funcionario->id,
                    'categoria' => 'admissao',
                    'titulo' => 'Admissão automatizada',
                    'descricao' => 'Evento automático de admissão sincronizado a partir da ficha admissional.',
                    'data_evento' => $ficha->data_admissao,
                    'payload_json' => ['source_table' => 'funcionarios_ficha_admissao'],
                ]);
            }
        }

        if (Schema::hasTable('rh_movimentacoes')) {
            RHMovimentacao::query()->where('empresa_id', $empresaId)->where('funcionario_id', $funcionario->id)->get()
                ->each(function ($row) use ($items) {
                    $items->push([
                        'source_uid' => 'movimentacao:' . $row->id,
                        'categoria' => 'movimentacao',
                        'titulo' => 'Movimentação automatizada',
                        'descricao' => trim(($row->tipo ?? 'Movimentação') . ' - ' . ($row->descricao ?? 'Sem descrição.')),
                        'data_evento' => $row->data_movimentacao,
                        'payload_json' => ['source_table' => 'rh_movimentacoes', 'source_id' => $row->id],
                    ]);
                });
        }

        if (Schema::hasTable('rh_ferias')) {
            RHFerias::query()->where('empresa_id', $empresaId)->where('funcionario_id', $funcionario->id)->get()
                ->each(function ($row) use ($items) {
                    $items->push([
                        'source_uid' => 'ferias:' . $row->id,
                        'categoria' => 'ferias',
                        'titulo' => 'Férias automatizadas',
                        'descricao' => sprintf('Período de férias sincronizado automaticamente de %s até %s.', optional($row->data_inicio)->format('d/m/Y') ?: date('d/m/Y', strtotime((string) $row->data_inicio)), optional($row->data_fim)->format('d/m/Y') ?: date('d/m/Y', strtotime((string) $row->data_fim))),
                        'data_evento' => $row->data_inicio,
                        'payload_json' => ['source_table' => 'rh_ferias', 'source_id' => $row->id],
                    ]);
                });
        }

        if (Schema::hasTable('apuracao_mensals')) {
            DB::table('apuracao_mensals')->where('empresa_id', $empresaId)->where('funcionario_id', $funcionario->id)->get()
                ->each(function ($row) use ($items) {
                    $items->push([
                        'source_uid' => 'folha:' . $row->id,
                        'categoria' => 'folha',
                        'titulo' => 'Fechamento automático de folha',
                        'descricao' => sprintf('Competência %02d/%04d sincronizada automaticamente com valor final de R$ %s.', (int) $row->mes, (int) $row->ano, number_format((float) ($row->valor_final ?? 0), 2, ',', '.')),
                        'data_evento' => sprintf('%04d-%02d-01', (int) $row->ano, (int) $row->mes),
                        'payload_json' => ['source_table' => 'apuracao_mensals', 'source_id' => $row->id],
                        'visibilidade_portal' => true,
                    ]);
                });
        }

        if (Schema::hasTable('rh_desligamentos')) {
            DB::table('rh_desligamentos')->where('empresa_id', $empresaId)->where('funcionario_id', $funcionario->id)->get()
                ->each(function ($row) use ($items) {
                    $items->push([
                        'source_uid' => 'desligamento:' . $row->id,
                        'categoria' => 'desligamento',
                        'titulo' => 'Desligamento automatizado',
                        'descricao' => trim(($row->tipo ?? 'Desligamento') . ' - ' . ($row->motivo ?? 'Sem motivo informado.')),
                        'data_evento' => $row->data_desligamento,
                        'payload_json' => ['source_table' => 'rh_desligamentos', 'source_id' => $row->id],
                    ]);
                });
        }

        return $items->filter(fn ($item) => !empty($item['data_evento']) && !empty($item['source_uid']));
    }

    protected function resolveStatus(Funcionario $funcionario): string
    {
        return in_array($funcionario->ativo, [0, '0', 'N', 'n', 'NAO', 'nao', 'NÃO', 'não', 'I', 'i'], true) ? 'arquivado' : 'ativo';
    }

    protected function isReady(): bool
    {
        return Schema::hasTable('rh_dossies') && Schema::hasTable('rh_dossie_eventos');
    }
}
