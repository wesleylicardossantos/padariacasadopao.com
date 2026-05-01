<?php

namespace App\Services;

use App\Models\Funcionario;
use App\Models\RHParametroFiscal;
use App\Models\RHRescisao;
use App\Models\RHRescisaoItem;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class RHRescisaoService
{
    public function processar(Funcionario $funcionario, array $entrada): ?RHRescisao
    {
        if (!Schema::hasTable('rh_rescisoes') || !Schema::hasTable('rh_rescisao_itens')) {
            return null;
        }

        return DB::transaction(function () use ($funcionario, $entrada) {
            $param = $this->obterParametroFiscalAtivo();
            $dataRescisao = Carbon::parse($entrada['data_rescisao']);
            $dataAdmissao = $this->resolverDataAdmissao($funcionario, $dataRescisao);
            $salario = (float) ($funcionario->salario ?? 0);
            $dependentes = (int) ($entrada['dependentes_irrf'] ?? 0);
            $descontosExtras = round((float) ($entrada['descontos_extras'] ?? 0), 2);
            $tipoAviso = (string) ($entrada['tipo_aviso'] ?? 'indenizado');
            $motivo = (string) ($entrada['motivo'] ?? 'Desligamento');

            $saldoSalario = $this->calcularSaldoSalario($salario, $dataRescisao);
            $avisoPrevio = $this->calcularAvisoPrevio($salario, $tipoAviso);
            $feriasVencidas = $this->calcularFeriasVencidas($funcionario, $salario, $dataAdmissao, $dataRescisao);
            $feriasProporcionais = $this->calcularFeriasProporcionais($salario, $dataAdmissao, $dataRescisao);
            $tercoFerias = round(($feriasVencidas + $feriasProporcionais) / 3, 2);
            $decimoTerceiro = $this->calcularDecimoProporcional($salario, $dataRescisao);

            $baseFgts = round($saldoSalario + $avisoPrevio + $decimoTerceiro, 2);
            $fgtsDeposito = round($baseFgts * (((float) ($param->fgts_percentual ?? 8)) / 100), 2);
            $fgtsMulta = round($fgtsDeposito * (((float) ($param->fgts_multa_percentual ?? 40)) / 100), 2);

            $baseInss = round($saldoSalario + $avisoPrevio + $decimoTerceiro, 2);
            $inss = $this->calcularInssProgressivo($baseInss, (array) ($param->inss_faixas_json ?? []), (float) ($param->inss_teto ?? $baseInss));
            $baseIrrf = max(0, $baseInss - $inss);
            $irrf = $this->calcularIrrfMensal(
                $baseIrrf,
                (array) ($param->irrf_faixas_json ?? []),
                $dependentes,
                (float) ($param->irrf_dependente ?? 0),
                (float) ($param->irrf_desconto_simplificado ?? 0),
                true
            );

            $totalBruto = round($saldoSalario + $avisoPrevio + $feriasVencidas + $feriasProporcionais + $tercoFerias + $decimoTerceiro + $fgtsMulta, 2);
            $totalDescontos = round($inss + $irrf + $descontosExtras, 2);
            $totalLiquido = round($totalBruto - $totalDescontos, 2);

            $documentos = [
                'trct' => !empty($entrada['gerar_trct']),
                'tqrct' => !empty($entrada['gerar_tqrct']),
                'homologacao' => !empty($entrada['gerar_homologacao']),
                'bloquear_portal' => !empty($entrada['bloquear_portal']),
                'arquivo_morto' => !empty($entrada['arquivo_morto']),
            ];

            $rescisaoPayload = [
                'empresa_id' => (int) $funcionario->empresa_id,
                'funcionario_id' => (int) $funcionario->id,
                'desligamento_id' => $entrada['desligamento_id'] ?? null,
                'competencia' => $dataRescisao->format('Y-m'),
                'data_admissao' => $dataAdmissao?->toDateString(),
                'data_rescisao' => $dataRescisao->toDateString(),
                'motivo' => $motivo,
                'tipo_aviso' => $tipoAviso,
                'dependentes_irrf' => $dependentes,
                'descontos_extras' => $descontosExtras,
                'saldo_salario' => $saldoSalario,
                'ferias_vencidas' => $feriasVencidas,
                'ferias_proporcionais' => $feriasProporcionais,
                'terco_ferias' => $tercoFerias,
                'decimo_terceiro' => $decimoTerceiro,
                'aviso_previo' => $avisoPrevio,
                'fgts_base' => $baseFgts,
                'fgts_deposito' => $fgtsDeposito,
                'inss' => $inss,
                'irrf' => $irrf,
                'fgts_multa' => $fgtsMulta,
                'total_bruto' => $totalBruto,
                'total_descontos' => $totalDescontos,
                'total_liquido' => $totalLiquido,
                'observacoes' => (string) ($entrada['observacao'] ?? ''),
                'observacao' => (string) ($entrada['observacao'] ?? ''),
                'status' => 'processada',
                'documentos_json' => $documentos,
                'processado_em' => now(),
                'usuario_id' => auth()->id() ?: null,
            ];

            $rescisaoPayload = $this->filtrarPayloadPorSchema('rh_rescisoes', $rescisaoPayload);

            if (array_key_exists('status', $rescisaoPayload) && $rescisaoPayload['status'] === 'processada') {
                $rescisaoPayload['status'] = Schema::hasColumn('rh_rescisoes', 'processado_em') ? 'processado' : 'processada';
            }

            $rescisao = RHRescisao::create($rescisaoPayload);

            $this->criarItens($rescisao, [
                ['SALDO', 'Saldo de salário', 'provento', null, $saldoSalario],
                ['AVISO', 'Aviso prévio', 'provento', null, $avisoPrevio],
                ['FERVENC', 'Férias vencidas', 'provento', null, $feriasVencidas],
                ['FERPROP', 'Férias proporcionais', 'provento', null, $feriasProporcionais],
                ['FER13', '1/3 constitucional', 'provento', null, $tercoFerias],
                ['DECIMO', '13º proporcional', 'provento', null, $decimoTerceiro],
                ['FGTS40', 'Multa FGTS 40%', 'provento', null, $fgtsMulta],
                ['INSS', 'Desconto INSS', 'desconto', null, $inss],
                ['IRRF', 'Desconto IRRF', 'desconto', null, $irrf],
                ['OUTDESC', 'Outros descontos', 'desconto', null, $descontosExtras],
            ]);

            return $rescisao->load(['itens', 'funcionario']);
        });
    }

    public function resumoExecutivo(int $empresaId): array
    {
        if (!Schema::hasTable('rh_rescisoes')) {
            return [
                'total' => 0,
                'mes' => 0,
                'liquido_mes' => 0.0,
                'fgts_mes' => 0.0,
                'desligamentos_recentes' => collect(),
            ];
        }

        $query = RHRescisao::query()->where('empresa_id', $empresaId);
        $inicioMes = now()->startOfMonth()->toDateString();
        $fimMes = now()->endOfMonth()->toDateString();

        return [
            'total' => (clone $query)->count(),
            'mes' => (clone $query)->whereBetween('data_rescisao', [$inicioMes, $fimMes])->count(),
            'liquido_mes' => (float) ((clone $query)->whereBetween('data_rescisao', [$inicioMes, $fimMes])->sum('total_liquido') ?: 0),
            'fgts_mes' => (float) ((clone $query)->whereBetween('data_rescisao', [$inicioMes, $fimMes])->sum('fgts_multa') ?: 0),
            'desligamentos_recentes' => (clone $query)->with('funcionario')->orderByDesc('data_rescisao')->limit(10)->get(),
        ];
    }

    public function exportarFgts(int $empresaId): string
    {
        $linhas = ['FUNCIONARIO;CPF;DATA_RESCISAO;BASE_FGTS;DEPOSITO_FGTS;MULTA_FGTS;TOTAL_LIQUIDO'];
        if (!Schema::hasTable('rh_rescisoes')) {
            return implode(PHP_EOL, $linhas);
        }

        RHRescisao::query()
            ->with('funcionario')
            ->where('empresa_id', $empresaId)
            ->orderByDesc('data_rescisao')
            ->get()
            ->each(function (RHRescisao $rescisao) use (&$linhas) {
                $linhas[] = implode(';', [
                    $this->sanitizeCsv((string) optional($rescisao->funcionario)->nome),
                    $this->sanitizeCsv((string) optional($rescisao->funcionario)->cpf),
                    optional($rescisao->data_rescisao)?->format('Y-m-d'),
                    number_format((float) $rescisao->fgts_base, 2, '.', ''),
                    number_format((float) $rescisao->fgts_deposito, 2, '.', ''),
                    number_format((float) $rescisao->fgts_multa, 2, '.', ''),
                    number_format((float) $rescisao->total_liquido, 2, '.', ''),
                ]);
            });

        return implode(PHP_EOL, $linhas);
    }

    private function criarItens(RHRescisao $rescisao, array $itens): void
    {
        foreach ($itens as [$codigo, $descricao, $tipo, $referencia, $valor]) {
            if ((float) $valor <= 0) {
                continue;
            }

            RHRescisaoItem::create($this->filtrarPayloadPorSchema('rh_rescisao_itens', [
                'rescisao_id' => $rescisao->id,
                'codigo' => $codigo,
                'descricao' => $descricao,
                'tipo' => $tipo,
                'referencia' => $referencia,
                'valor' => round((float) $valor, 2),
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }

    private function filtrarPayloadPorSchema(string $table, array $payload): array
    {
        if (!Schema::hasTable($table)) {
            return $payload;
        }

        $allowed = [];
        foreach ($payload as $column => $value) {
            if (Schema::hasColumn($table, $column)) {
                $allowed[$column] = $value;
            }
        }

        return $allowed;
    }

    private function resolverDataAdmissao(Funcionario $funcionario, Carbon $fallback): ?Carbon
    {
        foreach (['data_admissao', 'admissao', 'data_registro', 'created_at'] as $campo) {
            $valor = data_get($funcionario, $campo);
            if (!empty($valor)) {
                return Carbon::parse($valor);
            }
        }

        return $fallback->copy()->subYear();
    }

    private function obterParametroFiscalAtivo(): RHParametroFiscal
    {
        if (Schema::hasTable('rh_parametros_fiscais')) {
            $param = RHParametroFiscal::query()->where('ativo', 1)->orderByDesc('competencia')->first();
            if ($param) {
                return $param;
            }
        }

        return new RHParametroFiscal([
            'competencia' => now()->format('Y-m'),
            'inss_faixas_json' => [
                ['ate' => 1621.00, 'aliquota' => 7.5],
                ['ate' => 2902.84, 'aliquota' => 9.0],
                ['ate' => 4354.27, 'aliquota' => 12.0],
                ['ate' => 8475.55, 'aliquota' => 14.0],
            ],
            'inss_teto' => 8475.55,
            'irrf_faixas_json' => [
                ['ate' => 2428.80, 'aliquota' => 0.0, 'deducao' => 0.00],
                ['ate' => 2826.65, 'aliquota' => 7.5, 'deducao' => 182.16],
                ['ate' => 3751.05, 'aliquota' => 15.0, 'deducao' => 394.16],
                ['ate' => 4664.68, 'aliquota' => 22.5, 'deducao' => 675.49],
                ['ate' => 99999999.99, 'aliquota' => 27.5, 'deducao' => 908.73],
            ],
            'irrf_dependente' => 189.59,
            'irrf_desconto_simplificado' => 607.20,
            'fgts_percentual' => 8,
            'fgts_multa_percentual' => 40,
            'ativo' => true,
        ]);
    }

    private function calcularSaldoSalario(float $salario, Carbon $dataRescisao): float
    {
        return round(($salario / 30) * max(1, min(30, $dataRescisao->day)), 2);
    }

    private function calcularAvisoPrevio(float $salario, string $tipoAviso): float
    {
        return $tipoAviso === 'trabalhado' ? 0.0 : round($salario, 2);
    }

    private function calcularFeriasVencidas(Funcionario $funcionario, float $salario, ?Carbon $admissao, Carbon $dataRescisao): float
    {
        if (!$admissao) {
            return 0.0;
        }
        $anos = max(0, $admissao->diffInYears($dataRescisao));
        return $anos >= 1 ? round($salario, 2) : 0.0;
    }

    private function calcularFeriasProporcionais(float $salario, ?Carbon $admissao, Carbon $dataRescisao): float
    {
        if (!$admissao) {
            return 0.0;
        }
        $meses = max(0, (($dataRescisao->year - $admissao->year) * 12) + $dataRescisao->month - $admissao->month);
        $proporcionais = min(12, ($meses % 12) + ($dataRescisao->day >= 15 ? 1 : 0));
        return round(($salario / 12) * $proporcionais, 2);
    }

    private function calcularDecimoProporcional(float $salario, Carbon $dataRescisao): float
    {
        $avos = min(12, max(0, $dataRescisao->month - ($dataRescisao->day < 15 ? 1 : 0)));
        return round(($salario / 12) * $avos, 2);
    }

    private function calcularInssProgressivo(float $base, array $faixas, float $teto): float
    {
        if ($base <= 0 || empty($faixas)) {
            return 0.0;
        }

        $baseCalculo = min($base, $teto);
        $total = 0.0;
        $inicio = 0.0;

        foreach ($faixas as $faixa) {
            $fim = (float) ($faixa['ate'] ?? 0);
            $aliquota = ((float) ($faixa['aliquota'] ?? 0)) / 100;
            if ($baseCalculo > $inicio) {
                $parcela = min($baseCalculo, $fim) - $inicio;
                if ($parcela > 0) {
                    $total += $parcela * $aliquota;
                }
            }
            if ($baseCalculo <= $fim) {
                break;
            }
            $inicio = $fim;
        }

        return round($total, 2);
    }

    private function calcularIrrfMensal(float $baseTributavel, array $faixas, int $dependentes, float $valorDependente, float $descontoSimplificado, bool $usarSimplificado = true): float
    {
        if ($baseTributavel <= 0 || empty($faixas)) {
            return 0.0;
        }

        $deducaoDependentes = $dependentes * $valorDependente;
        $baseComDependentes = max(0, $baseTributavel - $deducaoDependentes);
        $baseFinal = $usarSimplificado ? max(0, $baseComDependentes - $descontoSimplificado) : $baseComDependentes;

        foreach ($faixas as $faixa) {
            if ($baseFinal <= (float) ($faixa['ate'] ?? 0)) {
                $aliquota = ((float) ($faixa['aliquota'] ?? 0)) / 100;
                $deducao = (float) ($faixa['deducao'] ?? 0);
                return round(max(0, ($baseFinal * $aliquota) - $deducao), 2);
            }
        }

        return 0.0;
    }

    private function sanitizeCsv(string $value): string
    {
        return str_replace([';', "
", "
"], [' ', ' ', ' '], trim($value));
    }
}
