<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Venda;
use App\Models\VendaCaixa;
use App\Models\Produto;
use App\Models\ItemVendaCaixa;
use App\Models\ItemVenda;
use App\Models\ContaReceber;
use App\Models\ContaPagar;
use App\Services\DashboardService;

class GraficoController extends Controller
{
    protected DashboardService $dashboardService;

    public function __construct(DashboardService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
    }

    public function vendasAnual(Request $request)
    {
        $empresaId = $request->empresa_id;
        $localId = $request->get('local_id', $request->get('filial_id', 'todos'));
        $year = $request->get('ano');

        return response()->json(
            $this->dashboardService->getAnnualSalesSeries($empresaId, $localId, $year ? (int) $year : null),
            200
        );
    }

    private function criaMeses(){
        $mesAtual = date('m');
        $mesAtual = (int)$mesAtual;
        $meses = [];
        for($i=1; $i<=$mesAtual; $i++){
            array_push($meses, $i < 10 ? "0$i" : $i);
        }
        return $meses;
    }

    private function criaMesesTrimestre(){
        $mesAtual = date('m');
        $mesAtual = (int)$mesAtual;
        $meses = [];
        for($i=1; $i<=$mesAtual; $i++){
            array_push($meses, $i < 10 ? "0$i" : $i);
        }
        return $meses;
    }

    public function produtos(Request $request){
        $meseStr = ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'];
        $somaCadastradoMes = [];
        $somaVendidosNoDia = [];
        $somaNaoVendidos = [];
        $meses = [];
        
        $filial_id = $request->filial_id;

        foreach($this->criaMeses() as $m){
            $countProdutos = Produto::
            where('empresa_id', $request->empresa_id)
            ->where('locais', 'like', "%{$filial_id}%")
            ->whereMonth('created_at', $m)
            ->count();
            array_push($somaCadastradoMes, $countProdutos);

            $caixa = ItemVendaCaixa::
            select('item_venda_caixas.*')
            ->join('venda_caixas', 'venda_caixas.id', '=', 'item_venda_caixas.venda_caixa_id')
            ->where('venda_caixas.empresa_id', $request->empresa_id)
            ->whereMonth('item_venda_caixas.created_at', $m)
            ->groupBy('item_venda_caixas.produto_id')
            ->get();

            $pedido = ItemVenda::
            select('item_vendas.*')
            ->join('vendas', 'vendas.id', '=', 'item_vendas.venda_id')
            ->where('vendas.empresa_id', $request->empresa_id)
            ->whereMonth('item_vendas.created_at', $m)
            ->groupBy('item_vendas.produto_id')
            ->get();

            $somaVendidos = 0;
            $somaVendidos = $this->somaProdutosDistintos($caixa, $pedido);
            array_push($somaVendidosNoDia, $somaVendidos);

            $caixa = \DB::table('produtos AS t1')
            ->select('t1.*')
            ->leftJoin('item_venda_caixas AS t2','t2.produto_id','=','t1.id')
            ->where('t1.empresa_id', $request->empresa_id)
            ->whereNull('t2.produto_id')->get();

            $pedido = \DB::table('produtos AS t1')
            ->select('t1.*')
            ->leftJoin('item_vendas AS t2','t2.produto_id','=','t1.id')
            ->where('t1.empresa_id', $request->empresa_id)
            ->whereNull('t2.produto_id')->get();

            $semVendas = 0;
            $semVendas = $this->somaProdutosNaoVendidos($caixa, $pedido);
            array_push($somaNaoVendidos, $semVendas);

            array_push($meses, $meseStr[(int)$m-1]);
        }

        $retorno = [
            'meses' => $meses,
            'somaCadastradoMes' => $somaCadastradoMes,
            'somaVendidosNoDia' => $somaVendidosNoDia,
            'somaNaoVendidos' => $somaNaoVendidos,
        ];

        return response()->json($retorno, 200);
    }

    private function somaProdutosDistintos($caixa, $pedido){
        $cont = sizeof($caixa);
        $ids = $caixa->pluck('produto_id')->toArray();
        foreach($pedido as $p){
            if(!in_array($p->produto_id, $ids)){
                $cont++;
            }
        }
        return $cont;
    }

    private function somaProdutosNaoVendidos($caixa, $pedido){
        $cont = sizeof($caixa);
        $ids = $caixa->pluck('id')->toArray();
        foreach($pedido as $p){
            if(!in_array($p->id, $ids)){
                $cont++;
            }
        }
        return $cont;
    }

    public function contasReceber(Request $request)
    {
        return response()->json(
            $this->dashboardService->getContasReceberSummary(
                $request->empresa_id,
                $request->get('local_id', $request->get('filial_id', 'todos'))
            ),
            200
        );
    }

    public function contasPagar(Request $request)
    {
        return response()->json(
            $this->dashboardService->getContasPagarSummary(
                $request->empresa_id,
                $request->get('local_id', $request->get('filial_id', 'todos'))
            ),
            200
        );
    }


    public function dreResumo(Request $request)
    {
        return response()->json(
            $this->dashboardService->getDreSummary(
                $request->empresa_id,
                $request->get('local_id', $request->get('filial_id', 'todos')),
                $request->get('ano') ? (int) $request->get('ano') : null,
                $request->get('mes') ? (int) $request->get('mes') : null
            ),
            200
        );
    }

    public function biResumo(Request $request)
    {
        return response()->json(
            $this->dashboardService->getBiOverview(
                $request->empresa_id,
                $request->get('local_id', $request->get('filial_id', 'todos')),
                $request->get('ano') ? (int) $request->get('ano') : null,
                $request->get('mes') ? (int) $request->get('mes') : null
            ),
            200
        );
    }

    public function auditoriaPdv(Request $request)
    {
        return response()->json(
            $this->dashboardService->getPdvDivergenceAudit(
                $request->empresa_id,
                $request->get('local_id', $request->get('filial_id', 'todos')),
                $request->get('ano') ? (int) $request->get('ano') : null,
                $request->get('mes') ? (int) $request->get('mes') : null
            ),
            200
        );
    }

    public function boxConsulta(Request $request){
        $dias = $request->dias;
        $data = [
            'totalDeVendas' => $this->totalDeVendasDias($dias, $request->empresa_id),
            'totalDeContaReceber' => $this->totalDeContaReceberDias($dias, $request->empresa_id),
            'totalDeContaPagar' => $this->totalDeContaPagarDias($dias, $request->empresa_id)
        ];

        return response()->json($data, 200);
    }

    private function totalDeContaPagarDias($dias, $empresa_id){
        $contas = ContaPagar::
        select(\DB::raw('sum(valor_integral) as total'))
        ->whereBetween('data_vencimento', [
            date('Y-m-d', strtotime("-$dias days")), 
            date('Y-m-d')
        ])
        ->where('status', false)
        ->where('empresa_id', $empresa_id)
        ->first(); 

        return $contas->total ? number_format($contas->total, 2, ',', '.') : 0;
    }

    private function totalDeContaReceberDias($dias, $empresa_id){
        $contas = ContaReceber::
        select(\DB::raw('sum(valor_integral) as total'))
        ->whereBetween('data_vencimento', [
            date('Y-m-d', strtotime("-$dias days")), 
            date('Y-m-d')
        ])
        ->where('status', false)
        ->where('empresa_id', $empresa_id)
        ->first(); 

        return $contas->total ? number_format($contas->total, 2, ',', '.') : 0;
    }

    private function totalDeVendasDias($dias, $empresa_id){
        $vendas = Venda::
        select(\DB::raw('sum(valor_total) as total'))
        ->whereBetween('created_at', [
            date('Y-m-d', strtotime("-$dias days")), 
            date('Y-m-d')
        ])
        ->where('empresa_id', $empresa_id)
        ->first();

        $vendaCaixas = VendaCaixa::
        select(\DB::raw('sum(valor_total) as total'))
        ->whereBetween('created_at', [
            date('Y-m-d', strtotime("-$dias days")), 
            date('Y-m-d', strtotime('+1 day'))
        ])
        ->where('empresa_id', $empresa_id)
        ->first();

        return number_format(($vendas->total ?? 0) + ($vendaCaixas->total ?? 0), 2, ',', '.');

    }

    public function getDataCards(Request $request)
    {
        return response()->json(
            $this->dashboardService->getCardsSnapshot(
                $request->empresa_id,
                $request->get('local_id', $request->get('filial_id', 'todos'))
            ),
            200
        );
    }

    public function auditoriaFinanceira(Request $request)
    {
        return response()->json(
            $this->dashboardService->getFinancialAudit(
                $request->empresa_id,
                $request->get('local_id', $request->get('filial_id', 'todos'))
            ),
            200
        );
    }
}
