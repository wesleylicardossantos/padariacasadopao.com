<?php
namespace App\Http\Controllers;

use App\Models\ContaPagar;
use App\Models\ContaReceber;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class RHIAHistoricoController extends Controller
{
    public function index(Request $request)
    {
        $empresaId = request()->empresa_id;
        $dados = [];

        for ($i = 5; $i >= 0; $i--) {
            $mes = date('m', strtotime("-$i month"));
            $ano = date('Y', strtotime("-$i month"));

            $inicio = "$ano-$mes-01";
            $fim = date('Y-m-t', strtotime($inicio));

            $receita = Schema::hasTable('conta_recebers') ?
                ContaReceber::where('empresa_id',$empresaId)->whereBetween('data_vencimento',[$inicio,$fim])->sum('valor_integral') : 0;

            $despesas = Schema::hasTable('conta_pagars') ?
                ContaPagar::where('empresa_id',$empresaId)->whereBetween('data_vencimento',[$inicio,$fim])->sum('valor_integral') : 0;

            $resultado = $receita - $despesas;

            $dados[] = compact('mes','ano','receita','despesas','resultado');
        }

        $tendencia = "estavel";
        if (count($dados) >= 2) {
            $ultimo = end($dados)['resultado'];
            $anterior = prev($dados)['resultado'];

            if ($ultimo > $anterior) $tendencia = "crescimento";
            if ($ultimo < $anterior) $tendencia = "queda";
        }

        return view('rh.ia_historico.index', compact('dados','tendencia'));
    }
}
