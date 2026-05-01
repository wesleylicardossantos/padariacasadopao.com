<?php

namespace App\Http\Controllers;

use App\Models\ContaPagar;
use App\Models\ContaReceber;
use App\Models\CreditoVenda;
use App\Models\OrdemServico;
use App\Models\SangriaCaixa;
use App\Models\SuprimentoCaixa;
use App\Models\Venda;
use App\Models\VendaCaixa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FluxoCaixaController extends Controller
{
    protected $empresa_id = null;
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $this->empresa_id = $request->empresa_id;
            $value = session('user_logged');
            if (!$value) {
                return redirect("/login");
            }
            return $next($request);
        });
    }

    public function index(Request $request)
    {
        $start_date = $request->get('start_date');
        $end_date = $request->get('end_date');

        $start_date = $this->normalizeDate($start_date);
        $end_date = $this->normalizeDate($end_date);

        if ($start_date && $end_date) {
            $datas = $this->returnPesquisa($start_date, $end_date);
        } else {
            $datas = $this->returnDateMesAtual();
        }

        $fluxo = $this->criarArrayDeDatas($datas['start'], $datas['end']);

        return view('fluxo_caixa.index', compact('datas', 'fluxo'));
    }

    private function returnDateMesAtual()
    {
        $hoje = date('Y-m-d');
        $primeiroDia = substr($hoje, 0, 7) . "-01";
        return ['start' => $primeiroDia, 'end' => $hoje];
    }

    private function normalizeDate($date)
    {
        if (!$date) return $date;

        // Aceita YYYY-MM-DD
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            return $date;
        }

        // Aceita DD/MM/YYYY
        if (preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $date)) {
            $parts = explode('/', $date);
            return $parts[2] . '-' . $parts[1] . '-' . $parts[0];
        }

        // fallback
        $ts = strtotime($date);
        return $ts ? date('Y-m-d', $ts) : $date;
    }

    private function returnPesquisa($start_date, $end_date)
    {
        return ['start' => $this->normalizeDate($start_date), 'end' => $this->normalizeDate($end_date)];
    }

    private function criarArrayDeDatas($inicio, $fim)
    {
        $diferenca = strtotime($fim) - strtotime($inicio);
        $dias = floor($diferenca / (60 * 60 * 24));
        $global = [];
        $dataAtual = $inicio;
        for ($aux = 0; $aux < $dias + 1; $aux++) {
            $contaReceber = $this->getContasReceber($dataAtual);
            $contaPagar = $this->getContasPagar($dataAtual);
            $credito = $this->getCreditoVenda($dataAtual);
            $venda = $this->getVendas($dataAtual);
            $vendaCaixa = $this->getVendaCaixa($dataAtual);
            $os = $this->getOs($dataAtual);
            $suprimento = $this->getSuprimentos($dataAtual);
            $sangria = $this->getSangrias($dataAtual);
            $tst = [
                'data' => $this->parseViewData($dataAtual),
                'data_raw' => $dataAtual,
                'conta_receber' => $contaReceber,
                'conta_pagar' => $contaPagar,
                'credito_venda' => $credito->valor ?? 0,
                'venda' => $venda->valor ?? 0,
                'venda_caixa' => $vendaCaixa->valor ?? 0,
                'os' => $os->valor ?? 0,
                'suprimento' => $suprimento,
                'sangria' => $sangria,
            ];
            array_push($global, $tst);
            $temp = [];
            $dataAtual = date('Y-m-d', strtotime($dataAtual . '+1day'));
        }
        return $global;
    }

    private function getContasReceber($data)
    {
        $valor = 0;
        $contas = ContaReceber::selectRaw('data_recebimento as data, sum(valor_recebido) as valor')
            // ->where('updated_at', $data)
            ->whereBetween('data_recebimento', [
                $data . " 00:00:00",
                $data . " 23:59:00"
            ])
            ->where('status', 1)
            ->where('empresa_id', $this->empresa_id)
            // ->groupBy('updated_at')
            ->first();
        $valor += $contas->valor ?? 0;
        return $valor;
    }

    private function getContasPagar($data)
    {
        $contas = ContaPagar::selectRaw('data_pagamento as data, sum(valor_pago) as valor')
            // ->where('updated_at', $data)
            ->whereBetween('data_pagamento', [
                $data . " 00:00:00",
                $data . " 23:59:00"
            ])
            ->where('empresa_id', $this->empresa_id)
            ->where('status', 1)
            ->first();
        return $contas->valor ?? 0;
    }

    private function getCreditoVenda($data)
    {
        $creditos = CreditoVenda::selectRaw('DATE_FORMAT(vendas.data_registro, "%Y-%m-%d") as data, sum(vendas.valor_total) as valor')
            ->join('vendas', 'vendas.id', '=', 'credito_vendas.venda_id')
            ->whereRaw("DATE_FORMAT(credito_vendas.updated_at, '%Y-%m-%d') = '$data'")
            ->where('credito_vendas.status', true)
            ->where('vendas.empresa_id', $this->empresa_id)
            ->groupBy('data')
            ->first();
        return $creditos;
    }

    private function getVendas($data)
    {
        $venda = Venda::selectRaw('DATE_FORMAT(data_registro, "%Y-%m-%d") as data, sum(valor_total) as valor')
            ->whereRaw("DATE_FORMAT(data_registro, '%Y-%m-%d') = '$data' ")
            ->where('empresa_id', $this->empresa_id)
            ->groupBy('data')
            ->first();
        return $venda;
    }

    private function getVendaCaixa($data)
    {
        $venda = VendaCaixa::selectRaw('DATE_FORMAT(data_registro, "%Y-%m-%d") as data, sum(valor_total) as valor')
            ->whereRaw("DATE_FORMAT(data_registro, '%Y-%m-%d') = '$data'")
            ->where('empresa_id', $this->empresa_id)
            ->groupBy('data')
            ->first();
        return $venda;
    }

    private function getOs($data)
    {
        $os = OrdemServico::selectRaw('DATE_FORMAT(updated_at, "%Y-%m-%d") as data, sum(valor) as valor')
            ->whereRaw("DATE_FORMAT(updated_at, '%Y-%m-%d') = '$data'")
            ->where('estado', 'ap')
            ->where('empresa_id', $this->empresa_id)
            ->groupBy('data')
            ->first();
        return $os;
    }

    private function getSuprimentos($data)
    {
        return (float) (SuprimentoCaixa::whereBetween('created_at', [
            $data . ' 00:00:00',
            $data . ' 23:59:59'
        ])
            ->where('empresa_id', $this->empresa_id)
            ->sum('valor'));
    }

    private function getSangrias($data)
    {
        return (float) (SangriaCaixa::whereBetween('created_at', [
            $data . ' 00:00:00',
            $data . ' 23:59:59'
        ])
            ->where('empresa_id', $this->empresa_id)
            ->sum('valor'));
    }

    public function detalharDia(Request $request, $date)
    {
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            return redirect()->route('fluxoCaixa.index')->with('flash_erro', 'Data inválida.');
        }

        $inicio = $date . ' 00:00:00';
        $fim = $date . ' 23:59:59';

        $vendas = Venda::whereBetween('data_registro', [$inicio, $fim])
            ->where('empresa_id', $this->empresa_id)
            ->orderBy('id', 'desc')
            ->get();

        $vendasCaixa = VendaCaixa::whereBetween('data_registro', [$inicio, $fim])
            ->where('empresa_id', $this->empresa_id)
            ->orderBy('id', 'desc')
            ->get();

        $contasReceber = ContaReceber::whereBetween('data_recebimento', [$inicio, $fim])
            ->where('status', 1)
            ->where('empresa_id', $this->empresa_id)
            ->orderBy('id', 'desc')
            ->get();

        $contasPagar = ContaPagar::whereBetween('data_pagamento', [$inicio, $fim])
            ->where('status', 1)
            ->where('empresa_id', $this->empresa_id)
            ->orderBy('id', 'desc')
            ->get();

        $ordensServico = OrdemServico::whereBetween('updated_at', [$inicio, $fim])
            ->where('estado', 'ap')
            ->where('empresa_id', $this->empresa_id)
            ->orderBy('id', 'desc')
            ->get();

        $suprimentos = SuprimentoCaixa::whereBetween('created_at', [$inicio, $fim])
            ->where('empresa_id', $this->empresa_id)
            ->orderBy('id', 'desc')
            ->get();

        $sangrias = SangriaCaixa::whereBetween('created_at', [$inicio, $fim])
            ->where('empresa_id', $this->empresa_id)
            ->orderBy('id', 'desc')
            ->get();

        $dataView = $this->parseViewData($date);

        $viewData = compact(
            'date',
            'dataView',
            'vendas',
            'vendasCaixa',
            'contasReceber',
            'contasPagar',
            'ordensServico',
            'suprimentos',
            'sangrias'
        );

        if ($request->ajax()) {
            return view('fluxo_caixa._detalhar_content', $viewData);
        }

        return view('fluxo_caixa.detalhar', $viewData);
    }

    public function excluirForm(Request $request, $date)
    {
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            return redirect()->route('fluxoCaixa.index')->with('flash_erro', 'Data inválida.');
        }

        $inicio = $date . ' 00:00:00';
        $fim = $date . ' 23:59:59';

        $vendas = Venda::whereBetween('data_registro', [$inicio, $fim])
            ->where('empresa_id', $this->empresa_id)
            ->orderBy('id', 'desc')
            ->get();

        $vendasCaixa = VendaCaixa::whereBetween('data_registro', [$inicio, $fim])
            ->where('empresa_id', $this->empresa_id)
            ->orderBy('id', 'desc')
            ->get();

        $contasReceber = ContaReceber::whereBetween('data_recebimento', [$inicio, $fim])
            ->where('status', 1)
            ->where('empresa_id', $this->empresa_id)
            ->orderBy('id', 'desc')
            ->get();

        $contasPagar = ContaPagar::whereBetween('data_pagamento', [$inicio, $fim])
            ->where('status', 1)
            ->where('empresa_id', $this->empresa_id)
            ->orderBy('id', 'desc')
            ->get();

        $creditos = CreditoVenda::whereBetween('updated_at', [$inicio, $fim])
            ->where('status', true)
            ->orderBy('id', 'desc')
            ->get();

        $suprimentos = SuprimentoCaixa::whereBetween('created_at', [$inicio, $fim])
            ->where('empresa_id', $this->empresa_id)
            ->orderBy('id', 'desc')
            ->get();

        $sangrias = SangriaCaixa::whereBetween('created_at', [$inicio, $fim])
            ->where('empresa_id', $this->empresa_id)
            ->orderBy('id', 'desc')
            ->get();

        $ordensServico = OrdemServico::whereBetween('updated_at', [$inicio, $fim])
            ->where('estado', 'ap')
            ->where('empresa_id', $this->empresa_id)
            ->orderBy('id', 'desc')
            ->get();

        $dataView = $this->parseViewData($date);

        return view('fluxo_caixa.excluir', compact(
            'date',
            'dataView',
            'vendas',
            'vendasCaixa',
            'contasReceber',
            'contasPagar',
            'creditos',
            'suprimentos',
            'sangrias',
            'ordensServico'
        ));
    }

    public function excluirSubmit(Request $request, $date)
    {
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            return redirect()->route('fluxoCaixa.index')->with('flash_erro', 'Data inválida.');
        }

        $idsVendas = $request->input('vendas', []);
        $idsVendasCaixa = $request->input('vendas_caixa', []);
        $idsContasReceber = $request->input('contas_receber', []);
        $idsContasPagar = $request->input('contas_pagar', []);
        $idsCreditos = $request->input('creditos', []);
        $idsSuprimentos = $request->input('suprimentos', []);
        $idsSangrias = $request->input('sangrias', []);
        $idsOs = $request->input('ordens_servico', []);

        if (
            empty($idsVendas) && empty($idsVendasCaixa) && empty($idsContasReceber) &&
            empty($idsContasPagar) && empty($idsCreditos) && empty($idsSuprimentos) &&
            empty($idsSangrias) && empty($idsOs)
        ) {
            return redirect()->back()->with('flash_alerta', 'Nenhum item selecionado para exclusão.');
        }

        DB::beginTransaction();
        try {
            $deleted = [
                'vendas' => 0,
                'vendas_caixa' => 0,
                'contas_receber' => 0,
                'contas_pagar' => 0,
                'creditos_venda' => 0,
                'suprimentos' => 0,
                'sangrias' => 0,
                'ordens_servico' => 0,
            ];

            if (!empty($idsVendas)) {
                $deleted['vendas'] = Venda::whereIn('id', $idsVendas)
                    ->where('empresa_id', $this->empresa_id)
                    ->delete();
            }

            if (!empty($idsVendasCaixa)) {
                $deleted['vendas_caixa'] = VendaCaixa::whereIn('id', $idsVendasCaixa)
                    ->where('empresa_id', $this->empresa_id)
                    ->delete();
            }

            if (!empty($idsContasReceber)) {
                $deleted['contas_receber'] = ContaReceber::whereIn('id', $idsContasReceber)
                    ->where('empresa_id', $this->empresa_id)
                    ->delete();
            }

            if (!empty($idsContasPagar)) {
                $deleted['contas_pagar'] = ContaPagar::whereIn('id', $idsContasPagar)
                    ->where('empresa_id', $this->empresa_id)
                    ->delete();
            }

            if (!empty($idsCreditos)) {
                $deleted['creditos_venda'] = CreditoVenda::whereIn('id', $idsCreditos)->delete();
            }

            if (!empty($idsSuprimentos)) {
                $deleted['suprimentos'] = SuprimentoCaixa::whereIn('id', $idsSuprimentos)
                    ->where('empresa_id', $this->empresa_id)
                    ->delete();
            }

            if (!empty($idsSangrias)) {
                $deleted['sangrias'] = SangriaCaixa::whereIn('id', $idsSangrias)
                    ->where('empresa_id', $this->empresa_id)
                    ->delete();
            }

            if (!empty($idsOs)) {
                $deleted['ordens_servico'] = OrdemServico::whereIn('id', $idsOs)
                    ->where('empresa_id', $this->empresa_id)
                    ->delete();
            }

            DB::commit();

            $msgParts = [];
            foreach ($deleted as $k => $v) {
                if ($v > 0) $msgParts[] = "$k: $v";
            }
            $msg = 'Exclusão concluída.' . (count($msgParts) ? (' Itens removidos - ' . implode(', ', $msgParts)) : '');

            return redirect()->route('fluxoCaixa.index')->with('flash_sucesso', $msg);
        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()->back()->with('flash_erro', 'Falha ao excluir: ' . $e->getMessage());
        }
    }


    private function parseViewData($date)
    {
        return date('d/m/Y', strtotime(str_replace("/", "-", $date)));
    }
}
