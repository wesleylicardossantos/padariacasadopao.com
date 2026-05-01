<?php

namespace App\Http\Controllers;

use App\Models\CategoriaConta;
use App\Models\ContaPagar;
use App\Models\Funcionario;
use App\Models\RHFolhaFechamento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class RHFechamentoFolhaController extends Controller
{
    public function store(Request $request)
    {
        if (!Schema::hasTable('rh_folha_fechamentos')) {
            session()->flash('flash_erro', 'Tabela de fechamento da folha ainda não instalada. Execute o SQL do patch.');
            return redirect('/rh/folha');
        }

        $request->validate([
            'mes' => 'required|numeric|min:1|max:12',
            'ano' => 'required|numeric|min:2000',
        ]);

        $empresaId = request()->empresa_id;
        $mes = (int)$request->mes;
        $ano = (int)$request->ano;

        $jaExiste = RHFolhaFechamento::where('empresa_id', $empresaId)
            ->where('mes', $mes)
            ->where('ano', $ano)
            ->where('status', 'fechado')
            ->first();

        if ($jaExiste) {
            session()->flash('flash_erro', 'Esta folha já foi fechada para a competência informada.');
            return redirect('/rh/folha?mes='.$mes.'&ano='.$ano);
        }

        try {
            DB::transaction(function () use ($empresaId, $mes, $ano, $request) {
                $funcionarios = Funcionario::where('empresa_id', $empresaId)
                    ->where(function($q){
                        $q->whereNull('ativo')->orWhere('ativo', 1);
                    })
                    ->get();

                $salarioBaseTotal = 0;
                $eventosTotal = 0;
                $descontosTotal = 0;

                foreach ($funcionarios as $item) {
                    $salarioBase = (float)($item->salario ?? 0);
                    $eventos = 0;
                    $descontos = 0;

                    if (Schema::hasTable('apuracao_mensals')) {
                        $apuracao = \App\Models\ApuracaoMensal::where('funcionario_id', $item->id)
                            ->whereMonth('created_at', $mes)
                            ->whereYear('created_at', $ano)
                            ->first();

                        if ($apuracao) {
                            $eventos = (float)($apuracao->valor ?? 0);
                            $descontos = (float)($apuracao->desconto ?? 0);
                        }
                    }

                    $salarioBaseTotal += $salarioBase;
                    $eventosTotal += $eventos;
                    $descontosTotal += $descontos;
                }

                $liquidoTotal = ($salarioBaseTotal + $eventosTotal) - $descontosTotal;

                $categoria = CategoriaConta::firstOrCreate(
                    ['empresa_id' => $empresaId, 'nome' => 'Folha de Pagamento', 'tipo' => 'pagar'],
                    ['nome' => 'Folha de Pagamento', 'empresa_id' => $empresaId, 'tipo' => 'pagar']
                );

                $ultimoDia = date('Y-m-t', strtotime($ano.'-'.str_pad($mes, 2, '0', STR_PAD_LEFT).'-01'));

                $contaPagar = ContaPagar::create([
                    'compra_id' => null,
                    'data_vencimento' => $ultimoDia,
                    'data_pagamento' => $ultimoDia,
                    'valor_integral' => $liquidoTotal,
                    'valor_pago' => 0,
                    'referencia' => 'Fechamento Folha '.str_pad($mes, 2, '0', STR_PAD_LEFT).'/'.$ano,
                    'categoria_id' => $categoria->id,
                    'status' => 0,
                    'empresa_id' => $empresaId,
                    'fornecedor_id' => 0,
                    'tipo_pagamento' => 'Folha',
                    'filial_id' => $request->filial_id == -1 ? null : ($request->filial_id ?: null),
                ]);

                RHFolhaFechamento::create([
                    'empresa_id' => $empresaId,
                    'mes' => $mes,
                    'ano' => $ano,
                    'salario_base_total' => $salarioBaseTotal,
                    'eventos_total' => $eventosTotal,
                    'descontos_total' => $descontosTotal,
                    'liquido_total' => $liquidoTotal,
                    'conta_pagar_id' => $contaPagar->id,
                    'status' => 'fechado',
                    'observacao' => $request->observacao,
                    'usuario_id' => auth()->id() ?? null,
                ]);
            });

            session()->flash('flash_sucesso', 'Folha fechada com sucesso e conta a pagar gerada no financeiro!');
        } catch (\Exception $e) {
            session()->flash('flash_erro', 'Erro ao fechar folha: '.$e->getMessage());
            __saveLogError($e, request()->empresa_id);
        }

        return redirect('/rh/folha?mes='.$mes.'&ano='.$ano);
    }

    public function reabrir(Request $request)
    {
        if (!Schema::hasTable('rh_folha_fechamentos')) {
            session()->flash('flash_erro', 'Tabela de fechamento da folha ainda não instalada.');
            return redirect('/rh/folha');
        }

        $request->validate([
            'mes' => 'required|numeric|min:1|max:12',
            'ano' => 'required|numeric|min:2000',
        ]);

        $empresaId = request()->empresa_id;
        $mes = (int)$request->mes;
        $ano = (int)$request->ano;

        $fechamento = RHFolhaFechamento::where('empresa_id', $empresaId)
            ->where('mes', $mes)
            ->where('ano', $ano)
            ->where('status', 'fechado')
            ->first();

        if (!$fechamento) {
            session()->flash('flash_erro', 'Nenhum fechamento encontrado para essa competência.');
            return redirect('/rh/folha?mes='.$mes.'&ano='.$ano);
        }

        try {
            DB::transaction(function () use ($fechamento) {
                if (!empty($fechamento->conta_pagar_id) && Schema::hasTable('conta_pagars')) {
                    $conta = ContaPagar::find($fechamento->conta_pagar_id);
                    if ($conta && (float)$conta->valor_pago <= 0) {
                        $conta->delete();
                    }
                }

                $fechamento->status = 'reaberto';
                if (Schema::hasColumn('rh_folha_fechamentos', 'reaberto_por')) {
                    $fechamento->reaberto_por = auth()->id() ?? null;
                }
                $fechamento->save();
            });

            session()->flash('flash_sucesso', 'Folha reaberta com sucesso!');
        } catch (\Exception $e) {
            session()->flash('flash_erro', 'Erro ao reabrir folha: '.$e->getMessage());
            __saveLogError($e, request()->empresa_id);
        }

        return redirect('/rh/folha?mes='.$mes.'&ano='.$ano);
    }
}
