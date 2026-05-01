<?php

namespace App\Http\Controllers;

use App\Models\SangriaCaixa;
use App\Models\VendaCaixa;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RelatorioVendasResumidoController extends Controller
{
    protected $empresa_id = null;

    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $value = session('user_logged');
            if (!$value) {
                return redirect('/login');
            }

            $this->empresa_id = $this->resolverEmpresaId($request, $value);

            if ($this->empresa_id) {
                $request->merge(['empresa_id' => $this->empresa_id]);
            }

            return $next($request);
        });
    }

    public function index(Request $request)
    {
        $mesSelecionado = $request->get('mes');

        try {
            $referencia = $mesSelecionado
                ? Carbon::createFromFormat('Y-m', $mesSelecionado)->startOfMonth()
                : Carbon::now()->startOfMonth();
        } catch (\Throwable $e) {
            $referencia = Carbon::now()->startOfMonth();
        }

        $inicio = $referencia->copy()->startOfMonth()->startOfDay();
        $fim = $referencia->copy()->endOfMonth()->endOfDay();

        $periodo = [];
        $cursor = $inicio->copy();
        while ($cursor->lte($fim)) {
            $periodo[$cursor->format('Y-m-d')] = [
                'data' => $cursor->format('d/m'),
                'dia' => $this->diaSemanaAbreviado($cursor),
                'v_manha' => 0.0,
                'v_tarde' => 0.0,
                'v_cartao' => 0.0,
                'saidas_cx' => 0.0,
                'venda_dia' => 0.0,
            ];
            $cursor->addDay();
        }

        $this->preencherResumoPdv($periodo, $inicio, $fim);
        $this->preencherSaidasCaixa($periodo, $inicio, $fim);

        $linhas = array_values(array_filter($periodo, function ($item) {
            return round($item['v_manha'], 2) > 0
                || round($item['v_tarde'], 2) > 0
                || round($item['v_cartao'], 2) > 0
                || round($item['saidas_cx'], 2) > 0
                || round($item['venda_dia'], 2) > 0;
        }));

        $totalVendas = array_reduce($linhas, function ($carry, $item) {
            return $carry + (float) $item['venda_dia'];
        }, 0.0);

        return view('controle.relatorio_vendas_resumido', [
            'title' => 'Relatório de Vendas - Resumido',
            'rotaAtiva' => 'Vendas',
            'linhas' => $linhas,
            'totalVendas' => $totalVendas,
            'mesSelecionado' => $referencia->format('Y-m'),
            'tituloCompetencia' => mb_strtoupper($this->mesAbreviado($referencia->month) . ' ' . $referencia->year),
        ]);
    }

    private function preencherResumoPdv(array &$periodo, Carbon $inicio, Carbon $fim): void
    {
        VendaCaixa::query()
            ->with(['fatura:id,venda_caixa_id,forma_pagamento,valor'])
            ->select([
                'id',
                'empresa_id',
                'data_registro',
                'created_at',
                'valor_total',
                'forma_pagamento',
                'tipo_pagamento',
                'estado_emissao',
                'estado',
                'rascunho',
                'consignado',
                'deleted_at',
            ])
            ->where('empresa_id', $this->empresa_id)
            ->whereNull('deleted_at')
            ->where(function ($query) {
                $query->whereNull('rascunho')
                    ->orWhere('rascunho', 0);
            })
            ->where(function ($query) {
                $query->whereNull('consignado')
                    ->orWhere('consignado', 0);
            })
            ->where(function ($query) {
                $query->whereNull('estado_emissao')
                    ->orWhereRaw('LOWER(estado_emissao) <> ?', ['cancelado']);
            })
            ->where(function ($query) {
                $query->whereNull('estado')
                    ->orWhereRaw('LOWER(estado) <> ?', ['cancelado']);
            })
            ->whereBetween(DB::raw('COALESCE(data_registro, created_at)'), [
                $inicio->format('Y-m-d H:i:s'),
                $fim->format('Y-m-d H:i:s'),
            ])
            ->orderBy('id')
            ->chunk(500, function ($vendas) use (&$periodo) {
                foreach ($vendas as $venda) {
                    $dataMovimento = $this->resolverDataMovimento($venda);
                    if (!$dataMovimento) {
                        continue;
                    }

                    $chave = $dataMovimento->format('Y-m-d');
                    if (!isset($periodo[$chave])) {
                        continue;
                    }

                    $valorTotal = (float) $venda->valor_total;
                    if ($dataMovimento->hour < 12) {
                        $periodo[$chave]['v_manha'] += $valorTotal;
                    } else {
                        $periodo[$chave]['v_tarde'] += $valorTotal;
                    }

                    $periodo[$chave]['venda_dia'] += $valorTotal;
                    $periodo[$chave]['v_cartao'] += $this->extrairValorCartao($venda, $valorTotal);
                }
            });
    }

    private function preencherSaidasCaixa(array &$periodo, Carbon $inicio, Carbon $fim): void
    {
        SangriaCaixa::query()
            ->select(['id', 'empresa_id', 'data_registro', 'created_at', 'valor'])
            ->where('empresa_id', $this->empresa_id)
            ->whereBetween(DB::raw('COALESCE(data_registro, created_at)'), [
                $inicio->format('Y-m-d H:i:s'),
                $fim->format('Y-m-d H:i:s'),
            ])
            ->orderBy('id')
            ->chunk(500, function ($sangrias) use (&$periodo) {
                foreach ($sangrias as $sangria) {
                    $dataMovimento = $this->resolverDataMovimento($sangria);
                    if (!$dataMovimento) {
                        continue;
                    }

                    $chave = $dataMovimento->format('Y-m-d');
                    if (!isset($periodo[$chave])) {
                        continue;
                    }

                    $periodo[$chave]['saidas_cx'] += (float) $sangria->valor;
                }
            });
    }

    private function extrairValorCartao(VendaCaixa $venda, float $valorTotal): float
    {
        if ($venda->relationLoaded('fatura') && $venda->fatura && $venda->fatura->count() > 0) {
            $totalCartao = 0.0;

            foreach ($venda->fatura as $fatura) {
                if ($this->pagamentoEhCartao($fatura->forma_pagamento)) {
                    $totalCartao += (float) $fatura->valor;
                }
            }

            if ($totalCartao > 0) {
                return $totalCartao;
            }
        }

        return $this->pagamentoEhCartao($venda->tipo_pagamento, $venda->forma_pagamento)
            ? $valorTotal
            : 0.0;
    }

    private function pagamentoEhCartao($tipoPagamento = null, $formaPagamento = null): bool
    {
        $tipo = strtoupper(trim((string) $tipoPagamento));
        $forma = strtoupper(trim((string) $formaPagamento));

        if (in_array($tipo, ['03', '04'], true)) {
            return true;
        }

        if (in_array($forma, ['03', '04'], true)) {
            return true;
        }

        $texto = $tipo . ' ' . $forma;

        foreach (['CART', 'CREDITO', 'CRÉDITO', 'DEBITO', 'DÉBITO'] as $agulha) {
            if ($texto !== '' && str_contains($texto, $agulha)) {
                return true;
            }
        }

        return false;
    }

    private function resolverDataMovimento($registro): ?Carbon
    {
        $valor = $registro->data_registro ?? $registro->created_at ?? null;

        if (!$valor) {
            return null;
        }

        try {
            return Carbon::parse($valor);
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function resolverEmpresaId(Request $request, $userLogged): ?int
    {
        $empresaId = $request->get('empresa_id')
            ?? $request->route('empresa_id')
            ?? data_get($userLogged, 'empresa');

        return $empresaId !== null ? (int) $empresaId : null;
    }

    private function diaSemanaAbreviado(Carbon $data): string
    {
        $dias = [
            0 => 'DOM',
            1 => 'SEG',
            2 => 'TER',
            3 => 'QUA',
            4 => 'QUI',
            5 => 'SEX',
            6 => 'SAB',
        ];

        return $dias[$data->dayOfWeek] ?? '';
    }

    private function mesAbreviado(int $mes): string
    {
        $meses = [
            1 => 'JAN',
            2 => 'FEV',
            3 => 'MAR',
            4 => 'ABR',
            5 => 'MAI',
            6 => 'JUN',
            7 => 'JUL',
            8 => 'AGO',
            9 => 'SET',
            10 => 'OUT',
            11 => 'NOV',
            12 => 'DEZ',
        ];

        return $meses[$mes] ?? '';
    }
}
