<?php

namespace App\Http\Controllers;

use App\Models\AberturaCaixa;
use App\Models\ConfigNota;
use App\Models\SangriaCaixa;
use App\Models\SuprimentoCaixa;
use App\Models\Usuario;
use App\Models\Venda;
use App\Models\VendaCaixa;
use Illuminate\Http\Request;
use Dompdf\Dompdf;
use Illuminate\Database\Eloquent\Builder;
use NFePHP\DA\NFe\ComprovanteFechamentoCaixa;

class AberturaCaixaController extends Controller
{
	protected $empresa_id = null;
	private $configNota = null;
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


	private function getConfigNota($empresaId = null)
	{
		$empresaId = $empresaId ?? $this->empresa_id;
		if ($empresaId === null) {
			return null;
		}

		if ($this->configNota && (int) $this->configNota->empresa_id === (int) $empresaId) {
			return $this->configNota;
		}

		return $this->configNota = ConfigNota::where('empresa_id', $empresaId)->first();
	}

	private function aberturaQuery(string $campoUltimaVenda, ?int $usuario = null): Builder
	{
		$config = $this->getConfigNota();
		$usuario = $usuario ?: get_id_user();

		return AberturaCaixa::where($campoUltimaVenda, 0)
			->where('empresa_id', $this->empresa_id)
			->where('status', 0)
			->when($config && $config->caixa_por_usuario == 1, function ($q) use ($usuario) {
				return $q->where('usuario_id', $usuario);
			});
	}

	private function vendaQueryBase(int $empresaId, ?int $usuario = null): Builder
	{
		$config = $this->getConfigNota($empresaId);

		return Venda::with(['duplicatas:id,venda_id,tipo_pagamento,valor_integral'])
			->where('empresa_id', $empresaId)
			->when($config && $config->caixa_por_usuario == 1 && $usuario, function ($q) use ($usuario) {
				return $q->where('usuario_id', $usuario);
			});
	}

	private function vendaCaixaQueryBase(int $empresaId, ?int $usuario = null): Builder
	{
		$config = $this->getConfigNota($empresaId);

		return VendaCaixa::with(['fatura:id,venda_caixa_id,forma_pagamento,valor'])
			->where('empresa_id', $empresaId)
			->when($config && $config->caixa_por_usuario == 1 && $usuario, function ($q) use ($usuario) {
				return $q->where('usuario_id', $usuario);
			});
	}

	private function carregarVendasPorAbertura($abertura, int $empresaId, ?int $usuario = null)
	{
		$vendasPdv = $this->vendaCaixaQueryBase($empresaId, $usuario)
			->whereBetween('id', [$abertura->primeira_venda_nfce + 1, $abertura->ultima_venda_nfce])
			->get();

		$vendas = $this->vendaQueryBase($empresaId, $usuario)
			->whereBetween('id', [$abertura->primeira_venda_nfe + 1, $abertura->ultima_venda_nfe])
			->get();

		return $this->agrupaVendas($vendas, $vendasPdv);
	}

	public function store(Request $request)
	{
		$ultimaVendaNfce = VendaCaixa::where('empresa_id', $request->empresa_id)
		->orderBy('id', 'desc')->first(['id']);
		$ultimaVendaNfe = Venda::where('empresa_id', $request->empresa_id)
		->orderBy('id', 'desc')->first(['id']);
		$verify = $this->verificaAberturaCaixa();

		if ($verify == -1) {
			try {
				$request->merge([
					'filial_id' => $request->filial_id == -1 ? null : $request->filial_id
				]);
				AberturaCaixa::create([
					'usuario_id' => get_id_user(),
					'valor' => __convert_value_bd($request->valor),
					'empresa_id' => $request->empresa_id,
					'primeira_venda_nfe' => $ultimaVendaNfe != null ?
					$ultimaVendaNfe->id : 0,
					'primeira_venda_nfce' => $ultimaVendaNfce != null ?
					$ultimaVendaNfce->id : 0,
					'status' => 0,
					'filial_id' => $request->filial_id,
				]);
				session()->flash("flash_sucesso", "Caixa aberto com Sucesso!");
			} catch (\Exception $e) {
				session()->flash("flash_erro", " Erro:" . $e->getMessage());
				__saveLogError($e, request()->empresa_id);
			}
			return redirect()->back();
		}
	}

	public function verificaHoje()
	{
		echo json_encode($this->verificaAberturaCaixa());
	}

	public function diaria()
	{
		date_default_timezone_set('America/Sao_Paulo');
		$hoje = date("Y-m-d") . " 00:00:00";
		$amanha = date('Y-m-d', strtotime('+1 days')) . " 00:00:00";
		$abertura = AberturaCaixa::whereBetween('data_registro', [
			$hoje,
			$amanha
		])
		->where('empresa_id', $this->empresa_id)
		->first();
		echo json_encode($abertura);
	}

	private function verificaAberturaCaixa()
	{
		$ab = $this->aberturaQuery('ultima_venda_nfce')->orderBy('id', 'desc')->first();
		$ab2 = $this->aberturaQuery('ultima_venda_nfe')->orderBy('id', 'desc')->first();
		if ($ab != null && $ab2 == null) {
			return $ab->valor;
		} else if ($ab == null && $ab2 != null) {
			$ab2->valor;
		} else if ($ab != null && $ab2 != null) {
			if (strtotime($ab->created_at) > strtotime($ab2->created_at)) {
				$ab->valor;
			} else {
				$ab2->valor;
			}
		} else {
			return -1;
		}
		if ($ab != null) return $ab->valor;
		else return -1;
	}

	public function index()
	{
		$config = $this->getConfigNota();
		if ($config == null) {
			session()->flash('flash_erro', 'Configure o emitente');
			return redirect()->route('configNF.index');
		}
		$abertura = $this->verificaAberturaCaixa();
		// dd($abertura);
		$user = Usuario::findOrFail(get_id_user());
		$somaTiposPagamento = [];
		$vendas = [];
		$caixa = [];
		if ($abertura != -1) {
			$caixa = $this->getCaixaAberto();
		}
		// if ($abertura == -1){
		// 	session()->flash('flash_warning', 'Abrir um caixa');
		// 	return redirect()->back();
		// }
		$ab = $this->aberturaQuery('ultima_venda_nfce')
			->where('ultima_venda_nfe', 0)
			->orderBy('id', 'desc')->first();
		$abertura = $ab;
		$usuarios = [];
		$user = Usuario::findOrFail(get_id_user());
		if ($user->adm) {
			$usuarios = Usuario::where('empresa_id', $this->empresa_id)
			->get();
		}
		$usuario_id = $user;
		return view('caixa.index', compact(
			'vendas',
			'usuario_id',
			'usuarios',
			'config',
			'abertura',
			'caixa'
		));
	}

	private function getCaixaAberto($usuario = 0)
	{
		$config = $this->getConfigNota();
		if ($usuario == 0) {
			$usuario = get_id_user();
		}
		$aberturaNfe = $this->aberturaQuery('ultima_venda_nfe', $usuario)->orderBy('id', 'desc')->first();
		$aberturaNfce = $this->aberturaQuery('ultima_venda_nfce', $usuario)->orderBy('id', 'desc')->first();
		$ultimaVendaCaixa = VendaCaixa::where('empresa_id', $this->empresa_id)
		->when($config && $config->caixa_por_usuario == 1, function ($q) use ($usuario) {
			return $q->where('usuario_id', $usuario);
		})
		->orderBy('id', 'desc')->first(['id']);
		$ultimaVenda = Venda::where('empresa_id', $this->empresa_id)
		->when($config && $config->caixa_por_usuario == 1, function ($q) use ($usuario) {
			return $q->where('usuario_id', $usuario);
		})
		->orderBy('id', 'desc')->first(['id']);
		$vendas = [];
		$somaTiposPagamento = [];
		if ($ultimaVendaCaixa != null || $ultimaVenda != null) {
			$ultimaVendaCaixa = $ultimaVendaCaixa != null ? $ultimaVendaCaixa->id : 0;
			$ultimaVenda = $ultimaVenda != null ? $ultimaVenda->id : 0;
			$vendasPdv = $this->vendaCaixaQueryBase($this->empresa_id, $usuario)
				->whereBetween('id', [
					($aberturaNfce != null ? $aberturaNfce->primeira_venda_nfce + 1 : 0),
					$ultimaVendaCaixa
				])
				->get();
			$vendas = $this->vendaQueryBase($this->empresa_id, $usuario)
				->whereBetween('id', [
					($aberturaNfe != null ? $aberturaNfe->primeira_venda_nfe + 1 : 0),
					$ultimaVenda
				])
				->get();
			$vendas = $this->agrupaVendas($vendas, $vendasPdv);
			$somaTiposPagamento = $this->somaTiposPagamento($vendas);
		}
		$suprimentos = [];
		$sangrias = [];
		if ($aberturaNfe != null) {
			$suprimentos = SuprimentoCaixa::whereBetween('created_at', [
				$aberturaNfe->created_at,
				date('Y-m-d H:i:s')
			])
			->where('empresa_id', $this->empresa_id)
			->when($config->caixa_por_usuario == 1, function ($q) use ($config, $usuario) {
				return $q->where('usuario_id', $usuario);
			})
			->get();
			$sangrias = SangriaCaixa::whereBetween('created_at', [
				$aberturaNfe->created_at,
				date('Y-m-d H:i:s')
			])
			->where('empresa_id', $this->empresa_id)
			->when($config->caixa_por_usuario == 1, function ($q) use ($config, $usuario) {
				return $q->where('usuario_id', $usuario);
			})
			->get();
		}
		return [
			'vendas' => $vendas,
			'sangrias' => $sangrias,
			'suprimentos' => $suprimentos,
			'somaTiposPagamento' => $somaTiposPagamento
		];
	}

	public function list(Request $request)
	{
		$start_date = $request->start_date;
		$end_date = $request->end_date;
		$data = AberturaCaixa::where('empresa_id', $this->empresa_id)
		->when(!empty($start_date), function ($query) use ($start_date) {
			return $query->whereDate('created_at', '>=', $start_date);
		})
		->when(!empty($end_date), function ($query) use ($end_date) {
			return $query->whereDate('created_at', '<=', $end_date);
		})
		->orderBy('created_at', 'desc')
		->paginate(env("PAGINACAO"));
		return view('caixa.list', compact('data'));
	}

	public function destroy($id)
	{
		$item = AberturaCaixa::findOrFail($id);
		if (!__valida_objeto($item)) {
			abort(403);
		}

		if (!$item->status) {
			session()->flash('flash_erro', 'Não é possível excluir um caixa ainda aberto!');
			return redirect()->route('caixa.list');
		}

		try {
			$item->delete();
			session()->flash('flash_sucesso', 'Operação de caixa excluída com sucesso!');
		} catch (\Exception $e) {
			session()->flash('flash_erro', 'Algo deu errado: ' . $e->getMessage());
			__saveLogError($e, request()->empresa_id);
		}

		return redirect()->route('caixa.list');
	}

	public function detalhes(Request $request, $id)
	{
		$config = $this->getConfigNota($request->empresa_id);
		$abertura = AberturaCaixa::findOrFail($id);

		if ($abertura) {

			$aberturaAnterior = AberturaCaixa::find($id - 1);

			$fim = $abertura->updated_at;
			$inicio = $abertura->created_at;

			$vendas = $this->carregarVendasPorAbertura($abertura, $request->empresa_id);
			$somaTiposPagamento = $this->somaTiposPagamento($vendas);
			$suprimentos = SuprimentoCaixa::whereBetween('created_at', [
				$inicio,
				$fim
			])
			->where('empresa_id', $request->empresa_id)
			->get();
			
			$sangrias = SangriaCaixa::whereBetween('created_at', [
				$inicio,
				$fim
			])
			->where('empresa_id', $request->empresa_id)
			->get();

			return view('caixa.detalhes', compact(
				'abertura',
				'vendas',
				'suprimentos',
				'sangrias',
				'somaTiposPagamento',
			));
		}
	}

	private function agrupaVendas($vendas, $vendasPdv)
	{
		$temp = [];
		foreach ($vendas as $v) {
			$v->tipo = 'VENDA';
			array_push($temp, $v);
		}
		foreach ($vendasPdv as $v) {
			$v->tipo = 'PDV';
			array_push($temp, $v);
		}
		return $temp;
	}

	private function somaTiposPagamento($vendas)
	{
		$tipos = $this->preparaTipos();
		foreach ($vendas as $v) {
			if ($v->estado_emissao != 'CANCELADO') {

				if (isset($tipos[$v->tipo_pagamento])) {

					if ($v->tipo_pagamento != 99) {
						if (isset($v->NFcNumero)) {
							if (!$v->rascunho && !$v->consignado) {
								$tipos[$v->tipo_pagamento] += $v->valor_total;
							}
						} else {
							if ($v->duplicatas && sizeof($v->duplicatas) > 0) {
								foreach ($v->duplicatas as $d) {
									$tipos[Venda::getTipoPagamentoNFe($d->tipo_pagamento)] += $d->valor_integral;
								}
							} else {
								$tipos[$v->tipo_pagamento] += $v->valor_total;
							}
						}
					} else {
						if ($v->fatura) {
							foreach ($v->fatura as $f) {
								$tipos[trim($f->forma_pagamento)] += $f->valor;
							}
						}
					}
				}
			}
		}
		return $tipos;
	}

	private function preparaTipos()
	{
		$temp = [];
		foreach (VendaCaixa::tiposPagamento() as $key => $tp) {
			$temp[$key] = 0;
		}
		return $temp;
	}

	public function imprimir($id)
	{
		$abertura = AberturaCaixa::findOrFail($id);
		$fim = $abertura->updated_at;
		$inicio = $abertura->created_at;

		$vendas = $this->carregarVendasPorAbertura($abertura, $this->empresa_id);
		$somaTiposPagamento = $this->somaTiposPagamento($vendas);
		$suprimentos = SuprimentoCaixa::whereBetween('created_at', [
			$inicio,
			$fim
		])
		->where('empresa_id', $this->empresa_id)
		->get();
		$sangrias = SangriaCaixa::whereBetween('created_at', [
			$inicio,
			$fim
		])
		->where('empresa_id', $this->empresa_id)
		->get();
		$usuario = Usuario::findOrFail(get_id_user());
		$config = $this->getConfigNota();
		$p = view('caixa.relatorio', compact(
			'abertura',
			'vendas',
			'suprimentos',
			'sangrias',
			'usuario',
			'config',
			'somaTiposPagamento'
		));
		$domPdf = new Dompdf(["enable_remote" => true]);
		$domPdf->loadHtml($p);
		$pdf = ob_get_clean();
		$domPdf->setPaper("A4");
		$domPdf->render();
		$domPdf->stream("Relatório caixa.pdf", array("Attachment" => false));
	}

	public function imprimir80($id)
	{
		$abertura = AberturaCaixa::find($id);
		$aberturas = AberturaCaixa::where('empresa_id', $this->empresa_id)
		->get();

		if (valida_objeto($abertura)) {

			$aberturaAnterior = AberturaCaixa::find($id - 1);

			$fim = $abertura->updated_at;
			$inicio = $aberturaAnterior == null ? '2016-01-01' : $aberturaAnterior->updated_at;

			$vendas = $this->carregarVendasPorAbertura($abertura, $this->empresa_id);
			$somaTiposPagamento = $this->somaTiposPagamento($vendas);

			$somaVendas = 0;

			foreach ($vendas as $v) {
				if ($v->estado != 'CANCELADO' && !$v->rascunho && !$v->consignado) {
					$total = $v->valor_total;
					if (!isset($v->cpf)) {
						$total = $total - $v->desconto + $v->acrescimo;
					}

					$somaVendas += $total;
				}
			}
			$suprimentos = SuprimentoCaixa::whereBetween('created_at', [
				$inicio,
				$fim
			])
			->where('empresa_id', $this->empresa_id)
			->get();

			$sangrias = SangriaCaixa::whereBetween('created_at', [
				$inicio,
				$fim	
			])
			->where('empresa_id', $this->empresa_id)
			->get();

			$config = $this->getConfigNota();
			$usuario = Usuario::find(get_id_user());

			$cupom = new ComprovanteFechamentoCaixa($vendas, '', $config, 80, $suprimentos, $sangrias, $somaTiposPagamento, $abertura, $usuario, $somaVendas);
			$cupom->monta();
			$pdf = $cupom->render();

			return response($pdf)
			->header('Content-Type', 'application/pdf');
		} else {
			return redirect('/403');
		}
	}
}
