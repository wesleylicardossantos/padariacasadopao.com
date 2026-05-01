<?php

namespace App\Http\Controllers;

use App\Models\Funcionario;
use App\Models\RHFalta;
use App\Modules\RH\Http\Controllers\Concerns\InteractsWithRH;
use App\Services\RHFolhaLockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class RHFaltaController extends Controller
{
    use InteractsWithRH;

    public function index(Request $request)
    {
        if (!Schema::hasTable('rh_faltas')) {
            session()->flash('flash_erro', 'Tabela de faltas ainda não instalada. Execute o SQL do patch RH V4.');
            return view('rh.faltas.index', ['data' => collect(), 'semTabela' => true]);
        }

        $funcionario = $request->funcionario;
        $tipo = $request->tipo;

        $data = RHFalta::with('funcionario')
            ->where('empresa_id', $this->empresaId($request))
            ->when(!empty($funcionario), fn ($q) => $q->whereHas('funcionario', fn ($f) => $f->where('nome', 'like', '%' . $funcionario . '%')))
            ->when(!empty($tipo), fn ($q) => $q->where('tipo', $tipo))
            ->orderByDesc('data_referencia')
            ->paginate($this->perPage());

        return view('rh.faltas.index', compact('data', 'funcionario', 'tipo'));
    }

    public function create(Request $request)
    {
        $funcionarios = Funcionario::where('empresa_id', $this->empresaId($request))->orderBy('nome')->get();
        $tipos = RHFalta::tipos();

        return view('rh.faltas.create', compact('funcionarios', 'tipos'));
    }

    public function store(Request $request)
    {
        try {
            RHFolhaLockService::bloquearSeFechada($this->empresaId($request), null, null, 'Folha fechada. Não é possível registrar faltas/atestados na competência atual.');
        } catch (\RuntimeException $e) {
            session()->flash('flash_erro', $e->getMessage());
            return redirect()->back();
        }

        if (!Schema::hasTable('rh_faltas')) {
            session()->flash('flash_erro', 'Tabela de faltas ainda não instalada. Execute o SQL do patch RH V4.');
            return redirect()->route('rh.faltas.index');
        }

        $request->validate([
            'funcionario_id' => 'required',
            'tipo' => 'required',
            'data_referencia' => 'required|date',
        ]);

        RHFalta::create([
            'empresa_id' => $this->empresaId($request),
            'funcionario_id' => $request->funcionario_id,
            'tipo' => $request->tipo,
            'data_referencia' => $request->data_referencia,
            'quantidade_horas' => $request->quantidade_horas ?: null,
            'descricao' => $request->descricao,
            'usuario_id' => auth()->id() ?? null,
        ]);

        session()->flash('flash_sucesso', 'Ocorrência de ponto registrada com sucesso!');
        return redirect()->route('rh.faltas.index');
    }
}
