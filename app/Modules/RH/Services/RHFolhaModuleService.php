<?php

namespace App\Modules\RH\Services;

use App\Models\ApuracaoMensal;
use App\Models\Empresa;
use App\Models\RHFolhaFechamento;
use App\Modules\RH\Repositories\FuncionarioRepository;
use App\Services\RHFolhaCalculoService;
use App\Modules\RH\Support\ResolveEmpresaId;
use App\Support\RHCompetenciaHelper;
use Illuminate\Support\Facades\Schema;

class RHFolhaModuleService
{
    public function __construct(
        private RHFolhaCalculoService $calculoService,
        private FuncionarioRepository $funcionarios,
        private RHAnalyticsModuleService $analytics,
        private RHFinanceiroIntegrationService $financeiro,
    ) {
    }

    public function montarFolha(int $empresaId, int $mes, int $ano, ?string $nome = null): array
    {
        $empresaId = $empresaId > 0 ? $empresaId : ResolveEmpresaId::fromRequest();
        $paginacao = (int) env('PAGINACAO', 20);

        $funcionarios = $this->funcionarios->queryByEmpresa($empresaId)
            ->when(!empty($nome), function ($q) use ($nome) {
                return $q->where('nome', 'like', "%{$nome}%");
            })
            ->orderBy('nome')
            ->paginate($paginacao);

        $linhas = collect();

        foreach ($funcionarios as $item) {
            $valores = $this->calculoService->calcularFuncionario($item, $mes, $ano);
            $linhas->push([
                'funcionario' => $item,
                'salario_base' => $valores['salario_base'] ?? 0,
                'eventos' => $valores['eventos'] ?? 0,
                'descontos' => $valores['descontos'] ?? 0,
                'total_descontos' => $valores['total_descontos'] ?? ($valores['descontos'] ?? 0),
                'descontos_manuais' => $valores['descontos_manuais'] ?? 0,
                'descontos_legais' => $valores['descontos_legais'] ?? 0,
                'liquido' => $valores['liquido'] ?? 0,
                'valores' => $valores,
            ]);
        }

        [$fechamentos, $fechamentoAtual] = $this->buscarFechamentos($empresaId, $mes, $ano);
        $statusFolha = $fechamentoAtual && isset($fechamentoAtual->status)
            ? strtolower(trim((string) $fechamentoAtual->status))
            : '';
        $snapshot = $this->financeiro->competencia($empresaId, $mes, $ano);

        return [
            'funcionarios' => $funcionarios,
            'linhas' => $linhas,
            'mes' => $mes,
            'ano' => $ano,
            'nome' => $nome,
            'totalSalario' => $linhas->sum('salario_base'),
            'totalEventos' => $linhas->sum('eventos'),
            'totalDescontos' => $linhas->sum('descontos'),
            'totalLiquido' => $linhas->sum('liquido'),
            'totalDescontosManuais' => $linhas->sum('descontos_manuais'),
            'totalDescontosLegais' => $linhas->sum('descontos_legais'),
            'fechamentos' => $fechamentos,
            'fechamentoAtual' => $fechamentoAtual,
            'competenciaFechada' => $statusFolha === 'fechado',
            'snapshotFinanceiro' => $snapshot,
            'alertasFinanceiros' => $this->montarAlertasFinanceiros($snapshot),
        ];
    }

    public function montarRecibo(int $empresaId, int $funcionarioId, int $mes, int $ano): array
    {
        $empresaId = $empresaId > 0 ? $empresaId : ResolveEmpresaId::fromRequest();
        $mes = RHCompetenciaHelper::numero($mes);
        $ano = (int) $ano > 0 ? (int) $ano : (int) date('Y');

        $empresa = Empresa::find($empresaId);
        $funcionario = $this->funcionarios->findByEmpresaOrFail($empresaId, $funcionarioId);
        $mesNome = RHCompetenciaHelper::nome($mes);
        $mesPadded = RHCompetenciaHelper::padded($mes);
        $apuracao = ApuracaoMensal::query()
            ->where('funcionario_id', $funcionario->id)
            ->where('ano', $ano)
            ->where(function ($q) use ($mes, $mesNome, $mesPadded) {
                $q->where('mes', $mesNome)
                    ->orWhere('mes', (string) $mes)
                    ->orWhere('mes', $mesPadded)
                    ->orWhereRaw('LOWER(CAST(mes AS CHAR)) = ?', [mb_strtolower($mesNome)]);
            })
            ->when($empresaId > 0, fn ($q) => $q->where('empresa_id', $empresaId))
            ->orderByDesc('id')
            ->first();

        $valores = $this->resolverValoresRecibo($funcionario, $mes, $ano, $apuracao);

        return [
            'empresa' => $empresa,
            'funcionario' => $funcionario,
            'apuracao' => $apuracao,
            'mes' => $mes,
            'ano' => $ano,
            'salarioBase' => $valores['salario_base'] ?? 0,
            'eventos' => $valores['eventos'] ?? 0,
            'descontos' => $valores['descontos'] ?? 0,
            'total_descontos' => $valores['total_descontos'] ?? ($valores['descontos'] ?? 0),
            'proventos' => $valores['proventos'] ?? 0,
            'liquido' => $valores['liquido'] ?? 0,
            'valores' => $valores,
        ];
    }

    public function montarResumoFinanceiro(int $empresaId, int $mes, int $ano): array
    {
        $empresaId = $empresaId > 0 ? $empresaId : ResolveEmpresaId::fromRequest();
        return $this->financeiro->competencia($empresaId, $mes, $ano);
    }

    public function montarResumoDetalhado(int $empresaId, int $mes, int $ano): array
    {
        $empresaId = $empresaId > 0 ? $empresaId : ResolveEmpresaId::fromRequest();
        $funcionarios = $this->funcionarios->ativosByEmpresa($empresaId)
            ->orderBy('nome')
            ->get();

        $competencia = $this->calculoService::competencia($mes, $ano);

        $resumo = collect();
        $folhaTotal = 0.0;
        $totalSalarioBase = 0.0;
        $totalEventos = 0.0;
        $totalDescontos = 0.0;
        $totalLiquido = 0.0;
        $totalInss = 0.0;
        $totalIrrf = 0.0;

        foreach ($funcionarios as $funcionario) {
            $valores = $this->calculoService->calcularFuncionario($funcionario, $competencia['mes'], $competencia['ano']);
            $linha = [
                'funcionario' => $funcionario,
                'salario_base' => (float) ($valores['salario_base'] ?? 0),
                'eventos' => (float) ($valores['eventos'] ?? 0),
                'descontos' => (float) ($valores['descontos'] ?? 0),
                'liquido' => (float) ($valores['liquido'] ?? 0),
                'inss' => (float) ($valores['inss'] ?? 0),
                'irrf' => (float) ($valores['irrf'] ?? 0),
                'valores' => $valores,
            ];

            $resumo->push($linha);
            $folhaTotal += $linha['liquido'];
            $totalSalarioBase += $linha['salario_base'];
            $totalEventos += $linha['eventos'];
            $totalDescontos += $linha['descontos'];
            $totalLiquido += $linha['liquido'];
            $totalInss += $linha['inss'];
            $totalIrrf += $linha['irrf'];
        }

        $snapshot = $this->financeiro->competencia($empresaId, $mes, $ano);

        return [
            'mes' => $mes,
            'ano' => $ano,
            'resumo' => $resumo,
            'folhaTotal' => $folhaTotal,
            'totalSalarioBase' => $totalSalarioBase,
            'totalEventos' => $totalEventos,
            'totalDescontos' => $totalDescontos,
            'totalLiquido' => $totalLiquido,
            'totalInss' => $totalInss,
            'totalIrrf' => $totalIrrf,
            'contasReceber' => $snapshot['receitaPrevista'],
            'contasReceberLiquidas' => $snapshot['receitaRecebida'],
            'contasPagar' => $snapshot['despesaPrevista'],
            'contasPagas' => $snapshot['despesaPaga'],
            'pesoFolha' => $snapshot['pesoFolhaReceita'],
            'pesoFolhaCaixa' => $snapshot['pesoFolhaCaixa'],
            'resultadoAposFolha' => $snapshot['resultadoPrevisto'],
            'resultadoCaixa' => $snapshot['resultadoCaixa'],
            'capitalComprometido' => $snapshot['capitalComprometido'],
            'coberturaFolha' => $snapshot['coberturaFolha'],
            'custosRh' => $snapshot['custosRh'],
            'categoriasPagar' => $snapshot['categoriasPagar'],
            'categoriasReceber' => $snapshot['categoriasReceber'],
            'alertasFinanceiros' => $this->montarAlertasFinanceiros($snapshot),
            'serieFinanceira' => $this->financeiro->serieMensal($empresaId, $mes, $ano, 6),
        ];
    }


    private function resolverValoresRecibo($funcionario, int $mes, int $ano, ?ApuracaoMensal $apuracao = null): array
    {
        $calculado = $this->calculoService->calcularFuncionario($funcionario, $mes, $ano);

        if (!$apuracao) {
            return $calculado;
        }

        $json = $apuracao->json_calculo;
        $jsonCalculo = is_string($json) ? json_decode($json, true) : (is_array($json) ? $json : null);
        if (is_array($jsonCalculo)) {
            return $this->normalizarResultadoRecibo(array_replace($calculado, $jsonCalculo), $calculado, $apuracao);
        }

        $resultado = $calculado;
        $resultado['total_proventos'] = (float) ($apuracao->total_proventos ?? $resultado['total_proventos'] ?? 0);
        $resultado['proventos'] = (float) ($apuracao->total_proventos ?? $resultado['proventos'] ?? 0);
        $resultado['total_descontos'] = (float) ($apuracao->total_descontos ?? $resultado['total_descontos'] ?? 0);
        $resultado['descontos'] = (float) ($apuracao->total_descontos ?? $resultado['descontos'] ?? 0);
        $resultado['liquido'] = (float) ($apuracao->liquido ?? $apuracao->valor_final ?? $resultado['liquido'] ?? 0);
        $resultado['base_inss'] = (float) ($apuracao->base_inss ?? $resultado['base_inss'] ?? 0);
        $resultado['base_fgts'] = (float) ($apuracao->base_fgts ?? $resultado['base_fgts'] ?? 0);
        $resultado['base_irrf'] = (float) ($apuracao->base_irrf ?? $resultado['base_irrf'] ?? 0);

        return $this->normalizarResultadoRecibo($resultado, $calculado, $apuracao);
    }


    private function normalizarResultadoRecibo(array $resultado, array $calculado, ?ApuracaoMensal $apuracao = null): array
    {
        $resultado['itens_proventos'] = $this->normalizarItensRecibo(
            $resultado['itens_proventos'] ?? [],
            $calculado['itens_proventos'] ?? [],
            'provento'
        );
        $resultado['itens_descontos'] = $this->normalizarItensRecibo(
            $resultado['itens_descontos'] ?? [],
            $calculado['itens_descontos'] ?? [],
            'desconto'
        );

        $irrfAtivo = $this->irrfAtivoNoResultado($resultado, $calculado);
        $resultado['faixa_irrf'] = $irrfAtivo
            ? $this->normalizarFaixaIrrf($resultado['faixa_irrf'] ?? $calculado['faixa_irrf'] ?? 0)
            : 0.0;

        $resultado['base_irrf'] = (float) ($apuracao?->base_irrf ?? $resultado['base_irrf'] ?? $calculado['base_irrf'] ?? 0);

        return $resultado;
    }

    private function normalizarItensRecibo(array $itensOriginais, array $itensCalculados, string $tipo): array
    {
        $mapaCalculado = [];
        foreach ($itensCalculados as $itemCalculado) {
            $chave = $this->chaveItemRecibo($itemCalculado);
            if ($chave !== '') {
                $mapaCalculado[$chave] = $itemCalculado;
            }
        }

        $normalizados = [];
        foreach ($itensOriginais as $itemOriginal) {
            if (!is_array($itemOriginal)) {
                continue;
            }

            $calculado = $mapaCalculado[$this->chaveItemRecibo($itemOriginal)] ?? null;
            $codigo = $this->normalizarCodigoEventoRecibo($itemOriginal, $calculado);
            $descricao = trim((string) ($itemOriginal['descricao'] ?? $calculado['descricao'] ?? ''));
            $referencia = $itemOriginal['referencia'] ?? $calculado['referencia'] ?? '';
            $valor = (float) ($itemOriginal['valor'] ?? $calculado['valor'] ?? 0);

            $normalizados[] = [
                'codigo' => $codigo,
                'descricao' => $descricao,
                'referencia' => $referencia,
                'valor' => $valor,
                'tipo' => $tipo,
            ];
        }

        foreach ($itensCalculados as $itemCalculado) {
            $chave = $this->chaveItemRecibo($itemCalculado);
            if ($chave === '') {
                continue;
            }
            $jaExiste = false;
            foreach ($normalizados as $normalizado) {
                if ($this->chaveItemRecibo($normalizado) === $chave) {
                    $jaExiste = true;
                    break;
                }
            }
            if (!$jaExiste) {
                $normalizados[] = [
                    'codigo' => $this->normalizarCodigoEventoRecibo($itemCalculado, $itemCalculado),
                    'descricao' => trim((string) ($itemCalculado['descricao'] ?? '')),
                    'referencia' => $itemCalculado['referencia'] ?? '',
                    'valor' => (float) ($itemCalculado['valor'] ?? 0),
                    'tipo' => $tipo,
                ];
            }
        }

        return $normalizados;
    }

    private function normalizarCodigoEventoRecibo(array $item, ?array $fallback = null): string
    {
        foreach (['evento_codigo', 'codigo'] as $campo) {
            $valor = $item[$campo] ?? null;
            if ($this->codigoEventoValido($valor, $item['descricao'] ?? null)) {
                return trim((string) $valor);
            }
        }

        foreach (['evento_codigo', 'codigo'] as $campo) {
            $valor = $fallback[$campo] ?? null;
            if ($this->codigoEventoValido($valor, $fallback['descricao'] ?? null)) {
                return trim((string) $valor);
            }
        }

        return '000';
    }

    private function codigoEventoValido(mixed $codigo, mixed $descricao = null): bool
    {
        if ($codigo === null) {
            return false;
        }

        $codigo = trim((string) $codigo);
        if ($codigo === '') {
            return false;
        }

        if ($descricao !== null && mb_strtoupper($codigo) === mb_strtoupper(trim((string) $descricao))) {
            return false;
        }

        return preg_match('/^[0-9A-Z._-]+$/u', $codigo) === 1;
    }

    private function chaveItemRecibo(array $item): string
    {
        $descricao = mb_strtoupper(trim((string) ($item['descricao'] ?? '')));
        $valor = number_format((float) ($item['valor'] ?? 0), 2, '.', '');

        return $descricao !== '' ? $descricao.'|'.$valor : '';
    }

    private function irrfAtivoNoResultado(array $resultado, array $calculado): bool
    {
        if ((float) ($resultado['irrf'] ?? $calculado['irrf'] ?? 0) > 0) {
            return true;
        }

        return (float) ($calculado['faixa_irrf'] ?? 0) > 0;
    }

    private function normalizarFaixaIrrf(mixed $faixa): float
    {
        if (is_numeric($faixa)) {
            return (float) $faixa;
        }

        $faixa = trim((string) $faixa);
        if ($faixa === '') {
            return 0.0;
        }

        $faixa = str_replace(['.', ','], ['', '.'], $faixa);

        return is_numeric($faixa) ? (float) $faixa : 0.0;
    }

    private function montarAlertasFinanceiros(array $snapshot): array
    {
        $alertas = [];

        if (($snapshot['pesoFolhaReceita'] ?? 0) > 40) {
            $alertas[] = 'Folha total acima de 40% da receita da competência.';
        }
        if (($snapshot['coberturaFolha'] ?? 0) < 1) {
            $alertas[] = 'A receita recebida ainda não cobre a folha líquida do mês.';
        }
        if (($snapshot['capitalComprometido'] ?? 0) > 100) {
            $alertas[] = 'Despesas e RH já comprometem mais de 100% da receita prevista.';
        }
        if (($snapshot['resultadoPrevisto'] ?? 0) < 0) {
            $alertas[] = 'Resultado previsto negativo após integrar Financeiro e RH.';
        }

        return $alertas;
    }

    private function buscarFechamentos(int $empresaId, int $mes, int $ano): array
    {
        if (!Schema::hasTable('rh_folha_fechamentos')) {
            return [collect(), null];
        }

        $query = RHFolhaFechamento::where('empresa_id', $empresaId)
            ->orderBy('ano', 'desc')
            ->orderBy('mes', 'desc')
            ->orderBy('id', 'desc');

        return [
            (clone $query)->limit(12)->get(),
            (clone $query)->where('mes', $mes)->where('ano', $ano)->first(),
        ];
    }
}
