<?php

namespace App\Http\Controllers;

use App\Models\Funcionario;
use App\Models\RHFerias;
use App\Modules\RH\Support\Concerns\InteractsWithRH;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class RHFeriasController extends Controller
{
    use InteractsWithRH;

    public function index(Request $request)
    {
        $funcionarioId = $request->funcionario_id;
        $status = $request->status;
        $hasTable = Schema::hasTable('rh_ferias');
        $data = collect();

        if ($hasTable) {
            $data = RHFerias::with('funcionario')
                ->when($this->empresaId($request) > 0, fn ($q) => $q->where('empresa_id', $this->empresaId($request)))
                ->when(!empty($funcionarioId), fn ($q) => $q->where('funcionario_id', $funcionarioId))
                ->when(!empty($status), fn ($q) => $q->where('status', $status))
                ->orderByDesc('data_inicio')
                ->paginate($this->perPage());
        }

        $funcionarios = Funcionario::query()
            ->when($this->empresaId($request) > 0, fn ($q) => $q->where('empresa_id', $this->empresaId($request)))
            ->orderBy('nome')
            ->get();

        return view('rh.ferias.index', compact('data', 'funcionarios', 'hasTable', 'funcionarioId', 'status'));
    }

    public function create(Request $request)
    {
        $funcionarios = Funcionario::query()
            ->when($this->empresaId($request) > 0, fn ($q) => $q->where('empresa_id', $this->empresaId($request)))
            ->orderBy('nome')
            ->get();

        return view('rh.ferias.create', compact('funcionarios'));
    }

    public function store(Request $request)
    {
        if (!Schema::hasTable('rh_ferias')) {
            return redirect()->route('rh.ferias.index')->with('flash_erro', 'Tabela rh_ferias não encontrada. Execute o SQL do módulo RH.');
        }

        $this->validateForm($request);

        RHFerias::create([
            'empresa_id' => $this->empresaId($request),
            'funcionario_id' => $request->funcionario_id,
            'data_inicio' => $request->data_inicio,
            'data_fim' => $request->data_fim,
            'status' => $request->status,
            'observacao' => $request->observacao ?? '',
            'usuario_id' => auth()->id() ?? null,
        ]);

        return redirect()->route('rh.ferias.index')->with('flash_sucesso', 'Férias registradas com sucesso!');
    }

    public function edit(Request $request, $id)
    {
        $this->ensureAdmin();

        abort_unless(Schema::hasTable('rh_ferias'), 404);

        $item = RHFerias::query()
            ->when($this->empresaId($request) > 0, fn ($q) => $q->where('empresa_id', $this->empresaId($request)))
            ->findOrFail($id);

        $funcionarios = Funcionario::query()
            ->when($this->empresaId($request) > 0, fn ($q) => $q->where('empresa_id', $this->empresaId($request)))
            ->orderBy('nome')
            ->get();

        return view('rh.ferias.edit', compact('item', 'funcionarios'));
    }

    public function update(Request $request, $id)
    {
        $this->ensureAdmin();

        abort_unless(Schema::hasTable('rh_ferias'), 404);
        $this->validateForm($request);

        $item = RHFerias::query()
            ->when($this->empresaId($request) > 0, fn ($q) => $q->where('empresa_id', $this->empresaId($request)))
            ->findOrFail($id);

        $item->update([
            'funcionario_id' => $request->funcionario_id,
            'data_inicio' => $request->data_inicio,
            'data_fim' => $request->data_fim,
            'status' => $request->status,
            'observacao' => $request->observacao ?? '',
        ]);

        return redirect()->route('rh.ferias.index')->with('flash_sucesso', 'Férias atualizadas com sucesso!');
    }

    public function destroy(Request $request, $id)
    {
        $this->ensureAdmin();

        abort_unless(Schema::hasTable('rh_ferias'), 404);

        RHFerias::query()
            ->when($this->empresaId($request) > 0, fn ($q) => $q->where('empresa_id', $this->empresaId($request)))
            ->findOrFail($id)
            ->delete();

        return redirect()->route('rh.ferias.index')->with('flash_sucesso', 'Registro de férias removido!');
    }

    private function ensureAdmin(): void
    {
        abort_unless(((int) (optional(auth()->user())->adm ?? 0) === 1), 403, 'Acesso restrito ao administrador.');
    }

    private function validateForm(Request $request): void
    {
        $request->validate([
            'funcionario_id' => 'required',
            'data_inicio' => 'required|date',
            'data_fim' => 'required|date',
            'status' => 'required',
        ]);
    }
}
