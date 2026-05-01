<?php

namespace App\Http\Controllers;

use App\Models\Funcionario;
use Illuminate\Http\Request;
use App\Modules\RH\Support\RHContext;
use Illuminate\Support\Facades\DB;

class RHOcorrenciaController extends Controller
{
public function index(Request $request)
{
    $empresaId = RHContext::empresaId(request());
    $funcionarioId = $request->funcionario_id;
    $tipo = $request->tipo;
    $data = collect();
    $hasTable = DB::getSchemaBuilder()->hasTable('rh_ocorrencias');

    if ($hasTable) {
        $query = DB::table('rh_ocorrencias')
            ->join('funcionarios', 'funcionarios.id', '=', 'rh_ocorrencias.funcionario_id')
            ->select('rh_ocorrencias.*', 'funcionarios.nome as funcionario_nome')
            ->where('rh_ocorrencias.empresa_id', $empresaId);

        if (!empty($funcionarioId)) {
            $query->where('rh_ocorrencias.funcionario_id', $funcionarioId);
        }
        if (!empty($tipo)) {
            $query->where('rh_ocorrencias.tipo', $tipo);
        }

        $data = $query->orderBy('rh_ocorrencias.data_ocorrencia', 'desc')->paginate(env('PAGINACAO', 20));
    }

    $funcionarios = Funcionario::where('empresa_id', $empresaId)->orderBy('nome')->get();
    return view('rh.ocorrencias.index', compact('data', 'funcionarios', 'hasTable', 'funcionarioId', 'tipo'));
}

public function create()
{
    $funcionarios = Funcionario::where('empresa_id', RHContext::empresaId(request()))->orderBy('nome')->get();
    return view('rh.ocorrencias.create', compact('funcionarios'));
}

public function store(Request $request)
{
    if (!DB::getSchemaBuilder()->hasTable('rh_ocorrencias')) {
        return redirect()->route('rh.ocorrencias.index')->with('flash_erro', 'Tabela rh_ocorrencias não encontrada. Execute o SQL do módulo RH.');
    }

    $request->validate([
        'funcionario_id' => 'required',
        'tipo' => 'required',
        'titulo' => 'required|max:120',
        'data_ocorrencia' => 'required|date'
    ]);

    DB::table('rh_ocorrencias')->insert([
        'empresa_id' => RHContext::empresaId(request()),
        'funcionario_id' => $request->funcionario_id,
        'tipo' => $request->tipo,
        'titulo' => $request->titulo,
        'descricao' => $request->descricao ?? '',
        'data_ocorrencia' => $request->data_ocorrencia,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    return redirect()->route('rh.ocorrencias.index')->with('flash_sucesso', 'Ocorrência registrada com sucesso!');
}

public function edit($id)
{
    abort_unless(DB::getSchemaBuilder()->hasTable('rh_ocorrencias'), 404);
    $item = DB::table('rh_ocorrencias')->where('id', $id)->first();
    $funcionarios = Funcionario::where('empresa_id', RHContext::empresaId(request()))->orderBy('nome')->get();
    return view('rh.ocorrencias.edit', compact('item', 'funcionarios'));
}

public function update(Request $request, $id)
{
    abort_unless(DB::getSchemaBuilder()->hasTable('rh_ocorrencias'), 404);

    DB::table('rh_ocorrencias')->where('id', $id)->update([
        'funcionario_id' => $request->funcionario_id,
        'tipo' => $request->tipo,
        'titulo' => $request->titulo,
        'descricao' => $request->descricao ?? '',
        'data_ocorrencia' => $request->data_ocorrencia,
        'updated_at' => now(),
    ]);

    return redirect()->route('rh.ocorrencias.index')->with('flash_sucesso', 'Ocorrência atualizada com sucesso!');
}

public function destroy($id)
{
    abort_unless(DB::getSchemaBuilder()->hasTable('rh_ocorrencias'), 404);
    DB::table('rh_ocorrencias')->where('id', $id)->delete();
    return redirect()->route('rh.ocorrencias.index')->with('flash_sucesso', 'Ocorrência removida!');
}

}
