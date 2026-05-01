<?php

namespace App\Modules\RH\Application\Financeiro;

use App\Models\ApuracaoMensal;
use App\Models\CategoriaConta;
use App\Models\ContaPagar;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class FolhaFinanceiroService
{
    public function sincronizarApuracao(ApuracaoMensal $apuracao, ?string $vencimento = null, ?int $filialId = null): ContaPagar
    {
        $empresaId = (int) ($apuracao->empresa_id ?: optional($apuracao->funcionario)->empresa_id ?: 0);
        if ($empresaId <= 0) {
            throw new \RuntimeException('Empresa da apuração não identificada para integração financeira.');
        }

        $categoria = $this->normalizarModel(CategoriaConta::firstOrCreate(
            [
                'empresa_id' => $empresaId,
                'nome' => 'Folha de Pagamento',
                'tipo' => 'pagar',
            ],
            [
                'empresa_id' => $empresaId,
                'nome' => 'Folha de Pagamento',
                'tipo' => 'pagar',
            ]
        ));

        if (!$categoria instanceof CategoriaConta) {
            throw new \RuntimeException('Categoria financeira da folha não pôde ser resolvida.');
        }

        $vencimentoDate = $this->resolverVencimento($apuracao, $vencimento);
        $referencia = $this->referencia($apuracao);

        $filialId = $this->normalizarId($filialId);

        $payload = [
            'empresa_id' => $empresaId,
            'compra_id' => null,
            'categoria_id' => $this->normalizarId($categoria->id),
            'fornecedor_id' => 0,
            'referencia' => $referencia,
            'valor_integral' => (float) $apuracao->valor_final,
            'data_vencimento' => $vencimentoDate,
            'data_pagamento' => $vencimentoDate,
            'status' => 0,
            'tipo_pagamento' => $apuracao->forma_pagamento ?: 'Folha',
            'filial_id' => $filialId,
        ];

        $conta = null;

        if (!empty($apuracao->conta_pagar_id)) {
            $conta = $this->normalizarModel(ContaPagar::find($this->normalizarId($apuracao->conta_pagar_id)));
        }

        if (!$conta) {
            $conta = ContaPagar::query()
                ->where('empresa_id', $empresaId)
                ->where('referencia', $referencia)
                ->where('categoria_id', $categoria->id)
                ->where('fornecedor_id', 0)
                ->first();
            $conta = $this->normalizarModel($conta);
        }

        if ($conta && (float) $conta->valor_pago > 0) {
            throw new \RuntimeException('A conta da folha já possui pagamento registrado e não pode ser sobrescrita.');
        }

        if ($conta) {
            $conta->fill($payload);
            $conta->valor_pago = 0;
            $conta->save();
        } else {
            $payload['valor_pago'] = 0;
            $conta = ContaPagar::create($payload);
        }

        if ((int) $apuracao->conta_pagar_id !== (int) $conta->id) {
            $apuracao->conta_pagar_id = $conta->id;
            $apuracao->save();
        }

        return $conta;
    }

    public function sincronizarCompetencia(int $empresaId, int $mes, int $ano, ?string $vencimento = null, ?int $filialId = null): int
    {
        return DB::transaction(function () use ($empresaId, $mes, $ano, $vencimento, $filialId) {
            $mesNome = $this->mesNome($mes);

            $apuracoes = ApuracaoMensal::with('funcionario')
                ->when($empresaId > 0, fn ($q) => $q->where('empresa_id', $empresaId))
                ->where('mes', $mesNome)
                ->where('ano', $ano)
                ->orderBy('id')
                ->get();

            $sincronizados = 0;
            foreach ($apuracoes as $apuracao) {
                $this->sincronizarApuracao($apuracao, $vencimento, $filialId);
                $sincronizados++;
            }

            return $sincronizados;
        });
    }

    public function mesNome(int $mes): string
    {
        $meses = ApuracaoMensal::mesesApuracao();
        $indice = max(1, min(12, $mes));
        return $meses[$indice - 1] ?? strtolower(date('F'));
    }





    private function normalizarModel(mixed $value): ?Model
    {
        if ($value instanceof Model) {
            return $value;
        }

        if ($value instanceof EloquentCollection || $value instanceof Collection) {
            $first = $value->first();
            return $first instanceof Model ? $first : null;
        }

        return null;
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

    private function resolverVencimento(ApuracaoMensal $apuracao, ?string $vencimento = null): string
    {
        if (!empty($vencimento)) {
            return Carbon::parse($vencimento)->format('Y-m-d');
        }

        $mesNumero = array_search(mb_strtolower((string) $apuracao->mes), ApuracaoMensal::mesesApuracao(), true);
        $mesNumero = $mesNumero === false ? (int) date('m') : ($mesNumero + 1);

        return Carbon::create((int) $apuracao->ano, $mesNumero, 1)->endOfMonth()->format('Y-m-d');
    }

    private function referencia(ApuracaoMensal $apuracao): string
    {
        $nome = trim((string) optional($apuracao->funcionario)->nome);
        $competencia = strtoupper((string) $apuracao->mes) . '/' . $apuracao->ano;

        return $nome !== ''
            ? sprintf('Folha %s - %s', $competencia, $nome)
            : sprintf('Folha %s - Apuração %d', $competencia, $apuracao->id);
    }
}
