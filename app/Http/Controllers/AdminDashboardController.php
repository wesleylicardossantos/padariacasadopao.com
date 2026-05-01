<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminDashboardController extends Controller
{
    public function index()
    {
        $vendasHoje = DB::table('vendas')
            ->whereDate('created_at', date('Y-m-d'))
            ->count();

        $faturamentoMes = DB::table('vendas')
            ->whereMonth('created_at', date('m'))
            ->sum('valor_total');

        $contasPendentes = DB::table('conta_pagar')
            ->where('status', 'pendente')
            ->count();

        $caixasAbertos = DB::table('caixas')
            ->where('status', 'aberto')
            ->count();

        return view('admin.dashboard', compact(
            'vendasHoje',
            'faturamentoMes',
            'contasPendentes',
            'caixasAbertos'
        ));
    }
}
