<?php

namespace App\Modules\RH\Application\ApuracaoMensal;

use App\Models\ApuracaoMensal;
use App\Models\ApuracaoSalarioEvento;
use App\Models\EventoSalario;
use App\Models\Funcionario;
use App\Models\FuncionarioEvento;
use Illuminate\Http\Request;
use App\Modules\RH\Application\Financeiro\FolhaFinanceiroService;
use App\Services\RHFolhaCompetenciaService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ApuracaoMensalService
{
    public function __construct(
        private FolhaFinanceiroService $folhaFinanceiro,
        private RHFolhaCompetenciaService $competenciaService,
    ) {
    }

    public function store(Request $request, int $empresaId): ApuracaoMensal
    {
        return DB::transaction(function () use ($request, $empresaId) {
            $ap = [
                'funcionario_id' => $request->funcionario,
                'mes' => $request->mes,
                'ano' => $request->ano,
                'valor_final' => __convert_value_bd($request->valor_total),
                'forma_pagamento' => $request->tipo_pagamento,
                'observacao' => $request->observacao ?? '',
            ];

            if (Schema::hasColumn('apuracao_mensals', 'empresa_id')) {
                $ap['empresa_id'] = $empresaId;
            }

            $apuracao = ApuracaoMensal::create($ap);

            foreach (($request->evento ?? []) as $i => $eventoId) {
                $evento = EventoSalario::find($eventoId);
                if (!$evento) {
                    continue;
                }

                ApuracaoSalarioEvento::create([
                    'empresa_id' => $empresaId,
                    'apuracao_id' => $apuracao->id,
                    'evento_id' => $evento->id,
                    'valor' => __convert_value_bd($request->valor[$i] ?? 0),
                    'metodo' => $request->metodo[$i] ?? null,
                    'condicao' => $request->condicao[$i] ?? null,
                    'nome' => $evento->nome,
                ]);
            }

            if ($request->conta_pagar) {
                $this->folhaFinanceiro->sincronizarApuracao(
                    $apuracao->fresh('funcionario'),
                    $request->vencimento,
                    $request->filial_id ?: null
                );
            }

            return $apuracao;
        });
    }


    public function gerarAutomatica(int $empresaId, int $mes, int $ano, bool $sobrescrever = false, bool $integrarFinanceiro = false, ?string $vencimento = null, ?int $filialId = null): int
    {
        return $this->competenciaService->processar(
            $empresaId,
            $mes,
            $ano,
            $sobrescrever,
            $integrarFinanceiro,
            $vencimento,
            $filialId
        );
    }
}
