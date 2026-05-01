<?php

namespace App\Services;

use App\Models\ApuracaoMensal;
use App\Models\ApuracaoSalarioEvento;
use App\Models\Funcionario;
use App\Models\RHCompetencia;
use App\Models\RHFolhaFechamento;
use App\Models\RHFolhaItem;
use App\Modules\RH\Application\Financeiro\FolhaFinanceiroService;
use App\Support\RHCompetenciaHelper;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class RHFolhaCompetenciaService
{
    public function __construct(
        private RHDefaultPayrollEventService $defaultEvents,
        private RHFolhaEngineService $engine,
        private FolhaFinanceiroService $financeiro,
    ) {
    }

    public function processar(int $empresaId, int $mes, int $ano, bool $sobrescrever = false, bool $integrarFinanceiro = true, ?string $vencimento = null, ?int $filialId = null): int
    {
        $this->defaultEvents->ensureDefaultsForEmpresa($empresaId);
        $competencia = $this->abrirCompetencia($empresaId, $mes, $ano, $sobrescrever);
        $mesNome = RHCompetenciaHelper::nome($mes);

        return DB::transaction(function () use ($empresaId, $mes, $ano, $mesNome, $competencia, $sobrescrever, $integrarFinanceiro, $vencimento, $filialId) {
            $gerados = 0;
            $totais = [
                'salario_base_total' => 0.0,
                'eventos_total' => 0.0,
                'descontos_total' => 0.0,
                'liquido_total' => 0.0,
            ];

            $funcionarios = Funcionario::query()
                ->when($empresaId > 0, fn ($q) => $q->where('empresa_id', $empresaId))
                ->when(Schema::hasColumn('funcionarios', 'ativo'), fn ($q) => $q->where('ativo', 1))
                ->orderBy('nome')
                ->get();

            foreach ($funcionarios as $funcionario) {
                $this->defaultEvents->syncFuncionarioBaseEvents($funcionario, $empresaId);

                $existing = ApuracaoMensal::query()
                    ->where('funcionario_id', $funcionario->id)
                    ->where('ano', $ano)
                    ->where('mes', $mesNome)
                    ->when($empresaId > 0 && Schema::hasColumn('apuracao_mensals', 'empresa_id'), fn ($q) => $q->where('empresa_id', $empresaId))
                    ->first();

                if ($existing && !$sobrescrever) {
                    continue;
                }

                if ($existing && $sobrescrever) {
                    ApuracaoSalarioEvento::query()->where('apuracao_id', $existing->id)->delete();
                    if (Schema::hasTable('rh_folha_itens') && Schema::hasColumn('rh_folha_itens', 'apuracao_id')) {
                        RHFolhaItem::query()->where('apuracao_id', $existing->id)->delete();
                    }
                    $existing->delete();
                }

                $calculo = $this->normalizarResultadoCalculo(
                    $this->engine->calcularFuncionario($funcionario, $mes, $ano, $empresaId)
                );
                if ($calculo['total_proventos'] <= 0 && $calculo['total_descontos'] <= 0 && $calculo['liquido'] <= 0) {
                    continue;
                }

                $apuracaoPayload = [
                    'funcionario_id' => $funcionario->id,
                    'mes' => $mesNome,
                    'ano' => $ano,
                    'valor_final' => $calculo['liquido'],
                    'forma_pagamento' => 'Outros',
                    'observacao' => 'Gerada automaticamente pelo motor mensal da folha',
                    'conta_pagar_id' => 0,
                ];
                foreach (['empresa_id', 'competencia_id', 'total_proventos', 'total_descontos', 'liquido', 'base_inss', 'base_fgts', 'base_irrf', 'json_calculo'] as $column) {
                    if (Schema::hasColumn('apuracao_mensals', $column)) {
                        $apuracaoPayload[$column] = match ($column) {
                            'empresa_id' => $empresaId,
                            'competencia_id' => $competencia->id,
                            'total_proventos' => $calculo['total_proventos'],
                            'total_descontos' => $calculo['total_descontos'],
                            'liquido' => $calculo['liquido'],
                            'base_inss' => $calculo['base_inss'],
                            'base_fgts' => $calculo['base_fgts'],
                            'base_irrf' => $calculo['base_irrf'],
                            'json_calculo' => json_encode($calculo, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                        };
                    }
                }

                $apuracao = ApuracaoMensal::create($apuracaoPayload);

                foreach ($calculo['eventos_apuracao'] as $eventoLinha) {
                    $eventoLinha = $this->normalizarLinhaEvento($eventoLinha);

                    $payload = [
                        'apuracao_id' => (int) $apuracao->id,
                        'evento_id' => $this->normalizarId($eventoLinha['evento_id'] ?? null),
                        'valor' => (float) ($eventoLinha['valor'] ?? 0),
                        'metodo' => $this->normalizarTexto($eventoLinha['metodo'] ?? null),
                        'condicao' => $this->normalizarTexto($eventoLinha['condicao'] ?? null),
                        'nome' => $this->normalizarTexto($eventoLinha['nome'] ?? null),
                    ];
                    if (Schema::hasColumn('apuracao_salario_eventos', 'empresa_id')) {
                        $payload['empresa_id'] = $empresaId;
                    }
                    ApuracaoSalarioEvento::create($payload);

                    if (Schema::hasTable('rh_folha_itens')) {
                        $itemPayload = [];
                        foreach ([
                            'empresa_id' => (int) $empresaId,
                            'competencia_id' => (int) $competencia->id,
                            'apuracao_id' => (int) $apuracao->id,
                            'funcionario_id' => (int) $funcionario->id,
                            'evento_id' => $this->normalizarId($eventoLinha['evento_id'] ?? null),
                            'codigo' => $this->normalizarTexto($eventoLinha['codigo'] ?? null),
                            'nome' => $this->normalizarTexto($eventoLinha['nome'] ?? null),
                            'tipo' => $this->normalizarTexto($eventoLinha['tipo'] ?? null),
                            'condicao' => $this->normalizarTexto($eventoLinha['condicao'] ?? null),
                            'referencia' => $this->normalizarNumero($eventoLinha['referencia'] ?? null),
                            'valor' => $this->normalizarNumero($eventoLinha['valor'] ?? 0) ?? 0,
                            'origem' => $this->normalizarTexto($eventoLinha['origem'] ?? null),
                        ] as $column => $value) {
                            if (Schema::hasColumn('rh_folha_itens', $column)) {
                                $itemPayload[$column] = $value;
                            }
                        }
                        RHFolhaItem::create($itemPayload);
                    }
                }

                if ($integrarFinanceiro) {
                    $this->financeiro->sincronizarApuracao(
                        $apuracao->fresh('funcionario'),
                        $vencimento,
                        $this->normalizarId($filialId)
                    );
                }

                $totais['salario_base_total'] += $calculo['salario_base'];
                $totais['eventos_total'] += $calculo['eventos_total'];
                $totais['descontos_total'] += $calculo['total_descontos'];
                $totais['liquido_total'] += $calculo['liquido'];
                $gerados++;
            }

            $this->atualizarFechamento($empresaId, $mes, $ano, $totais);
            $competencia->status = 'processada';
            if (Schema::hasColumn('rh_competencias', 'processado_em')) {
                $competencia->processado_em = now();
            }
            $competencia->save();

            return $gerados;
        });
    }

    private function abrirCompetencia(int $empresaId, int $mes, int $ano, bool $sobrescrever): RHCompetencia
    {
        if (!Schema::hasTable('rh_competencias')) {
            throw new \RuntimeException('Tabela rh_competencias não encontrada. Rode as migrations novas.');
        }

        $competencia = RHCompetencia::query()->firstOrCreate(
            ['empresa_id' => $empresaId, 'mes' => $mes, 'ano' => $ano],
            ['status' => 'aberta']
        );

        if ($sobrescrever) {
            if (Schema::hasTable('rh_folha_itens')) {
                RHFolhaItem::query()->where('competencia_id', $competencia->id)->delete();
            }
            ApuracaoMensal::query()
                ->where('ano', $ano)
                ->where('mes', RHCompetenciaHelper::nome($mes))
                ->when($empresaId > 0 && Schema::hasColumn('apuracao_mensals', 'empresa_id'), fn ($q) => $q->where('empresa_id', $empresaId))
                ->get()
                ->each(function ($item) {
                    ApuracaoSalarioEvento::query()->where('apuracao_id', $item->id)->delete();
                    $item->delete();
                });
        }

        return $competencia;
    }

    private function atualizarFechamento(int $empresaId, int $mes, int $ano, array $totais): void
    {
        if (!Schema::hasTable('rh_folha_fechamentos')) {
            return;
        }

        $model = RHFolhaFechamento::query()->firstOrNew([
            'empresa_id' => $empresaId,
            'mes' => $mes,
            'ano' => $ano,
        ]);

        $model->fill([
            'salario_base_total' => round((float) $totais['salario_base_total'], 2),
            'eventos_total' => round((float) $totais['eventos_total'], 2),
            'descontos_total' => round((float) $totais['descontos_total'], 2),
            'liquido_total' => round((float) $totais['liquido_total'], 2),
            'status' => 'processada',
            'observacao' => 'Atualizada automaticamente pelo motor mensal da folha.',
        ]);
        $model->save();
    }





    private function normalizarResultadoCalculo(mixed $calculo): array
    {
        if ($calculo instanceof Model) {
            $calculo = $calculo->toArray();
        }

        if ($calculo instanceof EloquentCollection || $calculo instanceof Collection) {
            $calculo = $calculo->first();
        }

        if (is_object($calculo) && method_exists($calculo, 'toArray')) {
            $calculo = $calculo->toArray();
        } elseif (is_object($calculo)) {
            $calculo = (array) $calculo;
        }

        if (!is_array($calculo)) {
            return [
                'total_proventos' => 0.0,
                'total_descontos' => 0.0,
                'liquido' => 0.0,
                'salario_base' => 0.0,
                'eventos_total' => 0.0,
                'base_inss' => 0.0,
                'base_fgts' => 0.0,
                'base_irrf' => 0.0,
                'eventos_apuracao' => [],
            ];
        }

        foreach (['total_proventos', 'total_descontos', 'liquido', 'salario_base', 'eventos_total', 'base_inss', 'base_fgts', 'base_irrf'] as $campo) {
            $calculo[$campo] = $this->normalizarNumero($calculo[$campo] ?? 0) ?? 0.0;
        }

        $eventos = $calculo['eventos_apuracao'] ?? [];
        if ($eventos instanceof EloquentCollection || $eventos instanceof Collection) {
            $eventos = $eventos->all();
        } elseif ($eventos instanceof Model) {
            $eventos = [$eventos->toArray()];
        } elseif (is_object($eventos) && method_exists($eventos, 'toArray')) {
            $eventos = $eventos->toArray();
        } elseif (!is_array($eventos)) {
            $eventos = [];
        }

        $calculo['eventos_apuracao'] = array_values(array_filter(array_map(fn ($linha) => $this->normalizarLinhaEvento($linha), $eventos), fn ($linha) => is_array($linha) && !empty($linha)));

        return $calculo;
    }

    private function normalizarLinhaEvento(mixed $linha): array
    {
        if ($linha instanceof Model) {
            $linha = $linha->toArray();
        }

        if ($linha instanceof EloquentCollection || $linha instanceof Collection) {
            $linha = $linha->first();
        }

        if (is_object($linha) && method_exists($linha, 'toArray')) {
            $linha = $linha->toArray();
        } elseif (is_object($linha)) {
            $linha = (array) $linha;
        }

        if (!is_array($linha)) {
            return [];
        }

        $linha['evento_id'] = $this->normalizarId($linha['evento_id'] ?? null);
        $linha['valor'] = $this->normalizarNumero($linha['valor'] ?? 0) ?? 0.0;
        $linha['referencia'] = $this->normalizarNumero($linha['referencia'] ?? null);
        foreach (['codigo', 'nome', 'tipo', 'metodo', 'condicao', 'origem'] as $campo) {
            $linha[$campo] = $this->normalizarTexto($linha[$campo] ?? null);
        }
        $linha['ordem_calculo'] = $this->normalizarId($linha['ordem_calculo'] ?? 0) ?? 0;

        return $linha;
    }

    private function normalizarNumero(mixed $value): ?float
    {
        if ($value instanceof Model) {
            return $this->normalizarNumero($value->id ?? null);
        }

        if ($value instanceof EloquentCollection || $value instanceof Collection) {
            return $this->normalizarNumero($value->first());
        }

        if (is_object($value) && isset($value->id)) {
            return $this->normalizarNumero($value->id);
        }

        if ($value === null || $value === '') {
            return null;
        }

        return is_numeric($value) ? (float) $value : null;
    }

    private function normalizarTexto(mixed $value): ?string
    {
        if ($value instanceof Model) {
            return $this->normalizarTexto($value->id ?? null);
        }

        if ($value instanceof EloquentCollection || $value instanceof Collection) {
            return $this->normalizarTexto($value->first());
        }

        if (is_object($value) && isset($value->id)) {
            return $this->normalizarTexto($value->id);
        }

        if ($value === null) {
            return null;
        }

        return trim((string) $value);
    }


    private function normalizarId(mixed $value): ?int
    {
        if ($value instanceof Model) {
            return isset($value->id) ? (int) $value->id : null;
        }

        if ($value instanceof EloquentCollection || $value instanceof Collection) {
            $first = $value->first();
            return $this->normalizarId($first);
        }

        if (is_object($value) && isset($value->id)) {
            return (int) $value->id;
        }

        if ($value === null || $value === '') {
            return null;
        }

        return (int) $value;
    }
}
