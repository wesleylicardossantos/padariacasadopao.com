<?php

namespace App\Modules\RH\Services;

use App\Models\ContaPagar;
use App\Models\ContaReceber;
use App\Models\Funcionario;
use App\Services\RHFolhaCalculoService;
use App\Services\RH\RHDossieAutomationService;
use App\Modules\RH\Support\ResolveEmpresaId;
use Illuminate\Support\Facades\Schema;

class RHAnalyticsModuleService
{
    public function __construct(private RHFolhaCalculoService $folhaCalculo, private RHDossieAutomationService $dossieAutomation)
    {
    }

    public function montarDreFolha(int $empresaId, int $mes, int $ano): array
    {
        [$empresaId, $mes, $ano] = $this->sanitizeCompetencia($empresaId, $mes, $ano);

        $inicio = sprintf('%04d-%02d-01', $ano, $mes);
        $fim = date('Y-m-t', strtotime($inicio));
        $custos = $this->calcularCustosCompetencia($empresaId, $mes, $ano);
        $receitaBruta = $this->sumReceita($empresaId, $inicio, $fim);
        $despesasOperacionais = $this->sumDespesasOperacionais($empresaId, $inicio, $fim);
        $folha = (float) ($custos['total_rh'] ?? 0);
        $resultadoOperacional = $receitaBruta - $despesasOperacionais - $folha;
        $margemOperacional = $receitaBruta > 0 ? ($resultadoOperacional / $receitaBruta) * 100 : 0;

        $linhas = [
            ['grupo' => 'Receita Bruta', 'valor' => $receitaBruta],
            ['grupo' => '(-) Despesas Operacionais', 'valor' => $despesasOperacionais],
            ['grupo' => '(-) Salários + Eventos', 'valor' => ($custos['salarios'] ?? 0) + ($custos['eventos'] ?? 0)],
            ['grupo' => '(-) Encargos + Benefícios + Provisões', 'valor' => ($custos['encargos'] ?? 0) + ($custos['beneficios'] ?? 0) + ($custos['provisoes'] ?? 0)],
            ['grupo' => '(=) Resultado Operacional', 'valor' => $resultadoOperacional],
        ];

        return compact('mes', 'ano', 'inicio', 'fim', 'receitaBruta', 'despesasOperacionais', 'custos', 'folha', 'resultadoOperacional', 'margemOperacional', 'linhas');
    }

    public function montarDreInteligente(int $empresaId, int $mes, int $ano): array
    {
        [$empresaId, $mes, $ano] = $this->sanitizeCompetencia($empresaId, $mes, $ano);

        $atual = $this->resumoCompetencia($empresaId, $mes, $ano);
        $refAnterior = strtotime(sprintf('%04d-%02d-01', $ano, $mes) . ' -1 month');
        $anterior = $this->resumoCompetencia($empresaId, (int) date('m', $refAnterior), (int) date('Y', $refAnterior));

        $custos = $atual['custos'];
        $totalRh = $atual['total_rh'];
        $pesoFolha = $atual['receita'] > 0 ? ($totalRh / $atual['receita']) * 100 : 0;
        $pesoFerias = $totalRh > 0 ? ((($custos['ferias'] ?? 0) + ($custos['um_terco_ferias'] ?? 0)) / $totalRh) * 100 : 0;
        $custoMedioFuncionario = ($custos['colaboradores'] ?? 0) > 0 ? ($totalRh / $custos['colaboradores']) : 0;
        $comparativo = [
            'receita' => $this->variacao($anterior['receita'], $atual['receita']),
            'rh' => $this->variacao($anterior['total_rh'], $atual['total_rh']),
            'resultado' => $this->variacao($anterior['resultado'], $atual['resultado']),
        ];

        $alertas = [];
        if ($pesoFolha > 40) $alertas[] = 'Folha total acima de 40% da receita bruta.';
        if ($comparativo['rh'] > 15) $alertas[] = 'Custo de RH cresceu acima de 15% contra o mês anterior.';
        if ($atual['resultado'] < 0) $alertas[] = 'Resultado operacional negativo após RH.';
        if ($pesoFerias > 15) $alertas[] = 'Férias e 1/3 de férias ganharam peso relevante no período.';

        return [
            'mes' => $mes,
            'ano' => $ano,
            'receitaBruta' => $atual['receita'],
            'despesasOperacionais' => $atual['despesas_operacionais'],
            'custos' => $custos,
            'totalRh' => $totalRh,
            'resultadoOperacional' => $atual['resultado'],
            'margemOperacional' => $atual['receita'] > 0 ? ($atual['resultado'] / $atual['receita']) * 100 : 0,
            'pesoFolha' => $pesoFolha,
            'pesoFerias' => $pesoFerias,
            'custoMedioFuncionario' => $custoMedioFuncionario,
            'comparativo' => $comparativo,
            'alertas' => $alertas,
            'ranking' => $this->rankingFuncionarios($empresaId, $mes, $ano),
        ];
    }

    public function montarDrePreditivo(int $empresaId, int $mes, int $ano, int $simContratacoes = 0, float $simSalario = 0): array
    {
        [$empresaId, $mes, $ano] = $this->sanitizeCompetencia($empresaId, $mes, $ano);

        $historico = [];
        for ($i = 2; $i >= 0; $i--) {
            $ref = strtotime(sprintf('%04d-%02d-01', $ano, $mes) . " -{$i} month");
            $historico[] = $this->resumoCompetencia($empresaId, (int) date('m', $ref), (int) date('Y', $ref));
        }

        $mediaReceita = $this->media(array_column($historico, 'receita'));
        $mediaRh = $this->media(array_column($historico, 'total_rh'));
        $mediaDespesa = $this->media(array_column($historico, 'despesas_operacionais'));
        $receitaProjetada = $mediaReceita * (1 + ($this->tendenciaPercentual(array_column($historico, 'receita')) / 100));
        $rhProjetado = $mediaRh * (1 + ($this->tendenciaPercentual(array_column($historico, 'total_rh')) / 100));
        $despesaProjetada = $mediaDespesa * (1 + ($this->tendenciaPercentual(array_column($historico, 'despesas_operacionais')) / 100));
        $resultadoProjetado = $receitaProjetada - $rhProjetado - $despesaProjetada;
        $margemProjetada = $receitaProjetada > 0 ? ($resultadoProjetado / $receitaProjetada) * 100 : 0;

        $simSalario = $simSalario > 0 ? $simSalario : (($this->queryFuncionariosAtivos($empresaId)->avg('salario')) ?: 0);
        $simImpactoTotal = $simContratacoes > 0 ? $simContratacoes * ($simSalario * 1.44) : 0;
        $resultadoSimulado = $resultadoProjetado - $simImpactoTotal;
        $margemSimulada = $receitaProjetada > 0 ? ($resultadoSimulado / $receitaProjetada) * 100 : 0;

        $alertas = [];
        if ($resultadoProjetado < 0) $alertas[] = 'Projeção do próximo mês aponta resultado operacional negativo.';
        if ($margemProjetada < 5) $alertas[] = 'Margem projetada abaixo de 5%.';
        if ($receitaProjetada > 0 && (($rhProjetado / $receitaProjetada) * 100) > 40) $alertas[] = 'RH projetado acima de 40% da receita.';
        if ($simContratacoes > 0 && $resultadoSimulado < 0) $alertas[] = 'A simulação de contratação derruba o resultado para negativo.';

        $funcionariosAtivos = (int) $this->queryFuncionariosAtivos($empresaId)->count();

        return compact('mes', 'ano', 'historico', 'receitaProjetada', 'rhProjetado', 'despesaProjetada', 'resultadoProjetado', 'margemProjetada', 'simContratacoes', 'simSalario', 'simImpactoTotal', 'resultadoSimulado', 'margemSimulada', 'alertas', 'funcionariosAtivos');
    }

    public function montarIADecisao(int $empresaId, int $mes, int $ano): array
    {
        [$empresaId, $mes, $ano] = $this->sanitizeCompetencia($empresaId, $mes, $ano);

        return app(RHDecisionEngineService::class)->analisar($empresaId, $mes, $ano);
    }

    public function montarDashboardExecutivo(int $empresaId, int $mes, int $ano): array
    {
        [$empresaId, $mes, $ano] = $this->sanitizeCompetencia($empresaId, $mes, $ano);

        $serie = [];
        for ($i = 5; $i >= 0; $i--) {
            $ref = strtotime(sprintf('%04d-%02d-01', $ano, $mes) . " -{$i} month");
            $resumo = $this->resumoCompetencia($empresaId, (int) date('m', $ref), (int) date('Y', $ref));
            $serie[] = [
                'competencia' => sprintf('%02d/%04d', $resumo['mes'] ?? (int) date('m', $ref), $resumo['ano'] ?? (int) date('Y', $ref)),
                'receita' => (float) ($resumo['receita'] ?? 0),
                'rh' => (float) ($resumo['total_rh'] ?? 0),
                'lucro' => (float) ($resumo['resultado'] ?? 0),
                'peso_folha' => ((float) ($resumo['receita'] ?? 0)) > 0 ? (((float) ($resumo['total_rh'] ?? 0) / (float) ($resumo['receita'] ?? 0)) * 100) : 0,
            ];
        }

        $atual = $this->resumoCompetencia($empresaId, $mes, $ano);
        $receitaAtual = (float) ($atual['receita'] ?? 0);
        $rhAtual = (float) ($atual['total_rh'] ?? 0);
        $lucroAtual = (float) ($atual['resultado'] ?? 0);
        $pesoFolhaAtual = $receitaAtual > 0 ? ($rhAtual / $receitaAtual) * 100 : 0;
        $margemAtual = $receitaAtual > 0 ? ($lucroAtual / $receitaAtual) * 100 : 0;
        $funcionariosAtivos = (int) ($atual['custos']['colaboradores'] ?? $this->queryFuncionariosAtivos($empresaId)->count());
        $dossie = $this->dossieAutomation->dashboardMetrics($empresaId, $mes, $ano);
        $alertas = [];
        if ($lucroAtual < 0) $alertas[] = 'Resultado operacional negativo no período.';
        if ($pesoFolhaAtual > 40) $alertas[] = 'Folha pressionando mais de 40% da receita.';
        if ($margemAtual < 5) $alertas[] = 'Margem operacional abaixo de 5%.';
        $alertas = array_values(array_unique(array_merge($alertas, $dossie['alertas'] ?? [])));

        return [
            'mes' => $mes,
            'ano' => $ano,
            'receitaAtual' => $receitaAtual,
            'rhAtual' => $rhAtual,
            'lucroAtual' => $lucroAtual,
            'pesoFolhaAtual' => $pesoFolhaAtual,
            'margemAtual' => $margemAtual,
            'funcionariosAtivos' => $funcionariosAtivos,
            'alertas' => $alertas,
            'timeline' => $serie,
            'ranking' => $this->rankingFuncionarios($empresaId, $mes, $ano),
            'setores' => $this->custosPorSetor($empresaId, $mes, $ano),
            'dossieStats' => $dossie,
        ];
    }

    private function custosPorSetor(int $empresaId, int $mes, int $ano): array
    {
        $competencia = RHFolhaCalculoService::competencia($mes, $ano);

        return $this->queryFuncionariosAtivos($empresaId)->get()->map(function ($funcionario) use ($competencia) {
            $valores = $this->safeCalcularFuncionario($funcionario, $competencia['mes'], $competencia['ano']);
            $setor = trim((string) ($funcionario->funcao ?? $funcionario->cargo ?? $funcionario->setor ?? 'Sem função'));
            $custo = (float) ($valores['proventos'] ?? 0) + ((float) ($funcionario->salario ?? 0) * 0.36);

            return [
                'setor' => $setor !== '' ? $setor : 'Sem função',
                'custo' => $custo,
            ];
        })->groupBy('setor')->map(function ($items, $setor) {
            return [
                'setor' => $setor,
                'custo' => (float) collect($items)->sum('custo'),
            ];
        })->sortByDesc('custo')->take(10)->values()->all();
    }

    public function calcularCustosCompetencia(int $empresaId, int $mes, int $ano): array
    {
        [$empresaId, $mes, $ano] = $this->sanitizeCompetencia($empresaId, $mes, $ano);

        $competencia = RHFolhaCalculoService::competencia($mes, $ano);
        $funcionarios = $this->queryFuncionariosAtivos($empresaId)->get();

        $salarios = 0.0;
        $eventos = 0.0;
        $descontos = 0.0;
        $folhaLiquida = 0.0;
        $ferias = 0.0;
        $umTercoFerias = 0.0;
        $encargos = 0.0;
        $beneficios = 0.0;
        $provisoes = 0.0;
        $fgts = 0.0;

        foreach ($funcionarios as $funcionario) {
            $valores = $this->safeCalcularFuncionario($funcionario, $competencia['mes'], $competencia['ano']);
            $salario = (float) ($valores['salario_base'] ?? $funcionario->salario ?? 0);
            $salarios += $salario;
            $eventos += (float) ($valores['eventos'] ?? 0);
            $descontos += (float) ($valores['descontos'] ?? 0);
            $folhaLiquida += (float) ($valores['liquido'] ?? 0);
            $fgts += (float) ($valores['fgts'] ?? 0);
            $encargos += $salario * 0.28;
            $beneficios += $salario * 0.08;
            $provisoes += ($salario / 12) + ($salario / 12);

            foreach (($valores['itens_proventos'] ?? []) as $item) {
                $descricao = strtoupper(trim((string) ($item['descricao'] ?? '')));
                $valor = (float) ($item['valor'] ?? 0);
                if (str_contains($descricao, 'FERIAS') && str_contains($descricao, '1/3')) {
                    $umTercoFerias += $valor;
                } elseif (str_contains($descricao, 'FERIAS')) {
                    $ferias += $valor;
                }
            }
        }

        $totalRh = $salarios + $eventos + $encargos + $beneficios + $provisoes;

        return [
            'salarios' => round($salarios, 2),
            'eventos' => round($eventos, 2),
            'descontos' => round($descontos, 2),
            'folha_liquida' => round($folhaLiquida, 2),
            'ferias' => round($ferias, 2),
            'um_terco_ferias' => round($umTercoFerias, 2),
            'encargos' => round($encargos, 2),
            'beneficios' => round($beneficios, 2),
            'provisoes' => round($provisoes, 2),
            'fgts' => round($fgts, 2),
            'colaboradores' => $funcionarios->count(),
            'total_rh' => round($totalRh, 2),
        ];
    }

    public function sumFolhaLiquida(int $empresaId, int $mes, int $ano): float
    {
        return (float) ($this->calcularCustosCompetencia($empresaId, $mes, $ano)['folha_liquida'] ?? 0);
    }

    private function resumoCompetencia(int $empresaId, int $mes, int $ano): array
    {
        $inicio = sprintf('%04d-%02d-01', $ano, $mes);
        $fim = date('Y-m-t', strtotime($inicio));
        $custos = $this->calcularCustosCompetencia($empresaId, $mes, $ano);
        $receita = $this->sumReceita($empresaId, $inicio, $fim);
        $despesasOperacionais = $this->sumDespesasOperacionais($empresaId, $inicio, $fim);
        $resultado = $receita - $despesasOperacionais - ($custos['total_rh'] ?? 0);

        return [
            'mes' => $mes,
            'ano' => $ano,
            'receita' => $receita,
            'despesas_operacionais' => $despesasOperacionais,
            'total_rh' => $custos['total_rh'] ?? 0,
            'resultado' => $resultado,
            'custos' => $custos,
        ];
    }

    private function rankingFuncionarios(int $empresaId, int $mes, int $ano): array
    {
        $competencia = RHFolhaCalculoService::competencia($mes, $ano);

        return $this->queryFuncionariosAtivos($empresaId)->get()->map(function ($funcionario) use ($competencia) {
            $valores = $this->safeCalcularFuncionario($funcionario, $competencia['mes'], $competencia['ano']);
            $nome = trim((string) ($funcionario->nome ?? $funcionario->razao_social ?? $funcionario->xNome ?? ''));
            $funcao = trim((string) ($funcionario->funcao ?? $funcionario->cargo ?? $funcionario->setor ?? 'Sem função'));
            $custoTotal = (float) ($valores['proventos'] ?? 0) + ((float) ($funcionario->salario ?? 0) * 0.36);

            return [
                'funcionario' => $funcionario,
                'nome' => $nome !== '' ? $nome : '—',
                'funcao' => $funcao !== '' ? $funcao : 'Sem função',
                'salario' => (float) ($funcionario->salario ?? 0),
                'eventos' => (float) ($valores['eventos'] ?? 0),
                'descontos' => (float) ($valores['descontos'] ?? 0),
                'liquido' => (float) ($valores['liquido'] ?? 0),
                'custo' => $custoTotal,
                'custo_total' => $custoTotal,
            ];
        })->sortByDesc('custo_total')->take(10)->values()->all();
    }

    private function queryFuncionariosAtivos(int $empresaId)
    {
        if ($empresaId <= 0 || !Schema::hasTable('funcionarios')) {
            return Funcionario::query()->whereRaw('1 = 0');
        }

        return Funcionario::where('empresa_id', $empresaId)
            ->where(function ($q) {
                $q->whereNull('ativo')->orWhere('ativo', 1);
            });
    }

    private function sumReceita(int $empresaId, string $inicio, string $fim): float
    {
        if (!Schema::hasTable('conta_recebers')) {
            return 0.0;
        }

        if ($empresaId <= 0 || !Schema::hasColumn('conta_recebers', 'data_vencimento') || !Schema::hasColumn('conta_recebers', 'valor_integral')) {
            return 0.0;
        }

        return (float) ContaReceber::where('empresa_id', $empresaId)
            ->whereBetween('data_vencimento', [$inicio, $fim])
            ->sum('valor_integral');
    }

    private function sumDespesasOperacionais(int $empresaId, string $inicio, string $fim): float
    {
        if (!Schema::hasTable('conta_pagars')) {
            return 0.0;
        }

        if ($empresaId <= 0 || !Schema::hasColumn('conta_pagars', 'data_vencimento') || !Schema::hasColumn('conta_pagars', 'valor_integral')) {
            return 0.0;
        }

        return (float) ContaPagar::where('empresa_id', $empresaId)
            ->whereBetween('data_vencimento', [$inicio, $fim])
            ->sum('valor_integral');
    }

    private function variacao(float $anterior, float $atual): float
    {
        if ($anterior <= 0) {
            return $atual > 0 ? 100.0 : 0.0;
        }

        return (($atual - $anterior) / $anterior) * 100;
    }

    private function media(array $valores): float
    {
        $valores = array_filter($valores, fn ($v) => $v !== null);
        if (count($valores) === 0) {
            return 0.0;
        }

        return array_sum($valores) / count($valores);
    }

    private function tendenciaPercentual(array $valores): float
    {
        if (count($valores) < 2) {
            return 0.0;
        }

        $primeiro = (float) $valores[0];
        $ultimo = (float) $valores[count($valores) - 1];
        if ($primeiro <= 0) {
            return 0.0;
        }

        return (($ultimo - $primeiro) / $primeiro) * 100;
    }

    private function sanitizeCompetencia(int $empresaId, int $mes, int $ano): array
    {
        $mes = $mes >= 1 && $mes <= 12 ? $mes : (int) date('m');
        $ano = $ano >= 2000 && $ano <= 2100 ? $ano : (int) date('Y');
        $empresaId = $empresaId > 0 ? $empresaId : ResolveEmpresaId::fromRequest();

        return [max(0, $empresaId), $mes, $ano];
    }

    private function safeCalcularFuncionario($funcionario, int $mes, int $ano): array
    {
        try {
            $valores = $this->folhaCalculo->calcularFuncionario($funcionario, $mes, $ano);

            return is_array($valores) ? $valores : [];
        } catch (\Throwable $e) {
            return [];
        }
    }
}
