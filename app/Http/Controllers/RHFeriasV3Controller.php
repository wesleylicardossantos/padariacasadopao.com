<?php

namespace App\Http\Controllers;

use App\Models\Funcionario;
use App\Models\RHFerias;
use Illuminate\Http\Request;
use App\Modules\RH\Support\RHContext;
use App\Support\SchemaSafe;
use Illuminate\Support\Facades\Schema;
use App\Services\RHFolhaLockService;

class RHFeriasV3Controller extends Controller
{
    public function index(Request $request)
    {
        if (!Schema::hasTable('rh_ferias')) {
            session()->flash('flash_erro', 'Tabela de férias ainda não instalada. Execute o SQL do patch RH V3.');
            return view('rh.ferias.index', [
                'data' => collect(),
                'semTabela' => true
            ]);
        }

        $funcionario = $request->funcionario;
        $status = $request->status;

        $data = RHFerias::with('funcionario')
            ->where('empresa_id', RHContext::empresaId(request()))
            ->when(!empty($funcionario), function ($q) use ($funcionario) {
                return $q->whereHas('funcionario', function($f) use ($funcionario){
                    $f->where('nome', 'like', "%$funcionario%");
                });
            })
            ->when($status !== null && $status !== '', function ($q) use ($status) {
                return $q->where('status', $status);
            })
            ->orderBy('data_inicio', 'desc')
            ->paginate(env("PAGINACAO"));

        return view('rh.ferias.index', compact('data', 'funcionario', 'status'));
    }

    public function create()
    {
        $funcionarios = Funcionario::where('empresa_id', RHContext::empresaId(request()))
            ->orderBy('nome')
            ->get();
        return view('rh.ferias.create', compact('funcionarios'));
    }

    public function store(Request $request)
    {
        if (RHFolhaLockService::bloquearSeFechada(RHContext::empresaId(request()), date('m', strtotime($request->data_inicio ?: date('Y-m-d'))), date('Y', strtotime($request->data_inicio ?: date('Y-m-d'))))) {
            session()->flash('flash_erro', 'Folha fechada para esta competência. Não é permitido cadastrar férias.');
            return redirect()->route('rh.ferias.index');
        }

        if (!Schema::hasTable('rh_ferias')) {
            session()->flash('flash_erro', 'Tabela de férias ainda não instalada. Execute o SQL do patch RH V3.');
            return redirect()->route('rh.ferias.index');
        }

        $request->validate([
            'funcionario_id' => 'required',
            'periodo_aquisitivo_inicio' => 'required|date',
            'periodo_aquisitivo_fim' => 'required|date',
            'data_inicio' => 'required|date',
            'data_fim' => 'required|date',
            'dias' => 'required|numeric',
        ]);

        RHFerias::create([
            ...SchemaSafe::filter('rh_ferias', [
                'empresa_id' => RHContext::empresaId(request()),
                'funcionario_id' => $request->funcionario_id,
                'periodo_aquisitivo_inicio' => $request->periodo_aquisitivo_inicio,
                'periodo_aquisitivo_fim' => $request->periodo_aquisitivo_fim,
                'data_inicio' => $request->data_inicio,
                'data_fim' => $request->data_fim,
                'dias' => $request->dias,
                'status' => $request->status ?? 'programada',
                'observacao' => $request->observacao,
                'usuario_id' => auth()->id() ?? null,
            ]),
        ]);

        session()->flash('flash_sucesso', 'Férias cadastradas com sucesso!');
        return redirect()->route('rh.ferias.index');
    }


    public function edit(Request $request, $id)
    {
        $this->ensureAdmin();
        abort_unless(Schema::hasTable('rh_ferias'), 404);

        $item = RHFerias::with('funcionario')
            ->where('empresa_id', RHContext::empresaId(request()))
            ->findOrFail($id);

        $funcionarios = Funcionario::where('empresa_id', RHContext::empresaId(request()))
            ->orderBy('nome')
            ->get();

        return view('rh.ferias.edit', compact('item', 'funcionarios'));
    }

    public function update(Request $request, $id)
    {
        $this->ensureAdmin();
        abort_unless(Schema::hasTable('rh_ferias'), 404);

        $request->validate([
            'funcionario_id' => 'required',
            'periodo_aquisitivo_inicio' => 'nullable|date',
            'periodo_aquisitivo_fim' => 'nullable|date',
            'data_inicio' => 'required|date',
            'data_fim' => 'required|date',
            'dias' => 'nullable|numeric',
        ]);

        $item = RHFerias::where('empresa_id', RHContext::empresaId(request()))->findOrFail($id);

        SchemaSafe::fillAndSave($item, [
            'funcionario_id' => $request->funcionario_id,
            'periodo_aquisitivo_inicio' => $request->periodo_aquisitivo_inicio,
            'periodo_aquisitivo_fim' => $request->periodo_aquisitivo_fim,
            'data_inicio' => $request->data_inicio,
            'data_fim' => $request->data_fim,
            'dias' => $request->dias ?? $item->dias,
            'status' => $request->status ?? $item->status,
            'observacao' => $request->observacao,
        ]);

        session()->flash('flash_sucesso', 'Férias atualizadas com sucesso!');
        return redirect()->route('rh.ferias.index');
    }

    public function destroy(Request $request, $id)
    {
        $this->ensureAdmin();
        abort_unless(Schema::hasTable('rh_ferias'), 404);

        RHFerias::where('empresa_id', RHContext::empresaId(request()))
            ->findOrFail($id)
            ->delete();

        session()->flash('flash_sucesso', 'Registro de férias removido!');
        return redirect()->route('rh.ferias.index');
    }

    private function ensureAdmin(): void
    {
        abort_unless(((int) (optional(auth()->user())->adm ?? 0) === 1), 403, 'Acesso restrito ao administrador.');
    }

}
