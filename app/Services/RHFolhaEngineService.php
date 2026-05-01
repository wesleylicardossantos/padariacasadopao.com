<?php

namespace App\Services;

use App\Models\ApuracaoMensal;
use App\Models\EventoSalario;
use App\Models\Funcionario;
use App\Models\FuncionarioEvento;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class RHFolhaEngineService
{
    public function calcularFuncionario(Funcionario $funcionario, int $mes, int $ano, ?int $empresaId = null): array
    {
        $competencia = RHFolhaCalculoService::competencia($mes, $ano);
        $empresaId = $empresaId ?: (int) ($funcionario->empresa_id ?? 0);
        $salarioBase = round((float) ($funcionario->salario ?? 0), 2);

        $eventosEmpresa = $this->buscarEventosEmpresa($empresaId);
        $eventosFuncionario = $this->buscarEventosFuncionario($funcionario, $empresaId);
        $apuracaoAtual = $this->buscarApuracao($funcionario->id, $competencia, $empresaId);

        $eventosApuracao = [];
        $proventosExtras = 0.0;
        $descontosManuais = 0.0;
        $baseInss = 0.0;
        $baseFgts = 0.0;
        $baseIrrf = 0.0;

        $salarioEvento = $eventosEmpresa->get('SALARIO');
        if ($salarioEvento) {
            $eventosApuracao[] = $this->linhaEvento($salarioEvento, $salarioBase, 'provento_base', 30.0, 'motor');
            if ($this->eventoIncide($salarioEvento, 'inss')) {
                $baseInss += $salarioBase;
            }
            if ($this->eventoIncide($salarioEvento, 'fgts')) {
                $baseFgts += $salarioBase;
            }
            if ($this->eventoIncide($salarioEvento, 'irrf')) {
                $baseIrrf += $salarioBase;
            }
        }

        foreach ($eventosFuncionario as $vinculo) {
            $evento = $vinculo->evento;
            if (!$evento) {
                continue;
            }

            if (!$this->registroAtivo($evento?->ativo ?? 1)) {
                continue;
            }

            if (!$this->registroAtivo($vinculo->ativo ?? 1)) {
                continue;
            }

            $codigo = $this->eventoCodigo($evento);
            if (in_array($codigo, ['SALARIO', 'INSS', 'IRRF', 'FGTS'], true)) {
                continue;
            }

            $valor = $this->resolverValorVinculo($vinculo, $evento, $salarioBase);
            if ($valor <= 0) {
                continue;
            }

            $condicao = (string) ($vinculo->condicao ?: $evento->condicao ?: 'soma');
            $origem = $this->resolverOrigemVinculo($vinculo, $evento);
            $referencia = $vinculo->referencia ?? null;
            $categoria = $condicao === 'diminui' ? 'desconto_manual' : 'provento_manual';

            $eventosApuracao[] = $this->linhaEvento($evento, $valor, $categoria, $referencia, $origem, $condicao);

            if ($condicao === 'diminui') {
                $descontosManuais += $valor;
                continue;
            }

            $proventosExtras += $valor;
            if ($this->eventoIncide($evento, 'inss')) {
                $baseInss += $valor;
            }
            if ($this->eventoIncide($evento, 'fgts')) {
                $baseFgts += $valor;
            }
            if ($this->eventoIncide($evento, 'irrf')) {
                $baseIrrf += $valor;
            }
        }

        $configInss = Config::get('rh_payroll.inss', []);
        $configIrrf = Config::get('rh_payroll.irrf', []);
        $dependentes = $this->resolverQuantidadeDependentes($funcionario);
        $deducaoDependentes = round($dependentes * (float) ($configIrrf['deducao_dependente'] ?? 0), 2);

        $eventoInss = $eventosEmpresa->get('INSS');
        $eventoIrrf = $eventosEmpresa->get('IRRF');
        $eventoFgts = $eventosEmpresa->get('FGTS');

        $baseInss = round(min($baseInss, (float) ($configInss['teto'] ?? $baseInss)), 2);
        $inss = $eventoInss ? $this->calcularInss($baseInss) : 0.0;

        $baseFgts = round(max($baseFgts, 0), 2);
        $fgts = $eventoFgts ? round($baseFgts * 0.08, 2) : 0.0;

        $baseIrrf = round(max($baseIrrf - ($eventoInss ? $inss : 0.0) - $deducaoDependentes, 0), 2);
        $descontoSimplificado = (float) ($configIrrf['desconto_simplificado_mensal'] ?? 0);
        $irrfSemSimplificado = $eventoIrrf ? $this->calcularIrrf($baseIrrf, 0.0) : 0.0;
        $irrfComSimplificado = $eventoIrrf ? $this->calcularIrrf($baseIrrf, $descontoSimplificado) : 0.0;
        $usarSimplificado = $eventoIrrf ? ($irrfComSimplificado < $irrfSemSimplificado) : false;
        $baseIrrfEfetiva = $usarSimplificado ? round(max($baseIrrf - $descontoSimplificado, 0), 2) : $baseIrrf;
        $irrf = $usarSimplificado ? $irrfComSimplificado : $irrfSemSimplificado;
        $faixaIrrf = $eventoIrrf ? $this->descobrirFaixaIrrf($baseIrrfEfetiva) : 0.0;

        foreach ([
            ['INSS', $inss, 'desconto_legal', null],
            ['IRRF', $irrf, 'desconto_legal', $usarSimplificado ? $descontoSimplificado : null],
            ['FGTS', $fgts, 'encargo', 8.0],
        ] as [$codigo, $valor, $tipo, $referencia]) {
            if ($valor <= 0) {
                continue;
            }
            $evento = $eventosEmpresa->get($codigo);
            if (!$evento) {
                continue;
            }
            $eventosApuracao[] = $this->linhaEvento($evento, $valor, $tipo, $referencia, 'motor', $evento->condicao ?: 'diminui');
        }

        usort($eventosApuracao, function (array $a, array $b) {
            return [
                $this->normalizarInteiro($a['ordem_calculo'] ?? 0),
                $this->normalizarInteiro($a['evento_id'] ?? 0),
            ] <=> [
                $this->normalizarInteiro($b['ordem_calculo'] ?? 0),
                $this->normalizarInteiro($b['evento_id'] ?? 0),
            ];
        });

        $totalProventos = round($salarioBase + $proventosExtras, 2);
        $totalDescontos = round($descontosManuais + $inss + $irrf, 2);
        $liquido = round($totalProventos - $totalDescontos, 2);

        $itensProventos = [];
        $itensDescontos = [];
        foreach ($eventosApuracao as $linha) {
            $item = [
                'codigo' => $linha['codigo'],
                'descricao' => $linha['nome'],
                'referencia' => $linha['referencia'] !== null ? number_format((float) $linha['referencia'], 2, ',', '.') : '',
                'valor' => $linha['valor'],
                'tipo' => $linha['condicao'] === 'diminui' ? 'desconto' : 'provento',
            ];
            if (($linha['tipo'] ?? '') === 'encargo') {
                continue;
            }
            if ($linha['condicao'] === 'diminui') {
                $itensDescontos[] = $item;
            } else {
                $itensProventos[] = $item;
            }
        }

        $resultado = [
            'competencia' => $competencia,
            'apuracao_id' => $apuracaoAtual->id ?? null,
            'salario_base' => $salarioBase,
            'eventos' => round($proventosExtras, 2),
            'eventos_total' => round($proventosExtras, 2),
            'proventos' => $totalProventos,
            'total_proventos' => $totalProventos,
            'descontos_manuais' => round($descontosManuais, 2),
            'descontos_legais' => round($inss + $irrf, 2),
            'descontos' => $totalDescontos,
            'total_descontos' => $totalDescontos,
            'liquido' => $liquido,
            'total_liquido' => $liquido,
            'inss' => $inss,
            'fgts' => $fgts,
            'irrf' => $irrf,
            'base_inss' => $baseInss,
            'base_fgts' => $baseFgts,
            'base_irrf' => $baseIrrfEfetiva,
            'base_irrf_sem_simplificado' => $baseIrrf,
            'faixa_irrf' => $faixaIrrf,
            'desconto_simplificado' => $usarSimplificado ? $descontoSimplificado : 0.0,
            'usa_desconto_simplificado' => $usarSimplificado,
            'dependentes' => $dependentes,
            'deducao_dependentes' => $deducaoDependentes,
            'referencias_legais' => [
                'inss' => Config::get('rh_payroll.inss.fonte'),
                'irrf' => Config::get('rh_payroll.irrf.fonte'),
            ],
            'itens_proventos' => $itensProventos,
            'itens_descontos' => $itensDescontos,
            'eventos_apuracao' => $eventosApuracao,
            'json_motor' => [
                'funcionario_id' => $funcionario->id,
                'empresa_id' => $empresaId,
                'mes' => $competencia['mes'],
                'ano' => $competencia['ano'],
                'apuracao_origem_id' => $apuracaoAtual->id ?? null,
            ],
        ];

        if ($apuracaoAtual && $liquido <= 0) {
            $resultado['apuracao_id'] = $apuracaoAtual->id;
        }

        return $resultado;
    }

    public function calcularInss(float $base): float
    {
        $config = Config::get('rh_payroll.inss', []);
        $faixas = $config['faixas'] ?? [];
        $teto = (float) ($config['teto'] ?? $base);
        $base = round(min(max($base, 0), $teto), 2);

        $contribuicao = 0.0;
        $limiteAnterior = 0.0;
        foreach ($faixas as $faixa) {
            $limite = (float) ($faixa['ate'] ?? $teto);
            $aliquota = (float) ($faixa['aliquota'] ?? 0);
            if ($base <= $limiteAnterior) {
                break;
            }
            $parcela = min($base, $limite) - $limiteAnterior;
            if ($parcela > 0) {
                $contribuicao += $parcela * $aliquota;
            }
            $limiteAnterior = $limite;
        }

        return round($contribuicao, 2);
    }

    public function calcularIrrf(float $base, float $descontoAplicado = 0.0): float
    {
        $base = round(max($base - $descontoAplicado, 0), 2);
        $config = Config::get('rh_payroll.irrf', []);
        $faixas = $config['faixas'] ?? [];

        $impostoBase = 0.0;
        foreach ($faixas as $faixa) {
            $ate = $faixa['ate'];
            if ($ate === null || $base <= (float) $ate) {
                $aliquota = (float) ($faixa['aliquota'] ?? 0);
                $deducao = (float) ($faixa['deducao'] ?? 0);
                $impostoBase = max(($base * $aliquota) - $deducao, 0);
                break;
            }
        }

        $reducao = $config['reducao_mensal_2026'] ?? [];
        if (($reducao['ativa'] ?? false) === true) {
            $faixaIsenta = (float) ($reducao['faixa_isenta_ate'] ?? 0);
            $faixaReducao = (float) ($reducao['faixa_reducao_ate'] ?? 0);
            $constante = (float) ($reducao['formula_linear']['constante'] ?? 0);
            $coeficiente = (float) ($reducao['formula_linear']['coeficiente'] ?? 0);

            if ($base <= $faixaIsenta) {
                return 0.0;
            }

            if ($base <= $faixaReducao) {
                $reducaoImposto = max($constante - ($coeficiente * $base), 0);
                $impostoBase = max($impostoBase - $reducaoImposto, 0);
            }
        }

        return round($impostoBase, 2);
    }



    private function descobrirFaixaIrrf(float $base): float
    {
        $faixas = Config::get('rh_payroll.irrf.faixas', []);
        foreach ($faixas as $indice => $faixa) {
            $ate = $faixa['ate'] ?? null;
            if ($ate === null || $base <= (float) $ate) {
                return (float) ($indice + 1);
            }
        }

        return 0.0;
    }

    private function resolverQuantidadeDependentes(Funcionario $funcionario): int
    {
        if (Schema::hasColumn('funcionarios', 'qtd_dependentes')) {
            return max(0, (int) ($funcionario->qtd_dependentes ?? 0));
        }

        if (Schema::hasColumn('funcionarios', 'dependentes') && !($funcionario->dependentes instanceof Collection)) {
            return max(0, (int) ($funcionario->dependentes ?? 0));
        }

        if (method_exists($funcionario, 'dependentes')) {
            if ($funcionario->relationLoaded('dependentes')) {
                $loaded = $funcionario->getRelation('dependentes');
                if ($loaded instanceof Collection) {
                    return max(0, $loaded->count());
                }
            }

            try {
                return max(0, (int) $funcionario->dependentes()->count());
            } catch (\Throwable) {
                return 0;
            }
        }

        return 0;
    }

    private function buscarEventosEmpresa(int $empresaId): Collection
    {
        return EventoSalario::query()
            ->when(Schema::hasColumn('evento_salarios', 'empresa_id'), function ($q) use ($empresaId) {
                return $q->where('empresa_id', $empresaId);
            })
            ->when(Schema::hasColumn('evento_salarios', 'ativo'), function ($q) {
                return $q->where(function ($builder) {
                    $builder->whereNull('ativo')->orWhereIn('ativo', EventoSalario::ATIVO_VALUES);
                });
            })
            ->orderByRaw(Schema::hasColumn('evento_salarios', 'ordem_calculo') ? 'ordem_calculo asc' : 'id asc')
            ->get()
            ->filter(fn ($item) => $this->registroAtivo($item->ativo ?? 1))
            ->keyBy(fn ($item) => $this->eventoCodigo($item));
    }

    private function buscarEventosFuncionario(Funcionario $funcionario, int $empresaId): Collection
    {
        if (!Schema::hasTable('funcionario_eventos')) {
            return collect();
        }

        $query = FuncionarioEvento::query()
            ->with('evento')
            ->where('funcionario_id', $funcionario->id)
            ->when(Schema::hasColumn('funcionario_eventos', 'empresa_id'), fn ($q) => $q->where('empresa_id', $empresaId))
            ->when(Schema::hasColumn('funcionario_eventos', 'ativo'), function ($q) {
                return $q->where(function ($builder) {
                    $builder->whereNull('ativo')->orWhereIn('ativo', FuncionarioEvento::ATIVO_VALUES);
                });
            });

        if (Schema::hasTable('evento_salarios')) {
            $query->whereExists(function ($sub) use ($empresaId) {
                $sub->select(DB::raw(1))
                    ->from('evento_salarios')
                    ->whereColumn('evento_salarios.id', 'funcionario_eventos.evento_id');

                if (Schema::hasColumn('evento_salarios', 'empresa_id')) {
                    $sub->where('evento_salarios.empresa_id', $empresaId);
                }

                if (Schema::hasColumn('evento_salarios', 'ativo')) {
                    $sub->where(function ($builder) {
                        $builder->whereNull('evento_salarios.ativo')->orWhereIn('evento_salarios.ativo', EventoSalario::ATIVO_VALUES);
                    });
                }
            });
        }

        return $query
            ->orderBy('id')
            ->get()
            ->filter(function (FuncionarioEvento $vinculo) {
                return $this->registroAtivo($vinculo->ativo ?? 1)
                    && $vinculo->evento
                    && $this->registroAtivo($vinculo->evento->ativo ?? 1);
            })
            ->values();
    }

    private function buscarApuracao(int $funcionarioId, array $competencia, int $empresaId): ?ApuracaoMensal
    {
        if (!Schema::hasTable('apuracao_mensals')) {
            return null;
        }

        return ApuracaoMensal::query()
            ->where('funcionario_id', $funcionarioId)
            ->when(Schema::hasColumn('apuracao_mensals', 'empresa_id'), fn ($q) => $q->where('empresa_id', $empresaId))
            ->where('ano', $competencia['ano'])
            ->where(function (Builder $builder) use ($competencia) {
                $builder
                    ->where('mes', $competencia['mes'])
                    ->orWhere('mes', $competencia['mes_padded'])
                    ->orWhereRaw('LOWER(CAST(mes AS CHAR)) = ?', [mb_strtolower($competencia['mes_nome'])]);
            })
            ->latest('id')
            ->first();
    }

    private function resolverValorVinculo(FuncionarioEvento $vinculo, EventoSalario $evento, float $salarioBase): float
    {
        $valorBase = round((float) ($vinculo->valor ?? 0), 2);
        $referencia = round((float) ($vinculo->referencia ?? 0), 4);
        $tipoCalculo = strtolower((string) ($vinculo->tipo_calculo ?? ''));
        $tipoValor = strtolower((string) ($evento->tipo_valor ?? 'fixo'));
        $metodo = strtolower((string) ($evento->metodo ?? 'fixo'));
        $formula = strtolower(trim((string) ($evento->formula ?? '')));

        if ($tipoCalculo === 'formula' || in_array($formula, ['calc_inss', 'calc_irrf', 'calc_fgts'], true)) {
            return 0.0;
        }

        if ($tipoCalculo === 'quantidade' || $metodo === 'quantidade' || $tipoValor === 'quantidade') {
            $quantidade = $referencia > 0 ? $referencia : 1;
            return round($quantidade * $valorBase, 2);
        }

        if ($tipoCalculo === 'hora' || $metodo === 'hora' || $tipoValor === 'hora') {
            $horas = $referencia > 0 ? $referencia : 0;
            return round($horas * $valorBase, 2);
        }

        if ($tipoCalculo === 'diaria' || $metodo === 'diaria' || $tipoValor === 'diaria') {
            $dias = $referencia > 0 ? $referencia : 0;
            return round($dias * $valorBase, 2);
        }

        if ($tipoCalculo === 'percentual' || $tipoValor === 'percentual' || $metodo === 'percentual') {
            return round($salarioBase * ($valorBase / 100), 2);
        }

        return $valorBase;
    }

    private function resolverOrigemVinculo(FuncionarioEvento $vinculo, EventoSalario $evento): string
    {
        $tipoCalculo = strtolower((string) ($vinculo->tipo_calculo ?? ''));
        $tipoValor = strtolower((string) ($evento->tipo_valor ?? 'fixo'));
        $metodo = strtolower((string) ($evento->metodo ?? 'fixo'));

        if ($tipoCalculo === 'percentual' || $tipoValor === 'percentual' || $metodo === 'percentual') {
            return 'funcionario_percentual';
        }

        if (in_array($tipoCalculo, ['quantidade', 'hora', 'diaria'], true) || in_array($tipoValor, ['quantidade', 'hora', 'diaria'], true) || in_array($metodo, ['quantidade', 'hora', 'diaria'], true)) {
            return 'funcionario_referencia';
        }

        return 'funcionario_evento';
    }

    private function eventoIncide(EventoSalario $evento, string $base): bool
    {
        $map = [
            'inss' => ['incide_inss', 'incidencia_inss'],
            'fgts' => ['incide_fgts', 'incidencia_fgts'],
            'irrf' => ['incide_irrf', 'incidencia_irrf'],
        ];

        foreach ($map[$base] ?? [] as $column) {
            if (Schema::hasColumn('evento_salarios', $column)) {
                return (bool) ($evento->{$column} ?? false);
            }
        }

        return false;
    }

    private function eventoCodigo(EventoSalario $evento): string
    {
        return mb_strtoupper((string) ($evento->codigo ?: $evento->nome ?: $evento->id));
    }

    private function registroAtivo(mixed $value): bool
    {
        if ($value === null || $value === '') {
            return true;
        }

        if (is_bool($value)) {
            return $value;
        }

        if (is_numeric($value)) {
            return (int) $value === 1;
        }

        $normalized = mb_strtoupper(trim((string) $value));

        return in_array($normalized, ['1', 'S', 'SIM', 'A', 'ATIVO'], true);
    }

    private function normalizarInteiro(mixed $value): int
    {
        if ($value instanceof EventoSalario) {
            return (int) ($value->id ?? 0);
        }

        if ($value instanceof \Illuminate\Database\Eloquent\Model) {
            return (int) ($value->id ?? 0);
        }

        if ($value instanceof Collection) {
            return $this->normalizarInteiro($value->first());
        }

        if (is_object($value) && isset($value->id)) {
            return (int) $value->id;
        }

        if ($value === null || $value === '') {
            return 0;
        }

        return is_numeric($value) ? (int) $value : 0;
    }


    private function linhaEvento(EventoSalario $evento, float $valor, string $tipo, ?float $referencia = null, string $origem = 'motor', ?string $condicao = null): array
    {
        return [
            'evento_id' => $this->normalizarInteiro($evento->id),
            'codigo' => $evento->codigo ?: str_pad((string) $evento->id, 3, '0', STR_PAD_LEFT),
            'nome' => $evento->nome,
            'tipo' => $tipo,
            'metodo' => $evento->metodo ?: 'fixo',
            'condicao' => $condicao ?: ($evento->condicao ?: 'soma'),
            'referencia' => $referencia,
            'valor' => round($valor, 2),
            'origem' => $origem,
            'ordem_calculo' => (int) ($evento->ordem_calculo ?? 0),
        ];
    }
}
