<?php

namespace App\Modules\RH\Services;

use App\Models\ApuracaoMensal;
use App\Models\Funcionario;
use App\Models\RHMovimentacao;
use App\Modules\RH\Support\RHContext;
use App\Services\RHFolhaCompetenciaService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class RHDashboardModuleService
{
    /** @var array<string,bool> */
    private array $tableExistsCache = [];

    /** @var array<string,bool> */
    private array $columnExistsCache = [];

    public function __construct(
        private RHFolhaCompetenciaService $folhaCompetenciaService,
    ) {
    }

    public function montarDashboard(int $empresaId, ?int $mesSelecionado = null, ?int $anoSelecionado = null): array
    {
        $empresaId = $this->resolveEmpresaId($empresaId);
        $cacheKey = sprintf('rh_dashboard_saas:%d:%s:%02d:%04d', $empresaId, now()->format('YmdHi'), max(1, min(12, (int) ($mesSelecionado ?: now()->format('m')))), (int) ($anoSelecionado ?: now()->format('Y')));

        return Cache::remember($cacheKey, now()->addMinutes(1), function () use ($empresaId, $mesSelecionado, $anoSelecionado) {
            $now = now();
            $mesAtual = (int) $now->format('m');
            $anoAtual = (int) $now->format('Y');
            $mes = $mesSelecionado && $mesSelecionado >= 1 && $mesSelecionado <= 12 ? (int) $mesSelecionado : $mesAtual;
            $ano = $anoSelecionado && $anoSelecionado >= 2000 && $anoSelecionado <= 2100 ? (int) $anoSelecionado : $anoAtual;

            if ($mes === $mesAtual && $ano === $anoAtual) {
                $this->tryAutoProcessarFolhaAtual($empresaId, $mes, $ano);
            }

            $contextoCompetencia = $mesSelecionado || $anoSelecionado
                ? ['mes' => $mes, 'ano' => $ano, 'status' => $this->resolverStatusCompetenciaSelecionada($empresaId, $mes, $ano)]
                : $this->resolverCompetenciaDashboard($empresaId, $mes, $ano);
            $mesReferencia = (int) $contextoCompetencia['mes'];
            $anoReferencia = (int) $contextoCompetencia['ano'];
            $mesNomeReferencia = $this->mesNome($mesReferencia);

            $funcionarioQuery = Funcionario::query()->withoutGlobalScope('rh_status_visibility');
            if ($empresaId > 0 && $this->hasColumn('funcionarios', 'empresa_id')) {
                $funcionarioQuery->where('empresa_id', $empresaId);
            }

            $selectAtivos = $this->hasColumn('funcionarios', 'ativo')
                ? "SUM(CASE WHEN ativo IS NULL OR ativo IN (1, '1', 'S', 's', 'SIM', 'sim', 'A', 'a') THEN 1 ELSE 0 END)"
                : 'COUNT(*)';
            $selectInativos = $this->hasColumn('funcionarios', 'ativo')
                ? "SUM(CASE WHEN ativo IN (0, '0', 'N', 'n', 'NAO', 'nao', 'NÃO', 'não', 'I', 'i') THEN 1 ELSE 0 END)"
                : '0';
            $selectFolha = $this->hasColumn('funcionarios', 'salario')
                ? ($this->hasColumn('funcionarios', 'ativo')
                    ? "SUM(CASE WHEN ativo IS NULL OR ativo IN (1, '1', 'S', 's', 'SIM', 'sim', 'A', 'a') THEN COALESCE(salario, 0) ELSE 0 END)"
                    : 'SUM(COALESCE(salario, 0))')
                : '0';

            $resumoFuncionarios = $funcionarioQuery
                ->selectRaw('COUNT(*) as total')
                ->selectRaw($selectAtivos . ' as ativos')
                ->selectRaw($selectInativos . ' as inativos')
                ->selectRaw($selectFolha . ' as folha_mensal')
                ->first();

            $totalFuncionarios = (int) ($resumoFuncionarios->total ?? 0);
            $ativos = (int) ($resumoFuncionarios->ativos ?? 0);
            $inativos = (int) ($resumoFuncionarios->inativos ?? 0);
            $folhaMensal = round((float) ($resumoFuncionarios->folha_mensal ?? 0), 2);
            $folhaBase = $folhaMensal;
            $percentualAtivos = $totalFuncionarios > 0 ? round(($ativos / $totalFuncionarios) * 100, 2) : 0.0;
            $custoMedioColaborador = $ativos > 0 ? round($folhaMensal / $ativos, 2) : 0.0;

            $admissoesMes = 0;
            if ($this->hasTable('funcionarios_ficha_admissao')) {
                $admissoesMes = (int) \App\Models\FuncionarioFichaAdmissao::query()
                    ->when($empresaId > 0 && $this->hasColumn('funcionarios_ficha_admissao', 'empresa_id'), fn (Builder $q) => $q->where('empresa_id', $empresaId))
                    ->whereMonth('data_admissao', $mesReferencia)
                    ->whereYear('data_admissao', $anoReferencia)
                    ->count();
            }

            $desligamentosMes = 0;
            if ($this->hasTable('rh_desligamentos')) {
                $desligamentosMes = (int) \App\Models\RHDesligamento::query()
                    ->when($empresaId > 0 && $this->hasColumn('rh_desligamentos', 'empresa_id'), fn (Builder $q) => $q->where('empresa_id', $empresaId))
                    ->whereMonth('data_desligamento', $mesReferencia)
                    ->whereYear('data_desligamento', $anoReferencia)
                    ->count();
            }

            $faltasMes = 0;
            $atrasosMes = 0;
            $atestadosMes = 0;
            if ($this->hasTable('rh_faltas')) {
                $faltasAgrupadas = \App\Models\RHFalta::query()
                    ->select('tipo', DB::raw('COUNT(*) as total'))
                    ->when($empresaId > 0 && $this->hasColumn('rh_faltas', 'empresa_id'), fn (Builder $q) => $q->where('empresa_id', $empresaId))
                    ->whereMonth('data_referencia', $mesReferencia)
                    ->whereYear('data_referencia', $anoReferencia)
                    ->groupBy('tipo')
                    ->pluck('total', 'tipo');

                $faltasMes = (int) ($faltasAgrupadas['falta'] ?? 0);
                $atrasosMes = (int) ($faltasAgrupadas['atraso'] ?? 0);
                $atestadosMes = (int) ($faltasAgrupadas['atestado'] ?? 0);
            }

            $turnover = $ativos > 0 ? round((($admissoesMes + $desligamentosMes) / $ativos) * 100, 2) : 0.0;

            $referenciaAnterior = Carbon::create($anoReferencia, $mesReferencia, 1)->subMonth();
            $absenteismoAnterior = $this->consultarAbsenteismoMes($empresaId, (int) $referenciaAnterior->format('m'), (int) $referenciaAnterior->format('Y'));

            $totalProventosMes = 0.0;
            $totalDescontosMes = 0.0;
            $liquidoMes = 0.0;
            $apagamentosMes = 0.0;
            $ultimaCompetencia = null;
            $serieFolha = collect();
            $serieHeadcount = collect();
            $competenciaLabel = sprintf('%02d/%04d', $mesReferencia, $anoReferencia);
            $competenciaStatus = $contextoCompetencia['status'] ?? 'aberta';
            $folhaProcessadaNoMes = false;

            if ($this->hasTable('apuracao_mensals')) {
                $apuracaoBase = ApuracaoMensal::query()
                    ->where('ano', $anoReferencia)
                    ->where('mes', $mesNomeReferencia)
                    ->when($empresaId > 0 && $this->hasColumn('apuracao_mensals', 'empresa_id'), function (Builder $query) use ($empresaId) {
                        $query->where('empresa_id', $empresaId);
                    }, function (Builder $query) use ($empresaId) {
                        if ($empresaId > 0) {
                            $query->whereHas('funcionario', function (Builder $funcionarioQuery) use ($empresaId) {
                                $funcionarioQuery->where('empresa_id', $empresaId);
                            });
                        }
                    });

                $folhaProcessadaNoMes = (clone $apuracaoBase)->exists();
                $totalProventosMes = $this->hasColumn('apuracao_mensals', 'total_proventos')
                    ? round((float) (clone $apuracaoBase)->sum('total_proventos'), 2)
                    : round((float) (clone $apuracaoBase)->sum('valor_final'), 2);

                $totalDescontosMes = $this->hasColumn('apuracao_mensals', 'total_descontos')
                    ? round((float) (clone $apuracaoBase)->sum('total_descontos'), 2)
                    : 0.0;

                $liquidoMes = $this->hasColumn('apuracao_mensals', 'liquido')
                    ? round((float) (clone $apuracaoBase)->sum('liquido'), 2)
                    : round((float) (clone $apuracaoBase)->sum('valor_final'), 2);

                $apagamentosMes = $this->hasColumn('apuracao_mensals', 'created_at')
                    ? round((float) (clone $apuracaoBase)
                        ->whereMonth('created_at', $mesReferencia)
                        ->whereYear('created_at', $anoReferencia)
                        ->sum($this->hasColumn('apuracao_mensals', 'liquido') ? 'liquido' : 'valor_final'), 2)
                    : $liquidoMes;

                $ultimaApuracao = ApuracaoMensal::query()
                    ->when($empresaId > 0 && $this->hasColumn('apuracao_mensals', 'empresa_id'), function (Builder $query) use ($empresaId) {
                        $query->where('empresa_id', $empresaId);
                    }, function (Builder $query) use ($empresaId) {
                        if ($empresaId > 0) {
                            $query->whereHas('funcionario', function (Builder $funcionarioQuery) use ($empresaId) {
                                $funcionarioQuery->where('empresa_id', $empresaId);
                            });
                        }
                    })
                    ->orderByDesc('ano')
                    ->orderByRaw($this->orderByMesCase())
                    ->first();

                if ($ultimaApuracao) {
                    $ultimaCompetencia = sprintf('%s/%s', $this->mesNumeroPorNome((string) $ultimaApuracao->mes), $ultimaApuracao->ano);
                }

                $serieFolha = $this->montarSerieFolha($empresaId, $mesReferencia, $anoReferencia);
                $serieHeadcount = $this->montarSerieHeadcount($empresaId, $mesReferencia, $anoReferencia);
            }

            if ($liquidoMes <= 0 && $this->hasTable('rh_folha_fechamentos')) {
                $fechamento = \App\Models\RHFolhaFechamento::query()
                    ->when($empresaId > 0, fn (Builder $q) => $q->where('empresa_id', $empresaId))
                    ->where('mes', $mesReferencia)
                    ->where('ano', $anoReferencia)
                    ->latest('id')
                    ->first();

                if ($fechamento) {
                    $totalProventosMes = round((float) ($fechamento->eventos_total ?? $totalProventosMes), 2);
                    $totalDescontosMes = round((float) ($fechamento->descontos_total ?? $totalDescontosMes), 2);
                    $liquidoMes = round((float) ($fechamento->liquido_total ?? $liquidoMes), 2);
                    $competenciaStatus = $fechamento->status ?: $competenciaStatus;
                }
            }

            $ticketMedioFolha = $ativos > 0 ? round(($liquidoMes > 0 ? $liquidoMes : $folhaMensal) / $ativos, 2) : 0.0;

            $colunasTopSalarios = ['id', 'nome', 'salario'];
            if ($this->hasColumn('funcionarios', 'funcao')) {
                $colunasTopSalarios[] = 'funcao';
            }
            if ($this->hasColumn('funcionarios', 'cargo')) {
                $colunasTopSalarios[] = 'cargo';
            }

            $topSalariosQuery = Funcionario::query()->withoutGlobalScope('rh_status_visibility');
            if ($empresaId > 0 && $this->hasColumn('funcionarios', 'empresa_id')) {
                $topSalariosQuery->where('empresa_id', $empresaId);
            }
            if ($this->hasColumn('funcionarios', 'ativo')) {
                $topSalariosQuery->where(function (Builder $query) {
                    $query->whereNull('ativo')->orWhereIn('ativo', [1, '1', 'S', 's', 'SIM', 'sim', 'A', 'a']);
                });
            }
            if ($this->hasColumn('funcionarios', 'salario')) {
                $topSalariosQuery->orderByDesc('salario');
            } elseif ($this->hasColumn('funcionarios', 'nome')) {
                $topSalariosQuery->orderBy('nome');
            } else {
                $topSalariosQuery->latest('id');
            }

            $topSalarios = $topSalariosQuery->limit(8)->get($colunasTopSalarios)->map(function ($funcionario) {
                if (!isset($funcionario->cargo) || blank($funcionario->cargo)) {
                    $funcionario->cargo = $funcionario->funcao ?? null;
                }
                if (!isset($funcionario->funcao) || blank($funcionario->funcao)) {
                    $funcionario->funcao = $funcionario->cargo ?? 'Sem função';
                }
                return $funcionario;
            });

            $movimentacoesRecentes = collect();
            if ($this->hasTable('rh_movimentacoes')) {
                $movimentacoesRecentes = RHMovimentacao::query()
                    ->with('funcionario:id,nome')
                    ->when($empresaId > 0 && $this->hasColumn('rh_movimentacoes', 'empresa_id'), fn (Builder $q) => $q->where('empresa_id', $empresaId))
                    ->orderByDesc('data_movimentacao')
                    ->orderByDesc('id')
                    ->limit(10)
                    ->get();
            }

            $feriasProximas = collect();
            if ($this->hasTable('rh_ferias')) {
                $feriasProximas = \App\Models\RHFerias::query()
                    ->with('funcionario:id,nome')
                    ->select('id', 'empresa_id', 'funcionario_id', 'data_inicio', 'data_fim', 'status')
                    ->when($empresaId > 0 && $this->hasColumn('rh_ferias', 'empresa_id'), fn (Builder $q) => $q->where('empresa_id', $empresaId))
                    ->whereIn('status', ['programada', 'pendente', 'gozo'])
                    ->orderBy('data_inicio')
                    ->limit(8)
                    ->get();
            }

            $alertas = $this->montarAlertas($empresaId, $now);
            [$graficoMeses, $graficoAdmissoes, $graficoDesligamentos] = $this->montarFluxoAnual($empresaId, $anoReferencia);

            $alertasCount = $alertas->count();
            $feriasPendentes = $feriasProximas->whereIn('status', ['pendente', 'programada'])->count();
            $movimentacoesMes = $movimentacoesRecentes
                ->filter(fn ($item) => !empty($item->data_movimentacao) && Carbon::parse($item->data_movimentacao)->month === $mesReferencia && Carbon::parse($item->data_movimentacao)->year === $anoReferencia)
                ->count();
            $variacaoHeadcount = $admissoesMes - $desligamentosMes;
            $saudeFolha = $folhaMensal > 0 ? round(($liquidoMes / max($folhaMensal, 1)) * 100, 2) : 0.0;

            return compact(
                'empresaId',
                'totalFuncionarios',
                'ativos',
                'inativos',
                'percentualAtivos',
                'folhaMensal',
                'folhaBase',
                'custoMedioColaborador',
                'admissoesMes',
                'desligamentosMes',
                'faltasMes',
                'atrasosMes',
                'atestadosMes',
                'turnover',
                'apagamentosMes',
                'totalProventosMes',
                'totalDescontosMes',
                'liquidoMes',
                'ticketMedioFolha',
                'ultimaCompetencia',
                'serieFolha',
                'serieHeadcount',
                'topSalarios',
                'movimentacoesRecentes',
                'feriasProximas',
                'alertas',
                'graficoMeses',
                'graficoAdmissoes',
                'graficoDesligamentos',
                'competenciaLabel',
                'competenciaStatus',
                'folhaProcessadaNoMes',
                'alertasCount',
                'feriasPendentes',
                'movimentacoesMes',
                'variacaoHeadcount',
                'saudeFolha'
            ) + [
                'movimentacoes' => $movimentacoesRecentes,
            ];
        });
    }

    private function resolveEmpresaId(int $empresaId): int
    {
        if ($empresaId > 0) {
            return $empresaId;
        }

        $empresaId = RHContext::empresaId(request());
        if ($empresaId > 0) {
            return $empresaId;
        }

        if ($this->hasTable('funcionarios') && $this->hasColumn('funcionarios', 'empresa_id')) {
            $empresaMaisRecorrente = (int) Funcionario::query()->withoutGlobalScope('rh_status_visibility')
                ->whereNotNull('empresa_id')
                ->groupBy('empresa_id')
                ->orderByRaw('COUNT(*) DESC')
                ->value('empresa_id');

            if ($empresaMaisRecorrente > 0) {
                return $empresaMaisRecorrente;
            }
        }

        if ($this->hasTable('apuracao_mensals') && $this->hasColumn('apuracao_mensals', 'empresa_id')) {
            return (int) (ApuracaoMensal::query()->whereNotNull('empresa_id')->orderByDesc('id')->value('empresa_id') ?? 0);
        }

        return 0;
    }

    private function tryAutoProcessarFolhaAtual(int $empresaId, int $mes, int $ano): void
    {
        if ($empresaId <= 0 || !$this->hasTable('apuracao_mensals') || !$this->hasTable('rh_competencias')) {
            return;
        }

        $mesNome = $this->mesNome($mes);
        $jaTemApuracao = ApuracaoMensal::query()
            ->when($this->hasColumn('apuracao_mensals', 'empresa_id'), fn (Builder $q) => $q->where('empresa_id', $empresaId))
            ->where('mes', $mesNome)
            ->where('ano', $ano)
            ->exists();

        if ($jaTemApuracao) {
            return;
        }

        $temAtivos = Funcionario::query()
            ->when($this->hasColumn('funcionarios', 'empresa_id'), fn (Builder $q) => $q->where('empresa_id', $empresaId))
            ->when($this->hasColumn('funcionarios', 'ativo'), fn (Builder $q) => $q->whereIn('ativo', [1, '1', 'S', 's', 'SIM', 'sim', 'A', 'a']))
            ->exists();

        if (!$temAtivos) {
            return;
        }

        $lockKey = sprintf('rh:auto_folha:%d:%02d:%04d', $empresaId, $mes, $ano);
        $lock = Cache::lock($lockKey, 20);

        if (!$lock->get()) {
            return;
        }

        try {
            $this->folhaCompetenciaService->processar($empresaId, $mes, $ano, false, true, null, null);
            Cache::forget(sprintf('rh_dashboard_saas:%d:%s', $empresaId, now()->format('YmdHi')));
        } catch (\Throwable $e) {
            Log::warning('Falha ao auto processar folha no dashboard RH.', [
                'empresa_id' => $empresaId,
                'mes' => $mes,
                'ano' => $ano,
                'erro' => $e->getMessage(),
            ]);
        } finally {
            optional($lock)->release();
        }
    }

    private function resolverStatusCompetenciaSelecionada(int $empresaId, int $mes, int $ano): string
    {
        if ($this->hasTable('rh_competencias')) {
            $competencia = \App\Models\RHCompetencia::query()
                ->when($empresaId > 0, fn (Builder $q) => $q->where('empresa_id', $empresaId))
                ->where('mes', $mes)
                ->where('ano', $ano)
                ->latest('id')
                ->first();

            if ($competencia) {
                return (string) ($competencia->status ?? 'aberta');
            }
        }

        if ($this->hasTable('apuracao_mensals')) {
            $query = ApuracaoMensal::query()
                ->where('ano', $ano)
                ->where('mes', $this->mesNome($mes));

            if ($empresaId > 0 && $this->hasColumn('apuracao_mensals', 'empresa_id')) {
                $query->where('empresa_id', $empresaId);
            } elseif ($empresaId > 0) {
                $query->whereHas('funcionario', function (Builder $funcionarioQuery) use ($empresaId) {
                    $funcionarioQuery->where('empresa_id', $empresaId);
                });
            }

            if ($query->exists()) {
                return 'processada';
            }
        }

        return 'aberta';
    }

    private function consultarAbsenteismoMes(int $empresaId, int $mes, int $ano): array
    {
        $dados = ['faltas' => 0, 'atrasos' => 0, 'atestados' => 0];

        if (!$this->hasTable('rh_faltas')) {
            return $dados;
        }

        $faltasAgrupadas = \App\Models\RHFalta::query()
            ->select('tipo', DB::raw('COUNT(*) as total'))
            ->when($empresaId > 0 && $this->hasColumn('rh_faltas', 'empresa_id'), fn (Builder $q) => $q->where('empresa_id', $empresaId))
            ->whereMonth('data_referencia', $mes)
            ->whereYear('data_referencia', $ano)
            ->groupBy('tipo')
            ->pluck('total', 'tipo');

        $dados['faltas'] = (int) ($faltasAgrupadas['falta'] ?? 0);
        $dados['atrasos'] = (int) ($faltasAgrupadas['atraso'] ?? 0);
        $dados['atestados'] = (int) ($faltasAgrupadas['atestado'] ?? 0);

        return $dados;
    }

    private function resolverCompetenciaDashboard(int $empresaId, int $mesAtual, int $anoAtual): array
    {
        if ($this->hasTable('rh_competencias')) {
            $competencia = \App\Models\RHCompetencia::query()
                ->when($empresaId > 0, fn (Builder $q) => $q->where('empresa_id', $empresaId))
                ->orderByDesc('ano')
                ->orderByDesc('mes')
                ->orderByDesc('id')
                ->first();

            if ($competencia) {
                return [
                    'mes' => (int) $competencia->mes,
                    'ano' => (int) $competencia->ano,
                    'status' => (string) ($competencia->status ?? 'aberta'),
                ];
            }
        }

        if ($this->hasTable('apuracao_mensals')) {
            $apuracao = ApuracaoMensal::query()
                ->when($empresaId > 0 && $this->hasColumn('apuracao_mensals', 'empresa_id'), fn (Builder $q) => $q->where('empresa_id', $empresaId))
                ->orderByDesc('ano')
                ->orderByRaw($this->orderByMesCase())
                ->orderByDesc('id')
                ->first();

            if ($apuracao) {
                return [
                    'mes' => (int) $this->mesNumeroPorNome((string) $apuracao->mes),
                    'ano' => (int) $apuracao->ano,
                    'status' => 'processada',
                ];
            }
        }

        return [
            'mes' => $mesAtual,
            'ano' => $anoAtual,
            'status' => 'aberta',
        ];
    }

    private function montarSerieFolha(int $empresaId, int $mes, int $ano): Collection
    {
        $serie = collect();

        for ($i = 5; $i >= 0; $i--) {
            $referencia = Carbon::create($ano, $mes, 1)->subMonths($i);
            $mesNome = $this->mesNome((int) $referencia->format('m'));
            $anoRef = (int) $referencia->format('Y');

            $query = ApuracaoMensal::query()
                ->where('ano', $anoRef)
                ->where('mes', $mesNome)
                ->when($empresaId > 0 && $this->hasColumn('apuracao_mensals', 'empresa_id'), function (Builder $q) use ($empresaId) {
                    $q->where('empresa_id', $empresaId);
                }, function (Builder $q) use ($empresaId) {
                    if ($empresaId > 0) {
                        $q->whereHas('funcionario', function (Builder $funcionarioQuery) use ($empresaId) {
                            $funcionarioQuery->where('empresa_id', $empresaId);
                        });
                    }
                });

            $proventos = $this->hasColumn('apuracao_mensals', 'total_proventos') ? (float) (clone $query)->sum('total_proventos') : (float) (clone $query)->sum('valor_final');
            $descontos = $this->hasColumn('apuracao_mensals', 'total_descontos') ? (float) (clone $query)->sum('total_descontos') : 0.0;
            $liquido = $this->hasColumn('apuracao_mensals', 'liquido') ? (float) (clone $query)->sum('liquido') : (float) (clone $query)->sum('valor_final');

            if ($liquido <= 0 && $this->hasTable('rh_folha_fechamentos')) {
                $fechamento = \App\Models\RHFolhaFechamento::query()
                    ->when($empresaId > 0, fn (Builder $q) => $q->where('empresa_id', $empresaId))
                    ->where('mes', (int) $referencia->format('m'))
                    ->where('ano', $anoRef)
                    ->latest('id')
                    ->first();

                if ($fechamento) {
                    $proventos = (float) ($fechamento->eventos_total ?? $proventos);
                    $descontos = (float) ($fechamento->descontos_total ?? $descontos);
                    $liquido = (float) ($fechamento->liquido_total ?? $liquido);
                }
            }

            $serie->push([
                'label' => $referencia->translatedFormat('M/Y'),
                'competencia' => sprintf('%02d/%04d', (int) $referencia->format('m'), $anoRef),
                'proventos' => round($proventos, 2),
                'descontos' => round($descontos, 2),
                'liquido' => round($liquido, 2),
            ]);
        }

        return $serie;
    }

    private function montarSerieHeadcount(int $empresaId, int $mes, int $ano): Collection
    {
        $serie = collect();

        for ($i = 5; $i >= 0; $i--) {
            $referencia = Carbon::create($ano, $mes, 1)->subMonths($i);
            $inicio = $referencia->copy()->startOfMonth();
            $fim = $referencia->copy()->endOfMonth();

            $ativos = Funcionario::query()
                ->when($empresaId > 0 && $this->hasColumn('funcionarios', 'empresa_id'), fn (Builder $q) => $q->where('empresa_id', $empresaId))
                ->when($this->hasColumn('funcionarios', 'ativo'), fn (Builder $q) => $q->where(function (Builder $inner) {
                    $inner->whereNull('ativo')->orWhereIn('ativo', [1, '1', 'S', 's', 'SIM', 'sim', 'A', 'a']);
                }))
                ->when($this->hasColumn('funcionarios', 'created_at'), fn (Builder $q) => $q->whereDate('created_at', '<=', $fim->toDateString()))
                ->count();

            $admissoes = 0;
            if ($this->hasTable('funcionarios_ficha_admissao')) {
                $admissoes = \App\Models\FuncionarioFichaAdmissao::query()
                    ->when($empresaId > 0 && $this->hasColumn('funcionarios_ficha_admissao', 'empresa_id'), fn (Builder $q) => $q->where('empresa_id', $empresaId))
                    ->whereBetween('data_admissao', [$inicio->toDateString(), $fim->toDateString()])
                    ->count();
            }

            $desligamentos = 0;
            if ($this->hasTable('rh_desligamentos')) {
                $desligamentos = \App\Models\RHDesligamento::query()
                    ->when($empresaId > 0 && $this->hasColumn('rh_desligamentos', 'empresa_id'), fn (Builder $q) => $q->where('empresa_id', $empresaId))
                    ->whereBetween('data_desligamento', [$inicio->toDateString(), $fim->toDateString()])
                    ->count();
            }

            $serie->push([
                'label' => $referencia->translatedFormat('M/Y'),
                'ativos' => $ativos,
                'admissoes' => $admissoes,
                'desligamentos' => $desligamentos,
            ]);
        }

        return $serie;
    }

    private function montarAlertas(int $empresaId, Carbon $now): Collection
    {
        $alertas = collect();

        if ($this->hasTable('funcionarios_ficha_admissao')) {
            $fichasQuery = \App\Models\FuncionarioFichaAdmissao::query()
                ->with('funcionario:id,nome')
                ->select($this->availableColumns('funcionarios_ficha_admissao', ['id', 'empresa_id', 'funcionario_id', 'cnh_validade', 'data_exame_admissional']));

            if ($empresaId > 0 && $this->hasColumn('funcionarios_ficha_admissao', 'empresa_id')) {
                $fichasQuery->where('empresa_id', $empresaId);
            }

            $fichas = $fichasQuery
                ->where(function (Builder $query) use ($now) {
                    if ($this->hasColumn('funcionarios_ficha_admissao', 'cnh_validade')) {
                        $query->whereBetween('cnh_validade', [$now->copy()->subDays(30)->toDateString(), $now->copy()->addDays(30)->toDateString()]);
                    }

                    if ($this->hasColumn('funcionarios_ficha_admissao', 'data_exame_admissional')) {
                        $method = $this->hasColumn('funcionarios_ficha_admissao', 'cnh_validade') ? 'orWhereBetween' : 'whereBetween';
                        $query->{$method}('data_exame_admissional', [$now->copy()->subYear()->subDays(30)->toDateString(), $now->copy()->subYear()->addDays(30)->toDateString()]);
                    }
                })
                ->get();

            foreach ($fichas as $ficha) {
                if (!empty($ficha->cnh_validade)) {
                    $diasCnh = $now->diffInDays(Carbon::parse($ficha->cnh_validade), false);
                    if ($diasCnh <= 30) {
                        $alertas->push([
                            'tipo' => 'CNH',
                            'funcionario' => optional($ficha->funcionario)->nome ?? 'Funcionário',
                            'descricao' => 'CNH próxima do vencimento',
                            'dias' => $diasCnh,
                            'gravidade' => $diasCnh < 0 ? 'danger' : 'warning',
                        ]);
                    }
                }

                if (!empty($ficha->data_exame_admissional)) {
                    $diasAso = $now->diffInDays(Carbon::parse($ficha->data_exame_admissional)->addYear(), false);
                    if ($diasAso <= 30) {
                        $alertas->push([
                            'tipo' => 'ASO',
                            'funcionario' => optional($ficha->funcionario)->nome ?? 'Funcionário',
                            'descricao' => 'ASO próximo do vencimento',
                            'dias' => $diasAso,
                            'gravidade' => $diasAso < 0 ? 'danger' : 'primary',
                        ]);
                    }
                }
            }
        }

        if ($this->hasTable('rh_ferias')) {
            $feriasQuery = \App\Models\RHFerias::query()
                ->with('funcionario:id,nome')
                ->select($this->availableColumns('rh_ferias', ['id', 'empresa_id', 'funcionario_id', 'data_inicio', 'status']));

            if ($empresaId > 0 && $this->hasColumn('rh_ferias', 'empresa_id')) {
                $feriasQuery->where('empresa_id', $empresaId);
            }

            if ($this->hasColumn('rh_ferias', 'status')) {
                $feriasQuery->whereIn('status', ['programada', 'pendente']);
            }

            $ferias = $this->hasColumn('rh_ferias', 'data_inicio')
                ? $feriasQuery->whereBetween('data_inicio', [$now->copy()->subDays(30)->toDateString(), $now->copy()->addDays(30)->toDateString()])->get()
                : collect();

            foreach ($ferias as $item) {
                $diasFerias = $now->diffInDays(Carbon::parse($item->data_inicio), false);
                if ($diasFerias <= 30) {
                    $alertas->push([
                        'tipo' => 'Férias',
                        'funcionario' => optional($item->funcionario)->nome ?? 'Funcionário',
                        'descricao' => 'Férias próximas do início',
                        'dias' => $diasFerias,
                        'gravidade' => $diasFerias < 0 ? 'danger' : 'success',
                    ]);
                }
            }
        }

        return $alertas->sortBy('dias')->values()->take(8);
    }

    private function montarFluxoAnual(int $empresaId, int $ano): array
    {
        $meses = ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'];
        $admissoes = array_fill(0, 12, 0);
        $desligamentos = array_fill(0, 12, 0);

        if ($this->hasTable('funcionarios_ficha_admissao')) {
            $rows = \App\Models\FuncionarioFichaAdmissao::query()
                ->select(DB::raw('MONTH(data_admissao) as mes, COUNT(*) as total'))
                ->when($empresaId > 0 && $this->hasColumn('funcionarios_ficha_admissao', 'empresa_id'), fn (Builder $q) => $q->where('empresa_id', $empresaId))
                ->whereYear('data_admissao', $ano)
                ->groupBy(DB::raw('MONTH(data_admissao)'))
                ->get();

            foreach ($rows as $row) {
                $indice = max(0, ((int) $row->mes) - 1);
                $admissoes[$indice] = (int) $row->total;
            }
        }

        if ($this->hasTable('rh_desligamentos')) {
            $rows = \App\Models\RHDesligamento::query()
                ->select(DB::raw('MONTH(data_desligamento) as mes, COUNT(*) as total'))
                ->when($empresaId > 0 && $this->hasColumn('rh_desligamentos', 'empresa_id'), fn (Builder $q) => $q->where('empresa_id', $empresaId))
                ->whereYear('data_desligamento', $ano)
                ->groupBy(DB::raw('MONTH(data_desligamento)'))
                ->get();

            foreach ($rows as $row) {
                $indice = max(0, ((int) $row->mes) - 1);
                $desligamentos[$indice] = (int) $row->total;
            }
        }

        return [$meses, $admissoes, $desligamentos];
    }

    /** @return array<int,string> */
    private function availableColumns(string $table, array $columns): array
    {
        $available = array_values(array_filter($columns, fn (string $column) => $this->hasColumn($table, $column)));
        return $available !== [] ? $available : ['id'];
    }

    private function hasTable(string $table): bool
    {
        if (!array_key_exists($table, $this->tableExistsCache)) {
            $this->tableExistsCache[$table] = Schema::hasTable($table);
        }
        return $this->tableExistsCache[$table];
    }

    private function hasColumn(string $table, string $column): bool
    {
        $key = $table . '.' . $column;
        if (!array_key_exists($key, $this->columnExistsCache)) {
            $this->columnExistsCache[$key] = $this->hasTable($table) && Schema::hasColumn($table, $column);
        }
        return $this->columnExistsCache[$key];
    }

    private function mesNome(int $mes): string
    {
        return [1 => 'janeiro', 2 => 'fevereiro', 3 => 'março', 4 => 'abril', 5 => 'maio', 6 => 'junho', 7 => 'julho', 8 => 'agosto', 9 => 'setembro', 10 => 'outubro', 11 => 'novembro', 12 => 'dezembro'][$mes] ?? 'janeiro';
    }

    private function mesNumeroPorNome(string $mes): string
    {
        $map = [
            'janeiro' => '01',
            'fevereiro' => '02',
            'março' => '03',
            'marco' => '03',
            'abril' => '04',
            'maio' => '05',
            'junho' => '06',
            'julho' => '07',
            'agosto' => '08',
            'setembro' => '09',
            'outubro' => '10',
            'novembro' => '11',
            'dezembro' => '12',
        ];

        return $map[mb_strtolower(trim($mes))] ?? '01';
    }

    private function orderByMesCase(): string
    {
        return "CASE mes
            WHEN 'janeiro' THEN 1
            WHEN 'fevereiro' THEN 2
            WHEN 'março' THEN 3
            WHEN 'marco' THEN 3
            WHEN 'abril' THEN 4
            WHEN 'maio' THEN 5
            WHEN 'junho' THEN 6
            WHEN 'julho' THEN 7
            WHEN 'agosto' THEN 8
            WHEN 'setembro' THEN 9
            WHEN 'outubro' THEN 10
            WHEN 'novembro' THEN 11
            WHEN 'dezembro' THEN 12
            ELSE 0
        END DESC";
    }
}
