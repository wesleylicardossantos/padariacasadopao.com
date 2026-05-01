<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Funcionario;

class FuncionarioController extends Controller
{

    // EXPORTAR PDF (view simples)
    public function exportPdf(Request $request)
    {
        $funcionarios = Funcionario::orderBy('nome','asc')->get();
        return view('funcionarios.relatorio_pdf', compact('funcionarios'));
    }

    // EXPORTAR EXCEL (CSV compatível com Excel)
    public function exportExcel(Request $request)
    {
        $funcionarios = Funcionario::orderBy('nome','asc')->get();

        $csv = "ID;CPF;Nome;Status\n";

        foreach($funcionarios as $i => $f){
            $csv .= ($i+1).";".$f->cpf.";".$f->nome.";".($f->ativo ? "Ativo":"Inativo")."\n";
        }

        return response($csv)
            ->header('Content-Type','text/csv; charset=UTF-8')
            ->header('Content-Disposition','attachment; filename="funcionarios.csv"');
    }

}
