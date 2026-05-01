<?php

namespace App\Http\Controllers;

use App\Models\CategoriaConta;
use App\Models\ContaPagar;
use App\Models\ContaReceber;
use App\Models\Funcionario;
use App\Services\RHFolhaCalculoService;
use Illuminate\Http\Request;
use App\Modules\RH\Support\RHContext;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class RHDreFolhaDetalhadoController extends Controller
{
    public function __construct(private RHFolhaCalculoService $folhaCalculo)
    {
    }

    public function index(Request $request)
    {
        $empresaId = $this->resolveEmpresaId($request);
        $mes = max(1, min(12, (int) ($request->mes ?: date('m'))));
        $ano = (int) ($request->ano ?: date('Y'));
        $encargosPerc = $this->toFloat($request->encargos, 28);
        $beneficiosPerc = $this->toFloat($request->beneficios, 8);
        $provisoesPerc = $this->toFloat($request->provisoes, 11.11);

        $inicio = sprintf('%04d-%02d-01', $ano, $mes);
        $fim = date('Y-m-t', strtotime($inicio));

        $receitaBruta = $this->sumReceita($empresaId, $inicio, $fim);
        $despesasOperacionais = $this->sumDespesasOperacionais($empresaId, $inicio, $fim);
        $funcionarios = $this->buscarFuncionarios($empresaId);

        $detalhes = [];
        $salarios = 0.0;
        $encargos = 0.0;
        $beneficios = 0.0;
        $provisoes = 0.0;
        $descontos = 0.0;
        $folhaTotal = 0.0;

        foreach ($funcionarios as $funcionario) {
            $valores = $this->safeCalcularFuncionario($funcionario, $mes, $ano);
            $subtotal = round((float) ($valores['proventos'] ?? (($funcionario->salario ?? 0) + ($valores['eventos'] ?? 0))), 2);
            $desconto = round((float) ($valores['descontos'] ?? 0), 2);
            $enc = round($subtotal * ($encargosPerc / 100), 2);
            $ben = round($subtotal * ($beneficiosPerc / 100), 2);
            $prov = round($subtotal * ($provisoesPerc / 100), 2);
            $custo = round($subtotal + $enc + $ben + $prov - $desconto, 2);

            $salarios += $subtotal;
            $encargos += $enc;
            $beneficios += $ben;
            $provisoes += $prov;
            $descontos += $desconto;
            $folhaTotal += $custo;

            $detalhes[] = [
                'funcionario' => $funcionario,
                'subtotal' => $subtotal,
                'encargos' => $enc,
                'beneficios' => $ben,
                'provisoes' => $prov,
                'descontos' => $desconto,
                'custo' => $custo,
            ];
        }

        usort($detalhes, fn ($a, $b) => $b['custo'] <=> $a['custo']);

        $resultadoOperacional = $receitaBruta - $despesasOperacionais - $folhaTotal;
        $margemOperacional = $receitaBruta > 0 ? ($resultadoOperacional / $receitaBruta) * 100 : 0;

        return view('rh.dre_folha_detalhado.index', compact(
            'mes',
            'ano',
            'encargosPerc',
            'beneficiosPerc',
            'provisoesPerc',
            'receitaBruta',
            'despesasOperacionais',
            'salarios',
            'encargos',
            'beneficios',
            'provisoes',
            'descontos',
            'folhaTotal',
            'resultadoOperacional',
            'margemOperacional',
            'detalhes'
        ));
    }

    private function resolveEmpresaId(Request $request): int
    {
        $candidatos = [
            $request->get('empresa_id'),
            $request->get('empresa_id'),
            data_get(session('empresa_selecionada'), 'empresa_id'),
            optional(Auth::user())->empresa_id,
            session('empresa_id'),
            data_get(session('user_logged'), 'empresa'),
            data_get(session('user_logged'), 'empresa_id'),
            data_get(session('usuario'), 'empresa'),
            data_get(session('usuario'), 'empresa_id'),
        ];

        foreach ($candidatos as $candidato) {
            if (is_numeric($candidato) && (int) $candidato > 0) {
                return (int) $candidato;
            }
        }

        return 0;
    }

    private function buscarFuncionarios(int $empresaId): Collection
    {
        if (!Schema::hasTable('funcionarios')) {
            return collect();
        }

        $query = Funcionario::query();
        if ($empresaId > 0 && Schema::hasColumn('funcionarios', 'empresa_id')) {
            $query->where('empresa_id', $empresaId);
        }

        if (Schema::hasColumn('funcionarios', 'ativo')) {
            $query->where(function ($q) {
                $q->whereNull('ativo')
                    ->orWhere('ativo', 1)
                    ->orWhere('ativo', true)
                    ->orWhere('ativo', '1')
                    ->orWhereRaw('LOWER(CAST(ativo AS CHAR)) in (?, ?)', ['s', 'sim']);
            });
        }

        $funcionarios = $query->orderBy('nome')->get();

        if ($funcionarios->isEmpty() && $empresaId > 0) {
            $fallback = Funcionario::query();
            if (Schema::hasColumn('funcionarios', 'empresa_id')) {
                $fallback->where('empresa_id', $empresaId);
            }
            $funcionarios = $fallback->orderBy('nome')->get();
        }

        return $funcionarios;
    }

    private function safeCalcularFuncionario($funcionario, int $mes, int $ano): array
    {
        try {
            $valores = $this->folhaCalculo->calcularFuncionario($funcionario, $mes, $ano);
            return is_array($valores) ? $valores : [];
        } catch (\Throwable $e) {
            return [
                'proventos' => round((float) ($funcionario->salario ?? 0), 2),
                'descontos' => 0.0,
                'eventos' => 0.0,
            ];
        }
    }

    private function sumReceita(int $empresaId, string $inicio, string $fim): float
    {
        if ($empresaId <= 0 || !Schema::hasTable('conta_recebers') || !Schema::hasColumn('conta_recebers', 'data_vencimento') || !Schema::hasColumn('conta_recebers', 'valor_integral')) {
            return 0.0;
        }

        $query = ContaReceber::query()->whereBetween('data_vencimento', [$inicio, $fim]);
        if (Schema::hasColumn('conta_recebers', 'empresa_id')) {
            $query->where('empresa_id', $empresaId);
        }

        return round((float) $query->sum('valor_integral'), 2);
    }

    private function sumDespesasOperacionais(int $empresaId, string $inicio, string $fim): float
    {
        if ($empresaId <= 0 || !Schema::hasTable('conta_pagars') || !Schema::hasColumn('conta_pagars', 'data_vencimento') || !Schema::hasColumn('conta_pagars', 'valor_integral')) {
            return 0.0;
        }

        $query = ContaPagar::query()->whereBetween('data_vencimento', [$inicio, $fim]);
        if (Schema::hasColumn('conta_pagars', 'empresa_id')) {
            $query->where('empresa_id', $empresaId);
        }

        if (Schema::hasTable('categoria_contas') && Schema::hasColumn('conta_pagars', 'categoria_id')) {
            $categoriaFolhaId = CategoriaConta::query()
                ->when(Schema::hasColumn('categoria_contas', 'empresa_id'), fn ($q) => $q->where('empresa_id', $empresaId))
                ->when(Schema::hasColumn('categoria_contas', 'tipo'), fn ($q) => $q->where('tipo', 'pagar'))
                ->where('nome', 'Folha de Pagamento')
                ->value('id');

            if ($categoriaFolhaId) {
                $query->where(function ($sub) use ($categoriaFolhaId) {
                    $sub->whereNull('categoria_id')->orWhere('categoria_id', '!=', $categoriaFolhaId);
                });
            }
        }

        return round((float) $query->sum('valor_integral'), 2);
    }

    private function toFloat($value, float $default): float
    {
        if ($value === null || $value === '') {
            return $default;
        }

        return (float) str_replace(',', '.', (string) $value);
    }
}
