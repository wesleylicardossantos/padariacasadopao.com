<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Funcionario;

class FuncionarioController extends Controller
{

    public function exportPdf(Request $request)
    {
        $query = Funcionario::query()->orderBy('nome','asc');

        if ($request->filled('status')) {
            if ($request->status === 'ativos') {
                $query->where('ativo',1);
            } elseif ($request->status === 'inativos') {
                $query->where('ativo',0);
            }
        }

        $funcionarios = $query->get();

        return view('funcionarios.relatorio_pdf', compact('funcionarios'));
    }

    public function exportExcel(Request $request)
    {
        $query = Funcionario::query()->orderBy('nome','asc');

        if ($request->filled('status')) {
            if ($request->status === 'ativos') {
                $query->where('ativo',1);
            } elseif ($request->status === 'inativos') {
                $query->where('ativo',0);
            }
        }

        $funcionarios = $query->get();

        $csv = "ID;CPF;Nome;Status\n";

        foreach ($funcionarios as $i => $f) {
            $csv .= ($i+1).';'.$f->cpf.';'.$f->nome.';'.($f->ativo ? 'Ativo':'Inativo')."\n";
        }

        return response($csv)
            ->header('Content-Type','text/csv; charset=UTF-8')
            ->header('Content-Disposition','attachment; filename="funcionarios.csv"');
    }

}
